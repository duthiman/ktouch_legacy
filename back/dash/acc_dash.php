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
					<div class="widget widget-stats bg-red">
						<div class="stats-icon"><i class="fa fa-desktop"></i></div>
						<div class="stats-info">
							<h4>TODAY SALES</h4>
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
							<h4>TOTAL ORDERS</h4>
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
							<h4>UNIQUE VISITORS</h4>
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
					<div class="widget widget-stats bg-black-lighter">
						<div class="stats-icon"><i class="fa fa-clock"></i></div>
						<div class="stats-info">
							<h4>AVG TIME ON SITE</h4>
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
							<h4 class="panel-title">WEEKLY SALES & PURCHASES ANALYTICS </h4>
						</div>
						<div class="panel-body">
							<div id="interactive-chart" class="height-sm"></div>
						</div>
					</div>
					<!-- end panel -->
					
					<!-- begin tabs -->
					<ul class="nav nav-tabs nav-tabs-purple nav-justified nav-justified-mobile" data-sortable-id="index-2">
						<li class="nav-item"><a href="#latest-sales" data-toggle="tab" class="nav-link active"><i class="fa fa-camera fa-lg m-r-5"></i> <span class="d-none d-md-inline">LATEST SALES</span></a></li>
						<li class="nav-item"><a href="#purchase" data-toggle="tab" class="nav-link"><i class="fa fa-archive fa-lg m-r-5"></i> <span class="d-none d-md-inline">LATEST PURCHASES</span></a></li>
						<li class="nav-item"><a href="#email" data-toggle="tab" class="nav-link"><i class="fa fa-envelope fa-lg m-r-5"></i> <span class="d-none d-md-inline"></span></a></li>
					</ul>
					<div class="tab-content" data-sortable-id="index-3">
						<div class="tab-pane fade active show" id="latest-sales">
							<div class="height-sm" data-scrollbar="true">
								 <table class="table table-striped table-bordered data-table-combine">
									<thead>
										<tr>
											<th>Type</th>
											<th class="hidden-sm">Sale No</th>
											<th>Customer</th>
											<th>Amount</th>
											<th>User</th>
										</tr>
									</thead>
									<tbody>
										<tr>
											<td>Cash</td>
											<td class="hidden-sm">0025</td>
											<td> Customer Name </td>
											<td>$349.00</td>
											<td class="text-nowrap"><a href="javascript:;">Derick Wong</a></td>
										</tr>
										<tr>
											<td>Broker</td>
											<td class="hidden-sm">0025</td>
											<td> Customer Name </td>
											<td>$399.00</td>
											<td class="text-nowrap"><a href="javascript:;">Derick Wong</a></td>
										</tr>
										<tr>
											<td>Credit</td>
											<td class="hidden-sm">26</td>
											<td> Customer Name </td>
											<td>$499.00</td>
											<td class="text-nowrap"><a href="javascript:;">Derick Wong</a></td>
										</tr>
										<tr>
											<td>13/02/2013</td>
											<td class="hidden-sm">27</td>
											<td> Customer Name </td>
											<td>$230.00</td>
											<td class="text-nowrap"><a href="javascript:;">Derick Wong</a></td>
										</tr>
										 
									</tbody>
								</table>
							</div>
						</div>
						<div class="tab-pane fade" id="purchase">
							<div class="height-sm" data-scrollbar="true">
								<table class="table table-striped table-bordered data-table-combine">
									<thead>
										<tr>
											<th>Type</th>
											<th class="hidden-sm">Sale No</th>
											<th>Customer</th>
											<th>Amount</th>
											<th>User</th>
											<th>Status</th>
										</tr>
									</thead>
									<tbody>
										<tr>
											<td>Cash</td>
											<td class="hidden-sm">0025</td>
											<td> Customer Name </td>
											<td>$349.00</td>
											<td class="text-nowrap"><a href="javascript:;">Derick Wong</a></td>
											<td >Received</td>
										</tr>
										<tr>
											<td>Broker</td>
											<td class="hidden-sm">0025</td>
											<td> Customer Name </td>
											<td>$399.00</td>
											<td class="text-nowrap"><a href="javascript:;">Derick Wong</a></td>
											<td >Pending</td>
										</tr>
										<tr>
											<td>Credit</td>
											<td class="hidden-sm">26</td>
											<td> Customer Name </td>
											<td>$499.00</td>
											<td class="text-nowrap"><a href="javascript:;">Derick Wong</a></td>
											<td >Pending</td>
										</tr>
										<tr>
											<td>13/02/2013</td>
											<td class="hidden-sm">27</td>
											<td> Customer Name </td>
											<td>$230.00</td>
											<td class="text-nowrap"><a href="javascript:;">Derick Wong</a></td>
											<td >Pending</td>
										</tr>
										 
									</tbody>
								</table>
							</div>
						</div>
						<div class="tab-pane fade" id="email">
							<div class="height-sm" data-scrollbar="true">
								<ul class="media-list media-list-with-divider">
									<li class="media media-sm">
										<a href="javascript:;" class="pull-left">
											<img src="../../assets/img/user/user-1.jpg" alt="" class="media-object rounded-corner" />
										</a>
										<div class="media-body">
											<a href="javascript:;"><h4 class="media-heading">Lorem ipsum dolor sit amet, consectetur adipiscing elit.</h4></a>
											<p class="m-b-5">
												Aenean mollis arcu sed turpis accumsan dignissim. Etiam vel tortor at risus tristique convallis. Donec adipiscing euismod arcu id euismod. Suspendisse potenti. Aliquam lacinia sapien ac urna placerat, eu interdum mauris viverra.
											</p>
											<i class="text-muted">Received on 04/16/2013, 12.39pm</i>
										</div>
									</li>
									<li class="media media-sm">
										<a href="javascript:;" class="pull-left">
											<img src="../../assets/img/user/user-2.jpg" alt="" class="media-object rounded-corner" />
										</a>
										<div class="media-body">
											<a href="javascript:;"><h4 class="media-heading">Praesent et sem porta leo tempus tincidunt eleifend et arcu.</h4></a>
											<p class="m-b-5">
												Proin adipiscing dui nulla. Duis pharetra vel sem ac adipiscing. Vestibulum ut porta leo. Pellentesque orci neque, tempor ornare purus nec, fringilla venenatis elit. Duis at est non nisl dapibus lacinia.
											</p>
											<i class="text-muted">Received on 04/16/2013, 12.39pm</i>
										</div>
									</li>
									<li class="media media-sm">
										<a href="javascript:;" class="pull-left">
											<img src="../../assets/img/user/user-3.jpg" alt="" class="media-object rounded-corner" />
										</a>
										<div class="media-body">
											<a href="javascript:;"><h4 class="media-heading">Ut mi eros, varius nec mi vel, consectetur convallis diam.</h4></a>
											<p class="m-b-5">
												Ut mi eros, varius nec mi vel, consectetur convallis diam. Nullam eget hendrerit eros. Duis lacinia condimentum justo at ultrices. Phasellus sapien arcu, fringilla eu pulvinar id, mattis quis mauris.
											</p>
											<i class="text-muted">Received on 04/16/2013, 12.39pm</i>
										</div>
									</li>
									<li class="media media-sm">
										<a href="javascript:;" class="pull-left">
											<img src="../../assets/img/user/user-4.jpg" alt="" class="media-object rounded-corner" />
										</a>
										<div class="media-body">
											<a href="javascript:;"><h4 class="media-heading">Aliquam nec dolor vel nisl dictum ullamcorper.</h4></a>
											<p class="m-b-5">
												Aliquam nec dolor vel nisl dictum ullamcorper. Duis vel magna enim. Aenean volutpat a dui vitae pulvinar. Nullam ligula mauris, dictum eu ullamcorper quis, lacinia nec mauris.
											</p>
											<i class="text-muted">Received on 04/16/2013, 12.39pm</i>
										</div>
									</li>
								</ul>
							</div>
						</div>
					</div>
					<!-- end tabs -->
				
                
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
				
				 
					
					<!-- begin panel -->
					<div class="panel panel-inverse" data-sortable-id="index-10">
						<div class="panel-heading">
							<div class="panel-heading-btn">
								<a href="javascript:;" class="btn btn-xs btn-icon btn-circle btn-default" data-click="panel-expand"><i class="fa fa-expand"></i></a>
								<a href="javascript:;" class="btn btn-xs btn-icon btn-circle btn-success" data-click="panel-reload"><i class="fa fa-redo"></i></a>
								<a href="javascript:;" class="btn btn-xs btn-icon btn-circle btn-warning" data-click="panel-collapse"><i class="fa fa-minus"></i></a>
								<a href="javascript:;" class="btn btn-xs btn-icon btn-circle btn-danger" data-click="panel-remove"><i class="fa fa-times"></i></a>
							</div>
							<h4 class="panel-title">Calendar</h4>
						</div>
						<div class="panel-body">
							<div id="datepicker-inline" class="datepicker-full-width overflow-y-scroll position-relative"><div></div></div>
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
