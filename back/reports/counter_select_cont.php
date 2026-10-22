 
<?php 
include('../../inc/connection.php');
require("../clude/funcs.php");
$from = $_GET['from'];
$to = $_GET['to'];  
$counter = $_GET['counter']; 
$total_cs=0;
?> 
<div class="panel-heading"> 
  <div class="pull-right">
    <a href="javascript:;" onclick="window.print()" class="btn btn-sm btn-success m-b-10  hidden-print" ><i class="fa fa-print m-r-5"></i> Print</a>
  </div>
  <h3><b style="color: white;"><?php echo get_counter($counter) ?> COUNTER ITEM SALES SUMMARY FROM <?PHP echo $from;?> TO <?php echo $to; ?></b></h3>
</div>
<div class="panel-body">
  <table  id="data-table-fixed-header" class="table table-bordered m-5">   
	  <thead> 
      <tr>
      	<th>ITEM</th>
      	<th>QTY</th> 
      	<th>PRICE</th>
        <th>TOTAL</th>	 
      </tr>
	  </thead>
	   <tbody>
	      <?php  
				$sales = mysqli_query($connection,"SELECT SUM(total) AS sum_cash,SUM(qty) AS cs_qty,price,item_id FROM kp_sale_items  WHERE counter='$counter'  AND shift BETWEEN '$from' AND '$to' GROUP BY item_id ORDER BY total DESC") or die(mysqli_error($connection));
				   if (mysqli_num_rows($sales) < 1) {
				   	?>
				   	<tr>
				   		<td>
				   		<p>There are no recorded sales</p></td>
				   	</tr>
				   	<?php
				   }else{
						   while ($sa = mysqli_fetch_assoc($sales)) {
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
<div>
	 
            
                   