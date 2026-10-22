<?php
if (defined('KP_GEN_FUNCS_LOADED')) {
    return;
}
define('KP_GEN_FUNCS_LOADED', 1);

if (session_status() === PHP_SESSION_NONE) {
    session_start();
}
////////////////////////////////////////////
/////////////////COMMON FUNCTIONS////////
//////////////////////////////////////////// 
function get_cust($id){
	$user=get_column("kp_customers","name","cust_id='$id'");
	return $user;
}
function get_user($id){
	$user=get_column("kp_users","username","user_id='$id'");
	return $user;
}
function get_staff($id){
	$user=get_column("kp_staff","staff_name","staff_id='$id'");
	return $user;
}
function get_item($id){
	$name=get_column("kp_items","name","item_id='$id'");
	return $name;
}
function get_cat($id){
	$name=get_column('kp_cat','name',"cat_id='$id'");
	return $name;
}
function get_sub($id){
	$name=get_column('kp_sub_cat','name',"sub_id='$id'");
	return $name;
}
function get_sup($id){
	$name=get_column('kp_suppliers','sup_name',"sup_id='$id'");
	return $name;
}
function get_counter($id){
	$name=get_column("kp_counters","name","cntr_id='$id'");
	return $name;
}
function waiter_open_status($id){
	$status=column_count("kp_sale_status","sale_id","status=3 AND user_id='$id'");
	return $status;
}
function user_counter($user_id){ 
	$counter=get_column("kp_users","counter","user_id='$user_id'");
	return $counter;
}
function shift(){
	global $connection;
	$check_shift=mysqli_query($connection,"SELECT shift FROM kp_shift");
	$status=mysqli_num_rows($check_shift);
	if ($status==0) {
	    $shift=date("d-M-Y");
	}else{
		$st=mysqli_fetch_assoc($check_shift);
		$shift=$st['shift'];
	}
	return $shift;
}
////////////////////////////////////////////
/////////////////COMMON FUNCTIONS////////
//////////////////////////////////////////// 

