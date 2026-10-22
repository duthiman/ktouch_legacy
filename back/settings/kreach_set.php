<?php
include("../../inc/connection.php");
include("../clude/funcs.php");

$kreach_defaults = [
    'base_url'    => 'https://www.kreach.co.ke',
    'send_ep'     => '/api/v1/sms/send',
    'bulk_ep'     => '/api/v1/sms/send/bulk',
    'account_ep'  => '/api/v1/account',
    'balance_ep'  => '/api/v1/account/balance',
];

// ── Auto-migrate ──────────────────────────────────────────────────────────────
mysqli_query($connection, "
    CREATE TABLE IF NOT EXISTS kp_sms_settings (
        set_id                       INT            NOT NULL DEFAULT 1,
        sms_provider                 VARCHAR(30)    NOT NULL DEFAULT 'kreach',
        sms_api_key                  VARCHAR(255)   NOT NULL DEFAULT '',
        sms_kreach_base_url          VARCHAR(255)   NOT NULL DEFAULT '',
        sms_kreach_send_endpoint     VARCHAR(120)   NOT NULL DEFAULT '/api/v1/sms/send',
        sms_kreach_bulk_endpoint     VARCHAR(120)   NOT NULL DEFAULT '/api/v1/sms/send/bulk',
        sms_kreach_account_endpoint  VARCHAR(120)   NOT NULL DEFAULT '/api/v1/account',
        sms_kreach_balance_endpoint  VARCHAR(120)   NOT NULL DEFAULT '/api/v1/account/balance',
        sms_sender_id                VARCHAR(30)    NOT NULL DEFAULT '',
        last_balance                 VARCHAR(50)    NOT NULL DEFAULT '',
        last_balance_checked_at      TIMESTAMP          NULL DEFAULT NULL,
        updated_at                   TIMESTAMP      NOT NULL DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
        PRIMARY KEY (set_id)
    ) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4
");

// Seed default row
$chk = mysqli_query($connection, "SELECT set_id FROM kp_sms_settings WHERE set_id=1 LIMIT 1");
if (!$chk || mysqli_num_rows($chk) === 0) {
    mysqli_query($connection, "INSERT INTO kp_sms_settings (set_id, sms_provider) VALUES (1,'kreach')");
}

$res = mysqli_query($connection, "SELECT * FROM kp_sms_settings WHERE set_id=1 LIMIT 1");
$row = mysqli_fetch_assoc($res) ?: [
    'sms_api_key'                 => '',
    'sms_kreach_base_url'         => $kreach_defaults['base_url'],
    'sms_kreach_send_endpoint'    => $kreach_defaults['send_ep'],
    'sms_kreach_bulk_endpoint'    => $kreach_defaults['bulk_ep'],
    'sms_kreach_account_endpoint' => $kreach_defaults['account_ep'],
    'sms_kreach_balance_endpoint' => $kreach_defaults['balance_ep'],
    'sms_sender_id'               => '',
    'last_balance'                => '',
    'last_balance_checked_at'     => null,
];

if (trim($row['sms_kreach_base_url']) === '') {
    $row['sms_kreach_base_url'] = $kreach_defaults['base_url'];
}
if (trim($row['sms_kreach_send_endpoint']) === '') {
    $row['sms_kreach_send_endpoint'] = $kreach_defaults['send_ep'];
}
if (trim($row['sms_kreach_bulk_endpoint']) === '') {
    $row['sms_kreach_bulk_endpoint'] = $kreach_defaults['bulk_ep'];
}
if (trim($row['sms_kreach_account_endpoint']) === '') {
    $row['sms_kreach_account_endpoint'] = $kreach_defaults['account_ep'];
}
if (trim($row['sms_kreach_balance_endpoint']) === '') {
    $row['sms_kreach_balance_endpoint'] = $kreach_defaults['balance_ep'];
}
?>
<!-- begin panel -->
<div class="panel">
  <div class="panel-body">
    <div id="kreach-results"></div>
    <h4><i class="fa fa-sms"></i> KReach SMS Settings</h4>
    <p class="text-muted" style="font-size:13px;">
        Add the K-REACH <strong>x-api-key</strong> only. The system will use the standard K-REACH API endpoint structure automatically.
    </p>

    <div class="row">
      <!-- Left: settings form -->
      <div class="col-sm-6">
        <fieldset>
          <legend class="m-b-10">API ACCESS</legend>
          <div class="form-group row m-b-10">
            <label class="col-md-5" style="color:blue; padding-top:7px;">API Key (x-api-key)</label>
            <div class="col-md-7">
              <input type="text" id="kr_api_key" class="form-control"
                     value="<?= htmlspecialchars($row['sms_api_key']) ?>"
                     placeholder="Enter KReach x-api-key" autocomplete="off" />
            </div>
          </div>
          <div class="alert alert-info m-b-10" style="font-size:12px; line-height:1.6;">
            <strong>Auto-configured by system</strong><br>
            Base URL: <code><?= htmlspecialchars($row['sms_kreach_base_url']) ?></code><br>
            Send SMS: <code><?= htmlspecialchars($row['sms_kreach_send_endpoint']) ?></code><br>
            Bulk SMS: <code><?= htmlspecialchars($row['sms_kreach_bulk_endpoint']) ?></code><br>
            Account: <code><?= htmlspecialchars($row['sms_kreach_account_endpoint']) ?></code><br>
            Balance: <code><?= htmlspecialchars($row['sms_kreach_balance_endpoint']) ?></code>
          </div>
        </fieldset>
      </div>

      <!-- Right: endpoint guide + test panel -->
      <div class="col-sm-6">
        <fieldset class="m-b-15">
          <legend class="m-b-10">ENDPOINT MAP</legend>
          <div class="table-responsive">
            <table class="table table-bordered table-sm" style="font-size:12px; margin-bottom:0;">
              <thead>
                <tr>
                  <th>Use</th>
                  <th>Endpoint</th>
                </tr>
              </thead>
              <tbody>
                <tr>
                  <td>Send SMS</td>
                  <td><code><?= htmlspecialchars($row['sms_kreach_send_endpoint']) ?></code></td>
                </tr>
                <tr>
                  <td>Bulk SMS</td>
                  <td><code><?= htmlspecialchars($row['sms_kreach_bulk_endpoint']) ?></code></td>
                </tr>
                <tr>
                  <td>Account Details</td>
                  <td><code><?= htmlspecialchars($row['sms_kreach_account_endpoint']) ?></code></td>
                </tr>
                <tr>
                  <td>Account Balance</td>
                  <td><code><?= htmlspecialchars($row['sms_kreach_balance_endpoint']) ?></code></td>
                </tr>
              </tbody>
            </table>
          </div>
          <small class="text-muted" style="display:block; margin-top:8px;">
            These are fixed from the K-REACH API collection, so normal users only need the access key.
          </small>
        </fieldset>

        <fieldset>
          <legend class="m-b-10">TEST CONNECTION</legend>

          <?php if ($row['last_balance']): ?>
          <div class="alert alert-info m-b-10" style="font-size:12px;">
            <i class="fa fa-wallet"></i>
            <strong>Last Balance:</strong> <?= htmlspecialchars($row['last_balance']) ?>
            <?php if ($row['last_balance_checked_at']): ?>
            <br><small class="text-muted">Checked: <?= $row['last_balance_checked_at'] ?></small>
            <?php endif; ?>
          </div>
          <?php endif; ?>

          <div class="m-b-10">
            <button class="btn btn-info btn-sm" onclick="kreachTestAccount();">
              <i class="fa fa-user-check"></i> Test Account
            </button>
            <button class="btn btn-primary btn-sm m-l-5" onclick="kreachTestBalance();">
              <i class="fa fa-wallet"></i> Check Balance
            </button>
          </div>

          <hr style="margin: 10px 0;">

          <div class="form-group m-b-8">
            <label style="color:#555; font-size:13px;">Test SMS — Phone (254...)</label>
            <input type="text" id="kr_test_phone" class="form-control form-control-sm"
                   placeholder="e.g. 254712345678" />
          </div>
          <div class="form-group m-b-8">
            <label style="color:#555; font-size:13px;">Message</label>
            <textarea id="kr_test_message" class="form-control form-control-sm" rows="2"
                      placeholder="Test message from K-Touch POS"></textarea>
          </div>
          <button class="btn btn-warning btn-sm" onclick="kreachTestSend();">
            <i class="fa fa-paper-plane"></i> Send Test SMS
          </button>

          <div id="kreach-test-results" class="m-t-10"></div>
        </fieldset>
      </div>
    </div>

  </div>
  <div class="panel-footer text-right">
    <button type="button" class="btn btn-lime" onclick="kreachSave();">
      <i class="fa fa-save"></i> Save Settings
    </button>
  </div>
</div>

<script type="text/javascript">
function kreachPayload() {
    return {
        api_key: $('#kr_api_key').val().trim()
    };
}

function kreachSave() {
    var d = kreachPayload(); d.action = 'save';
    $.ajax({ url: '../settings/kreach_act.php', type: 'POST', data: d, dataType: 'html' })
    .done(function(r){ $('#kreach-results').html(r); })
    .fail(function(){ $('#kreach-results').html('<div class="alert alert-danger">Something went wrong.</div>'); });
}

function kreachTestAccount() {
    var d = kreachPayload(); d.action = 'test_account';
    $('#kreach-test-results').html('<i class="fa fa-spinner fa-spin"></i> Connecting...');
    $.ajax({ url: '../settings/kreach_act.php', type: 'POST', data: d, dataType: 'html' })
    .done(function(r){ $('#kreach-test-results').html(r); })
    .fail(function(){ $('#kreach-test-results').html('<div class="alert alert-danger">Request failed.</div>'); });
}

function kreachTestBalance() {
    var d = kreachPayload(); d.action = 'test_balance';
    $('#kreach-test-results').html('<i class="fa fa-spinner fa-spin"></i> Checking balance...');
    $.ajax({ url: '../settings/kreach_act.php', type: 'POST', data: d, dataType: 'html' })
    .done(function(r){ $('#kreach-test-results').html(r); })
    .fail(function(){ $('#kreach-test-results').html('<div class="alert alert-danger">Request failed.</div>'); });
}

function kreachTestSend() {
    var d = kreachPayload();
    d.action  = 'test_send';
    d.phone   = $('#kr_test_phone').val().trim();
    d.message = $('#kr_test_message').val().trim();
    if (!d.phone || !d.message) {
        $('#kreach-test-results').html('<div class="alert alert-warning">Enter phone and message.</div>');
        return;
    }
    $('#kreach-test-results').html('<i class="fa fa-spinner fa-spin"></i> Sending...');
    $.ajax({ url: '../settings/kreach_act.php', type: 'POST', data: d, dataType: 'html' })
    .done(function(r){ $('#kreach-test-results').html(r); })
    .fail(function(){ $('#kreach-test-results').html('<div class="alert alert-danger">Request failed.</div>'); });
}
</script>
