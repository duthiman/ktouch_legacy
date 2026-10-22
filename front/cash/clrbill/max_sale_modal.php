<?php
session_start();
include('../../../inc/connection.php');
include('../funcs.php');

$user_id = isset($_GET['user_id']) ? intval($_GET['user_id']) : 0;
$user_name = get_column('kp_users', 'username', "user_id='$user_id'");
$max_sale = 0;
if ($user_id > 0) {
    $column_check = mysqli_query($connection, "SHOW COLUMNS FROM kp_users LIKE 'max_sale'");
    if ($column_check && mysqli_num_rows($column_check) > 0) {
        $max_sale = (float)get_column('kp_users', 'max_sale', "user_id='$user_id'");
    }
}
?>
<div class="modal-content">
  <div class="modal-header">
    <h4 class="modal-title">Increase Max Sale for <?php echo htmlspecialchars($user_name ?: 'User'); ?></h4>
    <button type="button" class="close" data-dismiss="modal" aria-hidden="true">×</button>
  </div>
  <div class="modal-body">
    <div id="max-sale-result"></div>
    <?php if ($user_id > 0) { ?>
      <div class="form-group">
        <label>Current Max Sale</label>
        <input type="text" class="form-control" value="<?php echo number_format($max_sale, 2); ?>" disabled />
      </div>
      <div class="form-group">
        <label>New Max Sale Amount</label>
        <input type="number" step="0.01" min="0" id="new_max_sale" class="form-control" value="<?php echo number_format($max_sale, 2, '.', ''); ?>" />
      </div>
      <input type="hidden" id="max_sale_user_id" value="<?php echo $user_id; ?>" />
    <?php } else { ?>
      <div class="alert alert-danger">Invalid user selected.</div>
    <?php } ?>
  </div>
  <div class="modal-footer">
    <button type="button" class="btn btn-sm btn-danger" data-dismiss="modal">Close</button>
    <?php if ($user_id > 0) { ?>
      <button type="button" id="save-max-sale-btn" class="btn btn-sm btn-success">Save</button>
    <?php } ?>
  </div>
</div>
<script>
$(function(){
  $('#save-max-sale-btn').on('click', function(){
    var userId = $('#max_sale_user_id').val();
    var newMax = $('#new_max_sale').val();
    if (newMax === '' || isNaN(newMax) || parseFloat(newMax) < 0) {
      $('#max-sale-result').html('<div class="alert alert-warning">Enter a valid max sale amount.</div>');
      return;
    }
    $.ajax({
      url: 'clrbill/max_sale_act.php',
      type: 'POST',
      data: { user_id: userId, max_sale: newMax },
      dataType: 'json'
    })
    .done(function(data) {
      if (data.status === 'success') {
        $('#max-sale-result').html('<div class="alert alert-success">' + data.message + '</div>');
        setTimeout(function(){
          $('#modal').modal('hide');
          if (typeof window.cashReloadMaxSaleContext === 'function') {
            window.cashReloadMaxSaleContext();
            window.cashReloadMaxSaleContext = null;
          } else if (typeof loadDeskPanel === 'function') {
            loadDeskPanel('clrbill/clearb.php', {}, 'Could not load clear bills.');
          }
        }, 600);
      } else {
        $('#max-sale-result').html('<div class="alert alert-danger">' + data.message + '</div>');
      }
    })
    .fail(function(){
      $('#max-sale-result').html('<div class="alert alert-danger">Unable to save max sale.</div>');
    });
  });
});
</script>
