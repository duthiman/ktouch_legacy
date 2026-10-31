<?php
session_start();
header('Content-Type: application/json; charset=UTF-8');
require_once '../../../inc/connection.php';
require_once '../../../inc/gen_funcs.php';
require_once __DIR__ . '/change_schema.php';
require_once '../../kwedia/api/funcs.php';
require_once '../../kwedia/api/bill_helpers.php';
require_once '../../kwedia/api/bill_logger.php';
require_once '../../kwedia/api/bill_repository.php';
require_once '../../kwedia/api/bill_service.php';
change_schema_boot($connection);

$user_id = (int)($_SESSION['user_id'] ?? 0);
$mode = strtolower(trim((string)($_REQUEST['mode'] ?? 'bill')));
$sale_id = (int)($_REQUEST['sale_id'] ?? 0);
$item_id = (int)($_REQUEST['item_id'] ?? 0);
$counter_id = (int)($_REQUEST['counter_id'] ?? 0);
$shift = (string)get_column('kp_shift', 'shift', '1');
$safe_shift = mysqli_real_escape_string($connection, $shift);
if ($user_id < 1 || $shift === '') { http_response_code(400); echo json_encode(['ok'=>false,'message'=>'Invalid session or active shift.']); exit; }

if ((string)($_REQUEST['action'] ?? '') === 'prepare_change') {
    $raw_items = (string)($_REQUEST['items'] ?? '');
    $selected = json_decode($raw_items, true);
    if (!is_array($selected) || !$selected) { http_response_code(400); echo json_encode(['ok'=>false,'message'=>'Select at least one item to change.']); exit; }
    $source_mode = strtolower(trim((string)($_REQUEST['source_mode'] ?? 'bill')));
    if (!in_array($source_mode, ['bill', 'item'], true)) { $source_mode = 'bill'; }
    $original_sale_id = (int)($selected[0]['sale_id'] ?? 0);
    $counter_id = (int)($selected[0]['counter_id'] ?? 0);
    if ($original_sale_id < 1) { http_response_code(400); echo json_encode(['ok'=>false,'message'=>'The selected sale is invalid.']); exit; }
    $clean=[]; $seen=[];
    foreach ($selected as $row) {
        $row_sale=(int)($row['sale_id'] ?? 0); $item=(int)($row['item_id'] ?? 0); $qty=(float)($row['change_qty'] ?? 0);
        $seen_key = $row_sale . ':' . $item;
        if (($source_mode === 'bill' && $row_sale !== $original_sale_id) || $row_sale < 1 || $item < 1 || $qty <= 0 || isset($seen[$seen_key])) { http_response_code(400); echo json_encode(['ok'=>false,'message'=>$source_mode === 'item' ? 'Selected items must be valid.' : 'All selected items must belong to one sale and be valid.']); exit; }
        $seen[$seen_key]=true; $clean[]=['sale_id'=>$row_sale,'item_id'=>$item,'qty'=>$qty];
    }
    $pending=mysqli_query($connection,"SELECT change_batch_id FROM kp_sale_changes WHERE user_id='".(int)$user_id."' AND status=2 LIMIT 1");
    if ($pending && mysqli_num_rows($pending)>0) { http_response_code(409); echo json_encode(['ok'=>false,'message'=>'You already have a pending change. Complete it before starting another.']); exit; }
    $sale_rows=[];
    foreach ($clean as $row) {
        $row_sale=(int)$row['sale_id'];
        if (isset($sale_rows[$row_sale])) { continue; }
        $sale_check=mysqli_query($connection,"SELECT sale_id,counter FROM kp_sales WHERE sale_id='".$row_sale."' AND user_id='".(int)$user_id."' AND shift='".mysqli_real_escape_string($connection,$shift)."' LIMIT 1");
        $sale_row=$sale_check?mysqli_fetch_assoc($sale_check):null;
        if (!$sale_row) { http_response_code(400); echo json_encode(['ok'=>false,'message'=>'One of the selected sales could not be found.']); exit; }
        $sale_rows[$row_sale]=$sale_row;
    }
    $counter_id=(int)($sale_rows[$original_sale_id]['counter'] ?? 0);
    foreach ($clean as $row) {
        $row_sale=(int)$row['sale_id']; $item=$row['item_id']; $available=mysqli_query($connection,"SELECT COALESCE(SUM(qty),0) AS qty FROM kp_sale_items WHERE sale_id='".$row_sale."' AND item_id='".$item."'");
        $available_row=$available?mysqli_fetch_assoc($available):null;
        if (!$available_row || (float)$available_row['qty'] < $row['qty']) { http_response_code(400); echo json_encode(['ok'=>false,'message'=>'One of the selected quantities is no longer available.']); exit; }
    }
    mysqli_begin_transaction($connection);
    $ok=true; $message=''; $batch_id=0;
    $insert=mysqli_query($connection,"INSERT INTO kp_sale_changes(original_sale_id,user_id,status) VALUES('".$original_sale_id."','".(int)$user_id."',2)");
    if (!$insert) {$ok=false;$message='Could not save the pending change.';} else $batch_id=(int)mysqli_insert_id($connection);
    if ($ok) foreach($clean as $row){$row_sale=(int)$row['sale_id'];$item=$row['item_id'];$qty=$row['qty'];$sql="INSERT INTO kp_sale_items_changes(sale_id,change_batch_id,old_item_id,qty,user_id,shift,status) VALUES('".$row_sale."','".$batch_id."','".$item."','".$qty."','".(int)$user_id."','".mysqli_real_escape_string($connection,$shift)."',2)";if(!mysqli_query($connection,$sql)){$ok=false;$message='Could not save all selected items.';break;}}
    if($ok) mysqli_commit($connection); else {mysqli_rollback($connection);http_response_code(500);echo json_encode(['ok'=>false,'message'=>$message]);exit;}
    $init=bill_service_initialise($user_id,$counter_id,1,0);
    if (!$init['ok'] || (int)($init['sale_id'] ?? 0)<1) { mysqli_query($connection,"DELETE FROM kp_sale_items_changes WHERE change_batch_id='".$batch_id."'"); mysqli_query($connection,"DELETE FROM kp_sale_changes WHERE change_batch_id='".$batch_id."'"); http_response_code(500); echo json_encode(['ok'=>false,'message'=>$init['message'] ?? 'Could not initialise the change sale.']); exit; }
    $new_sale_id=(int)$init['sale_id'];
    mysqli_query($connection,"UPDATE kp_sale_changes SET change_sale_id='".$new_sale_id."' WHERE change_batch_id='".$batch_id."' AND user_id='".(int)$user_id."'");
    mysqli_query($connection,"UPDATE kp_sale_items_changes SET change_sale_id='".$new_sale_id."' WHERE change_batch_id='".$batch_id."' AND user_id='".(int)$user_id."'");
    mysqli_query($connection,"UPDATE kp_sale_status SET sale_type='change' WHERE sale_id='".$new_sale_id."' AND user_id='".(int)$user_id."'");
    echo json_encode(['ok'=>true,'message'=>'Change sale initialized.','data'=>['batch_id'=>$batch_id,'sale_id'=>$new_sale_id,'redirect'=>'../kwedia/index.php?sale_id='.$new_sale_id.'&change=1']]); exit;
}

