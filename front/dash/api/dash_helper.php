<?php

if (!function_exists('dash_json_response')) {
    function dash_json_response($success, $message, $data = [], $status = 200)
    {
        http_response_code((int)$status);
        echo json_encode([
            'success' => (bool)$success,
            'message' => (string)$message,
            'data' => is_array($data) ? $data : [],
        ]);
        exit;
    }
}

if (!function_exists('dash_expire_session')) {
    function dash_expire_session()
    {
        $_SESSION = [];
        if (ini_get('session.use_cookies')) {
            $params = session_get_cookie_params();
            setcookie(session_name(), '', time() - 42000, $params['path'], $params['domain'], $params['secure'], $params['httponly']);
        }
        session_destroy();
    }
}

if (!function_exists('dash_current_user_id')) {
    function dash_current_user_id()
    {
        return isset($_SESSION['user_id']) ? (int)$_SESSION['user_id'] : 0;
    }
}

