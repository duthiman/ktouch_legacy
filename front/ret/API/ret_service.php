<?php

if (!function_exists('ret_service_bootstrap')) {
    function ret_service_bootstrap($connection, $sale_id, $user_id = 0)
    {
        if (!ret_repo_is_authorized($user_id)) {
            return [
                'ok' => false,
                'message' => 'Unauthorized.',
                'status' => 401,
            ];
        }

        $context = ret_repo_get_page_context($sale_id, $user_id);
        $sale = ret_repo_get_sale_rows($connection, $sale_id);
        $pending_returns = ret_repo_get_pending_returns($connection, $sale_id);
        $sale_count = (int)($sale['totals']['count'] ?? 0);

        return [
            'ok' => true,
            'message' => 'Return page loaded.',
            'context' => $context,
            'sale' => $sale,
            'pending_returns' => $pending_returns,
            'single_item_void' => [
                'should_prompt' => $sale_count === 1,
                'message' => $sale_count === 1
                    ? 'This sale has only one item. The waiter should void the whole sale instead of raising a return.'
                    : '',
                'void_url' => '../view/my_sales.php',
            ],
        ];
    }
}

if (!function_exists('ret_service_health')) {
    function ret_service_health($sale_id, $user_id = 0)
    {
        return [
            'ok' => true,
            'message' => 'Return API health check passed.',
            'context' => ret_repo_get_page_context($sale_id, $user_id),
        ];
    }
}

if (!function_exists('ret_service_return_modal')) {
    function ret_service_return_modal($connection, $sale_id, $item_id, $counter, $user_id = 0)
    {
        if (!ret_repo_is_authorized($user_id)) {
            return ['ok' => false, 'message' => 'Unauthorized.', 'status' => 401];
        }

        $row = ret_repo_get_sale_item($connection, $sale_id, $item_id, $counter);
        if (!$row) {
            return ['ok' => false, 'message' => 'Sale item not found.', 'status' => 404];
        }

        return [
            'ok' => true,
            'message' => 'Return item loaded.',
            'item' => [
                'sale_id' => (int)$sale_id,
                'item_id' => (int)$item_id,
                'item_name' => get_column('kp_items', 'name', "item_id='" . (int)$item_id . "'"),
                'ordered_qty' => (float)$row['qty'],
                'counter' => (int)$counter,
                'counter_name' => get_counter((int)$counter),
            ],
        ];
    }
}

if (!function_exists('ret_service_add_return')) {
    function ret_service_add_return($connection, $sale_id, $item_id, $counter, $qty, $user_id = 0)
    {
        if (!ret_repo_is_authorized($user_id)) {
            return ['ok' => false, 'message' => 'Unauthorized.', 'status' => 401];
        }

        $qty = (float)$qty;
        $sale_item = ret_repo_get_sale_item($connection, $sale_id, $item_id, $counter);
        if (!$sale_item) {
            return ['ok' => false, 'message' => 'Sale item not found.', 'status' => 404];
        }

        $ordered_qty = (float)$sale_item['qty'];
        $price = (float)$sale_item['price'];
        $current_ret_status = (int)($sale_item['ret_status'] ?? 0);

        if ($current_ret_status === 1) {
            return ['ok' => false, 'message' => 'This item was rejected for return and cannot be returned again on this order.', 'status' => 409];
        }

        if ($qty <= 0 || $qty > $ordered_qty) {
            return ['ok' => false, 'message' => 'Invalid return quantity.', 'status' => 422];
        }

        $shift = get_column('kp_shift', 'shift', '1');
        $amount = $qty * $price;
        $saved = ret_repo_insert_return_item($connection, [
            'sale_id' => $sale_id,
            'item_id' => $item_id,
            'oqty' => $ordered_qty,
            'qty' => $qty,
            'user_id' => $user_id,
            'shift' => $shift,
            'amount' => $amount,
            'sale_counter' => (int)$sale_item['counter'],
            'stock_counter' => (int)$sale_item['stock_counter'],
        ]);

        if (!$saved) {
            return ['ok' => false, 'message' => 'Could not raise return.', 'status' => 500];
        }

        ret_repo_update_sale_item_ret_status($connection, $sale_id, $item_id, $counter, 1);
        ret_log_action('RETURNED|' . $qty . '|' . get_item($item_id) . '|FROM ORDER NO|' . (int)$sale_id);

        return ret_service_bootstrap($connection, $sale_id, $user_id);
    }
}