$sales=[];
$sales_query=mysqli_query($connection,"SELECT s.sale_id,s.shift,s.counter,COALESCE(c.name,CONCAT('Counter ',s.counter)) AS counter_name,COALESCE(SUM(si.qty),0) AS item_count,COALESCE(SUM(COALESCE(si.total,si.qty*si.price)),0) AS total FROM kp_sales s LEFT JOIN kp_sale_items si ON si.sale_id=s.sale_id LEFT JOIN kp_counters c ON c.cntr_id=s.counter WHERE s.user_id='$user_id' AND s.shift='$safe_shift' GROUP BY s.sale_id,s.shift,s.counter,c.name ORDER BY s.sale_id DESC");
while($sales_query&&$row=mysqli_fetch_assoc($sales_query)){$sales[]=$row;}

$items=[];
$items_query=mysqli_query($connection,"SELECT si.item_id,COALESCE(i.name,CONCAT('Item ',si.item_id)) AS item_name,s.counter AS counter_id,COALESCE(c.name,CONCAT('Counter ',s.counter)) AS counter_name,SUM(si.qty) AS item_count,SUM(COALESCE(si.total,si.qty*si.price)) AS total FROM kp_sale_items si INNER JOIN kp_sales s ON s.sale_id=si.sale_id LEFT JOIN kp_items i ON i.item_id=si.item_id LEFT JOIN kp_counters c ON c.cntr_id=s.counter WHERE s.user_id='$user_id' AND s.shift='$safe_shift' GROUP BY si.item_id,i.name,s.counter,c.name ORDER BY s.counter,i.name");
while($items_query&&$row=mysqli_fetch_assoc($items_query)){$items[]=$row;}

$order_items=[];
if($sale_id>0){$order_query=mysqli_query($connection,"SELECT si.item_id,si.stock_counter,si.qty,si.price,si.total,COALESCE(i.name,CONCAT('Item ',si.item_id)) AS item_name,COALESCE(c.name,CONCAT('Counter ',si.stock_counter)) AS counter_name FROM kp_sale_items si LEFT JOIN kp_items i ON i.item_id=si.item_id LEFT JOIN kp_counters c ON c.cntr_id=si.stock_counter INNER JOIN kp_sales s ON s.sale_id=si.sale_id WHERE si.sale_id='$sale_id' AND s.user_id='$user_id' AND s.shift='$safe_shift' ORDER BY si.id ASC");while($order_query&&$row=mysqli_fetch_assoc($order_query)){$order_items[]=$row;}}
$item_bills=[];
if($item_id>0){$counter_filter=$counter_id>0?" AND s.counter='$counter_id'":'';$item_bill_query=mysqli_query($connection,"SELECT si.item_id,si.qty,si.total,s.sale_id,s.counter AS counter_id,COALESCE(i.name,CONCAT('Item ',si.item_id)) AS item_name,COALESCE(c.name,CONCAT('Counter ',s.counter)) AS counter_name FROM kp_sale_items si INNER JOIN kp_sales s ON s.sale_id=si.sale_id LEFT JOIN kp_items i ON i.item_id=si.item_id LEFT JOIN kp_counters c ON c.cntr_id=s.counter WHERE si.item_id='$item_id' AND s.user_id='$user_id' AND s.shift='$safe_shift' $counter_filter ORDER BY s.sale_id DESC");while($item_bill_query&&$row=mysqli_fetch_assoc($item_bill_query)){$item_bills[]=$row;}}
echo json_encode(['ok'=>true,'data'=>['mode'=>$mode==='item'?'item':'bill','shift'=>$shift,'sales'=>$sales,'items'=>$items,'order_items'=>$order_items,'item_bills'=>$item_bills,'changes'=>[]]]);
