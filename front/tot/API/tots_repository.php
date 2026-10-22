<?php

if (!function_exists('tots_repo_get_session_context')) {
    function tots_repo_get_session_context($user_id) {
        $user_id = (int)$user_id;
        $active_shift = tots_repo_get_active_shift();
        $user = tots_repo_get_user_row($user_id);
        $counter = (int)($user['counter'] ?? (function_exists('kp_tot_user_counter') ? (int)kp_tot_user_counter($user_id) : 0));
        $dpt = '';

        if ($counter > 0) {
            $counter_row = tots_repo_get_counter_row($counter);
            $dpt = isset($counter_row['dpt']) ? (string)$counter_row['dpt'] : '';
        }

        return [
            'user_id' => $user_id,
            'tot_id' => isset($_SESSION['tot']) ? (int)$_SESSION['tot'] : 0,
            'shift' => $active_shift !== '' ? $active_shift : (isset($_SESSION['shift']) ? (string)$_SESSION['shift'] : ''),
            'dpt' => $dpt !== '' ? $dpt : (isset($_SESSION['dpt']) ? (string)$_SESSION['dpt'] : ''),
            'counter' => $counter,
        ];
    }
}

if (!function_exists('tots_repo_get_user_row')) {
    function tots_repo_get_user_row($user_id) {
        global $connection;

        $user_id = (int)$user_id;
        if ($user_id < 1) {
            return null;
        }

        $result = mysqli_query($connection, "SELECT * FROM kp_users WHERE user_id='{$user_id}' LIMIT 1");

        return $result ? mysqli_fetch_assoc($result) : null;
    }
}

if (!function_exists('tots_repo_get_active_shift')) {
    function tots_repo_get_active_shift() {
        if (function_exists('get_column')) {
            return (string)get_column('kp_shift', 'shift', "1");
        }

        return '';
    }
}

if (!function_exists('tots_repo_get_counter_row')) {
    function tots_repo_get_counter_row($counter_id) {
        global $connection;

        $counter_id = (int)$counter_id;
        if ($counter_id < 1) {
            return null;
        }

        $result = mysqli_query($connection, "SELECT * FROM kp_counters WHERE cntr_id='{$counter_id}' LIMIT 1");

        return $result ? mysqli_fetch_assoc($result) : null;
    }
}

if (!function_exists('tots_repo_user_is_active')) {
    function tots_repo_user_is_active($user_row) {
        global $connection;

        if (!$user_row || !is_array($user_row)) {
            return false;
        }

        if (kp_schema_column_exists($connection, 'kp_users', 'work_status') && isset($user_row['work_status']) && (int)$user_row['work_status'] !== 2) {
            return false;
        }

        if (kp_schema_column_exists($connection, 'kp_users', 'user_work_status') && isset($user_row['user_work_status']) && (int)$user_row['user_work_status'] !== 2) {
            return false;
        }

        return true;
    }
}

if (!function_exists('tots_repo_counter_is_active')) {
    function tots_repo_counter_is_active($counter_row) {
        global $connection;

        if (!$counter_row || !is_array($counter_row)) {
            return false;
        }

        if (kp_schema_column_exists($connection, 'kp_counters', 'counter_status') && isset($counter_row['counter_status']) && (int)$counter_row['counter_status'] !== 1) {
            return false;
        }

        if (kp_schema_column_exists($connection, 'kp_counters', 'close_status') && isset($counter_row['close_status']) && (int)$counter_row['close_status'] === 2) {
            return false;
        }

        return true;
    }
}

