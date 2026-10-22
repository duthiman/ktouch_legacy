<?php
require_once __DIR__ . '/../../../inc/connection.php';
require_once __DIR__ . '/../../../inc/gen_funcs.php';
$user_id = (int)($_SESSION['mwene'] ?? $_SESSION['user_id'] ?? 0);
$url_po_id = (int)($_GET['po_id'] ?? $_GET['id'] ?? 0);
$po_id = $url_po_id > 0
  ? $url_po_id
  : (int)get_column('kp_thesion', 'trans_id', "user_id='{$user_id}'");
logs("../../",'back/kugura/','PURCHASE',$po_id,'-','-','LOADED CANCEL MODAL');
logs("",'back/kugura/','PURCHASE',$po_id,'-','-','LOADED CANCEL MODAL');

if ($user_id==0 || empty($user_id)) {
?>
<script>var hulla = new hullabaloo(); hulla.send("SESSION Issue — Logout and login again", "danger");</script>
<?php
} elseif ($po_id==0) {
?>
<script>var hulla = new hullabaloo(); hulla.send("No active order found. Try to hold and retry.", "danger");</script>
<?php
} else {
?>
<div class="modal-content" style="border:none;box-shadow:0 8px 32px rgba(0,0,0,.18);border-radius:10px;overflow:hidden;">
  <div class="modal-header py-2 px-3" style="background:linear-gradient(135deg,#dc2626,#ef4444);border:none;">
    <h5 class="modal-title text-white mb-0" style="font-size:.88rem;font-weight:700;">
      <i class="fa fa-times-circle me-2"></i>CANCEL PURCHASE ORDER #<?php echo $po_id; ?>
    </h5>
    <button type="button" class="btn-close btn-close-white" data-bs-dismiss="modal"></button>
  </div>
  <div class="modal-body px-3 py-3">
    <div id="cancel-result"></div>
    <div class="rounded p-3 mb-2" style="background:#fef2f2;border:1px solid #fecaca;">
      <div class="d-flex align-items-start gap-2">
        <i class="fa fa-exclamation-triangle mt-1" style="color:#dc2626;font-size:1.1rem;"></i>
        <div>
          <div style="font-size:.82rem;font-weight:700;color:#991b1b;">Warning — Irreversible Action</div>
          <div style="font-size:.76rem;color:#7f1d1d;">
            This will permanently delete PO #<?php echo $po_id; ?> and all its items. This cannot be undone.
          </div>
        </div>
      </div>
    </div>
  </div>
  <div class="modal-footer py-2 px-3" style="border-top:1px solid #f0f0f0;">
    <button type="button" class="btn btn-sm btn-outline-secondary" data-bs-dismiss="modal" style="font-size:.76rem;">
      <i class="fa fa-arrow-left me-1"></i>Keep Order
    </button>
    <button type="button" class="btn btn-sm btn-danger" id="btn-cancel-confirm" style="font-size:.76rem;font-weight:700;">
      <i class="fa fa-trash me-1"></i>Cancel Order
    </button>
  </div>
</div>
<script>
$(document).off('click.cancelConfirm').on('click.cancelConfirm', '#btn-cancel-confirm', function () {
  var btn = $(this);
  btn.prop('disabled', true).html('<span class="spinner-border spinner-border-sm me-1"></span> Cancelling…');
  $.ajax({
    url: 'kug/ord/cancel_po.php',
    type: 'POST',
    data: {},
    dataType: 'html'
  }).done(function (d) {
    $('#cancel-result').html(d);
  }).fail(function () {
    btn.prop('disabled', false).html('<i class="fa fa-trash me-1"></i>Cancel Order');
    $('#cancel-result').html('<div class="alert alert-danger py-1 small">Request failed.</div>');
  });
});
</script>
<?php 
} 
?>
