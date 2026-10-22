<?php
require_once __DIR__ . '/_bootstrap.php';

$result = mysqli_query(
    $connection,
    "SELECT q.queue_id, q.ref_id, q.sale_id, q.print_type, q.label, q.cntr_id, q.status, q.reprint_of,
            q.print_attempts, q.last_error, q.printed_at, q.created_at, c.name AS counter_name
     FROM kp_print_que_printed q
     LEFT JOIN kp_counters c ON c.cntr_id = q.cntr_id
     ORDER BY q.queue_id DESC
     LIMIT 100"
);
$rows = [];
while ($result && ($row = mysqli_fetch_assoc($result))) {
    $rows[] = $row;
}

chaba_api_json(['ok' => true, 'data' => $rows]);
