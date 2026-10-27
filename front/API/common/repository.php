<?php

if (!function_exists('common_repo_table_exists')) {
    function common_repo_table_exists($table_name) {
        global $connection;

        $safe_table = mysqli_real_escape_string($connection, (string)$table_name);
        $query = mysqli_query($connection, "SHOW TABLES LIKE '$safe_table'");
        return $query && mysqli_num_rows($query) > 0;
    }
}

if (!function_exists('common_repo_column_exists')) {
    function common_repo_column_exists($table_name, $column_name) {
        global $connection;

        static $cache = [];
        $key = $table_name . '.' . $column_name;
        if (array_key_exists($key, $cache)) {
            return $cache[$key];
        }

        $safe_table = mysqli_real_escape_string($connection, (string)$table_name);
        $safe_column = mysqli_real_escape_string($connection, (string)$column_name);
        $query = mysqli_query($connection, "SHOW COLUMNS FROM `$safe_table` LIKE '$safe_column'");
        $cache[$key] = $query && mysqli_num_rows($query) > 0;

        return $cache[$key];
    }
}

if (!function_exists('common_repo_first_existing_column')) {
    function common_repo_first_existing_column($table_name, $columns) {
        foreach ($columns as $column_name) {
            if (common_repo_column_exists($table_name, $column_name)) {
                return $column_name;
            }
        }

        return '';
    }
}

if (!function_exists('common_repo_escape')) {
    function common_repo_escape($value) {
        global $connection;

        return mysqli_real_escape_string($connection, (string)$value);
    }
}

if (!function_exists('common_repo_build_range_filters')) {
    function common_repo_build_range_filters($table_name, $date_from = '', $date_to = '', $datetime_from = '', $datetime_to = '', $time_from = '', $time_to = '') {
        $filters = [];

        $date_column = common_repo_first_existing_column($table_name, ['shift', 'po_date', 'rec_date', 'day', 'ord_date', 'pay_date']);
        $datetime_column = common_repo_first_existing_column($table_name, ['datime', 'created_at', 'appr_datime', 'rec_datime', 'raise_datime', 'counter_approve_datime', 'cashier_approve_datime']);
        $time_column = common_repo_first_existing_column($table_name, ['hour', 'datime', 'created_at']);

        if ($date_from !== '' && $date_column !== '') {
            $filters[] = "`$date_column` >= '" . common_repo_escape($date_from) . "'";
        }
        if ($date_to !== '' && $date_column !== '') {
            $filters[] = "`$date_column` <= '" . common_repo_escape($date_to) . "'";
        }
        if ($datetime_from !== '' && $datetime_column !== '') {
            $filters[] = "`$datetime_column` >= '" . common_repo_escape($datetime_from) . "'";
        }
        if ($datetime_to !== '' && $datetime_column !== '') {
            $filters[] = "`$datetime_column` <= '" . common_repo_escape($datetime_to) . "'";
        }
        if ($time_from !== '' && $time_column !== '') {
            if ($time_column === 'hour') {
                $filters[] = "`$time_column` >= '" . common_repo_escape($time_from) . "'";
            } else {
                $filters[] = "TIME(`$time_column`) >= '" . common_repo_escape($time_from) . "'";
            }
        }
        if ($time_to !== '' && $time_column !== '') {
            if ($time_column === 'hour') {
                $filters[] = "`$time_column` <= '" . common_repo_escape($time_to) . "'";
            } else {
                $filters[] = "TIME(`$time_column`) <= '" . common_repo_escape($time_to) . "'";
            }
        }

        return $filters;
    }
}

if (!function_exists('common_repo_fetch_all')) {
    function common_repo_fetch_all($sql) {
        global $connection;

        $rows = [];
        $query = mysqli_query($connection, $sql);
        if (!$query) {
            throw new RuntimeException(mysqli_error($connection));
        }

        while ($row = mysqli_fetch_assoc($query)) {
            $rows[] = $row;
        }

        return $rows;
    }
}

