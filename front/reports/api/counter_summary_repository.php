<?php
declare(strict_types=1);

/** Persists the summary snapshot created when a counter closes. */
function counter_summary_repo_create(mysqli $connection, int $counterId, int $userId, string $shift, array $sales): int
{
    $escShift = mysqli_real_escape_string($connection, $shift);
    $sql = "INSERT INTO kp_counter_close_summary (shift_date,cntr_id,user_id,total_bills,total_sales) VALUES ('{$escShift}'," . (int)$counterId . "," . (int)$userId . "," . (int)($sales['bills'] ?? 0) . "," . (float)($sales['total'] ?? 0) . ")";
    if (!mysqli_query($connection, $sql)) throw new RuntimeException('Could not save counter summary');
    return (int)mysqli_insert_id($connection);
}
