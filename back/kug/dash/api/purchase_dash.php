<?php
// kug/dash/api/purchase_dash.php — Purchase dashboard data API
header('Content-Type: application/json');
if (session_status() === PHP_SESSION_NONE) session_start();
if (!isset($_SESSION['mwene'])) exit(json_encode(['success'=>false,'message'=>'Unauthorized']));

include('../funcs.php');

$out = ['success' => true];

// ── KPI COUNTS ────────────────────────────────────────────────────────────────
$r = mysqli_query($connection, "SELECT COUNT(*) AS c FROM kp_po");
$out['total_pos'] = (int)(mysqli_fetch_assoc($r)['c'] ?? 0);

$r = mysqli_query($connection, "SELECT COUNT(*) AS c FROM kp_po WHERE appr_status=2");
$out['pending_approval'] = (int)(mysqli_fetch_assoc($r)['c'] ?? 0);

$r = mysqli_query($connection, "SELECT COUNT(*) AS c FROM kp_po WHERE appr_status=1 AND rec_status=2");
$out['pending_receiving'] = (int)(mysqli_fetch_assoc($r)['c'] ?? 0);

$r = mysqli_query($connection, "SELECT COUNT(*) AS c FROM kp_grn WHERE status=1");
$out['completed_grns'] = (int)(mysqli_fetch_assoc($r)['c'] ?? 0);

$r = mysqli_query($connection, "SELECT COUNT(*) AS c FROM kp_grn WHERE status=3");
$out['ongoing_grns'] = (int)(mysqli_fetch_assoc($r)['c'] ?? 0);

$r = mysqli_query($connection, "SELECT COUNT(*) AS c FROM kp_suppliers");
$out['total_suppliers'] = (int)(mysqli_fetch_assoc($r)['c'] ?? 0);

// ── FINANCIAL ────────────────────────────────────────────────────────────────
$r = mysqli_query($connection,
    "SELECT COALESCE(SUM(amount),0) AS v FROM kp_po
     WHERE appr_status=1 AND MONTH(po_date)=MONTH(CURDATE()) AND YEAR(po_date)=YEAR(CURDATE())");
$out['spend_this_month'] = (float)(mysqli_fetch_assoc($r)['v'] ?? 0);

$r = mysqli_query($connection,
    "SELECT COALESCE(SUM(amount),0) AS v FROM kp_po
     WHERE appr_status=1 AND po_date >= DATE_SUB(CURDATE(), INTERVAL 30 DAY)");
$out['spend_30d'] = (float)(mysqli_fetch_assoc($r)['v'] ?? 0);

$r = mysqli_query($connection,
    "SELECT COALESCE(SUM(amount),0) AS v FROM kp_po WHERE appr_status=1");
$out['spend_all_time'] = (float)(mysqli_fetch_assoc($r)['v'] ?? 0);

// ── MONTHLY TREND (last 6 months) ─────────────────────────────────────────────
$trend_labels = [];
$trend_count  = [];
$trend_amount = [];
for ($i = 5; $i >= 0; $i--) {
    $yr  = date('Y', strtotime("-$i months"));
    $mo  = date('n', strtotime("-$i months"));
    $lbl = date('M y', strtotime("-$i months"));
    $trend_labels[] = $lbl;

    $r = mysqli_query($connection,
        "SELECT COUNT(*) AS cnt, COALESCE(SUM(amount),0) AS tot FROM kp_po
         WHERE appr_status=1 AND YEAR(po_date)=$yr AND MONTH(po_date)=$mo");
    $row = mysqli_fetch_assoc($r);
    $trend_count[]  = (int)$row['cnt'];
    $trend_amount[] = (float)$row['tot'];
}
$out['trend_labels']  = $trend_labels;
$out['trend_count']   = $trend_count;
$out['trend_amount']  = $trend_amount;

// ── TOP SUPPLIERS BY SPEND ─────────────────────────────────────────────────────
$r = mysqli_query($connection,
    "SELECT s.sup_name, COALESCE(SUM(p.amount),0) AS tot
     FROM kp_po p
     JOIN kp_suppliers s ON s.sup_id=p.sup_id
     WHERE p.appr_status=1
     GROUP BY p.sup_id
     ORDER BY tot DESC LIMIT 5");
$sup_labels = []; $sup_values = [];
if ($r) while ($row = mysqli_fetch_assoc($r)) {
    $sup_labels[] = $row['sup_name'];
    $sup_values[] = (float)$row['tot'];
}
$out['supplier_labels'] = $sup_labels;
$out['supplier_values'] = $sup_values;

// ── PENDING APPROVALS LIST ─────────────────────────────────────────────────────
$r = mysqli_query($connection,
    "SELECT p.po_id, p.amount, p.po_date, p.potype,
            s.sup_name, u.username AS raised_by
     FROM kp_po p
     LEFT JOIN kp_suppliers s ON s.sup_id=p.sup_id
     LEFT JOIN kp_users u ON u.user_id=p.ord_user
     WHERE p.appr_status=2
     ORDER BY p.po_id DESC LIMIT 15");
$approvals = [];
if ($r) while ($row = mysqli_fetch_assoc($r)) {
    $row['amount'] = (float)$row['amount'];
    $approvals[] = $row;
}
$out['approval_list'] = $approvals;

// ── PENDING RECEIVING LIST ─────────────────────────────────────────────────────
$r = mysqli_query($connection,
    "SELECT p.po_id, p.amount, p.po_date, p.potype,
            s.sup_name, u.username AS raised_by,
            a.username AS approved_by,
            (SELECT COUNT(*) FROM kp_po_items pi WHERE pi.po_id=p.po_id AND pi.status=1) AS item_count
     FROM kp_po p
     LEFT JOIN kp_suppliers s ON s.sup_id=p.sup_id
     LEFT JOIN kp_users u ON u.user_id=p.ord_user
     LEFT JOIN kp_users a ON a.user_id=p.appr_user
     WHERE p.appr_status=1 AND p.rec_status=2
     ORDER BY p.po_id DESC LIMIT 15");
$receiving = [];
if ($r) while ($row = mysqli_fetch_assoc($r)) {
    $row['amount']     = (float)$row['amount'];
    $row['item_count'] = (int)$row['item_count'];
    $receiving[] = $row;
}
$out['receiving_list'] = $receiving;

// ── RECENT GRNS ───────────────────────────────────────────────────────────────
$r = mysqli_query($connection,
    "SELECT g.grn_id, g.po_id, g.status, g.day, g.amount,
            s.sup_name, u.username AS received_by,
            (SELECT COUNT(*) FROM kp_grn_items gi WHERE gi.grn_id=g.grn_id) AS item_count
     FROM kp_grn g
     LEFT JOIN kp_suppliers s ON s.sup_id=g.sup_id
     LEFT JOIN kp_users u ON u.user_id=g.user_id
     ORDER BY g.grn_id DESC LIMIT 15");
$grns = [];
if ($r) while ($row = mysqli_fetch_assoc($r)) {
    $row['grn_id']     = (int)$row['grn_id'];
    $row['amount']     = (float)$row['amount'];
    $row['item_count'] = (int)$row['item_count'];
    $grns[] = $row;
}
$out['grn_list'] = $grns;

echo json_encode($out);
