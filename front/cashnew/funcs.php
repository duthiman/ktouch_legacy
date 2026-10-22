<?php
$gen_funcs_candidates = array(
    dirname(__DIR__, 2) . DIRECTORY_SEPARATOR . 'inc' . DIRECTORY_SEPARATOR . 'gen_funcs.php',
    dirname(__DIR__) . DIRECTORY_SEPARATOR . 'inc' . DIRECTORY_SEPARATOR . 'gen_funcs.php',
    dirname(__DIR__) . DIRECTORY_SEPARATOR . '..' . DIRECTORY_SEPARATOR . 'inc' . DIRECTORY_SEPARATOR . 'gen_funcs.php'
);

foreach ($gen_funcs_candidates as $gen_funcs_file) {
    $resolved = realpath($gen_funcs_file);
    if ($resolved !== false && is_file($resolved)) {
        require_once($resolved);
        break;
    }
}
 
 
function counter_dpt($counter){
    global $connection;
    $get_counter_dpt = mysqli_query($connection,"SELECT dpt FROM kp_counters WHERE cntr_id='$counter'") or die("Could not get counter".mysqli_error($connection));
    $dp = mysqli_fetch_assoc($get_counter_dpt);
    $dpt=$dp['dpt'];
    
    return $dpt;
}
function ensure_payment_mode_columns($pay_modes){
    global $connection;
    foreach ($pay_modes as $mode_id) {
        $mode_id = preg_replace('/[^A-Za-z0-9_]/', '', (string)$mode_id);
        if ($mode_id === '') {
            continue;
        }
        $amount_col = "mode".$mode_id;
        $ref_col = "moderef".$mode_id;

        $check_amount = mysqli_query($connection, "SHOW COLUMNS FROM kp_sale_payment LIKE '$amount_col'") or die(mysqli_error($connection));
        if (mysqli_num_rows($check_amount) < 1) {
            mysqli_query($connection, "ALTER TABLE kp_sale_payment ADD COLUMN $amount_col DECIMAL(10,2) NOT NULL DEFAULT 0") or die(mysqli_error($connection));
        }

        $check_ref = mysqli_query($connection, "SHOW COLUMNS FROM kp_sale_payment LIKE '$ref_col'") or die(mysqli_error($connection));
        if (mysqli_num_rows($check_ref) < 1) {
            mysqli_query($connection, "ALTER TABLE kp_sale_payment ADD COLUMN $ref_col VARCHAR(120) NOT NULL DEFAULT ''") or die(mysqli_error($connection));
        }
    }

    $check_counter = mysqli_query($connection, "SHOW COLUMNS FROM kp_sale_payment LIKE 'counter'") or die(mysqli_error($connection));
    if (mysqli_num_rows($check_counter) < 1) {
        mysqli_query($connection, "ALTER TABLE kp_sale_payment ADD COLUMN counter INT NOT NULL DEFAULT 0") or die(mysqli_error($connection));
    }

    $check_ord_date = mysqli_query($connection, "SHOW COLUMNS FROM kp_sale_payment LIKE 'ord_date'") or die(mysqli_error($connection));
    if (mysqli_num_rows($check_ord_date) < 1) {
        mysqli_query($connection, "ALTER TABLE kp_sale_payment ADD COLUMN ord_date DATE NULL") or die(mysqli_error($connection));
    }
}
function insert_payment(){
    global $connection,$balance,$user_id,$arr,$sale_amount,$shift,$tendered,$invoices,$type,$orduser;
    $array = array();
    $array2 = array(); 
    ensure_payment_mode_columns($arr);
    $counter = 0;
    $invoice_list = array_filter(array_map('trim', explode(',', $invoices)));
    if (!empty($invoice_list)) {
        $first_sale_id = (int)$invoice_list[0];
        if ($first_sale_id > 0) {
            $counter = (int)get_column('kp_sales','counter',"sale_id='$first_sale_id'");
        }
    }
    if ($counter < 1) {
        $counter = (int)get_column('kp_users','counter',"user_id='$user_id'");
    }
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
        $field_values = array("pay_id","sale_id","user_id","tendered","pay_date","paid_total","type","counter");
        $result=array_merge( $field_values,$array);

        $data_values = array($pay_id,"'".$invoices."'",$user_id ,$tendered,"'".$shift."'",$sale_amount,$type,$counter);
        $result2=array_merge( $data_values,$array2); 

        if(Insertdata("kp_sale_payment", $result, $result2)){
            $myArray = explode(',', $invoices);  
            foreach($myArray as $i) {
                $sale_id=$i;
                $ord_date=get_column('kp_sales','shift',"sale_id='$sale_id'"); 
                $ord_user=get_column('kp_sales','user_id',"sale_id='$sale_id'"); 
                $paid_total=get_column('kp_sales','amount',"sale_id='$sale_id'");

                $insert_hist= mysqli_query($connection,"INSERT INTO kp_sales_paid(pay_id, sale_id, user_id, ord_user, paid_total, ord_date, pay_date) VALUES('$pay_id','$sale_id','$user_id','$ord_user','$paid_total','$ord_date','$shift')") or die('Error creating pay history'.mysqli_error($connection)); 
                $update_bal=mysqli_query($connection,"UPDATE kp_sale_payment SET ord_date='$ord_date' WHERE pay_id='$pay_id'") or die(mysqli_error($connection));
            }   
            foreach($arr as $i) {
                $modename="name".$i; 
                $amount=$_POST[$modename];
                $accbal=get_column('kp_pay_mode','accbal',"pay_id='$i'");
                $update_bal=mysqli_query($connection,"UPDATE kp_pay_mode SET accbal=accbal+$amount WHERE pay_id='$i'") or die(mysqli_error($connection));
                $insert_hist= mysqli_query($connection,"INSERT INTO kp_pay_mode_hist(pay_id,user_id,shift,day,amount,accbal) VALUES('$pay_id','$user_id','$shift',CURRENT_DATE,'$amount','$accbal')") or die('Error creating pay history'.mysqli_error($connection));
            }
            return true;
        }else{
            return false;
        }
} 

