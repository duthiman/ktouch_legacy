<?php
session_start();
if(isset($_REQUEST['items'])){
  $items = $_REQUEST['items'];
  $action="LOADED|-|-|CATEGORY CHANGE MODAL FOR |";
  logs($action,"../../"); 

  $arr = explode(',', $items);
}
?>
<div class="modal-content">
  <div class="modal-header">
    <button type="button" class="close" data-dismiss="modal" aria-hidden="true">×</button>
    <h4 class="modal-title">CHANGE SUB CATEGORY <?php echo count($arr); ?> ITEMS</h4>
  </div>
		<div class="modal-body p-5">
			<div id="results"></div>
			<form id="set-form">
      <div class="form-group row">
        <div class="form-group col-md-12">
          <form id="set-form">
          <fieldset>
            <legend style="font-size:18px; color:purple;">Product Settings</legend>
            <div class="form-group row">
              <div class="form-group  col-md-6 col-sm-6">
                <label>Select Product Type</label>
                <select class="form-control" id="stock" name="stock" class="selection form-control" data-parsley-required="true">
                  <option  value="0">----Select a Type -----</option> 
                  <option  value="1">Inventory</option> 
                  <option  value="2">Non Inventory</option> 
                  <option  value="3">Service</option> 
                </select>
              </div> 
              <div class="form-group  col-md-6 col-sm-6 col-xs-6" id="div-menu">
                <label>Show In Menu</label>
                <div class="col-md-12">
                  <div class="radio radio-css radio-inline">
                    <input type="radio" name="menu" id="myes" value="1"/>
                    <label for="myes">Yes</label>
                  </div>
                  <div class="radio radio-css radio-inline">
                    <input type="radio" name="menu" id="mno" value="2" checked />
                    <label for="mno">No</label>
                  </div>
                </div>
              </div>   
              <div class="form-group  col-md-6 col-sm-6 col-xs-6" id="div-deci">
                <label>Has Fractions/Decimals</label>
                <div class="col-md-12">
                  <div class="radio radio-css radio-inline">
                    <input type="radio" name="point" id="yes" value="1"/>
                    <label for="yes">Yes</label>
                  </div>
                  <div class="radio radio-css radio-inline">
                    <input type="radio" name="point" id="no" value="2" checked/>
                    <label for="no">No</label>
                  </div>
                </div>
              </div>   

              <div class="form-group col-md-6">
                <label>Department</label><br>
                <select class="form-control form-control-xs col-12 pickers" id="dpt" name="dpt"  style="width: 100%;">
                    <option value="0" selected="selected">--Select a Department--</option>
                   <option value="1">BAR</option> 
                  <option value="2">KITCHEN</option> 
                </select>
              </div> 
              <div class="form-group col-md-6">
                <label>Sell in Butchery</label><br>
                <label class="checkbox-inline"><input type="checkbox" name="butchery_enabled" value="1"> Yes</label>
              </div>
              <div class="form-group  col-md-6 col-sm-6 col-xs-6" id="div-change-price" style="display: none;">
                <label>Item Changes Price</label>
                <div class="col-md-12">
                  <div class="radio radio-css radio-inline">
                    <input type="radio" name="change-price" id="cpyes" value="1"/>
                    <label for="cpyes">Yes</label>
                  </div>
                  <div class="radio radio-css radio-inline">
                    <input type="radio" name="change-price" id="cpno" value="2" checked/>
                    <label for="cpno">No</label>
                  </div>
                </div>
              </div>
            </div>
          </fieldset>
            <input type="hidden"  class="form-control" value="<?php echo $items; ?>" name="items" id="items">
        </form>
        </div> 
      </div>
      <div class="modal-footer p-5">
        <a href="javascript:;" class="btn btn-xs btn-danger" data-dismiss="modal">Close</a>
				<a  id="set-prods" class="btn btn-xs btn-purple">Save</a>
			</div>
		</form>
		</div>
	</div> 

<script type="text/javascript">                       
 
// Add your custom JS code here
$( document ).ready(function() { 
   $('.pickers').select2({
    dropdownParent: $('#modal')
  }); 
  document.getElementById('set-prods').addEventListener('click', set_act);
    function set_act(){ 
    var str = $( "#set-form" ).serialize();  
    $('#results').html('');
    $.ajax({
      url: menuApiUrl,
      type: 'POST',                           
      data: str + '&action=save_settings',
      dataType: 'json'
    })
    .done(function(res){
      if (res && res.ok) {
        var hulla = new hullabaloo();
        hulla.send(res.message || 'Category Successfully Updated', "success");
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
  });
                    </script>
					


		<!--modal add-->
		
