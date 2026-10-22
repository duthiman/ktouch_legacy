<?php

if (!function_exists('lpo_repo_init_held_orders')) {
    function lpo_repo_init_held_orders()
    {
        $result = mysqli_query($GLOBALS['connection'], "
            SELECT ps.po_id, ps.sup_id, ps.potype, ps.po_day, ps.po_status,
                   s.sup_name
            FROM kp_po_status ps
            LEFT JOIN kp_suppliers s ON s.sup_id = ps.sup_id
            WHERE ps.po_status IN (2,3)
            ORDER BY ps.po_id DESC
            LIMIT 20
        ");
        $rows = [];
        if ($result) {
            while ($row = mysqli_fetch_assoc($result)) {
                $rows[] = [
                    'po_id' => (int)$row['po_id'], 'sup_id' => (int)$row['sup_id'],
                    'potype' => (int)$row['potype'], 'po_day' => (string)($row['po_day'] ?? ''),
                    'po_status' => (int)$row['po_status'], 'sup_name' => (string)($row['sup_name'] ?? 'Unknown')
                ];
            }
        }
        return $rows;
    }
}

if (!function_exists('lpo_repo_initialize')) {
    function lpo_repo_initialize($userId, $supplierId, $poType, $poBy = 5)
    {
        $userId = (int)$userId; $supplierId = (int)$supplierId;
        $poType = (int)$poType; $poBy = (int)$poBy;
        if ($userId < 1 || $supplierId < 1) return ['ok' => false, 'message' => 'Supplier and user are required.'];

        $connection = $GLOBALS['connection'];
        $safeUser = mysqli_real_escape_string($connection, (string)$userId);
        $safeSup = mysqli_real_escape_string($connection, (string)$supplierId);
        $existing = mysqli_query($connection, "SELECT po_id FROM kp_po_status WHERE po_user_id='{$safeUser}' AND sup_id='{$safeSup}' AND po_status=2 ORDER BY po_id DESC LIMIT 1");
        if ($existing && ($row = mysqli_fetch_assoc($existing))) {
            return ['ok' => true, 'po_id' => (int)$row['po_id'], 'continued' => true];
        }

        mysqli_begin_transaction($connection);
        try {
            $idResult = mysqli_query($connection, "SELECT po_id FROM kp_po_id LIMIT 1");
            if ($idResult && mysqli_num_rows($idResult) > 0) {
                if (!mysqli_query($connection, "UPDATE kp_po_id SET po_id=po_id+1")) throw new Exception('Could not create a purchase order number.');
            } else {
                if (!mysqli_query($connection, "INSERT INTO kp_po_id(po_id) VALUES(1)")) throw new Exception('Could not create a purchase order number.');
            }
            $newIdResult = mysqli_query($connection, "SELECT po_id FROM kp_po_id LIMIT 1");
            $newIdRow = $newIdResult ? mysqli_fetch_assoc($newIdResult) : null;
            $poId = (int)($newIdRow['po_id'] ?? 0);
            if ($poId < 1) throw new Exception('Could not read the purchase order number.');
            $day = date('Y-m-d');
            $stmt = mysqli_prepare($connection, "INSERT INTO kp_po_status(po_id,po_user_id,sup_id,potype,poby,po_day) VALUES(?,?,?,?,?,?)");
            if (!$stmt) throw new Exception('Could not prepare the purchase order.');
            mysqli_stmt_bind_param($stmt, 'iiiiis', $poId, $userId, $supplierId, $poType, $poBy, $day);
            if (!mysqli_stmt_execute($stmt)) throw new Exception('Could not initialize the purchase order.');
            mysqli_stmt_close($stmt);
            mysqli_commit($connection);
            return ['ok' => true, 'po_id' => $poId, 'continued' => false];
        } catch (Throwable $error) {
            mysqli_rollback($connection);
            return ['ok' => false, 'message' => $error->getMessage()];
        }
    }
}

if (!function_exists('lpo_repo_continue')) {
    function lpo_repo_continue($poId, $supplierId = 0)
    {
        $poId = (int)$poId;
        if ($poId < 1) return ['ok' => false, 'message' => 'Invalid purchase order.'];
        return ['ok' => true, 'po_id' => $poId, 'sup_id' => (int)$supplierId, 'continued' => true];
    }
}

if (!function_exists('lpo_repo_get_by_id')) {
    function lpo_repo_get_by_id($lpo_id)
    {
        $lpo_id = (int)$lpo_id;
        if ($lpo_id < 1) {
            return null;
        }

        return null;
    }
}

if (!function_exists('lpo_repo_scalar')) {
    function lpo_repo_scalar($sql, $field, $default = 0)
    {
        $result = mysqli_query($GLOBALS['connection'], $sql);
        if (!$result) {
            return $default;
        }

        $row = mysqli_fetch_assoc($result);
        return $row[$field] ?? $default;
    }
}

if (!function_exists('lpo_repo_list')) {
    function lpo_repo_list($filters = [])
    {
        return [];
    }
}

if (!function_exists('lpo_repo_dashboard_totals')) {
    function lpo_repo_dashboard_totals()
    {
        return [
            'total_pos' => (int)lpo_repo_scalar("SELECT COUNT(*) AS c FROM kp_po", 'c', 0),
            'pending_approval' => (int)lpo_repo_scalar("SELECT COUNT(*) AS c FROM kp_po WHERE appr_status=2", 'c', 0),
            'pending_receiving' => (int)lpo_repo_scalar("SELECT COUNT(*) AS c FROM kp_po WHERE appr_status=1 AND rec_status=2", 'c', 0),
            'completed_grns' => (int)lpo_repo_scalar("SELECT COUNT(*) AS c FROM kp_grn WHERE status=1", 'c', 0),
            'ongoing_grns' => (int)lpo_repo_scalar("SELECT COUNT(*) AS c FROM kp_grn WHERE status=3", 'c', 0),
            'total_suppliers' => (int)lpo_repo_scalar("SELECT COUNT(*) AS c FROM kp_suppliers", 'c', 0),
        ];
    }
}

if (!function_exists('lpo_repo_dashboard_spend')) {
    function lpo_repo_dashboard_spend()
    {
        return [
            'spend_this_month' => (float)lpo_repo_scalar(
                "SELECT COALESCE(SUM(amount),0) AS v FROM kp_po
                 WHERE appr_status=1 AND MONTH(po_date)=MONTH(CURDATE()) AND YEAR(po_date)=YEAR(CURDATE())",
                'v',
                0
            ),
            'spend_30d' => (float)lpo_repo_scalar(
                "SELECT COALESCE(SUM(amount),0) AS v FROM kp_po
                 WHERE appr_status=1 AND po_date >= DATE_SUB(CURDATE(), INTERVAL 30 DAY)",
                'v',
                0
            ),
            'spend_all_time' => (float)lpo_repo_scalar(
                "SELECT COALESCE(SUM(amount),0) AS v FROM kp_po WHERE appr_status=1",
                'v',
                0
            ),
        ];
    }
}

if (!function_exists('lpo_repo_dashboard_trend')) {
    function lpo_repo_dashboard_trend()
    {
        $trend_labels = [];
        $trend_count = [];
        $trend_amount = [];

        for ($i = 5; $i >= 0; $i--) {
            $yr = date('Y', strtotime("-$i months"));
            $mo = date('n', strtotime("-$i months"));
            $lbl = date('M y', strtotime("-$i months"));
            $trend_labels[] = $lbl;

            $result = mysqli_query(
                $GLOBALS['connection'],
                "SELECT COUNT(*) AS cnt, COALESCE(SUM(amount),0) AS tot FROM kp_po
                 WHERE appr_status=1 AND YEAR(po_date)=$yr AND MONTH(po_date)=$mo"
            );
            $row = $result ? mysqli_fetch_assoc($result) : ['cnt' => 0, 'tot' => 0];
            $trend_count[] = (int)($row['cnt'] ?? 0);
            $trend_amount[] = (float)($row['tot'] ?? 0);
        }

        return [
            'trend_labels' => $trend_labels,
            'trend_count' => $trend_count,
            'trend_amount' => $trend_amount,
        ];
    }
}

if (!function_exists('lpo_repo_dashboard_top_suppliers')) {
    function lpo_repo_dashboard_top_suppliers()
    {
        $result = mysqli_query(
            $GLOBALS['connection'],
            "SELECT s.sup_name, COALESCE(SUM(p.amount),0) AS tot
             FROM kp_po p
             JOIN kp_suppliers s ON s.sup_id=p.sup_id
             WHERE p.appr_status=1
             GROUP BY p.sup_id
             ORDER BY tot DESC LIMIT 5"
        );

        $labels = [];
        $values = [];
        if ($result) {
            while ($row = mysqli_fetch_assoc($result)) {
                $labels[] = (string)$row['sup_name'];
                $values[] = (float)$row['tot'];
            }
        }

        return [
            'supplier_labels' => $labels,
            'supplier_values' => $values,
        ];
    }
}

if (!function_exists('lpo_repo_dashboard_approval_list')) {
    function lpo_repo_dashboard_approval_list()
    {
        $result = mysqli_query(
            $GLOBALS['connection'],
            "SELECT p.po_id, p.amount, p.po_date, p.potype,
                    s.sup_name, u.username AS raised_by
             FROM kp_po p
             LEFT JOIN kp_suppliers s ON s.sup_id=p.sup_id
             LEFT JOIN kp_users u ON u.user_id=p.ord_user
             WHERE p.appr_status=2
             ORDER BY p.po_id DESC LIMIT 15"
        );

        $rows = [];
        if ($result) {
            while ($row = mysqli_fetch_assoc($result)) {
                $row['amount'] = (float)$row['amount'];
                $rows[] = $row;
            }
        }

        return $rows;
    }
}

if (!function_exists('lpo_repo_dashboard_receiving_list')) {
    function lpo_repo_dashboard_receiving_list()
    {
        $result = mysqli_query(
            $GLOBALS['connection'],
            "SELECT p.po_id, p.amount, p.po_date, p.potype,
                    s.sup_name, u.username AS raised_by,
                    a.username AS approved_by,
                    (SELECT COUNT(*) FROM kp_po_items pi WHERE pi.po_id=p.po_id AND pi.status=1) AS item_count
             FROM kp_po p
             LEFT JOIN kp_suppliers s ON s.sup_id=p.sup_id
             LEFT JOIN kp_users u ON u.user_id=p.ord_user
             LEFT JOIN kp_users a ON a.user_id=p.appr_user
             WHERE p.appr_status=1 AND p.rec_status=2
             ORDER BY p.po_id DESC LIMIT 15"
        );

        $rows = [];
        if ($result) {
            while ($row = mysqli_fetch_assoc($result)) {
                $row['amount'] = (float)$row['amount'];
                $row['item_count'] = (int)$row['item_count'];
                $rows[] = $row;
            }
        }

        return $rows;
    }
}

if (!function_exists('lpo_repo_dashboard_grn_list')) {
    function lpo_repo_dashboard_grn_list()
    {
        $result = mysqli_query(
            $GLOBALS['connection'],
            "SELECT g.grn_id, g.po_id, g.status, g.day, g.amount,
                    s.sup_name, u.username AS received_by,
                    (SELECT COUNT(*) FROM kp_grn_items gi WHERE gi.grn_id=g.grn_id) AS item_count
             FROM kp_grn g
             LEFT JOIN kp_suppliers s ON s.sup_id=g.sup_id
             LEFT JOIN kp_users u ON u.user_id=g.user_id
             ORDER BY g.grn_id DESC LIMIT 15"
        );

        $rows = [];
        if ($result) {
            while ($row = mysqli_fetch_assoc($result)) {
                $row['grn_id'] = (int)$row['grn_id'];
                $row['amount'] = (float)$row['amount'];
                $row['item_count'] = (int)$row['item_count'];
                $rows[] = $row;
            }
        }

        return $rows;
    }
}

if (!function_exists('lpo_repo_create')) {
    function lpo_repo_create($payload = [])
    {
        return [
            'ok' => false,
            'message' => 'LPO create is not implemented yet.',
            'lpo_id' => 0,
        ];
    }
}
