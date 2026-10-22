<?php  
$filename = "../../inc/gen_funcs.php";
if (file_exists("inc/gen_funcs.php")){
    include_once('inc/gen_funcs.php');
}elseif (file_exists("../inc/gen_funcs.php")){
    include_once('../inc/gen_funcs.php');
}elseif (file_exists($filename)){
    include_once('../../inc/gen_funcs.php');
}elseif (file_exists("../../../inc/gen_funcs.php")){
    include_once('../../../inc/gen_funcs.php');
}else{
    throw new RuntimeException('Unable to load inc/gen_funcs.php');
}
if (!function_exists('sales_thesion')) {
	function sales_thesion($folder,$sale_id,$counter,$st){
		global $connection; 
		$user_id = $_SESSION['user_id']; 
		$shift = get_column('kp_shift','shift',"1");
		$mac_address=get_mac();
		$page= $folder.basename($_SERVER['PHP_SELF']);
		$create_thesion= mysqli_query($connection,"INSERT INTO kp_thesion_front(user_id,sale_id,status,cntr_id,current_page,shift,datime,comp_address) VALUES('$user_id','$sale_id','$st','$counter','$page','$shift',CURRENT_TIMESTAMP,'$mac_address') ON DUPLICATE KEY UPDATE current_page='$page',shift='$shift',datime=CURRENT_TIMESTAMP,comp_address='$mac_address',sale_id='$sale_id',status='$st',cntr_id='$counter'") or die(mysqli_error($connection));
	}
}
if (!function_exists('kw_user_max_sale')) {
	function kw_user_max_sale($connection, $user_id) {
		$user_id = (int)$user_id;
		$maxSaleColumn = mysqli_query($connection, "SHOW COLUMNS FROM kp_users LIKE 'max_sale'");
		if (!$maxSaleColumn || mysqli_num_rows($maxSaleColumn) < 1) {
			return 0;
		}
		return (float)get_column('kp_users', 'max_sale', "user_id='$user_id'");
	}
}
if (!function_exists('kw_parent_sale_chain_total')) {
	function kw_parent_sale_chain_total($connection, $parent_sale_id, $pending_amount = 0) {
		$parent_sale_id = (int)$parent_sale_id;
		$pending_amount = (float)$pending_amount;
		if ($parent_sale_id < 1) {
			return max(0, $pending_amount);
		}
		$running_total = (float)column_total('kp_sales', 'amount', "sale_id='$parent_sale_id' OR parent='$parent_sale_id'");
		return $running_total + max(0, $pending_amount);
	}
}
if (!function_exists('kw_open_parent_owner')) {
	function kw_open_parent_owner($connection, $parent_sale_id) {
		$parent_sale_id = (int)$parent_sale_id;
		if ($parent_sale_id < 1) {
			return 0;
		}
		$query = mysqli_query(
			$connection,
			"SELECT user_id
			 FROM kp_shift_sales_open
			 WHERE sale_id='$parent_sale_id' AND status=1
			 ORDER BY open_id DESC
			 LIMIT 1"
		);
		if ($query && mysqli_num_rows($query) > 0) {
			$row = mysqli_fetch_assoc($query);
			return (int)$row['user_id'];
		}
		return 0;
	}
}
if (!function_exists('kw_open_parent_locked_message')) {
	function kw_open_parent_locked_message($locked_user_id) {
		$locked_user_id = (int)$locked_user_id;
		if ($locked_user_id < 1) {
			return 'This open bill is already locked by another user.';
		}
		return 'This open bill is locked to ' . strtoupper(get_user($locked_user_id)) . '.';
	}
}
function hhr(){
	global $connection;
	date_default_timezone_set("Africa/Nairobi");
	$siku=date('l');
	$current=date('H:i');   
	$get_hhr = mysqli_query($connection,"SELECT COUNT(hhr_id) as num FROM kp_hhr_set WHERE day='$siku' AND '$current' BETWEEN start_time AND end_time") or die("Could not get hhr settings".mysqli_error($connection));
	$gs = mysqli_fetch_assoc($get_hhr);

	if ($gs['num']==0) {
        $hhr=1;
    }else{
       $hhr=2;
    }
    return $hhr;
} 
function last_id(){
	$user_id = isset($_SESSION['user_id']) ? (int)$_SESSION['user_id'] : 0;
	if (function_exists('bill_repo_last_id')) {
		return bill_repo_last_id($user_id);
	}
	global $connection;	
	kw_ensure_sale_status_schema($connection);
	$check_order=mysqli_query($connection,"SELECT sale_id FROM kp_sale_status WHERE  user_id='$user_id' AND status=2");
	if ($check_order && mysqli_num_rows($check_order) > 0) {
		$on = mysqli_fetch_assoc($check_order);
		$sale_id = $on['sale_id'];
	}else{
		$get_order = "SELECT sale_id FROM kp_sale_id";
		$get_order_r = mysqli_query($connection,$get_order) or die("Could not get the last order numbers");
		if (mysqli_num_rows($get_order_r) < 1) {
			$new_id = "INSERT INTO kp_sale_id(sale_id) VALUES(0)";
			$new_id_r = mysqli_query($connection,$new_id) or die("Could not create a new order number".mysqli_error($connection));
			$prev_sale_id = 0;
		}else{
			$row = mysqli_fetch_assoc($get_order_r);
			$prev_sale_id = $row['sale_id'];
		}

		$sale_id = $prev_sale_id + 1;
		$create_order_status = "INSERT INTO kp_sale_status(sale_id,user_id) VALUES('$sale_id','$user_id')";
		$create_order_status_r = mysqli_query($connection,$create_order_status) or die("Could not create a new order number".mysqli_error($connection));

		$create_last_order_no = "UPDATE kp_sale_id SET sale_id='$sale_id'";
		$create_last_order_no_r = mysqli_query($connection,$create_last_order_no) or die("Could not create a new order number".mysqli_error($connection));

	}
	return $sale_id;
}

