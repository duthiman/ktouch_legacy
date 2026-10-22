<?php

if (!function_exists('attr_schema_ready')) {
    function attr_schema_ready()
    {
        global $connection;
        if (!$connection) {
            return false;
        }

        mysqli_query($connection, "CREATE TABLE IF NOT EXISTS kp_items_attributes (
            id INT NOT NULL AUTO_INCREMENT,
            name VARCHAR(255) NOT NULL,
            affects_price TINYINT(1) NOT NULL DEFAULT 0,
            adds_new_item TINYINT(1) NOT NULL DEFAULT 2,
            price_change DECIMAL(12,2) NOT NULL DEFAULT 0.00,
            linked_items TEXT NULL,
            status TINYINT(1) NOT NULL DEFAULT 1,
            PRIMARY KEY (id),
            KEY idx_attribute_name (name),
            KEY idx_attribute_status (status)
        ) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4");

        mysqli_query($connection, "CREATE TABLE IF NOT EXISTS kp_item_attribute_mapping (
            id INT NOT NULL AUTO_INCREMENT,
            item_id INT NOT NULL,
            attr_id INT NOT NULL,
            PRIMARY KEY (id),
            UNIQUE KEY uniq_item_attribute (item_id, attr_id),
            KEY idx_mapping_attribute (attr_id),
            KEY idx_mapping_item (item_id)
        ) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4");

        $attribute_columns = [
            'name' => "VARCHAR(255) NOT NULL DEFAULT ''",
            'affects_price' => "TINYINT(1) NOT NULL DEFAULT 0",
            'adds_new_item' => "TINYINT(1) NOT NULL DEFAULT 2",
            'price_change' => "DECIMAL(12,2) NOT NULL DEFAULT 0.00",
            'linked_items' => "TEXT NULL",
            'status' => "TINYINT(1) NOT NULL DEFAULT 1",
        ];
        foreach ($attribute_columns as $column => $definition) {
            $check = mysqli_query($connection, "SHOW COLUMNS FROM kp_items_attributes LIKE '" . $column . "'");
            if ($check && mysqli_num_rows($check) === 0) {
                mysqli_query($connection, "ALTER TABLE kp_items_attributes ADD COLUMN `" . $column . "` " . $definition);
            }
        }

        $mapping_columns = [
            'item_id' => "INT NOT NULL DEFAULT 0",
            'attr_id' => "INT NOT NULL DEFAULT 0",
        ];
        foreach ($mapping_columns as $column => $definition) {
            $check = mysqli_query($connection, "SHOW COLUMNS FROM kp_item_attribute_mapping LIKE '" . $column . "'");
            if ($check && mysqli_num_rows($check) === 0) {
                mysqli_query($connection, "ALTER TABLE kp_item_attribute_mapping ADD COLUMN `" . $column . "` " . $definition);
            }
        }

        return true;
    }
}
