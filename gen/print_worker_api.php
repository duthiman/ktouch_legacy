<?php
/**
 * JSON API for admin dashboard: print worker status, recent queue, start worker (Windows).
 */
session_start();
header('Content-Type: application/json; charset=UTF-8');

include __DIR__ . '/../inc/connection.php';

if (!isset($_SESSION['user_id'])) {
    echo json_encode(['ok' => false, 'error' => 'unauthorized']);
    exit;
}

$heartbeatFile = dirname(__DIR__) . '/tools/print_worker.heartbeat';
$configFile = dirname(__DIR__) . '/tools/print_worker.config.php';

$action = isset($_REQUEST['action']) ? trim((string) $_REQUEST['action']) : '';

if ($action === 'status') {
    $running = false;
    $heartbeat_age = null;
    if (is_file($heartbeatFile)) {
        $raw = @file_get_contents($heartbeatFile);
        $ts = (int) trim((string) $raw);
        if ($ts > 0) {
            $heartbeat_age = time() - $ts;
            // Allow long PDF/print jobs without a new heartbeat (worker writes once per loop).
            $running = $heartbeat_age >= 0 && $heartbeat_age <= 120;
        }
    }
    $pending = 0;
    $processing = 0;
    $failed = 0;
    if (dash_pw_table_exists($connection, 'kp_print_queue')) {
        $r = mysqli_query($connection, "SELECT COUNT(*) AS n FROM kp_print_queue WHERE status = 0");
        if ($r && ($row = mysqli_fetch_assoc($r))) {
            $pending = (int) $row['n'];
        }
        $r2 = mysqli_query($connection, "SELECT COUNT(*) AS n FROM kp_print_queue WHERE status = 3");
        if ($r2 && ($row2 = mysqli_fetch_assoc($r2))) {
            $processing = (int) $row2['n'];
        }
        $r3 = mysqli_query($connection, "SELECT COUNT(*) AS n FROM kp_print_queue WHERE status = 2");
        if ($r3 && ($row3 = mysqli_fetch_assoc($r3))) {
            $failed = (int) $row3['n'];
        }
    }
    $not_printed_total = $pending + $processing;
    echo json_encode([
        'ok' => true,
        'running' => $running,
        'heartbeat_age_seconds' => $heartbeat_age,
        'pending' => $pending,
        'processing' => $processing,
        'failed' => $failed,
        'not_printed_total' => $not_printed_total,
        'config_present' => is_file($configFile),
        'server_host' => (string) ($_SERVER['HTTP_HOST'] ?? ''),
        'server_name' => (string) ($_SERVER['SERVER_NAME'] ?? ''),
        'server_ip' => (string) ($_SERVER['SERVER_ADDR'] ?? ''),
        'browser_ip' => (string) ($_SERVER['REMOTE_ADDR'] ?? ''),
    ]);
    exit;
}

if ($action === 'history') {
    dash_pw_ensure_queue_cols($connection);
    $jobs = [];
    if (dash_pw_table_exists($connection, 'kp_print_queue')) {
        $base = "SELECT q.queue_id, q.sale_id, q.print_type, q.status, q.created_at,
                q.print_attempts, COALESCE(q.last_error,'') AS last_error,
                COALESCE(NULLIF(TRIM(c.printer_name),''), '') AS printer_name,
                COALESCE(c.name,'') AS counter_name
                FROM kp_print_queue q
                LEFT JOIN kp_counters c ON c.cntr_id = q.cntr_id";
        $seen = [];
        $rq = mysqli_query($connection, $base . ' WHERE q.status IN (0,3) ORDER BY q.queue_id ASC LIMIT 50');
        if ($rq) {
            while ($row = mysqli_fetch_assoc($rq)) {
                $id = (int) $row['queue_id'];
                $seen[$id] = true;
                dash_pw_enrich_queue_row($row);
                $jobs[] = $row;
            }
        }
        $rq2 = mysqli_query($connection, $base . ' WHERE q.status IN (1,2) ORDER BY q.queue_id DESC LIMIT 35');
        if ($rq2) {
            while ($row = mysqli_fetch_assoc($rq2)) {
                $id = (int) $row['queue_id'];
                if (isset($seen[$id])) {
                    continue;
                }
                dash_pw_enrich_queue_row($row);
                $jobs[] = $row;
            }
        }
    }
    echo json_encode(['ok' => true, 'jobs' => $jobs]);
    exit;
}