if (!function_exists('kw_ensure_sale_status_schema')) {
    function kw_ensure_sale_status_schema($connection) {
        static $checked = false;
        if ($checked) return;
        $check = mysqli_query($connection, "SHOW COLUMNS FROM kp_sale_status LIKE 'has_parent'");
        if ($check && mysqli_num_rows($check) === 0) {
            mysqli_query($connection, "ALTER TABLE kp_sale_status ADD has_parent INT NOT NULL DEFAULT '2' AFTER parent, ADD mode VARCHAR(55) NOT NULL DEFAULT 'fresh' AFTER has_parent, ADD datime DATETIME NOT NULL DEFAULT CURRENT_TIMESTAMP AFTER mode");
        }
        $counter_check = mysqli_query($connection, "SHOW COLUMNS FROM kp_sale_status LIKE 'counter_id'");
        if ($counter_check && mysqli_num_rows($counter_check) === 0) {
            mysqli_query($connection, "ALTER TABLE kp_sale_status ADD counter_id INT NOT NULL DEFAULT '0' AFTER user_id");
        }
        // Position identifies the round within a closed-bill continuation family.
        // It is intentionally kept on sale_status because that table is the
        // source of truth for the currently live child sale.
        $position_check = mysqli_query($connection, "SHOW COLUMNS FROM kp_sale_status LIKE 'bill_position'");
        if ($position_check && mysqli_num_rows($position_check) === 0) {
            mysqli_query($connection, "ALTER TABLE kp_sale_status ADD bill_position INT NOT NULL DEFAULT '0' AFTER parent_sale_id");
        }
        mysqli_query($connection, "CREATE TABLE IF NOT EXISTS kp_sale_items_live LIKE kp_sale_items");
        kw_schema_ensure_column('kp_sale_items_live', 'accompaniment_json', "LONGTEXT NULL AFTER attr_id");
        kw_schema_ensure_column('kp_sale_items_live', 'session_flag', "TINYINT(1) NOT NULL DEFAULT 2 AFTER accompaniment_json");
        kw_schema_ensure_column('kp_sale_items', 'hour', "VARCHAR(20) NOT NULL DEFAULT '' AFTER counter");
        kw_schema_ensure_column('kp_sale_items_live', 'hour', "VARCHAR(20) NOT NULL DEFAULT '' AFTER counter");
        $checked = true;
    }
}

