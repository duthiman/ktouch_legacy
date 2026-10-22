<?php

if (!function_exists('bills_log_event')) {
    function bills_log_event($message, array $context = []) {
        // Logger shell. Wire this to file/database logging later.
        return true;
    }
}
