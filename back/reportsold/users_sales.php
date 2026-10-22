<?php
include('../../inc/connection.php');
include('../clude/funcs.php');
$from = mysqli_real_escape_string($connection,$_GET['from']); 
$to = mysqli_real_escape_string($connection,$_GET['to']); 
$dpt = mysqli_real_escape_string($connection,$_GET['dpt']); 
?>
   <table class="datatable table table-valign-middle m-b-0">
               <thead>
                <tr><th colspan="2" class="text-center"><h3><b> <?php if ($dpt==1) { echo "BAR"; }elseif ($dpt==2) { echo "KITCHEN"; }else{ echo "ERROR";} ?> TOTAL ITEM SALES SUMMARY BETWEEN  <?PHP echo $from;?> TO  <?PHP echo $to;?> </b></h3></th> </tr>
                  <tr>
                     <th>Item</th>  
                     <th>Total</th>  
                  </tr>
               </thead>
               <tbody>
                          <?php 
                $sales = mysqli_query($connection,"SELECT sum(qty) as tt,item_id  FROM kp_sale_items WHERE dpt='$dpt'  AND shift BETWEEN '$from' AND '$to' GROUP BY item_id ORDER BY tt DESC") or die(mysqli_error($connection));
               if (mysqli_num_rows($sales) < 1) {
                ?>
                <div class="alert alert-warning">
                  <p>There are no recorded sales</p>
                </div>
                <?php
               }else{
                while ($sa = mysqli_fetch_assoc($sales)) { 
                  ?>
                  <tr>
                    <td><?php echo get_item($sa['item_id']); ?></td>   
                    <td><?php echo $sa['tt']; ?></td> 
                  </tr>
                  <?php 
                } 
               }
               ?>
             </tbody>
           </table> 
<table class="table table-bordered">
						  
<thead>
	<tr class=" text-center ">
		<td colspan="4" style="border-top: 2px solid #000;">
      <h3>
        <b><U>USERS <?php if ($dpt==1) { echo "BAR"; }elseif ($dpt==2) { echo "KITCHEN"; }else{ echo "ERROR";} ?> SALES SUMMARY DISTRIBUTION FOR SHIFT <?PHP echo $shift;?></U></b>
      </h3>
    </td>
	</tr>
  <tr>
  	 <th>Item Name:</th>
     <th>Qty</th>
  	 <th>price</th>
  	 <th>Total:</th>    
  </tr>
</thead>
<tbody>
<?php

$get_user = mysqli_query($connection,"SELECT user_id FROM kp_users WHERE role_id!=1") or die("Error fetching ussers.".mysqli_error($connection));
while ($sa = mysqli_fetch_assoc($get_user)) {
		$user_id = $sa['user_id'];
   
               $sales = mysqli_query($connection,"SELECT sum(qty) as qtt,price,item_id FROM kp_sale_items WHERE shift BETWEEN '$from' AND '$to'  AND dpt='$dpt' AND user_id='$user_id' GROUP BY item_id ORDER BY item_id DESC");
               if (mysqli_num_rows($sales) < 1) { 
               }else{
                $sum=0;
                ?>
                <tr>
      <th colspan="4"  style="font-size: 16px;"><?php echo get_user($user_id); ?> </th>    
    </tr>
                <?php
                while ($sa = mysqli_fetch_assoc($sales)) {
                  $price = $sa['price']; 
                  $item_id = $sa['item_id'];  
                  $qty=$sa['qtt']; 
                  $total= $qty*$price;
                  $sum=$sum+$total;

                  ?>
                  <tr>
                    <td><?php echo get_item($item_id); ?></td>                  
                    <td><?php echo $qty; ?></td> 
                    <td><?php echo $price; ?></td>
                    <td><?php echo $total; ?></td> 
                  </tr>
                  <?php 
                } 
                ?>
                <tr>
                  <th class="text-center" colspan="3" style="font-size: 16px;"><?php echo get_user($user_id);?> TOTAL</th>
                  <th  style="font-size: 16px;"><?php echo $sum;?></th>
                </tr> 
                <?php
              }
 
	} 
?>



</tbody>
</table>
 