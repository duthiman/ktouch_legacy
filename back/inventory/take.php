<?php
// Backward-compatible entry point. The stock-take page now lives in back/thit/take.
$query = isset($_SERVER['QUERY_STRING']) && $_SERVER['QUERY_STRING'] !== '' ? '?' . $_SERVER['QUERY_STRING'] : '';
header('Location: ../thit/take/take.php' . $query);
exit;
