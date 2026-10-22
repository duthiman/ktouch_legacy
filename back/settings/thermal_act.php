<?php
session_start();
include('../../inc/connection.php');

$pin = isset($_POST['pin']) ? (int)$_POST['pin'] : 2;
$logo = isset($_POST['logo']) ? (int)$_POST['logo'] : 2;
$tax = isset($_POST['tax']) ? (int)$_POST['tax'] : 2;
$thank = mysqli_real_escape_string($connection, trim($_POST['thank'] ?? ''));
$mpesa_select = isset($_POST['mpesa-select']) ? (int)$_POST['mpesa-select'] : 1;
$print_template = isset($_POST['print_template']) ? (int)$_POST['print_template'] : 1;
$show_qr        = isset($_POST['show_qr'])        ? (int)$_POST['show_qr']        : 2;
$thermal_logo_height = isset($_POST['thermal_logo_height']) ? (int)$_POST['thermal_logo_height'] : 60;

if ($pin !== 1)           { $pin = 2; }
if ($logo !== 1)          { $logo = 2; }
if ($tax !== 1)           { $tax = 2; }
if ($mpesa_select !== 2)  { $mpesa_select = 1; }
if ($print_template !== 2){ $print_template = 1; }
if ($show_qr !== 1)       { $show_qr = 2; }
if ($thermal_logo_height < 30) { $thermal_logo_height = 30; }
if ($thermal_logo_height > 180) { $thermal_logo_height = 180; }

$template_col = mysqli_query($connection, "SHOW COLUMNS FROM kp_settings_print LIKE 'print_template'");
if ($template_col && mysqli_num_rows($template_col) === 0) {
    mysqli_query($connection, "ALTER TABLE kp_settings_print ADD COLUMN print_template TINYINT(1) NOT NULL DEFAULT 1");
}
$qr_col = mysqli_query($connection, "SHOW COLUMNS FROM kp_settings_print LIKE 'show_qr'");
if ($qr_col && mysqli_num_rows($qr_col) === 0) {
    mysqli_query($connection, "ALTER TABLE kp_settings_print ADD COLUMN show_qr TINYINT(1) NOT NULL DEFAULT 2");
}
$logo_height_col = mysqli_query($connection, "SHOW COLUMNS FROM kp_settings_print LIKE 'thermal_logo_height'");
if ($logo_height_col && mysqli_num_rows($logo_height_col) === 0) {
    mysqli_query($connection, "ALTER TABLE kp_settings_print ADD COLUMN thermal_logo_height INT NOT NULL DEFAULT 60");
}

$check_gen = mysqli_query($connection, "SELECT * FROM kp_settings_print WHERE print_doc=1 LIMIT 1");
$exists = $check_gen && mysqli_num_rows($check_gen) > 0;

if (!$exists) {
    $sql = "INSERT INTO kp_settings_print
        (print_doc, show_logo, pin, tax, mpesa, thankyou_note, print_template, show_qr, thermal_logo_height)
        VALUES
        (1, '$logo', '$pin', '$tax', '$mpesa_select', '$thank', '$print_template', '$show_qr', '$thermal_logo_height')";
    $result = mysqli_query($connection, $sql);
} else {
    $sql = "UPDATE kp_settings_print SET
        print_doc=1,
        show_logo='$logo',
        pin='$pin',
        tax='$tax',
        mpesa='$mpesa_select',
        thankyou_note='$thank',
        print_template='$print_template',
        show_qr='$show_qr',
        thermal_logo_height='$thermal_logo_height'
        WHERE print_doc=1";
    $result = mysqli_query($connection, $sql);
}

if (!$result) {
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
    <strong>Success!</strong> Thermal print settings updated.
</div>
