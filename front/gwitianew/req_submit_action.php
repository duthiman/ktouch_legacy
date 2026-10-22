<?php
if (session_status() === PHP_SESSION_NONE) {
    session_start();
}
include('../../inc/connection.php');
include_once('../../inc/gen_funcs.php');
include_once('../chaba/funcs.php');
include_once('funcs.php');

header('Content-Type: application/json');

$user_id = (int)($_SESSION['user_id'] ?? 0);
$req_id = (int)($_SESSION['req'] ?? 0);
$shift = (string)($_SESSION['shift'] ?? get_column('kp_shift', 'shift', "1"));
$cntr_id = (int)($_POST['cntr_id'] ?? 0);
$print_mode = (int)get_print_mode();

if ($user_id < 1 || $req_id < 1) {
    echo json_encode(['ok' => false, 'message' => 'Requisition session is missing.']);
    exit;
}

if ($print_mode !== 1 && $cntr_id < 1) {
    echo json_encode(['ok' => false, 'message' => 'Please select a printer counter.']);
    exit;
}

$counter = (int)user_counter($user_id);
if ($counter < 1) {
    $counter = (int)get_column('kp_users', 'counter', "user_id='$user_id'");
}

$created_col = mysqli_query($connection, "SHOW COLUMNS FROM kp_counter_req LIKE 'created_at'");
if ($created_col && mysqli_num_rows($created_col) < 1) {
    mysqli_query($connection, "ALTER TABLE kp_counter_req ADD COLUMN created_at TIMESTAMP NOT NULL DEFAULT CURRENT_TIMESTAMP");
}
$issue_created_col = mysqli_query($connection, "SHOW COLUMNS FROM kp_counter_req LIKE 'issue_created_at'");
if ($issue_created_col && mysqli_num_rows($issue_created_col) < 1) {
    mysqli_query($connection, "ALTER TABLE kp_counter_req ADD COLUMN issue_created_at TIMESTAMP NULL DEFAULT NULL");
}
$rec_created_col = mysqli_query($connection, "SHOW COLUMNS FROM kp_counter_req LIKE 'rec_created_at'");
if ($rec_created_col && mysqli_num_rows($rec_created_col) < 1) {
    mysqli_query($connection, "ALTER TABLE kp_counter_req ADD COLUMN rec_created_at TIMESTAMP NULL DEFAULT NULL");
}

$req_exists = (int)column_count('kp_counter_req', 'req_id', "req_id='$req_id'");
if ($req_exists < 1) {
    $create_req = mysqli_query($connection, "INSERT INTO kp_counter_req(req_id,req_user,req_shift,counter) VALUES('$req_id','$user_id','$shift','$counter')");
    if (!$create_req) {
        echo json_encode(['ok' => false, 'message' => mysqli_error($connection)]);
        exit;
    }
    $action = "PLACED|-|-| REQUISITION NO|".$req_id;
    logs($action, '../logs');
}

mysqli_query($connection, "UPDATE kp_counter_req SET print_cntr_id='$cntr_id' WHERE req_id='$req_id'");
mysqli_query($connection, "DELETE FROM kp_counter_req_status WHERE req_id='$req_id'");

if ($print_mode === 1) {
    echo json_encode([
        'ok' => true,
        'redirect' => '../chaba/print_req.php'
    ]);
    exit;
}

ensure_chaba_print_system($connection);
$queue_id = kp_print_enqueue($connection, [
    'sale_id' => 0,
    'ref_id' => $req_id,
    'print_type' => 'gwitia',
    'label' => 'REQUISITION',
    'cntr_id' => $cntr_id,
    'route_path' => 'render_req.php',
    'route_query' => 'id=' . $req_id . '&user_id=' . $user_id,
    'requested_by' => $user_id,
]);

if ($queue_id < 1) {
    echo json_encode(['ok' => false, 'message' => 'Could not queue requisition print job.']);
    exit;
}

echo json_encode([
    'ok' => true,
    'message' => 'Requisition queued for printing.',
    'redirect' => '../dash/dash.php'
]);
