<?php

if (!function_exists('menu_service_get_items')) {
    function menu_service_get_items()
    {
        global $sql_details;

        menu_ensure_item_tables();
        if (!isset($_GET['draw']) || $_GET['draw'] == 1) {
            menu_ensure_item_stock_take_system();
        }
        return menu_repo_datatable_response($_GET, $sql_details);
    }
}

if (!function_exists('menu_service_search_items')) {
    function menu_service_search_items($request = [])
    {
        menu_ensure_item_tables();
        $request = is_array($request) ? $request : [];
        return [
            'ok' => true,
            'message' => 'Menu search completed.',
            'filters' => [
                'cat_id' => menu_clean_int($request['cat_id'] ?? 0),
                'sub_id' => menu_clean_int($request['sub_id'] ?? 0),
                'dpt' => menu_clean_int($request['dpt'] ?? 0),
                'stock' => menu_clean_int($request['stock'] ?? 0),
                'search' => menu_clean_text($request['search'] ?? ''),
            ],
        ] + menu_repo_search_items($request);
    }
}

if (!function_exists('menu_service_export_items')) {
    function menu_service_export_items($request)
    {
        global $sql_details;

        menu_ensure_item_tables();
        $params = is_array($request) ? $request : [];
        $params['start'] = 0;
        $params['length'] = -1;
        if (!isset($params['draw'])) {
            $params['draw'] = 1;
        }

        return menu_repo_datatable_response($params, $sql_details);
    }
}

if (!function_exists('menu_service_edit_item')) {
    function menu_service_edit_item($id, $column_name, $text, $field)
    {
        global $connection;

        $id = (int)$id;
        $field = menu_clean_text($field);
        $text = (string)$text;
        $allowed_columns = array('name', 'price', 'hhr', 'b_price', 'menu', 'deci', 'stock', 'recipe', 'portion', 'tots', 'dpt', 'tax_id');

        if ($id < 1 || !in_array($column_name, $allowed_columns, true)) {
            return ['ok' => false, 'message' => 'Invalid item update request.'];
        }

        $safe_text = mysqli_real_escape_string($connection, $text);
        $safe_column = $column_name === 'portion' ? 'port_status' : ($column_name === 'tots' ? 'tot_status' : $column_name);
        $sql = "UPDATE kp_items SET {$safe_column}='{$safe_text}' WHERE item_id='{$id}'";

        if (!menu_repo_update_item_column($id, $column_name, $text)) {
            return ['ok' => false, 'message' => 'Could not update item.'];
        }

        $item_name = function_exists('get_item') ? get_item($id) : menu_repo_get_item_name($id);
        if (function_exists('logs')) {
            $action = 'EDITED|-|-|ITEM DETAILS FOR |' . $item_name;
            logs($action, '../../logs');
        }

        return [
            'ok' => true,
            'message' => $item_name . ' ' . $field . ' Updated Successfully!!!!',
        ];
    }
}

if (!function_exists('menu_service_modal_view')) {
    function menu_service_modal_view($view, $params = [])
    {
        menu_ensure_item_tables();
        $allowed = [
            'prods_new' => __DIR__ . '/../prod_new2.php',
            'prod_new2' => __DIR__ . '/../prod_new2.php',
            'prod_cat' => __DIR__ . '/../prod_cat.php',
            'prod_sub' => __DIR__ . '/../prod_sub.php',
            'prods_del' => __DIR__ . '/../prods_del.php',
            'prod_set' => __DIR__ . '/../prod_set.php',
            'prods_tax' => __DIR__ . '/../prods_tax.php',
            'units_manage' => __DIR__ . '/../units_manage.php',
            'portion_manage' => __DIR__ . '/../portion_manage.php',
            'tots_manage' => __DIR__ . '/../tots_manage.php',
            'clear_confirm' => __DIR__ . '/../clear_confirm.php',
            'recipe_confirm' => __DIR__ . '/../recipe_confirm.php',
            'recipe_items' => __DIR__ . '/../recipe_items_modal.php',
        ];

        if (!isset($allowed[$view])) {
            return ['ok' => false, 'message' => 'Unknown modal view.'];
        }

        $params = is_array($params) ? $params : [];
        $params = menu_service_modal_params($view, $params);
        $html = menu_include_capture($allowed[$view], $params);
        return [
            'ok' => true,
            'message' => 'Modal loaded.',
            'html' => $html,
        ];
    }
}

