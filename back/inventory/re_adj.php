<?php
include("../../inc/connection.php");
if(isset($_REQUEST['id'])){
  $id = $_REQUEST['id'];
  $item_details = mysqli_query($connection,"SELECT * FROM kp_items WHERE item_id='$id'")or die(mysqli_error($connection));
  $itd = mysqli_fetch_assoc($item_details);
  $qty = $itd['store_reorder'];
  $name=strtoupper($itd['name']);
}
?>
<div class="modal-content">
  <div id="results"></div>
  <div class="modal-header">
    <h4 class="modal-title"> <strong> ADJUST REORDER LEVEL FOR  </strong> <?php echo $name; ?></h4> 
  </div>
  <div class="modal-body">
      <div class="form-group">
          <label >Reorder Level</label>
          <input type="text" class="form-control" name="new-qty" id="new-qty" value="<?php echo $qty; ?>"  />
      </div>
      <div class="form-group">
        <label >Reason</label>
        <textarea name="reason" class="form-control" placeholder="Reason" id="reason"> </textarea>
      </div>  
  </div>
  <div class="modal-footer">
    <a href="javascript:;" class="btn btn-sm btn-danger" data-dismiss="modal">Close</a>
    <button type="button" name="adj_qty" id="adj_qty" class="btn btn-sm btn-success">Save</button>
  </div>
</div>

<script type="text/javascript">
document.getElementById('adj_qty').addEventListener('click', adjust);
  function adjust(){
    var init = '<?php echo $qty; ?>';
    var qty = $("#new-qty").val(); 
    var item_id = '<?php echo $id; ?>';
    var reason = $("#reason").val(); 
    $.ajax(
        {
          url: 'API/inventory_api.php',
          type: 'POST',
          dataType: 'json',
          data: {
            action: 'adjust_reorder_qty',
            init: init,
            qty: qty,
            item_id: item_id,
            reason: reason
          }
        })
        .done(function(res)
         {
            var alertClass = res && res.ok ? 'success' : 'warning';
            var message = res && res.message ? res.message : 'Could not adjust reorder level.';
            $('#results').html('<div class="alert alert-' + alertClass + '"><strong>' + message + '</strong></div>');
            if (res && res.ok && res.data && res.data.reload) {
              window.setTimeout(function() {
                window.location.reload();
              }, 250);
            }
        })
        .fail(function()
        {
            $('#results').html('<div class="alert alert-danger"><a href="#" class="close" data-dismiss="alert">&times;</a><strong>Warning!</strong> Error occured.</div>');
            
        });
  }
</script> 
