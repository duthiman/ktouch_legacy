<?php
	date_default_timezone_set("Africa/Nairobi");
	$filename = "../../inc/gen_funcs.php";

	if (file_exists($filename)){
	    include_once('../../inc/gen_funcs.php');
	}elseif (file_exists("../inc/gen_funcs.php")){
	    include_once('../inc/gen_funcs.php');
	}else{}

	if (!function_exists('ensure_counter_close_stock_take_tables')) {
		function ensure_counter_stock_take_columns($connection, $table){
			$table = preg_replace('/[^a-zA-Z0-9_]/', '', (string)$table);
			if ($table === '') {
				return false;
			}
			$ok = true;
			$columns = [
				'user_id' => "INT NOT NULL DEFAULT 0",
				'issued' => "DECIMAL(12,2) NOT NULL DEFAULT 0",
				'tots' => "DECIMAL(12,2) NOT NULL DEFAULT 0",
				'transfers' => "DECIMAL(12,2) NOT NULL DEFAULT 0",
				'total_in' => "DECIMAL(12,2) NOT NULL DEFAULT 0",
				'sale' => "DECIMAL(12,2) NOT NULL DEFAULT 0",
				'trans_out' => "DECIMAL(12,2) NOT NULL DEFAULT 0",
				'tot_out' => "DECIMAL(12,2) NOT NULL DEFAULT 0",
				'total_out' => "DECIMAL(12,2) NOT NULL DEFAULT 0",
			];
			foreach ($columns as $column => $definition) {
				$check = mysqli_query($connection, "SHOW COLUMNS FROM `$table` LIKE '$column'");
				if ($check && mysqli_num_rows($check) > 0) {
					continue;
				}
				if (!mysqli_query($connection, "ALTER TABLE `$table` ADD COLUMN `$column` $definition")) {
					$ok = false;
				}
			}
			return $ok;
		}

		function ensure_counter_stock_take_table($connection, $counter){
			$counter = (int)$counter;
			if ($counter < 1) {
				return false;
			}
			$take_table = 'kp_cntr' . $counter . '_stock_take';
			$closing_table = 'kp_cntr' . $counter . '_closing_stock';
			$take_created = mysqli_query($connection, "CREATE TABLE IF NOT EXISTS `$take_table` (
				take_id INT NOT NULL AUTO_INCREMENT,
				item_id INT NOT NULL DEFAULT 0,
				staff_id INT NOT NULL DEFAULT 0,
				user_id INT NOT NULL DEFAULT 0,
				opening DECIMAL(12,2) NOT NULL DEFAULT 0,
				counted DECIMAL(12,2) NOT NULL DEFAULT 0,
				closing DECIMAL(12,2) NOT NULL DEFAULT 0,
				issued DECIMAL(12,2) NOT NULL DEFAULT 0,
				tots DECIMAL(12,2) NOT NULL DEFAULT 0,
				transfers DECIMAL(12,2) NOT NULL DEFAULT 0,
				total_in DECIMAL(12,2) NOT NULL DEFAULT 0,
				sale DECIMAL(12,2) NOT NULL DEFAULT 0,
				trans_out DECIMAL(12,2) NOT NULL DEFAULT 0,
				tot_out DECIMAL(12,2) NOT NULL DEFAULT 0,
				total_out DECIMAL(12,2) NOT NULL DEFAULT 0,
				shift DATE NOT NULL,
				PRIMARY KEY (take_id),
				KEY idx_counter_take_shift (shift),
				KEY idx_counter_take_item (item_id)
			) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4");
			$closing_created = mysqli_query($connection, "CREATE TABLE IF NOT EXISTS `$closing_table` (
				item_id INT NOT NULL DEFAULT 0,
				qty DECIMAL(12,2) NOT NULL DEFAULT 0,
				shift DATE NOT NULL,
				KEY idx_counter_closing_shift (shift),
				KEY idx_counter_closing_item (item_id)
			) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4");
			$columns_ok = ensure_counter_stock_take_columns($connection, $take_table);
			$take_exists = mysqli_query($connection, "SHOW TABLES LIKE '$take_table'");
			$closing_exists = mysqli_query($connection, "SHOW TABLES LIKE '$closing_table'");
			return (bool)$take_created && (bool)$closing_created && $columns_ok
				&& $take_exists && mysqli_num_rows($take_exists) > 0
				&& $closing_exists && mysqli_num_rows($closing_exists) > 0;
		}

		function ensure_all_counter_stock_take_columns($connection){
			$counters = mysqli_query($connection, "SELECT cntr_id FROM kp_counters");
			if (!$counters) {
				return;
			}
			while ($counter = mysqli_fetch_assoc($counters)) {
				ensure_counter_stock_take_table($connection, (int)$counter['cntr_id']);
			}
		}

		function ensure_counter_stock_take_rows($connection, $counter, $shift){
			$counter = (int)$counter;
			$shift = mysqli_real_escape_string($connection, (string)$shift);
			if ($counter < 1 || $shift === '') return 0;
			$table = 'kp_cntr' . $counter . '_stock_take';
			$stock_column = 'cntr' . $counter . '_qty';
			$column_check = mysqli_query($connection, "SHOW COLUMNS FROM kp_items LIKE '$stock_column'");
			if (!$column_check || mysqli_num_rows($column_check) < 1) return 0;
			$sql = "INSERT INTO `$table` (item_id, opening, counted, closing, shift)
			        SELECT i.item_id, COALESCE(i.`$stock_column`,0), COALESCE(i.`$stock_column`,0), COALESCE(i.`$stock_column`,0), '$shift'
			        FROM kp_items i
			        WHERE NOT EXISTS (
			          SELECT 1 FROM `$table` s WHERE s.item_id=i.item_id AND s.shift='$shift'
			        )";
			if (!mysqli_query($connection, $sql)) return 0;
			$count_result = mysqli_query($connection, "SELECT COUNT(*) AS total_rows FROM `$table` WHERE shift='$shift'");
			$count_row = $count_result ? mysqli_fetch_assoc($count_result) : null;
			return (int)($count_row['total_rows'] ?? 0);
		}

function populate_counter_stock_take_movements($connection, $counter, $shift){
    $counter = (int)$counter;
    $shift = mysqli_real_escape_string($connection, (string)$shift);
    $table = 'kp_cntr' . $counter . '_stock_take';
			if ($counter < 1 || $shift === '') {
				return;
    }
    ensure_counter_stock_take_columns($connection, $table);
    $movement_sql = "UPDATE `$table` s SET
        issued=COALESCE((SELECT SUM(ri.issue_qty) FROM kp_counter_req_items ri INNER JOIN kp_counter_req rq ON rq.req_id=ri.req_id WHERE ri.item_id=s.item_id AND ri.counter='$counter' AND ri.req_shift='$shift' AND rq.issue_status=1),0),
        tots=COALESCE((SELECT SUM(ti.tots) FROM kp_tot_items ti WHERE ti.tot_item_id=s.item_id AND ti.counter='$counter' AND ti.shift='$shift'),0),
        transfers=COALESCE((SELECT SUM(ti.qty) FROM kp_counter_trans_items ti INNER JOIN kp_counter_trans tr ON tr.trans_id=ti.trans_id WHERE ti.item_id=s.item_id AND tr.cntr_to='$counter' AND tr.shift='$shift' AND ti.status=1),0),
        sale=COALESCE((SELECT SUM(si.qty) FROM kp_sale_items si WHERE si.item_id=s.item_id AND si.stock_counter='$counter' AND si.shift='$shift'),0),
        trans_out=COALESCE((SELECT SUM(ti.qty) FROM kp_counter_trans_items ti INNER JOIN kp_counter_trans tr ON tr.trans_id=ti.trans_id WHERE ti.item_id=s.item_id AND tr.cntr_from='$counter' AND tr.shift='$shift'),0),
        tot_out=COALESCE((SELECT SUM(ti.tots) FROM kp_tot_items ti WHERE ti.item_id=s.item_id AND ti.counter='$counter' AND ti.shift='$shift'),0),
        total_in=COALESCE((SELECT SUM(ri.issue_qty) FROM kp_counter_req_items ri INNER JOIN kp_counter_req rq ON rq.req_id=ri.req_id WHERE ri.item_id=s.item_id AND ri.counter='$counter' AND ri.req_shift='$shift' AND rq.issue_status=1),0)+COALESCE((SELECT SUM(ti.tots) FROM kp_tot_items ti WHERE ti.tot_item_id=s.item_id AND ti.counter='$counter' AND ti.shift='$shift'),0)+COALESCE((SELECT SUM(ti.qty) FROM kp_counter_trans_items ti INNER JOIN kp_counter_trans tr ON tr.trans_id=ti.trans_id WHERE ti.item_id=s.item_id AND tr.cntr_to='$counter' AND tr.shift='$shift' AND ti.status=1),0),
        total_out=COALESCE((SELECT SUM(si.qty) FROM kp_sale_items si WHERE si.item_id=s.item_id AND si.stock_counter='$counter' AND si.shift='$shift'),0)+COALESCE((SELECT SUM(ti.qty) FROM kp_counter_trans_items ti INNER JOIN kp_counter_trans tr ON tr.trans_id=ti.trans_id WHERE ti.item_id=s.item_id AND tr.cntr_from='$counter' AND tr.shift='$shift'),0)+COALESCE((SELECT SUM(ti.tots) FROM kp_tot_items ti WHERE ti.item_id=s.item_id AND ti.counter='$counter' AND ti.shift='$shift'),0)
        WHERE s.shift='$shift'";
    if (!mysqli_query($connection, $movement_sql)) {
        error_log('Counter stock movement snapshot failed for ' . $table . ': ' . mysqli_error($connection));
        return false;
    }
    return true;
}

		function ensure_counter_close_stock_take_tables($connection){
			mysqli_query($connection,"CREATE TABLE IF NOT EXISTS kp_counter_close_stock_take_items (
				close_take_id INT NOT NULL AUTO_INCREMENT,
				close_sum_id INT NOT NULL DEFAULT 0,
				shift_date DATE NOT NULL,
				cntr_id INT NOT NULL DEFAULT 0,
				item_id INT NOT NULL DEFAULT 0,
				opening DECIMAL(12,2) NOT NULL DEFAULT 0,
				counted DECIMAL(12,2) NOT NULL DEFAULT 0,
				closing DECIMAL(12,2) NOT NULL DEFAULT 0,
				variance_qty DECIMAL(12,2) NOT NULL DEFAULT 0,
				is_ok TINYINT(1) NOT NULL DEFAULT 0,
				issued DECIMAL(12,2) NOT NULL DEFAULT 0,
				tots DECIMAL(12,2) NOT NULL DEFAULT 0,
				transfers DECIMAL(12,2) NOT NULL DEFAULT 0,
				total_in DECIMAL(12,2) NOT NULL DEFAULT 0,
				sale DECIMAL(12,2) NOT NULL DEFAULT 0,
				trans_out DECIMAL(12,2) NOT NULL DEFAULT 0,
				tot_out DECIMAL(12,2) NOT NULL DEFAULT 0,
				total_out DECIMAL(12,2) NOT NULL DEFAULT 0,
				created_at TIMESTAMP NOT NULL DEFAULT CURRENT_TIMESTAMP,
				PRIMARY KEY (close_take_id),
				KEY idx_close_sum_id (close_sum_id),
				KEY idx_counter_shift (cntr_id, shift_date)
			)") or die(mysqli_error($connection));
			ensure_counter_stock_take_columns($connection, 'kp_counter_close_stock_take_items');
			ensure_all_counter_stock_take_columns($connection);
		}
	}

	if (!function_exists('kp_counter_close_summary_id_by_shift')) {
		function kp_counter_close_summary_id_by_shift($connection, $counter, $shift){
			ensure_counter_close_stock_take_tables($connection);
			$counter = (int)$counter;
			$shift = mysqli_real_escape_string($connection, (string)$shift);
			return (int)get_column('kp_counter_close_summary', 'close_sum_id', "cntr_id='$counter' AND shift_date='$shift' ORDER BY close_sum_id DESC");
		}
	}

	if (!function_exists('store_counter_close_stock_take_snapshot')) {
		function store_counter_close_stock_take_snapshot($connection, $close_sum_id, $counter, $shift){
			ensure_counter_close_stock_take_tables($connection);
			$close_sum_id = (int)$close_sum_id;
			$counter = (int)$counter;
			$shift = mysqli_real_escape_string($connection, (string)$shift);
			if ($close_sum_id < 1 || $counter < 1 || $shift === '') {
				return 0;
			}
			$table = "kp_cntr".$counter."_stock_take";
			$table_exists = mysqli_query($connection, "SHOW TABLES LIKE '$table'");
			if (!$table_exists || mysqli_num_rows($table_exists) < 1) {
				return 0;
			}
			// Auto-heal missing rows for this shift before calculating movements.
			if (ensure_counter_stock_take_rows($connection, $counter, $shift) < 1) {
				return 0;
			}
            if (!populate_counter_stock_take_movements($connection, $counter, $shift)) {
                return 0;
            }
			mysqli_query($connection, "DELETE FROM kp_counter_close_stock_take_items WHERE close_sum_id='$close_sum_id'");
			$rows = mysqli_query($connection, "SELECT item_id, opening, counted, closing, issued, tots, transfers, total_in, sale, trans_out, tot_out, total_out FROM `$table` WHERE shift='$shift'");
			$saved = 0;
			if ($rows) {
				while ($row = mysqli_fetch_assoc($rows)) {
					$item_id = (int)$row['item_id'];
					$opening = (float)($row['opening'] ?? 0);
					$counted = (float)($row['counted'] ?? 0);
					$closing = (float)($row['closing'] ?? 0);
					$issued = (float)($row['issued'] ?? 0);
					$tots = (float)($row['tots'] ?? 0);
					$transfers = (float)($row['transfers'] ?? 0);
					$total_in = (float)($row['total_in'] ?? 0);
					$sale = (float)($row['sale'] ?? 0);
					$trans_out = (float)($row['trans_out'] ?? 0);
					$tot_out = (float)($row['tot_out'] ?? 0);
					$total_out = (float)($row['total_out'] ?? 0);
					$variance = $counted - $opening;
					$is_ok = abs($variance) <= 0.0001 ? 1 : 0;
					mysqli_query($connection, "INSERT INTO kp_counter_close_stock_take_items(close_sum_id,shift_date,cntr_id,item_id,opening,counted,closing,variance_qty,is_ok,issued,tots,transfers,total_in,sale,trans_out,tot_out,total_out) VALUES('$close_sum_id','$shift','$counter','$item_id','$opening','$counted','$closing','$variance','$is_ok','$issued','$tots','$transfers','$total_in','$sale','$trans_out','$tot_out','$total_out')") or die(mysqli_error($connection));
					$saved++;
				}
			}
			return $saved;
		}
	}

	if (!function_exists('kp_counter_stock_take_snapshot_summary')) {
		function kp_counter_stock_take_snapshot_summary($connection, $counter, $shift, $summary_id = 0){
			ensure_counter_close_stock_take_tables($connection);
			$counter = (int)$counter;
			$summary_id = (int)$summary_id;
			$shift = mysqli_real_escape_string($connection, (string)$shift);
			$summary = [
				'available' => false,
				'taken' => false,
				'ok_count' => 0,
				'variance_count' => 0,
				'variance_qty' => 0.0,
				'status' => 'NOT TAKEN',
				'summary_id' => 0,
			];
			if ($summary_id < 1) {
				$summary_id = kp_counter_close_summary_id_by_shift($connection, $counter, $shift);
			}
			if ($summary_id > 0) {
				$summary['summary_id'] = $summary_id;
				$summary['available'] = true;
				$query = mysqli_query($connection, "SELECT COUNT(*) AS total_rows, SUM(CASE WHEN is_ok=1 THEN 1 ELSE 0 END) AS ok_count, SUM(CASE WHEN is_ok=0 THEN 1 ELSE 0 END) AS variance_count, SUM(ABS(variance_qty)) AS variance_qty FROM kp_counter_close_stock_take_items WHERE close_sum_id='$summary_id'");
				if ($query && ($row = mysqli_fetch_assoc($query))) {
					$total_rows = (int)($row['total_rows'] ?? 0);
					if ($total_rows > 0) {
						$summary['taken'] = true;
						$summary['ok_count'] = (int)($row['ok_count'] ?? 0);
						$summary['variance_count'] = (int)($row['variance_count'] ?? 0);
						$summary['variance_qty'] = (float)($row['variance_qty'] ?? 0);
						$summary['status'] = $summary['variance_count'] > 0 ? 'VARIANCE' : 'OK';
						return $summary;
					}
				}
			}

			$table = "kp_cntr".$counter."_stock_take";
			$table_exists = mysqli_query($connection, "SHOW TABLES LIKE '$table'");
			if (!$table_exists || mysqli_num_rows($table_exists) < 1) {
				return $summary;
			}
			$summary['available'] = true;
			$rows = mysqli_query($connection, "SELECT opening, counted FROM `$table` WHERE shift='$shift'");
			if (!$rows || mysqli_num_rows($rows) < 1) {
				return $summary;
			}
			$summary['taken'] = true;
			while ($row = mysqli_fetch_assoc($rows)) {
				$opening = (float)($row['opening'] ?? 0);
				$counted = (float)($row['counted'] ?? 0);
				$variance = $counted - $opening;
				if (abs($variance) > 0.0001) {
					$summary['variance_count']++;
					$summary['variance_qty'] += abs($variance);
				} else {
					$summary['ok_count']++;
				}
			}
			$summary['status'] = $summary['variance_count'] > 0 ? 'VARIANCE' : 'OK';
			return $summary;
		}
	}

	if (!function_exists('kp_counter_stock_take_snapshot_rows')) {
		function kp_counter_stock_take_snapshot_rows($connection, $counter, $shift, $summary_id = 0, $view = 'all'){
			ensure_counter_close_stock_take_tables($connection);
			$counter = (int)$counter;
			$summary_id = (int)$summary_id;
			$shift = mysqli_real_escape_string($connection, (string)$shift);
			$view = strtolower(trim((string)$view));
			$rows_out = [];

			if ($summary_id < 1) {
				$summary_id = kp_counter_close_summary_id_by_shift($connection, $counter, $shift);
			}

			if ($summary_id > 0) {
				$where = "close_sum_id='$summary_id'";
				if ($view === 'variance') {
					$where .= " AND is_ok=0";
				} elseif ($view === 'ok') {
					$where .= " AND is_ok=1";
				}
				$query = mysqli_query($connection, "SELECT * FROM kp_counter_close_stock_take_items WHERE $where ORDER BY ABS(variance_qty) DESC, item_id ASC");
				if ($query) {
					while ($row = mysqli_fetch_assoc($query)) {
						$rows_out[] = [
							'item_id' => (int)$row['item_id'],
							'item' => get_item((int)$row['item_id']),
							'opening' => (float)$row['opening'],
							'counted' => (float)$row['counted'],
							'closing' => (float)$row['closing'],
							'variance' => (float)$row['variance_qty'],
							'is_ok' => (int)$row['is_ok'] === 1,
						];
					}
					return $rows_out;
				}
			}

			$table = "kp_cntr".$counter."_stock_take";
			$table_exists = mysqli_query($connection, "SHOW TABLES LIKE '$table'");
			if (!$table_exists || mysqli_num_rows($table_exists) < 1) {
				return $rows_out;
			}
			$query = mysqli_query($connection, "SELECT * FROM `$table` WHERE shift='$shift' ORDER BY take_id DESC");
			if ($query) {
				while ($row = mysqli_fetch_assoc($query)) {
					$opening = (float)($row['opening'] ?? 0);
					$counted = (float)($row['counted'] ?? 0);
					$variance = $counted - $opening;
					$is_ok = abs($variance) <= 0.0001;
					if ($view === 'variance' && $is_ok) {
						continue;
					}
					if ($view === 'ok' && !$is_ok) {
						continue;
					}
					$rows_out[] = [
						'item_id' => (int)$row['item_id'],
						'item' => get_item((int)$row['item_id']),
						'opening' => $opening,
						'counted' => $counted,
						'closing' => (float)($row['closing'] ?? 0),
						'variance' => $variance,
						'is_ok' => $is_ok,
					];
				}
			}
			return $rows_out;
		}
	}
?>