if (!function_exists('common_repo_fetch_sales')) {
    function common_repo_fetch_sales($filters = []) {
        $table = 'kp_sales';
        $where = [];

        if (!common_repo_table_exists($table)) {
            return [];
        }

        if (!empty($filters['sale_id'])) {
            $where[] = "sale_id='" . (int)$filters['sale_id'] . "'";
        }
        if (!empty($filters['user_id']) && common_repo_column_exists($table, 'user_id')) {
            $where[] = "user_id='" . (int)$filters['user_id'] . "'";
        }
        if (!empty($filters['counter_id']) && common_repo_column_exists($table, 'counter')) {
            $where[] = "counter='" . (int)$filters['counter_id'] . "'";
        }
        if (!empty($filters['shift']) && common_repo_column_exists($table, 'shift')) {
            $where[] = "shift='" . common_repo_escape($filters['shift']) . "'";
        }

        $where = array_merge($where, common_repo_build_range_filters(
            $table,
            (string)($filters['date_from'] ?? ''),
            (string)($filters['date_to'] ?? ''),
            (string)($filters['datetime_from'] ?? ''),
            (string)($filters['datetime_to'] ?? ''),
            (string)($filters['time_from'] ?? ''),
            (string)($filters['time_to'] ?? '')
        ));

        $sql = "SELECT * FROM kp_sales";
        if (!empty($where)) {
            $sql .= " WHERE " . implode(' AND ', $where);
        }
        $sql .= " ORDER BY sale_id DESC";

        return common_repo_fetch_all($sql);
    }
}

if (!function_exists('common_repo_fetch_sale_items')) {
    function common_repo_fetch_sale_items($filters = []) {
        $table = 'kp_sale_items';
        $where = [];

        if (!common_repo_table_exists($table)) {
            return [];
        }

        if (!empty($filters['sale_id']) && common_repo_column_exists($table, 'sale_id')) {
            $where[] = "sale_id='" . (int)$filters['sale_id'] . "'";
        }
        if (!empty($filters['user_id']) && common_repo_column_exists($table, 'user_id')) {
            $where[] = "user_id='" . (int)$filters['user_id'] . "'";
        }
        if (!empty($filters['counter_id']) && common_repo_column_exists($table, 'counter')) {
            $where[] = "counter='" . (int)$filters['counter_id'] . "'";
        }
        if (!empty($filters['shift']) && common_repo_column_exists($table, 'shift')) {
            $where[] = "shift='" . common_repo_escape($filters['shift']) . "'";
        }

        $where = array_merge($where, common_repo_build_range_filters(
            $table,
            (string)($filters['date_from'] ?? ''),
            (string)($filters['date_to'] ?? ''),
            (string)($filters['datetime_from'] ?? ''),
            (string)($filters['datetime_to'] ?? ''),
            (string)($filters['time_from'] ?? ''),
            (string)($filters['time_to'] ?? '')
        ));

        $sql = "SELECT * FROM kp_sale_items";
        if (!empty($where)) {
            $sql .= " WHERE " . implode(' AND ', $where);
        }
        $sql .= " ORDER BY sale_id DESC, id DESC";

        return common_repo_fetch_all($sql);
    }
}

if (!function_exists('common_repo_fetch_staff_list')) {
    function common_repo_fetch_staff_list() {
        if (!common_repo_table_exists('kp_staff')) {
            return [];
        }

        return common_repo_fetch_all("SELECT * FROM kp_staff ORDER BY staff_name ASC");
    }
}

if (!function_exists('common_repo_fetch_items')) {
    function common_repo_fetch_items($filters = []) {
        $table = 'kp_items';
        $where = [];

        if (!common_repo_table_exists($table)) {
            return [];
        }

        if (!empty($filters['category_id']) && common_repo_column_exists($table, 'cat_id')) {
            $where[] = "cat_id='" . (int)$filters['category_id'] . "'";
        }
        if (!empty($filters['subcategory_id']) && common_repo_column_exists($table, 'sub_id')) {
            $where[] = "sub_id='" . (int)$filters['subcategory_id'] . "'";
        }
        if (!empty($filters['dpt']) && common_repo_column_exists($table, 'dpt')) {
            $where[] = "dpt='" . (int)$filters['dpt'] . "'";
        }
        if (isset($filters['stock']) && $filters['stock'] !== '' && common_repo_column_exists($table, 'stock')) {
            $where[] = "stock='" . (int)$filters['stock'] . "'";
        }

        $sql = "SELECT * FROM kp_items";
        if (!empty($where)) {
            $sql .= " WHERE " . implode(' AND ', $where);
        }
        $sql .= " ORDER BY name ASC";

        return common_repo_fetch_all($sql);
    }
}