////////////////////////////////////////////
/////////////////START GENERAL FUNCTIONS////////
//////////////////////////////////////////// 
function  page_restrict(){
	if (!isset($_SESSION['user_id'])) {
	  header("Location:../log_out.php");
	}
}
function today(){
	date_default_timezone_set("Africa/Nairobi");
	$today=date("d-M-Y h:i:sa");
	return $today;
}
function totime(){
	date_default_timezone_set("Africa/Nairobi");
	$today=date("h:i:sa");
	return $today;
}
function check_duplicate($table,$column,$value){
	global $connection;
	//$sql="INSERT INTO ".$table." (".$field_values.") VALUES (".$data_values.")";
	$check_dup= mysqli_query($connection,"SELECT ".$column." FROM ".$table." WHERE ".$column."='$value'");
	$dup = mysqli_num_rows($check_dup);
	if ($dup>0) {
		return true;
	}else{
		return false;
	}
}
function front_perm($pass,$action){
	global $connection;
	$verify = mysqli_query($connection,"SELECT user_id,kp_roles.front_perm FROM kp_users INNER JOIN kp_roles ON kp_users.role_id=kp_roles.role_id WHERE pin='$pass'") or die("Could not verify the password".mysqli_error($connection));
	if (mysqli_num_rows($verify) < 1) {
		return false;
	}else{
		$row = mysqli_fetch_assoc($verify);
		$front_perm=$row['front_perm'];
		$arr = explode(',', $front_perm); 
		if (in_array($action, $arr)){
			return true;
		}else{
			return false;
	    }
	}
}
function menu($menu_id){
	global $connection;
	$user_id = $_SESSION['user_id'];
	$get_details = mysqli_query($connection,"SELECT * FROM kp_users WHERE user_id='$user_id'") or die("Could not get the user permissions");
    $row = mysqli_fetch_assoc($get_details);
    $perm=$row['permissions'];

    $arr = explode(',',$perm);
 
    if($perm=='*'){
    	return 1;
	}elseif (in_array($menu_id, $arr)) { 
		 	return 1;
	}else{ 
		return 0;
	} 
}
function ensure_split_message_system() {
	global $connection;

	mysqli_query($connection, "CREATE TABLE IF NOT EXISTS kp_split_message (
		id INT NOT NULL AUTO_INCREMENT,
		name VARCHAR(120) NOT NULL DEFAULT '',
		phone VARCHAR(40) NOT NULL DEFAULT '',
		code VARCHAR(80) NOT NULL DEFAULT '',
		amount DECIMAL(10,2) NOT NULL DEFAULT 0,
		account VARCHAR(120) NOT NULL DEFAULT '',
		balance DECIMAL(10,2) NOT NULL DEFAULT 0,
		cost DECIMAL(10,2) NOT NULL DEFAULT 0,
		date DATE NULL,
		time VARCHAR(30) NOT NULL DEFAULT '',
		datime DATETIME NULL,
		timestamp TIMESTAMP NOT NULL DEFAULT CURRENT_TIMESTAMP,
		status TINYINT(1) NOT NULL DEFAULT 2,
		claim_status TINYINT(1) NOT NULL DEFAULT 2,
		assign_status TINYINT(1) NOT NULL DEFAULT 2,
		approve_status TINYINT(1) NOT NULL DEFAULT 2,
		clear_status TINYINT(1) NOT NULL DEFAULT 2,
		user_id INT NOT NULL DEFAULT 0,
		claim_staff INT NOT NULL DEFAULT 0,
		assign_by INT NOT NULL DEFAULT 0,
		app_user INT NOT NULL DEFAULT 0,
		shift VARCHAR(60) NOT NULL DEFAULT '',
		PRIMARY KEY (id),
		UNIQUE KEY uniq_code (code),
		KEY idx_claim_status (claim_status, assign_status),
		KEY idx_user_shift (user_id, shift),
		KEY idx_claim_staff (claim_staff)
	)");

	$columns = array(
		'name' => "ALTER TABLE kp_split_message ADD COLUMN name VARCHAR(120) NOT NULL DEFAULT ''",
		'phone' => "ALTER TABLE kp_split_message ADD COLUMN phone VARCHAR(40) NOT NULL DEFAULT ''",
		'code' => "ALTER TABLE kp_split_message ADD COLUMN code VARCHAR(80) NOT NULL DEFAULT ''",
		'amount' => "ALTER TABLE kp_split_message ADD COLUMN amount DECIMAL(10,2) NOT NULL DEFAULT 0",
		'account' => "ALTER TABLE kp_split_message ADD COLUMN account VARCHAR(120) NOT NULL DEFAULT ''",
		'balance' => "ALTER TABLE kp_split_message ADD COLUMN balance DECIMAL(10,2) NOT NULL DEFAULT 0",
		'cost' => "ALTER TABLE kp_split_message ADD COLUMN cost DECIMAL(10,2) NOT NULL DEFAULT 0",
		'date' => "ALTER TABLE kp_split_message ADD COLUMN date DATE NULL",
		'time' => "ALTER TABLE kp_split_message ADD COLUMN time VARCHAR(30) NOT NULL DEFAULT ''",
		'datime' => "ALTER TABLE kp_split_message ADD COLUMN datime DATETIME NULL",
		'timestamp' => "ALTER TABLE kp_split_message ADD COLUMN timestamp TIMESTAMP NOT NULL DEFAULT CURRENT_TIMESTAMP",
		'status' => "ALTER TABLE kp_split_message ADD COLUMN status TINYINT(1) NOT NULL DEFAULT 2",
		'claim_status' => "ALTER TABLE kp_split_message ADD COLUMN claim_status TINYINT(1) NOT NULL DEFAULT 2",
		'assign_status' => "ALTER TABLE kp_split_message ADD COLUMN assign_status TINYINT(1) NOT NULL DEFAULT 2",
		'approve_status' => "ALTER TABLE kp_split_message ADD COLUMN approve_status TINYINT(1) NOT NULL DEFAULT 2",
		'clear_status' => "ALTER TABLE kp_split_message ADD COLUMN clear_status TINYINT(1) NOT NULL DEFAULT 2",
		'user_id' => "ALTER TABLE kp_split_message ADD COLUMN user_id INT NOT NULL DEFAULT 0",
		'claim_staff' => "ALTER TABLE kp_split_message ADD COLUMN claim_staff INT NOT NULL DEFAULT 0",
		'assign_by' => "ALTER TABLE kp_split_message ADD COLUMN assign_by INT NOT NULL DEFAULT 0",
		'app_user' => "ALTER TABLE kp_split_message ADD COLUMN app_user INT NOT NULL DEFAULT 0",
		'shift' => "ALTER TABLE kp_split_message ADD COLUMN shift VARCHAR(60) NOT NULL DEFAULT ''"
	);

	foreach ($columns as $column => $statement) {
		$col_chk = mysqli_query($connection, "SHOW COLUMNS FROM kp_split_message LIKE '$column'");
		if ($col_chk && mysqli_num_rows($col_chk) === 0) {
			mysqli_query($connection, $statement);
		}
	}

	$uniq_chk = mysqli_query($connection, "SHOW INDEX FROM kp_split_message WHERE Key_name='uniq_code'");
	if ($uniq_chk && mysqli_num_rows($uniq_chk) === 0) {
		mysqli_query($connection, "ALTER TABLE kp_split_message ADD UNIQUE KEY uniq_code (code)");
	}

	$claim_idx = mysqli_query($connection, "SHOW INDEX FROM kp_split_message WHERE Key_name='idx_claim_status'");
	if ($claim_idx && mysqli_num_rows($claim_idx) === 0) {
		mysqli_query($connection, "ALTER TABLE kp_split_message ADD KEY idx_claim_status (claim_status, assign_status)");
	}

	$user_shift_idx = mysqli_query($connection, "SHOW INDEX FROM kp_split_message WHERE Key_name='idx_user_shift'");
	if ($user_shift_idx && mysqli_num_rows($user_shift_idx) === 0) {
		mysqli_query($connection, "ALTER TABLE kp_split_message ADD KEY idx_user_shift (user_id, shift)");
	}

	$claim_staff_idx = mysqli_query($connection, "SHOW INDEX FROM kp_split_message WHERE Key_name='idx_claim_staff'");
	if ($claim_staff_idx && mysqli_num_rows($claim_staff_idx) === 0) {
		mysqli_query($connection, "ALTER TABLE kp_split_message ADD KEY idx_claim_staff (claim_staff)");
	}
}
function ensure_tax_system() {
	global $connection;

	mysqli_query($connection, "CREATE TABLE IF NOT EXISTS kp_tax (
		tax_id INT NOT NULL AUTO_INCREMENT,
		name VARCHAR(120) NOT NULL DEFAULT '',
		code VARCHAR(40) NOT NULL DEFAULT '',
		percent DECIMAL(10,2) NOT NULL DEFAULT 0,
		del_status TINYINT(1) NOT NULL DEFAULT 2,
		PRIMARY KEY (tax_id),
		KEY idx_del_status (del_status),
		KEY idx_name (name)
	)");

	$columns = array(
		'name' => "ALTER TABLE kp_tax ADD COLUMN name VARCHAR(120) NOT NULL DEFAULT ''",
		'code' => "ALTER TABLE kp_tax ADD COLUMN code VARCHAR(40) NOT NULL DEFAULT ''",
		'percent' => "ALTER TABLE kp_tax ADD COLUMN percent DECIMAL(10,2) NOT NULL DEFAULT 0",
		'del_status' => "ALTER TABLE kp_tax ADD COLUMN del_status TINYINT(1) NOT NULL DEFAULT 2"
	);

	foreach ($columns as $column => $statement) {
		$col_chk = mysqli_query($connection, "SHOW COLUMNS FROM kp_tax LIKE '$column'");
		if ($col_chk && mysqli_num_rows($col_chk) === 0) {
			mysqli_query($connection, $statement);
		}
	}

	$del_idx = mysqli_query($connection, "SHOW INDEX FROM kp_tax WHERE Key_name='idx_del_status'");
	if ($del_idx && mysqli_num_rows($del_idx) === 0) {
		mysqli_query($connection, "ALTER TABLE kp_tax ADD KEY idx_del_status (del_status)");
	}

	$name_idx = mysqli_query($connection, "SHOW INDEX FROM kp_tax WHERE Key_name='idx_name'");
	if ($name_idx && mysqli_num_rows($name_idx) === 0) {
		$name_col = mysqli_query($connection, "SHOW COLUMNS FROM kp_tax LIKE 'name'");
		$name_info = $name_col ? mysqli_fetch_assoc($name_col) : null;
		$name_type = $name_info ? strtolower((string)$name_info['Type']) : '';
		if (strpos($name_type, 'text') !== false || strpos($name_type, 'blob') !== false) {
			mysqli_query($connection, "ALTER TABLE kp_tax ADD KEY idx_name (name(191))");
		} else {
			mysqli_query($connection, "ALTER TABLE kp_tax ADD KEY idx_name (name)");
		}
	}
}
function ensure_kug_purchase_system() {
	global $connection;

	mysqli_query($connection, "CREATE TABLE IF NOT EXISTS kp_po_id (
		po_id INT NOT NULL DEFAULT 0
	)");

	mysqli_query($connection, "CREATE TABLE IF NOT EXISTS kp_po_status (
		id INT NOT NULL AUTO_INCREMENT,
		po_id INT NOT NULL DEFAULT 0,
		user_id INT NOT NULL DEFAULT 0,
		sup_id INT NOT NULL DEFAULT 0,
		po_status TINYINT(1) NOT NULL DEFAULT 2,
		day DATE NULL,
		datime TIMESTAMP NOT NULL DEFAULT CURRENT_TIMESTAMP,
		parent INT NOT NULL DEFAULT 0,
		PRIMARY KEY (id),
		KEY idx_user_status (user_id, po_status),
		KEY idx_po_id (po_id)
	)");

	mysqli_query($connection, "CREATE TABLE IF NOT EXISTS kp_po_items (
		id INT NOT NULL AUTO_INCREMENT,
		po_id INT NOT NULL DEFAULT 0,
		item_id INT NOT NULL DEFAULT 0,
		qty DECIMAL(10,2) NOT NULL DEFAULT 0,
		price DECIMAL(10,2) NOT NULL DEFAULT 0,
		total DECIMAL(10,2) NOT NULL DEFAULT 0,
		disc DECIMAL(10,2) NOT NULL DEFAULT 0,
		tax_id INT NOT NULL DEFAULT 0,
		tax_type TINYINT(1) NOT NULL DEFAULT 0,
		vatable DECIMAL(10,2) NOT NULL DEFAULT 0,
		vat DECIMAL(10,2) NOT NULL DEFAULT 0,
		day DATE NULL,
		status TINYINT(1) NOT NULL DEFAULT 2,
		rec_price DECIMAL(10,2) NOT NULL DEFAULT 0,
		rec_qty DECIMAL(10,2) NOT NULL DEFAULT 0,
		rec_total DECIMAL(10,2) NOT NULL DEFAULT 0,
		rec_user INT NOT NULL DEFAULT 0,
		rec_status TINYINT(1) NOT NULL DEFAULT 2,
		s2s TINYINT(1) NOT NULL DEFAULT 2,
		PRIMARY KEY (id),
		KEY idx_po_item (po_id, item_id),
		KEY idx_po_status (po_id, status)
	)");

	mysqli_query($connection, "CREATE TABLE IF NOT EXISTS kp_po (
		id INT NOT NULL AUTO_INCREMENT,
		po_id INT NOT NULL DEFAULT 0,
		req_id INT NOT NULL DEFAULT 0,
		sup_id INT NOT NULL DEFAULT 0,
		ord_user INT NOT NULL DEFAULT 0,
		user_id INT NOT NULL DEFAULT 0,
		shift VARCHAR(60) NOT NULL DEFAULT '',
		po_date DATE NULL,
		ord_date DATE NULL,
		amount DECIMAL(10,2) NOT NULL DEFAULT 0,
		disc DECIMAL(10,2) NOT NULL DEFAULT 0,
		tax_by TINYINT(1) NOT NULL DEFAULT 0,
		tax_type TINYINT(1) NOT NULL DEFAULT 0,
		tax_perc DECIMAL(10,2) NOT NULL DEFAULT 0,
		vatable DECIMAL(10,2) NOT NULL DEFAULT 0,
		vat DECIMAL(10,2) NOT NULL DEFAULT 0,
		cf_bal DECIMAL(10,2) NOT NULL DEFAULT 0,
		refno VARCHAR(255) NOT NULL DEFAULT '',
		rec_status TINYINT(1) NOT NULL DEFAULT 2,
		stock_status TINYINT(1) NOT NULL DEFAULT 2,
		s2s TINYINT(1) NOT NULL DEFAULT 2,
		rec_staff INT NOT NULL DEFAULT 0,
		rec_date DATE NULL,
		PRIMARY KEY (id),
		UNIQUE KEY uniq_po_id (po_id),
		KEY idx_po_shift (shift),
		KEY idx_po_rec (rec_status, stock_status)
	)");

	$poStatusColumns = array(
		'sup_id' => "ALTER TABLE kp_po_status ADD COLUMN sup_id INT NOT NULL DEFAULT 0",
		'po_status' => "ALTER TABLE kp_po_status ADD COLUMN po_status TINYINT(1) NOT NULL DEFAULT 2",
		'day' => "ALTER TABLE kp_po_status ADD COLUMN day DATE NULL",
		'datime' => "ALTER TABLE kp_po_status ADD COLUMN datime TIMESTAMP NOT NULL DEFAULT CURRENT_TIMESTAMP",
		'parent' => "ALTER TABLE kp_po_status ADD COLUMN parent INT NOT NULL DEFAULT 0"
	);
	foreach ($poStatusColumns as $column => $statement) {
		$col_chk = mysqli_query($connection, "SHOW COLUMNS FROM kp_po_status LIKE '$column'");
		if ($col_chk && mysqli_num_rows($col_chk) === 0) {
			mysqli_query($connection, $statement);
		}
	}

	$poItemColumns = array(
		'disc' => "ALTER TABLE kp_po_items ADD COLUMN disc DECIMAL(10,2) NOT NULL DEFAULT 0",
		'tax_id' => "ALTER TABLE kp_po_items ADD COLUMN tax_id INT NOT NULL DEFAULT 0",
		'tax_type' => "ALTER TABLE kp_po_items ADD COLUMN tax_type TINYINT(1) NOT NULL DEFAULT 0",
		'vatable' => "ALTER TABLE kp_po_items ADD COLUMN vatable DECIMAL(10,2) NOT NULL DEFAULT 0",
		'vat' => "ALTER TABLE kp_po_items ADD COLUMN vat DECIMAL(10,2) NOT NULL DEFAULT 0",
		'day' => "ALTER TABLE kp_po_items ADD COLUMN day DATE NULL",
		'status' => "ALTER TABLE kp_po_items ADD COLUMN status TINYINT(1) NOT NULL DEFAULT 2",
		'rec_price' => "ALTER TABLE kp_po_items ADD COLUMN rec_price DECIMAL(10,2) NOT NULL DEFAULT 0",
		'rec_qty' => "ALTER TABLE kp_po_items ADD COLUMN rec_qty DECIMAL(10,2) NOT NULL DEFAULT 0",
		'rec_total' => "ALTER TABLE kp_po_items ADD COLUMN rec_total DECIMAL(10,2) NOT NULL DEFAULT 0",
		'rec_user' => "ALTER TABLE kp_po_items ADD COLUMN rec_user INT NOT NULL DEFAULT 0",
		'rec_status' => "ALTER TABLE kp_po_items ADD COLUMN rec_status TINYINT(1) NOT NULL DEFAULT 2",
		's2s' => "ALTER TABLE kp_po_items ADD COLUMN s2s TINYINT(1) NOT NULL DEFAULT 2"
	);
	foreach ($poItemColumns as $column => $statement) {
		$col_chk = mysqli_query($connection, "SHOW COLUMNS FROM kp_po_items LIKE '$column'");
		if ($col_chk && mysqli_num_rows($col_chk) === 0) {
			mysqli_query($connection, $statement);
		}
	}

	$poColumns = array(
		'req_id' => "ALTER TABLE kp_po ADD COLUMN req_id INT NOT NULL DEFAULT 0",
		'ord_user' => "ALTER TABLE kp_po ADD COLUMN ord_user INT NOT NULL DEFAULT 0",
		'user_id' => "ALTER TABLE kp_po ADD COLUMN user_id INT NOT NULL DEFAULT 0",
		'shift' => "ALTER TABLE kp_po ADD COLUMN shift VARCHAR(60) NOT NULL DEFAULT ''",
		'po_date' => "ALTER TABLE kp_po ADD COLUMN po_date DATE NULL",
		'ord_date' => "ALTER TABLE kp_po ADD COLUMN ord_date DATE NULL",
		'disc' => "ALTER TABLE kp_po ADD COLUMN disc DECIMAL(10,2) NOT NULL DEFAULT 0",
		'tax_by' => "ALTER TABLE kp_po ADD COLUMN tax_by TINYINT(1) NOT NULL DEFAULT 0",
		'tax_type' => "ALTER TABLE kp_po ADD COLUMN tax_type TINYINT(1) NOT NULL DEFAULT 0",
		'tax_perc' => "ALTER TABLE kp_po ADD COLUMN tax_perc DECIMAL(10,2) NOT NULL DEFAULT 0",
		'vatable' => "ALTER TABLE kp_po ADD COLUMN vatable DECIMAL(10,2) NOT NULL DEFAULT 0",
		'vat' => "ALTER TABLE kp_po ADD COLUMN vat DECIMAL(10,2) NOT NULL DEFAULT 0",
		'cf_bal' => "ALTER TABLE kp_po ADD COLUMN cf_bal DECIMAL(10,2) NOT NULL DEFAULT 0",
		'refno' => "ALTER TABLE kp_po ADD COLUMN refno VARCHAR(255) NOT NULL DEFAULT ''",
		'rec_status' => "ALTER TABLE kp_po ADD COLUMN rec_status TINYINT(1) NOT NULL DEFAULT 2",
		'stock_status' => "ALTER TABLE kp_po ADD COLUMN stock_status TINYINT(1) NOT NULL DEFAULT 2",
		's2s' => "ALTER TABLE kp_po ADD COLUMN s2s TINYINT(1) NOT NULL DEFAULT 2",
		'rec_staff' => "ALTER TABLE kp_po ADD COLUMN rec_staff INT NOT NULL DEFAULT 0",
		'rec_date' => "ALTER TABLE kp_po ADD COLUMN rec_date DATE NULL"
	);
	foreach ($poColumns as $column => $statement) {
		$col_chk = mysqli_query($connection, "SHOW COLUMNS FROM kp_po LIKE '$column'");
		if ($col_chk && mysqli_num_rows($col_chk) === 0) {
			mysqli_query($connection, $statement);
		}
	}
}
function ensure_sales_parent_system() {
	global $connection;

	$sales_parent_chk = mysqli_query($connection, "SHOW COLUMNS FROM kp_sales LIKE 'parent'");
	if ($sales_parent_chk && mysqli_num_rows($sales_parent_chk) === 0) {
		mysqli_query($connection, "ALTER TABLE kp_sales ADD COLUMN parent INT NOT NULL DEFAULT 0");
	}

	$sales_parent_sale_chk = mysqli_query($connection, "SHOW COLUMNS FROM kp_sales LIKE 'parent_sale'");
	if ($sales_parent_sale_chk && mysqli_num_rows($sales_parent_sale_chk) === 0) {
		mysqli_query($connection, "ALTER TABLE kp_sales ADD COLUMN parent_sale INT NOT NULL DEFAULT 0");
	}

	$sale_status_parent_chk = mysqli_query($connection, "SHOW COLUMNS FROM kp_sale_status LIKE 'parent'");
	if ($sale_status_parent_chk && mysqli_num_rows($sale_status_parent_chk) === 0) {
		mysqli_query($connection, "ALTER TABLE kp_sale_status ADD COLUMN parent INT NOT NULL DEFAULT 0");
	}
}
function ensure_item_stock_take_system() {
	global $connection;

	$item_table_chk = mysqli_query($connection, "SHOW TABLES LIKE 'kp_items'");
	if (!$item_table_chk || mysqli_num_rows($item_table_chk) === 0) {
		return;
	}

	$columns = array(
		'stock_take_type' => "ALTER TABLE kp_items ADD COLUMN stock_take_type TINYINT(1) NOT NULL DEFAULT 2",
		'empty_bottle_weight' => "ALTER TABLE kp_items ADD COLUMN empty_bottle_weight DECIMAL(10,2) NOT NULL DEFAULT 0",
		'full_bottle_weight' => "ALTER TABLE kp_items ADD COLUMN full_bottle_weight DECIMAL(10,2) NOT NULL DEFAULT 0",
		'bottle_mls' => "ALTER TABLE kp_items ADD COLUMN bottle_mls DECIMAL(10,2) NOT NULL DEFAULT 0",
		'tot_size' => "ALTER TABLE kp_items ADD COLUMN tot_size DECIMAL(10,2) NOT NULL DEFAULT 30"
	);

	foreach ($columns as $column => $statement) {
		$col_chk = mysqli_query($connection, "SHOW COLUMNS FROM kp_items LIKE '$column'");
		if ($col_chk && mysqli_num_rows($col_chk) === 0) {
			mysqli_query($connection, $statement);
		}
	}
}
function ensure_user_sales_limit_system() {
	global $connection;

	$user_table_chk = mysqli_query($connection, "SHOW TABLES LIKE 'kp_users'");
	if (!$user_table_chk || mysqli_num_rows($user_table_chk) === 0) {
		return;
	}

	$columns = array(
		'max_sale' => "ALTER TABLE kp_users ADD COLUMN max_sale DECIMAL(12,2) NOT NULL DEFAULT 0"
	);

	foreach ($columns as $column => $statement) {
		$col_chk = mysqli_query($connection, "SHOW COLUMNS FROM kp_users LIKE '$column'");
		if ($col_chk && mysqli_num_rows($col_chk) === 0) {
			mysqli_query($connection, $statement);
		}
	}
}
function ensure_runtime_table($table){
	global $connection;
	static $ensured = array();

	$table = trim((string)$table);
	if ($table === 'kp_counterS') {
		$table = 'kp_counters';
	}
	if ($table === '' || isset($ensured[$table])) {
		return;
	}

	if ($table === 'kp_staff_token_entries' || $table === 'kp_staff_token_wallet') {
		$ensured[$table] = true;

		$settings_check = mysqli_query($connection, "SHOW TABLES LIKE 'kp_settings_print'");
		if ($settings_check && mysqli_num_rows($settings_check) > 0) {
			ensure_token_system();
		}
		return;
	}

	if ($table === 'kp_split_message' || $table === 'split_message') {
		$ensured[$table] = true;
		ensure_split_message_system();
		return;
	}

	if ($table === 'kp_tax') {
		$ensured[$table] = true;
		ensure_tax_system();
		return;
	}

	if ($table === 'kp_po' || $table === 'kp_po_items' || $table === 'kp_po_status' || $table === 'kp_po_id') {
		$ensured[$table] = true;
		ensure_kug_purchase_system();
		return;
	}

	if ($table === 'kp_sales' || $table === 'kp_sale_status') {
		$ensured[$table] = true;
		ensure_sales_parent_system();
		return;
	}

	if ($table === 'kp_items') {
		$ensured[$table] = true;
		ensure_item_stock_take_system();
		return;
	}

	if ($table === 'kp_users') {
		$ensured[$table] = true;
		ensure_user_sales_limit_system();
		return;
	}

	$ensured[$table] = true;
}
function column_total($table,$column,$filter){
	global $connection;
	if ($table === 'kp_counterS') {
		$table = 'kp_counters';
	}
	ensure_runtime_table($table);
	try {
	    $total = mysqli_query($connection,"SELECT SUM($column) AS total FROM $table WHERE $filter");
	} catch (mysqli_sql_exception $e) {
		return 0;
	}
	if (!$total) {
		return 0;
	}
    $row = mysqli_fetch_assoc($total);
    if (!$row || is_null($row['total'])) {
    	$total =0;
    }else{
    	$total = $row['total'];
    }
    return $total;
} 
function column_count($table,$column,$filter){
	global $connection;
	if ($table === 'kp_counterS') {
		$table = 'kp_counters';
	}
	ensure_runtime_table($table);
	try {
    	$result = mysqli_query($connection,"SELECT $column FROM $table WHERE $filter");
	} catch (mysqli_sql_exception $e) {
		return 0;
	}
	if (!$result) {
		return 0;
	}
	$rows = mysqli_num_rows($result);
	return $rows;
}
function get_column($table,$column,$filter){ 	
	global $connection;
	if ($table === 'kp_counterS') {
		$table = 'kp_counters';
	}
	ensure_runtime_table($table);
	$sql="SELECT $column FROM $table WHERE $filter"; 
	try {
		$result = mysqli_query($connection,$sql);
	} catch (mysqli_sql_exception $e) {
		return 0;
	}
	if ($result) {
		$rows = mysqli_fetch_assoc($result);
		$num=column_count($table,$column,$filter);
		if($num<1){
			$col=0;
		}else{
			$col=$rows[$column];
		}
		return $col; 
	}else{
		return mysqli_error($connection);
	}
}
function Insertdata($table,$field,$data){
	global $connection;
	$field_values= implode(',',$field);
	$data_values=implode(',',$data);
	$sql="INSERT INTO ".$table." (".$field_values.") VALUES (".$data_values.")"; 
	//echo $sql;
	if ($connection->query($sql)) {
		return true;
	}else{
		return false;
	}
}
function Updatedata($table,$string){
	global $connection; 
	$sql="UPDATE ".$table." SET ".$string; 
	//echo $sql;
	if ($connection->query($sql)) {
		return true;
	}else{
		return false;
	}
}
function sep($nos){
	for ($i=0; $i <$nos ; $i++) {
		echo "*"; 
	}
}
function get_id($table,$column){
	global $connection; 
	$get_id = "SELECT ".$column." FROM ".$table."";
	$get_id_r = mysqli_query($connection,$get_id) or die("Could not get the order numbers");
	$row = mysqli_fetch_array($get_id_r);
	if (empty($row) || $row[$column]==0) {  
		$create_new_id = mysqli_query($connection,"INSERT INTO  $table($column) VALUES(1)") or die("Could not create a new order status");
		$id = 1;
	}else{ 
		$id = $row[$column]+1;
		$create_new_id = mysqli_query($connection,"UPDATE  $table  SET  $column ='$id'") or die("Could not create a new order status");
	}
	return $id;
}
function get_mac() {
    // Since shell access is disabled, generate a pseudo-unique ID
    return md5(php_uname() . gethostname());
}
function ensure_log_directory($path){
    if (!is_dir($path)) {
        @mkdir($path, 0775, true);
    }
    return is_dir($path) && is_writable($path);
}
function append_log_entry($basePath, $year, $month, $day, $line, $jsonPayload = null){
    $dir = rtrim($basePath, '/\\').DIRECTORY_SEPARATOR.$year.DIRECTORY_SEPARATOR.$month;
    if (!ensure_log_directory($dir)) {
        return false;
    }

    $txtFile = $dir.DIRECTORY_SEPARATOR.$day.'.txt';
    $prefix = is_file($txtFile) ? PHP_EOL : '';
    @file_put_contents($txtFile, $prefix.$line, FILE_APPEND);

    if ($jsonPayload !== null) {
        $jsonFile = $dir.DIRECTORY_SEPARATOR.$day.'.jsonl';
        $jsonPrefix = is_file($jsonFile) ? PHP_EOL : '';
        @file_put_contents($jsonFile, $jsonPrefix.json_encode($jsonPayload, JSON_UNESCAPED_SLASHES | JSON_UNESCAPED_UNICODE), FILE_APPEND);
    }
}
//creates logs of the system in a text file inside its own folder
function logs($action,$url){
    global $connection,$user_id;

    date_default_timezone_set("Africa/Nairobi");

    $shift = date('Y-m-d');
    if (isset($connection) && $connection instanceof mysqli) {
        $get_shift = mysqli_query($connection,"SELECT shift FROM kp_shift LIMIT 1");
        if ($get_shift && mysqli_num_rows($get_shift) > 0) {
            $row = mysqli_fetch_assoc($get_shift);
            if (!empty($row['shift'])) {
                $shift = $row['shift'];
            }
        }
    }

    $activeUserId = 0;
    if (!empty($user_id)) {
        $activeUserId = (int)$user_id;
    } elseif (!empty($_SESSION['user_id'])) {
        $activeUserId = (int)$_SESSION['user_id'];
    } elseif (!empty($_SESSION['id'])) {
        $activeUserId = (int)$_SESSION['id'];
    }

    $name = 'System';
    if ($activeUserId > 0 && function_exists('get_user')) {
        $resolvedName = get_user($activeUserId);
        if (!empty($resolvedName)) {
            $name = $resolvedName;
        }
    }

    $day = date('Y-m-d');
	$month = date('F');
	$year = date('Y');
    $timestamp = date('d-M-Y h:i:sa');
    $requestUri = isset($_SERVER['REQUEST_URI']) ? $_SERVER['REQUEST_URI'] : '';
    $scriptName = isset($_SERVER['SCRIPT_NAME']) ? $_SERVER['SCRIPT_NAME'] : '';
    $ipAddress = isset($_SERVER['REMOTE_ADDR']) ? $_SERVER['REMOTE_ADDR'] : '';
    $module = trim(str_replace('\\', '/', dirname($scriptName)), '/');

    $line = $timestamp
        ."|".$name
        ."|".$action
        ."|SHIFT=".$shift
        ."|USER_ID=".$activeUserId
        ."|MODULE=".$module
        ."|URI=".$requestUri
        ."|IP=".$ipAddress;

    $jsonPayload = array(
        'timestamp' => date('c'),
        'display_time' => $timestamp,
        'user_id' => $activeUserId,
        'user_name' => $name,
        'action' => $action,
        'shift' => $shift,
        'module' => $module,
        'uri' => $requestUri,
        'script' => $scriptName,
        'ip' => $ipAddress
    );

    if (!empty($url)) {
        append_log_entry($url, $year, $month, $day, $line, $jsonPayload);
    }

    $rootPath = dirname(__DIR__).DIRECTORY_SEPARATOR.'logs';
    append_log_entry($rootPath, $year, $month, $day, $line, $jsonPayload);
}
////////////////////////////////////////////
/////////////////END GENERAL FUNCTIONS////////
////////////////////////////////////////////

