<?php
include('funcs.php');
$user_id = $_SESSION['id'];
$row_id  = (int)($_POST['id'] ?? 0);   // ✅ unique kp_po_items.id
$price   = mysqli_real_escape_string($connection, $_POST['price']);

if (empty($price)) {
    echo '<div class="alert alert-warning py-1 small"><i class="fa fa-exclamation-triangle me-1"></i>Price cannot be empty.</div>';
    exit;
}
if ($row_id <= 0) {
    echo '<div class="alert alert-danger py-1 small">Invalid item row.</div>';
    exit;
}

$today  = today();
$datime = todatime();
$totime = totime();

// Fetch po_id + item_id from the row for logging
$r = $connection->query("SELECT po_id, item_id FROM kp_po_items WHERE id='$row_id' LIMIT 1");
$info = $r ? $r->fetch_assoc() : null;
$po_id   = $info ? (int)$info['po_id']   : 0;
$item_id = $info ? (int)$info['item_id'] : 0;

// ✅ Update by unique row id
$update_order = "UPDATE kp_po_items SET price='$price', total=qty*('$price'-disc) WHERE id='$row_id'";
mysqli_query($connection, $update_order) or die("Could not update: " . mysqli_error($connection));

mysqli_query($connection,
    "INSERT INTO kp_po_hist(po_id,item_id,price,trans,trans_user,trans_day,trans_time,trans_datime)
     VALUES('$po_id','$item_id','$price','APPROVAL PRICE ADJUSTMENT','$user_id','$today','$totime','$datime')"
) or die(mysqli_error($connection));

logs("../../", 'back/kugura/', 'PURCHASE', $po_id, '-', '-', 'UPDATED PRICE TO ' . $price);
logs("",       'back/kugura/', 'PURCHASE', $po_id, '-', '-', 'UPDATED PRICE TO ' . $price);

echo '<div class="alert alert-success py-1 small"><i class="fa fa-check me-1"></i>Price updated successfully.</div>';
?>
