<?php

$bill_chaba_funcs = __DIR__ . '/../../chaba/funcs.php';
if (file_exists($bill_chaba_funcs)) {
    require_once $bill_chaba_funcs;
}

if (!function_exists('bill_repo_ping')) {
    function bill_repo_ping() {
        global $connection;

        return $connection ? true : false;
    }
}

if (!function_exists('bill_repo_get_active_sale_id')) {
    function bill_repo_get_active_sale_id($user_id) {
        global $connection;

        $user_id = (int)$user_id;
        if ($user_id < 1) {
            return 0;
        }

        $result = mysqli_query($connection, "SELECT sale_id FROM kp_sale_status WHERE user_id='$user_id' ORDER BY datime DESC, sale_id DESC LIMIT 1");
        $row = $result ? mysqli_fetch_assoc($result) : null;

        return (int)($row['sale_id'] ?? 0);
    }
}

if (!function_exists('repo_get_thesion')) {
    function repo_get_thesion($user_id) {
        global $connection;

        $user_id = (int)$user_id;
        $result = mysqli_query($connection, "SELECT * FROM kp_thesion_front WHERE user_id='$user_id' LIMIT 1");

        return $result ? mysqli_fetch_assoc($result) : null;
    }
}

if (!function_exists('repo_get_user_counter')) {
    function repo_get_user_counter($user_id) {
        global $connection;

        $user_id = (int)$user_id;
        $result = mysqli_query($connection, "SELECT counter_id FROM kp_sale_status WHERE user_id='$user_id' ORDER BY datime DESC, sale_id DESC LIMIT 1");
        $row = $result ? mysqli_fetch_assoc($result) : null;

        return (int)($row['counter_id'] ?? 0);
    }
}

if (!function_exists('repo_set_active_sale_id')) {
    function repo_set_active_sale_id($user_id, $sale_id, $session_status = null) {
        global $connection;

        $user_id = (int)$user_id;
        $sale_id = (int)$sale_id;
        $session_status = $session_status === null ? null : (int)$session_status;
        if ($user_id < 1) {
            return false;
        }

        $status = $session_status === null ? ($sale_id > 0 ? 2 : 0) : $session_status;
        $updated = mysqli_query(
            $connection,
            "UPDATE kp_thesion_front
             SET sale_id='$sale_id', status='$status'
             WHERE user_id='$user_id'"
        );
        if (!$updated) {
            return false;
        }

        // A new browser/session may not have a kp_thesion_front row yet.
        // Create it so the order page can resolve the sale immediately.
        $exists = mysqli_query($connection, "SELECT user_id FROM kp_thesion_front WHERE user_id='$user_id' LIMIT 1");
        if ($exists && mysqli_num_rows($exists) > 0) {
            return true;
        }

        $counter_id = repo_get_user_counter($user_id);
        return (bool)mysqli_query(
            $connection,
            "INSERT INTO kp_thesion_front(user_id,sale_id,status,cntr_id,datime)
             VALUES('$user_id','$sale_id','$status','$counter_id',CURRENT_TIMESTAMP)"
        );
    }
}

if (!function_exists('repo_get_shift')) {
    function repo_get_shift() {
        if (function_exists('get_column')) {
            return (string)get_column('kp_shift', 'shift', "1");
        }

        return '';
    }
}

if (!function_exists('repo_get_sale_status_by_user')) {
    function repo_get_sale_status_by_user($user_id) {
        global $connection;

        $user_id = (int)$user_id;
        $result = mysqli_query($connection, "SELECT * FROM kp_sale_status WHERE user_id='$user_id' ORDER BY datime DESC, sale_id DESC LIMIT 1");
        return $result ? mysqli_fetch_assoc($result) : null;
    }
}

if (!function_exists('repo_get_sale_status_for_sale')) {
    function repo_get_sale_status_for_sale($user_id, $sale_id) {
        global $connection;

        $user_id = (int)$user_id;
        $sale_id = (int)$sale_id;
        if ($user_id < 1 || $sale_id < 1) {
            return null;
        }

        $result = mysqli_query(
            $connection,
            "SELECT * FROM kp_sale_status WHERE user_id='$user_id' AND sale_id='$sale_id' LIMIT 1"
        );

        return $result ? mysqli_fetch_assoc($result) : null;
    }
}

if (!function_exists('repo_get_item')) {
    function repo_get_item($item_id) {
        global $connection;

        $item_id = (int)$item_id;
        $result = mysqli_query($connection, "SELECT * FROM kp_items WHERE item_id='$item_id' LIMIT 1");

        return $result ? mysqli_fetch_assoc($result) : null;
    }
}

if (!function_exists('repo_deduct_counter_stock')) {
    function repo_deduct_counter_stock($item_id, $counter_id, $qty) {
        global $connection;

        $item_id = (int)$item_id;
        $counter_id = (int)$counter_id;
        $qty = (float)$qty;

        if ($item_id < 1 || $counter_id < 1 || $qty <= 0) {
            return [
                'ok' => false,
                'message' => 'Invalid stock deduction request.',
            ];
        }

        $item = repo_get_item($item_id);
        if (!$item) {
            return [
                'ok' => false,
                'message' => 'Item not found for stock deduction.',
            ];
        }

        $stock_mode = (int)($item['stock'] ?? 0);
        if ($stock_mode !== 1) {
            return [
                'ok' => true,
                'message' => 'Item does not use direct counter stock.',
                'deducted' => false,
            ];
        }

        $qty_column = 'cntr' . $counter_id . '_qty';
        if (function_exists('kw_schema_column_exists') && !kw_schema_column_exists($connection, 'kp_items', $qty_column)) {
            return [
                'ok' => false,
                'message' => 'Counter stock column ' . $qty_column . ' was not found.',
            ];
        }

        $current_qty = isset($item[$qty_column]) ? (float)$item[$qty_column] : 0;
        if ($current_qty < $qty) {
            return [
                'ok' => false,
                'message' => 'Not enough stock on this counter.',
                'available_qty' => $current_qty,
                'requested_qty' => $qty,
                'column' => $qty_column,
            ];
        }

        $safe_column = '`' . str_replace('`', '``', $qty_column) . '`';
        $updated = mysqli_query(
            $connection,
            "UPDATE kp_items
             SET $safe_column = $safe_column - '$qty'
             WHERE item_id='$item_id'"
        );

        if (!$updated) {
            return [
                'ok' => false,
                'message' => 'Could not deduct counter stock.',
            ];
        }

        return [
            'ok' => true,
            'message' => 'Counter stock deducted.',
            'deducted' => true,
            'remaining_qty' => $current_qty - $qty,
            'column' => $qty_column,
        ];
    }
}

if (!function_exists('repo_fetch_recipe_rows')) {
    function repo_fetch_recipe_rows($item_id) {
        global $connection;

        $item_id = (int)$item_id;
        $rows = [];
        if ($item_id < 1) {
            return $rows;
        }

        $result = mysqli_query(
            $connection,
            "SELECT raw_id, qty
             FROM kp_recipe
             WHERE item_id='$item_id'"
        );

        if (!$result) {
            return $rows;
        }

        while ($row = mysqli_fetch_assoc($result)) {
            $rows[] = [
                'raw_id' => (int)($row['raw_id'] ?? 0),
                'qty' => (float)($row['qty'] ?? 0),
            ];
        }

        return $rows;
    }
}

if (!function_exists('repo_deduct_recipe_counter_stock')) {
    function repo_deduct_recipe_counter_stock($item_id, $counter_id, $qty, $trackingContext = []) {
        global $connection;

        $item_id = (int)$item_id;
        $counter_id = (int)$counter_id;
        $qty = (float)$qty;

        if ($item_id < 1 || $counter_id < 1 || $qty <= 0) {
            return [
                'ok' => false,
                'message' => 'Invalid recipe stock deduction request.',
            ];
        }

        $qty_column = 'cntr' . $counter_id . '_qty';
        if (function_exists('kw_schema_column_exists') && !kw_schema_column_exists($connection, 'kp_items', $qty_column)) {
            return [
                'ok' => false,
                'message' => 'Counter stock column ' . $qty_column . ' was not found.',
            ];
        }

        $recipe_rows = repo_fetch_recipe_rows($item_id);
        if (count($recipe_rows) === 0) {
            return [
                'ok' => false,
                'message' => 'Recipe rows were not found for this item.',
            ];
        }

        $checks = [];
        if (!mysqli_begin_transaction($connection)) {
            return [
                'ok' => false,
                'message' => 'Could not start the recipe stock transaction.',
            ];
        }
        foreach ($recipe_rows as $recipe_row) {
            $raw_id = (int)($recipe_row['raw_id'] ?? 0);
            $recipe_qty = (float)($recipe_row['qty'] ?? 0);
            $required_qty = $recipe_qty * $qty;
            if ($raw_id < 1 || $required_qty <= 0) {
                continue;
            }

            $raw_query = mysqli_query($connection, "SELECT * FROM kp_items WHERE item_id='{$raw_id}' LIMIT 1 FOR UPDATE");
            $raw_item = $raw_query ? mysqli_fetch_assoc($raw_query) : null;
            $available_qty = $raw_item && isset($raw_item[$qty_column]) ? (float)$raw_item[$qty_column] : 0;
            if ($available_qty < $required_qty) {
                mysqli_rollback($connection);
                return [
                    'ok' => false,
                    'message' => 'Not enough recipe stock on this counter.',
                    'raw_item_id' => $raw_id,
                    'available_qty' => $available_qty,
                    'requested_qty' => $required_qty,
                    'column' => $qty_column,
                ];
            }

            $checks[] = [
                'raw_id' => $raw_id,
                'required_qty' => $required_qty,
                'available_qty' => $available_qty,
            ];
        }

        if (count($checks) === 0) {
            mysqli_rollback($connection);
            return [
                'ok' => false,
                'message' => 'Recipe stock rows were invalid.',
            ];
        }

        $safe_column = '`' . str_replace('`', '``', $qty_column) . '`';
        foreach ($checks as $check) {
            $raw_id = (int)$check['raw_id'];
            $required_qty = (float)$check['required_qty'];
            $updated = mysqli_query(
                $connection,
                "UPDATE kp_items
                 SET $safe_column = $safe_column - '$required_qty'
                 WHERE item_id='$raw_id' AND $safe_column >= '$required_qty'"
            );

            if (!$updated || mysqli_affected_rows($connection) !== 1) {
                mysqli_rollback($connection);
                return [
                    'ok' => false,
                    'message' => 'Could not deduct recipe counter stock.',
                    'raw_item_id' => $raw_id,
                ];
            }

            // Keep recipe consumption in the same stock ledger as direct
            // sales. The caller supplies the sale context below through the
            // optional transaction context; when unavailable, the movement
            // is still recorded against the ingredient and counter.
            $tracked = repo_insert_stock_track([
                'item_id' => $raw_id,
                'qty' => $required_qty,
                'user_id' => (int)($trackingContext['user_id'] ?? ($_SESSION['user_id'] ?? 0)),
                'sale_id' => (int)($trackingContext['sale_id'] ?? 0),
                'status' => 'OUT',
                'sale_counter' => $counter_id,
                'stock_counter' => $counter_id,
                'shift' => (string)($trackingContext['shift'] ?? (function_exists('repo_get_shift') ? repo_get_shift() : date('Y-m-d'))),
                'hour' => (int)($trackingContext['hour'] ?? (function_exists('hhr') ? hhr() : 1)),
                'dpt' => (int)($raw_item['dpt'] ?? 0),
            ]);
            if (!$tracked) {
                mysqli_rollback($connection);
                return [
                    'ok' => false,
                    'message' => 'Could not track recipe ingredient stock. No stock was changed.',
                    'raw_item_id' => $raw_id,
                ];
            }
        }

        if (!mysqli_commit($connection)) {
            mysqli_rollback($connection);
            return [
                'ok' => false,
                'message' => 'Could not commit recipe stock deduction. No stock was changed.',
            ];
        }

        return [
            'ok' => true,
            'message' => 'Recipe counter stock deducted.',
            'deducted' => true,
            'column' => $qty_column,
            'components' => $checks,
        ];
    }
}

