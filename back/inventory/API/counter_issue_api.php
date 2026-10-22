<?php
session_start();
header('Content-Type: application/json; charset=utf-8');
require_once '../../../inc/connection.php';
require_once '../../../inc/gen_funcs.php';

$user_id = (int)($_SESSION['user_id'] ?? $_SESSION['mwene'] ?? 0);
if ($user_id < 1) {
    http_response_code(401);
    echo json_encode(['ok' => false, 'message' => 'Session expired.']);
    exit;
}

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
$counter_column = 'cntr' . $counter_id . '_qty';
$counter_select = ($counter_id > 0 && isset($columns[$counter_column])) ? ", `$counter_column` AS counter_stock" : ', 0 AS counter_stock';
$where = "del_status=2";
if ($safe_query !== '') {
    $where .= " AND (name LIKE '%$safe_query%'";
    if (isset($columns['stock_code'])) {
        $where .= " OR stock_code LIKE '%$safe_query%'";
    }
    $where .= ")";
}
$items = [];
$items_q = mysqli_query($connection, "SELECT item_id, name, store_qty $code_select $counter_select FROM kp_items WHERE $where ORDER BY name ASC LIMIT 80");
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
