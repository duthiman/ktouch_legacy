<?php
/**
 * Procedural logger for Billing API
 */
require_once __DIR__ . '/../../../inc/gen_funcs.php';

function bill_log($action_type, $details) {
    $log_entry = "BILLING_API|$action_type|$details|";
    // Uses the global logs function defined in gen_funcs.php
    logs($log_entry, "../../../");
}