<?php

function ad_schema_query($sql)
{
    global $connection;
    try {
        if (!mysqli_query($connection, $sql)) {
            throw new RuntimeException(mysqli_error($connection));
        }
        return true;
    } catch (Throwable $error) {
        throw new RuntimeException('Administration schema update failed: ' . $error->getMessage(), 0, $error);
    }
}

function ad_schema_columns($table)
{
    global $connection;
    $columns = [];
    $result = mysqli_query($connection, "SHOW COLUMNS FROM `{$table}`");
    while ($row = mysqli_fetch_assoc($result)) $columns[$row['Field']] = true;
    return $columns;
}

function ad_schema_ensure_columns($table, $definitions)
{
    $existing = ad_schema_columns($table);
    $add = [];
    foreach ($definitions as $column => $definition) {
        if (!isset($existing[$column])) $add[] = "ADD COLUMN `{$column}` {$definition}";
    }
    if ($add) ad_schema_query("ALTER TABLE `{$table}` " . implode(', ', $add));
}

function ad_schema_boot()
{
    // Menu permissions are used by the administration API. Older installations
    // may have kp_menu without menu_order, while fresh installations may not
    // have the table yet.
    ad_schema_query("CREATE TABLE IF NOT EXISTS `kp_menu` (
        `menu_id` INT NOT NULL AUTO_INCREMENT,
        `name` VARCHAR(150) NOT NULL DEFAULT '',
        `link` VARCHAR(255) NULL,
        `level` INT NOT NULL DEFAULT 1,
        `sub` TINYINT NOT NULL DEFAULT 0,
        `parent` INT NOT NULL DEFAULT 0,
        `icon` VARCHAR(100) NULL,
        `panel` VARCHAR(50) NULL,
        `modal` TINYINT NOT NULL DEFAULT 0,
        `modal_function` VARCHAR(150) NULL,
        `menu_order` INT NOT NULL DEFAULT 0,
        PRIMARY KEY (`menu_id`),
        KEY `idx_kp_menu_parent` (`parent`),
        KEY `idx_kp_menu_order` (`menu_order`)
    ) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4");
    ad_schema_ensure_columns('kp_menu', [
        'name' => "VARCHAR(150) NOT NULL DEFAULT ''",
        'link' => 'VARCHAR(255) NULL',
        'level' => 'INT NOT NULL DEFAULT 1',
        'sub' => 'TINYINT NOT NULL DEFAULT 0',
        'parent' => 'INT NOT NULL DEFAULT 0',
        'icon' => 'VARCHAR(100) NULL',
        'panel' => 'VARCHAR(50) NULL',
        'modal' => 'TINYINT NOT NULL DEFAULT 0',
        'modal_function' => 'VARCHAR(150) NULL',
        'menu_order' => 'INT NOT NULL DEFAULT 0'
    ]);

    ad_schema_query("CREATE TABLE IF NOT EXISTS `kp_relationship` (`id` INT NOT NULL AUTO_INCREMENT, `name` VARCHAR(80) NOT NULL, PRIMARY KEY (`id`), UNIQUE KEY `uq_relationship_name` (`name`)) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4");
    ad_schema_query("INSERT IGNORE INTO `kp_relationship` (`name`) VALUES ('HUSBAND'),('WIFE'),('FATHER'),('MOTHER'),('BROTHER'),('SISTER'),('UNCLE'),('AUNT'),('GRAND FATHER'),('GRAND MOTHER')");

    ad_schema_query("CREATE TABLE IF NOT EXISTS `kp_roles` (`role_id` INT NOT NULL AUTO_INCREMENT, `role_name` VARCHAR(100) NOT NULL, `role_access_panel` TINYINT NOT NULL DEFAULT 1, PRIMARY KEY (`role_id`)) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4");
    ad_schema_ensure_columns('kp_roles', ['role_description'=>'VARCHAR(255) NULL','role_creation_date'=>'DATETIME NULL','role_last_update'=>'DATETIME NULL','access_dash'=>'TINYINT NOT NULL DEFAULT 0','front_perm'=>'TEXT NULL']);
    ad_schema_query("CREATE TABLE IF NOT EXISTS `kp_users` (`user_id` INT NOT NULL AUTO_INCREMENT, `staff_id` INT NULL, `username` VARCHAR(100) NOT NULL, `password` VARCHAR(255) NULL, `pin` VARCHAR(100) NULL, `role_id` INT NULL, `drole` INT NULL, `sys_default` TINYINT NOT NULL DEFAULT 2, `work_status` TINYINT NOT NULL DEFAULT 2, PRIMARY KEY (`user_id`)) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4");
    ad_schema_ensure_columns('kp_users', ['staff_id'=>'INT NULL','username'=>'VARCHAR(100) NULL','password'=>'VARCHAR(255) NULL','pin'=>'VARCHAR(100) NULL','role_id'=>'INT NULL','drole'=>'INT NULL','sys_default'=>'TINYINT NOT NULL DEFAULT 2','work_status'=>'TINYINT NOT NULL DEFAULT 2','user_work_status'=>'TINYINT NOT NULL DEFAULT 2','permissions'=>'TEXT NULL','max_sale'=>'DECIMAL(12,2) NOT NULL DEFAULT 0']);
    ad_schema_query("CREATE TABLE IF NOT EXISTS kp_displinary (discipline_id INT NOT NULL AUTO_INCREMENT,user_id INT NOT NULL DEFAULT 0,reason TEXT NULL,day DATE NULL,PRIMARY KEY (discipline_id)) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4");

    ad_schema_query("CREATE TABLE IF NOT EXISTS `kp_staff` (
        `staff_id` INT UNSIGNED NOT NULL AUTO_INCREMENT,
        `staff_name` VARCHAR(150) NOT NULL,
        `staff_del_status` TINYINT NOT NULL DEFAULT 2,
        PRIMARY KEY (`staff_id`)
    ) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4");

    ad_schema_ensure_columns('kp_staff', [
        'surname' => 'VARCHAR(150) NULL', 'alias' => 'VARCHAR(150) NULL', 'staff_nid' => 'VARCHAR(80) NULL',
        'staff_nck_no' => 'VARCHAR(80) NULL', 'staff_email' => 'VARCHAR(190) NULL', 'staff_address' => 'VARCHAR(255) NULL',
        'staff_photo' => 'VARCHAR(255) NULL', 'staff_id_imgback' => 'VARCHAR(255) NULL', 'staff_phone' => 'VARCHAR(60) NULL',
        'whatsphone' => 'VARCHAR(60) NULL', 'staff_nssf' => 'VARCHAR(80) NULL', 'staff_nhif' => 'VARCHAR(80) NULL',
        'staff_kra_pin' => 'VARCHAR(80) NULL', 'staff_kin_name' => 'VARCHAR(150) NULL', 'staff_kin_phone' => 'VARCHAR(60) NULL',
        'staff_kin_id' => 'VARCHAR(80) NULL', 'staff_kin_relationship' => 'VARCHAR(100) NULL', 'staff_kin_relation' => 'VARCHAR(100) NULL',
        'staff_kin_address' => 'VARCHAR(255) NULL', 'staff_kin_residence' => 'VARCHAR(150) NULL', 'staff_hire_date' => 'DATETIME NULL',
        'staff_updated_date' => 'DATETIME NULL', 'staff_residence' => 'VARCHAR(150) NULL', 'staff_user_status' => 'TINYINT NOT NULL DEFAULT 0',
        'staff_cv' => 'VARCHAR(255) NULL', 'staff_cv_status' => 'TINYINT NOT NULL DEFAULT 0', 'staff_nid_img' => 'VARCHAR(255) NULL',
        'staff_nid_img_status' => 'TINYINT NOT NULL DEFAULT 0', 'staff_kin_id_image' => 'VARCHAR(255) NULL', 'staff_kin_id_image_status' => 'TINYINT NOT NULL DEFAULT 0',
        'staff_conduct_img' => 'VARCHAR(255) NULL', 'staff_conduct_img_status' => 'TINYINT NOT NULL DEFAULT 0', 'emp_type' => 'VARCHAR(80) NULL',
        'salary_cycle' => 'VARCHAR(50) NULL', 'salary_type' => 'VARCHAR(50) NULL', 'basic_salary' => 'DECIMAL(14,2) NULL', 'work_status' => 'VARCHAR(50) NULL'
    ]);

    ad_schema_query("CREATE TABLE IF NOT EXISTS `kp_staff_nok` (
        `nok_id` BIGINT UNSIGNED NOT NULL AUTO_INCREMENT, `staff_id` INT UNSIGNED NOT NULL,
        `nok_name` VARCHAR(150) NULL, `nok_phone` VARCHAR(60) NULL, `nok_id_number` VARCHAR(80) NULL,
        `nok_relationship` VARCHAR(100) NULL, `nok_address` VARCHAR(255) NULL, `nok_residence` VARCHAR(150) NULL,
        `is_current` TINYINT NOT NULL DEFAULT 1, `created_at` DATETIME NULL, `updated_at` DATETIME NULL,
        PRIMARY KEY (`nok_id`), KEY `idx_staff_nok_staff` (`staff_id`)
    ) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4");

    ad_schema_query("CREATE TABLE IF NOT EXISTS `kp_staff_employment` (
        `employment_id` BIGINT UNSIGNED NOT NULL AUTO_INCREMENT, `staff_id` INT UNSIGNED NOT NULL,
        `shop_id` INT NULL, `department_name` VARCHAR(150) NULL, `job_title` VARCHAR(150) NULL,
        `employment_type` VARCHAR(80) NULL, `contract_type` VARCHAR(80) NULL, `work_status` VARCHAR(50) NULL,
        `start_date` DATE NULL, `hire_date` DATE NULL, `probation_end_date` DATE NULL, `contract_end_date` DATE NULL,
        `exit_date` DATE NULL, `supervisor_name` VARCHAR(150) NULL, `notes` TEXT NULL, `is_current` TINYINT NOT NULL DEFAULT 1,
        `verified_by` INT NULL, `verified_at` DATETIME NULL, `updated_by` INT NULL, `updated_at` DATETIME NULL, `created_at` DATETIME NULL,
        PRIMARY KEY (`employment_id`), KEY `idx_staff_employment_staff` (`staff_id`)
    ) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4");

    ad_schema_query("CREATE TABLE IF NOT EXISTS `kp_staff_employment_history` (
        `history_id` BIGINT UNSIGNED NOT NULL AUTO_INCREMENT, `staff_id` INT UNSIGNED NOT NULL, `employment_id` BIGINT NULL,
        `financial_id` BIGINT NULL, `change_type` VARCHAR(80) NULL, `summary` VARCHAR(255) NULL, `change_data` JSON NULL,
        `changed_by` INT NULL, `changed_at` DATETIME NULL, PRIMARY KEY (`history_id`), KEY `idx_staff_history_staff` (`staff_id`)
    ) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4");

    ad_schema_query("CREATE TABLE IF NOT EXISTS `kp_staff_profile_checks` (
        `staff_id` INT UNSIGNED NOT NULL, `details_present` TINYINT NOT NULL DEFAULT 0, `nok_present` TINYINT NOT NULL DEFAULT 0,
        `docs_present` TINYINT NOT NULL DEFAULT 0, `image_present` TINYINT NOT NULL DEFAULT 0, `details_verified` TINYINT NOT NULL DEFAULT 0,
        `docs_verified` TINYINT NOT NULL DEFAULT 0, `available_count` INT NOT NULL DEFAULT 0, `required_total` INT NOT NULL DEFAULT 0,
        `completion_percent` DECIMAL(5,2) NOT NULL DEFAULT 0, `updated_at` DATETIME NULL, `employment_present` TINYINT NOT NULL DEFAULT 0,
        `financials_present` TINYINT NOT NULL DEFAULT 0, `employment_verified` TINYINT NOT NULL DEFAULT 0, `financials_verified` TINYINT NOT NULL DEFAULT 0,
        PRIMARY KEY (`staff_id`)
    ) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4");

    ad_schema_query("CREATE TABLE IF NOT EXISTS `kp_staff_documents` (
        `document_id` BIGINT UNSIGNED NOT NULL AUTO_INCREMENT, `staff_id` INT UNSIGNED NOT NULL, `doc_code` VARCHAR(80) NULL,
        `doc_title` VARCHAR(150) NULL, `file_name` VARCHAR(255) NULL, `file_path` VARCHAR(500) NULL, `file_ext` VARCHAR(20) NULL,
        `mime_type` VARCHAR(100) NULL, `file_size` BIGINT NULL, `is_current` TINYINT NOT NULL DEFAULT 1, `uploaded_by` INT NULL,
        `uploaded_at` DATETIME NULL, `updated_at` DATETIME NULL, PRIMARY KEY (`document_id`), KEY `idx_staff_documents_staff` (`staff_id`)
    ) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4");
}
