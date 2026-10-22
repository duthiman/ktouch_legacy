<?php

if (!function_exists('kug_service_bootstrap')) {
    function kug_service_bootstrap($user_id) {
        global $connection;

        $context = kug_repo_context($user_id);
        if ((int)$context['po_id'] < 1) {
            return ['ok' => false, 'message' => 'No active purchase draft found.', 'code' => 'PO_MISSING', 'redirect' => 'buy_init.php'];
        }
        if ((int)($context['settings']['front_purchase_enabled'] ?? 0) !== 1) {
            return ['ok' => false, 'message' => 'Front purchase is disabled in purchase settings.', 'code' => 'PURCHASE_DISABLED', 'redirect' => '../stock/store_dash.php?msg=front_purchase_disabled'];
        }

        $list = kug_repo_fetch_po_items((int)$context['po_id']);
        $supplier_name = (int)$context['sup_id'] > 0 && function_exists('get_sup') ? strtoupper((string)get_sup((int)$context['sup_id'])) : 'NONE';

        return [
            'ok' => true,
            'message' => 'Purchase page loaded.',
            'context' => $context,
            // Category, subcategory, and item data is loaded once by front/API/common/api.php.
            'categories_desktop_html' => '',
            'categories_mobile_html' => '',
            'items_html' => '',
            'list_html' => kug_render_list_html($list),
            'supplier_name' => $supplier_name,
        ];
    }
}

if (!function_exists('kug_service_init')) {
    function kug_service_init($user_id) {
        global $connection;

        $settings = function_exists('kp_purchase_settings') ? kp_purchase_settings($connection) : ['front_purchase_enabled' => 1];
        if ((int)($settings['front_purchase_enabled'] ?? 0) !== 1) {
            return ['ok' => false, 'message' => 'Front purchase is disabled in purchase settings.', 'code' => 'PURCHASE_DISABLED', 'redirect' => '../stock/store_dash.php?msg=front_purchase_disabled'];
        }

        $draft = kug_repo_find_open_po_status($user_id);
        if ($draft) {
            $po_id = (int)($draft['po_id'] ?? 0);
            $sup_id = (int)($draft['sup_id'] ?? 0);
        } else {
            $po_id = kug_repo_next_po_id();
            $sup_id = 0;
            if ($po_id < 1 || !kug_repo_create_po_status($po_id, $user_id, $sup_id)) {
                return ['ok' => false, 'message' => 'Could not create purchase draft.', 'code' => 'INIT_FAILED'];
            }
        }

        $_SESSION['buy_po'] = $po_id;
        $_SESSION['buy_sup'] = $sup_id;
        if (!isset($_SESSION['buy_tax'])) {
            $_SESSION['buy_tax'] = 0;
        }

        $counter = function_exists('user_counter') ? (int)user_counter($user_id) : 0;
        if (function_exists('logs')) {
            logs('STARTED|-|-|BUY STOCK DRAFT NO|' . $po_id . '|FOR ' . get_counter($counter) . ' COUNTER', '../logs');
        }

        return ['ok' => true, 'message' => 'Purchase draft ready.', 'code' => 'INIT_OK', 'redirect' => '../kug'];
    }
}

if (!function_exists('kug_service_get_subcategories')) {
    function kug_service_get_subcategories($user_id, $category_id) {
        $context = kug_repo_context($user_id);
        return [
            'ok' => true,
            'message' => 'Subcategories loaded.',
            'html' => kug_render_subcategories_html(kug_repo_fetch_subcategories($category_id, (string)$context['dpt'])),
        ];
    }
}

if (!function_exists('kug_service_get_items')) {
    function kug_service_get_items($user_id, $category_id = 0, $sub_id = 0) {
        $context = kug_repo_context($user_id);
        return [
            'ok' => true,
            'message' => 'Items loaded.',
            'html' => kug_render_items_html(kug_repo_fetch_items([
                'dpt' => $context['dpt'],
                'cat_id' => (int)$category_id,
                'sub_id' => (int)$sub_id,
            ])),
        ];
    }
}

