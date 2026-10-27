<?php 
require("../clude/head.php");
$no=0;

?>
<link href="../../assets/plugins/DataTables/media/css/dataTables.bootstrap.min.css" rel="stylesheet" />
<link href="../../assets/plugins/DataTables/extensions/Buttons/css/buttons.bootstrap.min.css" rel="stylesheet" />
<link href="../../assets/plugins/DataTables/extensions/Responsive/css/responsive.bootstrap.min.css" rel="stylesheet" />
<style>
  .counter-req-report{background:#f5f7fb;min-height:calc(100vh - 110px);padding:18px}
  .counter-req-card{background:#fff;border:1px solid #e1e7f0;border-radius:10px;box-shadow:0 8px 24px rgba(33,48,78,.07);overflow:hidden}
  .counter-req-head{display:flex;align-items:center;justify-content:space-between;padding:16px 20px;border-bottom:1px solid #e8edf4;background:linear-gradient(135deg,#fff,#faf7ff)}
  .counter-req-head h2{margin:0;color:#252d45;font-size:20px;font-weight:800}.counter-req-head p{margin:4px 0 0;color:#76829a;font-size:11px}
  .counter-req-icon{width:42px;height:42px;border-radius:10px;background:#f0e5f8;color:#752d9b;display:flex;align-items:center;justify-content:center;font-size:20px}
  #data-table-combine{margin:0!important;border:0!important}.counter-req-card .dataTables_wrapper{padding:14px 18px 18px}
  .counter-req-card table.dataTable thead th{background:#faf8fd;color:#303a55;border-bottom:2px solid #e6d8ef;padding:11px 12px;font-size:11px}
  .counter-req-card table.dataTable tbody td{padding:10px 12px;color:#40506a;font-size:11px;vertical-align:middle}
  .counter-req-card table.dataTable tbody tr:hover{background:#fbf8fe}.counter-req-card .dataTables_filter input{border:1px solid #d6deea;border-radius:7px;padding:6px 10px;margin-left:7px}
  .counter-req-card .dataTables_length select{border:1px solid #d6deea;border-radius:7px;padding:5px 8px}
  .counter-req-card .dt-buttons{display:inline-flex;gap:5px;margin-left:12px}.counter-req-card .dt-button{border:1px solid #d8c3e5!important;border-radius:7px!important;background:#fff!important;color:#752d9b!important;padding:5px 9px!important;font-size:10px!important}
  .counter-req-card .dt-button:hover{background:#f3eafa!important}.counter-req-view{display:inline-block;padding:5px 11px;border-radius:7px;background:#752d9b;color:#fff!important;font-weight:700;text-decoration:none!important}
  @media(max-width:767px){.counter-req-report{padding:8px}.counter-req-head{padding:12px}.counter-req-head h2{font-size:16px}.counter-req-card .dataTables_wrapper{padding:10px}.counter-req-card .dataTables_filter,.counter-req-card .dataTables_length{float:none;text-align:left;margin-bottom:8px}.counter-req-card .dt-buttons{margin:4px 0 0;display:flex;flex-wrap:wrap}}
</style>
 
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
		<div id="content" class="content counter-req-report">
			<div class="counter-req-card">
                    <div class="counter-req-head"><div><h2>Counter Requisitions</h2><p>Review requisition activity by shift and open the detailed report.</p></div><div class="counter-req-icon"><i class="fa fa-list-alt"></i></div></div>
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
								   				    <td class="hidden-print"><a class="counter-req-view" href="counter_req_day.php?shift=<?php echo urlencode($shift);?>">View</a></td>
						   			
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
<script>
$(function(){
  if ($.fn.DataTable) {
    $('#data-table-combine').DataTable({
      responsive:true,
      pageLength:10,
      order:[[0,'desc']],
      dom:'Blfrtip',
      buttons:[
        {extend:'excelHtml5',title:'Counter Requisitions'},
        {extend:'print',title:'Counter Requisitions'}
      ],
      columnDefs:[{targets:2,orderable:false,searchable:false}]
    });
  }
});
</script>
 
</body>
</html> 
 
