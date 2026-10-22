<?php

if (!function_exists('menu_json_response')) {
    function menu_json_response($ok, $message, $data = [], $status_code = 200)
    {
        http_response_code((int)$status_code);
        echo json_encode([
            'ok' => (bool)$ok,
            'message' => (string)$message,
            'data' => is_array($data) ? $data : [],
        ]);
        exit;
    }
}

if (!function_exists('menu_clean_text')) {
    function menu_clean_text($value)
    {
        return trim((string)$value);
    }
}

if (!function_exists('menu_clean_int')) {
    function menu_clean_int($value)
    {
        return (int)$value;
    }
}

if (!function_exists('menu_include_capture')) {
    function menu_include_capture($path, $vars = [])
    {
        if (!is_file($path)) {
            return '';
        }

        if (is_array($vars) && count($vars) > 0) {
            extract($vars, EXTR_SKIP);
        }

        ob_start();
        include $path;
        return (string)ob_get_clean();
    }
}
