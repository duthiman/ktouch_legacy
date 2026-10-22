<?php

if (!function_exists('ret_repo_get_page_context')) {
    function ret_repo_get_page_context($sale_id, $user_id = 0)
    {
        return [
            'sale_id' => (int)$sale_id,
            'user_id' => (int)$user_id,
        ];
    }
}

if (!function_exists('ret_repo_is_authorized')) {
    function ret_repo_is_authorized($user_id)
    {
        return (int)$user_id > 0;
    }
}

if (!function_exists('ret_repo_get_sale_rows')) {
    function ret_repo_get_sale_rows($connection, $sale_id)
    {
        $sale_id = (int)$sale_id;
        $rows = [];
        $source_table = 'kp_sale_items';
        $is_draft = false;

        $finalized_count = (int)column_count('kp_sale_items', 'id', "sale_id='$sale_id'");
        if ($finalized_count < 1) {
            $draft_count = (int)column_count('kp_sale_print', 'id', "sale_id='$sale_id'");
            if ($draft_count > 0) {
                $source_table = 'kp_sale_print';
                $is_draft = true;
            }
        }

        $query = mysqli_query(
            $connection,
            "SELECT id, item_id, stock_counter, counter, qty, price, total, ret_status
             FROM $source_table
             WHERE sale_id='$sale_id'
             ORDER BY id ASC"
        );

        if (!$query) {
            return [
                'source_table' => $source_table,
                'is_draft' => $is_draft,
                'rows' => [],
                'totals' => ['count' => 0, 'amount' => 0],
            ];
        }

        $grouped = [];
        $grand_total = 0;
        while ($row = mysqli_fetch_assoc($query)) {
            $item_id = (int)$row['item_id'];
            $counter = (int)$row['stock_counter'];
            $key = $item_id . ':' . $counter;
            if (!isset($grouped[$key])) {
                $grouped[$key] = [
                    'item_id' => $item_id,
                    'item_name' => get_column('kp_items', 'name', "item_id='$item_id'"),
                    'qty' => 0,
                    'price' => (float)$row['price'],
                    'total' => 0,
                    'counter' => $counter,
                    'counter_name' => get_column('kp_counters', 'name', "cntr_id='$counter'"),
                    'ret_status' => (int)$row['ret_status'],
                ];
            }
            $grouped[$key]['qty'] += (float)$row['qty'];
            $grouped[$key]['total'] += (float)$row['total'];
            $grand_total += (float)$row['total'];
        }

        return [
            'source_table' => $source_table,
            'is_draft' => $is_draft,
            'rows' => array_values($grouped),
            'totals' => [
                'count' => count($grouped),
                'amount' => $grand_total,
            ],
        ];
    }
}

if (!function_exists('ret_repo_get_pending_returns')) {
    function ret_repo_get_pending_returns($connection, $sale_id)
    {
        $sale_id = (int)$sale_id;
        $rows = [];
        $query = mysqli_query(
            $connection,
            "SELECT id, item_id, stock_counter, qty
             FROM kp_ret_items
             WHERE sale_id='$sale_id' AND ret_status=2
             ORDER BY id DESC"
        );

        if ($query) {
            while ($row = mysqli_fetch_assoc($query)) {
                $item_id = (int)$row['item_id'];
                $counter = (int)$row['stock_counter'];
                $rows[] = [
                    'ret_id' => (int)$row['id'],
                    'item_id' => $item_id,
                    'item_name' => get_column('kp_items', 'name', "item_id='$item_id'"),
                    'qty' => (float)$row['qty'],
                    'counter' => $counter,
                    'counter_name' => get_counter($counter),
                ];
            }
        }

        return $rows;
    }
}