if (!function_exists('kug_service_get_list')) {
    function kug_service_get_list($user_id) {
        $context = kug_repo_context($user_id);
        return [
            'ok' => true,
            'message' => 'Draft list loaded.',
            'html' => kug_render_list_html(kug_repo_fetch_po_items((int)$context['po_id'])),
        ];
    }
}

if (!function_exists('kug_service_toggle_tax')) {
    function kug_service_toggle_tax($user_id, $tax) {
        $context = kug_repo_context($user_id);
        $_SESSION['buy_tax'] = (int)$context['sup_id'] > 0 && (int)$tax === 1 ? 1 : 0;
        return [
            'ok' => true,
            'message' => 'Tax preference updated.',
            'buy_tax' => (int)$_SESSION['buy_tax'],
        ];
    }
}

if (!function_exists('kug_service_supplier_modal')) {
    function kug_service_supplier_modal($user_id) {
        $context = kug_repo_context($user_id);
        return [
            'ok' => true,
            'message' => 'Supplier list loaded.',
            'html' => kug_render_supplier_modal_html(kug_repo_fetch_suppliers(), (int)$context['sup_id']),
        ];
    }
}

if (!function_exists('kug_service_save_supplier')) {
    function kug_service_save_supplier($user_id, $sup_id) {
        $context = kug_repo_context($user_id);
        $sup_id = (int)$sup_id;
        kug_repo_update_supplier((int)$context['po_id'], $sup_id);
        $_SESSION['buy_sup'] = $sup_id;
        if ($sup_id === 0) {
            $_SESSION['buy_tax'] = 0;
        }

        $name = $sup_id > 0 && function_exists('get_sup') ? strtoupper((string)get_sup($sup_id)) : 'NONE';

        return [
            'ok' => true,
            'message' => 'Supplier updated.',
            'supplier_name' => $name,
            'supplier_id' => $sup_id,
            'buy_tax' => isset($_SESSION['buy_tax']) ? (int)$_SESSION['buy_tax'] : 0,
        ];
    }
}

if (!function_exists('kug_service_create_supplier')) {
    function kug_service_create_supplier($user_id, $payload) {
        $sup_name = trim((string)($payload['sup_name'] ?? ''));
        $sup_phone = trim((string)($payload['sup_phone'] ?? ''));
        $sup_pin = trim((string)($payload['sup_pin'] ?? ''));
        $sup_vat = trim((string)($payload['sup_vat'] ?? ''));

        if ($sup_name === '') {
            return ['ok' => false, 'message' => 'Supplier name is required.', 'code' => 'SUPPLIER_NAME_REQUIRED'];
        }

        if (kug_repo_supplier_exists_by_name($sup_name)) {
            return ['ok' => false, 'message' => 'That supplier already exists.', 'code' => 'SUPPLIER_EXISTS'];
        }

        $sup_id = kug_repo_next_supplier_id();
        if ($sup_id < 1 || !kug_repo_insert_supplier($sup_id, $sup_name, $sup_phone, $sup_pin, $sup_vat)) {
            return ['ok' => false, 'message' => 'Could not create supplier.', 'code' => 'SUPPLIER_CREATE_FAILED'];
        }

        $context = kug_repo_context($user_id);
        if ((int)($context['po_id'] ?? 0) > 0) {
            kug_repo_update_supplier((int)$context['po_id'], $sup_id);
        }

        $_SESSION['buy_sup'] = $sup_id;

        if (function_exists('logs')) {
            logs('NEW SUPPLIER ADDED FROM FRONT BUY| ' . $sup_name, '../logs');
        }

        return [
            'ok' => true,
            'message' => 'Supplier added successfully.',
            'code' => 'SUPPLIER_CREATED',
            'supplier_id' => $sup_id,
            'supplier_name' => strtoupper($sup_name),
            'buy_tax' => isset($_SESSION['buy_tax']) ? (int)$_SESSION['buy_tax'] : 0,
        ];
    }
}

