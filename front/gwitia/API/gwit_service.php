<?php

if (!function_exists('gwit_service_bootstrap')) {
    function gwit_service_bootstrap($connection, $user_id, $req_id)
    {
        if ((int)$user_id < 1 || (int)$req_id < 1) {
            return ['ok' => false, 'message' => 'Requisition session is missing.', 'status' => 401];
        }

        $context = gwit_repo_get_user_context($connection, $user_id);
        $req_items = gwit_repo_get_req_items($connection, $req_id);

        return [
            'ok' => true,
            'message' => 'Requisition loaded.',
            'context' => [
                'req_id' => (int)$req_id,
                'counter' => $context['counter'],
                'counter_name' => $context['counter_name'],
                'dpt' => $context['dpt'],
                'selected_cat_id' => 0,
            ],
            'req_items' => $req_items,
        ];
    }
}

if (!function_exists('gwit_service_get_items')) {
    function gwit_service_get_items($connection, $user_id, $cat_id, $sub_id = 0)
    {
        if ((int)$user_id < 1) {
            return ['ok' => false, 'message' => 'Unauthorized.', 'status' => 401];
        }

        $context = gwit_repo_get_user_context($connection, $user_id);
        if ((int)$sub_id > 0) {
            $items = gwit_repo_get_items_by_subcategory($connection, $sub_id, $context['dpt']);
            return ['ok' => true, 'message' => 'Items loaded.', 'items' => $items];
        }

        $subcategories = gwit_repo_get_subcategories($connection, $cat_id);
        $items = gwit_repo_get_items_by_category($connection, $cat_id, $context['dpt']);
        return ['ok' => true, 'message' => 'Category loaded.', 'subcategories' => $subcategories, 'items' => $items];
    }
}

if (!function_exists('gwit_service_get_req_items')) {
    function gwit_service_get_req_items($connection, $user_id, $req_id)
    {
        if ((int)$user_id < 1 || (int)$req_id < 1) {
            return ['ok' => false, 'message' => 'Unauthorized.', 'status' => 401];
        }

        return [
            'ok' => true,
            'message' => 'Draft items loaded.',
            'req_items' => gwit_repo_get_req_items($connection, $req_id),
        ];
    }
}

if (!function_exists('gwit_service_item_modal')) {
    function gwit_service_item_modal($connection, $user_id, $item_id)
    {
        if ((int)$user_id < 1) {
            return ['ok' => false, 'message' => 'Unauthorized.', 'status' => 401];
        }
        $item = gwit_repo_get_item($connection, $item_id);
        if (!$item) {
            return ['ok' => false, 'message' => 'Item not found.', 'status' => 404];
        }
        return [
            'ok' => true,
            'message' => 'Item loaded.',
            'item' => [
                'item_id' => (int)$item['item_id'],
                'name' => $item['name'],
                'port_status' => (int)$item['port_status'],
                'portions' => $item['portions'],
            ],
        ];
    }
}

if (!function_exists('gwit_service_add_item')) {
    function gwit_service_add_item($connection, $user_id, $req_id, $item_id, $qty)
    {
        if ((int)$user_id < 1 || (int)$req_id < 1) {
            return ['ok' => false, 'message' => 'Requisition session is missing.', 'status' => 401];
        }
        if ((int)$item_id < 1 || (float)$qty <= 0) {
            return ['ok' => false, 'message' => 'Please fill all the required fields.', 'status' => 422];
        }
        if (gwit_repo_req_item_exists($connection, $req_id, $item_id)) {
            return ['ok' => false, 'message' => 'Item already in the list.', 'status' => 409];
        }

        $counter = function_exists('user_counter') ? user_counter($user_id) : 0;
        if ((int)$counter < 1) {
            $counter = (int)get_column('kp_users', 'counter', "user_id='$user_id'");
        }
        $counterValue = (isset($GLOBALS['djob']) && (int)$GLOBALS['djob'] === 3 && (int)$counter === 0) ? 'kit' : $counter;
        $shift = get_column('kp_shift', 'shift', '1');

        if (!gwit_repo_add_req_item($connection, $req_id, $user_id, $item_id, $qty, $shift, $counterValue)) {
            return ['ok' => false, 'message' => 'Failed to add the item. Please try again.', 'status' => 500];
        }

        gwit_log_action('ADDED|' . $qty . '|' . get_item($item_id) . '|TO REQUISITION NO|' . $req_id);
        return gwit_service_get_req_items($connection, $user_id, $req_id);
    }
}

if (!function_exists('gwit_service_delete_item')) {
    function gwit_service_delete_item($connection, $user_id, $req_id, $item_id)
    {
        if ((int)$user_id < 1 || (int)$req_id < 1) {
            return ['ok' => false, 'message' => 'Requisition session is missing.', 'status' => 401];
        }
        gwit_repo_delete_req_item($connection, $req_id, $item_id);
        return gwit_service_get_req_items($connection, $user_id, $req_id);
    }
}

