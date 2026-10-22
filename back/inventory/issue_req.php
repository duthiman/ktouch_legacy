<?php
include("../../inc/connection.php");
include("../clude/funcs.php");
$id = $_REQUEST['id'];
$item_id = $_REQUEST['item'];

$get_qty = mysqli_query($connection,"SELECT store_qty,port_status,portions FROM kp_items WHERE item_id='$item_id'") or die("Could not get the stock");
$row = mysqli_fetch_assoc($get_qty);
$store_qty = $row['store_qty'];
$port_status = $row['port_status'];
$portions = $row['portions'];

$get_po_details = mysqli_query($connection,"SELECT * FROM kp_counter_req_items WHERE req_id='$id' AND item_id='$item_id'") or die("Could not get the purchase details ");
$row = mysqli_fetch_assoc($get_po_details);
$qty = $row['req_qty']; 


?>
<div class="modal-content">
  <div id="result"></div>
  <div class="modal-header">
    <button type="button" class="close" data-dismiss="modal" aria-hidden="true">×</button>
      <h4 class="modal-title"> <i class="fa fa-stats"></i> &nbsp;&nbsp;ISSUE <?php echo get_item($item_id); ?>
      </h4>
    </div>  
    <div class="modal-body">
      <div id="results"></div> 
      <label  style="color:green;">CURRENT UNPORTIONED STORE STOCK: <?PHP echo $store_qty; ?></label>  
      <div class="form-group">
        <label>Quantity</label>
        <input class="form-control" type="text" id="qty"  name="qty" value="<?php echo $qty; ?>" /> 
      </div> 
      <?php
      if ($port_status) {
       ?>
      <div class="form-group">
        <label>Portins</label>
        <input class="form-control" type="text" id="portions"  name="portions" value="<?php echo $portions; ?>"  disabled="true"/> 
        <p style="margin: 0px; color:blue;">This item is portioned, By issuing 1 item it will be received as  <?php echo $portions; ?> </p>

      </div> 
      <?php 
    }
    ?>
    </div>
    <div class="modal-footer">
      <button type="button" class="btn btn-danger btn-xs" data-dismiss="modal">Close</button> 
      <button id="btn-confirm" class="btn btn-purple btn-xs pull-right">Confirm</button>
    </div>
  </div>   
   <!--  </form>  -->
<script type="text/javascript"> 
$(document).ready(function() {
    $(document).on('click','#btn-confirm', function(e){
      $('#btn-confirm').text('');
      $('#btn-confirm').append('<span> Processing... </span>');
      $('#btn-confirm').attr('disabled',true);
    e.preventDefault();
    var req_id='<?php echo $id; ?>';
    var item='<?php echo $item_id; ?>';  
    var qty=$("#qty").val();
    var st='<?php echo $port_status; ?>'; 
    if (st==1) {
      var portions= qty* $("#portions").val(); 
    } else {
      var portions=""; 
    }  
    $('#results').html(''); // leave this diff blank 
    $.ajax({
      url: 'API/inventory_api.php',
      type: 'POST',
      dataType: 'json',
      data: {
        action: 'issue_request_item',
        req_id: req_id,
        item: item,
        qty: qty,
        portions: portions
      }
  })
    .done(function(res){  
      var alertClass = res && res.ok ? 'success' : 'danger';
      var message = res && res.message ? res.message : 'Could not issue this request item.';
      $('#results').html('<div class="alert alert-' + alertClass + '"><strong>' + message + '</strong></div>');
      if (res && res.ok && res.data && res.data.reload) {
        window.setTimeout(function() {
          window.location.reload();
        }, 200);
      }
  })
    .fail(function(){
      $('#results').html('<i class="fa  fa-info-sign"></i> Something went wrong, Please try again...');
    });
  });
});
</script>
