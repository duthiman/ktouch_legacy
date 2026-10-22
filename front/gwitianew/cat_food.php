<?php
session_start();
include('../../inc/connection.php');
include('funcs.php');
$id=$_GET['id']; 
$user_id=$_SESSION['user_id'];
$counter = get_column('kp_users','counter',"user_id='$user_id'"); 
$dpt = get_column('kp_counters','dpt',"cntr_id='$counter'");  
$get_food = "SELECT * FROM kp_items WHERE cat_id='$id' AND dpt='$dpt' AND stock=1 ORDER BY name ASC";
$get_food_r = mysqli_query($connection,$get_food) or die("Could not fetch food");
while ($f = mysqli_fetch_assoc($get_food_r)) {
  $item_id = $f['item_id'];
  $name = $f['name'];
?>
  <button   data-toggle="modal" data-target="#modal" data-id="<?php echo $item_id; ?>" class="btn btn-inverse m-r-5 m-b-5" id="getItem" style="font-weight: bold;
    color: #FFFFFF !important;
    font-size: 12px; 
    word-wrap:break-word; 
    width: 80px; 
    white-space:normal;  
    font-size: auto; 
    height:80px; 
    background: gray;
border: 2px solid #dbc9c9;
    /*background: #6F9F07; 
    border: 2px solid #6F9F07; */
    padding-left: 0px; 
    padding-right: 0px; 
    margin: 5px 5px 5px 5px; 
    border-radius: 10px;
     line-height: normal; ">
    <?php echo $name; ?>
  </button>
<?php
  }
?> 


 