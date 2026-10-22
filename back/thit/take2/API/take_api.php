<?php
session_start();
require_once '../../../../inc/connection.php';
require_once '../../../../inc/gen_funcs.php';
require_once __DIR__.'/take2_helpers.php';
require_once __DIR__.'/take2_schema.php';
require_once __DIR__.'/take2_repository.php';
require_once __DIR__.'/take2_service.php';
try {
    take2_schema_ensure();
    $action=take2_post('action','list');
    take2_json(true,'OK',take2_service($action,$_POST));
} catch(Throwable $e) { take2_json(false,$e->getMessage()); }
