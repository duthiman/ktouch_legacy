<?php
$item_id = isset($item_id) ? (int)$item_id : (int)($_REQUEST['id'] ?? 0);
$item_name = isset($item_name) ? (string)$item_name : '';
$act = isset($act) ? (string)$act : (string)($_REQUEST['act'] ?? '');
$function = $act === 'tot' ? 'tot()' : 'port()';
?>
<div class="modal-content">
	<div class="modal-header">
		<button type="button" class="close" data-dismiss="modal" aria-hidden="true">×</button>
		<h4 class="modal-title">AUTHORISE <?php echo htmlspecialchars($act, ENT_QUOTES, 'UTF-8'); ?> CLEAR FOR <?php echo htmlspecialchars($item_name, ENT_QUOTES, 'UTF-8'); ?></h4>
	</div>
	<div class="modal-body">
  <div id="modal-results"></div> 
    <p style="color:red;">Remember this clears this item from generating tots or portions</p> 
  </div>
    <div class="modal-footer">
      <a href="javascript:;" class="btn btn-sm btn-danger" data-dismiss="modal">Close</a>
      <button type="button" id="btn-cancel" class="btn btn-sm btn-success" onclick="<?php echo $function; ?>;">Submit</button>
    </div>
  </div> 
  <script type="text/javascript">
function tot(){   
  var item_id = '<?php echo $item_id; ?>';
  $.ajax({
    url: menuApiUrl,
    type: 'POST',
    data: { action: 'clear_tots', item_id: item_id },
    dataType: 'json'
  })
  .done(function(res){ 
    if (res && res.ok) {
      var hulla = new hullabaloo();
      hulla.send(res.message || 'Units Setup Successfully Cleared', "success");
      $('#modal').modal('toggle');
      if ($.fn.DataTable.isDataTable('#data-table-combine')) {
        $('#data-table-combine').DataTable().ajax.reload(null, false);
      }
      return;
    }
    $('#modal-results').html('<div class="alert alert-danger"><a href="#" class="close" data-dismiss="alert">&times;</a><strong>Warning!</strong> ' + ((res && res.message) ? res.message : 'Error occured.') + '</div>');
  })
  .fail(function(){
    $('#modal_results').html('<div class="alert alert-danger"><a href="#" class="close" data-dismiss="alert">&times;</a><strong>Warning!</strong> Error occured.</div>');
  });
}
function port(){   
  var item_id = '<?php echo $item_id; ?>';
  $.ajax({
    url: menuApiUrl,
    type: 'POST',
    data: { action: 'clear_portion', item_id: item_id },
    dataType: 'json'
  })
  .done(function(res){ 
    if (res && res.ok) {
      var hulla = new hullabaloo();
      hulla.send(res.message || 'Tots Successfully Cleared', "success");
      $('#modal').modal('toggle');
      if ($.fn.DataTable.isDataTable('#data-table-combine')) {
        $('#data-table-combine').DataTable().ajax.reload(null, false);
      }
      return;
    }
    $('#modal-results').html('<div class="alert alert-danger"><a href="#" class="close" data-dismiss="alert">&times;</a><strong>Warning!</strong> ' + ((res && res.message) ? res.message : 'Error occured.') + '</div>');
  })
  .fail(function(){
    $('#modal_results').html('<div class="alert alert-danger"><a href="#" class="close" data-dismiss="alert">&times;</a><strong>Warning!</strong> Error occured.</div>');
  });
}
  </script>
