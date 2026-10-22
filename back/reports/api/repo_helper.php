<?php

if (!function_exists('repo_json')) {
    function repo_json($ok, $message, $data = [], $status = 200)
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

if (!function_exists('repo_int')) {
    function repo_int($value) { return (int)$value; }
}

if (!function_exists('repo_text')) {
    function repo_text($value) { return trim((string)$value); }
}
