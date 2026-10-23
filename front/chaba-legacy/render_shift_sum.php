<?php
session_start();
?>
<!DOCTYPE html>
<html lang="en">
<head>
  <meta charset="UTF-8">
  <title>Shift Summary</title>
</head>
<body>
<?php
if (session_status() === PHP_SESSION_NONE) {
  session_start();
}
include('../../inc/connection.php');
require_once('../cash/funcs.php');  
 
$shift = trim((string)get_column('kp_shift','shift',"1"));
// Prevent invalid legacy shift values such as 0 from being used in DATE queries.
$shift_date = DateTime::createFromFormat('!Y-m-d', $shift);
if ($shift === '' || !$shift_date || $shift_date->format('Y-m-d') !== $shift) {
  $shift = date('Y-m-d');
}
$today = date('Y-m-d');
$kitchen_total=0;
$kitchen_untotal=0;
$bar_total=0;
$bar_untotal=0;
$nos=56;

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
$return_count = (int)column_count('kp_ret_items','id',"shift='$shift'");
$deleted_total = (float)column_total('kp_del_orders','amount',"shift='$shift'");
$deleted_count = (int)column_count('kp_del_orders','id',"shift='$shift'");
$expense_total_value = (float)column_total('kp_shop_expenses','amount',"shift_date='$shift'");
$net_after_expense = $paid_total - $expense_total_value;

