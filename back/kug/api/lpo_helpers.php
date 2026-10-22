<?php

if (!function_exists('lpo_api_json_response')) {
    function lpo_api_json_response($ok, $message, $data = [], $status_code = 200)
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

if (!function_exists('lpo_api_request_data')) {
    function lpo_api_request_data()
    {
        static $data = null;
        if ($data !== null) {
            return $data;
        }

        $data = $_POST;
        $raw = file_get_contents('php://input');
        if ($raw) {
            $decoded = json_decode($raw, true);
            if (json_last_error() === JSON_ERROR_NONE && is_array($decoded)) {
                $data = array_merge($data, $decoded);
            }
        }

        return $data;
    }
}

if (!function_exists('lpo_api_check_session')) {
    function lpo_api_check_session()
    {
        $user_id = 0;
        if (isset($_SESSION['user_id'])) {
            $user_id = (int)$_SESSION['user_id'];
        } elseif (isset($_SESSION['mwene'])) {
            $user_id = (int)$_SESSION['mwene'];
        }

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