if (!function_exists('repo_deduct_item_stock_from_counter')) {
    function repo_deduct_item_stock_from_counter($item_id, $counter_id, $qty) {
        $item_id = (int)$item_id;
        $counter_id = (int)$counter_id;
        $qty = (float)$qty;

        if ($item_id < 1 || $counter_id < 1 || $qty <= 0) {
            return [
                'ok' => false,
                'message' => 'Invalid stock deduction request.',
            ];
        }

        $item = repo_get_item($item_id);
        if (!$item) {
            return [
                'ok' => false,
                'message' => 'Item not found for stock deduction.',
            ];
        }

        $stock_mode = (int)($item['stock'] ?? 0);
        $uses_recipe = (int)($item['recipe'] ?? 0) === 1;

        if ($stock_mode === 1) {
            return repo_deduct_counter_stock($item_id, $counter_id, $qty);
        }

        if ($stock_mode === 2 && $uses_recipe) {
            return repo_deduct_recipe_counter_stock($item_id, $counter_id, $qty, $GLOBALS['kw_recipe_tracking_context'] ?? []);
        }

        return [
            'ok' => true,
            'message' => 'Item does not use counter stock deduction.',
            'deducted' => false,
        ];
    }
}

if (!function_exists('repo_return_counter_stock')) {
    function repo_return_counter_stock($item_id, $counter_id, $qty) {
        global $connection;

        $item_id = (int)$item_id;
        $counter_id = (int)$counter_id;
        $qty = (float)$qty;

        if ($item_id < 1 || $counter_id < 1 || $qty <= 0) {
            return [
                'ok' => false,
                'message' => 'Invalid stock return request.',
            ];
        }

        $qty_column = 'cntr' . $counter_id . '_qty';
        if (function_exists('kw_schema_column_exists') && !kw_schema_column_exists($connection, 'kp_items', $qty_column)) {
            return [
                'ok' => false,
                'message' => 'Counter stock column ' . $qty_column . ' was not found.',
            ];
        }

        $safe_column = '`' . str_replace('`', '``', $qty_column) . '`';
        $updated = mysqli_query(
            $connection,
            "UPDATE kp_items
             SET $safe_column = $safe_column + '$qty'
             WHERE item_id='$item_id'"
        );

        if (!$updated) {
            return [
                'ok' => false,
                'message' => 'Could not return counter stock.',
            ];
        }

        return [
            'ok' => true,
            'message' => 'Counter stock returned.',
            'returned' => true,
            'column' => $qty_column,
        ];
    }
}

if (!function_exists('repo_return_recipe_counter_stock')) {
    function repo_return_recipe_counter_stock($item_id, $counter_id, $qty) {
        $item_id = (int)$item_id;
        $counter_id = (int)$counter_id;
        $qty = (float)$qty;

        if ($item_id < 1 || $counter_id < 1 || $qty <= 0) {
            return [
                'ok' => false,
                'message' => 'Invalid recipe stock return request.',
            ];
        }

        $recipe_rows = repo_fetch_recipe_rows($item_id);
        if (count($recipe_rows) === 0) {
            return [
                'ok' => false,
                'message' => 'Recipe rows were not found for this item.',
            ];
        }

        $returned = [];
        foreach ($recipe_rows as $recipe_row) {
            $raw_id = (int)($recipe_row['raw_id'] ?? 0);
            $recipe_qty = (float)($recipe_row['qty'] ?? 0);
            $return_qty = $recipe_qty * $qty;
            if ($raw_id < 1 || $return_qty <= 0) {
                continue;
            }

            $result = repo_return_counter_stock($raw_id, $counter_id, $return_qty);
            if (empty($result['ok'])) {
                return $result;
            }

            $returned[] = [
                'raw_id' => $raw_id,
                'returned_qty' => $return_qty,
            ];
        }

        if (count($returned) === 0) {
            return [
                'ok' => false,
                'message' => 'Recipe stock rows were invalid.',
            ];
        }

        return [
            'ok' => true,
            'message' => 'Recipe counter stock returned.',
            'returned' => true,
            'components' => $returned,
        ];
    }
}

if (!function_exists('repo_return_item_stock_to_counter')) {
    function repo_return_item_stock_to_counter($item_id, $counter_id, $qty) {
        $item_id = (int)$item_id;
        $counter_id = (int)$counter_id;
        $qty = (float)$qty;

        if ($item_id < 1 || $counter_id < 1 || $qty <= 0) {
            return [
                'ok' => false,
                'message' => 'Invalid stock return request.',
            ];
        }

        $item = repo_get_item($item_id);
        if (!$item) {
            return [
                'ok' => false,
                'message' => 'Item not found for stock return.',
            ];
        }

        $stock_mode = (int)($item['stock'] ?? 0);
        $uses_recipe = (int)($item['recipe'] ?? 0) === 1;

        if ($stock_mode === 1) {
            return repo_return_counter_stock($item_id, $counter_id, $qty);
        }

        if ($stock_mode === 2 && $uses_recipe) {
            return repo_return_recipe_counter_stock($item_id, $counter_id, $qty);
        }

        return [
            'ok' => true,
            'message' => 'Item does not use counter stock return.',
            'returned' => false,
        ];
    }
}

if (!function_exists('bill_repo_last_id')) {
    function bill_repo_last_id($user_id) {
        global $connection;

        $user_id = (int)$user_id;
        if ($user_id < 1) {
            return 0;
        }

        if (function_exists('kw_ensure_sale_status_schema')) {
            kw_ensure_sale_status_schema($connection);
        }

        $check_order = mysqli_query($connection, "SELECT sale_id FROM kp_sale_status WHERE user_id='$user_id' AND status=2 LIMIT 1");
        if ($check_order && mysqli_num_rows($check_order) > 0) {
            $on = mysqli_fetch_assoc($check_order);
            return (int)($on['sale_id'] ?? 0);
        }

        $get_order = mysqli_query($connection, "SELECT sale_id FROM kp_sale_id LIMIT 1");
        if ($get_order && mysqli_num_rows($get_order) > 0) {
            $row = mysqli_fetch_assoc($get_order);
            $prev_sale_id = (int)($row['sale_id'] ?? 0);
        } else {
            $prev_sale_id = 0;
            mysqli_query($connection, "INSERT INTO kp_sale_id(sale_id) VALUES(0)");
        }

        $max_sales_id = 0;
        if (function_exists('get_max')) {
            $max_sales_id = (int)get_max('kp_sales', 'sale_id');
        } else {
            $result = mysqli_query($connection, "SELECT MAX(sale_id) AS max_sale_id FROM kp_sales");
            $row = $result ? mysqli_fetch_assoc($result) : null;
            $max_sales_id = (int)($row['max_sale_id'] ?? 0);
        }

        $sale_id = max($prev_sale_id, $max_sales_id) + 1;

        mysqli_query($connection, "UPDATE kp_sale_id SET sale_id='$sale_id'");

        return $sale_id;
    }
}

if (!function_exists('repo_get_sales_header')) {
    function repo_get_sales_header($sale_id) {
        global $connection;

        $sale_id = (int)$sale_id;
        $result = mysqli_query($connection, "SELECT * FROM kp_sales WHERE sale_id='$sale_id' LIMIT 1");

        return $result ? mysqli_fetch_assoc($result) : null;
    }
}

if (!function_exists('repo_get_closed_opened_parent')) {
    function repo_get_closed_opened_parent($parent_sale_id) {
        global $connection;

        $parent_sale_id = (int)$parent_sale_id;
        $result = mysqli_query($connection, "SELECT * FROM kp_shift_sales_open WHERE sale_id='$parent_sale_id' AND status=1 ORDER BY updated_at DESC, id DESC LIMIT 1");

        return $result ? mysqli_fetch_assoc($result) : null;
    }
}

