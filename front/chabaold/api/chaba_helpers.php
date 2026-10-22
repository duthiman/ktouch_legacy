<?php

if (!function_exists('chaba_api_json_response')) {
    function chaba_api_json_response($ok, $message, $data = [], $status_code = 200)
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

if (!function_exists('chaba_api_request_data')) {
    function chaba_api_request_data()
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

if (!function_exists('chaba_api_check_session')) {
    function chaba_api_check_session()
    {
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

if (!function_exists('chaba_api_clean_print_type')) {
    function chaba_api_clean_print_type($type)
    {
        $type = strtolower(trim((string)$type));
        $allowed = [
            'bill',
            'cashier_order',
            'bot',
            'kot',
            'got',
            'ret',
            'kug',
            'gwitia',
            'tots',
            'variance',
            'summary',
            'shift_sum',
            'staff_sum',
            'counter_sum',
            'cust_credit_pay',
        ];

        return in_array($type, $allowed, true) ? $type : '';
    }
}

if (!function_exists('chaba_api_clean_ticket_type')) {
    function chaba_api_clean_ticket_type($type)
    {
        $type = chaba_api_clean_print_type($type);
        $allowed = ['bot', 'kot', 'got', 'cashier_order'];
        return in_array($type, $allowed, true) ? $type : '';
    }
}

if (!function_exists('chaba_api_base_url')) {
    function chaba_api_base_url()
    {
        $https = (!empty($_SERVER['HTTPS']) && $_SERVER['HTTPS'] !== 'off') || (($_SERVER['SERVER_PORT'] ?? '') == 443);
        $scheme = $https ? 'https' : 'http';
        $host = $_SERVER['HTTP_HOST'] ?? 'localhost';
        $script_name = str_replace('\\', '/', $_SERVER['SCRIPT_NAME'] ?? '');
        $marker = '/front/chaba/api/';
        $pos = strpos($script_name, $marker);
        $base_path = $pos !== false ? substr($script_name, 0, $pos) : rtrim(dirname($script_name), '/');
        return rtrim($scheme . '://' . $host . $base_path, '/') . '/';
    }
}

if (!function_exists('chaba_api_absolute_url')) {
    function chaba_api_absolute_url($path)
    {
        if (!$path) {
            return '';
        }

        if (preg_match('#^https?://#i', $path)) {
            return $path;
        }

        $clean = ltrim(str_replace('\\', '/', $path), '/');
        return chaba_api_base_url() . $clean;
    }
}