/**
 * Return the system print mode:
 * 1 = Direct browser print
 * 2 = Browser print server tab
 * 3 = Windows print server worker
 * 4 = QZ Tray browser worker
 * Also ensures kp_print_queue table and print_mode column exist.
 */
function get_print_mode() {
    global $connection;
    // Ensure print queue table exists
    mysqli_query($connection,
        "CREATE TABLE IF NOT EXISTS kp_print_queue (
           queue_id   INT NOT NULL AUTO_INCREMENT,
           sale_id    INT NOT NULL DEFAULT 0,
           print_type VARCHAR(55) NOT NULL DEFAULT 'bill',
           cntr_id    INT NOT NULL DEFAULT 0,
           status     TINYINT(1) NOT NULL DEFAULT 0,
           created_at TIMESTAMP NOT NULL DEFAULT CURRENT_TIMESTAMP,
           PRIMARY KEY (queue_id),
           INDEX idx_cntr_status (cntr_id, status)
         )"
    );
    $queue_type_chk = mysqli_query($connection, "SHOW COLUMNS FROM kp_print_queue LIKE 'print_type'");
    if ($queue_type_chk && ($queue_type_row = mysqli_fetch_assoc($queue_type_chk))) {
        $queue_type = strtolower((string)($queue_type_row['Type'] ?? ''));
        if (strpos($queue_type, 'varchar(55)') === false) {
            mysqli_query($connection, "ALTER TABLE kp_print_queue CHANGE print_type print_type VARCHAR(55) CHARACTER SET utf8mb4 COLLATE utf8mb4_general_ci NOT NULL DEFAULT 'bill'");
        }
    }
    // Ensure print_mode column exists
    $col_chk = mysqli_query($connection, "SHOW COLUMNS FROM kp_settings_print LIKE 'print_mode'");
    if ($col_chk && mysqli_num_rows($col_chk) === 0) {
        mysqli_query($connection,
            "ALTER TABLE kp_settings_print ADD COLUMN print_mode TINYINT(1) NOT NULL DEFAULT 1"
        );
    }
    $r = mysqli_query($connection, "SELECT print_mode FROM kp_settings_print WHERE print_doc=1");
    if ($r && ($row = mysqli_fetch_assoc($r)) && isset($row['print_mode'])) {
        return (int)$row['print_mode'];
    }
    return 1; // default = direct print
}

