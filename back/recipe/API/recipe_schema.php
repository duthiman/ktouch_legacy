<?php

if (!function_exists('recipe_sync_item_flags')) {
    function recipe_sync_item_flags($item_id)
    {
        if (function_exists('update_item_recipe')) {
            update_item_recipe((int)$item_id);
        }
    }
}
