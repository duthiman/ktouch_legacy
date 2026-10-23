<?php
// Select the dashboard layout before rendering any loading page.
$user_agent = (string)($_SERVER['HTTP_USER_AGENT'] ?? '');
$is_mobile = (bool)preg_match('/Android|webOS|iPhone|iPad|iPod|BlackBerry|IEMobile|Opera Mini/i', $user_agent);
$layout = $is_mobile ? 'mobile.php' : 'desktop.php';
$query = !empty($_SERVER['QUERY_STRING']) ? '?' . $_SERVER['QUERY_STRING'] : '';
header('Location: ' . $layout . $query, true, 302);
exit;
