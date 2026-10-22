<?php
session_start();
include('../../../inc/connection.php');

$user_id = isset($_SESSION['user_id']) ? (int)$_SESSION['user_id'] : 0;
$shift_date = isset($_POST['shift_date']) ? $_POST['shift_date'] : date('Y-m-d');
$title = isset($_POST['title']) ? trim($_POST['title']) : '';
$amount = isset($_POST['amount']) ? (float)$_POST['amount'] : 0;
$note = isset($_POST['note']) ? trim($_POST['note']) : '';

mysqli_query($connection,"CREATE TABLE IF NOT EXISTS kp_shop_expenses (
  exp_id INT NOT NULL AUTO_INCREMENT,
  shift_date DATE NOT NULL,
  title VARCHAR(120) NOT NULL,
  amount DECIMAL(10,2) NOT NULL DEFAULT 0,
  note TEXT NULL,
  user_id INT NOT NULL DEFAULT 0,
  created_at TIMESTAMP NOT NULL DEFAULT CURRENT_TIMESTAMP,
  PRIMARY KEY (exp_id)
)") or die(mysqli_error($connection));

if ($user_id < 1) {
  echo '<div class="alert alert-danger">Session expired. Please log in again.</div>';
  exit;
}
if ($title === '' || $amount <= 0) {
  echo '<div class="alert alert-warning">Enter both expense title and a valid amount.</div>';
  exit;
}

$title = mysqli_real_escape_string($connection, $title);
$note = mysqli_real_escape_string($connection, $note);
$shift_date = mysqli_real_escape_string($connection, $shift_date);

$save = mysqli_query($connection,"INSERT INTO kp_shop_expenses(shift_date,title,amount,note,user_id) VALUES('$shift_date','$title','$amount','$note','$user_id')") or die(mysqli_error($connection));

if ($save) {
  echo '<div class="alert alert-success expense-saved-ok">Expense saved successfully.</div>';
} else {
  echo '<div class="alert alert-danger">Could not save expense.</div>';
}