function ensure_print_template_column() {
    global $connection;
    $col_chk = mysqli_query($connection, "SHOW COLUMNS FROM kp_settings_print LIKE 'print_template'");
    if ($col_chk && mysqli_num_rows($col_chk) === 0) {
        mysqli_query(
            $connection,
            "ALTER TABLE kp_settings_print ADD COLUMN print_template TINYINT(1) NOT NULL DEFAULT 1"
        );
    }
}

function get_print_template() {
    global $connection;
    ensure_print_template_column();
    $r = mysqli_query($connection, "SELECT print_template FROM kp_settings_print WHERE print_doc=1 LIMIT 1");
    if ($r && ($row = mysqli_fetch_assoc($r)) && isset($row['print_template'])) {
        $template = (int)$row['print_template'];
        if ($template === 2) {
            return 2;
        }
    }
    return 1;
}

function ensure_counter_close_rule_column() {
    global $connection;
    $col_chk = mysqli_query($connection, "SHOW COLUMNS FROM kp_settings_print LIKE 'counter_close_rule'");
    if ($col_chk && mysqli_num_rows($col_chk) === 0) {
        mysqli_query(
            $connection,
            "ALTER TABLE kp_settings_print ADD COLUMN counter_close_rule TINYINT(1) NOT NULL DEFAULT 1"
        );
    }
}

