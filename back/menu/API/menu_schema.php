<?php

if (!function_exists('menu_ensure_item_stock_take_system')) {
    function menu_ensure_item_stock_take_system()
    {
        if (function_exists('ensure_item_stock_take_system')) {
            ensure_item_stock_take_system();
        }
    }
}

if (!function_exists('menu_ensure_archive_table_columns')) {
    function menu_ensure_archive_table_columns($table1, $table2)
    {
        global $connection;

        $cols1 = [];
        $cols2 = [];

        $q1 = mysqli_query($connection, "SHOW COLUMNS FROM `$table1`");
        if (!$q1) {
            return;
        }
        while ($r = mysqli_fetch_assoc($q1)) {
            $cols1[$r['Field']] = $r;
        }

        $q2 = mysqli_query($connection, "SHOW COLUMNS FROM `$table2`");
        if (!$q2) {
            return;
        }
        while ($r = mysqli_fetch_assoc($q2)) {
            $cols2[$r['Field']] = $r;
        }

        foreach ($cols1 as $field => $info) {
            if (!isset($cols2[$field])) {
                $type = $info['Type'];
                $null = $info['Null'] === 'NO' ? 'NOT NULL' : 'NULL';
                $default = $info['Default'] !== null ? "DEFAULT '" . mysqli_real_escape_string($connection, $info['Default']) . "'" : '';
                $extra = $info['Extra'];
                mysqli_query($connection, "ALTER TABLE `$table2` ADD `$field` $type $null $default $extra");
            }
        }
    }
}

if (!function_exists('menu_ensure_item_tables')) {
    function menu_ensure_item_tables()
    {
        global $connection;

        $tables = ['kp_items', 'kp_items_arch'];
        $columns = [
            'item_id' => "INT NOT NULL AUTO_INCREMENT",
            'bname' => "VARCHAR(55) NOT NULL DEFAULT ''",
            'name' => "VARCHAR(120) NOT NULL DEFAULT ''",
            'cat_id' => "INT NOT NULL DEFAULT 0",
            'sub_id' => "INT NOT NULL DEFAULT 0",
            'dpt' => "INT NOT NULL DEFAULT 0",
            'buying' => "INT NOT NULL DEFAULT 0",
            'b_price' => "INT NOT NULL DEFAULT 0",
            'price' => "INT NOT NULL DEFAULT 0",
            'hhr' => "INT NOT NULL DEFAULT 0",
            'tax_id' => "INT NOT NULL DEFAULT 0",
            'menu' => "INT NOT NULL DEFAULT 0",
            'stock' => "INT NOT NULL DEFAULT 0",
            'recipe' => "INT NOT NULL DEFAULT 0",
            'port_status' => "INT NOT NULL DEFAULT 0",
            'portions' => "VARCHAR(255) NOT NULL DEFAULT ''",
            'tot_status' => "INT NOT NULL DEFAULT 0",
            'tot_num' => "DECIMAL(10,2) NOT NULL DEFAULT 0",
            'tot_item_id' => "INT NOT NULL DEFAULT 0",
            'stock_take_type' => "INT NOT NULL DEFAULT 2",
            'empty_bottle_weight' => "DECIMAL(10,2) NOT NULL DEFAULT 0",
            'full_bottle_weight' => "DECIMAL(10,2) NOT NULL DEFAULT 0",
            'bottle_mls' => "DECIMAL(10,2) NOT NULL DEFAULT 0",
            'tot_size' => "DECIMAL(10,2) NOT NULL DEFAULT 30",
            'deci' => "DECIMAL(10,2) NOT NULL DEFAULT 0",
            'butchery_enabled' => "TINYINT(1) NOT NULL DEFAULT 0",
        ];

        foreach ($tables as $table) {
            mysqli_query($connection, "CREATE TABLE IF NOT EXISTS `$table` (`item_id` INT NOT NULL AUTO_INCREMENT, PRIMARY KEY (`item_id`))");

            foreach ($columns as $column => $definition) {
                $result = mysqli_query($connection, "SHOW COLUMNS FROM `$table` LIKE '$column'");
                if ($result && mysqli_num_rows($result) < 1) {
                    mysqli_query($connection, "ALTER TABLE `$table` ADD COLUMN `$column` $definition");
                }
            }
        }

        mysqli_query($connection, "CREATE TABLE IF NOT EXISTS kp_items_uom (uom_id INT NOT NULL AUTO_INCREMENT, uom_name VARCHAR(80) NOT NULL, is_active TINYINT(1) NOT NULL DEFAULT 1, PRIMARY KEY (uom_id), UNIQUE KEY uq_uom_name (uom_name))");
        foreach (['Units', 'Litres', 'Kilograms'] as $uom_name) {
            $safe_name = mysqli_real_escape_string($connection, $uom_name);
            mysqli_query($connection, "INSERT IGNORE INTO kp_items_uom (uom_name) VALUES ('{$safe_name}')");
        }
        $uom_column = mysqli_query($connection, "SHOW COLUMNS FROM kp_items LIKE 'uom_id'");
        if ($uom_column && mysqli_num_rows($uom_column) < 1) {
            mysqli_query($connection, "ALTER TABLE kp_items ADD COLUMN uom_id INT NOT NULL DEFAULT 0");
        }
    }
}
