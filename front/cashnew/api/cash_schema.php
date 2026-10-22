<?php

if (!function_exists('cash_schema_boot')) {
    function cash_schema_boot($connection) {
        $queries = [
            "CREATE TABLE IF NOT EXISTS kp_user_pdq (
                id INT NOT NULL AUTO_INCREMENT,
                user_id INT NOT NULL DEFAULT 0,
                amount DECIMAL(10,2) NOT NULL DEFAULT 0,
                refno VARCHAR(120) NOT NULL DEFAULT '',
                shift DATETIME NULL,
                app_status INT NOT NULL DEFAULT 2,
                app_user INT NOT NULL DEFAULT 0,
                clear_status INT NOT NULL DEFAULT 2,
                datime DATETIME NOT NULL DEFAULT CURRENT_TIMESTAMP,
                PRIMARY KEY (id),
                KEY idx_user_shift (user_id, shift),
                KEY idx_clear_status (clear_status)
            )",
            "CREATE TABLE IF NOT EXISTS kp_shop_expenses (
                exp_id INT NOT NULL AUTO_INCREMENT,
                shift_date DATE NOT NULL,
                title VARCHAR(120) NOT NULL,
                amount DECIMAL(10,2) NOT NULL DEFAULT 0,
                note TEXT NULL,
                user_id INT NOT NULL DEFAULT 0,
                created_at TIMESTAMP NOT NULL DEFAULT CURRENT_TIMESTAMP,
                PRIMARY KEY (exp_id)
            )",
            "CREATE TABLE IF NOT EXISTS kp_counter_handover (
                handover_id INT NOT NULL AUTO_INCREMENT,
                shift_date DATE NOT NULL,
                cntr_id INT NOT NULL DEFAULT 0,
                from_user INT NOT NULL DEFAULT 0,
                to_user INT NOT NULL DEFAULT 0,
                total_system DECIMAL(10,2) NOT NULL DEFAULT 0,
                total_declared DECIMAL(10,2) NOT NULL DEFAULT 0,
                total_variance DECIMAL(10,2) NOT NULL DEFAULT 0,
                status VARCHAR(20) NOT NULL DEFAULT 'MATCH',
                note TEXT NULL,
                created_at TIMESTAMP NOT NULL DEFAULT CURRENT_TIMESTAMP,
                PRIMARY KEY (handover_id)
            )",
            "CREATE TABLE IF NOT EXISTS kp_counter_handover_modes (
                handover_mode_id INT NOT NULL AUTO_INCREMENT,
                handover_id INT NOT NULL DEFAULT 0,
                pay_id INT NOT NULL DEFAULT 0,
                system_amount DECIMAL(10,2) NOT NULL DEFAULT 0,
                declared_amount DECIMAL(10,2) NOT NULL DEFAULT 0,
                variance_amount DECIMAL(10,2) NOT NULL DEFAULT 0,
                status VARCHAR(20) NOT NULL DEFAULT 'MATCH',
                PRIMARY KEY (handover_mode_id)
            )",
            "CREATE TABLE IF NOT EXISTS kp_cash_collection (
                id INT NOT NULL AUTO_INCREMENT,
                pay_id INT NOT NULL DEFAULT 0,
                user_id INT NOT NULL DEFAULT 0,
                appr_by INT NOT NULL DEFAULT 0,
                shift DATE NOT NULL,
                day DATE NOT NULL,
                amount DECIMAL(10,2) NOT NULL DEFAULT 0,
                created_at TIMESTAMP NOT NULL DEFAULT CURRENT_TIMESTAMP,
                PRIMARY KEY (id)
            )",
            "CREATE TABLE IF NOT EXISTS kp_shop_expense_delete (
                del_id INT NOT NULL AUTO_INCREMENT,
                exp_id INT NOT NULL DEFAULT 0,
                shift_date DATE NOT NULL,
                title VARCHAR(120) NOT NULL,
                amount DECIMAL(10,2) NOT NULL DEFAULT 0,
                note TEXT NULL,
                created_by INT NOT NULL DEFAULT 0,
                deleted_by INT NOT NULL DEFAULT 0,
                appr_by INT NOT NULL DEFAULT 0,
                reason TEXT NULL,
                deleted_at TIMESTAMP NOT NULL DEFAULT CURRENT_TIMESTAMP,
                delete_shift DATE NULL,
                PRIMARY KEY (del_id)
            )",
        ];

        foreach ($queries as $query) {
            mysqli_query($connection, $query) or die(mysqli_error($connection));
        }

        mysqli_query($connection, "CREATE TABLE IF NOT EXISTS kp_staff_clearances (
            clearance_id INT NOT NULL AUTO_INCREMENT,
            staff_user_id INT NOT NULL DEFAULT 0,
            cleared_by INT NOT NULL DEFAULT 0,
            shift_date DATE NOT NULL,
            total_sales DECIMAL(10,2) NOT NULL DEFAULT 0,
            paid_sales DECIMAL(10,2) NOT NULL DEFAULT 0,
            unpaid_sales DECIMAL(10,2) NOT NULL DEFAULT 0,
            credit_sales DECIMAL(10,2) NOT NULL DEFAULT 0,
            mpesa_total DECIMAL(10,2) NOT NULL DEFAULT 0,
            pdq_total DECIMAL(10,2) NOT NULL DEFAULT 0,
            expected_cash DECIMAL(10,2) NOT NULL DEFAULT 0,
            actual_cash DECIMAL(10,2) NOT NULL DEFAULT 0,
            variance_amount DECIMAL(10,2) NOT NULL DEFAULT 0,
            variance_type VARCHAR(20) NOT NULL DEFAULT '',
            snapshot_json LONGTEXT NULL,
            note TEXT NULL,
            created_at TIMESTAMP NOT NULL DEFAULT CURRENT_TIMESTAMP,
            PRIMARY KEY (clearance_id),
            KEY idx_staff_shift (staff_user_id, shift_date)
        )") or die(mysqli_error($connection));

        $splitStatus = mysqli_query($connection, "SHOW COLUMNS FROM kp_split_message LIKE 'clear_status'") or die(mysqli_error($connection));
        if (mysqli_num_rows($splitStatus) < 1) {
            mysqli_query($connection, "ALTER TABLE kp_split_message ADD COLUMN clear_status INT NOT NULL DEFAULT 2") or die(mysqli_error($connection));
        }

        foreach (['is_default_cash', 'is_default_mpesa', 'is_default_card', 'is_default_complimentary'] as $column) {
            $result = mysqli_query($connection, "SHOW COLUMNS FROM kp_pay_mode LIKE '$column'") or die(mysqli_error($connection));
            if (mysqli_num_rows($result) < 1) {
                mysqli_query($connection, "ALTER TABLE kp_pay_mode ADD COLUMN $column INT NOT NULL DEFAULT 2") or die(mysqli_error($connection));
            }
        }

        foreach ([
            "cust_id INT NOT NULL DEFAULT 0",
            "sale_bal DECIMAL(10,2) NOT NULL DEFAULT 0",
            "cf_bal DECIMAL(10,2) NOT NULL DEFAULT 0",
            "type INT NOT NULL DEFAULT 0",
        ] as $definition) {
            $parts = explode(' ', $definition, 2);
            $column = $parts[0];
            $result = mysqli_query($connection, "SHOW COLUMNS FROM kp_sales LIKE '$column'") or die(mysqli_error($connection));
            if (mysqli_num_rows($result) < 1) {
                mysqli_query($connection, "ALTER TABLE kp_sales ADD COLUMN $definition") or die(mysqli_error($connection));
            }
        }

        mysqli_query($connection, "CREATE TABLE IF NOT EXISTS kp_customer_credit_payment_items (
            credit_item_id INT NOT NULL AUTO_INCREMENT,
            credit_pay_id INT NOT NULL DEFAULT 0,
            cust_id INT NOT NULL DEFAULT 0,
            sale_id INT NOT NULL DEFAULT 0,
            ord_date DATE NOT NULL,
            pay_date DATE NOT NULL,
            amount DECIMAL(10,2) NOT NULL DEFAULT 0,
            PRIMARY KEY (credit_item_id)
        )") or die(mysqli_error($connection));

        mysqli_query($connection, "CREATE TABLE IF NOT EXISTS kp_customer_credit_payment_modes (
            credit_mode_id INT NOT NULL AUTO_INCREMENT,
            credit_pay_id INT NOT NULL DEFAULT 0,
            pay_mode_id INT NOT NULL DEFAULT 0,
            amount DECIMAL(10,2) NOT NULL DEFAULT 0,
            reference_no VARCHAR(120) NOT NULL DEFAULT '',
            PRIMARY KEY (credit_mode_id)
        )") or die(mysqli_error($connection));

        if (function_exists('cash_ensure_merge_tables')) {
            cash_ensure_merge_tables();
        }
    }
}
