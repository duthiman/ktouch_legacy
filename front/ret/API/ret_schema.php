<?php

if (!function_exists('kp_return_ensure_settings_table')) {
    function kp_return_ensure_settings_table($connection)
    {
        mysqli_query(
            $connection,
            "CREATE TABLE IF NOT EXISTS kp_settings_return (
                settings_id INT NOT NULL AUTO_INCREMENT,
                approval_mode TINYINT(1) NOT NULL DEFAULT 1,
                PRIMARY KEY (settings_id)
            ) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4"
        );

        $hasRow = (int)column_count('kp_settings_return', 'settings_id', '1');
        if ($hasRow < 1) {
            mysqli_query($connection, "INSERT INTO kp_settings_return(settings_id, approval_mode) VALUES(1, 1)");
        }
    }
}

if (!function_exists('kp_return_ensure_schema')) {
    function kp_return_ensure_schema($connection)
    {
        kp_return_ensure_settings_table($connection);

        $columns = [
            'raise_datime' => "ALTER TABLE kp_ret_items ADD COLUMN raise_datime DATETIME NOT NULL DEFAULT CURRENT_TIMESTAMP",
            'cashier_status' => "ALTER TABLE kp_ret_items ADD COLUMN cashier_status TINYINT(1) NOT NULL DEFAULT 2",
            'counter_status' => "ALTER TABLE kp_ret_items ADD COLUMN counter_status TINYINT(1) NOT NULL DEFAULT 2",
            'cashier_approve_datime' => "ALTER TABLE kp_ret_items ADD COLUMN cashier_approve_datime DATETIME NULL DEFAULT NULL",
            'counter_approve_datime' => "ALTER TABLE kp_ret_items ADD COLUMN counter_approve_datime DATETIME NULL DEFAULT NULL",
        ];

        foreach ($columns as $column => $sql) {
            $chk = mysqli_query($connection, "SHOW COLUMNS FROM kp_ret_items LIKE '$column'");
            if ($chk && mysqli_num_rows($chk) === 0) {
                mysqli_query($connection, $sql);
            }
        }

        mysqli_query($connection, "UPDATE kp_ret_items SET cashier_status=2 WHERE cashier_status IS NULL OR cashier_status=0");
        mysqli_query($connection, "UPDATE kp_ret_items SET counter_status=2 WHERE counter_status IS NULL OR counter_status=0");
        mysqli_query($connection, "UPDATE kp_ret_items SET raise_datime=COALESCE(raise_datime, NOW())");
    }
}

if (!function_exists('kp_return_settings')) {
    function kp_return_settings($connection)
    {
        kp_return_ensure_settings_table($connection);
        $row = mysqli_fetch_assoc(mysqli_query($connection, "SELECT * FROM kp_settings_return WHERE settings_id=1 LIMIT 1")) ?: [];

        return [
            'approval_mode' => (int)($row['approval_mode'] ?? 1),
        ];
    }
}

if (!function_exists('kp_return_requires_cashier_approval')) {
    function kp_return_requires_cashier_approval($connection)
    {
        $settings = kp_return_settings($connection);
        return (int)($settings['approval_mode'] ?? 1) === 2;
    }
}

if (!function_exists('kp_return_mark_sale_void_if_full')) {
    function kp_return_mark_sale_void_if_full($connection, $sale_id)
    {
        $sale_id = (int)$sale_id;
        if ($sale_id < 1) {
            return;
        }

        $remaining = (int)column_count('kp_sale_items', 'item_id', "sale_id='$sale_id'");
        if ($remaining > 0) {
            return;
        }

        $voidColumns = [];
        $voidColsQuery = mysqli_query(
            $connection,
            "SELECT COLUMN_NAME
             FROM information_schema.COLUMNS
             WHERE TABLE_SCHEMA = DATABASE()
               AND TABLE_NAME = 'kp_sales'
               AND COLUMN_NAME IN ('void_status', 'voided', 'is_void')"
        );

        if ($voidColsQuery) {
            while ($row = mysqli_fetch_assoc($voidColsQuery)) {
                $voidColumns[] = $row['COLUMN_NAME'];
            }
        }

        foreach ($voidColumns as $column) {
            mysqli_query($connection, "UPDATE kp_sales SET `$column`=1 WHERE sale_id='$sale_id'");
        }

        mysqli_query($connection, "UPDATE kp_sales SET amount=0 WHERE sale_id='$sale_id'");
    }
}

if (!function_exists('ret_schema_boot')) {
    function ret_schema_boot($connection)
    {
        if (!$connection) {
            return;
        }

        kp_return_ensure_schema($connection);
    }
}
