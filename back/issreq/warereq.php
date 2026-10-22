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
      <!-- /.row -->
			<div class="row" >
				<!-- begin col-8 -->
                <div class="col-md-12">
                    <div class="panel panel-purple" data-sortable-id="index-1" style="margin-top: 10px;">
                        <div class="panel-heading">
                           <span style="color:white; font-size: 28px;"><B>STORE REQUISITION TO WAREHOUSE</B></span>
                        </div>                       
                        <div class="panel-body">
                        <?php
                        $po = mysqli_query($connection,"SELECT * FROM kp_req WHERE issue_status=2") or die("Could not fetch the purchase orders");
                        if (mysqli_num_rows($po) < 1) {
                            echo "There are no purchase orders in the system at the moment";
                        }else{
                        ?>
                            <table  class="table table-striped table-bordered table-condensed" id="dataTables-example">

                                <thead>
                                    <tr>
                                        <th>No:</th>
                                        <th>Requisition No.</th> 
                                        <th>Requisition By</th>
                                        <th>Shift</th>                                        
                                        <th>Action</th>
                                      
                                        
                                    </tr>
                                </thead>
                                <tbody>
                                <?php
                               
                                while ($row = mysqli_fetch_assoc($po)) {
                                    $req_id = $row['req_id']; 
                                    $staff_id = $row['req_user']; 
                                    $req_shift = $row['req_date'];
                                    $no=$no+1;
 
                                    ?>
                                    <tr>
                                      <td><?php echo $no; ?></td>
                                      <td><?php echo $req_id; ?></td> 
                                      <td><?php echo get_user($staff_id); ?></td> 
                                      <td><?php echo  $req_shift; ?></td>
                                      <td><a type="button" class="btn btn-outline-success btn-sm" href="req_view.php?req_id=<?php echo $req_id; ?>">ISSUE STORE</a></td>
                                    </tr>
                                    <?php
                                }
                                ?>
                                </tbody>
                         </table>
                         <?php
                         }
                         ?>
                        </div>
                    </div>
                    
                    
                </div>
                <!-- end col-8 -->
			</div>
			<!-- end row -->
		</div>
		
   <script type="text/javascript">
                             function delete_this(str){
                                var id = str;
                                $.ajax({
                                    type: "POST",
                                    url:"delete_order_item.php",
                                    data:"id="+id,
                                    success:function(data){
                                        
                                    }
                                })
                             }                      
                             </script>
   
		<!-- end #content -->
	 <!-- ================== BEGIN BASE JS ================== -->
  <script src="../../assets/plugins/jquery/jquery-3.2.1.min.js"></script>
  <script src="../../assets/plugins/jquery-ui/jquery-ui.min.js"></script>
  <script src="../../assets/plugins/bootstrap/4.0.0/js/bootstrap.bundle.min.js"></script>
  <!--[if lt IE 9]>
    <script src="../assets/crossbrowserjs/html5shiv.js"></script>
    <script src="../assets/crossbrowserjs/respond.min.js"></script>
    <script src="../assets/crossbrowserjs/excanvas.min.js"></script>
  <![endif]-->
  <script src="../../assets/plugins/slimscroll/jquery.slimscroll.min.js"></script>
  <script src="../../assets/plugins/js-cookie/js.cookie.js"></script>
  <script src="../../assets/js/theme/default.min.js"></script>
  
  <script src="../../assets/plugins/gritter/js/jquery.gritter.js"></script>
  <script src="../../assets/js/apps.min.js"></script>
  <!-- ================== END BASE JS ================== -->

  <!-- ================== BEGIN PAGE LEVEL JS ================== -->
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
  <!-- ================== END PAGE LEVEL JS ================== -->
   
  <script>
    $(document).ready(function() {
      App.init();
      TableManageCombine.init();

    }); 
  </script> 
</body>
</html>
  
        