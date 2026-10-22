<?php
/**
 * Data access layer for Billing
 */
require_once __DIR__ . '/../../../inc/connection.php';

function repo_find_active_child($parent_id) {
    global $connection;
    $id = mysqli_real_escape_string($connection, $parent_id);
    $sql = "SELECT * FROM kp_bills WHERE parent_id = '$id' AND status = 'active' LIMIT 1";
    $result = mysqli_query($connection, $sql);
    return mysqli_fetch_assoc($result);
}

function repo_get_open_bill($bill_id = null) {
    global $connection;
    if ($bill_id) {
        $id = mysqli_real_escape_string($connection, $bill_id);
        $where = "item_id = '$id'";
    } else {
        $where = "status = 'open'";
    }
    
    $sql = "SELECT * FROM kp_bills WHERE $where ORDER BY item_id DESC LIMIT 1";
    $result = mysqli_query($connection, $sql);
    return mysqli_fetch_assoc($result);
}

function repo_save_identifier($bill_id, $identifier) {
    global $connection;
    $id = mysqli_real_escape_string($connection, $bill_id);
    $val = mysqli_real_escape_string($connection, $identifier);
    
    $sql = "UPDATE kp_bills SET identifier = '$val' WHERE item_id = '$id'";
    return mysqli_query($connection, $sql);
}

function repo_get_bill_total($bill_id) {
    global $connection;
    $sid = mysqli_real_escape_string($connection, $bill_id);
    // Rule 4: Sum both session_flag 1 (sent) and 2 (active) for full open total
    return (float)column_total('kp_sale_print', 'total', "sale_id = '$sid' AND session_flag IN (1, 2)");
}

function repo_get_cart_items($sale_id) {
    global $connection;
    $sid = mysqli_real_escape_string($connection, $sale_id);
    // Rule 2: Show only active-session items (session_flag = 2)
    $sql = "SELECT * FROM kp_sale_print WHERE sale_id = '$sid' AND session_flag = 2 ORDER BY item_id ASC";
    $result = mysqli_query($connection, $sql);
    $items = [];
    while ($row = mysqli_fetch_assoc($result)) {
        $items[] = $row;
    }
    return $items;
}

function repo_commit_session_items($sale_id) {
    global $connection;
    $sid = mysqli_real_escape_string($connection, $sale_id);
    // Rule 3: After sending, update current items to 'already sent' status
    $sql = "UPDATE kp_sale_print SET session_flag = 1 WHERE sale_id = '$sid' AND session_flag = 2";
    return mysqli_query($connection, $sql);
}

function repo_finalize_and_move_items($sale_id, $shift) {
    global $connection;
    $sid = mysqli_real_escape_string($connection, $sale_id);
    $sft = mysqli_real_escape_string($connection, $shift);

    // Rule 6: Move all items (1 & 2) to kp_sale_items
    $move_sql = "INSERT INTO kp_sale_items (sale_id, item_id, user_id, qty, dpt, price, total, shift, counter, hour, stock_counter, attr_id)
                 SELECT sale_id, item_id, user_id, qty, dpt, price, total, '$sft', counter, hour, stock_counter, attr_id
                 FROM kp_sale_print 
                 WHERE sale_id = '$sid' AND session_flag IN (1, 2)";
    
    if (mysqli_query($connection, $move_sql)) {
        // Clear print table for this sale
        return mysqli_query($connection, "DELETE FROM kp_sale_print WHERE sale_id = '$sid'");
    }
    return false;
}

function repo_add_item_to_print($data) {
    global $connection;
    // Rule 1: New items always inserted with session_flag = 2
    $data['session_flag'] = 2;
    // Logic for insertion/update similar to existing legacy code but using the repository pattern...
}

// Additional repo functions follow the same pattern...