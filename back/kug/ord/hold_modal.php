<?php
require_once __DIR__ . '/../../../inc/connection.php';
require_once __DIR__ . '/../../../inc/gen_funcs.php';
$user_id = (int)($_SESSION['mwene'] ?? $_SESSION['user_id'] ?? 0);
$url_po_id = (int)($_GET['po_id'] ?? $_GET['id'] ?? 0);
$po_id = $url_po_id > 0
  ? $url_po_id
  : (int)get_column('kp_thesion', 'trans_id', "user_id='{$user_id}'");

logs("../../",'back/kugura/','PURCHASE',$po_id,'-','-','LOADED HOLD MODAL');
logs("",'back/kugura/','PURCHASE',$po_id,'-','-','LOADED HOLD MODAL');
?>
<div class="modal-content" style="border:none;box-shadow:0 8px 32px rgba(0,0,0,.18);border-radius:10px;overflow:hidden;">
  <div class="modal-header py-2 px-3" style="background:linear-gradient(135deg,#f59c1a,#fbbf24);border:none;">
    <h5 class="modal-title text-white mb-0" style="font-size:.88rem;font-weight:700;">
      <i class="fa fa-pause-circle me-2"></i>HOLD PURCHASE ORDER #<?php echo $po_id; ?>
    </h5>
    <button type="button" class="btn-close btn-close-white" data-bs-dismiss="modal"></button>
  </div>
  <div class="modal-body px-3 py-3">
    <div id="hold-result"></div>
    <div class="rounded p-3 mb-2" style="background:#fffbeb;border:1px solid #fde68a;">
      <div class="d-flex align-items-start gap-2">
        <i class="fa fa-exclamation-triangle mt-1" style="color:#f59c1a;font-size:1.1rem;"></i>
        <div>
          <div style="font-size:.82rem;font-weight:700;color:#92400e;">Warning</div>
          <div style="font-size:.76rem;color:#78350f;">
            This will place PO #<?php echo $po_id; ?> on hold. You can resume it later from the held orders list.
          </div>
        </div>
      </div>
    </div>
  </div>
  <div class="modal-footer py-2 px-3" style="border-top:1px solid #f0f0f0;">
    <button type="button" class="btn btn-sm btn-outline-secondary" data-bs-dismiss="modal" style="font-size:.76rem;">
      <i class="fa fa-times me-1"></i>Cancel
    </button>
    <button type="button" class="btn btn-sm btn-warning" id="btn-hold-confirm" style="font-size:.76rem;font-weight:700;">
      <i class="fa fa-pause-circle me-1"></i>Hold Order
    </button>
  </div>
</div>
