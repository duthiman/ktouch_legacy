<?php
include("funcs.php");
$user_id = $_SESSION['mwene'];

if (!isset($_REQUEST['id'])) { echo '<div class="text-danger p-3">No supplier selected.</div>'; exit; }
$sup_id = intval($_REQUEST['id']);

// Supplier info
$sup = mysqli_fetch_assoc(mysqli_query($connection, "SELECT * FROM kp_suppliers WHERE sup_id='$sup_id'"));
if (!$sup) { echo '<div class="text-danger p-3">Supplier not found.</div>'; exit; }

// PO summary
$po_q = mysqli_query($connection, "
  SELECT p.*,
         u.username AS raised_by,
         (SELECT COUNT(*) FROM kp_po_items WHERE po_id=p.po_id) AS item_count,
         (SELECT COALESCE(SUM(total),0) FROM kp_po_items WHERE po_id=p.po_id) AS amount
  FROM kp_po p
  LEFT JOIN kp_users u ON u.user_id = p.user_id
  WHERE p.sup_id='$sup_id'
  ORDER BY p.po_id DESC
");
$pos = [];
$total_spend = 0;
if ($po_q) { while ($r = mysqli_fetch_assoc($po_q)) { $pos[] = $r; $total_spend += floatval($r['amount']); } }

// GRN summary
$grn_q = mysqli_query($connection, "
  SELECT g.*, u.username AS received_by,
         (SELECT COUNT(*) FROM kp_grn_items WHERE grn_id=g.grn_id) AS item_count
  FROM kp_grn g
  LEFT JOIN kp_users u ON u.user_id = g.user_id
  WHERE g.sup_id='$sup_id'
  ORDER BY g.grn_id DESC
");
$grns = [];
$total_grn_amount = 0;
if ($grn_q) { while ($r = mysqli_fetch_assoc($grn_q)) { $grns[] = $r; $total_grn_amount += floatval($r['amount'] ?? 0); } }
?>
<style>
.stmt-back { cursor:pointer; }
.stmt-back:hover { color:#008080 !important; }
.stmt-header { background:linear-gradient(135deg,#1e3a5f,#2563eb); color:#fff; border-radius:10px; padding:1rem 1.2rem; margin-bottom:1rem; }
.stmt-header h5 { font-size:.92rem; font-weight:700; margin:0; }
.stmt-header .stmt-sub { font-size:.72rem; opacity:.85; }
.stmt-kpi { border-radius:8px; padding:.6rem .8rem; background:#fff; border:1px solid #e5e7eb; }
.stmt-kpi .kpi-val { font-size:1.2rem; font-weight:800; }
.stmt-kpi .kpi-lbl { font-size:.6rem; font-weight:700; text-transform:uppercase; color:#6c757d; }
.stmt-tbl thead th { font-size:.64rem; font-weight:700; text-transform:uppercase; letter-spacing:.04em; background:#f8f9fa; padding:.4rem .5rem; }
.stmt-tbl tbody td { font-size:.78rem; vertical-align:middle; padding:.35rem .5rem; }
.stmt-tbl tbody tr:hover { background:#f5f7ff !important; }
.po-click { cursor:pointer; text-decoration:underline; }
.po-click:hover { color:#008080; }
</style>

<!-- Back nav -->
<div class="mb-2">
  <span class="stmt-back text-muted" style="font-size:.78rem;cursor:pointer;" onclick="kugLoad()">
    <i class="fa fa-arrow-left me-1"></i>Back to Dashboard
  </span>
  <span class="mx-2 text-muted">/</span>
  <span class="text-muted" style="font-size:.78rem;cursor:pointer;" onclick="$.get('kug/dash/reports/sup_list.php',function(h){$('#kug-filt-rest').html(h);})">
    Supplier List
  </span>
</div>

<!-- Supplier Header Card -->
<div class="stmt-header">
  <div class="d-flex justify-content-between align-items-start flex-wrap gap-2">
    <div>
      <h5><i class="fa fa-building me-2"></i><?= htmlspecialchars($sup['sup_name']) ?></h5>
      <div class="stmt-sub mt-1">
        <?php if (!empty($sup['sup_phone'])): ?><i class="fa fa-phone me-1"></i><?= htmlspecialchars($sup['sup_phone']) ?> &nbsp; <?php endif; ?>
        <?php if (!empty($sup['sup_email'])): ?><i class="fa fa-envelope me-1"></i><?= htmlspecialchars($sup['sup_email']) ?> &nbsp; <?php endif; ?>
        <?php if (!empty($sup['sup_location'])): ?><i class="fa fa-map-marker me-1"></i><?= htmlspecialchars($sup['sup_location']) ?><?php endif; ?>
      </div>
      <?php if (!empty($sup['sup_pin'])): ?>
      <div class="stmt-sub mt-1">KRA PIN: <?= htmlspecialchars($sup['sup_pin']) ?>
        <?php if (!empty($sup['sup_vat'])): ?> &bull; VAT: <?= htmlspecialchars($sup['sup_vat']) ?><?php endif; ?>
      </div>
      <?php endif; ?>
    </div>
    <div class="text-end">
      <div style="font-size:.6rem;opacity:.8;">SUPPLIER ID</div>
      <div style="font-size:1.3rem;font-weight:800;">#<?= $sup_id ?></div>
    </div>
  </div>
</div>

<!-- KPI Cards -->
<div class="row g-2 mb-3">
  <div class="col-md-3 col-6">
    <div class="stmt-kpi">
      <div class="kpi-lbl"><i class="fa fa-shopping-cart me-1 text-primary"></i>Total POs</div>
      <div class="kpi-val text-primary"><?= count($pos) ?></div>
    </div>
  </div>
  <div class="col-md-3 col-6">
    <div class="stmt-kpi">
      <div class="kpi-lbl"><i class="fa fa-money-bill me-1 text-success"></i>Total Spend</div>
      <div class="kpi-val text-success">KSh <?= number_format($total_spend, 2) ?></div>
    </div>
  </div>
  <div class="col-md-3 col-6">
    <div class="stmt-kpi">
      <div class="kpi-lbl"><i class="fa fa-clipboard-list me-1 text-info"></i>Total GRNs</div>
      <div class="kpi-val text-info"><?= count($grns) ?></div>
    </div>
  </div>
  <div class="col-md-3 col-6">
    <div class="stmt-kpi">
      <div class="kpi-lbl"><i class="fa fa-coins me-1 text-warning"></i>GRN Value</div>
      <div class="kpi-val text-warning">KSh <?= number_format($total_grn_amount, 2) ?></div>
    </div>
  </div>
</div>

<!-- Purchase History -->
<div class="widget mb-3">
  <div class="widget-header p-2">
    <h4 class="my-0 text-cyan" style="font-size:.82rem;"><i class="fa fa-shopping-cart me-1"></i> PURCHASE ORDER HISTORY</h4>
  </div>
  <div class="p-2" style="overflow-x:auto;max-height:40vh;overflow-y:auto;">
    <?php if (count($pos) > 0): ?>
    <table class="table table-sm table-hover mb-0 stmt-tbl">
      <thead>
        <tr><th>PO #</th><th>Raised By</th><th>Date</th><th>Items</th><th>Amount</th><th>Type</th><th>Approval</th><th>Receiving</th></tr>
      </thead>
      <tbody>
        <?php foreach ($pos as $po):
          $appr = (int)($po['appr_status'] ?? 2);
          $rec  = (int)($po['rec_status'] ?? 2);
          $appr_b = $appr == 1 ? '<span class="badge bg-success" style="font-size:.6rem;">Approved</span>' : '<span class="badge bg-warning text-dark" style="font-size:.6rem;">Pending</span>';
          $rec_b = $rec == 1 ? '<span class="badge bg-success" style="font-size:.6rem;">Received</span>'
                 : ($rec == 3 ? '<span class="badge bg-info" style="font-size:.6rem;">Partial</span>'
                              : '<span class="badge bg-danger" style="font-size:.6rem;">Pending</span>');
          $type_b = ((int)($po['potype'] ?? 2)) == 1 ? '<span class="badge bg-info" style="font-size:.6rem;">Intl</span>' : '<span class="badge bg-secondary" style="font-size:.6rem;">Local</span>';
        ?>
        <tr>
          <td><strong class="text-primary po-click" data-po="<?= (int)$po['po_id'] ?>">#<?= (int)$po['po_id'] ?></strong></td>
          <td class="text-muted"><?= htmlspecialchars($po['raised_by'] ?? '–') ?></td>
          <td class="text-muted"><?= htmlspecialchars($po['po_date'] ?? $po['day'] ?? '–') ?></td>
          <td class="text-center"><span class="badge bg-secondary"><?= (int)($po['item_count'] ?? 0) ?></span></td>
          <td class="fw-medium"><?= number_format(floatval($po['amount'] ?? 0), 2) ?></td>
          <td><?= $type_b ?></td>
          <td><?= $appr_b ?></td>
          <td><?= $rec_b ?></td>
        </tr>
        <?php endforeach; ?>
      </tbody>
    </table>
    <?php else: ?>
    <div class="text-center py-4 text-muted small">No purchase orders for this supplier.</div>
    <?php endif; ?>
  </div>
</div>

<!-- GRN History -->
<div class="widget mb-3">
  <div class="widget-header p-2">
    <h4 class="my-0 text-cyan" style="font-size:.82rem;"><i class="fa fa-clipboard-list me-1"></i> GRN HISTORY</h4>
  </div>
  <div class="p-2" style="overflow-x:auto;max-height:35vh;overflow-y:auto;">
    <?php if (count($grns) > 0): ?>
    <table class="table table-sm table-hover mb-0 stmt-tbl">
      <thead>
        <tr><th>GRN #</th><th>PO #</th><th>Received By</th><th>Date</th><th>Items</th><th>Amount</th><th>Status</th><th></th></tr>
      </thead>
      <tbody>
        <?php foreach ($grns as $g):
          $st = (int)$g['status'];
          $st_b = $st == 1 ? '<span class="badge bg-success" style="font-size:.6rem;">Complete</span>'
                : ($st == 3 ? '<span class="badge bg-warning text-dark" style="font-size:.6rem;">Ongoing</span>'
                            : '<span class="badge bg-danger" style="font-size:.6rem;">Pending</span>');
        ?>
        <tr>
          <td><strong class="text-info">#<?= (int)$g['grn_id'] ?></strong></td>
          <td class="text-muted po-click" data-po="<?= (int)$g['po_id'] ?>">#<?= (int)$g['po_id'] ?></td>
          <td class="text-muted"><?= htmlspecialchars($g['received_by'] ?? '–') ?></td>
          <td class="text-muted"><?= htmlspecialchars($g['day'] ?? '–') ?></td>
          <td class="text-center"><span class="badge bg-secondary"><?= (int)($g['item_count'] ?? 0) ?></span></td>
          <td class="fw-medium"><?= number_format(floatval($g['amount'] ?? 0), 2) ?></td>
          <td><?= $st_b ?></td>
          <td>
            <button class="btn btn-xs btn-outline-cyan grn-view-btn" data-id="<?= (int)$g['grn_id'] ?>">
              <i class="fa fa-eye"></i>
            </button>
          </td>
        </tr>
        <?php endforeach; ?>
      </tbody>
    </table>
    <?php else: ?>
    <div class="text-center py-4 text-muted small">No GRN records for this supplier.</div>
    <?php endif; ?>
  </div>
</div>

<script>
// Clickable PO → detail in modal
$(document).off('click.poClick').on('click.poClick', '.po-click', function () {
  var po = $(this).data('po');
  if (!po) return;
  $('#load').html('<div class="modal-content"><div class="modal-body text-center p-4"><i class="fa fa-spinner fa-spin fa-2x text-muted"></i></div></div>');
  $('#modal').modal('show');
  $.ajax({ url: 'kug/dash/reports/po_detail.php', type: 'GET', data: { id: po }, dataType: 'html' })
    .done(function (d) { $('#load').addClass('modal-lg').html(d); })
    .fail(function () { $('#load').html('<div class="modal-content"><div class="modal-body text-danger p-3">Failed to load PO details.</div></div>'); });
});

// GRN view button
$(document).off('click.grnView2').on('click.grnView2', '.grn-view-btn', function () {
  var id = $(this).data('id');
  $('#load').html('<div class="modal-content"><div class="modal-body text-center p-4"><i class="fa fa-spinner fa-spin fa-2x text-muted"></i></div></div>');
  $('#modal').modal('show');
  $.ajax({ url: 'kug/dash/vwgrn.php', type: 'GET', data: { id: id }, dataType: 'html' })
    .done(function (d) { $('#load').addClass('modal-lg').html(d); })
    .fail(function () { $('#load').html('<div class="modal-content"><div class="modal-body text-danger p-3">Failed to load GRN.</div></div>'); });
});
</script>
