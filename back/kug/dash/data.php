<?php
session_start();
require_once('../../../inc/gen_funcs.php');  
$data = array(); 
$data["supps"]   = column_count('kp_suppliers','sup_id',"1");
$data["pos"]   	= column_count('kp_po_status','po_id',"1");
$data["apppend"]   	= column_count('kp_po','po_id',"appr_status=2");
$data["recpend"]   	= column_count('kp_po','po_id',"appr_status=1 AND rec_status=2");
$data["reccomp"]   	= column_count('kp_po','po_id',"appr_status=1 AND rec_status=1"); 
echo json_encode($data);
?>
 