if (!function_exists('repo_find_active_each_child')) {
    function repo_find_active_each_child($parent_sale_id, $user_id) {
        global $connection;

        $parent_sale_id = (int)$parent_sale_id;
        $user_id = (int)$user_id;
        $result = mysqli_query(
            $connection,
            "SELECT sale_id
             FROM kp_sale_status
             WHERE user_id='$user_id'
               AND mode='each'
               AND parent_status=1
               AND parent_sale_id='$parent_sale_id'
               AND status IN (2,3)
             ORDER BY bill_position DESC, sale_id DESC
             LIMIT 1"
        );
        $row = $result ? mysqli_fetch_assoc($result) : null;

        return (int)($row['sale_id'] ?? 0);
    }
}

if (!function_exists('repo_find_active_last_child')) {
    function repo_find_active_last_child($parent_sale_id, $user_id) {
        global $connection;

        $parent_sale_id = (int)$parent_sale_id;
        $user_id = (int)$user_id;
        $result = mysqli_query(
            $connection,
            "SELECT sale_id, child_sale_id
             FROM kp_shift_sales_open
             WHERE parent_sale_id='$parent_sale_id'
               AND user_id='$user_id'
               AND bill_mode='last'
               AND status=1
             ORDER BY id DESC
             LIMIT 1"
        );
        $row = $result ? mysqli_fetch_assoc($result) : null;
        $sale_id = (int)($row['sale_id'] ?? 0);
        $child_sale_id = (int)($row['child_sale_id'] ?? 0);

        return $sale_id > 0 ? $sale_id : $child_sale_id;
    }
}

if (!function_exists('repo_get_or_create_child_sale_id')) {
    function repo_get_or_create_child_sale_id($parent_sale_id, $mode, $user_id) {
        $parent_sale_id = (int)$parent_sale_id;
        $user_id = (int)$user_id;

        if ($parent_sale_id < 1 || $user_id < 1) {
            return 0;
        }

        return bill_repo_last_id($user_id);
    }
}

if (!function_exists('bill_repo_get_sales_open_registry')) {
    function bill_repo_get_sales_open_registry($sale_id, $user_id) {
        global $connection;

        $sale_id = (int)$sale_id;
        $user_id = (int)$user_id;
        $result = mysqli_query(
            $connection,
            "SELECT *
             FROM kp_shift_sales_open
             WHERE (sale_id='$sale_id' OR parent_sale_id='$sale_id' OR child_sale_id='$sale_id')
               AND user_id='$user_id'
               AND status=1
             ORDER BY id DESC
             LIMIT 1"
        );

        return $result ? mysqli_fetch_assoc($result) : null;
    }
}

if (!function_exists('bill_repo_sale_exists')) {
    function bill_repo_sale_exists($sale_id) {
        global $connection;

        $sale_id = (int)$sale_id;
        $result = mysqli_query($connection, "SELECT sale_id FROM kp_sales WHERE sale_id='$sale_id' LIMIT 1");

        return $result && mysqli_num_rows($result) > 0;
    }
}

if (!function_exists('bill_repo_get_sale_amount')) {
    function bill_repo_get_sale_amount($sale_id) {
        global $connection;

        $sale_id = (int)$sale_id;
        $result = mysqli_query($connection, "SELECT amount FROM kp_sales WHERE sale_id='$sale_id' LIMIT 1");
        $row = $result ? mysqli_fetch_assoc($result) : null;

        return (float)($row['amount'] ?? 0);
    }
}

if (!function_exists('bill_repo_get_children_sales_total')) {
    function bill_repo_get_children_sales_total($sale_id) {
        global $connection;

        $sale_id = (int)$sale_id;
        $result = mysqli_query($connection, "SELECT COALESCE(SUM(amount), 0) AS total_amount FROM kp_sales WHERE parent='$sale_id'");
        $row = $result ? mysqli_fetch_assoc($result) : null;

        return (float)($row['total_amount'] ?? 0);
    }
}

if (!function_exists('bill_repo_get_sale_print_total')) {
    function bill_repo_get_sale_print_total($sale_id) {
        global $connection;

        $sale_id = (int)$sale_id;
        $result = mysqli_query($connection, "SELECT COALESCE(SUM(total), 0) AS total_amount FROM kp_sale_print WHERE sale_id='$sale_id'");
        $row = $result ? mysqli_fetch_assoc($result) : null;

        return (float)($row['total_amount'] ?? 0);
    }
}

if (!function_exists('bill_repo_get_item_name')) {
    function bill_repo_get_item_name($item_id) {
        global $connection;

        $item_id = (int)$item_id;
        if ($item_id < 1) {
            return '';
        }

        // Sale rows only retain item_id. Resolve it from every supported
        // catalogue because the V1/open flow can use the inventory catalogue
        // while older installations still use kp_items.
        $lookups = [
            ['table' => 'kp_items', 'column' => 'name'],
            ['table' => 'kp_inventory_master', 'column' => 'item_name'],
            ['table' => 'kp_inventory_master', 'column' => 'name'],
        ];
        foreach ($lookups as $lookup) {
            if (function_exists('kw_schema_table_exists')
                && !kw_schema_table_exists($connection, $lookup['table'])) {
                continue;
            }
            if (function_exists('kw_schema_column_exists')
                && !kw_schema_column_exists($connection, $lookup['table'], $lookup['column'])) {
                continue;
            }

            $table = $lookup['table'];
            $column = $lookup['column'];
            $result = mysqli_query(
                $connection,
                "SELECT {$column} AS item_name FROM {$table} WHERE item_id='{$item_id}' LIMIT 1"
            );
            $row = $result ? mysqli_fetch_assoc($result) : null;
            $name = trim((string)($row['item_name'] ?? ''));
            if ($name !== '') {
                return $name;
            }
        }

        return '';
    }
}

if (!function_exists('bill_repo_get_attribute_name')) {
    function bill_repo_get_attribute_name($attr_id) {
        global $connection;

        $attr_id = (int)$attr_id;
        $result = mysqli_query($connection, "SELECT name FROM kp_items_attributes WHERE id='$attr_id' LIMIT 1");
        $row = $result ? mysqli_fetch_assoc($result) : null;

        return (string)($row['name'] ?? '');
    }
}

if (!function_exists('bill_repo_fetch_bill_items_summary')) {
    function bill_repo_fetch_bill_items_summary($sale_id, $use_finalized_table = false) {
        global $connection;

        $sale_id = (int)$sale_id;
        $table = $use_finalized_table ? 'kp_sale_items' : 'kp_sale_print';
        $items = [];
        $result = mysqli_query(
            $connection,
            "SELECT item_id, attr_id, SUM(qty) AS qty, SUM(total) AS total
             FROM {$table}
             WHERE sale_id='$sale_id'
             GROUP BY item_id, attr_id
             ORDER BY total DESC, item_id ASC"
        );

        if (!$result) {
            return $items;
        }

        while ($row = mysqli_fetch_assoc($result)) {
            $item_name = bill_repo_get_item_name((int)$row['item_id']);
            if ((int)$row['attr_id'] > 0) {
                $attr_name = bill_repo_get_attribute_name((int)$row['attr_id']);
                if ($attr_name !== '') {
                    $item_name .= ' (' . $attr_name . ')';
                }
            }

            $items[] = [
                'name' => $item_name,
                'qty' => (float)$row['qty'],
                'total' => (float)$row['total'],
            ];
        }

        return $items;
    }
}

if (!function_exists('bill_repo_fetch_family_bills')) {
    function bill_repo_fetch_family_bills($sale_id) {
        global $connection;

        $sale_id = (int)$sale_id;
        $bills = [];
        $result = mysqli_query(
            $connection,
            "SELECT sale_id, amount
             FROM kp_sales
             WHERE sale_id='$sale_id' OR parent='$sale_id'
             ORDER BY sale_id ASC"
        );

        if (!$result) {
            return $bills;
        }

        while ($row = mysqli_fetch_assoc($result)) {
            $bills[] = [
                'sale_id' => (int)$row['sale_id'],
                'amount' => (float)$row['amount'],
            ];
        }

        return $bills;
    }
}

if (!function_exists('bill_repo_is_parent_locked')) {
    function bill_repo_is_parent_locked($sale_id) {
        global $connection;

        $sale_id = (int)$sale_id;
        if ($sale_id < 1) {
            return false;
        }

        $open_result = mysqli_query($connection, "SELECT id FROM kp_shift_sales_open WHERE parent_sale_id='$sale_id' AND status=1 LIMIT 1");
        if ($open_result && mysqli_num_rows($open_result) > 0) {
            return true;
        }

        $closed_result = mysqli_query($connection, "SELECT id FROM kp_shift_sales_open WHERE sale_id='$sale_id' AND status=1 LIMIT 1");

        return $closed_result && mysqli_num_rows($closed_result) > 0;
    }
}

if (!function_exists('bill_repo_fetch_active_counters')) {
    function bill_repo_fetch_active_counters() {
        global $connection;

        $counters = [];
        $result = mysqli_query($connection, "SELECT cntr_id, name, dpt FROM kp_counters WHERE del_status=2 AND take_status=1 ORDER BY cntr_id ASC");
        if (!$result) {
            return $counters;
        }

        while ($row = mysqli_fetch_assoc($result)) {
            $counters[] = [
                'cntr_id' => (int)$row['cntr_id'],
                'name' => (string)$row['name'],
                'dpt' => (int)($row['dpt'] ?? 0),
            ];
        }

        return $counters;
    }
}

if (!function_exists('bill_repo_fetch_open_colors')) {
    function bill_repo_fetch_open_colors() {
        global $connection;

        $colors = [];
        $result = mysqli_query($connection, "SELECT id, color, label, color_key FROM kp_open_color ORDER BY id ASC");
        if (!$result) {
            return $colors;
        }

        while ($row = mysqli_fetch_assoc($result)) {
            $colors[] = [
                'id' => (int)($row['id'] ?? 0),
                'hex' => (string)($row['color'] ?? ''),
                'label' => (string)($row['label'] ?? ''),
                'key' => (string)($row['color_key'] ?? ''),
            ];
        }

        return $colors;
    }
}