if (!function_exists('kug_service_add_item')) {
    function kug_service_add_item($user_id, $payload) {
        $context = kug_repo_context($user_id);
        $po_id = (int)$context['po_id'];
        $item_id = (int)($payload['item_id'] ?? 0);
        $qty = (float)($payload['qty'] ?? 0);
        $price = round((float)($payload['price'] ?? 0), 2);
        $total = round((float)($payload['total'] ?? 0), 2);
        $buy_unit = trim((string)($payload['buy_unit'] ?? 'pcs'));
        $buy_unit_qty = (float)($payload['buy_unit_qty'] ?? 0);
        $buy_pack_size = (float)($payload['buy_pack_size'] ?? 0);
        $buy_unit_cost = (float)($payload['buy_unit_cost'] ?? 0);
        $shift = function_exists('get_column') ? (string)get_column('kp_shift', 'shift', "1") : date('Y-m-d');
        $allowed_units = ['pcs', 'crates', 'boxes', 'packs'];

        if ($po_id < 1) {
            return ['ok' => false, 'message' => 'No active purchase draft found.', 'code' => 'PO_MISSING'];
        }
        if ($item_id < 1 || $qty <= 0 || $price <= 0 || $total <= 0) {
            return ['ok' => false, 'message' => 'Quantity, price and total must all be greater than zero.', 'code' => 'INVALID_VALUES'];
        }
        if (!in_array($buy_unit, $allowed_units, true)) {
            return ['ok' => false, 'message' => 'Please choose a valid buying unit.', 'code' => 'INVALID_UNIT'];
        }
        if ($buy_unit_qty <= 0 || $buy_pack_size <= 0 || $buy_unit_cost <= 0) {
            return ['ok' => false, 'message' => 'Number of units, pieces per unit and cost per unit must all be greater than zero.', 'code' => 'INVALID_BREAKDOWN'];
        }
        if (abs(($buy_unit_qty * $buy_pack_size) - $qty) > 0.1) {
            return ['ok' => false, 'message' => 'Total pieces do not match the selected unit breakdown.', 'code' => 'QTY_MISMATCH'];
        }
        if (abs(($buy_unit_cost / $buy_pack_size) - $price) > 0.1) {
            return ['ok' => false, 'message' => 'Price per piece does not match the unit cost entered.', 'code' => 'PRICE_MISMATCH'];
        }
        if (abs(($buy_unit_qty * $buy_unit_cost) - $total) > 0.1 || abs(($qty * $price) - $total) > 0.1) {
            return ['ok' => false, 'message' => 'Total does not match the values entered.', 'code' => 'TOTAL_MISMATCH'];
        }
        if (kug_repo_po_item_exists($po_id, $item_id)) {
            return ['ok' => false, 'message' => 'Item already in the list.', 'code' => 'ITEM_EXISTS'];
        }
        if (!kug_repo_insert_po_item($po_id, $item_id, $qty, $price, $total, $shift)) {
            return ['ok' => false, 'message' => 'Failed to add the item. Please try again.', 'code' => 'ADD_FAILED'];
        }

        if (function_exists('logs') && function_exists('get_item')) {
            logs('ADDED|' . $qty . '|' . get_item($item_id) . '|AT ' . (int)round($price) . '|TO BUY STOCK NO|' . $po_id, '../logs');
        }

        return [
            'ok' => true,
            'message' => 'Item added.',
            'code' => 'ITEM_ADDED',
            'list_html' => kug_render_list_html(kug_repo_fetch_po_items($po_id)),
        ];
    }
}

