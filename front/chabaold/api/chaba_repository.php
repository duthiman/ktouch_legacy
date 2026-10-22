<?php

if (!function_exists('chaba_repo_get_sale')) {
    function chaba_repo_get_sale($sale_id)
    {
        global $connection;
        $sale_id = (int)$sale_id;
        if ($sale_id < 1) {
            return null;
        }
        $result = mysqli_query($connection, "SELECT * FROM kp_sales WHERE sale_id='$sale_id' LIMIT 1");
        if (!$result || mysqli_num_rows($result) < 1) {
            return null;
        }
        return mysqli_fetch_assoc($result);
    }
}

if (!function_exists('chaba_repo_get_sale_fallback_context')) {
    function chaba_repo_get_sale_fallback_context($sale_id)
    {
        $sale_id = (int)$sale_id;
        return [
            'sale_id' => $sale_id,
            'user_id' => (int)get_column('kp_sale_items', 'user_id', "sale_id='$sale_id'"),
            'shift' => (string)get_column('kp_sale_items', 'shift', "sale_id='$sale_id'"),
            'counter' => (int)get_column('kp_sale_items', 'counter', "sale_id='$sale_id'"),
            'amount' => (float)column_total('kp_sale_items', 'total', "sale_id='$sale_id'"),
        ];
    }
}

if (!function_exists('chaba_repo_get_sale_totals')) {
    function chaba_repo_get_sale_totals($sale_id)
    {
        $sale_id = (int)$sale_id;
        return [
            'items_total' => (float)column_total('kp_sale_items', 'total', "sale_id='$sale_id'"),
            'print_total' => (float)column_total('kp_sale_print', 'total', "sale_id='$sale_id'"),
            'items_count' => (int)column_count('kp_sale_items', 'item_id', "sale_id='$sale_id'"),
            'print_count' => (int)column_count('kp_sale_print', 'item_id', "sale_id='$sale_id'"),
        ];
    }
}

if (!function_exists('chaba_repo_get_sale_lines')) {
    function chaba_repo_get_sale_lines($sale_id, $table = 'kp_sale_items', $where_extra = '')
    {
        global $connection;
        $sale_id = (int)$sale_id;
        $table = $table === 'kp_sale_print' ? 'kp_sale_print' : 'kp_sale_items';
        $where_extra = trim((string)$where_extra);
        $where_sql = "sale_id='$sale_id'";
        if ($where_extra !== '') {
            $where_sql .= " AND " . $where_extra;
        }

        $rows = [];
        $result = mysqli_query(
            $connection,
            "SELECT SUM(qty) AS qty, price, item_id, attr_id, dpt, hour
             FROM $table
             WHERE $where_sql
             GROUP BY item_id, attr_id, price, dpt, hour
             ORDER BY dpt ASC, hour ASC, item_id ASC"
        );
        if (!$result) {
            return $rows;
        }

        $baseRows = [];
        $itemIds = [];
        $attrIds = [];
        while ($row = mysqli_fetch_assoc($result)) {
            $item_id = (int)$row['item_id'];
            $attr_id = (int)($row['attr_id'] ?? 0);
            $qty = (float)$row['qty'];
            $price = (float)$row['price'];
            $baseRows[] = [
                'item_id' => $item_id,
                'attr_id' => $attr_id,
                'qty' => $qty,
                'price' => $price,
                'total' => $qty * $price,
                'dpt' => (int)($row['dpt'] ?? 0),
                'hour' => (int)($row['hour'] ?? 1),
            ];
            if ($item_id > 0) {
                $itemIds[$item_id] = $item_id;
            }
            if ($attr_id > 0) {
                $attrIds[$attr_id] = $attr_id;
            }
        }

        $itemNames = [];
        if (!empty($itemIds)) {
            $itemSql = "SELECT item_id, name FROM kp_items WHERE item_id IN (" . implode(',', array_map('intval', $itemIds)) . ")";
            $itemResult = mysqli_query($connection, $itemSql);
            if ($itemResult) {
                while ($itemRow = mysqli_fetch_assoc($itemResult)) {
                    $itemNames[(int)$itemRow['item_id']] = (string)$itemRow['name'];
                }
            }
        }

        $attrNames = [];
        if (!empty($attrIds)) {
            $attrSql = "SELECT id, name FROM kp_items_attributes WHERE id IN (" . implode(',', array_map('intval', $attrIds)) . ")";
            $attrResult = mysqli_query($connection, $attrSql);
            if ($attrResult) {
                while ($attrRow = mysqli_fetch_assoc($attrResult)) {
                    $attrNames[(int)$attrRow['id']] = (string)$attrRow['name'];
                }
            }
        }

        foreach ($baseRows as $row) {
            $name = (string)($itemNames[(int)$row['item_id']] ?? '');
            $attr_id = (int)$row['attr_id'];
            if ($attr_id > 0 && !empty($attrNames[$attr_id])) {
                $name .= ' (' . $attrNames[$attr_id] . ')';
            }
            $row['name'] = $name;
            $rows[] = $row;
        }

        return $rows;
    }
}