function get_counter_close_rule() {
    global $connection;
    ensure_counter_close_rule_column();
    $r = mysqli_query($connection, "SELECT counter_close_rule FROM kp_settings_print WHERE print_doc=1 LIMIT 1");
    if ($r && ($row = mysqli_fetch_assoc($r)) && isset($row['counter_close_rule'])) {
        $rule = (int)$row['counter_close_rule'];
        if ($rule === 2) {
            return 2;
        }
    }
    return 1;
}

function counter_close_requires_all_bills_cleared() {
    return get_counter_close_rule() === 1;
}

function ensure_receipt_divider_settings() {
    global $connection;
    if (!isset($connection) || !$connection) {
        return;
    }
    $columns = array(
        'receipt_divider_symbol' => "ALTER TABLE kp_settings_print ADD COLUMN receipt_divider_symbol VARCHAR(8) NOT NULL DEFAULT '*'",
        'receipt_divider_count' => "ALTER TABLE kp_settings_print ADD COLUMN receipt_divider_count INT NOT NULL DEFAULT 50"
    );
    foreach ($columns as $column => $sql) {
        $chk = mysqli_query($connection, "SHOW COLUMNS FROM kp_settings_print LIKE '$column'");
        if ($chk && mysqli_num_rows($chk) === 0) {
            mysqli_query($connection, $sql);
        }
    }
}

function kp_receipt_divider_settings($db = null) {
    static $cached = null;
    if ($cached !== null) {
        return $cached;
    }
    if ($db === null) {
        global $connection;
        $db = $connection;
    }
    $defaults = array(
        'symbol' => '*',
        'count' => 50,
    );
    if (!$db) {
        $cached = $defaults;
        return $cached;
    }
    ensure_receipt_divider_settings();
    $row = mysqli_fetch_assoc(mysqli_query($db, "SELECT receipt_divider_symbol, receipt_divider_count FROM kp_settings_print WHERE print_doc=1 LIMIT 1")) ?: array();
    $symbol = trim((string)($row['receipt_divider_symbol'] ?? $defaults['symbol']));
    $count = (int)($row['receipt_divider_count'] ?? $defaults['count']);
    if ($symbol === '') {
        $symbol = $defaults['symbol'];
    }
    if (function_exists('mb_substr')) {
        $symbol = mb_substr($symbol, 0, 4);
    } else {
        $symbol = substr($symbol, 0, 4);
    }
    if ($count < 5) {
        $count = 5;
    }
    if ($count > 120) {
        $count = 120;
    }
    $cached = array(
        'symbol' => $symbol,
        'count' => $count,
    );
    return $cached;
}

function kp_receipt_divider_text($db = null) {
    $cfg = kp_receipt_divider_settings($db);
    return str_repeat((string)$cfg['symbol'], (int)$cfg['count']);
}

