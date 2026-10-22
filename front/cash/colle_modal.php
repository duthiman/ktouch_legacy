<?php
session_start();
include('../../inc/connection.php'); 
include('funcs.php');
$today = date('Y-m-d');
$user_id = $_SESSION['user_id'];
$id = $_REQUEST['paid'];
$amount = $_REQUEST['amount']; 
$action="LOADED|-|-|-|PAYMENT MODAL FOR CREDIT SALE PAYMENT";
logs($action,"../../");  
?>
<div class="modal-content"style="height: auto; width: auto;"> 
  <div id="colrest"></div>
  <form id="colform" name="colform" onsubmit="return false;"> 
    <div class="modal-header"> 
      <div class="row row-table text-center">
        <div class="col-md-12">
          <h4>SUPERVISOR/ADMIN CONFIRMATION FOR CASH COLLECTION  FROM <?PHP ECHO get_column('kp_pay_mode','name',"pay_id='$id'"); ?></h4>
        </div> 
      </div>
    </div>
    <div class="modal-body" id="load-cont" style="margin: 2px 2px;padding: 5px;"> 
      <fieldset id="form_fieldset" >
        <div class="p-3">
          <table class="table table-striped">
            <thead>
              <tr>
                <th><h4>AMOUNT</h4></th>
                <th><h4><?PHP echo $amount; ?></h4></th>
              </tr>
              <tr>
                <th><h4>CASHIER</h4></th>
                <th><h4><?php echo get_column('kp_users','username',"user_id='$user_id'"); ?></h4></th>
              </tr>
            </thead>
          </table> 
        </div>
        <input type="hidden" id="amount" class="form-control"  name="amount" value="<?php echo $amount; ?>"> 
        <input type="hidden" id="paid" class="form-control"  name="paid" value="<?php echo $id; ?>"> 
        <div class="form-group has-success col-md-12">
          <label class=" control-label">ENTER SUPERVISOR PASSWORD</label> 
          <input type="password" id="pass" class="form-control"  name="pass" > 
        </div>  
      </fieldset>
    </div>
  </form> 
    <div class="modal-footer" style="padding: 5px;"> 
      <button type="button" data-dismiss="modal" class="btn btn-outline-danger btn-md">CLOSE MODAL</button> 
      <button type="button" onclick="sub_col();" id="main-btn" class="btn btn-outline-info btn-md" >APPROVE COLLECTION</button>
    </div> 
</div>
  <script> 
    function sub_col(){
      if ($('#main-btn').prop('disabled')) {
        return;
      }
      $('#main-btn').prop('disabled', true);
     // $("#load-cont").html("<img src=../../assets/img/loader/pwait.gif>");
      var str = $('form[name="colform"]').serializeArray();  
      //console.log(str);
      $.ajax({
        url: 'collemact.php',
        type: 'POST',                           
        data: str,
        dataType: 'json'
      })
      .done(function(data){
        if (data.status === 'success') {
          $('#colrest').html('<div class="alert alert-success"><strong>Success!</strong> '+data.message+'</div>');
          setTimeout(function(){
            $('#modal2').modal('hide');
            $('#modal').modal('hide');
            if (typeof loadCashLive === 'function') {
              loadCashLive();
            }
          }, 500);
          return;
        }

        $('#colrest').html('<div class="alert alert-warning"><strong>Check!</strong> '+data.message+'</div>');
        $('#main-btn').prop('disabled', false);
      })
      .fail(function(){
        $('#main-btn').prop('disabled', false);
        $('#colrest').html('<div class="alert alert-danger"><a href="#" class="close" data-dismiss="alert">&times;</a><strong>Warning!</strong> Error occured.</div>');
      });
    }  

    $(document).off('keydown.cashCollect', '#pass').on('keydown.cashCollect', '#pass', function(e){
      if (e.key === 'Enter') {
        e.preventDefault();
        sub_col();
      }
    });
  </script>