if (!function_exists('repo_sum_sale_print')) {
    function repo_sum_sale_print($sale_id, $session_flag = null) {
        global $connection;

        $sale_id = (int)$sale_id;
        $where_flag = $session_flag === null ? '' : " AND session_flag='" . (int)$session_flag . "'";
        $result = mysqli_query($connection, "SELECT COALESCE(SUM(total), 0) AS total_amount FROM kp_sale_print WHERE sale_id='$sale_id'$where_flag");
        $row = $result ? mysqli_fetch_assoc($result) : null;

        return (float)($row['total_amount'] ?? 0);
    }
}

if (!function_exists('repo_fetch_sale_print')) {
    function repo_fetch_sale_print($sale_id, $session_flag = null) {
        global $connection;

        $sale_id = (int)$sale_id;
        $where_flag = $session_flag === null ? '' : " AND session_flag='" . (int)$session_flag . "'";
        $rows = [];
        $result = mysqli_query($connection, "SELECT * FROM kp_sale_print WHERE sale_id='$sale_id'$where_flag ORDER BY id ASC");
        if ($result) {
            while ($row = mysqli_fetch_assoc($result)) {
                $rows[] = $row;
            }
        }

        return $rows;
    }
}

if (!function_exists('repo_fetch_sale_print_grouped')) {
    function repo_fetch_sale_print_grouped($sale_id, $session_flag = null, $source_table = 'kp_sale_print') {
        global $connection;

        $sale_id = (int)$sale_id;
        $source_table = in_array($source_table, ['kp_sale_print', 'kp_sale_items_live'], true) ? $source_table : 'kp_sale_print';
        $where_flag = $session_flag === null ? '' : " AND session_flag='" . (int)$session_flag . "'";
        $rows = [];

        if (function_exists('kw_schema_ensure_column')) {
            kw_schema_ensure_column($source_table, 'accompaniment_json', "LONGTEXT NULL AFTER attr_id");
        }

        $result = mysqli_query(
            $connection,
            "SELECT SUM(qty) AS qty, item_id, SUM(total) AS total, MAX(attr_id) AS attr_id,
                    GROUP_CONCAT(COALESCE(accompaniment_json, '') SEPARATOR '|||') AS accompaniment_json,
                    MAX(dpt) AS dpt, MAX(price) AS price, hour
             FROM $source_table
             WHERE sale_id='$sale_id'$where_flag
             GROUP BY item_id, hour
             ORDER BY id ASC"
        );

        if (!$result) {
            return $rows;
        }

        while ($row = mysqli_fetch_assoc($result)) {
            $item_id = (int)($row['item_id'] ?? 0);
            $attr_id = isset($row['attr_id']) ? (int)$row['attr_id'] : 0;
            $base_name = bill_repo_get_item_name($item_id);
            $item_name = $base_name;
            $row_qty = (float)($row['qty'] ?? 0);
            $attributes = [];
            if ($attr_id > 0) {
                $attr_name = bill_repo_get_attribute_name($attr_id);
                if ($attr_name !== '') {
                    $attributes[] = ['id' => $attr_id, 'name' => $attr_name, 'qty' => $row_qty];
                }
            }
            $attribute_totals = [];
            $attribute_json_rows = explode('|||', (string)($row['accompaniment_json'] ?? ''));
            foreach ($attribute_json_rows as $attribute_json) {
                $decoded_attributes = json_decode($attribute_json, true);
                if (!is_array($decoded_attributes)) continue;
                foreach ($decoded_attributes as $attribute) {
                    if (!is_array($attribute)) continue;
                    $attribute_id = (int)($attribute['id'] ?? $attribute['attr_id'] ?? 0);
                    $attribute_name = (string)($attribute['name'] ?? $attribute['label'] ?? '');
                    if ($attribute_name === '' && $attribute_id > 0) $attribute_name = bill_repo_get_attribute_name($attribute_id);
                    if ($attribute_name === '') continue;
                    $attribute_key = $attribute_id > 0 ? 'id:' . $attribute_id : 'name:' . strtolower($attribute_name);
                    if (!isset($attribute_totals[$attribute_key])) {
                        $attribute_totals[$attribute_key] = ['id' => $attribute_id ?: null, 'name' => $attribute_name, 'qty' => 0];
                    }
                    $attribute_totals[$attribute_key]['qty'] += (float)($attribute['qty'] ?? 0);
                }
            }
            foreach ($attribute_totals as $attribute_total) $attributes[] = $attribute_total;

            $bucket = ((int)($row['hour'] ?? 0) === 2) ? 'happy_hour' : 'normal';
            $rows[] = [
                'item_id' => $item_id,
                'item_name' => $item_name,
                'name' => $item_name,
                'qty' => $row_qty,
                'base_name' => $base_name,
                'attributes' => $attributes,
                'total' => (float)($row['total'] ?? 0),
                'dpt' => (int)($row['dpt'] ?? 0),
                'price' => (float)($row['price'] ?? 0),
                'attr_id' => $attr_id > 0 ? $attr_id : null,
                'accompaniment_json' => (string)($row['accompaniment_json'] ?? ''),
                'hour' => (int)($row['hour'] ?? 0),
                'bucket' => $bucket,
            ];
        }

        return $rows;
    }
}

if (!function_exists('repo_infer_sale_counter_from_print')) {
    function repo_infer_sale_counter_from_print($sale_id) {
        global $connection;

        $sale_id = (int)$sale_id;
        if ($sale_id < 1) {
            return 0;
        }

        $result = mysqli_query(
            $connection,
            "SELECT counter, COUNT(*) AS row_count
             FROM kp_sale_print
             WHERE sale_id='$sale_id' AND session_flag=2 AND counter > 0
             GROUP BY counter
             ORDER BY row_count DESC, counter ASC
             LIMIT 1"
        );
        $row = $result ? mysqli_fetch_assoc($result) : null;
        return (int)($row['counter'] ?? 0);
    }
}

if (!function_exists('repo_mark_sale_print_sent')) {
    function repo_mark_sale_print_sent($sale_id) {
        global $connection;

        $sale_id = (int)$sale_id;
        return mysqli_query(
            $connection,
            "UPDATE kp_sale_print
             SET session_flag=1
             WHERE sale_id='$sale_id' AND session_flag=2"
        );
    }
}

if (!function_exists('repo_update_sale_print_session_flag_to_one')) {
    function repo_update_sale_print_session_flag_to_one($sale_id) {
        return repo_mark_sale_print_sent($sale_id);
    }
}

if (!function_exists('repo_upsert_sale_item')) {
    function repo_upsert_sale_item($data, $is_print_table = false, $table_override = '') {
        global $connection;

        $table = $table_override !== '' ? $table_override : ($is_print_table ? 'kp_sale_print' : 'kp_sale_items');
        if (!in_array($table, ['kp_sale_items', 'kp_sale_items_live', 'kp_sale_print'], true)) {
            $table = 'kp_sale_items_live';
        }
        if (function_exists('kw_schema_ensure_column')) {
            if ($is_print_table) {
                kw_schema_ensure_column('kp_sale_print', 'shift', "VARCHAR(60) NOT NULL DEFAULT '' AFTER total");
            }
            kw_schema_ensure_column($table, 'accompaniment_json', "LONGTEXT NULL AFTER attr_id");
            kw_schema_ensure_column($table, 'session_flag', "TINYINT(1) NOT NULL DEFAULT 2 AFTER accompaniment_json");
        }

        $item_id = (int)$data['item_id'];
        $sale_id = (int)$data['sale_id'];
        $hour = (int)$data['hour'];
        $qty = (float)$data['qty'];
        $total = (float)$data['total'];
        $user_id = (int)$data['user_id'];
        $dpt = (int)$data['dpt'];
        $price = (float)$data['price'];
        $shift = mysqli_real_escape_string($connection, (string)$data['shift']);
        $counter = (int)$data['counter'];
        $stock_counter = (int)$data['stock_counter'];
        $attr_id = isset($data['attr_id']) && $data['attr_id'] !== null && $data['attr_id'] !== '' ? (int)$data['attr_id'] : null;
        $accompaniment_json = isset($data['accompaniment_json']) && $data['accompaniment_json'] !== '' ? (string)$data['accompaniment_json'] : null;
        $accompaniment_sql = $accompaniment_json !== null ? mysqli_real_escape_string($connection, $accompaniment_json) : null;
        $session_flag = isset($data['session_flag']) ? (int)$data['session_flag'] : 2;

        $attr_check = $attr_id !== null ? "AND attr_id='$attr_id'" : "AND attr_id IS NULL";
        $acc_check = $accompaniment_sql !== null ? "AND accompaniment_json='$accompaniment_sql'" : "AND (accompaniment_json IS NULL OR accompaniment_json='')";
        $exists = mysqli_query($connection, "SELECT 1 FROM $table WHERE item_id='$item_id' AND sale_id='$sale_id' AND hour='$hour' AND counter='$counter' AND stock_counter='$stock_counter' AND session_flag='$session_flag' $attr_check $acc_check LIMIT 1");

        if ($exists && mysqli_num_rows($exists) > 0) {
            return mysqli_query($connection, "UPDATE $table SET qty=qty+'$qty', total=total+'$total' WHERE item_id='$item_id' AND sale_id='$sale_id' AND hour='$hour' AND counter='$counter' AND stock_counter='$stock_counter' AND session_flag='$session_flag' $attr_check $acc_check");
        }

        $attr_value = $attr_id !== null ? "'$attr_id'" : "NULL";
        $acc_value = $accompaniment_sql !== null ? "'$accompaniment_sql'" : "NULL";

        return mysqli_query($connection, "INSERT INTO $table (sale_id, item_id, user_id, qty, dpt, price, total, shift, counter, hour, stock_counter, attr_id, accompaniment_json, session_flag) VALUES('$sale_id', '$item_id', '$user_id', '$qty', '$dpt', '$price', '$total', '$shift', '$counter', '$hour', '$stock_counter', $attr_value, $acc_value, '$session_flag')");
    }
}

