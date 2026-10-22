<?php
session_start();
include('../../inc/connection.php'); 
include('funcs.php');
$today = date('Y-m-d');  
$id = $_REQUEST['id']; 
$action="LOADED|-|-|-|PAYMENT MODAL FOR CREDIT SALE PAYMENT";
logs($action,"../../");  
?>
<div class="modal-content"> 
  <div id="pay-res"></div>
  <form id="quick-form" name="quicks"> 
    <div class="modal-header"> 
      <div class="row row-table text-center">
        <div class="col-md-12">
          <h4>SUPERVISOR/ADMIN CASH COLLECTION FROM <?PHP ECHO get_column('kp_pay_mode','name',"pay_id='$id'"); ?></h4>
        </div> 
      </div>
    </div>
    <div class="modal-body" id="load-cont" style="margin: 2px 2px;padding: 5px;">  
      <div class="form-group has-success col-md-12">
        <h3 class="text-blue">ACCOUNT BALANCE: <?PHP echo get_column('kp_pay_mode','accbal',"pay_id='$id'"); ?></h3>  
      </div> 
      <div class="form-group has-success col-md-12">
        <label class=" control-label">AMOUNT TO COLLECT</label> 
        <input type="text" id="amount" class="form-control"  name="amount" value="0"> 
        <input type="hidden" id="paid" class="form-control"  name="paid" value="<?php echo $id; ?>"> 
      </div>  
    </div>
      <div class="modal-footer" style="padding: 5px;"> 
        <button type="button" data-dismiss="modal" class="btn btn-outline-danger btn-md">CLOSE</button> 
        <a id="collecash" class="btn btn-outline-success btn-md"  >COLLECT CASH</a>
      </div>
    </form>  
  </div>
  <script>  
    $(document).off('click.cashCollect', '#collecash').on('click.cashCollect', '#collecash', function(e){
      e.preventDefault();
      $('#modal2').modal({backdrop: 'static', keyboard: false});
      $('#modal2').modal('show');
      var str = $('form[name="quicks"]').serializeArray();   
      $.ajax({
        url: 'colle_modal.php',
        type: 'POST',                           
        data: str,
        dataType: 'html'
      })
      .done(function(data){
        $('#load2').html('');
        $('#load2').html(data);  
      })
      .fail(function(){
        $('#load2').html('<div class="alert alert-danger"><a href="#" class="close" data-dismiss="alert">&times;</a><strong>Warning!</strong> Error occured.</div>');
      });
    }); 
  </script>