if (!function_exists('ret_service_delete_return')) {
    function ret_service_delete_return($connection, $return_id, $user_id = 0)
    {
        if (!ret_repo_is_authorized($user_id)) {
            return ['ok' => false, 'message' => 'Unauthorized.', 'status' => 401];
        }

        $return_row = ret_repo_get_return_item($connection, $return_id);
        if (!$return_row) {
            return ['ok' => false, 'message' => 'Return item not found.', 'status' => 404];
        }

        $sale_id = (int)$return_row['sale_id'];
        $item_id = (int)$return_row['item_id'];
        $counter = (int)$return_row['stock_counter'];

        if (!ret_repo_delete_return_item($connection, $return_id)) {
            return ['ok' => false, 'message' => 'Could not remove return item.', 'status' => 500];
        }

        ret_repo_update_sale_item_ret_status($connection, $sale_id, $item_id, $counter, 2);
        ret_log_action('DELETED RETURN|' . $return_id . '|' . get_item($item_id) . '|FROM ORDER NO|' . $sale_id);

        return ret_service_bootstrap($connection, $sale_id, $user_id);
    }
}

if (!function_exists('ret_service_finish_return')) {
    function ret_service_finish_return($connection, $sale_id, $user_id = 0)
    {
        if (!ret_repo_is_authorized($user_id)) {
            return ['ok' => false, 'message' => 'Unauthorized.', 'status' => 401];
        }

        $pending_returns = ret_repo_get_pending_returns($connection, $sale_id);
        if (count($pending_returns) < 1) {
            return ['ok' => false, 'message' => 'There are no selected returns to finish.', 'status' => 422];
        }

        $requires_cashier_approval = kp_return_requires_cashier_approval($connection);
        $done = ret_repo_finish_sale_returns($connection, $sale_id, $requires_cashier_approval);
        if (!$done) {
            return ['ok' => false, 'message' => 'Could not finish return.', 'status' => 500];
        }

        ret_log_action('FINISHED RETURN|-|-|ORDER NO|' . (int)$sale_id);

        return [
            'ok' => true,
            'message' => 'Return has been sent for approval.',
            'redirect' => '../dash/dash.php',
        ];
    }
}

if (!function_exists('ret_queue_v1_prints')) {
    function ret_queue_v1_prints($connection, $sale_id, $return_id, $user_id, $return_counter, $sale_counter)
    {
        if (!function_exists('bill_service_queue_snapshot_job')) return [];
        $sale_id = (int)$sale_id;
        $return_id = (int)$return_id;
        $user_id = (int)$user_id;
        $return_counter = (int)$return_counter;
        $sale_counter = (int)$sale_counter;
        $queued = [];

        $return_rows = [];
        $query = mysqli_query($connection, "SELECT item_id, qty FROM kp_ret_items WHERE sale_id='$sale_id' AND confirm_status=1 ORDER BY id ASC");
        if ($query) while ($row = mysqli_fetch_assoc($query)) {
            $item_id = (int)$row['item_id'];
            $qty = (float)$row['qty'];
            $price = (float)get_column('kp_sale_items', 'price', "sale_id='$sale_id' AND item_id='$item_id' LIMIT 1");
            $return_rows[] = ['item_id'=>$item_id, 'name'=>get_item($item_id), 'qty'=>$qty, 'price'=>$price, 'total'=>$qty * $price];
        }
        if ($return_rows && $return_counter > 0) {
            $queue_id = bill_service_queue_snapshot_job($sale_id, $user_id, $return_counter, 'RET', $return_rows, $return_counter);
            if ($queue_id > 0) $queued[] = $queue_id;
        }

        if ($sale_counter > 0) {
            $sale = ret_repo_get_sale_rows($connection, $sale_id);
            $bill_rows = [];
            foreach (($sale['rows'] ?? []) as $row) {
                if ((float)($row['qty'] ?? 0) <= 0) continue;
                $bill_rows[] = [
                    'item_id'=>(int)($row['item_id'] ?? 0),
                    'name'=>(string)($row['item_name'] ?? ''),
                    'qty'=>(float)($row['qty'] ?? 0),
                    'price'=>(float)($row['price'] ?? 0),
                    'total'=>(float)($row['total'] ?? 0),
                ];
            }
            if ($bill_rows) {
                $queue_id = bill_service_queue_snapshot_job($sale_id, $user_id, $sale_counter, 'BILL', $bill_rows, $sale_counter);
                if ($queue_id > 0) $queued[] = $queue_id;
            }
        }
        return $queued;
    }
}

