<?php

if (!function_exists('inventory_repo_item_column_exists')) {
    function inventory_repo_item_column_exists($columnName)
    {
        global $connection;

        $columnName = mysqli_real_escape_string($connection, (string)$columnName);
        $result = mysqli_query($connection, "SHOW COLUMNS FROM kp_items LIKE '{$columnName}'");
        return $result && mysqli_num_rows($result) > 0;
    }
}

if (!function_exists('inventory_repo_pick_price_column')) {
    function inventory_repo_pick_price_column()
    {
        if (inventory_repo_item_column_exists('b_price')) {
            return 'b_price';
        }
        if (inventory_repo_item_column_exists('price')) {
            return 'price';
        }
        return '';
    }
}

if (!function_exists('inventory_repo_fetch_counters')) {
    function inventory_repo_fetch_counters()
    {
        global $connection;

        $rows = [];
        $result = mysqli_query($connection, "SELECT cntr_id, name FROM kp_counters WHERE del_status=2 ORDER BY cntr_id ASC");
        if (!$result) {
            return $rows;
        }

        while ($row = mysqli_fetch_assoc($result)) {
            $rows[] = [
                'id' => (int)$row['cntr_id'],
                'name' => (string)$row['name'],
                'qty_column' => 'cntr' . (int)$row['cntr_id'] . '_qty',
            ];
        }

        return $rows;
    }
}

if (!function_exists('inventory_repo_filter_usable_counters')) {
    function inventory_repo_filter_usable_counters($counters)
    {
        $usableCounters = [];
        foreach ($counters as $counter) {
            $column = preg_replace('/[^a-zA-Z0-9_]/', '', (string)($counter['qty_column'] ?? ''));
            if ($column === '') {
                continue;
            }
            $counter['qty_column'] = $column;
            $usableCounters[] = $counter;
        }
        return $usableCounters;
    }
}

if (!function_exists('inventory_repo_fetch_counter_stock_rows')) {
    function inventory_repo_fetch_counter_stock_rows($counters, $selectedCounterId = 0, $categoryId = 0, $subCategoryId = 0, $stockType = 0, $departmentId = 0)
    {
        global $connection;

        $usableCounters = inventory_repo_filter_usable_counters($counters);
        $selectedCounterId = (int)$selectedCounterId;
        $categoryId = (int)$categoryId;
        $subCategoryId = (int)$subCategoryId;
        $stockType = (int)$stockType;
        $departmentId = (int)$departmentId;
        $qtyColumns = [];
        $orParts = ["COALESCE(i.stock,2) IN (1,2,3)", "COALESCE(i.tot_status,2)=1"];

        foreach ($usableCounters as $counter) {
            $column = (string)$counter['qty_column'];
            $qtyColumns[] = "0 AS `{$column}`";
        }

        if ($selectedCounterId > 0) {
            $selectedColumn = 'cntr' . $selectedCounterId . '_qty';
            $selectedExists = false;
            foreach ($usableCounters as $counter) {
                if ((int)$counter['id'] === $selectedCounterId && (string)$counter['qty_column'] === $selectedColumn) {
                    $selectedExists = true;
                    break;
                }
            }
            if (!$selectedExists) {
                return [];
            }
            $orParts = ["COALESCE(i.stock,2) IN (1,2,3)", "COALESCE(i.tot_status,2)=1"];
        }

        $filters = [];
        if ($categoryId > 0) $filters[] = 'i.cat_id=' . $categoryId;
        if ($subCategoryId > 0) $filters[] = 'i.sub_id=' . $subCategoryId;
        if ($stockType > 0) $filters[] = 'i.stock=' . $stockType;
        if ($departmentId > 0) $filters[] = 'i.dpt=' . $departmentId;

        $priceColumn = inventory_repo_pick_price_column();
        $priceSql = $priceColumn !== '' ? "COALESCE(i.`{$priceColumn}`,0)" : "0";
        $selectQty = $qtyColumns ? ",\n                       " . implode(",\n                       ", $qtyColumns) : '';
        $whereExtras = $orParts ? ' AND (' . implode(' OR ', $orParts) . ')' : '';
        if ($filters) $whereExtras .= ' AND ' . implode(' AND ', $filters);

        $sql = "SELECT i.item_id,
                       i.name,
                       COALESCE(i.cat_id, 0) AS cat_id,
                       COALESCE(c.name, 'Uncategorised') AS cat_name,
                       COALESCE(i.sub_id, 0) AS sub_id,
                       COALESCE(s.name, '') AS sub_name,
                       COALESCE(i.dpt, 0) AS dpt,
                       COALESCE(i.stock, 2) AS stock_type,
                       {$priceSql} AS unit_cost
                       {$selectQty}
                FROM kp_items i
                LEFT JOIN kp_cat c ON c.cat_id = i.cat_id
                LEFT JOIN kp_sub_cat s ON s.sub_id = i.sub_id
                WHERE i.del_status=2{$whereExtras}
                ORDER BY c.name ASC, s.name ASC, i.name ASC";
        $result = mysqli_query($connection, $sql);

        $rows = [];
        if (!$result) {
            return $rows;
        }

        while ($row = mysqli_fetch_assoc($result)) {
            $counterQty = [];
            $totalQty = 0;
            $itemId = (int)$row['item_id'];
            $mapped = [];
            $mappedResult = mysqli_query($connection, "SELECT counter_id, qty, reorder_level FROM kp_counter_items WHERE item_id='{$itemId}' AND is_active=1");
            while ($mappedResult && $mappedRow = mysqli_fetch_assoc($mappedResult)) {
                $mapped[(int)$mappedRow['counter_id']] = [
                    'qty' => (float)($mappedRow['qty'] ?? 0),
                    'reorder' => (float)($mappedRow['reorder_level'] ?? 0),
                ];
            }
            if ($selectedCounterId > 0 && !isset($mapped[$selectedCounterId])) {
                continue;
            }
            foreach ($usableCounters as $counter) {
                $counterId = (int)$counter['id'];
                $qty = (float)($mapped[$counterId]['qty'] ?? 0);
                $totalQty += $qty;
                $counterQty[] = [
                    'counter_id' => $counterId,
                    'counter_name' => (string)$counter['name'],
                    'qty' => $qty,
                    'value' => $qty * (float)$row['unit_cost'],
                ];
            }

            $rows[] = [
                'item_id' => (int)$row['item_id'],
                'name' => (string)$row['name'],
                'cat_id' => (int)$row['cat_id'],
                'cat_name' => (string)$row['cat_name'],
                'sub_id' => (int)$row['sub_id'],
                'sub_name' => (string)$row['sub_name'],
                'dpt' => (int)$row['dpt'],
                'stock_type' => (int)$row['stock_type'],
                'unit_cost' => (float)$row['unit_cost'],
                'total_qty' => $totalQty,
                'total_value' => $totalQty * (float)$row['unit_cost'],
                'counter_qty' => $counterQty,
            ];
        }

        return $rows;
    }
}
