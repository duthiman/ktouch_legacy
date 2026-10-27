<?php
declare(strict_types=1);

function shift_service_status(mysqli $connection): array
{
    $shift = shift_repo_current($connection);
    return shift_api_response(true, $shift ? 'open' : 'closed', $shift ? 'Active shift loaded' : 'No active shift', ['shift' => $shift['shift'] ?? '', 'shift_id' => (int)($shift['shift_id'] ?? 0)]);
}

function shift_service_open(mysqli $connection, int $userId): array
{
    if ($userId < 1) return shift_api_response(false, 'invalid_request', 'A valid user is required');
    mysqli_begin_transaction($connection);
    try {
        $shift = shift_repo_today();
        $todayIsActive = shift_repo_today_is_active($connection, $shift);
        shift_repo_sync_active_marker($connection, $shift);
        if (!$todayIsActive) shift_repo_create_day($connection, $shift, $userId);
        shift_repo_reset_inventory_take_status($connection);
        shift_repo_reset_counters($connection);
        shift_repo_reset_users($connection);
        mysqli_commit($connection);
        return shift_api_response(true, 'opened', 'Shift opened successfully', ['shift' => $shift]);
    } catch (LogicException $e) { mysqli_rollback($connection); return shift_api_response(false, 'already_open', $e->getMessage());
    } catch (Throwable $e) { mysqli_rollback($connection); return shift_api_response(false, 'server_error', $e->getMessage()); }
}

function shift_service_close(mysqli $connection, int $userId): array
{
    if ($userId < 1) return shift_api_response(false, 'invalid_request', 'A valid user is required');
    $shift = shift_repo_current_value($connection);
    if ($shift === '') return shift_api_response(false, 'not_open', 'No active shift found');
    mysqli_begin_transaction($connection);
    try { shift_repo_close_active($connection, $shift, $userId); mysqli_commit($connection); return shift_api_response(true, 'closed', 'Shift closed successfully', ['shift' => $shift]);
    } catch (Throwable $e) { mysqli_rollback($connection); return shift_api_response(false, 'server_error', $e->getMessage(), ['shift' => $shift]); }
}
