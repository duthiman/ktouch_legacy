<?php

if (!function_exists('menu_repo_datatable_columns')) {
    function menu_repo_datatable_columns()
    {
        return array(
            array(
                'db' => 'item_id',
                'dt' => 'DT_RowClass',
                'formatter' => function ($d, $row) {
                    return 'row' . $d;
                }
            ),
            array('db' => 'item_id', 'dt' => 'id'),
            array('db' => 'name', 'dt' => 'name'),
            array('db' => 'price', 'dt' => 'price'),
            array('db' => 'dpt', 'dt' => 'dpt'),
            array('db' => 'hhr', 'dt' => 'hhr'),
            array('db' => 'cat_id', 'dt' => 'cat_id'),
            array('db' => 'sub_id', 'dt' => 'sub_id'),
            array('db' => 'b_price', 'dt' => 'bprice'),
            array('db' => 'qty', 'dt' => 'stock'),
            array('db' => 'tax_id', 'dt' => 'tax'),
            array('db' => 'menu', 'dt' => 'menu'),
            array('db' => 'stock', 'dt' => 'stock'),
            array('db' => 'recipe', 'dt' => 'recipe'),
            array('db' => 'port_status', 'dt' => 'portion'),
            array('db' => 'tot_status', 'dt' => 'tots'),
            array('db' => 'stock_take_type', 'dt' => 'stock_take_type'),
            array('db' => 'uom_id', 'dt' => 'uom_id'),
            array('db' => 'deci', 'dt' => 'deci')
        );
    }
}

if (!function_exists('menu_repo_datatable_response')) {
    function menu_repo_datatable_response($request, $sql_details)
    {
        require_once __DIR__ . '/../../../assets/plugins/DataTables/ssp.class.php';
        $filters = [];
        $filterMap = [
            'cat_id' => 'cat_id',
            'sub_id' => 'sub_id',
            'dpt' => 'dpt',
            'stock' => 'stock',
        ];
        foreach ($filterMap as $requestKey => $column) {
            $value = isset($request[$requestKey]) ? (int)$request[$requestKey] : 0;
            if ($value > 0) {
                $filters[] = "`{$column}` = {$value}";
            }
        }
        $where = count($filters) ? implode(' AND ', $filters) : null;
        return SSP::complex($request, $sql_details, 'kp_items', 'item_id', menu_repo_datatable_columns(), $where, null);
    }
}

if (!function_exists('menu_repo_search_items')) {
    /**
     * Search menu items using the filter values supplied by the inventory page.
     * The values are converted to integers before they reach this repository.
     */
    function menu_repo_search_items($filters = [])
    {
        global $connection;

        $filters = is_array($filters) ? $filters : [];
        $where = [];
        foreach (['cat_id', 'sub_id', 'dpt', 'stock'] as $field) {
            $value = isset($filters[$field]) ? (int)$filters[$field] : 0;
            if ($value > 0) {
                $where[] = "`{$field}` = {$value}";
            }
        }

        $search = trim((string)($filters['search'] ?? ''));
        if ($search !== '') {
            $safeSearch = mysqli_real_escape_string($connection, $search);
            $where[] = "`name` LIKE '%{$safeSearch}%'";
        }

        $whereSql = count($where) ? 'WHERE ' . implode(' AND ', $where) : '';
        $countResult = mysqli_query($connection, "SELECT COUNT(*) AS total FROM kp_items {$whereSql}");
        $total = 0;
        if ($countResult && ($countRow = mysqli_fetch_assoc($countResult))) {
            $total = (int)$countRow['total'];
        }

        $rows = [];
        $result = mysqli_query($connection, "SELECT item_id, name, cat_id, sub_id, dpt, stock FROM kp_items {$whereSql} ORDER BY name ASC LIMIT 200");
        if ($result) {
            while ($row = mysqli_fetch_assoc($result)) {
                $rows[] = [
                    'id' => (int)$row['item_id'],
                    'name' => (string)$row['name'],
                    'cat_id' => (int)$row['cat_id'],
                    'sub_id' => (int)$row['sub_id'],
                    'dpt' => (int)$row['dpt'],
                    'stock' => (int)$row['stock'],
                ];
            }
        }

        return ['total' => $total, 'items' => $rows];
    }
}

