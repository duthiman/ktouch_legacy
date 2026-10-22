<?php
/**
 * Temporary boundary for operations that belong to bill entry/printing.
 *
 * Open-page code must depend on this bridge only while the open-specific
 * workflows are moved into open_repo/open_service. Item entry and bill
 * printing remain owned by kwedia by design.
 */
$open_root = dirname(__DIR__, 3);
require_once $open_root . '/front/kwedia/api/funcs.php';
require_once $open_root . '/front/kwedia/api/bill_helpers.php';
require_once $open_root . '/front/kwedia/api/bill_logger.php';
require_once $open_root . '/front/kwedia/api/bill_repository.php';
require_once $open_root . '/front/kwedia/api/bill_service.php';