if (!function_exists('repo_insert_stock_track')) {
    function repo_insert_stock_track($data) {
        global $connection;

        $item_id = (int)$data['item_id'];
        $qty = (float)$data['qty'];
        $user_id = (int)$data['user_id'];
        $sale_id = (int)$data['sale_id'];
        $status = mysqli_real_escape_string($connection, (string)$data['status']);
        $sale_counter = (int)$data['sale_counter'];
        $stock_counter = (int)$data['stock_counter'];
        $shift = mysqli_real_escape_string($connection, (string)$data['shift']);
        $hour = (int)$data['hour'];
        $dpt = (int)$data['dpt'];

        return mysqli_query($connection, "INSERT INTO kp_stock_track (item_id, qty, user_id, sale_id, status, sale_counter, stock_counter, shift, hour, dpt) VALUES('$item_id', '$qty', '$user_id', '$sale_id', '$status', '$sale_counter', '$stock_counter', '$shift', '$hour', '$dpt') ON DUPLICATE KEY UPDATE qty = qty + $qty");
    }
}

if (!function_exists('repo_set_sale_status')) {
    function repo_set_sale_status($user_id, $sale_id, $status, $mode, $parent_status, $parent_sale_id, $counter_id = 0) {
        global $connection;

        $user_id = (int)$user_id;
        $sale_id = (int)$sale_id;
        $status = (int)$status;
        $mode = mysqli_real_escape_string($connection, bill_clean_bill_mode($mode));
        $parent_status = (int)$parent_status;
        $parent_sale_id = (int)$parent_sale_id;
        $counter_id = (int)$counter_id;

        if (function_exists('kw_ensure_sale_status_schema')) {
            kw_ensure_sale_status_schema($connection);
        }
        if (function_exists('kw_schema_ensure_column')) {
            kw_schema_ensure_column('kp_sale_status', 'sale_type', "VARCHAR(20) NOT NULL DEFAULT 'normal' AFTER bill_position");
        }
        $sale_type = ($parent_sale_id > 0 || $status === 3 || $mode === 'last') ? 'open' : 'normal';
        $sale_type = mysqli_real_escape_string($connection, $sale_type);

        return mysqli_query(
            $connection,
            "INSERT INTO kp_sale_status (user_id, sale_id, status, mode, parent_status, parent_sale_id, counter_id, counter, sale_type)
             VALUES ('$user_id', '$sale_id', '$status', '$mode', '$parent_status', '$parent_sale_id', '$counter_id', '$counter_id', '$sale_type')
             ON DUPLICATE KEY UPDATE
                user_id=VALUES(user_id),
                sale_id=VALUES(sale_id),
                status=VALUES(status),
                mode=VALUES(mode),
                parent_status=VALUES(parent_status),
                parent_sale_id=VALUES(parent_sale_id),
                counter_id=VALUES(counter_id),
                counter=VALUES(counter),
                sale_type=VALUES(sale_type)"
        );
    }
}

if (!function_exists('repo_enforce_single_sale_status')) {
    function repo_enforce_single_sale_status($user_id, $keep_sale_id = 0) {
        global $connection;
        $user_id = (int)$user_id;
        $keep_sale_id = (int)$keep_sale_id;
        $result = mysqli_query($connection, "SELECT sale_id FROM kp_sale_status WHERE user_id='$user_id'");
        if (!$result) {
            return ['ok' => false, 'message' => mysqli_error($connection)];
        }
        while ($row = mysqli_fetch_assoc($result)) {
            $sale_id = (int)($row['sale_id'] ?? 0);
            if ($sale_id < 1 || $sale_id === $keep_sale_id) {
                continue;
            }
            // An open sale is stored in kp_shift_sales_open and may retain print
            // snapshot rows for later continuation. Those snapshots must not
            // make its kp_sale_status row block a new order.
            $open_registry = function_exists('bill_repo_get_sales_open_registry')
                ? bill_repo_get_sales_open_registry($sale_id, $user_id)
                : null;
            if (is_array($open_registry) && !empty($open_registry)) {
                mysqli_query($connection, "DELETE FROM kp_sale_status WHERE user_id='$user_id' AND sale_id='$sale_id'");
                continue;
            }
            $live_count = (int)column_count('kp_sale_items_live', 'item_id', "sale_id='$sale_id'");
            $print_count = (int)column_count('kp_sale_print', 'item_id', "sale_id='$sale_id' AND session_flag=2");
            if (($live_count + $print_count) > 0) {
                return ['ok' => false, 'sale_id' => $sale_id, 'message' => 'Another sale with items is already active. Continue or cancel it first.'];
            }
            mysqli_query($connection, "DELETE FROM kp_sale_status WHERE user_id='$user_id' AND sale_id='$sale_id'");
        }
        return ['ok' => true];
    }
}

if (!function_exists('repo_set_sale_position')) {
    function repo_set_sale_position($user_id, $sale_id, $position) {
        global $connection;

        $user_id = (int)$user_id;
        $sale_id = (int)$sale_id;
        $position = (int)$position;
        if ($user_id < 1 || $sale_id < 1 || $position < 1) {
            return false;
        }
        if (function_exists('kw_ensure_sale_status_schema')) {
            kw_ensure_sale_status_schema($connection);
        }
        return mysqli_query(
            $connection,
            "UPDATE kp_sale_status
             SET bill_position='$position'
             WHERE user_id='$user_id' AND sale_id='$sale_id'"
        );
    }
}

if (!function_exists('repo_clear_sale_status')) {
    function repo_clear_sale_status($user_id, $sale_id = 0) {
        global $connection;

        $user_id = (int)$user_id;
        $sale_id = (int)$sale_id;
        $where_sale = $sale_id > 0 ? " AND sale_id='$sale_id'" : '';

        return mysqli_query($connection, "DELETE FROM kp_sale_status WHERE user_id='$user_id'$where_sale");
    }
}

if (!function_exists('repo_clear_open_family_status')) {
    function repo_clear_open_family_status($user_id, $parent_sale_id) {
        global $connection;

        $user_id = (int)$user_id;
        $parent_sale_id = (int)$parent_sale_id;
        if ($user_id < 1 || $parent_sale_id < 1) {
            return false;
        }

        return mysqli_query(
            $connection,
            "DELETE FROM kp_sale_status
             WHERE user_id='$user_id'
               AND (sale_id='$parent_sale_id' OR parent_sale_id='$parent_sale_id')"
        );
    }
}

if (!function_exists('repo_clear_sale_print')) {
    function repo_clear_sale_print($sale_id) {
        global $connection;

        $sale_id = (int)$sale_id;
        return mysqli_query($connection, "DELETE FROM kp_sale_print WHERE sale_id='$sale_id'");
    }
}

if (!function_exists('repo_insert_sales_header')) {
    function repo_insert_sales_header($data) {
        global $connection;

        $sale_id = (int)($data['sale_id'] ?? 0);
        $user_id = (int)($data['user_id'] ?? 0);
        $shift = mysqli_real_escape_string($connection, (string)($data['shift'] ?? ''));
        $amount = (float)($data['amount'] ?? 0);
        $counter = (int)($data['counter'] ?? 0);
        $bill_counter = (int)($data['bill_counter'] ?? $counter);
        $parent_sale_id = (int)($data['parent_sale_id'] ?? 0);

        if ($sale_id < 1 || $user_id < 1 || $amount < 0) {
            return false;
        }

        static $kp_sales_has_bill_counter = null;
        static $kp_sales_has_print_cntr_id = null;
        if ($kp_sales_has_bill_counter === null) {
            $check = mysqli_query($connection, "SHOW COLUMNS FROM kp_sales LIKE 'bill_counter'");
            $kp_sales_has_bill_counter = $check && mysqli_num_rows($check) > 0;
        }
        if ($kp_sales_has_print_cntr_id === null) {
            $check = mysqli_query($connection, "SHOW COLUMNS FROM kp_sales LIKE 'print_cntr_id'");
            $kp_sales_has_print_cntr_id = $check && mysqli_num_rows($check) > 0;
        }

        $columns = "sale_id, user_id, shift, amount, counter";
        $values = "'$sale_id', '$user_id', '$shift', '$amount', '$counter'";
        $update = "shift=VALUES(shift), amount=VALUES(amount), counter=VALUES(counter)";

        if ($kp_sales_has_bill_counter) {
            $columns .= ", bill_counter";
            $values .= ", '$bill_counter'";
            $update .= ", bill_counter=VALUES(bill_counter)";
        } elseif ($kp_sales_has_print_cntr_id) {
            $columns .= ", print_cntr_id";
            $values .= ", '$bill_counter'";
            $update .= ", print_cntr_id=VALUES(print_cntr_id)";
        }

        if ($parent_sale_id > 0) {
            $columns .= ", parent";
            $values .= ", '$parent_sale_id'";
            $update .= ", parent=VALUES(parent)";
        }

        return mysqli_query(
            $connection,
            "INSERT INTO kp_sales ($columns)
             VALUES ($values)
             ON DUPLICATE KEY UPDATE $update"
        );
    }
}

if (!function_exists('repo_insert_sale_items_from_print')) {
    function repo_insert_sale_items_from_print($sale_id) {
        global $connection;

        $sale_id = (int)$sale_id;
        if ($sale_id < 1) {
            return false;
        }

        if (function_exists('kw_schema_ensure_column')) {
            kw_schema_ensure_column('kp_sale_items', 'accompaniment_json', "LONGTEXT NULL AFTER attr_id");
            kw_schema_ensure_column('kp_sale_items', 'session_flag', "TINYINT(1) NOT NULL DEFAULT 2 AFTER accompaniment_json");
            kw_schema_ensure_column('kp_sale_print', 'accompaniment_json', "LONGTEXT NULL AFTER attr_id");
            kw_schema_ensure_column('kp_sale_print', 'session_flag', "TINYINT(1) NOT NULL DEFAULT 2 AFTER accompaniment_json");
        }

        mysqli_query($connection, "DELETE FROM kp_sale_items WHERE sale_id='$sale_id'");

        return mysqli_query(
            $connection,
            "INSERT INTO kp_sale_items (sale_id, item_id, user_id, qty, dpt, price, total, shift, counter, hour, stock_counter, attr_id, accompaniment_json, session_flag)
             SELECT sale_id, item_id, user_id, qty, dpt, price, total, shift, counter, hour, stock_counter, attr_id, accompaniment_json, session_flag
             FROM kp_sale_print
             WHERE sale_id='$sale_id'"
        );
    }
}

