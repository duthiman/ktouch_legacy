<?php
session_start();
include('../../inc/connection.php');
include('../../inc/gen_funcs.php');
require_once(__DIR__ . '/render_css.php');

$receiptDivider = function_exists('kp_receipt_divider_text') ? kp_receipt_divider_text($connection) : str_repeat('*', 50);

$sale_id = (int)($_GET['sale_id'] ?? 0);
$auto_print = (int)($_GET['auto_print'] ?? 0) === 1;
if ($sale_id < 1) { exit('Invalid sale'); }

$sale_r = mysqli_query($connection, "SELECT * FROM kp_sales WHERE sale_id='$sale_id'");
$sale = ($sale_r && mysqli_num_rows($sale_r) > 0) ? mysqli_fetch_assoc($sale_r) : null;
if (!$sale) {
    $fallback_user = (int)get_column('kp_sale_items', 'user_id', "sale_id='$sale_id'");
    $fallback_shift = (string)get_column('kp_sale_items', 'shift', "sale_id='$sale_id'");
    $fallback_amount = (float)column_total('kp_sale_items', 'total', "sale_id='$sale_id'");
    if ($fallback_amount <= 0) {
        $fallback_amount = (float)column_total('kp_sale_print', 'total', "sale_id='$sale_id'");
    }
    if ($fallback_user < 1 && $fallback_amount <= 0) {
        exit('Sale not found');
    }
    $sale = [
        'user_id' => $fallback_user,
        'shift' => $fallback_shift,
        'amount' => $fallback_amount,
    ];
}
$sale     = $sale;
$user_id  = $sale['user_id'];
$shift    = $sale['shift'];
$amount   = (float)$sale['amount'];

$spr = mysqli_query($connection, "SELECT * FROM kp_settings_print WHERE print_doc=1");
$row = $spr ? mysqli_fetch_assoc($spr) : [];
$gen = mysqli_fetch_assoc(mysqli_query($connection, "SELECT * FROM kp_settings_gen WHERE id=1"));
$thermal_logo = !empty($gen['thermal_logo_path']) ? $gen['thermal_logo_path'] : ($gen['image_path'] ?? '');
$thermal_logo_height = isset($row['thermal_logo_height']) ? (int)$row['thermal_logo_height'] : 60;
if ($thermal_logo_height < 30) { $thermal_logo_height = 30; }
if ($thermal_logo_height > 180) { $thermal_logo_height = 180; }
$print_template = kp_render_print_template_id();

date_default_timezone_set("Africa/Nairobi");
$time = date("h:i:a");
$nos  = 50;
?>
<!DOCTYPE html>
<html lang="en">
<head>
  <meta charset="UTF-8">
  <title>RETURN #<?php echo $sale_id; ?></title>
  <style>
    <?php echo kp_render_print_template_css($print_template); ?>
    .receipt-divider { margin:0; line-height:1.1; white-space:nowrap; overflow:hidden; text-align:center; font-weight:700; letter-spacing:.06em; }
    body, #receipt, table, #receipt-header, #receipt-body, #receipt-footer, .open-customer-box {
      background: #fff !important;
    }
  </style>
