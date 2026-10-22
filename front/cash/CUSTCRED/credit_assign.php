<?php
if (session_status() === PHP_SESSION_NONE) {
  session_start();
}
include('../../../inc/connection.php');
require_once('../funcs.php');
header('Content-Type: application/json');

function credit_json($status, $message, $extra = []) {
  echo json_encode(array_merge([
    'status' => $status,
    'message' => $message
  ], $extra));
  exit;
}

function ensure_sales_credit_columns() {
  global $connection;
  $columns = [
    "cust_id INT NOT NULL DEFAULT 0",
    "sale_bal DECIMAL(10,2) NOT NULL DEFAULT 0",
    "cf_bal DECIMAL(10,2) NOT NULL DEFAULT 0",
    "type INT NOT NULL DEFAULT 0"
  ];
  foreach ($columns as $definition) {
    $parts = explode(' ', $definition, 2);
    $column = $parts[0];
    $check = mysqli_query($connection, "SHOW COLUMNS FROM kp_sales LIKE '$column'") or die(mysqli_error($connection));
    if (mysqli_num_rows($check) < 1) {
      mysqli_query($connection, "ALTER TABLE kp_sales ADD COLUMN $definition") or die(mysqli_error($connection));
    }
  }

  $item_type_check = mysqli_query($connection, "SHOW COLUMNS FROM kp_sale_items LIKE 'type'") or die(mysqli_error($connection));
  if (mysqli_num_rows($item_type_check) < 1) {
    mysqli_query($connection, "ALTER TABLE kp_sale_items ADD COLUMN type INT NOT NULL DEFAULT 0") or die(mysqli_error($connection));
  }
}

$user_id = isset($_SESSION['user_id']) ? (int)$_SESSION['user_id'] : 0;
$invoices = isset($_POST['invoices']) ? trim($_POST['invoices']) : '';
$cust_id = isset($_POST['cust_id']) ? (int)$_POST['cust_id'] : 0;
$name = isset($_POST['name']) ? strtoupper(trim($_POST['name'])) : '';
$phone_number = isset($_POST['phone']) ? trim($_POST['phone']) : '';
$phone = preg_replace('/^0/', '+254', $phone_number);
$loc = isset($_POST['loc']) ? strtoupper(trim($_POST['loc'])) : '';

if ($user_id < 1) {
  credit_json('error', 'Session expired. Please log in again.');
}
if ($invoices === '') {
  credit_json('warning', 'Select at least one bill first.');
}

ensure_sales_credit_columns();

if ($cust_id < 1) {
  if ($name === '' || $phone === '') {
    credit_json('warning', 'Pick a customer or enter new customer name and phone.');
  }
  $phone_safe = mysqli_real_escape_string($connection, $phone);
  $existing = mysqli_query($connection,"SELECT cust_id FROM kp_customers WHERE phone='$phone_safe' LIMIT 1") or die(mysqli_error($connection));
  if (mysqli_num_rows($existing) > 0) {
    $cust_id = (int)get_column('kp_customers','cust_id',"phone='$phone_safe'");
  } else {
    $name_safe = mysqli_real_escape_string($connection, $name);
    $loc_safe = mysqli_real_escape_string($connection, $loc);
    mysqli_query($connection,"INSERT INTO kp_customers(name,phone,location) VALUES('$name_safe','$phone_safe','$loc_safe')") or die(mysqli_error($connection));
    $cust_id = (int)mysqli_insert_id($connection);
  }
}

$invoice_ids = array_filter(array_map('trim', explode(',', $invoices)));
if (empty($invoice_ids)) {
  credit_json('warning', 'No valid bills were selected.');
}

$done = 0;
$total = 0;
foreach ($invoice_ids as $sale_id) {
  $sale_id = (int)$sale_id;
  if ($sale_id < 1) {
    continue;
  }
  $sale_q = mysqli_query($connection,"SELECT sale_id,amount,pay_status FROM kp_sales WHERE sale_id='$sale_id' LIMIT 1") or die(mysqli_error($connection));
  if (mysqli_num_rows($sale_q) < 1) {
    continue;
  }
  $sale = mysqli_fetch_assoc($sale_q);
  if ((int)$sale['pay_status'] === 1 || (int)$sale['pay_status'] === 3) {
    continue;
  }
  $bill_total = (float)$sale['amount'];
  mysqli_query($connection,"UPDATE kp_sales SET pay_status=3,cust_id='$cust_id',sale_bal='$bill_total',cf_bal=cf_bal+'$bill_total',type=2 WHERE sale_id='$sale_id'") or die(mysqli_error($connection));
  mysqli_query($connection,"UPDATE kp_sale_items SET pay_status=3,type=2 WHERE sale_id='$sale_id'") or die(mysqli_error($connection));
  mysqli_query($connection,"UPDATE kp_customers SET bal=bal+'$bill_total' WHERE cust_id='$cust_id'") or die(mysqli_error($connection));
  cust_trans($cust_id, 'SALE', $sale_id, $bill_total);
  $done++;
  $total += $bill_total;
}

if ($done < 1) {
  credit_json('warning', 'No selected bills were moved to customer credit.');
}

credit_json('success', 'Moved '.$done.' bill(s) to '.get_cust($cust_id).' credit for '.number_format($total, 2).'.', [
  'cust_id' => $cust_id
]);