function cash_has_sales_column($column_name) {
    global $connection;
    $column_name = mysqli_real_escape_string($connection, $column_name);
    $check = mysqli_query($connection, "SHOW COLUMNS FROM kp_sales LIKE '$column_name'") or die(mysqli_error($connection));
    return $check && mysqli_num_rows($check) > 0;
}

function cash_ensure_merge_tables() {
    global $connection;
    mysqli_query($connection, "CREATE TABLE IF NOT EXISTS kp_bill_merge (
        merge_id INT NOT NULL AUTO_INCREMENT,
        merge_no VARCHAR(40) NOT NULL DEFAULT '',
        created_by INT NOT NULL DEFAULT 0,
        created_at TIMESTAMP NOT NULL DEFAULT CURRENT_TIMESTAMP,
        status TINYINT(1) NOT NULL DEFAULT 1,
        merge_state TINYINT(1) NOT NULL DEFAULT 1,
        total_amount DECIMAL(10,2) NOT NULL DEFAULT 0,
        cleared_by INT NOT NULL DEFAULT 0,
        cleared_at DATETIME NULL,
        note TEXT NULL,
        PRIMARY KEY (merge_id),
        INDEX idx_status (status),
        INDEX idx_created_by (created_by)
    )") or die(mysqli_error($connection));

    $state_col = mysqli_query($connection, "SHOW COLUMNS FROM kp_bill_merge LIKE 'merge_state'") or die(mysqli_error($connection));
    if ($state_col && mysqli_num_rows($state_col) < 1) {
        mysqli_query($connection, "ALTER TABLE kp_bill_merge ADD COLUMN merge_state TINYINT(1) NOT NULL DEFAULT 1 AFTER status") or die(mysqli_error($connection));
    }

    mysqli_query($connection, "CREATE TABLE IF NOT EXISTS kp_bill_merge_items (
        id INT NOT NULL AUTO_INCREMENT,
        merge_id INT NOT NULL DEFAULT 0,
        sale_id INT NOT NULL DEFAULT 0,
        root_sale_id INT NOT NULL DEFAULT 0,
        amount DECIMAL(10,2) NOT NULL DEFAULT 0,
        item_type VARCHAR(20) NOT NULL DEFAULT 'single',
        status TINYINT(1) NOT NULL DEFAULT 1,
        PRIMARY KEY (id),
        INDEX idx_merge_id (merge_id),
        INDEX idx_sale_id (sale_id),
        INDEX idx_root_sale_id (root_sale_id)
    )") or die(mysqli_error($connection));
}

function cash_merge_root_sale_id($sale_id) {
    $sale_id = (int)$sale_id;
    if ($sale_id < 1) {
        return 0;
    }
    $parent = 0;
    if (cash_has_sales_column('parent')) {
        $parent = (int)get_column('kp_sales', 'parent', "sale_id='$sale_id'");
    }
    if ($parent < 1 && cash_has_sales_column('parent_sale')) {
        $parent = (int)get_column('kp_sales', 'parent_sale', "sale_id='$sale_id'");
    }
    return $parent > 0 ? $parent : $sale_id;
}

