<?php
declare(strict_types=1);

namespace KTouch\Modules\Chaba\V1\Services;

use PDO;

/** Loads the complete counter-summary data used by the CSP renderer. */
final class CspService
{
    public function load(PDO $db, int $summaryId, int $counterId, string $shift): array
    {
        $summary = $this->one($db, 'SELECT * FROM kp_counter_close_summary WHERE close_sum_id = ? LIMIT 1', [$summaryId]);
        if ($summary) {
            $counterId = $counterId > 0 ? $counterId : (int)($summary['cntr_id'] ?? 0);
            $shift = $shift !== '' ? $shift : (string)($summary['shift_date'] ?? '');
        }
        if ($counterId < 1) {
            throw new \RuntimeException('Invalid counter for counter summary');
        }
        if ($shift === '') {
            $shift = (string)($this->scalar($db, 'SELECT shift FROM kp_shift WHERE shift_id = 1 LIMIT 1') ?? '');
        }

        $openingStaff = (int)($summary['opening_staff_id'] ?? $summary['open_by'] ?? $this->scalar($db, "SELECT open_by FROM kp_counter_hist WHERE shift = ? LIMIT 1", [$shift]) ?? 0);
        $closingStaff = (int)($summary['closing_staff_id'] ?? $summary['close_by'] ?? $this->scalar($db, "SELECT close_by FROM kp_counter_hist WHERE shift = ? LIMIT 1", [$shift]) ?? 0);
        $openingAt = (string)($summary['opening_time'] ?? $summary['opened_at'] ?? $this->scalar($db, "SELECT opening FROM kp_counter_hist WHERE shift = ? LIMIT 1", [$shift]) ?? '');
        $closingAt = (string)($summary['closing_time'] ?? $summary['closed_at'] ?? $this->scalar($db, "SELECT closing FROM kp_counter_hist WHERE shift = ? LIMIT 1", [$shift]) ?? '');

        $counterName = (string)($this->scalar($db, 'SELECT name FROM kp_counters WHERE cntr_id = ? LIMIT 1', [$counterId]) ?? '');
        $salesTotal = (float)($summary['total_sales'] ?? $this->scalar($db, 'SELECT COALESCE(SUM(amount),0) FROM kp_sales WHERE shift = ? AND counter = ?', [$shift, $counterId]) ?? 0);

        return [
            'summary_id' => $summaryId,
            'counter_id' => $counterId,
            'counter_name' => $counterName,
            'shift' => $shift,
            'summary' => $summary ?: [],
            'header' => [
                'counter_id' => $counterId,
                'counter_name' => $counterName,
                'shift' => $shift,
                'opening_staff_id' => $openingStaff,
                'closing_staff_id' => $closingStaff,
                'opening_at' => $openingAt,
                'closing_at' => $closingAt,
                'total_sales' => $salesTotal,
            ],
            'sales' => $this->rows($db, 'SELECT si.item_id, i.cat_id, SUM(si.qty) qty, SUM(si.total) amount FROM kp_sale_items si LEFT JOIN kp_items i ON i.item_id = si.item_id WHERE si.shift = ? AND si.stock_counter = ? GROUP BY si.item_id, i.cat_id ORDER BY i.cat_id ASC, amount DESC', [$shift, $counterId]),
            'requisitions' => $this->rows($db, 'SELECT ri.item_id, i.cat_id, r.issue_user, r.issue_status, r.rec_status, SUM(COALESCE(ri.issue_qty, ri.req_qty, 0)) qty FROM kp_counter_req r INNER JOIN kp_counter_req_items ri ON ri.req_id = r.req_id LEFT JOIN kp_items i ON i.item_id = ri.item_id WHERE r.req_shift = ? AND r.counter = ? GROUP BY ri.item_id, i.cat_id, r.issue_user, r.issue_status, r.rec_status ORDER BY r.issue_status ASC, i.cat_id ASC, ri.item_id ASC', [$shift, $counterId]),
            'user_sales' => $this->rows($db, 'SELECT user_id, COUNT(sale_id) orders_count, SUM(amount) sales_total FROM kp_sales WHERE shift = ? AND counter = ? GROUP BY user_id ORDER BY sales_total DESC', [$shift, $counterId]),
            'returns' => $this->rows($db, 'SELECT user_id, item_id, confirm_by, confirm_status, SUM(qty) qty FROM kp_ret_items WHERE shift = ? AND stock_counter = ? GROUP BY user_id, item_id, confirm_by, confirm_status ORDER BY user_id ASC, confirm_status ASC, item_id ASC', [$shift, $counterId]),
        ];
    }

    private function rows(PDO $db, string $sql, array $params = []): array
    {
        $q = $db->prepare($sql);
        $q->execute($params);
        return $q->fetchAll(PDO::FETCH_ASSOC);
    }

    private function one(PDO $db, string $sql, array $params = []): ?array
    {
        $q = $db->prepare($sql);
        $q->execute($params);
        return $q->fetch(PDO::FETCH_ASSOC) ?: null;
    }

    private function scalar(PDO $db, string $sql, array $params = []): mixed
    {
        $q = $db->prepare($sql);
        $q->execute($params);
        return $q->fetchColumn();
    }
}
