<?php
function take_clean_text($value) { return trim((string)$value); }
function take_clean_int($value) { return (int)$value; }
function take_clean_float($value) { return (float)$value; }
function take_user_id() { return isset($_SESSION['user_id']) ? (int)$_SESSION['user_id'] : 0; }
function take_json_response($ok, $message, $data = [], $status = 200) {
    http_response_code((int)$status);
    echo json_encode(['ok' => (bool)$ok, 'message' => (string)$message, 'data' => is_array($data) ? $data : []]);
    exit;
}
