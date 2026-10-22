<?php

if (!function_exists('tots_log_event')) {
    function tots_log_event($event, $context = []) {
        error_log('TOTS_API ' . json_encode([
            'event' => (string)$event,
            'context' => is_array($context) ? $context : [],
        ]));
    }
}

