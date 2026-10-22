<?php  
$filename = "../../inc/gen_funcs.php";
if (file_exists($filename)){
    include('../../inc/gen_funcs.php');
} elseif (file_exists("../inc/gen_funcs.php")){
    include('../inc/gen_funcs.php');
}

if (!function_exists('kp_schema_add_column')) {
    function kp_schema_add_column($connection, $table, $column, $definition)
    {
        $table = mysqli_real_escape_string($connection, $table);
        $column = mysqli_real_escape_string($connection, $column);
        $result = mysqli_query($connection, "SHOW COLUMNS FROM `{$table}` LIKE '{$column}'");
        if (!$result || mysqli_num_rows($result) === 0) {
            mysqli_query($connection, "ALTER TABLE `{$table}` ADD COLUMN `{$column}` {$definition}");
        }
    }
}

if (!function_exists('kp_ensure_trans_schema')) {
    function kp_ensure_trans_schema($connection)
    {
        if (!$connection) {
            return;
        }

        mysqli_query($connection, "CREATE TABLE IF NOT EXISTS kp_counter_trans_id (
            trans_id INT(11) NOT NULL DEFAULT 0
        ) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4");

        mysqli_query($connection, "CREATE TABLE IF NOT EXISTS kp_counter_trans_status (
            id INT(11) NOT NULL AUTO_INCREMENT,
            trans_id INT(11) NOT NULL DEFAULT 0,
            cntr_from INT(11) NOT NULL DEFAULT 0,
            cntr_to INT(11) NOT NULL DEFAULT 0,
            user_id INT(11) NOT NULL DEFAULT 0,
            created_at TIMESTAMP NOT NULL DEFAULT CURRENT_TIMESTAMP,
            PRIMARY KEY (id),
            INDEX idx_counter_trans_status_trans_id (trans_id)
        ) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4");

        mysqli_query($connection, "CREATE TABLE IF NOT EXISTS kp_counter_trans (
            id INT(11) NOT NULL AUTO_INCREMENT,
            trans_id INT(11) NOT NULL DEFAULT 0,
            cntr_from INT(11) NOT NULL DEFAULT 0,
            cntr_to INT(11) NOT NULL DEFAULT 0,
            user_from INT(11) NOT NULL DEFAULT 0,
            shift VARCHAR(55) NOT NULL DEFAULT '',
            created_at TIMESTAMP NOT NULL DEFAULT CURRENT_TIMESTAMP,
            PRIMARY KEY (id),
            INDEX idx_counter_trans_trans_id (trans_id)
        ) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4");

        mysqli_query($connection, "CREATE TABLE IF NOT EXISTS kp_counter_trans_items (
            id INT(11) NOT NULL AUTO_INCREMENT,
            trans_id INT(11) NOT NULL DEFAULT 0,
            item_id INT(11) NOT NULL DEFAULT 0,
            qty DECIMAL(12,2) NOT NULL DEFAULT 0,
            counter INT(11) NOT NULL DEFAULT 0,
            status TINYINT(1) NOT NULL DEFAULT 0,
            shift VARCHAR(55) NOT NULL DEFAULT '',
            dpt INT(11) NOT NULL DEFAULT 0,
            s2s VARCHAR(55) NOT NULL DEFAULT '',
            created_at TIMESTAMP NOT NULL DEFAULT CURRENT_TIMESTAMP,
            PRIMARY KEY (id),
            UNIQUE KEY ux_counter_trans_items_trans_item (trans_id, item_id),
            INDEX idx_counter_trans_items_trans_id (trans_id),
            INDEX idx_counter_trans_items_item_id (item_id)
        ) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4");

        kp_schema_add_column($connection, 'kp_counter_trans_items', 'status', 'TINYINT(1) NOT NULL DEFAULT 0');
        kp_schema_add_column($connection, 'kp_counter_trans_items', 'shift', "VARCHAR(55) NOT NULL DEFAULT ''");
        kp_schema_add_column($connection, 'kp_counter_trans_items', 'dpt', 'INT(11) NOT NULL DEFAULT 0');
        kp_schema_add_column($connection, 'kp_counter_trans_items', 's2s', "VARCHAR(55) NOT NULL DEFAULT ''");
        kp_schema_add_column($connection, 'kp_counter_trans_items', 'created_at', 'TIMESTAMP NOT NULL DEFAULT CURRENT_TIMESTAMP');
        kp_schema_add_column($connection, 'kp_counter_trans_status', 'user_id', 'INT(11) NOT NULL DEFAULT 0');
        kp_schema_add_column($connection, 'kp_counter_trans_status', 'created_at', 'TIMESTAMP NOT NULL DEFAULT CURRENT_TIMESTAMP');
        kp_schema_add_column($connection, 'kp_counter_trans', 'shift', "VARCHAR(55) NOT NULL DEFAULT ''");
        kp_schema_add_column($connection, 'kp_counter_trans', 'created_at', 'TIMESTAMP NOT NULL DEFAULT CURRENT_TIMESTAMP');
    }
}

if (isset($connection) && $connection) {
    kp_ensure_trans_schema($connection);
}
?>