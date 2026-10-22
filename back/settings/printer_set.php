<?php
session_start();
include('../../inc/connection.php');
include('../clude/funcs.php');
require_once('../../qz/common.php');
$chabaFuncsPath = realpath(__DIR__ . '/../../front/chaba/funcs.php');
if ($chabaFuncsPath && is_file($chabaFuncsPath)) {
    require_once $chabaFuncsPath;
}

if (!function_exists('ensure_chaba_print_system')) {
    function ensure_chaba_print_system($connection)
    {
        mysqli_query($connection, "CREATE TABLE IF NOT EXISTS kp_print_queue (
          queue_id INT NOT NULL AUTO_INCREMENT,
          sale_id INT NOT NULL DEFAULT 0,
          ref_id INT NOT NULL DEFAULT 0,
          print_type VARCHAR(55) NOT NULL DEFAULT 'bill',
          label VARCHAR(120) NOT NULL DEFAULT '',
          cntr_id INT NOT NULL DEFAULT 0,
          route_path VARCHAR(255) NOT NULL DEFAULT '',
          route_query TEXT NULL,
          requested_by INT NOT NULL DEFAULT 0,
          reprint_of INT NOT NULL DEFAULT 0,
          status TINYINT(1) NOT NULL DEFAULT 0,
          print_attempts TINYINT UNSIGNED NOT NULL DEFAULT 0,
          last_error VARCHAR(255) NOT NULL DEFAULT '',
          printed_at DATETIME NULL,
          created_at TIMESTAMP NOT NULL DEFAULT CURRENT_TIMESTAMP,
          PRIMARY KEY (queue_id),
          KEY idx_cntr_status (cntr_id, status),
          KEY idx_type_ref (print_type, ref_id),
          KEY idx_reprint (reprint_of)
        ) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4");

        mysqli_query($connection, "CREATE TABLE IF NOT EXISTS kp_print_reprints (
          reprint_id INT NOT NULL AUTO_INCREMENT,
          queue_id INT NOT NULL DEFAULT 0,
          source_queue_id INT NOT NULL DEFAULT 0,
          print_type VARCHAR(55) NOT NULL DEFAULT '',
          ref_id INT NOT NULL DEFAULT 0,
          cntr_id INT NOT NULL DEFAULT 0,
          requested_by INT NOT NULL DEFAULT 0,
          created_at TIMESTAMP NOT NULL DEFAULT CURRENT_TIMESTAMP,
          PRIMARY KEY (reprint_id),
          KEY idx_queue (queue_id),
          KEY idx_source_queue (source_queue_id)
        ) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4");
    }
}

if (!function_exists('kp_print_requeue')) {
    function kp_print_requeue($connection, $queue_id, $cntr_id, $user_id)
    {
        $queue_id = (int)$queue_id;
        $cntr_id = (int)$cntr_id;
        $user_id = (int)$user_id;
        if ($queue_id < 1 || $cntr_id < 1 || $user_id < 1) {
            return 0;
        }
        $q = mysqli_query($connection, "SELECT * FROM kp_print_queue WHERE queue_id='$queue_id' LIMIT 1");
        if (!$q || mysqli_num_rows($q) < 1) {
            return 0;
        }
        $job = mysqli_fetch_assoc($q);
        $sale_id = (int)($job['sale_id'] ?? 0);
        $ref_id = (int)($job['ref_id'] ?? $sale_id);
        $print_type = mysqli_real_escape_string($connection, (string)($job['print_type'] ?? 'bill'));
        $label = mysqli_real_escape_string($connection, (string)($job['label'] ?? strtoupper($print_type)));
        $route_path = mysqli_real_escape_string($connection, (string)($job['route_path'] ?? ''));
        $route_query = mysqli_real_escape_string($connection, (string)($job['route_query'] ?? ''));
        mysqli_query(
            $connection,
            "INSERT INTO kp_print_queue
            (sale_id, ref_id, print_type, label, cntr_id, route_path, route_query, requested_by, reprint_of)
            VALUES
            ('$sale_id', '$ref_id', '$print_type', '$label', '$cntr_id', '$route_path', '$route_query', '$user_id', '$queue_id')"
        );
        $new_queue_id = (int)mysqli_insert_id($connection);
        if ($new_queue_id > 0) {
            mysqli_query(
                $connection,
                "INSERT INTO kp_print_reprints
                (queue_id, source_queue_id, print_type, ref_id, cntr_id, requested_by)
                VALUES
                ('$new_queue_id', '$queue_id', '$print_type', '$ref_id', '$cntr_id', '$user_id')"
            );
        }
        return $new_queue_id;
    }
}

ensure_chaba_print_system($connection);
$qzTrustConfigured = function_exists('kp_qz_trust_is_configured') ? kp_qz_trust_is_configured() : false;
$qzSecurityBaseUrl = '../../qz/security.php';
$qzPreviewUrl = '../../qz/preview.php';
$qzFitSettings = function_exists('kp_qz_fit_settings') ? kp_qz_fit_settings($connection) : ['paper_width_mm' => 80, 'margin_mm' => 0, 'content_scale_pct' => 80];

// Ensure printer_name column exists (compatible with MySQL < 8)
$_col_check = mysqli_query($connection, "SHOW COLUMNS FROM kp_counters LIKE 'printer_name'");
if (mysqli_num_rows($_col_check) === 0) {
    mysqli_query($connection, "ALTER TABLE kp_counters ADD COLUMN printer_name VARCHAR(100) NOT NULL DEFAULT ''");
}

// Ensure print queue table exists
mysqli_query($connection, "CREATE TABLE IF NOT EXISTS kp_print_queue (
  queue_id   INT NOT NULL AUTO_INCREMENT,
  sale_id    INT NOT NULL DEFAULT 0,
  print_type VARCHAR(55) NOT NULL DEFAULT 'bill',
  cntr_id    INT NOT NULL DEFAULT 0,
  status     TINYINT(1) NOT NULL DEFAULT 0,
  created_at TIMESTAMP NOT NULL DEFAULT CURRENT_TIMESTAMP,
  PRIMARY KEY (queue_id),
  INDEX idx_cntr_status (cntr_id, status)
)");
$printer_set_print_type_chk = mysqli_query($connection, "SHOW COLUMNS FROM kp_print_queue LIKE 'print_type'");
if ($printer_set_print_type_chk && ($printer_set_print_type = mysqli_fetch_assoc($printer_set_print_type_chk))) {
    $printer_set_type = strtolower((string)($printer_set_print_type['Type'] ?? ''));
    if (strpos($printer_set_type, 'varchar(55)') === false) {
        mysqli_query($connection, "ALTER TABLE kp_print_queue CHANGE print_type print_type VARCHAR(55) CHARACTER SET utf8mb4 COLLATE utf8mb4_general_ci NOT NULL DEFAULT 'bill'");
    }
}

mysqli_query($connection, "CREATE TABLE IF NOT EXISTS kp_print_printers (
  prn_id INT UNSIGNED NOT NULL AUTO_INCREMENT,
  windows_name VARCHAR(255) NOT NULL,
  label VARCHAR(120) NOT NULL DEFAULT '',
  role VARCHAR(32) NOT NULL DEFAULT 'counter_bill_bot',
  cntr_id INT NOT NULL DEFAULT 0,
  sort_order SMALLINT NOT NULL DEFAULT 0,
  created_at TIMESTAMP NOT NULL DEFAULT CURRENT_TIMESTAMP,
  PRIMARY KEY (prn_id),
  KEY idx_role (role),
  KEY idx_cntr (cntr_id)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4");

// Ensure print_mode column exists
$_pm_chk = mysqli_query($connection, "SHOW COLUMNS FROM kp_settings_print LIKE 'print_mode'");
if ($_pm_chk && mysqli_num_rows($_pm_chk) === 0) {
    mysqli_query($connection, "ALTER TABLE kp_settings_print ADD COLUMN print_mode TINYINT(1) NOT NULL DEFAULT 1");
}

if (!function_exists('kpRoleLabel')) {
    function kpRoleLabel(string $role): string
    {
        static $labels = [
            'counter_bill_bot' => 'Counter receipts',
            'cashier_order' => 'Cashier order',
            'kitchen_main' => 'Kitchen KOT',
            'grill_kot' => 'Grill KOT',
        ];
        return $labels[$role] ?? ucwords(str_replace('_', ' ', $role));
    }
}

if (!function_exists('kp_print_registry_roles')) {
    function kp_print_registry_roles(): array
    {
        return [
            'counter_bill_bot',
            'cashier_order',
            'kitchen_main',
            'grill_kot',
        ];
    }
}

function kp_counter_dpt_title(int $dpt): string
{
    static $map = [
        1 => 'Bar',
        2 => 'Kitchen',
        3 => 'Grill',
        4 => 'Swimming / pool',
        5 => 'Leisure / other',
    ];
    return $map[$dpt] ?? ('Department ' . $dpt);
}

function kp_worker_config_file_path(): string
{
    return dirname(__DIR__) . '/../tools/print_worker.config.php';
}

function kp_worker_config_example_path(): string
{
    return dirname(__DIR__) . '/../tools/print_worker.config.example.php';
}

function kp_load_worker_config_data(): array
{
    $defaults = [
        'base_url' => '',
        'render_token' => '',
        'worker_user_id' => 1,
        'sumatra_path' => 'C:\\Program Files\\SumatraPDF\\SumatraPDF.exe',
        'edge_path' => '',
        'poll_seconds' => 2,
        'max_attempts' => 5,
        'php_binary' => 'C:\\Program Files\\Ampps\\php\\php.exe',
    ];

    $configFile = kp_worker_config_file_path();
    $exampleFile = kp_worker_config_example_path();
    $loaded = [];

    if (is_file($configFile)) {
        $tmp = require $configFile;
        if (is_array($tmp)) {
            $loaded = $tmp;
        }
    } elseif (is_file($exampleFile)) {
        $tmp = require $exampleFile;
        if (is_array($tmp)) {
            $loaded = $tmp;
        }
    }

    return array_merge($defaults, $loaded);
}

function kp_worker_export_value($value): string
{
    return var_export($value, true);
}

function kp_write_worker_config_file(array $config): bool
{
    $configFile = kp_worker_config_file_path();
    $content = "<?php\n"
        . "/**\n"
        . " * Auto-generated from Settings -> Print Server.\n"
        . " * Database settings are loaded from inc/connection.php.\n"
        . " */\n"
        . "return [\n"
        . "    // No trailing slash. Use the address this print machine opens.\n"
        . "    'base_url' => " . kp_worker_export_value((string)$config['base_url']) . ",\n\n"
        . "    // Same token used by gen/print_agent_render.php\n"
        . "    'render_token' => " . kp_worker_export_value((string)$config['render_token']) . ",\n\n"
        . "    // Must exist in kp_users\n"
        . "    'worker_user_id' => " . (int)$config['worker_user_id'] . ",\n\n"
        . "    'sumatra_path' => " . kp_worker_export_value((string)$config['sumatra_path']) . ",\n"
        . "    'edge_path' => " . kp_worker_export_value((string)$config['edge_path']) . ",\n\n"
        . "    'poll_seconds' => " . (int)$config['poll_seconds'] . ",\n"
        . "    'max_attempts' => " . (int)$config['max_attempts'] . ",\n\n"
        . "    'php_binary' => " . kp_worker_export_value((string)$config['php_binary']) . ",\n"
        . "];\n";

    return file_put_contents($configFile, $content, LOCK_EX) !== false;
}

function kp_build_print_worker_task_xml(array $cfg): string
{
    $taskName = isset($cfg['task_name']) && trim((string)$cfg['task_name']) !== '' ? trim((string)$cfg['task_name']) : 'KTouch Print Worker';
    $phpBinary = trim((string)$cfg['php_binary']);
    $workerScript = str_replace('/', '\\', realpath(dirname(__DIR__) . '/../tools/print_worker.php') ?: dirname(__DIR__) . '/../tools/print_worker.php');
    $workingDir = str_replace('/', '\\', dirname($workerScript));
    $arguments = '"' . $workerScript . '"';
    $now = date('Y-m-d\TH:i:s');

    $cmdXml = htmlspecialchars($phpBinary, ENT_XML1, 'UTF-8');
    $argsXml = htmlspecialchars($arguments, ENT_XML1, 'UTF-8');
    $dirXml = htmlspecialchars($workingDir, ENT_XML1, 'UTF-8');
    $nameXml = htmlspecialchars($taskName, ENT_XML1, 'UTF-8');

    $xml = <<<XML
<?xml version="1.0" encoding="UTF-16"?>
<Task version="1.2" xmlns="http://schemas.microsoft.com/windows/2004/02/mit/task">
  <RegistrationInfo>
    <Date>{$now}</Date>
    <Author>KTouch POS</Author>
    <Description>Starts the KTouch Windows print worker at logon.</Description>
  </RegistrationInfo>
  <Triggers>
    <LogonTrigger>
      <Enabled>true</Enabled>
      <Delay>PT10S</Delay>
    </LogonTrigger>
  </Triggers>
  <Principals>
    <Principal id="Author">
      <LogonType>InteractiveToken</LogonType>
      <RunLevel>LeastPrivilege</RunLevel>
    </Principal>
  </Principals>
  <Settings>
    <MultipleInstancesPolicy>IgnoreNew</MultipleInstancesPolicy>
    <DisallowStartIfOnBatteries>false</DisallowStartIfOnBatteries>
    <StopIfGoingOnBatteries>false</StopIfGoingOnBatteries>
    <AllowHardTerminate>true</AllowHardTerminate>
    <StartWhenAvailable>true</StartWhenAvailable>
    <RunOnlyIfNetworkAvailable>false</RunOnlyIfNetworkAvailable>
    <IdleSettings>
      <StopOnIdleEnd>false</StopOnIdleEnd>
      <RestartOnIdle>false</RestartOnIdle>
    </IdleSettings>
    <AllowStartOnDemand>true</AllowStartOnDemand>
    <Enabled>true</Enabled>
    <Hidden>false</Hidden>
    <RunOnlyIfIdle>false</RunOnlyIfIdle>
    <WakeToRun>false</WakeToRun>
    <ExecutionTimeLimit>PT0S</ExecutionTimeLimit>
    <Priority>7</Priority>
  </Settings>
  <Actions Context="Author">
    <Exec>
      <Command>{$cmdXml}</Command>
      <Arguments>{$argsXml}</Arguments>
      <WorkingDirectory>{$dirXml}</WorkingDirectory>
    </Exec>
  </Actions>
</Task>
XML;

    return "\xFF\xFE" . mb_convert_encoding($xml, 'UTF-16LE', 'UTF-8');
}

// Handle AJAX save
if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['action']) && $_POST['action'] === 'save_print_mode') {
    $mode = (int)$_POST['print_mode'];
    if (!in_array($mode, [1, 2, 3, 4], true)) $mode = 1;
    mysqli_query($connection, "UPDATE kp_settings_print SET print_mode='$mode' WHERE print_doc=1");
    echo json_encode(['ok' => true, 'mode' => $mode]);
    exit;
}

