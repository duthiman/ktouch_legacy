<?php
include("../../inc/connection.php");
include('../clude/funcs.php');
if(isset($_REQUEST['id'])){
  $id = (int)$_REQUEST['id'];
  $name=strtoupper(get_counter($id)); 
}
$counter_department = (int)get_column('kp_counters', 'dpt', "cntr_id='$id'");
$cashier_department = $counter_department === 2 ? 3 : 1;
?>
<div class="modal-content assign-cashier-modal">
  <style>
    .assign-cashier-modal .current-cashier-card {
      display:flex;
      align-items:center;
      justify-content:space-between;
      gap:12px;
      padding:12px 14px;
      margin-bottom:16px;
      border:1px solid #d9e2ec;
      border-radius:7px;
      background:#f7faff;
    }
    .assign-cashier-modal .current-cashier-label {
      display:block;
      margin-bottom:3px;
      color:#718096;
      font-size:11px;
      font-weight:700;
      letter-spacing:.06em;
      text-transform:uppercase;
    }
    .assign-cashier-modal .current-cashier-name {
      color:#243b53;
      font-size:16px;
      font-weight:700;
    }
    .assign-cashier-modal .current-cashier-id {
      padding:5px 9px;
      border-radius:7px;
      background:#2f855a;
      color:#fff;
      font-size:12px;
      font-weight:700;
      white-space:nowrap;
    }
  </style>
  <div id="results"></div>
  <div class="modal-header">
    <h4 class="modal-title"> <strong>ASSIGN NEW CASHIER FOR COUNTER</strong> <?php echo $name; ?></h4> 
  </div>
  <div class="modal-body"> 
    
    <div class="current-cashier-card" aria-label="Current cashier">
      <?php
        $current_cashier_id = (int)get_column('kp_counters', 'user_id', "cntr_id='$id'");
        $current_cashier_name = $current_cashier_id > 0 ? strtoupper(get_user($current_cashier_id)) : 'NO CASHIER ASSIGNED';
      ?>
      <div>
        <span class="current-cashier-label">Current cashier</span>
        <span class="current-cashier-name"><?php echo htmlspecialchars($current_cashier_name, ENT_QUOTES, 'UTF-8'); ?></span>
      </div>
      <?php if ($current_cashier_id > 0): ?>
        <span class="current-cashier-id">ID <?php echo $current_cashier_id; ?></span>
      <?php endif; ?>
    </div>
       <div class="form-group"> 
           <select type="text" class="selectpicker form-control"  id="users" name="users"> 
                     <option value="">----SELECT USER----</option>
                 <?php

          $item = "SELECT u.user_id, u.username
                   FROM kp_users u
                   WHERE u.work_status=2
                     AND u.drole='$cashier_department'
                     AND NOT EXISTS (
                       SELECT 1
                       FROM kp_counters assigned_counter
                       WHERE assigned_counter.user_id=u.user_id
                         AND assigned_counter.cntr_id<>'$id'
                     )
                   ORDER BY u.username ASC";
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
    var $button = $('#assign_cash');
    if (!user) { Swal.fire({icon:'warning', title:'Select a cashier', text:'Please select the new counter cashier.'}); return; }
    $button.prop('disabled', true).text('Assigning...');
    $.ajax({
      url: 'assign_cashier_act.php',
      type: 'POST',
      data: 'counter='+encodeURIComponent(cntr_id)+'&reason='+encodeURIComponent(reason)+'&user_id='+encodeURIComponent(user)+'&current_user_id='+encodeURIComponent('<?php echo $current_cashier_id; ?>'),
      dataType: 'json'
    })
    .done(function(data){
        if (data && data.ok) {
          Swal.fire({icon:'success', title:'Cashier assigned', text:data.message || 'The cashier was assigned successfully.', timer:1400, showConfirmButton:false}).then(function(){ window.location.reload(); });
        } else {
          $button.prop('disabled', false).text('Assign');
          $('#results').html('<div class="alert alert-danger">'+((data && data.message) || 'Could not assign cashier.')+'</div>');
        }
    })
    .fail(function(xhr){
      $button.prop('disabled', false).text('Assign');
      Swal.fire({icon:'error', title:'Assignment failed', text:(xhr.responseJSON && xhr.responseJSON.message) || 'Could not assign cashier.'});
    });
  }
</script>
