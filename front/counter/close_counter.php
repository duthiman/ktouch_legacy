<?php
session_start();
include_once('../../inc/connection.php');
include_once('funcs.php');
include_once('../../back/counter/funcs.php');
include_once('../chaba/funcs.php');
$is_ajax_close = isset($_POST['ajax']) && (int)$_POST['ajax'] === 1;

if (!function_exists('close_counter_alert_redirect')) {
	function close_counter_alert_redirect($message, $path = '../dash/dash.php') {
		global $is_ajax_close;
		$message = trim((string)$message);
		$path = trim((string)$path);
		if ($path === '') {
			$path = '../dash/dash.php';
		}
		if ($is_ajax_close) {
			header('Content-Type: application/json');
			echo json_encode([
				'ok' => false,
				'message' => $message,
				'redirect' => $path
			]);
			exit;
		}
		?>
		<script type="text/javascript">
			<?php if ($message !== '') { ?>
			alert(<?php echo json_encode($message); ?>);
			<?php } ?>
			window.location.href = <?php echo json_encode($path); ?>;
		</script>
		<?php
		exit;
	}
}

if (!function_exists('close_counter_success_redirect')) {
	function close_counter_success_redirect($message, $path) {
		global $is_ajax_close;
		$message = trim((string)$message);
		$path = trim((string)$path);
		if ($path === '') {
			$path = '../log_out.php';
		}
		if ($is_ajax_close) {
			header('Content-Type: application/json');
			echo json_encode([
				'ok' => true,
				'message' => $message,
				'redirect' => $path
			]);
			exit;
		}
		?>
		<script type="text/javascript">
			<?php if ($message !== '') { ?>
			alert(<?php echo json_encode($message); ?>);
			<?php } ?>
			window.location.href = <?php echo json_encode($path); ?>;
		</script>
		<?php
		exit;
	}
}

if (!function_exists('close_counter_safe_query')) {
	function close_counter_safe_query($connection, $sql, $context = '') {
		try {
			$result = mysqli_query($connection, $sql);
			if ($result === false) {
				error_log('Close counter query failed' . ($context !== '' ? ' [' . $context . ']' : '') . ': ' . mysqli_error($connection) . ' | SQL: ' . $sql);
			}
			return $result;
		} catch (Throwable $e) {
			error_log('Close counter query failed' . ($context !== '' ? ' [' . $context . ']' : '') . ': ' . $e->getMessage() . ' | SQL: ' . $sql);
			return false;
		}
	}
}

if (!function_exists('close_counter_clear_shift_table')) {
	function close_counter_clear_shift_table($connection) {
		$truncate = close_counter_safe_query($connection, 'TRUNCATE TABLE kp_shift', 'truncate shift table');
		if ($truncate !== false) {
			return true;
		}

		// TRUNCATE can be rejected by foreign-key constraints. DELETE is slower,
		// but allows the final shift close to complete when row deletion is valid.
		$delete = close_counter_safe_query($connection, 'DELETE FROM kp_shift', 'fallback delete shift table');
		if ($delete === false) {
			return false;
		}

		$remaining = close_counter_safe_query($connection, 'SELECT COUNT(*) AS total FROM kp_shift', 'verify shift table cleared');
		if ($remaining === false) {
			return false;
		}
		$row = mysqli_fetch_assoc($remaining);
		if ((int)($row['total'] ?? 0) !== 0) {
			error_log('Close counter shift table is not empty after fallback delete. Remaining rows: ' . (int)$row['total']);
			return false;
		}
		return true;
	}
}

if (!function_exists('close_counter_column_exists')) {
	function close_counter_column_exists($connection, $table, $column) {
		$table = mysqli_real_escape_string($connection, $table);
		$column = mysqli_real_escape_string($connection, $column);
		$query = close_counter_safe_query($connection, "SHOW COLUMNS FROM `$table` LIKE '$column'", 'show column ' . $table . '.' . $column);
		return $query && mysqli_num_rows($query) > 0;
	}
}

