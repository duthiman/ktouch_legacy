<?php

if (!function_exists('recipe_service_bootstrap')) {
    function recipe_service_bootstrap($item_id)
    {
        $item_id = (int)$item_id;
        if ($item_id < 1) {
            return ['ok' => false, 'message' => 'Invalid item selected.'];
        }

        recipe_sync_item_flags($item_id);
        return [
            'ok' => true,
            'message' => 'Recipe bootstrap loaded.',
            'item' => recipe_repo_get_item_row($item_id),
            'stock_items' => recipe_repo_fetch_stock_items(),
            'recipe_items' => recipe_repo_fetch_recipe_items($item_id),
        ];
    }
}

if (!function_exists('recipe_service_add_item')) {
    function recipe_service_add_item($item_id, $raw_id, $qty)
    {
        global $connection;

        $item_id = (int)$item_id;
        $raw_id = (int)$raw_id;
        $qty = recipe_clean_text($qty);

        if ($item_id < 1) {
            return ['ok' => false, 'message' => 'Make sure you select item to add stock to.'];
        }
        if ($raw_id < 1) {
            return ['ok' => false, 'message' => 'Please select raw materials.'];
        }
        if ($qty === '' || (float)$qty == 0) {
            return ['ok' => false, 'message' => 'Add quantity.'];
        }
        if (recipe_repo_recipe_item_exists($item_id, $raw_id)) {
            return ['ok' => false, 'message' => 'You already added the item.'];
        }
        if (!recipe_repo_insert_recipe_item($item_id, $raw_id, $qty)) {
            return ['ok' => false, 'message' => 'Error adding recipe item: ' . mysqli_error($connection)];
        }
        recipe_repo_update_recipe_parent_item($item_id);
        recipe_sync_item_flags($item_id);

        return [
            'ok' => true,
            'message' => 'Recipe item added.',
            'recipe_items' => recipe_repo_fetch_recipe_items($item_id),
        ];
    }
}

if (!function_exists('recipe_service_delete_item')) {
    function recipe_service_delete_item($id, $item_id)
    {
        global $connection;

        $id = (int)$id;
        $item_id = (int)$item_id;
        if ($id < 1 || $item_id < 1) {
            return ['ok' => false, 'message' => 'Invalid recipe item request.'];
        }
        if (!recipe_repo_delete_recipe_item($id, $item_id)) {
            return ['ok' => false, 'message' => 'Error deleting recipe item: ' . mysqli_error($connection)];
        }
        recipe_sync_item_flags($item_id);

        return [
            'ok' => true,
            'message' => 'Recipe item removed.',
            'recipe_items' => recipe_repo_fetch_recipe_items($item_id),
        ];
    }
}

if (!function_exists('recipe_service_list_items')) {
    function recipe_service_list_items($item_id)
    {
        $item_id = (int)$item_id;
        if ($item_id < 1) {
            return ['ok' => false, 'message' => 'Invalid item selected.', 'recipe_items' => []];
        }
        recipe_sync_item_flags($item_id);
        return [
            'ok' => true,
            'message' => 'Recipe items loaded.',
            'recipe_items' => recipe_repo_fetch_recipe_items($item_id),
        ];
    }
}
