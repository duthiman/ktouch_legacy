<?php
session_start();
header('Content-Type: application/json; charset=utf-8');

if (empty($_SESSION['user_id'])) {
    http_response_code(401);
    echo json_encode(['success' => false, 'message' => 'Session expired.']);
    exit;
}

require_once '../../inc/connection.php';
require_once '../chaba/funcs.php';

$shift = '';
$shift_query = mysqli_query($connection, "SELECT shift FROM kp_shift LIMIT 1");
if ($shift_query && ($shift_row = mysqli_fetch_assoc($shift_query))) {
    $shift = (string)($shift_row['shift'] ?? '');
}
$safe_shift = mysqli_real_escape_string($connection, $shift);
$bar_counter = (int)user_counter((int)($_SESSION['user_id'] ?? 0));

$action = trim((string)($_REQUEST['action'] ?? ''));
if ($_SERVER['REQUEST_METHOD'] === 'POST' && $action === 'queue_staff_summary') {
    $user_id = (int)($_POST['user_id'] ?? 0);
    $counter_id = (int)($_POST['counter_id'] ?? 0);
    if ($user_id < 1 || $counter_id < 1) {
        http_response_code(400);
        echo json_encode(['success' => false, 'message' => 'Waiter and printer are required.']);
        exit;
    }

    $counter_check = mysqli_query($connection, "SELECT cntr_id FROM kp_counters WHERE cntr_id='$counter_id' AND del_status=2 LIMIT 1");
    if (!$counter_check || mysqli_num_rows($counter_check) < 1) {
        http_response_code(404);
        echo json_encode(['success' => false, 'message' => 'Printer not found.']);
        exit;
    }

    ensure_chaba_print_system($connection);
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
    ensure_chaba_print_system($connection);
    $queue_id = kp_print_enqueue($connection, [
        'sale_id' => $sale_id,
        'ref_id' => $sale_id,
        'print_type' => 'bill',
        'label' => 'BILL REPRINT',
        'cntr_id' => $counter_id,
        'route_path' => 'render_bill.php',
        'route_query' => 'sale_id=' . $sale_id,
        'requested_by' => (int)$_SESSION['user_id'],
        'reprint_of' => 0,
    ]);
    echo json_encode(['success' => $queue_id > 0, 'message' => $queue_id > 0 ? 'Bill queued for reprint.' : 'Could not queue bill.', 'data' => ['queue_id' => $queue_id]]);
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
        $return_query = mysqli_query($connection, "SELECT r.id, r.sale_id, r.amount, r.user_id, u.username AS waiter
            FROM kp_ret_items r LEFT JOIN kp_users u ON u.user_id=r.user_id
            WHERE r.shift='$safe_shift' AND r.stock_counter='$bar_counter' AND r.ret_status=3
            ORDER BY r.id DESC LIMIT 20");
        if ($return_query) while ($row = mysqli_fetch_assoc($return_query)) {
            $returns[] = ['id' => (int)$row['id'], 'sale_id' => (int)$row['sale_id'], 'amount' => (float)$row['amount'], 'waiter' => (string)($row['waiter'] ?? 'Unknown')];
        }
        $request_query = mysqli_query($connection, "SELECT r.req_id, r.counter, r.req_user, u.username AS requester,
            COUNT(ri.id) AS items, COALESCE(SUM(ri.issue_qty), SUM(ri.req_qty), 0) AS qty
            FROM kp_counter_req r LEFT JOIN kp_counter_req_items ri ON ri.req_id=r.req_id
            LEFT JOIN kp_users u ON u.user_id=r.req_user
            WHERE r.req_shift='$safe_shift' AND r.counter='$bar_counter' AND r.issue_status=1 AND r.rec_status=2
            GROUP BY r.req_id ORDER BY r.req_id DESC LIMIT 20");
        if ($request_query) while ($row = mysqli_fetch_assoc($request_query)) {
            $requests[] = ['id' => (int)$row['req_id'], 'requester' => (string)($row['requester'] ?? 'Unknown'), 'items' => (int)$row['items'], 'qty' => (float)$row['qty'], 'counter' => (string)get_counter((int)$row['counter'])];
        }
    }
    echo json_encode(['success' => true, 'data' => ['returns' => $returns, 'requests' => $requests, 'counter' => $bar_counter]]);
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
    $counter_totals = [];
    if ($safe_shift !== '') {
        $sales_query = mysqli_query($connection, "SELECT COUNT(*) AS total_count, COALESCE(SUM(amount), 0) AS total_amount FROM kp_sales WHERE user_id='$user_id' AND shift='$safe_shift' AND counter='$bar_counter'");
        $sales = $sales_query ? mysqli_fetch_assoc($sales_query) : [];
        $sales_count = (int)($sales['total_count'] ?? 0);
        $sales_total = (float)($sales['total_amount'] ?? 0);

        $returns_query = mysqli_query($connection, "SELECT COUNT(*) AS total_count, COALESCE(SUM(amount), 0) AS total_amount FROM kp_ret_items WHERE user_id='$user_id' AND shift='$safe_shift' AND stock_counter='$bar_counter' AND confirm_status=1");
        $returns = $returns_query ? mysqli_fetch_assoc($returns_query) : [];
        $returns_count = (int)($returns['total_count'] ?? 0);
        $returns_total = (float)($returns['total_amount'] ?? 0);

        $items_query = mysqli_query($connection, "SELECT COALESCE(SUM(qty), 0) AS total_qty FROM kp_sale_items WHERE user_id='$user_id' AND shift='$safe_shift' AND stock_counter='$bar_counter'");
        $items = $items_query ? mysqli_fetch_assoc($items_query) : [];
        $items_count = (float)($items['total_qty'] ?? 0);

        $counter_query = mysqli_query($connection, "SELECT si.counter AS counter_id,
            COALESCE(c.name, CONCAT('Counter ', si.counter)) AS counter_name,
            COUNT(DISTINCT si.sale_id) AS sales_count, COALESCE(SUM(si.total), 0) AS sales_total,
            COALESCE(SUM(si.qty), 0) AS items_count
            FROM kp_sale_items si LEFT JOIN kp_counters c ON c.cntr_id=si.counter
            WHERE si.user_id='$user_id' AND si.shift='$safe_shift' AND si.stock_counter='$bar_counter'
            GROUP BY si.counter ORDER BY counter_name ASC");
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
             LEFT JOIN kp_sales s ON s.user_id=u.user_id AND s.shift='$safe_shift' AND s.counter='$bar_counter'
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
          WHERE shift='$safe_shift' AND counter='$bar_counter'
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
];
if ($safe_shift !== '') {
    $summary_query = mysqli_query($connection, "SELECT COUNT(*) AS orders_total, COALESCE(SUM(amount), 0) AS sales_total FROM kp_sales WHERE shift='$safe_shift' AND counter='$bar_counter'");
    $summary = $summary_query ? mysqli_fetch_assoc($summary_query) : [];
    $dashboard['orders_total'] = (int)($summary['orders_total'] ?? 0);
    $dashboard['sales_total'] = (float)($summary['sales_total'] ?? 0);

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
    $dashboard['stock_summary']['returns'] = (int)column_count('kp_ret_items', 'id', "shift='$safe_shift' AND stock_counter='$bar_counter'");
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
                'waiter' => (string)get_column('kp_users', 'username', "user_id='$recent_user_id'"),
                'amount' => (float)$recent['amount'],
                'time' => (string)($recent['datime'] ?? ($recent['created_at'] ?? ($recent['time'] ?? ''))),
                'status' => (int)$recent['pay_status'] === 1 ? 'PAID' : 'PENDING',
            ];
        }
    }
}

echo json_encode([
    'success' => true,
    'message' => 'Waiters loaded.',
    'data' => ['shift' => $shift, 'waiters' => $waiters, 'dashboard' => $dashboard],
]);
