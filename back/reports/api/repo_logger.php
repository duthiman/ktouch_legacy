<?php

if (!function_exists('repo_log')) {
    function repo_log($action, $user_id, $success = null, $message = '')
    {
        $line = date('c') . '|' . (int)$user_id . '|' . (string)$action . '|'
            . ($success === null ? '-' : ((int)$success ? '1' : '0')) . '|' . (string)$message . PHP_EOL;
        error_log(trim($line));
    }
}
