<?php

if (!function_exists('take_repo_context')) {
    function take_repo_context($user_id) {
        $user_id = (int)$user_id;
        $counter = function_exists('user_counter') ? user_counter($user_id) : 0;
        $shift = function_exists('get_column') ? (string)get_column('kp_shift', 'shift', "1") : '';
        $dpt = $counter > 0 && function_exists('get_column') ? (string)get_column('kp_counters', 'dpt', "cntr_id='{$counter}'") : '';

        return [
            'user_id' => $user_id,
            'counter' => (int)$counter,
            'shift' => $shift,
            'dpt' => $dpt,
            'counter_name' => $counter > 0 && function_exists('get_column') ? (string)get_column('kp_counters', 'name', "cntr_id='{$counter}'") : '',
            'take_column' => $counter > 0 ? 'cntr' . $counter . '_take' : '',
            'qty_column' => $counter > 0 ? 'cntr' . $counter . '_qty' : '',
            'closing_table' => $counter > 0 ? 'kp_cntr' . $counter . '_closing_stock' : '',
            'take_table' => $counter > 0 ? 'kp_cntr' . $counter . '_stock_take' : '',
        ];
    }
}

if (!function_exists('take_repo_fetch_categories')) {
    function take_repo_fetch_categories() {
        global $connection;
        $rows = [];
        $result = mysqli_query($connection, "SELECT cat_id, name FROM kp_cat ORDER BY name ASC");
        if (!$result) {
            return $rows;
        }
        while ($row = mysqli_fetch_assoc($result)) {
            $rows[] = $row;
        }
        return $rows;
    }
}

if (!function_exists('take_repo_fetch_remaining_items')) {
    function take_repo_fetch_remaining_items($closing_table, $take_column, $dpt, $counter = 0) {
        global $connection;
        $dpt = mysqli_real_escape_string($connection, (string)$dpt);
        $rows = [];
        $counter = (int)$counter;
        $sql = "SELECT kp_items.item_id, kp_items.name, kp_items.cat_id, kp_items.stock_take_type, kp_items.empty_bottle_weight, kp_items.full_bottle_weight, kp_items.bottle_mls, kp_items.tot_num, ci.qty AS opening_qty, COALESCE(kp_cat.name,'') AS category_name
                FROM kp_counter_items ci
                INNER JOIN kp_items ON kp_items.item_id=ci.item_id
                LEFT JOIN kp_cat ON kp_cat.cat_id=kp_items.cat_id
                WHERE ci.counter_id={$counter} AND ci.is_active=1 AND ci.take_status=2 AND kp_items.del_status=2 AND kp_items.stock=1 AND kp_items.dpt='{$dpt}'
                ORDER BY
                    CASE WHEN kp_items.cat_id=0 THEN 1 ELSE 0 END ASC,
                    kp_cat.name ASC,
                    kp_items.name ASC";
        $result = mysqli_query($connection, $sql);
        if (!$result) {
            return $rows;
        }
        while ($row = mysqli_fetch_assoc($result)) {
            $rows[] = $row;
        }
        return $rows;
    }
}

if (!function_exists('take_repo_fetch_group_items')) {
    function take_repo_fetch_group_items($closing_table, $take_column, $dpt, $cat_id, $counter = 0) {
        global $connection;
        $dpt = mysqli_real_escape_string($connection, (string)$dpt);
        $cat_id = (int)$cat_id;
        $rows = [];
        $counter = (int)$counter;
        $sql = "SELECT kp_items.*, ci.qty AS opening_qty FROM kp_counter_items ci INNER JOIN kp_items ON kp_items.item_id=ci.item_id WHERE ci.counter_id={$counter} AND ci.is_active=1 AND ci.take_status=2 AND kp_items.stock=1 AND kp_items.dpt='{$dpt}' AND kp_items.cat_id='{$cat_id}' ORDER BY kp_items.name ASC";
        $result = mysqli_query($connection, $sql);
        if (!$result) {
            return $rows;
        }
        while ($row = mysqli_fetch_assoc($result)) {
            $rows[] = $row;
        }
        return $rows;
    }
}

if (!function_exists('take_repo_fetch_item_meta')) {
    function take_repo_fetch_item_meta($item_id) {
        global $connection;
        $item_id = (int)$item_id;
        $result = mysqli_query($connection, "SELECT stock_take_type, empty_bottle_weight, full_bottle_weight, bottle_mls, tot_num, name FROM kp_items WHERE item_id='{$item_id}' LIMIT 1");
        return $result ? mysqli_fetch_assoc($result) : null;
    }
}

