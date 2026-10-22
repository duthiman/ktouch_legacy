<?php
include('../../inc/connection.php');
include('../../inc/gen_funcs.php');

header('Content-Type: application/json; charset=utf-8');

//$connection = connectidentitiDB();
if (!$connection) {
    echo json_encode([
        'status' => 0,
        'message' => 'Database connection failed'
    ]);
    exit;
}

$username = trim((string)($_REQUEST['username'] ?? ''));
$pin = trim((string)($_REQUEST['pin'] ?? ''));
$role_id = 2; // KPESA client quick add creates waiters only.

if ($username === '') {
    echo json_encode([
        'status' => 0,
        'message' => 'Username missing'
    ]);
    exit;
}

if ($pin === '') {
    echo json_encode([
        'status' => 0,
        'message' => 'PIN missing'
    ]);
    exit;
}

if (!preg_match('/^[A-Za-z0-9_.@-]{3,40}$/', $username)) {
    echo json_encode([
        'status' => 0,
        'message' => 'Invalid username format'
    ]);
    exit;
}

if (!preg_match('/^\d{4,8}$/', $pin)) {
    echo json_encode([
        'status' => 0,
        'message' => 'PIN must be 4 to 8 digits'
    ]);
    exit;
}

$usernameEscaped = mysqli_real_escape_string($connection, $username);
$pinEscaped = mysqli_real_escape_string($connection, $pin);

$checkUsername = mysqli_query($connection, "SELECT user_id FROM kp_users WHERE username='$usernameEscaped' LIMIT 1");
if ($checkUsername && mysqli_num_rows($checkUsername) > 0) {
    echo json_encode([
        'status' => 0,
        'message' => 'Username already exists'
    ]);
    exit;
}

$checkPin = mysqli_query($connection, "SELECT user_id FROM kp_users WHERE pin='$pinEscaped' OR cabi='$pinEscaped' LIMIT 1");
if ($checkPin && mysqli_num_rows($checkPin) > 0) {
    echo json_encode([
        'status' => 0,
        'message' => 'PIN already exists'
    ]);
    exit;
}

$stmt = $connection->prepare("INSERT INTO kp_users (username, cabi, pin, role_id, sys_default, work_status, log_status, target) VALUES (?, ?, ?, ?, 2, 2, 2, 0)");
if (!$stmt) {
    echo json_encode([
        'status' => 0,
        'message' => 'Could not prepare user insert'
    ]);
    exit;
}

$pinInt = (int)$pin;
$stmt->bind_param('ssii', $username, $pin, $pinInt, $role_id);
$ok = $stmt->execute();
$stmt->close();

if ($ok) {
    echo json_encode([
        'status' => 1,
        'message' => 'Waiter added successfully',
        'role_id' => $role_id
    ]);
} else {
    echo json_encode([
        'status' => 0,
        'message' => 'A database error occured. Please contact support'
    ]);
}
?>