if (!function_exists('kw_schema_table_exists')) {
    function kw_schema_table_exists($connection, $table_name) {
        $table_name = mysqli_real_escape_string($connection, (string)$table_name);
        $result = mysqli_query($connection, "SHOW TABLES LIKE '$table_name'");
        return $result && mysqli_num_rows($result) > 0;
    }
}

if (!function_exists('kw_schema_column_exists')) {
    function kw_schema_column_exists($connection, $table_name, $column_name) {
        $table_name = mysqli_real_escape_string($connection, (string)$table_name);
        $column_name = mysqli_real_escape_string($connection, (string)$column_name);
        $result = mysqli_query($connection, "SHOW COLUMNS FROM `$table_name` LIKE '$column_name'");
        return $result && mysqli_num_rows($result) > 0;
    }
}

if (!function_exists('kw_schema_ensure_column')) {
    function kw_schema_ensure_column($table_name, $column_name, $definition_sql) {
        global $connection;

        if (!kw_schema_table_exists($connection, $table_name)) {
            return false;
        }
        if (kw_schema_column_exists($connection, $table_name, $column_name)) {
            return true;
        }
        return mysqli_query(
            $connection,
            "ALTER TABLE `$table_name` ADD COLUMN `$column_name` $definition_sql"
        ) ? true : false;
    }
}

if (!function_exists('kw_schema_ensure_index')) {
    function kw_schema_ensure_index($table_name, $index_name, $index_sql) {
        global $connection;

        if (!kw_schema_table_exists($connection, $table_name)) {
            return false;
        }
        $table_name = mysqli_real_escape_string($connection, (string)$table_name);
        $index_name = mysqli_real_escape_string($connection, (string)$index_name);
        $check = mysqli_query($connection, "SHOW INDEX FROM `$table_name` WHERE Key_name='$index_name'");
        if ($check && mysqli_num_rows($check) > 0) {
            return true;
        }
        return mysqli_query($connection, "ALTER TABLE `$table_name` ADD $index_sql") ? true : false;
    }
}