if ($action === 'start') {
    if (strtoupper(substr(PHP_OS, 0, 3)) !== 'WIN') {
        echo json_encode(['ok' => false, 'message' => 'Start from dashboard is only supported on Windows. Run print_worker.php manually on this server.']);
        exit;
    }
    $running = false;
    if (is_file($heartbeatFile)) {
        $ts = (int) trim((string) @file_get_contents($heartbeatFile));
        if ($ts > 0 && (time() - $ts) <= 120) {
            $running = true;
        }
    }
    if ($running) {
        echo json_encode(['ok' => true, 'message' => 'Print worker is already running.', 'already' => true]);
        exit;
    }
    if (!is_file($configFile)) {
        echo json_encode(['ok' => false, 'message' => 'Missing tools/print_worker.config.php']);
        exit;
    }
    $workerScript = dirname(__DIR__) . DIRECTORY_SEPARATOR . 'tools' . DIRECTORY_SEPARATOR . 'print_worker.php';
    if (!is_file($workerScript)) {
        echo json_encode(['ok' => false, 'message' => 'print_worker.php not found.']);
        exit;
    }
    $cfg = require $configFile;
    $phpBin = dash_pw_resolve_php_binary($cfg);
    if ($phpBin === '' || !is_file($phpBin)) {
        echo json_encode(['ok' => false, 'message' => 'PHP binary not found. Set php_binary in print_worker.config.php']);
        exit;
    }
    $workerScriptReal = realpath($workerScript);
    if ($workerScriptReal === false) {
        $workerScriptReal = $workerScript;
    }
    // Detached console window (minimized). Worker mutex prevents duplicate instances.
    $title = 'KTouchPrintWorker';
    $cmd = 'cmd /c start "' . str_replace('"', '', $title) . '" /MIN '
        . escapeshellarg($phpBin) . ' '
        . escapeshellarg($workerScriptReal);
    @pclose(@popen($cmd, 'r'));
    echo json_encode(['ok' => true, 'message' => 'Start command sent. Status should show running within a few seconds if PHP can launch.']);
    exit;
}

if ($action === 'stop') {
    if (strtoupper(substr(PHP_OS, 0, 3)) !== 'WIN') {
        echo json_encode(['ok' => false, 'message' => 'Stop from dashboard is only supported on Windows. Close the worker console or end the PHP process on this server.']);
        exit;
    }
    $stopPath = dirname(__DIR__) . DIRECTORY_SEPARATOR . 'tools' . DIRECTORY_SEPARATOR . 'print_worker.stop';
    @file_put_contents($stopPath, (string) time(), LOCK_EX);
    echo json_encode([
        'ok' => true,
        'message' => 'Stop requested. The worker checks this every few seconds and will exit shortly (usually within one poll interval).',
    ]);
    exit;
}

echo json_encode(['ok' => false, 'error' => 'invalid action']);

/**
 * @param mysqli $connection
 */
function dash_pw_table_exists($connection, string $table): bool
{
    $t = mysqli_real_escape_string($connection, $table);
    $chk = mysqli_query($connection, "SHOW TABLES LIKE '$t'");
    return $chk && mysqli_num_rows($chk) > 0;
}

/**
 * @param mysqli $connection
 */
