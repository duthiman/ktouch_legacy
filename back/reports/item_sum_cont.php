 
<?php 
include('../../inc/connection.php');
require("funcs.php");
$from = $_GET['from'];
$to = $_GET['to'];   
$item_id = $_GET['item']; 
$sum=0;
 
$sales = mysqli_query($connection,"SELECT user_id,qty,sale_id,price,total FROM kp_sale_items WHERE shift BETWEEN '$from' AND '$to' AND item_id='$item_id' ORDER BY sale_id DESC") or die(mysqli_error($connection));
if (mysqli_num_rows($sales) < 1) {
	?>
	<div class="alert alert-warning">
		<p>There are no recorded sales</p>
	</div>

	<?php
}else{
	$salesss = mysqli_query($connection,"SELECT sum(qty) as tqty,sum(total) as tsum FROM kp_sale_items WHERE shift BETWEEN '$from' AND '$to' AND item_id='$item_id' GROUP BY item_id") or die(mysqli_error($connection));
	$ta = mysqli_fetch_assoc($salesss);
	?><table class="datatable table table-valign-middle m-b-0">
<thead>
	<tr><th colspan="5" class="text-center"><h3><b><u><?php echo get_item($item_id) ?> SALES SUMMARY FROM <?PHP echo $from;?> TO <?php echo $to; ?></u></b></h3></th> </tr>
  
</thead>
<tbody>
	<tr>
	 <td><h3><b>TOTAL ITEM SOLD</b></h3></td>
	 <td><h3><b><?php echo $ta['tqty']; ?></b></h3></td>
	 <td colspan="3"><h3><b><?php echo $ta['tsum']; ?></b></h3></td>
	</tr>
	<tr>
		<td colspan="5" class="text-center"><h3><b><u>ITEM SALE DISTRIBUTION</u></b></h3></td>
	</tr>
<tr>
  	 <th>Sale ID</th>
  	 <th>Served By</th> 
  	 <th>QTY</th> 
  	 <th>PRICE</th>
     <th>Total</th>	 
  </tr>
	<?php
	while ($sa = mysqli_fetch_assoc($sales)) { 
		?>
		<tr>
			<td><?php echo $sa['sale_id']; ?></td> 	 
			<td><?php echo get_user($sa['user_id']); ?></td> 
			<td><?php echo $sa['qty']; ?></td> 
			<td><?php echo $sa['price']; ?></td>   
			<td><?php echo $sa['total']; ?></td> 
		</tr>
		<?php
		$sum=$sum+$sa['total'];
	} 
	?>

</tbody>
<tfoot>
	<tr>
		<th colspan="4" class="text-center" style="font-size: 18px;"> <b>TOTAL</b></th>
		<th style="font-size: 18px;"><b><?php echo $sum; ?></b></th>
	</tr>
</tfoot>


	</table><?php
}
?>
