<?php
	date_default_timezone_set("Africa/Nairobi");
	$filename = "../../inc/gen_funcs.php";
	if (file_exists("../inc/gen_funcs.php")){
	    include('../inc/gen_funcs.php');
	}elseif (file_exists($filename)){
	    include('../../inc/gen_funcs.php');
	}else{}

//dash///	
function shift_sales(){
	global $connection;
	$shift=shift();
	if ($shift==0) {
		return 0;
	}else{
		return column_total("kp_sales",'amount',"shift='$shift'"); 
	}
}
function shift_purchases(){
	global $connection;
	$shift=shift();
	if ($shift==0) {
		return 0;
	}else{
		return column_total("kp_po",'amount',"shift='$shift'"); 
	}
}

function counter_req(){
	global $connection;
	$shift=shift(); 
		$get_total= mysqli_query($connection,"SELECT count(req_id) as  tt FROM kp_counter_req WHERE rec_status=2") or die(mysqli_error($connection));
		$n = mysqli_fetch_assoc($get_total); 
	return $n['tt']; 
}
function pending_purchase_approvals(){
	global $connection;
	$get_total = mysqli_query($connection,"SELECT count(po_id) as tt FROM kp_po WHERE rec_status=2 AND stock_status=2") or die(mysqli_error($connection));
	$n = mysqli_fetch_assoc($get_total);
	return $n['tt'];
}
function pending_purchase_receiving(){
	global $connection;
	$get_total = mysqli_query($connection,"SELECT count(po_id) as tt FROM kp_po WHERE rec_status=1 AND stock_status=2") or die(mysqli_error($connection));
	$n = mysqli_fetch_assoc($get_total);
	return $n['tt'];
}
function po_total($po_id){
	global $connection;
	$total_po = mysqli_query($connection,"SELECT SUM(total) AS gtotal FROM kp_po_items WHERE po_id='$po_id'") or die(mysqli_error($connection));
	$row = mysqli_fetch_assoc($total_po);
	return $row['gtotal'] ?: 0;
}
function is_front_po($refno){
	return strpos((string)$refno, 'FRONT BUY FOR ') === 0;
}
function front_po_counter_id($po_id){
	global $connection;
	$po = mysqli_query($connection,"SELECT req_id,refno FROM kp_po WHERE po_id='$po_id'") or die(mysqli_error($connection));
	$row = mysqli_fetch_assoc($po);
	if (!$row) {
		return 0;
	}
	if (!empty($row['req_id']) && (int)$row['req_id'] > 0) {
		return (int)$row['req_id'];
	}
	$refno = (string)$row['refno'];
	if (!is_front_po($refno)) {
		return 0;
	}
	$counter_name = trim(str_replace('FRONT BUY FOR ', '', $refno));
	if ($counter_name === '') {
		return 0;
	}
	return (int)get_column('kp_counters','cntr_id',"name='$counter_name'");
}
function front_po_counter_name($po_id){
	$counter = front_po_counter_id($po_id);
	if ($counter > 0) {
		return get_counter($counter);
	}
	return 'UNKNOWN';
}
////DASH///
?>
