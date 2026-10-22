<?php

if (!function_exists('inventory_clean_text')) {
    function inventory_clean_text($value)
    {
        return trim((string)$value);
    }
}

if (!function_exists('inventory_clean_int')) {
    function inventory_clean_int($value)
    {
        return (int)$value;
    }
}

if (!function_exists('inventory_clean_float')) {
    function inventory_clean_float($value)
    {
        return (float)$value;
    }
}

if (!function_exists('inventory_current_user_id')) {
    function inventory_current_user_id()
    {
        return isset($_SESSION['user_id']) ? (int)$_SESSION['user_id'] : 0;
    }
}

if (!function_exists('inventory_json_response')) {
    function inventory_json_response($ok, $message, $data = [], $status = 200)
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
