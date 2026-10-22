<?php
session_start();
include('../../inc/connection.php');
$user_id=$_SESSION['user_id'];

function ensure_pay_mode_default_columns($connection) {
    $default_columns = [
        'is_default_cash',
        'is_default_mpesa',
        'is_default_card',
        'is_default_complimentary'
    ];
    foreach ($default_columns as $column) {
        $c = mysqli_query($connection, "SHOW COLUMNS FROM kp_pay_mode LIKE '$column'");
        if ($c && mysqli_num_rows($c) === 0) {
            mysqli_query($connection, "ALTER TABLE kp_pay_mode ADD COLUMN $column INT NOT NULL DEFAULT 2");
        }
    }
}

ensure_pay_mode_default_columns($connection);
$name             = strtoupper(mysqli_real_escape_string($connection,$_REQUEST['name']));
$bank             = $_REQUEST['bank'];
$id               = $_REQUEST['id'];
$color            = $_REQUEST['color'];
$has_ref          = $_REQUEST['has_ref2'];
$has_change       = $_REQUEST['has_change2'];
$kpesa_profile_id = !empty($_REQUEST['kpesa_profile_id']) ? (int)$_REQUEST['kpesa_profile_id'] : null;
$kpesa_col        = $kpesa_profile_id !== null ? $kpesa_profile_id : 'NULL';
$default_cash     = (int)($_REQUEST['default_cash'] ?? 2);
$default_mpesa    = (int)($_REQUEST['default_mpesa'] ?? 2);
$default_card     = (int)($_REQUEST['default_card'] ?? 2);
$default_comp     = (int)($_REQUEST['default_complimentary'] ?? 2);

if (empty($name)) {
?>
<script type="text/javascript">
	var hulla = new hullabaloo();
    hulla.send("Name Cannot be empty !!!!", "danger");
</script>
<?php
}elseif (empty($color)) {
?>
<script type="text/javascript">
	var hulla = new hullabaloo();
    hulla.send("Color Cannot be Empty!!!!", "danger");
</script>
<?php
}elseif ($bank==0) {
?>
<script type="text/javascript">
	var hulla = new hullabaloo();
    hulla.send("Bank have to be Selected!!!!", "danger");
</script>
<?php
}else{

$update_settings = "UPDATE kp_pay_mode SET
    name='$name', bank_id='$bank', btn_color='$color',
    has_ref='$has_ref', has_change='$has_change',
    kpesa_profile_id=$kpesa_col,
    is_default_cash='$default_cash',
    is_default_mpesa='$default_mpesa',
    is_default_card='$default_card',
    is_default_complimentary='$default_comp'
    WHERE pay_id='$id'";
$update_settings_r = mysqli_query($connection,$update_settings) or die("Error:!!".mysqli_error($connection));

if (!$update_settings_r) {
?>
<script type="text/javascript">
	var hulla = new hullabaloo();
    hulla.send("Payment Mode not Edited!!!!", "danger");
</script>
<?php
 }else{
    if ($default_cash === 1) {
        mysqli_query($connection, "UPDATE kp_pay_mode SET is_default_cash=2 WHERE pay_id!='$id'");
    }
    if ($default_mpesa === 1) {
        mysqli_query($connection, "UPDATE kp_pay_mode SET is_default_mpesa=2 WHERE pay_id!='$id'");
    }
    if ($default_card === 1) {
        mysqli_query($connection, "UPDATE kp_pay_mode SET is_default_card=2 WHERE pay_id!='$id'");
    }
    if ($default_comp === 1) {
        mysqli_query($connection, "UPDATE kp_pay_mode SET is_default_complimentary=2 WHERE pay_id!='$id'");
    }
	?>
<script type="text/javascript">
	var hulla = new hullabaloo();
    hulla.send("Payment Mode edited Successfully!!!!", "success");
</script>
<?php
}
 }
?>
