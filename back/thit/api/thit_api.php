<?php
declare(strict_types=1);

require_once __DIR__ . '/../../../inc/connection.php';
require_once __DIR__ . '/thit_service.php';

if (session_status() === PHP_SESSION_NONE) session_start();

try {
    $action = $_GET['action'] ?? 'dashboard';
    if ($action !== 'dashboard') thit_json(['ok' => false, 'message' => 'Unknown action'], 404);
    thit_json(['ok' => true, 'data' => thit_service_dashboard($connection)]);
} catch (Throwable $e) {
    thit_json(['ok' => false, 'message' => 'Dashboard data could not be loaded', 'data' => []], 500);
}
