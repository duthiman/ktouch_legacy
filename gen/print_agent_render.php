<?php
$ip = $_SERVER['REMOTE_ADDR'] ?? '';
if ($ip !== '127.0.0.1' && $ip !== '::1') {
    http_response_code(403);
    header('Content-Type: text/plain');
    exit('Forbidden');
}

$configPath = dirname(__DIR__) . '/tools/print_worker.config.php';
if (!is_file($configPath)) {
    http_response_code(503);
    header('Content-Type: text/plain');
    exit('print_worker.config.php missing');
}
$config = require $configPath;
$expected = (string)($config['render_token'] ?? '');
$given = (string)($_GET['token'] ?? '');
if ($expected === '' || !hash_equals($expected, $given)) {
    http_response_code(403);
    header('Content-Type: text/plain');
    exit('Forbidden');
}

session_start();
$_SESSION['user_id'] = (int)($config['worker_user_id'] ?? 1);
if ($_SESSION['user_id'] < 1) {
    http_response_code(500);
    header('Content-Type: text/plain');
    exit('Invalid worker_user_id');
}

include(dirname(__DIR__) . '/inc/connection.php');
include(dirname(__DIR__) . '/front/chaba/funcs.php');

$queue_id = (int)($_GET['queue_id'] ?? 0);
if ($queue_id < 1) {
    http_response_code(400);
    header('Content-Type: text/plain');
    exit('Queue id required');
}

$job = kp_print_fetch_queue_job($connection, $queue_id);
if (!$job) {
    http_response_code(404);
    header('Content-Type: text/plain');
    exit('Queue job not found');
}

$routePath = trim((string)($job['route_path'] ?? ''));
if ($routePath === '' || strpos($routePath, '..') !== false || strpos($routePath, '\\') !== false) {
    http_response_code(400);
    header('Content-Type: text/plain');
    exit('Invalid route path');
}

$query = [];
parse_str((string)($job['route_query'] ?? ''), $query);
foreach ($query as $key => $value) {
    $_GET[$key] = $value;
    $_REQUEST[$key] = $value;
}
$_GET['queue_id'] = $queue_id;
$_REQUEST['queue_id'] = $queue_id;

$fullPath = dirname(__DIR__) . '/front/chaba/' . $routePath;
if (!is_file($fullPath)) {
    http_response_code(404);
    header('Content-Type: text/plain');
    exit('Print route not found');
}

header('Content-Type: text/html; charset=UTF-8');
require $fullPath;
