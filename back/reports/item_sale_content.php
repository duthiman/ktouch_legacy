<?php
include('../../inc/connection.php');
include('../clude/funcs.php');
$start_date = mysqli_real_escape_string($connection,$_GET['start_date']);
$end_date = mysqli_real_escape_string($connection,$_GET['end_date']);
 $total_cs=0
?>
 <div>
<table id="data-table-combine" class="table table-striped table-bordered">
						  
<thead>
	<tr class="text-center ">
		<td colspan="7" style="border-top: 2px solid #000;">
      <h3>
        <b><U>ITEMS SUMMARY FROM <?PHP echo $start_date;?> to <?php echo $end_date;?></U></b>
      </h3>
    </td>
	</tr>
  <tr>
  	 <th style="width: 250px;">Item Name:</th>
     <th style="width: 20px;">Qty</th>
  	 <th style="width: 50px;">Price</th> 
     <th style="width: 50px;">Total Amount</th>   
  </tr>
</thead>
<tbody>
<?php 
$get_id = mysqli_query($connection,"SELECT SUM(total) AS sum_cash,SUM(qty) AS cs_qty,price,item_id FROM kp_sale_items WHERE shift BETWEEN '$start_date' AND '$end_date' GROUP BY item_id ORDER BY cs_qty DESC ") or die("Error fetching items.".mysqli_error($connection));
if (mysqli_num_rows($get_id) < 1) {
	?>
	<div class="alert alert-warning">
		<p>There are no item sales t</p>
	</div>
	<?php
}else{
while ($sa = mysqli_fetch_assoc($get_id)) {
	$item_id = $sa['item_id']; 
  $sumc = $sa['sum_cash'];
  $sumcqty = $sa['cs_qty'];
  $total_cs=$total_cs+$sumc;    
  ?>
	<tr>
			<td><?php echo get_item($item_id); ?></td>
			<td><?php echo $sumcqty; ?></td>
      <td><?php echo $sa['price']; ?></td>
      <td><?php echo $sumc; ?></td>
    </tr>
		<?php 	
	}
}
?>
</tbody>
<tfoot>
  <tr>
    <td colspan="3" class="text-center"><h3>TOTAL</h3></td>
    <td><h3><?php echo $total_cs; ?></h3></td>
  </tr>
</tfoot>
</table>
</div>
