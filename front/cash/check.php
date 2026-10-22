<?php
session_start();
include('../../inc/connection.php');
include('funcs.php');
$shift = get_column('kp_shift','shift',"1");
$cntr_id=$_REQUEST['id'];
$st=$_REQUEST['st'];
if ($st==1) {
  $counters = mysqli_query($connection,"SELECT * FROM kp_sales  WHERE  shift='$shift' AND counter='$cntr_id'") or die('Error fetching the purchases.:'.mysqli_error($connection));
}else{
  $counters = mysqli_query($connection,"SELECT SUM(total) as total,sale_id,user_id FROM kp_sale_items  WHERE  shift='$shift' AND counter='$cntr_id' GROUP BY sale_id") or die('Error fetching the purchases.:'.mysqli_error($connection));
}
?> 

<table class="table table-striped">
  <thead>
    <tr> 
      <th>#</th>
      <th>SALE ID</th> 
      <th>WAITER</th> 
      <th>SALE AMOUNT</th> 
      <th>ITEMS AMOUNT</th>  
    </tr>
  </thead>
  <tbody>
    <?php   
      $no=0;  
      $sum=0;
      
      while ($pow = mysqli_fetch_assoc($counters)){
        $sale_id = $pow['sale_id'];   
        $user_id = $pow['user_id']; 
        $no=$no+1;
        if ($st==1) { 
          $amount=$pow['amount'];
          $item_amount=column_total('kp_sale_items','total',"sale_id='$sale_id'");
        }else{
          $amount=column_total('kp_sales','amount',"sale_id='$sale_id'");
          $item_amount=$pow['total'];
        }
          
        ?>
        <tr>   
          <td><b><?php echo $no; ?></b></td> 
          <td><b><?php echo $sale_id; ?></b></td> 
          <td><b><?php echo get_column('kp_users','username',"user_id='$user_id'"); ?></b></td>
          <td><b><?php echo $amount; ?></b></td>
          <td><b><?php echo $item_amount; ?></b></td>  
        </tr>
        <?php
      } 
    ?>
  </tbody> 
</table>
   