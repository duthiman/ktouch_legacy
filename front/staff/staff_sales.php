<?php
session_start();
include('../../inc/connection.php');
include('funcs.php');
$user_id = mysqli_real_escape_string($connection,$_GET['id']);
$cashier=$_SESSION['user_id'];
$counter=get_column('kp_users','counter',"user_id='$cashier'");  
 
$staff = mysqli_query($connection,"SELECT * FROM kp_sales WHERe pay_status=2 AND user_id='$user_id'  ORDER BY sale_id DESC") or die('Error fetching the staff data.:'.mysqli_error($connection));
if (mysqli_num_rows($staff) < 1) {
  echo "There are no staff sales in the system at the moment";
}else{
  ?>
  <table class="table table-striped">
    <thead>
      <tr>
        <th colspan="4" style="font-size: 20px;" class="text-center">
          <?php echo get_user($user_id) ." UNPAID BILLS"; ?> 
          <a href="../staff/staff_shift.php?id=<?php echo $user_id; ?>" class="btn btn-sm btn-warning pull-right" style="font-weight: bold; color: #FFFFFF !important;font-size: 14px;height: 35px;background: #000;border: 4px solid #e0bd65;padding-left: 5px;border-radius: 5px;">PRINT SUMMARY</a>
        </th> 
      </tr>
      <tr>
        <th>ORDER NO:</th>
        <th>SHIFT</th>
        <th>STAFF</th>
        <th>COUNTER</th>
        <th>AMOUNT</th>
        <th>ACTION</th>
      </tr>
    </thead>
    <tbody>
      <?php
      while ($row = mysqli_fetch_assoc($staff)) {
        $user_id = $row['user_id'];
        $sale_id = $row['sale_id'];
         $shift = $row['shift']; 
        $totals = $row['amount'];; 
        $scount = $row['counter'];
        ?>
        <tr>
          <td><?php echo $sale_id; ?></td>
          <td><?php echo $shift; ?></td>
          <td><?php echo get_user($user_id);?></td>
          <td><?php echo get_column('kp_counters','name',"cntr_id='$scount'");?></td>
          <td><?php echo $totals; ?></td>
          <th>
            <?php 
            if ($counter==$scount) {
              ?>
              <a href="bill_clear.php?id=<?php echo $sale_id; ?>" style="font-weight: bold; color: #FFFFFF !important;font-size: 12px;height: 35px;background: #000;border: 4px solid #e0bd65;padding-left: 5px;border-radius: 5px;" class="btn btn-md">CLEAR BILL</a>
              <a href="../cust/customers.php?id=<?php echo $sale_id; ?>" style="font-weight: bold; color: #FFFFFF !important;font-size: 12px;height: 35px;background: #000;border: 4px solid #e0bd65;padding-left: 5px;border-radius: 5px;" class="btn btn-md">ADD TO ACCOUNT</a>
            <?php
            }
            ?>
          </th>
        </tr>
      <?php
      }
      ?>
    </tbody>
  </table>
  <?php
  }
?> 