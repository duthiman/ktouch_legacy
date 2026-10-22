<?php
session_start();
header('Content-Type: application/json');

$user_id = isset($_SESSION['user_id']) ? (int)$_SESSION['user_id'] : 0;

echo json_encode(array(
    'active' => $user_id > 0,
    'user_id' => $user_id
));
?>
