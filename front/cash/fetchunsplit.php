<?php
session_start();
include('../../inc/connection.php');
require_once('../../inc/gen_funcs.php');  
$shift = get_column('kp_shift','shift',"1");
$data = array();
$query ="SELECT  * FROM mpesa_message WHERE split_status=0   ORDER BY datime DESC LIMIT 100";

$result = mysqli_query($connection, $query);  
$no=0;

 while($pow = mysqli_fetch_array($result)){
     $msg_id= $pow['id']; 
     $msg= $pow['message'];     
     $counter= $pow['counter'];  
     $button="<button href='#modal' class='btn btn-sm btn-outline-info pull-left' data-toggle='modal' id='split' data-id='". $msg_id."'>SPLIT MESSAGE</Button>"; 
   $data[]= array($msg,$button); 
 }
 echo "{".'"'."aaData".'"'.": ";
    echo json_encode($data,JSON_UNESCAPED_SLASHES);
 echo  "  }";
 
?>
 