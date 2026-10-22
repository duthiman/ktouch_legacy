<?php
session_start();
include('../../inc/connection.php');
include('funcs.php');
$shift = get_column('kp_shift','shift',"1");
$cntr_id=$_REQUEST['id'];
?> 
<div class="modal-content">
  <div class="modal-header">
    <button type="button" class="close" data-dismiss="modal" aria-label="Close"><span aria-hidden="true">&times;</span></button>
    <h4 class="modal-title" style="color: red; font-weight: bold;"> VIEW <?PHP ECHO get_column('kp_counters','name',"cntr_id='$cntr_id'"); ?> SALES IN ERROR</h4>
  </div>
  <div class="modal-body"> 
    <div class="mb-3">
      <button type="button" class="btn btn-outline-info"  onclick="check(1,'<?php echo $cntr_id; ?>')">CHECK BY SALES</button>
      <button type="button" class="btn btn-outline-primary"   onclick="check(2,'<?php echo $cntr_id; ?>')">CHECK BY SOLD ITEMS</button>
    </div>
    <div id="tt"></div> 
    <div id="col-res" class="tabel-responsive" style="height: 600px; overflow-y: scroll;"></div>
      
  </div>
  <div class="modal-footer">
    <button type="button" class="btn btn-outline-danger" data-dismiss="modal">CLOSE MODAL</button>
  
  </div>
</div>
<script type="text/javascript"></script>
<script> 
    function check(st,id){  

      $.ajax({
        url: 'API/cash_api.php',
        type: 'POST',                           
        data: {action:'error_check',st:st,id:id},
        dataType: 'html'
      })
      .done(function(data){
        $('#col-res').html(data);  
      })
      .fail(function(){
        $('#col-res').html('<div class="alert alert-danger"><a href="#" class="close" data-dismiss="alert">&times;</a><strong>Warning!</strong> Error occured.</div>');
      });
    }  
  </script>