if (!function_exists('kug_service_delete_item')) {
    function kug_service_delete_item($user_id, $item_id) {
        $context = kug_repo_context($user_id);
        kug_repo_delete_po_item((int)$context['po_id'], (int)$item_id);
        return [
            'ok' => true,
            'message' => 'Item removed.',
            'list_html' => kug_render_list_html(kug_repo_fetch_po_items((int)$context['po_id'])),
        ];
    }
}

if (!function_exists('kug_service_cancel_modal')) {
    function kug_service_cancel_modal($user_id) {
        return [
            'ok' => true,
            'message' => 'Cancel modal loaded.',
            'html' => kug_render_cancel_modal_html(),
        ];
    }
}

if (!function_exists('kug_service_cancel')) {
    function kug_service_cancel($user_id) {
        $context = kug_repo_context($user_id);
        $po_id = (int)$context['po_id'];

        if ($po_id < 1) {
            unset($_SESSION['buy_po'], $_SESSION['buy_sup'], $_SESSION['buy_tax']);
            return ['ok' => true, 'message' => 'No active draft to cancel.', 'code' => 'NO_DRAFT', 'redirect' => '../stock/store_dash.php'];
        }

        kug_repo_delete_po_items_by_status($po_id, 2);
        kug_repo_delete_po_status($po_id);
        if (function_exists('logs')) {
            logs('CANCELLED|-|-|BUY STOCK NO|' . $po_id, '../logs');
        }

        unset($_SESSION['buy_po'], $_SESSION['buy_sup'], $_SESSION['buy_tax']);

        return ['ok' => true, 'message' => 'Purchase draft cancelled.', 'code' => 'CANCELLED', 'redirect' => '../stock/store_dash.php?msg=4'];
    }
}

if (!function_exists('kug_service_submit_modal')) {
    function kug_service_submit_modal($user_id) {
        $context = kug_repo_context($user_id);
        $items = kug_repo_fetch_po_items((int)$context['po_id']);
        $supplier_name = (int)$context['sup_id'] > 0 && function_exists('get_sup') ? (string)get_sup((int)$context['sup_id']) : 'Not selected';

        return [
            'ok' => true,
            'message' => 'Submit confirmation loaded.',
            'html' => kug_render_submit_modal_html([
                'counter_name' => $context['counter_name'],
                'supplier_name' => $supplier_name,
                'buy_tax' => (int)$context['buy_tax'] === 1,
                'items' => $items,
                'has_items' => !empty($items),
                'has_supplier' => (int)$context['sup_id'] > 0,
            ]),
        ];
    }
}

