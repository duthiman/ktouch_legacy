<?php 
require("../clude/head.php");
$no=0;

?>
 
<!-- begin #page-container -->
<div id="page-container" class="fade page-sidebar-fixed page-header-fixed">
  <!-- begin #header -->  
  <?php 
    require("../clude/header.php");
    
  ?>
  <!-- end #header -->  
  <!-- begin #sidebar -->
  <?php 
    require("../clude/menu.php");
  ?>
  <!-- end #sidebar -->
		<!-- begin #content -->
		<div id="content" class="content">
			<div class="panel-body" style="background-color: #fff;">
                    <div class="table-responsive">
                    	
                     <table id="data-table-combine" class="table table-striped table-bordered">
						   <thead>
						      <tr>
						      	 <th>Day</th>
						      	 <th>Sales Total</th>
						         <!-- <th>Time</th> -->
						         <th class="hidden-print">Action</th>
						      </tr>
						   </thead>
						   <tbody>
                          <?php
						   $sales = mysqli_query($connection,"SELECT * FROM kp_sale_items WHERE dpt=2 GROUP BY shift ORDER BY shift ASC");
						   if (mysqli_num_rows($sales) < 1) {
						   	?>
						   	<div class="alert alert-warning">
						   		<p>There are no recorded sales</p>
						   	</div>
						   	<?php
						   }else{
						   	while ($sa = mysqli_fetch_assoc($sales)) {
						   		$order_no = $sa['sale_id'];
						   		$shift = $sa['shift'];

						   	$days_total = mysqli_query($connection,"SELECT SUM(total) AS days_sum FROM kp_sale_items WHERE dpt=2 AND shift='$shift'");
						   	$total = mysqli_fetch_assoc($days_total);
							
						   		?>
						   		<tr>
						   			<td><?php echo $shift; ?></td>
						   			<td><?php  echo $total['days_sum']; ?></td>
						   			 <td class="hidden-print"><a href="shift_kitchen_sales.php?shift=<?php echo $shift;?>">View</a></td>  
						   			
						   		</tr>
						   		<?php	
						   	}
						   }
						   ?>

						
						
							</tbody>
						

					    	</table>
                    </div>
                   
                </div>
                
         
            </div>
			<!-- end invoice -->
		</div>
		<!-- end #content -->
		
		<!-- begin scroll to top btn -->
		<a href="javascript:;" class="btn btn-icon btn-circle btn-success btn-scroll-to-top fade" data-click="scroll-top"><i class="fa fa-angle-up"></i></a>
		<!-- end scroll to top btn -->
	</div>
	<?php  
include("../clude/foot.php");
?>