function cash_merge_chain_sale_ids($root_sale_id, $only_unpaid = true) {
    global $connection;
    $root_sale_id = (int)$root_sale_id;
    if ($root_sale_id < 1) {
        return array();
    }
    $where = array("sale_id='$root_sale_id'");
    $childChecks = array();
    if (cash_has_sales_column('parent')) {
        $childChecks[] = "parent='$root_sale_id'";
    }
    if (cash_has_sales_column('parent_sale')) {
        $childChecks[] = "parent_sale='$root_sale_id'";
    }
    if (!empty($childChecks)) {
        $where[] = "(" . implode(' OR ', $childChecks) . ")";
    }
    $query = "SELECT sale_id FROM kp_sales WHERE (" . implode(' OR ', $where) . ")";
    if ($only_unpaid) {
        $query .= " AND pay_status=2";
    }
    $query .= " ORDER BY sale_id ASC";
    $result = mysqli_query($connection, $query) or die(mysqli_error($connection));
    $ids = array();
    while ($result && $row = mysqli_fetch_assoc($result)) {
        $ids[] = (int)$row['sale_id'];
    }
    $ids = array_values(array_unique(array_filter($ids)));
    sort($ids);
    return $ids;
}

function cash_merge_chain_total($root_sale_id, $only_unpaid = true) {
    $ids = cash_merge_chain_sale_ids($root_sale_id, $only_unpaid);
    if (empty($ids)) {
        return 0;
    }
    $safe_ids = implode(',', array_map('intval', $ids));
    return (float)column_total('kp_sales', 'amount', "sale_id IN ($safe_ids)");
}

function cash_sale_in_open_merge($sale_id, $root_sale_id = 0) {
    cash_ensure_merge_tables();
    $sale_id = (int)$sale_id;
    $root_sale_id = (int)$root_sale_id;
    $where = array("sale_id='$sale_id'");
    if ($root_sale_id > 0) {
        $where[] = "root_sale_id='$root_sale_id'";
    }
    $filter = "(" . implode(' OR ', $where) . ")";
    return column_count('kp_bill_merge_items', 'id', "$filter AND status=1 AND merge_id IN (SELECT merge_id FROM kp_bill_merge WHERE status=1)") > 0;
}

function cash_merge_open_headers() {
    global $connection;
    cash_ensure_merge_tables();
    $result = mysqli_query($connection, "SELECT * FROM kp_bill_merge WHERE status=1 ORDER BY merge_id DESC LIMIT 20") or die(mysqli_error($connection));
    $rows = array();
    while ($result && $row = mysqli_fetch_assoc($result)) {
        $rows[] = $row;
    }
    return $rows;
}

function cash_merge_items($merge_id) {
    global $connection;
    $merge_id = (int)$merge_id;
    $result = mysqli_query($connection, "SELECT * FROM kp_bill_merge_items WHERE merge_id='$merge_id' AND status=1 ORDER BY root_sale_id ASC, sale_id ASC") or die(mysqli_error($connection));
    $rows = array();
    while ($result && $row = mysqli_fetch_assoc($result)) {
        $rows[] = $row;
    }
    return $rows;
}

