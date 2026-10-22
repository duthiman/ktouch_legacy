<?php
include("funcs.php");
if (!isset($_REQUEST['id'])) { echo '<div class="text-danger p-3">No PO selected.</div>'; exit; }
$po_id = intval($_REQUEST['id']);

// PO header
$po = mysqli_fetch_assoc(mysqli_query($connection,
  "SELECT p.*, s.sup_name, s.sup_phone, s.sup_email, s.sup_location, u.username AS raised_by
   FROM kp_po p
   LEFT JOIN kp_suppliers s ON s.sup_id = p.sup_id
   LEFT JOIN kp_users u ON u.user_id = p.user_id
   WHERE p.po_id='$po_id'"
));
if (!$po) { echo '<div class="text-danger p-3">PO not found.</div>'; exit; }

// PO items
$items_q = mysqli_query($connection, "SELECT * FROM kp_po_items WHERE po_id='$po_id' ORDER BY item_id ASC");
$items = [];
$total = 0;
if ($items_q) { while ($r = mysqli_fetch_assoc($items_q)) { $items[] = $r; $total += floatval($r['total'] ?? 0); } }

// GRNs for this PO
$grns_q = mysqli_query($connection,
  "SELECT g.*, u.username AS received_by FROM kp_grn g LEFT JOIN kp_users u ON u.user_id=g.user_id WHERE g.po_id='$po_id' ORDER BY g.grn_id DESC"
);
$grns = [];
if ($grns_q) { while ($r = mysqli_fetch_assoc($grns_q)) $grns[] = $r; }

$appr = (int)($po['appr_status'] ?? 2);
$rec  = (int)($po['rec_status'] ?? 2);
$potype = (int)($po['potype'] ?? 2);
?>

