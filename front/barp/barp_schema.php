<?php

if (!function_exists('barp_ensure_change_schema')) {
    function barp_ensure_change_schema(mysqli $connection): void
    {
        mysqli_query($connection, "CREATE TABLE IF NOT EXISTS kp_sale_changes (
            change_batch_id INT NOT NULL AUTO_INCREMENT,
            original_sale_id INT NOT NULL,
            change_sale_id INT NOT NULL DEFAULT 0,
            user_id INT NOT NULL DEFAULT 0,
            status TINYINT(1) NOT NULL DEFAULT 2,
            created_at DATETIME NOT NULL DEFAULT CURRENT_TIMESTAMP,
            completed_at DATETIME DEFAULT NULL,
            PRIMARY KEY (change_batch_id),
            KEY idx_change_user_status (user_id, status),
            KEY idx_original_sale (original_sale_id)
        ) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4");

        mysqli_query($connection, "CREATE TABLE IF NOT EXISTS kp_sale_items_changes (
            change_id INT NOT NULL AUTO_INCREMENT,
            sale_id INT NOT NULL,
            change_batch_id INT NOT NULL DEFAULT 0,
            change_sale_id INT NOT NULL DEFAULT 0,
            old_item_id INT NOT NULL,
            new_item_id INT DEFAULT NULL,
            qty DECIMAL(12,3) NOT NULL DEFAULT 0.000,
            user_id INT NOT NULL DEFAULT 0,
            shift VARCHAR(60) NOT NULL DEFAULT '',
            status TINYINT(1) NOT NULL DEFAULT 2,
            created_at DATETIME NOT NULL DEFAULT CURRENT_TIMESTAMP,
            updated_at DATETIME DEFAULT NULL,
            PRIMARY KEY (change_id),
            KEY idx_sale_status (sale_id, status),
            KEY idx_change_batch (change_batch_id)
        ) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4");
    }
}
