<?php
session_start(); 
include('../../inc/connection.php'); 
include('funcs.php');
header('Content-Type: application/json');
$url = "../../assets";
$con=$connection;
page_restrict();
$user_id = $_SESSION['user_id']; 
//salerestrict();
$shift = get_column('kp_shift','shift',"1");
$sale_id = isset($_POST['id']) ? $_POST['id'] : 0;
$cust_id = 0;
$arr = isset($_POST['pay-mode']) ? $_POST['pay-mode'] : [];
$invoices = isset($_POST['invoices']) ? $_POST['invoices'] : '';
$balance = isset($_POST['balance']) ? $_POST['balance'] : 0;
$order_total = isset($_POST['grand_total']) ? $_POST['grand_total'] : 0;
$type = isset($_POST['type']) ? $_POST['type'] : 0;
$orduser = isset($_POST['orduser']) ? $_POST['orduser'] : 0;

$sale_amount=$order_total;
$tendered = isset($_POST['tendered']) ? $_POST['tendered'] : 0;
// $msg="Thank you for visiting our store today, We appriciate your business";
// create_sms($cust_id,$msg);
function pay_json_response($status, $message, $extra = []) {
	echo json_encode(array_merge([
		'status' => $status,
		'message' => $message
	], $extra));
	exit;
}

if (empty($arr) OR !$arr) { 
	pay_json_response('warning', 'You have to select payment mode.');
}elseif (empty($tendered) OR $tendered==0) { 
	pay_json_response('warning', 'You have to input money given.');
}elseif ( $tendered<$sale_amount) { 
	pay_json_response('warning', 'You have to input money amounting to this order.');
}else{
	if (insert_payment()) {
		$gate = isset($_POST['gate']) ? $_POST['gate'] : '';  
		$garr = explode(",", $gate); 
		$update_sale = false;
		foreach($garr as $c) { 
			if (empty($c)) {
				// code...
			}else{
				$update_wallet=mysqli_query($con,"UPDATE split_message SET status=1 WHERE code='$c'") or die(mysqli_error($con));
			}
		}
		$iarr = explode(",", $invoices); 
		foreach($iarr as $b) { 
			if (empty($b)) {
				// code...
			}else{
				$update_sale=mysqli_query($connection,"UPDATE kp_sales SET pay_status=1,pay_date='$shift' WHERE sale_id='$b'") or die(mysqli_error($connection));
			}
		}
		if ($update_sale) { 
			pay_json_response('success', 'Payment recorded successfully.', [
				'reload' => true,
				'invoices' => $invoices
			]);
		}else{
			pay_json_response('error', 'System could not create payment history. Call for assistance.');
		} 
	}else{
		pay_json_response('error', 'System could not insert payments. Call for assistance.');
	}
		
}
