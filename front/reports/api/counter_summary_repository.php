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
        try { $q = mysqli_query($connection, $sql); } catch (\Throwable $e) { return []; }
        if (!$q) return [];
        $out = []; while ($r = mysqli_fetch_assoc($q)) $out[] = $r; return $out;
    };
    $itemName = static function (int $id) use ($connection): string {
        $id = (int)$id;
        foreach (['kp_items.name','kp_items.item_name','kp_inventory_master.item_name','kp_inventory_items.item_name'] as $source) {
            [$table,$column] = explode('.', $source, 2);
            try { $q = mysqli_query($connection, "SELECT {$column} FROM {$table} WHERE item_id={$id} LIMIT 1"); } catch (\Throwable $e) { $q = false; }
            if ($q && ($name = trim((string)(mysqli_fetch_assoc($q)[$column] ?? ''))) !== '') return $name;
        }
        return $id > 0 ? 'Item ' . $id : '';
    };
    $nameRows = static function (array $items) use ($itemName): array {
        foreach ($items as &$row) { $name = $itemName((int)($row['item_id'] ?? 0)); $row['item_name'] = $name; $row['name'] = $name; }
        unset($row); return $items;
    };
    $salesRows = $nameRows($rows("SELECT si.item_id, i.cat_id, SUM(si.qty) qty, SUM(si.total) amount FROM kp_sale_items si LEFT JOIN kp_items i ON i.item_id=si.item_id WHERE si.shift='{$esc($shift)}' AND si.stock_counter={$counterId} GROUP BY si.item_id,i.cat_id ORDER BY i.cat_id,amount DESC"));
    foreach ($salesRows as &$row) {
        $catId = (int)($row['cat_id'] ?? 0); $row['category_name'] = 'Uncategorised';
        if ($catId > 0) { $cat = $rows("SELECT name FROM kp_cat WHERE cat_id={$catId} LIMIT 1"); if (!empty($cat[0]['name'])) $row['category_name'] = $cat[0]['name']; }
    }
    unset($row);
    $staff = $rows("SELECT user_id,COUNT(DISTINCT sale_id) orders_count,SUM(total) sales_total FROM kp_sale_items WHERE shift='{$esc($shift)}' AND stock_counter={$counterId} GROUP BY user_id ORDER BY sales_total DESC");
    $userSales = $rows("SELECT user_id,COUNT(sale_id) orders_count,SUM(amount) sales_total FROM kp_sales WHERE shift='{$esc($shift)}' AND counter={$counterId} GROUP BY user_id ORDER BY sales_total DESC");
    $returns = $nameRows($rows("SELECT user_id,item_id,confirm_by,confirm_status,SUM(qty) qty FROM kp_ret_items WHERE shift='{$esc($shift)}' AND stock_counter={$counterId} GROUP BY user_id,item_id,confirm_by,confirm_status ORDER BY user_id,confirm_status,item_id"));
    $transfers = $nameRows($rows("SELECT ti.item_id,SUM(ti.qty) qty,t.cntr_from,t.cntr_to,t.user_from,t.user_to,t.status FROM kp_counter_trans_items ti INNER JOIN kp_counter_trans t ON t.trans_id=ti.trans_id WHERE t.shift='{$esc($shift)}' AND (t.cntr_from={$counterId} OR t.cntr_to={$counterId}) GROUP BY ti.item_id,t.cntr_from,t.cntr_to,t.user_from,t.user_to,t.status"));
    $units = $nameRows($rows("SELECT item_id,user_id,SUM(qty) qty FROM kp_stock_track WHERE shift='{$esc($shift)}' AND stock_counter={$counterId} AND `trans`='UNIT_GEN' GROUP BY item_id,user_id"));
    $requisitions = $nameRows($rows("SELECT ri.item_id,SUM(COALESCE(ri.issue_qty,ri.req_qty,0)) qty,r.issue_user,r.issue_status,r.rec_status FROM kp_counter_req r INNER JOIN kp_counter_req_items ri ON ri.req_id=r.req_id WHERE r.req_shift='{$esc($shift)}' AND r.counter={$counterId} GROUP BY ri.item_id,r.issue_user,r.issue_status,r.rec_status"));
    $deleted = $rows("SELECT * FROM kp_del_orders WHERE shift='{$esc($shift)}'");
    $opening = $rows("SELECT opening,open_by,closing,close_by FROM kp_day_shift WHERE shift='{$esc($shift)}' ORDER BY shift_id DESC LIMIT 1");
    $opening = $opening[0] ?? [];
    $userNames = static function (array $items) use ($rows): array {
        foreach ($items as &$row) foreach (['user_id'=>'user_name','issue_user'=>'issuer_name','confirm_by'=>'approver_name','user_from'=>'from_user_name','user_to'=>'to_user_name'] as $idKey=>$nameKey) {
            $id = (int)($row[$idKey] ?? 0); if ($id < 1) continue; $u = $rows("SELECT username FROM kp_users WHERE user_id={$id} LIMIT 1"); $row[$nameKey] = (string)($u[0]['username'] ?? ('User '.$id));
        }
        unset($row); return $items;
    };
    $staff = $userNames($staff); $userSales = $userNames($userSales); $returns = $userNames($returns); $transfers = $userNames($transfers); $units = $userNames($units); $requisitions = $userNames($requisitions);
    return [
        'summary_id'=>$summaryId,'counter_id'=>$counterId,'counter_name'=>(string)($counter['name'] ?? ''),'shift'=>$shift,
        'summary'=>$sales,'bill_count'=>(int)($sales['bills'] ?? 0),'sales'=>$salesRows,'staff_sales'=>$staff,
        'user_sales'=>$userSales,'returns'=>$returns,'transfers'=>$transfers,'units_generated'=>$units,
        'requisitions'=>$requisitions,'deleted_bills'=>$deleted,'return_totals'=>[],'movement_totals'=>[],
        'stock_take'=>[],'opening_staff_id'=>(int)($opening['open_by'] ?? 0),'opening_at'=>(string)($opening['opening'] ?? ''),'closing_staff_id'=>(int)($opening['close_by'] ?? $userId),'closing_at'=>(string)($opening['closing'] ?? date('Y-m-d H:i:s'))
    ];
}