if (!function_exists('gwit_service_submit_modal')) {
    function gwit_service_submit_modal($connection, $user_id, $req_id)
    {
        if ((int)$user_id < 1 || (int)$req_id < 1) {
            return ['ok' => false, 'message' => 'Requisition session is missing.', 'status' => 401];
        }
        $context = gwit_repo_get_user_context($connection, $user_id);
        $req_items = gwit_repo_get_req_items($connection, $req_id);
        $print_mode = (int)get_print_mode();
        return [
            'ok' => true,
            'message' => 'Submit modal loaded.',
            'modal' => [
                'counter' => $context['counter'],
                'counter_name' => $context['counter_name'],
                'item_count' => count($req_items),
                'req_items' => $req_items,
                'print_mode' => $print_mode,
                'print_counters' => $print_mode === 1 ? [] : gwit_repo_get_print_counters($connection),
            ],
        ];
    }
}

if (!function_exists('gwit_service_cancel_modal')) {
    function gwit_service_cancel_modal($user_id, $req_id)
    {
        if ((int)$user_id < 1 || (int)$req_id < 1) {
            return ['ok' => false, 'message' => 'Requisition session is missing.', 'status' => 401];
        }
        return ['ok' => true, 'message' => 'Cancel modal loaded.'];
    }
}

if (!function_exists('gwit_service_cancel_request')) {
    function gwit_service_cancel_request($connection, $user_id, $req_id)
    {
        if ((int)$user_id < 1 || (int)$req_id < 1) {
            return ['ok' => false, 'message' => 'Requisition session is missing.', 'status' => 401];
        }
        gwit_repo_delete_req_items($connection, $req_id);
        gwit_log_action('CANCELLED|-|-|REQUISITION NO|' . $req_id);
        return ['ok' => true, 'message' => 'Requisition cancelled.', 'redirect' => '../stock/store_dash.php?msg=2'];
    }
}

