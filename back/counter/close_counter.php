<?php
include("../../inc/connection.php");
include('../clude/funcs.php');
if(isset($_REQUEST['id'])){
  $id = $_REQUEST['id'];

  $name=strtoupper(get_counter($id));
}
?>
<div class="modal-content">
  <div id="results"></div>
  <div class="modal-header">
    <h4 class="modal-title"> <strong>CLOSE COUNTER</strong> <?php echo $name; ?></h4> 
  </div>
  <div class="modal-body"> 
      <div class="form-group">
        <label >Reason For Closing</label>
        <textarea name="reason" class="form-control" placeholder="Reason" id="reason"> </textarea>
      </div>  
  </div>
  <div class="modal-footer">
    <a href="javascript:;" class="btn btn-sm btn-danger" data-dismiss="modal">Close</a>
    <button type="button" name="close-count" id="close-count" class="btn btn-sm btn-warning">Continue</button>
  </div>
</div>

<script type="text/javascript">
document.getElementById('close-count').addEventListener('click', close_count);
  function close_count(){  
    var cntr_id = '<?php echo $id; ?>';
    var reason = $("#reason").val();  
    $.ajax({
      url: 're_adj_act.php',
      type: 'POST',
      data: {cntrid: cntr_id, reason: reason, ajax: 1},
      dataType: 'json'
    })
    .done(function(data){
        var message = (data && data.message) ? data.message : 'Counter close completed.';
        var cls = data && data.ok ? 'success' : 'danger';
        $('#results').html('<div class="alert alert-'+cls+'">'+message+'</div>');
        if (data && data.ok) {
          setTimeout(function(){ window.location.reload(); }, 900);
        }
    })
    .fail(function(){
      $('#results').html('<div class="alert alert-danger"><a href="#" class="close" data-dismiss="alert">&times;</a><strong>Warning!</strong> Error occured.</div>');
    });
  }
</script> 