if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['action']) && $_POST['action'] === 'save_worker_config') {
    header('Content-Type: application/json; charset=UTF-8');
    $baseUrl = trim((string)($_POST['base_url'] ?? ''));
    $renderToken = trim((string)($_POST['render_token'] ?? ''));
    $workerUserId = (int)($_POST['worker_user_id'] ?? 0);
    $sumatraPath = trim((string)($_POST['sumatra_path'] ?? ''));
    $edgePath = trim((string)($_POST['edge_path'] ?? ''));
    $phpBinary = trim((string)($_POST['php_binary'] ?? ''));
    $pollSeconds = max(1, (int)($_POST['poll_seconds'] ?? 2));
    $maxAttempts = max(1, (int)($_POST['max_attempts'] ?? 5));

    if ($baseUrl === '' || !filter_var($baseUrl, FILTER_VALIDATE_URL)) {
        echo json_encode(['ok' => false, 'message' => 'Enter a valid Base URL.']);
        exit;
    }
    if ($renderToken === '') {
        echo json_encode(['ok' => false, 'message' => 'Render token is required.']);
        exit;
    }
    if ($workerUserId <= 0) {
        echo json_encode(['ok' => false, 'message' => 'Worker user id is required.']);
        exit;
    }
    if ($sumatraPath === '') {
        echo json_encode(['ok' => false, 'message' => 'SumatraPDF path is required.']);
        exit;
    }
    if ($phpBinary === '') {
        echo json_encode(['ok' => false, 'message' => 'PHP binary path is required.']);
        exit;
    }

    $ok = kp_write_worker_config_file([
        'base_url' => rtrim($baseUrl, '/'),
        'render_token' => $renderToken,
        'worker_user_id' => $workerUserId,
        'sumatra_path' => $sumatraPath,
        'edge_path' => $edgePath,
        'poll_seconds' => $pollSeconds,
        'max_attempts' => $maxAttempts,
        'php_binary' => $phpBinary,
    ]);

    echo json_encode([
        'ok' => $ok,
        'message' => $ok ? 'Print worker config saved.' : 'Could not write print worker config file.',
    ]);
    exit;
}

if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['action']) && $_POST['action'] === 'save_qz_fit') {
    header('Content-Type: application/json; charset=UTF-8');
    $paper = (float)($_POST['qz_paper_width_mm'] ?? 80);
    $margin = (float)($_POST['qz_margin_mm'] ?? 0);
    $scale = (float)($_POST['qz_scale_pct'] ?? 80);
    $dividerSymbol = trim((string)($_POST['receipt_divider_symbol'] ?? '*'));
    $dividerCount = (int)($_POST['receipt_divider_count'] ?? 50);
    if ($paper < 50) { $paper = 50; }
    if ($paper > 120) { $paper = 120; }
    if ($margin < 0) { $margin = 0; }
    if ($margin > 10) { $margin = 10; }
    if ($scale < 40) { $scale = 40; }
    if ($scale > 120) { $scale = 120; }
    if ($dividerSymbol === '') { $dividerSymbol = '*'; }
    if (function_exists('mb_substr')) {
        $dividerSymbol = mb_substr($dividerSymbol, 0, 4);
    } else {
        $dividerSymbol = substr($dividerSymbol, 0, 4);
    }
    if ($dividerCount < 5) { $dividerCount = 5; }
    if ($dividerCount > 120) { $dividerCount = 120; }
    $dividerCols = array(
        'receipt_divider_symbol' => "ALTER TABLE kp_settings_print ADD COLUMN receipt_divider_symbol VARCHAR(8) NOT NULL DEFAULT '*'",
        'receipt_divider_count' => "ALTER TABLE kp_settings_print ADD COLUMN receipt_divider_count INT NOT NULL DEFAULT 50"
    );
    foreach ($dividerCols as $dividerCol => $dividerSql) {
        $dividerChk = mysqli_query($connection, "SHOW COLUMNS FROM kp_settings_print LIKE '$dividerCol'");
        if ($dividerChk && mysqli_num_rows($dividerChk) === 0) {
            mysqli_query($connection, $dividerSql);
        }
    }
    mysqli_query(
        $connection,
        "UPDATE kp_settings_print
         SET qz_paper_width_mm='" . mysqli_real_escape_string($connection, (string)$paper) . "',
             qz_margin_mm='" . mysqli_real_escape_string($connection, (string)$margin) . "',
             qz_scale_pct='" . mysqli_real_escape_string($connection, (string)$scale) . "',
             receipt_divider_symbol='" . mysqli_real_escape_string($connection, $dividerSymbol) . "',
             receipt_divider_count='$dividerCount'
         WHERE print_doc=1"
    );
    echo json_encode(['ok' => true, 'message' => 'QZ thermal fit settings saved. Refresh the worker page and test again.']);
    exit;
}

if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['action']) && $_POST['action'] === 'download_worker_task') {
    $cfg = kp_load_worker_config_data();
    $phpBinary = trim((string)($_POST['php_binary'] ?? $cfg['php_binary']));
    $taskName = trim((string)($_POST['task_name'] ?? 'KTouch Print Worker'));
    if ($phpBinary === '') {
        http_response_code(400);
        exit('PHP binary path is required.');
    }
    $xml = kp_build_print_worker_task_xml([
        'php_binary' => $phpBinary,
        'task_name' => $taskName,
    ]);
    header('Content-Type: application/octet-stream');
    header('Content-Disposition: attachment; filename="KTouch_Print_Worker_Task.xml"');
    header('Content-Length: ' . strlen($xml));
    header('Cache-Control: no-cache, must-revalidate');
    header('Pragma: no-cache');
    echo $xml;
    exit;
}

if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['action']) && $_POST['action'] === 'cancel_job') {
    $queue_id = (int)$_POST['queue_id'];
    if ($queue_id > 0) {
        mysqli_query($connection, "UPDATE kp_print_queue SET status=2 WHERE queue_id='$queue_id' AND status=0");
    }
    echo json_encode(['ok' => true]);
    exit;
}

if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['action']) && $_POST['action'] === 'queue_history') {
    header('Content-Type: application/json; charset=UTF-8');
    $rows = [];
    $rq = mysqli_query(
        $connection,
        "SELECT q.queue_id, q.ref_id, q.sale_id, q.print_type, q.label, q.cntr_id, q.status, q.reprint_of,
                q.print_attempts, q.last_error, q.printed_at, q.created_at, q.route_path, q.route_query, c.name AS cntr_name,
                (SELECT COUNT(*) FROM kp_print_reprints rr WHERE rr.source_queue_id = q.queue_id) AS reprint_count
         FROM kp_print_queue q
         LEFT JOIN kp_counters c ON c.cntr_id = q.cntr_id
         ORDER BY q.queue_id DESC
         LIMIT 100"
    );
    while ($rq && ($row = mysqli_fetch_assoc($rq))) {
        $row['preview_url'] = function_exists('kp_print_job_preview_url') ? kp_print_job_preview_url($row) : '';
        $rows[] = $row;
    }
    echo json_encode(['ok' => true, 'rows' => $rows]);
    exit;
}

if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['action']) && $_POST['action'] === 'reprint_job') {
    header('Content-Type: application/json; charset=UTF-8');
    $queue_id = (int)($_POST['queue_id'] ?? 0);
    $cntr_id = (int)($_POST['cntr_id'] ?? 0);
    $user_id = (int)($_SESSION['user_id'] ?? 0);
    if ($queue_id < 1 || $cntr_id < 1 || $user_id < 1) {
        echo json_encode(['ok' => false, 'message' => 'Queue id, printer counter, and user are required.']);
        exit;
    }
    $new_queue_id = kp_print_requeue($connection, $queue_id, $cntr_id, $user_id);
    if ($new_queue_id < 1) {
        echo json_encode(['ok' => false, 'message' => 'Could not queue the reprint job.']);
        exit;
    }
    echo json_encode(['ok' => true, 'message' => 'Reprint queued successfully.', 'queue_id' => $new_queue_id]);
    exit;
}

if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['action']) && $_POST['action'] === 'list_win_printers') {
    header('Content-Type: application/json; charset=UTF-8');
    echo json_encode(['ok' => true, 'printers' => kp_win_printer_list()]);
    exit;
}

if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['action']) && $_POST['action'] === 'test_print') {
    header('Content-Type: application/json; charset=UTF-8');
    $win = trim((string) ($_POST['windows_name'] ?? ''));
    echo json_encode(kp_test_print_powershell($win));
    exit;
}

if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['action']) && $_POST['action'] === 'list_print_registry') {
    header('Content-Type: application/json; charset=UTF-8');
    $rows = [];
    $rq = mysqli_query(
        $connection,
        "SELECT p.*, c.name AS cntr_name, IFNULL(c.dpt, -1) AS cntr_dpt
         FROM kp_print_printers p
         LEFT JOIN kp_counters c ON c.cntr_id = p.cntr_id
         ORDER BY CASE p.role
                    WHEN 'kitchen_main' THEN 0
                    WHEN 'grill_kot' THEN 1
                    WHEN 'cashier_order' THEN 2
                    ELSE 3
                  END ASC,
                  IFNULL(c.dpt, 99) ASC, p.sort_order ASC, p.prn_id ASC"
    );
    if ($rq) {
        while ($row = mysqli_fetch_assoc($rq)) {
            $rows[] = $row;
        }
    }
    echo json_encode(['ok' => true, 'rows' => $rows]);
    exit;
}

if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['action']) && $_POST['action'] === 'add_print_registry') {
    header('Content-Type: application/json; charset=UTF-8');
    $windows = mysqli_real_escape_string($connection, trim($_POST['windows_name'] ?? ''));
    $label = mysqli_real_escape_string($connection, trim($_POST['label'] ?? ''));
    $role = preg_replace('/[^a-z_]/', '', strtolower(trim($_POST['role'] ?? '')));
    $cntr_id = (int)($_POST['cntr_id'] ?? 0);
    if ($windows === '') {
        echo json_encode(['ok' => false, 'message' => 'Pick a printer from the list.']);
        exit;
    }
    $allowed = ['kitchen_main', 'grill_kot', 'cashier_order', 'counter_bill_bot'];
    if (!in_array($role, $allowed, true)) {
        echo json_encode(['ok' => false, 'message' => 'Invalid role.']);
        exit;
    }
    if ($role === 'kitchen_main') {
        $cntr_id = 0;
        mysqli_query($connection, "DELETE FROM kp_print_printers WHERE role='kitchen_main'");
    } elseif ($role === 'grill_kot') {
        $chk = mysqli_query($connection, "SELECT cntr_id FROM kp_counters WHERE cntr_id='$cntr_id' AND dpt >= 2 AND del_status=2 LIMIT 1");
        if (!$chk || mysqli_num_rows($chk) < 1) {
            echo json_encode(['ok' => false, 'message' => 'Pick a kitchen / grill / back-of-house counter (department 2+) for the grill KOT printer.']);
            exit;
        }
    } else {
        $chk = mysqli_query($connection, "SELECT cntr_id FROM kp_counters WHERE cntr_id='$cntr_id' AND del_status=2 LIMIT 1");
        if (!$chk || mysqli_num_rows($chk) < 1) {
            $counterLabel = $role === 'cashier_order' ? 'cashier order printer' : 'bill + BOT printer';
            echo json_encode(['ok' => false, 'message' => 'Pick a valid counter for the ' . $counterLabel . '.']);
            exit;
        }
    }
    $ins = mysqli_query(
        $connection,
        "INSERT INTO kp_print_printers (windows_name, label, role, cntr_id) VALUES ('$windows', '$label', '$role', '$cntr_id')"
    );
    if (!$ins) {
        echo json_encode(['ok' => false, 'message' => mysqli_error($connection)]);
        exit;
    }
    kp_sync_print_registry($connection);
    echo json_encode(['ok' => true, 'message' => 'Printer added and counters updated.']);
    exit;
}