?>

   <style>
    <?php
    $print_template = get_print_template();
     if ($print_template === 2): ?>
    @page {
      size: 80mm auto;
      margin: 0;
    }
    * { box-sizing: border-box; }
    body {
      margin: 0;
      padding: 0;
      background: #fff;
      font-family: 'Courier New', Courier, monospace;
      font-size: 13px;
      color: #000;
      width: 80mm;
    }
    #receipt {
      margin: 0 auto;
      width: 79mm;
      max-width: 79mm;
      background: #fff;
      padding: 1.5mm 0;
    }
    #bord {
      width: 100%;
      max-width: 100%;
      box-sizing: border-box;
      border: 2px solid #000 !important;
    }
    .sep-eq { border: none; border-top: 2px solid #000; margin: 3px 0; }
    .sep-dot { border: none; border-top: 1px dashed #555; margin: 3px 0; }
    .text-center { text-align: center !important; }
    .receipt-divider { clear: both; display: block; margin: 3px 0 0; line-height: 1.1; white-space: nowrap; overflow: hidden; text-align: center; font-weight: 700; letter-spacing: .06em; }
    .meta {
      display: flex;
      justify-content: space-between;
      font-size: 11px;
      margin: 1px 0;
    }
    .meta span { white-space: nowrap; }
    .meta.center { justify-content: center; text-align: center; }
    h1.title {
      font-size: 22px;
      margin: 4px 0 2px;
      letter-spacing: 2px;
    }
    .items-tbl,
    .totals-tbl {
      width: 100%;
      max-width: 100%;
      border-collapse: collapse;
      margin: 2px 0;
      table-layout: fixed;
    }
    .items-tbl th {
      font-size: 11px;
      border-top: 1px solid #000;
      border-bottom: 1px solid #000;
      padding: 2px 1px;
      text-transform: uppercase;
    }
    .items-tbl th:nth-child(1),
    .items-tbl td:nth-child(1) { width: 12%; }
    .items-tbl th:nth-child(2),
    .items-tbl td:nth-child(2) { width: 46%; }
    .items-tbl th:nth-child(3),
    .items-tbl td:nth-child(3),
    .items-tbl th:nth-child(4),
    .items-tbl td:nth-child(4) { width: 21%; }
    .items-tbl td,
    .totals-tbl td {
      font-size: 12px;
      padding: 2px 1px;
      border: none;
    }
    .items-tbl td:nth-child(2) {
      font-size: 13px;
      font-weight: 700;
      line-height: 1.2;
      word-break: break-word;
    }
    .items-tbl td:nth-child(3),
    .items-tbl td:nth-child(4) {
      font-size: 12px;
      font-weight: 600;
    }
    .items-tbl th:first-child,
    .items-tbl td:first-child,
    .totals-tbl td:first-child { text-align: left; }
    .items-tbl th:not(:first-child),
    .items-tbl td:not(:first-child),
    .totals-tbl td:last-child { text-align: right; }
    .sec-label td {
      font-size: 11px;
      font-weight: bold;
      text-decoration: underline;
      padding-top: 4px;
    }
    .grand td {
      font-size: 16px;
      font-weight: bold;
      padding-top: 3px;
    }
    .mpesa-line {
      text-align: center;
      font-size: 13px;
      font-weight: bold;
      letter-spacing: 1px;
      margin: 3px 0;
    }
    .open-customer-box {
      margin: 6px 0 8px;
      padding: 6px 0 2px;
      border-top: 2px solid #000;
      border-bottom: 2px solid #000;
      width: 100%;
    }
    .open-customer-head {
      margin: 0 0 4px;
      text-align: center;
      font-size: 12px;
      font-weight: 900;
      text-transform: uppercase;
      letter-spacing: .08em;
    }
    .open-customer-line {
      display: flex;
      justify-content: space-between;
      gap: 10px;
      margin: 2px 0;
      font-size: 12px;
      font-weight: 700;
    }
    .open-customer-line strong:last-child {
      text-align: right;
    }
    .open-customer-total {
      margin-top: 5px;
      padding-top: 4px;
      border-top: 1px dashed #555;
      font-size: 15px;
      font-weight: 900;
    }
    .open-demo-summary {
      width: 100%;
      max-width: 100%;
      border-collapse: collapse;
      margin: 4px 0;
      table-layout: fixed;
    }
    .open-demo-summary th {
      padding: 3px 1px;
      text-align: center;
      border: none;
      font-weight: 900;
    }
    .open-demo-summary .bill-no th {
      font-size: 16px;
    }
    .open-demo-summary .line-label th,
    .open-demo-summary .line-value th {
      font-size: 14px;
    }
    .open-demo-summary .prev-bill th {
      font-size: 12px;
    }
    .open-demo-summary .total-label th,
    .open-demo-summary .total-value th {
      font-size: 22px;
    }
    .qr-wrap {
      text-align: center;
      margin: 6px 0;
    }
    .qr-wrap img,
    .qr-wrap canvas {
      display: block;
      margin: 0 auto;
      background: #fff;
      max-width: 100%;
      height: auto;
      image-rendering: pixelated;
    }
    .qr-box {
      display: inline-block;
      padding: 6px;
      background: #fff;
      border: 1px solid #000;
    }
    .qr-caption {
      margin: 3px 0 0;
      font-size: 10px;
      text-align: center;
    }
    @media print {
      body {
        margin: 0 !important;
        padding: 0 !important;
        width: 80mm !important;
        background: #fff;
      }
      #receipt {
        width: 79mm !important;
        max-width: 79mm !important;
        padding: 1.5mm 0 !important;
        background: #fff;
      }
      #bord {
        width: 100% !important;
        max-width: 100% !important;
        box-sizing: border-box !important;
      }
    }
    <?php else: ?>
    @page {
      size: 80mm auto;
      margin: 0;
    }
    * { box-sizing: border-box; }
    body {
      margin: 0;
      padding: 0;
      background: #fff;
      color: #000;
      font-family: Arial, Helvetica Neue, Helvetica, sans-serif;
      width: 80mm;
    }
    #receipt {
      padding: 1.5mm 0;
      margin: 0 auto;
      width: 79mm;
      max-width: 79mm;
      background: #fff;
    }
    #bord {
      width: 100%;
      max-width: 100%;
      box-sizing: border-box;
      border: 2px solid #000 !important;
    }
    .text-center { text-align: center !important; }
    .receipt-divider { margin: 0; line-height: 1.1; white-space: nowrap; overflow: hidden; text-align: center; font-weight: 700; letter-spacing: .06em; }
    .pull-left { float: left !important; }
    .pull-right { float: right !important; }
    h1.title { font-size: 26px; margin-bottom: 0; }
    table {
      padding: 0;
      margin: 0 auto;
      width: 100%;
      max-width: 100%;
      background: #fff;
      border-collapse: collapse;
      table-layout: fixed;
    }
    td, th {
      font-size: 12px;
      border: 1px solid #000 !important;
    }
    th {
      font-size: 13px;
      padding: 2px;
      margin: 2px;
    }
    #deSo th:nth-child(1),
    #deSo td:nth-child(1) { width: 12%; }
    #deSo th:nth-child(2),
    #deSo td:nth-child(2) { width: 46%; }
    #deSo th:nth-child(3),
    #deSo td:nth-child(3),
    #deSo th:nth-child(4),
    #deSo td:nth-child(4) { width: 21%; }
    #deSo td:nth-child(2) {
      font-size: 13px;
      font-weight: bold;
      line-height: 1.2;
      word-break: break-word;
    }
    #deSo td:nth-child(3),
    #deSo td:nth-child(4) {
      font-size: 12px;
      font-weight: 600;
    }
    .open-customer-box {
      margin: 6px auto 8px;
      padding: 6px 4px;
      width: 100%;
      border: 2px solid #000;
        background: #fff;
    }
    .open-customer-head {
      margin: 0 0 5px;
      text-align: center;
      font-size: 13px;
      font-weight: 900;
      text-transform: uppercase;
    }
    .open-customer-line {
      display: flex;
      justify-content: space-between;
      gap: 10px;
      margin: 3px 0;
      font-size: 13px;
      font-weight: 800;
    }
    .open-customer-total {
      margin-top: 5px;
      padding-top: 5px;
      border-top: 2px solid #000;
      font-size: 16px;
      font-weight: 900;
    }
    .open-demo-summary {
      width: 100%;
      max-width: 100%;
      border-collapse: collapse;
      margin: 4px 0;
      background: #fff;
      table-layout: fixed;
    }
    .open-demo-summary th {
      padding: 3px 1px;
      text-align: center;
      border: 1px solid #000 !important;
      font-weight: 900;
    }
    .open-demo-summary .bill-no th {
      font-size: 20px;
    }
    .open-demo-summary .line-label th,
    .open-demo-summary .line-value th {
      font-size: 14px;
    }
    .open-demo-summary .total-label th,
    .open-demo-summary .total-value th {
      font-size: 22px;
    }
    .qr-wrap {
      text-align: center;
      margin: 6px 0;
    }
    .qr-wrap img,
    .qr-wrap canvas {
      display: block;
      margin: 0 auto;
      background: #fff;
      max-width: 100%;
      height: auto;
      image-rendering: pixelated;
    }
    .qr-box {
      display: inline-block;
      padding: 6px;
      background: #fff;
      border: 1px solid #000;
    }
    .qr-caption {
      margin: 3px 0 0;
      font-size: 10px;
      text-align: center;
    }
    @media print {
      body {
        margin: 0 !important;
        padding: 0 !important;
        width: 80mm !important;
        background: #fff;
      }
      #receipt {
        width: 79mm !important;
        max-width: 79mm !important;
        padding: 0.5mm 0 !important;
        background: #fff;
      }
      #bord {
        width: 79mm !important;
        max-width: 80mm !important;
        box-sizing: border-box !important;
      }
      table {
        width: 78mm !important;
        max-width: 79mm !important;
      }
      td, th { font-size: 13px; border: 1.5px solid #000 !important; }
    }
    <?php endif; ?>
    /* Keep every summary table compact and use the full receipt width. */
    #receipt-body > table { width: 100% !important; max-width: 100% !important; table-layout: fixed !important; }
    #receipt-body > table th, #receipt-body > table td { padding: 3px 4px !important; vertical-align: middle; overflow-wrap: anywhere; word-break: normal; }
    #receipt-body > table td.compact-right, #receipt-body > table th.compact-right { text-align: right !important; white-space: nowrap; }
    #receipt-body > table td.compact-center, #receipt-body > table th.compact-center { text-align: center !important; }
    #receipt-body > table:nth-of-type(1) td:first-child { width: 72%; }
    #receipt-body > table:nth-of-type(1) td:last-child { width: 28%; text-align: right; white-space: nowrap; }
    #receipt-body > table:nth-of-type(2) th:nth-child(1), #receipt-body > table:nth-of-type(2) td:nth-child(1) { width: 8%; }
    #receipt-body > table:nth-of-type(2) th:nth-child(2), #receipt-body > table:nth-of-type(2) td:nth-child(2) { width: 42%; }
    #receipt-body > table:nth-of-type(2) th:nth-child(3), #receipt-body > table:nth-of-type(2) td:nth-child(3) { width: 25%; }
    #receipt-body > table:nth-of-type(2) th:nth-child(4), #receipt-body > table:nth-of-type(2) td:nth-child(4) { width: 15%; }
    #receipt-body > table:nth-of-type(2) th:nth-child(5), #receipt-body > table:nth-of-type(2) td:nth-child(5) { width: 10%; }
    #receipt-body > table:nth-of-type(3) td:first-child, #receipt-body > table:nth-of-type(6) td:first-child { width: 72%; }
    #receipt-body > table:nth-of-type(3) td:last-child, #receipt-body > table:nth-of-type(6) td:last-child { width: 28%; text-align: right; white-space: nowrap; }
    #receipt-body > table:nth-of-type(4) th:first-child, #receipt-body > table:nth-of-type(4) td:first-child,
    #receipt-body > table:nth-of-type(5) th:first-child, #receipt-body > table:nth-of-type(5) td:first-child { width: 43%; }
    #receipt-body > table:nth-of-type(4) th:nth-child(2), #receipt-body > table:nth-of-type(4) td:nth-child(2),
    #receipt-body > table:nth-of-type(5) th:nth-child(2), #receipt-body > table:nth-of-type(5) td:nth-child(2) { width: 28%; }
    #receipt-body > table:nth-of-type(4) th:last-child, #receipt-body > table:nth-of-type(4) td:last-child,
    #receipt-body > table:nth-of-type(5) th:last-child, #receipt-body > table:nth-of-type(5) td:last-child { width: 29%; text-align: right; white-space: nowrap; }
    #receipt-body > table:nth-of-type(7) th:first-child, #receipt-body > table:nth-of-type(7) td:first-child { width: 14%; }
    #receipt-body > table:nth-of-type(7) th:nth-child(2), #receipt-body > table:nth-of-type(7) td:nth-child(2) { width: 50%; }
    #receipt-body > table:nth-of-type(7) th:nth-child(3), #receipt-body > table:nth-of-type(7) td:nth-child(3) { width: 14%; }
    #receipt-body > table:nth-of-type(7) th:last-child, #receipt-body > table:nth-of-type(7) td:last-child { width: 22%; text-align: right; white-space: nowrap; }
    #receipt-body > table:nth-of-type(8) th:first-child, #receipt-body > table:nth-of-type(8) td:first-child { width: 18%; }
    #receipt-body > table:nth-of-type(8) th:nth-child(2), #receipt-body > table:nth-of-type(8) td:nth-child(2) { width: 57%; }
    #receipt-body > table:nth-of-type(8) th:last-child, #receipt-body > table:nth-of-type(8) td:last-child { width: 25%; text-align: right; white-space: nowrap; }
    #receipt-body > table:nth-of-type(9) td:first-child { width: 65%; }
    #receipt-body > table:nth-of-type(9) td:last-child { width: 35%; text-align: right; }
  </style>

