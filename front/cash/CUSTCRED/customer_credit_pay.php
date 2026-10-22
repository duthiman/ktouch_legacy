<?php
if (session_status() === PHP_SESSION_NONE) {
  session_start();
}
if (file_exists('../../../inc/connection.php')) {
  include('../../../inc/connection.php');
} elseif (file_exists('../../inc/connection.php')) {
  include('../../inc/connection.php');
}
require_once('../funcs.php');
require_once('../../chaba/funcs.php');
header('Content-Type: application/json');

function customer_credit_pay_json($status, $message, $extra = []) {
  echo json_encode(array_merge([
    'status' => $status,
    'message' => $message
  ], $extra));
  exit;
}

function ensure_customer_credit_pay_tables() {
  global $connection;
  mysqli_query($connection,"CREATE TABLE IF NOT EXISTS kp_customer_credit_payments (
    credit_pay_id INT NOT NULL AUTO_INCREMENT,
    cust_id INT NOT NULL DEFAULT 0,
    user_id INT NOT NULL DEFAULT 0,
    amount DECIMAL(10,2) NOT NULL DEFAULT 0,
    pay_mode_id INT NOT NULL DEFAULT 0,
    reference_no VARCHAR(120) NOT NULL DEFAULT '',
    note TEXT NULL,
    shift_date DATE NOT NULL,
    created_at TIMESTAMP NOT NULL DEFAULT CURRENT_TIMESTAMP,
    PRIMARY KEY (credit_pay_id)
  )") or die(mysqli_error($connection));
  mysqli_query($connection,"CREATE TABLE IF NOT EXISTS kp_customer_credit_payment_items (
    credit_item_id INT NOT NULL AUTO_INCREMENT,
    credit_pay_id INT NOT NULL DEFAULT 0,
    cust_id INT NOT NULL DEFAULT 0,
    sale_id INT NOT NULL DEFAULT 0,
    ord_date DATE NOT NULL,
    pay_date DATE NOT NULL,
    amount DECIMAL(10,2) NOT NULL DEFAULT 0,
    PRIMARY KEY (credit_item_id)
  )") or die(mysqli_error($connection));
  mysqli_query($connection,"CREATE TABLE IF NOT EXISTS kp_customer_credit_payment_modes (
    credit_mode_id INT NOT NULL AUTO_INCREMENT,
    credit_pay_id INT NOT NULL DEFAULT 0,
    pay_mode_id INT NOT NULL DEFAULT 0,
    amount DECIMAL(10,2) NOT NULL DEFAULT 0,
    reference_no VARCHAR(120) NOT NULL DEFAULT '',
    PRIMARY KEY (credit_mode_id)
  )") or die(mysqli_error($connection));
}

$user_id = isset($_SESSION['user_id']) ? (int)$_SESSION['user_id'] : 0;
$cust_id = isset($_POST['cust_id']) ? (int)$_POST['cust_id'] : 0;
$invoices = isset($_POST['invoices']) ? trim($_POST['invoices']) : '';
$arr = isset($_POST['pay-mode']) ? $_POST['pay-mode'] : [];
$tendered = isset($_POST['tendered']) ? (float)$_POST['tendered'] : 0;
$note = isset($_POST['note']) ? trim($_POST['note']) : '';
$shift = get_column('kp_shift','shift',"1");
if ($shift == 0) {
  $shift = date('Y-m-d');
}

ensure_customer_credit_pay_tables();

if ($user_id < 1) {
  customer_credit_pay_json('error', 'Session expired. Please log in again.');
}
if ($cust_id < 1) {
  customer_credit_pay_json('warning', 'Select a valid customer.');
}
if ($invoices === '') {
  customer_credit_pay_json('warning', 'Select at least one outstanding invoice.');
}
if (empty($arr)) {
  customer_credit_pay_json('warning', 'Select at least one payment mode.');
}

$invoice_ids = array_filter(array_map('intval', explode(',', $invoices)));
if (empty($invoice_ids)) {
  customer_credit_pay_json('warning', 'No valid invoices selected.');
}

$selected_total = 0;
$invoice_rows = [];
foreach ($invoice_ids as $sale_id) {
  $sale_q = mysqli_query($connection,"SELECT sale_id,shift,sale_bal,pay_status FROM kp_sales WHERE sale_id='$sale_id' AND cust_id='$cust_id' LIMIT 1") or die(mysqli_error($connection));
  if (mysqli_num_rows($sale_q) < 1) {
    continue;
  }
  $sale = mysqli_fetch_assoc($sale_q);
  if ((int)$sale['pay_status'] != 3 || (float)$sale['sale_bal'] <= 0) {
    continue;
  }
  $invoice_rows[] = $sale;
  $selected_total += (float)$sale['sale_bal'];
}

