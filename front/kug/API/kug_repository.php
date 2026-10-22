<?php

if (!function_exists('kug_repo_context')) {
    function kug_repo_context($user_id) {
        global $connection;

        $user_id = (int)$user_id;
        $counter = function_exists('user_counter') ? (int)user_counter($user_id) : (int)get_column('kp_users', 'counter', "user_id='{$user_id}'");
        $dpt = $counter > 0 ? (string)get_column('kp_counters', 'dpt', "cntr_id='{$counter}'") : '';
        $po_id = isset($_SESSION['buy_po']) ? (int)$_SESSION['buy_po'] : 0;
        $sup_id = isset($_SESSION['buy_sup']) ? (int)$_SESSION['buy_sup'] : ($po_id > 0 ? (int)get_column('kp_po_status', 'sup_id', "po_id='{$po_id}'") : 0);
        $settings = function_exists('kp_purchase_settings') ? kp_purchase_settings($connection) : ['front_purchase_enabled' => 1, 'front_auto_stock_enabled' => 0];

        return [
            'user_id' => $user_id,
            'counter' => $counter,
            'counter_name' => $counter > 0 && function_exists('get_counter') ? (string)get_counter($counter) : '',
            'dpt' => $dpt,
            'po_id' => $po_id,
            'sup_id' => $sup_id,
            'buy_tax' => isset($_SESSION['buy_tax']) ? (int)$_SESSION['buy_tax'] : 0,
            'settings' => $settings,
        ];
    }
}

if (!function_exists('kug_repo_fetch_categories')) {
    function kug_repo_fetch_categories($dpt = '') {
        global $connection;

        $dpt = mysqli_real_escape_string($connection, (string)$dpt);
        $sql = "SELECT DISTINCT c.cat_id, c.name FROM kp_cat c INNER JOIN kp_items i ON i.cat_id=c.cat_id WHERE i.del_status=2";
        if ($dpt === '2') {
            $sql .= " AND i.dpt='{$dpt}'";
        }
        $sql .= " ORDER BY c.name ASC";
        $rows = [];
        $result = mysqli_query($connection, $sql);
        if ($result) {
            while ($row = mysqli_fetch_assoc($result)) {
                $rows[] = ['cat_id' => (int)$row['cat_id'], 'name' => (string)$row['name']];
            }
        }
        return $rows;
    }
}

if (!function_exists('kug_repo_fetch_subcategories')) {
    function kug_repo_fetch_subcategories($category_id, $dpt = '') {
        global $connection;

        $category_id = (int)$category_id;
        $dpt = mysqli_real_escape_string($connection, (string)$dpt);
        $sql = "SELECT DISTINCT s.sub_id, s.name FROM kp_sub_cat s INNER JOIN kp_items i ON i.sub_id=s.sub_id WHERE s.cat_id='{$category_id}' AND i.del_status=2";
        if ($dpt === '2') {
            $sql .= " AND i.dpt='{$dpt}'";
        }
        $sql .= " ORDER BY s.name ASC";
        $rows = [];
        $result = mysqli_query($connection, $sql);
        if ($result) {
            while ($row = mysqli_fetch_assoc($result)) {
                $rows[] = ['sub_id' => (int)$row['sub_id'], 'name' => (string)$row['name']];
            }
        }
        return $rows;
    }
}

if (!function_exists('kug_repo_fetch_items')) {
    function kug_repo_fetch_items($filters = []) {
        global $connection;

        $where = ["del_status=2"];
        if (!empty($filters['dpt']) && (string)$filters['dpt'] === '2') {
            $where[] = "dpt='2'";
        }
        if (!empty($filters['cat_id'])) {
            $where[] = "cat_id='" . (int)$filters['cat_id'] . "'";
        }
        if (!empty($filters['sub_id'])) {
            $where[] = "sub_id='" . (int)$filters['sub_id'] . "'";
        }

        $sql = "SELECT item_id, name FROM kp_items WHERE " . implode(' AND ', $where) . " ORDER BY name ASC";
        $rows = [];
        $result = mysqli_query($connection, $sql);
        if ($result) {
            while ($row = mysqli_fetch_assoc($result)) {
                $rows[] = ['item_id' => (int)$row['item_id'], 'name' => (string)$row['name']];
            }
        }
        return $rows;
    }
}