if (!function_exists('menu_service_modal_params')) {
    function menu_service_modal_params($view, $params)
    {
        $params = is_array($params) ? $params : [];
        $id = isset($params['id']) ? (int)$params['id'] : 0;

        if (($view === 'portion_manage' || $view === 'clear_confirm' || $view === 'tots_manage' || $view === 'units_manage' || $view === 'prod_cat' || $view === 'prod_sub' || $view === 'prods_tax' || $view === 'prod_set') && $id > 0) {
            $itemRow = menu_repo_get_item_row($id);
            $params['item_id'] = $id;
            $params['item_name'] = isset($itemRow['name']) ? (string)$itemRow['name'] : '';
            $params['item_row'] = $itemRow;
        }

        if (($view === 'recipe_confirm' || $view === 'recipe_items') && $id > 0) {
            $itemRow = menu_repo_get_item_row($id);
            $params['item_id'] = $id;
            $params['item_name'] = isset($itemRow['name']) ? (string)$itemRow['name'] : '';
        }

        if ($view === 'prod_sub' && $id > 0 && isset($params['item_row']) && is_array($params['item_row'])) {
            $params['current_cat_id'] = isset($params['item_row']['cat_id']) ? (int)$params['item_row']['cat_id'] : 0;
        }

        if ($view === 'tots_manage' || $view === 'units_manage') {
            $params['stock_items'] = menu_repo_fetch_stock_items();
            if ($view === 'units_manage') $params['uoms'] = menu_repo_fetch_uoms();
        }

        if ($view === 'recipe_items' && $id > 0) {
            if (function_exists('update_item_recipe')) {
                update_item_recipe($id);
            }
            $params['recipe_items'] = menu_repo_fetch_recipe_items($id);
        }

        return $params;
    }
}

if (!function_exists('menu_service_selected_ids')) {
    function menu_service_selected_ids($items)
    {
        $raw = is_array($items) ? $items : explode(',', (string)$items);
        $ids = [];
        foreach ($raw as $item) {
            $id = (int)$item;
            if ($id > 0) {
                $ids[] = $id;
            }
        }
        return array_values(array_unique($ids));
    }
}

if (!function_exists('menu_service_bulk_or_single_ids')) {
    function menu_service_bulk_or_single_ids($items)
    {
        $ids = menu_service_selected_ids($items);
        return count($ids) > 0 ? $ids : [(int)$items];
    }
}

if (!function_exists('menu_service_change_category')) {
    function menu_service_change_category($items, $cat_id)
    {
        $ids = menu_service_bulk_or_single_ids($items);
        $cat_id = (int)$cat_id;
        if (count($ids) < 1 || $cat_id < 1) {
            return ['ok' => false, 'message' => 'Select items and a category first.'];
        }
        $cat_name = menu_repo_get_cat_name($cat_id);
        foreach ($ids as $item_id) {
            $item_name = menu_repo_get_item_name($item_id);
            menu_repo_update_item_category($item_id, $cat_id);
            if (function_exists('logs')) {
                logs('CHANGED|-|-|CATEGORY FOR |' . $item_name, '../../logs');
            }
            menu_repo_insert_item_history($item_id, 'ITEM CATEGORY CHANGED', (int)($_SESSION['user_id'] ?? 0));
        }
        return ['ok' => true, 'message' => 'Category successfully updated.', 'refresh' => true, 'cat_name' => $cat_name];
    }
}

if (!function_exists('menu_service_change_sub_category')) {
    function menu_service_change_sub_category($items, $sub_id)
    {
        $ids = menu_service_bulk_or_single_ids($items);
        $sub_id = (int)$sub_id;
        if (count($ids) < 1 || $sub_id < 1) {
            return ['ok' => false, 'message' => 'Select items and a sub category first.'];
        }
        foreach ($ids as $item_id) {
            $item_name = menu_repo_get_item_name($item_id);
            menu_repo_update_item_sub_category($item_id, $sub_id);
            if (function_exists('logs')) {
                logs('CHANGED|-|-|SUB CATEGORY FOR |' . $item_name, '../../');
            }
            menu_repo_insert_item_history($item_id, 'ITEM CATEGORY CHANGED', (int)($_SESSION['user_id'] ?? 0));
        }
        return ['ok' => true, 'message' => 'Sub category successfully updated.', 'refresh' => true];
    }
}