if (!function_exists('repo_sum_shift_open_items')) {
    function repo_sum_shift_open_items($sale_id) {
        global $connection;
        $sale_id = (int)$sale_id;
        $result = mysqli_query($connection, "SELECT COALESCE(SUM(total), 0) AS total_amount FROM kp_shift_sale_open_items WHERE sale_id='$sale_id'");
        $row = $result ? mysqli_fetch_assoc($result) : null;
        return (float)($row['total_amount'] ?? 0);
    }
}

if (!function_exists('repo_insert_sale_items_from_shift_open')) {
    function repo_insert_sale_items_from_shift_open($sale_id) {
        global $connection;
        $sale_id = (int)$sale_id;
        if ($sale_id < 1) return false;
        if (function_exists('kw_schema_ensure_column')) {
            kw_schema_ensure_column('kp_sale_items', 'accompaniment_json', "LONGTEXT NULL AFTER attr_id");
            kw_schema_ensure_column('kp_sale_items', 'session_flag', "TINYINT(1) NOT NULL DEFAULT 2 AFTER accompaniment_json");
            kw_schema_ensure_column('kp_sale_items', 'hour', "VARCHAR(20) NOT NULL DEFAULT '' AFTER counter");
        }
        mysqli_query($connection, "DELETE FROM kp_sale_items WHERE sale_id='$sale_id'");
        return mysqli_query($connection,
            "INSERT INTO kp_sale_items (sale_id,item_id,user_id,qty,dpt,price,total,shift,counter,hour,stock_counter,attr_id,accompaniment_json,session_flag)
             SELECT sale_id,item_id,user_id,qty,dpt,price,total,shift,counter,hour,stock_counter,attr_id,accompaniment_json,session_flag
             FROM kp_shift_sale_open_items WHERE sale_id='$sale_id'"
        );
    }
}

if (!function_exists('repo_update_sale_family_context')) {
    function repo_update_sale_family_context($sale_id, $parent_sale_id = 0) {
        global $connection;

        $sale_id = (int)$sale_id;
        $parent_sale_id = (int)$parent_sale_id;
        if ($sale_id < 1) {
            return false;
        }

        if (function_exists('kw_schema_ensure_column')) {
            kw_schema_ensure_column('kp_sales', 'root_parent_sale_id', "INT NOT NULL DEFAULT 0 AFTER bill_role");
            kw_schema_ensure_column('kp_sales', 'previous_sale_id', "INT NOT NULL DEFAULT 0 AFTER root_parent_sale_id");
            kw_schema_ensure_column('kp_sales', 'previous_balance', "DECIMAL(10,2) NOT NULL DEFAULT 0.00 AFTER previous_sale_id");
            kw_schema_ensure_column('kp_sales', 'current_total', "DECIMAL(10,2) NOT NULL DEFAULT 0.00 AFTER previous_balance");
            kw_schema_ensure_column('kp_sales', 'current_balance', "DECIMAL(10,2) NOT NULL DEFAULT 0.00 AFTER current_total");
        }

        $sale = repo_get_sales_header($sale_id);
        $amount = (float)($sale['amount'] ?? 0);
        if (!$sale) {
            return false;
        }

        if ($parent_sale_id < 1) {
            return mysqli_query(
                $connection,
                "UPDATE kp_sales
                 SET root_parent_sale_id=0,
                     previous_sale_id=0,
                     previous_balance=0.00,
                     current_total='$amount',
                     current_balance='$amount'
                 WHERE sale_id='$sale_id'"
            );
        }

        $previous_balance = (float)bill_repo_get_sale_amount($parent_sale_id);
        $previous_balance += (float)column_total('kp_sales', 'amount', "parent='$parent_sale_id' AND sale_id<'$sale_id'");

        $previous_sale_query = mysqli_query(
            $connection,
            "SELECT sale_id
             FROM kp_sales
             WHERE sale_id='$parent_sale_id'
                OR (parent='$parent_sale_id' AND sale_id<'$sale_id')
             ORDER BY sale_id DESC
             LIMIT 1"
        );
        $previous_sale_row = $previous_sale_query ? mysqli_fetch_assoc($previous_sale_query) : null;
        $previous_sale_id = (int)($previous_sale_row['sale_id'] ?? $parent_sale_id);
        $current_balance = $previous_balance + $amount;

        return mysqli_query(
            $connection,
            "UPDATE kp_sales
             SET parent='$parent_sale_id',
                 root_parent_sale_id='$parent_sale_id',
                 previous_sale_id='$previous_sale_id',
                 previous_balance='$previous_balance',
                 current_total='$amount',
                 current_balance='$current_balance'
             WHERE sale_id='$sale_id'"
        );
    }
}

if (!function_exists('repo_normalize_open_identifier')) {
    function repo_normalize_open_identifier($identifier = []) {
        return [
            'type' => trim((string)($identifier['type'] ?? $identifier['identifier_type'] ?? '')),
            'value' => trim((string)($identifier['value'] ?? $identifier['identifier_value'] ?? '')),
            'label' => trim((string)($identifier['label'] ?? $identifier['identifier_label'] ?? '')),
            'hex' => trim((string)($identifier['hex'] ?? $identifier['identifier_hex'] ?? '')),
        ];
    }
}

if (!function_exists('repo_register_parent_closed_opened')) {
    function repo_register_parent_closed_opened($parent_sale_id, $mode, $identifier, $user_id) {
        global $connection;

        $parent_sale_id = (int)$parent_sale_id;
        $user_id = (int)$user_id;
        $mode = mysqli_real_escape_string($connection, bill_clean_bill_mode($mode));
        $shift = mysqli_real_escape_string($connection, repo_get_shift());
        $identifier = repo_normalize_open_identifier($identifier);
        $identifier_type = mysqli_real_escape_string($connection, (string)$identifier['type']);
        $identifier_value = mysqli_real_escape_string($connection, (string)$identifier['value']);
        $identifier_label = mysqli_real_escape_string($connection, (string)$identifier['label']);
        $identifier_hex = mysqli_real_escape_string($connection, (string)$identifier['hex']);

        if (function_exists('kw_schema_ensure_column')) {
            kw_schema_ensure_column('kp_shift_sales_open', 'shift', "VARCHAR(40) NOT NULL DEFAULT '' AFTER bill_mode");
        }

        return mysqli_query(
            $connection,
            "INSERT INTO kp_shift_sales_open (sale_id, parent_sale_id, user_id, identifier_type, identifier_value, identifier_label, identifier_hex, bill_mode, sale_mode, order_stage, shift, status)
             VALUES ('$parent_sale_id', '$parent_sale_id', '$user_id', '$identifier_type', '$identifier_value', '$identifier_label', '$identifier_hex', '$mode', 'open', 'FRESH', '$shift', 1)
             ON DUPLICATE KEY UPDATE
                user_id=VALUES(user_id),
                identifier_type=VALUES(identifier_type),
                identifier_value=VALUES(identifier_value),
                identifier_label=VALUES(identifier_label),
                identifier_hex=VALUES(identifier_hex),
                bill_mode=VALUES(bill_mode),
                sale_mode='open',
                order_stage='FRESH',
                shift=VALUES(shift),
                status=1"
        );
    }
}

if (!function_exists('repo_sync_sales_open_identifier')) {
    function repo_sync_sales_open_identifier($parent_sale_id, $user_id, $identifier, $mode = 'last') {
        global $connection;

        $parent_sale_id = (int)$parent_sale_id;
        $user_id = (int)$user_id;
        $mode = bill_clean_bill_mode($mode);
        $identifier = repo_normalize_open_identifier($identifier);
        $identifier_type = mysqli_real_escape_string($connection, (string)$identifier['type']);
        $identifier_value = mysqli_real_escape_string($connection, (string)$identifier['value']);
        $identifier_label = mysqli_real_escape_string($connection, (string)$identifier['label']);
        $identifier_hex = mysqli_real_escape_string($connection, (string)$identifier['hex']);

        if ($parent_sale_id < 1 || $user_id < 1) {
            return false;
        }

        if (function_exists('kw_schema_ensure_column')) {
            kw_schema_ensure_column('kp_shift_sales_open', 'identifier_type', "VARCHAR(55) NOT NULL DEFAULT '' AFTER bill_mode");
            kw_schema_ensure_column('kp_shift_sales_open', 'identifier_value', "VARCHAR(255) NOT NULL DEFAULT '' AFTER identifier_type");
            kw_schema_ensure_column('kp_shift_sales_open', 'identifier_label', "VARCHAR(255) NOT NULL DEFAULT '' AFTER identifier_value");
            kw_schema_ensure_column('kp_shift_sales_open', 'identifier_hex', "VARCHAR(40) NOT NULL DEFAULT '' AFTER identifier_label");
        }

        return mysqli_query(
            $connection,
            "UPDATE kp_shift_sales_open
             SET identifier_type='$identifier_type',
                 identifier_value='$identifier_value',
                 identifier_label='$identifier_label',
                 identifier_hex='$identifier_hex',
                 bill_mode='" . mysqli_real_escape_string($connection, $mode) . "'
             WHERE parent_sale_id='$parent_sale_id' AND user_id='$user_id' AND status=1"
        );
    }
}

