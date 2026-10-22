<?php
  session_start();
  include('../../inc/connection.php');
  $id=$_GET['id'];  

  $get_food = "SELECT * FROM kp_items WHERE sub_id='$id' AND del_status=2  ORDER BY name ASC";
  $get_food_r = mysqli_query($connection,$get_food) or die("Could not fetch food");
    while ($f = mysqli_fetch_assoc($get_food_r)) {
      $item_id = $f['item_id'];
      $name = $f['name'];
?>
  <button   data-toggle="modal" data-target="#modal" data-id="<?php echo $item_id; ?>" class="btn btn-inverse m-r-5 m-b-5" id="getItem" style="font-weight: bold;
        color: #FFFFFF !important;
        font-size: 13px; 
        word-wrap:break-word; 
        width: 70px; 
        white-space:normal;  
        font-size: auto; 
        height:70px; 
        background: #6F9F07; 
        border: 2px solid #6F9F07; 
        padding-left: 0px; 
        padding-right: 0px; 
        margin: 5px 5px 5px 5px; 
        border-radius: 10px; ">
        <?php echo $name; ?>
  </button>
<?php
  }
?> 


 