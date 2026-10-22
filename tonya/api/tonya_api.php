<?php
require_once __DIR__ . '/tonya_boot.php';
header('Content-Type: application/json; charset=utf-8');
$mode = (string)($_REQUEST['mode'] ?? 'front');
$password = (string)($_REQUEST['password'] ?? '');
$result = $mode === 'admin' ? tonya_service_admin_login($password) : tonya_service_front_login($password);
http_response_code($result['ok'] ? 200 : 400);
echo json_encode(['ok' => $result['ok'], 'message' => $result['message'], 'redirect' => $result['redirect'], 'html' => $result['html']]);
