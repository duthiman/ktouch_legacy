<?php

if (!function_exists('view_log_action')) {
    function view_log_action($message, $path = '../logs')
    {
        if (function_exists('logs')) {
            logs((string)$message, $path);
        }
    }
}
