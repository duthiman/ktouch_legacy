<?php
require_once dirname(__DIR__) . '/tonya/api/tonya_boot.php';
$result = tonya_service_front_login($_POST['password'] ?? '');
if ($result['ok'] && $result['html']) {
    echo $result['html'];
} else {
    echo $result['html'];
}
