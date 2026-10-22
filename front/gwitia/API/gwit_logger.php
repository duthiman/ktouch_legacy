<?php

if (!function_exists('gwit_log_action')) {
    function gwit_log_action($message, $path = '../logs')
    {
        if (function_exists('logs')) {
            logs((string)$message, $path);
        }
    }
}
