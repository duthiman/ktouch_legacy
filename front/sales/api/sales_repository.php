<?php
declare(strict_types=1);

/** Shared sales data access used by counters, reports, and summaries. */
function sales_repo_counter_totals(mysqli $connection, int $counterId, string $shift): array
{
    $escShift = mysqli_real_escape_string($connection, $shift);
    $result = mysqli_query($connection, "SELECT COUNT(DISTINCT s.sale_id) AS bills, COALESCE(SUM(si.total),0) AS total FROM kp_sales s LEFT JOIN kp_sale_items si ON si.sale_id=s.sale_id WHERE s.shift='{$escShift}' AND s.counter=" . (int)$counterId);
    if (!$result) throw new RuntimeException('Could not read counter sales');
    return mysqli_fetch_assoc($result) ?: ['bills' => 0, 'total' => 0];
}
