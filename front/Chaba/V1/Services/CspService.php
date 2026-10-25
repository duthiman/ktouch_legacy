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

        $salesItems = $this->withNames($db, $this->rows($db, 'SELECT si.item_id, i.cat_id, SUM(si.qty) qty, SUM(si.total) amount FROM kp_sale_items si LEFT JOIN kp_items i ON i.item_id = si.item_id WHERE si.shift = ? AND si.stock_counter = ? GROUP BY si.item_id, i.cat_id ORDER BY i.cat_id ASC, amount DESC', [$shift, $counterId]));
        $billCount = (int)($this->scalar($db, 'SELECT COUNT(sale_id) FROM kp_sales WHERE shift = ? AND counter = ?', [$shift, $counterId]) ?? 0);
        $staffSales = $this->withUserNames($db, $this->rows($db, 'SELECT user_id, SUM(total) sales_total FROM kp_sale_items WHERE shift = ? AND stock_counter = ? GROUP BY user_id ORDER BY sales_total DESC', [$shift, $counterId]));

        $transfers = $this->transferRows($db, $shift, $counterId);
        $unitRows = $this->tableExists($db, 'kp_stock_track')
            ? $this->withNames($db, $this->withUserNames($db, $this->rows($db, "SELECT item_id, user_id, SUM(qty) qty FROM kp_stock_track WHERE shift = ? AND stock_counter = ? AND `trans` = 'UNIT_GEN' GROUP BY item_id, user_id ORDER BY item_id ASC", [$shift, $counterId])))
            : [];
        $deletedBills = $this->tableExists($db, 'kp_del_orders')
            ? $this->withUserNames($db, $this->rows($db, 'SELECT * FROM kp_del_orders WHERE shift = ?', [$shift]))
            : [];
        $returnTotals = [
            'accepted' => (int)($summary['today_returns_accepted_total'] ?? $this->scalar($db, 'SELECT COUNT(item_id) FROM kp_ret_items WHERE shift = ? AND stock_counter = ? AND confirm_status = 1', [$shift, $counterId]) ?? 0),
            'rejected' => (int)($summary['today_returns_rejected_total'] ?? $this->scalar($db, 'SELECT COUNT(item_id) FROM kp_ret_items WHERE shift = ? AND stock_counter = ? AND confirm_status = 3', [$shift, $counterId]) ?? 0),
        ];
        $movementTotals = [
            'to_counter' => (int)($summary['today_transfer_to_total'] ?? $this->scalar($db, 'SELECT COUNT(trans_id) FROM kp_counter_trans WHERE shift = ? AND cntr_to = ?', [$shift, $counterId]) ?? 0),
            'from_counter' => (int)($summary['today_transfer_from_total'] ?? $this->scalar($db, 'SELECT COUNT(trans_id) FROM kp_counter_trans WHERE shift = ? AND cntr_from = ?', [$shift, $counterId]) ?? 0),
        ];

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
            'bill_count' => $billCount,
            'sales' => $salesItems,
            'staff_sales' => $staffSales,
            'transfers' => $transfers,
            'units_generated' => $unitRows,
            'deleted_bills' => $deletedBills,
            'return_totals' => $returnTotals,
            'movement_totals' => $movementTotals,
            'stock_take' => $this->stockTake($db, $counterId, $shift),
            'requisitions' => $this->withNames($db, $this->withUserNames($db, $this->rows($db, 'SELECT ri.item_id, i.cat_id, r.issue_user, r.issue_status, r.rec_status, SUM(COALESCE(ri.issue_qty, ri.req_qty, 0)) qty FROM kp_counter_req r INNER JOIN kp_counter_req_items ri ON ri.req_id = r.req_id LEFT JOIN kp_items i ON i.item_id = ri.item_id WHERE r.req_shift = ? AND r.counter = ? GROUP BY ri.item_id, i.cat_id, r.issue_user, r.issue_status, r.rec_status ORDER BY r.issue_status ASC, i.cat_id ASC, ri.item_id ASC', [$shift, $counterId]))),
            'user_sales' => $this->rows($db, 'SELECT user_id, COUNT(sale_id) orders_count, SUM(amount) sales_total FROM kp_sales WHERE shift = ? AND counter = ? GROUP BY user_id ORDER BY sales_total DESC', [$shift, $counterId]),
            'returns' => $this->withNames($db, $this->withUserNames($db, $this->rows($db, 'SELECT user_id, item_id, confirm_by, confirm_status, SUM(qty) qty FROM kp_ret_items WHERE shift = ? AND stock_counter = ? GROUP BY user_id, item_id, confirm_by, confirm_status ORDER BY user_id ASC, confirm_status ASC, item_id ASC', [$shift, $counterId]), 'confirm_by')),
        ];
    }

    private function transferRows(PDO $db, string $shift, int $counterId): array
    {
        $rows = $this->rows($db, 'SELECT ti.trans_id, ti.item_id, SUM(ti.qty) qty, t.cntr_from, t.cntr_to, t.user_from, t.user_to, t.status FROM kp_counter_trans_items ti INNER JOIN kp_counter_trans t ON t.trans_id = ti.trans_id WHERE t.shift = ? AND (t.cntr_from = ? OR t.cntr_to = ?) GROUP BY ti.trans_id, ti.item_id, t.cntr_from, t.cntr_to, t.user_from, t.user_to, t.status ORDER BY ti.trans_id DESC, ti.item_id ASC', [$shift, $counterId, $counterId]);
        return $this->withNames($db, $this->withUserNames($db, $rows, 'user_from'), 'item_id');
    }

    private function stockTake(PDO $db, int $counterId, string $shift): array
    {
        $table = 'kp_cntr' . $counterId . '_stock_take';
        if (!$this->tableExists($db, $table)) return ['available' => false, 'taken' => false, 'ok_count' => 0, 'variance_count' => 0, 'variance_qty' => 0.0];
        $rows = $this->rows($db, "SELECT opening, counted FROM `{$table}` WHERE shift = ?", [$shift]);
        $result = ['available' => true, 'taken' => !empty($rows), 'ok_count' => 0, 'variance_count' => 0, 'variance_qty' => 0.0];
        foreach ($rows as $row) {
            $variance = (float)($row['opening'] ?? 0) - (float)($row['counted'] ?? 0);
            if (abs($variance) > 0.0001) { $result['variance_count']++; $result['variance_qty'] += abs($variance); }
            else $result['ok_count']++;
        }
        return $result;
    }

    private function tableExists(PDO $db, string $table): bool
    {
        $q = $db->prepare('SHOW TABLES LIKE ?');
        $q->execute([$table]);
        return (bool)$q->fetchColumn();
    }

    private function withNames(PDO $db, array $rows, string $idKey = 'item_id'): array
    {
        $cache = [];
        foreach ($rows as &$row) {
            $id = (int)($row[$idKey] ?? 0);
            if ($id < 1) continue;
            if (!array_key_exists($id, $cache)) {
                $q = $db->prepare('SELECT name FROM kp_items WHERE item_id = ? LIMIT 1');
                $q->execute([$id]);
                $cache[$id] = (string)($q->fetchColumn() ?: 'Item ' . $id);
            }
            $row['item_name'] = $cache[$id];
            $row['name'] = $cache[$id];
        }
        unset($row);
        return $rows;
    }

    private function withUserNames(PDO $db, array $rows, string $idKey = 'user_id'): array
    {
        $cache = [];
        foreach ($rows as &$row) {
            $id = (int)($row[$idKey] ?? 0);
            if ($id < 1) continue;
            if (!array_key_exists($id, $cache)) {
                $q = $db->prepare('SELECT username FROM kp_users WHERE user_id = ? LIMIT 1');
                $q->execute([$id]);
                $cache[$id] = (string)($q->fetchColumn() ?: 'User ' . $id);
            }
            $row['user_name'] = $cache[$id];
            $row['username'] = $cache[$id];
            if ($idKey === 'confirm_by') $row['approver_name'] = $cache[$id];
            if ($idKey === 'user_from') $row['from_user_name'] = $cache[$id];
        }
        unset($row);
        return $rows;
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