if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['action']) && $_POST['action'] === 'delete_print_registry') {
    header('Content-Type: application/json; charset=UTF-8');
    $prn_id = (int)($_POST['prn_id'] ?? 0);
    if ($prn_id > 0) {
        mysqli_query($connection, "DELETE FROM kp_print_printers WHERE prn_id='$prn_id'");
    }
    kp_sync_print_registry($connection);
    echo json_encode(['ok' => true]);
    exit;
}

if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['action']) && $_POST['action'] === 'sync_print_registry') {
    header('Content-Type: application/json; charset=UTF-8');
    kp_sync_print_registry($connection);
    echo json_encode(['ok' => true, 'message' => 'Counters updated from registry.']);
    exit;
}

if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['action']) && $_POST['action'] === 'save_printer') {
    $cntr_id      = (int)$_POST['cntr_id'];
    $printer_name = mysqli_real_escape_string($connection, trim($_POST['printer_name']));
    $upd = mysqli_query($connection, "UPDATE kp_counters SET printer_name='$printer_name' WHERE cntr_id='$cntr_id'");
    if ($upd) {
        echo '<div class="alert alert-success alert-dismissible" style="margin:0;">
                <button type="button" class="close" data-dismiss="alert">&times;</button>
                <strong>Saved.</strong> Printer name updated.
              </div>';
    } else {
        echo '<div class="alert alert-danger alert-dismissible" style="margin:0;">
                <button type="button" class="close" data-dismiss="alert">&times;</button>
                <strong>Error:</strong> ' . mysqli_error($connection) . '
              </div>';
    }
    exit;
}

// Load current print mode
$_pm_r = mysqli_query($connection, "SELECT print_mode FROM kp_settings_print WHERE print_doc=1");
$current_print_mode = 1;
if ($_pm_r && ($pm_row = mysqli_fetch_assoc($_pm_r)) && isset($pm_row['print_mode'])) {
    $current_print_mode = (int)$pm_row['print_mode'];
}

$is_ajax_request = !empty($_SERVER['HTTP_X_REQUESTED_WITH']) && strtolower((string)$_SERVER['HTTP_X_REQUESTED_WITH']) === 'xmlhttprequest';
if ($_SERVER['REQUEST_METHOD'] === 'GET' && !$is_ajax_request) {
    header('Location: settings.php?tab=printer-set');
    exit;
}

$base_scheme = (isset($_SERVER['HTTPS']) && $_SERVER['HTTPS'] == 'on') ? 'https' : 'http';
$base_host = (string)($_SERVER['HTTP_HOST'] ?? 'localhost');
$print_server_url = $base_scheme . '://' . $base_host . '/clients/ktouch/gpints/front/chaba/index.php';
$browser_worker_url = $base_scheme . '://' . $base_host . '/clients/ktouch/gpints/front/chaba/index.php';
$qz_worker_url = $base_scheme . '://' . $base_host . '/clients/ktouch/gpints/front/chaba/index.php';
$startup_script_url = '/clients/ktouch/gpints/gen/startup_print.bat';
$startup_bat_path = realpath(dirname(__DIR__) . '/../gen/startup_print.bat');
$worker_script_path = realpath(dirname(__DIR__) . '/../tools/print_worker.php');
$windows_worker_named_path = realpath(dirname(__DIR__) . '/../tools/windows_print_server_worker.php');
$worker_config_path = kp_worker_config_file_path();
$worker_config_example_path = kp_worker_config_example_path();
$worker_cfg = kp_load_worker_config_data();

// Load counters (all departments — grouped in UI by dpt)
$counters_q = mysqli_query($connection, "SELECT cntr_id, name, dpt, printer_name FROM kp_counters WHERE del_status=2 ORDER BY dpt ASC, name ASC");
$bar_counters = [];
$kit_counters = [];
$counters_by_dpt = [];
while ($c = mysqli_fetch_assoc($counters_q)) {
    $d = (int) $c['dpt'];
    if (!isset($counters_by_dpt[$d])) {
        $counters_by_dpt[$d] = [];
    }
    $counters_by_dpt[$d][] = $c;
    if ($c['dpt'] == 1) {
        $bar_counters[] = $c;
    } elseif ($c['dpt'] == 2) {
        $kit_counters[] = $c;
    }
}
$dpt_titles_js = [];
foreach (array_keys($counters_by_dpt) as $dk) {
    $dpt_titles_js[(string) (int) $dk] = kp_counter_dpt_title((int) $dk);
}

function kp_win_printer_list(): array
{
    if (stripos(PHP_OS, 'WIN') !== 0) {
        return [];
    }
    $names = [];
    $ps = 'powershell -NoProfile -ExecutionPolicy Bypass -Command "Get-Printer | Select-Object -ExpandProperty Name"';
    $out = @shell_exec($ps);
    if (is_string($out) && trim($out) !== '') {
        foreach (preg_split('/\R/u', trim($out)) as $line) {
            $line = trim($line);
            if ($line !== '' && strcasecmp($line, 'Name') !== 0) {
                $names[] = $line;
            }
        }
    }
    if (!$names) {
        $out2 = @shell_exec('wmic printer get name 2>NUL');
        if (is_string($out2)) {
            foreach (preg_split('/\R/', $out2) as $line) {
                $line = trim($line);
                if ($line === '' || stripos($line, 'Name') === 0) {
                    continue;
                }
                if (strpos($line, '=') === 0) {
                    $parts = explode('=', $line, 2);
                    $line = isset($parts[1]) ? trim($parts[1]) : $line;
                }
                if ($line !== '') {
                    $names[] = $line;
                }
            }
        }
    }
    $names = array_values(array_unique($names));
    natcasesort($names);
    return array_values($names);
}

function kp_default_kitchen_counter_id(mysqli $connection): int
{
    $id = (int) get_column('kp_counters', 'cntr_id', "dpt='2' AND kit_default='1' AND del_status='2'");
    if ($id > 0) {
        return $id;
    }
    $r = mysqli_query($connection, 'SELECT cntr_id FROM kp_counters WHERE dpt=2 AND del_status=2 ORDER BY cntr_id ASC LIMIT 1');
    if ($r && ($row = mysqli_fetch_assoc($r))) {
        return (int) $row['cntr_id'];
    }
    return 0;
}

function kp_sync_print_registry(mysqli $connection): void
{
    $res = mysqli_query($connection, 'SELECT * FROM kp_print_printers ORDER BY sort_order ASC, prn_id ASC');
    if (!$res) {
        return;
    }
    while ($p = mysqli_fetch_assoc($res)) {
        $wn = mysqli_real_escape_string($connection, trim((string) $p['windows_name']));
        if ($wn === '') {
            continue;
        }
        $role = (string) $p['role'];
        $cid = (int) $p['cntr_id'];
        if ($role === 'kitchen_main') {
            $kid = kp_default_kitchen_counter_id($connection);
            if ($kid > 0) {
                mysqli_query($connection, "UPDATE kp_counters SET printer_name='$wn' WHERE cntr_id='$kid' AND del_status=2");
            }
        } elseif ($role === 'grill_kot' && $cid > 0) {
            // Match by counter id only (grill / kitchen / other BOH depts)
            mysqli_query($connection, "UPDATE kp_counters SET printer_name='$wn' WHERE cntr_id='$cid' AND del_status=2");
        } elseif ($role === 'counter_bill_bot' && $cid > 0) {
            // Bar, pool bar, etc. — do not require dpt=1 so sync matches the counter you picked
            mysqli_query($connection, "UPDATE kp_counters SET printer_name='$wn' WHERE cntr_id='$cid' AND del_status=2");
        }
    }
}

/**
 * @return array{ok:bool,message:string}
 */
function kp_test_print_powershell(string $printerName): array
{
    if (stripos(PHP_OS, 'WIN') !== 0) {
        return ['ok' => false, 'message' => 'Test print only runs when PHP is on Windows (open this page on the print server).'];
    }
    $name = trim($printerName);
    if ($name === '') {
        return ['ok' => false, 'message' => 'Choose a Windows printer from the list first.'];
    }
    $nameB64 = base64_encode($name);
    $inner = '$ErrorActionPreference = "Stop"; '
        . '$name = [System.Text.Encoding]::UTF8.GetString([System.Convert]::FromBase64String("' . $nameB64 . '")); '
        . '$txt = "KTouch printer test`r`n" + (Get-Date).ToString("yyyy-MM-dd HH:mm:ss"); '
        . '$txt | Out-Printer -Name $name';
    $enc = base64_encode((string) iconv('UTF-8', 'UTF-16LE', $inner));
    $cmd = 'powershell -NoProfile -ExecutionPolicy Bypass -EncodedCommand ' . escapeshellarg($enc);
    $out = [];
    $code = 0;
    exec($cmd . ' 2>&1', $out, $code);
    if ($code !== 0) {
        $detail = trim(implode("\n", $out));
        if ($detail === '') {
            $detail = 'exit code ' . $code;
        }
        return ['ok' => false, 'message' => 'Windows could not print a test page: ' . $detail];
    }
    return ['ok' => true, 'message' => 'Test text sent to the printer queue. Check the device or Windows “Devices and printers” if nothing came out.'];
}
?>
<style>
.print-setup-shell {
  padding: 0;
}
.print-simple-box {
  background: #f8f9fb;
  border: 1px solid #dfe5ec;
  border-left: 4px solid #7e2014;
  border-radius: 8px;
  padding: 14px 16px;
  margin-bottom: 16px;
}
.print-plain-card {
  border: 1px solid #dfe5ec;
  border-radius: 8px;
  background: #fff;
  padding: 14px;
  margin-bottom: 16px;
}
.print-stat-number {
  font-size: 30px;
  font-weight: 900;
  line-height: 1;
  margin-bottom: 4px;
}
.print-stat-label {
  font-size: 12px;
  color: #6c757d;
}
.print-status-banner {
  margin-top: 12px;
  padding: 12px 14px;
  border-radius: 6px;
  background: #f8f9fb;
  border: 1px solid #dfe5ec;
  font-size: 13px;
}
.print-code-box {
  background: #17212b;
  color: #f8fbff;
  border-radius: 6px;
  padding: 12px 14px;
  font-family: Consolas, monospace;
  font-size: 12px;
  line-height: 1.6;
  overflow-x: auto;
}
</style>

