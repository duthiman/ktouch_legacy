<?php
session_start();
include('../../inc/connection.php');
$sale_id = $_GET['id'];
$user_id = get_column("kp_sales","user_id","sale_id='$trans_id'"); 
$shift = get_column('kp_shift','shift',"1"); 
$num=column_count('kp_sale_items','item_id',"sale_id='$sale_id'");

   
  header("Location:../chaba/render_ret.php?sale_id=$sale_id&auto_print=1");
 
