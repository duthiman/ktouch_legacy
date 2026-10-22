<?php

if (!function_exists('bills_escape')) {
    function bills_escape($connection, $value) {
        return mysqli_real_escape_string($connection, (string) $value);
    }
}

if (!function_exists('bills_json')) {
    function bills_json($payload, $statusCode = 200) {
        http_response_code($statusCode);
        header('Content-Type: application/json; charset=utf-8');
        echo json_encode($payload);
        exit;
    }
}
