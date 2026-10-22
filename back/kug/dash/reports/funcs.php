<?php
if(session_status() === PHP_SESSION_NONE) {
    session_start(); 
}
// __DIR__ = back/kug/dash/reports → gen_funcs is at uptown/inc/gen_funcs.php
$_gf = __DIR__ . '/../../../../inc/gen_funcs.php';
if (file_exists($_gf)) {
    include($_gf);
} elseif (file_exists(__DIR__ . '/../../../inc/gen_funcs.php')) {
    include(__DIR__ . '/../../../inc/gen_funcs.php');
} elseif (file_exists(__DIR__ . '/../../inc/gen_funcs.php')) {
    include(__DIR__ . '/../../inc/gen_funcs.php');
}
?>