<div class="panel panel-inverse">
  <div class="panel-heading">
    <h4 class="panel-title"><i class="fa fa-print"></i> Print Server – Printer Setup</h4>
  </div>
  <div class="panel-body print-setup-shell">

    <div id="printer-save-results"></div>
    <div class="print-simple-box">
      <h4 style="margin:0 0 8px;"><i class="fa fa-info-circle"></i> Printing Setup</h4>
      <p style="margin:0 0 8px;">Choose the print mode below, then map printers if you are using the queue method.</p>
      <ul style="margin:0; padding-left:18px;">
        <li><strong>Mode 1:</strong> Browser prints directly from the cashier screen.</li>
        <li><strong>Mode 2:</strong> Jobs enter the queue, then a browser print-server tab opens each print page and prints it.</li>
        <li><strong>Mode 3:</strong> Jobs enter the queue, then a Windows print server worker sends them directly to the mapped printer.</li>
        <li><strong>Mode 4:</strong> Jobs enter the queue, then a browser worker uses QZ Tray to send each print directly to the mapped printer.</li>
        <li><strong>Current mode:</strong> <?php echo $current_print_mode === 1 ? 'Direct browser printing' : ($current_print_mode === 2 ? 'Browser print server queue' : ($current_print_mode === 3 ? 'Windows print server queue' : 'QZ Tray browser queue')); ?></li>
      </ul>
    </div>

    <div class="row" style="margin-bottom:18px;">
      <div class="col-md-3">
        <div class="print-plain-card">
          <h4 style="margin-top:0;">Direct Browser Printing</h4>
          <p>The cashier prints the bill directly from the browser print dialog. This is the simple option and it matches the working flow you already had.</p>
          <label style="cursor:pointer;font-weight:700;">
            <input type="radio" name="print_mode_opt" value="1" <?php echo $current_print_mode == 1 ? 'checked' : ''; ?> onchange="savePrintMode(1)">
            Use Direct Browser Printing
          </label>
        </div>
      </div>
      <div class="col-md-3">
        <div class="print-plain-card">
          <h4 style="margin-top:0;">Browser Print Server Queue</h4>
          <p>Jobs first enter the queue, then a browser tab on the print machine opens each print page and prints it.</p>
          <label style="cursor:pointer;font-weight:700;">
            <input type="radio" name="print_mode_opt" value="2" <?php echo $current_print_mode == 2 ? 'checked' : ''; ?> onchange="savePrintMode(2)">
            Use Browser Print Server
          </label>
        </div>
      </div>
      <div class="col-md-3">
        <div class="print-plain-card">
          <h4 style="margin-top:0;">Windows Direct Printer Server</h4>
          <p>Jobs first enter the queue, then a Windows worker checks the print log and sends each document directly to the correct mapped printer.</p>
          <label style="cursor:pointer;font-weight:700;">
            <input type="radio" name="print_mode_opt" value="3" <?php echo $current_print_mode == 3 ? 'checked' : ''; ?> onchange="savePrintMode(3)">
            Use Windows Print Server
          </label>
        </div>
      </div>
      <div class="col-md-3">
        <div class="print-plain-card">
          <h4 style="margin-top:0;">QZ Tray Direct Printing</h4>
          <p>Jobs first enter the queue, then a browser worker uses QZ Tray to send the receipt straight to the mapped printer without the normal browser print dialog.</p>
          <label style="cursor:pointer;font-weight:700;">
            <input type="radio" name="print_mode_opt" value="4" <?php echo $current_print_mode == 4 ? 'checked' : ''; ?> onchange="savePrintMode(4)">
            Use QZ Tray Print Server
          </label>
        </div>
      </div>
    </div>

    <div class="text-right" style="margin:-6px 0 16px;">
      <span id="mode-save-indicator" style="display:none;" class="text-success"><i class="fa fa-check-circle"></i> Print mode saved</span>
    </div>

    <div class="row" style="margin-bottom:18px;">
      <div class="col-md-3">
        <div class="print-plain-card">
        <h4><i class="fa fa-mouse-pointer text-success"></i> Direct Browser Setup</h4>
        <p>Use this when the cashier computer should print the bill immediately from the browser.</p>
        <ol style="padding-left:18px;">
          <li>Select <strong>Mode 1</strong> above.</li>
          <li>Install and test the receipt printer in Windows on the cashier machine.</li>
          <li>Set the correct printer as default, or choose it in the browser print dialog.</li>
          <li>Place one sale and confirm the bill opens and prints correctly.</li>
        </ol>
      </div>
      </div>
      <div class="col-md-3">
        <div class="print-plain-card">
        <h4><i class="fa fa-chrome text-primary"></i> Browser Print Server Setup</h4>
        <p>Use this when different counters or kitchen stations must print automatically and you want the print pages to open inside a browser tab on the print machine.</p>
        <ol style="padding-left:18px;">
          <li>Select <strong>Mode 2</strong> above.</li>
          <li>Install every printer on the Windows print machine and print a Windows test page first.</li>
          <li>Load printers below and map each one to the correct role or counter.</li>
          <li>Run the browser print page and keep that tab open.</li>
        </ol>
      </div>
      </div>
      <div class="col-md-3">
        <div class="print-plain-card">
        <h4><i class="fa fa-desktop text-info"></i> Windows Server Setup</h4>
        <p>Use this when you have one local Windows machine that should watch the queue and send each job directly to the mapped printer without opening browser print tabs.</p>
        <ol style="padding-left:18px;">
          <li>Select <strong>Mode 3</strong> above.</li>
          <li>On the local Windows print machine, install every receipt printer and confirm Windows test prints first.</li>
          <li>Load printers below and map each one to the correct role or counter.</li>
          <li>Set the worker config so the machine can open this system, then run the Windows PHP worker.</li>
          <li>Refresh the worker health section and confirm it shows <strong>Running</strong>.</li>
        </ol>
      </div>
      </div>
      <div class="col-md-3">
        <div class="print-plain-card">
        <h4><i class="fa fa-plug text-purple"></i> QZ Tray Setup</h4>
        <p>Use this when you want the local browser worker page to send each queued print directly to the printer through QZ Tray instead of opening the browser print dialog.</p>
        <ol style="padding-left:18px;">
          <li>Select <strong>Mode 4</strong> above.</li>
          <li>Install every printer in Windows on the print machine and confirm Windows test prints first.</li>
          <li>Install <strong>QZ Tray</strong> on that machine and keep it running.</li>
          <li>Load printers below and map each one to the correct role or counter.</li>
          <li>Open the browser print worker page on that same machine, allow QZ Tray access, then keep that page open.</li>
        </ol>
      </div>
      </div>
    </div>

    <div class="row" style="margin-bottom:18px;">
      <div class="col-md-6">
        <div class="print-plain-card">
        <h4><i class="fa fa-chrome"></i> Browser Print Server</h4>
        <p>Open one browser tab on the print machine, select a counter, and keep it running. This is the easiest routed setup and gives the team a visible live queue page.</p>
        <div>
          <a href="<?php echo htmlspecialchars($browser_worker_url); ?>" target="_blank" rel="noopener" class="btn btn-warning btn-sm"><i class="fa fa-external-link"></i> Open Browser Tab Worker</a>
          <a href="<?php echo htmlspecialchars($startup_script_url); ?>" download class="btn btn-default btn-sm"><i class="fa fa-download"></i> Download Startup Script</a>
        </div>
        <div class="print-code-box" style="margin-top:8px;"><?php echo htmlspecialchars($browser_worker_url); ?></div>
      </div>
      </div>
      <div class="col-md-6">
        <div class="print-plain-card">
        <h4><i class="fa fa-desktop"></i> Windows Print Server Worker</h4>
        <p>Use this for a dedicated local Windows print server. It reads the queue silently and sends it directly to the mapped Windows printer.</p>
        <div class="print-code-box"><?php echo htmlspecialchars(($windows_worker_named_path ?: 'tools/windows_print_server_worker.php')); ?></div>
      </div>
      </div>
    </div>

    <div class="panel panel-default" style="margin-bottom:16px;">
      <div class="panel-heading" style="background:#5b2f71;color:#fff;">
        <h5 class="panel-title"><i class="fa fa-plug"></i> QZ Tray Server Setup Guide</h5>
      </div>
      <div class="panel-body" style="font-size:13px;">
        <p><strong>What this mode does:</strong> the system still queues bills, cashier order tickets, BOTs, KOTs, GOT grill tickets, returns, and the other slips, but the local browser worker page uses <strong>QZ Tray</strong> to send them straight to the mapped Windows printer.</p>
        <h5 style="font-weight:800;color:#4d2a73;">Step-by-step QZ Tray setup</h5>
        <ol style="padding-left:18px;">
          <li>On this printer settings page, choose <strong>Mode 4 - QZ Tray Print Server</strong>.</li>
          <li>Choose the local Windows machine that will physically handle printing. This is the machine where the receipt printers are installed.</li>
          <li>Install every receipt printer in <strong>Windows Printers</strong> on that machine first. Before touching the POS setup, print a normal Windows test page for each printer so you know Windows can already see them.</li>
          <li>Install <strong>QZ Tray</strong> on that same machine and start it. Confirm you can see the QZ Tray icon in the Windows task bar / tray area.</li>
          <li>Open this POS system from that same local print machine. The site may be hosted online, but the browser page must be opened on the local machine that has QZ Tray and the printers.</li>
          <li>In the printer setup section below, click <strong>Load From QZ Tray</strong>. The browser will ask QZ Tray for the local Windows printer list.</li>
          <li>If the browser or QZ Tray asks for permission, allow the connection. If prompted to trust the site, approve it so the browser can keep talking to QZ Tray.</li>
          <li>Select one returned printer name, choose what that printer is used for, then click <strong>Add printer</strong>.
            <div style="margin-top:8px;">
              <strong>Main kitchen</strong>: use for KOTs going to the main kitchen printer.<br>
              <strong>Grill / GOT</strong>: use for grill order tickets.<br>
              <strong>Counter receipts</strong>: use for bills, BOT, returns, purchase slips, summaries, and similar front-counter receipts.<br>
              <strong>Cashier order</strong>: use when cashier-order slips should go to their own printer for the selected counter.
            </div>
          </li>
          <li>Repeat the same process until every local printer has been assigned to the correct role or counter.</li>
          <li>Click <strong>Re-apply registry to counters</strong> so the saved printer mapping is pushed to the counters used by the queue.</li>
          <li>Open the QZ Tray worker page on the same print machine:
            <div class="print-code-box" style="margin-top:8px;"><?php echo htmlspecialchars($qz_worker_url); ?></div>
          </li>
          <li>Select the correct counter or printer station in the worker page and leave that page open.</li>
          <li>Keep both <strong>QZ Tray</strong> and the browser worker page open. QZ Tray does not poll jobs by itself; the browser worker page is the one that keeps checking the queue and sending jobs to QZ Tray.</li>
        </ol>
        <div class="alert alert-info" style="margin-top:10px;">
          <strong>Important:</strong> QZ Tray does not fetch queued jobs by itself. It only helps the open browser worker page send jobs to the local printer. If the page is closed, queued jobs will wait until the page is opened again.
        </div>
        <div class="alert alert-success" style="margin-top:10px;">
          <strong>Trusted QZ file locations:</strong><br>
          Public certificate: <code>qz/storage/digital-certificate.txt</code><br>
          Private key: <code>qz/storage/private-key.pem</code><br>
          Optional passphrase file: <code>qz/storage/private-key-passphrase.txt</code><br>
          Signing endpoint used by the browser: <code>qz/security.php?action=sign</code><br>
          Browser QZ security helper: <code>qz/ktouch-qz-security.js</code>
        </div>
        <div class="alert alert-warning" style="margin-top:10px;">
          <strong>If your system is hosted online:</strong> that is still fine. Just remember the printers are not read from the online server. The printers are read from the <strong>local print machine browser through QZ Tray</strong>, then the selected names are saved back to the online system.
        </div>
        <div class="panel panel-default" style="margin-top:12px;margin-bottom:0;">
          <div class="panel-heading" style="background:#264653;color:#fff;">
            <h5 class="panel-title"><i class="fa fa-sliders"></i> QZ Thermal Fit Settings</h5>
          </div>
          <div class="panel-body">
            <p class="text-muted" style="font-size:12px;">Tune these to match what works in the browser print dialog. Use the preview below to see the sample receipt before you test a real print.</p>
            <div id="kp_qz_fit_msg"></div>
            <div class="row">
              <div class="col-md-4 col-sm-4">
                <label>Paper Width (mm)</label>
                <input type="number" step="0.1" min="50" max="120" class="form-control" id="kp_qz_paper_width_mm" value="<?php echo htmlspecialchars((string)$qzFitSettings['paper_width_mm']); ?>">
              </div>
              <div class="col-md-4 col-sm-4">
                <label>Page Margin (mm)</label>
                <input type="number" step="0.1" min="0" max="10" class="form-control" id="kp_qz_margin_mm" value="<?php echo htmlspecialchars((string)$qzFitSettings['margin_mm']); ?>">
              </div>
              <div class="col-md-4 col-sm-4">
                <label>Content Scale (%)</label>
                <input type="number" step="1" min="40" max="120" class="form-control" id="kp_qz_scale_pct" value="<?php echo htmlspecialchars((string)$qzFitSettings['content_scale_pct']); ?>">
              </div>
            </div>
            <div style="margin-top:10px;">
              <button type="button" class="btn btn-success btn-sm" id="kp_btn_save_qz_fit"><i class="fa fa-save"></i> Save QZ Fit</button>
              <button type="button" class="btn btn-default btn-sm" id="kp_btn_refresh_qz_preview"><i class="fa fa-refresh"></i> Refresh Preview</button>
              <a href="<?php echo htmlspecialchars($qzPreviewUrl . '?paper_width_mm=' . rawurlencode((string)$qzFitSettings['paper_width_mm']) . '&margin_mm=' . rawurlencode((string)$qzFitSettings['margin_mm']) . '&scale_pct=' . rawurlencode((string)$qzFitSettings['content_scale_pct'])); ?>" target="_blank" class="btn btn-info btn-sm" id="kp_btn_open_qz_preview"><i class="fa fa-external-link"></i> Open Full Preview</a>
            </div>
            <div class="well well-sm" style="margin-top:12px;margin-bottom:0;background:#f8fafc;border:1px solid #d7dee8;">
              <div style="display:flex;justify-content:space-between;align-items:center;gap:8px;flex-wrap:wrap;margin-bottom:8px;">
                <strong style="color:#264653;">Live Receipt Preview</strong>
                <span id="kp_qz_preview_meta" class="text-muted" style="font-size:12px;">Using the values shown above</span>
              </div>
              <iframe
                id="kp_qz_preview_frame"
                title="QZ thermal receipt preview"
                src="<?php echo htmlspecialchars($qzPreviewUrl . '?paper_width_mm=' . rawurlencode((string)$qzFitSettings['paper_width_mm']) . '&margin_mm=' . rawurlencode((string)$qzFitSettings['margin_mm']) . '&scale_pct=' . rawurlencode((string)$qzFitSettings['content_scale_pct'])); ?>"
                style="width:100%;height:540px;border:1px solid #cbd5e1;border-radius:8px;background:#fff;"
              ></iframe>
            </div>
          </div>
        </div>
        <p style="margin-bottom:8px;"><strong>Good to know:</strong></p>
        <ul style="padding-left:18px;margin-bottom:0;">
          <li>The browser and QZ Tray must be on the same Windows print machine.</li>
          <li>Each queued job uses the mapped Windows printer name from <code>kp_counters.printer_name</code>.</li>
          <li>If a job says no printer is set, load the printers below, map them, then click <strong>Re-apply registry to counters</strong>.</li>
          <li>If QZ Tray is closed, the queue will stay pending until the worker page can connect again.</li>
          <li>If the browser says it cannot connect to <code>wss://localhost:8282</code>, QZ Tray is not running, the browser blocked the local certificate, or the browser is not on the same local machine.</li>
          <li>The QZ Tray prompt will keep showing as <strong>anonymous</strong> until a signed QZ certificate + signature endpoint is configured for this site.</li>
        </ul>
      </div>
    </div>

    <div class="panel panel-default" style="margin-bottom:16px;border-radius:10px;overflow:hidden;box-shadow:0 4px 18px rgba(0,0,0,.06);">
      <div class="panel-heading" style="background:linear-gradient(90deg,#1e3a5f,#2d6a4f);color:#fff;border:none;">
        <h5 class="panel-title" style="font-weight:800;"><i class="fa fa-desktop"></i> Printers on this server</h5>
      </div>
      <div class="panel-body" style="font-size:13px;">
        <p class="text-muted" style="margin-bottom:12px;">
          Add each physical printer once, then choose what it is for: <strong>Main kitchen (KOT)</strong> uses the default kitchen counter,
          <strong>Grill / GOT</strong> is linked to a kitchen / grill / BOH counter (department 2+), <strong>Cashier order</strong> is linked to the counter that should receive cashier-order slips, and <strong>Counter receipts</strong> is linked to the counter that actually sells those tickets and covers <strong>Bill + BOT + Returns + Purchase / Summary slips</strong>.
          The easiest online setup is to load names straight from <strong>QZ Tray</strong> in the browser on the print machine. After changes, use <strong>Re-apply registry to counters</strong> if needed.
        </p>
        <?php if (stripos(PHP_OS, 'WIN') !== 0): ?>
        <div class="alert alert-warning" style="font-size:12px;">Printer list loading uses PowerShell and only works when PHP runs on <strong>Windows</strong> (this server is not Windows — add printers via SQL or move settings to the print server).</div>
        <?php endif; ?>
        <div class="row" style="margin-bottom:12px;">
          <div class="col-md-3 col-sm-6" style="margin-bottom:8px;">
            <button type="button" class="btn btn-primary btn-block" id="kp_btn_load_qz"><i class="fa fa-plug"></i> Load From QZ Tray</button>
          </div>
          <div class="col-md-4 col-sm-6" style="margin-bottom:8px;">
            <select id="kp_sel_windows" class="form-control" style="font-weight:600;">
              <option value="">— Click “Load From QZ Tray” first —</option>
            </select>
          </div>
          <div class="col-md-2 col-sm-6" style="margin-bottom:8px;">
            <button type="button" class="btn btn-default btn-block" id="kp_btn_load_printers"><i class="fa fa-windows"></i> Windows</button>
          </div>
          <div class="col-md-3 col-sm-12" style="margin-bottom:8px;">
            <input type="text" id="kp_in_label" class="form-control" placeholder="Optional label (e.g. Front bar)">
          </div>
        </div>
        <div class="alert alert-info" style="font-size:12px;margin-bottom:12px;">
          <strong>Recommended:</strong> if your system is online, open this page on the local print machine and use <strong>Load From QZ Tray</strong>. That reads the Windows printers through the browser without needing the online server itself to see local printers.
        </div>
        <div class="row" style="margin-bottom:12px;">
          <div class="col-md-4 col-sm-6" style="margin-bottom:8px;">
            <label class="control-label" style="font-size:12px;font-weight:700;">Use for</label>
            <select id="kp_sel_role" class="form-control">
              <option value="kitchen_main">Main kitchen — KOT (default kitchen counter)</option>
              <option value="grill_kot">Grill / GOT — grill ticket printer (pick grill or BOH counter)</option>
              <option value="cashier_order">Cashier order — own printer for the selected counter</option>
              <option value="counter_bill_bot" selected>Counter receipts — Bill + BOT + returns + slips</option>
            </select>
          </div>
          <div class="col-md-5 col-sm-6" style="margin-bottom:8px;" id="kp_wrap_counter">
            <label class="control-label" style="font-size:12px;font-weight:700;">Counter</label>
            <select id="kp_sel_counter" class="form-control"></select>
          </div>
          <div class="col-md-3 col-sm-12" style="margin-bottom:8px;padding-top:22px;">
            <button type="button" class="btn btn-success btn-block" id="kp_btn_add"><i class="fa fa-plus"></i> Add printer</button>
          </div>
        </div>
        <div class="row" style="margin-bottom:10px;">
          <div class="col-md-12">
            <button type="button" class="btn btn-info btn-sm" id="kp_btn_test_printer"><i class="fa fa-print"></i> Test selected printer</button>
            <span class="text-muted" style="font-size:12px;margin-left:8px;">Sends one line of text through Windows (same queue name the worker uses). Run on the Windows print server.</span>
          </div>
        </div>
        <div id="kp_registry_msg"></div>
        <div class="table-responsive">
          <table class="table table-bordered table-condensed" style="margin-bottom:0;background:#fafafa;">
            <thead style="background:#eef2f7;">
              <tr>
                <th>Windows name</th>
                <th>Label</th>
                <th>Role</th>
                <th>Department</th>
                <th>Counter</th>
                <th style="width:120px;">Actions</th>
              </tr>
            </thead>
            <tbody id="kp_registry_tbody">
              <tr><td colspan="6" class="text-muted text-center">Loading…</td></tr>
            </tbody>
          </table>
        </div>
        <p style="margin-top:10px;margin-bottom:0;">
          <button type="button" class="btn btn-default btn-sm" id="kp_btn_sync"><i class="fa fa-link"></i> Re-apply registry to counters</button>
        </p>
      </div>
    </div>

    <div class="panel panel-default" style="margin-bottom:16px;">
      <div class="panel-heading" style="background:#31475f;color:#fff;">
        <h5 class="panel-title"><i class="fa fa-heartbeat"></i> Print Worker Health</h5>
      </div>
      <div class="panel-body">
        <div class="print-stat-grid">
          <div class="print-status-card">
            <h4>Worker Status</h4>
            <div class="print-stat-number" id="pw-status-state">...</div>
            <div class="print-stat-label">Current state</div>
          </div>
          <div class="print-status-card">
            <h4>Pending Queue</h4>
            <div class="print-stat-number" id="pw-status-pending">0</div>
            <div class="print-stat-label">Waiting jobs</div>
          </div>
          <div class="print-status-card">
            <h4>Failed Jobs</h4>
            <div class="print-stat-number" id="pw-status-failed">0</div>
            <div class="print-stat-label">Need attention</div>
          </div>
        </div>
        <div class="print-status-banner" id="pw-status-message">Checking print worker status...</div>
        <div class="print-quick-links" style="margin-top:12px;">
          <button type="button" class="btn btn-primary btn-sm" id="pw-refresh-btn"><i class="fa fa-refresh"></i> Refresh Worker Status</button>
          <button type="button" class="btn btn-success btn-sm" id="pw-start-btn"><i class="fa fa-play"></i> Start Windows Worker</button>
          <button type="button" class="btn btn-danger btn-sm" id="pw-stop-btn"><i class="fa fa-stop"></i> Stop Windows Worker</button>
          <a href="<?php echo htmlspecialchars($browser_worker_url); ?>" target="_blank" rel="noopener" class="btn btn-default btn-sm"><i class="fa fa-external-link"></i> Open Browser Tab Worker</a>
          <a href="<?php echo htmlspecialchars($qz_worker_url); ?>" target="_blank" rel="noopener" class="btn btn-warning btn-sm"><i class="fa fa-external-link"></i> Open QZ Tray Worker</a>
        </div>
      </div>
    </div>

    <div class="panel panel-default" style="margin-bottom:16px;">
      <div class="panel-heading" style="background:#34495e;color:#fff;">
        <h5 class="panel-title"><i class="fa fa-table"></i> Current counter mapping (what the print worker uses)</h5>
      </div>
      <div class="panel-body" style="padding:0;">
        <table class="table table-condensed" style="margin:0;font-size:13px;">
          <thead><tr style="background:#ecf0f1;"><th>Dept</th><th>Counter</th><th>Windows printer</th></tr></thead>
          <tbody>
            <?php
            $map_q = mysqli_query($connection, "SELECT cntr_id, name, dpt, printer_name FROM kp_counters WHERE del_status=2 ORDER BY dpt ASC, name ASC");
            while ($m = mysqli_fetch_assoc($map_q)) {
                $dpt = kp_counter_dpt_title((int) $m['dpt']);
                $pn = trim((string)$m['printer_name']);
                if ($pn === '') {
                    $pn = '<span class="text-danger">Not set</span>';
                } else {
                    $pn = htmlspecialchars($pn);
                }
                ?>
            <tr>
              <td><span class="label label-default"><?php echo htmlspecialchars($dpt); ?></span></td>
              <td><strong><?php echo htmlspecialchars($m['name']); ?></strong></td>
              <td><?php echo $pn; ?></td>
            </tr>
            <?php } ?>
          </tbody>
        </table>
        <p class="text-muted" style="padding:10px 12px;margin:0;font-size:12px;">
          <?php
          $kid = kp_default_kitchen_counter_id($connection);
          $knm = $kid > 0 ? htmlspecialchars(get_column('kp_counters', 'name', "cntr_id='$kid'")) : '—';
          ?>
          <strong>Default kitchen counter</strong> for “Main kitchen” KOT: <?php echo $knm; ?> (set “kit default” on a kitchen counter if needed).
          Separate cashier-order printers stay in the printer registry above and are resolved by print type.
        </p>
      </div>
    </div>

    <div class="panel panel-default" style="margin-bottom:16px;">
      <div class="panel-heading" style="background:#2d6a4f;color:#fff;">
        <h5 class="panel-title"><i class="fa fa-cog"></i> Windows Print Worker Config</h5>
      </div>
      <div class="panel-body" style="font-size:13px;">
        <div id="pw-config-msg"></div>
        <p>Fill these details here and the system will write <code>tools/print_worker.config.php</code> for you. You do not need to set the database manually because the worker already uses <code>inc/connection.php</code>.</p>
        <form id="pw-config-form">
          <div class="row">
            <div class="col-md-6">
              <div class="form-group">
                <label>Base URL</label>
                <input type="text" class="form-control" id="pw_base_url" name="base_url" value="<?php echo htmlspecialchars((string)$worker_cfg['base_url']); ?>" placeholder="http://127.0.0.1/clients/ktouch/gpints">
                <small class="text-muted">If the main app is online and this print server is local, use the public live URL here.</small>
              </div>
            </div>
            <div class="col-md-6">
              <div class="form-group">
                <label>Render Token</label>
                <div class="input-group">
                  <input type="text" class="form-control" id="pw_render_token" name="render_token" value="<?php echo htmlspecialchars((string)$worker_cfg['render_token']); ?>">
                  <span class="input-group-btn">
                    <button type="button" class="btn btn-default" id="pw-generate-token">Generate</button>
                  </span>
                </div>
              </div>
            </div>
          </div>
          <div class="row">
            <div class="col-md-4">
              <div class="form-group">
                <label>Worker User ID</label>
                <input type="number" min="1" class="form-control" id="pw_worker_user_id" name="worker_user_id" value="<?php echo (int)$worker_cfg['worker_user_id']; ?>">
              </div>
            </div>
            <div class="col-md-4">
              <div class="form-group">
                <label>Poll Seconds</label>
                <input type="number" min="1" class="form-control" id="pw_poll_seconds" name="poll_seconds" value="<?php echo (int)$worker_cfg['poll_seconds']; ?>">
              </div>
            </div>
            <div class="col-md-4">
              <div class="form-group">
                <label>Max Attempts</label>
                <input type="number" min="1" class="form-control" id="pw_max_attempts" name="max_attempts" value="<?php echo (int)$worker_cfg['max_attempts']; ?>">
              </div>
            </div>
          </div>
          <div class="row">
            <div class="col-md-6">
              <div class="form-group">
                <label>SumatraPDF Path</label>
                <input type="text" class="form-control" id="pw_sumatra_path" name="sumatra_path" value="<?php echo htmlspecialchars((string)$worker_cfg['sumatra_path']); ?>">
              </div>
            </div>
            <div class="col-md-6">
              <div class="form-group">
                <label>PHP Binary Path</label>
                <input type="text" class="form-control" id="pw_php_binary" name="php_binary" value="<?php echo htmlspecialchars((string)$worker_cfg['php_binary']); ?>">
              </div>
            </div>
          </div>
          <div class="row">
            <div class="col-md-6">
              <div class="form-group">
                <label>Edge Path</label>
                <input type="text" class="form-control" id="pw_edge_path" name="edge_path" value="<?php echo htmlspecialchars((string)$worker_cfg['edge_path']); ?>">
                <small class="text-muted">Optional. Leave blank if the machine uses the common Edge install path.</small>
              </div>
            </div>
            <div class="col-md-6">
              <div class="form-group">
                <label>Task Scheduler Name</label>
                <input type="text" class="form-control" id="pw_task_name" value="KTouch Print Worker">
              </div>
            </div>
          </div>
          <div style="display:flex; gap:10px; flex-wrap:wrap;">
            <button type="button" class="btn btn-success" id="pw-save-config-btn"><i class="fa fa-save"></i> Save Worker Config</button>
            <button type="button" class="btn btn-primary" id="pw-download-task-btn"><i class="fa fa-download"></i> Download Task Scheduler File</button>
          </div>
        </form>
      </div>
    </div>

    <!-- Queue status -->
    <div class="panel panel-default" style="margin-top:10px;">
      <div class="panel-heading">
        <h5 class="panel-title"><i class="fa fa-list"></i> Print Queue Status
          <button class="btn btn-xs btn-default pull-right" onclick="refreshQueue();">
            <i class="fa fa-refresh"></i> Refresh
          </button>
        </h5>
      </div>
      <div class="panel-body" id="queue-status-body">
        <?php
        $pending = (int)(mysqli_fetch_assoc(mysqli_query($connection, "SELECT COUNT(*) AS n FROM kp_print_queue WHERE status=0"))['n'] ?? 0);
        $done    = (int)(mysqli_fetch_assoc(mysqli_query($connection, "SELECT COUNT(*) AS n FROM kp_print_queue WHERE status=1"))['n'] ?? 0);
        $today   = (int)(mysqli_fetch_assoc(mysqli_query($connection, "SELECT COUNT(*) AS n FROM kp_print_queue WHERE DATE(created_at)=CURDATE()"))['n'] ?? 0);
        ?>
        <div class="row text-center">
          <div class="col-md-4">
            <div class="widget widget-stats bg-orange" style="padding:14px; border-radius:6px;">
              <div class="stats-number" style="font-size:28px; font-weight:bold;"><?php echo $pending; ?></div>
              <div class="stats-desc">Pending Jobs</div>
            </div>
          </div>
          <div class="col-md-4">
            <div class="widget widget-stats bg-green" style="padding:14px; border-radius:6px;">
              <div class="stats-number" style="font-size:28px; font-weight:bold;"><?php echo $done; ?></div>
              <div class="stats-desc">Completed (all time)</div>
            </div>
          </div>
          <div class="col-md-4">
            <div class="widget widget-stats bg-blue" style="padding:14px; border-radius:6px;">
              <div class="stats-number" style="font-size:28px; font-weight:bold;"><?php echo $today; ?></div>
              <div class="stats-desc">Jobs Today</div>
            </div>
          </div>
        </div>

        <!-- Recent pending jobs table -->
        <?php
        $recent_q = mysqli_query($connection,
            "SELECT q.queue_id, q.ref_id, q.sale_id, q.print_type, q.status, q.created_at, q.cntr_id, q.route_path, q.route_query, c.name AS cntr_name
             FROM kp_print_queue q
             LEFT JOIN kp_counters c ON c.cntr_id = q.cntr_id
             ORDER BY q.queue_id DESC LIMIT 20"
        );
        $type_labels = ['bill' => 'primary', 'bot' => 'warning', 'kot' => 'success'];
        ?>
        <table class="table table-condensed table-bordered" style="margin-top:12px; font-size:13px;">
          <thead>
            <tr>
              <th>#</th>
              <th>Type</th>
              <th>Sale ID</th>
              <th>Counter</th>
              <th>Status</th>
              <th>Time</th>
              <th></th>
            </tr>
          </thead>
          <tbody>
            <?php while ($jb = mysqli_fetch_assoc($recent_q)):
              $lbl = $type_labels[$jb['print_type']] ?? 'default';
              $st_class = $jb['status'] == 1 ? 'success' : 'warning';
              $st_text  = $jb['status'] == 1 ? 'Done' : 'Pending';
              $preview_url = function_exists('kp_print_job_preview_url') ? kp_print_job_preview_url($jb) : '';
            ?>
            <tr>
              <td><?php echo $jb['queue_id']; ?></td>
              <td><span class="label label-<?php echo $lbl; ?>"><?php echo strtoupper($jb['print_type']); ?></span></td>
              <td>#<?php echo $jb['sale_id']; ?></td>
              <td><?php echo htmlspecialchars($jb['cntr_name'] ?? '-'); ?></td>
              <td><span class="label label-<?php echo $st_class; ?>"><?php echo $st_text; ?></span></td>
              <td><?php echo $jb['created_at']; ?></td>
              <td>
                <?php if ($preview_url !== ''): ?>
                <a class="btn btn-xs btn-primary" href="<?php echo htmlspecialchars($preview_url); ?>" target="_blank" rel="noopener" title="Show receipt">
                  <i class="fa fa-file-text-o"></i>
                </a>
                <?php endif; ?>
                <?php if ((int)$jb['cntr_id'] > 0): ?>
                <button class="btn btn-xs btn-success" onclick="reprintQueueJob(<?php echo (int)$jb['queue_id']; ?>, <?php echo (int)$jb['cntr_id']; ?>);" title="Reprint">
                  <i class="fa fa-print"></i>
                </button>
                <?php endif; ?>
                <?php if ($jb['status'] == 0): ?>
                <button class="btn btn-xs btn-danger" onclick="cancelJob(<?php echo $jb['queue_id']; ?>);" title="Cancel job">
                  <i class="fa fa-times"></i>
                </button>
                <?php endif; ?>
              </td>
            </tr>
            <?php endwhile; ?>
          </tbody>
        </table>
      </div>
    </div><!-- end queue panel -->

  <!-- Task Scheduler Setup -->
    <div class="panel panel-default" style="margin-top:14px;">
      <div class="panel-heading" style="background:#8e44ad;color:#fff;">
        <h5 class="panel-title"><i class="fa fa-clock-o"></i> Auto-Start on Windows</h5>
      </div>
      <div class="panel-body" style="font-size:13px;">
        <p>Use Windows Task Scheduler when you want printing to come back automatically after every login or restart.</p>
        <div class="row">
          <div class="col-md-6">
            <h5 style="font-weight:800;color:#4d2a73;">Browser worker</h5>
            <ol>
              <li>Open <code>taskschd.msc</code>.</li>
              <li>Create a task named <code>K-TOUCH Browser Print Server</code>.</li>
              <li>Trigger: <strong>When I log on</strong>.</li>
              <li>Action: <strong>Start a program</strong>.</li>
              <li>Use your browser, or simply run the startup script listed below.</li>
            </ol>
          </div>
          <div class="col-md-6">
            <h5 style="font-weight:800;color:#4d2a73;">Windows PHP worker</h5>
            <ol>
              <li>Copy <code>print_worker.config.example.php</code> to <code>print_worker.config.php</code>.</li>
              <li>Do <strong>not</strong> set database values unless you want a different database. The worker already uses the normal app connection from <code>inc/connection.php</code>.</li>
              <li>Fill only the local machine values you need: <code>base_url</code>, <code>render_token</code>, <code>worker_user_id</code>, <code>sumatra_path</code>, and optionally <code>php_binary</code> / <code>edge_path</code>.</li>
              <li>Create a task that runs PHP with <code>print_worker.php</code> at login.</li>
              <li>Refresh the worker health panel above and confirm it shows <strong>Running</strong>.</li>
            </ol>
          </div>
        </div>
        <div class="print-plain-card" style="margin-top:12px;">
          <h5 style="margin-top:0;font-weight:800;color:#4d2a73;">Recommended local print server steps</h5>
          <ol style="padding-left:18px;margin-bottom:10px;">
            <li>Choose <strong>Mode 3</strong> in this page.</li>
            <li>Use one local Windows machine as the print server.</li>
            <li>Install all receipt printers on that machine and print a Windows test page for each one.</li>
            <li>In this page, load printers and map each printer to the right counter, kitchen, grill, bill, or BOT role.</li>
            <li>Copy <code>tools/print_worker.config.example.php</code> to <code>tools/print_worker.config.php</code>.</li>
            <li>Keep the same database connection already used by the system. No extra database setup is needed if this machine uses the same project files and <code>inc/connection.php</code>.</li>
            <li>Set <code>base_url</code> to the address that opens this system from the print machine.</li>
            <li>Set <code>render_token</code>, <code>worker_user_id</code>, <code>sumatra_path</code>, and <code>php_binary</code>.</li>
            <li>Run the worker once manually, confirm test prints work, then add it to Task Scheduler for auto-start.</li>
          </ol>
          <p style="margin-bottom:0;"><strong>If your main system is online but the print server is local:</strong> keep the printers on the local Windows machine, but set <code>base_url</code> to the public online URL of your live system instead of <code>localhost</code> or <code>127.0.0.1</code>. Make sure the print machine has internet access to that site, and keep the same <code>render_token</code> on both sides.</p>
        </div>
        <div class="print-code-box" style="margin-top:10px;">
