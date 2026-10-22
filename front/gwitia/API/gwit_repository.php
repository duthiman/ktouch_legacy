<?php

if (!function_exists('gwit_repo_get_user_context')) {
    function gwit_repo_get_user_context($connection, $user_id)
    {
        $user_id = (int)$user_id;
        $counter = (int)get_column('kp_users', 'counter', "user_id='$user_id'");
        $dpt = (int)get_column('kp_counters', 'dpt', "cntr_id='$counter'");
        $filter = '';
        if ($dpt === 2) {
            $filter = " WHERE dpt='$dpt'";
        }

        return [
            'user_id' => $user_id,
            'counter' => $counter,
            'counter_name' => $counter > 0 ? get_counter($counter) : '',
            'dpt' => $dpt,
            'category_filter' => $filter,
        ];
    }
}

if (!function_exists('gwit_repo_get_categories')) {
    function gwit_repo_get_categories($connection, $filter)
    {
        $rows = [];
        $query = mysqli_query($connection, "SELECT cat_id, name FROM kp_cat{$filter} ORDER BY name ASC");
        if ($query) {
            while ($row = mysqli_fetch_assoc($query)) {
                $rows[] = [
                    'cat_id' => (int)$row['cat_id'],
                    'name' => $row['name'],
                ];
            }
        }
        return $rows;
    }
}

if (!function_exists('gwit_repo_get_subcategories')) {
    function gwit_repo_get_subcategories($connection, $cat_id)
    {
        $cat_id = (int)$cat_id;
        $rows = [];
        $query = mysqli_query($connection, "SELECT sub_id, name FROM kp_sub_cat WHERE cat_id='$cat_id' ORDER BY name ASC");
        if ($query) {
            while ($row = mysqli_fetch_assoc($query)) {
                $rows[] = [
                    'sub_id' => (int)$row['sub_id'],
                    'name' => $row['name'],
                ];
            }
        }
        return $rows;
    }
}

if (!function_exists('gwit_repo_get_items_by_category')) {
    function gwit_repo_get_items_by_category($connection, $cat_id, $dpt)
    {
        $cat_id = (int)$cat_id;
        $dpt = (int)$dpt;
        $rows = [];
        $query = mysqli_query($connection, "SELECT item_id, name, port_status, portions FROM kp_items WHERE cat_id='$cat_id' AND dpt='$dpt' AND stock=1 ORDER BY name ASC");
        if ($query) {
            while ($row = mysqli_fetch_assoc($query)) {
                $rows[] = [
                    'item_id' => (int)$row['item_id'],
                    'name' => $row['name'],
                    'port_status' => (int)$row['port_status'],
                    'portions' => $row['portions'],
                ];
            }
        }
        return $rows;
    }
}

if (!function_exists('gwit_repo_get_items_by_subcategory')) {
    function gwit_repo_get_items_by_subcategory($connection, $sub_id, $dpt)
    {
        $sub_id = (int)$sub_id;
        $dpt = (int)$dpt;
        $rows = [];
        $query = mysqli_query($connection, "SELECT item_id, name, port_status, portions FROM kp_items WHERE sub_id='$sub_id' AND dpt='$dpt' AND stock=1 ORDER BY name ASC");
        if ($query) {
            while ($row = mysqli_fetch_assoc($query)) {
                $rows[] = [
                    'item_id' => (int)$row['item_id'],
                    'name' => $row['name'],
                    'port_status' => (int)$row['port_status'],
                    'portions' => $row['portions'],
                ];
            }
        }
        return $rows;
    }
}

if (!function_exists('gwit_repo_get_req_items')) {
    function gwit_repo_get_req_items($connection, $req_id)
    {
        $req_id = (int)$req_id;
        $rows = [];
        $query = mysqli_query($connection, "SELECT id, item_id, req_qty FROM kp_counter_req_items WHERE req_id='$req_id' ORDER BY id DESC");
        if ($query) {
            while ($row = mysqli_fetch_assoc($query)) {
                $item_id = (int)$row['item_id'];
                $rows[] = [
                    'id' => (int)$row['id'],
                    'item_id' => $item_id,
                    'item_name' => get_item($item_id),
                    'req_qty' => (float)$row['req_qty'],
                ];
            }
        }
        return $rows;
    }
}

