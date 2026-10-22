<?php
$item_id = isset($item_id) ? (int)$item_id : (int)($_REQUEST['id'] ?? 0);
$item_name = isset($item_name) ? (string)$item_name : '';
$item_row = isset($item_row) && is_array($item_row) ? $item_row : [];
$stock_items = isset($stock_items) && is_array($stock_items) ? $stock_items : [];
$current_tot_item = isset($item_row['tot_item_id']) ? (int)$item_row['tot_item_id'] : 0;
$current_tot_num = isset($item_row['tot_num']) ? (float)$item_row['tot_num'] : 0;
$stock_take_type = isset($item_row['stock_take_type']) ? (int)$item_row['stock_take_type'] : 2;
$empty_bottle_weight = isset($item_row['empty_bottle_weight']) ? (float)$item_row['empty_bottle_weight'] : 0;
$full_bottle_weight = isset($item_row['full_bottle_weight']) ? (float)$item_row['full_bottle_weight'] : 0;
$bottle_mls = isset($item_row['bottle_mls']) ? (float)$item_row['bottle_mls'] : 0;
$tot_size = isset($item_row['tot_size']) && (float)$item_row['tot_size'] > 0 ? (float)$item_row['tot_size'] : 30;
?>
<div class="modal-content" style="width:400px;">
	<div class="modal-header"> 
		<h4 class="modal-title">SET STOCK TAKE FOR <?php echo htmlspecialchars($item_name, ENT_QUOTES, 'UTF-8'); ?></h4>
	</div>
	<div class="modal-body"> 
    <div id="results"></div>
    <form class="form-horizontal" id="form">
      <div class="form-group row"> 
        <input class="form-control" type="hidden" id="item_id" name="item_id" value="<?php echo $item_id; ?>"/>
        <div class="form-group col-md-6 col-sm-6"> 
          <label>Quantity / Units Item</label><br>
          <select class="form-control pickers" id="tot_bot" name="tot_bot" style="width:100%">
            <option value="0">----SELECT A UNITS ITEM FROM THIS PRODUCT-----</option>
            <?php
              foreach ($stock_items as $stock_item) {
                $name = isset($stock_item['name']) ? $stock_item['name'] : '';
                $item_id2 = isset($stock_item['id']) ? (int)$stock_item['id'] : 0;
              ?>
              <option value="<?php echo $item_id2; ?>" <?php echo $current_tot_item === (int)$item_id2 ? 'selected' : ''; ?>><?php echo $name; ?></option>
            <?php
              }
              ?>
          </select>
          <small style="display:block; margin-top:4px; color:#7e3a20; font-weight:700;">This is the unit product used for this item</small>
        </div>
        <div class="form-group col-md-6 col-sm-6" id="div-tot-num">
          <label>Number of Units Per Bottle</label>
          <input class="form-control" type="text" id="tot_num" name="tot_num" value="<?php echo $current_tot_num > 0 ? rtrim(rtrim(number_format($current_tot_num, 2, '.', ''), '0'), '.') : '0'; ?>" placeholder="Auto calculated" readonly />
          <small style="display:block; margin-top:4px; color:#7e3a20; font-weight:700;">Calculated from MLs divided by unit size</small>
        </div>
        <div class="form-group col-md-6 col-sm-6">
          <label>Stock Take Type</label>
          <div style="display:flex; gap:14px; align-items:center; margin-top:6px;">
            <label style="margin:0; font-weight:700;">
              <input type="radio" name="stock_take_type" value="2" <?php echo $stock_take_type !== 1 ? 'checked' : ''; ?> />
              Units
            </label>
            <label style="margin:0; font-weight:700;">
              <input type="radio" name="stock_take_type" value="1" <?php echo $stock_take_type === 1 ? 'checked' : ''; ?> />
              Weight
            </label>
          </div>
        </div>
        <div class="form-group col-md-6 col-sm-6">
          <label>Units Size MLs</label>
          <input class="form-control weight-driver" type="text" id="tot_size" name="tot_size" value="<?php echo rtrim(rtrim(number_format($tot_size, 2, '.', ''), '0'), '.'); ?>" placeholder="30" />
        </div>
        <div id="weight_take_fields" style="<?php echo $stock_take_type === 1 ? '' : 'display:none;'; ?> width:100%; display:<?php echo $stock_take_type === 1 ? 'block' : 'none'; ?>;">
          <div class="form-group col-md-6 col-sm-6">
            <label>Empty Bottle Weight</label>
            <input class="form-control weight-driver" type="text" id="empty_bottle_weight" name="empty_bottle_weight" value="<?php echo $empty_bottle_weight > 0 ? rtrim(rtrim(number_format($empty_bottle_weight, 2, '.', ''), '0'), '.') : ''; ?>" placeholder="Required for weight stock take" />
          </div>
          <div class="form-group col-md-6 col-sm-6">
            <label>Full Bottle Weight</label>
            <input class="form-control weight-driver" type="text" id="full_bottle_weight" name="full_bottle_weight" value="<?php echo $full_bottle_weight > 0 ? rtrim(rtrim(number_format($full_bottle_weight, 2, '.', ''), '0'), '.') : ''; ?>" placeholder="Required for weight stock take" />
          </div>
          <div class="form-group col-md-6 col-sm-6">
            <label>Bottle MLs</label>
            <input class="form-control weight-driver" type="text" id="bottle_mls" name="bottle_mls" value="<?php echo $bottle_mls > 0 ? rtrim(rtrim(number_format($bottle_mls, 2, '.', ''), '0'), '.') : ''; ?>" placeholder="Required for weight stock take" />
          </div>
          <div class="form-group col-md-6 col-sm-6">
            <label>Weight Per Unit</label>
            <input class="form-control" type="text" id="unit_weight" value="" readonly />
          </div>
          <div class="form-group col-md-6 col-sm-6">
            <label>Measured Bottle Weight</label>
            <input class="form-control weight-driver" type="text" id="measured_bottle_weight" value="" placeholder="Enter bottle weight to calculate units" />
            <small style="display:block; margin-top:4px; color:#7e3a20; font-weight:700;">We will auto-calculate units from the measured bottle weight</small>
          </div>
          <div class="form-group col-md-6 col-sm-6">
            <label>Available Units</label>
            <input class="form-control" type="text" id="tot_available" value="<?php echo $current_tot_num > 0 ? rtrim(rtrim(number_format($current_tot_num, 2, '.', ''), '0'), '.') : '0'; ?>" readonly />
          </div>
        </div>
      </div>  
    </form>
  </div>
  <div class="modal-footer">
    <a data-id="<?php echo $item_id; ?>" name="clear_prod" id="clear_prod" class="btn btn-xs btn-purple" >Clear Tot Settings</a>
    <a href="javascript:;" class="btn btn-xs btn-danger" data-dismiss="modal">Close</a>
    <button type="button" name="new_prod" id="new_prod" class="btn btn-xs btn-success"  >Save</button>
  </div>
