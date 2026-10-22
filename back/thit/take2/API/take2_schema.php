<?php
function take2_schema_ensure() {
    global $connection;
    $queries = [
        "CREATE TABLE IF NOT EXISTS kp_stock_takes (
            take_id INT UNSIGNED NOT NULL AUTO_INCREMENT PRIMARY KEY,
            take_code VARCHAR(40) NOT NULL UNIQUE,
            scope_type VARCHAR(20) NOT NULL DEFAULT 'all', scope_id INT NOT NULL DEFAULT 0,
            status VARCHAR(30) NOT NULL DEFAULT 'DRAFT', notes TEXT NULL, approval_notes TEXT NULL, review_notes TEXT NULL,
            created_by INT NOT NULL DEFAULT 0, approved_by INT NOT NULL DEFAULT 0, assigned_by INT NOT NULL DEFAULT 0,
            reviewed_by INT NOT NULL DEFAULT 0, posted_by INT NOT NULL DEFAULT 0,
            created_at DATETIME NOT NULL DEFAULT CURRENT_TIMESTAMP, approved_at DATETIME NULL, submitted_at DATETIME NULL,
            reviewed_at DATETIME NULL, completed_at DATETIME NULL, INDEX(status), INDEX(scope_type,scope_id)
        ) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4",
        "CREATE TABLE IF NOT EXISTS kp_stock_take_lines (
            line_id INT UNSIGNED NOT NULL AUTO_INCREMENT PRIMARY KEY, take_id INT UNSIGNED NOT NULL, item_id INT NOT NULL,
            item_name VARCHAR(255) NOT NULL DEFAULT '', expected_qty DECIMAL(14,3) NOT NULL DEFAULT 0,
            counted_qty DECIMAL(14,3) NULL, final_qty DECIMAL(14,3) NULL, variance_qty DECIMAL(14,3) NULL,
            assigned_user_id INT NOT NULL DEFAULT 0, line_status VARCHAR(20) NOT NULL DEFAULT 'OPEN', retake_required TINYINT NOT NULL DEFAULT 0,
            count_notes TEXT NULL, retake_notes TEXT NULL, counted_at DATETIME NULL, UNIQUE KEY take_item(take_id,item_id), INDEX(take_id), INDEX(assigned_user_id)
        ) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4",
        "CREATE TABLE IF NOT EXISTS kp_stock_take_retakes (
            retake_id INT UNSIGNED NOT NULL AUTO_INCREMENT PRIMARY KEY, take_id INT UNSIGNED NOT NULL, line_id INT UNSIGNED NOT NULL, item_id INT NOT NULL,
            original_counted_qty DECIMAL(14,3) NULL, retake_qty DECIMAL(14,3) NULL, reason VARCHAR(255) NOT NULL DEFAULT '', request_notes TEXT NULL,
            approval_notes TEXT NULL, assigned_user_id INT NOT NULL DEFAULT 0, requested_by INT NOT NULL DEFAULT 0, completed_by INT NOT NULL DEFAULT 0,
            approved_by INT NOT NULL DEFAULT 0, status VARCHAR(25) NOT NULL DEFAULT 'REQUESTED', created_at DATETIME NOT NULL DEFAULT CURRENT_TIMESTAMP,
            completed_at DATETIME NULL, approved_at DATETIME NULL, INDEX(take_id), INDEX(status)
        ) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4",
        "CREATE TABLE IF NOT EXISTS kp_stock_take_events (
            event_id INT UNSIGNED NOT NULL AUTO_INCREMENT PRIMARY KEY, take_id INT UNSIGNED NOT NULL, user_id INT NOT NULL DEFAULT 0,
            event_type VARCHAR(40) NOT NULL, notes TEXT NULL, created_at DATETIME NOT NULL DEFAULT CURRENT_TIMESTAMP, INDEX(take_id)
        ) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4",
        "CREATE TABLE IF NOT EXISTS kp_stock_take_reviews (
            review_id INT UNSIGNED NOT NULL AUTO_INCREMENT PRIMARY KEY, take_id INT UNSIGNED NOT NULL, line_id INT UNSIGNED NOT NULL,
            item_id INT NOT NULL, explanation TEXT NOT NULL, reviewed_by INT NOT NULL DEFAULT 0,
            created_at DATETIME NOT NULL DEFAULT CURRENT_TIMESTAMP, updated_at DATETIME NULL, UNIQUE KEY take_line(take_id,line_id), INDEX(take_id)
        ) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4"
    ];
    foreach ($queries as $query) if (!mysqli_query($connection, $query)) throw new Exception(mysqli_error($connection));
}
