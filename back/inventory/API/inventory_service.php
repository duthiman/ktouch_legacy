<?php

if (!function_exists('inventory_service_stock_type_label')) {
    function inventory_service_stock_type_label($stockType)
    {
        $stockType = (int)$stockType;
        if ($stockType === 1) {
            return 'Inventory';
        }
        if ($stockType === 3) {
            return 'Service';
        }
        return 'Non Inventory';
    }
}

if (!function_exists('inventory_service_queue_request_print')) {
    function inventory_service_queue_request_print($req_id, $user_id, $counter)
    {
        global $connection;
        $req_id = (int)$req_id;
        $user_id = (int)$user_id;
        $counter = (int)$counter;
        if ($req_id < 1 || $user_id < 1 || $counter < 1 || !function_exists('kp_print_enqueue')) {
            return 0;
        }
        if (function_exists('ensure_chaba_print_system')) {
            ensure_chaba_print_system($connection);
        }
        return (int)kp_print_enqueue($connection, [
            'sale_id' => 0,
            'ref_id' => $req_id,
            'print_type' => 'gwitia',
            'label' => 'COUNTER ISSUE',
            'cntr_id' => $counter,
            'route_path' => 'render_req.php',
            'route_query' => 'id=' . $req_id . '&user_id=' . $user_id . '&cntr_id=' . $counter,
            'requested_by' => $user_id,
        ]);
    }
}

if (!function_exists('inventory_service_department_label')) {
    function inventory_service_department_label($departmentId)
    {
        $departmentId = (int)$departmentId;
        return $departmentId > 0 ? ('Department ' . $departmentId) : 'General';
    }
}

if (!function_exists('inventory_service_counter_stock_bootstrap')) {
    function inventory_service_counter_stock_bootstrap($selectedCounterId = 0, $categoryId = 0, $subCategoryId = 0, $stockType = 0, $departmentId = 0)
    {
        global $connection;
        $selectedCounterId = (int)$selectedCounterId;
        $counters = inventory_repo_filter_usable_counters(inventory_repo_fetch_counters());
        // Keep all counter quantity columns in the response. The selected
        // counter is a presentation/filter choice; restricting the payload
        // here makes the client reuse one counter's quantity for every view.
        $stockCounters = $counters;
        $rows = inventory_repo_fetch_counter_stock_rows($stockCounters, $selectedCounterId, $categoryId, $subCategoryId, $stockType, $departmentId);
        foreach ($rows as &$stockRow) {
            $itemId = (int)$stockRow['item_id'];
            $stockRow['counter_reorder'] = [];
            $stockRow['counter_stock'] = [];
            foreach ($stockCounters as $counter) {
                $counterId = (int)$counter['id'];
                $counterStockQuery = mysqli_query($connection, "SELECT qty, reorder_level FROM kp_counter_items WHERE counter_id='{$counterId}' AND item_id='{$itemId}' AND is_active=1 LIMIT 1");
                $counterStockRow = $counterStockQuery ? (mysqli_fetch_assoc($counterStockQuery) ?: []) : [];
                $qtyValue = (float)($counterStockRow['qty'] ?? 0);
                $reorderValue = (float)($counterStockRow['reorder_level'] ?? 0);
                $stockRow['counter_reorder'][(string)$counterId] = $reorderValue;
                $stockRow['counter_stock'][(string)$counterId] = [
                    'counter_id' => $counterId,
                    'qty' => $qtyValue,
                    'reorder' => $reorderValue,
                ];
            }
            foreach ($stockRow['counter_qty'] as &$counterQty) {
                $counterId = (string)(int)$counterQty['counter_id'];
                $counterQty['reorder'] = (float)($stockRow['counter_reorder'][$counterId] ?? 0);
            }
            unset($counterQty);
            if ($selectedCounterId > 0 && isset($stockRow['counter_stock'][(string)$selectedCounterId])) {
                $selectedStock = $stockRow['counter_stock'][(string)$selectedCounterId];
                $stockRow['selected_counter_id'] = $selectedCounterId;
                $stockRow['selected_counter_qty'] = (float)$selectedStock['qty'];
                $stockRow['selected_counter_reorder'] = (float)$selectedStock['reorder'];
            }
        }
        unset($stockRow);
        $categories = [];
        $subCategories = [];
        $departments = [];
        $stockTypes = [];
        $cards = [];

        foreach ($counters as $counter) {
            $cards[] = [
                'counter_id' => (int)$counter['id'],
                'counter_name' => (string)$counter['name'],
                'item_count' => 0,
                'total_qty' => 0,
                'total_value' => 0,
            ];
        }

        foreach ($rows as &$row) {
            $row['dpt_label'] = inventory_service_department_label($row['dpt'] ?? 0);
            $row['stock_type_label'] = inventory_service_stock_type_label($row['stock_type'] ?? 2);

            $categories[(int)$row['cat_id']] = [
                'id' => (int)$row['cat_id'],
                'name' => (string)$row['cat_name'],
            ];

            if ((int)$row['sub_id'] > 0) {
                $subCategories[(int)$row['sub_id']] = [
                    'id' => (int)$row['sub_id'],
                    'name' => (string)$row['sub_name'],
                    'cat_id' => (int)$row['cat_id'],
                ];
            }

            $departments[(int)$row['dpt']] = [
                'id' => (int)$row['dpt'],
                'name' => (string)$row['dpt_label'],
            ];

            $stockTypes[(int)$row['stock_type']] = [
                'id' => (int)$row['stock_type'],
                'name' => (string)$row['stock_type_label'],
            ];

            foreach ($row['counter_qty'] as $counterQty) {
                foreach ($cards as &$card) {
                    if ((int)$card['counter_id'] !== (int)$counterQty['counter_id']) {
                        continue;
                    }
                    if ((float)$counterQty['qty'] != 0.0) {
                        $card['item_count']++;
                    }
                    $card['total_qty'] += (float)$counterQty['qty'];
                    $card['total_value'] += (float)$counterQty['value'];
                    break;
                }
                unset($card);
            }
        }
        unset($row);

        return [
            'ok' => true,
            'message' => 'Counter stock loaded.',
            'counters' => $counters,
            'rows' => $rows,
            'categories' => array_values($categories),
            'sub_categories' => array_values($subCategories),
            'departments' => array_values($departments),
            'stock_types' => array_values($stockTypes),
            'cards' => $cards,
        ];
    }
}

