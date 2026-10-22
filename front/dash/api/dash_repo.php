<?php

// Dashboard repository functions live in this API folder.
$dash_repository_functions = __DIR__ . '/funcs.php';
if (file_exists($dash_repository_functions)) {
    require_once $dash_repository_functions;
}

if (!function_exists('dash_repo_load_payload')) {
    function dash_repo_load_payload($user_id)
    {
        return function_exists('dash_build_payload') ? dash_build_payload((int)$user_id) : [];
    }
}

if (!function_exists('dash_repo_load_alerts')) {
    function dash_repo_load_alerts($user_id, $message_code = null, $redirect_code = '')
    {
        return function_exists('dash_build_alerts')
            ? dash_build_alerts((int)$user_id, $message_code, (string)$redirect_code)
            : [];
    }
}
