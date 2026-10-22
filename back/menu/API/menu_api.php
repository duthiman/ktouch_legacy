<?php
session_start();

header('Content-Type: application/json');

require_once '../../../inc/connection.php';
require_once '../../../inc/gen_funcs.php';
require_once __DIR__ . '/menu_schema.php';
require_once __DIR__ . '/menu_helpers.php';
require_once __DIR__ . '/menu_repository.php';
require_once __DIR__ . '/menu_service.php';

$action = menu_clean_text($_REQUEST['action'] ?? '');

switch ($action) {
    case 'bootstrap':
        $result = menu_service_bootstrap();
        menu_json_response($result['ok'], $result['message'], [
            'categories' => $result['categories'] ?? [],
            'sub_categories' => $result['sub_categories'] ?? [],
            'taxes' => $result['taxes'] ?? [],
            'stock_items' => $result['stock_items'] ?? [],
            'counters' => $result['counters'] ?? [],
            'uoms' => $result['uoms'] ?? [],
        ], $result['ok'] ? 200 : 400);
        break;

    case 'datatable':
        echo json_encode(menu_service_get_items());
        exit;

    case 'search':
        $result = menu_service_search_items($_REQUEST);
        menu_json_response($result['ok'], $result['message'], [
            'filters' => $result['filters'] ?? [],
            'total' => $result['total'] ?? 0,
            'items' => $result['items'] ?? [],
        ], $result['ok'] ? 200 : 400);
        break;

    case 'export_rows':
        echo json_encode(menu_service_export_items($_REQUEST));
        exit;

    case 'edit_item':
        $result = menu_service_edit_item(
            menu_clean_int($_REQUEST['id'] ?? 0),
            menu_clean_text($_REQUEST['column_name'] ?? ''),
            $_REQUEST['text'] ?? '',
            menu_clean_text($_REQUEST['field'] ?? '')
        );
        menu_json_response($result['ok'], $result['message'], [], $result['ok'] ? 200 : 400);
        break;

    case 'modal':
        $result = menu_service_modal_view(menu_clean_text($_REQUEST['view'] ?? ''), $_REQUEST);
        menu_json_response($result['ok'], $result['message'], [
            'html' => $result['html'] ?? '',
        ], $result['ok'] ? 200 : 400);
        break;

    case 'save_category':
        $result = menu_service_change_category($_REQUEST['items'] ?? '', menu_clean_int($_REQUEST['cat_id'] ?? 0));
        menu_json_response($result['ok'], $result['message'], ['refresh' => !empty($result['refresh'])], $result['ok'] ? 200 : 400);
        break;

    case 'save_sub_category':
        $result = menu_service_change_sub_category($_REQUEST['items'] ?? '', menu_clean_int($_REQUEST['sub_id'] ?? 0));
        menu_json_response($result['ok'], $result['message'], ['refresh' => !empty($result['refresh'])], $result['ok'] ? 200 : 400);
        break;

    case 'save_settings':
        $result = menu_service_change_settings($_REQUEST['items'] ?? '', menu_clean_int($_REQUEST['stock'] ?? 0), menu_clean_int($_REQUEST['menu'] ?? 0), menu_clean_int($_REQUEST['point'] ?? 0), menu_clean_int($_REQUEST['dpt'] ?? 0), !empty($_REQUEST['butchery_enabled']));
        menu_json_response($result['ok'], $result['message'], ['refresh' => !empty($result['refresh'])], $result['ok'] ? 200 : 400);
        break;
    case 'set_butchery':
        $result = menu_service_set_butchery(menu_clean_int($_REQUEST['item_id'] ?? 0), !empty($_REQUEST['enabled']));
        menu_json_response($result['ok'], $result['message'], ['refresh' => !empty($result['refresh'])], $result['ok'] ? 200 : 400);
        break;

    case 'save_tax':
        $result = menu_service_change_tax($_REQUEST['items'] ?? '', menu_clean_int($_REQUEST['tax_id'] ?? 0));
        menu_json_response($result['ok'], $result['message'], ['refresh' => !empty($result['refresh'])], $result['ok'] ? 200 : 400);
        break;

    case 'discontinue_items':
        $result = menu_service_discontinue_items($_REQUEST['items'] ?? '');
        menu_json_response($result['ok'], $result['message'], ['refresh' => !empty($result['refresh'])], $result['ok'] ? 200 : 400);
        break;

    case 'create_item':
        $result = menu_service_create_item($_POST);
        menu_json_response($result['ok'], $result['message'], ['refresh' => !empty($result['refresh'])], $result['ok'] ? 200 : 400);
        break;

    case 'import_items':
        $result = menu_service_import_items($_FILES['file'] ?? []);
        menu_json_response($result['ok'], $result['message'], [
            'imported' => $result['imported'] ?? 0,
            'errors' => $result['errors'] ?? [],
            'rows' => $result['rows'] ?? [],
        ], $result['ok'] ? 200 : 400);
        break;

    case 'save_portion':
        $result = menu_service_save_portion(menu_clean_int($_REQUEST['id'] ?? 0), $_REQUEST['portions'] ?? 0);
        menu_json_response($result['ok'], $result['message'], ['refresh' => !empty($result['refresh'])], $result['ok'] ? 200 : 400);
        break;

    case 'clear_portion':
        $result = menu_service_clear_portion(menu_clean_int($_REQUEST['item_id'] ?? 0));
        menu_json_response($result['ok'], $result['message'], ['refresh' => !empty($result['refresh'])], $result['ok'] ? 200 : 400);
        break;

    case 'save_tots':
        $result = menu_service_save_tots($_POST);
        menu_json_response($result['ok'], $result['message'], ['refresh' => !empty($result['refresh'])], $result['ok'] ? 200 : 400);
        break;

    case 'save_units':
        $result = menu_service_save_uom(menu_clean_int($_REQUEST['item_id'] ?? 0), menu_clean_int($_REQUEST['uom_id'] ?? 0));
        menu_json_response($result['ok'], $result['message'], ['refresh' => !empty($result['refresh'])], $result['ok'] ? 200 : 400);
        break;

    case 'clear_tots':
        $result = menu_service_clear_tots(menu_clean_int($_REQUEST['item_id'] ?? 0));
        menu_json_response($result['ok'], $result['message'], ['refresh' => !empty($result['refresh'])], $result['ok'] ? 200 : 400);
        break;

    case 'search_unmapped_items':
        $result = menu_service_search_unmapped_items(menu_clean_int($_REQUEST['attr_id'] ?? 0), menu_clean_text($_REQUEST['search'] ?? ''));
        menu_json_response($result['ok'], $result['message'], [
            'items' => $result['items'] ?? [],
        ], $result['ok'] ? 200 : 400);
        break;

    default:
        menu_json_response(false, 'Unknown action.', ['action' => $action], 400);
        break;
}