if (!function_exists('inventory_service_take_items')) {
    function inventory_service_take_items()
    {
        global $connection;

        $rows = [];
        $result = mysqli_query($connection, "SELECT item_id, name FROM kp_items WHERE store_take=2 AND del_status=2 AND stock=1 ORDER BY name ASC");
        if ($result) {
            while ($row = mysqli_fetch_assoc($result)) {
                $rows[] = [
                    'item_id' => (int)$row['item_id'],
                    'name' => (string)$row['name'],
                ];
            }
        }

        return [
            'ok' => true,
            'message' => 'Take items loaded.',
            'items' => $rows,
        ];
    }
}

if (!function_exists('inventory_service_adjust_store_qty')) {
    function inventory_service_adjust_store_qty($item_id, $qty, $reason, $init, $user_id)
    {
        global $connection;

        $item_id = (int)$item_id;
        $qty = (float)$qty;
        $init = (float)$init;
        $user_id = (int)$user_id;
        $reason = mysqli_real_escape_string($connection, trim((string)$reason));
        $trans = 'STORE STOCK ADJUSTMENTS';

        if ($item_id < 1 || $qty === 0.0 && trim((string)$qty) === '') {
            return ['ok' => false, 'message' => 'Quantity is required.'];
        }
        if ($reason === '') {
            return ['ok' => false, 'message' => 'Reason is required.'];
        }

        $history = mysqli_query($connection, "INSERT INTO kp_store_hist(item_id,user_id,init,new,reason,trans) VALUES('{$item_id}','{$user_id}','{$init}','{$qty}','{$reason}','{$trans}')");
        if (!$history) {
            return ['ok' => false, 'message' => 'Could not save stock history.'];
        }

        $updated = mysqli_query($connection, "UPDATE kp_items SET store_qty='{$qty}' WHERE item_id='{$item_id}'");
        if (!$updated) {
            return ['ok' => false, 'message' => 'Could not adjust quantity.'];
        }

        return ['ok' => true, 'message' => 'Quantity adjusted successfully.', 'reload' => true];
    }
}

