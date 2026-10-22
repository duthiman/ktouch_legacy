<?php    
$counter= $id;
 
$nos=50;

$get_last = mysqli_query($connection,"SELECT sale_id FROM kp_sale_id") or die("Could not get the shift");
$row = mysqli_fetch_array($get_last);
$last=$row['sale_id'];

$check_shift = mysqli_query($connection,"SELECT * FROM kp_shift") or die("Could not get the shift".mysqli_error($connection));
$ts = mysqli_num_rows($check_shift); 


?>
 
<link href="../assets/css/thermal_print.css" rel="stylesheet" /> 
        <div class="receipt">
          <div id="bord">                            
            <p style="margin: 0px 0px 0px 0px;"><?php echo sep($nos); ?></p>
            <p style="margin: 0px 0px 0px 0px;"><?php echo sep($nos); ?></p>
            <h4 style="margin: 0px 0px 0px 0px; text-align: center;">
              <B><?php echo date('l jS F Y', strtotime($shift)); ?><BR>
                <?php echo get_column('kp_counters','name',"cntr_id='$counter'"); ?> SHIFT SUMMARY</B>
            </h4>
            <p style="margin: 0px 0px 0px 0px;"><?php echo sep($nos); ?></p>
            <table class="table">
              <tbody> 
                <tr>
                  <td><b>OPENING:</b></td>
                  <td><?php echo get_user(get_column('kp_counter_hist','open_by',"shift='$shift'"))?></td>
                  <td colspan="2">
                    <?php 
                    $new = date('l h:i:sa d-m-Y', strtotime(get_column('kp_counter_hist','opening',"shift='$shift'")));
                    echo $new;?>
                  </td>
              </tr>
              <tr>
                <td><b>CLOSING:</b></td>
                <td><?php echo get_user(get_column('kp_counter_hist','close_by',"shift='$shift'")) ?></td>
                <td colspan="2"><?php today();?></td>
              </tr>
              <tr>
                <td colspan="4"><b>ORDER NUMBER</b></td>
              </tr>
              <tr>
                <td colspan="2"><b>START NUMBER</b></td>
                <td colspan="2"><?php echo get_column('kp_counter_hist','open_ord',"shift='$shift'");?></td>
              </tr> 
              <tr>
                <td colspan="2"><b>END NUMBER</b></td>
                <td colspan="2"><?php echo get_column('kp_sale_id','sale_id',"1")-1;?></td>
              </tr>
              </tbody>
            </table>
            <p style="margin: 0px 0px 0px 0px;"><?php echo sep($nos); ?></p>
            <h4 style="margin: 0px 0px 0px 0px; text-align: center;"><B><?php echo get_column('kp_counters','name',"cntr_id='$counter'"); ?> STOCK  SALES SUMMARY BY DEPARTMENT</B></h4>
            <p style="margin: 0px 0px 0px 0px;"><?php echo sep($nos); ?></p>
          
            <table class="table">
              <tbody>
                <tr>
                  <td class="text-center"><b>DEPARTMENT</b></td>
                  <td class="text-center"><b>SALES</b></td>
                </tr>
                <tr>
                  <td>BAR:</td>
                  <td>
                    <?php 
                    $drinks=column_total('kp_sale_items','total',"dpt =1 AND  shift='$shift' AND  stock_counter='$counter'");
                    echo  $drinks;
                    ?>
                  </td>
                </tr>
                <tr>
                  <td>KITCHEN:</td>
                  <td>
                    <?php 
                    $food=column_total('kp_sale_items','total',"dpt =2 AND  shift='$shift' AND  stock_counter='$counter'");
                    echo  $food; 
                    ?>
                      
                    </td>
                  
                </tr>
                <tr>
                  <td><b>TOTAL</b></td>
                  <td><b><?php echo $food+$drinks;?></b></td>  
                </tr>
                <tr>
                  <td><b>CORRECTION</b></td>
                  <td></td> 
                </tr>
              </tbody>
            </table>
            <p style="margin: 0px 0px 0px 0px;"><?php echo sep($nos); ?></p>
            <h4 style="margin: 0px 0px 0px 0px; text-align: center;"><B>PAYMENT SUMMARY</B></h4>
            <p style="margin: 0px 0px 0px 0px;"><?php echo sep($nos); ?></p>
          <table class="table table-bordered">
            <thead>
              <tr class="text-center ">
                <th colspan="3">
                  <b><U><?PHP echo $today; ?> CASH BOX SUMMARY</U></b>
                </th>
              </tr> 
            </thead>
            <tbody>
              <?php
              $sum=0;
              $get_mode = "SELECT * FROM kp_pay_mode"; 
              $get_mode_r = mysqli_query($connection,$get_mode) or die("Could not fetch the payment mode");
              while ($o = mysqli_fetch_assoc($get_mode_r)) {  
                $mode_id=$o['pay_id']; 
                $name = $o['name']; 
                $col="mode".$mode_id;
                $mode_total=column_total('kp_sale_payment',$col,"pay_date='$shift' AND counter='$counter'");
                ?>
                <tr>
                   <td><?php echo $mode_id; ?></td>
                   <td><?php echo $name; ?></td> 
                   <td><?php echo  $mode_total;; ?></td>
                </tr>
                <?php  
                $sum=$sum+$mode_total;
              }  
              ?>
              <tr>
                <td colspan="2" class="text-center"><b>TOTAL</b></td>
                   <td><b><?php echo $sum; ?></b></td>  
                </tr> 
            </tbody>
          </table> 
          

            <p style="margin: 0px 0px 0px 0px;">*****************************************</p>
            <h4 style="margin: 0px 0px 0px 0px; text-align: center;"><B>BILLS SUMMARY</B></h4>
            <p style="margin: 0px 0px 0px 0px;">*****************************************</p>
          
          <table class="table">
            <tbody>
              <tr>
                <td class="text-center"><b>TOTAL BILLS</b></td>
                <td class="text-center" colspan="2"><h3><?php echo column_count('kp_sales','sale_id',"shift='$shift' AND counter='$counter'");?></h3></td>
              </tr>
            </tbody>
          </table>


            <p style="margin: 0px 0px 0px 0px;">*****************************************</p>
            <h4 style="margin: 0px 0px 0px 0px; text-align: center;"><B>DELETED BILLS</B></h4>
            <p style="margin: 0px 0px 0px 0px;">*****************************************</p>
          <table class="table">
            <tbody> 
                <?php
                  $delete = mysqli_query($connection,"SELECT * FROM kp_del_orders WHERe shift='$shift'") or die('Error fetching the staff data.:'.mysqli_error($connection));
                  if (mysqli_num_rows($delete) < 1) {
                   echo "<tr><td colspan='4'>No orders have been deleted Today</td</tr>";
                 }else{
                ?>
              <tr><td class="text-center"><b>Ord No</b></td>
                <td class="text-center"><b>Ord By</b></td>
                <td class="text-center"><b>App By</b></td>
                <td class="text-center"><b>AMT</b></td>
              </tr>
              <?php
                while ($row = mysqli_fetch_assoc( $delete )) {
                  $order_by = $row['user_id'];
                  $appr_by  = $row['appr_by'];
                  $order_no = $row['sale_id'];
                  $amount = $row['amount']; 
                  ?>
                  <tr>
                    <td><?php echo $order_no; ?></td>
                    <td><?php echo get_user($order_by); ?></td>
                    <td><?php echo get_user($appr_by); ?></td>
                    <td><?php echo $amount; ?></td>
                  </tr>
                  <?php
                  }
                }
              ?>
              <!--END DELETED BILLS -->
               </tbody>
          </table>
  


            <p style="margin: 0px 0px 0px 0px;">*****************************************</p>
            <h4 style="margin: 0px 0px 0px 0px; text-align: center;"><B><?php echo get_column('kp_counters','name',"cntr_id='$counter'"); ?> <BR>  RETURNED ITEMS</B></h4>
            <p style="margin: 0px 0px 0px 0px;">*****************************************</p>
          <table class="table">
            <tbody> 
                <?php
                  $delete = mysqli_query($connection,"SELECT * FROM kp_ret_items WHERE shift='$shift' AND stock_counter='$counter'") or die('Error fetching the staff data.:'.mysqli_error($connection));
                  if (mysqli_num_rows($delete) < 1) {
                   echo "<tr><td colspan='4'>No orders have been Returned Today</td</tr>";
                 }else{
                ?>
              <tr>
                <td class="text-center"><b>Id</b></td>
                <td class="text-center"><b>Item</b></td>
                <td class="text-center"><b>Ord By</b></td>
                <td class="text-center"><b>App By</b></td>
                <td class="text-center"><b>Qty</b></td>
                <td class="text-center"><b>AMT</b></td>
              </tr>
              <?php
                while ($row = mysqli_fetch_assoc( $delete )) {
                    $item_id = $row['item_id'];
                    $order_by = $row['user_id'];
                    $appr_by  = $row['confirm_by'];
                    $sale_id = $row['sale_id'];
                    $amount = $row['amount']; 
                    $qty = $row['qty']; 
                    ?>
              <tr>
                <td><?php echo $sale_id; ?></td>
                <td><?php echo get_item($item_id); ?></td>
                <td><?php echo get_user($order_by); ?></td>
                <td><?php echo get_user($appr_by); ?></td>
                <td><?php echo $qty; ?></td>
                <td><?php echo $amount; ?></td>
              </tr>
              <?php
                  }
                }
              ?>
              <!--END DELETED BILLS -->
               </tbody>
          </table>     

        <p style="margin: 0px 0px 0px 0px;">*****************************************</p>
          <h4 style="margin: 0px 0px 0px 0px; text-align: center;"><B><?php echo get_column('kp_counters','name',"cntr_id='$counter'"); ?>  STOCK OUT <BR> STAFF SUMMARY</B></h4>
         <p style="margin: 0px 0px 0px 0px;">*****************************************</p>
          <table class="table">
            <tbody>
              <!--START DELETED BILLS -->
              
                <?php
                $tt=0;
                  $purchase = mysqli_query($connection,"SELECT SUM(total) as stotal,user_id FROM kp_sale_items WHERE shift='$shift' AND counter='$counter' GROUP BY user_id ORDER BY stotal DESC") or die('Error fetching the purchases.:'.mysqli_error($connection));
                  if (mysqli_num_rows($purchase) < 1) {
                   echo "<tr><td colspan='4'>No Tokes Available today</td</tr>";
                 }else{
                ?>
              <tr>
                <td class="text-center"><b>STAFF</b></td>
                <td class="text-center"><b>BAR</b></td>
                <td class="text-center"><b>KITCHEN</b></td>
                <td class="text-center"><b>AMOUNT</b></td>  
              <?php
                while ($pow = mysqli_fetch_assoc($purchase)){
                    $user_id = $pow['user_id'];
                    $sales= $pow['stotal'];
                    $bar_amount=column_total('kp_sale_items','total',"shift='$shift' AND stock_counter='$counter' AND user_id='$user_id' AND dpt=1"); 
                    $kitchen_amount=column_total('kp_sale_items','total',"shift='$shift' AND stock_counter='$counter' AND user_id='$user_id' AND dpt=2"); 
                    $tt=$tt+$sales; 
                    ?>
              <tr>
                <td><b><?php echo get_user($user_id); ?></b></td>
                <td><b><?php echo $bar_amount; ?></b></td> 
                <td><b><?php echo $kitchen_amount; ?></b></td> 
                <td><b><?php echo $bar_amount+$kitchen_amount; ?></b></td> 
              </tr>
              <?php
                  }
                }
              ?>
              <!--END DELETED BILLS -->
               </tbody>
               <tfoot>
                 <tr>
                   <td colspan="3">TOTAL</td>
                   <td><b><?php echo $tt; ?></b></td>
                 </tr>
               </tfoot>
          </table>

          <p style="margin: 0px 0px 0px 0px;">*****************************************</p>
          <h4 style="margin: 0px 0px 0px 0px; text-align: center;"><B><?php echo get_column('kp_counters','name',"cntr_id='$counter'"); ?>  UNPAID BILLS <BR> STAFF SUMMARY</B></h4>
         <p style="margin: 0px 0px 0px 0px;">*****************************************</p>
          <table class="table">
            <tbody>
              <!--START DELETED BILLS -->
              
                <?php
                $tt=0;
                  $purchase = mysqli_query($connection,"SELECT sale_id,amount,user_id FROM kp_sales WHERE shift='$shift' AND counter='$counter' AND pay_status=2  ORDER BY sale_id DESC") or die('Error fetching the purchases.:'.mysqli_error($connection));
                  if (mysqli_num_rows($purchase) < 1) {
                   echo "<tr><td colspan='4'>No Tokes Available today</td</tr>";
                 }else{
                ?>
              <tr>
                <td class="text-center"><b>BILL NO</b></td>
                <td class="text-center" colspan="2"><b>STAFF</b></td> 
                <td class="text-center"><b>AMOUNT</b></td>  
              </tr>
                <?php
                while ($pow = mysqli_fetch_assoc($purchase)){
                    $sale_id = $pow['sale_id'];  
                    $user_id = $pow['user_id'];
                    $amount = $pow['amount'];  
                    ?>
              <tr>
                <td><b><?php echo $sale_id; ?></b></td> 
                <td colspan="2"><b><?php echo get_user($user_id); ?></b></td>
                <td><b><?php echo $amount; ?></b></td>  
              </tr>
              <?php
                  }
                }
              ?>
              <!--END DELETED BILLS -->
               </tbody> 
          </table>
          <p style="margin: 0px 0px 0px 0px;">*****************************************</p>
            <h4 style="margin: 0px 0px 0px 0px; text-align: center;"><B><?php echo get_column('kp_counters','name',"cntr_id='$counter'"); ?> <BR> ITEM STOCK OUT TOTAL</B></h4>
          <p style="margin: 0px 0px 0px 0px;">*****************************************</p>
          <table class="table">
            <tbody> 
                <?php
                $ss=0;
                  $delete = mysqli_query($connection,"SELECT sum(qty) as qty,sum(total) as tt,item_id,price FROM kp_sale_items WHERE shift='$shift' AND stock_counter='$counter' GROUP BY item_id ORDER BY qty DESC") or die('Error fetching the staff data.:'.mysqli_error($connection));
                  if (mysqli_num_rows($delete) < 1) {
                   echo "<tr><td colspan='4'>No orders have been deleted</td</tr>";
                 }else{
                ?>
              <tr> 
                <td class="text-center"><b>Item</b></td>
                <td class="text-center"><b>Qty</b></td>  
                <td class="text-center"><b>Price</b></td>
                <td class="text-center"><b>Total</b></td>
              </tr>
              <?php
                while ($row = mysqli_fetch_assoc( $delete )) {
                    $item_id = $row['item_id'];
                    $price = $row['price'];
                    $total  = $row['tt']; 
                    $qty = $row['qty'];
                      $ss=$ss+$total;
                    ?>
              <tr> 
                <td><?php echo get_item($item_id); ?></td> 
                <td><?php echo $qty; ?></td>
                <td><?php echo $price; ?></td>
                <td><?php echo $total; ?></td>
              </tr>
              <?php
                  }
                }
              ?>
              <tr>
                <td colspan="3" class="text-center"><b>TOTAL</b></td>
                <td><b><?php echo $ss; ?></b></td>
              </tr>
              <!--END DELETED BILLS -->
               </tbody>
          </table>   


          <p style="margin: 0px 0px 0px 0px;">*****************************************</p>
            <h4 style="margin: 0px 0px 0px 0px; text-align: center;"><B><?php echo get_column('kp_counters','name',"cntr_id='$counter'"); ?> <BR>STOCK TRANSFERS</B></h4>
            <p style="margin: 0px 0px 0px 0px;">*****************************************</p>
          <table class="table">
            <tbody> 
                <?php
                  $transfer_items = mysqli_query($connection,"SELECT sum(qty) as qty ,item_id,trans_id,status,s2s  FROM kp_counter_trans_items WHERE shift='$shift'  GROUP BY item_id ORDER BY qty DESC ") or die('Error fetching the staff data.:'.mysqli_error($connection));
                  if (mysqli_num_rows($transfer_items) < 1) {
                   echo "<tr><td colspan='2'>NO STOCK TRANSFERED TODAY</td</tr>";
                 }else{
                ?>
              <tr> 
                <td class="text-center"><b>ITEM</b></td>
                <td class="text-center"><b>QTY</b></td>
                <td class="text-center"><b>COUNTER FROM</b></td>
                 <td class="text-center"><b>USER FROM</b></td>
                <td class="text-center"><b>COUNTER TO</b></td>
                 <td class="text-center"><b>USER TO</b></td>     
              </tr>
              <?php
                while ($row = mysqli_fetch_assoc( $transfer_items )) {
                    $item_id = $row['item_id']; 
                    $qty = $row['qty'];
                    $trans_id=$row['trans_id']; 
                    $status=$row['status'];
                    $s2s=$row['s2s'];

                    $transfer = mysqli_query($connection,"SELECT *  FROM kp_counter_trans WHERE trans_id='$trans_id'") or die('Error fetching the staff data.:'.mysqli_error($connection));
                    $tow = mysqli_fetch_assoc($transfer);
                    $cntr_to=$tow['cntr_to'];
                    $user_to=$tow['user_to'];
                    $cntr_from=$tow['cntr_from'];
                    $user_from=$tow['user_from'];
                    ?>
              <tr> 
                <td><?php echo get_item($item_id); ?></td> 
                <td><?php echo $qty; ?></td> 
                <td><?php echo get_counter($cntr_to); ?></td> 
                <td><?php echo get_user($user_from); ?></td> 
                <td><?php echo get_counter($cntr_to); ?></td> 
                <td><?php echo get_user($user_to); ?></td> 


              </tr>
              <?php
                  }
                }
              ?>
              <!--END DELETED BILLS -->
               </tbody>
          </table> 

          <p style="margin: 0px 0px 0px 0px;">*****************************************</p>
            <h4 style="margin: 0px 0px 0px 0px; text-align: center;"><B><?php echo get_column('kp_counters','name',"cntr_id='$counter'"); ?>  TOTS GENERATED</B></h4>
            <p style="margin: 0px 0px 0px 0px;">*****************************************</p>
          <table class="table">
            <tbody> 
                <?php
                  $reprints = mysqli_query($connection,"SELECT *  FROM kp_tot_items WHERE shift='$shift' AND counter='$counter'") or die('Error fetching the staff data.:'.mysqli_error($connection));
                  if (mysqli_num_rows($reprints) < 1) {
                   echo "<tr><td colspan='2'>No orders have been Reprinted</td</tr>";
                 }else{
                ?>
              <tr> 
                <td class="text-center"><b>TOT ID</b></td>
                <td class="text-center"><b>ITEM</b></td>
                <td class="text-center"><b>QTY</b></td>
                <td class="text-center"><b>CASHIER</b></td>     
              </tr>
              <?php
                while ($row = mysqli_fetch_assoc( $reprints)) {
                    $tot_id = $row['tot_id']; 
                    $item_id = $row['tot_item_id']; 
                    $user_id = $row['user_id']; 
                    $issue_user = $row['issue_user'];
                    $qty = $row['tots'];
                    ?>
              <tr> 
                <td><?php echo $tot_id; ?></td> 
                <td><?php echo get_item($item_id); ?></td>
                <td><?php echo $qty; ?></td>  
                <td><?php echo get_user($user_id); ?></td> 
              </tr>
              <?php
                  }
                }
              ?>
              <!--END DELETED BILLS -->
               </tbody>
          </table>

          <p style="margin: 0px 0px 0px 0px;">*****************************************</p>
            <h4 style="margin: 0px 0px 0px 0px; text-align: center;"><B><?php echo get_column('kp_counters','name',"cntr_id='$counter'"); ?>  REQUISITIONED ITEMS</B></h4>
            <p style="margin: 0px 0px 0px 0px;">*****************************************</p>
          <table class="table">
            <tbody> 
                <?php
                  $reprints = mysqli_query($connection,"SELECT *  FROM kp_counter_req_items WHERE req_shift='$shift' AND counter='$counter'") or die('Error fetching the staff data.:'.mysqli_error($connection));
                  if (mysqli_num_rows($reprints) < 1) {
                   echo "<tr><td colspan='2'>No orders have been Reprinted</td</tr>";
                 }else{
                ?>
              <tr> 
                <td class="text-center"><b>REQ ID</b></td>
                <td class="text-center"><b>REQ ITEM</b></td>
                <td class="text-center"><b>REQ QTY</b></td>
                <td class="text-center"><b>CASHIER</b></td>  
                <td class="text-center"><b>STATUS</b></td>    
              </tr>
              <?php
                while ($row = mysqli_fetch_assoc( $reprints)) {
                    $sale_id = $row['req_id']; 
                    $item_id = $row['item_id']; 
                    $req_user = $row['req_user']; 
                    $issue_user = $row['issue_user'];
                    $req_qty = $row['req_qty'];
                    ?>
              <tr> 
                <td><?php echo $sale_id; ?></td> 
                <td><?php echo get_item($item_id); ?></td>
                <td><?php echo $req_qty; ?></td>  
                <td><?php echo get_user($req_user); ?></td>  
                <td><?php echo ""; ?></td> 
              </tr>
              <?php
                  }
                }
              ?>
              <!--END DELETED BILLS -->
               </tbody>
          </table>  

          <p style="margin: 0px 0px 0px 0px;">*****************************************</p>
            <h4 style="margin: 0px 0px 0px 0px; text-align: center;"><B>REPRINTED BILLS</B></h4>
            <p style="margin: 0px 0px 0px 0px;">*****************************************</p>
          <table class="table">
            <tbody> 
                <?php
                  $reprints = mysqli_query($connection,"SELECT *  FROM kp_sale_reprint WHERE shift='$shift'") or die('Error fetching the staff data.:'.mysqli_error($connection));
                  if (mysqli_num_rows($reprints) < 1) {
                   echo "<tr><td colspan='2'>No orders have been Reprinted</td</tr>";
                 }else{
                ?>
              <tr> 
                <td class="text-center"><b>SALE ID</b></td>
                <td class="text-center"><b>SALE BY</b></td> 
                <td class="text-center"><b>APPROVED BY</b></td>   
              </tr>
              <?php
                while ($row = mysqli_fetch_assoc( $reprints)) {
                    $sale_id = $row['sale_id']; 
                    $user_id = $row['user_id']; 
                    $appr_by = $row['appr_by'];
                    ?>
              <tr> 
                <td><?php echo $sale_id; ?></td> 
                <td><?php echo get_user($user_id); ?></td> 
                <td><?php echo get_user($appr_by); ?></td> 
              </tr>
              <?php
                  }
                }
              ?>
              <!--END DELETED BILLS -->
               </tbody>
          </table>  
         
        </div>
      </div> 
  