<?php

if (!function_exists('view_schema_boot')) {
    function view_schema_boot($connection)
    {
        if (!$connection) {
            return;
        }

        if (function_exists('ensure_sales_parent_system')) {
            ensure_sales_parent_system();
        }

        if (function_exists('cash_ensure_merge_tables')) {
            cash_ensure_merge_tables();
        }

        if (function_exists('cash_ensure_merge_request_table')) {
            cash_ensure_merge_request_table();
        }

        // A waiter requests a void first.  Approval is the only operation that
        // moves the snapshot into the permanent void tables and removes the sale.
        $tables = [
            "CREATE TABLE IF NOT EXISTS voids_draf (
                draft_id INT NOT NULL AUTO_INCREMENT,
                sale_id INT NOT NULL,
                requested_by INT NOT NULL DEFAULT 0,
                requested_at DATETIME NOT NULL DEFAULT CURRENT_TIMESTAMP,
                shift VARCHAR(60) NOT NULL DEFAULT '',
                counter_id INT NOT NULL DEFAULT 0,
                amount DECIMAL(14,2) NOT NULL DEFAULT 0,
                reason VARCHAR(255) NOT NULL DEFAULT '',
                status TINYINT NOT NULL DEFAULT 1,
                approved_by INT NOT NULL DEFAULT 0,
                approved_at DATETIME NULL,
                updated_at DATETIME NULL,
                PRIMARY KEY (draft_id),
                KEY idx_void_draft_sale_status (sale_id, status),
                KEY idx_void_draft_status (status)
            ) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4",
            "CREATE TABLE IF NOT EXISTS voids_draf_items (
                id INT NOT NULL AUTO_INCREMENT,
                draft_id INT NOT NULL,
                sale_id INT NOT NULL,
                item_id INT NOT NULL DEFAULT 0,
                qty DECIMAL(14,3) NOT NULL DEFAULT 0,
                price DECIMAL(14,2) NOT NULL DEFAULT 0,
                total DECIMAL(14,2) NOT NULL DEFAULT 0,
                counter_id INT NOT NULL DEFAULT 0,
                stock_counter INT NOT NULL DEFAULT 0,
                item_name VARCHAR(255) NOT NULL DEFAULT '',
                attributes_json LONGTEXT NULL,
                PRIMARY KEY (id),
                KEY idx_void_draft_items_draft (draft_id),
                KEY idx_void_draft_items_sale (sale_id)
            ) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4",
            "CREATE TABLE IF NOT EXISTS kp_sale_voids (
                void_id INT NOT NULL AUTO_INCREMENT,
                sale_id INT NOT NULL,
                requested_by INT NOT NULL DEFAULT 0,
                approved_by INT NOT NULL DEFAULT 0,
                amount DECIMAL(14,2) NOT NULL DEFAULT 0,
                shift VARCHAR(60) NOT NULL DEFAULT '',
                counter_id INT NOT NULL DEFAULT 0,
                reason VARCHAR(255) NOT NULL DEFAULT '',
                created_at DATETIME NOT NULL DEFAULT CURRENT_TIMESTAMP,
                PRIMARY KEY (void_id),
                UNIQUE KEY uq_sale_void (sale_id),
                KEY idx_void_shift (shift)
            ) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4",
            "CREATE TABLE IF NOT EXISTS kp_sale_void_items (
                id INT NOT NULL AUTO_INCREMENT,
                void_id INT NOT NULL,
                sale_id INT NOT NULL,
                item_id INT NOT NULL DEFAULT 0,
                qty DECIMAL(14,3) NOT NULL DEFAULT 0,
                price DECIMAL(14,2) NOT NULL DEFAULT 0,
                total DECIMAL(14,2) NOT NULL DEFAULT 0,
                counter_id INT NOT NULL DEFAULT 0,
                stock_counter INT NOT NULL DEFAULT 0,
                item_name VARCHAR(255) NOT NULL DEFAULT '',
                attributes_json LONGTEXT NULL,
                PRIMARY KEY (id),
                KEY idx_sale_void_items_void (void_id),
                KEY idx_sale_void_items_sale (sale_id)
            ) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4",
        ];
        foreach ($tables as $sql) {
            @mysqli_query($connection, $sql);
        }
    }
}
