<?php 
require("../clude/head.php");
$no=0;

?>
<link href="../../assets/plugins/DataTables/media/css/dataTables.bootstrap.min.css" rel="stylesheet" />
<link href="../../assets/plugins/DataTables/extensions/Buttons/css/buttons.bootstrap.min.css" rel="stylesheet" />
<link href="../../assets/plugins/DataTables/extensions/Responsive/css/responsive.bootstrap.min.css" rel="stylesheet" />
 
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
						      	 <th>Shift</th>   
						      	 <th>Req Count</th> 
						         <th class="hidden-print">Action</th>
						      </tr>
						   </thead>
						   <tbody>
                          <?php
						   $sales = mysqli_query($connection,"SELECT COUNT(req_id) as count,req_shift FROM kp_counter_req GROUP BY req_shift");
						   if (mysqli_num_rows($sales) < 1) {
						   	?>
                            <tr><td colspan="3" class="text-center text-muted">There are no recorded requisitions.</td></tr>
						   	<?php
						   }else{
						   	while ($sa = mysqli_fetch_assoc($sales)) {
						   		$count = $sa['count'];
						   		$shift = $sa['req_shift']; 
						   		?>
						   		<tr>
						   			<td><?php echo $shift; ?></td>
						   			<td><?php  echo $count; ?></td>
						   			 <td class="hidden-print"><a href="counter_req_day.php?shift=<?php echo $shift;?>">View</a></td>  
						   			
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
<script src="../../assets/plugins/DataTables/media/js/jquery.dataTables.js"></script>
<script src="../../assets/plugins/DataTables/media/js/dataTables.bootstrap.min.js"></script>
<script src="../../assets/plugins/DataTables/extensions/Buttons/js/dataTables.buttons.min.js"></script>
<script src="../../assets/plugins/DataTables/extensions/Buttons/js/buttons.bootstrap.min.js"></script>
<script src="../../assets/plugins/DataTables/extensions/Buttons/js/buttons.flash.min.js"></script>
<script src="../../assets/plugins/DataTables/extensions/Buttons/js/jszip.min.js"></script>
<script src="../../assets/plugins/DataTables/extensions/Buttons/js/pdfmake.min.js"></script>
<script src="../../assets/plugins/DataTables/extensions/Buttons/js/vfs_fonts.min.js"></script>
<script src="../../assets/plugins/DataTables/extensions/Buttons/js/buttons.html5.min.js"></script>
<script src="../../assets/plugins/DataTables/extensions/Buttons/js/buttons.print.min.js"></script>
<script src="../../assets/plugins/DataTables/extensions/Responsive/js/dataTables.responsive.min.js"></script>
<script src="../../assets/plugins/DataTables/extensions/AutoFill/js/dataTables.autoFill.min.js"></script>
<script src="../../assets/plugins/DataTables/extensions/ColReorder/js/dataTables.colReorder.min.js"></script>
<script src="../../assets/plugins/DataTables/extensions/KeyTable/js/dataTables.keyTable.min.js"></script>
<script src="../../assets/plugins/DataTables/extensions/RowReorder/js/dataTables.rowReorder.min.js"></script>
<script src="../../assets/plugins/DataTables/extensions/Select/js/dataTables.select.min.js"></script>
<script src="../../assets/js/demo/table-manage-combine.demo.min.js"></script>
 
</body>
</html> 
 
