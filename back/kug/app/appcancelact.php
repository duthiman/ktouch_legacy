<?php
include('funcs.php');
page_restrict();
$user_id = $_SESSION['mwene'];

$po_id = $_REQUEST['id'];
$comm  = $_REQUEST['comm'];

$get_id = mysqli_query($connection, "SELECT * FROM kp_po_items WHERE po_id='$po_id'") or die(mysqli_error($connection));
if (mysqli_num_rows($get_id) >= 1) {
    $amount = column_total('kp_po_items', 'total', "po_id='$po_id'");
    while ($gid = mysqli_fetch_assoc($get_id)) {
        $item_id = $gid['item_id'];
        $qty     = $gid['qty'];
        $price   = $gid['price'];
        $total   = $gid['total'];
        $save_cancel_items = "INSERT INTO kp_reject_po_items(po_id,item_id,qty,price,total) VALUES('$po_id','$item_id','$qty','$price','$total')";
        mysqli_query($connection, $save_cancel_items) or die("Could not track cancelled order" . mysqli_error($connection));
    }

    $delete   = "DELETE FROM kp_po_items WHERE po_id='$po_id'";
    $delete_r = mysqli_query($connection, $delete) or die("Could not remove sale items");

    table_copy('kp_po', 'kp_rejected_po', '*', "po_id='$po_id'");
    Updatedata('kp_reject_po', "appr_comment='$comm' WHERE po_id='$po_id'");

    $deletes   = "DELETE FROM kp_po WHERE po_id='$po_id'";
    mysqli_query($connection, $deletes) or die("Could not remove sale items");

    logs("../../", "back/ord/", 'PURCHASE CANCEL', $po_id, '-', '-', 'PURCHASE CANCELLED');
    logs("",        "back/ord/", 'PURCHASE CANCEL', $po_id, '-', '-', 'PURCHASE CANCELLED');

} else {
    table_copy('kp_po', 'kp_rejected_po', '*', "po_id='$po_id'");
    Updatedata('kp_reject_po', "appr_comment='$comm' WHERE po_id='$po_id'");

    $deletes = "DELETE FROM kp_po WHERE po_id='$po_id'";
    mysqli_query($connection, $deletes) or die("Could not remove sale items");

    logs("../../", "back/ord/", 'PURCHASE CANCEL', $po_id, '-', '-', 'PURCHASE CANCELLED');
    logs("",        "back/ord/", 'PURCHASE CANCEL', $po_id, '-', '-', 'PURCHASE CANCELLED');
}

echo '<div class="alert alert-danger py-2 small">
  <i class="fa fa-times-circle me-1"></i>
  PO #' . (int)$po_id . ' rejected and archived.
</div>';
echo '<script>
setTimeout(function(){
    $("#modal").modal("hide");
    $.ajax({url:"kug/app/index.php",type:"GET",dataType:"html"}).done(function(h){$("#content").html(h);}).fail(function(){if(typeof kugReload==="function"){kugReload();}});
}, 1200);
</script>';
?>
