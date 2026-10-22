<?php

if (!function_exists('repo_fetch_users')) {
    function repo_fetch_users($connection)
    {
        $rows = [];
        $result = mysqli_query($connection, "SELECT user_id, username FROM kp_users WHERE role_id!=1 ORDER BY username ASC");
        while ($result && ($row = mysqli_fetch_assoc($result))) {
            $rows[] = ['user_id' => (int)$row['user_id'], 'username' => (string)$row['username']];
        }
        return $rows;
    }
}

if (!function_exists('repo_fetch_user_sales')) {
    function repo_fetch_user_sales($connection, $user_id, $from, $to, $counter_id = 0)
    {
        $user_id = (int)$user_id;
        $from = mysqli_real_escape_string($connection, $from);
        $to = mysqli_real_escape_string($connection, $to);
        $counter_id = (int)$counter_id;
        $counter_where = $counter_id > 0 ? " AND s.counter='$counter_id'" : '';
        $rows = [];
        $sql = "SELECT s.sale_id,s.user_id,s.shift,s.amount,s.counter,
                    COALESCE(c.name,'') AS counter_name,
                    COUNT(si.id) AS item_lines,
                    COALESCE(SUM(si.qty),0) AS item_qty,
                    COALESCE(SUM(si.total),0) AS item_total
                FROM kp_sales s
                LEFT JOIN kp_counters c ON c.cntr_id=s.counter
                LEFT JOIN kp_sale_items si ON si.sale_id=s.sale_id
                WHERE s.user_id='$user_id' AND s.shift BETWEEN '$from' AND '$to' $counter_where
                GROUP BY s.sale_id,s.user_id,s.shift,s.amount,s.counter,c.name
                ORDER BY s.shift DESC,s.sale_id DESC";
        $result = mysqli_query($connection, $sql);
        while ($result && ($row = mysqli_fetch_assoc($result))) {
            $rows[] = [
                'sale_id' => (int)$row['sale_id'],
                'user_id' => (int)$row['user_id'],
                'shift' => (string)$row['shift'],
                'amount' => (float)$row['amount'],
                'counter_id' => (int)$row['counter'],
                'counter_name' => (string)$row['counter_name'],
                'item_lines' => (int)$row['item_lines'],
                'item_qty' => (float)$row['item_qty'],
                'item_total' => (float)$row['item_total'],
            ];
        }
        return $rows;
    }
}

if (!function_exists('repo_fetch_sale_items')) {
    function repo_fetch_sale_items($connection, $sale_id)
    {
        $sale_id = (int)$sale_id;
        $rows = [];
        $sql = "SELECT si.id,si.sale_id,si.item_id,COALESCE(i.name,'') AS item_name,
                    COALESCE(c.name,'Uncategorised') AS category_name,
                    si.user_id,si.qty,si.price,si.total,si.dpt,si.counter,si.stock_counter,
                    si.attr_id,COALESCE(a.name,'') AS attr_name
                FROM kp_sale_items si
                LEFT JOIN kp_items i ON i.item_id=si.item_id
                LEFT JOIN kp_cat c ON c.cat_id=i.cat_id
                LEFT JOIN kp_items_attributes a ON a.id=si.attr_id
                WHERE si.sale_id='$sale_id'
                ORDER BY si.id ASC";
        $result = mysqli_query($connection, $sql);
        while ($result && ($row = mysqli_fetch_assoc($result))) {
            $rows[] = [
                'id' => (int)$row['id'],
                'sale_id' => (int)$row['sale_id'],
                'item_id' => (int)$row['item_id'],
                'item_name' => (string)$row['item_name'],
                'category_name' => (string)$row['category_name'],
                'user_id' => (int)$row['user_id'],
                'qty' => (float)$row['qty'],
                'price' => (float)$row['price'],
                'total' => (float)$row['total'],
                'department' => (int)$row['dpt'],
                'counter_id' => (int)$row['counter'],
                'stock_counter_id' => (int)$row['stock_counter'],
                'attr_id' => (int)$row['attr_id'] > 0 ? (int)$row['attr_id'] : null,
                'attr_name' => (string)$row['attr_name'],
                'attribute' => (int)$row['attr_id'] > 0 ? ['id' => (int)$row['attr_id'], 'name' => (string)$row['attr_name']] : null,
            ];
        }
        return $rows;
    }
}

