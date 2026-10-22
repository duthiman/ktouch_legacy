<?php
session_start();
include('../../inc/connection.php');
require_once('../cash/funcs.php');
require_once(__DIR__ . '/render_css.php');

$user_id = (int)($_GET['id'] ?? 0);
$modal_preview = isset($_GET['modal']) ? (int)$_GET['modal'] : 0;
$auto_print = (int)($_GET['auto_print'] ?? 0) === 1;
$shift = get_column('kp_shift','shift',"1");
$nos = 58;

if ($user_id < 1) {
    exit('Invalid waiter');
}

$staff_name = get_user($user_id);
$total_sales = (float)column_total('kp_sales','amount',"user_id='$user_id' AND shift='$shift'");
$paid_sales = (float)column_total('kp_sales','amount',"user_id='$user_id' AND shift='$shift' AND pay_status=1");
$unpaid_sales = (float)column_total('kp_sales','amount',"user_id='$user_id' AND shift='$shift' AND pay_status=2");
$credit_sales = (float)column_total('kp_sales','amount',"user_id='$user_id' AND shift='$shift' AND pay_status=3");
$bill_count = (int)column_count('kp_sales','sale_id',"user_id='$user_id' AND shift='$shift'");
$paid_count = (int)column_count('kp_sales','sale_id',"user_id='$user_id' AND shift='$shift' AND pay_status=1");
$unpaid_count = (int)column_count('kp_sales','sale_id',"user_id='$user_id' AND shift='$shift' AND pay_status=2");
$credit_count = (int)column_count('kp_sales','sale_id',"user_id='$user_id' AND shift='$shift' AND pay_status=3");
$return_total = (float)column_total('kp_ret_items','amount',"user_id='$user_id' AND shift='$shift'");
$return_count = (int)column_count('kp_ret_items','sale_id',"user_id='$user_id' AND shift='$shift'");
$deleted_total = (float)column_total('kp_del_orders','amount',"user_id='$user_id' AND shift='$shift'");
$deleted_count = (int)column_count('kp_del_orders','sale_id',"user_id='$user_id' AND shift='$shift'");
$item_qty = (float)column_total('kp_sale_items','qty',"user_id='$user_id' AND shift='$shift'");
$avg_bill = $bill_count > 0 ? $total_sales / $bill_count : 0;
$claimed_mpesa = (float)column_total('kp_split_message','amount',"claim_staff='$user_id' AND date='$shift'");
$claimed_mpesa_count = (int)column_count('kp_split_message','id',"claim_staff='$user_id' AND date='$shift'");
$shift_tokens = function_exists('get_user_shift_token_total') ? (float)get_user_shift_token_total($user_id, $shift) : 0;
$lifetime_tokens = function_exists('get_user_token_total') ? (float)get_user_token_total($user_id) : 0;

$sales_items = mysqli_query(
    $connection,
    "SELECT item_id, SUM(qty) AS qty, SUM(total) AS tt
     FROM kp_sale_items
     WHERE shift='$shift' AND user_id='$user_id'
     GROUP BY item_id
     ORDER BY tt DESC"
) or die(mysqli_error($connection));

$returned_items = mysqli_query(
    $connection,
    "SELECT item_id, qty, amount, ret_status
     FROM kp_ret_items
     WHERE shift='$shift' AND user_id='$user_id'
     ORDER BY sale_id DESC, item_id DESC"
) or die(mysqli_error($connection));

$deleted_bills = mysqli_query(
    $connection,
    "SELECT sale_id, amount, appr_by
     FROM kp_del_orders
     WHERE shift='$shift' AND user_id='$user_id'
     ORDER BY sale_id DESC"
) or die(mysqli_error($connection));
?>
<style>
  <?php echo kp_render_print_template_css(); ?>
</style>
<script type="text/javascript">
  <?php if ($modal_preview !== 1 && $auto_print) { ?>
    var summaryClosed = false;
    function closeSummary(){
      if (summaryClosed) {
        return;
      }
      summaryClosed = true;
      window.location = '../staff/staff_shift.php?id=<?php echo $user_id; ?>';
    }
    window.addEventListener('load', function () {
      setTimeout(function () {
        window.print();
      }, 250);
    });
    window.addEventListener('afterprint', function () {
      setTimeout(closeSummary, 150);
    });
    document.addEventListener('visibilitychange', function () {
      if (!document.hidden) {
        setTimeout(closeSummary, 250);
      }
    });
  <?php } ?>
</script>
<div id="receipt">
  <div id="receipt-header" class="text-center">
    <p style="margin:0;"><?php echo sep($nos); ?></p>
    <h2 class="title" style="margin:0;"><?php echo htmlspecialchars($staff_name); ?><br><?php echo htmlspecialchars($shift); ?> STAFF SUMMARY</h2>
    <p style="margin:0;"><?php echo sep($nos); ?></p>
  </div>
  <div id="receipt-body">
    <table>
      <tbody>
        <tr><th colspan="3" class="text-center">SHIFT OVERVIEW</th></tr>
        <tr><td>Total Sales</td><td><?php echo number_format($total_sales, 2); ?></td><td><?php echo $bill_count; ?> bills</td></tr>
        <tr><td>Paid Bills</td><td><?php echo number_format($paid_sales, 2); ?></td><td><?php echo $paid_count; ?> bills</td></tr>
        <tr><td>Unpaid Bills</td><td><?php echo number_format($unpaid_sales, 2); ?></td><td><?php echo $unpaid_count; ?> bills</td></tr>
        <tr><td>Credit Bills</td><td><?php echo number_format($credit_sales, 2); ?></td><td><?php echo $credit_count; ?> bills</td></tr>
        <tr><td>Items Qty</td><td><?php echo number_format($item_qty, 2); ?></td><td>Served</td></tr>
        <tr><td>Average Bill</td><td><?php echo number_format($avg_bill, 2); ?></td><td>-</td></tr>
        <tr><td>Shift Tokens</td><td><?php echo number_format($shift_tokens, 2); ?></td><td>Earned</td></tr>
        <tr><td>All Tokens</td><td><?php echo number_format($lifetime_tokens, 2); ?></td><td>Wallet</td></tr>
      </tbody>
    </table>

    <p style="margin:0;"><?php echo sep($nos); ?></p>
    <table>
      <tbody>
        <tr><th colspan="3" class="text-center">EXCEPTIONS</th></tr>
        <tr><td>Returned Items</td><td><?php echo number_format($return_total, 2); ?></td><td><?php echo $return_count; ?> rows</td></tr>
        <tr><td>Deleted Bills</td><td><?php echo number_format($deleted_total, 2); ?></td><td><?php echo $deleted_count; ?> rows</td></tr>
        <tr><td>Claimed M-Pesa</td><td><?php echo number_format($claimed_mpesa, 2); ?></td><td><?php echo $claimed_mpesa_count; ?> messages</td></tr>
      </tbody>
    </table>
  </div>
</div>
