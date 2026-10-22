<?php
session_start();
include('../../inc/connection.php');
include('../../inc/gen_funcs.php');
include_once('../gwitia/API/gwit_schema.php');
require_once(__DIR__ . '/render_css.php');

if (function_exists('kp_ensure_gwitia_schema')) {
    kp_ensure_gwitia_schema($connection);
}

$receiptDivider = function_exists('kp_receipt_divider_text') ? kp_receipt_divider_text($connection) : str_repeat('*', 50);

$req_id = (int)($_GET['id'] ?? $_GET['req_id'] ?? ($_SESSION['req'] ?? 0));
$auto_print = (int)($_GET['auto_print'] ?? 0) === 1;
$return_to = trim((string)($_GET['return_to'] ?? ''));
$user_id = (int)($_GET['user_id'] ?? $_SESSION['user_id'] ?? 0);
if ($req_id < 1) {
    exit('Invalid requisition');
}

$counter = user_counter($user_id);
$shift = get_column('kp_shift', 'shift', "1");
$reqHeader = mysqli_query($connection, "SELECT * FROM kp_counter_req WHERE req_id='$req_id' ORDER BY id DESC LIMIT 1");
$reqMeta = ($reqHeader && mysqli_num_rows($reqHeader) > 0) ? mysqli_fetch_assoc($reqHeader) : null;
$issueStamp = $reqMeta ? trim((string)($reqMeta['issue_created_at'] ?? '')) : '';
$receiveStamp = $reqMeta ? trim((string)($reqMeta['rec_created_at'] ?? '')) : '';
$issueStatus = $issueStamp !== '' ? 'ISSUED OUT' : 'PENDING ISSUE';
$receiveStatus = $receiveStamp !== '' ? 'RECEIVED IN' : 'PENDING RECEIVE';
$print_template = kp_render_print_template_id();
?>
<!DOCTYPE html>
<html lang="en">
<head>
  <meta charset="UTF-8">
  <title>Requisition #<?php echo $req_id; ?></title>
  <style>
    <?php echo kp_render_print_template_css($print_template); ?>
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
      <h1 style="margin:0;"><?php echo get_counter($counter); ?></h1>
      <p class="receipt-divider"><?php echo htmlspecialchars($receiptDivider); ?></p>
      <h2 style="margin:0;">REQUESTED ITEMS</h2>
      <p class="receipt-divider"><?php echo htmlspecialchars($receiptDivider); ?></p>
      <h2 style="margin:0;">REQ BY: <?php echo strtoupper(get_user($user_id)); ?></h2>
      <h2 style="margin:0;">REQ NO: <?php echo $req_id; ?></h2>
      <h2 style="margin:0;"><strong>SHIFT: <?php echo $shift; ?></strong></h2>
      <h2 style="margin:0;"><?php echo htmlspecialchars($issueStatus); ?></h2>
      <h2 style="margin:0;"><?php echo htmlspecialchars($receiveStatus); ?></h2>
      <span><strong>PRINT TIME: <?php echo totime(); ?></strong></span>
      <p class="receipt-divider"><?php echo htmlspecialchars($receiptDivider); ?></p>
    </div>
    <div id="receipt-body">
      <table>
        <thead>
          <tr>
            <th>Name</th><th>Qty</th>
          </tr>
        </thead>
        <tbody>
          <?php
          $get_items = mysqli_query($connection, "SELECT * FROM kp_counter_req_items WHERE req_id='$req_id'");
          while ($s = mysqli_fetch_assoc($get_items)) {
              echo '<tr><td>' . htmlspecialchars(get_item($s['item_id'])) . '</td><td>' . htmlspecialchars($s['req_qty']) . '</td></tr>';
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
<?php if ($auto_print): ?>
<script>
  window.addEventListener('load', function () {
    setTimeout(function () {
      window.print();
    }, 250);
  });
  <?php if ($return_to !== ''): ?>
  window.addEventListener('afterprint', function () {
    window.location.replace(<?php echo json_encode($return_to); ?>);
  });
  <?php endif; ?>
</script>
<?php endif; ?>
</body>
</html>