if (!function_exists('menu_service_change_settings')) {
    function menu_service_set_butchery($item_id, $enabled)
    {
        global $connection;
        $item_id = (int)$item_id;
        if ($item_id < 1) return ['ok' => false, 'message' => 'Invalid item.'];
        menu_ensure_item_tables();
        $ok = mysqli_query($connection, "UPDATE kp_items SET butchery_enabled='" . ($enabled ? 1 : 0) . "' WHERE item_id='{$item_id}' LIMIT 1");
        return ['ok' => (bool)$ok, 'message' => $enabled ? 'Item enabled for butchery.' : 'Item removed from butchery.', 'refresh' => true];
    }

    function menu_service_change_settings($items, $stock, $menu, $point, $dpt, $butchery_enabled = false)
    {
        $ids = menu_service_bulk_or_single_ids($items);
        if (count($ids) < 1) {
            return ['ok' => false, 'message' => 'Select items first.'];
        }
        foreach ($ids as $item_id) {
            $item_name = menu_repo_get_item_name($item_id);
            menu_repo_update_item_settings($item_id, $dpt, $stock, $menu, $point);
            mysqli_query($GLOBALS['connection'], "UPDATE kp_items SET butchery_enabled='" . ($butchery_enabled ? 1 : 0) . "' WHERE item_id='" . (int)$item_id . "'");
            if (function_exists('logs')) {
                logs('CHANGED|-|-|PRODUCT SETTINGS FOR |' . $item_name, '../../logs');
            }
            menu_repo_insert_item_history($item_id, 'ITEM SETTINGSCHANGED', (int)($_SESSION['user_id'] ?? 0));
        }
        return ['ok' => true, 'message' => 'Product settings updated.', 'refresh' => true];
    }
}

if (!function_exists('menu_service_change_tax')) {
    function menu_service_change_tax($items, $tax_id)
    {
        $ids = menu_service_selected_ids($items);
        $tax_id = (int)$tax_id;
        if (count($ids) < 1 || $tax_id < 1) {
            return ['ok' => false, 'message' => 'Select items and a tax first.'];
        }
        $tax_name = menu_repo_get_tax_name($tax_id);
        foreach ($ids as $item_id) {
            $item_name = menu_repo_get_item_name($item_id);
            menu_repo_update_item_tax($item_id, $tax_id);
            if (function_exists('logs')) {
                logs('CHANGED|-|-|TAX FOR |' . $item_name, '../../');
            }
            menu_repo_insert_item_history($item_id, 'ITEM TAX CHANGED TO ' . $tax_name, (int)($_SESSION['user_id'] ?? 0));
        }
        return ['ok' => true, 'message' => 'Tax updated.', 'refresh' => true];
    }
}

if (!function_exists('menu_service_discontinue_items')) {
    function menu_service_discontinue_items($items)
    {
        $ids = menu_service_selected_ids($items);
        if (count($ids) < 1) {
            return ['ok' => false, 'message' => 'Select items first.'];
        }
        menu_ensure_archive_table_columns('kp_items', 'kp_items_arch');
        foreach ($ids as $item_id) {
            $item_name = menu_repo_get_item_name($item_id);
            $arch = menu_repo_archive_item($item_id);
            if ($arch) {
                menu_repo_insert_item_history($item_id, 'ITEM DISCONTINUED', (int)($_SESSION['user_id'] ?? 0));
                menu_repo_delete_item($item_id);
                if (function_exists('logs')) {
                    logs('DISCONTINUED|-|-|-|' . $item_name, '../../logs');
                }
            }
        }
        return ['ok' => true, 'message' => 'Selected items discontinued.', 'refresh' => true];
    }
}

