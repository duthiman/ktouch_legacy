<?php

if (!function_exists('view_clean_text')) {
    function view_clean_text($value)
    {
        return trim((string)$value);
    }
}

if (!function_exists('view_clean_int')) {
    function view_clean_int($value)
    {
        return (int)$value;
    }
}

if (!function_exists('view_json_response')) {
    function view_json_response($ok, $message, $data = [], $status = 200)
    {
        http_response_code((int)$status);
        echo json_encode([
            'ok' => (bool)$ok,
            'message' => (string)$message,
            'data' => is_array($data) ? $data : [],
        ]);
        exit;
    }
}

if (!function_exists('view_escape')) {
    function view_escape($value)
    {
        return htmlspecialchars((string)$value, ENT_QUOTES, 'UTF-8');
    }
}
