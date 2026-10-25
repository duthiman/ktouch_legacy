<?php
declare(strict_types=1);
namespace KTouch\Modules\Chaba\V1\Services;
use KTouch\Core\Auth\AuthContext;
use KTouch\Core\Exceptions\ValidationException;
use KTouch\Core\Exceptions\ApiException;
use KTouch\Modules\Chaba\V1\Repositories\ChabaRepository;
final class ChabaService
{
    public function __construct(private ChabaRepository $repo) {}
    public function handle(string $action,array $data,AuthContext $context): array {
        $pdo=$this->repo->db();
        $user=(int)($context->userId()??0);
        return match($action) {
            'ping'=>['status'=>'ok','module'=>'chaba','version'=>'v1','database'=>'connected','time'=>date('c')],
            'schema.ensure'=>['success'=>true,'schema'=>'chaba'],
            'templates.load'=>$this->templatesLoad(),
            'counters'=>$this->counters($pdo),
            'template.preview'=>$this->templatePreview($data),
            'enqueue'=>$this->enqueue($pdo,$data,$user),
            'list'=>$this->repo->list($pdo,self::id($data,'status',0,3),isset($data['cntr_id'])?self::id($data,'cntr_id',0):null),
            'report'=>$this->repo->report($pdo),
            'get'=>$this->document($pdo,$data,false),
            'render'=>$this->render($pdo,$data),
            'claim'=>$this->repo->claim($pdo,isset($data['cntr_id'])?self::id($data,'cntr_id',0):null),
            'next'=>$this->next($pdo,$data),
            'heartbeat'=>$this->repo->heartbeat($pdo,self::id($data,'queue_id'),self::id($data,'print_attempts',1,255)),
            'complete'=>$this->repo->complete($pdo,self::id($data,'queue_id'),self::id($data,'print_attempts',1,255)),
            'fail'=>$this->repo->fail($pdo,self::id($data,'queue_id'),self::id($data,'print_attempts',1,255),self::text($data,'last_error',255,'Printing failed')),
            'retry'=>$this->repo->retry($pdo,self::id($data,'queue_id')),
            'reprint'=>$this->repo->reprint($pdo,self::id($data,'source_queue_id'),$user),
            default=>throw new ValidationException('Unsupported Chaba action'),
        };
    }
    private function templatesLoad(): array { $base=dirname(__DIR__).'/Templates/Template1';$sections=['bill'=>['title','logo','header','items','tax','thank_you','paybill','qr','advertising'],'bot'=>['title','header','items'],'kot'=>['title','header','items'],'got'=>['title','header','items'],'cot'=>['title','header','items'],'wct'=>['title','header','summary'],'cct'=>['title','header','summary'],'csp'=>['title','header','gross_sales','item_totals','waiter_totals','returns','voids','changes','requisitions','units_generated','transfers','recipe_consumption','cancelled_orders'],'ssp'=>['title','header','total_sales','counter_sales','cashier_collections','customer_credit_bills','customer_credit_payments','waiter_totals'],'svp'=>['title','header','variance_summary','variance_items']];return ['template'=>'Template1','renders'=>array_map(static fn(string $type):array=>['type'=>strtoupper($type),'label'=>strtoupper($type),'file'=>$base.'/renders/'.$type.'.php','sections'=>$sections[$type]??[],'editable'=>true],array_keys($sections))]; }
    private function counters(\PDO $pdo): array {
        $hasPrinter = false;
        try {
            $q = $pdo->query("SHOW COLUMNS FROM kp_counters LIKE 'printer_name'");
            $hasPrinter = (bool)$q && (bool)$q->fetch(\PDO::FETCH_ASSOC);
        } catch (\Throwable $e) {}
        $sql = $hasPrinter
            ? 'SELECT cntr_id,name,dpt,printer_name FROM kp_counters WHERE del_status=2 ORDER BY dpt ASC,cntr_id ASC'
            : 'SELECT cntr_id,name,dpt FROM kp_counters WHERE del_status=2 ORDER BY dpt ASC,cntr_id ASC';
        try {
            $q = $pdo->query($sql);
            $rows = [];
            foreach ($q->fetchAll(\PDO::FETCH_ASSOC) as $row) {
                $rows[] = [
                    'cntr_id' => (int)($row['cntr_id'] ?? 0),
                    'id' => (int)($row['cntr_id'] ?? 0),
                    'name' => (string)($row['name'] ?? ''),
                    'counter_name' => (string)($row['name'] ?? ''),
                    'dpt' => (string)($row['dpt'] ?? ''),
                    'department' => (string)($row['dpt'] ?? ''),
                    'printer_name' => (string)($row['printer_name'] ?? ''),
                ];
            }
            return ['counters' => $rows];
        } catch (\Throwable $e) {
            throw new ApiException('Could not load counters: '.$e->getMessage(), 500, 'chaba_counters');
        }
    }
    private function templatePreview(array $d): array { $type=strtoupper(trim((string)($d['type']??'BILL')));if(!in_array(strtolower($type),['bill','bot','kot','got','cot','wct','cct','csp','ssp','svp'],true))throw new ValidationException('Unknown template type');$doc=['document_type'=>$type,'title'=>$d['title']??($type==='SVP'?'STOCK VARIANCE PRINT':($type==='SSP'?'SHIFT SUMMARY PRINT':($type==='CSP'?'COUNTER SUMMARY PRINT':($type==='WCT'?'WAITER CLEARANCE TICKET':($type==='CCT'?'CASHIER CLEARANCE TICKET':$type))))),'sections'=>$d['sections']??null,'header'=>$d['header']??['shift_date'=>date('Y-m-d'),'user'=>'Preview'],'items'=>$d['items']??[],'summary'=>$d['summary']??[],'footer'=>$d['footer']??'Template preview'];foreach(['show_logo','show_qr','show_tax','show_pin'] as $setting) if(array_key_exists($setting,$d)) $doc[$setting]=$d[$setting];return ['type'=>$type,'template'=>'Template1','document'=>$doc,'html'=>Document::html($doc)]; }
    private function enqueue(\PDO $pdo,array $d,int $user): array {
        $row=['sale_id'=>self::id($d,'sale_id',0),'ref_id'=>self::id($d,'ref_id',0),
            'cntr_id'=>self::id($d,'cntr_id',0),'requested_by'=>$user,'reprint_of'=>0,
            'print_type'=>self::text($d,'print_type',55,'bill'),'label'=>self::text($d,'label',120),
            'route_path'=>self::text($d,'route_path',255),'route_query'=>self::text($d,'route_query',65535),
            'shift'=>self::text($d,'shift',60,$this->currentShift($pdo))];
        if($row['print_type']==='')throw new ValidationException('print_type is required');
        $row['payload_json']=Document::build($d['payload_json']??$d['payload']??null,array_merge($this->companyHeader($pdo),[
            'document_type'=>$row['print_type'],'sale_id'=>$row['sale_id'],'ref_id'=>$row['ref_id'],
            'counter_id'=>$row['cntr_id'],'counter_name'=>$this->counterName($pdo,(int)$row['cntr_id']),'shift'=>$row['shift'],'requested_by'=>$user,
        ]));
        $queue=$this->repo->insert($pdo,$row);return ['queue_id'=>$queue,'status'=>0];
    }
    private function document(\PDO $pdo,array $d,bool $render): array {
        $id=self::id($d,'queue_id');$row=$this->repo->get($pdo,$id)??$this->repo->get($pdo,$id,true);
        if(!$row)throw new ApiException('Print job not found',404,'chaba_job');
        $legacy=empty($row['payload_json']);
        if($legacy) {
            if((string)($row['route_path']??'')==='render_counter_sum.php') {
                $query=[];parse_str((string)($row['route_query']??''),$query);
                $previousGet=$_GET;$_GET=array_merge($_GET,$query);
                $legacyRenderer=dirname(__DIR__,3).'/chaba-legacy/render_counter_sum.php';
                if(!is_file($legacyRenderer)) throw new ApiException('Counter summary renderer not found',404,'chaba_renderer');
                $legacyConnectionFile=dirname(__DIR__,4).'/inc/connection.php';
                if(is_file($legacyConnectionFile)) require_once $legacyConnectionFile;
                if(isset($connection) && $connection instanceof \mysqli) $GLOBALS['connection']=$connection;
                ob_start();
                try { include $legacyRenderer; $html=ob_get_clean(); }
                catch(\Throwable $e) { if(ob_get_level()>0)ob_end_clean();$_GET=$previousGet;throw new ApiException('Counter summary render failed: '.$e->getMessage(),409,'chaba_renderer'); }
                $_GET=$previousGet;
                return ['job'=>$row,'document'=>['document_type'=>'COUNTER_SUMMARY'],'legacy'=>true]+($render?['html'=>$html]:[]);
            }
            // Legacy batches are keyed by queue ID; snapshot jobs never take this path.
            try {
                $s=$pdo->prepare('SELECT * FROM kp_print_ticket_items WHERE batch_id=? ORDER BY id');
                $s->execute([$id]);$items=$s->fetchAll(\PDO::FETCH_ASSOC);
            } catch(\PDOException $e) {throw new ApiException('Legacy ticket items are unavailable; enqueue a complete snapshot',409,'chaba_legacy');}
            if(!$items)throw new ApiException('Legacy print job has no ticket items',409,'chaba_legacy');
            foreach($items as &$item) {
                $name=trim((string)($item['name']??$item['item_name']??''));
                if($name==='') foreach([
                    ['kp_items','name'],
                    ['kp_inventory_master','item_name'],
                    ['kp_inventory_master','name'],
                    ['kp_inventory_items','item_name'],
                ] as [$table,$column]) {
                    try {$q=$pdo->prepare("SELECT {$column} FROM {$table} WHERE item_id=? LIMIT 1");$q->execute([(int)($item['item_id']??0)]);$name=trim((string)($q->fetchColumn()?:''));} catch(\Throwable $e) {$name='';}
                    if($name!=='') break;
                }
                $item['name']=$name!==''?$name:'Item '.(int)($item['item_id']??0);
            }
            unset($item);
            $doc=Document::decode(Document::build(['items'=>$items],['document_type'=>$row['print_type'],'sale_id'=>$row['sale_id'],'ref_id'=>$row['ref_id'],'shift'=>$row['shift']]));
        } else {$doc=Document::decode($row['payload_json']);$doc['header']=array_replace($this->companyHeader($pdo),is_array($doc['header']??null)?$doc['header']:[]);if(!empty($row['print_type'])){$type=strtoupper((string)$row['print_type']);$doc['document_type']=$type;if(empty($doc['title']))$doc['title']=$type==='BOT'?'BAR ORDER TICKET':($type==='KOT'?'KITCHEN ORDER TICKET':($type==='CUSTOMER_BILL'?'CUSTOMER BILL':$type));}}
        $queueHeader=$this->queueHeader($pdo,$row);$doc['header']=array_replace($queueHeader,is_array($doc['header']??null)?$doc['header']:[]);foreach(['company_name','company_tagline','company_address','company_phone','logo_src','counter_name','sale_id','shift','print_time','waiter_name'] as $key)if((string)($doc['header'][$key]??'')==='')$doc['header'][$key]=$queueHeader[$key]??'';
        return ['job'=>$row,'document'=>$doc,'legacy'=>$legacy]+($render?['html'=>Document::html($doc)]:[]);
    }
    private function render(\PDO $pdo,array $d): array {
        // Render-test fallback only: it never creates, claims, or completes a queue job.
        if(array_key_exists('sale_id',$d)&&!array_key_exists('queue_id',$d)) {
            $saleId=self::id($d,'sale_id');
            $q=$pdo->prepare('SELECT queue_id FROM kp_print_queue WHERE sale_id=? ORDER BY queue_id DESC LIMIT 1');$q->execute([$saleId]);$queueId=(int)($q->fetchColumn()?:0);
            if($queueId>0)return$this->document($pdo,['queue_id'=>$queueId],true);
            $type=isset($d['document_type'])?strtoupper(trim((string)$d['document_type'])):'';
            // sale_id mode is a design/test render from sale tables; queue_id mode is the exact frozen print snapshot.
            return $this->saleDocument($pdo,$saleId,true,$type!==''?$type:'BILL');
        }
        return $this->document($pdo,$d,true);
    }
    private function saleDocument(\PDO $pdo,int $saleId,bool $render,string $documentType='BILL'): array {
        $sale=null;$saleTable='kp_shift_sales';
        foreach(['kp_shift_sales','kp_sales'] as $table) {
            $q=$pdo->prepare("SELECT sale_id,shift_history_id,shift_date,counter_id,waiter_id,bill_status,subtotal,discount,tax,total,paid_total,balance,created_at,updated_at FROM {$table} WHERE sale_id=? LIMIT 1");
            $q->execute([$saleId]);$sale=$q->fetch(\PDO::FETCH_ASSOC);
            if($sale){$saleTable=$table;break;}
        }
        if(!$sale)throw new ApiException('Sale not found',404,'chaba_sale');
        $itemsTable=$saleTable==='kp_shift_sales'?'kp_shift_sale_items':'kp_sale_items';
        $q=$pdo->prepare("SELECT item_id,item_name,qty,unit_price,amount,total,attributes FROM {$itemsTable} WHERE sale_id=? ORDER BY sale_item_id");
        $q->execute([$saleId]);$items=$q->fetchAll(\PDO::FETCH_ASSOC);
        if(!$items&&$itemsTable==='kp_shift_sale_items') {
            $q=$pdo->prepare('SELECT item_id,item_name,qty,unit_price,amount,total,attributes FROM kp_sale_items WHERE sale_id=? ORDER BY sale_item_id');
            $q->execute([$saleId]);$items=$q->fetchAll(\PDO::FETCH_ASSOC);
        }
        if(!$items)throw new ApiException('Sale has no items to render',409,'chaba_sale_items');
        $total=0;
        foreach($items as &$item) {
            $item['item_name']=(string)($item['item_name']??('Item '.(int)$item['item_id']));
            $item['qty']=(float)($item['qty']??0);$item['unit_price']=(float)($item['unit_price']??0);
            $item['amount']=(float)($item['amount']??($item['total']??($item['qty']*$item['unit_price'])));
            $total+=(float)$item['amount'];
        }
        unset($item);
        $title=$documentType==='BOT'?'BAR ORDER TICKET':($documentType==='KOT'?'KITCHEN ORDER TICKET':($documentType==='CUSTOMER_BILL'?'CUSTOMER BILL':'TEST BILL'));
        $summary=['gross'=>(float)($sale['subtotal']??$total),'discount'=>(float)($sale['discount']??0),'tax'=>(float)($sale['tax']??0),'vat'=>(float)($sale['tax']??0),'total'=>(float)($sale['total']??$total),'paid_total'=>(float)($sale['paid_total']??0),'balance'=>(float)($sale['balance']??0)];
        $doc=Document::decode(Document::build(['document_type'=>$documentType,'title'=>$title,'items'=>$items,'summary'=>$summary],array_merge($this->companyHeader($pdo),[
            'sale_id'=>$saleId,
            'counter_id'=>(int)($sale['counter_id']??0),'shift_date'=>$sale['shift_date']??null,
            'waiter_id'=>(int)($sale['waiter_id']??0),'print_time'=>date('Y-m-d H:i:s'),
        ])));
        return ['job'=>null,'sale_id'=>$saleId,'source'=>'sale','document'=>$doc,'legacy'=>false]+($render?['html'=>Document::html($doc)]:[]);
    }
    private function queueHeader(\PDO $pdo,array $row):array{$h=$this->companyHeader($pdo)+['sale_id'=>$row['sale_id']??null,'counter_id'=>$row['cntr_id']??null,'counter_name'=>$this->counterName($pdo,(int)($row['cntr_id']??0)),'shift'=>((string)($row['shift']??'')!==''?(string)$row['shift']:$this->currentShift($pdo)),'print_time'=>date('Y-m-d H:i:s'),'requested_by'=>$row['requested_by']??null];try{$q=$pdo->prepare('SELECT username FROM kp_users WHERE user_id=? LIMIT 1');$q->execute([(int)($row['requested_by']??0)]);$name=(string)($q->fetchColumn()?:'');$h['served_by']=$name;$h['waiter_name']=$name;}catch(\Throwable $e){}return$h;}
    private function counterName(\PDO $pdo,int $id):string{if($id<1)return'';try{$q=$pdo->prepare('SELECT name FROM kp_counters WHERE cntr_id=? LIMIT 1');$q->execute([$id]);return trim((string)($q->fetchColumn()?:''));}catch(\Throwable $e){return'';}}
    private function currentShift(\PDO $pdo):string{try{$v=$pdo->query('SELECT shift FROM kp_shift LIMIT 1')->fetchColumn();return trim((string)$v);}catch(\Throwable $e){return'';}}
    private function companyHeader(\PDO $pdo):array
    {
        $out=[];
        try {
            $q=$pdo->query('SELECT company_name,company_tag,address,phone1,phone2,image_path,thermal_logo_path FROM kp_settings_gen LIMIT 1');
            $row=$q?$q->fetch(\PDO::FETCH_ASSOC):null;
            if($row){$out['company_name']=(string)($row['company_name']??'');$out['company_tagline']=(string)($row['company_tag']??'');$out['company_address']=(string)($row['address']??'');$out['company_phone']=trim((string)($row['phone1']??'').' '.(string)($row['phone2']??''));$out['logo_src']=(string)($row['thermal_logo_path']??$row['image_path']??'');}
        } catch(\Throwable $e) {}
        try {
            $row=$pdo->query('SELECT show_logo,show_qr,thankyou_note FROM kp_settings_print WHERE print_doc=1 LIMIT 1')->fetch(\PDO::FETCH_ASSOC);
            if($row){$out['show_logo']=(int)($row['show_logo']??0)===1;$out['show_qr']=(int)($row['show_qr']??0)===1;$out['thankyou_note']=(string)($row['thankyou_note']??'');}
        } catch(\Throwable $e) {}
        if(!$out){
            $keys=['company_name','company_tagline','company_address','company_phone','company_logo_url'];$in=implode(',',array_fill(0,count($keys),'?'));
            try{$q=$pdo->prepare("SELECT d.setting_key,COALESCE(NULLIF(v.setting_value,''),d.default_value) value FROM kp_setting_definitions d LEFT JOIN kp_setting_values v ON v.setting_id=d.setting_id AND v.scope_type='SYSTEM' AND v.scope_id=0 WHERE d.setting_key IN ({$in})");$q->execute($keys);foreach($q->fetchAll(\PDO::FETCH_ASSOC) as $row){$map=['company_logo_url'=>'logo_src'];$out[$map[$row['setting_key']]??$row['setting_key']]=(string)$row['value'];}}catch(\Throwable $e){}
        }
        return $out;
    }
    private function next(\PDO $pdo,array $d):array
    {
        // A worker poll must not be poisoned by an old/incomplete queue row.
        // Claim and fail incompatible rows, then continue until a V1 snapshot
        // is available or the queue is empty.
        for ($attempt = 0; $attempt < 10; $attempt++) {
            $claimed = $this->repo->claim($pdo, null);
            if (empty($claimed['job'])) return ['job' => null];
            $job = $claimed['job'];
            try {
                $result = $this->document($pdo, ['queue_id' => (int)$job['queue_id']], true);
                $result['routing'] = [
                    'counter_id' => (int)($job['cntr_id'] ?? 0),
                    'document_type' => (string)($job['print_type'] ?? 'BILL'),
                    'label' => (string)($job['label'] ?? ''),
                ];
                return $result;
            } catch (\Throwable $e) {
                try {
                    $this->repo->fail($pdo, (int)$job['queue_id'], (int)$job['print_attempts'],
                        'V1 cannot render this queue job: '.substr($e->getMessage(), 0, 220));
                } catch (\Throwable $failError) {
                    error_log('Chaba V1 could not fail queue job '.$job['queue_id'].': '.$failError->getMessage());
                }
            }
        }
        return ['job' => null, 'message' => 'No renderable V1 job available'];
    }
    private static function id(array $d,string $key,int $min=1,int $max=2147483647): int {
        $v=$d[$key]??($min===0?0:null);
        if(filter_var($v,FILTER_VALIDATE_INT)===false||$v===null||(int)$v<$min||(int)$v>$max)throw new ValidationException("{$key} must be an integer between {$min} and {$max}");
        return (int)$v;
    }
    private static function text(array $d,string $key,int $max,string $default=''): string {
        $v=$d[$key]??$default;
        if(!is_string($v)||strlen($v)>$max)throw new ValidationException("{$key} must be text of at most {$max} bytes");
        return trim($v);
    }
}
