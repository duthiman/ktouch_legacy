<?php
include('funcs.php');
$user_id = $_SESSION['mwene'];
$po_id   = $_REQUEST['id'];
$comm    = $_REQUEST['comm'];

if (!$po_id) {
    echo '<div class="alert alert-danger py-2">Invalid PO ID.</div>';
    exit;
}

$sup = get_column('kp_po', 'sup_id', "po_id='$po_id'");
$day = get_column('kp_po', 'po_date', "po_id='$po_id'");

logs("../../", 'back/kugura/', 'PURCHASE', $po_id, '-', '-', 'LOADED CONFIRM CANCEL MODAL');
logs("",       'back/kugura/', 'PURCHASE', $po_id, '-', '-', 'LOADED CONFIRM CANCEL MODAL');

$no    = 0;
$total = 0;
?>
<div class="modal-content">
    <div id="po-results"></div>
    <div class="modal-header">
        <h4 class="modal-title">REJECT PURCHASE ORDER #<?php echo (int)$po_id; ?></h4>
        <button type="button" class="btn-close btn-close-white" data-bs-dismiss="modal" aria-label="Close"></button>
    </div>
    <div class="modal-body">
        <div class="alert alert-danger">
            <h6 class="fw-bold"><i class="fa fa-exclamation-triangle me-1"></i>Confirm Rejection</h6>
            <p class="mb-0">This will archive PO #<?php echo (int)$po_id; ?> and remove it from the pending list.</p>
        </div>
        <div class="alert alert-warning small">
            <b>Reason / Comment:</b><br>
            <?php echo htmlspecialchars($comm ?: '(no comment)'); ?>
        </div>
    </div>
    <div class="modal-footer">
        <button type="button" class="btn btn-outline-secondary btn-sm" data-bs-dismiss="modal">Cancel</button>
        <button class="btn btn-danger btn-sm" onclick="approvecanclel();">
            <i class="fa fa-times-circle me-1"></i>Confirm Rejection
        </button>
    </div>
</div>
<script>
function approvecanclel() {
    var id   = '<?php echo (int)$po_id; ?>';
    var comm = '<?php echo addslashes($comm); ?>';
    $('#po-results').html('');
    $.ajax({
        url: 'kug/app/appcancelact.php',
        type: 'POST',
        data: { id: id, comm: comm },
        dataType: 'html'
    }).done(function(data) {
        $('#po-results').html(data);
    }).fail(function() {
        $('#po-results').html('<div class="alert alert-danger py-2 small">Something went wrong, please try again.</div>');
    });
}
</script>
