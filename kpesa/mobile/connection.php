<?php

/**
 * Created by PhpStorm.
 * User: kapkei
 * Date: 23/02/19
 * Time: 15:08
 */
$server = 'localhost';
$username = 'root';
$password = 'mysql';

$connection = new mysqli($server, $username, $password, 'kpesa1');
if ($connection->connect_error) {
    die('error ' . $connection->connect_error);
}