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
    <h4 class="modal-title"> <strong>ASSIGN NEW CASHIER FOR COUNTER</strong> <?php echo $name; ?></h4> 
  </div>
  <div class="modal-body"> 
    
    <div class="form-group">
        <label >Current Cashier: <b><?php echo $cashier=get_column('kp_counters','user_id',"cntr_id='$id'"); ?></b></label> 
      </div>  
       <div class="form-group"> 
           <select type="text" class="selectpicker form-control"  id="users" name="users"> 
                     <option>----SELECT USER----</option>
                 <?php

          $item = "SELECT * FROM kp_users WHERE work_status=2 AND drole=1  ORDER BY username ASC";
          $item_r = mysqli_query($connection,$item) or die("Error getting categories");
          while ($ct = mysqli_fetch_assoc($item_r)) {
              $name = $ct['username'];
              $user_id = $ct['user_id'];
              ?>
              <option  value="<?php echo $user_id; ?>"><?php echo $name; ?></option>
              <?php
          }
          ?>
                </select>
      </div>
      <div class="form-group">
        <label >Reason For Assigning</label>
        <textarea name="reason" class="form-control" placeholder="Reason" id="reason"> </textarea>
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
    var reason = $("#reason").val(); 
    var user = $("#users").val(); 
    $.ajax({
      url: 'assign_cashier_act.php',
      typr: 'POST',                           
      data: 'counter='+cntr_id+'&reason='+reason+'&user_id='+user,
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