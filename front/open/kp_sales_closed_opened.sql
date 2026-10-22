CREATE TABLE IF NOT EXISTS kp_shift_sales_open (
    id INT NOT NULL AUTO_INCREMENT,
    original_sale_id INT NOT NULL,
    user_id INT NOT NULL,
    reopened_at TIMESTAMP NOT NULL DEFAULT CURRENT_TIMESTAMP,
    bill_mode VARCHAR(20) NOT NULL DEFAULT 'each',
    is_fresh_open TINYINT(1) NOT NULL DEFAULT 0,
    PRIMARY KEY (id),
    UNIQUE KEY uniq_original_sale_id (original_sale_id)
);
