<?php
session_start();
include('../../inc/connection.php');
require_once('funcs.php'); 
$user_id = $_SESSION['user_id'];
if (!isset($_SESSION['user_id']) || $_SESSION['user_id']==0) {
  header("Location:../log_out.php");
}

$get_thermal_details = mysqli_query($connection,"SELECT * FROM kp_settings_print") or die("Could not get the print details");
$row = mysqli_fetch_assoc($get_thermal_details);
$gen_r = mysqli_query($connection, "SELECT * FROM kp_settings_gen WHERE id=1");
$gen = $gen_r ? mysqli_fetch_assoc($gen_r) : array();
$thermal_logo = !empty($gen['thermal_logo_path']) ? $gen['thermal_logo_path'] : ($gen['image_path'] ?? '');
$print_template = isset($row['print_template']) ? (int)$row['print_template'] : 1;
$thermal_logo_height = isset($row['thermal_logo_height']) ? (int)$row['thermal_logo_height'] : 60;
if ($thermal_logo_height < 30) {
  $thermal_logo_height = 30;
}
if ($thermal_logo_height > 180) {
  $thermal_logo_height = 180;
}
if ($print_template !== 2) {
  $print_template = 1;
}

$shift = get_column('kp_shift','shift',"1");

date_default_timezone_set("Africa/Nairobi");
$time=date("h:i:a");
$today=today();

$status = $_GET['status'];
if ($status==1) { 
  $table="kp_sale_print"; 
  $sale_id = get_column('kp_thesion_front','sale_id',"user_id='$user_id'");
  $action="PRINTED|-|-|ORDER NO|".$sale_id;
  $fun="window.print();closeord();";
  // For a continued bill, parent is still in kp_sale_status at this point
  $parent_bill = (int)get_column('kp_sale_status', 'parent', "sale_id='$sale_id'");
  if ($parent_bill == 0) {
    $parent_bill = (int)get_column('kp_sales', 'parent', "sale_id='$sale_id'");
  }
  $open_state = trim((string)($_GET['open_state'] ?? ($_SESSION['open_sale_print'][$sale_id]['state'] ?? '')));
}elseif($status==2){
  $sale_id = $_GET['sale_id'];
  $user_id = get_column("kp_sales","user_id","sale_id='$sale_id'"); 
  $table="kp_sale_items"; 
  $_SESSION['sale_id']=$sale_id; 
  $parent_bill = (int)get_column('kp_sales', 'parent', "sale_id='$sale_id'");

  $action="PRINTED AFTER REPRINT|-|-|ORDER NO|".$sale_id;
  $fun="window.print();reprint()";
  $open_state = '';
}elseif($status==3){
  $sale_id = $_GET['sale_id'];
  $user_id = get_column("kp_sales","user_id","sale_id='$sale_id'"); 
  $table="kp_sale_items"; 
  $_SESSION['sale_id']=$sale_id; 
  $action="PRINTED AFTER RETURN|-|-|ORDER NO|".$sale_id;
  $fun="window.print();retord()";
  $parent_bill = 0;
  $open_state = '';
}

$sale_stub = ['parent' => $parent_bill];
$bill_context = function_exists('kp_chaba_bill_context')
  ? kp_chaba_bill_context($connection, $sale_id, $sale_stub, $open_state)
  : [
      'parent_bill' => $parent_bill,
      'has_parent_sale_col' => false,
      'has_sales_open_table' => false,
      'open_state' => $open_state,
      'closed_parent_final' => false,
      'open_display_bill_no' => 0,
    ];
$parent_bill = (int)$bill_context['parent_bill'];
$has_parent_sale_col = !empty($bill_context['has_parent_sale_col']);
$has_sales_open_table = !empty($bill_context['has_sales_open_table']);
$open_state = (string)$bill_context['open_state'];
$post_print_redirect = $_SESSION['kw_post_print_redirect'] ?? '../log_out.php';
unset($_SESSION['kw_post_print_redirect']);

$itemsnum=column_count($table,'item_id',"sale_id='$sale_id'");
$tbl = 1; 
logs($action,"../logs");  