if (!function_exists('gwit_service_submit_request')) {
    function gwit_auto_receive_request($connection, $req_id, $user_id, $counter)
    {
        $req_id = (int)$req_id;
        $user_id = (int)$user_id;
        $counter = (int)$counter;
        if (!$connection || $req_id < 1) {
            return false;
        }

        $header = mysqli_query($connection, "SELECT counter, rec_status FROM kp_counter_req WHERE req_id='{$req_id}' ORDER BY id DESC LIMIT 1");
        $headerRow = $header ? mysqli_fetch_assoc($header) : [];
        if ($counter < 1) {
            $counter = (int)($headerRow['counter'] ?? 0);
        }
        if ($counter < 1) {
            return false;
        }
        if ((int)($headerRow['rec_status'] ?? 0) === 1) {
            return true;
        }

        $column = 'cntr' . $counter . '_qty';
        $columnEscaped = mysqli_real_escape_string($connection, $column);
        $check = mysqli_query($connection, "SHOW COLUMNS FROM kp_items LIKE '{$columnEscaped}'");
        if (!$check || mysqli_num_rows($check) < 1) {
            mysqli_query($connection, "ALTER TABLE kp_items ADD COLUMN `{$column}` DECIMAL(14,4) NOT NULL DEFAULT 0");
        }
        $checkAfter = mysqli_query($connection, "SHOW COLUMNS FROM kp_items LIKE '{$columnEscaped}'");
        if (!$checkAfter || mysqli_num_rows($checkAfter) < 1) {
            return false;
        }

        $items = mysqli_query($connection, "SELECT item_id, req_qty FROM kp_counter_req_items WHERE req_id='{$req_id}'");
        if (!$items) {
            return false;
        }
        while ($item = mysqli_fetch_assoc($items)) {
            $itemId = (int)$item['item_id'];
            $qty = (float)$item['req_qty'];
            if ($itemId < 1 || $qty <= 0) {
                continue;
            }
            $stockUpdate = mysqli_query($connection, "UPDATE kp_items SET `{$column}` = COALESCE(`{$column}`,0) + {$qty} WHERE item_id='{$itemId}'");
            if (!$stockUpdate) {
                return false;
            }
            if (mysqli_affected_rows($connection) < 1) {
                return false;
            }
            // Record the automatic requisition receipt as an IN movement.
            // The counter quantity and the audit trail must be updated together.
            $trackQty = number_format($qty, 4, '.', '');
            $trackShift = mysqli_real_escape_string($connection, (string)($_SESSION['shift'] ?? date('Y-m-d')));
            $trackDpt = (int)get_column('kp_counters', 'dpt', "cntr_id='{$counter}'");
            // Older installations may not yet have the transaction reference
            // columns. Heal them before recording the requisition movement.
            foreach ([
                'req_id' => 'INT NOT NULL DEFAULT 0',
                'trans' => "VARCHAR(155) NOT NULL DEFAULT ''",
                'trans_id' => 'INT NOT NULL DEFAULT 0',
                'stock_counter' => 'INT NOT NULL DEFAULT 0',
                'shift' => "VARCHAR(55) NOT NULL DEFAULT ''",
                'dpt' => 'INT NOT NULL DEFAULT 0',
            ] as $trackColumn => $trackDefinition) {
                $trackColumnEsc = mysqli_real_escape_string($connection, $trackColumn);
                $trackColumnCheck = mysqli_query($connection, "SHOW COLUMNS FROM kp_stock_track LIKE '{$trackColumnEsc}'");
                if ($trackColumnCheck && mysqli_num_rows($trackColumnCheck) < 1) {
                    mysqli_query($connection, "ALTER TABLE kp_stock_track ADD COLUMN `{$trackColumn}` {$trackDefinition}");
                }
            }
            $trackSql = "INSERT INTO kp_stock_track
                (item_id, req_id, user_id, qty, status, `trans`, trans_id, stock_counter, shift, dpt)
                VALUES ('{$itemId}', '{$req_id}', '{$user_id}', '{$trackQty}', 'IN', 'REQ', '{$req_id}', '{$counter}', '{$trackShift}', '{$trackDpt}')";
            if (!mysqli_query($connection, $trackSql)) {
                return false;
            }
            if (!mysqli_query($connection, "UPDATE kp_counter_req_items SET issue_qty='{$qty}', rec_qty='{$qty}', source_counter='AUTO' WHERE req_id='{$req_id}' AND item_id='{$itemId}'")) {
                return false;
            }
        }
        $statusUpdate = mysqli_query($connection, "UPDATE kp_counter_req SET issue_status=1, rec_status=1, issued_by='{$user_id}', received_by='{$user_id}', issue_created_at=NOW(), rec_created_at=NOW() WHERE req_id='{$req_id}'");
        return (bool)$statusUpdate;
    }

    function gwit_service_submit_request($connection, $user_id, $req_id, $cntr_id)
    {
        if ((int)$user_id < 1 || (int)$req_id < 1) {
            return ['ok' => false, 'message' => 'Requisition session is missing.', 'status' => 401];
        }

        $print_mode = (int)get_print_mode();
        if ($print_mode !== 1 && (int)$cntr_id < 1) {
            return ['ok' => false, 'message' => 'Please select a printer counter.', 'status' => 422];
        }

        $counter = function_exists('user_counter') ? user_counter($user_id) : 0;
        if ((int)$counter < 1) {
            $counter = (int)get_column('kp_users', 'counter', "user_id='$user_id'");
        }
        if ($counter < 1) {
            return ['ok' => false, 'message' => 'The logged-in user has no valid counter assigned.', 'status' => 422];
        }
        $counterCheck = mysqli_query($connection, "SELECT cntr_id FROM kp_counters WHERE cntr_id='{$counter}' LIMIT 1");
        if (!$counterCheck || mysqli_num_rows($counterCheck) < 1) {
            return ['ok' => false, 'message' => 'Invalid counter ID: ' . $counter, 'status' => 422];
        }
        $shift = (string)($_SESSION['shift'] ?? get_column('kp_shift', 'shift', '1'));

        if (!gwit_repo_req_exists($req_id)) {
            if (!gwit_repo_create_req($connection, $req_id, $user_id, $shift, $counter)) {
                return ['ok' => false, 'message' => mysqli_error($connection), 'status' => 500];
            }
            gwit_log_action('PLACED|-|-| REQUISITION NO|' . $req_id);
        }

        gwit_repo_update_print_counter($connection, $req_id, $cntr_id);
        gwit_repo_clear_req_status($connection, $req_id);

        // A submitted GWITIA requisition must be posted to the destination
        // counter immediately. The settings flag used by the old approval
        // screen could otherwise leave the request placed but never stocked.
        if (!gwit_auto_receive_request($connection, $req_id, $user_id, $counter)) {
            return ['ok' => false, 'message' => 'Automatic request stock update failed.', 'status' => 500];
        }

        if ($print_mode === 1) {
            return [
                'ok' => true,
                'message' => 'Requisition sent.',
                'redirect' => '../chaba/render_req.php?auto_print=1&return_to=' . urlencode('../dash/dash.php'),
            ];
        }

        $reqRows = [];
        $reqQ = mysqli_query($connection, "SELECT r.item_id,r.req_qty,COALESCE(i.name,CONCAT('Item ',r.item_id)) item_name FROM kp_counter_req_items r LEFT JOIN kp_items i ON i.item_id=r.item_id WHERE r.req_id='{$req_id}' ORDER BY r.id");
        while ($reqQ && ($reqRow=mysqli_fetch_assoc($reqQ))) $reqRows[]=['name'=>(string)$reqRow['item_name'],'qty'=>(float)$reqRow['req_qty'],'price'=>0,'total'=>0,'item_id'=>(int)$reqRow['item_id']];
        $payload = json_encode(['payload_version'=>1,'document_type'=>'GWITIA','title'=>'REQUISITION','items'=>$reqRows,'summary'=>['item_count'=>count($reqRows)],'header'=>['ref_id'=>$req_id,'counter_id'=>(int)$cntr_id,'shift'=>$shift]],JSON_UNESCAPED_UNICODE|JSON_UNESCAPED_SLASHES);
        $payloadSql=mysqli_real_escape_string($connection,(string)$payload);
        $routeQuery='id='.$req_id.'&user_id='.$user_id;
        foreach (['shift'=>"VARCHAR(60) NOT NULL DEFAULT ''",'payload_json'=>'LONGTEXT NULL','items_json'=>'LONGTEXT NULL','print_attempts'=>"TINYINT UNSIGNED NOT NULL DEFAULT 0",'last_error'=>"VARCHAR(255) NOT NULL DEFAULT ''",'printed_at'=>'DATETIME NULL DEFAULT NULL'] as $col=>$def) { $c=mysqli_query($connection,'SHOW COLUMNS FROM kp_print_queue LIKE "'.$col.'"'); if(!$c||mysqli_num_rows($c)<1) mysqli_query($connection,"ALTER TABLE kp_print_queue ADD COLUMN `{$col}` {$def}"); }
        $queue_id = mysqli_query($connection,"INSERT INTO kp_print_queue (sale_id,ref_id,print_type,label,cntr_id,route_path,route_query,requested_by,reprint_of,status,shift,payload_json,items_json) VALUES (0,'{$req_id}','gwitia','REQUISITION','".(int)$cntr_id."','render_req.php','".mysqli_real_escape_string($connection,$routeQuery)."','".(int)$user_id."',0,0,'".mysqli_real_escape_string($connection,$shift)."','{$payloadSql}','')") ? mysqli_insert_id($connection) : 0;

        if ((int)$queue_id < 1) {
            return ['ok' => false, 'message' => 'Could not queue requisition print job.', 'status' => 500];
        }

        return [
            'ok' => true,
            'message' => 'Requisition queued for printing.',
            'redirect' => '../dash/dash.php',
        ];
    }
}

