<?php

if (!function_exists('cash_api_json_response')) {
    function cash_api_json_response($ok, $message, $data = [], $status_code = 200) {
        http_response_code((int)$status_code);
        echo json_encode([
            'ok' => (bool)$ok,
            'message' => (string)$message,
            'data' => is_array($data) ? $data : [],
        ]);
        exit;
    }
}

if (!function_exists('cash_api_clean_text')) {
    function cash_api_clean_text($value) {
        return trim((string)$value);
    }
}

if (!function_exists('cash_api_clean_int')) {
    function cash_api_clean_int($value) {
        return (int)$value;
    }
}

if (!function_exists('cash_api_clean_float')) {
    function cash_api_clean_float($value) {
        return (float)$value;
    }
}

if (!function_exists('cash_api_request_array')) {
    function cash_api_request_array($value) {
        return is_array($value) ? $value : [];
    }
}
