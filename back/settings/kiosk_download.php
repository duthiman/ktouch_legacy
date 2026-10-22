<?php
/**
 * kiosk_download.php
 * Generates a Windows Task Scheduler XML file that opens the front panel
 * in the chosen browser with optional kiosk mode and zoom level.
 */

// ── Input validation ─────────────────────────────────────────────────────────
$allowed_browsers = ['chrome', 'edge', 'firefox'];
$allowed_zooms    = ['0.50','0.60','0.67','0.75','0.80','0.90','1.00'];

$browser  = isset($_POST['browser'])  && in_array($_POST['browser'],  $allowed_browsers) ? $_POST['browser']  : 'chrome';
$zoom     = isset($_POST['zoom'])     && in_array($_POST['zoom'],     $allowed_zooms)    ? $_POST['zoom']     : '0.67';
$kiosk    = isset($_POST['kiosk'])    && $_POST['kiosk'] === '1'                         ? true               : false;
$url      = isset($_POST['url'])      ? filter_var(trim($_POST['url']), FILTER_SANITIZE_URL) : 'http://localhost/clients/ktouch/gpints/front/kwedia/';
$taskname = isset($_POST['taskname']) ? htmlspecialchars(trim($_POST['taskname']), ENT_XML1) : 'KTouch Front Panel';

if (!filter_var($url, FILTER_VALIDATE_URL)) {
    http_response_code(400);
    exit('Invalid URL provided.');
}

// ── Browser executable paths ─────────────────────────────────────────────────
$browser_paths = [
    'chrome'  => 'C:\\Program Files\\Google\\Chrome\\Application\\chrome.exe',
    'edge'    => 'C:\\Program Files (x86)\\Microsoft\\Edge\\Application\\msedge.exe',
    'firefox' => 'C:\\Program Files\\Mozilla Firefox\\firefox.exe',
];

$exe = $browser_paths[$browser];

// ── Build arguments string ───────────────────────────────────────────────────
$args_parts = [];

if ($browser === 'chrome') {
    if ($kiosk) {
        $args_parts[] = '--kiosk';
    }
    $args_parts[] = '--force-device-scale-factor=' . $zoom;
    $args_parts[] = '"' . $url . '"';

} elseif ($browser === 'edge') {
    if ($kiosk) {
        $args_parts[] = '--kiosk';
        $args_parts[] = '"' . $url . '"';
        $args_parts[] = '--edge-kiosk-type=fullscreen';
    } else {
        $args_parts[] = '--force-device-scale-factor=' . $zoom;
        $args_parts[] = '"' . $url . '"';
    }

} elseif ($browser === 'firefox') {
    if ($kiosk) {
        $args_parts[] = '-kiosk';
    }
    $args_parts[] = '"' . $url . '"';
    // Note: Firefox does not support --force-device-scale-factor
}

$arguments  = implode(' ', $args_parts);
$zoom_pct   = round((float)$zoom * 100) . '%';
$kiosk_desc = $kiosk ? 'kiosk mode' : 'normal mode';
$now        = date('Y-m-d\TH:i:s');

// ── XML – arguments must be XML-escaped ──────────────────────────────────────
$exe_xml  = htmlspecialchars($exe,       ENT_XML1, 'UTF-8');
$args_xml = htmlspecialchars($arguments, ENT_XML1, 'UTF-8');
$name_xml = htmlspecialchars($taskname,  ENT_XML1, 'UTF-8');

$xml = <<<XML
<?xml version="1.0" encoding="UTF-16"?>
<Task version="1.2" xmlns="http://schemas.microsoft.com/windows/2004/02/mit/task">
  <RegistrationInfo>
    <Date>{$now}</Date>
    <Author>KTouch POS</Author>
    <Description>Opens the KTouch front panel ({$zoom_pct} zoom, {$kiosk_desc}).</Description>
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
    <StartWhenAvailable>false</StartWhenAvailable>
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
      <Command>{$exe_xml}</Command>
      <Arguments>{$args_xml}</Arguments>
    </Exec>
  </Actions>
</Task>
XML;

// Windows Task Scheduler requires UTF-16 LE with BOM
$xml_utf16 = "\xFF\xFE" . mb_convert_encoding($xml, 'UTF-16LE', 'UTF-8');

$filename = 'KTouch_FrontPanel_Kiosk.xml';

header('Content-Type: application/octet-stream');
header('Content-Disposition: attachment; filename="' . $filename . '"');
header('Content-Length: ' . strlen($xml_utf16));
header('Cache-Control: no-cache, must-revalidate');
header('Pragma: no-cache');

echo $xml_utf16;
exit;