if (!function_exists('repo_fetch_user_item_summary')) {
    function repo_fetch_user_item_summary($connection, $user_id, $from, $to, $counter_id = 0)
    {
        $user_id = (int)$user_id;
        $from = mysqli_real_escape_string($connection, $from);
        $to = mysqli_real_escape_string($connection, $to);
        $counter_id = (int)$counter_id;
        $counter_where = $counter_id > 0 ? " AND si.counter='$counter_id'" : '';
        $rows = [];
        $sql = "SELECT COALESCE(cn.cntr_id,0) AS counter_id,
                    COALESCE(cn.name,'Unknown counter') AS counter_name,
                    COALESCE(c.name,'Uncategorised') AS category_name,
                    si.item_id,COALESCE(i.name,'Unknown item') AS item_name,
                    COUNT(DISTINCT si.sale_id) AS sale_count,
                    COALESCE(SUM(si.qty),0) AS qty,
                    COALESCE(SUM(si.total),0) AS total
                FROM kp_sale_items si
                INNER JOIN kp_sales s ON s.sale_id=si.sale_id AND s.user_id='$user_id'
                    AND s.shift BETWEEN '$from' AND '$to' $counter_where
                LEFT JOIN kp_items i ON i.item_id=si.item_id
                LEFT JOIN kp_cat c ON c.cat_id=i.cat_id
                LEFT JOIN kp_counters cn ON cn.cntr_id=si.counter
                GROUP BY cn.cntr_id,cn.name,c.name,i.name,si.item_id
                ORDER BY counter_name,category_name,item_name";
        $result = mysqli_query($connection, $sql);
        while ($result && ($row = mysqli_fetch_assoc($result))) {
            $rows[] = [
                'counter_id' => (int)$row['counter_id'],
                'counter_name' => (string)$row['counter_name'],
                'item_id' => (int)$row['item_id'],
                'category_name' => (string)$row['category_name'],
                'item_name' => (string)$row['item_name'],
                'sale_count' => (int)$row['sale_count'],
                'qty' => (float)$row['qty'],
                'total' => (float)$row['total'],
            ];
        }
        return $rows;
    }
}

if (!function_exists('repo_fetch_item_summary')) {
    function repo_fetch_item_summary($connection, $item_id, $from, $to)
    {
        $item_id = (int)$item_id;
        $from = mysqli_real_escape_string($connection, $from);
        $to = mysqli_real_escape_string($connection, $to);
        $rows = [];
        $sql = "SELECT si.sale_id,si.shift,si.user_id,COALESCE(u.username,'') AS username,
                    si.qty,si.price,si.total
                FROM kp_sale_items si
                LEFT JOIN kp_users u ON u.user_id=si.user_id
                WHERE si.shift BETWEEN '$from' AND '$to' AND si.item_id='$item_id'
                ORDER BY si.sale_id DESC,si.id ASC";
        $result = mysqli_query($connection, $sql);
        while ($result && ($row = mysqli_fetch_assoc($result))) {
            $rows[] = [
                'sale_id' => (int)$row['sale_id'],
                'shift' => (string)$row['shift'],
                'user_id' => (int)$row['user_id'],
                'username' => (string)$row['username'],
                'qty' => (float)$row['qty'],
                'price' => (float)$row['price'],
                'total' => (float)$row['total'],
            ];
        }
        return $rows;
    }
}

if (!function_exists('repo_fetch_counter_item_summary')) {
    function repo_fetch_counter_item_summary($connection, $counter_id, $from, $to)
    {
        $counter_id = (int)$counter_id;
        $from = mysqli_real_escape_string($connection, $from);
        $to = mysqli_real_escape_string($connection, $to);
        $rows = [];
        $counter_where = $counter_id > 0 ? "si.counter='$counter_id'" : '1=1';
        $sql = "SELECT COALESCE(cn.cntr_id,0) AS counter_id,COALESCE(cn.name,'Unknown counter') AS counter_name,
                    COALESCE(c.name,'Uncategorised') AS category_name,
                    COALESCE(i.name,'Unknown item') AS item_name,
                    si.item_id,si.price,COALESCE(SUM(si.qty),0) AS qty,
                    COALESCE(SUM(si.total),0) AS total
                FROM kp_sale_items si
                LEFT JOIN kp_items i ON i.item_id=si.item_id
                LEFT JOIN kp_cat c ON c.cat_id=i.cat_id
                LEFT JOIN kp_counters cn ON cn.cntr_id=si.counter
                WHERE $counter_where AND si.shift BETWEEN '$from' AND '$to'
                GROUP BY cn.cntr_id,cn.name,c.name,i.name,si.item_id,si.price
                ORDER BY counter_name,category_name,item_name";
        $result = mysqli_query($connection, $sql);
        while ($result && ($row = mysqli_fetch_assoc($result))) {
            $rows[] = [
                'counter_id' => (int)$row['counter_id'],
                'counter_name' => (string)$row['counter_name'],
                'category_name' => (string)$row['category_name'],
                'item_id' => (int)$row['item_id'],
                'item_name' => (string)$row['item_name'],
                'qty' => (float)$row['qty'],
                'price' => (float)$row['price'],
                'total' => (float)$row['total'],
            ];
        }
        return $rows;
    }
}

