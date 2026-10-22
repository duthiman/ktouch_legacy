<?php

require_once __DIR__ . '/bill_repository.php';
require_once __DIR__ . '/bill_logger.php';
require_once __DIR__ . '/bill_service.php';

if (!function_exists('kw_init_order')) {
    function kw_init_order($user_id, $counter, $status, $sale_id = 0) {
        return bill_service_initialise($user_id, $counter, $status, $sale_id);
    }
}
