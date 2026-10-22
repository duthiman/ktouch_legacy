<?php
session_start();
include('../../inc/connection.php');
include('../riko/funcs.php');

$sale_id = (int)($_GET['id'] ?? $_GET['sale_id'] ?? 0);
require __DIR__ . '/riko_ticket.php';
