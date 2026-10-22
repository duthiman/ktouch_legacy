<?php 
session_start(); 
include("../../inc/connection.php");
include("funcs.php"); 
$cust_id = $_REQUEST['cust'];
$sale_id = $_REQUEST['id'];
 
?> 
      <div class="modal-content">
        <div class="modal-header">
          <button type="button" class="close" id="btnCancel" aria-hidden="true">×</button>
          <h4 class="modal-title" id="upload-avatar-title">ASSIGN BILL NO <?PHP echo $sale_id; ?> TO <?php echo get_column('kp_customers','name',"cust_id='$cust_id'"); ?></h4>
        </div>
        <div class="modal-body" id="rest"> 
        <div class="alert alert-danger">
                        <p>WARNING!! This action is irreversible and will affect customers account balance..!!!!So be sure you have the correct customer.</p>
                      </div> 
				</div>
				<div class="modal-footer">
					<a  class="btn btn-danger btn-xs" id="btnClose" data-dismiss="modal" >Close</a>
					<a  id="assign" class="btn btn-sm btn-success btn-xs">ASSIGN</a>
				</div>  
      </div> 
<script type="text/javascript"> 
 
	$(document).on('click','#assign', function(e){
	 	var id=<?php echo $sale_id; ?>;  
	 	var cust=<?php echo $cust_id; ?>;
	 	if (cust==0 || id==0) {
 $('#rest').html('<div class="alert alert-danger"><a href="#" class="close" data-dismiss="alert">&times;</a><strong>Warning!</strong> Error occured. IDS can be zero</div>');
	 	} else {
		  $.ajax({
		    url: 'addbill_act.php',
		    type: 'POST',                           
		    data: {cust:cust,id:id},
		    dataType: 'html'
		  })
		  .done(function(data){
		    $('#rest').html(data); 
		  })
		  .fail(function(){
		    $('#rest').html('<div class="alert alert-danger"><a href="#" class="close" data-dismiss="alert">&times;</a><strong>Warning!</strong> Error occured.</div>');
		  });
		}
	});
</script>