function cash_ensure_merge_request_table() {
    global $connection;
    mysqli_query($connection, "CREATE TABLE IF NOT EXISTS kp_bill_merge_request (
        request_id INT NOT NULL AUTO_INCREMENT,
        merge_id INT NOT NULL DEFAULT 0,
        requester_user_id INT NOT NULL DEFAULT 0,
        requester_sale_id INT NOT NULL DEFAULT 0,
        requester_root_sale_id INT NOT NULL DEFAULT 0,
        target_user_id INT NOT NULL DEFAULT 0,
        target_sale_id INT NOT NULL DEFAULT 0,
        target_root_sale_id INT NOT NULL DEFAULT 0,
        status TINYINT(1) NOT NULL DEFAULT 1,
        created_at TIMESTAMP NOT NULL DEFAULT CURRENT_TIMESTAMP,
        responded_at DATETIME NULL,
        responded_by INT NOT NULL DEFAULT 0,
        note TEXT NULL,
        PRIMARY KEY (request_id),
        INDEX idx_merge_id (merge_id),
        INDEX idx_requester (requester_user_id),
        INDEX idx_target (target_user_id),
        INDEX idx_status (status)
    )") or die(mysqli_error($connection));

    $merge_col = mysqli_query($connection, "SHOW COLUMNS FROM kp_bill_merge_request LIKE 'merge_id'") or die(mysqli_error($connection));
    if ($merge_col && mysqli_num_rows($merge_col) < 1) {
        mysqli_query($connection, "ALTER TABLE kp_bill_merge_request ADD COLUMN merge_id INT NOT NULL DEFAULT 0 AFTER request_id") or die(mysqli_error($connection));
    }

    $req_sale_col = mysqli_query($connection, "SHOW COLUMNS FROM kp_bill_merge_request LIKE 'requester_sale_id'") or die(mysqli_error($connection));
    if ($req_sale_col && mysqli_num_rows($req_sale_col) < 1) {
        mysqli_query($connection, "ALTER TABLE kp_bill_merge_request ADD COLUMN requester_sale_id INT NOT NULL DEFAULT 0 AFTER requester_user_id") or die(mysqli_error($connection));
    }
    $target_sale_col = mysqli_query($connection, "SHOW COLUMNS FROM kp_bill_merge_request LIKE 'target_sale_id'") or die(mysqli_error($connection));
    if ($target_sale_col && mysqli_num_rows($target_sale_col) < 1) {
        mysqli_query($connection, "ALTER TABLE kp_bill_merge_request ADD COLUMN target_sale_id INT NOT NULL DEFAULT 0 AFTER target_user_id") or die(mysqli_error($connection));
    }
}

function cash_cleanup_merge_requests() {
    global $connection;
    cash_ensure_merge_request_table();
    $requests = mysqli_query($connection, "SELECT request_id, requester_sale_id, target_sale_id, status FROM kp_bill_merge_request WHERE status IN (1,2)") or die(mysqli_error($connection));
    while ($requests && $row = mysqli_fetch_assoc($requests)) {
        $request_id = (int)$row['request_id'];
        $requester_sale_id = (int)$row['requester_sale_id'];
        $target_sale_id = (int)$row['target_sale_id'];
        $requester_in_merge = $requester_sale_id > 0 ? cash_sale_in_open_merge($requester_sale_id, cash_merge_root_sale_id($requester_sale_id)) : false;
        $target_in_merge = $target_sale_id > 0 ? cash_sale_in_open_merge($target_sale_id, cash_merge_root_sale_id($target_sale_id)) : false;
        if ($requester_in_merge || $target_in_merge) {
            mysqli_query(
                $connection,
                "UPDATE kp_bill_merge_request
                 SET status=4, responded_at=NOW(), note='Closed after merge'
                 WHERE request_id='$request_id' AND status IN (1,2)"
            ) or die(mysqli_error($connection));
        }
    }
}

function cash_active_merge_draft($user_id, $create_if_missing = false) {
    global $connection;
    cash_ensure_merge_tables();
    $user_id = (int)$user_id;
    $res = mysqli_query($connection, "SELECT * FROM kp_bill_merge WHERE created_by='$user_id' AND status=0 AND merge_state=1 ORDER BY merge_id DESC LIMIT 1") or die(mysqli_error($connection));
    if ($res && mysqli_num_rows($res) > 0) {
        return mysqli_fetch_assoc($res);
    }
    if (!$create_if_missing) {
        return null;
    }
    mysqli_query($connection, "INSERT INTO kp_bill_merge (created_by, status, merge_state, total_amount) VALUES('$user_id', 0, 1, 0)") or die(mysqli_error($connection));
    $merge_id = (int)mysqli_insert_id($connection);
    $merge_no = 'DRF-' . str_pad((string)$merge_id, 5, '0', STR_PAD_LEFT);
    mysqli_query($connection, "UPDATE kp_bill_merge SET merge_no='$merge_no' WHERE merge_id='$merge_id'") or die(mysqli_error($connection));
    $res = mysqli_query($connection, "SELECT * FROM kp_bill_merge WHERE merge_id='$merge_id' LIMIT 1") or die(mysqli_error($connection));
    return $res ? mysqli_fetch_assoc($res) : null;
}

function cash_recalculate_merge_total($merge_id) {
    global $connection;
    $merge_id = (int)$merge_id;
    $total = (float)column_total('kp_bill_merge_items', 'amount', "merge_id='$merge_id' AND status=1");
    mysqli_query($connection, "UPDATE kp_bill_merge SET total_amount='$total' WHERE merge_id='$merge_id'") or die(mysqli_error($connection));
    return $total;
}