if (!function_exists('repo_upsert_sales_open')) {
    function repo_upsert_sales_open($data) {
        global $connection;

        $sale_id = (int)($data['sale_id'] ?? 0);
        $parent_sale_id = (int)($data['parent_sale_id'] ?? 0);
        $child_sale_id = (int)($data['child_sale_id'] ?? 0);
        $user_id = (int)($data['user_id'] ?? 0);
        $bill_mode = mysqli_real_escape_string($connection, bill_clean_bill_mode($data['bill_mode'] ?? 'last'));
        $counter_id = (int)($data['counter_id'] ?? 0);
        $print_cntr_id = (int)($data['print_cntr_id'] ?? $counter_id);
        $shift = mysqli_real_escape_string($connection, (string)($data['shift'] ?? ''));
        $identifier_type = mysqli_real_escape_string($connection, (string)($data['identifier_type'] ?? ''));
        $identifier_value = mysqli_real_escape_string($connection, (string)($data['identifier_value'] ?? ''));
        $identifier_label = mysqli_real_escape_string($connection, (string)($data['identifier_label'] ?? ''));
        $identifier_hex = mysqli_real_escape_string($connection, (string)($data['identifier_hex'] ?? ''));
        $status = (int)($data['status'] ?? 1);

        return mysqli_query(
            $connection,
            "INSERT INTO kp_shift_sales_open (sale_id, parent_sale_id, child_sale_id, user_id, bill_mode, identifier_type, identifier_value, identifier_label, identifier_hex, counter_id, print_cntr_id, shift, status)
             VALUES ('$sale_id', '$parent_sale_id', '$child_sale_id', '$user_id', '$bill_mode', '$identifier_type', '$identifier_value', '$identifier_label', '$identifier_hex', '$counter_id', '$print_cntr_id', '$shift', '$status')
             ON DUPLICATE KEY UPDATE
                parent_sale_id=VALUES(parent_sale_id),
                child_sale_id=VALUES(child_sale_id),
                user_id=VALUES(user_id),
                bill_mode=VALUES(bill_mode),
                identifier_type=VALUES(identifier_type),
                identifier_value=VALUES(identifier_value),
                identifier_label=VALUES(identifier_label),
                identifier_hex=VALUES(identifier_hex),
                counter_id=VALUES(counter_id),
                print_cntr_id=VALUES(print_cntr_id),
                shift=VALUES(shift),
                status=VALUES(status)"
        );
    }
}

if (!function_exists('repo_update_sales_open_family_total')) {
    function repo_update_sales_open_family_total($sale_id, $parent_sale_id = 0, $family_total = 0) {
        global $connection;

        $sale_id = (int)$sale_id;
        $parent_sale_id = (int)$parent_sale_id;
        $family_total = (float)$family_total;

        if ($sale_id < 1 && $parent_sale_id < 1) {
            return false;
        }

        if (function_exists('kw_schema_ensure_column')) {
            kw_schema_ensure_column('kp_shift_sales_open', 'family_total', "DECIMAL(10,2) NOT NULL DEFAULT 0.00 AFTER shift");
        }

        $where = [];
        if ($sale_id > 0) {
            $where[] = "sale_id='$sale_id'";
            $where[] = "child_sale_id='$sale_id'";
        }
        if ($parent_sale_id > 0) {
            $where[] = "parent_sale_id='$parent_sale_id'";
            if ($sale_id < 1) {
                $where[] = "sale_id='$parent_sale_id'";
            }
        }

        return mysqli_query(
            $connection,
            "UPDATE kp_shift_sales_open
             SET family_total='$family_total'
             WHERE " . implode(' OR ', $where)
        );
    }
}

if (!function_exists('repo_clear_sales_open')) {
    function repo_clear_sales_open($sale_id, $parent_sale_id = 0) {
        global $connection;

        $sale_id = (int)$sale_id;
        $parent_sale_id = (int)$parent_sale_id;

        return mysqli_query(
            $connection,
            "DELETE FROM kp_shift_sales_open WHERE sale_id='$sale_id' OR child_sale_id='$sale_id' OR parent_sale_id='$parent_sale_id'"
        );
    }
}

if (!function_exists('repo_clear_closed_opened_parent')) {
    function repo_clear_closed_opened_parent($parent_sale_id) {
        global $connection;

        $parent_sale_id = (int)$parent_sale_id;
        return mysqli_query($connection, "DELETE FROM kp_shift_sales_open WHERE sale_id='$parent_sale_id'");
    }
}

if (!function_exists('repo_update_closed_opened_family_total')) {
    function repo_update_closed_opened_family_total($parent_sale_id, $family_total) {
        global $connection;

        $parent_sale_id = (int)$parent_sale_id;
        $family_total = (float)$family_total;

        if ($parent_sale_id < 1) {
            return false;
        }

        if (function_exists('kw_schema_ensure_column')) {
            kw_schema_ensure_column('kp_shift_sales_open', 'family_total', "DECIMAL(10,2) NOT NULL DEFAULT 0.00 AFTER status");
        }

        return mysqli_query(
            $connection,
            "UPDATE kp_shift_sales_open
             SET family_total='$family_total'
             WHERE sale_id='$parent_sale_id'"
        );
    }
}