<script type="text/javascript">
  function close(){
    window.location = '../staff/staff_shift.php?id=<?php echo $user_id; ?>';
  }
</script> 
 
<!--  onload="close(); window.print()"--> 
<!-- START RECEIPT -->
  <div id="receipt">
    <div id="receipt-header" class="text-center"> 
      <p style="margin: 0px 0px 0px 0px;"><?php echo sep($nos); ?></p>
      <h1 class="title" style="margin: 0px 0px 0px 0px;">
        <?php echo  $shift; ?><BR> CASHIER SUMMARY
      </h1> 
    </div>
    <div id="receipt-body">
      <p style="margin: 0px 0px 0px 0px;"><?php echo sep($nos); ?></p>
      <h3 style="margin: 0px 0px 0px 0px; text-align: center;"><B>TOTAL SALES</B></h3>
      <p style="margin: 0px 0px 0px 0px;"><?php echo sep($nos); ?></p>
      <table>
        <tbody class="thermal-summary">
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
      <BR>
      <p style="margin: 0px 0px 0px 0px;"><?php echo sep($nos); ?></p>
      <h3 style="margin: 0px 0px 0px 0px; text-align: center;"><B>COUNTER SALES</B></h3>
      <p style="margin: 0px 0px 0px 0px;"><?php echo sep($nos); ?></p>
      <table class="table table-striped">
        <thead>
          <tr> 
            <th>#</th>
            <th>Counter</th>
            <th class="compact-right">Sales</th>  
            <th class="compact-right">Items</th>   
            <th class="compact-center">OK</th>   
          </tr>
        </thead>
        <tbody>
          <?php   
            $no=0;  
            $sum=0;
            $counters = mysqli_query($connection,"SELECT * FROM kp_counters WHERE 1") or die('Error fetching the purchases.:'.mysqli_error($connection));
            while ($pow = mysqli_fetch_assoc($counters)){
              $cntr_id = $pow['cntr_id'];  
              $name = $pow['name']; 

              $sale_amount=column_total('kp_sales','amount',"shift='$shift' AND counter='$cntr_id'"); 
              $item_amount=column_total('kp_sale_items','total',"shift='$shift' AND counter='$cntr_id'"); 
              if ($sale_amount!=$item_amount) {
                
              $btn='NOT OK';
                
              }else{
              $btn='OK';
              }
              $no=$no+1; 
              ?>
              <tr>   
                <td><b><?php echo $no; ?></b></td> 
                <td><b><?php echo $name; ?></b></td>     
                <td class="compact-right"><b><?php echo thermal_money($sale_amount); ?></b></td>  
                <td class="compact-right"><b><?php echo thermal_money($item_amount); ?></b></td>  
                <td class="compact-center"><b><?php echo $btn; ?></b></td>  
              </tr>
              <?php
            } 
          ?>
        </tbody> 
      </table>
      <p style="margin: 0px 0px 0px 0px;"><?php echo sep($nos); ?></p>
      <h3 style="margin: 0px 0px 0px 0px; text-align: center;"><B>CASHIERS COLLECTION</B></h3>
      <p style="margin: 0px 0px 0px 0px;"><?php echo sep($nos); ?></p>
      <table> 
        <tbody>
          <tr>
            <th><b>PAY MODE</b></th>
            <th class="compact-right"><b>AMOUNT</b></th> 
          </tr> 
          <?php 
          $get_mode = mysqli_query($connection,"SELECT * FROM kp_pay_mode");
          foreach ($get_mode  as $cow) {
          $pay_mode=$cow['name']; 
          $pay_id=$cow['pay_id'];
          $col="mode".$pay_id; 
            ?>
            <tr>
              <td><b><?php echo $pay_mode; ?></b></td>
              <td class="compact-right"><?php echo thermal_money(column_total('kp_sale_payment',$col,"pay_date='$shift'")); ?></td>
            </tr>
            <?php
          }
          ?> 
          <tr>
             <td><b>BILLS</b></td>
             <td class="compact-right"><?php echo $bill_count; ?></td>
            </tr>
            <tr>
             <td><b>SHORT / EXCESS</b></td>
             <td class="compact-right">-</td>
            </tr>
        </tbody>
      </table>
      <p style="margin: 0px 0px 0px 0px;"><?php echo sep($nos); ?></p>
      <h3 style="margin: 0px 0px 0px 0px; text-align: center;"><B>CUSTOMER CREDIT BILLS</B></h3>
      <p style="margin: 0px 0px 0px 0px;"><?php echo sep($nos); ?></p>
      <table>
        <tbody>
          <tr>
            <th><b>CUSTOMER</b></th>
            <th><b>BILL</b></th>
            <th class="compact-right"><b>AMOUNT</b></th>
          </tr>
          <?php
          $credit_sales = mysqli_query($connection,"SELECT * FROM kp_cust_hist WHERE trans='SALE' AND trans_date='$today' ORDER BY trans_id DESC") or die(mysqli_error($connection));
          if (mysqli_num_rows($credit_sales) < 1) {
            ?>
            <tr>
              <td>No customer credit bills</td>
              <td>-</td>
              <td>0.00</td>
            </tr>
            <?php
          } else {
            while ($credit_sale = mysqli_fetch_assoc($credit_sales)) {
              ?>
              <tr>
                <td><?php echo get_cust($credit_sale['cust_id']); ?></td>
                <td><?php echo $credit_sale['trans_id']; ?></td>
                <td class="compact-right"><?php echo thermal_money($credit_sale['amount']); ?></td>
              </tr>
              <?php
            }
          }
          ?>
        </tbody>
      </table>
      <p style="margin: 0px 0px 0px 0px;"><?php echo sep($nos); ?></p>
      <h3 style="margin: 0px 0px 0px 0px; text-align: center;"><B>CUSTOMER CREDIT PAYMENTS</B></h3>
      <p style="margin: 0px 0px 0px 0px;"><?php echo sep($nos); ?></p>
      <table>
        <tbody>
          <tr>
            <th><b>CUSTOMER</b></th>
            <th><b>MODE</b></th>
            <th class="compact-right"><b>AMOUNT</b></th>
          </tr>
          <?php
          $credit_payments = mysqli_query($connection,"SELECT kp_customer_credit_payment_items.*, kp_customer_credit_payments.credit_pay_id FROM kp_customer_credit_payment_items INNER JOIN kp_customer_credit_payments ON kp_customer_credit_payments.credit_pay_id=kp_customer_credit_payment_items.credit_pay_id WHERE kp_customer_credit_payment_items.pay_date='$shift' ORDER BY kp_customer_credit_payment_items.credit_item_id DESC") or die(mysqli_error($connection));
          if (mysqli_num_rows($credit_payments) < 1) {
            ?>
            <tr>
              <td>No customer credit payments</td>
              <td>-</td>
              <td>0.00</td>
            </tr>
            <?php
          } else {
            while ($credit_payment = mysqli_fetch_assoc($credit_payments)) {
              $mode_names = [];
              $modes = mysqli_query($connection,"SELECT pay_mode_id FROM kp_customer_credit_payment_modes WHERE credit_pay_id='".(int)$credit_payment['credit_pay_id']."'") or die(mysqli_error($connection));
              while ($mode = mysqli_fetch_assoc($modes)) {
                $mode_names[] = get_column('kp_pay_mode','name',"pay_id='".(int)$mode['pay_mode_id']."'");
              }
              ?>
              <tr>
                <td><?php echo get_cust($credit_payment['cust_id']); ?></td>
                <td><?php echo !empty($mode_names) ? htmlspecialchars(implode(', ', $mode_names)) : '-'; ?></td>
                <td class="compact-right"><?php echo thermal_money($credit_payment['amount']); ?></td>
              </tr>
              <?php
            }
          }
          ?>
        </tbody>
      </table>
      <p style="margin: 0px 0px 0px 0px;"><?php echo sep($nos); ?></p>
      <h3 style="margin: 0px 0px 0px 0px; text-align: center;"><B>SHOP EXPENSES</B></h3>
      <p style="margin: 0px 0px 0px 0px;"><?php echo sep($nos); ?></p>
      <table>
        <tbody>
          <tr>
            <th><b>EXPENSE</b></th>
            <th class="compact-right"><b>AMOUNT</b></th>
          </tr>
          <?php
          $expenses = mysqli_query($connection,"SELECT title,amount,note,created_at FROM kp_shop_expenses WHERE shift_date='$shift' ORDER BY exp_id DESC") or die(mysqli_error($connection));
          $expense_sum = 0;
          if (mysqli_num_rows($expenses) < 1) {
            ?>
            <tr>
              <td>No shop expenses</td>
              <td>0.00</td>
            </tr>
            <?php
          } else {
            while ($exp = mysqli_fetch_assoc($expenses)) {
              $expense_sum += (float)$exp['amount'];
              ?>
              <tr>
                <td>
                  <b><?php echo $exp['title']; ?></b>
                  <?php if (!empty($exp['note'])) { ?><br><small><?php echo $exp['note']; ?></small><?php } ?>
                </td>
                <td class="compact-right"><?php echo thermal_money($exp['amount']); ?></td>
              </tr>
              <?php
            }
          }
          ?>
          <tr>
            <td><b>TOTAL EXPENSES</b></td>
            <td class="compact-right"><b><?php echo thermal_money($expense_sum); ?></b></td>
          </tr>
          <tr>
            <td><b>TOTAL PAID LESS EXPENSES</b></td>
            <td class="compact-right"><b><?php echo thermal_money($paid_total - $expense_sum); ?></b></td>
          </tr>
        </tbody>
      </table>
      <p style="margin: 0px 0px 0px 0px;"><?php echo sep($nos); ?></p>
      <h3 style="margin: 0px 0px 0px 0px; text-align: center;"><B>RETURNED ITEMS</B></h3>
      <p style="margin: 0px 0px 0px 0px;"><?php echo sep($nos); ?></p>
      <table class="table">
        <tbody> 
          <?php 
          $delete = mysqli_query($connection,"SELECT * FROM kp_ret_items WHERE shift='$shift'") or die('Error fetching the staff data.:'.mysqli_error($connection));
          if (mysqli_num_rows($delete) < 1) {
            echo "<tr><td colspan='4'>No orders have been Returned</td</tr>";
          }else{
            ?>
            <tr>
              <td class="text-center"><b>BILL</b></td>
              <td class="text-center"><b>ITEM / TEAM</b></td>
              <td class="text-center"><b>QTY</b></td>
              <td class="text-center"><b>AMT</b></td>
            </tr>
            <?php
            while ($row = mysqli_fetch_assoc( $delete )) {
              $item_id = $row['item_id'];
              $order_by = $row['user_id'];
              $appr_by  = $row['confirm_by'];
              $sale_id = $row['sale_id'];
              $amount = $row['amount']; 
              $qty = $row['qty'];
              ?>
              <tr>
                <td><?php echo $sale_id; ?></td>
                <td>
                  <?php echo get_item($item_id); ?><br>
                  <span class="muted-line">W: <?php echo get_user($order_by); ?> / C: <?php echo get_user($appr_by); ?></span>
                </td>
                <td><?php echo $qty; ?></td>
                <td class="compact-right"><?php echo thermal_money($amount); ?></td>
              </tr>
            <?php
            }
          }
          ?>
          <!--END DELETED BILLS -->
        </tbody>
      </table>   
      <p style="margin: 0px 0px 0px 0px;"><?php echo sep($nos); ?></p>
      <h3 style="margin: 0px 0px 0px 0px; text-align: center;"><B>DELETED BILLS</B></h3>
      <p style="margin: 0px 0px 0px 0px;"><?php echo sep($nos); ?></p>
      <table class="table">
        <tbody> 
          <?php
          $delete = mysqli_query($connection,"SELECT * FROM kp_del_orders WHERe shift='$shift'") or die('Error fetching the staff data.:'.mysqli_error($connection));
          if (mysqli_num_rows($delete) < 1) {
            echo "<tr><td colspan='4'>No orders have been deleted</td</tr>";
          }else{
            ?>
            <tr>
              <td class="text-center"><b>Ord No</b></td>
              <td class="text-center"><b>TEAM</b></td>
              <td class="text-center"><b>AMT</b></td>
            </tr>
            <?php
            while ($row = mysqli_fetch_assoc( $delete )) {
              $order_by = $row['user_id'];
              $appr_by  = $row['appr_by'];
              $order_no = $row['sale_id'];
              $amount = $row['amount']; 
              ?>
              <tr>
                <td><?php echo $order_no; ?></td>
                <td>
                  <span class="muted-line">W: <?php echo get_user($order_by); ?></span><br>
                  <span class="muted-line">A: <?php echo get_user($appr_by); ?></span>
                </td>
                <td class="compact-right"><?php echo thermal_money($amount); ?></td>
              </tr>
              <?php
            }
          }
          ?>
        <!--END DELETED BILLS -->
        </tbody>
      </table> 
      <p style="margin: 0px 0px 0px 0px;"><?php echo sep($nos); ?></p>
      <h3 style="margin: 0px 0px 0px 0px; text-align: center;"><B>WAITERS TOTAL</B></h3>
      <p style="margin: 0px 0px 0px 0px;"><?php echo sep($nos); ?></p>
      <table class="table">
       <tbody>  
        <?php
        $sum = 0;
        $token_sum = 0;
        $sales = mysqli_query($connection,"SELECT SUM(amount) as total,user_id  FROM kp_sales WHERE shift='$shift' GROUP BY user_id ORDER BY total DESC") or die('Error fetching the staff data.:'.mysqli_error($connection));
          if (mysqli_num_rows($sales) < 1) {
            echo "<tr><td colspan='4'>No orders have been raised</td</tr>";
          }else{
            ?>
            <tr> 
              <td class="text-center"><b>Waiter</b></td> 
              <td class="text-center"><b>Total / Tokens</b></td>
            </tr>
            <?php
            while ($row = mysqli_fetch_assoc( $sales)) {
              $user = $row['user_id'];
              $amount = $row['total']; 
              $sum=$sum+$amount; 
              $waiter_tokens = function_exists('get_user_shift_token_total') ? (float)get_user_shift_token_total($user, $shift) : 0;
              $token_sum += $waiter_tokens;
              ?>
              <tr> 
                <td><?php echo get_column('kp_users','username',"user_id='$user'"); ?></td>   
                <td class="compact-right">
                  <?php echo thermal_money($amount); ?><br>
                  <span class="muted-line">T: <?php echo thermal_money($waiter_tokens); ?></span>
                </td>
              </tr>
              <?php
            }
          }  
          ?>
          <tr>
            <th colspan="2" class="text-center"><B style=" font-size: 16px;">Total: <?php  echo thermal_money($sum); ?> / Tokens: <?php echo thermal_money($token_sum); ?></B></th> 
          </tr> 
        </tbody>
      </table>  

<!--END ITEMS OUT -->
      <p style="margin: 0px 0px 0px 0px;"><?php echo sep($nos); ?></p>
      <p style="margin: 0px 0px 0px 0px;"><?php echo sep($nos); ?></p>
  </div>
 

</body>
</html>
