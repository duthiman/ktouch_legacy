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

// Migration: add kpesa_profile_id if missing
$c = mysqli_query($connection, "SHOW COLUMNS FROM kp_pay_mode LIKE 'kpesa_profile_id'");
if ($c && mysqli_num_rows($c) === 0) {
    mysqli_query($connection, "ALTER TABLE kp_pay_mode ADD COLUMN kpesa_profile_id INT NULL DEFAULT NULL");
}
ensure_pay_mode_default_columns($connection);

$name             = strtoupper(mysqli_real_escape_string($connection,$_REQUEST['name']));
$bank             = 1;
$color            = $_REQUEST['color'];
$has_ref          = $_REQUEST['has_ref'];
$has_change       = $_REQUEST['has_change'];
$has_gate         = $_REQUEST['has_gate'];
$gate_col         = mysqli_real_escape_string($connection, $_REQUEST['gate_col']);
$kpesa_profile_id = !empty($_REQUEST['kpesa_profile_id']) ? (int)$_REQUEST['kpesa_profile_id'] : null;
$kpesa_col        = $kpesa_profile_id !== null ? $kpesa_profile_id : 'NULL';
$default_cash     = (int)($_REQUEST['default_cash'] ?? 2);
$default_mpesa    = (int)($_REQUEST['default_mpesa'] ?? 2);
$default_card     = (int)($_REQUEST['default_card'] ?? 2);
$default_comp     = (int)($_REQUEST['default_complimentary'] ?? 2);

if (empty($name) OR empty($color) OR $bank==0) {
?>
<script type="text/javascript">
	var hulla = new hullabaloo();
    hulla.send("Make Sure You fill All fields !!!!", "danger");
</script>
<?php
}else{

$update_settings = "INSERT INTO kp_pay_mode(name,bank_id,btn_color,has_ref,has_change,created_by,gate,tabl,kpesa_profile_id,is_default_cash,is_default_mpesa,is_default_card,is_default_complimentary)
    VALUES('$name','$bank','$color','$has_ref','$has_change','$user_id','$has_gate','$gate_col',$kpesa_col,'$default_cash','$default_mpesa','$default_card','$default_comp')";
$update_settings_r = mysqli_query($connection,$update_settings) or die("Error:!!".mysqli_error($connection));

if (!$update_settings_r) {
?>
<script type="text/javascript">
	var hulla = new hullabaloo();
    hulla.send("Payment Mode not Created!!!!", "danger");
</script>
<?php
 }else{
	$id = mysqli_insert_id($connection);
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
 	$column_qty="ALTER TABLE kp_sale_payment ADD `mode".$id."` DECIMAL(10,2) NOT NULL;";
	$update_items_r = mysqli_query($connection,$column_qty) or die(mysqli_error($connection));
	if ($has_ref==1) {
		$column_qty="ALTER TABLE kp_sale_payment ADD `moderef".$id."` VARCHAR(22) NOT NULL;";
		$update_items_r = mysqli_query($connection,$column_qty) or die(mysqli_error($connection));
	}
	?>
<script type="text/javascript">
	var hulla = new hullabaloo();
    hulla.send("Payment Mode Created Successfully!!!!", "success");
</script>
<?php
}
 }
?>
