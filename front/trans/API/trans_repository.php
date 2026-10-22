<?php

if (!function_exists('trans_repo_context')) {
    function trans_repo_context($connection, $user_id)
    {
        $user_id = (int)$user_id;
        $counter = $user_id > 0 ? (int)get_column('kp_users', 'counter', "user_id='$user_id'") : 0;
        $dpt = $counter > 0 ? (int)get_column('kp_counters', 'dpt', "cntr_id='$counter'") : 0;

        return [
            'user_id' => $user_id,
            'counter_id' => $counter,
            'counter_name' => $counter > 0 ? (string)get_counter($counter) : '',
            'department_id' => $dpt,
            'shift' => (string)get_column('kp_shift', 'shift', '1'),
            'trans_id' => isset($_SESSION['trans']) ? (int)$_SESSION['trans'] : 0,
        ];
    }
}

if (!function_exists('trans_repo_category_rows')) {
    function trans_repo_category_rows($connection, $department_id)
    {
        $department_id = (int)$department_id;
        $where = '';
        if ($department_id === 2) {
            $where = "WHERE dpt='$department_id'";
        }

        $rows = [];
        $res = mysqli_query($connection, "SELECT cat_id, name FROM kp_cat $where ORDER BY name ASC");
        while ($res && $row = mysqli_fetch_assoc($res)) {
            $rows[] = [
                'cat_id' => (int)($row['cat_id'] ?? 0),
                'name' => (string)($row['name'] ?? ''),
            ];
        }

        return $rows;
    }
}

if (!function_exists('trans_repo_subcategory_rows')) {
    function trans_repo_subcategory_rows($connection, $cat_id)
    {
        $cat_id = (int)$cat_id;
        $rows = [];
        $res = mysqli_query($connection, "SELECT sub_id, name FROM kp_sub_cat WHERE cat_id='$cat_id' ORDER BY name ASC");
        while ($res && $row = mysqli_fetch_assoc($res)) {
            $rows[] = [
                'sub_id' => (int)($row['sub_id'] ?? 0),
                'name' => (string)($row['name'] ?? ''),
            ];
        }
        return $rows;
    }
}

if (!function_exists('trans_repo_item_rows_by_cat')) {
    function trans_repo_item_rows_by_cat($connection, $cat_id)
    {
        $cat_id = (int)$cat_id;
        $rows = [];
        $res = mysqli_query($connection, "SELECT item_id, name FROM kp_items WHERE cat_id='$cat_id' AND del_status=2 AND stock=1 ORDER BY name ASC");
        while ($res && $row = mysqli_fetch_assoc($res)) {
            $rows[] = [
                'item_id' => (int)($row['item_id'] ?? 0),
                'name' => (string)($row['name'] ?? ''),
            ];
        }
        return $rows;
    }
}

if (!function_exists('trans_repo_item_rows_by_sub')) {
    function trans_repo_item_rows_by_sub($connection, $sub_id)
    {
        $sub_id = (int)$sub_id;
        $rows = [];
        $res = mysqli_query($connection, "SELECT item_id, name FROM kp_items WHERE sub_id='$sub_id' AND del_status=2 ORDER BY name ASC");
        while ($res && $row = mysqli_fetch_assoc($res)) {
            $rows[] = [
                'item_id' => (int)($row['item_id'] ?? 0),
                'name' => (string)($row['name'] ?? ''),
            ];
        }
        return $rows;
    }
}

if (!function_exists('trans_repo_transfer_items')) {
    function trans_repo_transfer_items($connection, $trans_id)
    {
        $trans_id = (int)$trans_id;
        $rows = [];
        $res = mysqli_query($connection, "SELECT item_id, qty FROM kp_counter_trans_items WHERE trans_id='$trans_id' ORDER BY item_id ASC");
        while ($res && $row = mysqli_fetch_assoc($res)) {
            $item_id = (int)($row['item_id'] ?? 0);
            $rows[] = [
                'item_id' => $item_id,
                'item_name' => (string)get_item($item_id),
                'qty' => (float)($row['qty'] ?? 0),
            ];
        }
        return $rows;
    }
}

if (!function_exists('trans_repo_transfer_receive_items')) {
    function trans_repo_transfer_receive_items($connection, $trans_id)
    {
        $trans_id = (int)$trans_id;
        $rows = [];
        $res = mysqli_query($connection, "SELECT item_id, qty, status FROM kp_counter_trans_items WHERE trans_id='$trans_id' AND status=2 ORDER BY item_id ASC");
        while ($res && $row = mysqli_fetch_assoc($res)) {
            $item_id = (int)($row['item_id'] ?? 0);
            $rows[] = [
                'item_id' => $item_id,
                'item_name' => (string)get_item($item_id),
                'qty' => (float)($row['qty'] ?? 0),
                'status' => (int)($row['status'] ?? 0),
            ];
        }
        return $rows;
    }
}
