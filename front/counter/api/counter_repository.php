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

function counter_repo_assert_open(mysqli $connection, int $counterId): array
{
    $counter = counter_repo_get($connection, $counterId);
    if (!$counter) throw new LogicException('Counter not found');
    if ((int)($counter['counter_status'] ?? 2) !== 1) throw new LogicException('Counter is not open');
    return $counter;
}

function counter_repo_sales_totals(mysqli $connection, int $counterId, string $shift): array
{
    $shift = mysqli_real_escape_string($connection, $shift);
    $result = mysqli_query($connection, "SELECT COUNT(sale_id) bills, COALESCE(SUM(amount),0) total FROM kp_sales WHERE shift='{$shift}' AND counter=" . (int)$counterId);
    if (!$result) throw new RuntimeException('Could not read counter sales');
    return mysqli_fetch_assoc($result) ?: ['bills' => 0, 'total' => 0];
}

function counter_repo_create_summary(mysqli $connection, int $counterId, int $userId, string $shift, array $sales): int
{
    $shift = mysqli_real_escape_string($connection, $shift);
    $sql = "INSERT INTO kp_counter_close_summary (shift_date,cntr_id,user_id,total_bills,total_sales) VALUES ('{$shift}'," . (int)$counterId . "," . (int)$userId . "," . (int)($sales['bills'] ?? 0) . "," . (float)($sales['total'] ?? 0) . ")";
    if (!mysqli_query($connection, $sql)) throw new RuntimeException('Could not save counter summary');
    return (int)mysqli_insert_id($connection);
}

function counter_repo_queue_csp(mysqli $connection, int $summaryId, int $counterId, string $shift, int $userId): int
{
    $escShift = mysqli_real_escape_string($connection, $shift);
    $query = mysqli_real_escape_string($connection, "summary_id={$summaryId}&cntr_id={$counterId}&shift=" . urlencode($shift));
    $sql = "INSERT INTO kp_print_queue (sale_id,ref_id,print_type,label,cntr_id,route_path,route_query,requested_by,status,shift) VALUES (0,{$summaryId},'csp','COUNTER SUMMARY'," . (int)$counterId . ",'csp.php','{$query}'," . (int)$userId . ",0,'{$escShift}')";
    if (!mysqli_query($connection, $sql)) throw new RuntimeException('Could not queue counter summary');
    return (int)mysqli_insert_id($connection);
}

function counter_repo_close_status(mysqli $connection, int $counterId): void
{
    if (!mysqli_query($connection, "UPDATE kp_counters SET user_id=0,take_status=2,counter_status=2,close_status=2 WHERE cntr_id=" . (int)$counterId)) throw new RuntimeException('Could not close counter');
}

function counter_repo_release_cashier(mysqli $connection, int $userId): void
{
    if (!mysqli_query($connection, "UPDATE kp_users SET job=2,shift_status=2,counter=0,counter_status=2,cleared=1 WHERE user_id=" . (int)$userId)) throw new RuntimeException('Could not reset cashier');
}

function counter_repo_queue_ssp(mysqli $connection, string $shift, int $userId): int
{
    $escShift = mysqli_real_escape_string($connection, $shift);
    $payload = mysqli_real_escape_string($connection, (string)json_encode(['payload_version'=>1,'document_type'=>'SSP','title'=>'SHIFT SUMMARY PRINT','header'=>['shift_date'=>$shift,'print_time'=>date('Y-m-d H:i:s')]], JSON_UNESCAPED_SLASHES));
    $sql = "INSERT INTO kp_print_queue (sale_id,ref_id,print_type,label,cntr_id,route_path,route_query,requested_by,status,shift,payload_json) VALUES (0,0,'ssp','SHIFT SUMMARY',0,'ssp.php','shift=" . urlencode($shift) . "'," . (int)$userId . ",0,'{$escShift}','{$payload}')";
    if (!mysqli_query($connection, $sql)) throw new RuntimeException('Could not queue shift summary');
    return (int)mysqli_insert_id($connection);
}

function counter_repo_close_shift(mysqli $connection, string $shift, int $userId): void
{
    $escShift = mysqli_real_escape_string($connection, $shift);
    if (!mysqli_query($connection, "UPDATE kp_day_shift SET closing=NOW(),close_by=" . (int)$userId . ",status=1 WHERE shift='{$escShift}' AND status=2")) throw new RuntimeException('Could not close the active shift');
    if (!mysqli_query($connection, 'DELETE FROM kp_shift')) throw new RuntimeException('Could not clear the active shift');
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
