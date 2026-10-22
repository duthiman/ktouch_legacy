<?php 
session_start();
include('../../inc/connection.php');
include('../clude/funcs.php');
$user_id = $_SESSION['user_id'];  
$action="LOADED NEW QUOTE MODAL |";
$url="../../";
logs($action,$url);
?>
<div class="modal-content">
	<div class="modal-header">
		<button type="button" class="close" data-dismiss="modal" aria-label="Close"><span aria-hidden="true">&times;</span></button>
        <h4 class="modal-title">CONFIRM CREATE NEW REQUEST</h4>
    </div>
    <div class="modal-body">
        <div id="reqresult"></div>
    	<div class="container-fluid">
            <div class="row">
                <div class="alert alert-warning">
                    <p>WARNING!! Are you sure you want to raise a new request to the store.</p>
                </div>
            </div>
        </div>
    </div>
    <div class="modal-footer" >
    	<button type="button" class="btn btn-danger btn-xs" data-dismiss="modal">No</button>
        <button type="button" class="btn btn-lime btn-xs" id="newreq">Yes</button>
    </div>
</div>
<script type="text/javascript"> 

   

    $(document).on('click','#newreq', function(e){  
        $('#reqresult').html(''); 
        $.ajax({
            url: 'init_req.php',
            typr: 'POST',
            data: 'st=NEW',
            dataType: 'html'
        })
        .done(function(data){ 
            console.log(data);
            $('#reqresult').html(''); // blank before load.
            $('#reqresult').html(data);
        })
        .fail(function(){
            $('#reqresult').html('<i class="fa  fa-info-sign"></i> Something went wrong, Please try again...');
            $('#modal-loader').hide();
        });
    });
 
</script>