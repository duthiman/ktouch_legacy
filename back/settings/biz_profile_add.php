<?php
include("../../inc/connection.php");
include("../clude/funcs.php");

// ── Validate ──────────────────────────────────────────────────────────────────
$name = trim($_POST['profile_name'] ?? '');
$slug = trim($_POST['profile_slug'] ?? '');
$desc = trim($_POST['profile_desc'] ?? '');

if (!$name || !$slug) {
    echo '<div class="alert alert-danger"><button type="button" class="close" data-dismiss="alert">&times;</button>Profile Name and Slug are required.</div>';
    exit;
}

// Slug: lowercase alphanumeric + underscores only
$slug = strtolower(preg_replace('/[^a-z0-9_]/i', '', str_replace(' ', '_', $slug)));
if (!$slug) {
    echo '<div class="alert alert-danger"><button type="button" class="close" data-dismiss="alert">&times;</button>Invalid slug.</div>';
    exit;
}

// Check slug uniqueness
$chk = mysqli_prepare($connection,
    "SELECT profile_id FROM kp_business_profiles WHERE profile_slug = ? LIMIT 1");
mysqli_stmt_bind_param($chk, 's', $slug);
mysqli_stmt_execute($chk);
mysqli_stmt_store_result($chk);
if (mysqli_stmt_num_rows($chk) > 0) {
    mysqli_stmt_close($chk);
    echo '<div class="alert alert-danger"><button type="button" class="close" data-dismiss="alert">&times;</button>A profile with that slug already exists. Choose a different name/slug.</div>';
    exit;
}
mysqli_stmt_close($chk);

// ── Feature values (all validated/cast) ──────────────────────────────────────
$allowed_bool = ['0', '1'];
$f = [
    'multi_counter'               => in_array($_POST['multi_counter']     ?? '', $allowed_bool) ? $_POST['multi_counter']    : '0',
    'counter_count'               => max(1, min(50, (int)($_POST['counter_count'] ?? 1))),
    'has_store'                   => in_array($_POST['has_store']          ?? '', $allowed_bool) ? $_POST['has_store']         : '0',
    'auto_issue_to_store'         => in_array($_POST['auto_issue_to_store']?? '', $allowed_bool) ? $_POST['auto_issue_to_store']: '0',
    'has_kitchen'                 => in_array($_POST['has_kitchen']        ?? '', $allowed_bool) ? $_POST['has_kitchen']       : '0',
    'has_grill'                   => in_array($_POST['has_grill']          ?? '', $allowed_bool) ? $_POST['has_grill']         : '0',
    'purchase_flow'               => in_array($_POST['purchase_flow']      ?? '', ['simple','structured']) ? $_POST['purchase_flow'] : 'simple',
    'structure_purchase_required' => in_array($_POST['structure_purchase_required'] ?? '', $allowed_bool) ? $_POST['structure_purchase_required'] : '0',
    'has_sports'                  => in_array($_POST['has_sports']         ?? '', $allowed_bool) ? $_POST['has_sports']        : '0',
];

// ── Insert profile ────────────────────────────────────────────────────────────
$ins = mysqli_prepare($connection,
    "INSERT INTO kp_business_profiles (profile_name, profile_slug, description) VALUES (?, ?, ?)");
mysqli_stmt_bind_param($ins, 'sss', $name, $slug, $desc);
if (!mysqli_stmt_execute($ins)) {
    mysqli_stmt_close($ins);
    echo '<div class="alert alert-danger"><button type="button" class="close" data-dismiss="alert">&times;</button>Failed to create profile. Please try again.</div>';
    exit;
}
$new_id = mysqli_insert_id($connection);
mysqli_stmt_close($ins);

// ── Insert features ───────────────────────────────────────────────────────────
$ins_f = mysqli_prepare($connection,
    "INSERT INTO kp_profile_features (profile_id, feature_key, feature_value) VALUES (?, ?, ?)");

foreach ($f as $key => $value) {
    $value_str = (string)$value;
    mysqli_stmt_bind_param($ins_f, 'iss', $new_id, $key, $value_str);
    mysqli_stmt_execute($ins_f);
}
mysqli_stmt_close($ins_f);

// ── Success ───────────────────────────────────────────────────────────────────
$safe_name = htmlspecialchars($name);
echo '<div class="alert alert-success"><button type="button" class="close" data-dismiss="alert">&times;</button>'
   . '<strong>Profile created!</strong> &ldquo;' . $safe_name . '&rdquo; has been added and is now available.</div>';
