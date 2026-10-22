<?php 
require("../clude/head.php");
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
			 
			<!-- begin row -->
			<div class="row">
				<!-- begin col-3 -->
				<div class="col-lg-3 col-md-6">
					<div class="widget widget-stats  bg-black-lighter">
						<div class="stats-icon"><i class="fa fa-desktop"></i></div>
						<div class="stats-info">
							<p>TODAY REQUISITIONS</p>
							<p>3,291,922</p>	
						</div>
						<div class="stats-link">
							<a href="javascript:;">View DetailS<i class="fa fa-arrow-alt-circle-right"></i></a>
						</div>
					</div>
				</div>
				<!-- end col-3 -->
				<!-- begin col-3 -->
				<div class="col-lg-3 col-md-6">
					<div class="widget widget-stats bg-orange">
						<div class="stats-icon"><i class="fa fa-link"></i></div>
						<div class="stats-info">
							<p>TODAY PURCHASES</p>
							<p>20.44%</p>	
						</div>
						<div class="stats-link">
							<a href="javascript:;">View DetailS<i class="fa fa-arrow-alt-circle-right"></i></a>
						</div>
					</div>
				</div>
				<!-- end col-3 -->
				<!-- begin col-3 -->
				<div class="col-lg-3 col-md-6">
					<div class="widget widget-stats bg-purple-darker">
						<div class="stats-icon"><i class="fa fa-users"></i></div>
						<div class="stats-info">
							<p>BELOW REORDER LEVEL</p>
							<p>1,291,922</p>	
						</div>
						<div class="stats-link">
							<a href="javascript:;">View Detail <i class="fa fa-arrow-alt-circle-right"></i></a>
						</div>
					</div>
				</div>
				<!-- end col-3 -->
				<!-- begin col-3 -->
				<div class="col-lg-3 col-md-6">
					<div class="widget widget-stats bg-red">
						<div class="stats-icon"><i class="fa fa-clock"></i></div>
						<div class="stats-info">
							<p>OUT OF STOCK</p>
							<p>00:12:23</p>	
						</div>
						<div class="stats-link">
							<a href="javascript:;">View Detail <i class="fa fa-arrow-alt-circle-right"></i></a>
						</div>
					</div>
				</div>
				<!-- end col-3 -->
			</div>
			<!-- end row -->
			<!-- begin row -->
			<div class="row">
				<!-- begin col-8 -->
				<div class="col-lg-8">
					<!-- begin panel -->
					<div class="panel panel-default" data-sortable-id="index-1">
						<div class="panel-heading">
							<div class="panel-heading-btn">
								<a href="javascript:;" class="btn btn-xs btn-icon btn-circle btn-default" data-click="panel-expand"><i class="fa fa-expand"></i></a>
								<a href="javascript:;" class="btn btn-xs btn-icon btn-circle btn-success" data-click="panel-reload"><i class="fa fa-redo"></i></a>
								<a href="javascript:;" class="btn btn-xs btn-icon btn-circle btn-warning" data-click="panel-collapse"><i class="fa fa-minus"></i></a>
								<a href="javascript:;" class="btn btn-xs btn-icon btn-circle btn-danger" data-click="panel-remove"><i class="fa fa-times"></i></a>
							</div>
							<h4 class="panel-title">WEEKLY STOCK IN & OUT ANALYTICS </h4>
						</div>
						<div class="panel-body">
							<div id="interactive-chart" class="height-sm"></div>
						</div>
					</div>
					<!-- end panel -->
					
					 
				
                
				</div>
				<!-- end col-8 -->
				<!-- begin col-4 -->
				<div class="col-lg-4">
					<!-- begin panel -->
                    <div class="panel panel-default" data-sortable-id="flot-chart-5">
                        <div class="panel-heading">
                            <div class="panel-heading-btn">
                                <a href="javascript:;" class="btn btn-xs btn-icon btn-circle btn-default" data-click="panel-expand"><i class="fa fa-expand"></i></a>
                                <a href="javascript:;" class="btn btn-xs btn-icon btn-circle btn-success" data-click="panel-reload"><i class="fa fa-redo"></i></a>
                                <a href="javascript:;" class="btn btn-xs btn-icon btn-circle btn-warning" data-click="panel-collapse"><i class="fa fa-minus"></i></a>
                                <a href="javascript:;" class="btn btn-xs btn-icon btn-circle btn-danger" data-click="panel-remove"><i class="fa fa-times"></i></a>
                            </div>
                            <h4 class="panel-title">Flot Interactive Pie Chart</h4>
                        </div>
                        <div class="panel-body"> <div id="interactive-pie-chart" class="height-sm"></div>
                        </div>
                    </div>
		        	<!-- end panel -->
				
				  
				</div>
				<!-- end col-4 -->
			</div>
			<!-- end row -->
		</div>
		<!-- end #content -->

		
		<!-- begin scroll to top btn -->
		<a href="javascript:;" class="btn btn-icon btn-circle btn-success btn-scroll-to-top fade" data-click="scroll-top"><i class="fa fa-angle-up"></i></a>
		<!-- end scroll to top btn -->
	</div>
	<!-- end page container -->
	
	<!-- ================== BEGIN BASE JS ================== -->
	<script src="../../assets/plugins/jquery/jquery-3.2.1.min.js"></script>
	<script src="../../assets/plugins/jquery-ui/jquery-ui.min.js"></script>
	<script src="../../assets/plugins/bootstrap/4.0.0/js/bootstrap.bundle.min.js"></script>
	<!--[if lt IE 9]>
		<script src="../../assets/crossbrowserjs/html5shiv.js"></script>
		<script src="../../assets/crossbrowserjs/respond.min.js"></script>
		<script src="../../assets/crossbrowserjs/excanvas.min.js"></script>
	<![endif]-->
	<script src="../../assets/plugins/slimscroll/jquery.slimscroll.min.js"></script>
	<script src="../../assets/plugins/js-cookie/js.cookie.js"></script>
	<script src="../../assets/js/theme/default.min.js"></script>
	<script src="../../assets/js/apps.min.js"></script>
	<!-- ================== END BASE JS ================== -->
	
	<!-- ================== BEGIN PAGE LEVEL JS ================== -->
	<script src="../../assets/plugins/gritter/js/jquery.gritter.js"></script>
	<script src="../../assets/plugins/flot/jquery.flot.min.js"></script>
	<script src="../../assets/plugins/flot/jquery.flot.time.min.js"></script>
	<script src="../../assets/plugins/flot/jquery.flot.resize.min.js"></script>
	<script src="../../assets/plugins/flot/jquery.flot.pie.min.js"></script>
	<script src="../../assets/plugins/sparkline/jquery.sparkline.js"></script> 
	<script src="../../assets/plugins/bootstrap-datepicker/js/bootstrap-datepicker.js"></script>
	<!-- ================== BEGIN PAGE LEVEL JS ================== -->
    <script src="../assets/plugins/nvd3/d3.min.js"></script>
    <script src="../assets/plugins/nvd3/build/nv.d3.js"></script> 
	<!-- ================== END PAGE LEVEL JS ================== -->
	<script src="../../assets/js/demo/dashboard.js"></script>
	<!-- ================== END PAGE LEVEL JS ================== -->

	<!-- ================== BEGIN PAGE LEVEL JS ================== -->
  <script src="../assets/plugins/DataTables/media/js/jquery.dataTables.js"></script>
  <script src="../assets/plugins/DataTables/media/js/dataTables.bootstrap.min.js"></script>
  <script src="../assets/plugins/DataTables/extensions/Buttons/js/dataTables.buttons.min.js"></script>
  <script src="../assets/plugins/DataTables/extensions/Buttons/js/buttons.bootstrap.min.js"></script>
  <script src="../assets/plugins/DataTables/extensions/Buttons/js/buttons.flash.min.js"></script>
  <script src="../assets/plugins/DataTables/extensions/Buttons/js/jszip.min.js"></script>
  <script src="../assets/plugins/DataTables/extensions/Buttons/js/pdfmake.min.js"></script>
  <script src="../assets/plugins/DataTables/extensions/Buttons/js/vfs_fonts.min.js"></script>
  <script src="../assets/plugins/DataTables/extensions/Buttons/js/buttons.html5.min.js"></script>
  <script src="../assets/plugins/DataTables/extensions/Buttons/js/buttons.print.min.js"></script>
  <script src="../assets/plugins/DataTables/extensions/Responsive/js/dataTables.responsive.min.js"></script>
  <script src="../assets/plugins/DataTables/extensions/AutoFill/js/dataTables.autoFill.min.js"></script>
  <script src="../assets/plugins/DataTables/extensions/ColReorder/js/dataTables.colReorder.min.js"></script>
  <script src="../assets/plugins/DataTables/extensions/KeyTable/js/dataTables.keyTable.min.js"></script>
  <script src="../assets/plugins/DataTables/extensions/RowReorder/js/dataTables.rowReorder.min.js"></script>
  <script src="../assets/plugins/DataTables/extensions/Select/js/dataTables.select.min.js"></script>
  <script src="../assets/js/demo/table-manage-combine.demo.js"></script>
  <!-- ================== END PAGE LEVEL JS ================== -->
   
  <script>
    $(document).ready(function() {
      App.init();
      Dashboard.init();
      TableManageCombine.init(); 
		});
	</script>
</body>
</html>
