<?php  
$filename = "../../inc/gen_funcs.php";
if (file_exists($filename)){
    require_once('../../inc/gen_funcs.php');
}elseif (file_exists("../inc/gen_funcs.php")){
    require_once('../inc/gen_funcs.php');
}else{} 
?>
