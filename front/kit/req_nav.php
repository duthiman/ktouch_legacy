<?php
session_start();
include('../../inc/connection.php');
$id = $_GET['id']
?>
 
<?php
$get_sub_categories = mysqli_query($connection,"SELECT * FROM kp_sub_cat WHERE cat_id='$id'");
if (mysqli_num_rows($get_sub_categories) < 1) {
}else{
?>
<div class="navbar-header" style="margin-left: 100px; margin-top: 5PX;">
  <?php
  while ($sub = mysqli_fetch_assoc($get_sub_categories)) {
    $sub_id = $sub['sub_id'];
    ?>
    <button onclick="load_sub_food('<?php echo $sub_id; ?>')" style="font-family: 'comic sans ms';
      font-weight: bold;
      color: #FFFFFF !important;
      font-size: 14px; 
      word-wrap:break-word; 
      width: auto; 
      white-space:normal;  
      font-size: auto; 
      height:35px; 
      background: #000000; 
      border: 3px solid #B10421; 
      padding-left: 5px; 
      padding-right: 5px; 
      margin: 5px 5px 5px 5px; 
       border-radius: 10px 0px 10px 0px; "><?php echo $sub['name']; ?> </button>
      <?php
      }
    }
    ?>
</div>
 