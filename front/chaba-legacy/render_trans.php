<?php
session_start();
include('../../inc/connection.php');
//include('../../inc/gen_funcs.php');
include('../trans/funcs.php');

$receiptDivider = function_exists('kp_receipt_divider_text') ? kp_receipt_divider_text($connection) : str_repeat('*', 50);

$trans_id = (int)($_GET['trans_id'] ?? $_GET['id'] ?? $_SESSION['trans'] ?? 0);
$user_id = (int)($_GET['user_id'] ?? $_SESSION['user_id'] ?? 0);
if ($trans_id < 1) {
    exit('Invalid transfer');
}

$transferHeader = mysqli_query($connection, "SELECT * FROM kp_counter_trans_status WHERE trans_id='$trans_id' ORDER BY id DESC LIMIT 1");
$transferMeta = ($transferHeader && mysqli_num_rows($transferHeader) > 0) ? mysqli_fetch_assoc($transferHeader) : null;
$cntr_from = $transferMeta ? (int)$transferMeta['cntr_from'] : 0;
$cntr_to = $transferMeta ? (int)$transferMeta['cntr_to'] : 0;
$counter = user_counter($user_id);
$shift = get_column('kp_shift', 'shift', "1");

$fromLabel = $cntr_from > 0 ? get_counter($cntr_from) : 'UNKNOWN';
$toLabel = $cntr_to > 0 ? get_counter($cntr_to) : 'UNKNOWN';
$printTime = function_exists('totime') ? totime() : date('H:i:s');
?>
<!DOCTYPE html>
<html lang="en">
<head>
  <meta charset="UTF-8">
  <title>Transfer #<?php echo $trans_id; ?></title>
  <style>
    <?php echo file_get_contents(realpath(__DIR__ . '/../assets/css/thermal_print.css') ?: ''); ?>
    .receipt-divider { margin:0; line-height:1.1; white-space:nowrap; overflow:hidden; text-align:center; font-weight:700; letter-spacing:.06em; }
    body, #receipt, table, #receipt-header, #receipt-body, #receipt-footer {
      background: #fff !important;
    }
  </style>
</head>
<body>
<div id="receipt">
  <div id="bord">
    <div id="receipt-header" class="text-center">
      <p class="receipt-divider"><?php echo htmlspecialchars($receiptDivider); ?></p>
      <h1 style="margin:0;">TRANSFER</h1>
      <p class="receipt-divider"><?php echo htmlspecialchars($receiptDivider); ?></p>
      <h2 style="margin:0;">FROM: <?php echo htmlspecialchars($fromLabel); ?></h2>
      <h2 style="margin:0;">TO: <?php echo htmlspecialchars($toLabel); ?></h2>
      <h2 style="margin:0;">TRANSFER NO: <?php echo $trans_id; ?></h2>
      <h2 style="margin:0;">TRANSFER BY: <?php echo strtoupper(htmlspecialchars(get_user($user_id))); ?></h2>
      <h2 style="margin:0;">SHIFT: <?php echo htmlspecialchars($shift); ?></h2>
      <span><strong>PRINT TIME: <?php echo htmlspecialchars($printTime); ?></strong></span>
      <p class="receipt-divider"><?php echo htmlspecialchars($receiptDivider); ?></p>
    </div>
    <div id="receipt-body">
      <table>
        <thead>
          <tr>
            <th>Name</th>
            <th>Qty</th>
          </tr>
        </thead>
        <tbody>
          <?php
          $get_items = mysqli_query($connection, "SELECT * FROM kp_counter_trans_items WHERE trans_id='$trans_id'");
          while ($item = mysqli_fetch_assoc($get_items)) {
              echo '<tr><td>' . htmlspecialchars(get_item($item['item_id'])) . '</td><td>' . htmlspecialchars($item['qty']) . '</td></tr>';
          }
          ?>
        </tbody>
      </table>
    </div>
    <p class="receipt-divider"><?php echo htmlspecialchars($receiptDivider); ?></p>
    <p class="receipt-divider"><?php echo htmlspecialchars($receiptDivider); ?></p>
    <br>
  </div>
</div>
</body>
</html>
