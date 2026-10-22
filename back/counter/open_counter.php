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
    <h4 class="modal-title"> <strong>OPEN <?php echo $name; ?> COUNTER</strong></h4> 
  </div>
  <div class="modal-body">  
    <div class="form-group"> 
    <select type="text" class="selectpicker form-control"  id="users" name="users"> 
      <option>----SELECT COUNTER CASHIER----</option>
                 <?php

          $item = "SELECT * FROM kp_users WHERE user_work_status=2 AND drole=1 AND counter=0  ORDER BY user_username ASC";
          $item_r = mysqli_query($connection,$item) or die("Error getting categories");
          while ($ct = mysqli_fetch_assoc($item_r)) {
              $name = $ct['user_username'];
              $user_id = $ct['user_id'];
              ?>
              <option  value="<?php echo $user_id; ?>"><?php echo $name; ?></option>
              <?php
          }
          ?>
                </select>
      </div>
         
  </div>
  <div class="modal-footer">
    <a href="javascript:;" class="btn btn-sm btn-danger" data-dismiss="modal">Close</a>
    <button type="button" name="open_count" id="open_count" class="btn btn-sm btn-warning">Open Counter</button>
  </div>
</div>

<script type="text/javascript">
document.getElementById('open_count').addEventListener('click', open_count);
  function open_count(){  
    var cntr_id = '<?php echo $id; ?>'; 
    var user = $("#users").val(); 
    $.ajax({
      url: 'open_counter_act.php',
      typr: 'POST',                           
      data: 'counter='+cntr_id+'&user_id='+user,
      dataType: 'html'
    })
    .done(function(data){
      console.log(data);
        $('#results').html(''); // blank before load.
        $('#results').html(data);
    })
    .fail(function(){
      $('#results').html('<div class="alert alert-danger"><a href="#" class="close" data-dismiss="alert">&times;</a><strong>Warning!</strong> Error occured.</div>');
    });
  }
</script> 