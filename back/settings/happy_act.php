<?php
session_start();
include('../../inc/connection.php');

$user_id = $_SESSION['user_id'];
$day = mysqli_real_escape_string($connection,$_REQUEST['day']);
$start_t = mysqli_real_escape_string($connection,$_REQUEST['start_time']);   
$end_t = mysqli_real_escape_string($connection,$_REQUEST['end_time']);
 
$start_time  = date("H:i", strtotime($start_t));  
$end_time  = date("H:i", strtotime($end_t));  

$set_hhr= "INSERT INTO kp_hhr_set(day,start_time,end_time,set_by) VALUES ('$day','$start_time','$end_time','$user_id')";
$set_hhr_r = mysqli_query($connection,$set_hhr);

 
if (!$set_hhr_r) {
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
 
?>