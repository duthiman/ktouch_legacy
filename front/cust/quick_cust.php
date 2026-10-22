      <div class="modal-content"  >
        <div class="modal-header">
          <button type="button" class="close" id="btnCancel" aria-hidden="true">×</button>
          <h4 class="modal-title" id="upload-avatar-title">CREATE NEW CUSTOMER ACCOUNT</h4>
        </div>
        <form action="#" id="cust_form" method="POST" data-parsley-validate="true">
            	<div class="modal-body">
            		<div class="" id="cust_results"></div>
            		<div class="form-group row block" id="autocomplete" > 
	            		<div class="form-group col-md-12">
							<label class="col-form-label">Name* :</label> 
							<input class="form-control" type="text" id="cust_name" name="name"  placeholder="Enter Name..."  data-parsley-required="true" /> 
							</div>
						<div class="form-group col-md-12">
							<label class="col-form-label">Phone Number* :</label> 
							<input class="form-control" type="text" id="cust_phone" name="phone"  placeholder="Enter Phone..."  data-parsley-required="true" /> 
						</div> 
					</div>  
				</div>
				<div class="modal-footer">
					<a  class="btn btn-danger btn-xs" id="btnClose" data-dismiss="modal" >Close</a>
					<a  id="cust-save" class="btn btn-sm btn-success btn-xs">Save</a>
				</div> 
			</form>
      </div> 
<script type="text/javascript"> 

$('#cust_name').keyboard({ layout: 'qwerty' });  
$('#cust_phone').keyboard({ layout: 'num' });
	$(document).on('click','#cust-save', function(e){ 
   	var str = $( "#cust_form" ).serialize();  
	  $.ajax({
	    url: 'quick_cust_act.php',
	    type: 'POST',                           
	    data: str,
	    dataType: 'html'
	  })
	  .done(function(data){
	    $('#cust_results').html(data);  
	  })
	  .fail(function(){
	    $('#cust-results').html('<div class="alert alert-danger"><a href="#" class="close" data-dismiss="alert">&times;</a><strong>Warning!</strong> Error occured.</div>');
	  });
	});
</script>