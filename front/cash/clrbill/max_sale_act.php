<?php
session_start();
include('../../../inc/connection.php');

header('Content-Type: application/json');

$user_id = isset($_POST['user_id']) ? intval($_POST['user_id']) : 0;
$max_sale = isset($_POST['max_sale']) ? trim($_POST['max_sale']) : '';

if ($user_id <= 0 || $max_sale === '') {
    echo json_encode(['status' => 'error', 'message' => 'Invalid request.']);
    exit;
}
if (!is_numeric($max_sale) || floatval($max_sale) < 0) {
    echo json_encode(['status' => 'error', 'message' => 'Max sale must be a valid non-negative number.']);
    exit;
}

$column_check = mysqli_query($connection, "SHOW COLUMNS FROM kp_users LIKE 'max_sale'");
if (!$column_check) {
    echo json_encode(['status' => 'error', 'message' => 'Database error.']);
    exit;
}
if (mysqli_num_rows($column_check) === 0) {
    mysqli_query($connection, "ALTER TABLE kp_users ADD COLUMN max_sale DECIMAL(12,2) NOT NULL DEFAULT 0");
}

$amount = number_format(floatval($max_sale), 2, '.', '');
$update = mysqli_query($connection, "UPDATE kp_users SET max_sale = '$amount' WHERE user_id = '$user_id'");
if ($update) {
    echo json_encode(['status' => 'success', 'message' => 'Max sale increased to KES ' . number_format($amount, 2) . '.']);
} else {
    echo json_encode(['status' => 'error', 'message' => 'Unable to update max sale.']);
}
