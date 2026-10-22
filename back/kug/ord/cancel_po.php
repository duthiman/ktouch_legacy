<?php 
include('funcs.php');
$user_id = $_SESSION['mwene'];  
$po_id   = get_column('kp_thesion','trans_id',"user_id='$user_id'");  
logs("../../",'back/kugura/','PURCHASE',$po_id,'-','-','LOADED CANCEL MODAL');
logs("",'back/kugura/','PURCHASE',$po_id,'-','-','LOADED CANCEL MODAL');

if ($user_id==0 || empty($user_id)) {
    echo '<div class="alert alert-danger py-1 small">SESSION Issue — Logout and login again.</div>';
} elseif ($po_id==0) {
    echo '<div class="alert alert-danger py-1 small">No active order found.</div>';
} else {
    $today  = today();
    $datime = todatime();
    $totime = totime();
    $update_items = mysqli_query($connection,"DELETE FROM kp_po_items WHERE po_id='$po_id'") or die(mysqli_error($connection));
    $reset_status = mysqli_query($connection,"DELETE FROM kp_po_status WHERE po_id='$po_id'") or die("Error");

    if (!$reset_status || !$update_items) {
        echo '<div class="alert alert-danger py-1 small">Failed — contact developer.</div>';
    } else {
        mysqli_query($connection,"INSERT INTO kp_po_hist(po_id,item_id,qty,trans,trans_user,trans_day,trans_time,trans_datime,price) VALUES('$po_id','0','0','CANCELLED','$user_id','$today','$totime','$datime','0')") or die(mysqli_error($connection));
        logs("../../",'back/kugura/','PURCHASE',$po_id,'-','-','SUCCESSFULLY CANCELED');
        logs("",'back/kugura/','PURCHASE',$po_id,'-','-','SUCCESSFULLY CANCELED');
        $update_thesion = mysqli_query($connection,"UPDATE kp_thesion SET trans_id=0,datime='$datime' WHERE user_id='$user_id'") or die(mysqli_error($connection));

        echo '<div class="alert alert-success py-1 small"><i class="fa fa-check-circle me-1"></i>PO #'.$po_id.' cancelled successfully.</div>';
        echo '<script>
        setTimeout(function(){
            $("#modal").modal("hide");
            $.ajax({url:"kug/index.php",type:"GET",dataType:"html"}).done(function(h){$("#content").html(h);});
        }, 1000);
        </script>';
    }
}
?>