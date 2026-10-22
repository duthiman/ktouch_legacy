<?php
session_start();
include('../../inc/connection.php');
include('funcs.php');
?>
<div class="modal-content">
  <div class="modal-header">
    <button type="button" class="close" data-dismiss="modal" aria-label="Close"><span aria-hidden="true">&times;</span></button>
    <h4 class="modal-title" style="font-weight:bold;">ADD NEW SUPPLIER</h4>
  </div>
  <div class="modal-body">
    <div id="supplier-new-results"></div>
    <div class="row" style="margin-left:-8px; margin-right:-8px;">
      <div class="col-md-6" style="padding-left:8px; padding-right:8px;">
        <div class="form-group">
          <label>Supplier Name</label>
          <input type="text" class="form-control" id="new_sup_name" data-vk-layout="alpha" style="height:52px; font-size:18px; font-weight:bold;" placeholder="Supplier name">
        </div>
      </div>
      <div class="col-md-6" style="padding-left:8px; padding-right:8px;">
        <div class="form-group">
          <label>Phone</label>
          <input type="text" class="form-control" id="new_sup_phone" data-vk-layout="numeric" style="height:52px; font-size:18px;" placeholder="Phone number">
        </div>
      </div>
      <div class="col-md-6" style="padding-left:8px; padding-right:8px;">
        <div class="form-group">
          <label>KRA PIN</label>
          <input type="text" class="form-control" id="new_sup_pin" data-vk-layout="alpha" style="height:52px; font-size:18px;" placeholder="KRA PIN">
        </div>
      </div>
      <div class="col-md-6" style="padding-left:8px; padding-right:8px;">
        <div class="form-group">
          <label>VAT</label>
          <input type="text" class="form-control" id="new_sup_vat" data-vk-layout="alpha" style="height:52px; font-size:18px;" placeholder="VAT No">
        </div>
      </div>
    </div>
    <div data-vk-host></div>
  </div>
  <div class="modal-footer">
    <button type="button" class="btn btn-outline-danger" data-dismiss="modal" style="font-size:20px; min-width:120px;">CLOSE</button>
    <button type="button" class="btn btn-outline-success" id="save-new-front-supplier" style="font-size:20px; min-width:140px;">SAVE SUPPLIER</button>
  </div>
</div>
<script type="text/javascript">
$('#load2').closest('.modal-dialog').css({'width':'900px','max-width':'96vw'});
if (window.KTouchKeyboard) {
  window.KTouchKeyboard.init($('#load2'), {
    inputSelector: '#new_sup_name, #new_sup_phone, #new_sup_pin, #new_sup_vat',
    hostSelector: '[data-vk-host]',
    title: 'Supplier Keyboard'
  });
}
$(document).off('click.frontNewSupplier').on('click.frontNewSupplier', '#save-new-front-supplier', function () {
  $.ajax({
    url: 'API/kug_api.php',
    type: 'POST',
    dataType: 'json',
    data: {
      action: 'create_supplier',
      sup_name: $('#new_sup_name').val(),
      sup_phone: $('#new_sup_phone').val(),
      sup_pin: $('#new_sup_pin').val(),
      sup_vat: $('#new_sup_vat').val()
    }
  })
  .done(function (res) {
    if (!res || !res.ok) {
      $('#supplier-new-results').html('<div class="alert alert-danger">' + ((res && res.message) ? res.message : 'Something went wrong, please try again.') + '</div>');
      return;
    }
    var supplierName = (res.data && res.data.supplier_name) ? res.data.supplier_name : '';
    var supplierId = (res.data && res.data.supplier_id) ? Number(res.data.supplier_id) : 0;
    var buyTax = (res.data && res.data.buy_tax) ? Number(res.data.buy_tax) : 0;

    if ($('#sup_id option[value="' + supplierId + '"]').length < 1) {
      $('#sup_id').append('<option value="' + supplierId + '" selected>' + supplierName + '</option>');
    }
    $('#sup_id').val(String(supplierId));
    $('#supplier-name').text(supplierName);
    $('#supplier-name-mobile').text(supplierName);
    $('#supplier-status').css({'background-color':'#fff6c7','color':'#6a4d00'});
    $('#tax-wrap, #tax-wrap-mobile').show();
    $('#buy-tax, #buy-tax-mobile').prop('checked', buyTax === 1);
    $('#btn_sub, #btn_sub_mobile').removeClass('btn-disabled-soft').prop('disabled', false);
    $('#supplier-new-results').html('<div class="alert alert-success">' + (res.message || 'Supplier added successfully.') + '</div>');
    $('#modal2').modal('hide');
  })
  .fail(function () {
    $('#supplier-new-results').html('<div class="alert alert-danger">Something went wrong, please try again.</div>');
  });
});
</script>
