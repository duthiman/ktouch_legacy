 
<?php 
include('../../inc/connection.php');
require("funcs.php");
$start = $_GET['start']; 
$dpt = $_GET['dpt']; 
?>

                     <table class="datatable table table-valign-middle m-b-0">
						   <thead>
						   	<tr><th colspan="3" class="text-center"><h3><b> <?php if ($dpt==1) { echo "BAR"; }elseif ($dpt==2) { echo "KITCHEN"; }else{ echo "ERROR";} ?> SALES ON <?PHP echo $start;?></b></h3></th> </tr>
						      <tr>
						      	 <th>Sale ID</th>
						      	 <th>Served By</th> 
						         <th>Total</th>	 
						      </tr>
						   </thead>
						   <tbody>
                          <?php 
                $sales = mysqli_query($connection,"SELECT sum(total) as tc,sale_id,user_id FROM kp_sale_items WHERE dpt='$dpt'  AND shift='$start' GROUP BY sale_id ORDER BY sale_id DESC") or die(mysqli_error($connection));
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
						   			<td><?php echo $sa['sale_id']; ?></td> 	 
						   			<td><?php echo get_user($sa['user_id']); ?></td>   
						   			<td><?php echo $sa['tc']; ?></td> 
						   		</tr>
						   		<?php	
						   	} 
						   }
						   ?>

						
						
							</tbody>
						

					    	</table>
                   