<?php

if (!function_exists('dash_service_load')) {
    function dash_service_load($user_id, $message_code = null, $redirect_code = '')
    {
        $payload = dash_repo_load_payload((int)$user_id);
        $payload['alerts'] = dash_repo_load_alerts((int)$user_id, $message_code, $redirect_code);
        return [
            'ok' => true,
            'message' => 'Dashboard data loaded',
            'data' => $payload,
        ];
    }
}

