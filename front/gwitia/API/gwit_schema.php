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

if (!function_exists('kp_ensure_gwitia_schema')) {
    function kp_ensure_gwitia_schema($connection)
    {
        if (!$connection) {
            return;
        }

        mysqli_query($connection, "CREATE TABLE IF NOT EXISTS `kp_counter_req` (
            `id` INT(11) NOT NULL AUTO_INCREMENT,
            `req_id` INT(11) NOT NULL DEFAULT 0,
            `req_user` INT(11) NOT NULL DEFAULT 0,
            `req_shift` VARCHAR(55) NOT NULL DEFAULT '',
            `counter` VARCHAR(55) NOT NULL DEFAULT '',
            `print_cntr_id` INT(11) NOT NULL DEFAULT 0,
            `issue_status` TINYINT(1) NOT NULL DEFAULT 0,
            `rec_status` TINYINT(1) NOT NULL DEFAULT 0,
            `issued_by` INT(11) NOT NULL DEFAULT 0,
            `received_by` INT(11) NOT NULL DEFAULT 0,
            `created_at` TIMESTAMP NOT NULL DEFAULT CURRENT_TIMESTAMP,
            `issue_created_at` TIMESTAMP NULL DEFAULT NULL,
            `rec_created_at` TIMESTAMP NULL DEFAULT NULL,
            PRIMARY KEY (`id`)
        ) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci");

        mysqli_query($connection, "CREATE TABLE IF NOT EXISTS `kp_counter_req_items` (
            `id` INT(11) NOT NULL AUTO_INCREMENT,
            `req_id` INT(11) NOT NULL DEFAULT 0,
            `req_user` INT(11) NOT NULL DEFAULT 0,
            `item_id` INT(11) NOT NULL DEFAULT 0,
            `req_qty` DECIMAL(12,2) NOT NULL DEFAULT 0,
            `issue_qty` DECIMAL(12,2) NOT NULL DEFAULT 0,
            `rec_qty` DECIMAL(12,2) NOT NULL DEFAULT 0,
            `req_shift` VARCHAR(55) NOT NULL DEFAULT '',
            `counter` VARCHAR(55) NOT NULL DEFAULT '',
            `source_counter` VARCHAR(55) NOT NULL DEFAULT '',
            `created_at` TIMESTAMP NOT NULL DEFAULT CURRENT_TIMESTAMP,
            PRIMARY KEY (`id`)
        ) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci");

        mysqli_query($connection, "CREATE TABLE IF NOT EXISTS `kp_counter_req_status` (
            `id` INT(11) NOT NULL AUTO_INCREMENT,
            `req_id` INT(11) NOT NULL DEFAULT 0,
            `req_user` INT(11) NOT NULL DEFAULT 0,
            `counter` VARCHAR(55) NOT NULL DEFAULT '',
            `created_at` TIMESTAMP NOT NULL DEFAULT CURRENT_TIMESTAMP,
            PRIMARY KEY (`id`)
        ) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci");

        kp_schema_add_column($connection, 'kp_counter_req', 'req_id', "INT(11) NOT NULL DEFAULT 0");
        kp_schema_add_column($connection, 'kp_counter_req', 'req_user', "INT(11) NOT NULL DEFAULT 0");
        kp_schema_add_column($connection, 'kp_counter_req', 'req_shift', "VARCHAR(55) NOT NULL DEFAULT ''");
        kp_schema_add_column($connection, 'kp_counter_req', 'counter', "VARCHAR(55) NOT NULL DEFAULT ''");
        kp_schema_add_column($connection, 'kp_counter_req', 'print_cntr_id', "INT(11) NOT NULL DEFAULT 0");
        kp_schema_add_column($connection, 'kp_counter_req', 'issue_status', "TINYINT(1) NOT NULL DEFAULT 0");
        kp_schema_add_column($connection, 'kp_counter_req', 'rec_status', "TINYINT(1) NOT NULL DEFAULT 0");
        kp_schema_add_column($connection, 'kp_counter_req', 'issued_by', "INT(11) NOT NULL DEFAULT 0");
        kp_schema_add_column($connection, 'kp_counter_req', 'received_by', "INT(11) NOT NULL DEFAULT 0");
        kp_schema_add_column($connection, 'kp_counter_req', 'created_at', "TIMESTAMP NOT NULL DEFAULT CURRENT_TIMESTAMP");
        kp_schema_add_column($connection, 'kp_counter_req', 'issue_created_at', "TIMESTAMP NULL DEFAULT NULL");
        kp_schema_add_column($connection, 'kp_counter_req', 'rec_created_at', "TIMESTAMP NULL DEFAULT NULL");

        kp_schema_add_column($connection, 'kp_counter_req_items', 'req_id', "INT(11) NOT NULL DEFAULT 0");
        kp_schema_add_column($connection, 'kp_counter_req_items', 'req_user', "INT(11) NOT NULL DEFAULT 0");
        kp_schema_add_column($connection, 'kp_counter_req_items', 'item_id', "INT(11) NOT NULL DEFAULT 0");
        kp_schema_add_column($connection, 'kp_counter_req_items', 'req_qty', "DECIMAL(12,2) NOT NULL DEFAULT 0");
        kp_schema_add_column($connection, 'kp_counter_req_items', 'issue_qty', "DECIMAL(12,2) NOT NULL DEFAULT 0");
        kp_schema_add_column($connection, 'kp_counter_req_items', 'rec_qty', "DECIMAL(12,2) NOT NULL DEFAULT 0");
        kp_schema_add_column($connection, 'kp_counter_req_items', 'req_shift', "VARCHAR(55) NOT NULL DEFAULT ''");
        kp_schema_add_column($connection, 'kp_counter_req_items', 'counter', "VARCHAR(55) NOT NULL DEFAULT ''");
        kp_schema_add_column($connection, 'kp_counter_req_items', 'source_counter', "VARCHAR(55) NOT NULL DEFAULT ''");
        kp_schema_add_column($connection, 'kp_counter_req_items', 'created_at', "TIMESTAMP NOT NULL DEFAULT CURRENT_TIMESTAMP");

        kp_schema_add_column($connection, 'kp_counter_req_status', 'req_id', "INT(11) NOT NULL DEFAULT 0");
        kp_schema_add_column($connection, 'kp_counter_req_status', 'req_user', "INT(11) NOT NULL DEFAULT 0");
        kp_schema_add_column($connection, 'kp_counter_req_status', 'counter', "VARCHAR(55) NOT NULL DEFAULT ''");
        kp_schema_add_column($connection, 'kp_counter_req_status', 'created_at', "TIMESTAMP NOT NULL DEFAULT CURRENT_TIMESTAMP");

        mysqli_query($connection, "ALTER TABLE `kp_counter_req` MODIFY `counter` VARCHAR(55) NOT NULL DEFAULT ''");
        mysqli_query($connection, "ALTER TABLE `kp_counter_req` MODIFY `req_shift` VARCHAR(55) NOT NULL DEFAULT ''");
        mysqli_query($connection, "ALTER TABLE `kp_counter_req_items` MODIFY `counter` VARCHAR(55) NOT NULL DEFAULT ''");
        mysqli_query($connection, "ALTER TABLE `kp_counter_req_items` MODIFY `source_counter` VARCHAR(55) NOT NULL DEFAULT ''");
        mysqli_query($connection, "ALTER TABLE `kp_counter_req_items` MODIFY `req_shift` VARCHAR(55) NOT NULL DEFAULT ''");
        mysqli_query($connection, "ALTER TABLE `kp_counter_req_items` MODIFY `req_qty` DECIMAL(12,2) NOT NULL DEFAULT 0");
        mysqli_query($connection, "ALTER TABLE `kp_counter_req_items` MODIFY `issue_qty` DECIMAL(12,2) NOT NULL DEFAULT 0");
        mysqli_query($connection, "ALTER TABLE `kp_counter_req_items` MODIFY `rec_qty` DECIMAL(12,2) NOT NULL DEFAULT 0");
        mysqli_query($connection, "ALTER TABLE `kp_counter_req_status` MODIFY `counter` VARCHAR(55) NOT NULL DEFAULT ''");

        kp_schema_add_index($connection, 'kp_counter_req', 'idx_kp_counter_req_req_id', "INDEX `idx_kp_counter_req_req_id` (`req_id`)");
        kp_schema_add_index($connection, 'kp_counter_req', 'idx_kp_counter_req_req_user', "INDEX `idx_kp_counter_req_req_user` (`req_user`)");
        kp_schema_add_index($connection, 'kp_counter_req_items', 'idx_kp_counter_req_items_req_id', "INDEX `idx_kp_counter_req_items_req_id` (`req_id`)");
        kp_schema_add_index($connection, 'kp_counter_req_items', 'idx_kp_counter_req_items_item_id', "INDEX `idx_kp_counter_req_items_item_id` (`item_id`)");
        kp_schema_add_index($connection, 'kp_counter_req_status', 'idx_kp_counter_req_status_req_id', "INDEX `idx_kp_counter_req_status_req_id` (`req_id`)");
    }
}

if (!function_exists('gwit_schema_boot')) {
    function gwit_auto_request_enabled($connection)
    {
        mysqli_query($connection, "CREATE TABLE IF NOT EXISTS kp_settings_gwitia (settings_id INT NOT NULL PRIMARY KEY, auto_request_enabled TINYINT(1) NOT NULL DEFAULT 1, created_at TIMESTAMP NOT NULL DEFAULT CURRENT_TIMESTAMP) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4");
        $row = mysqli_query($connection, "SELECT auto_request_enabled FROM kp_settings_gwitia WHERE settings_id=1 LIMIT 1");
        if (!$row || mysqli_num_rows($row) < 1) {
            mysqli_query($connection, "INSERT INTO kp_settings_gwitia(settings_id, auto_request_enabled) VALUES(1,1)");
            return true;
        }
        $data = mysqli_fetch_assoc($row);
        return (int)($data['auto_request_enabled'] ?? 1) === 1;
    }

    function gwit_schema_boot($connection)
    {
        if (!$connection) {
            return;
        }

        kp_ensure_gwitia_schema($connection);
    }
}
