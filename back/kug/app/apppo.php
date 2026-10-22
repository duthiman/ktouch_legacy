<?php
include('funcs.php');
$user_id = $_SESSION['mwene'];
$po_id   = (int)mysqli_real_escape_string($connection, $_REQUEST['id'] ?? 0);
$comm    = mysqli_real_escape_string($connection, $_REQUEST['comm'] ?? '');

if ($po_id <= 0) {
    echo '<div class="alert alert-danger py-2 small">Invalid PO ID.</div>';
    exit;
}

$today  = today();
$datime = todatime();
$totime = totime();

$update_order = "UPDATE kp_po SET appr_status=1, po_status=1, appr_user='$user_id', appr_comment='$comm' WHERE po_id='$po_id'";
mysqli_query($connection, $update_order) or die("Could not update PO: " . mysqli_error($connection));

mysqli_query($connection,
    "INSERT INTO kp_po_hist(po_id,trans,trans_user,trans_day,trans_time,trans_datime,trans_comment)
     VALUES('$po_id','PO APPROVAL','$user_id','$today','$totime','$datime','$comm')"
) or die(mysqli_error($connection));

logs("../../", 'back/kugura/', 'PURCHASE', $po_id, '-', '-', 'APPROVED PURCHASE ORDER');
logs("../",   'back/kugura/', 'PURCHASE', $po_id, '-', '-', 'APPROVED PURCHASE ORDER');
?>
<div class="alert alert-success py-2 small">
  <i class="fa fa-check-circle me-1"></i>
  PO #<?php echo $po_id; ?> approved successfully!
</div>
<script>
// Open print window immediately
window.open('kug/ord/print_po.php?id=<?php echo $po_id; ?>', '_blank', 'width=900,height=700');

setTimeout(function() {
    $('#modal').modal('hide');
    // Redirect back to the Approve LPO dashboard
    $.ajax({
        url: 'kug/app/index.php',
        type: 'GET',
        dataType: 'html'
    }).done(function(h) {
        $('#content').html(h);
    }).fail(function() {
        // Fallback: try the main purchase dashboard
        if (typeof kugReload === 'function') { kugReload(); }
        else if (typeof kugLoad === 'function') { kugLoad(); }
    });
}, 1200);
</script>
