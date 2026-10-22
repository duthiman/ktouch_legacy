<?php
date_default_timezone_set("Africa/Nairobi");
if (file_exists("../../../inc/gen_funcs.php")){
    include('../../../inc/gen_funcs.php');
}elseif (file_exists("../../inc/gen_funcs.php")){
    include('../../inc/gen_funcs.php');
}elseif (file_exists("../inc/gen_funcs.php")){
    include('../inc/gen_funcs.php');
}else{
    
}
function po_total($po_id){
    global $connection;
      
    $total_po = mysqli_query($connection,"SELECT SUM(total) AS gtotal FROM kp_po_items WHERE po_id='$po_id'") or die("Error");
    $row = mysqli_fetch_assoc($total_po);
    $total = $row['gtotal'];
     
    return $total;

}