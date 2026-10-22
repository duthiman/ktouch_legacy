<?php
if (session_status() === PHP_SESSION_NONE) {
  session_start();
}
include('../../inc/connection.php');
require_once('../funcs.php');
header('Content-Type: application/json');

function handover_json($status, $message, $extra = []) {
  echo json_encode(array_merge([
    'status' => $status,
    'message' => $message
  ], $extra));
  exit;
}

$user_id = isset($_SESSION['user_id']) ? (int)$_SESSION['user_id'] : 0;
$counter = isset($_POST['counter']) ? (int)$_POST['counter'] : 0;
$to_user = isset($_POST['to_user']) ? (int)$_POST['to_user'] : 0;
$mode_ids = isset($_POST['mode_ids']) ? $_POST['mode_ids'] : [];
$note = isset($_POST['note']) ? trim($_POST['note']) : '';
$shift = get_column('kp_shift','shift',"1");
if ($shift == 0) {
  $shift = date('Y-m-d');
}

mysqli_query($connection,"CREATE TABLE IF NOT EXISTS kp_counter_handover (
  handover_id INT NOT NULL AUTO_INCREMENT,
  shift_date DATE NOT NULL,
  cntr_id INT NOT NULL DEFAULT 0,
  from_user INT NOT NULL DEFAULT 0,
  to_user INT NOT NULL DEFAULT 0,
  total_system DECIMAL(10,2) NOT NULL DEFAULT 0,
  total_declared DECIMAL(10,2) NOT NULL DEFAULT 0,
  total_variance DECIMAL(10,2) NOT NULL DEFAULT 0,
  status VARCHAR(20) NOT NULL DEFAULT 'MATCH',
  note TEXT NULL,
  created_at TIMESTAMP NOT NULL DEFAULT CURRENT_TIMESTAMP,
  PRIMARY KEY (handover_id)
)") or die(mysqli_error($connection));

mysqli_query($connection,"CREATE TABLE IF NOT EXISTS kp_counter_handover_modes (
  handover_mode_id INT NOT NULL AUTO_INCREMENT,
  handover_id INT NOT NULL DEFAULT 0,
  pay_id INT NOT NULL DEFAULT 0,
  system_amount DECIMAL(10,2) NOT NULL DEFAULT 0,
  declared_amount DECIMAL(10,2) NOT NULL DEFAULT 0,
  variance_amount DECIMAL(10,2) NOT NULL DEFAULT 0,
  status VARCHAR(20) NOT NULL DEFAULT 'MATCH',
  PRIMARY KEY (handover_mode_id)
)") or die(mysqli_error($connection));

if ($user_id < 1) {
  handover_json('error', 'Session expired. Please log in again.');
}
if ($counter < 1 || user_counter($user_id) != $counter) {
  handover_json('warning', 'This cashier has no active counter to hand over.');
}
if ($to_user < 1 || $to_user === $user_id) {
  handover_json('warning', 'Select a valid receiving cashier.');
}

$receiver_check = mysqli_query($connection,"SELECT user_id FROM kp_users WHERE user_id='$to_user' AND drole=1 AND counter_status=2 LIMIT 1") or die(mysqli_error($connection));
if (mysqli_num_rows($receiver_check) < 1) {
  handover_json('warning', 'Selected cashier is not available for handover.');
}

$system_total = 0;
$declared_total = 0;
$variance_total = 0;
$rows = [];
foreach ($mode_ids as $pay_id) {
  $pay_id = (int)$pay_id;
  if ($pay_id < 1) {
    continue;
  }
  $expected = isset($_POST['expected_'.$pay_id]) ? (float)$_POST['expected_'.$pay_id] : 0;
  $declared = isset($_POST['declared_'.$pay_id]) ? (float)$_POST['declared_'.$pay_id] : 0;
  $variance = $declared - $expected;
  $status = abs($variance) < 0.009 ? 'MATCH' : 'VARIANCE';
  $system_total += $expected;
  $declared_total += $declared;
  $variance_total += $variance;
  $rows[] = [
    'pay_id' => $pay_id,
    'system_amount' => $expected,
    'declared_amount' => $declared,
    'variance_amount' => $variance,
    'status' => $status
  ];
}

$overall_status = abs($variance_total) < 0.009 ? 'MATCH' : 'VARIANCE';
$note_safe = mysqli_real_escape_string($connection, $note);
mysqli_query($connection,"INSERT INTO kp_counter_handover(shift_date,cntr_id,from_user,to_user,total_system,total_declared,total_variance,status,note) VALUES('$shift','$counter','$user_id','$to_user','$system_total','$declared_total','$variance_total','$overall_status','$note_safe')") or die(mysqli_error($connection));
$handover_id = (int)mysqli_insert_id($connection);

foreach ($rows as $row) {
  mysqli_query($connection,"INSERT INTO kp_counter_handover_modes(handover_id,pay_id,system_amount,declared_amount,variance_amount,status) VALUES('$handover_id','".$row['pay_id']."','".$row['system_amount']."','".$row['declared_amount']."','".$row['variance_amount']."','".$row['status']."')") or die(mysqli_error($connection));
}

mysqli_query($connection,"UPDATE kp_users SET job=2,counter_status=2,counter=0 WHERE user_id='$user_id'") or die(mysqli_error($connection));
mysqli_query($connection,"UPDATE kp_users SET job=1,counter_status=1,counter='$counter' WHERE user_id='$to_user'") or die(mysqli_error($connection));
mysqli_query($connection,"UPDATE kp_counters SET user_id='$to_user',take_status=3,counter_status=1 WHERE cntr_id='$counter'") or die(mysqli_error($connection));

$action = "COUNTER HANDOVER|$shift|COUNTER ".get_counter($counter)."|FROM ".get_user($user_id)." TO ".get_user($to_user)."|STATUS $overall_status";
logs($action,'../../logs');

handover_json('success', 'Handover saved with '.$overall_status.' and counter moved to '.get_user($to_user).'.', [
  'redirect' => '../log_out.php'
]);