if (!function_exists('inventory_service_adjust_reorder_qty')) {
    function inventory_service_adjust_reorder_qty($item_id, $qty, $reason, $init, $user_id)
    {
        global $connection;

        $item_id = (int)$item_id;
        $qty = (float)$qty;
        $init = (float)$init;
        $user_id = (int)$user_id;
        $reason = mysqli_real_escape_string($connection, trim((string)$reason));
        $trans = 'STORE REORDER ADJUSTMENTS';

        if ($item_id < 1 || $qty === 0.0 && trim((string)$qty) === '') {
            return ['ok' => false, 'message' => 'Reorder level is required.'];
        }
        if ($reason === '') {
            return ['ok' => false, 'message' => 'Reason is required.'];
        }

        $history = mysqli_query($connection, "INSERT INTO kp_store_hist(item_id,user_id,init,new,reason,trans) VALUES('{$item_id}','{$user_id}','{$init}','{$qty}','{$reason}','{$trans}')");
        if (!$history) {
            return ['ok' => false, 'message' => 'Could not save reorder history.'];
        }

        $updated = mysqli_query($connection, "UPDATE kp_items SET store_reorder='{$qty}' WHERE item_id='{$item_id}'");
        if (!$updated) {
            return ['ok' => false, 'message' => 'Could not adjust reorder level.'];
        }

        return ['ok' => true, 'message' => 'Reorder level adjusted successfully.', 'reload' => true];
    }
}

if (!function_exists('inventory_service_adjust_counter_reorder_qty')) {
    function inventory_service_adjust_counter_reorder_qty($item_id, $counter_id, $qty, $reason, $user_id)
    {
        global $connection;

        $item_id = (int)$item_id;
        $counter_id = (int)$counter_id;
        $qty = (float)$qty;
        if ($item_id < 1 || $counter_id < 1 || $qty < 0) {
            return ['ok' => false, 'message' => 'Invalid item, counter or reorder level.'];
        }

        $updated = mysqli_query($connection, "INSERT INTO kp_counter_items(counter_id,item_id,reorder_level,is_active,created_at,updated_at) VALUES('{$counter_id}','{$item_id}','{$qty}',1,CURRENT_TIMESTAMP,CURRENT_TIMESTAMP) ON DUPLICATE KEY UPDATE reorder_level=VALUES(reorder_level), is_active=1, updated_at=CURRENT_TIMESTAMP");
        if (!$updated) {
            return ['ok' => false, 'message' => 'Could not save counter reorder level.'];
        }

        return ['ok' => true, 'message' => 'Counter reorder level updated.'];
    }
}

if (!function_exists('inventory_service_take_stock')) {
    function inventory_service_take_stock($item_id, $qty, $shift, $user_id)
    {
        global $connection;

        $item_id = (int)$item_id;
        $qty = (float)$qty;
        $shift = mysqli_real_escape_string($connection, trim((string)$shift));
        $user_id = (int)$user_id;

        if ($item_id < 1) {
            return ['ok' => false, 'message' => 'Select an item first.'];
        }
        if ($qty <= 0) {
            return ['ok' => false, 'message' => 'Quantity is required.'];
        }

        $closingStock = mysqli_query($connection, "SELECT store_qty FROM kp_items WHERE item_id='{$item_id}'");
        $open = $closingStock ? mysqli_fetch_assoc($closingStock) : null;
        $closing = $open ? (float)$open['store_qty'] : 0;

        $updated = mysqli_query($connection, "UPDATE kp_items SET store_take=1,store_qty='{$qty}' WHERE item_id='{$item_id}'");
        if (!$updated) {
            return ['ok' => false, 'message' => 'Could not update stock take quantity.'];
        }

        $saved = mysqli_query($connection, "INSERT INTO kp_store_stock_take(item_id,user_id,closing,counted,shift) VALUES('{$item_id}','{$user_id}','{$closing}','{$qty}','{$shift}')");
        if (!$saved) {
            return ['ok' => false, 'message' => 'Could not save taken stock.'];
        }

        $items = inventory_service_take_items();
        return ['ok' => true, 'message' => 'Stock taken successfully.', 'items' => $items['items'] ?? []];
    }
}