if (!function_exists('kug_repo_fetch_po_items')) {
    function kug_repo_fetch_po_items($po_id) {
        global $connection;

        $po_id = (int)$po_id;
        $rows = [];
        $result = mysqli_query($connection, "SELECT * FROM kp_po_items WHERE po_id='{$po_id}' ORDER BY id DESC");
        if ($result) {
            while ($row = mysqli_fetch_assoc($result)) {
                $row['item_name'] = function_exists('get_item') ? (string)get_item($row['item_id']) : ('Item #' . (int)$row['item_id']);
                $rows[] = $row;
            }
        }
        return $rows;
    }
}

if (!function_exists('kug_repo_po_item_exists')) {
    function kug_repo_po_item_exists($po_id, $item_id) {
        global $connection;
        $po_id = (int)$po_id;
        $item_id = (int)$item_id;
        $result = mysqli_query($connection, "SELECT id FROM kp_po_items WHERE po_id='{$po_id}' AND item_id='{$item_id}' LIMIT 1");
        return $result && mysqli_num_rows($result) > 0;
    }
}

if (!function_exists('kug_repo_insert_po_item')) {
    function kug_repo_insert_po_item($po_id, $item_id, $qty, $price, $total, $shift) {
        global $connection;
        $po_id = (int)$po_id;
        $item_id = (int)$item_id;
        $qty = (float)$qty;
        $price = (int)round((float)$price);
        $total = (int)round((float)$total);
        $shift = mysqli_real_escape_string($connection, (string)$shift);
        return mysqli_query($connection, "INSERT INTO kp_po_items(po_id,item_id,qty,price,total,day,status) VALUES('{$po_id}','{$item_id}','{$qty}','{$price}','{$total}','{$shift}','2')");
    }
}

if (!function_exists('kug_repo_delete_po_item')) {
    function kug_repo_delete_po_item($po_id, $item_id) {
        global $connection;
        $po_id = (int)$po_id;
        $item_id = (int)$item_id;
        return mysqli_query($connection, "DELETE FROM kp_po_items WHERE po_id='{$po_id}' AND item_id='{$item_id}'");
    }
}

if (!function_exists('kug_repo_fetch_suppliers')) {
    function kug_repo_fetch_suppliers() {
        global $connection;
        $rows = [];
        $result = mysqli_query($connection, "SELECT sup_id, sup_name FROM kp_suppliers ORDER BY sup_name ASC");
        if ($result) {
            while ($row = mysqli_fetch_assoc($result)) {
                $rows[] = ['sup_id' => (int)$row['sup_id'], 'sup_name' => (string)$row['sup_name']];
            }
        }
        return $rows;
    }
}

if (!function_exists('kug_repo_update_supplier')) {
    function kug_repo_update_supplier($po_id, $sup_id) {
        global $connection;
        $po_id = (int)$po_id;
        $sup_id = (int)$sup_id;
        return mysqli_query($connection, "UPDATE kp_po_status SET sup_id='{$sup_id}' WHERE po_id='{$po_id}'");
    }
}

if (!function_exists('kug_repo_find_open_po_status')) {
    function kug_repo_find_open_po_status($user_id) {
        global $connection;
        $user_id = (int)$user_id;
        $result = mysqli_query($connection, "SELECT * FROM kp_po_status WHERE user_id='{$user_id}' AND po_status=2 ORDER BY datime DESC LIMIT 1");
        return $result ? mysqli_fetch_assoc($result) : null;
    }
}

if (!function_exists('kug_repo_next_po_id')) {
    function kug_repo_next_po_id() {
        if (function_exists('get_id')) {
            return (int)get_id('kp_po_id', 'po_id');
        }
        return 0;
    }
}

