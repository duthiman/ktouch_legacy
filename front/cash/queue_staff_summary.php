<?php
session_start();
require_once __DIR__ . '/../../inc/connection.php';
require_once __DIR__ . '/funcs.php';
require_once __DIR__ . '/../chaba/funcs.php';
header('Content-Type: application/json; charset=utf-8');
$staffId=(int)($_POST['staff_id']??0);$userId=(int)($_SESSION['user_id']??0);
if($staffId<1){echo json_encode(['ok'=>false,'message'=>'Invalid waiter selected.']);exit;}
$counter=(int)get_column('kp_users','counter',"user_id='$userId'");
$queueId=kp_print_enqueue($connection,['sale_id'=>0,'ref_id'=>$staffId,'print_type'=>'staff_sum','label'=>'STAFF SUMMARY','cntr_id'=>$counter,'route_path'=>'front/chaba/print_staff_summary.php','route_query'=>http_build_query(['id'=>$staffId]),'requested_by'=>$userId]);
echo json_encode(['ok'=>$queueId>0,'message'=>$queueId>0?'Staff summary queued for printing.':'Could not queue staff summary.','queue_id'=>$queueId]);
