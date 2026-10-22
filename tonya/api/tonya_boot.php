<?php
if (session_status() === PHP_SESSION_NONE) {
    session_start();
}
require_once dirname(__DIR__, 2) . '/inc/connection.php';
require_once dirname(__DIR__, 2) . '/inc/gen_funcs.php';
require_once dirname(__DIR__, 2) . '/front/funcs.php';
require_once __DIR__ . '/tonya_repo.php';
require_once __DIR__ . '/tonya_service.php';
