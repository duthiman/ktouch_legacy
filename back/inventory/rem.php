<?php
include("../../inc/connection.php");
if(isset($_REQUEST['item'])){
  $id = $_REQUEST['item'];
  $req_id = $_REQUEST['id'];
  $item_details = mysqli_query($connection,"SELECT * FROM kp_items WHERE item_id='$id'")or die(mysqli_error($connection));
  $itd = mysqli_fetch_assoc($item_details);
  $qty = $itd['store_reorder'];
  $name=strtoupper($itd['name']);
}
?>
<div class="modal-content">
  <div id="results"></div>
  <div class="modal-header">
    <h4 class="modal-title"> <strong>REMOVE </strong> <?php echo $name; ?> FROM REQUEST NO. <?PHP ECHO $req_id; ?> </h4> 
  </div>
  <div class="modal-body">
      <div class="form-group">
        <label >Reason</label>
        <textarea name="reason" class="form-control" placeholder="Reason" id="reason"> </textarea>
      </div>  
  </div>
  <div class="modal-footer">
    <button type="button" class="btn btn-lg btn-outline-danger" data-dismiss="modal">CLOSE</button>
    <button type="button" name="remitem" id="remitem" class="btn-lg btn-outline-success">REMOVE ITEM </button>
  </div>
</div>

<script type="text/javascript">
document.getElementById('remitem').addEventListener('click', adjust);
function adjust(){
  var reason = $("#reason").val(); 
  $.ajax({
    url: 'API/inventory_api.php',
    type: 'POST',
    dataType: 'json',
    data: {
      action: 'remove_request_item',
      req: <?php echo $req_id; ?>,
      item_id: <?php echo $id; ?>,
      reason: reason
    }
  })
  .done(function(res){
    var alertClass = res && res.ok ? 'success' : 'warning';
    var message = res && res.message ? res.message : 'Could not remove the request item.';
    $('#results').html('<div class="alert alert-' + alertClass + '"><strong>' + message + '</strong></div>');
    if (res && res.ok && res.data && res.data.reload) {
      window.setTimeout(function() {
        window.location.reload();
      }, 250);
    }
  })
  .fail(function(){
    $('#results').html('<div class="alert alert-danger"><a href="#" class="close" data-dismiss="alert">&times;</a><strong>Warning!</strong> Error occured.</div>');
  });
}
</script> 
