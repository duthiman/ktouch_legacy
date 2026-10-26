<?php
declare(strict_types=1);

function counter_service_status(mysqli $connection, int $counterId): array
{
    $counter = counter_repo_get($connection, $counterId);
    if (!$counter) return counter_api_response(false, 'not_found', 'Counter not found', ['counter_id' => $counterId]);
    return counter_api_response(true, 'loaded', 'Counter status loaded', [
        'counter_id' => $counterId,
        'counter' => $counter,
        'open_counters' => counter_repo_open_count($connection),
        'shift' => counter_repo_current_shift($connection),
    ]);
}

function counter_service_open(mysqli $connection, int $counterId, int $userId, int $takeStatus): array
{
    if ($counterId < 1 || $userId < 1 || !in_array($takeStatus, [1, 2, 3], true)) return counter_api_response(false, 'invalid_request', 'Valid counter, cashier, and take status are required', []);
    $counter = counter_repo_get($connection, $counterId);
    if (!$counter) return counter_api_response(false, 'not_found', 'Counter not found', ['counter_id' => $counterId]);
    if ((int)($counter['counter_status'] ?? 2) === 1) return counter_api_response(false, 'already_open', 'Counter is already open', ['counter_id' => $counterId]);
    try {
        counter_repo_open($connection, $counterId, $userId, $takeStatus);
        counter_log('counter_opened', ['counter_id' => $counterId, 'user_id' => $userId, 'take_status' => $takeStatus]);
        return counter_api_response(true, 'opened', 'Counter opened successfully', ['counter_id' => $counterId, 'user_id' => $userId, 'take_status' => $takeStatus]);
    } catch (Throwable $e) {
        counter_log('counter_open_failed', ['counter_id' => $counterId, 'error' => $e->getMessage()]);
        return counter_api_response(false, 'server_error', $e->getMessage(), ['counter_id' => $counterId]);
    }
}