if (!function_exists('kw_ensure_bill_schema')) {
    function kw_ensure_bill_schema() {
        global $connection;

        static $checked = false;
        if ($checked) {
            return true;
        }

        if (function_exists('ensure_chaba_print_system')) {
            ensure_chaba_print_system($connection);
        }

        mysqli_query($connection, "CREATE TABLE IF NOT EXISTS kp_sale_status (
            id INT NOT NULL AUTO_INCREMENT,
            sale_id INT NOT NULL DEFAULT 0,
            user_id INT NOT NULL DEFAULT 0,
            status TINYINT(1) NOT NULL DEFAULT 2,
            parent INT NOT NULL DEFAULT 0,
            PRIMARY KEY (id),
            UNIQUE KEY uniq_sale_user (sale_id, user_id),
            KEY idx_user_status (user_id, status),
            KEY idx_parent (parent)
        ) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4");
        kw_ensure_sale_status_schema($connection);
        kw_schema_ensure_column('kp_sale_status', 'parent_status', "TINYINT(1) NOT NULL DEFAULT 2 AFTER mode");
        kw_schema_ensure_column('kp_sale_status', 'parent_sale_id', "INT NOT NULL DEFAULT 0 AFTER parent_status");

        mysqli_query($connection, "CREATE TABLE IF NOT EXISTS kp_shift_sales_open (
            id INT NOT NULL AUTO_INCREMENT,
            sale_id INT NOT NULL DEFAULT 0,
            child_sale_id INT NOT NULL DEFAULT 0,
            parent_sale_id INT NOT NULL DEFAULT 0,
            user_id INT NOT NULL DEFAULT 0,
            identifier_type VARCHAR(55) NOT NULL DEFAULT '',
            identifier_value VARCHAR(255) NOT NULL DEFAULT '',
            identifier_label VARCHAR(255) NOT NULL DEFAULT '',
            identifier_hex VARCHAR(40) NOT NULL DEFAULT '',
            parent_closed_open_id INT NOT NULL DEFAULT 0,
            counter_id INT NOT NULL DEFAULT 0,
            print_cntr_id INT NOT NULL DEFAULT 0,
            open_amount DECIMAL(10,2) NOT NULL DEFAULT 0.00,
            shift VARCHAR(60) NOT NULL DEFAULT '',
            bill_mode VARCHAR(20) NOT NULL DEFAULT 'last',
            last_bill INT NOT NULL DEFAULT 0,
            bill_count INT NOT NULL DEFAULT 0,
            family_total DECIMAL(10,2) NOT NULL DEFAULT 0.00,
            status TINYINT(1) NOT NULL DEFAULT 1,
            created_at TIMESTAMP NOT NULL DEFAULT CURRENT_TIMESTAMP,
            PRIMARY KEY (id),
            KEY idx_sale_user (sale_id, user_id),
            KEY idx_parent_user (parent_sale_id, user_id),
            KEY idx_child_user (child_sale_id, user_id)
        ) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4");
        kw_schema_ensure_column('kp_shift_sales_open', 'sale_mode', "VARCHAR(20) NOT NULL DEFAULT 'open' AFTER bill_mode");
        kw_schema_ensure_column('kp_shift_sales_open', 'order_stage', "VARCHAR(20) NOT NULL DEFAULT 'FRESH' AFTER sale_mode");
        kw_schema_ensure_column('kp_shift_sales_open', 'updated_at', "TIMESTAMP NOT NULL DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP AFTER created_at");

        mysqli_query($connection, "CREATE TABLE IF NOT EXISTS kp_shift_sale_open_items (
            id INT NOT NULL AUTO_INCREMENT,
            source_live_id INT NOT NULL DEFAULT 0,
            sale_id INT NOT NULL DEFAULT 0,
            open_id INT NOT NULL DEFAULT 0,
            item_id INT NOT NULL DEFAULT 0,
            user_id INT NOT NULL DEFAULT 0,
            qty DECIMAL(14,4) NOT NULL DEFAULT 0,
            price DECIMAL(14,4) NOT NULL DEFAULT 0,
            total DECIMAL(14,4) NOT NULL DEFAULT 0,
            dpt INT NOT NULL DEFAULT 0,
            shift VARCHAR(60) NOT NULL DEFAULT '',
            counter INT NOT NULL DEFAULT 0,
            stock_counter INT NOT NULL DEFAULT 0,
            attr_id INT NOT NULL DEFAULT 0,
            accompaniment_json LONGTEXT NULL,
            session_flag TINYINT(1) NOT NULL DEFAULT 2,
            created_at TIMESTAMP NOT NULL DEFAULT CURRENT_TIMESTAMP,
            PRIMARY KEY (id),
            KEY idx_open_sale (sale_id, open_id),
            KEY idx_source_live (sale_id, source_live_id),
            KEY idx_open_pending (sale_id, session_flag),
            KEY idx_open_dpt (sale_id, dpt)
        ) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4");
        kw_schema_ensure_column('kp_shift_sale_open_items', 'source_live_id', "INT NOT NULL DEFAULT 0 AFTER id");
        kw_schema_ensure_column('kp_shift_sale_open_items', 'hour', "VARCHAR(20) NOT NULL DEFAULT '' AFTER counter");

        // Keep the older registry name compatible with the shift-scoped
        // registry used by the open-order code.
        mysqli_query($connection, "CREATE TABLE IF NOT EXISTS kp_sales_open LIKE kp_shift_sales_open");
        $legacyOpenIdCheck = mysqli_query($connection, "SHOW COLUMNS FROM kp_sales_open LIKE 'open_id'");
        if ($legacyOpenIdCheck && mysqli_num_rows($legacyOpenIdCheck) === 0) {
            $legacyPrimaryCheck = mysqli_query($connection, "SHOW INDEX FROM kp_sales_open WHERE Key_name='PRIMARY'");
            if ($legacyPrimaryCheck && mysqli_num_rows($legacyPrimaryCheck) > 0) {
                mysqli_query($connection, "ALTER TABLE kp_sales_open ADD COLUMN open_id INT NOT NULL AUTO_INCREMENT UNIQUE FIRST");
            } else {
                mysqli_query($connection, "ALTER TABLE kp_sales_open ADD COLUMN open_id INT NOT NULL AUTO_INCREMENT PRIMARY KEY FIRST");
            }
        }

        mysqli_query($connection, "CREATE TABLE IF NOT EXISTS kp_open_color (
            id INT NOT NULL AUTO_INCREMENT,
            color VARCHAR(20) NOT NULL DEFAULT '',
            label VARCHAR(60) NOT NULL DEFAULT '',
            color_key VARCHAR(40) NOT NULL DEFAULT '',
            PRIMARY KEY (id)
        ) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4");
        kw_schema_ensure_index('kp_open_color', 'uniq_color_key', "UNIQUE KEY `uniq_color_key` (`color_key`)");

        mysqli_query($connection, "CREATE TABLE IF NOT EXISTS kp_open_identifier_preset (
            id INT NOT NULL AUTO_INCREMENT,
            user_id INT NOT NULL DEFAULT 0,
            preset_name VARCHAR(120) NOT NULL DEFAULT '',
            last_used_at DATETIME NULL,
            created_at TIMESTAMP NOT NULL DEFAULT CURRENT_TIMESTAMP,
            PRIMARY KEY (id),
            UNIQUE KEY uniq_user_preset (user_id, preset_name)
        ) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4");

        kw_schema_ensure_column('kp_sale_print', 'shift', "VARCHAR(60) NOT NULL DEFAULT '' AFTER total");
        kw_schema_ensure_column('kp_sale_print', 'accompaniment_json', "LONGTEXT NULL AFTER attr_id");
        kw_schema_ensure_column('kp_sale_print', 'session_flag', "TINYINT(1) NOT NULL DEFAULT 2 AFTER accompaniment_json");

        kw_schema_ensure_column('kp_sale_items', 'accompaniment_json', "LONGTEXT NULL AFTER attr_id");
        kw_schema_ensure_column('kp_sale_items', 'session_flag', "TINYINT(1) NOT NULL DEFAULT 2 AFTER accompaniment_json");

        kw_schema_ensure_column('kp_sales', 'parent', "INT NOT NULL DEFAULT 0 AFTER print_cntr_id");
        kw_schema_ensure_column('kp_sales', 'is_child_bill', "TINYINT(1) NOT NULL DEFAULT 0 AFTER parent");
        kw_schema_ensure_column('kp_sales', 'bill_role', "VARCHAR(20) NOT NULL DEFAULT 'closed' AFTER is_child_bill");
        kw_schema_ensure_column('kp_sales', 'bill_status', "TINYINT(1) NOT NULL DEFAULT 2 AFTER bill_role");
        kw_schema_ensure_column('kp_sales', 'root_parent_sale_id', "INT NOT NULL DEFAULT 0 AFTER bill_role");
        kw_schema_ensure_column('kp_sales', 'previous_sale_id', "INT NOT NULL DEFAULT 0 AFTER root_parent_sale_id");
        kw_schema_ensure_column('kp_sales', 'previous_balance', "DECIMAL(10,2) NOT NULL DEFAULT 0.00 AFTER previous_sale_id");
        kw_schema_ensure_column('kp_sales', 'current_total', "DECIMAL(10,2) NOT NULL DEFAULT 0.00 AFTER previous_balance");
        kw_schema_ensure_column('kp_sales', 'current_balance', "DECIMAL(10,2) NOT NULL DEFAULT 0.00 AFTER current_total");

        mysqli_query($connection, "CREATE TABLE IF NOT EXISTS kp_print_ticket_items (
            id INT NOT NULL AUTO_INCREMENT,
            batch_id INT NOT NULL DEFAULT 0,
            sale_id INT NOT NULL DEFAULT 0,
            user_id INT NOT NULL DEFAULT 0,
            item_id INT NOT NULL DEFAULT 0,
            attr_id INT NOT NULL DEFAULT 0,
            qty DECIMAL(10,2) NOT NULL DEFAULT 0,
            price DECIMAL(10,2) NOT NULL DEFAULT 0,
            total DECIMAL(10,2) NOT NULL DEFAULT 0,
            dpt INT NOT NULL DEFAULT 0,
            counter INT NOT NULL DEFAULT 0,
            stock_counter INT NOT NULL DEFAULT 0,
            created_at TIMESTAMP NOT NULL DEFAULT CURRENT_TIMESTAMP,
            PRIMARY KEY (id),
            KEY idx_batch_sale (batch_id, sale_id),
            KEY idx_batch_dpt (batch_id, dpt)
        ) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4");

        $checked = true;
        return true;
    }
}