</div>
</div>
<script> 
  document.getElementById('new_prod').addEventListener('click', tots);
  function toggleWeightFields() {
    var type = $('input[name="stock_take_type"]:checked').val();
    if (type === '1') {
      $('#weight_take_fields').show();
    } else {
      $('#weight_take_fields').hide();
    }
  }
  function updateTotCount() {
    var emptyWeight = parseFloat($('#empty_bottle_weight').val());
    var fullWeight = parseFloat($('#full_bottle_weight').val());
    var measuredWeight = parseFloat($('#measured_bottle_weight').val());
    var bottleMls = parseFloat($('#bottle_mls').val());
    var totSize = parseFloat($('#tot_size').val());
    if (!isNaN(emptyWeight) && !isNaN(fullWeight) && fullWeight > emptyWeight && !isNaN(bottleMls) && bottleMls > 0 && !isNaN(totSize) && totSize > 0) {
      var unitWeight = (fullWeight - emptyWeight) / (bottleMls / totSize);
      $('#unit_weight').val((Math.round(unitWeight * 100) / 100).toString().replace(/\.0+$/, '').replace(/(\.\d*[1-9])0+$/, '$1'));
    } else {
      $('#unit_weight').val('');
    }
    if (!isNaN(measuredWeight) && measuredWeight > 0 && !isNaN(emptyWeight) && emptyWeight >= 0 && !isNaN(fullWeight) && fullWeight > emptyWeight && !isNaN(bottleMls) && bottleMls > 0 && !isNaN(totSize) && totSize > 0) {
      var unitsPerBottle = bottleMls / totSize;
      var unitWeightCalc = (fullWeight - emptyWeight) / unitsPerBottle;
      var availableUnits = (measuredWeight - emptyWeight) / unitWeightCalc;
      var formattedAvailable = (Math.round(availableUnits * 100) / 100).toString().replace(/\.0+$/, '').replace(/(\.\d*[1-9])0+$/, '$1');
      $('#tot_available').val(formattedAvailable);
    } else if (!isNaN(bottleMls) && bottleMls > 0 && !isNaN(totSize) && totSize > 0) {
      var totNum = bottleMls / totSize;
      var formatted = (Math.round(totNum * 100) / 100).toString().replace(/\.0+$/, '').replace(/(\.\d*[1-9])0+$/, '$1');
      $('#tot_num').val(formatted);
      $('#tot_available').val(formatted);
    } else {
      $('#tot_num').val('0');
      $('#tot_available').val('0');
    }
  }
  function tots(){  
    var str = $( "#form" ).serialize();   
    $.ajax({
      url: menuApiUrl,
      type: 'POST',                           
      data: str + '&action=save_tots',
      dataType: 'json'
    })
    .done(function(res){
      if (res && res.ok) {
        var hulla = new hullabaloo();
        hulla.send(res.message || 'Units Setup Successfully Saved', "success");
        $('#modal').modal('toggle');
        if ($.fn.DataTable.isDataTable('#data-table-combine')) {
          $('#data-table-combine').DataTable().ajax.reload(null, false);
        }
        return;
      }
      $('#results').html('<div class="alert alert-danger"><a href="#" class="close" data-dismiss="alert">&times;</a><strong>Warning!</strong> ' + ((res && res.message) ? res.message : 'Error occured.') + '</div>');
    })
    .fail(function(){
      $('#results').html('<div class="alert alert-danger"><a href="#" class="close" data-dismiss="alert">&times;</a><strong>Warning!</strong> Error occured.</div>');
    });
  }
  $( document ).ready(function() {  
    $('.pickers').select2({ dropdownParent: $('#modal')});
    toggleWeightFields();
    updateTotCount();
    $(document).on('change', 'input[name="stock_take_type"]', toggleWeightFields);
    $(document).on('input change', '.weight-driver', updateTotCount);
    $(document).on('click','#clear_prod', function(e) { 
      var uid = $(this).data('id'); 
      $.ajax({
        url: menuApiUrl,
        type: 'GET',
        data: { action: 'modal', view: 'clear_confirm', id: uid, act: 'tot' },
        dataType: 'json'
      })
      .done(function(res){ 
        $('#load').html(''); 
        $('#load').html((res && res.data && res.data.html) ? res.data.html : '');
      })
      .fail(function(){
        $('#load').html('<i class="fa  fa-info-sign"></i> Something went wrong, Please try again...'); 
      });
    });
  });  
    
</script>
