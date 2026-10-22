<?php
session_start();
include('../../inc/connection.php');
include('funcs.php');

$shift = get_column('kp_shift','shift',"1");
if ($shift == 0) {
  $shift = date('Y-m-d');
}
$today = date('Y-m-d');

mysqli_query($connection,"CREATE TABLE IF NOT EXISTS kp_shop_expenses (
  exp_id INT NOT NULL AUTO_INCREMENT,
  shift_date DATE NOT NULL,
  title VARCHAR(120) NOT NULL,
  amount DECIMAL(10,2) NOT NULL DEFAULT 0,
  note TEXT NULL,
  user_id INT NOT NULL DEFAULT 0,
  created_at TIMESTAMP NOT NULL DEFAULT CURRENT_TIMESTAMP,
  PRIMARY KEY (exp_id)
)") or die(mysqli_error($connection));

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

$payload = [
  'shift' => $shift,
  'total_sales' => column_total('kp_sales','amount',"shift='$shift'"),
  'sales_unpaid' => column_total('kp_sales','amount',"shift='$shift' AND pay_status=2"),
  'sales_paid' => column_total('kp_sales','amount',"shift='$shift' AND pay_status=1"),
  'previous_paid' => column_total('kp_sales_paid','paid_total',"ord_date!=pay_date AND pay_date='$shift'"),
  'total_paid' => column_total('kp_sale_payment','paid_total',"pay_date='$shift'"),
  'credit_today' => column_total('kp_cust_hist','amount',"trans='SALE' AND trans_date='$today'"),
  'credit_paid_today' => column_total('kp_customer_credit_payment_items','amount',"pay_date='$shift' AND ord_date='$shift'"),
  'credit_paid_previous' => column_total('kp_customer_credit_payment_items','amount',"pay_date='$shift' AND ord_date!='$shift'"),
  'credit_customer_count' => mysqli_num_rows(mysqli_query($connection, "SELECT DISTINCT cust_id FROM kp_cust_hist WHERE trans='SALE' AND trans_date='$today'")),
  'expense_total' => column_total('kp_shop_expenses','amount',"shift_date='$shift'"),
  'expense_count' => column_count('kp_shop_expenses','exp_id',"shift_date='$shift'")
];

header('Content-Type: application/json');
echo json_encode($payload);
