<?php
$kug_gen_funcs = dirname(__DIR__, 2) . DIRECTORY_SEPARATOR . 'inc' . DIRECTORY_SEPARATOR . 'gen_funcs.php';
if (is_file($kug_gen_funcs)) {
    include_once($kug_gen_funcs);
}

if (function_exists('ensure_kug_purchase_system')) {
    ensure_kug_purchase_system();
}

if (!function_exists('kp_ensure_purchase_settings')) {
    function kp_ensure_purchase_settings($connection)
    {
        if (!$connection) {
            return;
        }
        mysqli_query($connection, "CREATE TABLE IF NOT EXISTS kp_settings_purchase (
            settings_id INT NOT NULL AUTO_INCREMENT,
            front_purchase_enabled TINYINT(1) NOT NULL DEFAULT 1,
            front_auto_stock_enabled TINYINT(1) NOT NULL DEFAULT 1,
            auto_request_default_applied TINYINT(1) NOT NULL DEFAULT 0,
            created_at TIMESTAMP NOT NULL DEFAULT CURRENT_TIMESTAMP,
            PRIMARY KEY (settings_id)
        ) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci");

        $colMap = [
            'front_purchase_enabled' => "ALTER TABLE kp_settings_purchase ADD COLUMN front_purchase_enabled TINYINT(1) NOT NULL DEFAULT 1",
            'front_auto_stock_enabled' => "ALTER TABLE kp_settings_purchase ADD COLUMN front_auto_stock_enabled TINYINT(1) NOT NULL DEFAULT 1",
            'auto_request_default_applied' => "ALTER TABLE kp_settings_purchase ADD COLUMN auto_request_default_applied TINYINT(1) NOT NULL DEFAULT 0",
            'created_at' => "ALTER TABLE kp_settings_purchase ADD COLUMN created_at TIMESTAMP NOT NULL DEFAULT CURRENT_TIMESTAMP",
        ];
        foreach ($colMap as $column => $sql) {
            $check = mysqli_query($connection, "SHOW COLUMNS FROM kp_settings_purchase LIKE '$column'");
            if ($check && mysqli_num_rows($check) === 0) {
                mysqli_query($connection, $sql);
            }
        }

        $exists = mysqli_query($connection, "SELECT settings_id FROM kp_settings_purchase WHERE settings_id=1 LIMIT 1");
        if (!$exists || mysqli_num_rows($exists) < 1) {
            mysqli_query($connection, "INSERT INTO kp_settings_purchase(settings_id, front_purchase_enabled, front_auto_stock_enabled, auto_request_default_applied) VALUES(1,1,1,1)");
        } else {
            $migration = mysqli_query($connection, "SELECT auto_request_default_applied FROM kp_settings_purchase WHERE settings_id=1 LIMIT 1");
            $migrationRow = $migration ? mysqli_fetch_assoc($migration) : [];
            if ((int)($migrationRow['auto_request_default_applied'] ?? 0) !== 1) {
                mysqli_query($connection, "UPDATE kp_settings_purchase SET front_auto_stock_enabled=1, auto_request_default_applied=1 WHERE settings_id=1");
            }
        }
    }
}

if (!function_exists('kp_ensure_purchase_po_columns')) {
    function kp_ensure_purchase_po_columns($connection)
    {
        if (!$connection) {
            return;
        }
        $columnMap = [
            'appr_status' => "ALTER TABLE kp_po ADD COLUMN appr_status TINYINT(1) NOT NULL DEFAULT 2",
            'appr_user' => "ALTER TABLE kp_po ADD COLUMN appr_user INT NOT NULL DEFAULT 0",
            'appr_datime' => "ALTER TABLE kp_po ADD COLUMN appr_datime DATETIME NULL DEFAULT NULL",
            'rec_datime' => "ALTER TABLE kp_po ADD COLUMN rec_datime DATETIME NULL DEFAULT NULL",
        ];
        foreach ($columnMap as $column => $sql) {
            $check = mysqli_query($connection, "SHOW COLUMNS FROM kp_po LIKE '$column'");
            if ($check && mysqli_num_rows($check) === 0) {
                mysqli_query($connection, $sql);
            }
        }
    }
}

if (!function_exists('kp_purchase_settings')) {
    function kp_purchase_settings($connection)
    {
        kp_ensure_purchase_settings($connection);
        kp_ensure_purchase_po_columns($connection);
        $defaults = [
            'front_purchase_enabled' => 1,
            'front_auto_stock_enabled' => 1,
        ];
        $q = mysqli_query($connection, "SELECT * FROM kp_settings_purchase WHERE settings_id=1 LIMIT 1");
        if ($q && mysqli_num_rows($q) > 0) {
            $row = mysqli_fetch_assoc($q);
            return [
                'front_purchase_enabled' => (int)($row['front_purchase_enabled'] ?? 1),
                'front_auto_stock_enabled' => (int)($row['front_auto_stock_enabled'] ?? 1),
            ];
        }
        return $defaults;
    }
}

if (!function_exists('kp_purchase_setting')) {
    function kp_purchase_setting($connection, $key, $default = 0)
    {
        $settings = kp_purchase_settings($connection);
        return array_key_exists($key, $settings) ? $settings[$key] : $default;
    }
}

if (isset($connection) && $connection) {
    kp_ensure_purchase_settings($connection);
    kp_ensure_purchase_po_columns($connection);
}

if (!function_exists('kp_items_column_exists')) {
    function kp_items_column_exists($connection, $column)
    {
        $column = mysqli_real_escape_string($connection, $column);
        $check = mysqli_query($connection, "SHOW COLUMNS FROM kp_items LIKE '$column'");
        return $check && mysqli_num_rows($check) > 0;
    }
}

if (!function_exists('kp_ensure_counter_stock_column')) {
    function kp_ensure_counter_stock_column($connection, $counter)
    {
        $counter = (int)$counter;
        if (!$connection || $counter < 1) {
            return false;
        }
        $column = 'cntr' . $counter . '_qty';
        if (kp_items_column_exists($connection, $column)) {
            return true;
        }
        $safeColumn = preg_replace('/[^a-zA-Z0-9_]/', '', $column);
        mysqli_query($connection, "ALTER TABLE kp_items ADD COLUMN `{$safeColumn}` DECIMAL(14,4) NOT NULL DEFAULT 0");
        return kp_items_column_exists($connection, $column);
    }
}

if (!function_exists('kp_purchase_stock_column')) {
    function kp_purchase_stock_column($connection, $counter = 0, $useCounterStock = false)
    {
        $counter = (int)$counter;
        if ($useCounterStock && $counter > 0) {
            $counterColumn = 'cntr' . $counter . '_qty';
            if (kp_items_column_exists($connection, $counterColumn)) {
                return $counterColumn;
            }
        }
        return 'qty';
    }
}

function po_total($po_id){
    global $connection;
    $total_po = mysqli_query($connection,"SELECT SUM(total) AS gtotal FROM kp_po_items WHERE po_id='$po_id'") or die(mysqli_error($connection));
    $row = mysqli_fetch_assoc($total_po);
    return $row['gtotal'] ?: 0;
}
?>