if (!function_exists('menu_service_create_item')) {
    function menu_service_create_item($payload)
    {
        $user_id = (int)($_SESSION['user_id'] ?? 0);
        $name = strtoupper(menu_clean_text($payload['name'] ?? ''));
        $tax_id = (int)($payload['tax_id'] ?? 0);
        $price = $payload['sprice'] ?? '';
        if ($name === '') {
            return ['ok' => false, 'message' => 'Name is required.'];
        }
        if (function_exists('check_duplicate') && check_duplicate('kp_items', 'name', $name)) {
            return ['ok' => false, 'message' => 'Warning!!! Item already Exist'];
        }
        if ($price === '') {
            return ['ok' => false, 'message' => 'Selling price is required.'];
        }
        if ($tax_id < 1) {
            return ['ok' => false, 'message' => 'Tax is required.'];
        }
        $cat_id = (int)($payload['cat_id'] ?? 0);
        $sub_id = (int)($payload['sub_id'] ?? 0);
        $dpt = (int)($payload['dpt'] ?? 0);
        $uom_id = (int)($payload['uom'] ?? 0);
        $bprice = (float)($payload['bprice'] ?? 0);
        $hhr = (float)$price;
        $stock = (int)($payload['stock'] ?? 0);
        $menu = (int)($payload['menu'] ?? 1);
        $deci = (int)($payload['point'] ?? 1);
        $stock_qty = (float)($payload['stock_qty'] ?? 100);
        $reorder = (float)($payload['reorder'] ?? 10);
        $butchery_enabled = !empty($payload['butchery_enabled']) ? 1 : 0;
        $field_values = array("bname","name","cat_id","sub_id","dpt","uom_id","price","hhr","menu","deci","stock","store_qty","store_reorder","b_price","tax_id","butchery_enabled");
        $data_values = array("'" . $name . "'","'" . $name . "'","'{$cat_id}'","'{$sub_id}'","'{$dpt}'","'{$uom_id}'","'" . (float)$price . "'","'{$hhr}'","'{$menu}'","'{$deci}'","'{$stock}'","'{$stock_qty}'","'{$reorder}'","'{$bprice}'","'{$tax_id}'","'{$butchery_enabled}'");
        if (!menu_repo_insert_item($field_values, $data_values)) {
            global $connection;
            return ['ok' => false, 'message' => 'Error while adding item ' . mysqli_error($connection)];
        }
        $item_id = menu_repo_last_insert_id();
        global $connection;
        $counter_stock = is_array($payload['counter_stock'] ?? null) ? $payload['counter_stock'] : [];
        $counter_reorder = is_array($payload['counter_reorder'] ?? null) ? $payload['counter_reorder'] : [];
        foreach ($counter_stock as $counter_id => $qty) {
            $counter_id = (int)$counter_id;
            if ($counter_id < 1) continue;
            $qty_column = 'cntr' . $counter_id . '_qty';
            $reorder_column = 'cntr' . $counter_id . '_reorder';
            $qty_exists = mysqli_query($connection, "SHOW COLUMNS FROM kp_items LIKE '{$qty_column}'");
            $reorder_exists = mysqli_query($connection, "SHOW COLUMNS FROM kp_items LIKE '{$reorder_column}'");
            $updates = [];
            if ($qty_exists && mysqli_num_rows($qty_exists) > 0) $updates[] = "`{$qty_column}`='" . (float)$qty . "'";
            if ($reorder_exists && mysqli_num_rows($reorder_exists) > 0) $updates[] = "`{$reorder_column}`='" . (float)($counter_reorder[$counter_id] ?? 10) . "'";
            if ($updates) mysqli_query($connection, "UPDATE kp_items SET " . implode(',', $updates) . " WHERE item_id='{$item_id}'");
        }
        menu_repo_insert_item_history($item_id, 'ITEM CREATED', $user_id);
        return ['ok' => true, 'message' => 'Item Added Successfully', 'refresh' => true];
    }
}

if (!function_exists('menu_service_save_portion')) {
    function menu_service_save_portion($item_id, $portions)
    {
        $item_id = (int)$item_id;
        $portions = (float)$portions;
        if ($item_id < 1) {
            return ['ok' => false, 'message' => 'Recheck your selection'];
        }
        if ($portions <= 0) {
            return ['ok' => false, 'message' => 'Portions are required'];
        }
        $ok = menu_repo_update_portion($item_id, $portions, true);
        return $ok
            ? ['ok' => true, 'message' => 'Portions Successfully Added', 'refresh' => true]
            : ['ok' => false, 'message' => 'Could not be added!!!! Contact admin'];
    }
}

if (!function_exists('menu_service_clear_portion')) {
    function menu_service_clear_portion($item_id)
    {
        $item_id = (int)$item_id;
        $ok = menu_repo_update_portion($item_id, 0, false);
        return $ok
            ? ['ok' => true, 'message' => 'Tots Successfully Cleared', 'refresh' => true]
            : ['ok' => false, 'message' => 'Could not be Cleared!!!! Contact admin'];
    }
}

