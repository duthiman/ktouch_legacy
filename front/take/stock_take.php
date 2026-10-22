<?php
session_start();
include('../../inc/connection.php');
include_once('../../inc/gen_funcs.php');
require_once __DIR__ . '/API/take_schema.php';
take_ensure_item_stock_take_system();

if (!isset($_SESSION['user_id'])) {
  header("Location:../log_out.php");
}
$user_id = $_SESSION['user_id'];
?>
<!DOCTYPE html>
<html lang="en">
<head>
  <title>Stock Take</title>
  <meta charset="utf-8">
  <meta name="viewport" content="width=device-width, initial-scale=1, shrink-to-fit=no">
  <link href="../assets/css/bootstrap.css" rel="stylesheet" />
  <link rel="stylesheet" href="../assets/css/style.css">
  <link rel="stylesheet" href="take_css.css">
  <script src="../assets/js/jquery.js"></script>
</head>
<body>
  <div class="take-toast-stack" id="take_toast_stack"></div>
  <div class="desktop-shell">
    <header>
      <nav class="navbar navbar-fixed-top scrolling-navbar double-nav">
        <ul class="nav navbar-nav navbar-right">
          <li class="nav-item">
            <a href="../log_out.php" style="font-family: 'comic sans ms'; font-size: 12PX; font-weight: bold; color: #02550E !important; padding-top:25%; margin-right: 20px; border-radius: 100%; height: 80px; width:80px; background-color: #FFF;border: 5px solid #02550E;" class="btn btn-sm btn-success m-b-10">Log-out</a>
          </li>
        </ul>
      </nav>
    </header>
    <div id="content" class="content" style="margin-top: 70px; margin-left:80px;">
      <div class="row">
        <div class="col-md-3" style="width:360px">
          <div class="panel">
            <div class="panel-heading" style="padding:1px;">
              <b class="panel-title" style="font-size:24px;"><span id="take_counter_name_desktop">Counter</span> COUNTER STOCK TAKE</b>
            </div>
            <div class="panel-body" style="width:325px">
              <div id="results"></div>
              <button type="button" id="confirm_take_desktop" name="count" class="btn btn-info pull-left open-take-confirm" style="font-family: 'comic sans ms'; font-size: 12PX; font-weight: bold; color: #02550E !important; border-radius: 10px; height: 40px; width:150px; background-color: #FFF;border: 3px solid #02550E;display:none;">Confirm Stock Take</button>
                <form class="form-horizontal" id="form" style="display:none;">
                  <div class="take-progress-strip">
                    <div class="take-progress-chip take-progress-remaining">
                      <strong>Remaining</strong>
                      <span id="take_remaining_count_desktop">0</span>
                    </div>
                    <div class="take-progress-chip take-progress-variance open-variance-items">
                      <strong>Variance Items</strong>
                      <span id="take_variance_count_desktop">0</span>
                    </div>
                  </div>
                  <div class="take-selected-item" id="selected_item_label_desktop">
                    <strong>Selected Item</strong>
                    <span>Loading items...</span>
                  </div>
                  <div class="take-variance-strip" id="take_variance_desktop">
                    <div class="take-variance-card">
                      <strong>Expected</strong>
                      <span class="take-variance-expected">0</span>
                    </div>
                    <div class="take-variance-card">
                      <strong>Entered</strong>
                      <span class="take-variance-entered">0</span>
                    </div>
                    <div class="take-variance-card">
                      <strong>Variance</strong>
                      <span class="take-variance-value">0</span>
                    </div>
                  </div>
                  <select name="item_id" id="item_id" class="form-control" style="width:268px;margin-bottom: 10px;"></select>
                  <label class="take-entry-label" id="take_entry_label_desktop">Counted Units</label>
                  <input type="hidden" name="qty" id="qty_value" value="" />
                  <div class="take-entry-row" style="width:324px;max-width:100%;">
                    <input type="text" name="take_entry" id="telNumber" class="form-control" style="width:100%;" inputmode="none" autocomplete="off" readonly autofocus/>
                    <button type="button" class="take-backspace-btn" onclick="return window.codexSharedNumpadBackspace(this);">&larr;</button>
                  </div>
                  <div class="take-entry-help" id="take_entry_help_desktop">Enter the counted units for this item.</div>
                  <div style="margin-top:20px;"><?php $numpad_decimal = true; include("../clude/numpad.php"); $numpad_decimal = false; ?></div>
                  <button type="button" name="count" class="btn btn-info pull-right" style="font-family: 'comic sans ms'; font-size: 12PX; font-weight: bold; color: #02550E !important; border-radius: 10px; height: 50px; width:70px; background-color: #FFF;border: 3px solid #02550E; margin-right: 30px;" id="take">Submit </button>
                </form>
            </div>
          </div>
        </div>
        <div class="col-md-6" style="overflow-y:scroll; height:550px;">
          <div class="panel panel-primary">
            <div class="panel-body" id="list"></div>
          </div>
        </div>
      </div>
    </div>
  </div>

  <div class="mobile-shell">
    <div class="take-mobile-shell">
      <div class="take-topbar">
        <div class="take-brand">
          <div>
            <strong>Stock Take</strong>
            <span id="take_counter_name_mobile">Counter</span>
          </div>
          <div class="take-badge">
            <strong>Shift</strong>
            <span id="take_shift_mobile">Loading</span>
          </div>
        </div>
        <div class="take-top-actions">
          <a href="stock_take.php" class="take-top-btn">Refresh</a>
          <a href="../log_out.php" class="take-top-btn">Log Out</a>
        </div>
      </div>

      <div class="take-card"> 
        <div id="results_mobile"></div>
        <div id="take_form_mobile_wrap">
          <div class="take-empty-card" id="take_mobile_empty" style="display:none;">No remaining items to add. You can confirm the stock take below.</div>
          <form class="take-form-shell" id="form_mobile" style="display:none;">
            <div class="take-progress-strip">
              <div class="take-progress-chip take-progress-remaining">
                <strong>Remaining</strong>
                <span id="take_remaining_count_mobile">0</span>
              </div>
              <div class="take-progress-chip take-progress-variance open-variance-items">
                <strong>Variance Items</strong>
                <span id="take_variance_count_mobile">0</span>
              </div>
            </div>
            <div class="take-selected-item" id="selected_item_label_mobile">
              <strong>Selected Item</strong>
              <span>Loading items...</span>
            </div>
            <div class="take-variance-strip" id="take_variance_mobile">
              <div class="take-variance-card">
                <strong>Expected</strong>
                <span class="take-variance-expected">0</span>
              </div>
              <div class="take-variance-card">
                <strong>Entered</strong>
                <span class="take-variance-entered">0</span>
              </div>
              <div class="take-variance-card">
                <strong>Variance</strong>
                <span class="take-variance-value">0</span>
              </div>
            </div>
            <select name="item_id" id="item_id_mobile" class="form-control"></select>
            <label class="take-entry-label" id="take_entry_label_mobile">Counted Units</label>
            <input type="hidden" name="qty" id="qty_value_mobile" value="" />
            <div class="take-entry-row">
              <input type="text" name="take_entry" id="telNumber_mobile" class="form-control" inputmode="none" autocomplete="off" readonly autofocus/>
              <button type="button" class="take-backspace-btn" onclick="return window.codexSharedNumpadBackspace(this);">&larr;</button>
            </div>
            <div class="take-entry-help" id="take_entry_help_mobile">Enter the counted units for this item.</div>
            <div style="margin-top:8px;"><?php $numpad_decimal = true; include("../clude/numpad.php"); $numpad_decimal = false; ?></div>
            <div class="take-action-row">
              <button type="button" name="count" class="take-action-btn take-submit-btn" id="take_mobile" style="grid-column: 1 / -1;">Add To List</button>
            </div>
          </form>
        </div>
      </div>

      <div class="take-card take-live-panel">
        <p class="take-section-title">Counted Items</p>
        <div id="list_mobile"></div>
      </div>
      <div class="take-card take-footer-card">
        <button type="button" class="take-action-btn take-confirm-btn open-take-confirm" style="width:100%;">Confirm Stock Take</button>
      </div>
    </div>
  </div>
  <div id="take_confirm_modal" class="take-modal">
    <div id="take_confirm_load"></div>
  </div>
  <div id="take_variance_modal" class="take-modal">
    <div id="take_variance_load"></div>
  </div>
  <div id="take_weight_modal" class="take-weight-modal">
    <div class="take-weight-box">
      <div class="take-weight-head">
        <strong>Weight Stock Take</strong>
        <span id="take_weight_item_name">Selected Item</span>
      </div>
      <div class="take-weight-body">
        <div id="take_weight_results"></div>
        <label for="take_weight_input">Current Bottle Weight</label>
        <input type="number" step="0.01" min="0" id="take_weight_input" class="form-control" inputmode="decimal" autocomplete="off" />
        <div class="take-weight-note">Enter the current bottle weight. The system will convert it to available tots automatically.</div>
        <div class="take-weight-actions">
          <button type="button" class="take-weight-btn take-weight-cancel" id="take_weight_cancel">Cancel</button>
          <button type="button" class="take-weight-btn take-weight-save" id="take_weight_confirm">Use Weight</button>
        </div>
      </div>
    </div>
  </div>

  <script src="take.js"></script>
</body>
</html>
