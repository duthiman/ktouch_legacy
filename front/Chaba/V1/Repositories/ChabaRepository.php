<?php
declare(strict_types=1);
namespace KTouch\Modules\Chaba\V1\Repositories;
use PDO;
use KTouch\Core\Database\PdoFactory;
use KTouch\Core\Logging\RequestLogger;
use KTouch\Core\Exceptions\ApiException;
use KTouch\Modules\Chaba\V1\Database\Schema;
final class ChabaRepository
{
    public function __construct(private PdoFactory $factory,private ?RequestLogger $logger=null) {}
    public function db(): PDO { return $this->factory->create(); }
    public function transaction(PDO $pdo,callable $work): array {
        $pdo->beginTransaction();
        try {$result=$work();$pdo->commit();return $result;} catch(\Throwable $e) {if($pdo->inTransaction())$pdo->rollBack();throw $e;}
    }
    public function get(PDO $pdo,int $id,bool $archive=false,bool $lock=false): ?array {
        $table=$archive?'kp_print_queue_printed':'kp_print_queue';
        $s=$pdo->prepare("SELECT * FROM {$table} WHERE queue_id=?".($lock?' FOR UPDATE':''));$s->execute([$id]);
        return $s->fetch(PDO::FETCH_ASSOC)?:null;
    }
    public function insert(PDO $pdo,array $data): int {
        $columns=['sale_id','ref_id','print_type','label','cntr_id','route_path','route_query','requested_by','reprint_of','shift','payload_json','items_json'];
        if(!array_key_exists('items_json',$data)){ $decoded=json_decode((string)($data['payload_json']??''),true);$data['items_json']=is_array($decoded)&&isset($decoded['items'])?json_encode($decoded['items'],JSON_THROW_ON_ERROR):null; }
        $s=$pdo->prepare('INSERT INTO kp_print_queue ('.implode(',',$columns).') VALUES ('.implode(',',array_fill(0,count($columns),'?')).')');
        $s->execute(array_map(static fn($key)=>$data[$key]??null,$columns));return (int)$pdo->lastInsertId();
    }
    public function insertItems(PDO $pdo,int $queue,int $sale,array $items):void{$s=$pdo->prepare('INSERT INTO kp_print_queue_items(queue_id,sale_id,item_id,item_name,qty,unit_price,amount,attributes,payload_json) VALUES(?,?,?,?,?,?,?,?,?)');foreach($items as $item){$s->execute([$queue,$sale,(int)($item['item_id']??0),(string)($item['item_name']??$item['name']??''),(float)($item['qty']??0),(float)($item['unit_price']??0),(float)($item['amount']??0),isset($item['attributes'])?json_encode($item['attributes'],JSON_THROW_ON_ERROR):null,json_encode($item,JSON_THROW_ON_ERROR)]);}}
    public function claim(PDO $pdo,?int $counter): array {
        return $this->transaction($pdo,function() use($pdo,$counter) {
            $s=$pdo->prepare('SELECT * FROM kp_print_queue WHERE status=0 AND print_attempts<255'.($counter!==null?' AND cntr_id=?':'').' ORDER BY created_at,queue_id LIMIT 1 FOR UPDATE');
            $s->execute($counter!==null?[$counter]:[]);$row=$s->fetch(PDO::FETCH_ASSOC);
            if(!$row)return ['job'=>null];
            $pdo->prepare("UPDATE kp_print_queue SET status=3,print_attempts=print_attempts+1,last_error='',claimed_at=NOW(),lease_until=DATE_ADD(NOW(), INTERVAL 60 SECOND) WHERE queue_id=? AND status=0")->execute([$row['queue_id']]);
            $row['status']=3;$row['print_attempts']=(int)$row['print_attempts']+1;
            $row['claimed_at']=date('Y-m-d H:i:s');$row['lease_until']=date('Y-m-d H:i:s',time()+60);
            return ['job'=>$row];
        });
    }
    public function heartbeat(PDO $pdo,int $id,int $attempt): array {
        $s=$pdo->prepare("UPDATE kp_print_queue SET lease_until=DATE_ADD(NOW(), INTERVAL 60 SECOND) WHERE queue_id=? AND status=3 AND print_attempts=?");
        $s->execute([$id,$attempt]);
        if($s->rowCount()!==1) throw new ApiException('Job is not claimed by this print attempt',409,'chaba_state');
        return ['alive'=>true,'queue_id'=>$id,'print_attempts'=>$attempt,'lease_seconds'=>60];
    }
    public function complete(PDO $pdo,int $id,int $attempt): array {
        return $this->transaction($pdo,function() use($pdo,$id,$attempt) {
            $row=$this->get($pdo,$id,false,true);
            if(!$row) {
                $done=$this->get($pdo,$id,true);
                if($done&&(int)$done['status']===1&&(int)$done['print_attempts']===$attempt)return ['completed'=>true,'queue_id'=>$id,'already_completed'=>true];
            }
            $this->assertClaim($row,$attempt);
            $pdo->prepare("UPDATE kp_print_queue SET status=1,printed_at=NOW(),last_error='' WHERE queue_id=? AND status=3 AND print_attempts=?")->execute([$id,$attempt]);
            // Explicit columns accommodate independently healed legacy column order.
            $cols=implode(',',array_map(static fn($v)=>'`'.$v.'`',array_keys(Schema::COLUMNS)));
            $pdo->prepare("INSERT INTO kp_print_queue_printed ({$cols}) SELECT {$cols} FROM kp_print_queue WHERE queue_id=? AND status=1")->execute([$id]);
            $pdo->prepare('DELETE FROM kp_print_queue WHERE queue_id=? AND status=1')->execute([$id]);
            return ['completed'=>true,'queue_id'=>$id];
        });
    }
    public function fail(PDO $pdo,int $id,int $attempt,string $error): array {
        return $this->transaction($pdo,function() use($pdo,$id,$attempt,$error) {
            $this->assertClaim($this->get($pdo,$id,false,true),$attempt);
            $pdo->prepare('UPDATE kp_print_queue SET status=2,last_error=? WHERE queue_id=?')->execute([$error,$id]);
            return ['failed'=>true,'queue_id'=>$id];
        });
    }
    private function assertClaim(?array $row,int $attempt): void {
        if(!$row||(int)$row['status']!==3||(int)$row['print_attempts']!==$attempt)throw new ApiException('Job is not claimed by this print attempt',409,'chaba_state');
    }
    public function retry(PDO $pdo,int $id): array {
        $s=$pdo->prepare("UPDATE kp_print_queue SET status=0,last_error='' WHERE queue_id=? AND status=2 AND print_attempts<255");$s->execute([$id]);
        if($s->rowCount()!==1)throw new ApiException('Only failed jobs below 255 attempts can be retried',409,'chaba_state');
        return ['queued'=>true,'queue_id'=>$id];
    }
    public function reprint(PDO $pdo,int $source,int $user): array {
        return $this->transaction($pdo,function() use($pdo,$source,$user) {
            $row=$this->get($pdo,$source,true,true);
            if(!$row||(int)$row['status']!==1)throw new ApiException('Successfully printed source job not found',404,'chaba_job');
            if(empty($row['payload_json']))throw new ApiException('Legacy source has no frozen snapshot; enqueue a complete document first',409,'chaba_legacy');
            \KTouch\Modules\Chaba\V1\Services\Document::decode($row['payload_json']);
            $row['reprint_of']=$source;$row['requested_by']=$user;
            $id=$this->insert($pdo,$row);
            $pdo->prepare('INSERT INTO kp_print_reprints(queue_id,source_queue_id,print_type,ref_id,cntr_id,requested_by) VALUES(?,?,?,?,?,?)')->execute([$id,$source,$row['print_type'],$row['ref_id'],$row['cntr_id'],$user]);
            return ['queue_id'=>$id,'reprint_of'=>$source];
        });
    }
    public function list(PDO $pdo,int $status,?int $counter): array {
        $archive=$status===1;$table=$archive?'kp_print_queue_printed':'kp_print_queue';
        $s=$pdo->prepare("SELECT queue_id,sale_id,ref_id,print_type,label,cntr_id,status,print_attempts,last_error,shift,requested_by,reprint_of,created_at,printed_at FROM {$table} WHERE status=?".($counter!==null?' AND cntr_id=?':'').' ORDER BY queue_id DESC LIMIT 100');
        $s->execute($counter!==null?[$status,$counter]:[$status]);return ['items'=>$s->fetchAll(PDO::FETCH_ASSOC)];
    }
    public function report(PDO $pdo): array {
        // kp_shift is the single marker for the currently open shift.
        try {$shift=(string)($pdo->query('SELECT shift FROM kp_shift WHERE shift_id=1 LIMIT 1')->fetchColumn()?:'');} catch(\Throwable $e) {$shift='';}
        $where=$shift!==''?' WHERE shift=?':'';$args=$shift!==''?[$shift]:[];
        $sql="SELECT status,print_type,COUNT(*) total FROM (SELECT status,print_type FROM kp_print_queue{$where} UNION ALL SELECT status,print_type FROM kp_print_queue_printed{$where}) q GROUP BY status,print_type";
        $params=$shift!==''?[$shift,$shift]:[];$s=$pdo->prepare($sql);$s->execute($params);
        $counts=['queued'=>0,'completed'=>0,'failed'=>0,'printing'=>0,'other'=>0];$total=0;$bills=0;$printedBills=0;$bots=0;$kots=0;
        foreach($s->fetchAll(PDO::FETCH_ASSOC) as $row){$n=(int)$row['total'];$total+=$n;$type=strtoupper((string)$row['print_type']);$isBill=in_array($type,['BILL','CUSTOMER_BILL','RECEIPT'],true);if($isBill){$bills+=$n;if((int)$row['status']===1)$printedBills+=$n;}if($type==='BOT')$bots+=$n;if($type==='KOT')$kots+=$n;switch((int)$row['status']){case 0:$counts['queued']+=$n;break;case 1:$counts['completed']+=$n;break;case 2:$counts['failed']+=$n;break;case 3:$counts['printing']+=$n;break;default:$counts['other']+=$n;}}
        return ['shift'=>$shift,'receipts'=>$total,'by_status'=>$counts,'queued'=>$counts['queued'],'billsPrinted'=>$printedBills,'bots'=>$bots,'kots'=>$kots,'totalBills'=>$bills];
    }
}