if (!function_exists('ensure_counter_close_tables')) {
	function ensure_counter_close_tables(){
		global $connection;
		close_counter_safe_query($connection,"CREATE TABLE IF NOT EXISTS kp_counter_close_summary (
			close_sum_id INT NOT NULL AUTO_INCREMENT,
			shift_date DATE NOT NULL,
			cntr_id INT NOT NULL DEFAULT 0,
			user_id INT NOT NULL DEFAULT 0,
			open_ord INT NOT NULL DEFAULT 0,
			close_ord INT NOT NULL DEFAULT 0,
			open_orders INT NOT NULL DEFAULT 0,
			pending_returns INT NOT NULL DEFAULT 0,
			pending_transfers INT NOT NULL DEFAULT 0,
			total_bills INT NOT NULL DEFAULT 0,
			total_sales DECIMAL(10,2) NOT NULL DEFAULT 0,
			total_item_sales DECIMAL(10,2) NOT NULL DEFAULT 0,
			bar_sales DECIMAL(10,2) NOT NULL DEFAULT 0,
			kitchen_sales DECIMAL(10,2) NOT NULL DEFAULT 0,
			expense_total DECIMAL(10,2) NOT NULL DEFAULT 0,
			day_credit_total DECIMAL(10,2) NOT NULL DEFAULT 0,
			credit_paid_today_total DECIMAL(10,2) NOT NULL DEFAULT 0,
			credit_paid_previous_total DECIMAL(10,2) NOT NULL DEFAULT 0,
			same_day_payments_total DECIMAL(10,2) NOT NULL DEFAULT 0,
			previous_day_payments_total DECIMAL(10,2) NOT NULL DEFAULT 0,
			payments_total DECIMAL(10,2) NOT NULL DEFAULT 0,
			today_requests_total INT NOT NULL DEFAULT 0,
			today_returns_accepted_total INT NOT NULL DEFAULT 0,
			today_returns_rejected_total INT NOT NULL DEFAULT 0,
			today_voided_items_total INT NOT NULL DEFAULT 0,
			today_items_sold_total DECIMAL(12,2) NOT NULL DEFAULT 0,
			today_transfer_to_total INT NOT NULL DEFAULT 0,
			today_transfer_from_total INT NOT NULL DEFAULT 0,
			created_at TIMESTAMP NOT NULL DEFAULT CURRENT_TIMESTAMP,
			PRIMARY KEY (close_sum_id),
			KEY idx_counter_shift (cntr_id, shift_date)
		)", 'create kp_counter_close_summary');
		$extraColumns = [
			'today_requests_total' => "ALTER TABLE kp_counter_close_summary ADD COLUMN today_requests_total INT NOT NULL DEFAULT 0 AFTER payments_total",
			'today_returns_accepted_total' => "ALTER TABLE kp_counter_close_summary ADD COLUMN today_returns_accepted_total INT NOT NULL DEFAULT 0 AFTER today_requests_total",
			'today_returns_rejected_total' => "ALTER TABLE kp_counter_close_summary ADD COLUMN today_returns_rejected_total INT NOT NULL DEFAULT 0 AFTER today_returns_accepted_total",
			'today_voided_items_total' => "ALTER TABLE kp_counter_close_summary ADD COLUMN today_voided_items_total INT NOT NULL DEFAULT 0 AFTER today_returns_rejected_total",
			'today_items_sold_total' => "ALTER TABLE kp_counter_close_summary ADD COLUMN today_items_sold_total DECIMAL(12,2) NOT NULL DEFAULT 0 AFTER today_voided_items_total",
			'today_transfer_to_total' => "ALTER TABLE kp_counter_close_summary ADD COLUMN today_transfer_to_total INT NOT NULL DEFAULT 0 AFTER today_items_sold_total",
			'today_transfer_from_total' => "ALTER TABLE kp_counter_close_summary ADD COLUMN today_transfer_from_total INT NOT NULL DEFAULT 0 AFTER today_transfer_to_total",
		];
		foreach ($extraColumns as $column => $sql) {
			if (!close_counter_column_exists($connection, 'kp_counter_close_summary', $column)) {
				close_counter_safe_query($connection, $sql, 'alter kp_counter_close_summary ' . $column);
			}
		}

		close_counter_safe_query($connection,"CREATE TABLE IF NOT EXISTS kp_counter_close_mode_totals (
			close_mode_id INT NOT NULL AUTO_INCREMENT,
			close_sum_id INT NOT NULL DEFAULT 0,
			pay_id INT NOT NULL DEFAULT 0,
			same_day_amount DECIMAL(10,2) NOT NULL DEFAULT 0,
			previous_day_amount DECIMAL(10,2) NOT NULL DEFAULT 0,
			total_amount DECIMAL(10,2) NOT NULL DEFAULT 0,
			created_at TIMESTAMP NOT NULL DEFAULT CURRENT_TIMESTAMP,
			PRIMARY KEY (close_mode_id),
			KEY idx_close_sum_id (close_sum_id)
		)", 'create kp_counter_close_mode_totals');
	}
}