if ($selected_total <= 0 || empty($invoice_rows)) {
  customer_credit_pay_json('warning', 'Selected invoices are no longer outstanding.');
}

$mode_total = 0;
$mode_rows = [];
foreach ($arr as $mode_id) {
  $mode_id = (int)$mode_id;
  if ($mode_id < 1) {
    continue;
  }
  $amount_key = 'name'.$mode_id;
  $ref_key = 'ref'.$mode_id;
  $mode_amount = isset($_POST[$amount_key]) ? (float)$_POST[$amount_key] : 0;
  $mode_ref = isset($_POST[$ref_key]) ? trim($_POST[$ref_key]) : '';
  if ($mode_amount <= 0) {
    continue;
  }
  $mode_rows[] = [
    'pay_mode_id' => $mode_id,
    'amount' => $mode_amount,
    'reference_no' => $mode_ref
  ];
  $mode_total += $mode_amount;
}

if (empty($mode_rows)) {
  customer_credit_pay_json('warning', 'Enter payment amount for the selected payment mode.');
}

if ($mode_total <= 0 || $tendered <= 0) {
  customer_credit_pay_json('warning', 'Enter a valid payment amount.');
}
if (abs($mode_total - $tendered) > 0.009) {
  customer_credit_pay_json('warning', 'Payment mode total must match the payment total.');
}
if ($tendered - $selected_total > 0.009) {
  customer_credit_pay_json('warning', 'Payment cannot be more than the selected credit balance.');
}

$note_safe = mysqli_real_escape_string($connection, $note);
$credit_pay_id = pay_id();
mysqli_query($connection,"INSERT INTO kp_customer_credit_payments(credit_pay_id,cust_id,user_id,amount,pay_mode_id,reference_no,note,shift_date) VALUES('$credit_pay_id','$cust_id','$user_id','$tendered','0','','{$note_safe}','$shift')") or die(mysqli_error($connection));

foreach ($mode_rows as $mode_row) {
  $ref_safe = mysqli_real_escape_string($connection, $mode_row['reference_no']);
  mysqli_query($connection,"INSERT INTO kp_customer_credit_payment_modes(credit_pay_id,pay_mode_id,amount,reference_no) VALUES('$credit_pay_id','".$mode_row['pay_mode_id']."','".$mode_row['amount']."','$ref_safe')") or die(mysqli_error($connection));
}

 $remaining_payment = $tendered;
foreach ($invoice_rows as $sale) {
  if ($remaining_payment <= 0) {
    break;
  }
  $sale_id = (int)$sale['sale_id'];
  $ord_date = mysqli_real_escape_string($connection, $sale['shift']);
  $sale_balance = (float)$sale['sale_bal'];
  $amount = $remaining_payment >= $sale_balance ? $sale_balance : $remaining_payment;
  $new_balance = $sale_balance - $amount;
  mysqli_query($connection,"INSERT INTO kp_customer_credit_payment_items(credit_pay_id,cust_id,sale_id,ord_date,pay_date,amount) VALUES('$credit_pay_id','$cust_id','$sale_id','$ord_date','$shift','$amount')") or die(mysqli_error($connection));
  if ($new_balance <= 0.009) {
    mysqli_query($connection,"UPDATE kp_sales SET sale_bal=0,pay_status=1,pay_date='$shift' WHERE sale_id='$sale_id'") or die(mysqli_error($connection));
    mysqli_query($connection,"UPDATE kp_sale_items SET pay_status=1 WHERE sale_id='$sale_id'") or die(mysqli_error($connection));
  } else {
    mysqli_query($connection,"UPDATE kp_sales SET sale_bal='$new_balance',pay_status=3 WHERE sale_id='$sale_id'") or die(mysqli_error($connection));
    mysqli_query($connection,"UPDATE kp_sale_items SET pay_status=3 WHERE sale_id='$sale_id'") or die(mysqli_error($connection));
  }
  $remaining_payment -= $amount;
}

mysqli_query($connection,"UPDATE kp_customers SET bal=bal-'$tendered' WHERE cust_id='$cust_id'") or die(mysqli_error($connection));
cust_trans($cust_id, 'PAYMENT', $credit_pay_id, $tendered);

customer_credit_pay_json('success', 'Customer credit payment recorded for '.get_cust($cust_id).' amount '.number_format($tendered, 2).'.', [
  'cust_id' => $cust_id,
  'credit_pay_id' => $credit_pay_id,
  'print_select_url' => function_exists('kp_print_counter_select_url')
    ? kp_print_counter_select_url('cust_credit_pay', $credit_pay_id, '../cash/index.php')
    : ''
]);
