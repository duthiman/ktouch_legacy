<?php
require_once __DIR__ . '/../../../inc/connection.php';
require_once __DIR__ . '/../../../inc/gen_funcs.php';
$user_id = (int)($_SESSION['mwene'] ?? $_SESSION['user_id'] ?? 0);
$url_po_id = (int)($_GET['po_id'] ?? $_GET['id'] ?? 0);
$po_id = $url_po_id > 0
  ? $url_po_id
  : (int)get_column('kp_thesion', 'trans_id', "user_id='{$user_id}'");
$sup_id  = get_column('kp_po_status','sup_id',"po_id='$po_id'");
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
    <div id="sup-chg-result"></div>
    <label class="form-label fw-semibold small mb-1"><i class="fa fa-building me-1 text-muted"></i>Select new supplier</label>
    <select class="form-select form-select-sm" id="sup-chg-sel" style="font-size:.8rem;">
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
    <button type="button" class="btn btn-sm btn-outline-success" id="btn-new-supplier" style="font-size:.76rem;font-weight:700;">
      <i class="fa fa-plus me-1"></i>Add New
    </button>
    <button type="button" class="btn btn-sm" id="btn-sup-chg" disabled
            style="font-size:.76rem;font-weight:700;background:#008080;color:#fff;">
      <i class="fa fa-exchange-alt me-1"></i>Change Supplier
    </button>
  </div>
</div>
<script>
  (function(){
    var addButton=document.getElementById('btn-new-supplier');
    if(addButton){addButton.addEventListener('click',function(){
      var load=document.getElementById('load');
      if(load && window.jQuery){
        jQuery(load).load('../../sup/sup_new.php', function(){
          var form=document.getElementById('sup_form');
          var save=document.getElementById('new_sup');
          if(form && save){
            form.addEventListener('submit', function(event){
              event.preventDefault();
              save.disabled=true;
              save.textContent='Saving...';
              var data={
                action:'create',
                name:document.getElementById('sup_name').value,
                sup_phone:document.getElementById('sup_phone').value,
                sup_email:document.getElementById('sup_email').value,
                sup_address:document.getElementById('sup_address').value,
                sup_location:document.getElementById('sup_location').value,
                sup_pin:document.getElementById('sup_pin').value,
                sup_vat:document.getElementById('sup_vat').value
              };
              jQuery.post('../../sup/api.php', data, function(response){
                var result=document.getElementById('results');
                if(response && response.ok){
                  result.innerHTML='<div class="alert alert-success">'+(response.message||'Supplier added.')+'</div>';
                  setTimeout(function(){ jQuery(load).load('sup_change.php?po_id=<?php echo $po_id; ?>'); }, 500);
                }else{
                  result.innerHTML='<div class="alert alert-danger">'+((response&&response.message)||'Could not add supplier.')+'</div>';
                  save.disabled=false; save.textContent='Save';
                }
              }, 'json').fail(function(){
                document.getElementById('results').innerHTML='<div class="alert alert-danger">Could not add supplier.</div>';
                save.disabled=false; save.textContent='Save';
              });
            });
          }
        });
      }
    });}
  }());
</script>
