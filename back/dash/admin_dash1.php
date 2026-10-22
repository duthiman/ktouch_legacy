<?php 
require("../clude/head.php");
require_once("funcs.php");
?>
<script type="text/javascript" src="gstatic/loader.js"></script>
<style>
  @media (max-width: 767px) {
    #content.content {
      margin: 5px !important;
      padding: 0 !important;
    }
    #content .row {
      margin-left: -5px;
      margin-right: -5px;
    }
    #content [class*="col-"] {
      padding-left: 5px;
      padding-right: 5px;
    }
    #content .widget.widget-stats {
      min-height: 150px;
      margin-bottom: 10px;
    }
    #content .panel {
      margin-bottom: 10px;
    }
    #content .panel-body {
      padding: 10px;
    }
    #content .table {
      font-size: 12px;
    }
    #content .stats-info h4 {
      font-size: 12px;
      line-height: 1.3;
    }
    #content .stats-info p {
      font-size: 22px;
    }
  }
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
		<div id="content" class="content">
			 
			<!-- begin row -->
			<div class="row">
				<!-- begin col-3 -->
				<div class="col-lg-3 col-md-6 col-sm-6 col-xs-6">
					<div class="widget widget-stats bg-red">
						<div class="stats-icon"><i class="fa fa-desktop"></i></div>
						<div class="stats-info">
							<h4>OPEN SHIFT</h4>
							<p><?PHP  echo shift(); ?></p>
						</div>
						<div class="stats-link">
							<!-- <a href="javascript:;">View DetailS<i class="fa fa-arrow-alt-circle-right"></i></a> -->
						</div>
					</div>
				</div>
				<!-- end col-3 -->
				<!-- begin col-3 -->
				<div class="col-lg-3 col-md-6 col-sm-6 col-xs-6">
					<div class="widget widget-stats bg-orange">
						<div class="stats-icon"><i class="fa fa-link"></i></div>
						<div class="stats-info">
							<h4>TOTAL ORDERS TODAY</h4>
							<p><?php  echo shift_sales(); ?></p>
						</div>
						<div class="stats-link">
							<!-- <a href="javascript:;">View DetailS<i class="fa fa-arrow-alt-circle-right"></i></a> -->
						</div>
					</div>
				</div>
				<!-- end col-3 -->
				<!-- begin col-3 -->
				<div class="col-lg-3 col-md-6 col-sm-6 col-xs-6">
					<div class="widget widget-stats bg-purple-darker">
						<div class="stats-icon"><i class="fa fa-users"></i></div>
						<div class="stats-info">
							<h4>PURCHASES TODAY</h4>
							<p><?php  echo shift_purchases(); ?></p>
						</div>
						<div class="stats-link">
							<a href="../kugura/po_list.php">View Purchases <i class="fa fa-arrow-alt-circle-right"></i></a>
						</div>
					</div>
				</div>
				<!-- end col-3 -->
				<!-- begin col-3 -->
				<div class="col-lg-3 col-md-6 col-sm-6 col-xs-6">
					<div class="widget widget-stats bg-lime-darker">
						<div class="stats-icon"><i class="fa fa-clock"></i></div>
						<div class="stats-info">
							<h4>COUNTER REQUISITIONS</h4>
							<p><?php  echo counter_req(); ?></p>
						</div>
						<div class="stats-link">
							 <a href="../inventory/counter_req.php">View Detail <i class="fa fa-arrow-alt-circle-right"></i></a>
						</div>
					</div>
				</div>
				<!-- end col-3 -->
			</div>
			<!-- end row -->
			<!-- begin row -->
			<div class="row">
				<div class="col-md-12">
					<div class="panel panel-default">
						<div class="panel-heading">
							<h4 class="panel-title">FRONT BUY PURCHASE APPROVALS</h4>
						</div>
						<div class="panel-body">
							<?php
							$front_pos = mysqli_query($connection,"SELECT * FROM kp_po WHERE refno LIKE 'FRONT BUY FOR %' AND (rec_status=2 OR stock_status=2) ORDER BY po_id DESC LIMIT 20") or die(mysqli_error($connection));
							if (mysqli_num_rows($front_pos) < 1) {
								echo "There are no front-raised purchase orders waiting for approval.";
							}else{
							?>
							<table class="table table-striped table-bordered">
								<thead>
									<tr>
										<th>PO</th>
										<th>Counter</th>
										<th>Raised By</th>
										<th>Supplier</th>
										<th>Total</th>
										<th>Date</th>
										<th>Action</th>
									</tr>
								</thead>
								<tbody>
									<?php while ($po = mysqli_fetch_assoc($front_pos)) { ?>
									<tr>
										<td><?php echo $po['po_id']; ?></td>
										<td><?php echo front_po_counter_name($po['po_id']); ?></td>
										<td><?php echo get_user($po['ord_user']); ?></td>
										<td><?php echo $po['sup_id'] > 0 ? get_sup($po['sup_id']) : '<span style="color:red;">NO SUPPLIER</span>'; ?></td>
										<td><?php echo number_format(po_total($po['po_id']),2); ?></td>
										<td><?php echo $po['po_date']; ?></td>
										<td>
											<a class="btn btn-info btn-xs" href="../../front/chaba/render_kug.php?sale_id=<?php echo $po['po_id']; ?>&auto_print=1">Receipt</a>
											<?php if ((int)$po['sup_id'] > 0) { ?>
											<a class="btn btn-success btn-xs" href="../kugura/front_po_approve.php?id=<?php echo $po['po_id']; ?>">Approve</a>
											<?php } else { ?>
											<a class="btn btn-warning btn-xs" href="javascript:;" data-toggle="modal" data-target="#modal" data-id="<?php echo $po['po_id']; ?>" id="front-po-supplier-btn">Add Supplier & Approve</a>
											<?php } ?>
										</td>
									</tr>
									<?php } ?>
								</tbody>
							</table>
							<?php } ?>
						</div>
					</div>
				</div>
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
							<div id="weekly-chart" class="height-sm"></div>
						</div>
					</div>
					<!-- end panel -->
					
				 
				
                
				</div>
				<!-- end col-8 -->
				<!-- begin col-4 -->
				<div class="col-lg-4">
					<!-- begin panel -->
                    <div class="panel panel-default"  >
                        <!-- <div class="panel-heading">
                            <div class="panel-heading-btn">
                                <a href="javascript:;" class="btn btn-xs btn-icon btn-circle btn-default" data-click="panel-expand"><i class="fa fa-expand"></i></a>
                                <a href="javascript:;" class="btn btn-xs btn-icon btn-circle btn-success" data-click="panel-reload"><i class="fa fa-redo"></i></a>
                                <a href="javascript:;" class="btn btn-xs btn-icon btn-circle btn-warning" data-click="panel-collapse"><i class="fa fa-minus"></i></a>
                                <a href="javascript:;" class="btn btn-xs btn-icon btn-circle btn-danger" data-click="panel-remove"><i class="fa fa-times"></i></a>
                            </div>
                            <h4 class="panel-title">TODAY TOP 10 ITEMS</h4>
                        </div> -->
                        <div class="panel-body" id="donutchart" class="height-sm">  </div>
                    </div>
		        	<!-- end panel --> 
				</div>
				<!-- end col-4 --><!-- begin col-4 -->
				<div class="col-md-3">
					<!-- begin panel -->
          <div class="panel panel-default" data-sortable-id="flot-chart-5">
            <div class="panel-heading"> 
              <h4 class="panel-title">STAFF PERFOMANCE</h4>
            </div>
            <div class="panel-body">
            	<table class="table table-striped">
            		<thead>
            			<tr>
            				<th>#</th>
            				<th>STAFF</th>
            				<th>ORDERS</th>
            				<th>TOTAL</th>
            			</tr>
            		</thead>
            		<tbody>
            			<?php
            			$shift=shift();
            			if ($shift==0) {
            				 echo "<tr><td colspan='4'>SHIFT NOT OPEN</td</tr>";
            			}else{
            				$no=0;
	            			$sum=0;
	                  $purchase = mysqli_query($connection,"SELECT  count(sale_id) as tt,SUM(amount) as stotal,user_id FROM kp_sales WHERE shift='$shift'  GROUP BY user_id ORDER BY stotal DESC") or die('Error fetching the purchases.:'.mysqli_error($connection));
            			}
            			
                  if (mysqli_num_rows($purchase) < 1) {
                   echo "<tr><td colspan='4'>NO SALES TODAY</td</tr>";
                 }else{
                 	while ($pow = mysqli_fetch_assoc($purchase)){
                    $user_id = $pow['user_id'];
                    $sales= $pow['stotal']; 
                    $tbills= $pow['tt'];  
				 						$no=$no+1;
				 						$sum=$sum+$sales;
                  ?>
              <tr>
                <td><b><?php echo $no; ?></b></td>
                <td><b><?php echo get_user($user_id); ?></b></td>
                <td><b><?php echo $tbills; ?></b></td>  
                <td><b><?php echo $sales; ?></b></td> 
              </tr>
              <?php
                  }
                }
              ?>
              <tfoot>
              	<tr>
              		<td colspan="3" class="text-center"><h4>TOTAL</h4></td>
              		<td><h4><?php echo $sum; ?></h4></td>
              	</tr>
              </tfoot>
                        		</tbody>
                        	</table>
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
<div id="modal" class="modal fade" tabindex="-1">
	<div class="modal-dialog" id="load"></div>
