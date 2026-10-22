<?php
include("../../inc/connection.php");
include("../clude/funcs.php");

$action = trim($_POST['action'] ?? 'save');

// ── cURL helper ───────────────────────────────────────────────────────────────
function kpesa_http($url, $method = 'GET', array $headers = [], $body = null) {
    // Force HTTPS — KPesa redirects http→https and cURL converts POST→GET on 302
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
        ]);
        if ($method === 'POST') {
            curl_setopt($ch, CURLOPT_POST, true);
            if ($body) curl_setopt($ch, CURLOPT_POSTFIELDS, $body);
        }
        $resp = curl_exec($ch);
        $err  = curl_error($ch);
        $code = curl_getinfo($ch, CURLINFO_HTTP_CODE);
        curl_close($ch);
        return ['code' => $code, 'body' => $resp ?: '', 'error' => $err];
    }
    // Fallback: file_get_contents
    $opts = ['http' => ['method' => $method, 'timeout' => 15,
        'header'  => implode("\r\n", $headers),
        'content' => $body,
        'ignore_errors' => true,
    ]];
    $resp = @file_get_contents($url, false, stream_context_create($opts));
    return ['code' => 0, 'body' => $resp ?: '', 'error' => ''];
}

// ── SAVE ──────────────────────────────────────────────────────────────────────
if ($action === 'save') {
    $kpesa_active = (int)($_POST['kpesa_active'] ?? 1) === 2 ? 2 : 1;
    $api_key      = mysqli_real_escape_string($connection, trim($_POST['api_key']  ?? ''));
    $fetch_url    = mysqli_real_escape_string($connection, trim($_POST['fetch_url'] ?? ''));
    $has_stk_push = (int)($_POST['has_stk_push'] ?? 0) === 1 ? 1 : 0;
    $stk_url      = mysqli_real_escape_string($connection, trim($_POST['stk_url']  ?? ''));

    $kc = mysqli_query($connection, "SHOW COLUMNS FROM kp_settings_print LIKE 'kpesa_active'");
    if ($kc && mysqli_num_rows($kc) === 0) {
        mysqli_query($connection, "ALTER TABLE kp_settings_print ADD COLUMN kpesa_active TINYINT(1) NOT NULL DEFAULT 1");
    }
    $printChk = mysqli_query($connection, "SELECT print_doc FROM kp_settings_print WHERE print_doc=1 LIMIT 1");
    if ($printChk && mysqli_num_rows($printChk) > 0) {
        mysqli_query($connection, "UPDATE kp_settings_print SET kpesa_active='$kpesa_active' WHERE print_doc=1");
    } else {
        mysqli_query($connection, "INSERT INTO kp_settings_print (print_doc, kpesa_active) VALUES (1, '$kpesa_active')");
    }

    $chk = mysqli_query($connection, "SELECT set_id FROM kp_kpesa_settings WHERE set_id=1 LIMIT 1");
    if ($chk && mysqli_num_rows($chk) > 0) {
        $ok = mysqli_query($connection,
            "UPDATE kp_kpesa_settings SET
                api_key='$api_key', fetch_url='$fetch_url',
                has_stk_push=$has_stk_push, stk_url='$stk_url'
             WHERE set_id=1");
    } else {
        $ok = mysqli_query($connection,
            "INSERT INTO kp_kpesa_settings (set_id,api_key,fetch_url,has_stk_push,stk_url)
             VALUES (1,'$api_key','$fetch_url',$has_stk_push,'$stk_url')");
    }
    echo $ok
        ? '<div class="alert alert-success"><button class="close" data-dismiss="alert">&times;</button><strong>Saved!</strong> KPesa settings updated.</div>'
        : '<div class="alert alert-danger"><button class="close" data-dismiss="alert">&times;</button>Failed to save: ' . htmlspecialchars(mysqli_error($connection)) . '</div>';
    exit;
}

// ── TEST FETCH ────────────────────────────────────────────────────────────────
if ($action === 'test_fetch') {
    $api_key   = trim($_POST['api_key']   ?? '');
    $fetch_url = trim($_POST['fetch_url'] ?? '');

    if (!$api_key || !$fetch_url) {
        echo '<div class="alert alert-warning">API Key and Fetch URL are required.</div>';
        exit;
    }

    $result = kpesa_http($fetch_url, 'POST',
        ['Content-Type: application/json', 'x-api-key: ' . $api_key],
        json_encode(['request' => []])
    );

    $data = json_decode($result['body'], true);

    if ($result['error']) {
        echo '<div class="alert alert-danger"><strong>cURL error:</strong> ' . htmlspecialchars($result['error']) . '</div>';
        exit;
    }
    if ($result['code'] !== 200) {
        echo '<div class="alert alert-danger"><strong>HTTP ' . $result['code'] . '</strong> — '
           . htmlspecialchars(substr($result['body'], 0, 300)) . '</div>';
        exit;
    }
    if (!is_array($data)) {
        echo '<div class="alert alert-warning">Unexpected response: <pre style="font-size:11px;">' . htmlspecialchars(substr($result['body'],0,500)) . '</pre></div>';
        exit;
    }

    // Render receipts table
    $items = $data['data'] ?? $data['receipts'] ?? (isset($data[0]) ? $data : []);
    if (empty($items)) {
        echo '<div class="alert alert-info"><i class="fa fa-info-circle"></i> Connection OK — no receipts returned.</div>';
        exit;
    }
    echo '<div class="alert alert-success m-b-5"><i class="fa fa-check-circle"></i> Connected — ' . count($items) . ' receipt(s) found.</div>';
    echo '<div style="overflow-x:auto;max-height:260px;overflow-y:auto;">';
    echo '<table class="table table-sm table-bordered table-striped" style="font-size:12px;">';
    // Headers from first item
    echo '<thead><tr>';
    foreach (array_keys((array)$items[0]) as $col) {
        echo '<th>' . htmlspecialchars($col) . '</th>';
    }
    echo '</tr></thead><tbody>';
    foreach (array_slice($items, 0, 20) as $item) {
        echo '<tr>';
        foreach ((array)$item as $v) {
            echo '<td>' . htmlspecialchars((string)$v) . '</td>';
        }
        echo '</tr>';
    }
    echo '</tbody></table></div>';
    exit;
}

// ── TEST STK ──────────────────────────────────────────────────────────────────
if ($action === 'test_stk') {
    $api_key = trim($_POST['api_key'] ?? '');
    $stk_url = trim($_POST['stk_url']  ?? '');
    $phone   = preg_replace('/\D/', '', trim($_POST['phone'] ?? ''));

    if (!$api_key || !$stk_url || !$phone) {
        echo '<div class="alert alert-warning">API Key, STK URL and phone are required.</div>';
        exit;
    }

    $payload = json_encode([
        'request' => [
            'request_id' => 'STK-TEST-' . time(),
            'data'       => [
                'phone'          => $phone,
                'amount'         => 1,
                'reference_code' => 'STK-TEST',
                'description'    => 'KPesa STK test from K-Touch settings',
            ]
        ]
    ]);

    $result = kpesa_http($stk_url, 'POST',
        ['Content-Type: application/json', 'x-api-key: ' . $api_key],
        $payload
    );

    $data = json_decode($result['body'], true);
    $msg  = is_array($data) ? ($data['message'] ?? $data['msg'] ?? json_encode($data)) : $result['body'];

    if ($result['error']) {
        echo '<div class="alert alert-danger"><strong>cURL error:</strong> ' . htmlspecialchars($result['error']) . '</div>';
        exit;
    }
    if ($result['code'] >= 200 && $result['code'] < 300) {
        echo '<div class="alert alert-success"><strong>STK sent (HTTP ' . $result['code'] . ').</strong> ' . htmlspecialchars(substr($msg, 0, 300)) . '</div>';
    } else {
        echo '<div class="alert alert-danger"><strong>HTTP ' . $result['code'] . ':</strong> ' . htmlspecialchars(substr($msg, 0, 300)) . '</div>';
    }
    exit;
}

echo '<div class="alert alert-danger">Unknown action.</div>';
