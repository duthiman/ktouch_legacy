<?php

function kug_schema_column_exists(mysqli $connection, string $table, string $column): bool
{
    $table = preg_replace('/[^a-zA-Z0-9_]/', '', $table);
    $column = $connection->real_escape_string($column);
    $result = $connection->query("SHOW COLUMNS FROM `{$table}` LIKE '{$column}'");
    return $result && $result->num_rows > 0;
}

function kug_schema_add_column(mysqli $connection, string $table, string $column, string $definition): void
{
    $table = preg_replace('/[^a-zA-Z0-9_]/', '', $table);
    if (!kug_schema_column_exists($connection, $table, $column)) {
        if (!$connection->query("ALTER TABLE `{$table}` ADD COLUMN `{$column}` {$definition}")) {
            throw new RuntimeException($connection->error);
        }
    }
}

function kug_schema_ensure_decimal(mysqli $connection, string $table, string $column, string $definition = 'DECIMAL(10,2) NOT NULL DEFAULT 0'): void
{
    $table = preg_replace('/[^a-zA-Z0-9_]/', '', $table);
    $column = $connection->real_escape_string($column);
    $result = $connection->query("SHOW COLUMNS FROM `{$table}` LIKE '{$column}'");
    $field = $result ? $result->fetch_assoc() : null;
    if ($field && strtolower((string)$field['Type']) !== 'decimal(10,2)') {
        if (!$connection->query("ALTER TABLE `{$table}` MODIFY COLUMN `{$column}` {$definition}")) {
            throw new RuntimeException($connection->error);
        }
    }
}

function kug_schema_create_tables(mysqli $connection): void
{
    $tables = [
        "CREATE TABLE IF NOT EXISTS kp_po_status (
            id INT NOT NULL AUTO_INCREMENT, po_id INT NOT NULL DEFAULT 0,
            sup_id INT NOT NULL DEFAULT 0, po_day DATE NULL,
            po_status TINYINT NOT NULL DEFAULT 2, PRIMARY KEY (id),
            UNIQUE KEY uniq_po_status (po_id)
        ) ENGINE=InnoDB",
        "CREATE TABLE IF NOT EXISTS kp_po_items (
            id INT NOT NULL AUTO_INCREMENT, po_id INT NOT NULL DEFAULT 0,
            item_id INT NOT NULL DEFAULT 0, qty DECIMAL(12,2) NOT NULL DEFAULT 0,
            price DECIMAL(10,2) NOT NULL DEFAULT 0, total DECIMAL(12,2) NOT NULL DEFAULT 0,
            disc DECIMAL(12,2) NOT NULL DEFAULT 0, status TINYINT NOT NULL DEFAULT 2,
            s2s TINYINT NOT NULL DEFAULT 2, day DATE NULL, PRIMARY KEY (id),
            KEY idx_po_item (po_id, item_id)
        ) ENGINE=InnoDB",
        "CREATE TABLE IF NOT EXISTS kp_po (
            id INT NOT NULL AUTO_INCREMENT, po_id INT NOT NULL DEFAULT 0,
            sup_id INT NOT NULL DEFAULT 0, ord_user INT NOT NULL DEFAULT 0,
            po_date DATE NULL, amount DECIMAL(12,2) NOT NULL DEFAULT 0,
            rec_status TINYINT NOT NULL DEFAULT 2, stock_status TINYINT NOT NULL DEFAULT 2,
            s2s TINYINT NOT NULL DEFAULT 2, PRIMARY KEY (id), UNIQUE KEY uniq_po (po_id)
        ) ENGINE=InnoDB",
        "CREATE TABLE IF NOT EXISTS kp_items (
            item_id INT NOT NULL AUTO_INCREMENT, name VARCHAR(255) NOT NULL DEFAULT '',
            store_qty DECIMAL(12,2) NOT NULL DEFAULT 0, b_price DECIMAL(12,2) NOT NULL DEFAULT 0,
            PRIMARY KEY (item_id)
        ) ENGINE=InnoDB",
        "CREATE TABLE IF NOT EXISTS kp_grn_id (grn_id INT NOT NULL DEFAULT 0) ENGINE=InnoDB",
        "CREATE TABLE IF NOT EXISTS kp_grn (
            id INT NOT NULL AUTO_INCREMENT, grn_id INT NOT NULL DEFAULT 0,
            po_id INT NOT NULL DEFAULT 0, user_id INT NOT NULL DEFAULT 0,
            sup_id INT NOT NULL DEFAULT 0, amount DECIMAL(12,2) NOT NULL DEFAULT 0,
            paid DECIMAL(12,2) NOT NULL DEFAULT 0, bal DECIMAL(12,2) NOT NULL DEFAULT 0,
            status TINYINT NOT NULL DEFAULT 1, pay_day DATE NOT NULL, day DATE NOT NULL,
            datime TIMESTAMP NOT NULL DEFAULT CURRENT_TIMESTAMP, PRIMARY KEY (id),
            KEY idx_grn_po (po_id), KEY idx_grn_number (grn_id)
        ) ENGINE=InnoDB",
        "CREATE TABLE IF NOT EXISTS kp_grn_items (
            id INT NOT NULL AUTO_INCREMENT, grn_id INT NOT NULL DEFAULT 0,
            item_id INT NOT NULL DEFAULT 0, qty DECIMAL(12,2) NOT NULL DEFAULT 0,
            price DECIMAL(12,2) NOT NULL DEFAULT 0, total DECIMAL(12,2) NOT NULL DEFAULT 0,
            user_id INT NOT NULL DEFAULT 0, day DATE NOT NULL,
            s2s TINYINT NOT NULL DEFAULT 1, PRIMARY KEY (id),
            KEY idx_grn_item (grn_id, item_id)
        ) ENGINE=InnoDB",
        "CREATE TABLE IF NOT EXISTS kp_stock_track (
            id INT NOT NULL AUTO_INCREMENT, grn_id INT NOT NULL DEFAULT 0,
            item_id INT NOT NULL DEFAULT 0, po_id INT NOT NULL DEFAULT 0,
            user_id INT NOT NULL DEFAULT 0, qty DECIMAL(12,2) NOT NULL DEFAULT 0,
            status VARCHAR(30) NOT NULL DEFAULT 'IN', `trans` VARCHAR(155) NOT NULL DEFAULT 'GRN',
            day DATE NOT NULL, PRIMARY KEY (id), KEY idx_stock_item (item_id),
            KEY idx_stock_po (po_id), KEY idx_stock_grn (grn_id)
        ) ENGINE=InnoDB",
    ];

    foreach ($tables as $sql) {
        if (!$connection->query($sql)) {
            throw new RuntimeException($connection->error);
        }
    }
}

