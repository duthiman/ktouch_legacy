<?php

if (!function_exists('take_ensure_item_stock_take_system')) {
    function take_ensure_item_stock_take_system()
    {
        global $connection;

        $item_table_chk = mysqli_query($connection, "SHOW TABLES LIKE 'kp_items'");
        if (!$item_table_chk || mysqli_num_rows($item_table_chk) === 0) {
            return;
        }

        $columns = array(
            'stock_take_type' => "ALTER TABLE kp_items ADD COLUMN stock_take_type TINYINT(1) NOT NULL DEFAULT 2",
            'empty_bottle_weight' => "ALTER TABLE kp_items ADD COLUMN empty_bottle_weight DECIMAL(10,2) NOT NULL DEFAULT 0",
            'full_bottle_weight' => "ALTER TABLE kp_items ADD COLUMN full_bottle_weight DECIMAL(10,2) NOT NULL DEFAULT 0",
            'bottle_mls' => "ALTER TABLE kp_items ADD COLUMN bottle_mls DECIMAL(10,2) NOT NULL DEFAULT 0",
            'tot_size' => "ALTER TABLE kp_items ADD COLUMN tot_size DECIMAL(10,2) NOT NULL DEFAULT 30"
        );

        foreach ($columns as $column => $statement) {
            $col_chk = mysqli_query($connection, "SHOW COLUMNS FROM kp_items LIKE '$column'");
            if ($col_chk && mysqli_num_rows($col_chk) === 0) {
                mysqli_query($connection, $statement);
            }
        }
    }
}

if (!function_exists('take_user_counter_status')) {
    function take_user_counter_status($user_id)
    {
        global $connection;

        $user_id = (int)$user_id;
        if ($user_id < 1) {
            return 0;
        }

        $counter = (int)get_column('kp_users', 'counter', "user_id='{$user_id}'");
        if ($counter < 1) {
            return 0;
        }

        return (int)get_column('kp_counters', 'take_status', "cntr_id='{$counter}'");
    }
}