function cash_merge_draft_items($merge_id) {
    return cash_merge_items($merge_id);
}

function cash_add_sale_to_merge_draft($merge_id, $sale_id, &$error_message = '') {
    global $connection;
    $merge_id = (int)$merge_id;
    $sale_id = (int)$sale_id;
    if ($merge_id < 1 || $sale_id < 1) {
        $error_message = 'Invalid merge draft or bill.';
        return false;
    }
    $root_sale_id = cash_merge_root_sale_id($sale_id);
    $amount = (float)get_column('kp_sales', 'amount', "sale_id='$sale_id'");
    if ($amount <= 0) {
        $error_message = 'This bill has no amount to merge.';
        return false;
    }
    if (cash_sale_in_open_merge($sale_id, $root_sale_id)) {
        $existing = column_count('kp_bill_merge_items', 'id', "merge_id='$merge_id' AND sale_id='$sale_id' AND status=1");
        if ($existing < 1) {
            $error_message = 'This bill is already part of another active merge.';
            return false;
        }
    }
    $existing = column_count('kp_bill_merge_items', 'id', "merge_id='$merge_id' AND sale_id='$sale_id' AND status=1");
    if ($existing > 0) {
        cash_recalculate_merge_total($merge_id);
        return true;
    }
    $item_type = ($root_sale_id > 0 && $root_sale_id !== $sale_id) ? 'open_child' : 'single';
    $safe_type = mysqli_real_escape_string($connection, $item_type);
    mysqli_query(
        $connection,
        "INSERT INTO kp_bill_merge_items (merge_id, sale_id, root_sale_id, amount, item_type, status)
         VALUES('$merge_id', '$sale_id', '$root_sale_id', '$amount', '$safe_type', 1)"
    ) or die(mysqli_error($connection));
    cash_recalculate_merge_total($merge_id);
    return true;
}

function cash_remove_sale_from_merge_draft($merge_id, $sale_id) {
    global $connection;
    $merge_id = (int)$merge_id;
    $sale_id = (int)$sale_id;
    mysqli_query($connection, "UPDATE kp_bill_merge_items SET status=3 WHERE merge_id='$merge_id' AND sale_id='$sale_id' AND status=1") or die(mysqli_error($connection));
    mysqli_query($connection, "UPDATE kp_bill_merge_request SET status=4, responded_at=NOW(), note='Closed after bill removed from draft' WHERE merge_id='$merge_id' AND target_sale_id='$sale_id' AND status IN (1,2)") or die(mysqli_error($connection));
    return cash_recalculate_merge_total($merge_id);
}

function cash_create_merge_record($created_by, $roots, &$error_message = '') {
    global $connection;
    cash_ensure_merge_tables();
    $created_by = (int)$created_by;
    $roots = is_array($roots) ? $roots : array($roots);
    $roots = array_values(array_unique(array_filter(array_map('intval', $roots))));
    if (count($roots) < 1) {
        $error_message = 'Select at least one bill or chain.';
        return false;
    }

    $safe_roots = array();
    $merge_total = 0.0;
    $expanded_items = array();

    foreach ($roots as $root_sale_id) {
        if ($root_sale_id < 1) {
            continue;
        }
        if (cash_sale_in_open_merge($root_sale_id, $root_sale_id)) {
            $error_message = 'Bill #' . $root_sale_id . ' is already in an open merge.';
            return false;
        }
        $chain_ids = cash_merge_chain_sale_ids($root_sale_id, true);
        if (empty($chain_ids)) {
            continue;
        }
        $root_total = 0.0;
        foreach ($chain_ids as $sale_id) {
            if (cash_sale_in_open_merge($sale_id, $root_sale_id)) {
                $error_message = 'One of the bills under #' . $root_sale_id . ' is already in another open merge.';
                return false;
            }
            $amount = (float)get_column('kp_sales', 'amount', "sale_id='$sale_id'");
            if ($amount <= 0) {
                continue;
            }
            $expanded_items[] = array(
                'sale_id' => $sale_id,
                'root_sale_id' => $root_sale_id,
                'amount' => $amount,
                'item_type' => count($chain_ids) > 1 ? 'open_chain' : 'single'
            );
            $root_total += $amount;
        }
        if ($root_total > 0) {
            $safe_roots[] = $root_sale_id;
            $merge_total += $root_total;
        }
    }

    if (count($safe_roots) < 1 || count($expanded_items) < 1 || $merge_total <= 0) {
        $error_message = 'There were no eligible bills left to merge.';
        return false;
    }

    mysqli_query($connection, "INSERT INTO kp_bill_merge (created_by, total_amount, status) VALUES('$created_by', '$merge_total', 1)") or die(mysqli_error($connection));
    $merge_id = (int)mysqli_insert_id($connection);
    $merge_no = 'MRG-' . str_pad((string)$merge_id, 5, '0', STR_PAD_LEFT);
    mysqli_query($connection, "UPDATE kp_bill_merge SET merge_no='$merge_no' WHERE merge_id='$merge_id'") or die(mysqli_error($connection));

    foreach ($expanded_items as $item) {
        $sale_id = (int)$item['sale_id'];
        $root_sale_id = (int)$item['root_sale_id'];
        $amount = (float)$item['amount'];
        $item_type = mysqli_real_escape_string($connection, $item['item_type']);
        mysqli_query(
            $connection,
            "INSERT INTO kp_bill_merge_items (merge_id, sale_id, root_sale_id, amount, item_type, status)
             VALUES('$merge_id', '$sale_id', '$root_sale_id', '$amount', '$item_type', 1)"
        ) or die(mysqli_error($connection));
    }

    return array(
        'merge_id' => $merge_id,
        'merge_no' => $merge_no,
        'roots' => $safe_roots,
        'total_amount' => $merge_total,
        'items' => $expanded_items
    );
}

