<?php
session_start();
header('Content-Type: application/json; charset=utf-8');
require_once '../../inc/connection.php';
require_once '../../inc/gen_funcs.php';
require_once '../../front/counter/api/counter_inventory_schema.php';
require_once '../../front/chaba/funcs.php';

$user_id = (int)($_SESSION['user_id'] ?? $_SESSION['mwene'] ?? 0);
counter_inventory_ensure_schema();
$request_id = (int)($_POST['request_id'] ?? 0);
$basket = json_decode((string)($_POST['basket_json'] ?? '[]'), true);

function counter_issue_json_error($message, $status = 422) {
    http_response_code($status);
    echo json_encode(['ok' => false, 'message' => $message]);
    exit;
}

if ($user_id < 1 || $request_id < 1 || !is_array($basket) || !$basket) {
    counter_issue_json_error('A valid request and at least one item are required.');
}

$request_q = mysqli_query($connection, "SELECT req_id, counter, issue_status, rec_status FROM kp_counter_req WHERE req_id='$request_id' LIMIT 1");
$request = $request_q ? mysqli_fetch_assoc($request_q) : null;
if (!$request) {
    counter_issue_json_error('The counter issue could not be found.', 404);
}
$counter_id = (int)$request['counter'];
if ($counter_id < 1) {
    counter_issue_json_error('The request has no destination counter.');
}


$clean_items = [];
foreach ($basket as $item) {
    $item_id = (int)($item['id'] ?? 0);
    $qty = (float)($item['qty'] ?? 0);
    if ($item_id < 1 || $qty <= 0) {
        counter_issue_json_error('Every issue item must have a valid quantity.');
    }
    $clean_items[$item_id] = ($clean_items[$item_id] ?? 0) + $qty;
}

