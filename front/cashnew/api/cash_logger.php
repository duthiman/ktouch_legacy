<?php

if (!function_exists('cash_log_event')) {
    function cash_log_event($action, $context = []) {
        $year = date('Y');
        $month = date('F');
        $base_dir = dirname(__DIR__) . DIRECTORY_SEPARATOR . 'logs' . DIRECTORY_SEPARATOR . $year . DIRECTORY_SEPARATOR . $month;

        if (!is_dir($base_dir)) {
            @mkdir($base_dir, 0777, true);
        }

        $day = date('Y-m-d');
        $jsonl_path = $base_dir . DIRECTORY_SEPARATOR . $day . '.jsonl';
        $text_path = $base_dir . DIRECTORY_SEPARATOR . $day . '.txt';

        $entry = [
            'timestamp' => date('c'),
            'action' => (string)$action,
            'user_id' => isset($_SESSION['user_id']) ? (int)$_SESSION['user_id'] : 0,
            'uri' => isset($_SERVER['REQUEST_URI']) ? (string)$_SERVER['REQUEST_URI'] : '',
            'context' => is_array($context) ? $context : [],
        ];

        @file_put_contents($jsonl_path, json_encode($entry) . PHP_EOL, FILE_APPEND);
        @file_put_contents($text_path, date('d-M-Y h:i:sa') . '|' . $entry['action'] . '|' . json_encode($entry['context']) . PHP_EOL, FILE_APPEND);
    }
}
