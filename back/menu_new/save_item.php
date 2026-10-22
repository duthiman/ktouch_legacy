<?php
session_start();
header('Content-Type: application/json');

require('../../inc/connection.php');
require('storage.php');

if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
    echo json_encode(array('ok' => false, 'message' => 'Invalid request method.'));
    exit;
}

$form_type = menu_new_text($_POST['form_type'] ?? '');

if ($form_type === '') {
    echo json_encode(array('ok' => false, 'message' => 'Form type is required.'));
    exit;
}

$items = menu_new_load_items();
$draft_id = menu_new_next_id($items);
$created_by = isset($_SESSION['user_id']) ? (int)$_SESSION['user_id'] : 0;
$created_at = date('Y-m-d H:i:s');

$draft = array(
    'draft_id' => $draft_id,
    'form_type' => $form_type,
    'name' => '',
    'summary' => '',
    'control_method' => '',
    'pricing_mode' => '',
    'department' => '',
    'created_at' => $created_at,
    'created_by' => $created_by,
    'data' => array()
);

switch ($form_type) {
    case 'sellable':
        $draft['name'] = menu_new_text($_POST['name'] ?? '', 'Untitled Sellable Item');
        $draft['summary'] = menu_new_text($_POST['category'] ?? 'General') . ' / ' . menu_new_text($_POST['subcategory'] ?? 'General');
        $draft['control_method'] = menu_new_text($_POST['control_method'] ?? 'direct_sell');
        $draft['pricing_mode'] = menu_new_text($_POST['pricing_mode'] ?? 'fixed');
        $draft['department'] = menu_new_text($_POST['department'] ?? 'general');
        $draft['data'] = array(
            'code' => menu_new_text($_POST['code'] ?? ''),
            'category' => menu_new_text($_POST['category'] ?? ''),
            'subcategory' => menu_new_text($_POST['subcategory'] ?? ''),
            'price' => menu_new_number($_POST['price'] ?? 0),
            'min_price' => menu_new_number($_POST['min_price'] ?? 0),
            'max_price' => menu_new_number($_POST['max_price'] ?? 0),
            'pos' => menu_new_bool($_POST['pos'] ?? 0),
            'taxable' => menu_new_bool($_POST['taxable'] ?? 0),
            'active' => menu_new_bool($_POST['active'] ?? 0),
            'attr_accomp' => menu_new_bool($_POST['attr_accomp'] ?? 0),
            'attr_temp' => menu_new_bool($_POST['attr_temp'] ?? 0),
            'attr_chilli' => menu_new_bool($_POST['attr_chilli'] ?? 0),
            'attr_addon' => menu_new_bool($_POST['attr_addon'] ?? 0),
            'open_price' => menu_new_bool($_POST['open_price'] ?? 0),
            'require_perm' => menu_new_bool($_POST['require_perm'] ?? 0),
            'batch_source' => menu_new_text($_POST['batch_source'] ?? ''),
            'batch_yield' => menu_new_number($_POST['batch_yield'] ?? 0),
            'batch_unit' => menu_new_text($_POST['batch_unit'] ?? ''),
            'bottle_ml' => menu_new_number($_POST['bottle_ml'] ?? 0),
            'shot_ml' => menu_new_number($_POST['shot_ml'] ?? 0),
            'empty_weight' => menu_new_number($_POST['empty_weight'] ?? 0),
            'full_weight' => menu_new_number($_POST['full_weight'] ?? 0)
        );
        break;

    case 'raw':
        $draft['name'] = menu_new_text($_POST['name'] ?? '', 'Untitled Raw Material');
        $draft['summary'] = menu_new_text($_POST['category'] ?? 'General') . ' / ' . menu_new_text($_POST['store_area'] ?? 'Store');
        $draft['control_method'] = menu_new_text($_POST['control_method'] ?? 'simple_issue');
        $draft['pricing_mode'] = 'purchase';
        $draft['department'] = menu_new_text($_POST['store_area'] ?? 'store');
        $draft['data'] = array(
            'code' => menu_new_text($_POST['code'] ?? ''),
            'purchase_unit' => menu_new_text($_POST['purchase_unit'] ?? ''),
            'stock_unit' => menu_new_text($_POST['stock_unit'] ?? ''),
            'purchase_price' => menu_new_number($_POST['purchase_price'] ?? 0),
            'reorder' => menu_new_number($_POST['reorder'] ?? 0),
            'conversion_basis' => menu_new_text($_POST['conversion_basis'] ?? ''),
            'conversion_qty' => menu_new_number($_POST['conversion_qty'] ?? 0),
            'conversion_unit' => menu_new_text($_POST['conversion_unit'] ?? ''),
            'purchasable' => menu_new_bool($_POST['purchasable'] ?? 0),
            'stockable' => menu_new_bool($_POST['stockable'] ?? 0),
            'direct_issue' => menu_new_bool($_POST['direct_issue'] ?? 0),
            'low_control' => menu_new_bool($_POST['low_control'] ?? 0)
        );
        break;

    case 'batch':
        $draft['name'] = menu_new_text($_POST['name'] ?? '', 'Untitled Batch Item');
        $draft['summary'] = menu_new_text($_POST['category'] ?? 'General') . ' / batch';
        $draft['control_method'] = 'batch';
        $draft['pricing_mode'] = 'fixed';
        $draft['department'] = menu_new_text($_POST['department'] ?? 'kitchen');
        $draft['data'] = array(
            'price' => menu_new_number($_POST['price'] ?? 0),
            'sale_unit' => menu_new_text($_POST['sale_unit'] ?? ''),
            'source_item' => menu_new_text($_POST['source_item'] ?? ''),
            'source_qty' => menu_new_number($_POST['source_qty'] ?? 0),
            'source_unit' => menu_new_text($_POST['source_unit'] ?? ''),
            'yield' => menu_new_number($_POST['yield'] ?? 0),
            'production_unit' => menu_new_text($_POST['production_unit'] ?? ''),
            'mode' => menu_new_text($_POST['mode'] ?? 'single_source'),
            'pos' => menu_new_bool($_POST['pos'] ?? 0),
            'stock_output' => menu_new_bool($_POST['stock_output'] ?? 0),
            'source_deduct' => menu_new_bool($_POST['source_deduct'] ?? 0),
            'notes' => menu_new_text($_POST['notes'] ?? '')
        );
        break;

    case 'bottle':
        $draft['name'] = menu_new_text($_POST['name'] ?? '', 'Untitled Bottle Item');
        $draft['summary'] = menu_new_text($_POST['bottle_name'] ?? 'Bottle Stock') . ' / tot';
        $draft['control_method'] = 'bottle';
        $draft['pricing_mode'] = menu_new_text($_POST['pricing_mode'] ?? 'fixed');
        $draft['department'] = menu_new_text($_POST['department'] ?? 'bar');
        $draft['data'] = array(
            'bottle_name' => menu_new_text($_POST['bottle_name'] ?? ''),
            'price' => menu_new_number($_POST['price'] ?? 0),
            'shot_name' => menu_new_text($_POST['shot_name'] ?? ''),
            'bottle_ml' => menu_new_number($_POST['bottle_ml'] ?? 0),
            'shot_ml' => menu_new_number($_POST['shot_ml'] ?? 0),
            'empty_weight' => menu_new_number($_POST['empty_weight'] ?? 0),
            'full_weight' => menu_new_number($_POST['full_weight'] ?? 0),
            'current_weight' => menu_new_number($_POST['current_weight'] ?? 0),
            'pos' => menu_new_bool($_POST['pos'] ?? 0),
            'track_weight' => menu_new_bool($_POST['track_weight'] ?? 0),
            'track_ml' => menu_new_bool($_POST['track_ml'] ?? 0),
            'allow_open_bottle' => menu_new_bool($_POST['allow_open_bottle'] ?? 0)
        );
        break;

    default:
        echo json_encode(array('ok' => false, 'message' => 'Unsupported form type.'));
        exit;
}

$items[] = $draft;
menu_new_save_items($items);

echo json_encode(array(
    'ok' => true,
    'message' => 'Draft saved in menu_new workspace.',
    'draft_id' => $draft_id
));
