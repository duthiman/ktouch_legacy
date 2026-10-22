<?php
/* ──────────────────────────────────────────────────────────────
   PURCHASE ORDER – PRINT VIEW   (kug/ord/print_po.php)
   Modern clean layout · snap-aware · receipt_desc support
   ────────────────────────────────────────────────────────────── */
include(__DIR__ . '/funcs.php');

$user_id = $_SESSION['mwene'];

/* ── resolve PO id ─────────────────────────────────────────── */
if (isset($_GET['id'])) {
    $po_id = intval($_GET['id']);
} else {
    $po_id = get_column('kp_thesion','trans_id',"user_id='$user_id'");
}

/* ── header data ───────────────────────────────────────────── */
$sup_id  = get_column('kp_po','sup_id',"po_id='$po_id'");
$po_date = get_column('kp_po','po_date',"po_id='$po_id'");
$ord_user = get_column('kp_po','ord_user',"po_id='$po_id'");

if (!$sup_id) $sup_id = get_column('kp_po_status','sup_id',"po_id='$po_id'");
if (!$po_date) $po_date = get_column('kp_po_status','po_day',"po_id='$po_id'");

$sup_q = mysqli_query($connection,"SELECT * FROM kp_suppliers WHERE sup_id='$sup_id'") or die(mysqli_error($connection));
$sup   = mysqli_fetch_assoc($sup_q);

$gen_q = mysqli_query($connection,"SELECT * FROM kp_settings_gen LIMIT 1") or die("gen err");
$gen   = mysqli_fetch_assoc($gen_q);

$raised_by = $ord_user ? get_column('kp_users','username',"user_id='$ord_user'") : get_column('kp_users','username',"user_id='$user_id'");

/* ── approved by (if approved) ─────────────────────────────── */
$appr_user_id = get_column('kp_po','appr_user',"po_id='$po_id'");
$appr_status  = get_column('kp_po','appr_status',"po_id='$po_id'");
$approved_by  = ($appr_user_id && $appr_status == 1) ? get_column('kp_users','username',"user_id='$appr_user_id'") : '';

/* ── snap helper ───────────────────────────────────────────── */
function snap_val($snap, $key, $fallback = '') {
    if (!$snap) return $fallback;
    $obj = is_array($snap) ? $snap : @json_decode($snap, true);
    return (isset($obj[$key]) && $obj[$key] !== '') ? $obj[$key] : $fallback;
}

/* ── log ───────────────────────────────────────────────────── */
$action = "PRINTED PURCHASE ORDER";
logs("../../",'back/kugura/','PURCHASE',$po_id,'-','-',$action);

