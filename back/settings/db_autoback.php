<?php
/**
 * db_autoback.php — Auto-backup entry point for Windows Task Scheduler.
 *
 * Call via:  php "C:\Program Files\Ampps\www\clients\ktouch\gpints\back\settings\db_autoback.php"
 * Or via HTTP with a secret key:
 *   http://localhost/clients/ktouch/gpints/back/settings/db_autoback.php?key=AUTOBACK_SECRET
 *
 * Task Scheduler setup (once):
 *   Program : C:\Program Files\Ampps\php8\php.exe
 *   Arguments: "C:\Program Files\Ampps\www\clients\ktouch\gpints\back\settings\db_autoback.php"
 *   Trigger  : Daily at desired time (e.g. 01:00)
 */

// ---------- Secret key guard when called over HTTP ----------
if (PHP_SAPI !== 'cli') {
    $expected_key = 'AUTOBACK_SECRET';   // <-- change this to something private
    $provided_key = $_GET['key'] ?? '';
    if (!hash_equals($expected_key, $provided_key)) {
        http_response_code(403);
        exit('Forbidden');
    }
}

// Fake minimal session so do_backup() can log user_id = 0
if (PHP_SAPI === 'cli') {
    $_SESSION = [];
    $_SERVER['REMOTE_ADDR'] = '127.0.0.1';
}

require_once __DIR__ . '/../../inc/connection.php';

// ---------- Inline do_backup (mirrors dbback.php logic) ----------
define('BACKUP_DIR',    realpath(__DIR__ . '/../../assets/back/db') . DIRECTORY_SEPARATOR);
define('MYSQLDUMP_BIN', 'C:/Program Files/Ampps/mysql/bin/mysqldump.exe');
define('MAX_SLOTS',     5);

function do_autobackup() {
    global $connection, $dbhost, $dbuser, $dbpass, $dbname;

    if (!is_dir(BACKUP_DIR)) {
        mkdir(BACKUP_DIR, 0755, true);
    }

    $cnt_res = mysqli_query($connection, "SELECT COUNT(*) AS c FROM kp_backdb");
    $cnt     = (int) mysqli_fetch_assoc($cnt_res)['c'];
    $now_dt  = date('Y-m-d H:i:s');

    if ($cnt < MAX_SLOTS) {
        $slot      = $cnt + 1;
        $filename  = 'backup_slot_' . $slot . '.sql';
        $overwrite = false;
    } else {
        $old_res   = mysqli_query($connection,
            "SELECT back_id, backupfilename FROM kp_backdb ORDER BY backupfiledate ASC LIMIT 1");
        $old_row   = mysqli_fetch_assoc($old_res);
        $slot      = $old_row['back_id'];
        $filename  = $old_row['backupfilename'];
        $overwrite = true;
        if (file_exists(BACKUP_DIR . $filename)) {
            @unlink(BACKUP_DIR . $filename);
        }
    }

    $host = $dbhost ?? 'localhost';
    $user = $dbuser ?? 'root';
    $pass = $dbpass ?? 'mysql';
    $db   = $dbname ?? 'ktouch_gpints';

    $optfile = tempnam(sys_get_temp_dir(), 'autoback_');
    file_put_contents($optfile,
        "[mysqldump]\nuser={$user}\npassword={$pass}\nhost={$host}\n");

    $filepath = BACKUP_DIR . $filename;
    $cmd      = '"' . MYSQLDUMP_BIN . '"'
              . ' --defaults-extra-file="' . $optfile . '"'
              . ' --single-transaction --routines --triggers'
              . ' ' . escapeshellarg($db)
              . ' > "' . $filepath . '" 2>&1';

    $output  = null;
    $retcode = null;
    exec($cmd, $output, $retcode);
    @unlink($optfile);

    if ($retcode !== 0 || !file_exists($filepath) || filesize($filepath) < 100) {
        $err = implode(' ', (array) $output);
        error_log("[AutoBack] mysqldump failed: {$err}");
        return false;
    }

    $ip       = mysqli_real_escape_string($connection, $_SERVER['REMOTE_ADDR'] ?? '127.0.0.1');
    $filename = mysqli_real_escape_string($connection, $filename);
    $now_dt   = mysqli_real_escape_string($connection, $now_dt);
    $uid      = 0;

    if ($overwrite) {
        mysqli_query($connection,
            "UPDATE kp_backdb
             SET backupfilename='{$filename}', backupfiledate='{$now_dt}',
                 username='{$uid}', ipaddress='{$ip}'
             WHERE back_id={$slot}");
    } else {
        mysqli_query($connection,
            "INSERT INTO kp_backdb (backupfilename, backupfiledate, username, ipaddress)
             VALUES ('{$filename}', '{$now_dt}', '{$uid}', '{$ip}')");
    }

    // Safety prune
    mysqli_query($connection,
        "DELETE FROM kp_backdb
         WHERE back_id NOT IN (
             SELECT back_id FROM (
                 SELECT back_id FROM kp_backdb ORDER BY backupfiledate DESC LIMIT " . MAX_SLOTS . "
             ) t
         )");

    error_log("[AutoBack] Backup saved: {$filename}  at {$now_dt}");
    return true;
}

$ok = do_autobackup();

if (PHP_SAPI === 'cli') {
    echo $ok ? "Backup completed successfully.\n" : "Backup FAILED — check error_log.\n";
} else {
    header('Content-Type: text/plain');
    echo $ok ? 'OK' : 'ERROR';
}
