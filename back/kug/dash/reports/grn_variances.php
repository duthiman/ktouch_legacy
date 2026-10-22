<?php
include("funcs.php");
$user_id = $_SESSION['mwene'];
?>
<style>
.rpt-back { cursor:pointer; }
.rpt-back:hover { color:#008080 !important; }
.rpt-tbl thead th { font-size:.64rem; font-weight:700; text-transform:uppercase; letter-spacing:.04em; background:#f8f9fa; padding:.4rem .5rem; }
.rpt-tbl tbody td { font-size:.78rem; vertical-align:middle; padding:.35rem .5rem; }
.rpt-tbl tbody tr:hover { background:#f5f7ff !important; }
.var-over { color:#dc3545; font-weight:700; }
.var-under { color:#f59c1a; font-weight:700; }
.var-match { color:#16a34a; font-weight:700; }
</style>

<div class="widget">
  <div class="widget-header p-2 d-flex justify-content-between align-items-center">
    <h4 class="my-0 text-cyan" style="font-size:.84rem;">
      <span class="rpt-back text-muted me-2" onclick="kugLoad()"><i class="fa fa-arrow-left"></i></span>
      <i class="fa fa-exclamation-triangle me-1 text-warning"></i> GRN VARIANCES
    </h4>
    <input type="text" id="rpt-var-search" class="form-control form-control-sm" placeholder="Search…" style="width:180px;font-size:.74rem;">
  </div>
  <div class="p-2" style="overflow-x:auto;max-height:70vh;overflow-y:auto;">
    <?php
    // For each GRN item, compare received qty vs PO ordered qty
    $query = "
      SELECT gi.grn_id, gi.item_id, gi.qty AS received_qty, gi.price AS grn_price, gi.total AS grn_total,
             g.po_id, g.day AS grn_date, s.sup_name,
             pi.qty AS ordered_qty, pi.price AS po_price, pi.total AS po_total,
             (gi.qty - COALESCE(pi.qty, gi.qty)) AS qty_variance,
             (gi.total - COALESCE(pi.total, gi.total)) AS amount_variance
      FROM kp_grn_items gi
      INNER JOIN kp_grn g ON g.grn_id = gi.grn_id
      LEFT JOIN kp_suppliers s ON s.sup_id = g.sup_id
      LEFT JOIN kp_po_items pi ON pi.po_id = g.po_id AND pi.item_id = gi.item_id
      WHERE (gi.qty != COALESCE(pi.qty, gi.qty) OR gi.price != COALESCE(pi.price, gi.price))
      ORDER BY g.grn_id DESC
      LIMIT 200
    ";
    $result = mysqli_query($connection, $query);
    if ($result && mysqli_num_rows($result) > 0): ?>
    <table class="table table-sm table-hover mb-0 rpt-tbl" id="rpt-var-table">
      <thead>
        <tr>
          <th>GRN #</th><th>PO #</th><th>Supplier</th><th>Item</th><th>Date</th>
          <th>Ordered Qty</th><th>Received Qty</th><th>Qty Var</th>
          <th>PO Price</th><th>GRN Price</th><th>Amount Var</th>
        </tr>
      </thead>
      <tbody>
        <?php while ($row = mysqli_fetch_assoc($result)):
          $qty_var = floatval($row['qty_variance']);
          $amt_var = floatval($row['amount_variance']);
          $qty_cls = $qty_var > 0 ? 'var-over' : ($qty_var < 0 ? 'var-under' : 'var-match');
          $amt_cls = $amt_var > 0 ? 'var-over' : ($amt_var < 0 ? 'var-under' : 'var-match');
          $item_name = get_item($row['item_id'] ?? 0);
        ?>
        <tr>
          <td><strong class="text-info">#<?= (int)$row['grn_id'] ?></strong></td>
          <td class="text-muted">#<?= (int)$row['po_id'] ?></td>
          <td><?= htmlspecialchars($row['sup_name'] ?? '–') ?></td>
          <td><?= htmlspecialchars($item_name) ?></td>
          <td class="text-muted"><?= htmlspecialchars($row['grn_date'] ?? '–') ?></td>
          <td class="text-center"><?= number_format(floatval($row['ordered_qty'] ?? 0)) ?></td>
          <td class="text-center"><?= number_format(floatval($row['received_qty'] ?? 0)) ?></td>
          <td class="text-center <?= $qty_cls ?>"><?= ($qty_var > 0 ? '+' : '') . number_format($qty_var) ?></td>
          <td><?= number_format(floatval($row['po_price'] ?? 0), 2) ?></td>
          <td><?= number_format(floatval($row['grn_price'] ?? 0), 2) ?></td>
          <td class="<?= $amt_cls ?>"><?= ($amt_var > 0 ? '+' : '') . number_format($amt_var, 2) ?></td>
        </tr>
        <?php endwhile; ?>
      </tbody>
    </table>
    <?php else: ?>
    <div class="text-center py-5 text-muted">
      <i class="fa fa-check-circle fa-3x mb-2" style="opacity:.2;color:#16a34a;"></i>
      <div class="small">No variances found — all GRN items match PO quantities and prices.</div>
    </div>
    <?php endif; ?>
  </div>
</div>

<script>
$('#rpt-var-search').on('input', function () {
  var term = $(this).val().toLowerCase();
  $('#rpt-var-table tbody tr').each(function () {
    $(this).toggle($(this).text().toLowerCase().indexOf(term) >= 0);
  });
});
</script>
