<?php
if (session_status() === PHP_SESSION_NONE) {
  session_start();
}
include('../../inc/connection.php');
require_once('../cash/funcs.php');
require_once(__DIR__ . '/render_css.php');

$auto_print = (int)($_GET['auto_print'] ?? 0) === 1;
$user_id = $_SESSION['user_id'];
$shift = get_column('kp_shift','shift',"1");
$today = date('Y-m-d');
$nos = 56;
$token_sum = function_exists('get_user_shift_token_total')
  ? (float)get_user_shift_token_total($user_id, $shift)
  : 0.0;

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

mysqli_query($connection,"CREATE TABLE IF NOT EXISTS kp_customer_credit_payment_modes (
  credit_mode_id INT NOT NULL AUTO_INCREMENT,
  credit_pay_id INT NOT NULL DEFAULT 0,
  pay_mode_id INT NOT NULL DEFAULT 0,
  amount DECIMAL(10,2) NOT NULL DEFAULT 0,
  reference_no VARCHAR(120) NOT NULL DEFAULT '',
  PRIMARY KEY (credit_mode_id)
)") or die(mysqli_error($connection));

if (!function_exists('thermal_money')) {
  function thermal_money($amount) {
    return number_format((float)$amount, 2);
  }
}

$total_sales = (float)column_total('kp_sales','amount',"shift='$shift'");
$credit_sales_today = (float)column_total('kp_cust_hist','amount',"trans='SALE' AND trans_date='$today'");
$credit_paid_today = (float)column_total('kp_customer_credit_payment_items','amount',"pay_date='$shift' AND ord_date='$shift'");
$credit_paid_previous = (float)column_total('kp_customer_credit_payment_items','amount',"pay_date='$shift' AND ord_date!='$shift'");
$outstanding_credit = (float)column_total('kp_customers','bal',"bal>0");
$paid_total = (float)column_total('kp_sale_payment','paid_total',"pay_date='$shift'");
$unpaid_total = (float)column_total('kp_sales','amount',"shift='$shift' AND pay_status=2");
$bill_count = (int)column_count('kp_sales','sale_id',"shift='$shift'");
$return_total = (float)column_total('kp_ret_items','amount',"shift='$shift'");
$return_count = (int)column_count('kp_ret_items','sale_id',"shift='$shift'");
$deleted_total = (float)column_total('kp_del_orders','amount',"shift='$shift'");
$deleted_count = (int)column_count('kp_del_orders','sale_id',"shift='$shift'");
$expense_total_value = (float)column_total('kp_shop_expenses','amount',"shift_date='$shift'");
$net_after_expense = $paid_total - $expense_total_value;
?>
<style>
  <?php echo kp_render_print_template_css(); ?>
</style>
<script type="text/javascript">
  <?php if ($auto_print) { ?>
  window.addEventListener('load', function () {
    setTimeout(function () {
      window.print();
    }, 250);
  });
  <?php } ?>
</script>
<div id="receipt">
  <div id="receipt-header" class="text-center">
    <p style="margin: 0;"><?php echo sep($nos); ?></p>
    <h1 class="title" style="margin: 0;"><?php echo  $shift; ?><br> CASHIER SUMMARY</h1>
  </div>
  <div id="receipt-body">
    <p style="margin: 0;"><?php echo sep($nos); ?></p>
    <h3 style="margin: 0; text-align: center;"><b>TOTAL SALES</b></h3>
    <p style="margin: 0;"><?php echo sep($nos); ?></p>
    <table>
      <tbody>
        <tr><td>Total Sales</td><td><?php echo thermal_money($total_sales); ?></td></tr>
        <tr><td>Total Paid</td><td><?php echo thermal_money($paid_total); ?></td></tr>
        <tr><td>Unpaid Bills</td><td><?php echo thermal_money($unpaid_total); ?></td></tr>
        <tr><td>Bills Raised</td><td><?php echo $bill_count; ?></td></tr>
        <tr><td>Credit Bills Today</td><td><?php echo thermal_money($credit_sales_today); ?></td></tr>
        <tr><td>Credit Paid Today</td><td><?php echo thermal_money($credit_paid_today); ?></td></tr>
        <tr><td>Credit Paid Previous</td><td><?php echo thermal_money($credit_paid_previous); ?></td></tr>
        <tr><td>Outstanding Customer Credit</td><td><?php echo thermal_money($outstanding_credit); ?></td></tr>
        <tr><td>Returns</td><td><?php echo $return_count; ?> / <?php echo thermal_money($return_total); ?></td></tr>
        <tr><td>Deleted Bills</td><td><?php echo $deleted_count; ?> / <?php echo thermal_money($deleted_total); ?></td></tr>
        <tr><td>Expenses</td><td><?php echo thermal_money($expense_total_value); ?></td></tr>
        <tr><td>Net Paid Less Expenses</td><td><?php echo thermal_money($net_after_expense); ?></td></tr>
      </tbody>
    </table>
  </div>
</div>
