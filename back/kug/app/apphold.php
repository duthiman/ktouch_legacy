<?php
include('funcs.php');
$user_id = $_SESSION['mwene'];
$po_id   = (int)$_REQUEST['po'];

logs("../../", 'back/kugura/', 'PURCHASE', $po_id, '-', '-', 'LOADED HOLD MODAL');
logs("",       'back/kugura/', 'PURCHASE', $po_id, '-', '-', 'LOADED HOLD MODAL');
?>
<div class="modal-content">
    <div class="modal-header" style="background:#f59c1a;">
        <h4 class="modal-title text-white">HOLD APPROVAL — PO #<?php echo $po_id; ?></h4>
        <button type="button" class="btn-close btn-close-white" data-bs-dismiss="modal" aria-label="Close"></button>
    </div>
    <div class="modal-body">
        <div id="hold-result"></div>
        <div class="alert alert-warning">
            <h6 class="fw-bold"><i class="fa fa-exclamation-triangle me-1"></i>Warning</h6>
            <p class="mb-0">This will place PO #<?php echo $po_id; ?> on hold. The PO will be removed from the pending approval list until it is released.</p>
        </div>
    </div>
    <div class="modal-footer">
        <button type="button" class="btn btn-outline-secondary btn-sm" data-bs-dismiss="modal">Close</button>
        <button type="button" class="btn btn-warning btn-sm" id="btn-confirm-hold"
                data-po="<?php echo $po_id; ?>">
            <i class="fa fa-pause-circle me-1"></i>Confirm Hold
        </button>
    </div>
</div>
<script>
$(document).on('click', '#btn-confirm-hold', function() {
    var po  = $(this).data('po');
    var btn = this;
    btn.disabled = true;
    btn.innerHTML = '<span class="spinner-border spinner-border-sm"></span> Holding...';
    $.ajax({
        url: 'kug/app/appholdact.php',
        type: 'POST',
        data: { po: po },
        dataType: 'html'
    }).done(function(d) {
        $('#hold-result').html(d);
    }).fail(function() {
        btn.disabled = false;
        btn.innerHTML = '<i class="fa fa-pause-circle me-1"></i>Confirm Hold';
        $('#hold-result').html('<div class="alert alert-danger py-2 small">Request failed. Please try again.</div>');
    });
});
</script>
