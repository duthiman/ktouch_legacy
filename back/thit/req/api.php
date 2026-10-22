<?php
declare(strict_types=1);

require_once __DIR__ . '/../../../inc/connection.php';
require_once __DIR__ . '/../../../inc/gen_funcs.php';

if (session_status() === PHP_SESSION_NONE) session_start();
header('Content-Type: application/json; charset=utf-8');

function req_json(array $data, int $status = 200): never
{
    http_response_code($status);
    echo json_encode($data, JSON_UNESCAPED_UNICODE | JSON_UNESCAPED_SLASHES);
    exit;
}

$action = $_GET['action'] ?? $_POST['action'] ?? 'view';
if ($action === 'details') {
    $detailId = (int)($_GET['req_id'] ?? $_POST['req_id'] ?? 0);
    if ($detailId < 1) req_json(['ok' => false, 'message' => 'A valid requisition is required.'], 422);
    $detailResult = mysqli_query($connection, "SELECT * FROM kp_counter_req WHERE req_id={$detailId} LIMIT 1");
    if (!$detailResult || mysqli_num_rows($detailResult) < 1) req_json(['ok' => false, 'message' => 'Requisition was not found.'], 404);
    $detail = mysqli_fetch_assoc($detailResult);
    $detail['requester_name'] = function_exists('get_user') ? get_user((int)($detail['req_user'] ?? 0)) : ($detail['req_user'] ?? '');
    $detail['counter_name'] = function_exists('get_counter') ? get_counter((int)($detail['counter'] ?? 0)) : ($detail['counter'] ?? '');
    $detailItems = [];
    $detailItemsResult = mysqli_query($connection, "SELECT ri.*, i.name, i.store_qty, i.port_status, i.portions FROM kp_counter_req_items ri LEFT JOIN kp_items i ON i.item_id=ri.item_id WHERE ri.req_id={$detailId} AND ri.issue_status=2 ORDER BY ri.item_id");
    if ($detailItemsResult) while ($item = mysqli_fetch_assoc($detailItemsResult)) $detailItems[] = $item;
    req_json(['ok' => true, 'data' => ['request' => $detail, 'items' => $detailItems]]);
}
if ($action === 'list') {
    $rows = [];
    $result = mysqli_query($connection, "SELECT * FROM kp_counter_req WHERE issue_status=2 ORDER BY req_id DESC");
    if ($result) {
        while ($row = mysqli_fetch_assoc($result)) {
            $row['requester_name'] = function_exists('get_user') ? get_user((int)($row['req_user'] ?? 0)) : ($row['req_user'] ?? '');
            $row['counter_name'] = function_exists('get_counter') ? get_counter((int)($row['counter'] ?? 0)) : ($row['counter'] ?? '');
            $row['item_count'] = 0;
            $id = (int)($row['req_id'] ?? 0);
            $itemCount = mysqli_query($connection, "SELECT COUNT(*) total FROM kp_counter_req_items WHERE req_id={$id} AND issue_status=2");
            if ($itemCount && ($countRow = mysqli_fetch_assoc($itemCount))) $row['item_count'] = (int)$countRow['total'];
            $rows[] = $row;
        }
    }
    req_json(['ok' => true, 'data' => ['requests' => $rows]]);
}

$reqId = (int)($_GET['req_id'] ?? $_POST['req_id'] ?? 0);
if ($reqId < 1) req_json(['ok' => false, 'message' => 'A valid requisition is required.'], 422);

$reqEsc = mysqli_real_escape_string($connection, (string)$reqId);
$requestResult = mysqli_query($connection, "SELECT * FROM kp_counter_req WHERE req_id='{$reqEsc}' LIMIT 1");
if (!$requestResult || mysqli_num_rows($requestResult) < 1) req_json(['ok' => false, 'message' => 'Counter requisition was not found.'], 404);

$request = mysqli_fetch_assoc($requestResult);
$items = [];
$itemsResult = mysqli_query($connection, "SELECT ri.*, i.name FROM kp_counter_req_items ri LEFT JOIN kp_items i ON i.item_id=ri.item_id WHERE ri.req_id='{$reqEsc}' AND ri.issue_status=2 ORDER BY ri.item_id");
if ($itemsResult) {
    while ($item = mysqli_fetch_assoc($itemsResult)) $items[] = $item;
}

$request['requester_name'] = function_exists('get_user') ? get_user((int)($request['req_user'] ?? 0)) : ($request['req_user'] ?? '');
$request['counter_name'] = function_exists('get_counter') ? get_counter((int)($request['counter'] ?? 0)) : ($request['counter'] ?? '');
foreach ($items as &$item) {
    if (empty($item['name']) && function_exists('get_item')) $item['name'] = get_item((int)$item['item_id']);
}
unset($item);

req_json(['ok' => true, 'data' => ['request' => $request, 'items' => $items]]);
