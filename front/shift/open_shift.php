<?php
session_start();
include('../../inc/connection.php');
include('funcs.php');
$user_id=$_SESSION['user_id'];

function shift_open_query($connection, $sql, $message) {
	if (!mysqli_query($connection, $sql)) {
		error_log('Shift opening failed: ' . $message . ' | ' . mysqli_error($connection));
		echo '<p>' . htmlspecialchars($message, ENT_QUOTES, 'UTF-8') . '</p>';
		exit;
	}
}

// Heal the two shift registry tables before reading or writing them.
shift_open_query($connection, "CREATE TABLE IF NOT EXISTS kp_shift (
	shift_id INT NOT NULL AUTO_INCREMENT,
	shift DATE NOT NULL,
	PRIMARY KEY (shift_id)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4", 'Could not prepare the shift table');
shift_open_query($connection, "CREATE TABLE IF NOT EXISTS kp_day_shift (
	shift_id INT NOT NULL AUTO_INCREMENT,
	shift DATE NOT NULL,
	opening DATETIME NULL,
	closing DATETIME NULL,
	open_by INT NOT NULL DEFAULT 0,
	close_by INT NOT NULL DEFAULT 0,
	open_ord INT NOT NULL DEFAULT 0,
	close_ord INT NOT NULL DEFAULT 0,
	status TINYINT NOT NULL DEFAULT 2,
	PRIMARY KEY (shift_id),
	KEY idx_day_shift_status (status, shift)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4", 'Could not prepare the day-shift table');

$shiftColumns = [
	'shift' => "ALTER TABLE kp_shift ADD COLUMN shift DATE NOT NULL",
];
foreach ($shiftColumns as $column => $alter) {
	$columnEsc = mysqli_real_escape_string($connection, $column);
	$columnCheck = mysqli_query($connection, "SHOW COLUMNS FROM kp_shift LIKE '$columnEsc'");
	if (!$columnCheck) {
		shift_open_query($connection, $alter, 'Could not heal the shift table');
	} elseif (mysqli_num_rows($columnCheck) < 1) {
		shift_open_query($connection, $alter, 'Could not heal the shift table');
	}
}
$dayColumns = [
	'shift' => "ALTER TABLE kp_day_shift ADD COLUMN shift DATE NOT NULL",
	'opening' => "ALTER TABLE kp_day_shift ADD COLUMN opening DATETIME NULL",
	'closing' => "ALTER TABLE kp_day_shift ADD COLUMN closing DATETIME NULL",
	'open_by' => "ALTER TABLE kp_day_shift ADD COLUMN open_by INT NOT NULL DEFAULT 0",
	'open_ord' => "ALTER TABLE kp_day_shift ADD COLUMN open_ord INT NOT NULL DEFAULT 0",
	'status' => "ALTER TABLE kp_day_shift ADD COLUMN status TINYINT NOT NULL DEFAULT 2",
];
foreach ($dayColumns as $column => $alter) {
	$columnEsc = mysqli_real_escape_string($connection, $column);
	$columnCheck = mysqli_query($connection, "SHOW COLUMNS FROM kp_day_shift LIKE '$columnEsc'");
	if (!$columnCheck) {
		shift_open_query($connection, $alter, 'Could not heal the day-shift table');
	} elseif (mysqli_num_rows($columnCheck) < 1) {
		shift_open_query($connection, $alter, 'Could not heal the day-shift table');
	}
}

// kp_shift is the active-shift marker. Do not insert another row while it
// contains a shift; this also protects against double-clicks and retries.
$activeShift = mysqli_query($connection, "SELECT shift FROM kp_shift ORDER BY shift DESC LIMIT 1");
if (!$activeShift) {
	shift_open_query($connection, 'SELECT 1', 'Could not check the current shift');
}
if (mysqli_num_rows($activeShift) > 0) {
	$row = mysqli_fetch_assoc($activeShift);
	echo '<p>A shift is already open for ' . htmlspecialchars((string)($row['shift'] ?? ''), ENT_QUOTES, 'UTF-8') . '.</p>';
	exit;
}
$activeDay = mysqli_query($connection, "SELECT shift FROM kp_day_shift WHERE status=2 AND closing IS NULL ORDER BY shift DESC LIMIT 1");
if ($activeDay && mysqli_num_rows($activeDay) > 0) {
	$row = mysqli_fetch_assoc($activeDay);
	echo '<p>A day shift is already open for ' . htmlspecialchars((string)($row['shift'] ?? ''), ENT_QUOTES, 'UTF-8') . '.</p>';
	exit;
}

$place = "INSERT INTO kp_shift(shift) VALUES(CURRENT_DATE)";
shift_open_query($connection, $place, 'Could not open shift');

$get_order = "SELECT sale_id FROM kp_sale_id";
$get_order_r = mysqli_query($connection,$get_order);
if (!$get_order_r) {
	shift_open_query($connection, 'SELECT 1', 'Could not get the last order numbers');
}
$row = mysqli_fetch_array($get_order_r);
$last_id = (int)($row['sale_id'] ?? 0);

$shift= mysqli_query($connection,"INSERT INTO kp_day_shift(shift,opening,open_by,open_ord,status) VALUES(CURRENT_DATE,CURRENT_TIMESTAMP,'$user_id','$last_id',2) ");
if ($shift) {
	$update_waiter= mysqli_query($connection,"UPDATE kp_users SET job=2,counter_status=2");
	if ($update_waiter) {
		$update_counter= mysqli_query($connection,"UPDATE kp_counters SET take_status=2,counter_status=2");
		if ($update_counter) {
			$action="OPENED|-|-|SHIFT|".date('Y-m-d');
			logs($action,'../logs');
			?>
			<script type="text/javascript"> 
				window.setTimeout(function(){
					window.location.href = '../log_out.php';
				}, 100);
			</script>
			<?php 
		}else{
			echo "COULD NOT UPDATE WAITERS DETAILS";
		}
	}else{
	echo "COULD NOT UPDATE WAITERS DETAILS";
}
}else{
	echo "COULD NOT ADD DAY SHIFT DETAILS";
}
?>
