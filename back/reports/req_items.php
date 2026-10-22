<?php 
include('../../inc/connection.php');
include('../clude/funcs.php');

if(isset($_REQUEST['id']))
  {

    $req_id = $_REQUEST['id'];  
?>

     <div class="modal-content">
      <div class="modal-header"> 
           <h4 class="modal-title"> <i class="glyphicon glyphicon-user"></i>Request No: <?php echo $req_id; ?> Items </h4> 
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
                     <th>REQUESTED</th> 
                     <th>ISSUED</th> 
                     <th>RECEIVED</th>  
                  </tr>
               </thead>
               <tbody>

            <?php
            $get_sale = "SELECT * FROM  kp_counter_req_items WHERE req_id='$req_id'";
            $get_sale_r = mysqli_query($connection,$get_sale) or die("Could not fetch the order");
            
            while ($o = mysqli_fetch_assoc($get_sale_r)) {
               $item_id = $o['item_id'];
               
              $quantity=$o['req_qty']; 
              $issueqty=$o['issue_qty'];
              $recqty=$o['rec_qty']; 
  
              ?>
              <tr>
                 <td><?php echo get_item($item_id); ?></td>
                 <td><?php echo $quantity; ?></td> 
                 <td><?php echo $issueqty; ?></td> 
                 <td><?php echo $recqty; ?></td> 
              </tr>
              <?php  } ?>
             
            </tbody>
    <?php
  
}?>
  
</div></div>
        </div> 
                        
      
                        
 








