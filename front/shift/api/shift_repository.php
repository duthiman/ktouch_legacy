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
    $current = shift_repo_current($connection);
    if ($current) throw new LogicException('A shift is already open');
    $activeDay = mysqli_query($connection, "SELECT shift FROM kp_day_shift WHERE status=2 AND closing IS NULL ORDER BY opening DESC LIMIT 1");
    if ($activeDay && ($row = mysqli_fetch_assoc($activeDay))) {
        if (!mysqli_query($connection, "INSERT INTO kp_shift (shift) VALUES ('" . mysqli_real_escape_string($connection, $row['shift']) . "')")) throw new RuntimeException('Could not restore active shift');
        return (string)$row['shift'];
    }
    $shift = date('Y-m-d');
    $escShift = mysqli_real_escape_string($connection, $shift);
    if (!mysqli_query($connection, "INSERT INTO kp_shift (shift) VALUES ('{$escShift}')")) throw new RuntimeException('Could not open shift');
    if (!mysqli_query($connection, "INSERT INTO kp_day_shift (shift,opening,open_by,status) VALUES ('{$escShift}',NOW()," . (int)$userId . ",2)")) throw new RuntimeException('Could not create day shift');
    return $shift;
}

function shift_repo_close_active(mysqli $connection, string $shift, int $userId): void
{
    $escShift = mysqli_real_escape_string($connection, $shift);
    if (!mysqli_query($connection, "UPDATE kp_day_shift SET closing=NOW(),close_by=" . (int)$userId . ",status=1 WHERE shift='{$escShift}' AND status=2")) throw new RuntimeException('Could not close the active shift');
    if (!mysqli_query($connection, 'DELETE FROM kp_shift')) throw new RuntimeException('Could not clear the active shift');
}