if (!function_exists('tots_repo_fetch_categories')) {
    function tots_repo_fetch_categories($dpt = '') {
        global $connection;

        $dpt = mysqli_real_escape_string($connection, (string)$dpt);
        $rows = [];
        $sql = "SELECT c.cat_id, c.name
                FROM kp_cat c
                WHERE EXISTS (
                    SELECT 1
                    FROM kp_items i
                    WHERE i.cat_id = c.cat_id
                      AND i.del_status = 2
                      AND i.stock = 1
                      AND i.tot_status = 1
                      AND i.tot_item_id > 0
                      AND i.tot_num > 0";
        if ($dpt !== '') {
            $sql .= " AND i.dpt = '{$dpt}'";
        }
        $sql .= "
                )
                ORDER BY c.cat_id ASC";
        $result = mysqli_query($connection, $sql);

        if (!$result) {
            return $rows;
        }

        while ($row = mysqli_fetch_assoc($result)) {
            $rows[] = [
                'cat_id' => (int)$row['cat_id'],
                'name' => (string)$row['name'],
            ];
        }

        return $rows;
    }
}

if (!function_exists('tots_repo_fetch_initial_items')) {
    function tots_repo_fetch_initial_items($dpt) {
        global $connection;

        $dpt = mysqli_real_escape_string($connection, (string)$dpt);
        $rows = [];
        $sql = "SELECT item_id, name FROM kp_items WHERE del_status=2 AND dpt='{$dpt}' AND stock=1 AND tot_status=1 ORDER BY name ASC";
        $result = mysqli_query($connection, $sql);

        if (!$result) {
            return $rows;
        }

        while ($row = mysqli_fetch_assoc($result)) {
            $rows[] = [
                'item_id' => (int)$row['item_id'],
                'name' => (string)$row['name'],
            ];
        }

        return $rows;
    }
}

if (!function_exists('tots_repo_fetch_items_by_category')) {
    function tots_repo_fetch_items_by_category($category_id, $dpt = '') {
        global $connection;

        $category_id = (int)$category_id;
        $dpt = mysqli_real_escape_string($connection, (string)$dpt);
        $rows = [];
        $sql = "SELECT item_id, name
                FROM kp_items
                WHERE cat_id='{$category_id}'
                  AND del_status=2
                  AND stock=1
                  AND tot_status=1
                  AND tot_item_id > 0
                  AND tot_num > 0";
        if ($dpt !== '') {
            $sql .= " AND dpt='{$dpt}'";
        }
        $sql .= " ORDER BY name ASC";
        $result = mysqli_query($connection, $sql);

        if (!$result) {
            return $rows;
        }

        while ($row = mysqli_fetch_assoc($result)) {
            $rows[] = [
                'item_id' => (int)$row['item_id'],
                'name' => (string)$row['name'],
            ];
        }

        return $rows;
    }
}

if (!function_exists('tots_repo_fetch_items_by_subcategory')) {
    function tots_repo_fetch_items_by_subcategory($sub_id, $dpt = '') {
        global $connection;

        $sub_id = (int)$sub_id;
        $dpt = mysqli_real_escape_string($connection, (string)$dpt);
        $rows = [];
        $sql = "SELECT item_id, name
                FROM kp_items
                WHERE sub_id='{$sub_id}'
                  AND del_status=2
                  AND stock=1
                  AND tot_status=1
                  AND tot_item_id > 0
                  AND tot_num > 0";
        if ($dpt !== '') {
            $sql .= " AND dpt='{$dpt}'";
        }
        $sql .= " ORDER BY name ASC";
        $result = mysqli_query($connection, $sql);

        if (!$result) {
            return $rows;
        }

        while ($row = mysqli_fetch_assoc($result)) {
            $rows[] = [
                'item_id' => (int)$row['item_id'],
                'name' => (string)$row['name'],
            ];
        }

        return $rows;
    }
}

