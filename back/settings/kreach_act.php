<?php
include("../../inc/connection.php");
include("../clude/funcs.php");

$action = trim($_POST['action'] ?? 'save');

function kreach_defaults() {
    return [
        'base_url'    => 'https://www.kreach.co.ke',
        'send_ep'     => '/api/v1/sms/send',
        'bulk_ep'     => '/api/v1/sms/send/bulk',
        'account_ep'  => '/api/v1/account',
        'balance_ep'  => '/api/v1/account/balance',
    ];
}

// ── HTTP helper ───────────────────────────────────────────────────────────────
// Force HTTPS — K-REACH redirects http→https, cURL converts POST→GET on 302
function kreach_http($url, $method = 'GET', array $headers = [], $body = null) {
    if (stripos($url, 'http://') === 0) {
        $url = 'https://' . substr($url, 7);
    }
    if (function_exists('curl_init')) {
        $ch = curl_init();
        curl_setopt_array($ch, [
            CURLOPT_URL            => $url,
            CURLOPT_RETURNTRANSFER => true,
            CURLOPT_SSL_VERIFYPEER => false,
            CURLOPT_SSL_VERIFYHOST => false,
            CURLOPT_FOLLOWLOCATION => false,
            CURLOPT_TIMEOUT        => 15,
            CURLOPT_HTTPHEADER     => $headers,
            CURLOPT_CUSTOMREQUEST  => $method,
        ]);
        if (in_array($method, ['POST','PUT']) && $body) {
            curl_setopt($ch, CURLOPT_POSTFIELDS, $body);
        }
        $resp = curl_exec($ch);
        $err  = curl_error($ch);
        $code = curl_getinfo($ch, CURLINFO_HTTP_CODE);
        curl_close($ch);
        return ['code' => $code, 'body' => $resp ?: '', 'error' => $err];
    }
    $opts = ['http' => ['method'=>$method,'timeout'=>15,'ignore_errors'=>true,
        'header' => implode("\r\n", $headers), 'content' => $body]];
    $resp = @file_get_contents($url, false, stream_context_create($opts));
    return ['code' => 0, 'body' => $resp ?: '', 'error' => ''];
}

function kreach_build_url($base, $endpoint) {
    return rtrim($base, '/') . '/' . ltrim($endpoint, '/');
}

function kreach_normalize_phone($phone) {
    $phone = preg_replace('/\D/', '', (string)$phone);
    if ($phone === '') {
        return '';
    }
    if (strpos($phone, '254') === 0) {
        return $phone;
    }
    if (strpos($phone, '0') === 0 && strlen($phone) >= 10) {
        return '254' . substr($phone, 1);
    }
    if (strpos($phone, '7') === 0 && strlen($phone) === 9) {
        return '254' . $phone;
    }
    if (strpos($phone, '1') === 0 && strlen($phone) === 9) {
        return '254' . $phone;
    }
    return $phone;
}

