<?php
require_once __DIR__ . '/_bootstrap.php';

$cntrId = (int)($_GET['cntr_id'] ?? 0);
$allMode = (int)($_GET['all'] ?? 1);
$where = $allMode === 1 || $cntrId < 1
    ? "q.status = 0"
    : "q.cntr_id = '" . $cntrId . "' AND q.status = 0";

$sql = "SELECT q.queue_id, q.sale_id, q.ref_id, q.print_type, q.label, q.cntr_id, q.route_path, q.route_query,
               q.reprint_of, q.created_at, c.name AS counter_name, c.printer_name
        FROM kp_print_queue q
        LEFT JOIN kp_counters c ON c.cntr_id = q.cntr_id
        WHERE $where
        ORDER BY q.queue_id ASC
        LIMIT 20";
$result = mysqli_query($connection, $sql);
$jobs = [];
while ($result && ($row = mysqli_fetch_assoc($result))) {
    if (function_exists('kp_print_resolve_printer_name')) {
        $resolved = kp_print_resolve_printer_name($connection, (string)($row['print_type'] ?? ''), (int)($row['cntr_id'] ?? 0));
        if ($resolved) {
            $row['printer_name'] = $resolved;
        }
    }

    $jobs[] = [
        'id' => (int)$row['queue_id'],
        'queue_id' => (int)$row['queue_id'],
        'sale_id' => (int)$row['sale_id'],
        'ref_id' => (int)$row['ref_id'],
        'printer_role' => (string)$row['print_type'],
        'print_type' => (string)$row['print_type'],
        'label' => (string)$row['label'],
        'counter_id' => (int)$row['cntr_id'],
        'counter_name' => (string)($row['counter_name'] ?? ''),
        'printer_name' => trim((string)($row['printer_name'] ?? '')),
        'document_url' => chaba_api_render_url($row),
        'created_at' => (string)$row['created_at'],
        'copies' => 1
    ];
}

chaba_api_json([
    'ok' => true,
    'data' => $jobs
]);