if (!function_exists('common_repo_fetch_catalog')) {
    function common_repo_fetch_catalog($user_id, $dpt = '', $stock = 0, $menu = 0) {
        global $connection;

        $user_id = (int)$user_id;
        if ((string)$dpt === '') {
            $context_query = mysqli_query($connection, "SELECT u.counter, c.dpt
                FROM kp_users u
                LEFT JOIN kp_counters c ON c.cntr_id = u.counter
                WHERE u.user_id='{$user_id}' LIMIT 1");
            if ($context_query && ($context_row = mysqli_fetch_assoc($context_query))) {
                $dpt = (string)($context_row['dpt'] ?? '');
            }
        }

        $base_counter_id = isset($context_row) ? (int)($context_row['counter'] ?? 0) : 0;
        $kit_counter_id = 0;
        if ($base_counter_id > 0) {
            $kit_query = mysqli_query($connection, "SELECT cntr_id FROM kp_counters WHERE dpt=2 AND kit_default=1 LIMIT 1");
            if ($kit_query && ($kit_row = mysqli_fetch_assoc($kit_query))) {
                $kit_counter_id = (int)$kit_row['cntr_id'];
            }
        }

        $where = ["i.del_status=2"];
        if ((string)$dpt === '2') {
            $where[] = "i.dpt='2'";
        }
        if ((int)$stock === 1) {
            $where[] = "i.stock='1'";
        }
        if ((int)$menu === 1) {
            $where[] = "i.menu='1'";
        }

        $rows = common_repo_fetch_all("SELECT
                i.*,
                i.name AS item_name,
                c.name AS category_name,
                s.name AS subcategory_name
            FROM kp_items i
            LEFT JOIN kp_cat c ON c.cat_id=i.cat_id
            LEFT JOIN kp_sub_cat s ON s.sub_id=i.sub_id
            WHERE " . implode(' AND ', $where) . "
            ORDER BY c.name ASC, s.name ASC, i.name ASC");

        $categories = [];
        $subcategories = [];
        $items = [];
        $category_seen = [];
        $subcategory_seen = [];

        foreach ($rows as $row) {
            $cat_id = (int)($row['cat_id'] ?? 0);
            $sub_id = (int)($row['sub_id'] ?? 0);

            if ($cat_id > 0 && !isset($category_seen[$cat_id])) {
                $category_seen[$cat_id] = true;
                $categories[] = [
                    'cat_id' => $cat_id,
                    'name' => (string)($row['category_name'] ?? ''),
                ];
            }

            if ($sub_id > 0 && !isset($subcategory_seen[$sub_id])) {
                $subcategory_seen[$sub_id] = true;
                $subcategories[] = [
                    'sub_id' => $sub_id,
                    'cat_id' => $cat_id,
                    'name' => (string)($row['subcategory_name'] ?? ''),
                ];
            }

            $items[] = [
                'item_id' => (int)($row['item_id'] ?? 0),
                'name' => (string)($row['item_name'] ?? ''),
                'cat_id' => $cat_id,
                'sub_id' => $sub_id,
                'port_status' => (int)($row['port_status'] ?? 0),
                'portions' => (string)($row['portions'] ?? ''),
                'deci' => (int)($row['deci'] ?? 0),
                'has_attributes' => (int)($row['has_attributes'] ?? 0),
                'stock' => (int)($row['stock'] ?? 0),
                'qty' => common_catalog_item_qty($row, $base_counter_id, $kit_counter_id),
                'reorder' => common_catalog_item_reorder($row, $base_counter_id, $kit_counter_id),
                'stock_color' => common_catalog_item_color($row, $base_counter_id, $kit_counter_id),
            ];
        }

        return [
            'categories' => $categories,
            'subcategories' => $subcategories,
            'items' => $items,
            'dpt' => (string)$dpt,
        ];
    }
}

if (!function_exists('common_catalog_item_counter')) {
    function common_catalog_item_counter($row, $base_counter_id, $kit_counter_id) {
        return (int)($row['dpt'] ?? 0) === 2 && $kit_counter_id > 0 ? $kit_counter_id : (int)$base_counter_id;
    }
}

if (!function_exists('common_catalog_item_qty')) {
    function common_catalog_item_qty($row, $base_counter_id, $kit_counter_id) {
        global $connection;
        $stock = (int)($row['stock'] ?? 0);
        $counter_id = common_catalog_item_counter($row, $base_counter_id, $kit_counter_id);
        if ($stock === 1 && $counter_id > 0) {
            $mapped = mysqli_query($connection, "SELECT qty FROM kp_counter_items WHERE counter_id='" . (int)$counter_id . "' AND item_id='" . (int)($row['item_id'] ?? 0) . "' AND is_active=1 LIMIT 1");
            if ($mapped && ($mappedRow = mysqli_fetch_assoc($mapped))) {
                return (float)$mappedRow['qty'];
            }
            return (float)($row['cntr' . $counter_id . '_qty'] ?? 0);
        }
        return $stock === 2 ? 100 : 0;
    }
}

if (!function_exists('common_catalog_item_reorder')) {
    function common_catalog_item_reorder($row, $base_counter_id, $kit_counter_id) {
        global $connection;
        $stock = (int)($row['stock'] ?? 0);
        $counter_id = common_catalog_item_counter($row, $base_counter_id, $kit_counter_id);
        if ($stock === 1 && $counter_id > 0) {
            $mapped = mysqli_query($connection, "SELECT reorder_level FROM kp_counter_items WHERE counter_id='" . (int)$counter_id . "' AND item_id='" . (int)($row['item_id'] ?? 0) . "' AND is_active=1 LIMIT 1");
            if ($mapped && ($mappedRow = mysqli_fetch_assoc($mapped))) {
                return (float)$mappedRow['reorder_level'];
            }
            return (float)($row['cntr' . $counter_id . '_reorder'] ?? 0);
        }
        return $stock === 2 ? 50 : 0;
    }
}

if (!function_exists('common_catalog_item_color')) {
    function common_catalog_item_color($row, $base_counter_id, $kit_counter_id) {
        $qty = common_catalog_item_qty($row, $base_counter_id, $kit_counter_id);
        $reorder = common_catalog_item_reorder($row, $base_counter_id, $kit_counter_id);
        if ($qty <= 0) {
            return 'red';
        }
        return $qty <= $reorder ? 'orange' : 'green';
    }
}

if (!function_exists('common_repo_fetch_users')) {
    function common_repo_fetch_users() {
        if (!common_repo_table_exists('kp_users')) {
            return [];
        }

        return common_repo_fetch_all("SELECT * FROM kp_users ORDER BY username ASC");
    }
}

if (!function_exists('common_repo_fetch_roles')) {
    function common_repo_fetch_roles() {
        if (!common_repo_table_exists('kp_roles')) {
            return [];
        }

        return common_repo_fetch_all("SELECT * FROM kp_roles ORDER BY role_name ASC");
    }
}

if (!function_exists('common_repo_fetch_purchases')) {
    function common_repo_fetch_purchases($filters = []) {
        $table = 'kp_po';
        $where = [];

        if (!common_repo_table_exists($table)) {
            return [];
        }

        if (!empty($filters['supplier_id']) && common_repo_column_exists($table, 'sup_id')) {
            $where[] = "sup_id='" . (int)$filters['supplier_id'] . "'";
        }
        if (!empty($filters['user_id'])) {
            $user_filters = [];
            foreach (['user_id', 'ord_user', 'rec_staff', 'appr_user'] as $column_name) {
                if (common_repo_column_exists($table, $column_name)) {
                    $user_filters[] = "$column_name='" . (int)$filters['user_id'] . "'";
                }
            }
            if (!empty($user_filters)) {
                $where[] = '(' . implode(' OR ', $user_filters) . ')';
            }
        }

        $where = array_merge($where, common_repo_build_range_filters(
            $table,
            (string)($filters['date_from'] ?? ''),
            (string)($filters['date_to'] ?? ''),
            (string)($filters['datetime_from'] ?? ''),
            (string)($filters['datetime_to'] ?? ''),
            (string)($filters['time_from'] ?? ''),
            (string)($filters['time_to'] ?? '')
        ));

        $sql = "SELECT * FROM kp_po";
        if (!empty($where)) {
            $sql .= " WHERE " . implode(' AND ', $where);
        }
        $sql .= " ORDER BY po_id DESC";

        return common_repo_fetch_all($sql);
    }
}

if (!function_exists('common_repo_fetch_purchase_items')) {
    function common_repo_fetch_purchase_items($filters = []) {
        $table = 'kp_po_items';
        $where = [];

        if (!common_repo_table_exists($table)) {
            return [];
        }

        if (!empty($filters['po_id']) && common_repo_column_exists($table, 'po_id')) {
            $where[] = "po_id='" . (int)$filters['po_id'] . "'";
        }
        if (!empty($filters['shift']) && common_repo_column_exists($table, 'day')) {
            $where[] = "day='" . common_repo_escape($filters['shift']) . "'";
        }
        if (!empty($filters['supplier_id']) && common_repo_table_exists('kp_po') && common_repo_column_exists('kp_po', 'sup_id')) {
            $where[] = "po_id IN (SELECT po_id FROM kp_po WHERE sup_id='" . (int)$filters['supplier_id'] . "')";
        }

        $where = array_merge($where, common_repo_build_range_filters(
            $table,
            (string)($filters['date_from'] ?? ''),
            (string)($filters['date_to'] ?? ''),
            (string)($filters['datetime_from'] ?? ''),
            (string)($filters['datetime_to'] ?? ''),
            (string)($filters['time_from'] ?? ''),
            (string)($filters['time_to'] ?? '')
        ));

        $sql = "SELECT * FROM kp_po_items";
        if (!empty($where)) {
            $sql .= " WHERE " . implode(' AND ', $where);
        }
        $sql .= " ORDER BY po_id DESC, id DESC";

        return common_repo_fetch_all($sql);
    }
}

if (!function_exists('common_repo_fetch_stock_track')) {
    function common_repo_fetch_stock_track($filters = []) {
        $table = 'kp_stock_track';
        $where = [];

        if (!common_repo_table_exists($table)) {
            return [];
        }

        if (!empty($filters['counter_id']) && common_repo_column_exists($table, 'stock_counter')) {
            $where[] = "stock_counter='" . (int)$filters['counter_id'] . "'";
        }
        if (!empty($filters['shift']) && common_repo_column_exists($table, 'shift')) {
            $where[] = "shift='" . common_repo_escape($filters['shift']) . "'";
        }
        if (!empty($filters['user_id']) && common_repo_column_exists($table, 'user_id')) {
            $where[] = "user_id='" . (int)$filters['user_id'] . "'";
        }

        $where = array_merge($where, common_repo_build_range_filters(
            $table,
            (string)($filters['date_from'] ?? ''),
            (string)($filters['date_to'] ?? ''),
            (string)($filters['datetime_from'] ?? ''),
            (string)($filters['datetime_to'] ?? ''),
            (string)($filters['time_from'] ?? ''),
            (string)($filters['time_to'] ?? '')
        ));

        $sql = "SELECT * FROM kp_stock_track";
        if (!empty($where)) {
            $sql .= " WHERE " . implode(' AND ', $where);
        }
        $order_column = common_repo_column_exists($table, 'id') ? 'id' : (common_repo_column_exists($table, 'item_id') ? 'item_id' : 'user_id');
        $sql .= " ORDER BY $order_column DESC";

        return common_repo_fetch_all($sql);
    }
}

?>