function recipe_qty($item_id,$qty_column){
	global $connection;
	$a=array();
	$get_id=mysqli_query($connection,"SELECT raw_id,qty FROM kp_recipe WHERE item_id='$item_id'"); 
	foreach ($get_id as $row) {
		$rec_qty=$row['qty'];
		$raw_id=$row['raw_id'];
		$stock = get_column('kp_items',$qty_column,"item_id='$raw_id'");
		$count=$stock/$rec_qty;
		array_push($a, $count);
	}

	
	if (empty($a)) {
	  return 0;
	}else{
	  return min($a);
	}
	//print_r($a);
} 

/**
 * Returns a context array for API operations during order placement.
 */
if (!function_exists('bill_get_send_order_context')) {
    function bill_get_send_order_context($user_id, $sale_id) {
        return [
            'user_id' => (int)$user_id,
            'active_sale_id' => (int)$sale_id,
        ];
    }
}

if (!function_exists('bill_handle_registry_before_send')) {
    function bill_handle_registry_before_send($context) {
        return true;
    }
}

if (!function_exists('bill_handle_registry_after_send')) {
    function bill_handle_registry_after_send($context, $params) {
        global $billApiService;
        if (isset($billApiService)) {
            return $billApiService->handleRegistryStateAfterSend($context, $params);
        }
    }
}

