<?php
require_once __DIR__ . '/../../../inc/gen_funcs.php';

if (!function_exists('bill_log')) {
    function bill_log($action_type, $details = '') {
        $entry = 'BILL|' . trim((string)$action_type) . '|' . trim((string)$details) . '|';
        logs($entry, "../../../");
    }
}

?>
