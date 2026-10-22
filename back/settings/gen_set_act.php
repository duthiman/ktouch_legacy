<?php
session_start();
include('../../inc/connection.php'); 
$name = mysqli_real_escape_string($connection,$_POST['name']);
$tag = mysqli_real_escape_string($connection,$_POST['tag']);
$phone = mysqli_real_escape_string($connection,$_POST['phone']);
$phone2 = mysqli_real_escape_string($connection,$_POST['phone2']);
$address = mysqli_real_escape_string($connection,$_POST['address']);
$email = mysqli_real_escape_string($connection,$_POST['email']); 
$tax_type = mysqli_real_escape_string($connection,$_POST['tax-type']); 
$vat = mysqli_real_escape_string($connection,$_POST['vat']); 
$mpesa_no = mysqli_real_escape_string($connection,$_POST['mpesa-no']);
$mpesa_account = mysqli_real_escape_string($connection,$_POST['mpesa-account']); 
$kra_pin = strtoupper(mysqli_real_escape_string($connection,$_POST['kra-pin'])); 
$mpesa_select = isset($_POST['mpesa-select']) ? (int) $_POST['mpesa-select'] : 1;
$mpesa_source = strtoupper($_POST['mpesa-source'] ?? 'MAIN') === 'COUNTER' ? 'COUNTER' : 'MAIN';
$counterTypes = $_POST['counter_mpesa_type'] ?? [];
$counterShortcodes = $_POST['counter_mpesa_shortcode'] ?? [];
$counterAccounts = $_POST['counter_mpesa_account'] ?? [];
$counter_close_rule = isset($_POST['counter-close-rule']) ? (int) $_POST['counter-close-rule'] : 1;
if ($mpesa_select !== 2) {
    $mpesa_select = 1;
}
if ($counter_close_rule !== 2) {
    $counter_close_rule = 1;
}
$counterCols = ['mpesa_type' => "VARCHAR(20) NOT NULL DEFAULT 'TILL'", 'mpesa_shortcode' => "VARCHAR(50) NOT NULL DEFAULT ''", 'mpesa_account' => "VARCHAR(100) NOT NULL DEFAULT ''"];
foreach ($counterCols as $counterCol => $counterDef) { $counterCheck = mysqli_query($connection, "SHOW COLUMNS FROM kp_counters LIKE '$counterCol'"); if ($counterCheck && mysqli_num_rows($counterCheck) === 0) mysqli_query($connection, "ALTER TABLE kp_counters ADD COLUMN $counterCol $counterDef"); }
foreach ($counterTypes as $counterId => $counterType) { $counterId=(int)$counterId; if ($counterId<1) continue; $type=strtoupper($counterType)==='PAYBILL'?'PAYBILL':'TILL'; $short=mysqli_real_escape_string($connection, trim($counterShortcodes[$counterId] ?? '')); $account=mysqli_real_escape_string($connection, trim($counterAccounts[$counterId] ?? '')); mysqli_query($connection, "UPDATE kp_counters SET mpesa_type='$type', mpesa_shortcode='$short', mpesa_account='$account' WHERE cntr_id='$counterId'"); }


$close_rule_col = mysqli_query($connection, "SHOW COLUMNS FROM kp_settings_print LIKE 'counter_close_rule'");
 $source_col = mysqli_query($connection, "SHOW COLUMNS FROM kp_settings_print LIKE 'mpesa_source'");
 if ($source_col && mysqli_num_rows($source_col) === 0) { mysqli_query($connection, "ALTER TABLE kp_settings_print ADD COLUMN mpesa_source VARCHAR(20) NOT NULL DEFAULT 'MAIN'"); }
if ($close_rule_col && mysqli_num_rows($close_rule_col) === 0) {
    mysqli_query($connection, "ALTER TABLE kp_settings_print ADD COLUMN counter_close_rule TINYINT(1) NOT NULL DEFAULT 1");
}
$check_gen =mysqli_query($connection,"SELECT * FROM kp_settings_gen");
$pow=mysqli_fetch_assoc($check_gen);
 // $file_name = $_FILES["photos"]["name"];