if (!function_exists('ret_service_approval_list')) {
    function ret_service_approval_list($connection, $user_id = 0)
    {
        if (!ret_repo_is_authorized($user_id)) {
            return ['ok' => false, 'message' => 'Unauthorized.', 'status' => 401];
        }

        $payload = ret_repo_get_approval_rows($connection, $user_id);
        return [
            'ok' => true,
            'message' => 'Return approvals loaded.',
            'rows' => $payload['rows'],
            'requires_cashier_approval' => $payload['requires_cashier_approval'],
        ];
    }
}

if (!function_exists('ret_service_approval_modal')) {
    function ret_service_approval_modal($connection, $return_id, $user_id = 0, $mode = 'confirm')
    {
        if (!ret_repo_is_authorized($user_id)) {
            return ['ok' => false, 'message' => 'Unauthorized.', 'status' => 401];
        }

        $return_row = ret_repo_get_return_item($connection, $return_id);
        if (!$return_row) {
            return ['ok' => false, 'message' => 'Return item not found.', 'status' => 404];
        }

        $item_id = (int)$return_row['item_id'];
        $cashier_status = (int)($return_row['cashier_status'] ?? 2);
        $requires_cashier_approval = kp_return_requires_cashier_approval($connection);
        $is_cashier_stage = ($requires_cashier_approval && $cashier_status !== 1);

        $title = $mode === 'reject'
            ? ($is_cashier_stage ? 'Cashier Reject Return' : 'Reject Return')
            : ($is_cashier_stage ? 'Cashier Approve Return' : 'Confirm Return');

        return [
            'ok' => true,
            'message' => 'Return approval modal loaded.',
            'modal' => [
                'mode' => $mode,
                'title' => $title,
                'id' => $return_id,
                'sale_id' => (int)$return_row['sale_id'],
                'item' => get_column('kp_items', 'name', "item_id='$item_id'"),
                'qty' => (float)$return_row['qty'],
                'counter' => get_counter((int)$return_row['stock_counter']),
                'stage' => $is_cashier_stage ? 'cashier' : 'counter',
                'message' => $mode === 'reject'
                    ? ($is_cashier_stage
                        ? 'This action stops the return before it reaches the counter. The item will remain on the original bill.'
                        : 'This action sends the return back to the waiter. Make sure the rejection is valid and the waiter is informed because all actions are documented.')
                    : ($is_cashier_stage
                        ? 'This action sends the return to the same counter for final stock approval. Stock will not be affected yet.'
                        : 'This action returns items to stock and prints a new receipt. Make sure you receive the items from the waiter before confirming.'),
            ],
        ];
    }
}

if (!function_exists('ret_service_cashier_approve')) {
    function ret_service_cashier_approve($connection, $return_id, $user_id = 0)
    {
        if (!ret_repo_is_authorized($user_id)) {
            return ['ok' => false, 'message' => 'Unauthorized.', 'status' => 401];
        }

        $row = ret_repo_get_return_item($connection, $return_id);
        if (!$row) {
            return ['ok' => false, 'message' => 'Return item not found.', 'status' => 404];
        }

        if (!ret_repo_cashier_approve($connection, $return_id)) {
            return ['ok' => false, 'message' => 'Could not approve return.', 'status' => 500];
        }

        ret_log_action('CASHIER APPROVED RETURN|' . $row['qty'] . '|' . get_item((int)$row['item_id']) . '|FROM ORDER NO|' . (int)$row['sale_id']);
        return ret_service_approval_list($connection, $user_id);
    }
}

if (!function_exists('ret_service_reject_return')) {
    function ret_service_reject_return($connection, $return_id, $user_id = 0)
    {
        if (!ret_repo_is_authorized($user_id)) {
            return ['ok' => false, 'message' => 'Unauthorized.', 'status' => 401];
        }

        $row = ret_repo_get_return_item($connection, $return_id);
        if (!$row) {
            return ['ok' => false, 'message' => 'Return item not found.', 'status' => 404];
        }

        ret_repo_update_sale_item_status_by_sale_counter($connection, $row['sale_id'], $row['item_id'], $row['sale_counter'], 2);
        $requires_cashier_approval = kp_return_requires_cashier_approval($connection);
        if (!ret_repo_reject_return($connection, $return_id, $row['cashier_status'], $requires_cashier_approval)) {
            return ['ok' => false, 'message' => 'Could not reject return.', 'status' => 500];
        }

        ret_log_action('REJECTED|' . $row['qty'] . '|' . get_item((int)$row['item_id']) . '|FROM ORDER NO|' . (int)$row['sale_id']);
        return ret_service_approval_list($connection, $user_id);
    }
}

