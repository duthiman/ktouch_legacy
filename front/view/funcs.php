<?php
if (!defined('KP_GEN_FUNCS_LOADED')) {
    $filename = "../../inc/gen_funcs.php";
    if (file_exists($filename)) {
        include_once('../../inc/gen_funcs.php');
    } elseif (file_exists("../inc/gen_funcs.php")) {
        include_once('../inc/gen_funcs.php');
    }
}

if (!function_exists('cash_ensure_merge_tables')) {
    $cashFuncCandidates = array(
        dirname(__DIR__) . DIRECTORY_SEPARATOR . 'cash' . DIRECTORY_SEPARATOR . 'funcs.php',
        dirname(__DIR__, 2) . DIRECTORY_SEPARATOR . 'cash' . DIRECTORY_SEPARATOR . 'funcs.php',
        dirname(__DIR__) . DIRECTORY_SEPARATOR . '..' . DIRECTORY_SEPARATOR . 'cash' . DIRECTORY_SEPARATOR . 'funcs.php'
    );

    foreach ($cashFuncCandidates as $cashFuncFile) {
        $resolved = realpath($cashFuncFile);
        if ($resolved !== false && is_file($resolved)) {
            include_once($resolved);
            if (function_exists('cash_ensure_merge_tables')) {
                break;
            }
        }
    }
}
?>