if (!function_exists('kug_repo_create_po_status')) {
    function kug_repo_create_po_status($po_id, $user_id, $sup_id = 0) {
        global $connection;
        $po_id = (int)$po_id;
        $user_id = (int)$user_id;
        $sup_id = (int)$sup_id;
        return mysqli_query($connection, "INSERT INTO kp_po_status(po_id,user_id,sup_id,day) VALUES('{$po_id}','{$user_id}','{$sup_id}',CURRENT_DATE)");
    }
}

if (!function_exists('kug_repo_po_has_items')) {
    function kug_repo_po_has_items($po_id) {
        global $connection;
        $po_id = (int)$po_id;
        $result = mysqli_query($connection, "SELECT id FROM kp_po_items WHERE po_id='{$po_id}' LIMIT 1");
        return $result && mysqli_num_rows($result) > 0;
    }
}

if (!function_exists('kug_repo_delete_po_status')) {
    function kug_repo_delete_po_status($po_id) {
        global $connection;
        $po_id = (int)$po_id;
        return mysqli_query($connection, "DELETE FROM kp_po_status WHERE po_id='{$po_id}'");
    }
}

if (!function_exists('kug_repo_delete_po_items_by_status')) {
    function kug_repo_delete_po_items_by_status($po_id, $status = 2) {
        global $connection;
        $po_id = (int)$po_id;
        $status = (int)$status;
        return mysqli_query($connection, "DELETE FROM kp_po_items WHERE po_id='{$po_id}' AND status='{$status}'");
    }
}

if (!function_exists('kug_repo_po_total')) {
    function kug_repo_po_total($po_id) {
        global $connection;
        $po_id = (int)$po_id;
        $result = mysqli_query($connection, "SELECT SUM(total) AS gtotal FROM kp_po_items WHERE po_id='{$po_id}'");
        if ($result) {
            $row = mysqli_fetch_assoc($result);
            return (float)($row['gtotal'] ?? 0);
        }
        return 0;
    }
}

if (!function_exists('kug_repo_insert_or_update_po_header')) {
    function kug_repo_insert_or_update_po_header($data) {
        global $connection;

        $po_id = (int)$data['po_id'];
        $counter = (int)$data['counter'];
        $sup_id = (int)$data['sup_id'];
        $user_id = (int)$data['user_id'];
        $shift = mysqli_real_escape_string($connection, (string)$data['shift']);
        $amount = (float)$data['amount'];
        $buy_tax = (int)$data['buy_tax'];
        $vatable = (float)$data['vatable'];
        $vat = (float)$data['vat'];
        $balance = (float)$data['balance'];
        $refno = mysqli_real_escape_string($connection, (string)$data['refno']);
        $appr_status = (int)$data['appr_status'];
        $appr_user = (int)$data['appr_user'];
        $rec_status = (int)$data['rec_status'];
        $rec_staff = (int)$data['rec_staff'];
        $stock_status = (int)$data['stock_status'];
        $s2s = (int)$data['s2s'];
        $po_exists = function_exists('column_count') ? (int)column_count('kp_po', 'id', "po_id='{$po_id}'") : 0;

        if ($po_exists < 1) {
            return mysqli_query($connection, "INSERT INTO kp_po(po_id,req_id,sup_id,ord_user,user_id,shift,po_date,amount,disc,tax_by,tax_type,tax_perc,vatable,vat,cf_bal,refno,appr_status,appr_user,appr_datime,rec_status,rec_staff,rec_date,rec_datime,stock_status,s2s) VALUES('{$po_id}','{$counter}','{$sup_id}','{$user_id}','{$user_id}','{$shift}',CURRENT_DATE,'{$amount}','0','{$buy_tax}','VAT','0','{$vatable}','{$vat}','{$balance}','{$refno}','{$appr_status}','{$appr_user}'," . ($appr_status === 1 ? 'CURRENT_TIMESTAMP' : 'NULL') . ",'{$rec_status}','{$rec_staff}',CURRENT_DATE," . ($rec_status === 1 ? 'CURRENT_TIMESTAMP' : 'NULL') . ",'{$stock_status}','{$s2s}')");
        }

        return mysqli_query($connection, "UPDATE kp_po SET req_id='{$counter}',sup_id='{$sup_id}',ord_user='{$user_id}',user_id='{$user_id}',shift='{$shift}',po_date=CURRENT_DATE,amount='{$amount}',disc='0',tax_by='{$buy_tax}',tax_type='VAT',tax_perc='0',vatable='{$vatable}',vat='{$vat}',cf_bal='{$balance}',refno='{$refno}',appr_status='{$appr_status}',appr_user='{$appr_user}',appr_datime=" . ($appr_status === 1 ? 'CURRENT_TIMESTAMP' : 'NULL') . ",rec_status='{$rec_status}',stock_status='{$stock_status}',s2s='{$s2s}',rec_staff='{$rec_staff}',rec_date=CURRENT_DATE,rec_datime=" . ($rec_status === 1 ? 'CURRENT_TIMESTAMP' : 'NULL') . " WHERE po_id='{$po_id}'");
    }
}