if (!function_exists('tots_repo_fetch_subcategories')) {
    function tots_repo_fetch_subcategories($category_id, $dpt = '') {
        global $connection;

        $category_id = (int)$category_id;
        $dpt = mysqli_real_escape_string($connection, (string)$dpt);
        $rows = [];
        $sql = "SELECT s.sub_id, s.name
                FROM kp_sub_cat s
                WHERE s.cat_id='{$category_id}'
                  AND EXISTS (
                    SELECT 1
                    FROM kp_items i
                    WHERE i.sub_id = s.sub_id
                      AND i.del_status = 2
                      AND i.stock = 1
                      AND i.tot_status = 1
                      AND i.tot_item_id > 0
                      AND i.tot_num > 0";
        if ($dpt !== '') {
            $sql .= " AND i.dpt = '{$dpt}'";
        }
        $sql .= "
                  )
                ORDER BY s.name ASC";
        $result = mysqli_query($connection, $sql);

        if (!$result) {
            return $rows;
        }

        while ($row = mysqli_fetch_assoc($result)) {
            $rows[] = [
                'sub_id' => (int)$row['sub_id'],
                'name' => (string)$row['name'],
            ];
        }

        return $rows;
    }
}

if (!function_exists('tots_repo_fetch_live_items')) {
    function tots_repo_fetch_live_items($tot_id) {
        global $connection;

        $tot_id = (int)$tot_id;
        $rows = [];
        $sql = "SELECT tot_item_id, tots FROM kp_tot_items WHERE tot_id='{$tot_id}'";
        $result = mysqli_query($connection, $sql);

        if (!$result) {
            return $rows;
        }

        while ($row = mysqli_fetch_assoc($result)) {
            $tot_item_id = (int)$row['tot_item_id'];
            $rows[] = [
                'tot_item_id' => $tot_item_id,
                'tots' => (string)$row['tots'],
                'name' => function_exists('get_item') ? (string)get_item($tot_item_id) : ('Item #' . $tot_item_id),
            ];
        }

        return $rows;
    }
}

if (!function_exists('tots_repo_find_tot_item')) {
    function tots_repo_find_tot_item($tot_id, $item_id) {
        global $connection;

        $tot_id = (int)$tot_id;
        $item_id = (int)$item_id;
        $sql = "SELECT * FROM kp_tot_items WHERE item_id='{$item_id}' AND tot_id='{$tot_id}'";
        $result = mysqli_query($connection, $sql);

        return $result ? mysqli_fetch_assoc($result) : null;
    }
}

if (!function_exists('tots_repo_find_tot_item_by_generated')) {
    function tots_repo_find_tot_item_by_generated($tot_id, $tot_item_id) {
        global $connection;

        $tot_id = (int)$tot_id;
        $tot_item_id = (int)$tot_item_id;
        $sql = "SELECT * FROM kp_tot_items WHERE tot_item_id='{$tot_item_id}' AND tot_id='{$tot_id}'";
        $result = mysqli_query($connection, $sql);

        return $result ? mysqli_fetch_assoc($result) : null;
    }
}

if (!function_exists('tots_repo_get_item_row')) {
    function tots_repo_get_item_row($item_id) {
        global $connection;

        $item_id = (int)$item_id;
        $sql = "SELECT * FROM kp_items WHERE item_id='{$item_id}' LIMIT 1";
        $result = mysqli_query($connection, $sql);

        return $result ? mysqli_fetch_assoc($result) : null;
    }
}

if (!function_exists('tots_repo_insert_tot_item')) {
    function tots_repo_insert_tot_item($tot_id, $user_id, $item_id, $tot_item_id, $tots, $shift, $counter) {
        global $connection;

        $tot_id = (int)$tot_id;
        $user_id = (int)$user_id;
        $item_id = (int)$item_id;
        $tot_item_id = (int)$tot_item_id;
        $tots = (float)$tots;
        $shift = mysqli_real_escape_string($connection, (string)$shift);
        $counter = (int)$counter;

        return mysqli_query($connection, "INSERT INTO kp_tot_items(tot_id,user_id,item_id,tot_item_id,tots,shift,counter) VALUES('{$tot_id}','{$user_id}','{$item_id}','{$tot_item_id}','{$tots}','{$shift}','{$counter}')");
    }
}

