<?php
date_default_timezone_set("Africa/Nairobi");

$filename = "../../inc/gen_funcs.php";
if (file_exists($filename)){
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
function is_front_po($refno){
    return strpos((string)$refno, 'FRONT BUY FOR ') === 0;
}
function front_po_counter_id($po_id){
    global $connection;
    $po = mysqli_query($connection,"SELECT req_id,refno FROM kp_po WHERE po_id='$po_id'") or die(mysqli_error($connection));
    $row = mysqli_fetch_assoc($po);
    if (!$row) {
        return 0;
    }
    if (!empty($row['req_id']) && (int)$row['req_id'] > 0) {
        return (int)$row['req_id'];
    }
    $refno = (string)$row['refno'];
    if (!is_front_po($refno)) {
        return 0;
    }
    $counter_name = trim(str_replace('FRONT BUY FOR ', '', $refno));
    if ($counter_name === '') {
        return 0;
    }
    return (int)get_column('kp_counters','cntr_id',"name='$counter_name'");
}
function front_po_counter_name($po_id){
    $counter = front_po_counter_id($po_id);
    if ($counter > 0) {
        return get_counter($counter);
    }
    return 'UNKNOWN';
}
