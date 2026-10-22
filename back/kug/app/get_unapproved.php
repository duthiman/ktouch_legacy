<?php
include('funcs.php');

$query  = "SELECT p.po_id, p.po_date, p.datime, p.ord_user, p.sup_id
           FROM kp_po p
           WHERE p.appr_status=2
           ORDER BY p.po_id DESC";
$result = mysqli_query($connection, $query);
$rows   = [];
while ($row = mysqli_fetch_assoc($result)) {
    $rows[] = $row;
}
$count = count($rows);
?>
<div class="card border-0 shadow-sm">
  <div class="card-header py-2" style="background:linear-gradient(135deg,#f59c1a,#e87d20);border:none;">
    <div class="d-flex align-items-center justify-content-between">
      <h6 class="mb-0 fw-bold text-white">
        <i class="fa fa-check-circle me-2"></i>
        Pending Approval &nbsp;<span class="badge bg-white text-dark" id="appr-count"><?php echo $count; ?></span>
      </h6>
    </div>
  </div>
  <div class="card-body p-0">
    <?php if ($count === 0): ?>
      <div class="text-center py-5 text-muted">
        <i class="fa fa-check-circle fa-3x mb-3 text-success"></i>
        <p class="mb-0">No purchase orders pending approval.</p>
      </div>
    <?php else: ?>
    <div class="table-responsive">
      <table class="table table-sm table-hover mb-0" id="appr-table">
        <thead class="table-light">
          <tr>
            <th>#</th>
            <th>PO #</th>
            <th>Supplier</th>
            <th>Raised By</th>
            <th>Date</th>
            <th>Age</th>
            <th>Action</th>
          </tr>
        </thead>
        <tbody>
          <?php
          $no = 0;
          foreach ($rows as $pow):
            $no++;
            $po_id   = (int)$pow['po_id'];
            $user    = get_column('kp_users','username',"user_id='".(int)$pow['ord_user']."'");
            $sup     = get_column('kp_suppliers','sup_name',"sup_id='".(int)$pow['sup_id']."'");
            $po_date = $pow['po_date'];
            $age     = time_elapsed_str($pow['datime']);
          ?>
          <tr>
            <td><?php echo $no; ?></td>
            <td><strong><?php echo $po_id; ?></strong></td>
            <td><?php echo htmlspecialchars($sup); ?></td>
            <td><?php echo htmlspecialchars($user); ?></td>
            <td><?php echo htmlspecialchars($po_date); ?></td>
            <td><span class="badge bg-warning text-dark"><?php echo $age; ?></span></td>
            <td>
              <button type="button"
                class="btn btn-sm btn-warning ajax-loader"
                data-folder="kug/app"
                data-file="apppage"
                data-target="#content"
                data-target-type="div"
                data-method="GET"
                data-id="<?php echo $po_id; ?>">
                <i class="fa fa-check me-1"></i>Review
              </button>
            </td>
          </tr>
          <?php endforeach; ?>
        </tbody>
      </table>
    </div>
    <?php endif; ?>
  </div>
</div>

<?php
function time_elapsed_str($datetime) {
    $now  = new DateTime();
    $ago  = new DateTime($datetime);
    $diff = $now->diff($ago);
    if ($diff->y > 0) return $diff->y . ' yr' . ($diff->y > 1 ? 's' : '') . ' ago';
    if ($diff->m > 0) return $diff->m . ' mo ago';
    if ($diff->d > 0) return $diff->d . ' day' . ($diff->d > 1 ? 's' : '') . ' ago';
    if ($diff->h > 0) return $diff->h . ' hr' . ($diff->h > 1 ? 's' : '') . ' ago';
    if ($diff->i > 0) return $diff->i . ' min ago';
    return 'just now';
}
?>
