<?php
declare(strict_types=1);

require_once __DIR__ . '/../../../inc/connection.php';
header('Content-Type: application/json; charset=utf-8');

function stockman_json(array $data, int $status = 200): never { http_response_code($status); echo json_encode($data, JSON_UNESCAPED_UNICODE | JSON_UNESCAPED_SLASHES); exit; }
function stockman_columns(mysqli $db): array { $columns = []; $result = $db->query('SHOW COLUMNS FROM kp_items'); while ($row = $result->fetch_assoc()) $columns[$row['Field']] = true; return $columns; }

try {
    $action = $_GET['action'] ?? $_POST['action'] ?? 'items';
    $columns = stockman_columns($connection);
    if (!isset($columns['item_id'], $columns['name'], $columns['store_qty'])) stockman_json(['ok' => false, 'message' => 'Stock item fields are not available.'], 500);
    if ($action === 'items') {
        $where = isset($columns['del_status']) ? 'WHERE del_status=2' : '';
        $rows = [];
        $result = $connection->query("SELECT item_id,name,store_qty" . (isset($columns['store_reorder']) ? ',store_reorder' : ',0 store_reorder') . " FROM kp_items {$where} ORDER BY name");
        while ($row = $result->fetch_assoc()) $rows[] = $row;
        stockman_json(['ok' => true, 'data' => ['items' => $rows]]);
    }
    $itemId = (int)($_POST['item_id'] ?? 0); $value = (float)($_POST['value'] ?? -1);
    if ($itemId < 1 || $value < 0) stockman_json(['ok' => false, 'message' => 'Enter a valid value.'], 422);
    $field = $action === 'adjust_reorder' ? 'store_reorder' : ($action === 'adjust_stock' ? 'store_qty' : '');
    if (!$field || !isset($columns[$field])) stockman_json(['ok' => false, 'message' => 'Invalid stock action.'], 422);
    $stmt = $connection->prepare("UPDATE kp_items SET `{$field}`=? WHERE item_id=?");
    $stmt->bind_param('di', $value, $itemId); $stmt->execute();
    stockman_json(['ok' => true, 'message' => 'Stock details updated.']);
} catch (Throwable $e) { stockman_json(['ok' => false, 'message' => 'Stock data could not be loaded.'], 500); }
