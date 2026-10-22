<?php

if (!function_exists('attr_repo_fetch_attributes')) {
    function attr_repo_fetch_attributes()
    {
        global $connection;
        $rows = [];
        $result = mysqli_query($connection, "SELECT * FROM kp_items_attributes ORDER BY name ASC");
        if (!$result) {
            return $rows;
        }
        while ($row = mysqli_fetch_assoc($result)) {
            $rows[] = $row;
        }
        return $rows;
    }
}

if (!function_exists('attr_repo_attribute_exists_by_name')) {
    function attr_repo_attribute_exists_by_name($name)
    {
        global $connection;
        $safe_name = mysqli_real_escape_string($connection, strtolower((string)$name));
        $result = mysqli_query($connection, "SELECT id FROM kp_items_attributes WHERE LOWER(name)='{$safe_name}' LIMIT 1");
        return $result && mysqli_num_rows($result) > 0;
    }
}

if (!function_exists('attr_repo_insert_attribute')) {
    function attr_repo_insert_attribute($name, $affects_price, $price_change, $status = 1)
    {
        global $connection;
        $name = mysqli_real_escape_string($connection, (string)$name);
        $affects_price = (int)$affects_price;
        $price_change = (float)$price_change;
        $status = (int)$status;
        return mysqli_query($connection, "INSERT INTO kp_items_attributes (name, affects_price, adds_new_item, price_change, linked_items, status) VALUES ('{$name}', '{$affects_price}', 2, '{$price_change}', '', '{$status}')");
    }
}

if (!function_exists('attr_repo_fetch_mapped_items')) {
    function attr_repo_fetch_mapped_items($attr_id)
    {
        global $connection;
        $rows = [];
        $attr_id = (int)$attr_id;
        $sql = "SELECT i.item_id, i.name FROM kp_items i INNER JOIN kp_item_attribute_mapping m ON i.item_id = m.item_id WHERE m.attr_id = '{$attr_id}' ORDER BY i.name ASC";
        $result = mysqli_query($connection, $sql);
        if (!$result) {
            return $rows;
        }
        while ($row = mysqli_fetch_assoc($result)) {
            $rows[] = $row;
        }
        return $rows;
    }
}

if (!function_exists('attr_repo_mapping_exists')) {
    function attr_repo_mapping_exists($item_id, $attr_id)
    {
        global $connection;
        $item_id = (int)$item_id;
        $attr_id = (int)$attr_id;
        $result = mysqli_query($connection, "SELECT id FROM kp_item_attribute_mapping WHERE item_id='{$item_id}' AND attr_id='{$attr_id}' LIMIT 1");
        return $result && mysqli_num_rows($result) > 0;
    }
}

if (!function_exists('attr_repo_insert_mapping')) {
    function attr_repo_insert_mapping($item_id, $attr_id)
    {
        global $connection;
        return mysqli_query($connection, "INSERT INTO kp_item_attribute_mapping (item_id, attr_id) VALUES ('" . (int)$item_id . "', '" . (int)$attr_id . "')");
    }
}

if (!function_exists('attr_repo_delete_mapping')) {
    function attr_repo_delete_mapping($item_id, $attr_id)
    {
        global $connection;
        return mysqli_query($connection, "DELETE FROM kp_item_attribute_mapping WHERE item_id='" . (int)$item_id . "' AND attr_id='" . (int)$attr_id . "'");
    }
}

if (!function_exists('attr_repo_item_mapping_count')) {
    function attr_repo_item_mapping_count($item_id)
    {
        global $connection;
        $item_id = (int)$item_id;
        $result = mysqli_query($connection, "SELECT COUNT(*) AS mapping_count FROM kp_item_attribute_mapping WHERE item_id='{$item_id}'");
        $row = $result ? mysqli_fetch_assoc($result) : null;
        return $row ? (int)$row['mapping_count'] : 0;
    }
}

if (!function_exists('attr_repo_update_item_has_attributes')) {
    function attr_repo_update_item_has_attributes($item_id, $has_attributes)
    {
        global $connection;
        return mysqli_query($connection, "UPDATE kp_items SET has_attributes='" . (int)$has_attributes . "' WHERE item_id='" . (int)$item_id . "'");
    }
}
