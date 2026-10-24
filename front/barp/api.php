<?php
session_start();
header('Content-Type: application/json; charset=utf-8');

if (empty($_SESSION['user_id'])) {
    http_response_code(401);
    echo json_encode(['success' => false, 'message' => 'Session expired.']);
    exit;
}

require_once '../../inc/connection.php';
require_once '../../inc/gen_funcs.php';

if (!function_exists('kp_print_enqueue')) {
    function kp_print_enqueue($connection, array $data) {
        get_print_mode();
        $columns = [
            'ref_id' => "INT NOT NULL DEFAULT 0",
            'label' => "VARCHAR(120) NOT NULL DEFAULT ''",
            'route_path' => "VARCHAR(255) NOT NULL DEFAULT ''",
            'route_query' => 'MEDIUMTEXT NULL',
            'requested_by' => 'INT NOT NULL DEFAULT 0',
            'reprint_of' => 'INT NOT NULL DEFAULT 0',
            'print_attempts' => 'TINYINT UNSIGNED NOT NULL DEFAULT 0',
            'last_error' => "VARCHAR(255) NOT NULL DEFAULT ''",
            'printed_at' => 'DATETIME NULL',
            'shift' => "VARCHAR(60) NOT NULL DEFAULT ''",
            'payload_json' => 'LONGTEXT NULL',
            'items_json' => 'LONGTEXT NULL',
        ];
        foreach ($columns as $column => $definition) {
            $check = mysqli_query($connection, "SHOW COLUMNS FROM kp_print_queue LIKE '{$column}'");
            if ($check && mysqli_num_rows($check) === 0) {
                mysqli_query($connection, "ALTER TABLE kp_print_queue ADD COLUMN `{$column}` {$definition}");
            }
        }
        $sale_id = (int)($data['sale_id'] ?? 0);
        $ref_id = (int)($data['ref_id'] ?? $sale_id);
        $type = mysqli_real_escape_string($connection, (string)($data['print_type'] ?? 'bill'));
        $label = mysqli_real_escape_string($connection, (string)($data['label'] ?? ''));
        $counter = (int)($data['cntr_id'] ?? 0);
        $path = mysqli_real_escape_string($connection, (string)($data['route_path'] ?? ''));
        $query = mysqli_real_escape_string($connection, (string)($data['route_query'] ?? ''));
        $requested = (int)($data['requested_by'] ?? 0);
        $sql = "INSERT INTO kp_print_queue
            (sale_id,ref_id,print_type,label,cntr_id,route_path,route_query,requested_by,reprint_of,status,print_attempts,last_error)
            VALUES ({$sale_id},{$ref_id},'{$type}','{$label}',{$counter},'{$path}','{$query}',{$requested},0,0,0,'')";
        return mysqli_query($connection, $sql) ? (int)mysqli_insert_id($connection) : 0;
    }
}

$shift = '';
$shift_query = mysqli_query($connection, "SELECT shift FROM kp_shift LIMIT 1");
if ($shift_query && ($shift_row = mysqli_fetch_assoc($shift_query))) {
    $shift = (string)($shift_row['shift'] ?? '');
}
$safe_shift = mysqli_real_escape_string($connection, $shift);
$bar_counter = (int)user_counter((int)($_SESSION['user_id'] ?? 0));

$action = trim((string)($_REQUEST['action'] ?? ''));

