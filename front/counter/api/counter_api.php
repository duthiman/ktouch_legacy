<?php
declare(strict_types=1);
session_start();
require_once '../../../inc/connection.php';
require_once '../../shift/api/shift_repository.php';
require_once '../../sales/api/sales_repository.php';
require_once __DIR__ . '/counter_helpers.php';
require_once __DIR__ . '/counter_logger.php';
require_once __DIR__ . '/counter_schema.php';
require_once __DIR__ . '/counter_repository.php';
require_once __DIR__ . '/counter_service.php';

counter_schema_ensure($connection);
$data = array_replace($_GET, $_POST);
$action = trim((string)($data['action'] ?? 'status'));
$counterId = counter_api_int($data, 'counter_id', counter_api_int($data, 'counter', 0));
$userId = counter_api_int($data, 'user_id', (int)($_SESSION['user_id'] ?? 0));

try {
    $response = match ($action) {
        'status', 'counter.status' => counter_service_status($connection, $counterId),
        'open', 'counter.open' => counter_service_open($connection, $counterId, $userId, counter_api_int($data, 'take_status', 3)),
        'close', 'counter.close' => counter_service_close($connection, $counterId, $userId),
        default => counter_api_response(false, 'unsupported_action', 'Unsupported counter action', ['action' => $action]),
    };
    if (!$response['ok']) {
        http_response_code(match ($response['status']) {
            'not_found' => 404,
            'already_open', 'invalid_state' => 409,
            'server_error' => 500,
            default => 422,
        });
    }
    counter_api_json($response);
} catch (Throwable $e) {
    http_response_code(500);
    counter_api_json(counter_api_response(false, 'server_error', 'Counter service failed', ['error' => $e->getMessage()]));
}