if (!function_exists('tots_repo_update_counter_qty')) {
    function tots_repo_update_counter_qty($item_id, $qty_column, $amount, $operator) {
        global $connection;

        $item_id = (int)$item_id;
        $amount = (float)$amount;
        $qty_column = preg_replace('/[^a-zA-Z0-9_]/', '', (string)$qty_column);
        $operator = $operator === '+' ? '+' : '-';

        return mysqli_query($connection, "UPDATE kp_items SET `{$qty_column}`=`{$qty_column}`{$operator}'{$amount}' WHERE item_id='{$item_id}'");
    }
}

if (!function_exists('tots_repo_insert_stock_track')) {
    function tots_repo_insert_stock_track($item_id, $qty, $user_id, $tot_id, $status, $shift, $dpt, $counter) {
        global $connection;

        $item_id = (int)$item_id;
        $qty = (float)$qty;
        $user_id = (int)$user_id;
        $tot_id = (int)$tot_id;
        $status = mysqli_real_escape_string($connection, (string)$status);
        $shift = mysqli_real_escape_string($connection, (string)$shift);
        $dpt = mysqli_real_escape_string($connection, (string)$dpt);
        $counter = (int)$counter;
        foreach ([
            'trans' => "VARCHAR(155) NOT NULL DEFAULT ''",
            'trans_id' => 'INT NOT NULL DEFAULT 0',
        ] as $trackColumn => $trackDefinition) {
            $columnEsc = mysqli_real_escape_string($connection, $trackColumn);
            $columnCheck = mysqli_query($connection, "SHOW COLUMNS FROM kp_stock_track LIKE '{$columnEsc}'");
            if ($columnCheck && mysqli_num_rows($columnCheck) < 1) {
                mysqli_query($connection, "ALTER TABLE kp_stock_track ADD COLUMN `{$trackColumn}` {$trackDefinition}");
            }
        }

        return mysqli_query($connection, "INSERT INTO kp_stock_track(item_id,qty,user_id,tot_id,`trans`,trans_id,status,shift,dpt,stock_counter) VALUES('{$item_id}','{$qty}','{$user_id}','{$tot_id}','UNIT_GEN','{$tot_id}','{$status}','{$shift}','{$dpt}','{$counter}')");
    }
}

if (!function_exists('tots_repo_delete_tot_item')) {
    function tots_repo_delete_tot_item($tot_id, $item_id) {
        global $connection;

        $tot_id = (int)$tot_id;
        $item_id = (int)$item_id;

        return mysqli_query($connection, "DELETE FROM kp_tot_items WHERE item_id='{$item_id}' AND tot_id='{$tot_id}'");
    }
}

if (!function_exists('tots_repo_delete_stock_track')) {
    function tots_repo_delete_stock_track($item_id, $tot_id, $counter) {
        global $connection;

        $item_id = (int)$item_id;
        $tot_id = (int)$tot_id;
        $counter = (int)$counter;

        return mysqli_query($connection, "DELETE FROM kp_stock_track WHERE item_id='{$item_id}' AND tot_id='{$tot_id}' AND stock_counter='{$counter}'");
    }
}

if (!function_exists('tots_repo_fetch_print_counters')) {
    function tots_repo_fetch_print_counters() {
        global $connection;

        $rows = [];
        $result = mysqli_query($connection, "SELECT cntr_id, name, dpt FROM kp_counters WHERE del_status=2 ORDER BY dpt ASC, cntr_id ASC");

        if (!$result) {
            return $rows;
        }

        while ($row = mysqli_fetch_assoc($result)) {
            $rows[] = [
                'cntr_id' => (int)$row['cntr_id'],
                'name' => (string)$row['name'],
                'dpt' => (int)$row['dpt'],
            ];
        }

        return $rows;
    }
}

