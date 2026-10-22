<?php
session_start();

header('Content-Type: application/json');

require_once '../../../inc/connection.php';
require_once '../../../inc/gen_funcs.php';
require_once __DIR__ . '/recipe_schema.php';
require_once __DIR__ . '/recipe_helpers.php';
require_once __DIR__ . '/recipe_repository.php';
require_once __DIR__ . '/recipe_service.php';

$action = recipe_clean_text($_REQUEST['action'] ?? '');

switch ($action) {
    case 'bootstrap':
        $result = recipe_service_bootstrap(recipe_clean_int($_REQUEST['item_id'] ?? 0));
        recipe_json_response($result['ok'], $result['message'], [
            'item' => $result['item'] ?? [],
            'stock_items' => $result['stock_items'] ?? [],
            'recipe_items' => $result['recipe_items'] ?? [],
        ], $result['ok'] ? 200 : 400);
        break;

    case 'list_recipe_items':
        $result = recipe_service_list_items(recipe_clean_int($_REQUEST['item_id'] ?? 0));
        recipe_json_response($result['ok'], $result['message'], [
            'recipe_items' => $result['recipe_items'] ?? [],
        ], $result['ok'] ? 200 : 400);
        break;

    case 'add_recipe_item':
        $result = recipe_service_add_item(
            recipe_clean_int($_POST['item_id'] ?? 0),
            recipe_clean_int($_POST['raw_id'] ?? 0),
            $_POST['qty'] ?? ''
        );
        recipe_json_response($result['ok'], $result['message'], [
            'recipe_items' => $result['recipe_items'] ?? [],
        ], $result['ok'] ? 200 : 400);
        break;

    case 'delete_recipe_item':
        $result = recipe_service_delete_item(
            recipe_clean_int($_REQUEST['id'] ?? 0),
            recipe_clean_int($_REQUEST['item_id'] ?? 0)
        );
        recipe_json_response($result['ok'], $result['message'], [
            'recipe_items' => $result['recipe_items'] ?? [],
        ], $result['ok'] ? 200 : 400);
        break;

    default:
        recipe_json_response(false, 'Unknown action.', ['action' => $action], 400);
        break;
}