/*
 * Closing a counter touches several counter-specific tables and one dynamic
 * stock column on kp_items.  Older installations may not have these yet,
 * so repair them before the first close query is executed.
 */
if (!function_exists('ensure_counter_close_dependencies')) {
	function ensure_counter_close_dependencies($connection, $counter) {
		$counter = (int)$counter;
		if ($counter < 1) {
			return false;
		}

		$stock_column = 'cntr' . $counter . '_qty';
		$reorder_column = 'cntr' . $counter . '_reorder';
		$columns = [
			$stock_column => "DECIMAL(12,2) NOT NULL DEFAULT 0",
			$reorder_column => "DECIMAL(12,2) NOT NULL DEFAULT 0"
		];
		foreach ($columns as $column => $definition) {
			if (!close_counter_column_exists($connection, 'kp_items', $column)) {
				close_counter_safe_query(
					$connection,
					"ALTER TABLE kp_items ADD COLUMN `$column` $definition",
					'add kp_items.' . $column
				);
			}
		}

		$ok = true;
		if (function_exists('ensure_counter_stock_take_table')) {
			$ok = ensure_counter_stock_take_table($connection, $counter) && $ok;
		}
		if (function_exists('ensure_counter_close_stock_take_tables')) {
			ensure_counter_close_stock_take_tables($connection);
		}
		ensure_counter_close_tables();

		$take_table = 'kp_cntr' . $counter . '_stock_take';
		$closing_table = 'kp_cntr' . $counter . '_closing_stock';
		$take_exists = close_counter_safe_query($connection, "SHOW TABLES LIKE '$take_table'", 'verify stock take table');
		$closing_exists = close_counter_safe_query($connection, "SHOW TABLES LIKE '$closing_table'", 'verify closing stock table');
		return $ok && $take_exists && mysqli_num_rows($take_exists) > 0
			&& $closing_exists && mysqli_num_rows($closing_exists) > 0
			&& close_counter_column_exists($connection, 'kp_items', $stock_column);
	}
}

