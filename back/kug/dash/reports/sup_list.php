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
.sup-view-btn { cursor:pointer; text-decoration:underline; }
.sup-view-btn:hover { color:#008080; }
</style>

<div class="widget">
  <div class="widget-header p-2 d-flex justify-content-between align-items-center">
    <h4 class="my-0 text-cyan" style="font-size:.84rem;">
      <span class="rpt-back text-muted me-2" onclick="kugLoad()"><i class="fa fa-arrow-left"></i></span>
      <i class="fa fa-building me-1"></i> SUPPLIER LIST
    </h4>
    <div class="d-flex gap-2 align-items-center">
      <input type="text" id="rpt-sup-search" class="form-control form-control-sm" placeholder="Search…" style="width:180px;font-size:.74rem;">
    </div>
  </div>
  <div class="p-2" style="overflow-x:auto;max-height:70vh;overflow-y:auto;">
    <?php
    $query = "SELECT s.*,
                     (SELECT COUNT(*) FROM kp_po WHERE sup_id=s.sup_id) AS po_count,
                     (SELECT COALESCE(SUM(pi.total),0) FROM kp_po p INNER JOIN kp_po_items pi ON pi.po_id=p.po_id WHERE p.sup_id=s.sup_id) AS total_spend
              FROM kp_suppliers s
              ORDER BY s.sup_name ASC";
    $result = mysqli_query($connection, $query);
    if ($result && mysqli_num_rows($result) > 0): ?>
    <table class="table table-sm table-hover mb-0 rpt-tbl" id="rpt-sup-table">
      <thead>
        <tr>
          <th>#</th><th>Supplier Name</th><th>Phone</th><th>Email</th>
          <th>Location</th><th>KRA PIN</th><th>POs</th><th>Total Spend</th><th></th>
        </tr>
      </thead>
      <tbody>
        <?php $no=0; while ($row = mysqli_fetch_assoc($result)):
          $no++;
          $spend = number_format(floatval($row['total_spend'] ?? 0), 2);
        ?>
        <tr>
          <td><?= $no ?></td>
          <td><strong class="sup-view-btn text-primary" data-sup="<?= (int)$row['sup_id'] ?>"><?= htmlspecialchars($row['sup_name'] ?? '–') ?></strong></td>
          <td class="text-muted"><?= htmlspecialchars($row['sup_phone'] ?? '–') ?></td>
          <td class="text-muted"><?= htmlspecialchars($row['sup_email'] ?? '–') ?></td>
          <td class="text-muted"><?= htmlspecialchars($row['sup_location'] ?? '–') ?></td>
          <td class="text-muted"><?= htmlspecialchars($row['sup_pin'] ?? '–') ?></td>
          <td class="text-center"><span class="badge bg-primary"><?= (int)($row['po_count'] ?? 0) ?></span></td>
          <td class="fw-medium"><?= $spend ?></td>
          <td>
            <button class="btn btn-xs btn-outline-info sup-statement-btn" data-sup="<?= (int)$row['sup_id'] ?>" data-name="<?= htmlspecialchars($row['sup_name'] ?? '') ?>">
              <i class="fa fa-file-alt me-1"></i>Statement
            </button>
          </td>
        </tr>
        <?php endwhile; ?>
      </tbody>
    </table>
    <?php else: ?>
    <div class="text-center py-5 text-muted">
      <i class="fa fa-building fa-3x mb-2" style="opacity:.2;"></i>
      <div class="small">No suppliers found.</div>
    </div>
    <?php endif; ?>
  </div>
</div>

<script>
$('#rpt-sup-search').on('input', function () {
  var term = $(this).val().toLowerCase();
  $('#rpt-sup-table tbody tr').each(function () {
    $(this).toggle($(this).text().toLowerCase().indexOf(term) >= 0);
  });
});

// Supplier name click → statement
$(document).off('click.supView').on('click.supView', '.sup-view-btn,.sup-statement-btn', function () {
  var sup = $(this).data('sup');
  $('#kug-filt-rest').html('<div class="text-center p-4"><i class="fa fa-spinner fa-spin fa-2x text-muted"></i></div>');
  $.get('kug/dash/reports/sup_statement.php', { id: sup }, function (html) {
    $('#kug-filt-rest').html(html);
  }).fail(function () {
    $('#kug-filt-rest').html('<div class="alert alert-danger m-3">Failed to load supplier statement.</div>');
  });
});
</script>
