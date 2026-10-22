<?php

if (!function_exists('kp_schema_table_exists')) {
    function kp_schema_table_exists($connection, $table)
    {
        $table = mysqli_real_escape_string($connection, $table);
        $result = mysqli_query($connection, "SHOW TABLES LIKE '{$table}'");
        return $result && mysqli_num_rows($result) > 0;
    }
}

if (!function_exists('kp_schema_column_exists')) {
    function kp_schema_column_exists($connection, $table, $column)
    {
        $table = mysqli_real_escape_string($connection, $table);
        $column = mysqli_real_escape_string($connection, $column);
        $result = mysqli_query($connection, "SHOW COLUMNS FROM `{$table}` LIKE '{$column}'");
        return $result && mysqli_num_rows($result) > 0;
    }
}

if (!function_exists('kp_schema_index_exists')) {
    function kp_schema_index_exists($connection, $table, $index)
    {
        $table = mysqli_real_escape_string($connection, $table);
        $index = mysqli_real_escape_string($connection, $index);
        $result = mysqli_query($connection, "SHOW INDEX FROM `{$table}` WHERE Key_name='{$index}'");
        return $result && mysqli_num_rows($result) > 0;
    }
}

if (!function_exists('kp_schema_add_column')) {
    function kp_schema_add_column($connection, $table, $column, $definition)
    {
        if (!kp_schema_column_exists($connection, $table, $column)) {
            mysqli_query($connection, "ALTER TABLE `{$table}` ADD COLUMN `{$column}` {$definition}");
        }
    }
}

if (!function_exists('kp_schema_add_index')) {
    function kp_schema_add_index($connection, $table, $index, $definition)
    {
        if (!kp_schema_index_exists($connection, $table, $index)) {
            mysqli_query($connection, "ALTER TABLE `{$table}` ADD {$definition}");
        }
    }
}

