<?php
if(session_status() === PHP_SESSION_NONE) {
    session_start(); 
}  
if (file_exists("../inc/gen_funcs.php")){
    include('../inc/gen_funcs.php');
}elseif (file_exists("../../inc/gen_funcs.php")){
    include('../../inc/gen_funcs.php');
}elseif (file_exists("../../../inc/gen_funcs.php")){
    include('../../../inc/gen_funcs.php');
}elseif (file_exists("../../../../inc/gen_funcs.php")){
    include('../../../../inc/gen_funcs.php');
}else{
    
}

?>