<div class="modal-content" style="border:none;box-shadow:0 8px 40px rgba(0,0,0,.18);">

  <div class="d-flex justify-content-between align-items-center px-3 py-2" style="background:linear-gradient(135deg,#1e3a5f,#2563eb);color:#fff;border-radius:8px 8px 0 0;">
    <div>
      <span style="font-size:.92rem;font-weight:700;"><i class="fa fa-file-invoice me-2"></i>PURCHASE ORDER #<?= $po_id ?></span>
      <span class="ms-2" style="font-size:.68rem;opacity:.8;">
        <?= $potype == 1 ? '(International)' : '(Local)' ?>
      </span>
    </div>
    <button type="button" class="btn-close" style="filter:invert(1) brightness(2);opacity:.8;" data-bs-dismiss="modal"></button>
  </div>

  <div class="modal-body p-3" style="max-height:70vh;overflow-y:auto;">

    <!-- PO Info -->
    <div class="row g-3 mb-3">
      <div class="col-md-6">
        <div class="border rounded p-2" style="font-size:.76rem;">
          <div style="font-size:.62rem;font-weight:700;color:#6c757d;text-transform:uppercase;margin-bottom:.3rem;">SUPPLIER</div>
          <div class="fw-bold"><?= htmlspecialchars($po['sup_name'] ?? '–') ?></div>
          <div class="text-muted"><?= htmlspecialchars($po['sup_phone'] ?? '') ?> <?= !empty($po['sup_email']) ? '• '.$po['sup_email'] : '' ?></div>
          <?php if (!empty($po['sup_location'])): ?><div class="text-muted"><i class="fa fa-map-marker me-1"></i><?= htmlspecialchars($po['sup_location']) ?></div><?php endif; ?>
        </div>
      </div>
      <div class="col-md-6">
        <div class="border rounded p-2" style="font-size:.76rem;">
          <div class="d-flex justify-content-between">
            <div>
              <div style="font-size:.62rem;font-weight:700;color:#6c757d;text-transform:uppercase;">Raised By</div>
              <div><?= htmlspecialchars($po['raised_by'] ?? '–') ?></div>
            </div>
            <div>
              <div style="font-size:.62rem;font-weight:700;color:#6c757d;text-transform:uppercase;">Date</div>
              <div><?= htmlspecialchars($po['po_date'] ?? $po['day'] ?? '–') ?></div>
            </div>
          </div>
          <div class="d-flex gap-2 mt-2">
            <?php
            echo $appr == 1 ? '<span class="badge bg-success" style="font-size:.62rem;">Approved</span>' : '<span class="badge bg-warning text-dark" style="font-size:.62rem;">Pending Approval</span>';
            echo $rec == 1 ? '<span class="badge bg-success" style="font-size:.62rem;">Received</span>' : ($rec == 3 ? '<span class="badge bg-info" style="font-size:.62rem;">Partial</span>' : '<span class="badge bg-danger" style="font-size:.62rem;">Not Received</span>');
            ?>
          </div>
        </div>
      </div>
    </div>

    <!-- PO Items -->
    <div class="mb-3">
      <div style="font-size:.68rem;font-weight:700;color:#6c757d;text-transform:uppercase;margin-bottom:.3rem;">
        <i class="fa fa-list me-1"></i>LINE ITEMS (<?= count($items) ?>)
      </div>
      <?php if (count($items) > 0): ?>
      <div style="overflow-x:auto;">
        <table class="table table-sm table-hover mb-0" style="font-size:.76rem;">
          <thead style="background:#f8f9fa;">
            <tr>
              <th style="font-size:.62rem;">#</th>
              <th style="font-size:.62rem;">ITEM</th>
              <th style="font-size:.62rem;">QTY</th>
              <th style="font-size:.62rem;">PRICE</th>
              <th style="font-size:.62rem;">TOTAL</th>
            </tr>
          </thead>
          <tbody>
            <?php $n=0; foreach ($items as $it): $n++;
              // Try to decode snap for item name
              $snap = json_decode($it['snap'] ?? '{}', true);
              $item_name = $snap['name'] ?? $snap['item_name'] ?? get_item($it['item_id'] ?? 0);
            ?>
            <tr>
              <td><?= $n ?></td>
              <td class="fw-medium"><?= htmlspecialchars($item_name) ?></td>
              <td><?= number_format(floatval($it['qty'] ?? 0)) ?></td>
              <td><?= number_format(floatval($it['price'] ?? 0), 2) ?></td>
              <td class="fw-bold"><?= number_format(floatval($it['total'] ?? 0), 2) ?></td>
            </tr>
            <?php endforeach; ?>
          </tbody>
          <tfoot style="background:#f0fdf9;">
            <tr>
              <td colspan="4" class="text-end fw-bold" style="font-size:.76rem;">TOTAL</td>
              <td class="fw-bold" style="font-size:.88rem;color:#008080;"><?= number_format($total, 2) ?></td>
            </tr>
          </tfoot>
        </table>
      </div>
      <?php else: ?>
      <div class="text-muted small text-center py-3">No line items found.</div>
      <?php endif; ?>
    </div>

    <!-- GRNs -->
    <?php if (count($grns) > 0): ?>
    <div>
      <div style="font-size:.68rem;font-weight:700;color:#6c757d;text-transform:uppercase;margin-bottom:.3rem;">
        <i class="fa fa-clipboard-list me-1"></i>GOODS RECEIVED NOTES (<?= count($grns) ?>)
      </div>
      <table class="table table-sm table-hover mb-0" style="font-size:.76rem;">
        <thead style="background:#f8f9fa;">
          <tr>
            <th style="font-size:.62rem;">GRN #</th>
            <th style="font-size:.62rem;">RECEIVED BY</th>
            <th style="font-size:.62rem;">DATE</th>
            <th style="font-size:.62rem;">AMOUNT</th>
            <th style="font-size:.62rem;">STATUS</th>
          </tr>
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
            <td class="text-muted"><?= htmlspecialchars($g['received_by'] ?? '–') ?></td>
            <td class="text-muted"><?= htmlspecialchars($g['day'] ?? '–') ?></td>
            <td class="fw-medium"><?= number_format(floatval($g['amount'] ?? 0), 2) ?></td>
            <td><?= $st_b ?></td>
          </tr>
          <?php endforeach; ?>
        </tbody>
      </table>
    </div>
    <?php endif; ?>

  </div>

  <div class="modal-footer py-2 px-3" style="border-top:1px solid #f0f0f0;">
    <button type="button" class="btn btn-sm btn-outline-secondary" data-bs-dismiss="modal"><i class="fa fa-times me-1"></i>Close</button>
  </div>

</div>
