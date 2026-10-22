<?php

if (!function_exists('recipe_json_response')) {
    function recipe_json_response($ok, $message, $data = [], $status_code = 200)
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

if (!function_exists('recipe_clean_int')) {
    function recipe_clean_int($value)
    {
        return (int)$value;
    }
}

if (!function_exists('recipe_clean_text')) {
    function recipe_clean_text($value)
    {
        return trim((string)$value);
    }
}
