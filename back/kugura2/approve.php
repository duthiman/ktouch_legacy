<?php
$po_id = (int)($_GET['po_id'] ?? 0);
$query = $po_id > 0 ? ('?po_id=' . $po_id) : '';
header('Location:../purchase/approve.php' . $query);
exit;
