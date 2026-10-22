<?php
session_start();
include('../../inc/connection.php');
header('Content-Type: application/json');

try {
    $con = $connection;//connectidentitiDB();
    mysqli_query($con, "CREATE TABLE IF NOT EXISTS kp_pdq_terminals (
        terminal_id INT NOT NULL AUTO_INCREMENT,
        bank_name VARCHAR(120) NOT NULL,
        card_label VARCHAR(120) NOT NULL,
        terminal_code VARCHAR(80) NOT NULL DEFAULT '',
        status INT NOT NULL DEFAULT 2,
        created_at DATETIME NOT NULL DEFAULT CURRENT_TIMESTAMP,
        PRIMARY KEY (terminal_id),
        KEY idx_status (status)
    ) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci");

    $rows = [];
    $q = mysqli_query($con, "SELECT terminal_id, bank_name, card_label, terminal_code FROM kp_pdq_terminals WHERE status=2 ORDER BY bank_name ASC, card_label ASC");
    if ($q) while($r = mysqli_fetch_assoc($q)) $rows[] = $r;

    echo json_encode(['status'=>1, 'message'=>'Card terminals fetched', 'terminals'=>$rows]);
} catch (Exception $e) {
    echo json_encode(['status'=>0, 'message'=>$e->getMessage(), 'terminals'=>[]]);
}