$counter=get_column('kp_thesion_front','cntr_id',"sale_id='$sale_id'"); 
$skip_customer_bill = (int)($_GET['skip_customer_bill'] ?? 0) === 1;
$open_bill_number = 0;
$open_previous_total = 0.0;
$open_current_total = 0.0;
$open_combined_total = 0.0;
$closed_parent_final = !empty($bill_context['closed_parent_final']);
$open_bill_number = (int)($bill_context['open_display_bill_no'] ?? 0);
$is_continuation_child = (!empty($parent_bill) && $parent_bill > 0 && !$closed_parent_final && (int)$sale_id !== (int)$parent_bill);
if ($is_continuation_child) {
  $open_current_total = (float)column_total($table, 'total', "sale_id='$sale_id'");
  $open_previous_total = (float)get_column('kp_sales', 'amount', "sale_id='$parent_bill'");
  $child_parent_where = "parent='$parent_bill'";
  if ($has_parent_sale_col) {
    $child_parent_where = "(parent='$parent_bill' OR parent_sale='$parent_bill')";
  }
  $open_previous_children = mysqli_query($connection, "SELECT SUM(amount) AS prev_total, COUNT(sale_id) AS prev_count FROM kp_sales WHERE $child_parent_where AND sale_id!='$sale_id'");
  if ($open_previous_children) {
    $prev_row = mysqli_fetch_assoc($open_previous_children);
    $open_previous_total += (float)($prev_row['prev_total'] ?? 0);
    $open_bill_number = ((int)($prev_row['prev_count'] ?? 0)) + 2;
  } else {
    $open_bill_number = 2;
  }
  $open_combined_total = $open_previous_total + $open_current_total;
}
$show_continue_summary = ($parent_bill > 0 && (int)$sale_id !== (int)$parent_bill);
if ($show_continue_summary) {
    $open_display_previous = (float)($bill_context['previous_balance'] ?? ($open_display_previous ?? 0));
    $open_current_total = (float)($bill_context['current_total'] ?? ($open_current_total ?? 0));
    $open_balance_total = (float)($bill_context['current_balance'] ?? ($open_display_previous + $open_current_total));
    if (empty($bill_context['open_display_bill_no'])) {
        $bill_context['open_display_bill_no'] = 2;
    }
}
  ?>
  <!DOCTYPE html>
  <html lang="en">
  <head> 
    <title>RECEIPT</title>
    <style>
      <?php if ($print_template === 2) { ?>
      * { box-sizing: border-box; }
      body {
        margin: 4mm;
        background: #ddd;
        font-family: 'Courier New', Courier, monospace;
        font-size: 13px;
      }
      #receipt {
        margin: 0 auto;
        width: 79mm;
        background: #fff;
        padding: 3mm 2mm;
        color: #000;
      }
      #bord {
        border: 0;
      }
      table {
        width: 100%;
        border-collapse: collapse;
        margin: 2px 0;
        background: #fff;
      }
      td, th {
        font-size: 12px;
        border: none;
        padding: 2px 1px;
        vertical-align: top;
      }
      th {
        border-top: 1px solid #000;
        border-bottom: 1px solid #000;
        text-transform: uppercase;
      }
      .text-center { text-align: center !important; }
      .text-left   { text-align: left !important; }
      .text-right  { text-align: right !important; }
      .pull-left   { float: left !important; }
      .pull-right  { float: right !important; }
      h1.title {
        font-size: 22px;
        margin: 4px 0 2px;
        letter-spacing: 2px;
      }
      p, span { font-size: 12px; }
      .sep-eq  { border: none; border-top: 2px solid #000; margin: 3px 0; }
      .sep-dot { border: none; border-top: 1px dashed #555; margin: 3px 0; }
      .meta {
        display: flex;
        justify-content: space-between;
        font-size: 11px;
        margin: 1px 0;
      }
      .meta span { white-space: nowrap; }
      .items-tbl thead th:first-child,
      .items-tbl tbody td:first-child { text-align: left; }
      .items-tbl thead th:not(:first-child),
      .items-tbl tbody td:not(:first-child) { text-align: right; }
      .items-tbl .sec-label td {
        font-size: 11px;
        font-weight: bold;
        text-decoration: underline;
        padding-top: 4px;
      }
      .totals-tbl td:first-child { text-align: left; }
      .totals-tbl td:last-child { text-align: right; }
      .totals-tbl .grand td {
        font-size: 16px;
        font-weight: bold;
        padding-top: 3px;
      }
      .qr-wrap {
        text-align: center;
        margin: 6px 0;
      }
      .qr-wrap canvas,
      .qr-wrap img {
        display: block;
        margin: 0 auto;
        background: #ffffff;
        border: 1px solid #000;
      }
      .qr-caption {
        font-size: 10px;
        margin: 3px 0;
        text-align: center;
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
      .open-customer-total {
        margin-top: 5px;
        padding-top: 4px;
        border-top: 1px dashed #555;
        font-size: 15px;
        font-weight: 900;
      }
      .open-demo-summary .line-label th,
      .open-demo-summary .line-value th {
        font-size: 14px;
      }
      @media print {
        body { margin: 1mm; background: #fff; }
        #receipt {
          width: 100%;
          padding: 1mm;
          background: #fff;
        }
        .qr-wrap canvas { width: 140px !important; height: 140px !important; }
        p.break { page-break-after: always; }
      }
      <?php } else { ?>
      body {
        margin: 2mm;
        background: #f5f5f5;
      }
      #receipt {
        padding: 1mm;
        margin: 0 auto;
        width: 79mm;
        background: #25e25e;
        color: #000;
        font-family: Arial, Helvetica Neue, Helvetica, sans-serif;
      }
      #bord {
        border: 2px solid #000 !important;
      }
      table {
        padding: 1mm;
        margin: 0 auto;
        width: 75mm;
        background: #25e25e;
        border-collapse: collapse;
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
      .text-center { text-align: center !important; }
      .text-left   { text-align: left !important; }
      .text-right  { text-align: right !important; }
      .pull-left   { float: left !important; }
      .pull-right  { float: right !important; }
      h1.title {
        font-size: 26px;
        margin-bottom: 0px;
      }
      p   { font-size: 13px; }
      span{ font-size: 13px; }
      .qr-wrap {
        text-align: center;
        margin: 6px 0;
      }
      .qr-wrap canvas,
      .qr-wrap img {
        display: block;
        margin: 0 auto;
        background: #25e25e;
      }
      .qr-caption {
        font-size: 10px;
        margin: 3px 0;
        text-align: center;
      }
      .open-customer-box {
        margin: 6px auto 8px;
        padding: 6px 4px;
        width: 95%;
        border: 2px solid #000;
        background: #b7f0ba;
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
      .open-demo-summary .line-label th,
      .open-demo-summary .line-value th {
        font-size: 14px;
      }
      @media print {
        body { margin: 2mm; background: #fff; }
        #receipt {
          padding: 1mm;
          height: 100% !important;
          width: 100% !important;
          background: #25e25e;
        }
        #bord  { border: 2px solid #000 !important; }
        table  {
          padding: 1mm;
          margin: 0 auto;
          width: 98% !important;
          background: #25e25e;
          border-collapse: collapse;
        }
        td, th { font-size: 13px; border: 1.5px solid #000 !important; }
        th     { font-size: 12px; padding: 2px; margin: 2px; }
        span   { font-size: 12px; padding: 5px; }
        p.break { page-break-after: always; }
      }
      <?php } ?>
      .receipt-divider{
        clear: both !important;
        display: block !important;
        margin-top: 3px !important;
      }
  </style>
    <script src="https://cdnjs.cloudflare.com/ajax/libs/qrcodejs/1.0.0/qrcode.min.js"></script>
    <script type="text/javascript">
      (function () {
        var printStarted = false;
        var redirected = false;
        var redirectUrl = '';
        <?php if ($status==1) { ?>
        redirectUrl = <?php echo json_encode($post_print_redirect); ?>;
        <?php } elseif ($status==2) { ?>
        redirectUrl = '../view/my_sales.php';
        <?php } elseif ($status==3) { ?>
        redirectUrl = '../ret/returns.php';
        <?php } ?>

        function redirectAfterPrint() {
          if (redirected || !redirectUrl) {
            return;
          }
          redirected = true;
          window.location.replace(redirectUrl);
        }

        function startPrint() {
          if (printStarted) {
            return;
          }
          printStarted = true;
          window.print();
        }

        window.addEventListener('load', function () {
          setTimeout(startPrint, 250);
        });

        window.addEventListener('afterprint', function () {
          setTimeout(redirectAfterPrint, 150);
        });

        document.addEventListener('visibilitychange', function () {
          if (!document.hidden && printStarted) {
            setTimeout(redirectAfterPrint, 300);
          }
        });
      })();
    </script>
  </head>
  <!--" -->
  <body> 
  <?php 
  if ($itemsnum>0) {
    ?>

  <?php if (!$skip_customer_bill) { ?>
  <div id="receipt"> 
    <div id="bord">
      <?php if ($print_template === 2) { ?>
      <div id="receipt-header" class="text-center">
        <?php if ($row['show_logo']==1 && !empty($thermal_logo)) { ?>
        <img src="<?php echo htmlspecialchars($thermal_logo); ?>" style="height: <?php echo $thermal_logo_height; ?>px; width:auto; margin-top: 10px;" /><br>
        <?php } ?>
        <h1 class="title"><?php echo strtoupper(get_column("kp_settings_gen","company_name","id=1")); ?></h1>
        <?php if (!empty($gen['company_tag'])) { ?>
          <p style="font-size: 12px; margin: 0;"><em><?php echo htmlspecialchars($gen['company_tag']); ?></em></p>
        <?php } ?>
        <hr class="sep-dot">
        <?php if (!empty($gen['address'])) { ?>
          <p style="margin: 0;"><?php echo htmlspecialchars($gen['address']); ?></p>
        <?php } ?>
        <p style="margin: 0;">Cell: <?php echo htmlspecialchars($gen['phone1'] ?? ''); ?> | <?php echo htmlspecialchars($gen['phone2'] ?? ''); ?></p>
        <?php if ($row['pin']==1 && !empty($gen['kra_pin'])) { ?>
          <p style="margin: 0;">PIN: <?php echo htmlspecialchars($gen['kra_pin']); ?></p>
        <?php } ?>
      </div>

      <hr class="sep-eq">

      <div class="meta"><span>BILL NO: <b><?php echo $sale_id; ?></b></span><span><?php echo date("d-M-Y"); ?></span></div>
      <div class="meta"><span>CASHIER: <b><?php echo strtoupper(get_column("kp_users","username","user_id='$user_id'")); ?></b></span><span><?php echo $time; ?></span></div>
      <div class="meta"><span>SHIFT: <b><?php echo $shift; ?></b></span><span>COUNTER: <?php echo strtoupper(get_column('kp_counters','name',"cntr_id='$counter'")); ?></span></div>
      <?php if ($show_continue_summary) { ?>
      <div class="meta"><span>CONTINUATION</span><span>#<?php echo $parent_bill; ?></span></div>
      <div class="meta"><span>OPEN STATE</span><span>STILL CONTINUING</span></div>
      <?php } ?>
      <hr class="sep-eq">
      <?php } else { ?>
      <div id="receipt-header" class="text-center" style="margin-right: 5px;">
        <p style="margin: 0px 0px 0px 0px;">***********************************************************</p>
        <?php if ($row['show_logo']==1 && !empty($thermal_logo)){ ?>
        <img src="<?php echo htmlspecialchars($thermal_logo); ?>" style="height: <?php echo $thermal_logo_height; ?>px; width:auto; margin-left: 30px; margin-top: 20px;" /></br>
        <?php } ?>
        <h1 class="title"><?php echo strtoupper(get_column("kp_settings_gen","company_name","id=1")); ?></h1>
        <?php 
        if (empty($gen['company_tag'])) {}else{ 
          ?>
          <p style="font-size: 18px;margin: 0px 0px 0px 0px;">(<?php echo $gen['company_tag']; ?>)</p>
          <?php 
        } ?>      
        <p style="margin: 0px 0px 0px 0px;">**********************************************************</p>
        <?php 
        if (empty($gen['company_tag'])) {}else{ 
        ?>
        <p style="margin: 0px 0px 0px 0px; "><?php echo get_column("kp_settings_gen","address","id=1"); ?>,</P><BR> 
          <?php 
        } ?>
        <p style="margin: 0px 0px 0px 0px; "> Cell: <?php echo get_column("kp_settings_gen","phone1","id=1"); ?> | <?php echo get_column("kp_settings_gen","phone2","id=1"); ?><BR></P>
        <p style="margin: 0px 0px 0px 0px;">***********************************************************</p>
        <?php if ($row['pin']==1 ){ ?>
        <span class="pull-left">PIN: <?php echo get_column("kp_settings_gen","kra_pin","id=1"); ?></span> 
        <?php } ?>
        <span class="pull-left"> <b>SERVED BY:  <?php echo strtoupper(get_column("kp_users","username","user_id='$user_id'")); ?></span>
        <span class="pull-right"><b>BILL No: <?php echo $sale_id ?></b></span> <br>
        <span class="pull-left"><b>SHIFT: <?php echo $shift; ?></b></span> 
        <span class="pull-right"><b>SALE TIME: <?php echo $time; ?></b></span>   
        <?php if ($show_continue_summary) { ?>
        <p style="margin: 2px 0px; font-size:13px; font-weight:bold; text-align:center;">&#x2197; CONTINUATION OF BILL #<?php echo $parent_bill; ?></p>
        <p style="margin: 2px 0px; font-size:13px; font-weight:bold; text-align:center;">STILL CONTINUING</p>
        <?php } ?>
        <p style="margin: 0px 0px 0px 0px;">***********************************************************</p>
      </div>
      <?php } ?>
      <div id="receipt-body">
        <?php 
        $get_orders = mysqli_query($connection,"SELECT SUM(total) AS order_total FROM $table WHERE sale_id='$sale_id'");
        $or = mysqli_fetch_assoc($get_orders);
        if(empty($or)){
          $amount=0;
        }else{
          $amount = $or['order_total'];
        } 
        $vat=round((16/116)*$amount,2,PHP_ROUND_HALF_UP);
        $ctl=round((2/102)*$amount,2,PHP_ROUND_HALF_UP);
        $vatable=$amount-$vat;
        ?>
        <table class="<?php echo $print_template === 2 ? 'items-tbl' : 'table table-bordered'; ?>" id="deSo"> 
          <tbody>
            <?php
           // In the receipt-body section for the bill:
$get_order_r = mysqli_query($connection,"SELECT sum(qty) as sqty, item_id, price, attr_id FROM $table WHERE sale_id='$sale_id' AND hour=1 GROUP BY item_id, attr_id") or die("Could not fetch the order");
$num=mysqli_num_rows($get_order_r);
if ($num==0) {
}else{ 
    while ($o = mysqli_fetch_assoc($get_order_r)) {
        $item_id = $o['item_id'];
        $price = $o['price']; 
        $qty = $o['sqty'];
        $attr_id = $o['attr_id'];
        
        // Get item name with attribute if exists
        $item_name = get_column("kp_items","name","item_id='$item_id'");
        if (!empty($attr_id)) {
            $attr_name = get_column('kp_items_attributes', 'name', "id='$attr_id'");
            $item_name .= " ($attr_name)";
        }
        ?>
        <tr>
            <td><?php echo $qty; ?></td>
            <td><?php echo $item_name; ?></td>
            <td colspan="2"><?php echo number_format($price,2); ?></td>
            <td><?php echo number_format($qty * $price,2);?></td>
        </tr>
        <?php
    }
}
         // Similarly for happy hour items:
$get_horder_r = mysqli_query($connection,"SELECT sum(qty) as sqty, item_id, price, attr_id FROM $table WHERE sale_id='$sale_id' AND hour=2 GROUP BY item_id, attr_id") or die("Could not fetch the order");
$num=mysqli_num_rows($get_horder_r);
if ($num==0) {
}else{
    ?>
    <tr class="<?php echo $print_template === 2 ? 'sec-label' : ''; ?>"> 
        <td colspan="5" style="font-size: 14px"> <b>HAPPY HOUR ORDER</b> </td> 
    </tr>
    <?php
    while ($o = mysqli_fetch_assoc($get_horder_r)) {
        $item_id = $o['item_id'];
        $price = $o['price']; 
        $qty = $o['sqty'];
        $attr_id = $o['attr_id'];
        
        // Get item name with attribute if exists
        $item_name = get_column("kp_items","name","item_id='$item_id'");
        if (!empty($attr_id)) {
            $attr_name = get_column('kp_items_attributes', 'name', "id='$attr_id'");
            $item_name .= " ($attr_name)";
        }
        ?>
        <tr>
            <td><?php echo $qty; ?></td>
            <td><?php echo $item_name; ?></td>
            <td colspan="2"><?php echo number_format($price,2); ?></td>
            <td><?php echo number_format($qty * $price,2);?></td>
        </tr>
        <?php
    }
}
            ?>
          </tbody>
          <?php if ($print_template === 2) { ?>
          <tfoot class="totals-tbl">
            <tr class="grand">
              <td colspan="4"><strong>TOTAL</strong></td>
              <td><strong><?php echo number_format($amount,2); ?></strong></td>
            </tr>
          </tfoot>
          <?php } else { ?>
          <tfoot>
            <tr>
              <th colspan="5" class="text-center"><B style=" font-size: 22px;">Total: <?php  echo number_format($amount,2); ?>&nbsp;</B></th> 
            </tr>
          </tfoot>
          <?php } ?>
        </table>
        <?php if ($print_template === 2) { ?>
        <hr class="sep-dot">
        <?php } else { ?>
        <hr>
        <?php } ?>
        <?php if ($show_continue_summary) { ?>
        <table class="<?php echo $print_template === 2 ? 'totals-tbl' : 'table table-bordered'; ?>" style="margin-top:4px;">
          <tbody>
            <tr>
              <td colspan="2"><strong style="font-size:16px;">BILL NO.</strong></td>
              <td colspan="3" class="text-right"><strong style="font-size:16px;"><?php echo $open_bill_number; ?></strong></td>
            </tr>
            <tr>
              <td colspan="5" class="text-center"><strong style="font-size:12px;">PREVIOUS BILL NO. <?php echo (int)($bill_context['previous_bill_sale_id'] ?? 0); ?></strong></td>
            </tr>
            <tr>
              <td colspan="2"><strong style="font-size:14px;">PREVIOUS TOTAL</strong></td>
              <td colspan="3" class="text-right"><strong style="font-size:14px;"><?php echo number_format($open_previous_total, 2); ?></strong></td>
            </tr>
            <tr>
              <td colspan="2"><strong style="font-size:14px;">CURRENT BILL</strong></td>
              <td colspan="3" class="text-right"><strong style="font-size:14px;"><?php echo number_format($open_current_total, 2); ?></strong></td>
            </tr>
            <tr>
              <td colspan="2"><strong>BALANCE</strong></td>
              <td colspan="3" class="text-right"><strong><?php echo number_format($open_combined_total, 2); ?></strong></td>
            </tr>
          </tbody>
        </table>
        <?php } ?>
      </div>
      <div id="receipt-footer" style="margin-right: 5px;"> 
        <?php 
        if (get_column("kp_settings_print","tax","print_doc=1")==2) {
          ?>
          <?php if ($print_template === 2) { ?>
          <table class="totals-tbl">
            <tr><td>Gross</td><td><?php echo number_format($amount,2); ?></td></tr>
            <tr><td>V.A.T (16%)</td><td><?php echo number_format($vat,2); ?></td></tr>
            <tr><td>Cat Levy (2%)</td><td><?php echo number_format($ctl,2); ?></td></tr>
          </table>
          <?php } else { ?>
          <p style="margin: 0px 0px 0px 0px;">***********************************************************</p> 
          <span class="pull-left">Gross:<?php echo $amount; ?></span> 
          <span class="pull-right">V.A.T(16%): <?php  echo $vat; ?></span><br>
          <span class="pull-right">Cat Levy(2%): <?php echo $ctl; ?></span>  
          <?php } ?>
          <?php
        }
        ?>  
        <p style="margin: 0px 0px 0px 0px;"><?php echo $print_template === 2 ? '' : '***********************************************************'; ?></p>
        <p class="text-center" style="margin: 0px 0px 0px 0px;"><b>THANK YOU</b></p>
        <p class="text-center" style="margin: 0px 0px 0px 0px;"><?php echo $row['thankyou_note'];?></p>
        <p class="text-center" style="margin: 0px 0px 0px 0px;"> This is not an ETR. Request one</p>
        <?php if ($print_template === 2) { ?>
        <hr class="sep-dot">
        <div class="mpesa-line"><?php if ($row['mpesa']==2 ){ echo "PAYBILL ".get_column("kp_settings_gen","mpesa_no","id=1")." ACCOUNT: ".get_column("kp_settings_gen","mpesa_account","id=1"); } else { echo "TILL ".get_column("kp_settings_gen","mpesa_no","id=1"); } ?></div>
        <hr class="sep-dot">
        <?php } else { ?>
        <p style="margin: 0px 0px 0px 0px;">***********************************************************</p>
        <h2 class="text-center" style="margin: 0px 0px 0px 0px;font-size: 20px;font-weight: bold; font-family: 'Helvetica LT W01 Black Cond';letter-spacing: 3px;"><?php if ($row['mpesa']==2 ){ echo "PAYBILL". " ".get_column("kp_settings_gen","mpesa_no","id=1"). " ACCOUNT:". get_column("kp_settings_gen","mpesa_account","id=1"); } else{echo " TILL ". " ".get_column("kp_settings_gen","mpesa_no","id=1"); }?> </h2>
        <p style="margin: 0px 0px 0px 0px;">***********************************************************</p>
        <?php } ?>
        <?php if (!empty($row['show_qr']) && $row['show_qr'] == 1) { ?>
        <div class="qr-wrap">
          <div id="qr-bill"></div>
          <p class="qr-caption">Bill: <?php echo $sale_id; ?></p>
        </div>
        <?php } ?>
        <?php if ($print_template === 2) { ?>
        <hr class="sep-dot">
        <?php } else { ?>
        <p style="margin: 0px 0px 0px 0px;">***********************************************************</p>
        <?php } ?>
        <p class="text-center" style="margin: 0px 0px 0px 0px; font-size: 11px;"> K-TOUCH POS Designed &amp; Distributed by K-TEN <br> +254716100335 | +254735100335 .</p>
        <?php if ($print_template !== 2) { ?>
        <p style="margin: 0px 0px 0px 0px;">***********************************************************</p>
        <?php } ?>
      </div>
    </div>
  </div>
  <?php } ?>
  <?php 
  }  
  ?>

  <!-- START THE TICKETS -->
  <?php  
  $get_order = "SELECT sale_id FROM $table WHERE sale_id='$sale_id' AND status=2 AND dpt=1  GROUP BY item_id";
  $get_order_r = mysqli_query($connection,$get_order) or die("Could not fetch the order");
  $quantity = mysqli_num_rows($get_order_r);
  if ($quantity<1 || $status==3) {}else{
    ?>
    <p class="break"></p>
    <div id="receipt">
      <br>
      <div id="bord">
        <div id="receipt-header" class="text-center">
          <p style="margin: 0px 0px 0px 0px;">***********************************************************</p>
          <h1 style="margin: 0px 0px 0px 0px;"><?php echo get_column('kp_counters','name',"cntr_id='$counter'"); ?></h1> 
          <p style="margin: 0px 0px 0px 0px;">***********************************************************</p> <h2 style="margin: 0px 0px 0px 0px;">BAR ORDER TICKET(BOT)</h2> 
          <p style="margin: 0px 0px 0px 0px;">***********************************************************</p> 
          <h2 style="margin: 0px 0px 0px 0px;"> WAITER: </strong><?php echo strtoupper(get_column("kp_users","username","user_id='$user_id'")); ?></h2> 
          <h2 style="margin: 0px 0px 0px 0px;">Order No: <?php echo $sale_id; ?></h2>
          <h2 style="margin: 0px 0px 0px 0px;"><strong>SHIFT: <?php  echo $shift; ?></strong></h2> 
          <span class="text-center"><strong>PRINT TIME: <?php echo totime(); ?></strong></span>
          <p style="margin: 0px 0px 0px 0px;">***********************************************************</p>
        </div>
        <div id="receipt-body">
          <table>
            <thead>
              <tr>
                <th>Name</th>
                <th>Qty</th>
                <th>Price</th>
                <th>Total</th>
              </tr>
            </thead>
            <tbody>
              <?php
             // In the KOT section:
$get_order = "SELECT item_id, attr_id FROM $table WHERE sale_id='$sale_id' AND status=2 AND dpt=1 GROUP BY item_id, attr_id";
$get_order_r = mysqli_query($connection,$get_order) or die("Could not fetch the order");
while ($o = mysqli_fetch_assoc($get_order_r)) {
    $item_id = $o['item_id'];
    $attr_id = $o['attr_id'];
    $price = get_column("kp_items","price","item_id='$item_id'");
    
    $get_quantity = "SELECT SUM(qty) AS the_qty FROM $table WHERE item_id='$item_id' AND sale_id='$sale_id' AND status=2 AND dpt=1";
    if (!empty($attr_id)) {
        $get_quantity .= " AND attr_id='$attr_id'";
    } else {
        $get_quantity .= " AND attr_id IS NULL";
    }
    
    $get_quantity_r = mysqli_query($connection,$get_quantity) or die("Could not get the quantity");
    $s = mysqli_fetch_assoc($get_quantity_r);
    $quantity = $s['the_qty'];
    
    // Get item name with attribute if exists
    $item_name = get_column("kp_items","name","item_id='$item_id'");
    if (!empty($attr_id)) {
        $attr_name = get_column('kp_items_attributes', 'name', "id='$attr_id'");
        $item_name .= " ($attr_name)";
    }
    ?>
    <tr>
        <td><?php echo $item_name; ?></td>
        <td><?php echo $quantity; ?></td>
        <td><?php echo $price; ?></td>
        <td id="myStyle"><?php echo number_format($quantity * $price,2); ?></td>
    </tr>
    <?php
}
              ?>
            </tbody> 
            <tfoot>
              <?php 
              $get_orders = mysqli_query($connection,"SELECT SUM(total) AS order_total FROM $table WHERE sale_id='$sale_id'  AND status=2 AND dpt=1 ");
              $or = mysqli_fetch_assoc($get_orders);
              $sum = $or['order_total'];  
              ?>
              <tr>
                <th colspan="5" class="text-center"><B style=" font-size: 22px;">Total: <?php  echo number_format($sum,2); ?>&nbsp;</B></th> 
              </tr> 
            </tfoot>
          </table>
        </div>
        <p style="margin: 0px 0px 0px 0px;">***********************************************************</p>
        <p style="margin: 0px 0px 0px 0px;">***********************************************************</p>
        <br>   
      </div>
    </div>
  <?php 
  } 
   if ($status==3) { 
    ?>
  <p class="break">.</p> 
  <div id="receipt">
    <div id="bord">
      <div id="receipt-header" class="text-center">
        <h1 class="title">RETURNED ITEMS</h1>
        <h1 class="title"><?php echo strtoupper(get_column("kp_settings_gen","company_name","id=1")); ?></h1> 
         <p style="margin: 0px 0px 0px 0px;">********************************************************</p>
         <span><b>SHIFT:  <?php  echo $shift; ?></b></span> <BR> 
        <span><b>RETURN BY:<?php echo strtoupper(get_user($user_id)); ?></b></span><BR> 
        <span><b>BILL NO:<?php echo $sale_id; ?></b></span><BR> 
         <span><b>PRINT DATE <?php  today(); ?></b></span> <BR>  
        <BR>
        
        <p style="margin: 0px 0px 0px 0px;">*********************************************************</p>
     </div>
      <div id="receipt-body">
        <table>
          <thead>
            <tr>
              <th>#</th>
              <th>Item</th>
              <th>Qty</th> 
              <th>Confirmed By</th>
            </tr>
          </thead>
          <tbody>
            <?php
            $no=0;
            $get_items = mysqli_query($connection,"SELECT * FROM kp_ret_items WHERE sale_id='$sale_id'");
            while ($s = mysqli_fetch_assoc($get_items)){
              $item_id = $s['item_id'];
              $qty=$s['qty'];
              $confirm_by=$s['confirm_by']; 
              $no=$no+1; 
              ?>
            <tr>
             <td><?php echo $no; ?></td>
             <td><?php echo get_item($item_id); ?></td>
             <td><?php echo $qty; ?></td>  
             <td><?php echo get_user($confirm_by); ?></td>
            </tr>
            <?php
              }
            ?>
          </tbody> 
        </table>
        <p style="margin: 0px 0px 0px 0px;">*********************************************************</p>
        <p style="margin: 0px 0px 0px 0px;">*********************************************************</p>
      </div>
    </div>
  </div>
   <?php 
  } ?>
  <script>
    <?php if (!empty($row['show_qr']) && $row['show_qr'] == 1) { ?>
    if (document.getElementById('qr-bill')) {
      new QRCode(document.getElementById('qr-bill'), {
        text: '<?php echo $sale_id; ?>',
        width: 140,
        height: 140,
        colorDark: '#000000',
        colorLight: '#ffffff',
        correctLevel: QRCode.CorrectLevel.M
      });
    }
    <?php } ?>
  </script>
</body>
</html>
 