function ensure_token_system() {
    global $connection;

    $token_columns = array(
        "token_enabled TINYINT(1) NOT NULL DEFAULT 2",
        "token_mode TINYINT(1) NOT NULL DEFAULT 1",
        "token_piece_value DECIMAL(10,2) NOT NULL DEFAULT 0",
        "token_percent_value DECIMAL(10,2) NOT NULL DEFAULT 0",
        "token_brackets TEXT NULL"
    );
    foreach ($token_columns as $definition) {
        $parts = explode(' ', trim($definition), 2);
        $column = $parts[0];
        $col_chk = mysqli_query($connection, "SHOW COLUMNS FROM kp_settings_print LIKE '$column'");
        if ($col_chk && mysqli_num_rows($col_chk) === 0) {
            mysqli_query($connection, "ALTER TABLE kp_settings_print ADD COLUMN $definition");
        }
    }

    $sale_token_chk = mysqli_query($connection, "SHOW COLUMNS FROM kp_sales LIKE 'token_points'");
    if ($sale_token_chk && mysqli_num_rows($sale_token_chk) === 0) {
        mysqli_query($connection, "ALTER TABLE kp_sales ADD COLUMN token_points DECIMAL(10,2) NOT NULL DEFAULT 0");
    }

    $item_token_chk = mysqli_query($connection, "SHOW COLUMNS FROM kp_sale_items LIKE 'token_points'");
    if ($item_token_chk && mysqli_num_rows($item_token_chk) === 0) {
        mysqli_query($connection, "ALTER TABLE kp_sale_items ADD COLUMN token_points DECIMAL(10,2) NOT NULL DEFAULT 0");
    }

    mysqli_query($connection, "CREATE TABLE IF NOT EXISTS kp_staff_token_wallet (
        user_id INT NOT NULL,
        total_tokens DECIMAL(10,2) NOT NULL DEFAULT 0,
        updated_at TIMESTAMP NOT NULL DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
        PRIMARY KEY (user_id)
    )");

    mysqli_query($connection, "CREATE TABLE IF NOT EXISTS kp_staff_token_entries (
        entry_id INT NOT NULL AUTO_INCREMENT,
        user_id INT NOT NULL DEFAULT 0,
        shift_date DATE NULL,
        sale_id INT NOT NULL DEFAULT 0,
        item_id INT NOT NULL DEFAULT 0,
        attr_id INT NULL DEFAULT NULL,
        token_key VARCHAR(120) NOT NULL DEFAULT '',
        token_mode TINYINT(1) NOT NULL DEFAULT 1,
        token_amount DECIMAL(10,2) NOT NULL DEFAULT 0,
        snapshot_amount DECIMAL(10,2) NOT NULL DEFAULT 0,
        snapshot_qty DECIMAL(10,2) NOT NULL DEFAULT 0,
        item_name VARCHAR(255) NOT NULL DEFAULT '',
        source_action VARCHAR(60) NOT NULL DEFAULT '',
        notes VARCHAR(255) NOT NULL DEFAULT '',
        status TINYINT(1) NOT NULL DEFAULT 1,
        created_at TIMESTAMP NOT NULL DEFAULT CURRENT_TIMESTAMP,
        PRIMARY KEY (entry_id),
        KEY idx_user_shift (user_id, shift_date),
        KEY idx_sale (sale_id),
        KEY idx_token_key (token_key)
    )");
}

function get_token_config() {
    global $connection;
    ensure_token_system();

    $defaults = array(
        'token_enabled' => 2,
        'token_mode' => 1,
        'token_piece_value' => 0,
        'token_percent_value' => 0,
        'token_brackets' => "0,100,5\n100,250,12\n250,,20"
    );

    $r = mysqli_query($connection, "SELECT token_enabled, token_mode, token_piece_value, token_percent_value, token_brackets FROM kp_settings_print WHERE print_doc=1 LIMIT 1");
    if ($r && ($row = mysqli_fetch_assoc($r))) {
        return array_merge($defaults, $row);
    }
    return $defaults;
}

function token_system_enabled() {
    $cfg = get_token_config();
    return (int)$cfg['token_enabled'] === 1;
}

function parse_token_brackets($raw) {
    $lines = preg_split('/\r\n|\r|\n/', (string)$raw);
    $rows = array();
    foreach ($lines as $line) {
        $line = trim($line);
        if ($line === '') {
            continue;
        }
        $parts = preg_split('/\s*[,|]\s*/', $line);
        if (count($parts) < 3) {
            continue;
        }
        $min = is_numeric($parts[0]) ? (float)$parts[0] : 0;
        $max = trim((string)$parts[1]);
        $maxValue = ($max === '' || !is_numeric($max)) ? null : (float)$max;
        $token = is_numeric($parts[2]) ? (float)$parts[2] : 0;
        $rows[] = array(
            'min' => $min,
            'max' => $maxValue,
            'token' => $token
        );
    }

    usort($rows, function ($a, $b) {
        if ($a['min'] == $b['min']) {
            return 0;
        }
        return ($a['min'] < $b['min']) ? -1 : 1;
    });

    return $rows;
}

function token_bracket_amount($saleAmount, $rawBrackets) {
    $brackets = parse_token_brackets($rawBrackets);
    foreach ($brackets as $row) {
        $min = (float)$row['min'];
        $max = $row['max'];
        if ($saleAmount < $min) {
            continue;
        }
        if ($max === null || $saleAmount < (float)$max) {
            return (float)$row['token'];
        }
    }
    return 0;
}

function rebuild_staff_token_wallet($user_id = 0) {
    global $connection;
    ensure_token_system();
    $where = ((int)$user_id > 0) ? "WHERE user_id='".(int)$user_id."'" : "";
    $users = mysqli_query($connection, "SELECT DISTINCT user_id FROM kp_staff_token_entries $where");
    if (!$users) {
        return;
    }
    while ($row = mysqli_fetch_assoc($users)) {
        $uid = (int)$row['user_id'];
        $total = (float)column_total('kp_staff_token_entries', 'token_amount', "user_id='$uid' AND status=1");
        mysqli_query($connection, "INSERT INTO kp_staff_token_wallet(user_id,total_tokens) VALUES('$uid','$total') ON DUPLICATE KEY UPDATE total_tokens='$total'");
    }
}

function get_user_token_total($user_id) {
    ensure_token_system();
    return (float)get_column('kp_staff_token_wallet', 'total_tokens', "user_id='".(int)$user_id."'");
}

function get_user_shift_token_total($user_id, $shift) {
    ensure_token_system();
    return (float)column_total('kp_staff_token_entries', 'token_amount', "user_id='".(int)$user_id."' AND shift_date='$shift' AND status=1");
}

function build_sale_token_targets($sale_id) {
    global $connection;
    $sale_id = (int)$sale_id;
    $targets = array();
    if ($sale_id < 1 || !token_system_enabled()) {
        return $targets;
    }

    $cfg = get_token_config();
    $sale_q = mysqli_query($connection, "SELECT sale_id,user_id,shift,amount FROM kp_sales WHERE sale_id='$sale_id' LIMIT 1");
    if (!$sale_q || mysqli_num_rows($sale_q) < 1) {
        return $targets;
    }
    $sale = mysqli_fetch_assoc($sale_q);
    $saleAmount = (float)$sale['amount'];
    $mode = (int)$cfg['token_mode'];

    if ($mode === 1) {
        $pieceValue = (float)$cfg['token_piece_value'];
        if ($pieceValue <= 0) {
            return $targets;
        }
        $items = mysqli_query($connection, "SELECT item_id,attr_id,hour,counter,qty,total,price FROM kp_sale_items WHERE sale_id='$sale_id' ORDER BY item_id ASC");
        while ($item = mysqli_fetch_assoc($items)) {
            $qty = (float)$item['qty'];
            $tokenAmount = round($qty * $pieceValue, 2);
            $attrId = isset($item['attr_id']) && $item['attr_id'] !== null ? (int)$item['attr_id'] : 0;
            $tokenKey = 'item:'.$sale_id.':'.$item['item_id'].':'.$attrId.':'.$item['hour'].':'.$item['counter'];
            $targets[$tokenKey] = array(
                'user_id' => (int)$sale['user_id'],
                'shift' => $sale['shift'],
                'sale_id' => $sale_id,
                'item_id' => (int)$item['item_id'],
                'attr_id' => $attrId > 0 ? $attrId : null,
                'token_key' => $tokenKey,
                'token_mode' => 1,
                'token_amount' => $tokenAmount,
                'snapshot_amount' => (float)$item['total'],
                'snapshot_qty' => $qty,
                'item_name' => get_item($item['item_id']),
                'notes' => 'Pieces mode'
            );
        }
        return $targets;
    }

    if ($mode === 2) {
        $tokenAmount = round(token_bracket_amount($saleAmount, $cfg['token_brackets']), 2);
        $targets['sale:'.$sale_id] = array(
            'user_id' => (int)$sale['user_id'],
            'shift' => $sale['shift'],
            'sale_id' => $sale_id,
            'item_id' => 0,
            'attr_id' => null,
            'token_key' => 'sale:'.$sale_id,
            'token_mode' => 2,
            'token_amount' => $tokenAmount,
            'snapshot_amount' => $saleAmount,
            'snapshot_qty' => 0,
            'item_name' => '',
            'notes' => 'Bracket rate mode'
        );
        return $targets;
    }

    $percentValue = (float)$cfg['token_percent_value'];
    $tokenAmount = round(($saleAmount * $percentValue) / 100, 2);
    $targets['sale:'.$sale_id] = array(
        'user_id' => (int)$sale['user_id'],
        'shift' => $sale['shift'],
        'sale_id' => $sale_id,
        'item_id' => 0,
        'attr_id' => null,
        'token_key' => 'sale:'.$sale_id,
        'token_mode' => 3,
        'token_amount' => $tokenAmount,
        'snapshot_amount' => $saleAmount,
        'snapshot_qty' => 0,
        'item_name' => '',
        'notes' => 'Percentage mode'
    );
    return $targets;
}