if (!function_exists('ret_service_confirm_return')) {
    function ret_service_confirm_return($connection, $return_id, $user_id = 0)
    {
        if (!ret_repo_is_authorized($user_id)) {
            return ['ok' => false, 'message' => 'Unauthorized.', 'status' => 401];
        }

        $row = ret_repo_get_return_item($connection, $return_id);
        if (!$row) {
            return ['ok' => false, 'message' => 'Return item not found.', 'status' => 404];
        }

        if (kp_return_requires_cashier_approval($connection) && (int)$row['cashier_status'] !== 1) {
            return ['ok' => false, 'message' => 'Cashier approval is still required.', 'status' => 409];
        }
        if ((int)($row['confirm_status'] ?? 0) === 1 || (int)($row['counter_status'] ?? 0) === 1) {
            return ['ok' => false, 'message' => 'This return has already been approved.', 'status' => 409];
        }

        $rqty = (float)$row['qty'];
        $oqty = (float)$row['oqty'];
        $item_id = (int)$row['item_id'];
        $sale_id = (int)$row['sale_id'];
        $counter = (int)$row['stock_counter'];
        $sale_counter = (int)$row['sale_counter'];
        $shift = get_column('kp_shift', 'shift', '1');
        $qty_column = 'cntr' . $counter . '_qty';
        $recipe = (int)get_column('kp_items', 'recipe', "item_id='$item_id'");

        if ($recipe === 1) {
            $recipe_rows = ret_repo_get_recipe_rows($connection, $item_id);
            foreach ($recipe_rows as $recipe_row) {
                $raw_id = (int)$recipe_row['raw_id'];
                $nqty = (float)$recipe_row['qty'] * $rqty;
                ret_repo_add_item_stock($connection, $raw_id, $qty_column, $nqty);
                ret_repo_insert_stock_track($connection, $raw_id, $nqty, $user_id, $sale_id, $shift, $counter);
            }
        } else {
            ret_repo_add_item_stock($connection, $item_id, $qty_column, $rqty);
            ret_repo_insert_stock_track($connection, $item_id, $rqty, $user_id, $sale_id, $shift, $counter);
        }

        ret_repo_mark_confirmed($connection, $return_id, $user_id);

        $bal = $oqty - $rqty;
        if ($bal == 0.0) {
            ret_repo_delete_sale_item($connection, $sale_id, $item_id, $sale_counter);
            ret_repo_delete_stock_track($connection, $sale_id, $item_id, $counter);
        } else {
            ret_repo_update_sale_item_balance($connection, $sale_id, $item_id, $sale_counter, $bal);
            ret_repo_update_track_balance($connection, $sale_id, $item_id, $counter, $bal);
        }

        $total = ret_repo_get_sale_total($connection, $sale_id);
        ret_repo_update_sale_total($connection, $sale_id, $total);
        kp_return_mark_sale_void_if_full($connection, $sale_id);
        $remaining_items = (int)column_count('kp_sale_items', 'item_id', "sale_id='$sale_id'");
        /* Wait until every return row for this sale has been approved before
         * printing. The renderer will then print all confirmed return items
         * together in one receipt. */
        $pending_return_items = (int)column_count(
            'kp_ret_items',
            'id',
            "sale_id='$sale_id' AND (confirm_status=0 OR confirm_status=2 OR confirm_status IS NULL)"
        );
        $queued_prints = [];
        if ($counter > 0 && $pending_return_items === 0) {
            $queued_prints = ret_queue_v1_prints($connection, $sale_id, $return_id, $user_id, $counter, $remaining_items > 0 ? $sale_counter : 0);
        }
        if (function_exists('sync_sale_tokens')) {
            sync_sale_tokens($sale_id, 'sale_return_sync');
        }

        ret_log_action('CONFIRMED RETURN|' . $rqty . '|' . get_item($item_id) . '|FROM ORDER NO|' . $sale_id);
        return [
            'ok' => true,
            'message' => 'Return confirmed successfully.',
            'print_sale_id' => $sale_id,
            'remaining_items' => $remaining_items,
            'queued_prints' => $queued_prints,
            'approvals' => ret_service_approval_list($connection, $user_id),
        ];
    }
}
