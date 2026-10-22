<?php

if (!function_exists('inventory_schema_table_exists')) {
    function inventory_schema_table_exists($table)
    {
        global $connection;

        $table = mysqli_real_escape_string($connection, (string)$table);
        $result = mysqli_query($connection, "SHOW TABLES LIKE '{$table}'");
        return $result && mysqli_num_rows($result) > 0;
    }
}

if (!function_exists('inventory_schema_add_item_column')) {
    function inventory_schema_add_item_column($column, $definition)
    {
        global $connection;

        if (!function_exists('inventory_repo_item_column_exists')) {
            return;
        }

        if (!inventory_repo_item_column_exists($column)) {
            mysqli_query($connection, "ALTER TABLE `kp_items` ADD COLUMN `{$column}` {$definition}");
        }
    }
}

if (!function_exists('inventory_ensure_counter_stock_schema')) {
    function inventory_ensure_counter_stock_schema()
    {
        if (!inventory_schema_table_exists('kp_items')) {
            return;
        }

        inventory_schema_add_item_column('cat_id', "INT(11) NOT NULL DEFAULT 0");
        inventory_schema_add_item_column('sub_id', "INT(11) NOT NULL DEFAULT 0");
        inventory_schema_add_item_column('dpt', "INT(11) NOT NULL DEFAULT 0");
        inventory_schema_add_item_column('stock', "INT(11) NOT NULL DEFAULT 2");
        inventory_schema_add_item_column('tot_status', "INT(11) NOT NULL DEFAULT 2");
        inventory_schema_add_item_column('b_price', "DECIMAL(12,2) NOT NULL DEFAULT 0");
    }
}
