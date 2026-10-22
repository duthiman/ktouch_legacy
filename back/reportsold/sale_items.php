<?php 
include('../../inc/connection.php');
include('../clude/funcs.php');

if(isset($_REQUEST['id']))
  {

    $sale_id = $_REQUEST['id'];
     $dpt = $_REQUEST['dpt'];
    $total=0;
?>

     <div class="modal-content">
      <div class="modal-header"> 
           <h4 class="modal-title"> <i class="glyphicon glyphicon-user"></i>Receipt No: <?php echo $sale_id; ?> Items </h4> 
        </div> 
        <div class="modal-body">                     
           <div id="modal-loader" style="display: none; text-align: center;">
           <!-- ajax loader -->
           </div>
           <div class="table-responsive">
              <table class="table table-bordered">
               <thead>
                  <tr>
                     <th>Name</th>
                     <th>Quantity</th>
                     <th>Price</th>
 
                     <th>Total</th>
                  </tr>
               </thead>
               <tbody>

            <?php 
            $get_sale = "SELECT SUM(qty) AS tq,item_id,price FROM kp_sale_items WHERE sale_id='$sale_id'  GROUP BY item_id";
            $get_sale_r = mysqli_query($connection,$get_sale) or die("Could not fetch the order");
            
            while ($o = mysqli_fetch_assoc($get_sale_r)) {
               $item_id = $o['item_id'];
               
              $quantity=$o['tq']; 
              $price = $o['price'];
              $amount=$quantity*$price; 
 
                $total=$total+$amount;
              
              ?>
              <tr>
                 <td><?php echo get_item($item_id); ?></td>
                 <td><?php echo $quantity; ?></td>
                 <td><?php echo $price; ?></td>
 
                 <td><?php echo $amount; ?></td>
              </tr>
              <?php  } ?>
            
            <thead>
                  <tr>
                     <th colspan="3">Total</th>
                     <th><?php echo $total; ?></th>
                  </tr>
               </thead>
            </tbody>
    <?php
  
}?>
  
</div></div>
        </div> 
                        
      
                        
 








