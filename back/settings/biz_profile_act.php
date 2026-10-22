<?php
include("../../inc/connection.php");
include("../clude/funcs.php");

// ── Validate input ────────────────────────────────────────────────────────────
$profile_id = isset($_POST['profile_id']) ? (int)$_POST['profile_id'] : 0;

if ($profile_id <= 0) {
    echo '<div class="alert alert-danger"><button type="button" class="close" data-dismiss="alert">&times;</button>Invalid profile selected.</div>';
    exit;
}

// Confirm the profile exists and is active
$check = mysqli_prepare($connection,
    "SELECT profile_id, profile_name FROM kp_business_profiles WHERE profile_id = ? AND is_active = 1 LIMIT 1");
mysqli_stmt_bind_param($check, 'i', $profile_id);
mysqli_stmt_execute($check);
mysqli_stmt_bind_result($check, $valid_id, $profile_name);
$found = mysqli_stmt_fetch($check);
mysqli_stmt_close($check);

if (!$found) {
    echo '<div class="alert alert-danger"><button type="button" class="close" data-dismiss="alert">&times;</button>Profile not found.</div>';
    exit;
}

// ── Update all users to the selected profile ─────────────────────────────────
$upd = mysqli_prepare($connection,
    "UPDATE kp_users SET profile_id = ?");
mysqli_stmt_bind_param($upd, 'i', $profile_id);
$ok = mysqli_stmt_execute($upd);
mysqli_stmt_close($upd);

if (!$ok) {
    echo '<div class="alert alert-danger"><button type="button" class="close" data-dismiss="alert">&times;</button>Failed to update profile. Please try again.</div>';
    exit;
}

// ── Clear all cached profiles from active sessions ────────────────────────────
// This forces a fresh DB load on the next page hit for any logged-in user.
// Works for single-server deployments using PHP file-based sessions.
if (session_status() === PHP_SESSION_ACTIVE) {
    unset($_SESSION['biz_profile']);
}

// Attempt to clear other sessions on disk so no one keeps a stale cache
$session_path = session_save_path();
if ($session_path && is_dir($session_path)) {
    foreach (glob($session_path . '/sess_*') as $sess_file) {
        // Read, strip biz_profile key, rewrite
        $content = file_get_contents($sess_file);
        if ($content !== false && strpos($content, 'biz_profile') !== false) {
            // Safest: just delete the key by rewriting the session data
            // On next hit the session is intact but biz_profile is absent — triggers reload
            $content = preg_replace('/biz_profile\|[^;]+;/', '', $content);
            file_put_contents($sess_file, $content);
        }
    }
}

// ── Success ───────────────────────────────────────────────────────────────────
$safe_name = htmlspecialchars($profile_name);
echo '<div class="alert alert-success"><button type="button" class="close" data-dismiss="alert">&times;</button>'
   . '<strong>Profile updated!</strong> System is now running as: <strong>' . $safe_name . '</strong>. '
   . 'All users will load the new settings on their next page.</div>';
