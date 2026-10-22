 
<?php 
include('../../inc/connection.php');
require("funcs.php");
$from = $_GET['from'];
$to = $_GET['to'];  
$counter = $_GET['counter']; 
$sum=0;
if ($counter=="ALL") {
	$line="";
}else{
	$line="counter='$counter'  AND";
}
?>

                     <table  id="data-table-fixed-header" class="table table-bordered">
					   <thead>
						   <!-- 	<tr>
						   	 	<th colspan="4" class="text-center">
						   	 		<h3><b><?php if ($counter=="ALL" ) { echo "ALL COUNTERS";  }else{echo get_counter($counter)." COUNTER"; }?>  SALES SUMMARY FROM <?PHP echo $from;?> TO <?php echo $to; ?></b></h3>
						   	 	</th> 
						   	</tr> --> 
						      <tr>
						      	 <th>Shift</th>
						      	 <th>Sale ID</th>
						      	 <th>Served By</th> 
						         <th>Total</th>	 
						      </tr>
						   </thead>
						   <tbody>
                          <?php 
                $sales = mysqli_query($connection,"SELECT amount,sale_id,user_id,shift FROM kp_sales WHERE $line shift BETWEEN '$from' AND '$to' ORDER BY sale_id DESC") or die(mysqli_error($connection));
						   if (mysqli_num_rows($sales) < 1) {
						   	?>
						   	<div class="alert alert-warning">
						   		<p>There are no recorded sales</p>
						   	</div>
						   	<?php
						   }else{
						   	while ($sa = mysqli_fetch_assoc($sales)) { 
						   		?>
						   		<tr>
						   			<td><?php echo $sa['shift']; ?></td> 
						   			<td><?php echo $sa['sale_id']; ?></td> 	 
						   			<td><?php echo get_user($sa['user_id']); ?></td>   
						   			<td><?php echo $sa['amount']; ?></td> 
						   		</tr>
						   		<?php
						   		$sum=$sum+$sa['amount'];
						   	} 
						   }
						   ?>

						
						
							</tbody>
							<tfoot>
								<tr>
									<td colspan="3" class="text-center" style="font-size: 18px;"> <b>TOTAL</b></td>
									<td style="font-size: 18px;"><b><?php echo $sum; ?></b></td>
								</tr>
							</tfoot>	</table>
                   <script type="text/javascript">
                   </script>