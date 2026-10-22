<?php
include('../../inc/connection.php');

$user_id = isset($_POST['user_id']) ? intval($_POST['user_id']) : 0;
$max_sale = isset($_POST['max_sale']) ? trim($_POST['max_sale']) : '';

if ($user_id <= 0 || $max_sale === '') {
    echo '<div class="alert alert-danger">Invalid request.</div>';
    exit;
}

if (!is_numeric($max_sale) || floatval($max_sale) < 0) {
    echo '<div class="alert alert-danger">Max sale must be a valid non-negative amount.</div>';
    exit;
}

// Ensure column exists.
$column_check = mysqli_query($connection, "SHOW COLUMNS FROM kp_users LIKE 'max_sale'");
if (!$column_check) {
    echo '<div class="alert alert-danger">Database error.</div>';
    exit;
}
if (mysqli_num_rows($column_check) === 0) {
    mysqli_query($connection, "ALTER TABLE kp_users ADD COLUMN max_sale DECIMAL(12,2) NOT NULL DEFAULT 0");
}

$amount = number_format(floatval($max_sale), 2, '.', '');
$update = mysqli_query($connection, "UPDATE kp_users SET max_sale = '$amount' WHERE user_id = '$user_id'") or die(mysqli_error($connection));
if ($update) {
    echo '<div class="alert alert-success">Max sale set to KES ' . number_format($amount, 2) . ' successfully.</div>';
    echo '<script>setTimeout(function(){ window.location.href = "user.php"; }, 400);</script>';
} else {
    echo '<div class="alert alert-danger">Error saving max sale.</div>';
}
?>
