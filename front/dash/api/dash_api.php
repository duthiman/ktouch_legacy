<?php

require_once __DIR__ . '/dash_boot.php';

$user_id = dash_current_user_id();
if ($user_id < 1) {
    dash_expire_session();
    dash_json_response(false, 'Session expired. Please login again.', [
        'auto_logout' => true,
        'redirect' => '../index.php',
    ], 401);
}

$result = dash_service_load(
    $user_id,
    isset($_GET['msg']) ? (int)$_GET['msg'] : null,
    $_GET['redir'] ?? ''
);

dash_json_response(
    $result['ok'],
    $result['message'],
    $result['data'],
    $result['ok'] ? 200 : 500
);

