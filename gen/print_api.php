<?php
session_start();
include('../inc/connection.php');
include('../front/chaba/funcs.php');

ensure_chaba_print_system($connection);

header('Content-Type: application/json');
$action = isset($_GET['action']) ? trim((string)$_GET['action']) : '';

if ($action === 'pending') {
    $cntr_id = (int)($_GET['cntr_id'] ?? 0);
    $all_mode = (int)($_GET['all'] ?? 0);
    if ($cntr_id < 1 && $all_mode !== 1) {
        echo json_encode([]);
        exit;
    }
    $where = $all_mode === 1
        ? "q.status = 0"
        : "q.cntr_id = '$cntr_id' AND q.status = 0";
    $r = mysqli_query(
        $connection,
        "SELECT q.queue_id, q.sale_id, q.ref_id, q.print_type, q.label, q.cntr_id, q.route_path, q.route_query,
                q.reprint_of, q.created_at, c.name AS counter_name, c.printer_name
         FROM kp_print_queue q
         LEFT JOIN kp_counters c ON c.cntr_id = q.cntr_id
         WHERE $where
         ORDER BY q.queue_id ASC
         LIMIT 10"
    );
    $jobs = [];
    while ($r && ($row = mysqli_fetch_assoc($r))) {
        if (function_exists('kp_print_resolve_printer_name')) {
            $row['printer_name'] = kp_print_resolve_printer_name(
                $connection,
                (string)($row['print_type'] ?? ''),
                (int)($row['cntr_id'] ?? 0)
            );
        }
        $jobs[] = $row;
    }
    echo json_encode($jobs);
    exit;
}

if ($action === 'claim') {
    $queue_id = (int)($_GET['queue_id'] ?? 0);
    if ($queue_id < 1) {
        echo json_encode(['ok' => false, 'message' => 'invalid queue_id']);
        exit;
    }

    mysqli_begin_transaction($connection);
    $claim_q = mysqli_query(
        $connection,
        "SELECT queue_id
         FROM kp_print_queue
         WHERE queue_id = '$queue_id' AND status = 0
         LIMIT 1
         FOR UPDATE"
    );

    if (!$claim_q || mysqli_num_rows($claim_q) < 1) {
        mysqli_rollback($connection);
        echo json_encode(['ok' => false, 'message' => 'job already claimed']);
        exit;
    }

    mysqli_query(
        $connection,
        "UPDATE kp_print_queue
         SET status = 3
         WHERE queue_id = '$queue_id' AND status = 0"
    );
    $claimed = mysqli_affected_rows($connection) > 0;
    if ($claimed) {
        mysqli_commit($connection);
    } else {
        mysqli_rollback($connection);
    }

    echo json_encode(['ok' => $claimed]);
    exit;
}

if ($action === 'done') {
    $queue_id = (int)($_GET['queue_id'] ?? 0);
    if ($queue_id > 0) {
        mysqli_query(
            $connection,
            "UPDATE kp_print_queue
             SET status = 1, printed_at = NOW(), last_error = ''
             WHERE queue_id = '$queue_id' AND status = 3"
        );
        if (mysqli_affected_rows($connection) > 0) {
            mysqli_query($connection, "DELETE FROM kp_print_job_items WHERE queue_id='$queue_id'");
        }
    }
    echo json_encode(['ok' => true]);
    exit;
}

if ($action === 'fail') {
    $queue_id = (int)($_GET['queue_id'] ?? 0);
    $max_attempts = max(1, (int)($_GET['max_attempts'] ?? 5));
    $error = trim((string)($_GET['error'] ?? 'Print job failed'));
    if ($queue_id > 0) {
        $escaped = mysqli_real_escape_string($connection, mb_substr($error, 0, 240));
        mysqli_query(
            $connection,
            "UPDATE kp_print_queue
             SET status = IF(print_attempts + 1 >= '$max_attempts', 2, 0),
                 print_attempts = print_attempts + 1,
                 last_error = '$escaped'
             WHERE queue_id = '$queue_id' AND status = 3"
        );
    }
    echo json_encode(['ok' => true]);
    exit;
}

if ($action === 'counters') {
    $r = mysqli_query(
        $connection,
        "SELECT cntr_id, name, dpt, printer_name
         FROM kp_counters
         WHERE del_status = 2
         ORDER BY dpt ASC, cntr_id ASC"
    );
    $counters = [];
    while ($r && ($row = mysqli_fetch_assoc($r))) {
        $counters[] = $row;
    }
    echo json_encode($counters);
    exit;
}

if ($action === 'history') {
    $r = mysqli_query(
        $connection,
        "SELECT q.queue_id, q.ref_id, q.sale_id, q.print_type, q.label, q.cntr_id, q.status, q.reprint_of,
                q.print_attempts, q.last_error, q.printed_at, q.created_at, c.name AS counter_name
         FROM kp_print_queue q
         LEFT JOIN kp_counters c ON c.cntr_id = q.cntr_id
         ORDER BY q.queue_id DESC
         LIMIT 80"
    );
    $rows = [];
    while ($r && ($row = mysqli_fetch_assoc($r))) {
        $rows[] = $row;
    }
    echo json_encode($rows);
    exit;
}

echo json_encode(['error' => 'invalid action']);