date_default_timezone_set("Africa/Nairobi");
$today = date("Y-m-d H:i");
$logo = "../../../" . $gen['image_path'];
?>
<!DOCTYPE html>
<html lang="en">
<head>
<meta charset="utf-8">
<title>PO #<?= $po_id ?> – Print</title>
<style>
/* ── Reset & Base ─────────────────────────────────── */
*{margin:0;padding:0;box-sizing:border-box;}
body{font-family:'Segoe UI',Tahoma,Geneva,Verdana,sans-serif;font-size:13px;color:#1e293b;background:#fff;padding:20px 30px;}
table{border-collapse:collapse;width:100%;}

/* ── Header ───────────────────────────────────────── */
.pr-header{display:flex;justify-content:space-between;align-items:flex-start;border-bottom:3px solid #008080;padding-bottom:12px;margin-bottom:14px;}
.pr-logo img{max-height:64px;}
.pr-company{text-align:right;}
.pr-company h1{font-size:18px;font-weight:800;color:#008080;margin-bottom:2px;}
.pr-company .pr-doc-title{font-size:22px;font-weight:900;color:#1e293b;letter-spacing:.5px;margin-bottom:4px;}
.pr-company address{font-size:11px;color:#64748b;font-style:normal;line-height:1.5;}

/* ── Info grid ────────────────────────────────────── */
.pr-info{display:flex;gap:16px;margin-bottom:16px;}
.pr-info-box{flex:1;border:1px solid #e2e8f0;border-radius:8px;padding:10px 12px;}
.pr-info-box h3{font-size:10px;font-weight:700;text-transform:uppercase;letter-spacing:.06em;color:#008080;margin-bottom:6px;border-bottom:1px solid #e2e8f0;padding-bottom:4px;}
.pr-info-row{display:flex;justify-content:space-between;padding:2px 0;font-size:12px;}
.pr-info-row .lbl{color:#64748b;font-weight:600;}
.pr-info-row .val{font-weight:700;color:#1e293b;}

/* ── Items table ──────────────────────────────────── */
.pr-table{margin-bottom:16px;}
.pr-table thead th{background:#008080;color:#fff;font-size:10px;font-weight:700;text-transform:uppercase;letter-spacing:.04em;padding:8px 10px;text-align:left;}
.pr-table thead th:first-child{border-radius:6px 0 0 0;}
.pr-table thead th:last-child{border-radius:0 6px 0 0;}
.pr-table tbody td{padding:7px 10px;border-bottom:1px solid #f1f5f9;font-size:12px;vertical-align:top;}
.pr-table tbody tr:nth-child(even){background:#f8fafc;}
.pr-table .desc-main{font-weight:700;color:#1e293b;}
.pr-table .desc-sub{font-size:10.5px;color:#64748b;margin-top:1px;}
.pr-table .text-right{text-align:right;}
.pr-table tfoot td{padding:6px 10px;font-weight:700;font-size:12px;border-top:2px solid #e2e8f0;}

/* ── Signatures ───────────────────────────────────── */
.pr-sigs{display:flex;gap:20px;margin:30px 0 16px;}
.pr-sig-block{flex:1;text-align:center;}
.pr-sig-line{border-top:1.5px solid #94a3b8;margin-top:40px;padding-top:4px;font-size:11px;font-weight:700;color:#334155;}
.pr-sig-role{font-size:9px;color:#94a3b8;text-transform:uppercase;letter-spacing:.05em;}

/* ── Footer ───────────────────────────────────────── */
.pr-footer{border-top:2px solid #e2e8f0;padding-top:8px;text-align:center;font-size:10px;color:#94a3b8;margin-top:10px;}
.pr-footer strong{color:#008080;}

/* ── No-print controls ────────────────────────────── */
.pr-actions{text-align:center;margin-bottom:14px;}
.pr-actions button{border:none;border-radius:6px;padding:8px 18px;font-size:13px;font-weight:700;cursor:pointer;margin:0 4px;}
.pr-actions .btn-print{background:#008080;color:#fff;}
.pr-actions .btn-close-win{background:#e2e8f0;color:#334155;}
@media print{.pr-actions{display:none !important;} body{padding:10px;}}
</style>
</head>
<body onload="window.print()">

<!-- ── No-print buttons ────────────────────────────── -->
<div class="pr-actions">
  <button class="btn-print" onclick="window.print()">&#128438; Re-Print</button>
  <button class="btn-close-win" onclick="window.close()">&#10005; Close</button>
</div>

<!-- ═══ HEADER ══════════════════════════════════════ -->
<div class="pr-header">
  <div class="pr-logo">
    <img src="<?= $logo ?>" alt="Logo">
  </div>
  <div class="pr-company">
    <div class="pr-doc-title">PURCHASE ORDER</div>
    <h1><?= htmlspecialchars($gen['company_name']) ?></h1>
    <address>
      <?= htmlspecialchars($gen['address']) ?><br>
      Tel: <?= htmlspecialchars($gen['phone1']) ?>
      <?php if (!empty($gen['phone2'])): ?>, <?= htmlspecialchars($gen['phone2']) ?><?php endif; ?><br>
      <?php if (!empty($gen['email'])): ?><?= htmlspecialchars($gen['email']) ?><?php endif; ?>
      <?php if (!empty($gen['website'])): ?> | <?= htmlspecialchars($gen['website']) ?><?php endif; ?>
    </address>
  </div>
</div>

<!-- ═══ INFO GRID ═══════════════════════════════════ -->
<div class="pr-info">
  <div class="pr-info-box">
    <h3><span style="margin-right:4px;">&#128666;</span> Supplier</h3>
    <div class="pr-info-row"><span class="lbl">Name</span><span class="val"><?= htmlspecialchars($sup['sup_name'] ?? '-') ?></span></div>
    <div class="pr-info-row"><span class="lbl">Phone</span><span class="val"><?= htmlspecialchars($sup['sup_phone'] ?? '-') ?></span></div>
    <div class="pr-info-row"><span class="lbl">Email</span><span class="val"><?= htmlspecialchars($sup['sup_email'] ?? '-') ?></span></div>
    <div class="pr-info-row"><span class="lbl">Address</span><span class="val"><?= htmlspecialchars($sup['sup_address'] ?? '-') ?></span></div>
  </div>
  <div class="pr-info-box">
    <h3><span style="margin-right:4px;">&#128203;</span> Order Details</h3>
    <div class="pr-info-row"><span class="lbl">PO No.</span><span class="val" style="color:#008080;"><?= $po_id ?></span></div>
    <div class="pr-info-row"><span class="lbl">Date</span><span class="val"><?= htmlspecialchars($po_date) ?></span></div>
    <div class="pr-info-row"><span class="lbl">Raised By</span><span class="val"><?= htmlspecialchars($raised_by) ?></span></div>
  </div>
</div>

<!-- ═══ ITEMS TABLE ═════════════════════════════════ -->
<?php
$items_q = mysqli_query($connection, "SELECT * FROM kp_po_items WHERE po_id='$po_id' ORDER BY id ASC") or die(mysqli_error($connection));
$no = 0; $sum = 0; $sumd = 0;
?>
<table class="pr-table">
  <thead>
    <tr>
      <th style="width:36px;">#</th>
      <th>Item Description</th>
      <th class="text-right" style="width:70px;">Qty</th>
      <th class="text-right" style="width:90px;">Unit Price</th>
      <th class="text-right" style="width:80px;">Disc</th>
      <th class="text-right" style="width:100px;">Total</th>
    </tr>
  </thead>
  <tbody>
  <?php if (mysqli_num_rows($items_q) < 1): ?>
    <tr><td colspan="6" style="text-align:center;padding:20px;color:#94a3b8;">No items in this order.</td></tr>
  <?php else: ?>
    <?php while ($row = mysqli_fetch_assoc($items_q)):
        $no++;
        $price = $row['price'];
        $qty   = $row['qty'];
        $disc  = $row['disc'];
        $total = $row['total'];
        $sum  += $total;
        $sumd += $disc;
        $snap  = isset($row['snap']) ? @json_decode($row['snap'], true) : null;

        /* Build description from snap, fallback to DB */
        $item_name = snap_val($snap, 'item_name', '');
        if (!$item_name) {
            $item_name = get_column('kp_inventory_master','item_name',"item_id='".$row['item_id']."'");
        }
        $receipt_desc   = snap_val($snap, 'receipt_desc', '');
        $variation_code = snap_val($snap, 'variation_code', '');
        $barcode        = snap_val($snap, 'barcode', '');
        $uom            = snap_val($snap, 'uom', '');

        /* If no receipt_desc in snap, try DB variant */
        if (!$receipt_desc && !empty($row['variant_id'])) {
            $receipt_desc = get_column('kp_inventory_stock_variants','receipt_desc',"variant_id='".$row['variant_id']."'");
        }
        if (!$receipt_desc) $receipt_desc = $item_name;
    ?>
    <tr>
      <td style="text-align:center;color:#94a3b8;font-weight:700;"><?= $no ?></td>
      <td>
        <div class="desc-main"><?= htmlspecialchars($receipt_desc) ?></div>
        <?php
          $sub_parts = [];
          if ($variation_code) $sub_parts[] = $variation_code;
          if ($barcode) $sub_parts[] = 'BC: ' . $barcode;
          if ($uom) $sub_parts[] = $uom;
          if ($sub_parts): ?>
          <div class="desc-sub"><?= htmlspecialchars(implode(' &bull; ', $sub_parts)) ?></div>
        <?php endif; ?>
      </td>
      <td class="text-right"><?= $qty ?></td>
      <td class="text-right"><?= number_format($price, 2) ?></td>
      <td class="text-right"><?= number_format($disc, 2) ?></td>
      <td class="text-right"><?= number_format($total, 2) ?></td>
    </tr>
    <?php endwhile; ?>
  <?php endif; ?>
  </tbody>
  <tfoot>
    <tr>
      <td colspan="4"></td>
      <td class="text-right" style="color:#64748b;">Sub Total</td>
      <td class="text-right">Ksh <?= number_format($sum, 2) ?></td>
    </tr>
    <?php if ($sumd > 0): ?>
    <tr>
      <td colspan="4"></td>
      <td class="text-right" style="color:#64748b;">Discount</td>
      <td class="text-right" style="color:#ef4444;">-Ksh <?= number_format($sumd, 2) ?></td>
    </tr>
    <?php endif; ?>
    <tr style="font-size:14px;">
      <td colspan="4"></td>
      <td class="text-right" style="color:#008080;font-weight:800;">GRAND TOTAL</td>
      <td class="text-right" style="color:#008080;font-weight:800;">Ksh <?= number_format($sum - $sumd, 2) ?></td>
    </tr>
  </tfoot>
</table>

<!-- ═══ SIGNATURES ══════════════════════════════════ -->
<div class="pr-sigs">
  <div class="pr-sig-block">
    <div class="pr-sig-line">Prepared By</div>
    <div class="pr-sig-role"><?= htmlspecialchars($raised_by) ?></div>
  </div>
  <div class="pr-sig-block">
    <div class="pr-sig-line">Approved By</div>
    <div class="pr-sig-role"><?= $approved_by ? htmlspecialchars($approved_by) : 'Name &amp; Signature' ?></div>
  </div>
  <div class="pr-sig-block">
    <div class="pr-sig-line">Received By (Supplier)</div>
    <div class="pr-sig-role">Name, Stamp &amp; Signature</div>
  </div>
</div>

<!-- ═══ FOOTER ══════════════════════════════════════ -->
<div class="pr-footer">
  <strong><?= htmlspecialchars($gen['company_name']) ?></strong>
  &nbsp;|&nbsp; <?= htmlspecialchars($gen['address']) ?>
  &nbsp;|&nbsp; Tel: <?= htmlspecialchars($gen['phone1']) ?>
  <br>Printed: <?= $today ?>
</div>

</body>
</html>