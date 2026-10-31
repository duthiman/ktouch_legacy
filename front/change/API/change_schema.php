<?php
if (!function_exists('change_schema_boot')) {
    function change_schema_boot($connection)
    {
        mysqli_query($connection, "CREATE TABLE IF NOT EXISTS kp_sale_changes (
            change_batch_id INT NOT NULL AUTO_INCREMENT, original_sale_id INT NOT NULL,
            change_sale_id INT NOT NULL DEFAULT 0, user_id INT NOT NULL DEFAULT 0,
            status TINYINT(1) NOT NULL DEFAULT 2, created_at DATETIME NOT NULL DEFAULT CURRENT_TIMESTAMP,
            completed_at DATETIME NULL DEFAULT NULL, PRIMARY KEY (change_batch_id),
            KEY idx_change_user_status (user_id, status), KEY idx_original_sale (original_sale_id)
        ) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4");
        mysqli_query($connection, "CREATE TABLE IF NOT EXISTS kp_sale_items_changes (
            change_id INT NOT NULL AUTO_INCREMENT, sale_id INT NOT NULL,
            change_batch_id INT NOT NULL DEFAULT 0, change_sale_id INT NOT NULL DEFAULT 0,
            old_item_id INT NOT NULL, new_item_id INT NULL DEFAULT NULL,
            qty DECIMAL(12,3) NOT NULL DEFAULT 0, user_id INT NOT NULL DEFAULT 0,
            shift VARCHAR(60) NOT NULL DEFAULT '', status TINYINT(1) NOT NULL DEFAULT 2,
            created_at DATETIME NOT NULL DEFAULT CURRENT_TIMESTAMP,
            updated_at DATETIME NULL DEFAULT NULL, PRIMARY KEY (change_id),
            KEY idx_sale_status (sale_id, status), KEY idx_old_item (old_item_id), KEY idx_batch_status (change_batch_id, status)
        ) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4");
        foreach ([
            'change_batch_id' => "INT NOT NULL DEFAULT 0 AFTER sale_id",
            'change_sale_id' => "INT NOT NULL DEFAULT 0 AFTER change_batch_id",
        ] as $name => $definition) {
            $column = mysqli_query($connection, "SHOW COLUMNS FROM kp_sale_items_changes LIKE '" . $name . "'");
            if ($column && mysqli_num_rows($column) === 0) {
                mysqli_query($connection, "ALTER TABLE kp_sale_items_changes ADD COLUMN " . $name . " " . $definition);
            }
        }
        $column = mysqli_query($connection, "SHOW COLUMNS FROM kp_sale_status LIKE 'sale_type'");
        if ($column && mysqli_num_rows($column) === 0) {
            mysqli_query($connection, "ALTER TABLE kp_sale_status ADD COLUMN sale_type VARCHAR(20) NOT NULL DEFAULT 'normal'");
        }
    }
}
