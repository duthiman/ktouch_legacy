<?php
require_once __DIR__ . '/_bootstrap.php';

if ($_SERVER['REQUEST_METHOD'] === 'GET') {
    $rows = [];
    $result = mysqli_query($connection, "SELECT cntr_id, name, dpt, printer_name FROM kp_counters WHERE del_status = 2 ORDER BY dpt ASC, name ASC");
    while ($result && ($row = mysqli_fetch_assoc($result))) {
        $rows[] = [
            'cntr_id' => (int)$row['cntr_id'],
            'name' => (string)$row['name'],
            'dpt' => (int)$row['dpt'],
            'printer_name' => trim((string)($row['printer_name'] ?? ''))
        ];
    }

    chaba_api_json([
        'ok' => true,
        'data' => [
            'counters' => $rows
        ]
    ]);
}

$data = chaba_api_request_data();
$counters = $data['counters'] ?? [];
if (!is_array($counters)) {
    chaba_api_json(['ok' => false, 'message' => 'Counters payload is required'], 400);
}

foreach ($counters as $counter) {
    $cntrId = (int)($counter['cntr_id'] ?? 0);
    $printerName = mysqli_real_escape_string($connection, trim((string)($counter['printer_name'] ?? '')));
    if ($cntrId < 1) {
        continue;
    }
    mysqli_query($connection, "UPDATE kp_counters SET printer_name='$printerName' WHERE cntr_id='$cntrId'");
}

chaba_api_json(['ok' => true, 'message' => 'Counter printers updated']);