if (!function_exists('kw_api_require_user_id')) {
    function kw_api_require_user_id() {
        if (!isset($_SESSION['user_id']) || (int)$_SESSION['user_id'] < 1) {
            return 0;
        }
        return (int)$_SESSION['user_id'];
    }
}

if (!function_exists('kw_api_user_counter_id')) {
    function kw_api_user_counter_id($user_id) {
        $user_id = (int)$user_id;
        if ($user_id < 1) {
            return 0;
        }
        return (int)get_column('kp_thesion_front', 'cntr_id', "user_id='$user_id'");
    }
}

if (!function_exists('kw_api_counter_department')) {
    function kw_api_counter_department($counter_id) {
        $counter_id = (int)$counter_id;
        if ($counter_id < 1) {
            return 0;
        }
        return (int)get_column('kp_counters', 'dpt', "cntr_id='$counter_id'");
    }
}

if (!function_exists('kw_api_item_counter_id')) {
    function kw_api_item_counter_id($base_counter_id, $item_department) {
        $base_counter_id = (int)$base_counter_id;
        $item_department = (int)$item_department;
        if ($item_department === 2) {
            return (int)get_column('kp_counters', 'cntr_id', "dpt=2 AND kit_default=1");
        }
        return $base_counter_id;
    }
}

if (!function_exists('kw_api_item_stock_meta')) {
    function kw_api_item_stock_meta($item_row, $counter_id) {
        $counter_id = (int)$counter_id;
        $stock = isset($item_row['stock']) ? (int)$item_row['stock'] : 0;
        $recipe = isset($item_row['recipe']) ? (int)$item_row['recipe'] : 0;
        $item_id = isset($item_row['item_id']) ? (int)$item_row['item_id'] : 0;
        $qty = 0;
        $reorder = 0;

        if ($counter_id > 0) {
            $qty_column = 'cntr' . $counter_id . '_qty';
            $reorder_column = 'cntr' . $counter_id . '_reorder';
            if ($stock === 1) {
                $qty = isset($item_row[$qty_column]) ? (float)$item_row[$qty_column] : 0;
                $reorder = isset($item_row[$reorder_column]) ? (float)$item_row[$reorder_column] : 0;
            } elseif ($stock === 2) {
                if ($recipe === 1) {
                    $qty = recipe_qty($item_id, $qty_column);
                    $reorder = 2;
                } else {
                    $qty = 100;
                    $reorder = 50;
                }
            }
        }

        if ($qty <= 0) {
            $color = 'red';
        } elseif ($qty <= $reorder) {
            $color = 'orange';
        } else {
            $color = 'green';
        }

        return [
            'qty' => $qty,
            'reorder' => $reorder,
            'stock_color' => $color,
        ];
    }
}

