<?php
declare(strict_types=1);

function shift_schema_ensure(mysqli $connection): void
{
    mysqli_query($connection, "CREATE TABLE IF NOT EXISTS kp_shift (shift_id INT NOT NULL AUTO_INCREMENT, shift DATE NOT NULL, PRIMARY KEY (shift_id)) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4");
    mysqli_query($connection, "CREATE TABLE IF NOT EXISTS kp_day_shift (shift_id INT NOT NULL AUTO_INCREMENT, shift DATE NOT NULL, opening DATETIME NULL, closing DATETIME NULL, open_by INT NOT NULL DEFAULT 0, close_by INT NOT NULL DEFAULT 0, open_ord INT NOT NULL DEFAULT 0, close_ord INT NOT NULL DEFAULT 0, status TINYINT NOT NULL DEFAULT 2, PRIMARY KEY (shift_id), KEY idx_day_shift_status (status,shift)) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4");
}