if (!function_exists('menu_service_save_tots')) {
    function menu_service_save_tots($payload)
    {
        menu_ensure_item_stock_take_system();
        $tot_bot = (int)($payload['tot_bot'] ?? 0);
        $item_id = (int)($payload['item_id'] ?? 0);
        $stock_take_type = (int)($payload['stock_take_type'] ?? 2);
        $empty_bottle_weight = (float)($payload['empty_bottle_weight'] ?? 0);
        $full_bottle_weight = (float)($payload['full_bottle_weight'] ?? 0);
        $bottle_mls = (float)($payload['bottle_mls'] ?? 0);
        $tot_size = (float)($payload['tot_size'] ?? 30);
        if ($item_id < 1) {
            return ['ok' => false, 'message' => 'Recheck your selection'];
        }
        if ($tot_bot < 1) {
            return ['ok' => false, 'message' => 'The units item cannot be empty'];
        }
        if ($stock_take_type !== 1 && $stock_take_type !== 2) {
            return ['ok' => false, 'message' => 'Select a stock take type'];
        }
        if ($stock_take_type === 1) {
            if ($empty_bottle_weight <= 0) {
                return ['ok' => false, 'message' => 'Empty bottle weight is required for weight stock take'];
            }
            if ($full_bottle_weight <= 0) {
                return ['ok' => false, 'message' => 'Full bottle weight is required for weight stock take'];
            }
            if ($bottle_mls <= 0) {
                return ['ok' => false, 'message' => 'Bottle MLs are required for weight stock take'];
            }
            if ($tot_size <= 0) {
                return ['ok' => false, 'message' => 'Tot size MLs are required for weight stock take'];
            }
            if ($full_bottle_weight <= $empty_bottle_weight) {
                return ['ok' => false, 'message' => 'Full bottle weight must be greater than empty bottle weight'];
            }
        } else {
            $empty_bottle_weight = 0;
            $full_bottle_weight = 0;
            $bottle_mls = 0;
            $tot_size = 30;
        }
        $tot_num = ($tot_size > 0 && $bottle_mls > 0) ? round($bottle_mls / $tot_size, 2) : (float)($payload['tot_num'] ?? 0);
        if ($tot_num <= 0) {
            return ['ok' => false, 'message' => 'Units per bottle are required'];
        }
        $ok = menu_repo_update_tots($item_id, $tot_bot, $tot_num, $stock_take_type, $empty_bottle_weight, $full_bottle_weight, $bottle_mls, $tot_size, true);
        return $ok
            ? ['ok' => true, 'message' => 'Units Setup Successfully Saved', 'refresh' => true]
            : ['ok' => false, 'message' => 'Could not be added!!!! Contact admin'];
    }
}

if (!function_exists('menu_service_save_units')) {
    function menu_service_save_units($item_id, $tot_bot)
    {
        $item_id = (int)$item_id;
        $tot_bot = (int)$tot_bot;
        $row = menu_repo_get_item_row($item_id);
        if ($item_id < 1 || $tot_bot < 1) return ['ok' => false, 'message' => 'Select a units item first.'];
        if (!$row) return ['ok' => false, 'message' => 'Item was not found.'];
        $ok = menu_repo_update_tots($item_id, $tot_bot, (float)($row['tot_num'] ?? 1), (int)($row['stock_take_type'] ?? 2), (float)($row['empty_bottle_weight'] ?? 0), (float)($row['full_bottle_weight'] ?? 0), (float)($row['bottle_mls'] ?? 0), (float)($row['tot_size'] ?? 30), true);
        return $ok ? ['ok' => true, 'message' => 'Units updated successfully.', 'refresh' => true] : ['ok' => false, 'message' => 'Could not update units.'];
    }
}

if (!function_exists('menu_service_save_uom')) {
    function menu_service_save_uom($item_id, $uom_id)
    {
        global $connection;
        $item_id = (int)$item_id; $uom_id = (int)$uom_id;
        if ($item_id < 1 || $uom_id < 1) return ['ok' => false, 'message' => 'Select a unit of measure first.'];
        $ok = mysqli_query($connection, "UPDATE kp_items SET uom_id='{$uom_id}' WHERE item_id='{$item_id}' LIMIT 1");
        return $ok ? ['ok' => true, 'message' => 'Unit of measure updated.', 'refresh' => true] : ['ok' => false, 'message' => 'Could not update unit of measure.'];
    }
}

