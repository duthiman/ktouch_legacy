<?php
session_start();
include('../../inc/connection.php');

$col_chk = mysqli_query($connection, "SHOW COLUMNS FROM kp_settings_gen LIKE 'thermal_logo_path'");
if ($col_chk && mysqli_num_rows($col_chk) === 0) {
    mysqli_query($connection, "ALTER TABLE kp_settings_gen ADD COLUMN thermal_logo_path VARCHAR(255) NOT NULL DEFAULT ''");
}

$check_gen = mysqli_query($connection, "SELECT * FROM kp_settings_gen LIMIT 1");
$pow = $check_gen ? mysqli_fetch_assoc($check_gen) : array();
if (!$pow) {
    $pow = array(
        'image_path' => '',
        'thermal_logo_path' => ''
    );
}

function logo_upload_path(string $prefix, array $file, string $fallback): string
{
    if (empty($file['name'])) {
        return $fallback;
    }
    $filename = basename((string)$file['name']);
    $tmp_name = $file['tmp_name'] ?? '';
    $ext = strtolower(pathinfo($filename, PATHINFO_EXTENSION));
    $allowed = array('jpg', 'jpeg', 'png', 'gif', 'webp');
    if ($tmp_name === '' || !in_array($ext, $allowed, true)) {
        return $fallback;
    }
    $check = @getimagesize($tmp_name);
    if ($check === false) {
        return $fallback;
    }
    if (($file['size'] ?? 0) > 2000000) {
        return $fallback;
    }
    $safe_name = preg_replace('/[^A-Za-z0-9_\-]/', '_', $prefix) . '.' . $ext;
    $target_path = "../../assets/img/logo/" . $safe_name;
    if (@move_uploaded_file($tmp_name, $target_path)) {
        return $target_path;
    }
    return $fallback;
}

$main_logo_path = logo_upload_path('main_logo', $_FILES['main_logo'] ?? array(), $pow['image_path'] ?? '');
$thermal_logo_path = logo_upload_path('thermal_logo', $_FILES['thermal_logo'] ?? array(), $pow['thermal_logo_path'] ?? '');

if ($check_gen && mysqli_num_rows($check_gen) < 1) {
    $insert = "INSERT INTO kp_settings_gen(image_path, thermal_logo_path) VALUES ('$main_logo_path', '$thermal_logo_path')";
    $ok = mysqli_query($connection, $insert);
} else {
    $main_logo_path = mysqli_real_escape_string($connection, $main_logo_path);
    $thermal_logo_path = mysqli_real_escape_string($connection, $thermal_logo_path);
    $update = "UPDATE kp_settings_gen SET image_path='$main_logo_path', thermal_logo_path='$thermal_logo_path'";
    $ok = mysqli_query($connection, $update);
}

if (!$ok) {
?>
<div class="alert alert-danger">
  <a href="#" class="close" data-dismiss="alert">&times;</a>
  <strong>Failed.</strong> <?php echo htmlspecialchars(mysqli_error($connection)); ?>
</div>
<?php
    exit;
}
?>
<div class="alert alert-success">
  <a href="#" class="close" data-dismiss="alert">&times;</a>
  <strong>Success!</strong> Logos updated.
</div>