try {
    $connection->begin_transaction();
    $lock_request = mysqli_query($connection, "SELECT req_id, counter, issue_status FROM kp_counter_req WHERE req_id='$request_id' LIMIT 1 FOR UPDATE");
    $locked = $lock_request ? mysqli_fetch_assoc($lock_request) : null;
    if (!$locked || in_array((int)$locked['issue_status'], [1, 4], true)) {
        throw new RuntimeException('This counter issue has already been completed.');
    }

    $shift = mysqli_real_escape_string($connection, (string)($_POST['shift'] ?? date('Y-m-d')));
    foreach ($clean_items as $item_id => $qty) {
        $item_q = mysqli_query($connection, "SELECT name, store_qty, dpt FROM kp_items WHERE item_id='$item_id' LIMIT 1 FOR UPDATE");
        $item = $item_q ? mysqli_fetch_assoc($item_q) : null;
        if (!$item) {
            throw new RuntimeException('One of the selected items no longer exists.');
        }
        $store_qty = (float)$item['store_qty'];
        if ($qty > $store_qty) {
            throw new RuntimeException($item['name'] . ' has only ' . $store_qty . ' units available. Adjust stock first.');
        }

        $safe_qty = (float)$qty;
        $updated = mysqli_query($connection, "UPDATE kp_items SET store_qty=store_qty-'$safe_qty' WHERE item_id='$item_id' AND store_qty >= '$safe_qty'");
        if (!$updated || mysqli_affected_rows($connection) !== 1) {
            throw new RuntimeException('Store stock changed before this issue was completed for ' . $item['name'] . '. Please reload and try again.');
        }
        if (!mysqli_query($connection, "INSERT INTO kp_counter_items (counter_id,item_id,qty,is_active) VALUES ('$counter_id','$item_id','$safe_qty',1) ON DUPLICATE KEY UPDATE qty=qty+VALUES(qty),is_active=1")) {
            throw new RuntimeException('Could not update destination counter inventory.');
        }
        if (!mysqli_query($connection, "INSERT INTO kp_store_stock_track(item_id,req_id,user_id,qty,status,shift) VALUES('$item_id','$request_id','$user_id','$safe_qty','OUT',CURRENT_DATE)")) {
            throw new RuntimeException('Could not record the store stock movement.');
        }
        $item_department = (int)($item['dpt'] ?? get_column('kp_items', 'dpt', "item_id='$item_id'"));
        if (!mysqli_query($connection, "INSERT INTO kp_stock_track(item_id,qty,user_id,req_id,`trans`,trans_id,status,stock_counter,shift,dpt) VALUES('$item_id','$safe_qty','$user_id','$request_id','REQ','$request_id','IN','$counter_id',CURRENT_DATE,'$item_department')")) {
            throw new RuntimeException('Could not record the counter stock receipt.');
        }

        $existing_q = mysqli_query($connection, "SELECT id FROM kp_counter_req_items WHERE req_id='$request_id' AND item_id='$item_id' LIMIT 1");
        if ($existing_q && mysqli_num_rows($existing_q) > 0) {
            if (!mysqli_query($connection, "UPDATE kp_counter_req_items SET issue_qty='$safe_qty',rec_qty='$safe_qty',issue_user='$user_id',issue_shift=CURRENT_DATE,issue_status=1,rec_status=1 WHERE req_id='$request_id' AND item_id='$item_id'")) {
                throw new RuntimeException('Could not update the requisition item status.');
            }
        } else {
            $counter_value = (string)$counter_id;
            mysqli_query($connection, "INSERT INTO kp_counter_req_items(req_id,req_user,item_id,req_qty,issue_qty,rec_qty,req_shift,counter,source_counter,issue_user,issue_shift,issue_status,rec_status) VALUES('$request_id','$user_id','$item_id','$safe_qty','$safe_qty','$safe_qty','$shift','$counter_value','STORE','$user_id',CURRENT_DATE,1,1)");
        }
        if (mysqli_errno($connection)) {
            throw new RuntimeException('Could not save the request item: ' . mysqli_error($connection));
        }
    }

    // Issuing stock is not the same as receiving it. Keep the request waiting
    // for the counter to receive it, and record the issuing user only.
    $header_update = "UPDATE kp_counter_req SET
        issue_status=3,
        rec_status=1,
        issue_user='$user_id',
        rec_user='$user_id',
        issue_shift=CURRENT_DATE,
        rec_shift=CURRENT_DATE,
        issue_created_at=CURRENT_TIMESTAMP,
        rec_created_at=CURRENT_TIMESTAMP,
        print_cntr_id='$counter_id',
        issued_by='$user_id',
        received_by='$user_id'
        WHERE req_id='$request_id'";
    if (!mysqli_query($connection, $header_update)) {
        throw new RuntimeException('Could not complete the counter issue.');
    }

    if (function_exists('logs')) {
        logs('ISSUED|-|-|REQUISITION NO|' . $request_id . '| FOR COUNTER|' . $counter_id, '../logs');
    }

    if (function_exists('ensure_chaba_print_system')) {
        ensure_chaba_print_system($connection);
    }
    $queue_id = function_exists('kp_print_enqueue') ? kp_print_enqueue($connection, [
        'sale_id' => 0,
        'ref_id' => $request_id,
        'print_type' => 'gwitia',
        'label' => 'COUNTER ISSUE',
        'cntr_id' => $counter_id,
        'route_path' => 'render_req.php',
        'route_query' => 'id=' . $request_id . '&user_id=' . $user_id . '&cntr_id=' . $counter_id,
        'requested_by' => $user_id,
    ]) : 0;
    if ($queue_id < 1) {
        throw new RuntimeException('Stock was not committed because the issue print could not be queued.');
    }

    $connection->commit();
    echo json_encode(['ok' => true, 'message' => 'Stock issued, received by the counter and queued for printing.', 'queue_id' => $queue_id, 'redirect' => 'counter_req.php?msg=1']);
} catch (Throwable $error) {
    $connection->rollback();
    counter_issue_json_error($error->getMessage(), 500);
}
