<?php
include("../../inc/connection.php");
include('../clude/funcs.php');
if(isset($_REQUEST['id'])){
  $id = $_REQUEST['id'];
  $name=strtoupper(get_counter($id)); 
}
?>
<div class="modal-content">
  <link rel="stylesheet" href="../../assets/plugins/sweetalert/dist/sweetalert2.min.css">
  <script src="../../assets/plugins/sweetalert/dist/sweetalert2.all.min.js"></script>
  <div id="results"></div>
  <div class="modal-header">
    <h4 class="modal-title"> <strong>CHANGE TAKE STATUS FOR  <?php echo $name; ?>  COUNTER</strong></h4> 
  </div>
  <div class="modal-body"> 
    
      <div class="form-group">
        <label >Current Cashier: <b><?php echo get_column('kp_counters','user_id',"cntr_id='$id'");//($id); ?></b></label> 
      </div>  
       <div class="form-group"> 
           <select type="text" class="selectpicker form-control"  id="stat" name="stat"> 
              <option>----SELECT STATUS----</option> 
              <option  value="1">FINISHED</option>
              <option  value="2">NOT STARTED</option>
              <option  value="3">CONTINUING</option>
                </select>
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
    //var reason = $("#reason").val(); 
    var stat = $("#stat").val(); 
    var $button = $('#assign_cash');
    if (!stat || stat === '----SELECT STATUS----') { Swal.fire({icon:'warning', title:'Select a status', text:'Please select the counter take status.'}); return; }
    $button.prop('disabled', true).text('Saving...');
    $.ajax({
      url: 'change_stat_act.php',
      typr: 'POST',                           
      data: 'counter='+cntr_id+'&stat='+stat,
      dataType: 'json'
    })
    .done(function(data){
        if (data && data.ok) {
          Swal.fire({icon:'success', title:'Status updated', text:data.message || 'Counter take status updated.', timer:1400, showConfirmButton:false}).then(function(){ window.location.reload(); });
        } else {
          $button.prop('disabled', false).text('Assign');
          $('#results').html('<div class="alert alert-danger">'+((data && data.message) || 'Could not update status.')+'</div>');
        }
    })
    .fail(function(xhr){
      $button.prop('disabled', false).text('Assign');
      Swal.fire({icon:'error', title:'Update failed', text:(xhr.responseJSON && xhr.responseJSON.message) || 'Could not update status.'});
    });
  }
</script> 
