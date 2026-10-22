<?php
declare(strict_types=1);
namespace KTouch\Modules\Chaba\V1\Database;
use PDO;
use KTouch\Core\Logging\RequestLogger;
final class Schema
{
    public const COLUMNS = [
        'queue_id'=>'INT NOT NULL AUTO_INCREMENT', 'sale_id'=>'INT NOT NULL DEFAULT 0',
        'ref_id'=>'INT NOT NULL DEFAULT 0', 'print_type'=>"VARCHAR(55) NOT NULL DEFAULT 'bill'",
        'label'=>"VARCHAR(120) NOT NULL DEFAULT ''", 'cntr_id'=>'INT NOT NULL DEFAULT 0',
        'route_path'=>"VARCHAR(255) NOT NULL DEFAULT ''", 'route_query'=>'TEXT NULL',
        'requested_by'=>'INT NOT NULL DEFAULT 0', 'reprint_of'=>'INT NOT NULL DEFAULT 0',
        'status'=>'TINYINT UNSIGNED NOT NULL DEFAULT 0', 'print_attempts'=>'TINYINT UNSIGNED NOT NULL DEFAULT 0',
        'last_error'=>"VARCHAR(255) NOT NULL DEFAULT ''", 'shift'=>"VARCHAR(60) NOT NULL DEFAULT ''",
        'payload_json'=>'LONGTEXT NULL', 'items_json'=>'LONGTEXT NULL', 'created_at'=>'TIMESTAMP NOT NULL DEFAULT CURRENT_TIMESTAMP',
        'printed_at'=>'DATETIME NULL DEFAULT NULL',
    ];
    public const INDEXES = [
        'idx_queue_status_created'=>'status,created_at','idx_queue_counter_status'=>'cntr_id,status',
        'idx_queue_type_ref'=>'print_type,ref_id','idx_queue_shift_status'=>'shift,status','idx_queue_reprint'=>'reprint_of',
    ];
    public static function ensure(PDO $pdo, ?RequestLogger $logger=null): array
    {
        // The print worker polls frequently. Once the three Chaba tables exist,
        // avoid repeating SHOW COLUMNS/SHOW INDEX and DDL on every poll.
        $ready=$pdo->query("SELECT COUNT(*) FROM information_schema.tables WHERE table_schema=DATABASE() AND table_name IN ('kp_print_queue','kp_print_queue_printed','kp_print_reprints')")->fetchColumn();
        if((int)$ready===3)return ['success'=>true,'fast'=>true];
        $lock='chaba_schema_'.substr(hash('sha256',(string)$pdo->query('SELECT DATABASE()')->fetchColumn()),0,24);
        $stmt=$pdo->prepare('SELECT GET_LOCK(?,10)');$stmt->execute([$lock]);
        if((int)$stmt->fetchColumn()!==1) return ['success'=>false,'errors'=>['Chaba schema lock unavailable']];
        $errors=[];
        $run=static function(string $sql) use($pdo,$logger,&$errors): void {
            try {$pdo->exec($sql);} catch(\Throwable $e) {
                $message='Chaba schema operation failed: '.$e->getMessage();
                $errors[]=$message;
                error_log($message.' SQL: '.$sql);
                $logger?->log(['type'=>'chaba_schema_error','operation'=>$sql,'error'=>$e->getMessage()]);
            }
        };
        try {
            $audit=['reprint_id'=>'INT NOT NULL AUTO_INCREMENT','queue_id'=>'INT NOT NULL DEFAULT 0',
                'source_queue_id'=>'INT NOT NULL DEFAULT 0','print_type'=>"VARCHAR(55) NOT NULL DEFAULT ''",
                'ref_id'=>'INT NOT NULL DEFAULT 0','cntr_id'=>'INT NOT NULL DEFAULT 0',
                'requested_by'=>'INT NOT NULL DEFAULT 0','created_at'=>'TIMESTAMP NOT NULL DEFAULT CURRENT_TIMESTAMP'];
            $tables=[
                'kp_print_queue'=>[self::COLUMNS,self::INDEXES,'queue_id'],
                'kp_print_queue_printed'=>[self::COLUMNS,self::INDEXES+['idx_archive_printed_at'=>'printed_at','idx_archive_shift'=>'shift','idx_archive_type_ref'=>'print_type,ref_id'],'queue_id'],
                'kp_print_reprints'=>[$audit,['idx_reprints_queue'=>'queue_id','idx_reprints_source'=>'source_queue_id'],'reprint_id'],
            ];
            $legacy=(int)$pdo->query("SELECT COUNT(*) FROM information_schema.tables WHERE table_schema=DATABASE() AND table_name='kp_print_que_printed'")->fetchColumn();$current=(int)$pdo->query("SELECT COUNT(*) FROM information_schema.tables WHERE table_schema=DATABASE() AND table_name='kp_print_queue_printed'")->fetchColumn();if($legacy&& !$current)$run('RENAME TABLE kp_print_que_printed TO kp_print_queue_printed');
            foreach($tables as $table=>[$columns,$indexes,$primary]) {
                $definitions=[];
                foreach($columns as $name=>$type) $definitions[]="`{$name}` {$type}";
                // CREATE IF NOT EXISTS also works for pre-existing partial tables.
            if($table==='kp_print_queue_printed') $run('CREATE TABLE IF NOT EXISTS kp_print_queue_printed LIKE kp_print_queue');
            else $run('CREATE TABLE IF NOT EXISTS `'.$table.'` ('.implode(',',$definitions).", PRIMARY KEY (`{$primary}`)) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4");
                try {
                    $existing=array_column($pdo->query("SHOW COLUMNS FROM `{$table}`")->fetchAll(PDO::FETCH_ASSOC),'Field');
                    foreach($columns as $name=>$type) if(!in_array($name,$existing,true)) {
                        $run("ALTER TABLE `{$table}` ADD COLUMN `{$name}` {$type}".($name===$primary?' PRIMARY KEY':''));
                    }
                    $keys=array_column($pdo->query("SHOW INDEX FROM `{$table}`")->fetchAll(PDO::FETCH_ASSOC),'Key_name');
                    if(!in_array('PRIMARY',$keys,true)) $run("ALTER TABLE `{$table}` ADD PRIMARY KEY (`{$primary}`)");
                    foreach($indexes as $name=>$cols) if(!in_array($name,$keys,true)) $run("ALTER TABLE `{$table}` ADD INDEX `{$name}` ({$cols})");
                } catch(\Throwable $e) {
                    $errors[]="Cannot inspect {$table}: ".$e->getMessage();
                    error_log(end($errors));
                    $logger?->log(['type'=>'chaba_schema_error','table'=>$table,'error'=>$e->getMessage()]);
                }
            }
        } finally {$stmt=$pdo->prepare('SELECT RELEASE_LOCK(?)');$stmt->execute([$lock]);}
        return ['success'=>$errors===[],'errors'=>$errors];
    }
}
