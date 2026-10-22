<?php  
$filename = "../../inc/gen_funcs.php";
if (file_exists($filename)){
    include('../../inc/gen_funcs.php');
}elseif (file_exists("../inc/gen_funcs.php")){
    include('../inc/gen_funcs.php');
}else{} 

// function user_counter_status($user_id){
//     global $connection;
//     $get_user_counter = mysqli_query($connection,"SELECT counter FROM kp_users WHERE user_id='$user_id'") or die("Could not get counter".mysqli_error($connection));
//     $gs = mysqli_fetch_assoc($get_user_counter);
//     $counter=$gs['counter'];
    
//     $get_counter_status = mysqli_query($connection,"SELECT take_status FROM kp_counters WHERE cntr_id='$counter'") or die("Could not get counter counter status".mysqli_error($connection));
//     $st = mysqli_fetch_assoc($get_counter_status);
//     $status=$st['take_status']; 
//     return $status;
// }

 
function counter_dpt($counter){
    global $connection;
    $get_counter_dpt = mysqli_query($connection,"SELECT dpt FROM kp_counters WHERE cntr_id='$counter'") or die("Could not get counter".mysqli_error($connection));
    $dp = mysqli_fetch_assoc($get_counter_dpt);
    $dpt=$dp['dpt'];
    
    return $dpt;
}
?>