if (!function_exists('kw_api_fetch_categories')) {
    function kw_api_fetch_categories($user_id) {
        global $connection;
        $user_id = (int)$user_id;
        $counter_id = kw_api_user_counter_id($user_id);
        $department = kw_api_counter_department($counter_id);
        $filter = '';
        if ($department === 2) {
            $filter = "WHERE dpt='2'";
        }

        $categories = [];
        $query = mysqli_query($connection, "SELECT cat_id, name FROM kp_cat $filter ORDER BY name ASC");
        if ($query) {
            while ($row = mysqli_fetch_assoc($query)) {
                $categories[] = [
                    'cat_id' => (int)$row['cat_id'],
                    'name' => $row['name'],
                ];
            }
        }
        return $categories;
    }
}

if (!function_exists('kw_api_fetch_subcategories')) {
    function kw_api_fetch_subcategories($category_id) {
        global $connection;
        $category_id = (int)$category_id;
        $subcategories = [];
        if ($category_id < 1) {
            return $subcategories;
        }

        $query = mysqli_query($connection, "SELECT sub_id, name FROM kp_sub_cat WHERE cat_id='$category_id' ORDER BY name ASC");
        if ($query) {
            while ($row = mysqli_fetch_assoc($query)) {
                $subcategories[] = [
                    'sub_id' => (int)$row['sub_id'],
                    'name' => $row['name'],
                ];
            }
        }
        return $subcategories;
    }
}

if (!function_exists('kw_api_fetch_items')) {
    function kw_api_fetch_items($user_id, $category_id, $subcategory_id = 0) {
        global $connection;
        $user_id = (int)$user_id;
        $category_id = (int)$category_id;
        $subcategory_id = (int)$subcategory_id;
        $counter_id = kw_api_user_counter_id($user_id);
        $department = kw_api_counter_department($counter_id);
        $items = [];

        if ($category_id < 1 && $subcategory_id < 1) {
            return $items;
        }

        $where = ["del_status=2", "menu=1"];
        if ($subcategory_id > 0) {
            $where[] = "sub_id='$subcategory_id'";
        } else {
            $where[] = "cat_id='$category_id'";
        }
        if ($department === 2) {
            $where[] = "dpt='2'";
        }

        $sql = "SELECT * FROM kp_items WHERE " . implode(' AND ', $where) . " ORDER BY name ASC";
        $query = mysqli_query($connection, $sql);
        if (!$query) {
            return $items;
        }

        while ($row = mysqli_fetch_assoc($query)) {
            $item_counter_id = kw_api_item_counter_id($counter_id, isset($row['dpt']) ? (int)$row['dpt'] : 0);
            $stock_meta = kw_api_item_stock_meta($row, $item_counter_id);
            $items[] = [
                'item_id' => (int)$row['item_id'],
                'name' => $row['name'],
                'cat_id' => (int)$row['cat_id'],
                'sub_id' => (int)$row['sub_id'],
                'deci' => isset($row['deci']) ? (int)$row['deci'] : 0,
                'has_attributes' => isset($row['has_attributes']) ? (int)$row['has_attributes'] : 0,
                'stock' => isset($row['stock']) ? (int)$row['stock'] : 0,
                'qty' => $stock_meta['qty'],
                'reorder' => $stock_meta['reorder'],
                'stock_color' => $stock_meta['stock_color'],
            ];
        }

        return $items;
    }
}

