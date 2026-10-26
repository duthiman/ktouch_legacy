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

function counter_summary_repo_document(mysqli $connection, int $summaryId, int $counterId, int $userId, string $shift, array $counter, array $sales): array
{
    $esc = static fn(string $v): string => mysqli_real_escape_string($connection, $v);
    $rows = static function (string $sql) use ($connection): array {
        $q = mysqli_query($connection, $sql); if (!$q) return [];
        $out = []; while ($r = mysqli_fetch_assoc($q)) $out[] = $r; return $out;
    };
    $itemName = static function (int $id) use ($connection): string {
        $id = (int)$id;
        foreach (['kp_items.name','kp_items.item_name','kp_inventory_master.item_name','kp_inventory_items.item_name'] as $source) {
            [$table,$column] = explode('.', $source, 2);
            $q = mysqli_query($connection, "SELECT {$column} FROM {$table} WHERE item_id={$id} LIMIT 1");
            if ($q && ($name = trim((string)(mysqli_fetch_assoc($q)[$column] ?? ''))) !== '') return $name;
        }
        return $id > 0 ? 'Item ' . $id : '';
    };
    $nameRows = static function (array $items) use ($itemName): array {
        foreach ($items as &$row) { $name = $itemName((int)($row['item_id'] ?? 0)); $row['item_name'] = $name; $row['name'] = $name; }
        unset($row); return $items;
    };
    $salesRows = $nameRows($rows("SELECT si.item_id, i.cat_id, SUM(si.qty) qty, SUM(si.total) amount FROM kp_sale_items si LEFT JOIN kp_items i ON i.item_id=si.item_id WHERE si.shift='{$esc($shift)}' AND si.stock_counter={$counterId} GROUP BY si.item_id,i.cat_id ORDER BY i.cat_id,amount DESC"));
    foreach ($salesRows as &$row) $row['category_name'] = 'Category ' . (int)($row['cat_id'] ?? 0);
    unset($row);
    $staff = $rows("SELECT user_id,COUNT(DISTINCT sale_id) orders_count,SUM(total) sales_total FROM kp_sale_items WHERE shift='{$esc($shift)}' AND stock_counter={$counterId} GROUP BY user_id ORDER BY sales_total DESC");
    $userSales = $rows("SELECT user_id,COUNT(sale_id) orders_count,SUM(amount) sales_total FROM kp_sales WHERE shift='{$esc($shift)}' AND counter={$counterId} GROUP BY user_id ORDER BY sales_total DESC");
    $returns = $nameRows($rows("SELECT user_id,item_id,confirm_by,confirm_status,SUM(qty) qty FROM kp_ret_items WHERE shift='{$esc($shift)}' AND stock_counter={$counterId} GROUP BY user_id,item_id,confirm_by,confirm_status ORDER BY user_id,confirm_status,item_id"));
    $transfers = $nameRows($rows("SELECT ti.item_id,SUM(ti.qty) qty,t.cntr_from,t.cntr_to,t.user_from,t.user_to,t.status FROM kp_counter_trans_items ti INNER JOIN kp_counter_trans t ON t.trans_id=ti.trans_id WHERE t.shift='{$esc($shift)}' AND (t.cntr_from={$counterId} OR t.cntr_to={$counterId}) GROUP BY ti.item_id,t.cntr_from,t.cntr_to,t.user_from,t.user_to,t.status"));
    $units = $nameRows($rows("SELECT item_id,user_id,SUM(qty) qty FROM kp_stock_track WHERE shift='{$esc($shift)}' AND stock_counter={$counterId} AND `trans`='UNIT_GEN' GROUP BY item_id,user_id"));
    $requisitions = $nameRows($rows("SELECT ri.item_id,SUM(COALESCE(ri.issue_qty,ri.req_qty,0)) qty,r.issue_user,r.issue_status,r.rec_status FROM kp_counter_req r INNER JOIN kp_counter_req_items ri ON ri.req_id=r.req_id WHERE r.req_shift='{$esc($shift)}' AND r.counter={$counterId} GROUP BY ri.item_id,r.issue_user,r.issue_status,r.rec_status"));
    $deleted = $rows("SELECT * FROM kp_del_orders WHERE shift='{$esc($shift)}'");
    return [
        'summary_id'=>$summaryId,'counter_id'=>$counterId,'counter_name'=>(string)($counter['name'] ?? ''),'shift'=>$shift,
        'summary'=>$sales,'bill_count'=>(int)($sales['bills'] ?? 0),'sales'=>$salesRows,'staff_sales'=>$staff,
        'user_sales'=>$userSales,'returns'=>$returns,'transfers'=>$transfers,'units_generated'=>$units,
        'requisitions'=>$requisitions,'deleted_bills'=>$deleted,'return_totals'=>[],'movement_totals'=>[],
        'stock_take'=>[]
    ];
}
