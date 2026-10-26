<?php
declare(strict_types=1);

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

function counter_repo_assert_open(mysqli $connection, int $counterId): array
{
    $counter = counter_repo_get($connection, $counterId);
    if (!$counter) throw new LogicException('Counter not found');
    if ((int)($counter['counter_status'] ?? 2) !== 1) throw new LogicException('Counter is not open');
    return $counter;
}

function counter_repo_close_blockers(mysqli $connection, int $counterId, string $shift): array
{
    $escShift = mysqli_real_escape_string($connection, $shift);
    $counterId = (int)$counterId;
    $count = static function (string $table, string $where) use ($connection): int {
        $tableCheck = mysqli_query($connection, "SHOW TABLES LIKE '" . mysqli_real_escape_string($connection, $table) . "'");
        if (!$tableCheck || mysqli_num_rows($tableCheck) < 1) return 0;
        $query = mysqli_query($connection, "SELECT COUNT(*) AS total FROM `{$table}` WHERE {$where}");
        $row = $query ? mysqli_fetch_assoc($query) : null;
        return (int)($row['total'] ?? 0);
    };
    return [
        'pending_returns' => $count('kp_ret_items', "shift='{$escShift}' AND stock_counter={$counterId} AND ret_status=3"),
        'pending_transfers' => $count('kp_counter_req', "counter={$counterId} AND issue_status=1 AND rec_status=2"),
        'uncleared_bills' => $count('kp_sales', "shift='{$escShift}' AND counter={$counterId} AND pay_status=2"),
    ];
}

function counter_repo_close_status(mysqli $connection, int $counterId): void
{
    if (!mysqli_query($connection, "UPDATE kp_counters SET user_id=0,take_status=2,counter_status=2,close_status=2 WHERE cntr_id=" . (int)$counterId)) throw new RuntimeException('Could not close counter');
}

function counter_repo_release_cashier(mysqli $connection, int $userId): void
{
    if (!mysqli_query($connection, "UPDATE kp_users SET job=2,shift_status=2,counter=0,counter_status=2,cleared=1 WHERE user_id=" . (int)$userId)) throw new RuntimeException('Could not reset cashier');
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
