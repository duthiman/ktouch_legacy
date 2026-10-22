<?php
session_start();
include('../../inc/connection.php');
include('../../front/ret/funcs.php');

kp_return_ensure_schema($connection);
$settings = kp_return_settings($connection);
$message = '';

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $approval_mode = isset($_POST['approval_mode']) && (int)$_POST['approval_mode'] === 2 ? 2 : 1;
    mysqli_query($connection, "UPDATE kp_settings_return SET approval_mode='$approval_mode' WHERE settings_id=1") or die(mysqli_error($connection));
    $settings = kp_return_settings($connection);
    $message = 'Return settings saved.';
}
?>
<!DOCTYPE html>
<html lang="en">
<head>
  <meta charset="utf-8">
  <meta name="viewport" content="width=device-width, initial-scale=1">
  <title>Return Settings</title>
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
    .mode-card { border:1px solid rgba(126,58,32,.12); border-radius:16px; padding:14px; margin-bottom:12px; background:#fff; }
    .mode-card input { margin-right:10px; }
    .mode-card strong, .mode-card span { display:block; }
    .mode-card strong { font-size:14px; font-weight:900; color:#2d2217; }
    .mode-card span { margin-top:6px; font-size:12px; font-weight:700; color:#6f5434; }
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
      <p>Control whether returns follow the current single approval flow or require cashier approval before counter approval.</p>
      <div class="settings-tabs">
        <a href="printer_set.php" class="settings-tab">Print</a>
        <a href="purchase_set.php" class="settings-tab">Purchase</a>
        <a href="return_set.php" class="settings-tab active">Returns</a>
      </div>
    </div>

    <?php if ($message !== '') { ?>
      <div class="alert alert-success" style="margin-top:14px; border-radius:16px; font-weight:800;"><?php echo htmlspecialchars($message); ?></div>
    <?php } ?>

    <form method="post" class="settings-card">
      <div class="settings-block">
        <h3>Return Approval Flow</h3>
        <p>Use the current simple flow, or make the cashier approve first before the same counter restores stock.</p>

        <label class="mode-card">
          <input type="radio" name="approval_mode" value="1"<?php echo (int)$settings['approval_mode'] === 1 ? ' checked' : ''; ?>>
          <strong>Option 1: Current Flow</strong>
          <span>Returns go to the counter approval stage directly. Stock is restored only when the counter confirms.</span>
        </label>

        <label class="mode-card">
          <input type="radio" name="approval_mode" value="2"<?php echo (int)$settings['approval_mode'] === 2 ? ' checked' : ''; ?>>
          <strong>Option 2: Cashier Then Counter</strong>
          <span>Cashier approves first. After that, the same stock counter gives the final approval and only then stock is returned.</span>
        </label>
      </div>

      <div class="settings-actions">
        <a href="../dash/" class="btn-back">Back</a>
        <button type="submit" class="btn-save">Save Return Settings</button>
      </div>
    </form>
  </div>
</body>
</html>
