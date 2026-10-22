<?php

if (session_status() !== PHP_SESSION_ACTIVE) {
    session_start();
}

if (!headers_sent()) {
    header('Content-Type: application/json; charset=UTF-8');
}

require_once dirname(__DIR__, 3) . '/inc/connection.php';
require_once dirname(__DIR__, 3) . '/inc/gen_funcs.php';
require_once __DIR__ . '/dash_helper.php';
require_once __DIR__ . '/dash_repo.php';
require_once __DIR__ . '/dash_service.php';

