<?php

if (!function_exists('counter_inventory_ensure_schema')) {
    function counter_inventory_ensure_schema()
    {
        global $connection;
        mysqli_query($connection, "CREATE TABLE IF NOT EXISTS kp_counter_items (
            id INT NOT NULL AUTO_INCREMENT,
            counter_id INT NOT NULL,
            item_id INT NOT NULL,
            qty DECIMAL(14,3) NOT NULL DEFAULT 0,
            reorder_level DECIMAL(14,3) NOT NULL DEFAULT 0,
            is_active TINYINT(1) NOT NULL DEFAULT 1,
            created_at TIMESTAMP NOT NULL DEFAULT CURRENT_TIMESTAMP,
            updated_at TIMESTAMP NOT NULL DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
            PRIMARY KEY (id),
            UNIQUE KEY uq_counter_item (counter_id,item_id),
            KEY idx_counter_active (counter_id,is_active),
            KEY idx_item (item_id)
        ) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4");
        if (!kp_counter_items_column_exists($connection, 'take_status')) {
            mysqli_query($connection, "ALTER TABLE kp_counter_items ADD COLUMN take_status TINYINT(1) NOT NULL DEFAULT 2 AFTER reorder_level");
        }

        $counters = mysqli_query($connection, "SHOW TABLES LIKE 'kp_counters'");
        $items = mysqli_query($connection, "SHOW TABLES LIKE 'kp_items'");
        if (!$counters || mysqli_num_rows($counters) < 1 || !$items || mysqli_num_rows($items) < 1) return;

        mysqli_query($connection, "INSERT IGNORE INTO kp_counter_items (counter_id,item_id,qty,reorder_level,is_active)
            SELECT c.cntr_id,i.item_id,0,0,1
            FROM kp_counters c
            INNER JOIN kp_items i ON i.dpt=c.dpt
            WHERE c.del_status=2 AND i.del_status=2 AND i.stock=1");
    }
}

if (!function_exists('kp_counter_items_column_exists')) {
    function kp_counter_items_column_exists($connection, $column)
    {
        $column = mysqli_real_escape_string($connection, (string)$column);
        $result = mysqli_query($connection, "SHOW COLUMNS FROM kp_counter_items LIKE '{$column}'");
        return $result && mysqli_num_rows($result) > 0;
    }
}
