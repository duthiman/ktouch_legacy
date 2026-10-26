<?php
declare(strict_types=1);

/** Shared sales data access used by counters, reports, and summaries. */
function sales_repo_counter_totals(mysqli $connection, int $counterId, string $shift): array
{
    $escShift = mysqli_real_escape_string($connection, $shift);
    $result = mysqli_query($connection, "SELECT COUNT(sale_id) AS bills, COALESCE(SUM(amount),0) AS total FROM kp_sales WHERE shift='{$escShift}' AND counter=" . (int)$counterId);
    if (!$result) throw new RuntimeException('Could not read counter sales');
    return mysqli_fetch_assoc($result) ?: ['bills' => 0, 'total' => 0];
}