function sync_sale_tokens($sale_id, $sourceAction = 'sale_sync') {
    global $connection;
    ensure_token_system();
    $sale_id = (int)$sale_id;
    if ($sale_id < 1) {
        return false;
    }

    $sale_q = mysqli_query($connection, "SELECT sale_id,user_id,shift FROM kp_sales WHERE sale_id='$sale_id' LIMIT 1");
    if (!$sale_q || mysqli_num_rows($sale_q) < 1) {
        return false;
    }
    $sale = mysqli_fetch_assoc($sale_q);
    $user_id = (int)$sale['user_id'];
    $shift = $sale['shift'];

    $targets = build_sale_token_targets($sale_id);
    $existing = array();
    $existing_q = mysqli_query($connection, "SELECT token_key,SUM(token_amount) AS total FROM kp_staff_token_entries WHERE sale_id='$sale_id' AND status=1 GROUP BY token_key");
    while ($existing_q && ($row = mysqli_fetch_assoc($existing_q))) {
        $existing[$row['token_key']] = (float)$row['total'];
    }

    $keys = array_unique(array_merge(array_keys($targets), array_keys($existing)));
    foreach ($keys as $key) {
        $desired = isset($targets[$key]) ? (float)$targets[$key]['token_amount'] : 0;
        $current = isset($existing[$key]) ? (float)$existing[$key] : 0;
        $delta = round($desired - $current, 2);
        if (abs($delta) < 0.001) {
            continue;
        }
        $target = isset($targets[$key]) ? $targets[$key] : array(
            'user_id' => $user_id,
            'shift' => $shift,
            'sale_id' => $sale_id,
            'item_id' => 0,
            'attr_id' => null,
            'token_key' => $key,
            'token_mode' => 0,
            'snapshot_amount' => 0,
            'snapshot_qty' => 0,
            'item_name' => '',
            'notes' => 'Token reversal sync'
        );
        $attrValue = $target['attr_id'] === null ? "NULL" : "'".(int)$target['attr_id']."'";
        $itemName = mysqli_real_escape_string($connection, (string)$target['item_name']);
        $notes = mysqli_real_escape_string($connection, (string)$target['notes']);
        $action = mysqli_real_escape_string($connection, $sourceAction);
        $tokenKey = mysqli_real_escape_string($connection, $key);
        mysqli_query($connection, "INSERT INTO kp_staff_token_entries(
            user_id,shift_date,sale_id,item_id,attr_id,token_key,token_mode,token_amount,snapshot_amount,snapshot_qty,item_name,source_action,notes,status
        ) VALUES(
            '".$target['user_id']."','".$target['shift']."','".$target['sale_id']."','".$target['item_id']."',$attrValue,'$tokenKey','".$target['token_mode']."','$delta','".$target['snapshot_amount']."','".$target['snapshot_qty']."','$itemName','$action','$notes',1
        )");
    }

    $saleTotal = 0;
    foreach ($targets as $target) {
        $saleTotal += (float)$target['token_amount'];
    }
    mysqli_query($connection, "UPDATE kp_sales SET token_points='".round($saleTotal, 2)."' WHERE sale_id='$sale_id'");
    mysqli_query($connection, "UPDATE kp_sale_items SET token_points='0' WHERE sale_id='$sale_id'");
    foreach ($targets as $target) {
        if ((int)$target['token_mode'] !== 1 || (int)$target['item_id'] < 1) {
            continue;
        }
        $attrFilter = $target['attr_id'] === null ? "attr_id IS NULL" : "attr_id='".(int)$target['attr_id']."'";
        mysqli_query($connection, "UPDATE kp_sale_items SET token_points='".$target['token_amount']."' WHERE sale_id='$sale_id' AND item_id='".(int)$target['item_id']."' AND $attrFilter");
    }

    rebuild_staff_token_wallet($user_id);
    return true;
}

