<?php
session_start();
include('../../inc/connection.php');
include('funcs.php');

$user_id=(int)$_SESSION['user_id'];
$shift = get_column('kp_shift','shift',"1");

// Recover the active shift if kp_shift was cleared while kp_day_shift is
// still open. This keeps counter opening usable after an interrupted close.
if (empty($shift)) {
	$active_shift_q = mysqli_query($connection, "SELECT shift FROM kp_day_shift WHERE status=2 ORDER BY opening DESC LIMIT 1");
	if ($active_shift_q && ($active_shift_row = mysqli_fetch_assoc($active_shift_q))) {
		$shift = trim((string)($active_shift_row['shift'] ?? ''));
		if ($shift !== '') {
			$shift_sql = mysqli_real_escape_string($connection, $shift);
			mysqli_query($connection, "INSERT INTO kp_shift(shift) VALUES('$shift_sql')");
		}
	}
}

$cash_id=(int)($_REQUEST['cash_id'] ?? 0);
$cntr=(int)($_REQUEST['cntr'] ?? 0); 
$column="cntr".$cntr."_take";

if ($cash_id < 1 || $cntr < 1) {
	echo "INVALID COUNTER OR CASHIER";
	exit;
}
if (empty($shift)) {
	echo "NO OPEN SHIFT. OPEN THE SHIFT FIRST.";
	exit;
}

// Do not allow another counter to open while the previous shift is still
// pending closure. This is a shift-level lock, not just a selected-counter
// check.
$closing_shift_q = mysqli_query($connection, "SELECT cntr_id FROM kp_counters WHERE del_status=2 AND close_status=3 LIMIT 1");
if ($closing_shift_q && mysqli_num_rows($closing_shift_q) > 0) {
	echo "WAIT FOR SHIFT TO BE CLOSED";
	exit;
}

// Opening is decided per selected counter. Other counters may remain open;
// this counter is eligible only when its own counter_status is CLOSED (2).
$selected_counter_q = mysqli_query(
	$connection,
	"SELECT cntr_id, name, counter_status
	 FROM kp_counters
	 WHERE cntr_id='$cntr' AND del_status=2
	 LIMIT 1"
);
$selected_counter = $selected_counter_q ? mysqli_fetch_assoc($selected_counter_q) : null;
if (!$selected_counter) {
	echo 'COUNTER NOT FOUND OR INACTIVE';
	exit;
}
if ((int)($selected_counter['counter_status'] ?? 0) !== 2) {
	$selected_name = trim((string)($selected_counter['name'] ?? '')) ?: ('Counter ' . $cntr);
	echo 'CLOSE ' . htmlspecialchars($selected_name, ENT_QUOTES, 'UTF-8') . ' BEFORE OPENING IT AGAIN';
	exit;
}

$get_order = "SELECT sale_id FROM kp_sale_id";
$get_order_r = mysqli_query($connection,$get_order) or die("Could not get the last order numbers");
$row = mysqli_fetch_array($get_order_r);
$last_id = $row['sale_id'];

mysqli_begin_transaction($connection);

$reset_counter_users = mysqli_query($connection,"UPDATE kp_users SET counter_status=2,counter=0 WHERE counter='$cntr' AND user_id!='$cash_id'");
$reset_cashier_assignment = mysqli_query($connection,"UPDATE kp_users SET counter=0,counter_status=2 WHERE user_id='$cash_id' AND counter!='$cntr'");
$update_cashier= mysqli_query($connection,"UPDATE kp_users SET job=1,shift_status=1,counter_status=1,counter='$cntr',cleared=2 WHERE user_id='$cash_id'");
if ($update_cashier) {
	$update_take= mysqli_query($connection,"UPDATE kp_items SET ".$column."=2");
	if ($update_take) {
		$update_counter= mysqli_query($connection,"UPDATE kp_counters SET user_id='$cash_id',take_status=3,counter_status=1,close_status=3 WHERE cntr_id='$cntr'") or die(mysqli_error($connection));
		if ($update_counter) {
			$counter_history= mysqli_query($connection,"INSERT INTO kp_counter_hist(cntr_id,shift,opening,open_by,open_ord,status) VALUES('$cntr',CURRENT_DATE,CURRENT_TIMESTAMP,'$user_id','$last_id',2) ") or die(mysqli_error($connection));
			if ($counter_history) {
				mysqli_commit($connection);
				$action="OPENED ".get_counter($cntr)." COUNTER |-|-|SHIFT|".date('Y-m-d');
				logs($action,'../logs');
				?>
				<script type="text/javascript"> 
					window.setTimeout(function(){
						window.location.href = '../log_out.php';
					}, 100);
						 
				</script>
				<?php
			}else{
				mysqli_rollback($connection);
				echo "COULD NOT UPDATE COUNTER HISTORY";
			}

		}else{
			mysqli_rollback($connection);
			echo "COULD NOT UPDATE COUNTER";
		}
	}else{
		mysqli_rollback($connection);
		echo "COULD NOT UPDATE TAKE";
	}
}else{
	mysqli_rollback($connection);
	echo "COULD NOT UPDATE CASHIER DETAILS";
}



 
