<?php
session_start();
include('../../inc/connection.php');
include("../clude/funcs.php");
$user_id = $_SESSION['user_id'];
$req_id = $_REQUEST['req_id'];
$qty = $_REQUEST['qty'];
$item_id = $_REQUEST['item']; 

if (empty($_REQUEST['portions'])) {
  $pqty = $_REQUEST['qty'];
}else{
  $pqty = $_REQUEST['portions'];
}

 
  $update_bar_track = mysqli_query($connection,"INSERT INTO kp_store_stock_track(item_id,req_id,user_id,qty,status,shift) VALUES('$item_id','$req_id','$user_id','$qty','OUT',CURRENT_DATE)") or die(mysqli_error($connection));

  $adjust_quantity = mysqli_query($connection, "UPDATE kp_items SET store_qty = store_qty+'$qty' WHERE item_id='$item_id'") or die(mysqli_error($connection));

  $update = mysqli_query($connection,"UPDATE kp_req_items SET rec_qty='$pqty',rec_date=CURRENT_DATE,rec_user='$user_id',rec_status=1  WHERE req_id='$req_id' AND item_id='$item_id'") or die(mysqli_error($connection)."Failed to update the po");
 
  if (!$update) {
  		?>
    <div class="alert alert-danger">
      <strong>ERROR ISSUING <?php echo get_item($item_id); ?> CONTACT ADMINISTRATOR</strong>
    </div>
    <?php
  }else{
    ?>
    <div class="alert alert-success">
      <strong><?php echo $qty." ". get_item($item_id); ?> ISSUED SUCCSSFULLY!!!</strong>
    </div>
    <script type="text/javascript">
    	window.setTimeout(function(){
        window.location.reload();
      }, 50);
    </script>
  <?php
  } 
?>