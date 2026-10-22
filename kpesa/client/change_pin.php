<?php
session_start();
include('../../inc/connection.php');
include('../../inc/gen_funcs.php');

header('Content-Type: application/json');

$response = ['status' => 0, 'message' => 'Could not change PIN'];

$user_id = isset($_REQUEST['user_id']) && is_numeric($_REQUEST['user_id']) ? (int)$_REQUEST['user_id'] : 0;
$pin = trim((string)($_REQUEST['pin'] ?? ''));

if ($user_id <= 0) {
    $response['message'] = 'User ID missing';
    echo json_encode($response);
    exit;
}

if ($pin === '' || !preg_match('/^\d{4,10}$/', $pin)) {
    $response['message'] = 'Enter a valid PIN';
    echo json_encode($response);
    exit;
}

try {
    //$connection = connectidentitiDB();
} catch (Throwable $e) {
    $response['message'] = 'Database connection failed';
    echo json_encode($response);
    exit;
}

$stmt = $connection->prepare("UPDATE kp_users SET pin = ? WHERE user_id = ?");
if (!$stmt) {
    $response['message'] = 'Could not prepare the PIN update.';
    echo json_encode($response);
    $connection->close();
    exit;
}

$stmt->bind_param('si', $pin, $user_id);
$stmt->execute();

if ($stmt->affected_rows > 0) {
    $response['status'] = 1;
    $response['message'] = 'PIN changed successfully';
} else {
    $existsStmt = $connection->prepare("SELECT user_id FROM kp_users WHERE user_id = ? LIMIT 1");
    if ($existsStmt) {
        $existsStmt->bind_param('i', $user_id);
        $existsStmt->execute();
        $result = $existsStmt->get_result();
        if ($result && $result->fetch_assoc()) {
            $response['status'] = 1;
            $response['message'] = 'PIN changed successfully';
        } else {
            $response['message'] = 'User not found';
        }
        $existsStmt->close();
    } else {
        $response['message'] = 'User not found';
    }
}

$stmt->close();
$connection->close();

echo json_encode($response);
?>