if (!function_exists('repo_fetch_item_orders')) {
    function repo_fetch_item_orders($connection, $user_id, $item_id, $from, $to, $counter_id = 0)
    {
        $user_id = (int)$user_id;
        $item_id = (int)$item_id;
        $counter_id = (int)$counter_id;
        $from = mysqli_real_escape_string($connection, $from);
        $to = mysqli_real_escape_string($connection, $to);
        $counter_where = $counter_id > 0 ? " AND s.counter='$counter_id'" : '';
        $orders = [];
        $sql = "SELECT DISTINCT s.sale_id,s.user_id,s.shift,s.amount,s.counter,
                    COALESCE(c.name,'') AS counter_name
                FROM kp_sales s
                INNER JOIN kp_sale_items selected ON selected.sale_id=s.sale_id
                    AND selected.item_id='$item_id'
                LEFT JOIN kp_counters c ON c.cntr_id=s.counter
                WHERE s.user_id='$user_id' AND s.shift BETWEEN '$from' AND '$to' $counter_where
                ORDER BY s.shift DESC,s.sale_id DESC";
        $result = mysqli_query($connection, $sql);
        while ($result && ($row = mysqli_fetch_assoc($result))) {
            $orders[] = [
                'sale_id' => (int)$row['sale_id'],
                'user_id' => (int)$row['user_id'],
                'shift' => (string)$row['shift'],
                'amount' => (float)$row['amount'],
                'counter_id' => (int)$row['counter'],
                'counter_name' => (string)$row['counter_name'],
                'items' => repo_fetch_sale_items($connection, (int)$row['sale_id']),
            ];
        }
        return $orders;
    }
}

if (!function_exists('repo_fetch_user_returns')) {
    function repo_fetch_user_returns($connection, $user_id, $from, $to)
    {
        $user_id = (int)$user_id;
        $from = mysqli_real_escape_string($connection, $from);
        $to = mysqli_real_escape_string($connection, $to);
        $rows = [];
        $sql = "SELECT r.id,r.sale_id,r.item_id,r.qty,r.amount,r.shift,r.user_id,
                    r.confirm_status,r.confirm_by,r.ret_status,
                    COALESCE(i.name,'Unknown item') AS item_name,
                    COALESCE(c.name,'Uncategorised') AS category_name,
                    COALESCE(u.username,'') AS username,
                    COALESCE(cb.username,'') AS confirmed_by
                FROM kp_ret_items r
                LEFT JOIN kp_items i ON i.item_id=r.item_id
                LEFT JOIN kp_cat c ON c.cat_id=i.cat_id
                LEFT JOIN kp_users u ON u.user_id=r.user_id
                LEFT JOIN kp_users cb ON cb.user_id=r.confirm_by
                WHERE r.user_id='$user_id' AND r.shift BETWEEN '$from' AND '$to'
                ORDER BY r.shift DESC,r.id DESC";
        $result = mysqli_query($connection, $sql);
        while ($result && ($row = mysqli_fetch_assoc($result))) {
            $confirm_status = (int)$row['confirm_status'];
            $ret_status = (int)$row['ret_status'];
            $status = $confirm_status === 1 ? 'Accepted' : ($confirm_status === 3 ? 'Rejected' : ($ret_status === 3 ? 'Pending' : 'Processing'));
            $rows[] = [
                'id' => (int)$row['id'], 'sale_id' => (int)$row['sale_id'], 'item_id' => (int)$row['item_id'],
                'item_name' => (string)$row['item_name'], 'category_name' => (string)$row['category_name'],
                'username' => (string)$row['username'], 'shift' => (string)$row['shift'],
                'qty' => (float)$row['qty'], 'amount' => (float)$row['amount'],
                'confirm_status' => $confirm_status, 'ret_status' => $ret_status,
                'status' => $status, 'confirmed_by' => (string)$row['confirmed_by'],
            ];
        }
        return $rows;
    }
}
