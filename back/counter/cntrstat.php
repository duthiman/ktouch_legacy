<?php 
session_start();
include("../../inc/connection.php");
include('../clude/funcs.php');
include_once('funcs.php');
$user_id = isset($_SESSION['user_id']) ? (int)$_SESSION['user_id'] : 0;
$today = date('Y-m-d');
if($_REQUEST['st']==1){
  $shift=shift();
}else{
  $shift=$_REQUEST['shift'];
}
$nos=55;
?>
  <div class="row">
    <div class="col-md-12 mb-2">
      <div class="modal-content "> 
        <div class="modal-header text-center">
          <h1 style="color: darkblue;"><?PHP echo $shift; ?> SHIFT SUMMARY</h1>
        </div>
      </div> 
    </div>
      <div class="col-md-4">
        <div class="row">
          <div class="col-12">
            <div class="panel">
              <div class="modal-content "> 
                <div class="modal-header">
                  <h4 class="modal-title">  <?PHP echo $shift; ?> COUNTER PERFOMANCE</h4>
                </div> 
                <div class="modal-body">   
                  <div class="table-responsive">  
                    <table class="table table-striped">
                      <thead>
                        <tr>
                          <th>#</th>
                          <th>COUNTER</th>
                          <th>ORDERS</th>
                          <th>TOTAL ITEMS OUT</th>
                          <th>TOTAL SALES</th> 
                          <th>ACTION</th> 
                        </tr>
                      </thead>
                      <tbody>
                        <?php 
                        if ($shift==0) {
                           echo "<tr><td colspan='4'>SHIFT NOT OPEN</td</tr>";
                        }else{
                          $no=0;
                          $sum=0;
                          $msum=0;
                          $splitMessageTotal = ($user_id > 0) ? column_total('split_message','amount',"shift='$shift' AND claim_staff='$user_id' AND claim_status=1") : 0;
                          $purchase = mysqli_query($connection,"SELECT  count(sale_id) as tt,SUM(amount) as stotal,counter FROM kp_sales WHERE shift='$shift'  GROUP BY counter ORDER BY stotal DESC") or die('Error fetching the purchases.:'.mysqli_error($connection));
                          if (mysqli_num_rows($purchase) < 1) {
                           echo "<tr><td colspan='4'>NO SALES TODAY</td</tr>";
                          }else{
                            while ($pow = mysqli_fetch_assoc($purchase)){
                              $cntr_id = $pow['counter'];
                              $sales= $pow['stotal']; 
                              $tbills= $pow['tt'];  
                              $no=$no+1;
                              $sum=$sum+$sales;
                              $takeSummary = function_exists('kp_counter_stock_take_snapshot_summary') ? kp_counter_stock_take_snapshot_summary($connection, $cntr_id, $shift) : ['ok_count'=>0,'variance_count'=>0,'summary_id'=>0];
                              $summary_id = (int)($takeSummary['summary_id'] ?? 0);
                              ?>
                              <tr>
                                <td><b><?php echo $no; ?></b></td>
                                <td><b><?php echo get_column('kp_counters','name',"cntr_id='$cntr_id'"); ?></b></td>
                                <td><b><?php echo $tbills; ?></b></td>
                                <td><b><?php echo column_count('kp_sale_items','item_id',"stock_counter='$cntr_id' AND shift='$shift'"); ?></b></td>  
                                <td><b><?php echo $sales; ?></b></td>   
                                <td>
                                  <button class="btn btn-outline-success btn-sm" onclick="stocktakeview(<?php echo $cntr_id; ?>,'<?php echo $shift; ?>','ok',<?php echo $summary_id; ?>);">OK <?php echo (int)$takeSummary['ok_count']; ?></button>
                                  <button class="btn btn-outline-danger btn-sm" onclick="stocktakeview(<?php echo $cntr_id; ?>,'<?php echo $shift; ?>','variance',<?php echo $summary_id; ?>);">Variance <?php echo (int)$takeSummary['variance_count']; ?></button>
                                  <button class="btn btn-outline-info btn-sm" onclick="printsum(<?php echo $cntr_id; ?>,'<?php echo $shift; ?>',<?php echo $summary_id; ?>);"><i class='fa  fa-print' ></i> Print Summary</button>
                                </td>
                              </tr>
                              <?php
                            }
                          }
                        }
                        ?>
                      </tbody>
                      <tfoot>
                        <tr>
                          <td colspan="3" class="text-center"><h4>TOTAL</h4></td>
                          <td><h4><?php echo $sum; ?></h4></td>
                          <td><h4><?php echo $splitMessageTotal; ?></h4></td>
                          <td></td>
                        </tr>
                      </tfoot>
                    </table>
                  </div>
                </div>
              </div>
            </div>
          </div>
          <div class="col-12">
            <div class="panel">
              <div class="modal-content "> 
                <div class="modal-header">
                  <h4 class="modal-title">  <?PHP echo $shift; ?> PAYMENT SUMMARY</h4>
                </div> 
                <div class="modal-body">   
                  <div class="table-responsive">   
                      <table class="table table-bordered">
              
          <thead>
            <tr class="text-center ">
              <th colspan="3">
                <b><U><?PHP echo $today; ?> CASH BOX SUMMARY</U></b>
              </th>
            </tr> <tr>
                          <th>#</th>
                          <th>PAY MODE</th>
                          <th>AMOUNT</th> 
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
                $mode_total=column_total('kp_sale_payment',$col,"pay_date='$shift'");
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
                    </table>
                  </div>
                </div>
              </div>
            </div>
          </div>
        </div>
      </div>
      <div class="col-md-8">
        <div class="row"> 
          <!--   --> 
          <div class="col-md-3 "> 
            <div class="modal-content">  
              <div class="modal-body">       
                <div class="table-responsive">
                  <p style="margin: 0px 0px 0px 0px;"><?php echo sep($nos); ?></p>
                  <h3 style="margin: 0px 0px 0px 0px;  "><B>SALES COMPARISON</B></h3>
                  <p style="margin: 0px 0px 0px 0px;"><?php echo sep($nos); ?></p>
                  <table class="table">
                    <tbody>
                      <tr>
                        <td class="tt">TOTAL SALES</td>
                        <td class="tt"><?PHP echo column_total('kp_sales','amount',"shift='$shift'"); ?></td>
                      </tr> 
                     <tr>
                       <td class="tt">ITEM SALES</td>
                       <td class="tt"><?PHP echo column_total('kp_sale_items','total',"shift='$shift'"); ?></td>
                     </tr>

                     <tr>
                       <td class="tt" colspan="2"><?PHP if(column_total('kp_sale_items','total',"shift='$shift'")==column_total('kp_sales','amount',"shift='$shift'")){ ECHO "<H2 class='text-green'> SALE AMOUNTS MATCH </H2>";
                       }else{
                          ECHO "<H5 class='text-red text-center'> SALE AMOUNTS DOESNT MATCH </H5>";
                       } ?></td>
                     </tr>
                     </tbody>
                  </table> 
                </div>
              </div>
            </div> 
          </div> 
          <div class="col-md-3 "> 
            <div class="modal-content ">  
              <div class="modal-body">       
                <div class="table-responsive">
                    <p style="margin: 0px 0px 0px 0px;"><?php echo sep($nos); ?></p>
                    <h3 style="margin: 0px 0px 0px 0px;  "><B>STOCK REQUESTS</B></h3>
                    <p style="margin: 0px 0px 0px 0px;"><?php echo sep($nos); ?></p>
                    <table class="table">
                     <tbody>
                        <tr>
                       <td class="tt">TOTAL REQUEST</td>
                       <td class="tt"><?PHP ECHO column_count('kp_counter_req','req_id',"req_shift='$shift'"); ?></td>
                     </tr>
                     <tr>
                       <td class="tt">ISSUED REQUESTS</td>
                       <td class="tt"><?PHP ECHO column_count('kp_counter_req','req_id',"req_shift='$shift' AND issue_status=1"); ?></td>
                     </tr>
                     <tr>
                       <td class="tt">PENDING REQUESTS</td>
                       <td class="tt"><?PHP ECHO column_count('kp_counter_req','req_id',"req_shift='$shift' AND issue_status=2"); ?></td>
                     </tr>
                     </tbody>
                  </table> 
                </div>
              </div>
            </div> 
          </div>  
          <div class="col-md-3 "> 
            <div class="modal-content ">  
              <div class="modal-body">       
                <div class="table-responsive">
                  <p style="margin: 0px 0px 0px 0px;"><?php echo sep($nos); ?></p>
                  <h3 style="margin: 0px 0px 0px 0px;  "><B>STOCK TRANSFERS</B></h3>
                  <p style="margin: 0px 0px 0px 0px;"><?php echo sep($nos); ?></p>
                  <table class="table">
                    <tbody>  
                      <tr>
                        <td class="tt">TOTAL TRANSFERS</td>
                        <td class="tt"><?PHP ECHO column_count('kp_counter_trans','trans_id',"shift='$shift'"); ?></td> 
                      </tr>  
                      <tr>
                        <td class="tt">COMPLETE TRANSFERS</td>
                        <td class="tt"><?PHP ECHO column_count('kp_counter_trans','trans_id',"shift='$shift' AND status=1"); ?></td> 
                      </tr> 
                      <tr>
                        <td class="tt">PENDING TRANSFER</td>
                        <td class="tt"><?PHP ECHO column_count('kp_counter_trans','trans_id',"shift='$shift' AND status=2"); ?></td> 
                      </tr>
                     </tbody> 
                  </table> 
                </div>
              </div>
            </div> 
          </div>  
          <div class="col-md-3 "> 
            <div class="modal-content ">  
              <div class="modal-body">       
                <div class="table-responsive">
                    <p style="margin: 0px 0px 0px 0px;"><?php echo sep($nos); ?></p>
                    <h3 style="margin: 0px 0px 0px 0px;  "><B>RETURNS</B></h3>
                    <p style="margin: 0px 0px 0px 0px;"><?php echo sep($nos); ?></p>
                    <table class="table">
                      <tbody>  
                        <tr>
                          <td class="tt">TOTAL RETURNS</td>
                          <td class="tt"><?PHP ECHO column_count('kp_ret_items','item_id',"shift='$shift'"); ?></td> 
                        </tr>  
                        <tr>
                          <td class="tt">COMPLETE RETURNS</td>
                          <td class="tt"><?PHP ECHO column_count('kp_ret_items','item_id',"shift='$shift' AND confirm_status=1"); ?></td> 
                        </tr> 
                        <tr>
                          <td class="tt">PENDING RETURNS</td>
                          <td class="tt"><?PHP ECHO column_count('kp_ret_items','item_id',"shift='$shift' AND confirm_status=2"); ?></td> 
                        </tr>
                      </tbody> 
                    </table> 
                </div>
              </div>
            </div> 
          </div>  
          <div class="col-md-12 mt-2"> 
            <div class="modal-content ">   
              <div class="modal-header">  
                <h3 style="margin: 0px 0px 0px 0px;  "><B><?PHP echo $shift; ?> TOTAL ITEMS SOLD</B></h3> 
              </div>
              <div class="modal-body">       
                <div class="table-responsive" style="overflow-y:scroll; height: 500px;">
                    <table class="table table-valign-middle m-b-0">
                <thead>
                <tr><th colspan="5"><h4><b>TODAYS SALES REPORT</b></h4></th> </tr>
                  <tr>
                     <th>#:</th>
                     <th>ITEM:</th> 
                     <th>QTY</th> 
                     <th>TOTAL</th>                 
                     <th>ACTION</th>
                  </tr>
               </thead>
               <tbody>
                          <?php 
               $sales = mysqli_query($connection,"SELECT sum(qty) as tt,item_id,sum(total) AS tot FROM kp_sale_items WHERE shift='$shift' GROUP BY item_id ");
               if (mysqli_num_rows($sales) < 1) {
                ?>
                <div class="alert alert-warning">
                  <p>There are no recorded sales</p>
                </div>
                <?php
               }else{
                $no=0;
                $sums=0;
                $qt=0;
                while ($sa = mysqli_fetch_assoc($sales)) {
                  $item_id = $sa['item_id'];    
                  $qty=$sa['tt'];
                  $total=$sa['tot'];
                  $sums=$sums+$total; 
                  $qt= $qt+$qty;
                  $no= $no+1;

                  ?>
                  <tr>  
                  <td><?php echo $no;  ?></td>               
                    <td><?php echo get_column('kp_items','name',"item_id='$item_id'"); ?></td> 
                    <td><?php echo $qty; ?></td>
                    <td><?php echo $total; ?></td>
                    <td> <button class="btn btn-outline-info btn-sm" onclick="viewdets(<?php echo $sale_id; ?>);"><i class='fa  fa-eye' ></i> View Items</button></td>
                  </tr>
                  <?php 
                }
                ?>
                <tfoot>
                  <tr >
                    <th colspan="2" class="text-center">TOTAL</th> 
                    <th><?php echo $qt; ?></th>
                    <th><?php echo $sums; ?></th>
                    <th></th>
                  </tr>
                </tfoot>
                <?php
               }
               ?>

            
            
              </tbody>
            

                </table>
                </div>
              </div>
            </div> 
          </div>  
        </div>
      </div>
    </div>
		<div class="row">
    </div>
    <!-- end row -->
