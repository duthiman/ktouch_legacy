<?php
session_start();
if(isset($_REQUEST['items'])){
  $items = $_REQUEST['items'];
  // $get_item_details = mysqli_query($connection,"SELECT * FROM kp_items WHERE item_id='$id'") or die("Could not get the product details");
  // $row = mysqli_fetch_assoc($get_item_details);
  // $item_id = $row['item_id'];
  // $cat_id = $row['cat_id'];
  $action="LOADED|-|-|CATEGORY CHANGE MODAL FOR |";
  logs($action,"../../logs/"); 

  $arr = explode(',', $items);
}
?>
<div class="modal-content">
  <div class="modal-header">
    <button type="button" class="close" data-dismiss="modal" aria-hidden="true">×</button>
    <h4 class="modal-title">CHANGE CATEGORY FOR 
      <?php echo count($arr); ?> ITEMS</h4>
  </div>
		<div class="modal-body">
			<div id="results"></div>
			<form  id="cat-form">
      <div class="form-group row">
        <div class="form-group col-md-12">
          <select type="text" class="form-control pickers col-md-12" id="cat_id" name="cat_id" style="width:100%;"></select> 
          <input type="hidden"  class="form-control" value="<?php echo $items; ?>" name="items" id="items">
        </div> 
      </div>
      <div class="modal-footer p-4">
        <a  class="btn btn-xs btn-danger" data-dismiss="modal">Close</a>
				<a  id="edit-prods" class="btn btn-xs btn-success">Save</a>
			</div>
		</form>
		</div>
	</div> 

<script type="text/javascript">
// Add your custom JS code here
$( document ).ready(function(){
  fillSelectOptions('#cat_id', menuBootstrapCache.categories, '---SELECT CATEGORY----', 'id', function(category) {
    return category.name;
  });
   $('.pickers').select2({
    dropdownParent: $('#modal')
  }); 
  document.getElementById('edit-prods').addEventListener('click', cat_act);
  function cat_act(){ 
    var str = $( "#cat-form" ).serialize(); 
    $('#results').html('');
    $.ajax({
      url: menuApiUrl,
      type: 'POST',                           
      data: str + '&action=save_category',
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
		