if (!function_exists('kug_service_submit')) {
    function kug_service_queue_flpo($po_id, $user_id, $counter, $counter_name, $shift, $sup_id, $amount, $items) {
        global $connection;

        $supplier = '';
        $supplierResult = mysqli_query($connection, "SELECT sup_name FROM kp_suppliers WHERE sup_id='" . (int)$sup_id . "' LIMIT 1");
        if ($supplierResult && ($supplierRow = mysqli_fetch_assoc($supplierResult))) {
            $supplier = (string)($supplierRow['sup_name'] ?? '');
        }

        $payloadItems = [];
        foreach ((array)$items as $item) {
            $qty = (float)($item['qty'] ?? 0);
            $price = (float)($item['price'] ?? 0);
            $total = (float)($item['total'] ?? ($qty * $price));
            $payloadItems[] = [
                'name' => (string)($item['item_name'] ?? ('Item #' . (int)($item['item_id'] ?? 0))),
                'item_id' => (int)($item['item_id'] ?? 0),
                'qty' => $qty,
                'price' => $price,
                'total' => $total,
            ];
        }

        $payload = [
            'payload_version' => 1,
            'document_type' => 'FLPO',
            'title' => 'FRONT PURCHASE ORDER',
            'items' => $payloadItems,
            'summary' => [
                'item_count' => count($payloadItems),
                'amount' => (float)$amount,
                'total' => (float)$amount,
            ],
            'header' => [
                'po_id' => (int)$po_id,
                'supplier' => $supplier,
                'supplier_name' => $supplier,
                'counter_name' => (string)$counter_name,
                'shift' => (string)$shift,
                'requested_by' => (int)$user_id,
                'print_time' => date('Y-m-d H:i:s'),
            ],
        ];

        $json = mysqli_real_escape_string($connection, json_encode($payload, JSON_UNESCAPED_UNICODE | JSON_UNESCAPED_SLASHES));
        $shiftSql = mysqli_real_escape_string($connection, (string)$shift);
        $labelSql = mysqli_real_escape_string($connection, 'FRONT PURCHASE ORDER');
        $routeSql = mysqli_real_escape_string($connection, 'render_flpo.php');
        $querySql = mysqli_real_escape_string($connection, 'po_id=' . (int)$po_id);
        $itemsSql = mysqli_real_escape_string($connection, json_encode($payloadItems, JSON_UNESCAPED_UNICODE | JSON_UNESCAPED_SLASHES));

        $sql = "INSERT INTO kp_print_queue
            (sale_id,ref_id,print_type,label,cntr_id,route_path,route_query,requested_by,reprint_of,status,shift,payload_json,items_json)
            VALUES (0,'" . (int)$po_id . "','FLPO','{$labelSql}','" . (int)$counter . "','{$routeSql}','{$querySql}','" . (int)$user_id . "',0,0,'{$shiftSql}','{$json}','{$itemsSql}')";
        return mysqli_query($connection, $sql) ? (int)mysqli_insert_id($connection) : 0;
    }

    function kug_service_submit($user_id) {
        global $connection;

        $context = kug_repo_context($user_id);
        $settings = $context['settings'];
        $po_id = (int)$context['po_id'];
        $sup_id = (int)$context['sup_id'];
        $buy_tax = (int)$context['buy_tax'];
        $counter = (int)$context['counter'];
        $counter_name = (string)$context['counter_name'];
        $shift = function_exists('get_column') ? (string)get_column('kp_shift', 'shift', "1") : date('Y-m-d');

        if ((int)($settings['front_purchase_enabled'] ?? 0) !== 1) {
            return ['ok' => false, 'message' => 'Front purchase is disabled in purchase settings.', 'code' => 'PURCHASE_DISABLED', 'redirect' => '../stock/store_dash.php?msg=front_purchase_disabled'];
        }
        if ($po_id < 1) {
            return ['ok' => false, 'message' => 'No active purchase draft found.', 'code' => 'PO_MISSING'];
        }
        if ($sup_id < 1) {
            return ['ok' => false, 'message' => 'Select supplier first.', 'code' => 'SUPPLIER_REQUIRED'];
        }
        if (!kug_repo_po_has_items($po_id)) {
            return ['ok' => false, 'message' => 'Add items first.', 'code' => 'ITEMS_REQUIRED'];
        }

        $amount = kug_repo_po_total($po_id);
        $balance = $sup_id > 0 ? (float)get_column('kp_suppliers', 'sup_bal', "sup_id='{$sup_id}'") : 0;
        kug_repo_update_supplier($po_id, $sup_id);
        $autoStock = (int)($settings['front_auto_stock_enabled'] ?? 0) === 1;

        $header = [
            'po_id' => $po_id,
            'counter' => $counter,
            'sup_id' => $sup_id,
            'user_id' => $user_id,
            'shift' => $shift,
            'amount' => $amount,
            'buy_tax' => $buy_tax,
            'vatable' => $buy_tax === 1 ? 0 : $amount,
            'vat' => 0,
            'balance' => $balance,
            'refno' => 'FRONT BUY FOR ' . $counter_name,
            'appr_status' => $autoStock ? 1 : 2,
            'appr_user' => $autoStock ? $user_id : 0,
            'rec_status' => $autoStock ? 1 : 2,
            'rec_staff' => $autoStock ? $user_id : 0,
            'stock_status' => $autoStock ? 1 : 2,
            's2s' => $autoStock ? 1 : 0,
        ];

        if (!kug_repo_insert_or_update_po_header($header)) {
            return ['ok' => false, 'message' => mysqli_error($connection), 'code' => 'HEADER_FAILED'];
        }

        kug_repo_update_po_items_for_submit($po_id, $header['rec_status'], $header['rec_staff'], $header['s2s']);

        if ($autoStock) {
            $grn_id = function_exists('get_id') ? (int)get_id('kp_grn_id', 'grn_id') : 0;
            if ($grn_id > 0) {
                if (!kug_repo_insert_grn_header($grn_id, $po_id, $user_id, $sup_id, $amount)) {
                    return ['ok' => false, 'message' => mysqli_error($connection), 'code' => 'GRN_HEADER_FAILED'];
                }
                $items = kug_repo_fetch_po_items($po_id);
                if (function_exists('kp_ensure_counter_stock_column')) {
                    if (!kp_ensure_counter_stock_column($connection, $counter)) {
                        return ['ok' => false, 'message' => 'Could not prepare the counter stock column.', 'code' => 'COUNTER_STOCK_COLUMN_FAILED'];
                    }
                }
                $column = function_exists('kp_purchase_stock_column') ? kp_purchase_stock_column($connection, $counter, true) : '';
                if ($column === '') {
                    return ['ok' => false, 'message' => 'Could not identify the counter stock column.', 'code' => 'COUNTER_STOCK_COLUMN_FAILED'];
                }

                foreach ($items as $item) {
                    $item_id = (int)$item['item_id'];
                    $qty = (float)$item['qty'];
                    $price = (float)$item['price'];
                    $total = (float)$item['total'];
                    $dpt = function_exists('get_column') ? (string)get_column('kp_items', 'dpt', "item_id='{$item_id}'") : '';

                    if (!kug_repo_insert_grn_item($grn_id, $item_id, $qty, $price, $total, $user_id)) {
                        return ['ok' => false, 'message' => mysqli_error($connection), 'code' => 'GRN_ITEM_FAILED'];
                    }
                    kug_repo_update_item_buy_price($item_id, $price);
                    if (!kug_repo_update_item_stock($item_id, $column, $qty)) {
                        return ['ok' => false, 'message' => mysqli_error($connection), 'code' => 'STOCK_UPDATE_FAILED'];
                    }
                    if (!kug_repo_insert_stock_track($item_id, $po_id, $grn_id, $user_id, $qty, $counter, $dpt)) {
                        return ['ok' => false, 'message' => mysqli_error($connection), 'code' => 'STOCK_TRACK_FAILED'];
                    }
                }

                if ($sup_id > 0) {
                    kug_repo_update_supplier_balance($sup_id, $amount);
                }
            }
        }

        kug_repo_delete_po_status($po_id);
        unset($_SESSION['buy_po'], $_SESSION['buy_sup'], $_SESSION['buy_tax']);

        if (function_exists('logs')) {
            logs(($autoStock ? 'PLACED AND STOCKED' : 'RAISED FOR APPROVAL') . '|-|-|BUY STOCK NO|' . $po_id . '|FOR ' . $counter_name . ' COUNTER', '../logs');
        }

        $printQueueId = kug_service_queue_flpo($po_id, $user_id, $counter, $counter_name, $shift, $sup_id, $amount, $items ?? kug_repo_fetch_po_items($po_id));

        return [
            'ok' => true,
            'message' => $autoStock ? 'Purchase placed and stocked.' : 'Purchase raised for approval.',
            'code' => $autoStock ? 'PO_STOCKED' : 'PO_RAISED',
            'queued_print_job' => $printQueueId,
            'redirect' => '../stock/store_dash.php?msg=' . ($autoStock ? 'po_stocked' : 'po_raised'),
        ];
    }
}
