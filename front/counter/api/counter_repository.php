<?php
declare(strict_types=1);

function counter_repo_current_shift(mysqli $connection): string
{
    $result = mysqli_query($connection, "SELECT shift FROM kp_shift ORDER BY shift DESC LIMIT 1");
    $row = $result ? mysqli_fetch_assoc($result) : null;
    return (string)($row['shift'] ?? '');
}

function counter_repo_get(mysqli $connection, int $counterId): ?array
{
    $counterId = (int)$counterId;
    $result = mysqli_query($connection, "SELECT * FROM kp_counters WHERE cntr_id={$counterId} LIMIT 1");
    return $result ? (mysqli_fetch_assoc($result) ?: null) : null;
}

function counter_repo_open_count(mysqli $connection): int
{
    $result = mysqli_query($connection, "SELECT COUNT(*) total FROM kp_counters WHERE del_status=2 AND counter_status=1");
    $row = $result ? mysqli_fetch_assoc($result) : null;
    return (int)($row['total'] ?? 0);
}

function counter_repo_open(mysqli $connection, int $counterId, int $userId, int $takeStatus): bool
{
    $counterId = (int)$counterId; $userId = (int)$userId; $takeStatus = (int)$takeStatus;
    mysqli_begin_transaction($connection);
    try {
        $counter = counter_repo_get($connection, $counterId);
        if (!$counter) throw new RuntimeException('Counter not found');
        $user = mysqli_query($connection, "SELECT user_id FROM kp_users WHERE user_id={$userId} AND counter=0 LIMIT 1");
        if (!$user || mysqli_num_rows($user) !== 1) throw new RuntimeException('Cashier is not available');
        if (!mysqli_query($connection, "UPDATE kp_users SET job=1,counter_status=1,counter={$counterId} WHERE user_id={$userId}")) throw new RuntimeException('Could not assign cashier');
        if (!mysqli_query($connection, "UPDATE kp_counters SET user_id={$userId},take_status={$takeStatus},counter_status=1,close_status=3 WHERE cntr_id={$counterId}")) throw new RuntimeException('Could not open counter');
        mysqli_commit($connection); return true;
    } catch (Throwable $e) { mysqli_rollback($connection); throw $e; }
}
