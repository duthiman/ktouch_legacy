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
    if ($safe_shift !== '') {
        $sales_query = mysqli_query($connection, "SELECT COUNT(*) AS total_count, COALESCE(SUM(amount), 0) AS total_amount FROM kp_sales WHERE user_id='$user_id' AND shift='$safe_shift'");
        $sales = $sales_query ? mysqli_fetch_assoc($sales_query) : [];
        $sales_count = (int)($sales['total_count'] ?? 0);
        $sales_total = (float)($sales['total_amount'] ?? 0);

        $returns_query = mysqli_query($connection, "SELECT COUNT(*) AS total_count, COALESCE(SUM(amount), 0) AS total_amount FROM kp_ret_items WHERE user_id='$user_id' AND shift='$safe_shift' AND confirm_status=1");
        $returns = $returns_query ? mysqli_fetch_assoc($returns_query) : [];
        $returns_count = (int)($returns['total_count'] ?? 0);
        $returns_total = (float)($returns['total_amount'] ?? 0);

        $items_query = mysqli_query($connection, "SELECT COALESCE(SUM(qty), 0) AS total_qty FROM kp_sale_items WHERE user_id='$user_id' AND shift='$safe_shift'");
        $items = $items_query ? mysqli_fetch_assoc($items_query) : [];
        $items_count = (float)($items['total_qty'] ?? 0);
    }

    $position = 0;
    if ($safe_shift !== '') {
        $ranking_query = mysqli_query(
            $connection,
            "SELECT u.user_id, COALESCE(SUM(s.amount), 0) AS sales_total
             FROM kp_users u
             LEFT JOIN kp_sales s ON s.user_id=u.user_id AND s.shift='$safe_shift'
             WHERE u.job <> 1
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
     WHERE u.job <> 1
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

echo json_encode([
    'success' => true,
    'message' => 'Waiters loaded.',
    'data' => ['shift' => $shift, 'waiters' => $waiters],
]);
