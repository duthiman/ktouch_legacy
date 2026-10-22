<?php
include("../../inc/connection.php");
include("../clude/funcs.php");
$id = $_REQUEST['id'];
$item_id = $_REQUEST['item'];

$get_qty = mysqli_query($connection,"SELECT ware_qty,port_status,portions FROM kp_items WHERE item_id='$item_id'") or die("Could not get the stock");
$row = mysqli_fetch_assoc($get_qty);
$store_qty = $row['ware_qty'];
$port_status = $row['port_status'];
$portions = $row['portions'];

$get_po_details = mysqli_query($connection,"SELECT * FROM kp_req_items WHERE req_id='$id' AND item_id='$item_id'") or die("Could not get the purchase details ");
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
      <button type="button" class="btn btn-outline-danger btn-sm" data-dismiss="modal">CLOSE MODAL</button> 
      <button id="btn-confirm" class="btn btn-outline-success btn-sm pull-right">RECEIVE ITEM</button>
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
      url: 'recact.php',
      typr: 'POST',
      data: 'req_id='+req_id+'&item='+item+'&qty='+qty+'&portions='+portions,
      dataType: 'html'
    })
    .done(function(data){  
      $('#results').html(data);
      // $('#rec-modal').modal('toggle'); 
    })
    .fail(function(){
      $('#results').html('<i class="fa  fa-info-sign"></i> Something went wrong, Please try again...');
    });
  });
});
</script>