function dash_pw_ensure_queue_cols($connection): void
{
    static $done = false;
    if ($done) {
        return;
    }
    if (!dash_pw_table_exists($connection, 'kp_print_queue')) {
        $done = true;
        return;
    }
    $r = mysqli_query($connection, "SHOW COLUMNS FROM kp_print_queue LIKE 'print_attempts'");
    if ($r && mysqli_num_rows($r) === 0) {
        mysqli_query($connection, 'ALTER TABLE kp_print_queue ADD COLUMN print_attempts TINYINT UNSIGNED NOT NULL DEFAULT 0');
    }
    $r2 = mysqli_query($connection, "SHOW COLUMNS FROM kp_print_queue LIKE 'last_error'");
    if ($r2 && mysqli_num_rows($r2) === 0) {
        mysqli_query($connection, "ALTER TABLE kp_print_queue ADD COLUMN last_error VARCHAR(255) NOT NULL DEFAULT ''");
    }
    $done = true;
}

function dash_pw_status_label(int $status, string $last_error, int $attempts): string
{
    if ($status === 1) {
        return 'Printed';
    }
    if ($status === 3) {
        return 'Printing';
    }
    if ($status === 2) {
        return 'Failed / stopped';
    }
    if ($status === 0 && $attempts > 0 && $last_error !== '') {
        return 'Pending (retry)';
    }
    return 'Pending';
}

function dash_pw_issue_text(int $status, string $last_error, int $attempts, string $counter_name = ''): string
{
    if ($status === 1) {
        return '';
    }
    if ($last_error !== '') {
        if (preg_match('/No printer_name for cntr_id\s+(\d+)/i', $last_error, $m)) {
            $who = $counter_name !== '' ? '"' . $counter_name . '"' : 'counter #' . (int) $m[1];
            return 'No Windows printer set for ' . $who . '. Assign it in Settings → Print Server.';
        }
        if (preg_match('/No Windows printer set for counter/i', $last_error)) {
            $who = $counter_name !== '' ? '"' . $counter_name . '"' : 'this counter';
            return 'No Windows printer is stored for ' . $who . ' in kp_counters.printer_name. In Settings → Print Server, add the printer for that exact counter and click “Re-apply registry to counters”.';
        }
        return $last_error;
    }
    if ($status === 0) {
        return $attempts > 0 ? 'Waiting in queue after a previous error.' : 'Waiting in queue.';
    }
    if ($status === 3) {
        return 'Worker claimed this job.';
    }
    if ($status === 2) {
        return 'Marked failed or cancelled (see printer queue admin if cancelled).';
    }
    return '';
}

/**
 * @param array<string,mixed> $row
 */
function dash_pw_enrich_queue_row(array &$row): void
{
    $st = (int) ($row['status'] ?? 0);
    $err = (string) ($row['last_error'] ?? '');
    $att = (int) ($row['print_attempts'] ?? 0);
    $pn = trim((string) ($row['printer_name'] ?? ''));
    $cn = trim((string) ($row['counter_name'] ?? ''));
    // UI: show counter name when Windows printer is not set (not a print target name).
    $row['printer_display'] = $pn !== '' ? $pn : ($cn !== '' ? $cn : '—');
    $row['status_label'] = dash_pw_status_label($st, $err, $att);
    $row['issue'] = dash_pw_issue_text($st, $err, $att, $cn);
    $row['row_tone'] = dash_pw_row_tone($st);
}

function dash_pw_row_tone(int $status): string
{
    if ($status === 1) {
        return 'done';
    }
    if ($status === 2) {
        return 'fail';
    }
    if ($status === 3) {
        return 'printing';
    }
    return 'pending';
}

/**
 * @param array<string,mixed> $cfg
 */
function dash_pw_resolve_php_binary(array $cfg): string
{
    $p = isset($cfg['php_binary']) ? trim((string) $cfg['php_binary']) : '';
    if ($p !== '' && is_file($p)) {
        return $p;
    }
    $candidates = [
        'C:\\Program Files\\Ampps\\php\\php.exe',
        'C:\\Ampps\\php\\php.exe',
        dirname(PHP_BINARY) . '\\php.exe',
        PHP_BINARY,
    ];
    foreach ($candidates as $c) {
        if ($c && is_file($c)) {
            return $c;
        }
    }
    return '';
}