if (!function_exists('ret_repo_get_sale_item')) {
    function ret_repo_get_sale_item($connection, $sale_id, $item_id, $counter)
    {
        $sale_id = (int)$sale_id;
        $item_id = (int)$item_id;
        $counter = (int)$counter;
        $query = mysqli_query(
            $connection,
            "SELECT *
             FROM kp_sale_items
             WHERE sale_id='$sale_id' AND item_id='$item_id' AND stock_counter='$counter'
             LIMIT 1"
        );

        return $query ? mysqli_fetch_assoc($query) : null;
    }
}

if (!function_exists('ret_repo_insert_return_item')) {
    function ret_repo_insert_return_item($connection, $data)
    {
        $sale_id = (int)$data['sale_id'];
        $item_id = (int)$data['item_id'];
        $oqty = (float)$data['oqty'];
        $qty = (float)$data['qty'];
        $user_id = (int)$data['user_id'];
        $shift = ret_escape($data['shift']);
        $amount = (float)$data['amount'];
        $sale_counter = (int)$data['sale_counter'];
        $stock_counter = (int)$data['stock_counter'];

        return mysqli_query(
            $connection,
            "INSERT INTO kp_ret_items(sale_id,item_id,oqty,qty,user_id,shift,amount,sale_counter,stock_counter)
             VALUES('$sale_id','$item_id','$oqty','$qty','$user_id','$shift','$amount','$sale_counter','$stock_counter')"
        );
    }
}

if (!function_exists('ret_repo_update_sale_item_ret_status')) {
    function ret_repo_update_sale_item_ret_status($connection, $sale_id, $item_id, $counter, $ret_status)
    {
        $sale_id = (int)$sale_id;
        $item_id = (int)$item_id;
        $counter = (int)$counter;
        $ret_status = (int)$ret_status;

        return mysqli_query(
            $connection,
            "UPDATE kp_sale_items
             SET ret_status='$ret_status'
             WHERE item_id='$item_id' AND sale_id='$sale_id' AND stock_counter='$counter'"
        );
    }
}

if (!function_exists('ret_repo_get_return_item')) {
    function ret_repo_get_return_item($connection, $return_id)
    {
        $return_id = (int)$return_id;
        $query = mysqli_query($connection, "SELECT * FROM kp_ret_items WHERE id='$return_id' LIMIT 1");
        return $query ? mysqli_fetch_assoc($query) : null;
    }
}

if (!function_exists('ret_repo_delete_return_item')) {
    function ret_repo_delete_return_item($connection, $return_id)
    {
        $return_id = (int)$return_id;
        return mysqli_query($connection, "DELETE FROM kp_ret_items WHERE id='$return_id'");
    }
}

if (!function_exists('ret_repo_finish_sale_returns')) {
    function ret_repo_finish_sale_returns($connection, $sale_id, $requires_cashier_approval)
    {
        $sale_id = (int)$sale_id;
        $cashier_sql = $requires_cashier_approval
            ? "cashier_status=2"
            : "cashier_status=1, cashier_approve_datime=NOW()";

        return mysqli_query(
            $connection,
            "UPDATE kp_ret_items
             SET ret_status=3, $cashier_sql
             WHERE sale_id='$sale_id' AND (confirm_status=2 OR confirm_status=0 OR confirm_status IS NULL)"
        );
    }
}

if (!function_exists('ret_repo_get_user_counter')) {
    function ret_repo_get_user_counter($user_id)
    {
        $user_id = (int)$user_id;
        return get_column('kp_users', 'counter', "user_id='$user_id'");
    }
}

