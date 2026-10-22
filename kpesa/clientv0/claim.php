<?php
session_start();
include('../../inc/connection.php'); 
include('../../inc/gen_funcs.php');  
//$connection = connectidentitiDB();
$user_id=$_REQUEST['user_id'];
$id=$_REQUEST['id']; 
if ( empty($id) or $id==0) {
  ?>
  <script type="text/javascript">
    var hulla = new hullabaloo();
    hulla.send("You have to select message!!!!", "warning");
  </script>
  <?php
}else{
  $track=mysqli_query($connection,"INSERT INTO message_claim(user_id,msg_id) VALUES('$user_id','$id')");
  if ($track) {
    $edit_item = "UPDATE split_message SET claim_status=1,claim_staff ='$user_id' WHERE id='$id'";
    $edit_item_r = mysqli_query($connection,$edit_item);
    if (!$edit_item_r) {
      $track=mysqli_query($connection,"DELETE FROM message_claim WHERE user_id=$user_id AND msg_id=$id) VALUES('$user_id','$id')") or die("Could not track the item".mysqli_error($connection)); 

         $response["status"] =0;
         $response["message"] ="Message Not claimed, Ask Admin to check issue"; 
         echo json_encode($response);
    }else{ 
     $response["status"] =1;
     $response["message"] ="Message claimed Success!!! "; 
     echo json_encode($response);
    }
  }else{ ;
   $response["status"] =0;
   $response["message"] ="Contact Cashier!!!Error Tracking claims"; 
   echo json_encode($response);
  }
}
?>