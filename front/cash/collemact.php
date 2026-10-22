<?php
session_start();
include('../../inc/connection.php');
include('funcs.php'); 
$user_id=$_SESSION['user_id'];
$pin = isset($_REQUEST['pass']) ? trim($_REQUEST['pass']) : '';
$amount = isset($_REQUEST['amount']) ? (float)$_REQUEST['amount'] : 0;
$id = isset($_REQUEST['paid']) ? (int)$_REQUEST['paid'] : 0;
$shift = get_column('kp_shift','shift',"1");
$today=date("Y-m-d"); 
header('Content-Type: application/json');

if ($id < 1 || $amount <= 0) {
    echo json_encode([
        'status' => 'warning',
        'message' => 'Invalid collection request. Enter a valid amount and try again.'
    ]);
    exit;
}

if ($pin === '') {
    echo json_encode([
        'status' => 'warning',
        'message' => 'Passcode is required for supervisor approval.'
    ]);
    exit;
}

if (!front_perm($pin,8)) {
    echo json_encode([
        'status' => 'warning',
        'message' => 'Wrong passcode or insufficient approval rights.'
    ]);
    exit;
}

$pin_safe = mysqli_real_escape_string($connection, $pin);
$appr_by = (int)get_column("kp_users","user_id","pin='$pin_safe'");
if ($appr_by < 1) {
    echo json_encode([
        'status' => 'error',
        'message' => 'Approver account could not be validated.'
    ]);
    exit;
}

$insert_reprint = mysqli_query($connection,"INSERT INTO kp_cash_collection(pay_id,user_id,appr_by,shift,day,amount) VALUES('$id','$user_id','$appr_by','$shift',CURRENT_DATE,'$amount')");
if (!$insert_reprint) {
    echo json_encode([
        'status' => 'error',
        'message' => 'Failed to record collection history: '.mysqli_error($connection)
    ]);
    exit;
}

$update_bal = mysqli_query($connection,"UPDATE kp_pay_mode SET accbal=accbal-$amount WHERE pay_id='$id'");
if (!$update_bal) {
    echo json_encode([
        'status' => 'error',
        'message' => 'Failed to update payment mode balance: '.mysqli_error($connection)
    ]);
    exit;
}

echo json_encode([
    'status' => 'success',
    'message' => 'Cash collection approved and posted successfully.'
]);
?>