if (!function_exists('inventory_service_issue_request_item')) {
    function inventory_service_issue_request_item($req_id, $item_id, $qty, $issue_qty, $user_id)
    {
        global $connection;

        $req_id = (int)$req_id;
        $item_id = (int)$item_id;
        $qty = (float)$qty;
        $issue_qty = (float)$issue_qty;
        $user_id = (int)$user_id;

        $counter = (int)get_column('kp_counter_req', 'counter', "req_id='{$req_id}'");
        if ($counter < 1) {
            return ['ok' => false, 'message' => 'The destination counter is not available.'];
        }

        $stockResult = mysqli_query($connection, "SELECT store_qty FROM kp_items WHERE item_id='{$item_id}'");
        $stockRow = $stockResult ? mysqli_fetch_assoc($stockResult) : null;
        $storeQty = $stockRow ? (float)$stockRow['store_qty'] : 0;

        if ($req_id < 1 || $item_id < 1 || $qty <= 0) {
            return ['ok' => false, 'message' => 'Request item details are incomplete.'];
        }

        if ($qty > $storeQty) {
            return ['ok' => false, 'message' => 'You cannot issue more than you have. Requested: ' . $qty . ' | Available: ' . $storeQty];
        }

        $track = mysqli_query($connection, "INSERT INTO kp_store_stock_track(item_id,req_id,user_id,qty,status,shift) VALUES('{$item_id}','{$req_id}','{$user_id}','{$qty}','OUT',CURRENT_DATE)");
        if (!$track) {
            return ['ok' => false, 'message' => 'Could not track issued stock.'];
        }

        $department = (int)get_column('kp_items', 'dpt', "item_id='{$item_id}'");
        $counterTrack = mysqli_query($connection, "INSERT INTO kp_stock_track(item_id,qty,user_id,req_id,`trans`,trans_id,status,stock_counter,shift,dpt) VALUES('{$item_id}','{$qty}','{$user_id}','{$req_id}','REQ','{$req_id}','IN','{$counter}',CURRENT_DATE,'{$department}')");
        if (!$counterTrack) {
            return ['ok' => false, 'message' => 'Could not track stock received by the counter.'];
        }

        $adjusted = mysqli_query($connection, "UPDATE kp_items SET store_qty = store_qty-'{$qty}' WHERE item_id='{$item_id}' AND store_qty >= '{$qty}'");
        if (!$adjusted) {
            return ['ok' => false, 'message' => 'Could not update store quantity.'];
        }
        if (!mysqli_query($connection, "INSERT INTO kp_counter_items (counter_id,item_id,qty,is_active) VALUES ('{$counter}','{$item_id}','{$qty}',1) ON DUPLICATE KEY UPDATE qty=qty+VALUES(qty),is_active=1")) {
            return ['ok' => false, 'message' => 'Could not update destination counter inventory.'];
        }

        $updated = mysqli_query($connection, "UPDATE kp_counter_req_items SET issue_qty='{$issue_qty}',rec_qty='{$issue_qty}',issue_shift=CURRENT_DATE,issue_user='{$user_id}',issue_status=1,rec_status=1 WHERE req_id='{$req_id}' AND item_id='{$item_id}'");
        if (!$updated) {
            return ['ok' => false, 'message' => 'Could not mark request item as issued.'];
        }

        return ['ok' => true, 'message' => $qty . ' ' . get_item($item_id) . ' issued successfully.', 'reload' => true];
    }
}

