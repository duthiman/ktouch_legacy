<?php
declare(strict_types=1);

require_once __DIR__ . '/thit_helpers.php';

function thit_repo_kpis(mysqli $db): array
{
    $pendingGrn = 0;
    if (thit_table_exists($db, 'kp_po') && thit_column_exists($db, 'kp_po', 'rec_status')) {
        $where = "rec_status=2";
        if (thit_column_exists($db, 'kp_po', 'stock_status')) $where .= " AND stock_status=2";
        $pendingGrn = (int)thit_scalar($db, "SELECT COUNT(*) FROM kp_po WHERE {$where}");
    }

    $pendingIssues = 0;
    if (thit_table_exists($db, 'kp_counter_req')) {
        $where = [];
        if (thit_column_exists($db, 'kp_counter_req', 'issue_status')) $where[] = 'issue_status=1';
        if (thit_column_exists($db, 'kp_counter_req', 'rec_status')) $where[] = 'rec_status=2';
        if ($where) $pendingIssues = (int)thit_scalar($db, 'SELECT COUNT(*) FROM kp_counter_req WHERE ' . implode(' AND ', $where));
    }

    $out = $low = 0;
    if (thit_table_exists($db, 'kp_items') && thit_column_exists($db, 'kp_items', 'store_qty')) {
        $base = thit_column_exists($db, 'kp_items', 'del_status') ? 'del_status=2 AND ' : '';
        $stock = thit_column_exists($db, 'kp_items', 'stock') ? 'stock=1 AND ' : '';
        $out = (int)thit_scalar($db, "SELECT COUNT(*) FROM kp_items WHERE {$base}{$stock} COALESCE(store_qty,0)<=0");
        if (thit_column_exists($db, 'kp_items', 'store_reorder')) {
            $low = (int)thit_scalar($db, "SELECT COUNT(*) FROM kp_items WHERE {$base}{$stock} COALESCE(store_qty,0)>0 AND COALESCE(store_reorder,0)>0 AND store_qty<=store_reorder");
        }
    }

    $approvals = 0;
    $pendingDispatch = 0;
    if (thit_table_exists($db, 'kp_po')) {
        if (thit_column_exists($db, 'kp_po', 'appr_status')) $approvals = (int)thit_scalar($db, "SELECT COUNT(*) FROM kp_po WHERE appr_status=2");
        $conditions = [];
        foreach (['appr_status=1', 'rec_status=1', 'stock_status=2'] as $condition) {
            [$column] = explode('=', $condition);
            if (thit_column_exists($db, 'kp_po', $column)) $conditions[] = $condition;
        }
        if ($conditions) $pendingDispatch = (int)thit_scalar($db, 'SELECT COUNT(*) FROM kp_po WHERE ' . implode(' AND ', $conditions));
    }

    $counterRequests = 0;
    if (thit_table_exists($db, 'kp_counter_req') && thit_column_exists($db, 'kp_counter_req', 'rec_status')) {
        $counterRequests = (int)thit_scalar($db, "SELECT COUNT(*) FROM kp_counter_req WHERE rec_status=2");
    }
    return compact('pendingGrn', 'pendingIssues', 'out', 'low', 'approvals', 'pendingDispatch', 'counterRequests');
}

function thit_repo_stock_values(mysqli $db): array
{
    $retail = $cost = 0.0;
    if (thit_table_exists($db, 'kp_items') && thit_column_exists($db, 'kp_items', 'store_qty')) {
        $qty = 'COALESCE(store_qty,0)';
        $filter = thit_column_exists($db, 'kp_items', 'del_status') ? 'WHERE del_status=2' : '';
        if (thit_column_exists($db, 'kp_items', 'price')) $retail = thit_number(thit_scalar($db, "SELECT SUM({$qty}*COALESCE(price,0)) FROM kp_items {$filter}"));
        if (thit_column_exists($db, 'kp_items', 'bprice')) $cost = thit_number(thit_scalar($db, "SELECT SUM({$qty}*COALESCE(bprice,0)) FROM kp_items {$filter}"));
    }
    return ['retail' => $retail, 'cost' => $cost, 'margin' => $retail - $cost, 'marginPct' => $retail > 0 ? (($retail - $cost) / $retail) * 100 : 0];
}

function thit_repo_recent_grns(mysqli $db): array
{
    if (!thit_table_exists($db, 'kp_grn')) return [];
    $columns = ['grn_id'];
    foreach (['po_id', 'status', 'day', 'amount', 'sup_id', 'user_id'] as $column) if (thit_column_exists($db, 'kp_grn', $column)) $columns[] = $column;
    return thit_rows($db, 'SELECT ' . implode(',', $columns) . ' FROM kp_grn ORDER BY ' . (in_array('grn_id', $columns, true) ? 'grn_id' : $columns[0]) . ' DESC LIMIT 8');
}

function thit_repo_activity(mysqli $db): array
{
    $labels = $counts = [];
    if (thit_table_exists($db, 'kp_po') && thit_column_exists($db, 'kp_po', 'po_date')) {
        $rows = thit_rows($db, "SELECT DATE_FORMAT(po_date,'%Y-%m') label, COUNT(*) total FROM kp_po WHERE po_date >= DATE_SUB(CURDATE(), INTERVAL 5 MONTH) GROUP BY DATE_FORMAT(po_date,'%Y-%m') ORDER BY label");
        foreach ($rows as $row) { $labels[] = $row['label']; $counts[] = (int)$row['total']; }
    }
    if (!$labels) { for ($i=5; $i>=0; $i--) { $labels[] = date('M y', strtotime("-{$i} months")); $counts[] = 0; } }
    return compact('labels', 'counts');
}

function thit_repo_counters(mysqli $db): array
{
    if (!thit_table_exists($db, 'kp_counters')) return [];
    $name = thit_column_exists($db, 'kp_counters', 'name') ? 'name' : (thit_column_exists($db, 'kp_counters', 'counter_name') ? 'counter_name' : 'cntr_id');
    return thit_rows($db, "SELECT cntr_id, {$name} counter_name FROM kp_counters ORDER BY cntr_id LIMIT 8");
}
