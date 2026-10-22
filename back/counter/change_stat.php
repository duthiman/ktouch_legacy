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
    <h4 class="modal-title"> <strong>CHANGE TAKE STATUS FOR  <?php echo $name; ?>  COUNTER</strong></h4> 
  </div>
  <div class="modal-body"> 
    
      <div class="form-group">
        <label >Current Cashier: <b><?php echo get_column('kp_counters','user_id',"cntr_id='$id'");//($id); ?></b></label> 
      </div>  
       <div class="form-group"> 
           <select type="text" class="selectpicker form-control"  id="stat" name="stat"> 
              <option>----SELECT STATUS----</option> 
              <option  value="1">FINISHED</option>
              <option  value="2">NOT STARTED</option>
              <option  value="3">CONTINUING</option>
                </select>
      </div>   
  </div>
  <div class="modal-footer">
    <a href="javascript:;" class="btn btn-sm btn-danger" data-dismiss="modal">Close</a>
    <button type="button" name="assign_cash" id="assign_cash" class="btn btn-sm btn-warning">Assign</button>
  </div>
</div>

<script type="text/javascript">
document.getElementById('assign_cash').addEventListener('click', assign_cash);
  function assign_cash(){  
    var cntr_id = '<?php echo $id; ?>';
    //var reason = $("#reason").val(); 
    var stat = $("#stat").val(); 
    $.ajax({
      url: 'change_stat_act.php',
      typr: 'POST',                           
      data: 'counter='+cntr_id+'&stat='+stat,
      dataType: 'html'
    })
    .done(function(data){
        $('#results').html(''); // blank before load.
        $('#results').html(data);
    })
    .fail(function(){
      $('#results').html('<div class="alert alert-danger"><a href="#" class="close" data-dismiss="alert">&times;</a><strong>Warning!</strong> Error occured.</div>');
    });
  }
</script> 