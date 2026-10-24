<?php

/*
|--------------------------------------------------------------------------
| BILL HELPERS
|--------------------------------------------------------------------------
| What belongs here:
| - Small reusable utility functions
| - JSON response helpers
| - Session helper
| - Input cleanup / normalization
*/

if (!function_exists('bill_json_response')) {
    function bill_json_response($ok, $message, $data = [], $status_code = 200) {
        http_response_code((int)$status_code);
        echo json_encode([
            'ok' => (bool)$ok,
            'message' => (string)$message,
            'data' => is_array($data) ? $data : [],
        ]);
        exit;
    }
}

if (!function_exists('bill_check_session')) {
    function bill_check_session() {
        $user_id = isset($_SESSION['user_id']) ? (int)$_SESSION['user_id'] : 0;

        if ($user_id < 1) {
            return [
                'ok' => false,
                'message' => 'Session expired. Please login again.',
                'user_id' => 0,
            ];
        }

        return [
            'ok' => true,
            'message' => 'Session is active.',
            'user_id' => $user_id,
        ];
    }
}

if (!function_exists('bill_clean_text')) {
    function bill_clean_text($value) {
        return trim((string)$value);
    }
}

if (!function_exists('bill_clean_bill_mode')) {
    function bill_clean_bill_mode($mode) {
        $mode = strtolower(trim((string)$mode));
        return in_array($mode, ['fresh', 'each', 'last'], true) ? $mode : 'fresh';
    }
}

if (!function_exists('bill_normalize_sale_status_row')) {
    function bill_normalize_sale_status_row($row) {
        return [
            'sale_id' => (int)($row['sale_id'] ?? 0),
            'user_id' => (int)($row['user_id'] ?? 0),
            'status' => (int)($row['status'] ?? 0),
            'mode' => bill_clean_bill_mode($row['mode'] ?? 'fresh'),
            'sale_type' => strtolower(trim((string)($row['sale_type'] ?? 'normal'))) === 'open' ? 'open' : 'normal',
            'parent_status' => (int)($row['parent_status'] ?? 2),
            'parent_sale_id' => (int)($row['parent_sale_id'] ?? 0),
        ];
    }
}

if (!function_exists('bill_detect_scenario')) {
    function bill_detect_scenario($status_row) {
        $row = bill_normalize_sale_status_row($status_row);

        if ($row['status'] === 2 && $row['mode'] === 'fresh' && $row['parent_status'] === 2 && $row['parent_sale_id'] === 0) {
            return 'fresh_closed';
        }

        if ($row['status'] === 3 && $row['mode'] === 'last' && $row['parent_status'] === 2 && $row['parent_sale_id'] === 0) {
            return 'fresh_open_last';
        }

        if ($row['status'] === 2 && $row['mode'] === 'each' && $row['parent_status'] === 2 && $row['parent_sale_id'] === 0) {
            return 'fresh_open_each';
        }

        if ($row['status'] === 3 && $row['mode'] === 'last' && $row['parent_status'] === 1 && $row['parent_sale_id'] > 0) {
            return 'child_open_last';
        }

        if ($row['status'] === 2 && $row['mode'] === 'each' && $row['parent_status'] === 1 && $row['parent_sale_id'] > 0) {
            return 'child_open_each';
        }

        return '';
    }
}

?>
