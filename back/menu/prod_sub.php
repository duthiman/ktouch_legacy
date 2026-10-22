<?php
session_start();
if(isset($_REQUEST['items'])){
$items = $_REQUEST['items'];
  $action="LOADED|-|-|CATEGORY CHANGE MODAL FOR |";
  logs($action,"../../"); 

  $arr = explode(',', $items);
}
$current_cat_id = isset($current_cat_id) ? (int)$current_cat_id : 0;
?>
<div class="modal-content">
  <div class="modal-header">
    <button type="button" class="close" data-dismiss="modal" aria-hidden="true">×</button>
    <h4 class="modal-title">CHANGE SUB CATEGORY <?php echo count($arr); ?> ITEMS</h4>
  </div>
		<div class="modal-body p-5">
			<div id="results"></div>
			<form id="sub-form">
      <div class="form-group row">
        <div class="form-group col-md-12">
          <select type="text" class="form-control pickers" id="sub_id" name="sub_id" style="width:100%;"></select> 
          <input type="hidden"  class="form-control" value="<?php echo $items; ?>" name="items" id="items">
        </div> 
      </div>
      <div class="modal-footer p-5">
        <a href="javascript:;" class="btn btn-xs btn-danger" data-dismiss="modal">Close</a>
				<a  id="edit-prods" class="btn btn-xs btn-purple">Save</a>
			</div>
		</form>
		</div>
	</div> 

<script type="text/javascript">                       
 
// Add your custom JS code here
$( document ).ready(function() { 
   fillSubCategoryOptions('#sub_id', <?php echo $current_cat_id; ?>, 0);
   $('.pickers').select2({
    dropdownParent: $('#modal')
  }); 
  $('#cat_id').on('change', function() {
    fillSubCategoryOptions('#sub_id', $(this).val(), 0);
  });
  document.getElementById('edit-prods').addEventListener('click', sub_act);
    function sub_act(){ 
    var str = $( "#sub-form" ).serialize(); 
    $('#results').html('');
    $.ajax({
      url: menuApiUrl,
      type: 'POST',                           
      data: str + '&action=save_sub_category',
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
		