if (!function_exists('kp_ensure_tot_schema')) {
    function kp_ensure_tot_schema($connection)
    {
        if (!$connection) {
            return;
        }

        mysqli_query($connection, "CREATE TABLE IF NOT EXISTS `kp_tot` (
            `id` INT(11) NOT NULL AUTO_INCREMENT,
            `tot_id` INT(11) NOT NULL DEFAULT 0,
            `user_id` INT(11) NOT NULL DEFAULT 0,
            `shift` VARCHAR(55) NOT NULL DEFAULT '',
            `counter` INT(11) NOT NULL DEFAULT 0,
            `created_at` TIMESTAMP NOT NULL DEFAULT CURRENT_TIMESTAMP,
            PRIMARY KEY (`id`)
        ) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci");

        mysqli_query($connection, "CREATE TABLE IF NOT EXISTS `kp_tot_items` (
            `id` INT(11) NOT NULL AUTO_INCREMENT,
            `tot_id` INT(11) NOT NULL DEFAULT 0,
            `user_id` INT(11) NOT NULL DEFAULT 0,
            `item_id` INT(11) NOT NULL DEFAULT 0,
            `tot_item_id` INT(11) NOT NULL DEFAULT 0,
            `tots` DECIMAL(12,2) NOT NULL DEFAULT 0,
            `shift` VARCHAR(55) NOT NULL DEFAULT '',
            `counter` INT(11) NOT NULL DEFAULT 0,
            `created_at` TIMESTAMP NOT NULL DEFAULT CURRENT_TIMESTAMP,
            PRIMARY KEY (`id`)
        ) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci");

        mysqli_query($connection, "CREATE TABLE IF NOT EXISTS `kp_tot_status` (
            `id` INT(11) NOT NULL AUTO_INCREMENT,
            `tot_id` INT(11) NOT NULL DEFAULT 0,
            `user_id` INT(11) NOT NULL DEFAULT 0,
            `tot_status` TINYINT(1) NOT NULL DEFAULT 2,
            `counter` INT(11) NOT NULL DEFAULT 0,
            `created_at` TIMESTAMP NOT NULL DEFAULT CURRENT_TIMESTAMP,
            PRIMARY KEY (`id`)
        ) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci");

        kp_schema_add_column($connection, 'kp_tot', 'tot_id', "INT(11) NOT NULL DEFAULT 0");
        kp_schema_add_column($connection, 'kp_tot', 'user_id', "INT(11) NOT NULL DEFAULT 0");
        kp_schema_add_column($connection, 'kp_tot', 'shift', "VARCHAR(55) NOT NULL DEFAULT ''");
        kp_schema_add_column($connection, 'kp_tot', 'counter', "INT(11) NOT NULL DEFAULT 0");
        kp_schema_add_column($connection, 'kp_tot', 'created_at', "TIMESTAMP NOT NULL DEFAULT CURRENT_TIMESTAMP");

        kp_schema_add_column($connection, 'kp_tot_items', 'tot_id', "INT(11) NOT NULL DEFAULT 0");
        kp_schema_add_column($connection, 'kp_tot_items', 'user_id', "INT(11) NOT NULL DEFAULT 0");
        kp_schema_add_column($connection, 'kp_tot_items', 'item_id', "INT(11) NOT NULL DEFAULT 0");
        kp_schema_add_column($connection, 'kp_tot_items', 'tot_item_id', "INT(11) NOT NULL DEFAULT 0");
        kp_schema_add_column($connection, 'kp_tot_items', 'tots', "DECIMAL(12,2) NOT NULL DEFAULT 0");
        kp_schema_add_column($connection, 'kp_tot_items', 'shift', "VARCHAR(55) NOT NULL DEFAULT ''");
        kp_schema_add_column($connection, 'kp_tot_items', 'counter', "INT(11) NOT NULL DEFAULT 0");
        kp_schema_add_column($connection, 'kp_tot_items', 'created_at', "TIMESTAMP NOT NULL DEFAULT CURRENT_TIMESTAMP");

        kp_schema_add_column($connection, 'kp_tot_status', 'tot_id', "INT(11) NOT NULL DEFAULT 0");
        kp_schema_add_column($connection, 'kp_tot_status', 'user_id', "INT(11) NOT NULL DEFAULT 0");
        kp_schema_add_column($connection, 'kp_tot_status', 'tot_status', "TINYINT(1) NOT NULL DEFAULT 2");
        kp_schema_add_column($connection, 'kp_tot_status', 'counter', "INT(11) NOT NULL DEFAULT 0");
        kp_schema_add_column($connection, 'kp_tot_status', 'created_at', "TIMESTAMP NOT NULL DEFAULT CURRENT_TIMESTAMP");

        mysqli_query($connection, "ALTER TABLE `kp_tot` MODIFY `shift` VARCHAR(55) NOT NULL DEFAULT ''");
        mysqli_query($connection, "ALTER TABLE `kp_tot_items` MODIFY `shift` VARCHAR(55) NOT NULL DEFAULT ''");
        mysqli_query($connection, "ALTER TABLE `kp_tot_items` MODIFY `tots` DECIMAL(12,2) NOT NULL DEFAULT 0");

        kp_schema_add_index($connection, 'kp_tot', 'idx_kp_tot_tot_id', "INDEX `idx_kp_tot_tot_id` (`tot_id`)");
        kp_schema_add_index($connection, 'kp_tot', 'idx_kp_tot_user_id', "INDEX `idx_kp_tot_user_id` (`user_id`)");
        kp_schema_add_index($connection, 'kp_tot_items', 'idx_kp_tot_items_tot_id', "INDEX `idx_kp_tot_items_tot_id` (`tot_id`)");
        kp_schema_add_index($connection, 'kp_tot_items', 'idx_kp_tot_items_item_id', "INDEX `idx_kp_tot_items_item_id` (`item_id`)");
        kp_schema_add_index($connection, 'kp_tot_status', 'idx_kp_tot_status_tot_id', "INDEX `idx_kp_tot_status_tot_id` (`tot_id`)");
    }
}

if (!function_exists('kp_tot_user_counter')) {
    function kp_tot_user_counter($user_id)
    {
        $user_id = (int)$user_id;
        if ($user_id < 1) {
            return 0;
        }

        return (int)get_column('kp_users', 'counter', "user_id='{$user_id}'");
    }
}

if (!function_exists('kp_tot_qty_column')) {
    function kp_tot_qty_column($counter_id)
    {
        $counter_id = (int)$counter_id;
        if ($counter_id < 1) {
            return '';
        }

        return 'cntr' . $counter_id . '_qty';
    }
}

if (isset($connection) && $connection) {
    kp_ensure_tot_schema($connection);
}
