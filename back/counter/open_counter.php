<?php
include("../../inc/connection.php");
include('../clude/funcs.php');
if(isset($_REQUEST['id'])){
  $id = (int)$_REQUEST['id'];
  $name=strtoupper(get_counter($id)); 
  $counter_row = mysqli_query($connection, "SELECT take_status FROM kp_counters WHERE cntr_id='$id' LIMIT 1");
  $counter_data = $counter_row ? mysqli_fetch_assoc($counter_row) : null;
  $take_status = (int)($counter_data['take_status'] ?? 0);
  $take_status_label = $take_status === 1 ? 'FINISHED' : ($take_status === 2 ? 'NOT STARTED' : ($take_status === 3 ? 'CONTINUING' : 'UNKNOWN'));
}
?>
<div class="modal-content open-counter-modal">
  <style>
    .open-counter-modal .counter-open-summary { display:flex; justify-content:space-between; align-items:center; gap:12px; padding:12px 14px; margin-bottom:16px; border:1px solid #d9e2ec; border-radius:7px; background:#f7faff; }
    .open-counter-modal .counter-open-summary small { display:block; color:#718096; font-size:11px; font-weight:700; letter-spacing:.06em; text-transform:uppercase; }
    .open-counter-modal .counter-open-summary strong { color:#243b53; font-size:16px; }
    .open-counter-modal .counter-take-status { padding:6px 10px; border-radius:7px; background:<?php echo $take_status === 3 ? '#2f855a' : '#718096'; ?>; color:#fff; font-size:12px; font-weight:700; }
  </style>
  <div id="results"></div>
  <div class="modal-header">
    <h4 class="modal-title"> <strong>OPEN <?php echo $name; ?> COUNTER</strong></h4> 
  </div>
  <div class="modal-body">  
    <div class="counter-open-summary">
      <div><small>Counter</small><strong><?php echo htmlspecialchars($name, ENT_QUOTES, 'UTF-8'); ?></strong></div>
      <div class="counter-take-status">TAKE: <?php echo $take_status_label; ?></div>
    </div>
    <div class="form-group"> 
    <label for="users"><strong>Counter cashier</strong></label>
    <select type="text" class="selectpicker form-control"  id="users" name="users"> 
      <option value="">----SELECT COUNTER CASHIER----</option>
                 <?php

          $item = "SELECT user_id, username FROM kp_users WHERE work_status=2 AND drole=1 AND counter=0 ORDER BY username ASC";
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
        <label for="take_status"><strong>Counter take status</strong></label>
        <select class="selectpicker form-control" id="take_status" name="take_status">
          <option value="1">FINISHED</option>
          <option value="2">NOT STARTED</option>
          <option value="3" selected>CONTINUING</option>
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
    var take_status = $("#take_status").val();
    $.ajax({
      url: 'open_counter_act.php',
      type: 'POST',
      data: 'counter='+encodeURIComponent(cntr_id)+'&user_id='+encodeURIComponent(user)+'&take_status='+encodeURIComponent(take_status),
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
