<?php

if (!function_exists('gwit_clean_text')) {
    function gwit_clean_text($value)
    {
        return trim((string)$value);
    }
}

if (!function_exists('gwit_clean_int')) {
    function gwit_clean_int($value)
    {
        return (int)$value;
    }
}

if (!function_exists('gwit_clean_float')) {
    function gwit_clean_float($value)
    {
        return (float)$value;
    }
}

if (!function_exists('gwit_escape')) {
    function gwit_escape($value)
    {
        return htmlspecialchars((string)$value, ENT_QUOTES, 'UTF-8');
    }
}

if (!function_exists('gwit_json_response')) {
    function gwit_json_response($ok, $message, $data = [], $status = 200)
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
