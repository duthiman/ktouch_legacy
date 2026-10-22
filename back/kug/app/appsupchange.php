<?php
include('funcs.php');
$user_id = $_SESSION['mwene'];
$po_id   = $_REQUEST['id'];
logs("../../",'back/kugura','PURCHASE',$po_id,'-','-','LOADED SUPPLIER CHANGE MODAL');
logs("",'back/kugura','PURCHASE',$po_id,'-','-','LOADED SUPPLIER CHANGE MODAL');
?>
<div class="modal-content" style="border:none;box-shadow:0 8px 32px rgba(0,0,0,.18);border-radius:10px;overflow:hidden;">
  <div class="modal-header py-2 px-3" style="background:linear-gradient(135deg,#008080,#0d9488);border:none;">
    <h5 class="modal-title text-white mb-0" style="font-size:.88rem;font-weight:700;">
      <i class="fa fa-exchange-alt me-2"></i>CHANGE SUPPLIER — PO #<?php echo $po_id; ?>
    </h5>
    <button type="button" class="btn-close btn-close-white" data-bs-dismiss="modal"></button>
  </div>
  <div class="modal-body px-3 py-3">
    <div id="app-sup-result"></div>
    <label class="form-label fw-semibold small mb-1"><i class="fa fa-building me-1 text-muted"></i>Select new supplier</label>
    <select class="form-select form-select-sm" id="app-sup-sel" style="font-size:.8rem;">
      <option value="">-- Choose Supplier --</option>
      <?php
      $get_types = mysqli_query($connection,"SELECT * FROM kp_suppliers ORDER BY sup_name ASC") or die("Could not get suppliers");
      while ($type = mysqli_fetch_assoc($get_types)) {
      ?>
        <option value="<?php echo $type['sup_id']; ?>"><?php echo htmlspecialchars($type['sup_phone']."-".strtoupper($type['sup_name'])); ?></option>
      <?php } ?>
    </select>
  </div>
  <div class="modal-footer py-2 px-3" style="border-top:1px solid #f0f0f0;">
    <button type="button" class="btn btn-sm btn-outline-secondary" data-bs-dismiss="modal" style="font-size:.76rem;">
      <i class="fa fa-times me-1"></i>Cancel
    </button>
    <button type="button" class="btn btn-sm" id="btn-app-sup-chg" disabled
            style="font-size:.76rem;font-weight:700;background:#008080;color:#fff;">
      <i class="fa fa-exchange-alt me-1"></i>Change Supplier
    </button>
  </div>
</div>
<script>
(function(){
  var $sel = $('#app-sup-sel'), $btn = $('#btn-app-sup-chg'), poId = <?php echo (int)$po_id; ?>;
  if (typeof $.fn.select2 !== 'undefined') {
    $sel.select2({ dropdownParent: $('#modal'), width:'100%', placeholder:'-- Choose Supplier --' });
  }
  $sel.on('change', function(){ $btn.prop('disabled', !$(this).val()); });

  $(document).off('click.appSupChg').on('click.appSupChg', '#btn-app-sup-chg', function(){
    var sup = $sel.val();
    if (!sup) return;
    $btn.prop('disabled',true).html('<span class="spinner-border spinner-border-sm me-1"></span>Saving…');
    $.ajax({
      url: 'kug/app/appsupact.php',
      type: 'POST',
      data: { sup: sup, po: poId },
      dataType: 'html'
    }).done(function(d){
      $('#app-sup-result').html(d);
    }).fail(function(){
      $btn.prop('disabled',false).html('<i class="fa fa-exchange-alt me-1"></i>Change Supplier');
      $('#app-sup-result').html('<div class="alert alert-danger py-1 small">Request failed.</div>');
    });
  });
})();
</script>