if (!function_exists('tots_repo_tot_exists')) {
    function tots_repo_tot_exists($tot_id) {
        global $connection;

        $tot_id = (int)$tot_id;
        $result = mysqli_query($connection, "SELECT tot_id FROM kp_tot WHERE tot_id='{$tot_id}' LIMIT 1");
        return $result && mysqli_num_rows($result) > 0;
    }
}

if (!function_exists('tots_repo_create_tot_header')) {
    function tots_repo_create_tot_header($tot_id, $user_id, $shift, $counter) {
        global $connection;

        $tot_id = (int)$tot_id;
        $user_id = (int)$user_id;
        $shift = mysqli_real_escape_string($connection, (string)$shift);
        $counter = (int)$counter;

        return mysqli_query($connection, "INSERT INTO kp_tot(tot_id,user_id,shift,counter) VALUES('{$tot_id}','{$user_id}','{$shift}','{$counter}')");
    }
}

if (!function_exists('tots_repo_clear_tot_status')) {
    function tots_repo_clear_tot_status($tot_id) {
        global $connection;

        $tot_id = (int)$tot_id;
        return mysqli_query($connection, "DELETE FROM kp_tot_status WHERE tot_id='{$tot_id}'");
    }
}

if (!function_exists('tots_repo_find_open_tot_status')) {
    function tots_repo_find_open_tot_status($user_id) {
        global $connection;

        $user_id = (int)$user_id;
        $result = mysqli_query($connection, "SELECT * FROM kp_tot_status WHERE user_id='{$user_id}' AND tot_status=2 ORDER BY id DESC LIMIT 1");

        return $result ? mysqli_fetch_assoc($result) : null;
    }
}

if (!function_exists('tots_repo_get_next_tot_id')) {
    function tots_repo_get_next_tot_id() {
        global $connection;

        $result = mysqli_query($connection, "SELECT tot_id FROM kp_tot_id LIMIT 1");
        if ($result && mysqli_num_rows($result) > 0) {
            $row = mysqli_fetch_assoc($result);
            $last_tot = (int)($row['tot_id'] ?? 0);
            $new_tot = $last_tot + 1;
            mysqli_query($connection, "UPDATE kp_tot_id SET tot_id='{$new_tot}'");
            return $new_tot;
        }

        mysqli_query($connection, "INSERT INTO kp_tot_id(tot_id) VALUES(1)");
        return 1;
    }
}

if (!function_exists('tots_repo_create_tot_status')) {
    function tots_repo_create_tot_status($tot_id, $user_id, $counter = 0) {
        global $connection;

        $tot_id = (int)$tot_id;
        $user_id = (int)$user_id;
        $counter = (int)$counter;

        return mysqli_query($connection, "INSERT INTO kp_tot_status(tot_id,user_id,tot_status,counter) VALUES('{$tot_id}','{$user_id}','2','{$counter}')");
    }
}

if (!function_exists('tots_repo_fetch_tot_rows')) {
    function tots_repo_fetch_tot_rows($tot_id) {
        global $connection;

        $tot_id = (int)$tot_id;
        $rows = [];
        $result = mysqli_query($connection, "SELECT * FROM kp_tot_items WHERE tot_id='{$tot_id}'");

        if (!$result) {
            return $rows;
        }

        while ($row = mysqli_fetch_assoc($result)) {
            $rows[] = $row;
        }

        return $rows;
    }
}

if (!function_exists('tots_repo_delete_tot_items')) {
    function tots_repo_delete_tot_items($tot_id) {
        global $connection;

        $tot_id = (int)$tot_id;
        return mysqli_query($connection, "DELETE FROM kp_tot_items WHERE tot_id='{$tot_id}'");
    }
}

if (!function_exists('tots_repo_delete_tot_stock_track')) {
    function tots_repo_delete_tot_stock_track($tot_id) {
        global $connection;

        $tot_id = (int)$tot_id;
        return mysqli_query($connection, "DELETE FROM kp_stock_track WHERE tot_id='{$tot_id}'");
    }
}
