<?php
session_start();
include('../../inc/connection.php');
include('funcs.php');

$user_id = $_SESSION['user_id'];
$po_id = (int)$_GET['id'];
$new_sup_id = isset($_GET['sup_id']) ? (int)$_GET['sup_id'] : 0;

$po_q = mysqli_query($connection, "SELECT * FROM kp_po WHERE po_id='$po_id'") or die(mysqli_error($connection));
$po = mysqli_fetch_assoc($po_q);
if (!$po || !is_front_po($po['refno'])) {
    header("Location:../dash/admin_dash.php");
    exit;
}

$sup_id = (int)$po['sup_id'];
if ($sup_id < 1 && $new_sup_id > 0) {
    $sup_id = $new_sup_id;
}
if ($sup_id < 1) {
    header("Location:../dash/admin_dash.php?msg=frontpo-nosupplier");
    exit;
}

$counter = front_po_counter_id($po_id);
$amount = (float)po_total($po_id);
$balance = get_column('kp_suppliers','sup_bal',"sup_id='$sup_id'");
if (empty($balance)) {
    $balance = 0;
}

mysqli_query($connection, "UPDATE kp_po SET sup_id='$sup_id', amount='$amount', cf_bal='$balance', rec_status=1, stock_status=1, s2s=1, rec_staff='$user_id', rec_date=CURRENT_DATE WHERE po_id='$po_id'") or die(mysqli_error($connection));
mysqli_query($connection, "UPDATE kp_po_items SET rec_price=price, rec_qty=qty, rec_total=total, rec_user='$user_id', rec_status=1, s2s=1 WHERE po_id='$po_id'") or die(mysqli_error($connection));

$grn_id = get_id('kp_grn_id','grn_id');
mysqli_query($connection, "INSERT INTO kp_grn(grn_id,po_id,user_id,sup_id,amount,bal,day) VALUES('$grn_id','$po_id','$user_id','$sup_id','$amount','$amount',CURRENT_DATE)") or die(mysqli_error($connection));

$items = mysqli_query($connection, "SELECT * FROM kp_po_items WHERE po_id='$po_id'") or die(mysqli_error($connection));
while ($item = mysqli_fetch_assoc($items)) {
    $item_id = $item['item_id'];
    $qty = $item['qty'];
    $price = $item['price'];
    $total = $item['total'];
    $dpt = get_column('kp_items','dpt',"item_id='$item_id'");
    mysqli_query($connection, "INSERT INTO kp_grn_items(grn_id,item_id,qty,price,total,user_id,day,s2s) VALUES('$grn_id','$item_id','$qty','$price','$total','$user_id',CURRENT_DATE,1)") or die(mysqli_error($connection));
    mysqli_query($connection, "UPDATE kp_items SET b_price='$price' WHERE item_id='$item_id'") or die(mysqli_error($connection));
    if ($counter > 0) {
        $column = "cntr".$counter."_qty";
        mysqli_query($connection, "UPDATE kp_items SET $column=$column+'$qty' WHERE item_id='$item_id'") or die(mysqli_error($connection));
        mysqli_query($connection, "INSERT INTO kp_stock_track(item_id,po_id,grn_id,user_id,qty,status,stock_counter,shift,dpt) VALUES('$item_id','$po_id','$grn_id','$user_id','$qty','IN','$counter',CURRENT_DATE,'$dpt')") or die(mysqli_error($connection));
    } else {
        mysqli_query($connection, "UPDATE kp_items SET store_qty=store_qty+'$qty' WHERE item_id='$item_id'") or die(mysqli_error($connection));
        mysqli_query($connection, "INSERT INTO kp_store_stock_track(grn_id,item_id,po_id,user_id,qty,status,shift) VALUES('$grn_id','$item_id','$po_id','$user_id','$qty','IN',CURRENT_DATE)") or die(mysqli_error($connection));
    }
}

mysqli_query($connection, "UPDATE kp_suppliers SET sup_bal=sup_bal+'$amount' WHERE sup_id='$sup_id'") or die(mysqli_error($connection));

$action = "APPROVED FRONT BUY PO|".$po_id."|FOR|".front_po_counter_name($po_id);
logs($action, "../../");
header("Location:po_print.php?id=".$po_id);
?>
