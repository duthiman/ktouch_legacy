<?php
require_once __DIR__ . '/_bootstrap.php';

$printed = (int)(mysqli_fetch_row(mysqli_query($connection, "SELECT COUNT(*) FROM kp_print_queue WHERE status = 1 AND DATE(printed_at) = CURDATE()"))[0] ?? 0)
    + (int)(mysqli_fetch_row(mysqli_query($connection, "SELECT COUNT(*) FROM kp_print_que_printed WHERE status = 1 AND DATE(printed_at) = CURDATE()"))[0] ?? 0);
$failed = (int)(mysqli_fetch_row(mysqli_query($connection, "SELECT COUNT(*) FROM kp_print_queue WHERE status = 2 AND DATE(created_at) = CURDATE()"))[0] ?? 0);
$pending = (int)(mysqli_fetch_row(mysqli_query($connection, "SELECT COUNT(*) FROM kp_print_queue WHERE status IN (0, 3)"))[0] ?? 0);

chaba_api_json([
    'ok' => true,
    'data' => [
        'printed_today' => $printed,
        'failed_today' => $failed,
        'pending_now' => $pending
    ]
]);