Generic browser worker: <?php echo htmlspecialchars($print_server_url); ?><br>
Browser tab worker (Mode 2): <?php echo htmlspecialchars($browser_worker_url); ?><br>
QZ Tray worker (Mode 4): <?php echo htmlspecialchars($qz_worker_url); ?><br>
Startup script: <?php echo htmlspecialchars($startup_bat_path ?: 'gen/startup_print.bat'); ?><br>
Worker config example: <?php echo htmlspecialchars($worker_config_example_path ?: 'tools/print_worker.config.example.php'); ?><br>
Worker config file: <?php echo htmlspecialchars($worker_config_path ?: 'tools/print_worker.config.php'); ?><br>
Windows worker script: <?php echo htmlspecialchars($worker_script_path ?: 'tools/print_worker.php'); ?><br>
Named Windows worker file: <?php echo htmlspecialchars($windows_worker_named_path ?: 'tools/windows_print_server_worker.php'); ?>
        </div>
        <p style="margin-top:8px;"><em>The browser worker remembers its last selected counter and starts polling again when reopened.</em></p>
      </div>
    </div>

  </div><!-- end panel-body -->
</div><!-- end panel -->

<script src="../../qz/ktouch-qz-security.js"></script>
<script>
var KP_QZ_LIBRARY_URLS = [
  'https://cdn.jsdelivr.net/npm/qz-tray@2.2.5/qz-tray.js',
  'https://unpkg.com/qz-tray@2.2.5/qz-tray.js',
  '../../qz/qz-tray.js'
];
var kpQzLibraryPromise = null;