if (!function_exists('ret_repo_get_approval_rows')) {
    function ret_repo_get_approval_rows($connection, $user_id)
    {
        $user_id = (int)$user_id;
        $user_counter = ret_repo_get_user_counter($user_id);
        $return_settings = kp_return_settings($connection);
        $requires_cashier_approval = (int)($return_settings['approval_mode'] ?? 1) === 2;
        $return_where = $requires_cashier_approval
            ? "((cashier_status=2 AND ret_status!=1) OR (cashier_status=1 AND counter_status=2 AND ret_status=3))"
            : "(ret_status=3 AND counter_status=2)";

        $rows = [];
        $query = mysqli_query($connection, "SELECT * FROM kp_ret_items WHERE $return_where ORDER BY sale_id DESC, id DESC");
        if ($query) {
            while ($row = mysqli_fetch_assoc($query)) {
                $counter = (int)$row['stock_counter'];
                $cashier_status = (int)($row['cashier_status'] ?? 2);
                $stage = (!$requires_cashier_approval || $cashier_status === 1) ? 'counter' : 'cashier';
                $rows[] = [
                    'id' => (int)$row['id'],
                    'staff' => get_column('kp_users', 'username', "user_id='" . (int)$row['user_id'] . "'"),
                    'sale_id' => (int)$row['sale_id'],
                    'item' => get_column('kp_items', 'name', "item_id='" . (int)$row['item_id'] . "'"),
                    'qty' => (float)$row['qty'],
                    'counter' => get_counter($counter),
                    'stock_counter' => $counter,
                    'stage' => $stage,
                    'cashier_status' => $cashier_status,
                    'counter_status' => (int)($row['counter_status'] ?? 2),
                    'can_counter_action' => ((string)$user_counter === (string)$counter),
                ];
            }
        }

        return [
            'rows' => $rows,
            'requires_cashier_approval' => $requires_cashier_approval,
            'user_counter' => $user_counter,
        ];
    }
}

if (!function_exists('ret_repo_cashier_approve')) {
    function ret_repo_cashier_approve($connection, $return_id)
    {
        $return_id = (int)$return_id;
        return mysqli_query(
            $connection,
            "UPDATE kp_ret_items
             SET cashier_status=1,
                 cashier_approve_datime=NOW(),
                 confirm_status=2,
                 ret_status=3
             WHERE id='$return_id' AND (confirm_status=0 OR confirm_status=2 OR confirm_status IS NULL)"
        );
    }
}

if (!function_exists('ret_repo_reject_return')) {
    function ret_repo_reject_return($connection, $return_id, $cashier_status, $requires_cashier_approval)
    {
        $return_id = (int)$return_id;
        $stage_sql = $requires_cashier_approval && (int)$cashier_status !== 1
            ? "cashier_status=3"
            : "counter_status=3";

        return mysqli_query(
            $connection,
            "UPDATE kp_ret_items
             SET $stage_sql, confirm_status=3, ret_status=2
             WHERE id='$return_id'"
        );
    }
}

if (!function_exists('ret_repo_update_sale_item_status_by_sale_counter')) {
    function ret_repo_update_sale_item_status_by_sale_counter($connection, $sale_id, $item_id, $sale_counter, $ret_status)
    {
        $sale_id = (int)$sale_id;
        $item_id = (int)$item_id;
        $sale_counter = (int)$sale_counter;
        $ret_status = (int)$ret_status;

        return mysqli_query(
            $connection,
            "UPDATE kp_sale_items
             SET ret_status='$ret_status'
             WHERE item_id='$item_id' AND sale_id='$sale_id' AND counter='$sale_counter'"
        );
    }
}

if (!function_exists('ret_repo_get_recipe_rows')) {
    function ret_repo_get_recipe_rows($connection, $item_id)
    {
        $item_id = (int)$item_id;
        $rows = [];
        $query = mysqli_query($connection, "SELECT raw_id, qty FROM kp_recipe WHERE item_id='$item_id'");
        if ($query) {
            while ($row = mysqli_fetch_assoc($query)) {
                $rows[] = $row;
            }
        }
        return $rows;
    }
}

if (!function_exists('ret_repo_add_item_stock')) {
    function ret_repo_add_item_stock($connection, $item_id, $qty_column, $qty)
    {
        $item_id = (int)$item_id;
        $qty = (float)$qty;
        $qty_column = preg_replace('/[^a-zA-Z0-9_]/', '', $qty_column);
        return mysqli_query($connection, "UPDATE kp_items SET `$qty_column`=`$qty_column`+'$qty' WHERE item_id='$item_id'");
    }
}