if (!function_exists('gwit_repo_get_item')) {
    function gwit_repo_get_item($connection, $item_id)
    {
        $item_id = (int)$item_id;
        $query = mysqli_query($connection, "SELECT item_id, name, port_status, portions FROM kp_items WHERE item_id='$item_id' LIMIT 1");
        return $query ? mysqli_fetch_assoc($query) : null;
    }
}

if (!function_exists('gwit_repo_req_item_exists')) {
    function gwit_repo_req_item_exists($connection, $req_id, $item_id)
    {
        $req_id = (int)$req_id;
        $item_id = (int)$item_id;
        $query = mysqli_query($connection, "SELECT id FROM kp_counter_req_items WHERE item_id='$item_id' AND req_id='$req_id' LIMIT 1");
        return $query && mysqli_num_rows($query) > 0;
    }
}

if (!function_exists('gwit_repo_add_req_item')) {
    function gwit_repo_add_req_item($connection, $req_id, $user_id, $item_id, $qty, $shift, $counter)
    {
        $req_id = (int)$req_id;
        $user_id = (int)$user_id;
        $item_id = (int)$item_id;
        $qty = (float)$qty;
        $shift = mysqli_real_escape_string($connection, (string)$shift);
        $counterValue = mysqli_real_escape_string($connection, (string)$counter);

        return mysqli_query(
            $connection,
            "INSERT INTO kp_counter_req_items(req_id,req_user,item_id,req_qty,req_shift,counter)
             VALUES('$req_id','$user_id','$item_id','$qty','$shift','$counterValue')"
        );
    }
}

if (!function_exists('gwit_repo_delete_req_item')) {
    function gwit_repo_delete_req_item($connection, $req_id, $item_id)
    {
        $req_id = (int)$req_id;
        $item_id = (int)$item_id;
        return mysqli_query($connection, "DELETE FROM kp_counter_req_items WHERE req_id='$req_id' AND item_id='$item_id'");
    }
}

if (!function_exists('gwit_repo_delete_req_items')) {
    function gwit_repo_delete_req_items($connection, $req_id)
    {
        $req_id = (int)$req_id;
        return mysqli_query($connection, "DELETE FROM kp_counter_req_items WHERE req_id='$req_id'");
    }
}

if (!function_exists('gwit_repo_req_exists')) {
    function gwit_repo_req_exists($req_id)
    {
        $req_id = (int)$req_id;
        return (int)column_count('kp_counter_req', 'req_id', "req_id='$req_id'") > 0;
    }
}

if (!function_exists('gwit_repo_create_req')) {
    function gwit_repo_create_req($connection, $req_id, $user_id, $shift, $counter)
    {
        $req_id = (int)$req_id;
        $user_id = (int)$user_id;
        $shift = mysqli_real_escape_string($connection, (string)$shift);
        $counter = mysqli_real_escape_string($connection, (string)$counter);
        return mysqli_query($connection, "INSERT INTO kp_counter_req(req_id,req_user,req_shift,counter) VALUES('$req_id','$user_id','$shift','$counter')");
    }
}

if (!function_exists('gwit_repo_update_print_counter')) {
    function gwit_repo_update_print_counter($connection, $req_id, $counter_id)
    {
        $req_id = (int)$req_id;
        $counter_id = (int)$counter_id;
        return mysqli_query($connection, "UPDATE kp_counter_req SET print_cntr_id='$counter_id' WHERE req_id='$req_id'");
    }
}

if (!function_exists('gwit_repo_clear_req_status')) {
    function gwit_repo_clear_req_status($connection, $req_id)
    {
        $req_id = (int)$req_id;
        return mysqli_query($connection, "DELETE FROM kp_counter_req_status WHERE req_id='$req_id'");
    }
}

if (!function_exists('gwit_repo_get_print_counters')) {
    function gwit_repo_get_print_counters($connection)
    {
        $rows = [];
        $query = mysqli_query($connection, "SELECT cntr_id, name, dpt FROM kp_counters WHERE del_status=2 ORDER BY dpt ASC, cntr_id ASC");
        if ($query) {
            while ($row = mysqli_fetch_assoc($query)) {
                $rows[] = [
                    'cntr_id' => (int)$row['cntr_id'],
                    'name' => $row['name'],
                    'dpt' => (int)$row['dpt'],
                ];
            }
        }
        return $rows;
    }
}