if (!function_exists('kug_repo_update_po_items_for_submit')) {
    function kug_repo_update_po_items_for_submit($po_id, $rec_status, $rec_staff, $s2s) {
        global $connection;
        $po_id = (int)$po_id;
        $rec_status = (int)$rec_status;
        $rec_staff = (int)$rec_staff;
        $s2s = (int)$s2s;
        return mysqli_query($connection, "UPDATE kp_po_items SET status='{$rec_status}', rec_price=price, rec_qty=qty, rec_total=total, rec_user='{$rec_staff}', rec_status='{$rec_status}', s2s='{$s2s}' WHERE po_id='{$po_id}'");
    }
}

if (!function_exists('kug_repo_insert_grn_header')) {
    function kug_repo_insert_grn_header($grn_id, $po_id, $user_id, $sup_id, $amount) {
        global $connection;
        $grn_id = (int)$grn_id;
        $po_id = (int)$po_id;
        $user_id = (int)$user_id;
        $sup_id = (int)$sup_id;
        $amount = (float)$amount;
        return mysqli_query($connection, "INSERT INTO kp_grn(grn_id,po_id,user_id,sup_id,amount,bal,day) VALUES('{$grn_id}','{$po_id}','{$user_id}','{$sup_id}','{$amount}','{$amount}',CURRENT_DATE)");
    }
}

if (!function_exists('kug_repo_insert_grn_item')) {
    function kug_repo_insert_grn_item($grn_id, $item_id, $qty, $price, $total, $user_id) {
        global $connection;
        $grn_id = (int)$grn_id;
        $item_id = (int)$item_id;
        $qty = (float)$qty;
        $price = (float)$price;
        $total = (float)$total;
        $user_id = (int)$user_id;
        return mysqli_query($connection, "INSERT INTO kp_grn_items(grn_id,item_id,qty,price,total,user_id,day,s2s) VALUES('{$grn_id}','{$item_id}','{$qty}','{$price}','{$total}','{$user_id}',CURRENT_DATE,1)");
    }
}

if (!function_exists('kug_repo_update_item_buy_price')) {
    function kug_repo_update_item_buy_price($item_id, $price) {
        global $connection;
        $item_id = (int)$item_id;
        $price = (float)$price;
        return mysqli_query($connection, "UPDATE kp_items SET b_price='{$price}' WHERE item_id='{$item_id}'");
    }
}

if (!function_exists('kug_repo_update_item_stock')) {
    function kug_repo_update_item_stock($item_id, $column, $qty) {
        global $connection;
        $item_id = (int)$item_id;
        $column = preg_replace('/[^a-zA-Z0-9_]/', '', (string)$column);
        $qty = (float)$qty;
        return mysqli_query($connection, "UPDATE kp_items SET `{$column}`=COALESCE(`{$column}`,0)+'{$qty}' WHERE item_id='{$item_id}'");
    }
}