if (!function_exists('menu_repo_update_item_column')) {
    function menu_repo_update_item_column($item_id, $column_name, $value)
    {
        global $connection;
        $item_id = (int)$item_id;
        $safe_text = mysqli_real_escape_string($connection, (string)$value);
        $safe_column = $column_name === 'portion' ? 'port_status' : ($column_name === 'tots' ? 'tot_status' : $column_name);
        return mysqli_query($connection, "UPDATE kp_items SET {$safe_column}='{$safe_text}' WHERE item_id='{$item_id}'");
    }
}

if (!function_exists('menu_repo_get_item_name')) {
    function menu_repo_get_item_name($item_id)
    {
        return (string)get_column('kp_items', 'name', "item_id='" . (int)$item_id . "'");
    }
}

if (!function_exists('menu_repo_get_cat_name')) {
    function menu_repo_get_cat_name($cat_id)
    {
        return (string)get_column('kp_cat', 'name', "cat_id='" . (int)$cat_id . "'");
    }
}

if (!function_exists('menu_repo_get_tax_name')) {
    function menu_repo_get_tax_name($tax_id)
    {
        return (string)get_column('kp_tax', 'name', "tax_id='" . (int)$tax_id . "'");
    }
}

if (!function_exists('menu_repo_insert_item_history')) {
    function menu_repo_insert_item_history($item_id, $transaction, $user_id)
    {
        global $connection;
        $item_id = (int)$item_id;
        $user_id = (int)$user_id;
        $transaction = mysqli_real_escape_string($connection, (string)$transaction);
        return mysqli_query($connection, "INSERT INTO kp_item_hist(item_id,transaction,user_id,day) VALUES('{$item_id}','{$transaction}','{$user_id}',CURRENT_DATE)");
    }
}

if (!function_exists('menu_repo_update_item_category')) {
    function menu_repo_update_item_category($item_id, $cat_id)
    {
        global $connection;
        return mysqli_query($connection, "UPDATE kp_items SET cat_id='" . (int)$cat_id . "' WHERE item_id='" . (int)$item_id . "'");
    }
}

if (!function_exists('menu_repo_update_item_sub_category')) {
    function menu_repo_update_item_sub_category($item_id, $sub_id)
    {
        global $connection;
        return mysqli_query($connection, "UPDATE kp_items SET sub_id='" . (int)$sub_id . "' WHERE item_id='" . (int)$item_id . "'");
    }
}

if (!function_exists('menu_repo_update_item_settings')) {
    function menu_repo_update_item_settings($item_id, $dpt, $stock, $menu, $point)
    {
        global $connection;
        return mysqli_query($connection, "UPDATE kp_items SET dpt='" . (int)$dpt . "', stock='" . (int)$stock . "', menu='" . (int)$menu . "', deci='" . (int)$point . "' WHERE item_id='" . (int)$item_id . "'");
    }
}

if (!function_exists('menu_repo_update_item_tax')) {
    function menu_repo_update_item_tax($item_id, $tax_id)
    {
        global $connection;
        return mysqli_query($connection, "UPDATE kp_items SET tax_id='" . (int)$tax_id . "' WHERE item_id='" . (int)$item_id . "'");
    }
}

if (!function_exists('menu_repo_archive_item')) {
    function menu_repo_archive_item($item_id)
    {
        global $connection;
        return mysqli_query($connection, "INSERT INTO kp_items_arch SELECT * FROM kp_items WHERE item_id='" . (int)$item_id . "'");
    }
}

if (!function_exists('menu_repo_delete_item')) {
    function menu_repo_delete_item($item_id)
    {
        global $connection;
        return mysqli_query($connection, "DELETE FROM kp_items WHERE item_id='" . (int)$item_id . "'");
    }
}

if (!function_exists('menu_repo_insert_item')) {
    function menu_repo_insert_item($field_values, $data_values)
    {
        return Insertdata('kp_items', $field_values, $data_values);
    }
}

if (!function_exists('menu_repo_last_insert_id')) {
    function menu_repo_last_insert_id()
    {
        global $connection;
        return (int)mysqli_insert_id($connection);
    }
}

