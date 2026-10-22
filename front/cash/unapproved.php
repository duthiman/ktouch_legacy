<?php
session_start();
include('../../inc/connection.php');
require_once('../../inc/gen_funcs.php');  
$data = array();
$con=$connection;
$query = "SELECT  * FROM split_message WHERE claim_status=1 AND assign_status=2 ORDER BY concat(date,' ',str_to_date(time,'%l:%i %p')) DESC LIMIT 150";
function con_column($table,$column,$filter){    
    global $connection; 
    $sql="SELECT $column FROM $table WHERE $filter";  
    if ($result = mysqli_query($con,$sql)) {
        $rows = mysqli_fetch_assoc($result);
        $col=$rows[$column];
        return $col; 
    }else{
        return mysqli_error($con);
    }
}
$result = mysqli_query($con, $query);
$no=0; 
 while($pow = mysqli_fetch_array($result)){
    $id = $pow['id'];
    $code= $pow['code']; 
    $name= $pow['name']; 
    $amount= $pow['amount'];  
    $time= $pow['time'];
    $date= $pow['date'];
    $app_status= $pow['app_status'];
    $claim_staff= $pow['claim_staff'];
    //$counter= $pow['counter']; 

    $waiter="<b style='color:red;'>".get_column('kp_users','username',"user_id='$claim_staff'")."'s <b>";
    
        $button="<button href='#modal' class='btn btn-xs btn-lime pull-left' data-toggle='modal' id='approve' data-id='". $id."'>APPROVE</Button>";
    

   $data[]= array($button,$waiter,$code,$amount,$time,$name); 
 }
 echo "{".'"'."aaData".'"'.": ";
    echo json_encode($data,JSON_UNESCAPED_SLASHES);
 echo  "  }";
 
?>