<?php
// Repository boundary for open-orders data. SQL remains in the shared billing
// repository; this file gives the open page a small, stable data boundary.
$open_root = dirname(__DIR__, 3);
require_once __DIR__ . '/open_bill_bridge.php';

function open_repo_user_name($user_id)
{
    $user_id = (int)$user_id;
    if ($user_id < 1 || !function_exists('bill_repo_user_name')) {
        return '';
    }
    return (string)bill_repo_user_name($user_id);
}