if (!function_exists('gwit_service_start_modal')) {
    function gwit_service_start_modal($connection, $user_id)
    {
        if ((int)$user_id < 1) {
            return ['ok' => false, 'message' => 'Unauthorized.', 'status' => 401];
        }

        $context = gwit_repo_get_user_context($connection, $user_id);
        return [
            'ok' => true,
            'message' => 'Start requisition modal loaded.',
            'modal' => [
                'counter_name' => $context['counter_name'],
            ],
        ];
    }
}

if (!function_exists('gwit_service_init_request')) {
    function gwit_service_init_request($connection, $user_id)
    {
        if ((int)$user_id < 1) {
            return ['ok' => false, 'message' => 'Unauthorized.', 'status' => 401];
        }

        $check_order = mysqli_query($connection, "SELECT req_id FROM kp_counter_req_status WHERE req_user_id='$user_id' AND req_status=2");
        if ($check_order && mysqli_num_rows($check_order) > 0) {
            $on = mysqli_fetch_assoc($check_order);
            $new_req = (int)$on['req_id'];
            $_SESSION['req'] = $new_req;
        } else {
            $get_id = mysqli_query($connection, "SELECT req_id FROM kp_counter_req_id");
            if ($get_id && mysqli_num_rows($get_id) > 0) {
                $row = mysqli_fetch_assoc($get_id);
                $last_req = (int)$row['req_id'];
                $new_req = $last_req + 1;
                mysqli_query($connection, "INSERT INTO kp_counter_req_status(req_id,req_user_id) VALUES('$new_req','$user_id')");
                mysqli_query($connection, "UPDATE kp_counter_req_id SET req_id='$new_req'");
                $_SESSION['req'] = $new_req;
            } else {
                mysqli_query($connection, "INSERT INTO kp_counter_req_id(req_id) VALUES(1)");
                $new_req = 1;
                mysqli_query($connection, "INSERT INTO kp_counter_req_status(req_id,req_user_id) VALUES('$new_req','$user_id')");
                $_SESSION['req'] = $new_req;
            }
        }

        $counter = function_exists('user_counter') ? user_counter($user_id) : 0;
        gwit_log_action('STARTED|-|-|REQUISTIO NO|' . $_SESSION['req'] . 'FOR' . get_counter($counter) . ' COUNTER ');

        return [
            'ok' => true,
            'message' => 'Requisition started.',
            'redirect' => '../gwitia',
        ];
    }
}
