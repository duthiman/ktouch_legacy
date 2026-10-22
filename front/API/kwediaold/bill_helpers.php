<?php
/**
 * Helper functions for Billing API
 */

function json_response($status, $message = '', $data = []) {
    return [
        'status'  => $status,
        'message' => $message,
        'data'    => $data,
        'time'    => date('Y-m-d H:i:s')
    ];
}

function validate_bill_input($data) {
    return !empty($data);
}

/**
 * Procedural check for an active user session
 */
function check_bill_api_auth() {
    if (!isset($_SESSION['user_id']) || (int)$_SESSION['user_id'] <= 0) {
        http_response_code(401);
        echo json_encode(json_response('error', 'Unauthorized access. Please log in.'));
        exit;
    }
}

/**
 * Ensures the 'shift' column exists in kp_sale_print table.
 */
function kwedia_ensure_sale_print_shift_column() {
    global $connection;
    static $checked = false;
    if ($checked) return;
    $shiftColumn = mysqli_query($connection, "SHOW COLUMNS FROM kp_sale_print LIKE 'shift'");
    if ($shiftColumn && mysqli_num_rows($shiftColumn) === 0) {
        mysqli_query($connection, "ALTER TABLE kp_sale_print ADD COLUMN shift VARCHAR(60) NOT NULL DEFAULT '' AFTER total");
    }
    $checked = true;
}