if (!function_exists('menu_repo_update_portion')) {
    function menu_repo_update_portion($item_id, $portions, $enabled)
    {
        global $connection;
        $status = $enabled ? 1 : 2;
        $portions = $enabled ? (float)$portions : 0;
        return mysqli_query($connection, "UPDATE kp_items SET port_status='{$status}', portions='{$portions}' WHERE item_id='" . (int)$item_id . "'");
    }
}

if (!function_exists('menu_repo_update_tots')) {
    function menu_repo_update_tots($item_id, $tot_bot, $tot_num, $stock_take_type, $empty_bottle_weight, $full_bottle_weight, $bottle_mls, $tot_size, $enabled)
    {
        global $connection;
        $tot_status = $enabled ? 1 : 2;
        return mysqli_query($connection, "UPDATE kp_items SET tot_status='{$tot_status}', tot_num='" . (float)$tot_num . "', tot_item_id='" . (int)$tot_bot . "', stock_take_type='" . (int)$stock_take_type . "', empty_bottle_weight='" . (float)$empty_bottle_weight . "', full_bottle_weight='" . (float)$full_bottle_weight . "', bottle_mls='" . (float)$bottle_mls . "', tot_size='" . (float)$tot_size . "' WHERE item_id='" . (int)$item_id . "'");
    }
}