function cash_create_merge_record_from_sales($created_by, $sale_ids, &$error_message = '') {
    global $connection;
    cash_ensure_merge_tables();
    $created_by = (int)$created_by;
    $sale_ids = is_array($sale_ids) ? $sale_ids : array($sale_ids);
    $sale_ids = array_values(array_unique(array_filter(array_map('intval', $sale_ids))));
    if (count($sale_ids) < 1) {
        $error_message = 'Select at least one bill.';
        return false;
    }

    $merge_total = 0.0;
    $expanded_items = array();

    foreach ($sale_ids as $sale_id) {
        if ($sale_id < 1) {
            continue;
        }
        $amount = (float)get_column('kp_sales', 'amount', "sale_id='$sale_id'");
        if ($amount <= 0) {
            continue;
        }
        $pay_status = (int)get_column('kp_sales', 'pay_status', "sale_id='$sale_id'");
        if ($pay_status === 1) {
            continue;
        }
        $root_sale_id = cash_merge_root_sale_id($sale_id);
        if (cash_sale_in_open_merge($sale_id, $root_sale_id)) {
            $error_message = 'Bill #' . $sale_id . ' is already in an open merge.';
            return false;
        }
        $expanded_items[] = array(
            'sale_id' => $sale_id,
            'root_sale_id' => $root_sale_id > 0 ? $root_sale_id : $sale_id,
            'amount' => $amount,
            'item_type' => ($root_sale_id > 0 && $root_sale_id !== $sale_id) ? 'open_child' : 'single'
        );
        $merge_total += $amount;
    }

    if (count($expanded_items) < 1 || $merge_total <= 0) {
        $error_message = 'There were no eligible bills left to merge.';
        return false;
    }

    mysqli_query($connection, "INSERT INTO kp_bill_merge (created_by, total_amount, status) VALUES('$created_by', '$merge_total', 1)") or die(mysqli_error($connection));
    $merge_id = (int)mysqli_insert_id($connection);
    $merge_no = 'MRG-' . str_pad((string)$merge_id, 5, '0', STR_PAD_LEFT);
    mysqli_query($connection, "UPDATE kp_bill_merge SET merge_no='$merge_no' WHERE merge_id='$merge_id'") or die(mysqli_error($connection));

    foreach ($expanded_items as $item) {
        $sale_id = (int)$item['sale_id'];
        $root_sale_id = (int)$item['root_sale_id'];
        $amount = (float)$item['amount'];
        $item_type = mysqli_real_escape_string($connection, $item['item_type']);
        mysqli_query(
            $connection,
            "INSERT INTO kp_bill_merge_items (merge_id, sale_id, root_sale_id, amount, item_type, status)
             VALUES('$merge_id', '$sale_id', '$root_sale_id', '$amount', '$item_type', 1)"
        ) or die(mysqli_error($connection));
    }

    cash_cleanup_merge_requests();

    return array(
        'merge_id' => $merge_id,
        'merge_no' => $merge_no,
        'total_amount' => $merge_total,
        'items' => $expanded_items
    );
}
?>
