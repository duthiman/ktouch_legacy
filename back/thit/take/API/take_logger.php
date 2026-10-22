<?php
function take_logger_log($event, $context = []) {
    $message = (string)$event;
    if (is_array($context) && count($context)) {
        $message .= ' ' . json_encode($context);
    }
    if (function_exists('logs')) {
        logs($message, '../../../logs');
    }
    error_log('[stock-take] ' . $message);
}
