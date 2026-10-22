<?php
session_start();
include('../../inc/connection.php');
include('funcs.php');

$po_id = (int)$_REQUEST['id'];
$po_q = mysqli_query($connection, "SELECT * FROM kp_po WHERE po_id='$po_id'") or die(mysqli_error($connection));
$po = mysqli_fetch_assoc($po_q);
?>
<div class="modal-content">
  <div class="modal-header">
    <button type="button" class="close" data-dismiss="modal" aria-label="Close"><span aria-hidden="true">&times;</span></button>
    <h4 class="modal-title">SELECT SUPPLIER FOR PO <?php echo $po_id; ?></h4>
  </div>
  <div class="modal-body">
    <p><strong>Counter:</strong> <?php echo front_po_counter_name($po_id); ?></p>
    <p><strong>Ref:</strong> <?php echo $po['refno']; ?></p>
    <p><strong>Current Supplier:</strong> <?php echo (int)$po['sup_id'] > 0 ? get_sup($po['sup_id']) : 'NONE'; ?></p>
    <select class="form-control" id="front-po-supplier">
      <option value="0">SELECT SUPPLIER</option>
      <?php
      $suppliers = mysqli_query($connection, "SELECT * FROM kp_suppliers ORDER BY sup_name ASC") or die(mysqli_error($connection));
      while ($sup = mysqli_fetch_assoc($suppliers)) {
      ?>
      <option value="<?php echo $sup['sup_id']; ?>" <?php echo ((int)$po['sup_id'] === (int)$sup['sup_id']) ? 'selected' : ''; ?>><?php echo strtoupper($sup['sup_name']); ?></option>
      <?php } ?>
    </select>
  </div>
  <div class="modal-footer">
    <button type="button" class="btn btn-danger btn-xs" data-dismiss="modal">Close</button>
    <button type="button" class="btn btn-success btn-xs" id="front-po-approve-with-supplier" data-id="<?php echo $po_id; ?>">Approve</button>
  </div>
</div>
<script type="text/javascript">
$(document).on('click','#front-po-approve-with-supplier', function(){
  var poId = $(this).data('id');
  var supId = $('#front-po-supplier').val();
  if (supId == 0) {
    alert('Select supplier first');
    return;
  }
  window.location = '../kugura/front_po_approve.php?id=' + poId + '&sup_id=' + supId;
});
</script>
