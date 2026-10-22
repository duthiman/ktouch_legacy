<?php
require_once __DIR__ . '/_bootstrap.php';

$pending = (int)(mysqli_fetch_row(mysqli_query($connection, "SELECT COUNT(*) FROM kp_print_queue WHERE status = 0"))[0] ?? 0);
$claimed = (int)(mysqli_fetch_row(mysqli_query($connection, "SELECT COUNT(*) FROM kp_print_queue WHERE status = 3"))[0] ?? 0);
$printedToday = (int)(mysqli_fetch_row(mysqli_query($connection, "SELECT COUNT(*) FROM kp_print_queue WHERE status = 1 AND DATE(printed_at) = CURDATE()"))[0] ?? 0)
    + (int)(mysqli_fetch_row(mysqli_query($connection, "SELECT COUNT(*) FROM kp_print_que_printed WHERE status = 1 AND DATE(printed_at) = CURDATE()"))[0] ?? 0);

chaba_api_json([
    'ok' => true,
    'message' => 'Chaba API reachable',
    'time' => date('c'),
    'data' => [
        'pending_jobs' => $pending,
        'claimed_jobs' => $claimed,
        'printed_today' => $printedToday
    ]
]);
