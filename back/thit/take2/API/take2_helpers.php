<?php
function take2_json($ok, $message = '', $data = []) {
    header('Content-Type: application/json; charset=utf-8');
    echo json_encode(['ok' => (bool)$ok, 'message' => (string)$message, 'data' => $data], JSON_UNESCAPED_UNICODE);
    exit;
}

function take2_user_id() {
    foreach (['user_id', 'id', 'userid'] as $key) {
        if (isset($_SESSION[$key]) && is_numeric($_SESSION[$key])) return (int)$_SESSION[$key];
    }
    return 0;
}

function take2_post($key, $default = '') {
    return isset($_POST[$key]) ? trim((string)$_POST[$key]) : $default;
}

function take2_escape($value) {
    global $connection;
    return mysqli_real_escape_string($connection, (string)$value);
}

function take2_event($takeId, $type, $notes = '') {
    global $connection;
    $takeId = (int)$takeId;
    $userId = take2_user_id();
    $type = take2_escape($type);
    $notes = take2_escape($notes);
    mysqli_query($connection, "INSERT INTO kp_stock_take_events (take_id,user_id,event_type,notes) VALUES ($takeId,$userId,'$type','$notes')");
}
