<?php

if (!function_exists('dash_stock_alerts')) {
    function dash_stock_alerts()
    {
        global $connection;

        $result = array(
            'out_of_stock' => 0,
            'below_reorder' => 0,
            'all_items' => 0,
            'counters' => array(),
        );

        $counter_q = mysqli_query($connection, "SELECT cntr_id, name FROM kp_counters ORDER BY name ASC");
        if (!$counter_q) {
            return $result;
        }

        $columns = array();
        $column_q = mysqli_query($connection, "SHOW COLUMNS FROM kp_items");
        if ($column_q) {
            while ($column = mysqli_fetch_assoc($column_q)) {
                $columns[$column['Field']] = true;
            }
        }

        while ($counter = mysqli_fetch_assoc($counter_q)) {
            $counter_id = (int)$counter['cntr_id'];
            $qty_column = 'cntr' . $counter_id . '_qty';
            $reorder_column = 'cntr' . $counter_id . '_reorder';
            $has_qty = isset($columns[$qty_column]);
            $has_reorder = isset($columns[$reorder_column]);
            $counter_items = array();
            $counter_out = 0;
            $counter_reorder = 0;

            $qty_select = $has_qty ? "`$qty_column`" : '0';
            $reorder_select = $has_reorder ? "`$reorder_column`" : '0';
            $item_q = mysqli_query($connection, "SELECT item_id, name, stock, $qty_select AS counter_qty, $reorder_select AS reorder_qty FROM kp_items WHERE del_status=2 ORDER BY name ASC");
            if (!$item_q) {
                continue;
            }
            while ($item = mysqli_fetch_assoc($item_q)) {
                $kind = (int)($item['stock'] ?? 0);
                $item_id = (int)$item['item_id'];
                $qty = (float)$item['counter_qty'];
                $reorder = (float)$item['reorder_qty'];

                // Stock type 2 is treated as always available by the counter POS.
                if ($kind === 2) {
                    $qty = 100;
                    $reorder = 50;
                }

                $status = $qty <= 0 ? 'out' : (($reorder > 0 && $qty <= $reorder) ? 'reorder' : 'ok');
                if ($status === 'out') {
                    $counter_out++;
                    $result['out_of_stock']++;
                } elseif ($status === 'reorder') {
                    $counter_reorder++;
                    $result['below_reorder']++;
                }

                $counter_items[] = array(
                    'item_id' => $item_id,
                    'name' => (string)$item['name'],
                    'current' => $qty,
                    'reorder_level' => $reorder,
                    'status' => $status,
                );
                $result['all_items']++;
            }

            $result['counters'][] = array(
                'id' => $counter_id,
                'name' => (string)$counter['name'],
                'out_of_stock' => $counter_out,
                'below_reorder' => $counter_reorder,
                'items' => $counter_items,
            );
        }

        return $result;
    }
}
