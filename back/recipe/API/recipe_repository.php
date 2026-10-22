<?php

if (!function_exists('recipe_repo_get_item_row')) {
    function recipe_repo_get_item_row($item_id)
    {
        global $connection;

        $item_id = (int)$item_id;
        if ($item_id < 1) {
            return [];
        }

        $result = mysqli_query($connection, "SELECT * FROM kp_items WHERE item_id='{$item_id}' LIMIT 1");
        if (!$result) {
            return [];
        }

        $row = mysqli_fetch_assoc($result);
        return is_array($row) ? $row : [];
    }
}

if (!function_exists('recipe_repo_get_item_name')) {
    function recipe_repo_get_item_name($item_id)
    {
        return (string)get_column('kp_items', 'name', "item_id='" . (int)$item_id . "'");
    }
}

if (!function_exists('recipe_repo_fetch_stock_items')) {
    function recipe_repo_fetch_stock_items()
    {
        global $connection;

        $rows = [];
        $result = mysqli_query($connection, "SELECT item_id, name FROM kp_items WHERE stock=1 ORDER BY name ASC");
        if (!$result) {
            return $rows;
        }

        while ($row = mysqli_fetch_assoc($result)) {
            $rows[] = [
                'id' => (int)$row['item_id'],
                'name' => (string)$row['name'],
            ];
        }

        return $rows;
    }
}

if (!function_exists('recipe_repo_fetch_recipe_items')) {
    function recipe_repo_fetch_recipe_items($item_id)
    {
        global $connection;

        $item_id = (int)$item_id;
        $rows = [];
        if ($item_id < 1) {
            return $rows;
        }

        $result = mysqli_query($connection, "SELECT id, raw_id, qty FROM kp_recipe WHERE item_id='{$item_id}'");
        if (!$result) {
            return $rows;
        }

        while ($row = mysqli_fetch_assoc($result)) {
            $rawId = (int)$row['raw_id'];
            $rows[] = [
                'id' => (int)$row['id'],
                'raw_id' => $rawId,
                'raw_name' => recipe_repo_get_item_name($rawId),
                'qty' => (string)$row['qty'],
            ];
        }

        return $rows;
    }
}

if (!function_exists('recipe_repo_recipe_item_exists')) {
    function recipe_repo_recipe_item_exists($item_id, $raw_id)
    {
        global $connection;

        $item_id = (int)$item_id;
        $raw_id = (int)$raw_id;
        $result = mysqli_query($connection, "SELECT raw_id FROM kp_recipe WHERE raw_id='{$raw_id}' AND item_id='{$item_id}' LIMIT 1");
        return $result && mysqli_num_rows($result) > 0;
    }
}

if (!function_exists('recipe_repo_insert_recipe_item')) {
    function recipe_repo_insert_recipe_item($item_id, $raw_id, $qty)
    {
        global $connection;

        $item_id = (int)$item_id;
        $raw_id = (int)$raw_id;
        $qty = mysqli_real_escape_string($connection, (string)$qty);
        return mysqli_query($connection, "INSERT INTO kp_recipe(item_id,raw_id,qty) VALUES ('{$item_id}','{$raw_id}','{$qty}')");
    }
}

if (!function_exists('recipe_repo_update_recipe_parent_item')) {
    function recipe_repo_update_recipe_parent_item($item_id)
    {
        global $connection;

        $item_id = (int)$item_id;
        return mysqli_query($connection, "UPDATE kp_items SET recipe=1,stock=2,portions=0,port_status=2 WHERE item_id='{$item_id}'");
    }
}

if (!function_exists('recipe_repo_delete_recipe_item')) {
    function recipe_repo_delete_recipe_item($id, $item_id)
    {
        global $connection;

        $id = (int)$id;
        $item_id = (int)$item_id;
        return mysqli_query($connection, "DELETE FROM kp_recipe WHERE id='{$id}' AND item_id='{$item_id}'");
    }
}