// ── SAVE ──────────────────────────────────────────────────────────────────────
if ($action === 'save') {
    $defaults   = kreach_defaults();
    $api_key    = mysqli_real_escape_string($connection, trim($_POST['api_key']    ?? ''));
    $base_url   = mysqli_real_escape_string($connection, $defaults['base_url']);
    $send_ep    = mysqli_real_escape_string($connection, $defaults['send_ep']);
    $bulk_ep    = mysqli_real_escape_string($connection, $defaults['bulk_ep']);
    $account_ep = mysqli_real_escape_string($connection, $defaults['account_ep']);
    $balance_ep = mysqli_real_escape_string($connection, $defaults['balance_ep']);

    $chk = mysqli_query($connection, "SELECT set_id FROM kp_sms_settings WHERE set_id=1 LIMIT 1");
    if ($chk && mysqli_num_rows($chk) > 0) {
        $ok = mysqli_query($connection,
            "UPDATE kp_sms_settings SET
                sms_api_key='$api_key',
                sms_kreach_base_url='$base_url',
                sms_kreach_send_endpoint='$send_ep',
                sms_kreach_bulk_endpoint='$bulk_ep',
                sms_kreach_account_endpoint='$account_ep',
                sms_kreach_balance_endpoint='$balance_ep'
             WHERE set_id=1");
    } else {
        $ok = mysqli_query($connection,
            "INSERT INTO kp_sms_settings
                (set_id,sms_provider,sms_api_key,sms_kreach_base_url,
                 sms_kreach_send_endpoint,sms_kreach_bulk_endpoint,
                 sms_kreach_account_endpoint,sms_kreach_balance_endpoint)
             VALUES
                (1,'kreach','$api_key','$base_url',
                 '$send_ep','$bulk_ep','$account_ep','$balance_ep')");
    }
    echo $ok
        ? '<div class="alert alert-success"><button class="close" data-dismiss="alert">&times;</button><strong>Saved!</strong> KReach SMS settings updated.</div>'
        : '<div class="alert alert-danger"><button class="close" data-dismiss="alert">&times;</button>Save failed: ' . htmlspecialchars(mysqli_error($connection)) . '</div>';
    exit;
}

// ── Shared input for test actions ─────────────────────────────────────────────
$defaults   = kreach_defaults();
$api_key    = trim($_POST['api_key']    ?? '');
$base_url   = $defaults['base_url'];
$account_ep = $defaults['account_ep'];
$balance_ep = $defaults['balance_ep'];
$send_ep    = $defaults['send_ep'];

if (!$api_key) {
    echo '<div class="alert alert-warning">API Key is required to test.</div>';
    exit;
}
$auth_headers = [
    'Accept: application/json',
    'x-api-key: ' . $api_key,
];

// ── TEST ACCOUNT ──────────────────────────────────────────────────────────────
if ($action === 'test_account') {
    $url    = kreach_build_url($base_url, $account_ep);
    $result = kreach_http($url, 'GET', $auth_headers);

    if ($result['error']) {
        echo '<div class="alert alert-danger"><strong>cURL:</strong> ' . htmlspecialchars($result['error']) . '</div>';
        exit;
    }
    $data = json_decode($result['body'], true);
    if ($result['code'] === 200 && is_array($data)) {
        echo '<div class="alert alert-success"><i class="fa fa-check-circle"></i> <strong>Connected!</strong></div>';
        echo '<table class="table table-sm table-bordered" style="font-size:12px;">';
        array_walk_recursive($data, function(&$v){ $v = htmlspecialchars((string)$v); });
        foreach ($data as $k => $v) {
            if (is_array($v)) $v = json_encode($v);
            echo "<tr><td style='color:#555;'>$k</td><td><strong>$v</strong></td></tr>";
        }
        echo '</table>';
    } else {
        echo '<div class="alert alert-danger"><strong>HTTP ' . $result['code'] . ':</strong> '
           . htmlspecialchars(substr($result['body'], 0, 300)) . '</div>';
    }
    exit;
}

// ── TEST BALANCE ──────────────────────────────────────────────────────────────
if ($action === 'test_balance') {
    $url    = kreach_build_url($base_url, $balance_ep);
    $result = kreach_http($url, 'GET', $auth_headers);

    if ($result['error']) {
        echo '<div class="alert alert-danger"><strong>cURL:</strong> ' . htmlspecialchars($result['error']) . '</div>';
        exit;
    }
    $data    = json_decode($result['body'], true);
    $balance = '';

    if ($result['code'] === 200 && is_array($data)) {
        // Common K-REACH response shapes
        $balance = $data['balance']
            ?? $data['data']['balance']
            ?? $data['credits']
            ?? $data['data']['credits']
            ?? json_encode($data);

        // Persist balance to DB
        $bal_safe  = mysqli_real_escape_string($connection, (string)$balance);
        $bal_clean = htmlspecialchars((string)$balance);
        mysqli_query($connection,
            "UPDATE kp_sms_settings
                SET last_balance='$bal_safe', last_balance_checked_at=NOW()
              WHERE set_id=1");

        echo '<div class="alert alert-success">'
           . '<i class="fa fa-wallet"></i> <strong>Balance: ' . $bal_clean . '</strong>'
           . '<br><small class="text-muted">Saved to settings.</small></div>';
    } else {
        echo '<div class="alert alert-danger"><strong>HTTP ' . $result['code'] . ':</strong> '
           . htmlspecialchars(substr($result['body'], 0, 300)) . '</div>';
    }
    exit;
}

// ── TEST SEND ─────────────────────────────────────────────────────────────────
if ($action === 'test_send') {
    $phone   = kreach_normalize_phone(trim($_POST['phone'] ?? ''));
    $message = trim($_POST['message'] ?? 'Test message from K-Touch POS.');

    if (!$phone) {
        echo '<div class="alert alert-warning">Enter a valid phone number.</div>';
        exit;
    }

    $url     = kreach_build_url($base_url, $send_ep);
    $payload = json_encode([
        'phone' => $phone,
        'phone_number' => $phone,
        'message' => $message
    ]);
    $result  = kreach_http($url, 'POST',
        array_merge($auth_headers, ['Content-Type: application/json']),
        $payload
    );

    if ($result['error']) {
        echo '<div class="alert alert-danger"><strong>cURL:</strong> ' . htmlspecialchars($result['error']) . '</div>';
        exit;
    }
    $data = json_decode($result['body'], true);
    $msg  = is_array($data)
        ? ($data['message'] ?? $data['msg'] ?? $data['description'] ?? json_encode($data))
        : $result['body'];

    if ($result['code'] >= 200 && $result['code'] < 300) {
        echo '<div class="alert alert-success"><i class="fa fa-check-circle"></i> <strong>SMS sent (HTTP '
           . $result['code'] . ').</strong><br>' . htmlspecialchars(substr($msg, 0, 300)) . '</div>';
    } else {
        echo '<div class="alert alert-danger"><strong>HTTP ' . $result['code'] . ':</strong> '
           . htmlspecialchars(substr($msg, 0, 300)) . '</div>';
    }
    exit;
}

echo '<div class="alert alert-danger">Unknown action.</div>';
