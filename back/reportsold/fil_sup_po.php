<?php
include('../../inc/connection.php');
include('../../inc/gen_funcs.php');
$start_date = mysqli_real_escape_string($connection,$_GET['start_date']);
$end_date = mysqli_real_escape_string($connection,$_GET['end_date']);
$sup_id = mysqli_real_escape_string($connection,$_GET['sup']);
 
?> 
<table class="table table-striped table-bordered">
  <thead>
    <tr class="text-center ">
      <td colspan="4" style="border-top: 2px solid #000;">
        <h3><b><U><?php echo get_supplier($sup_id); ?> SUPPLIES FROM <?PHP echo $start_date;?> TO <?php echo $end_date;?></U></b></h3>
      </td>
    </tr>
    <tr>
        <th>PO NO:</th> 
        <th>RAISED BY</th>
        <th>ORDER DATE</th> 
        <th>TOTAL</th>  
    </tr>
  </thead>
  <tbody>
    <?php
    $sales = mysqli_query($connection,"SELECT * FROM kp_po WHERE sup_id='$sup_id' AND ord_date BETWEEN '$start_date' AND '$end_date'  ORDER BY po_id ASC");
    if (mysqli_num_rows($sales) < 1) {
    ?>
    <tr>
      <td colspan="5">
        <div class="alert alert-warning">
          <p>There are no recorded Purchase Orders</p>
        </div>  
      </td>
    </tr>
    <?php
  }else{
    while ($sa = mysqli_fetch_assoc($sales)) {
      $po_id = $sa['po_id']; 
      $user_id = $sa['ord_staff'];
      $datime = $sa['ord_date'];
      $amount=$sa['amount'];
      $sum=$sum+$amount; 
    ?>
    <tr ondblclick="view(<?php echo $po_id; ?>)">
      <td><?php echo $po_id; ?></td>                   
      <td><?php echo get_user($user_id); ?></td>
      <td><?php echo $datime; ?></td>
      <td><?php echo $amount; ?></td> 
    </tr>
    <?php 
    }
    ?> 
    <tr>
      <th colspan="3" class="text-center"><h3>TOTAL</h3></th> 
      <th><h3><?php echo $sum; ?></h3></th> 
    </tr> 
    <?php
    }
    ?>
  </tbody>
</table>
</div>
