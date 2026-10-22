<?php
/* -------------------------------------------------------------
   back/kug/schema.php
   Auto-creates all tables required by the Purchase (kug) module.
   Safe to call on every request — uses CREATE TABLE IF NOT EXISTS.
   ------------------------------------------------------------- */

if (defined('KUG_SCHEMA_DONE')) return;
define('KUG_SCHEMA_DONE', true);

global $connection;
if (!($connection instanceof mysqli)) return;

$_kug_tables = [];

// ── Suppliers ─────────────────────────────────────────────────

$_kug_tables['kp_suppliers'] = "CREATE TABLE IF NOT EXISTS `kp_suppliers` (
    `sup_id`       INT          NOT NULL AUTO_INCREMENT,
    `sup_name`     VARCHAR(255) NOT NULL DEFAULT '',
    `sup_address`  VARCHAR(255) NOT NULL DEFAULT '',
    `sup_email`    VARCHAR(100) NOT NULL DEFAULT '',
    `sup_phone`    VARCHAR(50)  NOT NULL DEFAULT '',
    `sup_location` VARCHAR(100) NOT NULL DEFAULT '',
    `sup_pin`      VARCHAR(50)  NOT NULL DEFAULT '',
    `sup_vat`      VARCHAR(50)  NOT NULL DEFAULT '',
    `sup_type`     VARCHAR(50)  NOT NULL DEFAULT '',
    PRIMARY KEY (`sup_id`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4";

// ── PO sequence counter ───────────────────────────────────────

$_kug_tables['kp_po_id'] = "CREATE TABLE IF NOT EXISTS `kp_po_id` (
    `po_id` INT NOT NULL DEFAULT 0
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4";

// ── PO draft status (in-progress order before placement) ─────

$_kug_tables['kp_po_status'] = "CREATE TABLE IF NOT EXISTS `kp_po_status` (
    `id`         INT          NOT NULL AUTO_INCREMENT,
    `po_id`      INT          NOT NULL DEFAULT 0,
    `po_user_id` INT          NOT NULL DEFAULT 0,
    `sup_id`     INT          NOT NULL DEFAULT 0,
    `potype`     INT          NOT NULL DEFAULT 0,
    `poby`       INT          NOT NULL DEFAULT 0,
    `po_day`     DATE         NULL DEFAULT NULL,
    `po_status`  INT          NOT NULL DEFAULT 2,
    PRIMARY KEY (`id`),
    KEY `idx_po_status_po`   (`po_id`),
    KEY `idx_po_status_user` (`po_user_id`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4";

// ── Purchase Order master ─────────────────────────────────────

$_kug_tables['kp_po'] = "CREATE TABLE IF NOT EXISTS `kp_po` (
    `po_id`        INT           NOT NULL DEFAULT 0,
    `sup_id`       INT           NOT NULL DEFAULT 0,
    `ord_user`     INT           NOT NULL DEFAULT 0,
    `po_date`      DATE          NULL DEFAULT NULL,
    `amount`       DECIMAL(15,2) NOT NULL DEFAULT 0.00,
    `potype`       INT           NOT NULL DEFAULT 0,
    `poby`         INT           NOT NULL DEFAULT 0,
    `appr_status`  INT           NOT NULL DEFAULT 2
                                 COMMENT '1=approved,2=pending,3=held,4=cancelled',
    `po_status`    INT           NOT NULL DEFAULT 0,
    `appr_user`    INT           NOT NULL DEFAULT 0,
    `appr_comment` TEXT          NULL DEFAULT NULL,
    `rec_status`   INT           NOT NULL DEFAULT 2
                                 COMMENT '1=fully received,2=pending,3=partial',
    PRIMARY KEY (`po_id`),
    KEY `idx_po_sup`    (`sup_id`),
    KEY `idx_po_user`   (`ord_user`),
    KEY `idx_po_appr`   (`appr_status`),
    KEY `idx_po_rec`    (`rec_status`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4";

// ── PO line items ─────────────────────────────────────────────

$_kug_tables['kp_po_items'] = "CREATE TABLE IF NOT EXISTS `kp_po_items` (
    `id`         INT           NOT NULL AUTO_INCREMENT,
    `req_id`     INT           NOT NULL DEFAULT 0,
    `po_id`      INT           NOT NULL DEFAULT 0,
    `item_id`    INT           NOT NULL DEFAULT 0,
    `stock_id`   VARCHAR(30)   NULL DEFAULT NULL,
    `variant_id` INT           NOT NULL DEFAULT 0,
    `qty`        DECIMAL(12,3) NOT NULL DEFAULT 0.000,
    `price`      DECIMAL(12,2) NOT NULL DEFAULT 0.00,
    `disc`       DECIMAL(12,2) NOT NULL DEFAULT 0.00,
    `total`      DECIMAL(15,2) NOT NULL DEFAULT 0.00,
    `status`     INT           NOT NULL DEFAULT 1,
    `rec_qty`    DECIMAL(12,3) NOT NULL DEFAULT 0.000,
    `rec_price`  DECIMAL(12,2) NOT NULL DEFAULT 0.00,
    `rec_total`  DECIMAL(15,2) NOT NULL DEFAULT 0.00,
    `rec_user`   INT           NOT NULL DEFAULT 0,
    `rec_status` INT           NOT NULL DEFAULT 0,
    `s2s`        INT           NOT NULL DEFAULT 0,
    `day`        DATE          NULL DEFAULT NULL,
    `datime`     DATETIME      NULL DEFAULT NULL,
    `snap`       LONGTEXT      NULL DEFAULT NULL,
    PRIMARY KEY (`id`),
    KEY `idx_po_items_po`   (`po_id`),
    KEY `idx_po_items_item` (`item_id`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4";

// ── PO audit history ──────────────────────────────────────────

$_kug_tables['kp_po_hist'] = "CREATE TABLE IF NOT EXISTS `kp_po_hist` (
    `id`           INT          NOT NULL AUTO_INCREMENT,
    `po_id`        INT          NOT NULL DEFAULT 0,
    `item_id`      INT          NOT NULL DEFAULT 0,
    `qty`          DECIMAL(12,3)NOT NULL DEFAULT 0.000,
    `price`        DECIMAL(12,2)NOT NULL DEFAULT 0.00,
    `trans`        VARCHAR(100) NOT NULL DEFAULT '',
    `trans_user`   INT          NOT NULL DEFAULT 0,
    `trans_day`    DATE         NULL DEFAULT NULL,
    `trans_time`   TIME         NULL DEFAULT NULL,
    `trans_datime` DATETIME     NULL DEFAULT NULL,
    `trans_comment`TEXT         NULL DEFAULT NULL,
    PRIMARY KEY (`id`),
    KEY `idx_po_hist_po` (`po_id`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4";

// ── Rejected / cancelled POs ──────────────────────────────────

$_kug_tables['kp_rejected_po'] = "CREATE TABLE IF NOT EXISTS `kp_rejected_po` (
    `po_id`        INT           NOT NULL DEFAULT 0,
    `sup_id`       INT           NOT NULL DEFAULT 0,
    `ord_user`     INT           NOT NULL DEFAULT 0,
    `po_date`      DATE          NULL DEFAULT NULL,
    `amount`       DECIMAL(15,2) NOT NULL DEFAULT 0.00,
    `potype`       INT           NOT NULL DEFAULT 0,
    `poby`         INT           NOT NULL DEFAULT 0,
    `appr_status`  INT           NOT NULL DEFAULT 0,
    `po_status`    INT           NOT NULL DEFAULT 0,
    `appr_user`    INT           NOT NULL DEFAULT 0,
    `appr_comment` TEXT          NULL DEFAULT NULL,
    `rec_status`   INT           NOT NULL DEFAULT 0,
    PRIMARY KEY (`po_id`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4";

$_kug_tables['kp_reject_po'] = "CREATE TABLE IF NOT EXISTS `kp_reject_po` (
    `po_id`        INT           NOT NULL DEFAULT 0,
    `sup_id`       INT           NOT NULL DEFAULT 0,
    `ord_user`     INT           NOT NULL DEFAULT 0,
    `po_date`      DATE          NULL DEFAULT NULL,
    `amount`       DECIMAL(15,2) NOT NULL DEFAULT 0.00,
    `potype`       INT           NOT NULL DEFAULT 0,
    `poby`         INT           NOT NULL DEFAULT 0,
    `appr_status`  INT           NOT NULL DEFAULT 0,
    `po_status`    INT           NOT NULL DEFAULT 0,
    `appr_user`    INT           NOT NULL DEFAULT 0,
    `appr_comment` TEXT          NULL DEFAULT NULL,
    `rec_status`   INT           NOT NULL DEFAULT 0,
    PRIMARY KEY (`po_id`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4";

$_kug_tables['kp_reject_po_items'] = "CREATE TABLE IF NOT EXISTS `kp_reject_po_items` (
    `id`      INT           NOT NULL AUTO_INCREMENT,
    `po_id`   INT           NOT NULL DEFAULT 0,
    `item_id` INT           NOT NULL DEFAULT 0,
    `qty`     DECIMAL(12,3) NOT NULL DEFAULT 0.000,
    `price`   DECIMAL(12,2) NOT NULL DEFAULT 0.00,
    `total`   DECIMAL(15,2) NOT NULL DEFAULT 0.00,
    PRIMARY KEY (`id`),
    KEY `idx_reject_po_items_po` (`po_id`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4";

// ── GRN sequence counter ──────────────────────────────────────

$_kug_tables['kp_grn_id'] = "CREATE TABLE IF NOT EXISTS `kp_grn_id` (
    `grn_id` INT NOT NULL DEFAULT 0
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4";

// ── Goods Received Note master ────────────────────────────────

$_kug_tables['kp_grn'] = "CREATE TABLE IF NOT EXISTS `kp_grn` (
    `grn_id`        INT           NOT NULL DEFAULT 0,
    `po_id`         INT           NOT NULL DEFAULT 0,
    `sup_id`        INT           NOT NULL DEFAULT 0,
    `inv`           VARCHAR(100)  NOT NULL DEFAULT '',
    `dnote`         VARCHAR(100)  NOT NULL DEFAULT '',
    `day`           DATE          NULL DEFAULT NULL,
    `datime`        DATETIME      NULL DEFAULT NULL,
    `user_id`       INT           NOT NULL DEFAULT 0,
    `status`        INT           NOT NULL DEFAULT 3
                                  COMMENT '1=complete,2=pending,3=ongoing',
    `tostore_status`INT           NOT NULL DEFAULT 2,
    `amount`        DECIMAL(15,2) NOT NULL DEFAULT 0.00,
    PRIMARY KEY (`grn_id`),
    KEY `idx_grn_po`     (`po_id`),
    KEY `idx_grn_sup`    (`sup_id`),
    KEY `idx_grn_status` (`status`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4";

$_kug_tables['kp_grn_status'] = "CREATE TABLE IF NOT EXISTS `kp_grn_status` (
    `id`      INT      NOT NULL AUTO_INCREMENT,
    `grn_id`  INT      NOT NULL DEFAULT 0,
    `user_id` INT      NOT NULL DEFAULT 0,
    `po_id`   INT      NOT NULL DEFAULT 0,
    `datime`  DATETIME NULL DEFAULT NULL,
    PRIMARY KEY (`id`),
    KEY `idx_grn_status_grn` (`grn_id`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4";

// ── GRN line items ────────────────────────────────────────────

$_kug_tables['kp_grn_items'] = "CREATE TABLE IF NOT EXISTS `kp_grn_items` (
    `id`             INT           NOT NULL AUTO_INCREMENT,
    `grn_id`         INT           NOT NULL DEFAULT 0,
    `po_id`          INT           NOT NULL DEFAULT 0,
    `item_id`        INT           NOT NULL DEFAULT 0,
    `product_code`   VARCHAR(100)  NOT NULL DEFAULT '',
    `barcode`        VARCHAR(100)  NOT NULL DEFAULT '',
    `receipt_desc`   VARCHAR(255)  NOT NULL DEFAULT '',
    `qty`            DECIMAL(12,3) NOT NULL DEFAULT 0.000,
    `price`          DECIMAL(12,2) NOT NULL DEFAULT 0.00,
    `total`          DECIMAL(15,2) NOT NULL DEFAULT 0.00,
    `user_id`        INT           NOT NULL DEFAULT 0,
    `day`            DATE          NULL DEFAULT NULL,
    `datime`         DATETIME      NULL DEFAULT NULL,
    `confirm_qty`    DECIMAL(12,3) NOT NULL DEFAULT 0.000,
    `confirm_status` INT           NOT NULL DEFAULT 0,
    `tostore_status` INT           NOT NULL DEFAULT 2,
    `tostore_user`   INT           NOT NULL DEFAULT 0,
    `s2s`            INT           NOT NULL DEFAULT 0,
    `extra`          TEXT          NULL DEFAULT NULL,
    PRIMARY KEY (`id`),
    KEY `idx_grn_items_grn`  (`grn_id`),
    KEY `idx_grn_items_item` (`item_id`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4";

// ── Variant requests (new items requested during GRN) ─────────

$_kug_tables['kp_variant_requests'] = "CREATE TABLE IF NOT EXISTS `kp_variant_requests` (
    `id`          INT          NOT NULL AUTO_INCREMENT,
    `item_name`   VARCHAR(255) NOT NULL DEFAULT '',
    `brand`       VARCHAR(100) NOT NULL DEFAULT '',
    `size_val`    VARCHAR(100) NOT NULL DEFAULT '',
    `color`       VARCHAR(100) NOT NULL DEFAULT '',
    `uom`         VARCHAR(50)  NOT NULL DEFAULT '',
    `notes`       TEXT         NULL DEFAULT NULL,
    `status`      TINYINT      NOT NULL DEFAULT 0
                               COMMENT '0=pending,1=approved,2=rejected',
    `user_id`     INT          NOT NULL DEFAULT 0,
    `created_at`  DATETIME     NOT NULL DEFAULT CURRENT_TIMESTAMP,
    `approved_at` DATETIME     NULL DEFAULT NULL,
    `approved_by` INT          NOT NULL DEFAULT 0,
    PRIMARY KEY (`id`),
    KEY `idx_var_req_status` (`status`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4";

// ── Execute ───────────────────────────────────────────────────

foreach ($_kug_tables as $_tname => $_sql) {
    if (!mysqli_query($connection, $_sql)) {
        error_log("kug/schema.php: failed creating $_tname – " . mysqli_error($connection));
    }
}
unset($_kug_tables, $_tname, $_sql);
