<?php
session_start();
include('../../../inc/connection.php');
include_once('../../../inc/gen_funcs.php');
include_once(__DIR__ . '/../API/ret_schema.php');
page_restrict();
kp_return_ensure_schema($connection);
?>
<!DOCTYPE html>
<html lang="en">
<head>
  <meta charset="utf-8">
  <meta name="viewport" content="width=device-width, initial-scale=1, shrink-to-fit=no">
  <meta http-equiv="x-ua-compatible" content="ie=edge">
  <title>Returns</title>
  <link rel="stylesheet" href="../../../assets/plugins/bootstrap/3.3.7/css/bootstrap.min.css">
  <link rel="stylesheet" href="returns.css?v=<?php echo filemtime(__DIR__ . '/returns.css'); ?>">
  <script src="../../../assets/plugins/jquery/jquery-3.2.1.min.js"></script>
  <script src="../../../assets/plugins/bootstrap/3.3.7/js/bootstrap.min.js"></script>
  <script src="../../../assets/plugins/Hullabaloo/js/hullabaloo.js"></script>
</head>
<body class="fixed-sn deep-orange-skin">
  <div class="desktop-shell">
    <header>
      <nav class="navbar navbar-fixed-top scrolling-navbar double-nav">
        <ul class="nav navbar-nav navbar-left">
          <li class="nav-item">
            <a href="../../dash/dash.php" style="font-family: 'comic sans ms'; font-size: 12PX; font-weight: bold; color: #02550E !important; padding-top:30%; border-radius: 100%; height: 70px; width:70px; background-color: #FFF;border: 5px solid #02550E;" class="btn btn-sm btn-success m-b-10">Home</a>
          </li>
        </ul>
        <ul class="nav navbar-nav navbar-right" style="margin-right: 10px; margin-top: 10px;">
          <li class="nav-item">
            <a href="../../log_out.php" style="font-family: 'comic sans ms'; font-size: 12PX; font-weight: bold; color: #02550E !important; padding-top:30%; border-radius: 100%; height: 80px; width:80px; background-color: #FFF; border: 5px solid #02550E;" class="btn btn-sm btn-success">Log Out</a>
          </li>
        </ul>
      </nav>
    </header>
    <div class="container returns-wrap">
      <div class="returns-panel">
        <div class="returns-head">
          <h2>Returns Approval</h2>
          <p id="returns_intro">Confirm or reject completed return requests for your counter.</p>
        </div>
        <div class="returns-body" id="returns_desktop_body"></div>
      </div>
    </div>
  </div>

  <div class="mobile-shell">
    <div class="mobile-app">
      <div class="mobile-topbar">
        <h1>Returns</h1>
        <p>Approve or reject raised returns</p>
        <div class="mobile-actions">
          <a href="../../dash/dash.php">Home</a>
          <a href="../../log_out.php">Log Out</a>
        </div>
      </div>
      <div class="mobile-card">
        <div class="mobile-card-head">Pending Return Actions</div>
        <div class="mobile-card-body" id="returns_mobile_body"></div>
      </div>
    </div>
  </div>

  <div id="modal" class="modal fade" tabindex="-1" role="dialog" aria-hidden="true">
    <div class="modal-dialog" id="load"></div>
  </div>

  <script src="returns.js?v=<?php echo filemtime(__DIR__ . '/returns.js'); ?>"></script>
</body>
</html>