function kpLoadQzLibraryScript(src) {
  return new Promise(function (resolve, reject) {
    var existing = document.querySelector('script[data-kp-qz-src="' + src.replace(/"/g, '\\"') + '"]');
    if (existing) {
      if (window.qz && window.qz.websocket) {
        resolve(window.qz);
        return;
      }
      existing.addEventListener('load', function () { resolve(window.qz); }, { once: true });
      existing.addEventListener('error', function () { reject(new Error('Could not load ' + src)); }, { once: true });
      return;
    }

    var script = document.createElement('script');
    script.src = src;
    script.async = false;
    script.setAttribute('data-kp-qz-src', src);
    script.onload = function () { resolve(window.qz); };
    script.onerror = function () { reject(new Error('Could not load ' + src)); };
    document.head.appendChild(script);
  });
}

function kpEnsureQzLibrary() {
  if (window.qz && window.qz.websocket) {
    return Promise.resolve(window.qz);
  }
  if (kpQzLibraryPromise) {
    return kpQzLibraryPromise;
  }

  kpQzLibraryPromise = new Promise(function (resolve, reject) {
    var urls = KP_QZ_LIBRARY_URLS.slice();

    function tryNext(lastError) {
      if (!urls.length) {
        reject(lastError || new Error('QZ Tray JavaScript library not loaded'));
        return;
      }
      var src = urls.shift();
      kpLoadQzLibraryScript(src).then(function () {
        if (window.qz && window.qz.websocket) {
          resolve(window.qz);
          return;
        }
        tryNext(new Error('QZ Tray JavaScript library loaded from ' + src + ' but window.qz is unavailable'));
      }).catch(function (err) {
        tryNext(err);
      });
    }

    tryNext();
  }).catch(function (err) {
    kpQzLibraryPromise = null;
    throw err;
  });

  return kpQzLibraryPromise;
}

if (!window.KTouchQzSecurity) {
  window.KTouchQzSecurity = (function () {
    function normalizeError(err, fallback) {
      if (!err) return fallback;
      if (err.message) return err.message;
      return String(err);
    }
    function createLogger(logger) {
      return typeof logger === 'function' ? logger : function () {};
    }
    function create(options) {
      options = options || {};
      var log = createLogger(options.logger);
      var certificateUrl = String(options.certificateUrl || '');
      var signUrl = String(options.signUrl || '');
      var certificateCache = null;
      var setupDone = false;

      function getQzObject() {
        return window.qz || null;
      }

      function fetchCertificate(resolve, reject) {
        if (certificateCache !== null) {
          resolve(certificateCache);
          return;
        }
        fetch(certificateUrl, { method: 'GET', credentials: 'same-origin', cache: 'no-store' })
          .then(function (response) {
            if (!response.ok) throw new Error('Certificate request failed with HTTP ' + response.status);
            return response.text();
          })
          .then(function (text) {
            var certificate = String(text || '').trim();
            if (!certificate) throw new Error('Certificate response was empty');
            certificateCache = certificate;
            resolve(certificateCache);
          })
          .catch(function (err) {
            var message = normalizeError(err, 'Certificate request failed');
            log('certificate_error', message);
            reject(new Error(message));
          });
      }

      function fetchSignature(toSign, resolve, reject) {
        var body = new URLSearchParams();
        body.set('request', toSign);
        fetch(signUrl, {
          method: 'POST',
          credentials: 'same-origin',
          cache: 'no-store',
          headers: {
            'Content-Type': 'application/x-www-form-urlencoded; charset=UTF-8',
            'X-Requested-With': 'XMLHttpRequest'
          },
          body: body.toString()
        }).then(function (response) {
          return response.json().catch(function () {
            throw new Error('Signing response was not valid JSON');
          }).then(function (payload) {
            if (!response.ok || !payload || !payload.ok || !payload.signature) {
              throw new Error((payload && payload.message) ? payload.message : ('Signing request failed with HTTP ' + response.status));
            }
            return payload.signature;
          });
        }).then(function (signature) {
          resolve(signature);
        }).catch(function (err) {
          var message = normalizeError(err, 'Signing request failed');
          log('sign_error', message);
          reject(new Error(message));
        });
      }

      return {
        configure: function () {
          if (setupDone) return Promise.resolve();
          return kpEnsureQzLibrary().then(function () {
            return new Promise(function (resolve, reject) {
            var qzObject = getQzObject();
            if (!qzObject) {
              reject(new Error('QZ Tray JavaScript library not loaded'));
              return;
            }
            if (!certificateUrl || !signUrl) {
              reject(new Error('QZ security endpoints are not configured'));
              return;
            }
            qzObject.security.setCertificatePromise(function (certResolve, certReject) {
              fetchCertificate(certResolve, certReject);
            });
            if (typeof qzObject.security.setSignatureAlgorithm === 'function') {
              qzObject.security.setSignatureAlgorithm('SHA512');
            }
            qzObject.security.setSignaturePromise(function (toSign) {
              return function (signResolve, signReject) {
                fetchSignature(toSign, signResolve, signReject);
              };
            });
            setupDone = true;
            log('configured', 'QZ trusted signing configured');
            resolve();
            });
          });
        },
        ensureReady: function () {
          return this.configure().then(function () {
            var qzObject = getQzObject();
            if (!qzObject) {
              throw new Error('QZ Tray JavaScript library not loaded');
            }
            if (qzObject.websocket.isActive()) return null;
            return qzObject.websocket.connect();
          });
        }
      };
    }
    return { create: create };
  }());
}
</script>
<script>
var KP_COUNTERS_BY_DPT = <?php echo json_encode($counters_by_dpt); ?>;
var KP_DPT_TITLES = <?php echo json_encode($dpt_titles_js); ?>;
var KP_PRINTER_SOURCE = 'windows';
var KP_QZ_TRUST_CONFIGURED = <?php echo $qzTrustConfigured ? 'true' : 'false'; ?>;
var KP_QZ_PREVIEW_URL = <?php echo json_encode($qzPreviewUrl); ?>;
var kpQzSecurity = null;

function kpGetQzSecurity() {
  if (kpQzSecurity) {
    return kpQzSecurity;
  }
  if (!window.KTouchQzSecurity || typeof window.KTouchQzSecurity.create !== 'function') {
    throw new Error('QZ security helper not loaded');
  }
  kpQzSecurity = window.KTouchQzSecurity.create({
    certificateUrl: '<?php echo htmlspecialchars($qzSecurityBaseUrl, ENT_QUOTES); ?>?action=certificate',
    signUrl: '<?php echo htmlspecialchars($qzSecurityBaseUrl, ENT_QUOTES); ?>?action=sign',
    logger: function (eventName, detail) {
      if (window.console && console.warn) {
        console.warn('[QZ Security][' + eventName + ']', detail);
      }
    }
  });
  return kpQzSecurity;
}

function kpRoleLabel(role) {
  if (role === 'kitchen_main') return 'Main kitchen (KOT)';
  if (role === 'grill_kot') return 'Grill / GOT';
  if (role === 'cashier_order') return 'Cashier order';
  return 'Counter receipts';
}

function kpFillCounterSelect() {
  var role = $('#kp_sel_role').val();
  var $s = $('#kp_sel_counter').empty();
  if (role === 'kitchen_main') {
    $('#kp_wrap_counter').hide();
    return;
  }
  $('#kp_wrap_counter').show();
  $s.append('<option value="0">— Select counter —</option>');
  var dpts = Object.keys(KP_COUNTERS_BY_DPT || {}).map(function (x) { return parseInt(x, 10); }).sort(function (a, b) { return a - b; });
  for (var di = 0; di < dpts.length; di++) {
    var d = dpts[di];
    if (role === 'grill_kot' && d < 2) {
      continue;
    }
    var list = KP_COUNTERS_BY_DPT[String(d)] || [];
    if (!list.length) {
      continue;
    }
    var title = (KP_DPT_TITLES && KP_DPT_TITLES[String(d)]) ? KP_DPT_TITLES[String(d)] : ('Department ' + d);
    var $og = $('<optgroup></optgroup>').attr('label', title);
    for (var j = 0; j < list.length; j++) {
      var c = list[j];
      var hint = (c.printer_name && String(c.printer_name).trim()) ? (' — ' + c.printer_name) : '';
      $og.append($('<option></option>').attr('value', c.cntr_id).text(c.name + hint));
    }
    $s.append($og);
  }
}

function kpTestPrinter(windowsName) {
  var win = (windowsName != null && String(windowsName).trim() !== '') ? String(windowsName).trim() : String($('#kp_sel_windows').val() || '').trim();
  if (!win) {
    kpMsg('Choose a Windows printer from the list.', 'warning');
    return;
  }
  $('#kp_btn_test_printer').prop('disabled', true);
  $.post('../settings/printer_set.php', { action: 'test_print', windows_name: win }, function (res) {
    if (res && res.ok) {
      kpMsg(res.message || 'Test sent.', 'info');
    } else {
      kpMsg((res && res.message) || 'Test failed.', 'danger');
    }
  }, 'json').fail(function () {
    kpMsg('Test request failed.', 'danger');
  }).always(function () {
    $('#kp_btn_test_printer').prop('disabled', false);
  });
}

function kpLoadWinPrinters() {
  $('#kp_btn_load_printers').prop('disabled', true);
  $.post('../settings/printer_set.php', { action: 'list_win_printers' }, function (res) {
    var printers = [];
    if (res && res.ok && res.printers && res.printers.length) {
      printers = res.printers;
    }
    kpPopulatePrinterSelect(printers, printers.length ? 'windows' : 'none');
    if (!res || !res.ok || !res.printers || !res.printers.length) {
      kpMsg('Could not read Windows printers. Enter names manually in the database or enable PowerShell.', 'warning');
      return;
    }
    kpMsg('Loaded printers from the local Windows server.', 'info');
  }, 'json').fail(function () {
    kpMsg('Request failed.', 'danger');
  }).always(function () {
    $('#kp_btn_load_printers').prop('disabled', false);
  });
}

function kpPopulatePrinterSelect(printers, source) {
  KP_PRINTER_SOURCE = source || 'windows';
  var $sel = $('#kp_sel_windows').empty();
  var title = source === 'qz' ? '— Select QZ Tray printer —' : '— Select printer —';
  if (!printers || !printers.length) {
    var emptyText = source === 'qz'
      ? '(No printers found from QZ Tray on this machine)'
      : '(No printers found — run on Windows server / enable shell_exec)';
    $sel.append($('<option></option>').val('').text(emptyText));
    return;
  }
  $sel.append($('<option></option>').val('').text(title));
  for (var i = 0; i < printers.length; i++) {
    var n = String(printers[i] || '').trim();
    if (!n) continue;
    $sel.append($('<option></option>').val(n).text(n));
  }
}

function kpEnsureQzReady() {
  return kpEnsureQzLibrary().then(function () {
    return kpGetQzSecurity().ensureReady();
  }).catch(function (err) {
    var msg = 'Could not connect to trusted QZ Tray on this machine. Make sure QZ Tray is open, running on this same PC, and that the QZ certificate/private key files are configured on the server.';
    if (err && err.message) {
      msg += ' Technical detail: ' + err.message;
    }
    throw new Error(msg);
  });
}

function kpQzFetchPrinters() {
  if (!window.qz || !qz.printers) {
    return Promise.reject(new Error('QZ Tray JavaScript library is not available on this page.'));
  }
  if (typeof qz.printers.findAll === 'function') {
    return qz.printers.findAll().then(function (printers) {
      return Array.isArray(printers) ? printers : [];
    });
  }
  if (typeof qz.printers.details === 'function') {
    return qz.printers.details().then(function (printers) {
      if (!Array.isArray(printers)) {
        return [];
      }
      return printers.map(function (printer) {
        if (printer && printer.name) {
          return printer.name;
        }
        return '';
      }).filter(function (name) {
        return String(name || '').trim() !== '';
      });
    });
  }
  return qz.printers.find().then(function (printers) {
    if (Array.isArray(printers)) {
      return printers;
    }
    if (typeof printers === 'string' && printers.trim() !== '') {
      return [printers.trim()];
    }
    return [];
  });
}

function kpLoadQzPrinters() {
  $('#kp_btn_load_qz').prop('disabled', true);
  kpEnsureQzReady()
    .then(function () { return kpQzFetchPrinters(); })
    .then(function (printers) {
      kpPopulatePrinterSelect(printers || [], 'qz');
      if (printers && printers.length) {
        kpMsg('Loaded printers from QZ Tray on this local machine.', 'info');
      } else {
        kpMsg('QZ Tray connected, but it did not return any printers.', 'warning');
      }
    })
    .catch(function (err) {
      var msg = (err && err.message) ? err.message : String(err);
      kpPopulatePrinterSelect([], 'qz');
      kpMsg(
        'Could not read printers from QZ Tray.<br>' +
        '1. Open QZ Tray on this same Windows machine.<br>' +
        '2. Make sure this browser page is also opened on that same machine.<br>' +
        '3. Confirm trusted signing files are configured on the server if QZ keeps showing anonymous prompts.<br>' +
        '4. If needed, restart QZ Tray and refresh this page.<br><br>' +
        '<strong>Technical detail:</strong> ' + msg,
        'danger'
      );
    })
    .finally(function () {
      $('#kp_btn_load_qz').prop('disabled', false);
    });
}

function kpMsg(html, kind) {
  kind = kind || 'info';
  var cls = kind === 'danger' ? 'alert-danger' : (kind === 'warning' ? 'alert-warning' : 'alert-info');
  $('#kp_registry_msg').html('<div class="alert ' + cls + '" style="margin-bottom:10px;padding:8px 12px;">' + html + '</div>');
  setTimeout(function () { $('#kp_registry_msg').html(''); }, 6000);
}

function kpQzFitMsg(html, kind) {
  kind = kind || 'info';
  var cls = kind === 'danger' ? 'alert-danger' : (kind === 'warning' ? 'alert-warning' : 'alert-info');
  $('#kp_qz_fit_msg').html('<div class="alert ' + cls + '" style="margin-bottom:10px;padding:8px 12px;">' + html + '</div>');
}

function kpQzFitValues() {
  return {
    paper: parseFloat($('#kp_qz_paper_width_mm').val()) || 80,
    margin: parseFloat($('#kp_qz_margin_mm').val()) || 0,
    scale: parseFloat($('#kp_qz_scale_pct').val()) || 80
  };
}

function kpQzPreviewUrlForValues() {
  var vals = kpQzFitValues();
  return KP_QZ_PREVIEW_URL
    + '?paper_width_mm=' + encodeURIComponent(vals.paper)
    + '&margin_mm=' + encodeURIComponent(vals.margin)
    + '&scale_pct=' + encodeURIComponent(vals.scale);
}

function kpRefreshQzPreview() {
  var vals = kpQzFitValues();
  var url = kpQzPreviewUrlForValues();
  $('#kp_qz_preview_frame').attr('src', url);
  $('#kp_btn_open_qz_preview').attr('href', url);
  $('#kp_qz_preview_meta').text(
    'Previewing ' + vals.paper + 'mm paper, ' + vals.margin + 'mm margin, ' + vals.scale + '% scale'
  );
}

function kpLoadRegistry() {
  $.post('../settings/printer_set.php', { action: 'list_print_registry' }, function (res) {
    var $tb = $('#kp_registry_tbody').empty();
    if (!res || !res.ok) {
      $tb.html('<tr><td colspan="6" class="text-center text-muted">Could not load registry.</td></tr>');
      return;
    }
    var rows = res.rows || [];
    if (!rows.length) {
      $tb.html('<tr><td colspan="6" class="text-center text-muted">No printers added yet. Load list, pick role, add.</td></tr>');
      return;
    }
    for (var i = 0; i < rows.length; i++) {
      var r = rows[i];
      var cname = (r.cntr_name && String(r.cntr_name).trim()) ? r.cntr_name : (r.role === 'kitchen_main' ? 'Default kitchen' : '—');
      var dptNum = parseInt(r.cntr_dpt, 10);
      var dptCell = '—';
      if (r.role === 'kitchen_main') {
        dptCell = 'Kitchen (default)';
      } else if (!isNaN(dptNum) && dptNum >= 0) {
        dptCell = (KP_DPT_TITLES && KP_DPT_TITLES[String(dptNum)]) ? KP_DPT_TITLES[String(dptNum)] : ('Dept ' + dptNum);
      }
      var winName = r.windows_name;
      var $tr = $('<tr/>');
      $tr.append($('<td/>').append($('<code/>').css('font-size', '12px').text(winName)));
      $tr.append($('<td/>').text(r.label || ''));
      $tr.append($('<td/>').append($('<span class="label label-primary"/>').text(kpRoleLabel(r.role))));
      $tr.append($('<td/>').text(dptCell));
      $tr.append($('<td/>').text(cname));
      var $tdAct = $('<td/>');
      var $btnTest = $('<button type="button" class="btn btn-info btn-xs" title="Test print"><i class="fa fa-print"></i></button>');
      $btnTest.on('click', function (wn) {
        return function () { kpTestPrinter(wn); };
      }(winName));
      var $btnDel = $('<button type="button" class="btn btn-danger btn-xs"><i class="fa fa-trash"></i></button>');
      $btnDel.on('click', function (pid) {
        return function () { kpDeletePrn(pid); };
      }(r.prn_id));
      $tdAct.append($btnTest, ' ', $btnDel);
      $tr.append($tdAct);
      $tb.append($tr);
    }
  }, 'json');
}

function kpDeletePrn(id) {
  if (!confirm('Remove this printer from the registry? Counter names will be re-synced.')) return;
  $.post('../settings/printer_set.php', { action: 'delete_print_registry', prn_id: id }, function (res) {
    if (res && res.ok) {
      kpMsg('Removed.', 'info');
      kpLoadRegistry();
    } else {
      kpMsg('Delete failed.', 'danger');
    }
  }, 'json').fail(function () { kpMsg('Delete failed.', 'danger'); });
}

function saveQzFitSettings() {
  var payload = {
    action: 'save_qz_fit',
    qz_paper_width_mm: $('#kp_qz_paper_width_mm').val(),
    qz_margin_mm: $('#kp_qz_margin_mm').val(),
    qz_scale_pct: $('#kp_qz_scale_pct').val()
  };
  var $btn = $('#kp_btn_save_qz_fit').prop('disabled', true);
  $.post('../settings/printer_set.php', payload, function (res) {
    if (res && res.ok) {
      kpQzFitMsg(res.message || 'Saved.', 'info');
      kpRefreshQzPreview();
    } else {
      kpQzFitMsg((res && res.message) || 'Could not save QZ fit settings.', 'danger');
    }
  }, 'json').fail(function () {
    kpQzFitMsg('Could not save QZ fit settings.', 'danger');
  }).always(function () {
    $btn.prop('disabled', false);
  });
}

function reprintQueueJob(queueId, counterId) {
  if (!queueId || !counterId) {
    kpMsg('Queue job or printer counter is missing.', 'warning');
    return;
  }
  if (!confirm('Queue this print again to the same counter printer?')) return;
  $.post('../settings/printer_set.php', {
    action: 'reprint_job',
    queue_id: queueId,
    cntr_id: counterId
  }, function (res) {
    if (res && res.ok) {
      kpMsg(res.message || 'Reprint queued successfully.', 'info');
      refreshQueue();
    } else {
      kpMsg((res && res.message) || 'Could not queue the reprint job.', 'danger');
    }
  }, 'json').fail(function () {
    kpMsg('Could not queue the reprint job.', 'danger');
  });
}

function pwStatusText(res) {
  if (!res || !res.ok) return 'Could not read print worker status.';
  if (res.running) {
    if (res.processing > 0) return 'The print worker is running and actively processing jobs.';
    return 'The print worker is running and waiting for the next print job.';
  }
  if (res.config_present) {
    return 'The print worker is not running right now. You can start the Windows worker or open the browser worker page.';
  }
  return 'The browser worker can still run, but the Windows PHP worker needs its config file first.';
}

function loadPrintWorkerStatus() {
  $.getJSON('../../gen/print_worker_api.php', { action: 'status' }, function (res) {
    if (!res || !res.ok) {
      $('#pw-status-state').text('Error');
      $('#pw-status-message').text('Could not read worker status.');
      return;
    }
    $('#pw-status-state').text(res.running ? 'Running' : 'Stopped');
    $('#pw-status-pending').text(res.pending || 0);
    $('#pw-status-failed').text(res.failed || 0);
    $('#pw-status-message').html(
      pwStatusText(res) + (res.heartbeat_age_seconds != null ? ' Last heartbeat: ' + res.heartbeat_age_seconds + 's ago.' : '')
    );
  }).fail(function () {
    $('#pw-status-state').text('Error');
    $('#pw-status-message').text('Could not read worker status.');
  });
}

function workerAction(actionName) {
  $.getJSON('../../gen/print_worker_api.php', { action: actionName }, function (res) {
    kpMsg((res && res.message) ? res.message : ('Worker action: ' + actionName), res && res.ok ? 'info' : 'danger');
    setTimeout(loadPrintWorkerStatus, 1200);
  }).fail(function () {
    kpMsg('Could not ' + actionName + ' the worker.', 'danger');
  });
}

function pwConfigMsg(message, kind) {
  kind = kind || 'info';
  var cls = kind === 'danger' ? 'alert-danger' : (kind === 'warning' ? 'alert-warning' : 'alert-success');
  $('#pw-config-msg').html('<div class="alert ' + cls + '" style="margin-bottom:10px;padding:8px 12px;">' + message + '</div>');
}

function pwCollectConfigForm() {
  return {
    action: 'save_worker_config',
    base_url: $('#pw_base_url').val(),
    render_token: $('#pw_render_token').val(),
    worker_user_id: $('#pw_worker_user_id').val(),
    sumatra_path: $('#pw_sumatra_path').val(),
    edge_path: $('#pw_edge_path').val(),
    php_binary: $('#pw_php_binary').val(),
    poll_seconds: $('#pw_poll_seconds').val(),
    max_attempts: $('#pw_max_attempts').val()
  };
}

function pwDownloadWorkerTask() {
  var $form = $('<form method="post" action="../settings/printer_set.php" target="_blank"></form>');
  var payload = {
    action: 'download_worker_task',
    php_binary: $('#pw_php_binary').val(),
    task_name: $('#pw_task_name').val()
  };
  $.each(payload, function (key, value) {
    $form.append($('<input type="hidden">').attr('name', key).val(value));
  });
  $('body').append($form);
  $form.trigger('submit');
  $form.remove();
}

$(function () {
  if (!KP_QZ_TRUST_CONFIGURED) {
    kpMsg('QZ trusted signing is not configured yet. Put <code>digital-certificate.txt</code> and <code>private-key.pem</code> in <code>qz/storage/</code> to stop repeated Allow/Block prompts.', 'warning');
  }
  kpFillCounterSelect();
  kpLoadRegistry();
  loadPrintWorkerStatus();
  $('#kp_sel_role').on('change', kpFillCounterSelect);
  $('#kp_btn_load_qz').on('click', kpLoadQzPrinters);
  $('#kp_btn_load_printers').on('click', kpLoadWinPrinters);
  $('#kp_btn_test_printer').on('click', function () { kpTestPrinter(null); });
  $('#kp_btn_sync').on('click', function () {
    $.post('../settings/printer_set.php', { action: 'sync_print_registry' }, function (res) {
      if (res && res.ok) {
        kpMsg(res.message || 'Synced.', 'info');
        setTimeout(function () { location.reload(); }, 800);
      }
    }, 'json');
  });
  $('#kp_btn_save_qz_fit').on('click', saveQzFitSettings);
  $('#kp_btn_refresh_qz_preview').on('click', kpRefreshQzPreview);
  $('#kp_qz_paper_width_mm, #kp_qz_margin_mm, #kp_qz_scale_pct').on('input change', kpRefreshQzPreview);
  kpRefreshQzPreview();
  $('#kp_btn_add').on('click', function () {
    var win = $('#kp_sel_windows').val();
    if (!win) {
      kpMsg('Choose a Windows printer from the list.', 'warning');
      return;
    }
    var role = $('#kp_sel_role').val();
    var cntr = parseInt($('#kp_sel_counter').val(), 10) || 0;
    if (role !== 'kitchen_main' && cntr < 1) {
      kpMsg('Pick which counter this printer belongs to.', 'warning');
      return;
    }
    $.post('../settings/printer_set.php', {
      action: 'add_print_registry',
      windows_name: win,
      label: $('#kp_in_label').val(),
      role: role,
      cntr_id: cntr
    }, function (res) {
      if (res && res.ok) {
        kpMsg(res.message || 'Saved.', 'info');
        $('#kp_in_label').val('');
        kpLoadRegistry();
        setTimeout(function () { location.reload(); }, 900);
      } else {
        kpMsg((res && res.message) || 'Could not save.', 'danger');
      }
    }, 'json').fail(function () { kpMsg('Request failed.', 'danger'); });
  });
  $('#pw-refresh-btn').on('click', loadPrintWorkerStatus);
  $('#pw-start-btn').on('click', function () { workerAction('start'); });
  $('#pw-stop-btn').on('click', function () { workerAction('stop'); });
  $('#pw-generate-token').on('click', function () {
    var token = '';
    var chars = 'ABCDEFGHIJKLMNOPQRSTUVWXYZabcdefghijklmnopqrstuvwxyz0123456789';
    for (var i = 0; i < 48; i++) {
      token += chars.charAt(Math.floor(Math.random() * chars.length));
    }
    $('#pw_render_token').val(token);
  });
  $('#pw-save-config-btn').on('click', function () {
    var payload = pwCollectConfigForm();
    var $btn = $(this).prop('disabled', true);
    $.post('../settings/printer_set.php', payload, function (res) {
      if (res && res.ok) {
        pwConfigMsg(res.message || 'Saved worker config.', 'success');
        setTimeout(loadPrintWorkerStatus, 800);
      } else {
        pwConfigMsg((res && res.message) ? res.message : 'Could not save worker config.', 'danger');
      }
    }, 'json').fail(function () {
      pwConfigMsg('Could not save worker config.', 'danger');
    }).always(function () {
      $btn.prop('disabled', false);
    });
  });
  $('#pw-download-task-btn').on('click', function () {
    pwDownloadWorkerTask();
  });
});

function cancelJob(queue_id) {
    if (!confirm('Cancel this print job?')) return;
    $.post('../settings/printer_set.php', {
        action: 'cancel_job',
        queue_id: queue_id
    }, function() {
        refreshQueue();
    }).fail(function() {
        var hulla = new hullabaloo();
        hulla.send('Could not cancel job!', 'danger');
    });
}

function refreshQueue() {
    $.ajax({
        url: '../settings/printer_set.php',
        data: '',
        dataType: 'html'
    }).done(function(data) {
        $('#set').html(data);
    });
}

function savePrintMode(mode) {
    $.post('../settings/printer_set.php', {
        action: 'save_print_mode',
        print_mode: mode
    }, function(data) {
        $('#mode-save-indicator').show();
        setTimeout(function() { $('#mode-save-indicator').hide(); }, 2500);
        // Refresh page to update border colours and alert
        setTimeout(function() { refreshQueue(); }, 600);
    }, 'json').fail(function() {
        var hulla = new hullabaloo();
        hulla.send('Could not save print mode!', 'danger');
    });
}
</script>