function barp_summary_print_blockers($user_id, $shift, $counter)
{
    global $connection;
    $user_id = (int)$user_id;
    $safe_shift = mysqli_real_escape_string($connection, (string)$shift);
    $open_orders = 0;
    $open_table = mysqli_query($connection, "SHOW TABLES LIKE 'kp_sales_open'");
    if ($open_table && mysqli_num_rows($open_table) > 0) {
        $open_query = mysqli_query($connection, "SELECT COUNT(*) AS total_count FROM kp_sales_open WHERE user_id='$user_id' AND status=1");
        $open_orders = (int)(($open_query ? mysqli_fetch_assoc($open_query) : [])['total_count'] ?? 0);
    }

    $pending_returns = 0;
    if ($safe_shift !== '' && (int)$counter > 0) {
        $return_query = mysqli_query($connection, "SELECT COUNT(*) AS total_count FROM kp_ret_items
            WHERE user_id='$user_id' AND shift='$safe_shift' AND stock_counter='" . (int)$counter . "'
            AND (confirm_status=0 OR confirm_status=2 OR confirm_status IS NULL)");
        $pending_returns = (int)(($return_query ? mysqli_fetch_assoc($return_query) : [])['total_count'] ?? 0);
    }

    $reasons = [];
    if ($open_orders > 0) $reasons[] = $open_orders . ' open order' . ($open_orders === 1 ? '' : 's');
    if ($pending_returns > 0) $reasons[] = $pending_returns . ' pending return' . ($pending_returns === 1 ? '' : 's');
    return ['blocked' => !empty($reasons), 'open_orders' => $open_orders, 'pending_returns' => $pending_returns, 'reasons' => $reasons];
}

if ($action === 'summary_print_check') {
    $user_id = (int)($_GET['user_id'] ?? 0);
    if ($user_id < 1) {
        http_response_code(400);
        echo json_encode(['success' => false, 'message' => 'Invalid waiter.']);
        exit;
    }
    $blockers = barp_summary_print_blockers($user_id, $shift, $bar_counter);
    echo json_encode(['success' => true, 'data' => $blockers]);
    exit;
}

if ($_SERVER['REQUEST_METHOD'] === 'POST' && $action === 'queue_staff_summary') {
    $user_id = (int)($_POST['user_id'] ?? 0);
    $counter_id = (int)($_POST['counter_id'] ?? 0);
    if ($user_id < 1 || $counter_id < 1) {
        http_response_code(400);
        echo json_encode(['success' => false, 'message' => 'Waiter and printer are required.']);
        exit;
    }

    $blockers = barp_summary_print_blockers($user_id, $shift, $bar_counter);
    if ($blockers['blocked']) {
        http_response_code(409);
        echo json_encode(['success' => false, 'message' => 'Resolve ' . implode(' and ', $blockers['reasons']) . ' before printing the summary.', 'data' => $blockers]);
        exit;
    }

    $counter_check = mysqli_query($connection, "SELECT cntr_id FROM kp_counters WHERE cntr_id='$counter_id' AND del_status=2 LIMIT 1");
    if (!$counter_check || mysqli_num_rows($counter_check) < 1) {
        http_response_code(404);
        echo json_encode(['success' => false, 'message' => 'Printer not found.']);
        exit;
    }

    get_print_mode();
    $queue_id = kp_print_enqueue($connection, [
        'sale_id' => 0,
        'ref_id' => $user_id,
        'print_type' => 'staff_sum',
        'label' => 'STAFF SUMMARY',
        'cntr_id' => $counter_id,
        'route_path' => 'render_staff_summary.php',
        'route_query' => 'id=' . $user_id . '&user_id=' . $user_id . ($safe_shift !== '' ? '&shift=' . rawurlencode($shift) : ''),
        'requested_by' => (int)$_SESSION['user_id'],
    ]);

    echo json_encode(['success' => $queue_id > 0, 'message' => $queue_id > 0 ? 'Summary queued for printing.' : 'Could not queue summary.', 'data' => ['queue_id' => $queue_id]]);
    exit;
}

if ($_SERVER['REQUEST_METHOD'] === 'POST' && $action === 'queue_bill_reprint') {
    $sale_id = (int)($_POST['sale_id'] ?? 0);
    $counter_id = (int)($_POST['counter_id'] ?? 0);
    if ($sale_id < 1 || $counter_id < 1) {
        http_response_code(400);
        echo json_encode(['success' => false, 'message' => 'Bill and printer are required.']);
        exit;
    }
    $counter_check = mysqli_query($connection, "SELECT cntr_id FROM kp_counters WHERE cntr_id='$counter_id' AND del_status=2 LIMIT 1");
    if (!$counter_check || mysqli_num_rows($counter_check) < 1) {
        http_response_code(404);
        echo json_encode(['success' => false, 'message' => 'Printer not found.']);
        exit;
    }
    get_print_mode();
    $queue_id = kp_print_enqueue($connection, [
        'sale_id' => $sale_id,
        'ref_id' => $sale_id,
        'print_type' => 'bill',
        'label' => 'BILL REPRINT',
        'cntr_id' => $counter_id,
        'route_path' => '',
        'route_query' => '',
        'requested_by' => (int)$_SESSION['user_id'],
        'reprint_of' => 0,
    ]);
    echo json_encode(['success' => $queue_id > 0, 'message' => $queue_id > 0 ? 'Bill queued for reprint.' : 'Could not queue bill.', 'data' => ['queue_id' => $queue_id]]);
    exit;
}

if ($_SERVER['REQUEST_METHOD'] === 'POST' && $action === 'cancel_pending_change') {
    $batch_id = (int)($_POST['change_batch_id'] ?? 0);
    if ($batch_id < 1) {
        echo json_encode(['success' => false, 'message' => 'Pending change not found.']);
        exit;
    }
    require_once __DIR__ . '/../kwedia/api/bill_repository.php';
    $pending_query = mysqli_query($connection, "SELECT change_sale_id FROM kp_sale_changes WHERE change_batch_id='$batch_id' AND status=2 LIMIT 1");
    $pending_row = $pending_query ? mysqli_fetch_assoc($pending_query) : null;
    if (!$pending_row) {
        echo json_encode(['success' => false, 'message' => 'This change is no longer pending.']);
        exit;
    }
    $change_sale_id = (int)$pending_row['change_sale_id'];
    mysqli_begin_transaction($connection);
    try {
        if ($change_sale_id > 0) {
            $items_query = mysqli_query($connection, "SELECT item_id, qty, stock_counter FROM kp_sale_items WHERE sale_id='$change_sale_id'");
            if ($items_query && function_exists('repo_return_item_stock_to_counter')) {
                while ($item = mysqli_fetch_assoc($items_query)) {
                    $returned = repo_return_item_stock_to_counter((int)$item['item_id'], (int)$item['stock_counter'], (float)$item['qty']);
                    if (empty($returned['ok'])) {
                        throw new RuntimeException((string)($returned['message'] ?? 'Could not restore replacement stock.'));
                    }
                }
            }
            mysqli_query($connection, "DELETE FROM kp_sale_items WHERE sale_id='$change_sale_id'");
            mysqli_query($connection, "DELETE FROM kp_sale_print WHERE sale_id='$change_sale_id'");
            mysqli_query($connection, "DELETE FROM kp_stock_track WHERE sale_id='$change_sale_id'");
            mysqli_query($connection, "DELETE FROM kp_sale_status WHERE sale_id='$change_sale_id'");
            mysqli_query($connection, "DELETE FROM kp_sales WHERE sale_id='$change_sale_id'");
        }
        mysqli_query($connection, "DELETE FROM kp_sale_items_changes WHERE change_batch_id='$batch_id'");
        if (!mysqli_query($connection, "DELETE FROM kp_sale_changes WHERE change_batch_id='$batch_id' AND status=2")) {
            throw new RuntimeException('Could not cancel the pending change.');
        }
        mysqli_commit($connection);
        echo json_encode(['success' => true, 'message' => 'Pending change cancelled.']);
    } catch (Throwable $error) {
        mysqli_rollback($connection);
        echo json_encode(['success' => false, 'message' => $error->getMessage()]);
    }
    exit;
}

if ($action === 'print_counters') {
    $counters = [];
    $counter_query = mysqli_query($connection, "SELECT cntr_id, name, dpt FROM kp_counters WHERE del_status=2 ORDER BY dpt ASC, cntr_id ASC");
    if ($counter_query) {
        while ($counter = mysqli_fetch_assoc($counter_query)) {
            $counters[] = ['id' => (int)$counter['cntr_id'], 'name' => (string)$counter['name'], 'department' => (string)$counter['dpt']];
        }
    }
    echo json_encode(['success' => true, 'message' => 'Printers loaded.', 'data' => ['counters' => $counters]]);
    exit;
}

if ($action === 'notifications') {
    $returns = [];
    $requests = [];
    if ($safe_shift !== '' && $bar_counter > 0) {
        $return_query = mysqli_query($connection, "SELECT r.id, r.sale_id, r.item_id, r.qty, r.amount, r.user_id, r.confirm_status, r.ret_status, u.username AS waiter
            FROM kp_ret_items r LEFT JOIN kp_users u ON u.user_id=r.user_id
            WHERE r.shift='$safe_shift' AND r.stock_counter='$bar_counter'
              AND r.ret_status=3 AND (r.confirm_status=0 OR r.confirm_status=2 OR r.confirm_status IS NULL)
            ORDER BY r.id DESC LIMIT 20");
        if ($return_query) while ($row = mysqli_fetch_assoc($return_query)) {
            $returns[] = ['id' => (int)$row['id'], 'sale_id' => (int)$row['sale_id'], 'item_id' => (int)$row['item_id'], 'item' => (string)get_item((int)$row['item_id']), 'qty' => (float)$row['qty'], 'amount' => (float)$row['amount'], 'waiter' => (string)($row['waiter'] ?? 'Unknown')];
        }
        $request_query = mysqli_query($connection, "SELECT r.req_id, r.counter, r.req_user, u.username AS requester,
            (SELECT COUNT(*) FROM kp_counter_req_items ri WHERE ri.req_id=r.req_id) AS items,
            (SELECT COALESCE(SUM(CASE WHEN ri.issue_qty>0 THEN ri.issue_qty ELSE ri.req_qty END),0) FROM kp_counter_req_items ri WHERE ri.req_id=r.req_id) AS qty
            FROM kp_counter_req r LEFT JOIN kp_users u ON u.user_id=r.req_user
            WHERE r.req_shift='$safe_shift' AND r.counter='$bar_counter' AND r.issue_status=1 AND r.rec_status=2
            ORDER BY r.req_id DESC LIMIT 20");
        if ($request_query) while ($row = mysqli_fetch_assoc($request_query)) {
            $requests[] = ['id' => (int)$row['req_id'], 'requester' => (string)($row['requester'] ?? 'Unknown'), 'items' => (int)$row['items'], 'qty' => (float)$row['qty'], 'counter' => (string)get_counter((int)$row['counter'])];
        }
    }
    echo json_encode(['success' => true, 'data' => ['returns' => $returns, 'requests' => $requests, 'counter' => $bar_counter]]);
    exit;
}

if ($action === 'stock_alerts') {
    $out = [];
    $reorder = [];
    $all = [];
    if ($bar_counter > 0) {
        $qty_col = 'cntr' . $bar_counter . '_qty';
        $reorder_col = 'cntr' . $bar_counter . '_reorder';
        $stock_query = mysqli_query($connection, "SELECT item_id,name,stock,`$qty_col` AS counter_qty,`$reorder_col` AS reorder_qty FROM kp_items WHERE del_status=2 ORDER BY name ASC");
        if ($stock_query) while ($row = mysqli_fetch_assoc($stock_query)) {
            $kind = (int)($row['stock'] ?? 0);
            $qty = $kind === 2 ? 100 : (float)($row['counter_qty'] ?? 0);
            $level = $kind === 2 ? 50 : (float)($row['reorder_qty'] ?? 0);
            $item = ['item_id'=>(int)$row['item_id'], 'name'=>(string)$row['name'], 'current'=>(float)$qty, 'counter_stock'=>(float)($row['counter_qty'] ?? 0), 'reorder_level'=>(float)$level];
            $all[] = $item;
            if ($qty <= 0) $out[] = $item;
            elseif ($level > 0 && $qty <= $level) $reorder[] = $item;
        }
    }
    echo json_encode(['success'=>true, 'data'=>['out_of_stock'=>$out, 'below_reorder'=>$reorder, 'all_items'=>$all, 'counter'=>$bar_counter]]);
    exit;
}

if ($_SERVER['REQUEST_METHOD'] === 'POST' && $action === 'add_counter_stock') {
    $item_id = (int)($_POST['item_id'] ?? 0);
    $add_qty_raw = trim((string)($_POST['qty'] ?? ''));
    if ($bar_counter < 1 || $item_id < 1 || $add_qty_raw === '' || !is_numeric($add_qty_raw) || (float)$add_qty_raw <= 0) {
        http_response_code(422);
        echo json_encode(['success'=>false, 'message'=>'A positive stock quantity is required.']);
        exit;
    }
    $add_qty = (float)$add_qty_raw;
    $qty_col = 'cntr' . $bar_counter . '_qty';
    $item_query = mysqli_query($connection, "SELECT name,dpt,`$qty_col` AS current_qty FROM kp_items WHERE item_id='$item_id' AND del_status=2 LIMIT 1");
    $item = $item_query ? mysqli_fetch_assoc($item_query) : null;
    if (!$item) {
        http_response_code(404);
        echo json_encode(['success'=>false, 'message'=>'Stock item was not found.']);
        exit;
    }
    $old_qty = (float)$item['current_qty'];
    $user_id = (int)($_SESSION['user_id'] ?? 0);
    $shift_sql = mysqli_real_escape_string($connection, $shift);
    $dpt_sql = mysqli_real_escape_string($connection, (string)$item['dpt']);
    mysqli_begin_transaction($connection);
    try {
        $new_qty = $old_qty + $add_qty;
        if (!mysqli_query($connection, "UPDATE kp_items SET `$qty_col`='{$new_qty}' WHERE item_id='{$item_id}'")) throw new RuntimeException('Could not add counter stock.');
        if (!mysqli_query($connection, "INSERT INTO kp_stock_track(item_id,qty,user_id,`trans`,trans_id,status,stock_counter,shift,dpt) VALUES('{$item_id}','{$add_qty}','{$user_id}','COUNTER_ADD','0','counter_add','{$bar_counter}','{$shift_sql}','{$dpt_sql}')")) throw new RuntimeException('Could not track the counter stock addition.');
        mysqli_commit($connection);
    } catch (Throwable $error) {
        mysqli_rollback($connection);
        http_response_code(500);
        echo json_encode(['success'=>false, 'message'=>$error->getMessage()]);
        exit;
    }
    if (function_exists('logs')) logs('COUNTER STOCK ADDED|' . $item_id . '|' . $item['name'] . '|OLD|' . $old_qty . '|ADDED|' . $add_qty . '|NEW|' . $new_qty, '../logs');
    echo json_encode(['success'=>true, 'message'=>'Stock added to the counter and tracked.', 'data'=>['item_id'=>$item_id, 'name'=>$item['name'], 'old_qty'=>$old_qty, 'new_qty'=>$new_qty, 'added_qty'=>$add_qty]]);
    exit;
}

if ($action === 'requisitions') {
    $requests = [];
    if ($safe_shift !== '' && $bar_counter > 0) {
        $request_query = mysqli_query($connection, "SELECT r.req_id, r.counter, r.req_user, u.username AS requester,
            COUNT(ri.id) AS items, COALESCE(SUM(ri.issue_qty), SUM(ri.req_qty), 0) AS qty
            FROM kp_counter_req r LEFT JOIN kp_counter_req_items ri ON ri.req_id=r.req_id
            LEFT JOIN kp_users u ON u.user_id=r.req_user
            WHERE r.req_shift='$safe_shift' AND r.counter='$bar_counter' AND r.issue_status=1 AND r.rec_status=2
            GROUP BY r.req_id ORDER BY r.req_id DESC");
        if ($request_query) while ($row = mysqli_fetch_assoc($request_query)) $requests[] = ['id'=>(int)$row['req_id'], 'requester'=>(string)($row['requester'] ?? 'Unknown'), 'items'=>(int)$row['items'], 'qty'=>(float)$row['qty'], 'counter'=>(string)get_counter((int)$row['counter'])];
    }
    echo json_encode(['success'=>true, 'data'=>['requests'=>$requests]]);
    exit;
}

if ($action === 'counter_stock') {
    $requests = [];
    $items = [];
    $issued_count = 0;
    if ($safe_shift !== '' && $bar_counter > 0) {
        $issued_count = (int)column_count('kp_counter_req', 'req_id', "req_shift='$safe_shift' AND counter='$bar_counter' AND issue_status=1");
        $stock_qty_col = 'cntr' . $bar_counter . '_qty';
        $issued_query = mysqli_query($connection, "SELECT ri.item_id, i.name,
                COALESCE(SUM(ri.issue_qty), 0) AS issued_qty,
                COALESCE(i.`$stock_qty_col`, 0) AS current_qty
            FROM kp_counter_req_items ri
            INNER JOIN kp_counter_req r ON r.req_id=ri.req_id
            LEFT JOIN kp_items i ON i.item_id=ri.item_id
            WHERE r.req_shift='$safe_shift' AND r.counter='$bar_counter'
                AND r.issue_status=1 AND ri.issue_qty>0
            GROUP BY ri.item_id, i.name, i.`$stock_qty_col`
            ORDER BY i.name ASC");
        if ($issued_query) while ($row = mysqli_fetch_assoc($issued_query)) {
            $items[] = [
                'item_id' => (int)$row['item_id'],
                'name' => (string)($row['name'] ?? get_item((int)$row['item_id'])),
                'issued' => (float)$row['issued_qty'],
                'current' => (float)$row['current_qty'],
            ];
        }
        $request_count = (int)column_count('kp_counter_req', 'req_id', "req_shift='$safe_shift' AND counter='$bar_counter'");
    } else {
        $request_count = 0;
    }
    echo json_encode(['success'=>true, 'data'=>[
        'requests'=>$request_count,
        'issued'=>$issued_count,
        'items'=>$items,
        'counter'=>$bar_counter,
        'shift'=>$shift,
    ]]);
    exit;
}

if ($action === 'requisition_items') {
    $req_id = (int)($_GET['req_id'] ?? 0);
    $items = [];
    if ($req_id > 0) {
        $item_query = mysqli_query($connection, "SELECT item_id, req_qty, issue_qty, rec_qty FROM kp_counter_req_items WHERE req_id='$req_id' ORDER BY id ASC");
        if ($item_query) while ($row = mysqli_fetch_assoc($item_query)) $items[] = ['item_id'=>(int)$row['item_id'], 'name'=>(string)get_item((int)$row['item_id']), 'requested'=>(float)$row['req_qty'], 'issued'=>(float)$row['issue_qty'], 'received'=>(float)$row['rec_qty']];
    }
    echo json_encode(['success'=>true, 'data'=>['items'=>$items]]);
    exit;
}

if ($action === 'waiter_summary') {
    $user_id = (int)($_GET['user_id'] ?? 0);
    if ($user_id < 1) {
        http_response_code(400);
        echo json_encode(['success' => false, 'message' => 'Invalid waiter.']);
        exit;
    }

    $safe_shift = mysqli_real_escape_string($connection, $shift);
    $user_query = mysqli_query($connection, "SELECT user_id, username FROM kp_users WHERE user_id='$user_id' LIMIT 1");
    $user = $user_query ? mysqli_fetch_assoc($user_query) : null;
    if (!$user) {
        http_response_code(404);
        echo json_encode(['success' => false, 'message' => 'Waiter not found.']);
        exit;
    }

    $sales_total = 0.0;
    $sales_count = 0;
    $returns_count = 0;
    $returns_total = 0.0;
    $items_count = 0.0;
    $month_sales = 0.0;
    $today_sales = 0.0;
    $total_returns = 0.0;
    $all_returns_total = 0.0;
    $changes_count = 0;
    $voids_count = 0;
    $sales_rows = [];
    $item_summary = [];
    $returned_items = [];
    $counter_totals = [];
    if ($safe_shift !== '') {
        $month_start = date('Y-m-01 00:00:00');
        $month_end = date('Y-m-t 23:59:59');
        $today = date('Y-m-d');
        $month_sales = (float)column_total('kp_sales', 'amount', "user_id='$user_id' AND datime>='$month_start' AND datime<='$month_end'");
        $today_sales = (float)column_total('kp_sales', 'amount', "user_id='$user_id' AND DATE(datime)='$today'");
        $all_returns_total = (float)column_total('kp_ret_items', 'amount', "user_id='$user_id'");
        $change_count_query = mysqli_query($connection, "SELECT COUNT(DISTINCT c.change_batch_id) AS total_count FROM kp_sale_changes c LEFT JOIN kp_sales s ON s.sale_id=c.original_sale_id WHERE c.user_id='$user_id' AND s.shift='$safe_shift'");
        $changes_count = (int)(($change_count_query ? mysqli_fetch_assoc($change_count_query) : [])['total_count'] ?? 0);
        $voids_count = (int)column_count('kp_del_orders', 'sale_id', "user_id='$user_id' AND shift='$safe_shift'");
        // Match the waiter dashboard: waiter performance is based on all of
        // the waiter's finalized sales in the active shift, not the sale
        // header counter. A bill may contain items from several counters.
        $sales_query = mysqli_query($connection, "SELECT COUNT(*) AS total_count, COALESCE(SUM(amount), 0) AS total_amount FROM kp_sales WHERE user_id='$user_id' AND shift='$safe_shift'");
        $sales = $sales_query ? mysqli_fetch_assoc($sales_query) : [];
        $sales_count = (int)($sales['total_count'] ?? 0);
        $sales_total = (float)($sales['total_amount'] ?? 0);

        $sales_list_query = mysqli_query($connection, "SELECT s.sale_id, s.amount, s.pay_status,
            COALESCE(SUM(si.qty), 0) AS items_count
            FROM kp_sales s
            LEFT JOIN kp_sale_items si ON si.sale_id=s.sale_id AND si.shift='$safe_shift' AND si.stock_counter='$bar_counter'
            WHERE s.user_id='$user_id' AND s.shift='$safe_shift'
            GROUP BY s.sale_id, s.amount, s.pay_status
            ORDER BY s.sale_id DESC");
        if ($sales_list_query) while ($row = mysqli_fetch_assoc($sales_list_query)) {
            $sales_rows[] = [
                'sale_id' => (int)$row['sale_id'],
                'amount' => (float)$row['amount'],
                'items_count' => (float)$row['items_count'],
                'status' => (int)$row['pay_status'] === 1 ? 'PAID' : ((int)$row['pay_status'] === 3 ? 'CREDIT' : 'PENDING'),
            ];
        }

        $item_summary_query = mysqli_query($connection, "SELECT item_id, COALESCE(SUM(qty), 0) AS qty, COALESCE(SUM(total), 0) AS total
            FROM kp_sale_items
            WHERE user_id='$user_id' AND shift='$safe_shift'
            GROUP BY item_id ORDER BY total DESC");
        if ($item_summary_query) while ($row = mysqli_fetch_assoc($item_summary_query)) {
            $item_summary[] = ['item_id' => (int)$row['item_id'], 'name' => (string)get_item((int)$row['item_id']), 'qty' => (float)$row['qty'], 'total' => (float)$row['total']];
        }

        $returns_query = mysqli_query($connection, "SELECT COUNT(*) AS total_count, COALESCE(SUM(amount), 0) AS total_amount FROM kp_ret_items WHERE user_id='$user_id' AND shift='$safe_shift' AND stock_counter='$bar_counter' AND confirm_status=1");
        $returns = $returns_query ? mysqli_fetch_assoc($returns_query) : [];
        $returns_count = (int)($returns['total_count'] ?? 0);
        $returns_total = (float)($returns['total_amount'] ?? 0);
        $all_returns_query = mysqli_query($connection, "SELECT item_id, qty, amount, confirm_status, confirm_by FROM kp_ret_items WHERE user_id='$user_id' AND shift='$safe_shift' AND stock_counter='$bar_counter' ORDER BY id DESC");
        if ($all_returns_query) while ($row = mysqli_fetch_assoc($all_returns_query)) {
            $status = (int)($row['confirm_status'] ?? 2);
            $returned_items[] = ['item_id'=>(int)$row['item_id'], 'name'=>(string)get_item((int)$row['item_id']), 'qty'=>(float)$row['qty'], 'amount'=>(float)$row['amount'], 'status'=>$status === 1 ? 'APPROVED' : ($status === 3 ? 'REJECTED' : 'PENDING'), 'confirmed_by'=>(int)($row['confirm_by'] ?? 0) > 0 ? (string)get_user((int)$row['confirm_by']) : '—'];
        }
        $total_returns = (float)column_total('kp_ret_items', 'amount', "user_id='$user_id' AND shift='$safe_shift' AND stock_counter='$bar_counter'");

        $items_query = mysqli_query($connection, "SELECT COALESCE(SUM(qty), 0) AS total_qty FROM kp_sale_items WHERE user_id='$user_id' AND shift='$safe_shift'");
        $items = $items_query ? mysqli_fetch_assoc($items_query) : [];
        $items_count = (float)($items['total_qty'] ?? 0);

        // Counter sales must come from finalized sale headers, not item-row
        // totals. A sale can contain multiple item rows and multiple counters.
        $counter_query = mysqli_query($connection, "SELECT s.counter AS counter_id,
            COALESCE(c.name, CONCAT('Counter ', s.counter)) AS counter_name,
            COUNT(s.sale_id) AS sales_count, COALESCE(SUM(s.amount), 0) AS sales_total,
            COALESCE((SELECT SUM(si.qty) FROM kp_sale_items si WHERE si.user_id='$user_id' AND si.shift='$safe_shift' AND si.sale_id IN (SELECT s2.sale_id FROM kp_sales s2 WHERE s2.user_id='$user_id' AND s2.shift='$safe_shift' AND s2.counter=s.counter)), 0) AS items_count
            FROM kp_sales s LEFT JOIN kp_counters c ON c.cntr_id=s.counter
            WHERE s.user_id='$user_id' AND s.shift='$safe_shift'
            GROUP BY s.counter, c.name ORDER BY counter_name ASC");
        if ($counter_query) while ($row = mysqli_fetch_assoc($counter_query)) {
            $counter_totals[] = ['counter_id' => (int)$row['counter_id'], 'counter_name' => (string)$row['counter_name'], 'sales_count' => (int)$row['sales_count'], 'sales_total' => (float)$row['sales_total'], 'items_count' => (float)$row['items_count']];
        }
    }

    $position = 0;
    if ($safe_shift !== '') {
        $ranking_query = mysqli_query(
            $connection,
            "SELECT u.user_id, COALESCE(SUM(s.amount), 0) AS sales_total
             FROM kp_users u
             LEFT JOIN kp_sales s ON s.user_id=u.user_id AND s.shift='$safe_shift'
             GROUP BY u.user_id
             ORDER BY sales_total DESC, u.user_id ASC"
        );
        $rank = 0;
        if ($ranking_query) {
            while ($ranking_row = mysqli_fetch_assoc($ranking_query)) {
                $rank++;
                if ((int)$ranking_row['user_id'] === $user_id) {
                    $position = $rank;
                    break;
                }
            }
        }
    }

    echo json_encode([
        'success' => true,
        'message' => 'Waiter summary loaded.',
        'data' => [
            'shift' => $shift,
            'waiter' => ['user_id' => $user_id, 'name' => (string)$user['username']],
            'sales_total' => $sales_total,
            'sales_count' => $sales_count,
            'returns_count' => $returns_count,
            'returns_total' => $returns_total,
            'items_count' => $items_count,
            'month_sales' => $month_sales,
            'today_sales' => $today_sales,
            'total_returns' => $total_returns,
            'all_returns_total' => $all_returns_total,
            'changes_count' => $changes_count,
            'voids_count' => $voids_count,
            'sales' => $sales_rows,
            'item_summary' => $item_summary,
            'returned_items' => $returned_items,
            'position' => $position,
            'counter_totals' => $counter_totals,
        ],
    ]);
    exit;
}

$waiters = [];
$sales_join = $safe_shift !== ''
    ? "LEFT JOIN (
          SELECT user_id, COUNT(*) AS order_count, COALESCE(SUM(amount), 0) AS sales_total
          FROM kp_sales
          WHERE shift='$safe_shift'
          GROUP BY user_id
       ) s ON s.user_id=u.user_id"
    : "LEFT JOIN (SELECT 0 AS user_id, 0 AS order_count, 0 AS sales_total) s ON s.user_id=u.user_id";

$query = mysqli_query(
    $connection,
    "SELECT u.user_id, u.username, COALESCE(s.order_count, 0) AS order_count,
            COALESCE(s.sales_total, 0) AS sales_total
     FROM kp_users u
     $sales_join
     WHERE COALESCE(s.order_count, 0) > 0
     ORDER BY u.username ASC"
);

if ($query) {
    while ($row = mysqli_fetch_assoc($query)) {
        $waiters[] = [
            'user_id' => (int)$row['user_id'],
            'name' => (string)$row['username'],
            'order_count' => (int)$row['order_count'],
            'sales_total' => (float)$row['sales_total'],
        ];
    }
}

$dashboard = [
    'sales_total' => 0,
    'orders_total' => 0,
    'waiters_total' => count($waiters),
    'returns_count' => 0,
    'returns_total' => 0,
    'returns_pending' => 0,
    'returns_pending_total' => 0,
    'returns_rejected' => 0,
    'returns_rejected_total' => 0,
    'returns_approved' => 0,
    'returns_approved_total' => 0,
    'items_total' => 0,
    'active_sales' => [],
    'sale_balances' => [],
    'counter_transactions' => [],
    'counter_sales' => [],
    'stock_summary' => ['total_items'=>0, 'out_of_stock'=>0, 'below_reorder'=>0, 'requisitioned'=>0, 'returns'=>0, 'returns_approved'=>0, 'returns_rejected'=>0, 'tots_made'=>0, 'tots_qty'=>0, 'transfers'=>0, 'transfers_in'=>0, 'transfers_out'=>0],
    'counter_opening' => '',
    'counter_open_hours' => 0,
    'balance_summary' => ['sales_total'=>0, 'items_total'=>0, 'difference'=>0, 'balanced'=>true],
    'requisition_summary' => ['requests'=>0, 'issued'=>0, 'received'=>0],
    'recent_receipts' => [],
    'hourly_sales' => [],
];
if ($safe_shift !== '') {
    $summary_query = mysqli_query($connection, "SELECT COUNT(*) AS orders_total, COALESCE(SUM(amount), 0) AS sales_total FROM kp_sales WHERE shift='$safe_shift' AND counter='$bar_counter'");
    $summary = $summary_query ? mysqli_fetch_assoc($summary_query) : [];
    $dashboard['orders_total'] = (int)($summary['orders_total'] ?? 0);
    $dashboard['sales_total'] = (float)($summary['sales_total'] ?? 0);

    $hourly_query = mysqli_query($connection, "SELECT HOUR(s.datime) AS sale_hour,
        COALESCE(SUM(si.total), 0) AS total
        FROM kp_sale_items si
        INNER JOIN kp_sales s ON s.sale_id=si.sale_id
        WHERE si.shift='$safe_shift' AND si.stock_counter='$bar_counter'
        GROUP BY HOUR(s.datime) ORDER BY sale_hour ASC");
    if ($hourly_query) while ($row = mysqli_fetch_assoc($hourly_query)) {
        $dashboard['hourly_sales'][] = [
            'hour' => (int)$row['sale_hour'],
            'total' => max(0, (float)$row['total']),
        ];
    }

    $returns_query = mysqli_query($connection, "SELECT COUNT(*) AS returns_count, COALESCE(SUM(amount), 0) AS returns_total FROM kp_ret_items WHERE shift='$safe_shift' AND stock_counter='$bar_counter'");
    $returns = $returns_query ? mysqli_fetch_assoc($returns_query) : [];
    $dashboard['returns_count'] = (int)($returns['returns_count'] ?? 0);
    $dashboard['returns_total'] = (float)($returns['returns_total'] ?? 0);
    $dashboard['items_total'] = (float)column_total('kp_sale_items', 'qty', "shift='$safe_shift' AND stock_counter='$bar_counter'");

    $pending_query = mysqli_query($connection, "SELECT COUNT(*) AS total_count, COALESCE(SUM(amount), 0) AS total_amount FROM kp_ret_items WHERE shift='$safe_shift' AND stock_counter='$bar_counter' AND ret_status=3");
    $pending = $pending_query ? mysqli_fetch_assoc($pending_query) : [];
    $dashboard['returns_pending'] = (int)($pending['total_count'] ?? 0);
    $dashboard['returns_pending_total'] = (float)($pending['total_amount'] ?? 0);
    $rejected_query = mysqli_query($connection, "SELECT COUNT(*) AS total_count, COALESCE(SUM(amount), 0) AS total_amount FROM kp_ret_items WHERE shift='$safe_shift' AND stock_counter='$bar_counter' AND confirm_status=3");
    $rejected = $rejected_query ? mysqli_fetch_assoc($rejected_query) : [];
    $dashboard['returns_rejected'] = (int)($rejected['total_count'] ?? 0);
    $dashboard['returns_rejected_total'] = (float)($rejected['total_amount'] ?? 0);
    $approved_query = mysqli_query($connection, "SELECT COUNT(*) AS total_count, COALESCE(SUM(amount), 0) AS total_amount FROM kp_ret_items WHERE shift='$safe_shift' AND stock_counter='$bar_counter' AND confirm_status=1");
    $approved = $approved_query ? mysqli_fetch_assoc($approved_query) : [];
    $dashboard['returns_approved'] = (int)($approved['total_count'] ?? 0);
    $dashboard['returns_approved_total'] = (float)($approved['total_amount'] ?? 0);

    $balance_summary_query = mysqli_query($connection, "SELECT COALESCE(SUM(s.amount), 0) AS sales_total, COALESCE(SUM(si.item_total), 0) AS items_total
        FROM kp_sales s LEFT JOIN (SELECT sale_id, SUM(total) AS item_total FROM kp_sale_items WHERE shift='$safe_shift' AND stock_counter='$bar_counter' GROUP BY sale_id) si ON si.sale_id=s.sale_id
        WHERE s.shift='$safe_shift' AND s.counter='$bar_counter'");
    $balance_summary = $balance_summary_query ? mysqli_fetch_assoc($balance_summary_query) : [];
    $balance_difference = (float)($balance_summary['sales_total'] ?? 0) - (float)($balance_summary['items_total'] ?? 0);
    $dashboard['balance_summary'] = ['sales_total'=>(float)($balance_summary['sales_total'] ?? 0), 'items_total'=>(float)($balance_summary['items_total'] ?? 0), 'difference'=>$balance_difference, 'balanced'=>abs($balance_difference) < 0.01];

    $dashboard['requisition_summary'] = [
        'requests'=>(int)column_count('kp_counter_req', 'req_id', "req_shift='$safe_shift' AND counter='$bar_counter'"),
        'issued'=>(int)column_count('kp_counter_req', 'req_id', "req_shift='$safe_shift' AND counter='$bar_counter' AND issue_status=1"),
        'received'=>(int)column_count('kp_counter_req', 'req_id', "req_shift='$safe_shift' AND counter='$bar_counter' AND rec_status=1"),
    ];

    $balance_query = mysqli_query($connection, "SELECT s.sale_id, s.amount AS sale_total, COALESCE(SUM(si.total), 0) AS item_total, COALESCE(SUM(si.qty), 0) AS item_qty
        FROM kp_sales s LEFT JOIN kp_sale_items si ON si.sale_id=s.sale_id AND si.shift='$safe_shift' AND si.stock_counter='$bar_counter'
        WHERE s.shift='$safe_shift' AND s.counter='$bar_counter' GROUP BY s.sale_id, s.amount ORDER BY s.sale_id DESC LIMIT 20");
    if ($balance_query) while ($row = mysqli_fetch_assoc($balance_query)) {
        $difference = (float)$row['sale_total'] - (float)$row['item_total'];
        $dashboard['sale_balances'][] = ['sale_id'=>(int)$row['sale_id'], 'sale_total'=>(float)$row['sale_total'], 'item_total'=>(float)$row['item_total'], 'item_qty'=>(float)$row['item_qty'], 'difference'=>$difference, 'balanced'=>abs($difference) < 0.01];
    }

    $active_query = mysqli_query($connection, "SELECT DISTINCT ss.sale_id, ss.user_id, u.username, '' AS opened_at
        FROM kp_sale_status ss LEFT JOIN kp_users u ON u.user_id=ss.user_id LEFT JOIN kp_thesion_front tf ON tf.user_id=ss.user_id
        LEFT JOIN kp_sales s ON s.sale_id=ss.sale_id
        WHERE ss.status=2 AND (tf.cntr_id='$bar_counter' OR s.counter='$bar_counter') ORDER BY ss.sale_id DESC");
    if ($active_query) while ($row = mysqli_fetch_assoc($active_query)) $dashboard['active_sales'][] = ['sale_id'=>(int)$row['sale_id'], 'user_id'=>(int)$row['user_id'], 'waiter'=>(string)($row['username'] ?? 'Unknown'), 'opened_at'=>(string)$row['opened_at']];

    $history_query = mysqli_query($connection, "SELECT opening FROM kp_counter_hist WHERE shift='$safe_shift' AND cntr_id='$bar_counter' ORDER BY opening DESC LIMIT 1");
    if ($history_query && ($history = mysqli_fetch_assoc($history_query))) {
        $opening = (string)$history['opening'];
        $dashboard['counter_opening'] = $opening;
        $dashboard['counter_open_hours'] = max(0, round((time() - strtotime($opening)) / 3600, 1));
    }
    $dashboard['counter_transactions'] = [
        ['label'=>'Sales', 'count'=>$dashboard['orders_total'], 'total'=>$dashboard['sales_total']],
        ['label'=>'Sale items', 'count'=>$dashboard['items_total'], 'total'=>(float)column_total('kp_sale_items', 'total', "shift='$safe_shift' AND stock_counter='$bar_counter'")],
        ['label'=>'Returns', 'count'=>$dashboard['returns_count'], 'total'=>$dashboard['returns_total']],
        ['label'=>'Pending returns', 'count'=>$dashboard['returns_pending'], 'total'=>$dashboard['returns_pending_total']],
        ['label'=>'Rejected returns', 'count'=>$dashboard['returns_rejected'], 'total'=>$dashboard['returns_rejected_total']],
        ['label'=>'Requests', 'count'=>(int)column_count('kp_counter_req', 'req_id', "req_shift='$safe_shift' AND counter='$bar_counter'"), 'total'=>0],
        ['label'=>'Transfers in', 'count'=>(int)column_count('kp_counter_trans', 'trans_id', "shift='$safe_shift' AND cntr_to='$bar_counter'"), 'total'=>0],
        ['label'=>'Transfers out', 'count'=>(int)column_count('kp_counter_trans', 'trans_id', "shift='$safe_shift' AND cntr_from='$bar_counter'"), 'total'=>0],
    ];
    $counter_sales_query = mysqli_query($connection, "SELECT s.counter AS counter_id, COALESCE(c.name, CONCAT('Counter ', s.counter)) AS counter_name, COUNT(s.sale_id) AS sale_count, COALESCE(SUM(s.amount), 0) AS total
        FROM kp_sales s LEFT JOIN kp_counters c ON c.cntr_id=s.counter
        WHERE s.shift='$safe_shift' GROUP BY s.counter ORDER BY total DESC");
    if ($counter_sales_query) while ($row = mysqli_fetch_assoc($counter_sales_query)) $dashboard['counter_sales'][] = ['counter_id'=>(int)$row['counter_id'], 'label'=>(string)$row['counter_name'], 'sale_count'=>(int)$row['sale_count'], 'total'=>(float)$row['total']];
    $stock_qty_col = 'cntr' . $bar_counter . '_qty';
    $stock_reorder_col = 'cntr' . $bar_counter . '_reorder';
    $stock_query = mysqli_query($connection, "SELECT stock, recipe, `$stock_qty_col` AS counter_qty, `$stock_reorder_col` AS reorder_qty FROM kp_items WHERE del_status=2");
    if ($stock_query) while ($stock = mysqli_fetch_assoc($stock_query)) {
        $dashboard['stock_summary']['total_items']++;
        $kind = (int)($stock['stock'] ?? 0);
        $qty = $kind === 2 ? 100 : (float)($stock['counter_qty'] ?? 0);
        $reorder = $kind === 2 ? 50 : (float)($stock['reorder_qty'] ?? 0);
        if ($qty <= 0) $dashboard['stock_summary']['out_of_stock']++;
        elseif ($reorder > 0 && $qty <= $reorder) $dashboard['stock_summary']['below_reorder']++;
    }
    $dashboard['stock_summary']['requisitioned'] = (int)column_count('kp_counter_req', 'req_id', "req_shift='$safe_shift' AND counter='$bar_counter' AND issue_status=1 AND rec_status=2");
    $dashboard['stock_summary']['returns'] = (int)column_count('kp_ret_items', 'id', "shift='$safe_shift' AND stock_counter='$bar_counter' AND ret_status=3 AND (confirm_status=0 OR confirm_status=2 OR confirm_status IS NULL)");
    $dashboard['stock_summary']['returns_approved'] = (int)column_count('kp_ret_items', 'id', "shift='$safe_shift' AND stock_counter='$bar_counter' AND confirm_status=1");
    $dashboard['stock_summary']['returns_rejected'] = (int)column_count('kp_ret_items', 'id', "shift='$safe_shift' AND stock_counter='$bar_counter' AND confirm_status=3");
    $dashboard['stock_summary']['tots_made'] = (int)column_count('kp_tot_items', 'id', "shift='$safe_shift' AND counter='$bar_counter'");
    $dashboard['stock_summary']['tots_qty'] = (float)column_total('kp_tot_items', 'tots', "shift='$safe_shift' AND counter='$bar_counter'");
    $dashboard['stock_summary']['transfers'] = (int)column_count('kp_counter_trans', 'trans_id', "shift='$safe_shift' AND (cntr_to='$bar_counter' OR cntr_from='$bar_counter')");
    $dashboard['stock_summary']['transfers_in'] = (int)column_count('kp_counter_trans', 'trans_id', "shift='$safe_shift' AND cntr_to='$bar_counter'");
    $dashboard['stock_summary']['transfers_out'] = (int)column_count('kp_counter_trans', 'trans_id', "shift='$safe_shift' AND cntr_from='$bar_counter'");

    $recent_query = mysqli_query($connection, "SELECT * FROM kp_sales WHERE shift='$safe_shift' AND counter='$bar_counter' ORDER BY sale_id DESC LIMIT 10");
    if ($recent_query) {
        while ($recent = mysqli_fetch_assoc($recent_query)) {
            $recent_user_id = (int)($recent['user_id'] ?? 0);
            $dashboard['recent_receipts'][] = [
                'sale_id' => (int)$recent['sale_id'],
                'user_id' => $recent_user_id,
                'waiter' => (string)get_column('kp_users', 'username', "user_id='$recent_user_id'"),
                'amount' => (float)$recent['amount'],
                'time' => (string)($recent['datime'] ?? ($recent['created_at'] ?? ($recent['time'] ?? ''))),
                'status' => (int)$recent['pay_status'] === 1 ? 'PAID' : 'PENDING',
            ];
        }
    }
}

 $pending_changes = [];
 $pending_query = mysqli_query($connection, "SELECT c.change_batch_id, c.original_sale_id, c.change_sale_id, c.user_id, u.username,
        ss.status AS sale_status,
        (SELECT COUNT(*) FROM kp_sale_items_changes ci WHERE ci.change_batch_id=c.change_batch_id) AS selected_count,
        (SELECT COALESCE(SUM(si.qty),0) FROM kp_sale_items si WHERE si.sale_id=c.change_sale_id) AS new_item_count
        FROM kp_sale_changes c
        LEFT JOIN kp_users u ON u.user_id=c.user_id
        LEFT JOIN kp_sale_status ss ON ss.sale_id=c.change_sale_id AND ss.user_id=c.user_id
        WHERE c.status=2 AND c.change_sale_id>0
        ORDER BY c.created_at ASC");
 if ($pending_query) while ($pending = mysqli_fetch_assoc($pending_query)) {
     $pending_changes[] = [
         'batch_id' => (int)$pending['change_batch_id'],
         'original_sale_id' => (int)$pending['original_sale_id'],
         'change_sale_id' => (int)$pending['change_sale_id'],
         'user_id' => (int)$pending['user_id'],
         'user' => (string)($pending['username'] ?? 'Unknown user'),
         'sale_status' => (int)($pending['sale_status'] ?? 0),
         'selected_count' => (int)($pending['selected_count'] ?? 0),
         'new_item_count' => (float)($pending['new_item_count'] ?? 0),
     ];
 }
echo json_encode([
    'success' => true,
    'message' => 'Waiters loaded.',
    'data' => ['shift' => $shift, 'waiters' => $waiters, 'dashboard' => $dashboard, 'pending_changes' => $pending_changes],
]);