if (!function_exists('menu_repo_search_unmapped_items')) {
    function menu_repo_search_unmapped_items($attr_id, $search = '', $limit = 20)
    {
        global $connection;

        $attr_id = (int)$attr_id;
        $limit = max(1, (int)$limit);
        $search = trim((string)$search);
        $rows = [];

        if ($attr_id < 1) {
            return $rows;
        }

        $mapped_ids = [];
        $mapped_result = mysqli_query($connection, "SELECT item_id FROM kp_item_attribute_mapping WHERE attr_id='{$attr_id}'");
        if ($mapped_result) {
            while ($row = mysqli_fetch_assoc($mapped_result)) {
                $mapped_ids[] = (int)$row['item_id'];
            }
        }

        $where = [];
        if ($search !== '') {
            $safe_search = mysqli_real_escape_string($connection, $search);
            $where[] = "name LIKE '%{$safe_search}%'";
        }
        if (count($mapped_ids) > 0) {
            $where[] = "item_id NOT IN (" . implode(',', $mapped_ids) . ")";
        }

        $where_sql = count($where) > 0 ? 'WHERE ' . implode(' AND ', $where) : '';
        $sql = "SELECT item_id, name FROM kp_items {$where_sql} ORDER BY name ASC LIMIT {$limit}";
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

if (!function_exists('menu_repo_fetch_categories')) {
    function menu_repo_fetch_categories()
    {
        global $connection;

        $rows = [];
        $result = mysqli_query($connection, "SELECT cat_id, name FROM kp_cat ORDER BY name ASC");
        if (!$result) {
            return $rows;
        }

        while ($row = mysqli_fetch_assoc($result)) {
            $rows[] = [
                'id' => (int)$row['cat_id'],
                'name' => (string)$row['name'],
            ];
        }

        return $rows;
    }
}

if (!function_exists('menu_repo_fetch_counters')) {
    function menu_repo_fetch_counters()
    {
        global $connection;
        $rows = [];
        $result = mysqli_query($connection, "SELECT cntr_id, name FROM kp_counters WHERE del_status=2 ORDER BY cntr_id ASC");
        if (!$result) return $rows;
        while ($row = mysqli_fetch_assoc($result)) {
            $rows[] = ['id' => (int)$row['cntr_id'], 'name' => (string)$row['name']];
        }
        return $rows;
    }
}

if (!function_exists('menu_repo_fetch_uoms')) {
    function menu_repo_fetch_uoms()
    {
        global $connection;
        $rows = [];
        $result = mysqli_query($connection, "SELECT uom_id, uom_name FROM kp_items_uom WHERE is_active=1 ORDER BY uom_name ASC");
        if (!$result) return $rows;
        while ($row = mysqli_fetch_assoc($result)) $rows[] = ['id' => (int)$row['uom_id'], 'name' => (string)$row['uom_name']];
        return $rows;
    }
}

if (!function_exists('menu_repo_fetch_sub_categories')) {
    function menu_repo_fetch_sub_categories()
    {
        global $connection;

        $rows = [];
        $result = mysqli_query($connection, "SELECT sub_id, name, cat_id FROM kp_sub_cat ORDER BY name ASC");
        if (!$result) {
            return $rows;
        }

        while ($row = mysqli_fetch_assoc($result)) {
            $rows[] = [
                'id' => (int)$row['sub_id'],
                'name' => (string)$row['name'],
                'cat_id' => isset($row['cat_id']) ? (int)$row['cat_id'] : 0,
            ];
        }

        return $rows;
    }
}

if (!function_exists('menu_repo_fetch_taxes')) {
    function menu_repo_fetch_taxes()
    {
        global $connection;

        $rows = [];
        $result = mysqli_query($connection, "SELECT tax_id, name, percent FROM kp_tax ORDER BY name ASC");
        if (!$result) {
            return $rows;
        }

        while ($row = mysqli_fetch_assoc($result)) {
            $rows[] = [
                'id' => (int)$row['tax_id'],
                'name' => (string)$row['name'],
                'percent' => (float)$row['percent'],
            ];
        }

        return $rows;
    }
}

if (!function_exists('menu_repo_fetch_stock_items')) {
    function menu_repo_fetch_stock_items()
    {
        global $connection;

        $rows = [];
        $result = mysqli_query($connection, "SELECT item_id, name FROM kp_items WHERE stock=1 ORDER BY name ASC");
        if (!$result) {
            return $rows;
        }

        while ($row = mysqli_fetch_assoc($result)) {
            $rows[] = [
                'id' => (int)$row['item_id'],
                'name' => (string)$row['name'],
            ];
        }

        return $rows;
    }
}

if (!function_exists('menu_repo_import_item_row')) {
    function menu_repo_import_item_row($row)
    {
        global $connection;

        $stockCode = mysqli_real_escape_string($connection, (string)($row['stock_code'] ?? ''));
        $name = mysqli_real_escape_string($connection, (string)($row['name'] ?? ''));
        $basePrice = (float)($row['base_price'] ?? 0);
        $sPrice = (float)($row['sprice'] ?? 0);
        $wPrice = (float)($row['wprice'] ?? 0);
        $bPrice = (float)($row['bprice'] ?? 0);
        $reorder = (float)($row['reorder'] ?? 0);
        $qty = (float)($row['qty'] ?? 0);

        $sql = "INSERT INTO kp_items(stock_code,name,base_price,sprice,wprice,bprice,reorder,qty) VALUES('{$stockCode}','{$name}','{$basePrice}','{$sPrice}','{$wPrice}','{$bPrice}','{$reorder}','{$qty}')";
        return mysqli_query($connection, $sql);
    }
}

if (!function_exists('menu_repo_get_item_row')) {
    function menu_repo_get_item_row($item_id)
    {
        global $connection;

        $item_id = (int)$item_id;
        if ($item_id < 1) {
            return [];
        }

        $result = mysqli_query($connection, "SELECT * FROM kp_items WHERE item_id='{$item_id}' LIMIT 1");
        if (!$result) {
            return [];
        }

        $row = mysqli_fetch_assoc($result);
        return is_array($row) ? $row : [];
    }
}

if (!function_exists('menu_repo_fetch_recipe_items')) {
    function menu_repo_fetch_recipe_items($item_id)
    {
        global $connection;

        $item_id = (int)$item_id;
        $rows = [];
        if ($item_id < 1) {
            return $rows;
        }

        $result = mysqli_query($connection, "SELECT id, raw_id, qty FROM kp_recipe WHERE item_id='{$item_id}'");
        if (!$result) {
            return $rows;
        }

        while ($row = mysqli_fetch_assoc($result)) {
            $rawId = isset($row['raw_id']) ? (int)$row['raw_id'] : 0;
            $rows[] = [
                'id' => isset($row['id']) ? (int)$row['id'] : 0,
                'raw_id' => $rawId,
                'qty' => isset($row['qty']) ? (string)$row['qty'] : '',
                'raw_name' => menu_repo_get_item_name($rawId),
            ];
        }

        return $rows;
    }
}
