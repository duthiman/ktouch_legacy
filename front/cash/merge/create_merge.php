<?php
session_start();
include('../../../inc/connection.php');
include('../funcs.php');

header('Content-Type: application/json');

if (!isset($_SESSION['user_id']) || (int)$_SESSION['user_id'] === 0) {
    echo json_encode(array('ok' => false, 'err' => 'Session expired.'));
    exit;
}

cash_ensure_merge_tables();

$user_id = (int)$_SESSION['user_id'];
$roots = $_POST['roots'] ?? array();
$error_message = '';
$created = cash_create_merge_record($user_id, $roots, $error_message);
if (!$created) {
    echo json_encode(array('ok' => false, 'err' => $error_message ?: 'Could not create the merge.'));
    exit;
}

$merge_id = (int)$created['merge_id'];
$merge_no = $created['merge_no'];
$merge_total = (float)$created['total_amount'];
$expanded_items = $created['items'];

$grouped = array();
foreach ($expanded_items as $item) {
    $root_sale_id = (int)$item['root_sale_id'];
    if (!isset($grouped[$root_sale_id])) {
        $grouped[$root_sale_id] = 0;
    }
    $grouped[$root_sale_id] += (float)$item['amount'];
}

$preview = '<div class="cash-panel"><div class="cash-panel-head"><h4>New Merge Created</h4></div><div class="cash-panel-body">';
$preview .= '<div class="cash-merge-basket-card" style="margin:0;"><small>Merge Number</small><strong>' . htmlspecialchars($merge_no) . '</strong><p><b>Total:</b> ' . number_format($merge_total, 2) . '</p></div>';
foreach ($grouped as $root_sale_id => $root_total) {
    $preview .= '<div class="cash-merge-basket-card"><small>Included Bill Group</small><strong>#' . (int)$root_sale_id . '</strong><p><b>Amount:</b> ' . number_format((float)$root_total, 2) . '</p></div>';
}
$preview .= '</div></div>';

echo json_encode(array(
    'ok' => true,
    'merge_id' => $merge_id,
    'merge_no' => $merge_no,
    'total_amount' => number_format($merge_total, 2, '.', ''),
    'preview_html' => $preview
));
?>
