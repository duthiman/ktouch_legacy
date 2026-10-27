<?php
declare(strict_types=1);

function shift_repo_current(mysqli $connection): ?array
{
    $result = mysqli_query($connection, 'SELECT shift FROM kp_shift ORDER BY shift DESC LIMIT 1');
    return $result ? (mysqli_fetch_assoc($result) ?: null) : null;
}

function shift_repo_current_value(mysqli $connection): string
{
    return (string)(shift_repo_current($connection)['shift'] ?? '');
}

function shift_repo_today(): string
{
    return date('Y-m-d');
}

function shift_repo_today_is_active(mysqli $connection, string $shift): bool
{
    $escShift = mysqli_real_escape_string($connection, $shift);
    $activeToday = mysqli_query($connection, "SELECT shift FROM kp_day_shift WHERE shift='{$escShift}' AND status=2 AND closing IS NULL ORDER BY opening DESC LIMIT 1");
    return $activeToday && mysqli_num_rows($activeToday) > 0;
}

function shift_repo_sync_active_marker(mysqli $connection, string $shift): void
{
    $escShift = mysqli_real_escape_string($connection, $shift);
    mysqli_query($connection, "DELETE FROM kp_shift");
    if (!mysqli_query($connection, "INSERT INTO kp_shift (shift) VALUES ('{$escShift}')")) {
        throw new RuntimeException('Could not open shift');
    }
}

function shift_repo_create_day(mysqli $connection, string $shift, int $userId): void
{
    $escShift = mysqli_real_escape_string($connection, $shift);
    if (!mysqli_query($connection, "INSERT INTO kp_day_shift (shift,opening,open_by,status) VALUES ('{$escShift}',NOW()," . (int)$userId . ",2)")) {
        throw new RuntimeException('Could not create day shift');
    }
}

function shift_repo_reset_inventory_take_status(mysqli $connection): void
{
    if (!mysqli_query($connection, "UPDATE kp_counter_items SET take_status=2, updated_at=NOW() WHERE is_active=1")) {
        throw new RuntimeException('Could not reset counter stock-take status');
    }
}

function shift_repo_reset_counters(mysqli $connection): void
{
    if (!mysqli_query($connection, "UPDATE kp_counters SET user_id=0, counter_status=2, close_status=2")) {
        throw new RuntimeException('Could not reset counter statuses');
    }
}

function shift_repo_reset_users(mysqli $connection): void
{
    if (!mysqli_query($connection, "UPDATE kp_users SET counter=0, counter_status=2, job=2")) {
        throw new RuntimeException('Could not reset user counter assignments');
    }
}

function shift_repo_close_active(mysqli $connection, string $shift, int $userId): void
{
    $escShift = mysqli_real_escape_string($connection, $shift);
    if (!mysqli_query($connection, "UPDATE kp_day_shift SET closing=NOW(),close_by=" . (int)$userId . ",status=1 WHERE shift='{$escShift}' AND status=2")) throw new RuntimeException('Could not close the active shift');
    if (!mysqli_query($connection, 'DELETE FROM kp_shift')) throw new RuntimeException('Could not clear the active shift');
}
