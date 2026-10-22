<?php
header('Content-Type: application/json');

if (session_status() === PHP_SESSION_NONE) {
    session_start();
}

require_once __DIR__ . '/../../../inc/connection.php';
require_once __DIR__ . '/funcs.php';

function dash_json_response($success, $message, $data = [])
{
    echo json_encode([
        'success' => (bool)$success,
        'message' => $message,
        'data' => $data,
    ]);
    exit;
}

function dash_expire_session()
{
    $_SESSION = [];
    if (ini_get('session.use_cookies')) {
        $params = session_get_cookie_params();
        setcookie(session_name(), '', time() - 42000, $params['path'], $params['domain'], $params['secure'], $params['httponly']);
    }
    session_destroy();
}

if (!isset($_SESSION['user_id']) || (int)$_SESSION['user_id'] < 1) {
    dash_expire_session();
    dash_json_response(false, 'Session expired. Please login again.', [
        'auto_logout' => true,
        'redirect' => '../index.php',
    ]);
}

$user_id = (int)$_SESSION['user_id'];
$data = dash_build_payload($user_id);
$data['alerts'] = dash_build_alerts(
    $user_id,
    isset($_GET['msg']) ? (int)$_GET['msg'] : null,
    $_GET['redir'] ?? ''
);

dash_json_response(true, 'Dashboard data loaded', $data);
