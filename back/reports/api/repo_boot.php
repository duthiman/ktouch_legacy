<?php

if (session_status() !== PHP_SESSION_ACTIVE) {
    session_start();
}

if (!headers_sent()) {
    header('Content-Type: application/json; charset=UTF-8');
}

require_once '../../../inc/connection.php';
require_once '../../../inc/gen_funcs.php';
require_once '../funcs.php';
require_once __DIR__ . '/repo_helper.php';
require_once __DIR__ . '/repo_logger.php';
require_once __DIR__ . '/repo_repository.php';
require_once __DIR__ . '/repo_service.php';
