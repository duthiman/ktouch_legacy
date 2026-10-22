<?php

if (!function_exists('lpo_api_ensure_schema')) {
    function lpo_api_ensure_schema($connection)
    {
        // Older installations may have a minimal kp_po_status table.
        $columns = [
            'po_user_id' => 'INT NOT NULL DEFAULT 0',
            'sup_id' => 'INT NOT NULL DEFAULT 0',
            'potype' => 'TINYINT NOT NULL DEFAULT 2',
            'poby' => 'INT NOT NULL DEFAULT 5',
            'po_day' => 'DATE NULL',
            'po_status' => 'TINYINT NOT NULL DEFAULT 2',
        ];
        foreach ($columns as $name => $definition) {
            $column_check = mysqli_query($connection, "SHOW COLUMNS FROM kp_po_status LIKE '" . $name . "'");
            if ($column_check && mysqli_num_rows($column_check) === 0) {
                mysqli_query($connection, "ALTER TABLE kp_po_status ADD COLUMN `{$name}` {$definition}");
            }
        }
        return [
            'ok' => true,
            'message' => 'LPO schema is ready.',
        ];
    }
}
