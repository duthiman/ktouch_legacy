<?php
/* ──────────────────────────────────────────────────────────────
   Approval Dashboard API   (kug/app/api/approval_dash.php)
   Returns JSON with KPIs, lists, trend data for approve module
   ────────────────────────────────────────────────────────────── */
header('Content-Type: application/json');
if (session_status() === PHP_SESSION_NONE) session_start();
if (!isset($_SESSION['mwene'])) exit(json_encode(['success'=>false]));

include(__DIR__ . '/../../funcs.php');

$out = ['success' => true];

/* ── KPIs ─────────────────────────────────────────────────── */
$r = mysqli_query($connection, "SELECT COUNT(*) AS c FROM kp_po WHERE appr_status=2");
$out['pending'] = (int)(mysqli_fetch_assoc($r)['c'] ?? 0);

$r = mysqli_query($connection, "SELECT COUNT(*) AS c FROM kp_po WHERE appr_status=1");
$out['approved'] = (int)(mysqli_fetch_assoc($r)['c'] ?? 0);

$r = mysqli_query($connection, "SELECT COUNT(*) AS c FROM kp_po WHERE appr_status=3");
$out['rejected'] = (int)(mysqli_fetch_assoc($r)['c'] ?? 0);

$r = mysqli_query($connection, "SELECT COUNT(*) AS c FROM kp_po WHERE appr_status=4");
$out['on_hold'] = (int)(mysqli_fetch_assoc($r)['c'] ?? 0);

/* pending value */
$r = mysqli_query($connection, "SELECT COALESCE(SUM(amount),0) AS v FROM kp_po WHERE appr_status=2");
$out['pending_value'] = (float)(mysqli_fetch_assoc($r)['v'] ?? 0);

/* approved this month */
$r = mysqli_query($connection,
    "SELECT COUNT(*) AS c, COALESCE(SUM(amount),0) AS v FROM kp_po
     WHERE appr_status=1 AND MONTH(po_date)=MONTH(CURDATE()) AND YEAR(po_date)=YEAR(CURDATE())");
$row = mysqli_fetch_assoc($r);
$out['approved_month_count'] = (int)($row['c'] ?? 0);
$out['approved_month_value'] = (float)($row['v'] ?? 0);

/* today */
$r = mysqli_query($connection,
    "SELECT COUNT(*) AS c FROM kp_po WHERE appr_status=2 AND po_date=CURDATE()");
$out['today_pending'] = (int)(mysqli_fetch_assoc($r)['c'] ?? 0);

/* ── Pending Approval List (full) ─────────────────────────── */
$r = mysqli_query($connection,
    "SELECT p.po_id, p.amount, p.po_date, p.datime, p.potype,
            s.sup_name, u.username AS raised_by,
            (SELECT COUNT(*) FROM kp_po_items pi WHERE pi.po_id=p.po_id) AS item_count
     FROM kp_po p
     LEFT JOIN kp_suppliers s ON s.sup_id=p.sup_id
     LEFT JOIN kp_users u ON u.user_id=p.ord_user
     WHERE p.appr_status=2
     ORDER BY p.po_id DESC");
$pending_list = [];
if ($r) while ($row = mysqli_fetch_assoc($r)) {
    $row['amount']     = (float)$row['amount'];
    $row['item_count'] = (int)$row['item_count'];
    $pending_list[] = $row;
}
$out['pending_list'] = $pending_list;

/* ── Recently Approved (last 20) ──────────────────────────── */
$r = mysqli_query($connection,
    "SELECT p.po_id, p.amount, p.po_date, p.appr_comment,
            s.sup_name, u.username AS raised_by,
            a.username AS approved_by
     FROM kp_po p
     LEFT JOIN kp_suppliers s ON s.sup_id=p.sup_id
     LEFT JOIN kp_users u ON u.user_id=p.ord_user
     LEFT JOIN kp_users a ON a.user_id=p.appr_user
     WHERE p.appr_status=1
     ORDER BY p.po_id DESC LIMIT 20");
$approved_list = [];
if ($r) while ($row = mysqli_fetch_assoc($r)) {
    $row['amount'] = (float)$row['amount'];
    $approved_list[] = $row;
}
$out['approved_list'] = $approved_list;

/* ── Recently Rejected (last 10) ──────────────────────────── */
$r = mysqli_query($connection,
    "SELECT p.po_id, p.amount, p.po_date, p.appr_comment,
            s.sup_name, u.username AS raised_by
     FROM kp_po p
     LEFT JOIN kp_suppliers s ON s.sup_id=p.sup_id
     LEFT JOIN kp_users u ON u.user_id=p.ord_user
     WHERE p.appr_status=3
     ORDER BY p.po_id DESC LIMIT 10");
$rejected_list = [];
if ($r) while ($row = mysqli_fetch_assoc($r)) {
    $row['amount'] = (float)$row['amount'];
    $rejected_list[] = $row;
}
$out['rejected_list'] = $rejected_list;

/* ── On Hold (last 10) ────────────────────────────────────── */
$r = mysqli_query($connection,
    "SELECT p.po_id, p.amount, p.po_date,
            s.sup_name, u.username AS raised_by
     FROM kp_po p
     LEFT JOIN kp_suppliers s ON s.sup_id=p.sup_id
     LEFT JOIN kp_users u ON u.user_id=p.ord_user
     WHERE p.appr_status=4
     ORDER BY p.po_id DESC LIMIT 10");
$held_list = [];
if ($r) while ($row = mysqli_fetch_assoc($r)) {
    $row['amount'] = (float)$row['amount'];
    $held_list[] = $row;
}
$out['held_list'] = $held_list;

/* ── Approval trend (6 months) ────────────────────────────── */
$trend_labels = []; $trend_approved = []; $trend_pending = [];
for ($i = 5; $i >= 0; $i--) {
    $yr  = date('Y', strtotime("-$i months"));
    $mo  = date('n', strtotime("-$i months"));
    $lbl = date('M y', strtotime("-$i months"));
    $trend_labels[] = $lbl;

    $r = mysqli_query($connection,
        "SELECT COUNT(*) AS c FROM kp_po
         WHERE appr_status=1 AND YEAR(po_date)=$yr AND MONTH(po_date)=$mo");
    $trend_approved[] = (int)(mysqli_fetch_assoc($r)['c'] ?? 0);

    $r = mysqli_query($connection,
        "SELECT COUNT(*) AS c FROM kp_po
         WHERE appr_status=2 AND YEAR(po_date)=$yr AND MONTH(po_date)=$mo");
    $trend_pending[] = (int)(mysqli_fetch_assoc($r)['c'] ?? 0);
}
$out['trend_labels']   = $trend_labels;
$out['trend_approved'] = $trend_approved;
$out['trend_pending']  = $trend_pending;

/* ── Top suppliers with pending ───────────────────────────── */
$r = mysqli_query($connection,
    "SELECT s.sup_name, COUNT(*) AS cnt, COALESCE(SUM(p.amount),0) AS tot
     FROM kp_po p
     JOIN kp_suppliers s ON s.sup_id=p.sup_id
     WHERE p.appr_status=2
     GROUP BY p.sup_id ORDER BY cnt DESC LIMIT 5");
$top_sup_labels = []; $top_sup_values = [];
if ($r) while ($row = mysqli_fetch_assoc($r)) {
    $top_sup_labels[] = $row['sup_name'];
    $top_sup_values[] = (int)$row['cnt'];
}
$out['top_sup_labels'] = $top_sup_labels;
$out['top_sup_values'] = $top_sup_values;

echo json_encode($out);
