<?php
session_start();
include('../../inc/connection.php'); 

$query = "SELECT  * FROM kp_split_message WHERE claim_status=2 ORDER BY concat(date,' ',str_to_date(time,'%l:%i %p')) DESC LIMIT 700";

$result = mysqli_query($connection, $query);
 $no=0; 
 while($pow = mysqli_fetch_array($result)){
    $id = $pow['id'];
    $code= $pow['code']; 
    $name= $pow['name']; 
    $amount= $pow['amount'];  
    $time= $pow['time'];
    $date= $pow['date']; 

    $return_arr[] = array("id" => $id,
                    "name" => $name,
                    "code" => $code,
                    "amount" => $amount,
                    "time" => $time,
                    "date" => $date);
}
  $response = array();
  $response['messages'] = $return_arr;
  $response["status"] = 1; 
  $response["message"] = "Unclaimed Messages"; 

   echo json_encode($response); 
?>
 