if (!function_exists('store_counter_close_totals')) {
	function store_counter_close_totals($shift,$counter,$user_id,$last_order,$open_orders,$pending_returns,$pending_transfers){
		global $connection;
		ensure_counter_close_tables();

		$open_ord = (int)get_column('kp_counter_hist','open_ord',"shift='$shift' AND cntr_id='$counter'");
		$total_bills = (int)column_count('kp_sales','sale_id',"shift='$shift' AND counter='$counter'");
		$total_sales = (float)column_total('kp_sales','amount',"shift='$shift' AND counter='$counter'");
		$total_item_sales = (float)column_total('kp_sale_items','total',"shift='$shift' AND stock_counter='$counter'");
		$bar_sales = (float)column_total('kp_sale_items','total',"dpt=1 AND shift='$shift' AND stock_counter='$counter'");
		$kitchen_sales = (float)column_total('kp_sale_items','total',"dpt=2 AND shift='$shift' AND stock_counter='$counter'");
		$expense_total = (float)column_total('kp_shop_expenses','amount',"shift_date='$shift'");
		$day_credit_total = (float)column_total('kp_sales','amount',"shift='$shift' AND counter='$counter' AND pay_status=3");
		$today_requests_total = (int)column_count('kp_counter_req','req_id',"req_shift='$shift' AND counter='$counter'");
		$today_returns_accepted_total = (int)column_count('kp_ret_items','item_id',"shift='$shift' AND stock_counter='$counter' AND confirm_status=1");
		$today_returns_rejected_total = (int)column_count('kp_ret_items','item_id',"shift='$shift' AND stock_counter='$counter' AND confirm_status=3");
		$today_voided_items_total = 0;
		if (close_counter_column_exists($connection, 'kp_void_items', 'item_id')) {
			$today_voided_items_total = (int)column_count('kp_void_items','item_id',"shift='$shift' AND stock_counter='$counter'");
		}
		$today_items_sold_total = (float)column_total('kp_sale_items','qty',"shift='$shift' AND stock_counter='$counter'");
		$today_transfer_to_total = (int)column_count('kp_counter_trans','trans_id',"shift='$shift' AND cntr_to='$counter'");
		$today_transfer_from_total = (int)column_count('kp_counter_trans','trans_id',"shift='$shift' AND cntr_from='$counter'");

		$credit_paid_today_total = 0;
		$credit_paid_previous_total = 0;
		$credit_pay_q = mysqli_query($connection,"
			SELECT s.shift AS ord_shift, SUM(i.amount) AS amt
			FROM kp_customer_credit_payment_items i
			INNER JOIN kp_sales s ON i.sale_id = s.sale_id
			WHERE i.pay_date='$shift' AND s.counter='$counter'
			GROUP BY s.shift
		");
		if ($credit_pay_q) {
			while ($credit_pay = mysqli_fetch_assoc($credit_pay_q)) {
				$amt = (float)$credit_pay['amt'];
				if ($credit_pay['ord_shift'] == $shift) {
					$credit_paid_today_total += $amt;
				}else{
					$credit_paid_previous_total += $amt;
				}
			}
		}

		$same_day_payments_total = 0;
		$previous_day_payments_total = 0;
		$payment_modes = close_counter_safe_query($connection,"SELECT pay_id FROM kp_pay_mode ORDER BY pay_id ASC",'load pay modes');
		$mode_totals = array();
		while ($payment_modes && ($mode = mysqli_fetch_assoc($payment_modes))) {
			$pay_id = (int)$mode['pay_id'];
			$col = "mode".$pay_id;
			$same_day_amount = (float)column_total('kp_sale_payment',$col,"ord_date='$shift' AND pay_date='$shift' AND counter='$counter'");
			$previous_day_amount = (float)column_total('kp_sale_payment',$col,"ord_date!='$shift' AND pay_date='$shift' AND counter='$counter'");
			$total_amount = $same_day_amount + $previous_day_amount;
			$same_day_payments_total += $same_day_amount;
			$previous_day_payments_total += $previous_day_amount;
			$mode_totals[] = array(
				'pay_id' => $pay_id,
				'same_day_amount' => $same_day_amount,
				'previous_day_amount' => $previous_day_amount,
				'total_amount' => $total_amount
			);
		}
		$payments_total = $same_day_payments_total + $previous_day_payments_total;

		$insert_summary = close_counter_safe_query($connection,"INSERT INTO kp_counter_close_summary(
			shift_date,cntr_id,user_id,open_ord,close_ord,open_orders,pending_returns,pending_transfers,total_bills,total_sales,total_item_sales,bar_sales,kitchen_sales,expense_total,day_credit_total,credit_paid_today_total,credit_paid_previous_total,same_day_payments_total,previous_day_payments_total,payments_total,today_requests_total,today_returns_accepted_total,today_returns_rejected_total,today_voided_items_total,today_items_sold_total,today_transfer_to_total,today_transfer_from_total
		) VALUES(
			'$shift','$counter','$user_id','$open_ord','$last_order','$open_orders','$pending_returns','$pending_transfers','$total_bills','$total_sales','$total_item_sales','$bar_sales','$kitchen_sales','$expense_total','$day_credit_total','$credit_paid_today_total','$credit_paid_previous_total','$same_day_payments_total','$previous_day_payments_total','$payments_total','$today_requests_total','$today_returns_accepted_total','$today_returns_rejected_total','$today_voided_items_total','$today_items_sold_total','$today_transfer_to_total','$today_transfer_from_total'
		)", 'insert counter close summary');
		if (!$insert_summary) {
			return 0;
		}

		$close_sum_id = mysqli_insert_id($connection);
		foreach ($mode_totals as $mode_row) {
			close_counter_safe_query($connection,"INSERT INTO kp_counter_close_mode_totals(close_sum_id,pay_id,same_day_amount,previous_day_amount,total_amount) VALUES(
				'$close_sum_id','".$mode_row['pay_id']."','".$mode_row['same_day_amount']."','".$mode_row['previous_day_amount']."','".$mode_row['total_amount']."'
			)", 'insert counter close mode totals');
		}
		return (int)$close_sum_id;
	}
}

if (!function_exists('queue_counter_close_summary')) {
	function queue_counter_close_summary($connection, $summary_id, $counter, $user_id, $shift) {
		$summary_id = (int)$summary_id;
		$counter = (int)$counter;
		$user_id = (int)$user_id;
		if ($summary_id < 1 || $counter < 1 || $user_id < 1) {
			return 0;
		}
		$routeQuery = 'summary_id=' . urlencode((string)$summary_id)
			. '&cntr_id=' . urlencode((string)$counter)
			. '&shift=' . urlencode((string)$shift);
		return (int)kp_print_enqueue($connection, [
			'sale_id' => 0,
			'ref_id' => $summary_id,
			'print_type' => 'counter_sum',
			'label' => 'COUNTER SUMMARY',
			'cntr_id' => $counter,
			'route_path' => 'render_counter_sum.php',
			'route_query' => $routeQuery,
			'requested_by' => $user_id,
		]);
	}
}

if (!function_exists('close_counter_should_use_queue')) {
	function close_counter_should_use_queue($print_mode) {
		$print_mode = (int)$print_mode;
		if (function_exists('kp_print_mode_uses_queue')) {
			return kp_print_mode_uses_queue($print_mode);
		}
		return in_array($print_mode, [2, 3, 4], true);
	}
}

if (!function_exists('redirect_counter_close_summary_print')) {
	function redirect_counter_close_summary_print($summary_id, $counter, $shift, $return_to = '../log_out.php') {
		$summary_id = (int)$summary_id;
		$counter = (int)$counter;
		if ($summary_id < 1 || $counter < 1) {
			return;
		}
		$url = '../chaba/render_counter_sum.php'
			. '?summary_id=' . urlencode((string)$summary_id)
			. '&cntr_id=' . urlencode((string)$counter)
			. '&shift=' . urlencode((string)$shift)
			. '&direct_print=1'
			. '&return_to=' . urlencode((string)$return_to);
		header('Location: ' . $url);
		exit;
	}
}

$shift = get_column('kp_shift','shift',"1");
$user_id=$_SESSION['user_id'];
$counter= user_counter($user_id);
$count = open_counters();
$require_clear_bills = function_exists('counter_close_requires_all_bills_cleared') ? counter_close_requires_all_bills_cleared() : true;
page_restrict();
$check_counter=mysqli_query($connection,"SELECT * FROM kp_counters WHERE close_status=3"); 
$uab = mysqli_num_rows($check_counter); 

$get_sale_r = mysqli_query($connection,"SELECT sale_id FROM kp_sale_id") or die("Could not get the last order numbers".mysqli_error($connection));
$sow = mysqli_fetch_assoc($get_sale_r);
$last = $sow['sale_id'];
$print_mode = (int)get_print_mode();
$use_queue_print = close_counter_should_use_queue($print_mode);

try {
if (empty($counter) OR $counter==0) {
	?>
	<script type="text/javascript">
        window.setTimeout(function(){
            window.location.href = '../log_out.php';
        }, 100);
	</script>
	<?php
}else{ 
	// Heal the counter schema before TRUNCATE/INSERT/UPDATE operations below.
	if (!ensure_counter_close_dependencies($connection, $counter)) {
		close_counter_alert_redirect('The counter closing tables could not be prepared. Check the database permissions and try again.');
	}
	$counter_open_orders = column_count('kp_sales','sale_id',"shift='$shift' AND counter='$counter' AND pay_status=2");
	$shift_open_orders = ($count==1) ? column_count('kp_sales','sale_id',"shift='$shift' AND pay_status=2") : 0;
	$pending_returns = column_count('kp_ret_items','id',"shift='$shift' AND stock_counter='$counter' AND ret_status=3");
	$pending_transfers = column_count('kp_counter_req','req_id',"counter='$counter' AND issue_status=1 AND rec_status=2");

	if ($pending_returns > 0) {
		close_counter_alert_redirect('There are pending returns on this counter. Finish or reject them before closing.');
	}

	//EMPTY COUNTER CLOSING STOCK TABLE
	$empty = "TRUNCATE TABLE kp_cntr".$counter."_closing_stock";
	$empty_r = close_counter_safe_query($connection,$empty,'truncate closing stock');
	if ($empty_r) {	
		//INSERT THE CLOSING STOCK 
		$qty_column="cntr".$counter."_qty";
		$closing_stock = close_counter_safe_query($connection,"INSERT INTO kp_cntr".$counter."_closing_stock(item_id,qty,shift) SELECT item_id,$qty_column,'$shift' as shift FROM kp_items",'insert closing stock');
		if ($closing_stock) {
			//UPDATE STOCK CLOSING IN TAKE
			$update_take = close_counter_safe_query($connection,"UPDATE kp_cntr".$counter."_stock_take  SET closing=(SELECT qty FROM kp_cntr".$counter."_closing_stock WHERE kp_cntr".$counter."_stock_take.item_id=kp_cntr".$counter."_closing_stock.item_id) WHERE shift='$shift'",'update stock take closing');
			if ($update_take) {
				//UPDATE COUNTER HISTORY
				$counter_hist= close_counter_safe_query($connection,"UPDATE kp_counter_hist SET  closing=CURRENT_TIMESTAMP, close_by='$user_id',close_ord='$last' WHERE shift='$shift' AND  cntr_id='$counter'",'update counter history');
				if ($counter_hist) {
					//UPDATE THE CASHIERS SHIFT HISTORY
					$cashier_shift_hist= close_counter_safe_query($connection,"UPDATE kp_user_shift_hist SET closing=CURRENT_TIME,closing_datime=CURRENT_TIMESTAMP WHERE shift='$shift' AND user_id='$user_id'",'update user shift history');
					if ($cashier_shift_hist) {
						//GET NO OF COUNTERS OPEN
						$count=open_counters();
						if ($count==1) {
							//UPDATE COUNTERS
							$update_counter= close_counter_safe_query($connection,"UPDATE kp_counters SET take_status=2,counter_status=2,close_status=2",'close all counters');
							if ($update_counter) {
								//UPDATE THE CASHIER CLOSING COUNTER STATUS
								$cashier= close_counter_safe_query($connection,"UPDATE kp_users SET job=2,shift_status=2,counter=0,cleared=1,counter_status=2",'reset all users');
								if ($cashier) {
									//CLOSE THE DAYS SHIFT
									$day_shift= close_counter_safe_query($connection,"UPDATE kp_day_shift SET closing=CURRENT_TIMESTAMP,close_by='$user_id',close_ord='$last',status=1 WHERE shift='$shift' AND status=2 ",'close day shift');
									if ($day_shift) {
										//EMPTY THE SHIFT TABLE
										$empty_s = close_counter_clear_shift_table($connection);
										if ($empty_s) {
											$close_sum_id = store_counter_close_totals($shift,$counter,$user_id,$last,$counter_open_orders,$pending_returns,$pending_transfers);
											if ($close_sum_id > 0 && function_exists('store_counter_close_stock_take_snapshot')) {
												try {
													store_counter_close_stock_take_snapshot($connection, $close_sum_id, $counter, $shift);
												} catch (Throwable $e) {
													error_log('Counter close snapshot failed: ' . $e->getMessage());
												}
											}
											if ($close_sum_id > 0 && $use_queue_print) {
												$queued = queue_counter_close_summary($connection, $close_sum_id, $counter, $user_id, $shift);
												if ($queued < 1) {
													error_log('Counter close summary queue failed for summary #' . $close_sum_id . ' mode ' . $print_mode);
												}
											} elseif ($close_sum_id > 0) {
												redirect_counter_close_summary_print($close_sum_id, $counter, $shift, '../log_out.php');
											}
											$action="CLOSED|-|-|COUNTER|".date('Y-m-d');
											logs($action,'../logs');
											if (function_exists('backup_tables')) {
												try {
													backup_tables();
												} catch (Throwable $e) {
													error_log('Counter close backup failed: ' . $e->getMessage());
												}
											}
											close_counter_success_redirect('Counter is closed.', '../log_out.php');
										}else{
											?>
												<p>COULD NOT EMPTY SHIFT TABLE. Check the server error log for the database reason.</p>
											<?php
										}
									}else{
										?>
										<p>COULD NOT TRACK DAY SHIFT</p>
										<?php 
									}
								}else{
									?>
									<p>COULD NOT UPDATE USERS</p>
									<?php 
								}
							}else{
								?>
								<p>COULD NOT UPDATE COUNTER HISTORY</p>
								<?php 
							}
						}else{
							//UPDATE COUNTERS
							$update_counter= close_counter_safe_query($connection,"UPDATE kp_counters SET take_status=2,counter_status=2,close_status=3  WHERE cntr_id='$counter'",'close one counter');
							if ($update_counter) {
								//UPDATE THE CASHIER CLOSING COUNTER STATUS
								$cashier= close_counter_safe_query($connection,"UPDATE kp_users SET job=2,shift_status=2,counter=0,cleared=1,counter_status=2 WHERE user_id='$user_id'",'reset cashier status');
								if ($cashier) {
									$close_sum_id = store_counter_close_totals($shift,$counter,$user_id,$last,$counter_open_orders,$pending_returns,$pending_transfers);
									if ($close_sum_id > 0 && function_exists('store_counter_close_stock_take_snapshot')) {
										try {
											store_counter_close_stock_take_snapshot($connection, $close_sum_id, $counter, $shift);
										} catch (Throwable $e) {
											error_log('Counter close snapshot failed: ' . $e->getMessage());
										}
									}
									if ($close_sum_id > 0 && $use_queue_print) {
										$queued = queue_counter_close_summary($connection, $close_sum_id, $counter, $user_id, $shift);
										if ($queued < 1) {
											error_log('Counter close summary queue failed for summary #' . $close_sum_id . ' mode ' . $print_mode);
										}
									} elseif ($close_sum_id > 0) {
										redirect_counter_close_summary_print($close_sum_id, $counter, $shift, '../log_out.php');
									}
									$action="CLOSED|-|-|COUNTER|".date('Y-m-d');
									logs($action,'../logs');
									close_counter_success_redirect('Counter is closed.', '../log_out.php');
								}else{
									?>
									<p>COULD NOT UPDATE CASHIER STATUS</p>
									<?php
								}
							}else{
								?>
								<p>COULD NOT UPDATE COUNTER</p>
								<?php 
							}
						}
					}else{
						?>
						<p>COULD NOT UPDATE CASHIER HISTORY</p>
						<?php
					}
				}else{
					?>
					<p>COULD NOT UPDATE CASHIER HISTORY</p>
					<?php
				}
			}else{
				?>
				<p>Could not Update Closing stock for take!!!Contact Administrator tO OVERRIDE</p>
				<?php 
			}
		}else{
			echo mysqli_error($connection);
			?>
			<p>Could not insert closing stock!!!Contact Administrator tO OVERRIDE</p>
			<?php 
		}
	}else{
	?>
		<p>Could not empty counter closing stock!!!Contact Administrator tO OVERRIDE</p>
	<?php  
	}
}
} catch (Throwable $e) {
	error_log('Close counter fatal recovery: ' . $e->getMessage());
	close_counter_alert_redirect('Counter close hit an error. The system recovered safely where possible. Please reopen the dashboard and check the summary/print queue.');
}

 

 
