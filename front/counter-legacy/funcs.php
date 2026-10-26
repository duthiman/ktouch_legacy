<?php  
$filename = "../../inc/gen_funcs.php";
if (file_exists($filename)){
    include_once('../../inc/gen_funcs.php');
}elseif (file_exists("../inc/gen_funcs.php")){
    include_once('../inc/gen_funcs.php');
}else{}

$frontFuncsPath = realpath(__DIR__ . '/../funcs.php');
if ($frontFuncsPath && is_file($frontFuncsPath)) {
    include_once($frontFuncsPath);
}

function open_counters(){
    global $connection;
    $get_counter = mysqli_query($connection,"SELECT * FROM kp_counters WHERE counter_status=1") or die("Could not get the open counters".mysqli_error($connection));
    $open=mysqli_num_rows($get_counter);
    return $open;
}
?>
