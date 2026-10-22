<?php
session_start();
$trans_id = (int)($_SESSION['trans'] ?? 0);
if ($trans_id < 1) {
    exit('Invalid transfer');
}
header('Location: render_trans.php?trans_id=' . $trans_id);
exit;
 