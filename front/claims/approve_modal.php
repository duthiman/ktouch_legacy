 <?php
  session_start(); 
  include("../../inc/connection.php");
  include("funcs.php");
  $user_id = $_SESSION['user_id'];
  $id=$_REQUEST['id']; 
  $claim_staff= get_column('split_message','claim_staff',"id='$id'");
  ?>
 <div class="modal-content" >
    <div class="modal-header">
      <h4 class="modal-title">APPROVE THIS MPESA AS CLAIMED BY <?php echo get_column('kp_users','username',"user_id='$claim_staff'"); ?> </h4> 
    </div>
    <div class="modal-body">
      <p id="app_res"></p>
      <table class="table table-bordered" style="padding: 2px;">
        <thead>
          <tr>
            <th>CODE</th>
            <th><?php echo get_column('split_message','code',"id='$id'"); ?></th>
          </tr>
          <tr>
            <th>NAME</th>
            <th><?php echo get_column('split_message','name',"id='$id'"); ?></th>
          </tr>
          <tr>
            <th>AMOUNT</th>
            <th><?php echo get_column('split_message','amount',"id='$id'"); ?></th>
          </tr>
          <tr>
            <th>TIME</th>
            <th><?php echo get_column('split_message','time',"id='$id'"); ?></th>
          </tr>
        </thead>
      </table>
      <div class="container-fluid"> 
        <div class="alert alert-warning col-md-12">
            <h4 class="text-danger">ARE YOU SURE YOU WANT TO APPROVE <?php echo get_column('kp_users','username',"user_id='$claim_staff'"); ?> THIS MPESA!!!! </h4>
          </div>
      </div>
    </div> 
    <div class="modal-footer">
      <button type="button" class="btn btn-outline-danger btn-sm" data-dismiss="modal">CLOSE MODAL</button>  
      <a class="btn btn-outline-success btn-sm" id="appconfirm" data-id1="<?php echo $id; ?>">APPROVE MESSAGE</a>
    </div>
  </div>
  <script type="text/javascript">
     
// Add your custom JS code here
$( document ).ready(function() { 
   $(document).on('click','#appconfirm', function(e){
      e.preventDefault(); 
      var id = $(this).data('id1');  
      if (id==0) {
        var hulla = new hullabaloo();
        hulla.send("You have to select message!!!!", "warning");
      }else{
        $.ajax({
          url: '../claims/app_act.php',
          typr: 'POST',
          data: {id:id},
          dataType: 'html'
        })
        .done(function(data){
          $('#app_res').html(''); // blank before load.
          $('#app_res').html(data);
        })
        .fail(function(){
          $('#app_res').html('<i class="fa  fa-info-sign"></i> Something went wrong, Please try again...'); 
        });
      }
    });
});
  </script>