<?php
include('../../inc/connection.php');
include('../clude/funcs.php');
$start_date = mysqli_real_escape_string($connection,$_GET['start_date']);
$end_date = mysqli_real_escape_string($connection,$_GET['end_date']);

if ($start_date==$end_date) {
  $tt="FROM ".$start_date." TO ".$end_date; 
}else{
  $tt="ON ".$start_date ; 
}

$total_cs=0
 
?>
 <div>
<table id="data-table-combine" class="table table-striped table-bordered">
						  
<thead>
	<tr class="text-center ">
		<td colspan="8" style="border-top: 2px solid #000;">
      <h3>
        <b><U>HAPPY HOUR ITEMS SUMMARY <?php echo $tt; ?></U></b>
      </h3>
    </td>
	</tr>
  <tr>
  	 <th style="width: 250px;">Item Name:</th>
     <th style="width: 50px;">Price</th>
     <?php
                      $get_counter = mysqli_query($connection,"SELECT * FROM kp_counters WHERE del_status=2 ") or die("Could not get any items");
                      foreach ($get_counter  as $cow) {
                      ?>
                      <th><?php echo $cow['name']; ?></th>
                      <th><?php echo $cow['name']; ?> CASH</th>
                    <?php } ?> 
     <th style="width: 20px;">Qty</th> 
     <th style="width: 50px;">Total Amount</th>   
  </tr>
</thead>
<tbody>
<?php  
$get_id = mysqli_query($connection,"SELECT SUM(total) AS sum_cash,SUM(qty) AS cs_qty,kp_sale_items.price,kp_sale_items.item_id FROM kp_sale_items INNER JOIN kp_items on kp_items.item_id=kp_sale_items.item_id WHERE hour=2 AND kp_sale_items.price != kp_items.price AND shift BETWEEN '$start_date' AND '$end_date' GROUP BY kp_sale_items.item_id ORDER BY cs_qty DESC ") or die("Error fetching items.".mysqli_error($connection));
if (mysqli_num_rows($get_id) < 1) {
	?>
	<div class="alert alert-warning">
		<p>There are no item sales t</p>
	</div>
	<?php
}else{
   foreach ($get_counter  as $cow) {
       $counter= $cow['cntr_id']; 
       $csum="sum";
       $counter.$csum=0;
     }
while ($sa = mysqli_fetch_assoc($get_id)) {
	$item_id = $sa['item_id']; 
  $sumc = $sa['sum_cash'];
  $sumcqty = $sa['cs_qty'];
  $total_cs=$total_cs+$sumc;    
  ?>
	<tr>
		<td><?php echo get_item($item_id); ?></td>
    <td><?php echo $sa['price']; ?></td>
    <?php
    foreach ($get_counter  as $cow) {
       $counter= $cow['cntr_id']; 
       
      $get_cqty = mysqli_query($connection,"SELECT SUM(total) AS sum_cash,SUM(qty) AS cs_qty,kp_sale_items.price,kp_sale_items.item_id FROM kp_sale_items INNER JOIN kp_items on kp_items.item_id=kp_sale_items.item_id WHERE hour=2 AND kp_sale_items.price != kp_items.price AND kp_sale_items.item_id='$item_id' AND counter='$counter' AND shift BETWEEN '$start_date' AND '$end_date' GROUP BY kp_sale_items.item_id ORDER BY cs_qty DESC ") or die("Error fetching items.".mysqli_error($connection));
      $ka = mysqli_fetch_assoc($get_cqty);

      $counter.$csum=$counter.$csum+$ka['sum_cash'];
    ?>
      <td><?php echo $ka['cs_qty']; ?></td>
      <td><?php echo $ka['sum_cash']; ?></td>
    <?php } ?>
		<td><?php echo $sumcqty; ?></td>
    <td><?php echo $sumc; ?></td>
  </tr>
	<?php 	
	}
}
?>
</tbody>
<tfoot>
  <tr>
    <td colspan="2" class="text-center"><h3>TOTAL</h3></td>
     <?php
    foreach ($get_counter  as $cow) {
       $counter= $cow['cntr_id']; 
       ?>
    <td colspan="2" class="text-right"><h3>
      <?php 
      $get_cqty = mysqli_query($connection,"SELECT SUM(total) AS sum_cash ,kp_sale_items.price,kp_sale_items.item_id FROM kp_sale_items INNER JOIN kp_items on kp_items.item_id=kp_sale_items.item_id WHERE hour=2 AND kp_sale_items.price != kp_items.price AND counter='$counter' AND shift BETWEEN '$start_date' AND '$end_date'") or die("Error fetching items.".mysqli_error($connection));
      $ka = mysqli_fetch_assoc($get_cqty);
      echo $ka['sum_cash']; ?>
    </h3></td>
    <?php
  }
    ?>
    <td></td> 
    <td><h3><?php echo $total_cs; ?></h3></td>
  </tr>
</tfoot>
</table>
</div>
