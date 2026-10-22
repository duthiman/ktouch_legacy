<?php
include('funcs.php');
$user_id = $_SESSION['mwene'];
$po_id   = (int)($_POST['po'] ?? 0);

if ($po_id <= 0) {
    echo '<div class="alert alert-danger py-2 small">Invalid PO ID.</div>';
    exit;
}

$today  = today();
$datime = todatime();
$totime = totime();

mysqli_query($connection,
    "UPDATE kp_po SET appr_status=3 WHERE po_id='$po_id'"
) or die(mysqli_error($connection));

mysqli_query($connection,
    "INSERT INTO kp_po_hist(po_id,trans,trans_user,trans_day,trans_time,trans_datime)
     VALUES('$po_id','PO HELD','$user_id','$today','$totime','$datime')"
) or die(mysqli_error($connection));

logs('../../', 'back/kugura/', 'PURCHASE', $po_id, '-', '-', 'PO HELD');
logs('',       'back/kugura/', 'PURCHASE', $po_id, '-', '-', 'PO HELD');

echo '<div class="alert alert-warning py-2 small">
  <i class="fa fa-pause-circle me-1"></i>
  PO #' . $po_id . ' is now on hold.
</div>';
echo '<script>
setTimeout(function(){
    $("#modal").modal("hide");
    $.ajax({url:"kug/app/index.php",type:"GET",dataType:"html"}).done(function(h){$("#content").html(h);}).fail(function(){if(typeof kugReload==="function"){kugReload();}});
}, 1200);
</script>';
?>
