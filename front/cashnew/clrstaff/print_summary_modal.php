<?php
session_start();
include('../../../inc/connection.php');
require_once('../../../inc/gen_funcs.php');

$user_id = (int)($_POST['id'] ?? $_GET['id'] ?? 0);
if ($user_id < 1) {
    ?>
    <div class="modal-content">
      <div class="modal-body">
        <div class="alert alert-warning" style="margin-bottom:0;">Invalid waiter selected for print preview.</div>
      </div>
    </div>
    <?php
    exit;
}

$staff_name = get_user($user_id);
?>
<div class="modal-content" style="border-radius:18px; overflow:hidden;">
  <div class="modal-header" style="background:#fff6e9; border-bottom:1px solid #eadfce;">
    <h4 class="modal-title" style="font-weight:900; color:#7a491b;">Print Staff Summary</h4>
    <button type="button" class="close" data-dismiss="modal" aria-label="Close">
      <span aria-hidden="true">&times;</span>
    </button>
  </div>
  <div class="modal-body" style="padding:14px; background:#fffdf9;">
    <div style="display:flex; justify-content:space-between; align-items:center; gap:12px; margin-bottom:12px; flex-wrap:wrap;">
      <div>
        <strong style="display:block; color:#5b4330;"><?php echo htmlspecialchars($staff_name); ?></strong>
        <span style="display:block; color:#8c7b68; font-size:12px;">Preview the shift summary here, then print without leaving the cash page.</span>
      </div>
      <div style="display:flex; gap:8px; flex-wrap:wrap;">
        <button type="button" class="btn btn-outline-secondary" data-dismiss="modal">Close</button>
        <button type="button" class="btn btn-warning" onclick="cashQueueStaffSummary(<?php echo $user_id; ?>)">Queue Summary</button>
      </div>
    </div>
    <div style="border:1px solid #eadfce; border-radius:16px; overflow:hidden; background:#fff;">
      <iframe
        id="cash-staff-print-frame"
        src="../chaba/render_staff_summary.php?id=<?php echo $user_id; ?>&modal=1"
        style="width:100%; height:70vh; border:0; background:#fff;"
        title="Staff Summary Print Preview"></iframe>
    </div>
  </div>
</div>
<script type="text/javascript">
  function cashQueueStaffSummary(staffId) {
    $.ajax({url:'API/cash_api.php',type:'POST',dataType:'json',data:{action:'queue_staff_summary',staff_id:staffId}}).done(function(res){
      if(res.ok){ alert(res.message || 'Staff summary queued for printing.'); $('#modal').modal('hide'); }
      else alert(res.message || 'Could not queue staff summary.');
    }).fail(function(){ alert('Could not queue staff summary.'); });
  }
</script>