if (!function_exists('chaba_repo_get_ticket_lines')) {
    function chaba_repo_get_ticket_lines($sale_id, $department_id, $source = 'sale_items', $batch_id = 0)
    {
        global $connection;
        $sale_id = (int)$sale_id;
        $department_id = (int)$department_id;
        $batch_id = (int)$batch_id;
        $rows = [];

        if ($source === 'ticket_batch' && $batch_id > 0) {
            $sql = "SELECT SUM(qty) AS qty, price, item_id, attr_id
                    FROM kp_print_ticket_items
                    WHERE batch_id='$batch_id' AND sale_id='$sale_id' AND dpt='$department_id'
                    GROUP BY item_id, attr_id, price
                    ORDER BY item_id ASC";
        } else {
            $table = $source === 'sale_print' ? 'kp_sale_print' : 'kp_sale_items';
            $sql = "SELECT SUM(qty) AS qty, price, item_id, attr_id
                    FROM $table
                    WHERE sale_id='$sale_id' AND dpt='$department_id'
                    GROUP BY item_id, attr_id, price
                    ORDER BY item_id ASC";
        }

        $result = mysqli_query($connection, $sql);
        if (!$result) {
            return $rows;
        }

        while ($row = mysqli_fetch_assoc($result)) {
            $item_id = (int)$row['item_id'];
            $attr_id = (int)($row['attr_id'] ?? 0);
            $qty = (float)$row['qty'];
            $price = (float)$row['price'];
            $name = (string)get_column('kp_items', 'name', "item_id='$item_id'");
            if ($attr_id > 0) {
                $attr_name = (string)get_column('kp_items_attributes', 'name', "id='$attr_id'");
                if ($attr_name !== '') {
                    $name .= ' (' . $attr_name . ')';
                }
            }
            $rows[] = [
                'item_id' => $item_id,
                'attr_id' => $attr_id,
                'name' => $name,
                'qty' => $qty,
                'price' => $price,
                'total' => $qty * $price,
                'dpt' => $department_id,
            ];
        }

        return $rows;
    }
}

if (!function_exists('chaba_repo_get_print_settings')) {
    function chaba_repo_get_print_settings()
    {
        global $connection;
        $row = mysqli_fetch_assoc(mysqli_query($connection, "SELECT * FROM kp_settings_print WHERE print_doc=1 LIMIT 1"));
        return is_array($row) ? $row : [];
    }
}

if (!function_exists('chaba_repo_get_company_settings')) {
    function chaba_repo_get_company_settings()
    {
        global $connection;
        $row = mysqli_fetch_assoc(mysqli_query($connection, "SELECT * FROM kp_settings_gen WHERE id=1 LIMIT 1"));
        return is_array($row) ? $row : [];
    }
}