if (!function_exists('bill_repo_print_queue')) {
    function bill_repo_print_queue($data) {
        global $connection;

        if (function_exists('kp_print_enqueue')) {
            return (int)kp_print_enqueue($connection, $data);
        }

        // V1 queue fallback: create a complete immutable document snapshot
        // directly from the finalized sale. This keeps Kwedia procedural while
        // making the queued job renderable by front/Chaba V1.
        $sale_id = (int)($data['sale_id'] ?? 0);
        $user_id = (int)($data['requested_by'] ?? 0);
        if ($sale_id < 1 || $user_id < 1) return 0;

        // Heal queue columns used by V1 before inserting. This is intentionally
        // additive: existing queue data is preserved and older installations
        // can place closed orders without a manual migration first.
        $queue_columns = [
            'shift' => "VARCHAR(60) NOT NULL DEFAULT ''",
            'payload_json' => 'LONGTEXT NULL',
            'items_json' => 'LONGTEXT NULL',
            'print_attempts' => "TINYINT UNSIGNED NOT NULL DEFAULT 0",
            'last_error' => "VARCHAR(255) NOT NULL DEFAULT ''",
            'printed_at' => 'DATETIME NULL DEFAULT NULL',
        ];
        $existing_columns = [];
        $column_result = mysqli_query($connection, 'SHOW COLUMNS FROM kp_print_queue');
        while ($column_result && ($column_row = mysqli_fetch_assoc($column_result))) $existing_columns[(string)$column_row['Field']] = true;
        foreach ($queue_columns as $column => $definition) {
            if (!isset($existing_columns[$column])) mysqli_query($connection, "ALTER TABLE kp_print_queue ADD COLUMN `{$column}` {$definition}");
        }

        $items = [];
        $result = mysqli_query($connection, "SELECT si.item_id, si.qty, si.price, si.total, si.attr_id, si.accompaniment_json, COALESCE(i.name, CONCAT('Item ', si.item_id)) item_name
            FROM kp_sale_print si LEFT JOIN kp_items i ON i.item_id=si.item_id
            WHERE si.sale_id='{$sale_id}' ORDER BY si.item_id");
        while ($result && ($row = mysqli_fetch_assoc($result))) {
            $attributes = [];
            $raw = json_decode((string)($row['accompaniment_json'] ?? ''), true);
            if (is_array($raw)) {
                foreach (['attributes','accompaniments','selections','items'] as $key) if (isset($raw[$key]) && is_array($raw[$key])) { $raw = $raw[$key]; break; }
                if (isset($raw['name']) || isset($raw['label']) || isset($raw['title'])) $raw = [$raw];
                foreach ($raw as $attribute) {
                    $name = is_string($attribute) ? trim($attribute) : (is_array($attribute) ? trim((string)($attribute['name'] ?? $attribute['label'] ?? $attribute['title'] ?? '')) : '');
                    if ($name !== '') $attributes[] = ['name' => $name, 'qty' => is_array($attribute) && (float)($attribute['qty'] ?? 1) > 0 ? (float)$attribute['qty'] : 1];
                }
            }
            $attrId = (int)($row['attr_id'] ?? 0);
            if ($attrId > 0) { $attrQ = mysqli_query($connection, "SELECT name FROM kp_items_attributes WHERE id='{$attrId}' LIMIT 1"); $attrRow = $attrQ ? mysqli_fetch_assoc($attrQ) : null; if ($attrRow && trim((string)$attrRow['name']) !== '') array_unshift($attributes, ['name' => trim((string)$attrRow['name']), 'qty' => 1]); }
            $displayName = '__CHABA_ITEM__' . json_encode(['name' => (string)$row['item_name'], 'attributes' => $attributes], JSON_UNESCAPED_UNICODE | JSON_UNESCAPED_SLASHES);
            $items[] = [
                'name' => $displayName,
                'qty' => (float)$row['qty'],
                'price' => (float)$row['price'],
                'total' => (float)$row['total'],
                'item_id' => (int)$row['item_id'],
                'attributes' => $attributes,
            ];
        }
        if (strtoupper((string)($data['print_type'] ?? '')) === 'BILL' && count($items) > 1) {
            $merged = [];
            foreach ($items as $item) {
                $key = (int)$item['item_id'] . '|' . (int)($item['attr_id'] ?? 0) . '|' . json_encode($item['attributes'] ?? []);
                if (!isset($merged[$key])) {
                    $merged[$key] = $item;
                    continue;
                }
                $merged[$key]['qty'] = (float)$merged[$key]['qty'] + (float)$item['qty'];
                $merged[$key]['total'] = (float)$merged[$key]['total'] + (float)$item['total'];
            }
            $items = array_values($merged);
        }
        if (!$items) return 0;

        $amount = 0.0;
        foreach ($items as $item) $amount += (float)$item['total'];
        $payload = json_encode([
            'payload_version' => 1,
            'document_type' => strtoupper((string)($data['print_type'] ?? 'BILL')),
            'title' => (string)($data['label'] ?? 'BILL'),
            'items' => $items,
            'summary' => ['item_count' => count($items), 'amount' => $amount, 'total' => $amount],
            'header' => ['sale_id' => $sale_id],
        ], JSON_UNESCAPED_UNICODE | JSON_UNESCAPED_SLASHES);
        if ($payload === false) return 0;

        $sale_id_sql = (string)$sale_id;
        $ref_id = (int)($data['ref_id'] ?? $sale_id);
        $print_type = mysqli_real_escape_string($connection, (string)($data['print_type'] ?? 'bill'));
        $label = mysqli_real_escape_string($connection, (string)($data['label'] ?? ''));
        $counter = (int)($data['cntr_id'] ?? 0);
        $route_path = mysqli_real_escape_string($connection, (string)($data['route_path'] ?? ''));
        $route_query = mysqli_real_escape_string($connection, (string)($data['route_query'] ?? ''));
        $shift = mysqli_real_escape_string($connection, (string)($data['shift'] ?? ''));
        $payload_sql = mysqli_real_escape_string($connection, $payload);
        $sql = "INSERT INTO kp_print_queue
            (sale_id,ref_id,print_type,label,cntr_id,route_path,route_query,requested_by,reprint_of,status,print_attempts,last_error,shift,payload_json,items_json)
            VALUES ('{$sale_id_sql}','{$ref_id}','{$print_type}','{$label}','{$counter}','{$route_path}','{$route_query}','{$user_id}',0,0,0,'','{$shift}','{$payload_sql}','')";
        if (!mysqli_query($connection, $sql)) return 0;
        return (int)mysqli_insert_id($connection);
    }
}

if (!function_exists('repo_insert_sale')) {
    function repo_insert_sale($data) {
        return repo_insert_sales_header($data);
    }
}

if (!function_exists('repo_insert_sale_items')) {
    function repo_insert_sale_items($sale_id) {
        return repo_insert_sale_items_from_print($sale_id);
    }
}

if (!function_exists('repo_update_sale_status')) {
    function repo_update_sale_status($user_id, $sale_id, $status, $mode, $parent_status, $parent_sale_id) {
        return repo_set_sale_status($user_id, $sale_id, $status, $mode, $parent_status, $parent_sale_id);
    }
}

if (!function_exists('repo_insert_closed_opened')) {
    function repo_insert_closed_opened($parent_sale_id, $mode, $identifier, $user_id) {
        return repo_register_parent_closed_opened($parent_sale_id, $mode, $identifier, $user_id);
    }
}

if (!function_exists('repo_clear_sale_open')) {
    function repo_clear_sale_open($sale_id, $parent_sale_id = 0) {
        return repo_clear_sales_open($sale_id, $parent_sale_id);
    }
}

if (!function_exists('repo_clear_sale_closed_open')) {
    function repo_clear_sale_closed_open($parent_sale_id) {
        return repo_clear_closed_opened_parent($parent_sale_id);
    }
}

if (!function_exists('repo_print_queue')) {
    function repo_print_queue($data) {
        return bill_repo_print_queue($data);
    }
}

if (!function_exists('repo_reset_thesion_front')) {
    function repo_reset_thesion_front($user_id) {
        global $connection;

        $user_id = (int)$user_id;
        return mysqli_query(
            $connection,
            "UPDATE kp_thesion_front
             SET sale_id=0, status=0
             WHERE user_id='$user_id'"
        );
    }
}

if (!function_exists('bill_repo_get_items')) {
    function bill_repo_get_items($user_id, $category_id, $subcategory_id = 0) {
        global $connection;

        $user_id = (int)$user_id;
        $category_id = (int)$category_id;
        $subcategory_id = (int)$subcategory_id;
        $counter_id = function_exists('kw_api_user_counter_id') ? (int)kw_api_user_counter_id($user_id) : 0;
        $department = function_exists('kw_api_counter_department') ? (int)kw_api_counter_department($counter_id) : 0;
        $items = [];

        if ($category_id < 1 && $subcategory_id < 1) {
            return $items;
        }

        $where = ["del_status=2", "menu=1"];
        if ($subcategory_id > 0) {
            $where[] = "sub_id='$subcategory_id'";
        } else {
            $where[] = "cat_id='$category_id'";
        }
        if ($department === 2) {
            $where[] = "dpt='2'";
        }

        $sql = "SELECT * FROM kp_items WHERE " . implode(' AND ', $where) . " ORDER BY name ASC";
        $query = mysqli_query($connection, $sql);
        if (!$query) {
            return $items;
        }

        while ($row = mysqli_fetch_assoc($query)) {
            $item_counter_id = function_exists('kw_api_item_counter_id')
                ? (int)kw_api_item_counter_id($counter_id, isset($row['dpt']) ? (int)$row['dpt'] : 0)
                : $counter_id;
            $stock_meta = function_exists('kw_api_item_stock_meta')
                ? kw_api_item_stock_meta($row, $item_counter_id)
                : ['qty' => 0, 'reorder' => 0, 'stock_color' => 'green'];

            $items[] = [
                'item_id' => (int)$row['item_id'],
                'name' => (string)$row['name'],
                'cat_id' => (int)$row['cat_id'],
                'sub_id' => (int)$row['sub_id'],
                'deci' => isset($row['deci']) ? (int)$row['deci'] : 0,
                'has_attributes' => isset($row['has_attributes']) ? (int)$row['has_attributes'] : 0,
                'stock' => isset($row['stock']) ? (int)$row['stock'] : 0,
                'qty' => $stock_meta['qty'],
                'reorder' => $stock_meta['reorder'],
                'stock_color' => $stock_meta['stock_color'],
            ];
        }

        return $items;
    }
}

if (!function_exists('bill_repo_get_categories')) {
    function bill_repo_get_categories($user_id) {
        global $connection;

        $user_id = (int)$user_id;
        $counter_id = function_exists('kw_api_user_counter_id') ? (int)kw_api_user_counter_id($user_id) : 0;
        $department = function_exists('kw_api_counter_department') ? (int)kw_api_counter_department($counter_id) : 0;
        $filter = '';
        $categories = [];

        if ($department === 2) {
            $filter = "WHERE dpt='2'";
        }

        $query = mysqli_query($connection, "SELECT cat_id, name FROM kp_cat $filter ORDER BY name ASC");
        if ($query) {
            while ($row = mysqli_fetch_assoc($query)) {
                $categories[] = [
                    'cat_id' => (int)$row['cat_id'],
                    'name' => (string)$row['name'],
                ];
            }
        }

        return $categories;
    }
}

if (!function_exists('bill_repo_get_subcategories')) {
    function bill_repo_get_subcategories($category_id) {
        global $connection;

        $category_id = (int)$category_id;
        $subcategories = [];

        if ($category_id < 1) {
            return $subcategories;
        }

        $query = mysqli_query($connection, "SELECT sub_id, name FROM kp_sub_cat WHERE cat_id='$category_id' ORDER BY name ASC");
        if ($query) {
            while ($row = mysqli_fetch_assoc($query)) {
                $subcategories[] = [
                    'sub_id' => (int)$row['sub_id'],
                    'name' => (string)$row['name'],
                ];
            }
        }

        return $subcategories;
    }
}

if (!function_exists('bill_repo_delete_item')) {
    function bill_repo_delete_item($user_id, $item_id, $hour = 0, $attr_id = null, $accompaniment_json = null) {
        global $connection;

        $user_id = (int)$user_id;
        $item_id = (int)$item_id;
        $hour = (int)$hour;
        $sale_id = bill_repo_get_active_sale_id($user_id);

        if ($sale_id < 1 || $item_id < 1) {
            return false;
        }

        $rows_to_return = [];
        $fetch_rows = mysqli_query(
            $connection,
            "SELECT item_id, qty, stock_counter
             FROM kp_sale_items_live
             WHERE sale_id='$sale_id' AND item_id='$item_id'"
        );
        if ($fetch_rows) {
            while ($row = mysqli_fetch_assoc($fetch_rows)) {
                $rows_to_return[] = [
                    'item_id' => (int)($row['item_id'] ?? 0),
                    'qty' => (float)($row['qty'] ?? 0),
                    'stock_counter' => (int)($row['stock_counter'] ?? 0),
                ];
            }
        }

        foreach ($rows_to_return as $row) {
            $return_result = repo_return_item_stock_to_counter(
                (int)$row['item_id'],
                (int)$row['stock_counter'],
                (float)$row['qty']
            );
            if (empty($return_result['ok'])) {
                return false;
            }
        }

        mysqli_query($connection, "DELETE FROM kp_sale_items_live WHERE sale_id='$sale_id' AND item_id='$item_id'");
        mysqli_query($connection, "DELETE FROM kp_stock_track WHERE sale_id='$sale_id' AND item_id='$item_id'");

        return true;
    }
}

if (!function_exists('bill_repo_cancel_order')) {
    function bill_repo_cancel_order($user_id) {
        global $connection;

        $user_id = (int)$user_id;
        $sale_id = bill_repo_get_active_sale_id($user_id);

        if ($sale_id < 1) {
            return false;
        }

        $rows_to_return = [];
        $fetch_rows = mysqli_query(
            $connection,
            "SELECT item_id, qty, stock_counter
             FROM kp_sale_print
             WHERE sale_id='$sale_id'"
        );
        if ($fetch_rows) {
            while ($row = mysqli_fetch_assoc($fetch_rows)) {
                $rows_to_return[] = [
                    'item_id' => (int)($row['item_id'] ?? 0),
                    'qty' => (float)($row['qty'] ?? 0),
                    'stock_counter' => (int)($row['stock_counter'] ?? 0),
                ];
            }
        }

        foreach ($rows_to_return as $row) {
            $return_result = repo_return_item_stock_to_counter(
                (int)$row['item_id'],
                (int)$row['stock_counter'],
                (float)$row['qty']
            );
            if (empty($return_result['ok'])) {
                return false;
            }
        }

        mysqli_query($connection, "DELETE FROM kp_sale_print WHERE sale_id='$sale_id'");
        mysqli_query($connection, "DELETE FROM kp_stock_track WHERE sale_id='$sale_id'");

        return true;
    }
}

?>

