<?php
session_start();
header('Content-Type: application/json; charset=utf-8');
require_once '../../../inc/connection.php';
require_once '../../../inc/gen_funcs.php';
require_once __DIR__ . '/../../../front/counter/api/counter_inventory_schema.php';

$user_id = (int)($_SESSION['user_id'] ?? $_SESSION['mwene'] ?? 0);
if ($user_id < 1) {
    http_response_code(401);
    echo json_encode(['ok' => false, 'message' => 'Session expired.']);
    exit;
}
counter_inventory_ensure_schema($connection);

$action = trim((string)($_GET['action'] ?? $_POST['action'] ?? ''));
if ($action !== 'search') {
    http_response_code(400);
    echo json_encode(['ok' => false, 'message' => 'Unknown request.']);
    exit;
}

$query = trim((string)($_GET['q'] ?? ''));
$counter_id = (int)($_GET['counter'] ?? 0);
$safe_query = mysqli_real_escape_string($connection, $query);
$columns = [];
$column_q = mysqli_query($connection, "SHOW COLUMNS FROM kp_items");
if ($column_q) {
    while ($column = mysqli_fetch_assoc($column_q)) {
        $columns[$column['Field']] = true;
    }
}
$code_select = isset($columns['stock_code']) ? ', stock_code' : ", '' AS stock_code";
$counter_select = ', COALESCE(ci.qty,0) AS counter_stock';
$department_id = $counter_id > 0 ? (int)get_column('kp_counters', 'dpt', "cntr_id='{$counter_id}'") : 0;
$where = "i.del_status=2";
if ($department_id > 0) {
    $where .= " AND (i.dpt='{$department_id}' OR COALESCE(i.dpt,0)=0)";
}
if ($safe_query !== '') {
    $where .= " AND (i.name LIKE '%$safe_query%'";
    if (isset($columns['stock_code'])) {
        $where .= " OR i.stock_code LIKE '%$safe_query%'";
    }
    $where .= ")";
}
$items = [];
$items_q = mysqli_query($connection, "SELECT i.item_id, i.name, i.store_qty $code_select $counter_select FROM kp_items i LEFT JOIN kp_counter_items ci ON ci.counter_id='{$counter_id}' AND ci.item_id=i.item_id AND ci.is_active=1 WHERE $where ORDER BY i.name ASC LIMIT 80");
if ($items_q) {
    while ($row = mysqli_fetch_assoc($items_q)) {
        $items[] = [
            'id' => (int)$row['item_id'],
            'name' => (string)$row['name'],
            'sku' => (string)($row['stock_code'] ?? ''),
            'stock' => (float)($row['store_qty'] ?? 0),
            'counter_stock' => (float)($row['counter_stock'] ?? 0),
            'unit' => 'Units',
        ];
    }
}
echo json_encode(['ok' => true, 'items' => $items]);
