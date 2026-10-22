<?php

if (!function_exists('ret_log_action')) {
    function ret_log_action($message, $path = '../logs')
    {
        if (function_exists('logs')) {
            logs((string)$message, $path);
        }
    }
}
