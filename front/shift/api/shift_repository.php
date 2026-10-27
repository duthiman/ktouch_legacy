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

function shift_repo_open(mysqli $connection, int $userId): string
{
    // A shift may only be opened for today. Never restore an older active
    // day, because that makes new counter sales inherit the wrong date.
    $shift = date('Y-m-d');
    $escShift = mysqli_real_escape_string($connection, $shift);
    $activeToday = mysqli_query($connection, "SELECT shift FROM kp_day_shift WHERE shift='{$escShift}' AND status=2 AND closing IS NULL ORDER BY opening DESC LIMIT 1");
    $todayExists = $activeToday && mysqli_num_rows($activeToday) > 0;

    // Keep the single active-shift marker aligned with today's day shift.
    mysqli_query($connection, "DELETE FROM kp_shift");
    if (!mysqli_query($connection, "INSERT INTO kp_shift (shift) VALUES ('{$escShift}')")) throw new RuntimeException('Could not open shift');

    if (!$todayExists && !mysqli_query($connection, "INSERT INTO kp_day_shift (shift,opening,open_by,status) VALUES ('{$escShift}',NOW()," . (int)$userId . ",2)")) {
        throw new RuntimeException('Could not create day shift');
    }
    // Every assigned counter item must be pending for the new day's stock
    // take. The take screen uses this flag to show the required items.
    if (!mysqli_query($connection, "UPDATE kp_counter_items SET take_status=2, updated_at=NOW() WHERE is_active=1")) {
        throw new RuntimeException('Could not reset counter stock-take status');
    }
    // Start each shift with no counter or cashier carried over from a
    // previous session. Counters are opened explicitly afterwards.
    if (!mysqli_query($connection, "UPDATE kp_counters SET user_id=0, counter_status=2, close_status=2")) {
        throw new RuntimeException('Could not reset counter statuses');
    }
    if (!mysqli_query($connection, "UPDATE kp_users SET counter=0, counter_status=2, job=2")) {
        throw new RuntimeException('Could not reset user counter assignments');
    }
    return $shift;
}

function shift_repo_close_active(mysqli $connection, string $shift, int $userId): void
{
    $escShift = mysqli_real_escape_string($connection, $shift);
    if (!mysqli_query($connection, "UPDATE kp_day_shift SET closing=NOW(),close_by=" . (int)$userId . ",status=1 WHERE shift='{$escShift}' AND status=2")) throw new RuntimeException('Could not close the active shift');
    if (!mysqli_query($connection, 'DELETE FROM kp_shift')) throw new RuntimeException('Could not clear the active shift');
}