if (!function_exists('menu_service_clear_tots')) {
    function menu_service_clear_tots($item_id)
    {
        menu_ensure_item_stock_take_system();
        $item_id = (int)$item_id;
        $ok = menu_repo_update_tots($item_id, 0, 0, 2, 0, 0, 0, 30, false);
        return $ok
            ? ['ok' => true, 'message' => 'Units Setup Successfully Cleared', 'refresh' => true]
            : ['ok' => false, 'message' => 'Could not be Cleared!!!! Contact admin'];
    }
}

if (!function_exists('menu_service_search_unmapped_items')) {
    function menu_service_search_unmapped_items($attr_id, $search = '')
    {
        $attr_id = (int)$attr_id;

        if ($attr_id < 1) {
            return [
                'ok' => false,
                'message' => 'Invalid attribute ID.',
                'items' => [],
            ];
        }

        return [
            'ok' => true,
            'message' => 'Items loaded.',
            'items' => menu_repo_search_unmapped_items($attr_id, $search, 20),
        ];
    }
}

if (!function_exists('menu_service_bootstrap')) {
    function menu_service_bootstrap()
    {
        menu_ensure_item_tables();
        return [
            'ok' => true,
            'message' => 'Menu bootstrap loaded.',
            'categories' => menu_repo_fetch_categories(),
            'sub_categories' => menu_repo_fetch_sub_categories(),
            'taxes' => menu_repo_fetch_taxes(),
            'stock_items' => menu_repo_fetch_stock_items(),
            'counters' => menu_repo_fetch_counters(),
            'uoms' => menu_repo_fetch_uoms(),
        ];
    }
}

if (!function_exists('menu_service_import_preview_rows')) {
    function menu_service_import_preview_rows($filePath)
    {
        $rows = [];
        if (!is_file($filePath)) {
            return $rows;
        }

        $handle = fopen($filePath, 'r');
        if (!$handle) {
            return $rows;
        }

        $line = 0;
        while (($csv = fgetcsv($handle, 10000, ',')) !== false) {
            if ($line === 0) {
                $line++;
                continue;
            }
            $line++;
            $name = trim((string)($csv[1] ?? ''));
            if ($name === '') {
                continue;
            }
            $rows[] = [
                'stock_code' => (string)($csv[0] ?? ''),
                'name' => strtoupper($name),
                'base_price' => (string)($csv[2] ?? ''),
                'sprice' => (string)($csv[3] ?? ''),
                'wprice' => (string)($csv[4] ?? ''),
                'bprice' => (string)($csv[5] ?? ''),
                'reorder' => (string)($csv[6] ?? ''),
                'qty' => (string)($csv[7] ?? ''),
            ];
        }

        fclose($handle);
        return $rows;
    }
}

if (!function_exists('menu_service_import_items')) {
    function menu_service_import_items($fileInfo)
    {
        global $connection;

        if (!isset($fileInfo['tmp_name']) || !is_uploaded_file($fileInfo['tmp_name'])) {
            return ['ok' => false, 'message' => 'Choose a CSV file first.'];
        }

        $rows = menu_service_import_preview_rows($fileInfo['tmp_name']);
        if (count($rows) < 1) {
            return ['ok' => false, 'message' => 'No valid item rows were found in the file.'];
        }

        $userId = (int)($_SESSION['user_id'] ?? 0);
        $imported = 0;
        $errors = [];

        foreach ($rows as $row) {
            if (!menu_repo_import_item_row($row)) {
                $errors[] = [
                    'name' => $row['name'],
                    'error' => mysqli_error($connection),
                ];
                continue;
            }

            $itemId = menu_repo_last_insert_id();
            menu_repo_insert_item_history($itemId, 'ITEM IMPORTED', $userId);
            $imported++;
        }

        $ext = strtolower(pathinfo((string)($fileInfo['name'] ?? ''), PATHINFO_EXTENSION));
        if ($ext !== '') {
            $targetPath = __DIR__ . '/../uploads/' . date('dmYHis') . '.' . $ext;
            @move_uploaded_file($fileInfo['tmp_name'], $targetPath);
        }

        return [
            'ok' => $imported > 0,
            'message' => $imported > 0 ? 'Data imported successfully.' : 'No rows were imported.',
            'imported' => $imported,
            'errors' => $errors,
            'rows' => $rows,
        ];
    }
}
