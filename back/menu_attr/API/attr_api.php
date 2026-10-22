<?php
session_start();

header('Content-Type: application/json');

require_once '../../../inc/connection.php';
require_once __DIR__ . '/attr_schema.php';
require_once __DIR__ . '/attr_helpers.php';
require_once __DIR__ . '/attr_repository.php';
require_once __DIR__ . '/attr_service.php';

attr_schema_ready();

$action = attr_clean_text($_REQUEST['action'] ?? '');

switch ($action) {
    case 'modal':
        $result = attr_service_modal_view(attr_clean_text($_REQUEST['view'] ?? ''), $_REQUEST);
        attr_json_response($result['ok'], $result['message'], ['html' => $result['html'] ?? ''], $result['ok'] ? 200 : 400);
        break;

    case 'create_attribute':
        $result = attr_service_create_attribute($_REQUEST['name'] ?? '', $_REQUEST['affects_price'] ?? 0, $_REQUEST['price_change'] ?? 0, $_REQUEST['status'] ?? 1);
        attr_json_response($result['ok'], $result['message'], ['refresh' => !empty($result['refresh'])], $result['ok'] ? 200 : 400);
        break;

    case 'load_mapped_items':
        $result = attr_service_load_mapped_items(attr_clean_int($_REQUEST['attr_id'] ?? 0));
        attr_json_response($result['ok'], $result['message'], ['html' => $result['html'] ?? ''], $result['ok'] ? 200 : 400);
        break;

    case 'map_item':
        $result = attr_service_map_item(attr_clean_int($_REQUEST['item_id'] ?? 0), attr_clean_int($_REQUEST['attr_id'] ?? 0));
        attr_json_response($result['ok'], $result['message'], [], $result['ok'] ? 200 : 400);
        break;

    case 'unmap_item':
        $result = attr_service_unmap_item(attr_clean_int($_REQUEST['item_id'] ?? 0), attr_clean_int($_REQUEST['attr_id'] ?? 0));
        attr_json_response($result['ok'], $result['message'], [], $result['ok'] ? 200 : 400);
        break;

    default:
        attr_json_response(false, 'Unknown action.', ['action' => $action], 400);
        break;
}
