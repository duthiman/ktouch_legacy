<?php

if (!function_exists('trans_log_action')) {
    function trans_log_action($message, $path = '../logs')
    {
        if (function_exists('logs')) {
            logs((string)$message, $path);
        }
    }
}
