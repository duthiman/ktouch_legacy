<?php

if (!function_exists('trans_schema_boot')) {
    function trans_schema_boot($connection)
    {
        if (!$connection) {
            return;
        }

        mysqli_query(
            $connection,
            "CREATE TABLE IF NOT EXISTS kp_counter_trans_id (
                trans_id INT NOT NULL DEFAULT 0
            ) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4"
        );

        $has_id_row = (int)column_count('kp_counter_trans_id', 'trans_id', '1');
        if ($has_id_row < 1) {
            mysqli_query($connection, "INSERT INTO kp_counter_trans_id(trans_id) VALUES(0)");
        }
    }
}
