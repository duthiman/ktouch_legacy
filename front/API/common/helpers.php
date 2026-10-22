<?php

if (!function_exists('common_json_response')) {
    function common_json_response($ok, $message, $data = [], $status_code = 200) {
        http_response_code((int)$status_code);
        echo json_encode([
            'ok' => (bool)$ok,
            'message' => (string)$message,
            'data' => is_array($data) ? $data : [],
        ]);
        exit;
    }
}

if (!function_exists('common_check_session')) {
    function common_check_session() {
        $user_id = isset($_SESSION['user_id']) ? (int)$_SESSION['user_id'] : 0;

        if ($user_id < 1) {
            return [
                'ok' => false,
                'message' => 'Session expired. Please login again.',
                'user_id' => 0,
            ];
        }

        return [
            'ok' => true,
            'message' => 'Session is active.',
            'user_id' => $user_id,
        ];
    }
}

if (!function_exists('common_clean_text')) {
    function common_clean_text($value) {
        return trim((string)$value);
    }
}

if (!function_exists('common_clean_int')) {
    function common_clean_int($value) {
        return (int)$value;
    }
}

if (!function_exists('common_clean_bool_flag')) {
    function common_clean_bool_flag($value) {
        return in_array((string)$value, ['1', 'true', 'yes'], true) ? 1 : 0;
    }
}

?>
