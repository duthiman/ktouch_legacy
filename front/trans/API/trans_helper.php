<?php

if (!function_exists('trans_clean_text')) {
    function trans_clean_text($value)
    {
        return trim((string)$value);
    }
}

if (!function_exists('trans_clean_int')) {
    function trans_clean_int($value)
    {
        return (int)$value;
    }
}

if (!function_exists('trans_clean_number')) {
    function trans_clean_number($value)
    {
        return (float)$value;
    }
}

if (!function_exists('trans_json_response')) {
    function trans_json_response($ok, $message, $data = [], $status = 200)
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

if (!function_exists('trans_escape')) {
    function trans_escape($value)
    {
        return htmlspecialchars((string)$value, ENT_QUOTES, 'UTF-8');
    }
}
