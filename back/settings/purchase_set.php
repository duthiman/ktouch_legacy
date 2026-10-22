<?php
session_start();
include('../../inc/connection.php');
include('../../front/kug/funcs.php');
include_once('../../front/gwitia/API/gwit_schema.php');

$settings = kp_purchase_settings($connection);
$gwitAutoRequest = function_exists('gwit_auto_request_enabled') ? gwit_auto_request_enabled($connection) : true;
$message = '';

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $frontPurchaseEnabled = isset($_POST['front_purchase_enabled']) ? 1 : 0;
    $frontAutoStockEnabled = isset($_POST['front_auto_stock_enabled']) ? 1 : 0;
    $gwitAutoRequestEnabled = isset($_POST['gwit_auto_request_enabled']) ? 1 : 0;
    mysqli_query($connection, "UPDATE kp_settings_purchase SET front_purchase_enabled='$frontPurchaseEnabled', front_auto_stock_enabled='$frontAutoStockEnabled' WHERE settings_id=1") or die(mysqli_error($connection));
    mysqli_query($connection, "CREATE TABLE IF NOT EXISTS kp_settings_gwitia (settings_id INT NOT NULL PRIMARY KEY, auto_request_enabled TINYINT(1) NOT NULL DEFAULT 1, created_at TIMESTAMP NOT NULL DEFAULT CURRENT_TIMESTAMP) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4");
    mysqli_query($connection, "INSERT INTO kp_settings_gwitia(settings_id, auto_request_enabled) VALUES(1,'$gwitAutoRequestEnabled') ON DUPLICATE KEY UPDATE auto_request_enabled=VALUES(auto_request_enabled)");
    $settings = kp_purchase_settings($connection);
    $gwitAutoRequest = $gwitAutoRequestEnabled === 1;
    $message = 'Purchase settings saved.';
}
?>
<!DOCTYPE html>
<html lang="en">
<head>
  <meta charset="utf-8">
  <meta name="viewport" content="width=device-width, initial-scale=1">
  <title>Purchase Settings</title>
  <link rel="stylesheet" href="../../assets/plugins/bootstrap/3.3.7/css/bootstrap.min.css">
  <style>
    body { margin:0; min-height:100vh; background:linear-gradient(180deg,#f8f1df 0%,#efdcb8 100%); color:#2d2217; }
    .settings-shell { max-width:960px; margin:0 auto; padding:18px 12px 30px; }
    .settings-head { padding:18px; border-radius:24px; background:linear-gradient(135deg,#7e2014 0%,#b94828 50%,#e7a33a 100%); color:#fff8ea; box-shadow:0 18px 34px rgba(74,37,11,.18); }
    .settings-head h1 { margin:0; font-size:28px; font-weight:900; text-transform:uppercase; }
    .settings-head p { margin:6px 0 0; font-size:13px; font-weight:700; }
    .settings-tabs { display:flex; gap:10px; flex-wrap:wrap; margin-top:12px; }
    .settings-tabs a { min-height:42px; padding:0 16px; border-radius:12px; display:inline-flex; align-items:center; justify-content:center; text-decoration:none !important; font-size:12px; font-weight:900; text-transform:uppercase; }
    .settings-tab { background:rgba(255,255,255,.14); color:#fff !important; }
    .settings-tab.active { background:#fff; color:#7e2014 !important; }
    .settings-card { margin-top:16px; background:rgba(255,255,255,.95); border-radius:22px; box-shadow:0 16px 28px rgba(0,0,0,.08); padding:18px; }
    .settings-block { padding:16px; border-radius:18px; background:#faf5ea; margin-bottom:14px; }
    .settings-block h3 { margin:0 0 6px; font-size:18px; font-weight:900; color:#3b2818; }
    .settings-block p { margin:0 0 12px; font-size:13px; font-weight:700; color:#6d5130; }
    .switch-row { display:flex; align-items:flex-start; gap:12px; padding:12px 0; border-top:1px solid rgba(126,58,32,.08); }
    .switch-row:first-of-type { border-top:0; padding-top:0; }
    .switch-row input { transform:scale(1.4); margin-top:4px; }
    .switch-copy strong, .switch-copy span { display:block; }
    .switch-copy strong { font-size:14px; font-weight:900; color:#2d2217; }
    .switch-copy span { margin-top:4px; font-size:12px; font-weight:700; color:#6f5434; }
    .settings-actions { display:flex; justify-content:flex-end; gap:10px; flex-wrap:wrap; margin-top:16px; }
    .settings-actions a, .settings-actions button { min-height:46px; padding:0 18px; border-radius:14px; border:0; text-decoration:none !important; font-size:13px; font-weight:900; text-transform:uppercase; display:inline-flex; align-items:center; justify-content:center; }
    .btn-back { background:#ead6d6; color:#7e2014 !important; }
    .btn-save { background:linear-gradient(135deg,#355f16 0%,#58a03f 100%); color:#fff; }
  </style>
</head>
<body>
  <div class="settings-shell">
    <div class="settings-head">
      <h1>Settings</h1>
      <p>Configure how front-raised purchases behave before they reach backend approval.</p>
      <div class="settings-tabs">
        <a href="printer_set.php" class="settings-tab">Print</a>
        <a href="purchase_set.php" class="settings-tab active">Purchase</a>
        <a href="return_set.php" class="settings-tab">Returns</a>
      </div>
    </div>

    <?php if ($message !== '') { ?>
      <div class="alert alert-success" style="margin-top:14px; border-radius:16px; font-weight:800;"><?php echo htmlspecialchars($message); ?></div>
    <?php } ?>

    <form method="post" class="settings-card">
      <div class="settings-block">
        <h3>Front Purchase Module</h3>
        <p>Control whether front requisitions are handled automatically or follow the normal approval and receiving procedure.</p>

        <label class="switch-row">
          <input type="checkbox" name="front_purchase_enabled" value="1"<?php echo (int)$settings['front_purchase_enabled'] === 1 ? ' checked' : ''; ?>>
          <span class="switch-copy">
            <strong>Enable Front Purchase</strong>
            <span>If turned off, the buy stock screen in the front will not open, and users will be sent back to stock dashboard.</span>
          </span>
        </label>

        <label class="switch-row">
          <input type="checkbox" name="gwit_auto_request_enabled" value="1"<?php echo $gwitAutoRequest ? ' checked' : ''; ?>>
          <span class="switch-copy">
            <strong>Gwitía Automatic Request</strong>
            <span>Controls Gwitía only. When enabled, Gwitía requests are automatically issued, received, and added to the requesting counter stock.</span>
          </span>
        </label>

        <label class="switch-row">
          <input type="checkbox" name="front_auto_stock_enabled" value="1"<?php echo (int)$settings['front_auto_stock_enabled'] === 1 ? ' checked' : ''; ?>>
          <span class="switch-copy">
            <strong>Automatic Request</strong>
            <span>True: the request is automatically approved, received, tracked in <code>kp_stock_track</code>, and added to stock. False: use the procedural request workflow.</span>
          </span>
        </label>
      </div>

      <div class="settings-actions">
        <a href="../dash/" class="btn-back">Back</a>
        <button type="submit" class="btn-save">Save Purchase Settings</button>
      </div>
    </form>
  </div>
</body>
</html>