function shift_summary_repo_document(mysqli $connection, string $shift, int $userId): array
{
    $esc = mysqli_real_escape_string($connection, $shift);
    $rows = static function (string $sql) use ($connection): array { try { $q=mysqli_query($connection,$sql); } catch (\Throwable $e) { return []; } if(!$q)return[];$o=[];while($r=mysqli_fetch_assoc($q))$o[]=$r;return$o; };
    $totals = $rows("SELECT COUNT(sale_id) bills,COALESCE(SUM(amount),0) total FROM kp_sales WHERE shift='{$esc}'");
    $counterSales = $rows("SELECT counter,COUNT(sale_id) bills,COALESCE(SUM(amount),0) total FROM kp_sales WHERE shift='{$esc}' GROUP BY counter ORDER BY counter");
    $payments = $rows("SELECT pay_mode_id,COALESCE(SUM(amount),0) amount,COUNT(sale_id) bills FROM kp_payments WHERE shift='{$esc}' GROUP BY pay_mode_id");
    $waiters = $rows("SELECT user_id,COUNT(sale_id) bills,COALESCE(SUM(amount),0) total FROM kp_sales WHERE shift='{$esc}' GROUP BY user_id ORDER BY total DESC");
    $credit = $rows("SELECT sale_id,customer,amount,datime FROM kp_sales WHERE shift='{$esc}' AND pay_status=2");
    $day = $rows("SELECT opening,open_by,closing,close_by FROM kp_day_shift WHERE shift='{$esc}' ORDER BY shift_id DESC LIMIT 1");
    return ['payload_version'=>1,'document_type'=>'SSP','title'=>'SHIFT SUMMARY PRINT','header'=>['shift'=>$shift,'shift_date'=>$shift,'print_time'=>date('Y-m-d H:i:s'),'opening_at'=>$day[0]['opening']??'','opening_staff_id'=>$day[0]['open_by']??0,'closing_at'=>$day[0]['closing']??date('Y-m-d H:i:s'),'closing_staff_id'=>$day[0]['close_by']??$userId],'total_sales'=>$totals[0]??['bills'=>0,'total'=>0],'counter_sales'=>$counterSales,'cashier_collections'=>$payments,'customer_credit_bills'=>$credit,'customer_credit_payments'=>[],'waiter_totals'=>$waiters];
}