function kug_ensure_schema(mysqli $connection): void
{
    kug_schema_create_tables($connection);

    kug_schema_add_column($connection, 'kp_po_status', 'po_day', 'DATE NULL');
    kug_schema_add_column($connection, 'kp_po_status', 'po_status', 'TINYINT NOT NULL DEFAULT 2');
    kug_schema_add_column($connection, 'kp_po_status', 'sup_id', 'INT NOT NULL DEFAULT 0');
    kug_schema_add_column($connection, 'kp_po_items', 'status', 'TINYINT NOT NULL DEFAULT 2');
    kug_schema_add_column($connection, 'kp_po_items', 's2s', 'TINYINT NOT NULL DEFAULT 2');
    kug_schema_add_column($connection, 'kp_po_items', 'day', 'DATE NULL');
    kug_schema_ensure_decimal($connection, 'kp_po_items', 'price');
    kug_schema_add_column($connection, 'kp_items', 'store_qty', 'DECIMAL(12,2) NOT NULL DEFAULT 0');
    kug_schema_add_column($connection, 'kp_items', 'b_price', 'DECIMAL(12,2) NOT NULL DEFAULT 0');
    kug_schema_add_column($connection, 'kp_po', 'rec_status', 'TINYINT NOT NULL DEFAULT 2');
    kug_schema_add_column($connection, 'kp_po', 'stock_status', 'TINYINT NOT NULL DEFAULT 2');
    kug_schema_add_column($connection, 'kp_po', 's2s', 'TINYINT NOT NULL DEFAULT 2');
    kug_schema_add_column($connection, 'kp_po', 'shift', "VARCHAR(60) NOT NULL DEFAULT ''");
    kug_schema_add_column($connection, 'kp_po', 'rec_staff', 'INT NOT NULL DEFAULT 0');
    kug_schema_add_column($connection, 'kp_po', 'rec_date', 'DATE NULL');
    kug_schema_add_column($connection, 'kp_po', 'disc', 'DECIMAL(12,2) NOT NULL DEFAULT 0');
    kug_schema_add_column($connection, 'kp_po', 'tax_type', 'TINYINT NOT NULL DEFAULT 1');
    kug_schema_add_column($connection, 'kp_po', 'tax_perc', 'DECIMAL(12,2) NOT NULL DEFAULT 0');
    kug_schema_add_column($connection, 'kp_po', 'vatable', 'DECIMAL(12,2) NOT NULL DEFAULT 0');
    kug_schema_add_column($connection, 'kp_po', 'vat', 'DECIMAL(12,2) NOT NULL DEFAULT 0');
    kug_schema_add_column($connection, 'kp_po', 'refno', "VARCHAR(255) NOT NULL DEFAULT ''");
}