function reverse_sale_tokens($sale_id, $sourceAction = 'sale_void') {
    global $connection;
    ensure_token_system();
    $sale_id = (int)$sale_id;
    if ($sale_id < 1) {
        return false;
    }

    $sale_q = mysqli_query($connection, "SELECT sale_id,user_id,shift FROM kp_sales WHERE sale_id='$sale_id' LIMIT 1");
    if (!$sale_q || mysqli_num_rows($sale_q) < 1) {
        return false;
    }
    $sale = mysqli_fetch_assoc($sale_q);
    $user_id = (int)$sale['user_id'];
    $shift = $sale['shift'];

    $existing_q = mysqli_query($connection, "SELECT token_key,item_id,attr_id,token_mode,item_name,SUM(token_amount) AS total FROM kp_staff_token_entries WHERE sale_id='$sale_id' AND status=1 GROUP BY token_key,item_id,attr_id,token_mode,item_name");
    while ($existing_q && ($row = mysqli_fetch_assoc($existing_q))) {
        $total = round((float)$row['total'], 2);
        if (abs($total) < 0.001) {
            continue;
        }
        $attrValue = $row['attr_id'] === null ? "NULL" : "'".(int)$row['attr_id']."'";
        $tokenKey = mysqli_real_escape_string($connection, (string)$row['token_key']);
        $itemName = mysqli_real_escape_string($connection, (string)$row['item_name']);
        $action = mysqli_real_escape_string($connection, $sourceAction);
        mysqli_query($connection, "INSERT INTO kp_staff_token_entries(
            user_id,shift_date,sale_id,item_id,attr_id,token_key,token_mode,token_amount,snapshot_amount,snapshot_qty,item_name,source_action,notes,status
        ) VALUES(
            '$user_id','$shift','$sale_id','".(int)$row['item_id']."',$attrValue,'$tokenKey','".(int)$row['token_mode']."','".(-1 * $total)."',0,0,'$itemName','$action','Sale token reversal',1
        )");
    }

    mysqli_query($connection, "UPDATE kp_sales SET token_points='0' WHERE sale_id='$sale_id'");
    mysqli_query($connection, "UPDATE kp_sale_items SET token_points='0' WHERE sale_id='$sale_id'");
    rebuild_staff_token_wallet($user_id);
    return true;
}
function thesion_page($folder){
	global $connection; 
	$user_id = $_SESSION['user_id']; 
	$mac_address=get_mac();
	$page= $folder.basename($_SERVER['PHP_SELF']);
	$create_thesion= mysqli_query($connection,"INSERT INTO kp_thesion_front(user_id,current_page,datime,comp_address) VALUES('$user_id','$page',CURRENT_TIMESTAMP,'$mac_address') ON DUPLICATE KEY UPDATE current_page='$page',datime=CURRENT_TIMESTAMP,comp_address='$mac_address'") or die(mysqli_error($connection));
}
function clear_thesion_ids($user_id){
	global $connection; 
	$delete_thesion = mysqli_query($connection,"UPDATE kp_thesion_front SET sale_id=0 WHERE user_id='$user_id'") or die(mysqli_error($connection));
}
function clear_thesion($user_id){
	global $connection; 
	$delete_thesion = mysqli_query($connection,"DELETE FROM kp_thesion_front  WHERE user_id='$user_id'") or die(mysqli_error($connection));
}
function update_thesion($column,$value,$page){
	global $connection;
	$user_id=$_SESSION['user_id']; 
	$mac_address=get_mac(); 
	$create_thesion= mysqli_query($connection,"INSERT INTO kp_thesion(user_id,$column,datime,comp_address) VALUES('$user_id','$value',CURRENT_TIMESTAMP,'$mac_address') ON DUPLICATE KEY  UPDATE   $column='$value',current_page='$page',datime=CURRENT_TIMESTAMP,comp_address='$mac_address'") or die(mysqli_error($connection));
}
/* backup the db OR just a table */
function backup_tables($tables = '*'){
	global $connection; 
	$backuptime = date('YMd_His');
	$backupdatabasefiletime = date('Y-m-d H:i:s');
	$username = $_SESSION['user_id'];
	$ipaddress = $_SERVER["REMOTE_ADDR"];
	
	$backupdatabasefilename = $backuptime.'.sql';
	$backupdatabasefiletime =  $backupdatabasefiletime;
	
	$query1db = "insert into kp_backdb (backupfilename, backupfiledate, username, ipaddress) 
	values ('$backupdatabasefilename', '$backupdatabasefiletime', '$username', '$ipaddress')";
	$exec1db = mysqli_query($connection,$query1db) or die ("Error in Query1db".mysqli_error($connection));

	$return = '';
	//get all of the tables
	if($tables == '*')
	{
		$tables = array();
		$result = mysqli_query($connection, 'SHOW TABLES');
		while($row = mysqli_fetch_row($result))
		{
			$tables[] = $row[0];
		}
	}
	else
	{
		$tables = is_array($tables) ? $tables : explode(',',$tables);
	}
	
	//cycle through
	foreach($tables as $table)
	{
		$result = mysqli_query($connection, 'SELECT * FROM '.$table);
		$num_fields = mysqli_num_fields($result);
		
		//$return.= 'DROP TABLE '.$table.';';
		$row2 = mysqli_fetch_row(mysqli_query($connection, 'SHOW CREATE TABLE '.$table));
		$return.= "\n\n".$row2[1].";\n\n";
		
		for ($i = 0; $i < $num_fields; $i++) 
		{
			while($row = mysqli_fetch_row($result))
			{
				$return.= 'INSERT INTO '.$table.' VALUES(';
				for($j=0; $j<$num_fields; $j++) 
				{
					// PHP 8.1+ deprecates passing NULL to addslashes().
					// Preserve the existing export format while treating NULL as empty.
					$row[$j] = addslashes((string)($row[$j] ?? ''));
					
					//Slash after and before double quote is compulsory.
					$patterns = "/\n/";
					$replacements = "/\\n/";
					$string = $row[$j]; 
					
					//$row[$j] = preg_replace("\n","\\n",$row[$j]); 
					$row[$j] = preg_replace($patterns, $replacements, $string);
					
					if (isset($row[$j])) { $return.= '"'.$row[$j].'"' ; } else { $return.= '""'; }
					if ($j<($num_fields-1)) { $return.= ','; }
				}
				$return.= ");\n";
			}
		}
		$return.="\n\n\n";
	}
	$handle = fopen('../../assets/back/db/'.$backuptime.'.sql','w+'); //z given to list folders at the end.
	fwrite($handle,$return);
	fclose($handle);
}
function insert_payments2(){
    global $connection, $balance,  $user_id,$arr,$sale_amount,$shift,$tendered,$invoices;
    $array = array();
    $array2 = array(); 
    foreach($arr as $i) {
        $ref="moderef".$i;
        $mode="mode".$i;
        $refv="ref".$i;
        $modename="name".$i; 
        if (empty($_POST[$refv]) ) {
            $moderef="";
        }else{ 
            $moderef=",".$ref;
        }
        $array[] =  $mode.$moderef;

        if (empty($_POST[$refv])) {
            $modeval=""; 
        }else{ 
            $modeval=",'".$_POST[$refv]."'";
        }
        $array2[]=$_POST[$modename]-$balance.$modeval;
    }
        $pay_id=pay_id(); 
        $field_values = array("pay_id","sale_id","user_id","tendered","pay_date","paid_total","type");
        $result=array_merge( $field_values,$array);

        $data_values = array($pay_id,"'".$invoices."'",$user_id ,$tendered,"'".$shift."'",$sale_amount,1);
        $result2=array_merge( $data_values,$array2); 

        if(Insertdata("kp_sale_payment", $result, $result2)){
            return true;
        }else{
            return false;
        }
} 
function insert_payments(){
    global $connection, $balance, $sale_id, $user_id,$arr,$sale_amount,$shift,$tendered,$cust_id;
    $array = array();
    $array2 = array();
    $counter=get_column('kp_sales','counter',"sale_id='$sale_id'");
    $oshift=get_column('kp_sales','shift',"sale_id='$sale_id'");
    foreach($arr as $i) {
        $ref="moderef".$i;
        $mode="mode".$i;
        $refv="ref".$i;
        $modename="name".$i; 
        if (empty($_POST[$refv]) ) {
            $moderef="";
        }else{ 
            $moderef=",".$ref;
        }
        $array[] =  $mode.$moderef;

        if (empty($_POST[$refv])) {
            $modeval=""; 
        }else{ 
            $modeval=",'".$_POST[$refv]."'";
        }
        $array2[]=$_POST[$modename]-$balance.$modeval;
    }
        $pay_id=pay_id(); 
        $field_values = array("pay_id","sale_id","user_id","cust_id","tendered","balance","ord_date","pay_date","paid_total","type","counter");
        $result=array_merge( $field_values,$array);

        $data_values = array($pay_id,$sale_id,$user_id,$cust_id,$tendered,0,"'".$shift."'","'".$oshift."'",$sale_amount,1,$counter);
        $result2=array_merge( $data_values,$array2); 

        if(Insertdata("kp_sale_payment", $result, $result2)){
            return true;
        }else{
            return false;
        }
} 
function cust_trans($cust_id,$trans,$trans_id,$amount){
    global $connection;
    $user_id = $_SESSION['user_id'];
    $created_check = mysqli_query($connection,"SHOW COLUMNS FROM kp_cust_hist LIKE 'created_at'") or die(mysqli_error($connection));
    if ($created_check && mysqli_num_rows($created_check) < 1) {
        mysqli_query($connection,"ALTER TABLE kp_cust_hist ADD COLUMN created_at TIMESTAMP NOT NULL DEFAULT CURRENT_TIMESTAMP") or die(mysqli_error($connection));
    }
    $get_cust = "SELECT * FROM kp_cust_hist WHERE cust_id='$cust_id' AND  trans_id='$trans_id' AND user_id='$user_id' AND amount='$amount' AND trans_date=CURRENT_DATE ";
    $get_cust_r = mysqli_query($connection,$get_cust) or die("Could not get customer history".mysqli_error($connection));
    $row = mysqli_fetch_array($get_cust_r);
    if (empty($row)) {

    $bal=get_column('kp_customers','bal',"cust_id='$cust_id'");   
    $create_hist = mysqli_query($connection,"INSERT INTO kp_cust_hist(cust_id,trans,trans_id,user_id,amount,cf_bal,trans_date) VALUES('$cust_id','$trans','$trans_id','$user_id','$amount','$bal',CURRENT_DATE)") or die("Could not create a new order status".mysqli_error($connection));
        if ($create_hist) {
            return true;
        }else{
            return false;
        }
    }else{
        return true;
    }
}
function pay_id(){ 
    global $connection;
    $get_id=mysqli_query($connection,"SELECT pay_id FROM kp_pay_id");
    if (mysqli_num_rows($get_id) > 0) {
        $create_pay = mysqli_query($connection,"UPDATE kp_pay_id SET pay_id=pay_id+1") or die("Could not create a new pay id");
        $get_pay_id= mysqli_query($connection,"SELECT pay_id FROM kp_pay_id") or die("Could not get the last pay id");
        $pow = mysqli_fetch_array($get_pay_id);
        $pay_id = $pow['pay_id'];
    }else{
        $create_new_id = mysqli_query($connection,"INSERT INTO kp_pay_id(pay_id) VALUES(1)") or die(mysqli_error($connection)." Could not create a new order status");
        $pay_id= 1;
    }
    return $pay_id;
}
function user_counter_status($user_id){
    global $connection;
    $get_user_counter = mysqli_query($connection,"SELECT counter FROM kp_users WHERE user_id='$user_id'") or die("Could not get counter".mysqli_error($connection));
    $gs = mysqli_fetch_assoc($get_user_counter);
    $counter=$gs['counter'];
    if ($counter==0) {
        $status=0;
    }else{
        $get_counter_status = mysqli_query($connection,"SELECT take_status FROM kp_counters WHERE cntr_id='$counter'") or die("Could not get counter counter status".mysqli_error($connection));
        $st = mysqli_fetch_assoc($get_counter_status);
        $status=$st['take_status'];
    } 
    return $status;
}
function update_item_recipe($item_id){
	global $connection;
	$validate_item = mysqli_query($connection, "SELECT raw_id FROM kp_recipe WHERE  item_id='$item_id' ");
    if (mysqli_num_rows($validate_item)== 0) { 
    	$update_item = mysqli_query($connection,"UPDATE kp_items SET recipe=2 WHERE item_id='$item_id'") or die(mysqli_error($connection));
    }else{
    	$update_item = mysqli_query($connection,"UPDATE kp_items SET recipe=1,stock=2,portions=0,port_status=2 WHERE item_id='$item_id'") or die(mysqli_error($connection));
    }
}