if (!function_exists('take_repo_fetch_opening_qty')) {
    function take_repo_fetch_opening_qty($closing_table, $item_id, $counter = 0) {
        global $connection;
        $item_id = (int)$item_id;
        $counter = (int)$counter;
        $result = mysqli_query($connection, "SELECT qty FROM kp_counter_items WHERE counter_id={$counter} AND item_id='{$item_id}' AND is_active=1 LIMIT 1");
        $row = $result ? mysqli_fetch_assoc($result) : null;
        return $row ? (float)$row['qty'] : 0.0;
    }
}

if (!function_exists('take_repo_insert_take_row')) {
    function take_repo_insert_take_row($take_table, $item_id, $user_id, $opening, $counted, $shift) {
        global $connection;
        $item_id = (int)$item_id;
        $user_id = (int)$user_id;
        $opening = (float)$opening;
        $counted = (float)$counted;
        $shift = mysqli_real_escape_string($connection, (string)$shift);
        return mysqli_query($connection, "INSERT INTO {$take_table}(item_id,user_id,opening,counted,shift) VALUES('{$item_id}','{$user_id}','{$opening}','{$counted}','{$shift}')");
    }
}

if (!function_exists('take_repo_update_item_count')) {
    function take_repo_update_item_count($take_column, $qty_column, $item_id, $counted, $counter = 0) {
        global $connection;
        $item_id = (int)$item_id;
        $counted = (float)$counted;
        $counter = (int)$counter;
        return mysqli_query($connection, "UPDATE kp_counter_items SET qty='{$counted}', take_status=1 WHERE counter_id={$counter} AND item_id='{$item_id}' AND is_active=1");
    }
}

if (!function_exists('take_repo_fetch_take_rows')) {
    function take_repo_fetch_take_rows($take_table, $shift) {
        global $connection;
        $shift = mysqli_real_escape_string($connection, (string)$shift);
        $rows = [];
        $result = mysqli_query($connection, "SELECT take_rows.*, kp_items.name FROM {$take_table} AS take_rows LEFT JOIN kp_items ON kp_items.item_id=take_rows.item_id WHERE take_rows.shift='{$shift}' ORDER BY take_rows.take_id DESC");
        if (!$result) {
            return $rows;
        }
        while ($row = mysqli_fetch_assoc($result)) {
            $rows[] = $row;
        }
        return $rows;
    }
}

if (!function_exists('take_repo_delete_take_row')) {
    function take_repo_delete_take_row($take_table, $item_id, $shift) {
        global $connection;
        $item_id = (int)$item_id;
        $shift = mysqli_real_escape_string($connection, (string)$shift);
        return mysqli_query($connection, "DELETE FROM {$take_table} WHERE item_id='{$item_id}' AND shift='{$shift}'");
    }
}

if (!function_exists('take_repo_fetch_take_row_by_item')) {
    function take_repo_fetch_take_row_by_item($take_table, $item_id, $shift) {
        global $connection;
        $item_id = (int)$item_id;
        $shift = mysqli_real_escape_string($connection, (string)$shift);
        $result = mysqli_query($connection, "SELECT * FROM {$take_table} WHERE item_id='{$item_id}' AND shift='{$shift}' LIMIT 1");
        return $result ? mysqli_fetch_assoc($result) : null;
    }
}

if (!function_exists('take_repo_reset_item_after_delete')) {
    function take_repo_reset_item_after_delete($qty_column, $take_column, $item_id, $opening, $counter = 0) {
        global $connection;
        $item_id = (int)$item_id;
        $opening = (float)$opening;
        $counter = (int)$counter;
        return mysqli_query($connection, "UPDATE kp_counter_items SET qty='{$opening}' WHERE counter_id={$counter} AND item_id='{$item_id}' AND is_active=1");
    }
}

if (!function_exists('take_repo_update_counter_take_status')) {
    function take_repo_update_counter_take_status($counter, $status) {
        global $connection;
        $counter = (int)$counter;
        $status = (int)$status;
        return mysqli_query($connection, "UPDATE kp_counters SET take_status='{$status}' WHERE cntr_id='{$counter}'");
    }
}

if (!function_exists('take_repo_open_take_count')) {
    function take_repo_open_take_count() {
        global $connection;
        return (int)column_count('kp_counters', 'cntr_id', "take_status=2");
    }
}

if (!function_exists('take_repo_mark_stock_shift_complete')) {
    function take_repo_mark_stock_shift_complete() {
        global $connection;
        return mysqli_query($connection, "UPDATE kp_shift SET stock_status=1");
    }
}
