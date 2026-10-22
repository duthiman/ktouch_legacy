<?php
function take_schema_ensure() {
    global $connection;
    mysqli_query($connection, "CREATE TABLE IF NOT EXISTS kp_store_take_status (id INT(11) NOT NULL AUTO_INCREMENT, status TINYINT(1) NOT NULL DEFAULT 1, created_at TIMESTAMP NOT NULL DEFAULT CURRENT_TIMESTAMP, PRIMARY KEY (id)) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4");
    mysqli_query($connection, "CREATE TABLE IF NOT EXISTS kp_store_stock_take (
        take_id INT(11) NOT NULL AUTO_INCREMENT,
        item_id INT(11) NOT NULL DEFAULT 0,
        user_id INT(11) NOT NULL DEFAULT 0,
        closing DECIMAL(14,3) NOT NULL DEFAULT 0,
        counted DECIMAL(14,3) NOT NULL DEFAULT 0,
        shift VARCHAR(80) NOT NULL DEFAULT '',
        created_at TIMESTAMP NOT NULL DEFAULT CURRENT_TIMESTAMP,
        PRIMARY KEY (take_id), KEY idx_take_shift (shift), KEY idx_take_item (item_id)
    ) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4");
}
