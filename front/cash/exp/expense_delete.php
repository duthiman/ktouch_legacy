<?php
session_start();
include(dirname(__DIR__, 3) . '/inc/connection.php');
include(dirname(__DIR__) . '/funcs.php');

$user_id = isset($_SESSION['user_id']) ? (int)$_SESSION['user_id'] : 0;
$exp_id = isset($_POST['id']) ? (int)$_POST['id'] : 0;
$pin = isset($_POST['pass']) ? trim($_POST['pass']) : '';
$reason = isset($_POST['reason']) ? trim($_POST['reason']) : '';
$shift = get_column('kp_shift','shift',"1");
if ($shift == 0) {
  $shift = date('Y-m-d');
}

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

mysqli_query($connection,"CREATE TABLE IF NOT EXISTS kp_shop_expense_delete (
  del_id INT NOT NULL AUTO_INCREMENT,
  exp_id INT NOT NULL DEFAULT 0,
  shift_date DATE NOT NULL,
  title VARCHAR(120) NOT NULL,
  amount DECIMAL(10,2) NOT NULL DEFAULT 0,
  note TEXT NULL,
  created_by INT NOT NULL DEFAULT 0,
  deleted_by INT NOT NULL DEFAULT 0,
  appr_by INT NOT NULL DEFAULT 0,
  reason TEXT NULL,
  deleted_at TIMESTAMP NOT NULL DEFAULT CURRENT_TIMESTAMP,
  delete_shift DATE NULL,
  PRIMARY KEY (del_id)
)") or die(mysqli_error($connection));

$reason_col = mysqli_query($connection, "SHOW COLUMNS FROM kp_shop_expense_delete LIKE 'reason'") or die(mysqli_error($connection));
if (mysqli_num_rows($reason_col) < 1) {
  mysqli_query($connection, "ALTER TABLE kp_shop_expense_delete ADD COLUMN reason TEXT NULL AFTER appr_by") or die(mysqli_error($connection));
}

if ($user_id < 1) {
  echo '<div class="alert alert-danger">Session expired. Please log in again.</div>';
  exit;
}
if ($exp_id < 1) {
  echo '<div class="alert alert-warning">Select a valid expense to delete.</div>';
  exit;
}
if ($pin === '') {
  echo '<div class="alert alert-warning">Enter supervisor password to continue.</div>';
  exit;
}
if ($reason === '') {
  echo '<div class="alert alert-warning">Enter a reason for deleting this expense.</div>';
  exit;
}

$pin_safe = mysqli_real_escape_string($connection, $pin);
$reason_safe = mysqli_real_escape_string($connection, $reason);
$approval_q = mysqli_query($connection,"SELECT user_id,username,role_id,drole FROM kp_users WHERE pin='$pin_safe' LIMIT 1") or die(mysqli_error($connection));
if (mysqli_num_rows($approval_q) < 1) {
  echo '<div class="alert alert-danger">Wrong supervisor password. Trials are documented.</div>';
  exit;
}
$approval = mysqli_fetch_assoc($approval_q);
$appr_by = (int)$approval['user_id'];
$approver_role = isset($approval['role_id']) ? (int)$approval['role_id'] : 0;

if ($approver_role === 3) {
  echo '<div class="alert alert-danger">Only supervisor or admin approval can delete expenses.</div>';
  exit;
}

$expense_q = mysqli_query($connection,"SELECT * FROM kp_shop_expenses WHERE exp_id='$exp_id' LIMIT 1") or die(mysqli_error($connection));
if (mysqli_num_rows($expense_q) < 1) {
  echo '<div class="alert alert-warning">That expense was already removed or is missing.</div>';
  exit;
}
$expense = mysqli_fetch_assoc($expense_q);

$title = mysqli_real_escape_string($connection, $expense['title']);
$note = mysqli_real_escape_string($connection, (string)$expense['note']);
$expense_shift = mysqli_real_escape_string($connection, $expense['shift_date']);
$amount = (float)$expense['amount'];
$created_by = (int)$expense['user_id'];

$track = mysqli_query($connection,"INSERT INTO kp_shop_expense_delete(exp_id,shift_date,title,amount,note,created_by,deleted_by,appr_by,reason,delete_shift) VALUES('$exp_id','$expense_shift','$title','$amount','$note','$created_by','$user_id','$appr_by','$reason_safe','$shift')") or die(mysqli_error($connection));

if ($track) {
  mysqli_query($connection,"DELETE FROM kp_shop_expenses WHERE exp_id='$exp_id' LIMIT 1") or die(mysqli_error($connection));
  $action = "DELETED SHOP EXPENSE|$exp_id|".$expense['title']."|".$expense['amount']."|REASON ".$reason."|APPROVED BY ".$approval['username'];
  logs($action,"../../");
  echo '<div class="alert alert-success expense-delete-ok">Expense deleted with supervisor approval.</div>';
} else {
  echo '<div class="alert alert-danger">Could not track the deletion.</div>';
}