if (!function_exists('inventory_service_issue_all_request')) {
    function inventory_service_issue_all_request($req_id, $user_id)
    {
        global $connection;

        $req_id = (int)$req_id;
        $user_id = (int)$user_id;

        if ($req_id < 1 || $user_id < 1) {
            return ['ok' => false, 'message' => 'Request could not be issued.'];
        }

        $counter = function_exists('get_column') ? get_column('kp_counter_req', 'counter', "req_id='{$req_id}'") : 0;
        $issueCreatedCol = mysqli_query($connection, "SHOW COLUMNS FROM kp_counter_req LIKE 'issue_created_at'");
        if ($issueCreatedCol && mysqli_num_rows($issueCreatedCol) < 1) {
            mysqli_query($connection, "ALTER TABLE kp_counter_req ADD COLUMN issue_created_at TIMESTAMP NULL DEFAULT NULL");
        }

        $check = mysqli_query($connection, "SELECT item_id, req_qty FROM kp_counter_req_items WHERE req_id='{$req_id}' AND issue_status=2");
        if ($check) {
            while ($row = mysqli_fetch_assoc($check)) {
                $itemId = (int)$row['item_id'];
                $reqQty = (float)$row['req_qty'];
                $portionStatus = function_exists('get_column') ? (int)get_column('kp_items', 'port_status', "item_id='{$itemId}'") : 2;
                $portions = function_exists('get_column') ? (float)get_column('kp_items', 'portions', "item_id='{$itemId}'") : 0;
                $issueQty = $portionStatus === 1 && $portions > 0 ? $reqQty * $portions : $reqQty;
                $single = inventory_service_issue_request_item($req_id, $itemId, $reqQty, $issueQty, $user_id);
                if (!$single['ok']) {
                    return $single;
                }
            }
        }

        $pending = mysqli_query($connection, "SELECT item_id FROM kp_counter_req_items WHERE issue_status=2 AND req_id='{$req_id}'");
        if ($pending && mysqli_num_rows($pending) < 1) {
            mysqli_query($connection, "UPDATE kp_counter_req SET
                issue_status=3,
                rec_status=1,
                issue_user='{$user_id}',
                rec_user='{$user_id}',
                issue_shift=CURRENT_DATE,
                rec_shift=CURRENT_DATE,
                issue_created_at=CURRENT_TIMESTAMP,
                rec_created_at=CURRENT_TIMESTAMP,
                print_cntr_id='{$counter}',
                issued_by='{$user_id}',
                received_by='{$user_id}'
                WHERE req_id='{$req_id}'");
            inventory_service_queue_request_print($req_id, $user_id, (int)$counter);
            if (function_exists('logs') && function_exists('get_counter')) {
                $action = 'ISSUED|-|-|REQUISITION NO|' . $req_id . '| FOR ' . get_counter($counter) . ' COUNTER';
                logs($action, '../logs');
            }
            return ['ok' => true, 'message' => 'All request items issued successfully.', 'redirect' => 'counter_req.php?msg=2'];
        }

        return ['ok' => true, 'message' => 'Some items are still pending.', 'redirect' => 'req_view.php?cntr=' . (int)$counter . '&req_id=' . $req_id];
    }
}

if (!function_exists('inventory_service_cancel_request')) {
    function inventory_service_cancel_request($req_id, $reason, $user_id)
    {
        global $connection;

        $req_id = (int)$req_id;
        $user_id = (int)$user_id;
        $reason = mysqli_real_escape_string($connection, trim((string)$reason));

        if ($req_id < 1) {
            return ['ok' => false, 'message' => 'Request is required.'];
        }
        if ($reason === '') {
            return ['ok' => false, 'message' => 'Reason is required.'];
        }

        $counter = function_exists('get_column') ? get_column('kp_counter_req', 'counter', "req_id='{$req_id}'") : 0;
        $shift = function_exists('get_column') ? get_column('kp_shift', 'shift', "1") : '';
        $items = mysqli_query($connection, "SELECT item_id FROM kp_counter_req_items WHERE req_id='{$req_id}'");
        if ($items) {
            while ($row = mysqli_fetch_assoc($items)) {
                $itemId = (int)$row['item_id'];
                mysqli_query($connection, "INSERT INTO kp_counter_req_del_items(item_id,user_id,req_id,reason,shift,counter) VALUES('{$itemId}','{$user_id}','{$req_id}','{$reason}','{$shift}','{$counter}')");
                mysqli_query($connection, "DELETE FROM kp_counter_req_items WHERE req_id='{$req_id}' AND item_id='{$itemId}'");
            }
        }

        mysqli_query($connection, "INSERT INTO kp_counter_req_del(req_id,user_id,shift,reason,counter) VALUES('{$req_id}','{$user_id}','{$shift}','{$reason}','{$counter}')");
        mysqli_query($connection, "DELETE FROM kp_counter_req WHERE req_id='{$req_id}'");

        if (function_exists('logs') && function_exists('get_counter')) {
            $action = 'CANCELLED|-|-|REQUEST NO|' . $req_id . '| FOR' . get_counter($counter) . 'COUNTER';
            logs($action, '../../logs');
        }

        return ['ok' => true, 'message' => 'Request cancelled successfully.', 'redirect' => 'counter_req.php?msg=2'];
    }
}

if (!function_exists('inventory_service_remove_request_item')) {
    function inventory_service_remove_request_item($req_id, $item_id, $reason, $user_id)
    {
        global $connection;

        $req_id = (int)$req_id;
        $item_id = (int)$item_id;
        $user_id = (int)$user_id;
        $reason = mysqli_real_escape_string($connection, trim((string)$reason));

        if ($reason === '') {
            return ['ok' => false, 'message' => 'Reason is required.'];
        }

        $counter = function_exists('get_column') ? get_column('kp_counter_req', 'counter', "req_id='{$req_id}'") : 0;
        $shift = function_exists('get_column') ? get_column('kp_shift', 'shift', "1") : '';
        $track = mysqli_query($connection, "INSERT INTO kp_counter_req_del_items(item_id,user_id,req_id,reason,shift,counter) VALUES('{$item_id}','{$user_id}','{$req_id}','{$reason}','{$shift}','{$counter}')");
        if (!$track) {
            return ['ok' => false, 'message' => 'Could not track the removed item.'];
        }

        $deleted = mysqli_query($connection, "DELETE FROM kp_counter_req_items WHERE item_id='{$item_id}' AND req_id='{$req_id}'");
        if (!$deleted) {
            return ['ok' => false, 'message' => 'Could not remove the item.'];
        }

        return ['ok' => true, 'message' => 'Request item removed successfully.', 'reload' => true];
    }
}