if (!function_exists('kug_repo_insert_stock_track')) {
    function kug_repo_insert_stock_track($item_id, $po_id, $grn_id, $user_id, $qty, $counter, $dpt) {
        global $connection;
        $item_id = (int)$item_id;
        $po_id = (int)$po_id;
        $grn_id = (int)$grn_id;
        $user_id = (int)$user_id;
        $qty = (float)$qty;
        $counter = (int)$counter;
        $dpt = (string)$dpt;
        foreach ([
            'trans' => "VARCHAR(155) NOT NULL DEFAULT ''",
            'trans_id' => 'INT NOT NULL DEFAULT 0',
        ] as $trackColumn => $trackDefinition) {
            $columnEsc = mysqli_real_escape_string($connection, $trackColumn);
            $columnCheck = mysqli_query($connection, "SHOW COLUMNS FROM kp_stock_track LIKE '{$columnEsc}'");
            if ($columnCheck && mysqli_num_rows($columnCheck) < 1) {
                mysqli_query($connection, "ALTER TABLE kp_stock_track ADD COLUMN `{$trackColumn}` {$trackDefinition}");
            }
        }
        return mysqli_query($connection, "INSERT INTO kp_stock_track(item_id,po_id,grn_id,user_id,qty,status,`trans`,trans_id,stock_counter,shift,dpt) VALUES('{$item_id}','{$po_id}','{$grn_id}','{$user_id}','{$qty}','IN','PURCHASE','{$po_id}','{$counter}',CURRENT_DATE,'{$dpt}')");
    }
}

if (!function_exists('kug_repo_update_supplier_balance')) {
    function kug_repo_update_supplier_balance($sup_id, $amount) {
        global $connection;
        $sup_id = (int)$sup_id;
        $amount = (float)$amount;
        return mysqli_query($connection, "UPDATE kp_suppliers SET sup_bal=sup_bal+'{$amount}' WHERE sup_id='{$sup_id}'");
    }
}

if (!function_exists('kug_repo_supplier_exists_by_name')) {
    function kug_repo_supplier_exists_by_name($sup_name) {
        $safe_name = trim((string)$sup_name);
        if ($safe_name === '') {
            return false;
        }
        return function_exists('column_count') ? (int)column_count('kp_suppliers', 'sup_id', "sup_name='" . addslashes($safe_name) . "' AND del_status=2") > 0 : false;
    }
}

if (!function_exists('kug_repo_next_supplier_id')) {
    function kug_repo_next_supplier_id() {
        global $connection;

        $sup_id = 1;
        $max_supplier_id = mysqli_query($connection, "SELECT MAX(sup_id) AS max_id FROM kp_suppliers");
        if ($max_supplier_id) {
            $max_row = mysqli_fetch_assoc($max_supplier_id);
            $sup_id = ((int)($max_row['max_id'] ?? 0)) + 1;
        }

        while (function_exists('column_count') && (int)column_count('kp_suppliers', 'sup_id', "sup_id='{$sup_id}'") > 0) {
            $sup_id++;
        }

        return $sup_id;
    }
}

if (!function_exists('kug_repo_insert_supplier')) {
    function kug_repo_insert_supplier($sup_id, $sup_name, $sup_phone, $sup_pin, $sup_vat) {
        global $connection;

        $sup_id = (int)$sup_id;
        $safe_name = mysqli_real_escape_string($connection, (string)$sup_name);
        $safe_phone = mysqli_real_escape_string($connection, (string)$sup_phone);
        $safe_pin = mysqli_real_escape_string($connection, (string)$sup_pin);
        $safe_vat = mysqli_real_escape_string($connection, (string)$sup_vat);

        return mysqli_query(
            $connection,
            "INSERT INTO kp_suppliers(sup_id,sup_name,sup_phone,sup_pin,sup_vat)
             VALUES('{$sup_id}','{$safe_name}','{$safe_phone}','{$safe_pin}','{$safe_vat}')"
        );
    }
}