</div>
<!-- end page container -->
	
	<!-- ================== BEGIN BASE JS ================== -->
	<script src="../../assets/plugins/jquery/jquery-3.2.1.min.js"></script>
	<script src="../../assets/plugins/jquery-ui/jquery-ui.min.js"></script>
	<script src="../../assets/plugins/bootstrap/4.0.0/js/bootstrap.bundle.min.js"></script> 
	<script src="../../assets/plugins/slimscroll/jquery.slimscroll.min.js"></script>
	<script src="../../assets/plugins/js-cookie/js.cookie.js"></script>
	<script src="../../assets/js/theme/default.min.js"></script>
	<script src="../../assets/js/apps.min.js"></script>
	<!-- ================== END BASE JS ================== -->
	
	<!-- ================== BEGIN PAGE LEVEL JS ================== -->  
  <script type="text/javascript">
  	$(document).ready(function() {
      App.init(); 
      $(document).on('click','#front-po-supplier-btn', function(e){
        e.preventDefault();
        var id = $(this).data('id');
        $.ajax({
          url: '../kugura/front_po_supplier.php',
          typr: 'POST',
          data: {id:id},
          dataType: 'html'
        })
        .done(function(data){
          $('#load').html(data);
        })
        .fail(function() {
          $('#load').html('<i class="fa fa-info-sign"></i> Something went wrong, Please try again...');
        });
      });
	});
 google.charts.load("current", {packages:["corechart"]});
  google.charts.setOnLoadCallback(drawChart);
  function drawChart() {
    var data = google.visualization.arrayToDataTable([
      ['Task', 'Hours per Day'],
      <?php 
      $day=shift();
        $get_items = mysqli_query($connection,"SELECT item_id,SUM(total) AS totals FROM kp_sale_items WHERE shift='$day' GROUP BY item_id ORDER BY totals DESC LIMIT 10");
          while ($row = mysqli_fetch_assoc($get_items)) {
          $item_id = $row['item_id'];
          $item_details = mysqli_query($connection,"SELECT name FROM kp_items WHERE item_id='$item_id'");
          $det = mysqli_fetch_assoc($item_details);
          $item_name = $det['name'];

          $total = $row['totals'];
          echo "['".$item_name."', ".$total."],";
        }
        ?>
    ]);

    var options = {
      title: 'TODAY TOP 10 ITEMS',
      pieHole: 0.3,
    };

    var chart = new google.visualization.PieChart(document.getElementById('donutchart'));
    chart.draw(data, options);
  }
    
   google.charts.setOnLoadCallback(drawChart1);
   function drawChart1() {
    var data = google.visualization.arrayToDataTable([
      ['Year', 'Sales'],
      <?php
      $get_week_sales = mysqli_query($connection,"SELECT * FROM kp_sales GROUP BY shift ORDER BY shift DESC LIMIT 10") or die("Error fetching sales.:".mysqli_error($connection));
      while ($row = mysqli_fetch_assoc($get_week_sales)) {
      		$day = $row['shift'];
      		$get_total_sales = mysqli_query($connection,"SELECT SUM(amount) AS total FROM kp_sales WHERE shift='$day'") or die(mysqli_error($connection));
      		$tt = mysqli_fetch_assoc($get_total_sales);
      		$total = $tt['total'];

      		$your_date = date("d M Y", strtotime($day));

      		echo "['".$your_date."', ".$total."],";
      }
      ?>
      
    ]);

    var options = {
      //title: 'SALES ANALYTICS (Last 10 Days)',
      curveType: 'function',
      legend: { position: 'bottom' }
    };

    var chart = new google.visualization.ColumnChart(document.getElementById('weekly-chart'));

    chart.draw(data, options);
  }
    
	</script>


</body>
</html>
