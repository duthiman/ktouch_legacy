<?php
declare(strict_types=1);

// Single public entry point for shift actions.
$action = trim((string)($_GET['action'] ?? 'shift.status'));
if ($action === '') $action = 'shift.status';
$_GET['action'] = $action;
require __DIR__ . '/api/shift_api.php';