$filename = isset($_FILES['photo']['name']) ? basename($_FILES['photo']['name']) : '';
$tmp_name = $_FILES['photo']['tmp_name'] ?? '';
$ext = strtolower(pathinfo($filename,PATHINFO_EXTENSION));
$imagename = $filename !== '' ? ($name.".".$ext) : '';
$target_path = $pow['image_path'] ?? '';
$uploadOk = 1;
// Check if image file is a actual image or fake image
if($filename !== '') {
    $check = getimagesize($_FILES['photo']['tmp_name']);
    if($check !== false) {
        $uploadOk = 1;
        if ($_FILES['photo']['size'] > 500000) {
            $uploadOk = 0;
        } else {
            if($ext != "jpg" && $ext != "png" && $ext != "jpeg" && $ext != "gif" ) {
                $uploadOk = 0;
            }
        }
    } else {
        $uploadOk = 0;
    }
}
 
// Check if $uploadOk is set to 0 by an error
if ($uploadOk == 0) {
    $target_path=$pow['image_path'];
// if everything is ok, try to upload file
} else {
    if ($filename !== '' && move_uploaded_file($tmp_name,$target_path)) {
     
    } elseif ($filename !== '') {
        $target_path=$pow['image_path'];
    }
}

$print_chk = mysqli_query($connection, "SELECT * FROM kp_settings_print WHERE print_doc=1 LIMIT 1");
if ($print_chk && mysqli_num_rows($print_chk) > 0) {
    mysqli_query($connection, "UPDATE kp_settings_print SET mpesa='$mpesa_select', mpesa_source='$mpesa_source', counter_close_rule='$counter_close_rule' WHERE print_doc=1");
} else {
    mysqli_query($connection, "INSERT INTO kp_settings_print (print_doc, mpesa, mpesa_source, counter_close_rule) VALUES (1, '$mpesa_select', '$mpesa_source', '$counter_close_rule')");
}

if (mysqli_num_rows($check_gen)<1) {
	$set_print= "INSERT INTO kp_settings_gen(company_name,company_tag,address,phone1,phone2,email,kra_pin,vat,tax_type,mpesa_no, mpesa_account,image_path) 
				 VALUES ('$name','$tag','$address','$phone','$phone2','$email','$kra_pin','$vat','$tax_type','$mpesa_no','$mpesa_account','$target_path')";
	$set_print_r = mysqli_query($connection,$set_print) or die(mysqli_error($connection)); 

	if (!$set_print_r) {
?>
<div class="alert alert-danger">
	<a href="#" class="close" data-dismiss="alert">&times;</a>
	<strong>Failed</strong> Failed to insert details.
</div>
<?php
}else{
	?>
<div class="alert alert-success">
	<a href="#" class="close" data-dismiss="alert">&times;</a>
	<strong>Success!</strong> Successfully Inserted.
</div>
<?php
}

}else{

	$update_print= "UPDATE kp_settings_gen SET company_name='$name', company_tag='$tag', address='$address', phone1='$phone', phone2='$phone2', email='$email',kra_pin='$kra_pin', mpesa_no='$mpesa_no', mpesa_account='$mpesa_account',image_path='$target_path', vat='$vat',tax_type='$tax_type'";
$update_print_r = mysqli_query($connection,$update_print) or die(mysqli_error($connection));	

if (!$update_print_r) {
?>
<div class="alert alert-danger">
	<a href="#" class="close" data-dismiss="alert">&times;</a>
	<strong>Failed</strong> Failed to update.
</div>
<?php
}else{
	?>
<div class="alert alert-success">
	<a href="#" class="close" data-dismiss="alert">&times;</a>
	<strong>Success!</strong> Successfully Updated.
</div>
<?php
} 
}
	 
  

 
?>
