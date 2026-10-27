<?php
declare(strict_types=1);
session_start();
$projectRoot = dirname(__DIR__, 3);
require_once $projectRoot . DIRECTORY_SEPARATOR . 'inc' . DIRECTORY_SEPARATOR . 'connection.php';
require_once __DIR__ . '/shift_helpers.php';
require_once __DIR__ . '/shift_schema.php';
require_once __DIR__ . '/../../counter/api/counter_inventory_schema.php';
require_once __DIR__ . '/shift_repository.php';
require_once __DIR__ . '/shift_service.php';

shift_schema_ensure($connection);
counter_inventory_ensure_schema();
$data = array_replace($_GET, $_POST);
$action = trim((string)($data['action'] ?? 'status'));
$userId = shift_api_int($data, 'user_id', (int)($_SESSION['user_id'] ?? 0));
try {
    $response = match ($action) {
        'status', 'shift.status' => shift_service_status($connection),
        'open', 'shift.open' => shift_service_open($connection, $userId),
        'close', 'shift.close' => shift_service_close($connection, $userId),
        default => shift_api_response(false, 'unsupported_action', 'Unsupported shift action', ['action' => $action]),
    };
    if (!$response['ok']) http_response_code(match ($response['status']) { 'already_open' => 409, 'not_open' => 409, 'server_error' => 500, default => 422 });
    shift_api_json($response);
} catch (Throwable $e) { http_response_code(500); shift_api_json(shift_api_response(false, 'server_error', 'Shift service failed', ['error' => $e->getMessage()])); }
