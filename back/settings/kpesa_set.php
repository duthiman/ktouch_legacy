<?php
include("../../inc/connection.php");
include("../clude/funcs.php");

// ── Auto-migrate ──────────────────────────────────────────────────────────────
mysqli_query($connection, "
    CREATE TABLE IF NOT EXISTS kp_kpesa_settings (
        set_id       INT            NOT NULL DEFAULT 1,
        api_key      VARCHAR(255)   NOT NULL DEFAULT '',
        fetch_url    VARCHAR(255)   NOT NULL DEFAULT 'https://sawela.kpesa.co.ke/api/v1/deposits/payments',
        has_stk_push TINYINT(1)     NOT NULL DEFAULT 0,
        stk_url      VARCHAR(255)   NOT NULL DEFAULT '',
        is_active    TINYINT(1)     NOT NULL DEFAULT 1,
        updated_at   TIMESTAMP      NOT NULL DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
        PRIMARY KEY (set_id)
    ) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4
");
$kc = mysqli_query($connection, "SHOW COLUMNS FROM kp_settings_print LIKE 'kpesa_active'");
if ($kc && mysqli_num_rows($kc) === 0) {
    mysqli_query($connection, "ALTER TABLE kp_settings_print ADD COLUMN kpesa_active TINYINT(1) NOT NULL DEFAULT 1");
}
$kpesa_flag = (int)get_column('kp_settings_print', 'kpesa_active', "print_doc=1");
if ($kpesa_flag !== 2) {
    $kpesa_flag = 1;
}

// Seed default row
$chk = mysqli_query($connection, "SELECT set_id FROM kp_kpesa_settings WHERE set_id=1 LIMIT 1");
if (!$chk || mysqli_num_rows($chk) === 0) {
    mysqli_query($connection, "INSERT INTO kp_kpesa_settings (set_id) VALUES (1)");
}

$res = mysqli_query($connection, "SELECT * FROM kp_kpesa_settings WHERE set_id=1 LIMIT 1");
$row = mysqli_fetch_assoc($res) ?: ['api_key'=>'','fetch_url'=>'https://sawela.kpesa.co.ke/api/v1/deposits/payments','has_stk_push'=>0,'stk_url'=>''];
?>
<!-- begin panel -->
<div class="panel">
  <div class="panel-body">
    <div id="kpesa-results"></div>
    <h4><i class="fa fa-mobile-alt"></i> KPesa Payment Gateway</h4>
    <p class="text-muted" style="font-size:13px;">
        Configure the K-PESA in-house M-PESA gateway. Used to fetch deposit receipts and optionally push STK prompts to customers.
    </p>

    <div class="row">
      <div class="col-sm-7">
        <fieldset>
          <legend class="m-b-10">CONNECTION SETTINGS</legend>
          <div class="form-group row m-b-10">
            <label class="col-md-4" style="color:blue; padding-top:6px;">Integration Mode</label>
            <div class="col-md-8">
              <div class="radio radio-css">
                <input type="radio" name="kpesa_active" id="kpesa-active-on" value="1" <?= $kpesa_flag === 1 ? 'checked' : '' ?> />
                <label for="kpesa-active-on">KPesa active: use live M-Pesa and card collections in staff clearance</label>
              </div>
              <div class="radio radio-css">
                <input type="radio" name="kpesa_active" id="kpesa-active-off" value="2" <?= $kpesa_flag === 2 ? 'checked' : '' ?> />
                <label for="kpesa-active-off">KPesa inactive: cashier enters M-Pesa and card figures manually</label>
              </div>
            </div>
          </div>
          <div class="form-group row m-b-10">
            <label class="col-md-4" style="color:blue; padding-top:7px;">API Key (x-api-key)</label>
            <div class="col-md-8">
              <input type="text" id="kp_api_key" class="form-control"
                     value="<?= htmlspecialchars($row['api_key']) ?>"
                     placeholder="Enter KPesa x-api-key" autocomplete="off" />
            </div>
          </div>
          <div class="form-group row m-b-10">
            <label class="col-md-4" style="color:blue; padding-top:7px;">Fetch URL</label>
            <div class="col-md-8">
              <input type="text" id="kp_fetch_url" class="form-control"
                     value="<?= htmlspecialchars($row['fetch_url']) ?>"
                     placeholder="https://sawela.kpesa.co.ke/api/v1/deposits/payments" />
            </div>
          </div>
          <div class="form-group row m-b-10">
            <label class="col-md-4" style="color:blue; padding-top:6px;">STK Push</label>
            <div class="col-md-8">
              <div class="radio radio-css radio-inline">
                <input type="radio" name="kp_stk" id="kp_stk_yes" value="1"
                       <?= $row['has_stk_push'] ? 'checked' : '' ?>
                       onchange="toggleStkUrl(1)" />
                <label for="kp_stk_yes" style="color:blue;">Enabled</label>
              </div>
              <div class="radio radio-css radio-inline">
                <input type="radio" name="kp_stk" id="kp_stk_no" value="0"
                       <?= !$row['has_stk_push'] ? 'checked' : '' ?>
                       onchange="toggleStkUrl(0)" />
                <label for="kp_stk_no" style="color:blue;">Disabled</label>
              </div>
            </div>
          </div>
          <div class="form-group row m-b-10" id="kp_stk_url_row"
               style="<?= $row['has_stk_push'] ? '' : 'display:none;' ?>">
            <label class="col-md-4" style="color:blue; padding-top:7px;">STK Push URL</label>
            <div class="col-md-8">
              <input type="text" id="kp_stk_url" class="form-control"
                     value="<?= htmlspecialchars($row['stk_url']) ?>"
                     placeholder="https://sawela.kpesa.co.ke/api/v1/stk/push" />
            </div>
          </div>
        </fieldset>

        <!-- Test actions -->
        <fieldset class="m-t-15">
          <legend class="m-b-10">TEST CONNECTION</legend>
          <div class="row">
            <div class="col-sm-12 m-b-10">
              <button class="btn btn-info btn-sm" onclick="kpesaTestFetch();">
                <i class="fa fa-download"></i> Test Fetch Receipts
              </button>
              <button class="btn btn-warning btn-sm m-l-5" id="kp_stk_test_btn"
                      style="<?= $row['has_stk_push'] ? '' : 'display:none;' ?>"
                      onclick="kpesaTestStk();">
                <i class="fa fa-phone"></i> Test STK Push
              </button>
            </div>
            <div class="col-sm-6" id="kp_stk_phone_wrap"
                 style="<?= $row['has_stk_push'] ? '' : 'display:none;' ?>">
              <input type="text" id="kp_test_phone" class="form-control form-control-sm"
                     placeholder="254712345678" />
              <small class="text-muted">Phone for STK test (254...)</small>
            </div>
          </div>
        </fieldset>
      </div>

      <div class="col-sm-5">
        <fieldset>
          <legend class="m-b-10">FETCH RESULTS</legend>
          <div id="kpesa-fetch-results">
            <p class="text-muted" style="font-size:12px;">Click "Test Fetch Receipts" to preview recent KPesa transactions.</p>
          </div>
        </fieldset>
      </div>
    </div>

  </div>
  <div class="panel-footer text-right">
    <button type="button" class="btn btn-lime" onclick="kpesaSave();">
      <i class="fa fa-save"></i> Save Settings
    </button>
  </div>
</div>

<script type="text/javascript">
function toggleStkUrl(val) {
    $('#kp_stk_url_row, #kp_stk_phone_wrap, #kp_stk_test_btn').toggle(val == 1);
}

function kpesaSave() {
    $.ajax({
        url: '../settings/kpesa_act.php',
        type: 'POST',
        data: {
            action:       'save',
            kpesa_active: $('input[name="kpesa_active"]:checked').val(),
            api_key:      $('#kp_api_key').val().trim(),
            fetch_url:    $('#kp_fetch_url').val().trim(),
            has_stk_push: $('input[name="kp_stk"]:checked').val(),
            stk_url:      $('#kp_stk_url').val().trim()
        },
        dataType: 'html'
    })
    .done(function(data){ $('#kpesa-results').html(data); })
    .fail(function(){ $('#kpesa-results').html('<div class="alert alert-danger">Something went wrong.</div>'); });
}

function kpesaTestFetch() {
    var key = $('#kp_api_key').val().trim();
    var url = $('#kp_fetch_url').val().trim();
    if (!key || !url) {
        $('#kpesa-fetch-results').html('<div class="alert alert-warning">Enter API Key and Fetch URL first.</div>');
        return;
    }
    $('#kpesa-fetch-results').html('<p class="text-muted"><i class="fa fa-spinner fa-spin"></i> Fetching...</p>');
    $.ajax({
        url: '../settings/kpesa_act.php',
        type: 'POST',
        data: { action: 'test_fetch', api_key: key, fetch_url: url },
        dataType: 'html'
    })
    .done(function(data){ $('#kpesa-fetch-results').html(data); })
    .fail(function(){ $('#kpesa-fetch-results').html('<div class="alert alert-danger">Request failed.</div>'); });
}

function kpesaTestStk() {
    var key   = $('#kp_api_key').val().trim();
    var url   = $('#kp_stk_url').val().trim();
    var phone = $('#kp_test_phone').val().trim();
    if (!key || !url || !phone) {
        $('#kpesa-results').html('<div class="alert alert-warning">Enter API Key, STK URL and test phone number.</div>');
        return;
    }
    $('#kpesa-results').html('<p class="text-muted"><i class="fa fa-spinner fa-spin"></i> Sending STK push...</p>');
    $.ajax({
        url: '../settings/kpesa_act.php',
        type: 'POST',
        data: { action: 'test_stk', api_key: key, stk_url: url, phone: phone },
        dataType: 'html'
    })
    .done(function(data){ $('#kpesa-results').html(data); })
    .fail(function(){ $('#kpesa-results').html('<div class="alert alert-danger">Request failed.</div>'); });
}
</script>