</head>
<body>
<div id="receipt">
  <div id="bord">
    <div id="receipt-header" class="text-center" style="margin-right:5px;">
      <p class="receipt-divider"><?php echo htmlspecialchars($receiptDivider); ?></p>
      <?php if (!empty($row['show_logo']) && $row['show_logo'] == 1 && !empty($thermal_logo)): ?>
        <img src="<?php echo htmlspecialchars($thermal_logo); ?>" style="height:<?php echo $thermal_logo_height; ?>px;width:auto;margin-top:10px;"><br>
      <?php endif; ?>
      <h1 class="title"><?php echo strtoupper(htmlspecialchars($gen['company_name'])); ?></h1>
      <?php if (!empty($gen['company_tag'])): ?>
        <p style="font-size:18px;margin:0;">(<?php echo htmlspecialchars($gen['company_tag']); ?>)</p>
      <?php endif; ?>
      <p class="receipt-divider"><?php echo htmlspecialchars($receiptDivider); ?></p>
      <?php if (!empty($gen['address'])): ?>
        <p style="margin:0;"><?php echo htmlspecialchars($gen['address']); ?></p>
      <?php endif; ?>
      <p style="margin:0;">Cell: <?php echo htmlspecialchars($gen['phone1']); ?> | <?php echo htmlspecialchars($gen['phone2']); ?></p>
      <p class="receipt-divider"><?php echo htmlspecialchars($receiptDivider); ?></p>
      <h2 style="margin:4px 0;font-size:22px;font-weight:bold;">*** RETURN RECEIPT ***</h2>
      <p class="receipt-divider"><?php echo htmlspecialchars($receiptDivider); ?></p>
      <?php if (!empty($row['pin']) && $row['pin'] == 1): ?>
        <span class="pull-left">PIN: <?php echo htmlspecialchars($gen['kra_pin']); ?></span>
      <?php endif; ?>
      <span class="pull-left"><b>SERVED BY: <?php echo strtoupper(get_column('kp_users', 'username', "user_id='$user_id'")); ?></b></span>
      <span class="pull-right"><b>BILL No: <?php echo $sale_id; ?></b></span><br>
      <span class="pull-left"><b>SHIFT: <?php echo $shift; ?></b></span>
      <span class="pull-right"><b>TIME: <?php echo $time; ?></b></span>
      <p class="receipt-divider"><?php echo htmlspecialchars($receiptDivider); ?></p>
    </div>

    <div id="receipt-body">
      <table class="table table-bordered" id="deSo">
        <tfoot>
          <tr>
            <th colspan="5" class="text-center">
              <b style="font-size:22px;">Total: <?php echo number_format($amount, 2); ?></b>
            </th>
          </tr>
        </tfoot>
        <tbody>
          <?php
          $r1 = mysqli_query($connection,
            "SELECT SUM(qty) AS sqty, item_id, price, attr_id
             FROM kp_sale_items
             WHERE sale_id='$sale_id' AND hour=1
             GROUP BY item_id, attr_id"
          );
          while ($o = mysqli_fetch_assoc($r1)):
            $iname = get_column('kp_items', 'name', "item_id='{$o['item_id']}'");
            if (!empty($o['attr_id'])) {
              $iname .= ' (' . get_column('kp_items_attributes', 'name', "id='{$o['attr_id']}'") . ')';
            }
          ?>
          <tr>
            <td><?php echo $o['sqty']; ?></td>
            <td><?php echo $iname; ?></td>
            <td colspan="2"><?php echo number_format((float)$o['price'], 2); ?></td>
            <td><?php echo number_format($o['sqty'] * $o['price'], 2); ?></td>
          </tr>
          <?php endwhile; ?>

          <?php
          $r2 = mysqli_query($connection,
            "SELECT SUM(qty) AS sqty, item_id, price, attr_id
             FROM kp_sale_items
             WHERE sale_id='$sale_id' AND hour=2
             GROUP BY item_id, attr_id"
          );
          if (mysqli_num_rows($r2) > 0): ?>
          <tr>
            <td colspan="5"><b>HAPPY HOUR ORDER</b></td>
          </tr>
          <?php while ($o = mysqli_fetch_assoc($r2)):
              $iname = get_column('kp_items', 'name', "item_id='{$o['item_id']}'");
              if (!empty($o['attr_id'])) {
                $iname .= ' (' . get_column('kp_items_attributes', 'name', "id='{$o['attr_id']}'") . ')';
              }
          ?>
          <tr>
            <td><?php echo $o['sqty']; ?></td>
            <td><?php echo $iname; ?></td>
            <td colspan="2"><?php echo number_format((float)$o['price'], 2); ?></td>
            <td><?php echo number_format($o['sqty'] * $o['price'], 2); ?></td>
          </tr>
          <?php endwhile; endif; ?>
        </tbody>
      </table>
    </div>

    <div id="receipt-footer" style="margin-right:5px;">
      <p class="receipt-divider"><?php echo htmlspecialchars($receiptDivider); ?></p>
      <p class="text-center" style="margin:0;"><b>RETURN PROCESSED</b></p>
      <p class="text-center" style="margin:0;"><?php echo htmlspecialchars($row['thankyou_note'] ?? ''); ?></p>
      <p class="receipt-divider"><?php echo htmlspecialchars($receiptDivider); ?></p>
      <p class="text-center" style="margin:0;font-size:11px;">
        K-TOUCH POS Designed &amp; Distributed by K-TEN<br>
        +254716100335 | +254735100335
      </p>
      <p style="margin:0;"><?php echo str_repeat('*', $nos); ?></p>
    </div>
  </div>
</div>
<?php if ($auto_print): ?>
<script>
  window.addEventListener('load', function () {
    window.print();
  });
</script>
<?php endif; ?>
</body>
</html>
