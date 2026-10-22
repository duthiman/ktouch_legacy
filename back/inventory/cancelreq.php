<?php
include("../../inc/connection.php");
include("../../inc/gen_funcs.php");
if(isset($_REQUEST['id'])){
  $id = $_REQUEST['id'];
  $counter=get_column('kp_counter_req','counter',"req_id='$id'");
  ?>
  <div class="modal-content">
    <div id="results"></div>
    <div class="modal-header">
      <button type="button" class="close" data-dismiss="modal" aria-label="Close"><span aria-hidden="true">&times;</span></button>
      <h4 class="modal-title">REQUEST CANCELLATION</h4>
    </div>
    <div class="modal-body">
      <div class="container-fluid">
        <div class="row">
              <div class="alert alert-danger">
                <h4>WARNING!! This action is irreversible and will delete currently requested items.</h4>
                <h3>YOU ARE ABOUT TO CANCEL REQUEST NUMBER <?PHP ECHO $id; ?> FROM <?php echo get_column('kp_counters','name',"cntr_id='$counter'"); ?></h3>
              </div>
        </div>
        <div class="form-group">
        <label >Reason</label>
        <textarea name="reason" class="form-control" placeholder="Reason" id="reason"> </textarea>
      </div>  
      </div>
    </div>
    <div class="modal-footer">
      <button type="button" class="btn btn-outline-danger btn-lg" data-dismiss="modal">CLOSE</button>
      <a class="btn btn-outline-success btn-lg" id="cancelact">CANCEL REQUEST</a>
    </div>
  </div>
  <script type="text/javascript">
document.getElementById('cancelact').addEventListener('click', canc);
function canc(){
  var reason = $("#reason").val(); 
  $.ajax({
    url: 'API/inventory_api.php',
    type: 'POST',
    dataType: 'json',
    data: {
      action: 'cancel_request',
      req: <?php echo $id; ?>,
      reason: reason
    }
  })
  .done(function(res){
    var alertClass = res && res.ok ? 'success' : 'warning';
    var message = res && res.message ? res.message : 'Could not cancel request.';
    $('#results').html('<div class="alert alert-' + alertClass + '"><strong>' + message + '</strong></div>');
    if (res && res.ok && res.data && res.data.redirect) {
      window.setTimeout(function() {
        window.location.href = res.data.redirect;
      }, 250);
    }
  })
  .fail(function(){
    $('#results').html('<div class="alert alert-danger"><a href="#" class="close" data-dismiss="alert">&times;</a><strong>Warning!</strong> Error occured.</div>');
  });
}
</script> 
  <?php 
}
?>
