<?php
declare(strict_types=1);

function counter_schema_ensure(mysqli $connection): void
{
    mysqli_query($connection, "CREATE TABLE IF NOT EXISTS kp_shift (
        shift_id INT NOT NULL AUTO_INCREMENT,
        shift DATE NOT NULL,
        PRIMARY KEY (shift_id)
    ) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4");

    mysqli_query($connection, "CREATE TABLE IF NOT EXISTS kp_day_shift (
        shift_id INT NOT NULL AUTO_INCREMENT,
        shift DATE NOT NULL,
        opening DATETIME NULL,
        closing DATETIME NULL,
        open_by INT NOT NULL DEFAULT 0,
        close_by INT NOT NULL DEFAULT 0,
        open_ord INT NOT NULL DEFAULT 0,
        close_ord INT NOT NULL DEFAULT 0,
        status TINYINT NOT NULL DEFAULT 2,
        PRIMARY KEY (shift_id),
        KEY idx_day_shift_status (status, shift)
    ) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4");

    mysqli_query($connection, "CREATE TABLE IF NOT EXISTS kp_counter_close_summary (
        close_sum_id INT NOT NULL AUTO_INCREMENT,
        shift_date DATE NOT NULL,
        cntr_id INT NOT NULL DEFAULT 0,
        user_id INT NOT NULL DEFAULT 0,
        total_bills INT NOT NULL DEFAULT 0,
        total_sales DECIMAL(12,2) NOT NULL DEFAULT 0,
        created_at TIMESTAMP NOT NULL DEFAULT CURRENT_TIMESTAMP,
        PRIMARY KEY (close_sum_id),
        KEY idx_counter_summary_shift (shift_date, cntr_id)
    ) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4");

    $columns = [
        'claimed_at' => "ALTER TABLE kp_print_queue ADD COLUMN claimed_at DATETIME NULL",
        'lease_until' => "ALTER TABLE kp_print_queue ADD COLUMN lease_until DATETIME NULL",
        'print_attempts' => "ALTER TABLE kp_print_queue ADD COLUMN print_attempts TINYINT UNSIGNED NOT NULL DEFAULT 0",
        'last_error' => "ALTER TABLE kp_print_queue ADD COLUMN last_error VARCHAR(255) NOT NULL DEFAULT ''",
        'shift' => "ALTER TABLE kp_print_queue ADD COLUMN shift VARCHAR(60) NOT NULL DEFAULT ''",
        'payload_json' => "ALTER TABLE kp_print_queue ADD COLUMN payload_json LONGTEXT NULL",
        'items_json' => "ALTER TABLE kp_print_queue ADD COLUMN items_json LONGTEXT NULL",
    ];
    foreach ($columns as $column => $sql) {
        $check = mysqli_query($connection, "SHOW COLUMNS FROM kp_print_queue LIKE '" . mysqli_real_escape_string($connection, $column) . "'");
        if ($check && mysqli_num_rows($check) === 0) mysqli_query($connection, $sql);
    }
}
