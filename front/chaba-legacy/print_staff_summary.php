<?php
session_start();
include('../../inc/connection.php');
require_once('../cash/funcs.php');

$user_id = (int)($_GET['id'] ?? 0);
$modal_preview = isset($_GET['modal']) ? (int)$_GET['modal'] : 0;
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
$return_count = (int)column_count('kp_ret_items','id',"user_id='$user_id' AND shift='$shift'");
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
     ORDER BY sale_id DESC"
) or die(mysqli_error($connection));

$deleted_bills = mysqli_query(
    $connection,
    "SELECT sale_id, amount, appr_by
     FROM kp_del_orders
     WHERE shift='$shift' AND user_id='$user_id'
     ORDER BY sale_id DESC"
) or die(mysqli_error($connection));

?>
<link href="../assets/css/thermal_print.css" rel="stylesheet"/>
<script type="text/javascript">
  <?php if ($modal_preview !== 1) { ?>
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

    <p style="margin:0;"><?php echo sep($nos); ?></p>
    <h3 style="margin:0; text-align:center;"><b>SALES ITEMS SUMMARY</b></h3>
    <p style="margin:0;"><?php echo sep($nos); ?></p>
    <table class="table">
      <tbody>
        <tr>
          <th>Item</th>
          <th>Qty</th>
          <th>Price</th>
          <th>Total</th>
        </tr>
        <?php if (mysqli_num_rows($sales_items) < 1) { ?>
          <tr><td colspan="4">No item movement for this shift</td></tr>
        <?php } else { ?>
          <?php while ($row = mysqli_fetch_assoc($sales_items)) {
            $qty = (float)$row['qty'];
            $total = (float)$row['tt'];
            $price = $qty > 0 ? $total / $qty : 0;
          ?>
            <tr>
              <td><?php echo get_item($row['item_id']); ?></td>
              <td><?php echo number_format($qty, 2); ?></td>
              <td><?php echo number_format($price, 2); ?></td>
              <td><?php echo number_format($total, 2); ?></td>
            </tr>
          <?php } ?>
        <?php } ?>
      </tbody>
    </table>

    <p style="margin:0;"><?php echo sep($nos); ?></p>
    <h3 style="margin:0; text-align:center;"><b>COUNTER BREAKDOWN</b></h3>
    <p style="margin:0;"><?php echo sep($nos); ?></p>
    <table class="table">
      <tbody>
      <tr>
        <th>Counter</th>
        <th>Sales</th>
        <th>Items</th>
        <th>Status</th>
      </tr>
      <?php
      $counters = mysqli_query($connection,"SELECT cntr_id,name FROM kp_counters ORDER BY name ASC") or die(mysqli_error($connection));
      while ($pow = mysqli_fetch_assoc($counters)) {
        $cntr_id = (int)$pow['cntr_id'];
        $sale_amount = (float)column_total('kp_sales','amount',"shift='$shift' AND counter='$cntr_id' AND user_id='$user_id'");
        $item_amount = (float)column_total('kp_sale_items','total',"shift='$shift' AND counter='$cntr_id' AND user_id='$user_id'");
        if ($sale_amount <= 0 && $item_amount <= 0) {
          continue;
        }
        ?>
        <tr>
          <td><?php echo htmlspecialchars($pow['name']); ?></td>
          <td><?php echo number_format($sale_amount, 2); ?></td>
          <td><?php echo number_format($item_amount, 2); ?></td>
          <td><?php echo abs($sale_amount - $item_amount) < 0.01 ? 'OK' : 'CHECK'; ?></td>
        </tr>
        <?php
      }
      ?>
      </tbody>
    </table>

    
    <p style="margin:0;"><?php echo sep($nos); ?></p>
    <h4 style="margin: 0; text-align: center;"><b>RETURNED ITEMS</b></h4>
    <p style="margin:0;"><?php echo sep($nos); ?></p>
    <table class="table">
      <tbody>
        <tr>
          <th>Item</th>
          <th>Qty</th>
          <th>Amount</th>
          <th>Status</th>
        </tr>
        <?php if (mysqli_num_rows($returned_items) < 1) { ?>
          <tr><td colspan="4">No items returned this shift</td></tr>
        <?php } else { ?>
          <?php while ($row = mysqli_fetch_assoc($returned_items)) { ?>
            <tr>
              <td><?php echo get_item($row['item_id']); ?></td>
              <td><?php echo number_format((float)$row['qty'], 2); ?></td>
              <td><?php echo number_format((float)$row['amount'], 2); ?></td>
              <td><?php echo (isset($row['ret_status']) && (int)$row['ret_status'] === 1) ? 'CONFIRMED' : 'PENDING'; ?></td>
            </tr>
          <?php } ?>
        <?php } ?>
      </tbody>
    </table>
    <br>
    <p style="margin:0;"><?php echo sep($nos); ?></p>
    <h4 style="margin: 0; text-align: center;"><b>DELETED BILLS</b></h4>
    <p style="margin:0;"><?php echo sep($nos); ?></p>
    <table class="table">
      <tbody>
        <tr>
          <th>Bill No</th>
          <th>Amount</th>
          <th>Confirm By</th>
        </tr>
        <?php if (mysqli_num_rows($deleted_bills) < 1) { ?>
          <tr><td colspan="3">No bills deleted this shift</td></tr>
        <?php } else { ?>
          <?php while ($row = mysqli_fetch_assoc($deleted_bills)) { ?>
            <tr>
              <td><?php echo (int)$row['sale_id']; ?></td>
              <td><?php echo number_format((float)$row['amount'], 2); ?></td>
              <td><?php echo $row['appr_by'] ? get_user($row['appr_by']) : '-'; ?></td>
            </tr>
          <?php } ?>
        <?php } ?>
      </tbody>
    </table>
    <br>
    <p style="margin:0;"><?php echo sep($nos); ?></p>
    <h4 style="margin: 0px 0px 0px 0px; text-align: center;"><B>USER MPESA </B></h4>
    <p style="margin:0;"><?php echo sep($nos); ?></p>
    <table> 
        <tbody>
          <tr>
            <td>NO:</td>
            <th><b>CODE</b></th>
            <th style="width: 100px;"><b>TIME</b></th> 
            <th style="width: 100px;"><b>AMOUNT</b></th> 
          </tr>   
           <?php 
           $no=0;
           $sum=0;
            $get_mpesa = mysqli_query($connection,"SELECT * FROM  kp_split_message WHERE claim_staff='$user_id' AND shift='$shift'");
            foreach ($get_mpesa  as $mow) {
              $code=$mow['code'];
              $amount=$mow['amount'];
              $time=$mow['time']; 
              $no=$no+1;
              $confirm=$mow['claim_staff']; 
              $sum=$sum+$amount
              ?>
              <tr>
                <td><?php echo $no; ?></td>
             <td> <b><?php echo $code; ?></b></td>
             <td><?php echo $time; ?></td> 
             <td><?php echo $amount; ?></td>
           </tr>
              <?php
          }
            ?> 
            <tr>
              <td class="text-center" colspan="3"><b><H3>TOTAL</H3></b> </td>
              <td><b><h3><?php echo $sum; ?></h3></b></td>

            </tr>
        </tbody>
      </table>
    <p style="margin:0;"><?php echo sep($nos); ?></p>
    <p class="text-center" style="margin:0;"><b>END OF STAFF SHIFT SUMMARY</b></p>
    <p style="margin:0;"><?php echo sep($nos); ?></p>

    
  </div>
</div>
