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
						         <th class="hidden-print">Action</th>
						      </tr>
						   </thead>
						   <tbody>
                          <?php
						   $sales = mysqli_query($connection,"SELECT SUM(total) AS days_sum,shift FROM kp_sale_items GROUP BY shift ORDER BY shift ASC");
						   if (mysqli_num_rows($sales) < 1) {
						   	?>
						   	<div class="alert alert-warning">
						   		<p>There are no recorded sales</p>
						   	</div>
						   	<?php
						   }else{
						   	while ($sa = mysqli_fetch_assoc($sales)) {
						   		//$order_no = $sa['sale_id'];
						   		$shift = $sa['shift']; 
						   		?>
						   		<tr>
						   			<td><?php echo $shift; ?></td>
						   			<td><?php  echo $sa['days_sum']; ?></td>
						   			 <td class="hidden-print"><a href="shift_bar_sales.php?shift=<?php echo $shift;?>">View</a></td>  
						   			
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
<!-- ================== BEGIN PAGE LEVEL JS ================== -->
<script src="../../assets/plugins/DataTables/media/js/jquery.dataTables.js"></script>
<script src="../../assets/plugins/DataTables/media/js/dataTables.bootstrap.min.js"></script>
<script src="../../assets/plugins/DataTables/extensions/Buttons/js/dataTables.buttons.min.js"></script>
<script src="../../assets/plugins/DataTables/extensions/Buttons/js/buttons.bootstrap.min.js"></script>
<script src="../../assets/plugins/DataTables/extensions/Buttons/js/buttons.flash.min.js"></script>   
<script src="../../assets/plugins/DataTables/extensions/ColReorder/js/dataTables.colReorder.min.js"></script> 
  <script src="../../assets/plugins/DataTables/extensions/Buttons/js/buttons.print.min.js"></script> 
<script src="../../assets/js/demo/table-manage-combine.demo.min.js"></script>
  <!-- ================== END PAGE LEVEL JS ================== -->
<script type="text/javascript">
  $(document).ready(function() { 
    App.init();
      TableManageCombine.init();     
  }); 
</script>  
</body>
</html>