if (!function_exists('ret_repo_insert_stock_track')) {
    function ret_repo_insert_stock_track($connection, $item_id, $qty, $user_id, $sale_id, $shift, $counter)
    {
        $item_id = (int)$item_id;
        $qty = (float)$qty;
        $user_id = (int)$user_id;
        $sale_id = (int)$sale_id;
        $counter = (int)$counter;
        $shift = ret_escape($shift);
        return mysqli_query(
            $connection,
            "INSERT INTO kp_stock_track(item_id,qty,user_id,sale_id,status,shift,stock_counter)
             VALUES('$item_id','$qty','$user_id','$sale_id','RETURN','$shift','$counter')"
        );
    }
}

if (!function_exists('ret_repo_delete_sale_item')) {
    function ret_repo_delete_sale_item($connection, $sale_id, $item_id, $sale_counter)
    {
        $sale_id = (int)$sale_id;
        $item_id = (int)$item_id;
        $sale_counter = (int)$sale_counter;
        return mysqli_query($connection, "DELETE FROM kp_sale_items WHERE item_id='$item_id' AND sale_id='$sale_id' AND counter='$sale_counter'");
    }
}

if (!function_exists('ret_repo_delete_stock_track')) {
    function ret_repo_delete_stock_track($connection, $sale_id, $item_id, $counter)
    {
        $sale_id = (int)$sale_id;
        $item_id = (int)$item_id;
        $counter = (int)$counter;
        return mysqli_query($connection, "DELETE FROM kp_stock_track WHERE item_id='$item_id' AND sale_id='$sale_id' AND stock_counter='$counter'");
    }
}

if (!function_exists('ret_repo_update_sale_item_balance')) {
    function ret_repo_update_sale_item_balance($connection, $sale_id, $item_id, $sale_counter, $qty)
    {
        $sale_id = (int)$sale_id;
        $item_id = (int)$item_id;
        $sale_counter = (int)$sale_counter;
        $qty = (float)$qty;
        return mysqli_query(
            $connection,
            "UPDATE kp_sale_items SET qty='$qty', total='$qty'*price WHERE item_id='$item_id' AND sale_id='$sale_id' AND counter='$sale_counter'"
        );
    }
}

if (!function_exists('ret_repo_update_track_balance')) {
    function ret_repo_update_track_balance($connection, $sale_id, $item_id, $counter, $qty)
    {
        $sale_id = (int)$sale_id;
        $item_id = (int)$item_id;
        $counter = (int)$counter;
        $qty = (float)$qty;
        return mysqli_query($connection, "UPDATE kp_stock_track SET qty='$qty' WHERE item_id='$item_id' AND sale_id='$sale_id' AND stock_counter='$counter'");
    }
}

if (!function_exists('ret_repo_mark_confirmed')) {
    function ret_repo_mark_confirmed($connection, $return_id, $user_id)
    {
        $return_id = (int)$return_id;
        $user_id = (int)$user_id;
        return mysqli_query(
            $connection,
            "UPDATE kp_ret_items
             SET ret_status=1, confirm_by='$user_id', confirm_status=1, counter_status=1, counter_approve_datime=NOW()
             WHERE id='$return_id'"
        );
    }
}

if (!function_exists('ret_repo_get_sale_total')) {
    function ret_repo_get_sale_total($connection, $sale_id)
    {
        $sale_id = (int)$sale_id;
        $query = mysqli_query($connection, "SELECT SUM(total) as total FROM kp_sale_items WHERE sale_id='$sale_id'");
        $row = $query ? mysqli_fetch_assoc($query) : [];
        return (float)($row['total'] ?? 0);
    }
}

if (!function_exists('ret_repo_update_sale_total')) {
    function ret_repo_update_sale_total($connection, $sale_id, $total)
    {
        $sale_id = (int)$sale_id;
        $total = (float)$total;
        return mysqli_query($connection, "UPDATE kp_sales SET amount='$total' WHERE sale_id='$sale_id'");
    }
}
