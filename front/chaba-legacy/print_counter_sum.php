<?php
session_start();
include('../../inc/connection.php');
include_once('../../inc/gen_funcs.php');
include_once('../counter/funcs.php');
include_once('../open/funcs.php');
include_once('funcs.php');

if (!isset($_SESSION['user_id']) || (int)$_SESSION['user_id'] < 1) {
    header('Location:../log_out.php');
    exit;
}

$user_id = (int)$_SESSION['user_id'];
$shift = trim((string)get_column('kp_shift', 'shift', "1"));
$counter = (int)user_counter($user_id);
if ($counter < 1) {
    $counter = (int)get_column('kp_users', 'counter', "user_id='$user_id'");
}

if ($counter < 1 || $shift === '') {
 //   header('Location:../dash/dash.php');
    exit;
}

$summary_id = (int)($_GET['summary_id'] ?? 0);
if ($summary_id < 1) {
    $summary_id = (int)get_column(
        'kp_counter_close_summary',
        'close_sum_id',
        "cntr_id='$counter' AND shift_date='" . mysqli_real_escape_string($connection, $shift) . "' ORDER BY close_sum_id DESC"
    );
}

if ($summary_id < 1) {
 //   header('Location:../dash/dash.php');
    exit;
}

$return_to = '../log_out.php';
$print_mode = (int)get_print_mode();
$use_queue = function_exists('kp_print_mode_uses_queue')
    ? kp_print_mode_uses_queue($print_mode)
    : in_array($print_mode, [2, 3, 4], true);

if ($use_queue) {
    $queueUrl = 'receipt_counter.php'
        . '?type=counter_sum'
        . '&id=' . urlencode((string)$summary_id)
        . '&summary_id=' . urlencode((string)$summary_id)
        . '&cntr_id=' . urlencode((string)$counter)
        . '&shift=' . urlencode((string)$shift)
        . '&return_to=' . urlencode($return_to);
    header('Location:' . $queueUrl);
    exit;
}

$directUrl = 'render_counter_sum.php'
    . '?summary_id=' . urlencode((string)$summary_id)
    . '&cntr_id=' . urlencode((string)$counter)
    . '&shift=' . urlencode((string)$shift)
    . '&direct_print=1'
    . '&return_to=' . urlencode($return_to);
header('Location:' . $directUrl);
exit;
?>
