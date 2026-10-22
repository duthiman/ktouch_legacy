<?php  
$filename = "../../inc/gen_funcs.php";
if (file_exists($filename)){
    include_once('../../inc/gen_funcs.php');
}elseif (file_exists("../inc/gen_funcs.php")){
    include_once('../inc/gen_funcs.php');
}else{}  
?>
