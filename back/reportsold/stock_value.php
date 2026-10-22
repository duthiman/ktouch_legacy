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
			<!-- begin row -->
			<div class="row">
        <!-- begin col-12 -->
        <div class="panel col-lg-8"> 
            <div class="panel-heading"> 
              <h4 class="panel-title">STOCK VALUE REPORT</h4>
            </div>
            <div class="panel-body"> 
              <?php
              $get_items = mysqli_query($connection,"SELECT * FROM kp_items WHERE del_status=2") or die("Could not get any items");
              if (mysqli_num_rows($get_items ) < 1) {
                echo "There are no registered items in the database";
              }else{
                ?>
                <table id="data-table-combine" class="table table-striped table-bordered">
                  <thead>
                    <tr>
                      <th>Item ID</th>                                  
                      <th>Name</th>
                      <th>QTY</th>  
                      <th>Buying Price</th>                                      
                      <th>Selling Price</th>                                    
                      <th>Cost Value</th>                                     
                      <th>Selling Value</th>    
                    </tr>
                  </thead>
                  <tbody>
                    <?php 
                    foreach ($get_items  as $row) {
                      $no=$no+1;
                      ?>
                        <tr>
                          <td><?php echo $row['item_id'];?></td>  
                          <td><?php echo strtoupper($row['name']); ?></td> 
                          <td><?php  echo $row['stock']; ?></td> 
                          <td><?php echo number_format($row['b_price']); ?></td>    
                          <td><?php echo number_format($row['price']); ?></td> 
                          <td><?php echo number_format($row['b_price']*$row['stock']); ?></td> 
                          <td><?php echo number_format($row['price']*$row['stock']); ?></td>
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
        <div class="panel col-lg-4"> 
            <div class="panel-heading"> 
              <h4 class="panel-title">STOCK VALUE SUMMARY</h4>
            </div>
            <div class="panel-body"> 
              <?php
              $get_sums = mysqli_query($connection,"SELECT sum(stock*b_price) as cv,SUM(stock*price) as mp  FROM kp_items WHERE del_status=2") or die("Could not get any items");
              if (mysqli_num_rows($get_sums) < 1) {
                echo "ERROR";
              }else{
                $row=mysqli_fetch_assoc($get_sums)
                ?>
                <table id="data-table-combine" class="table table-striped table-bordered">
                  <thead>
                    <tr>
                      <th>COST VALUE</th>    
                      <th><?php echo number_format($row['cv'],2);?></th> 
                    </tr> 
                    <tr>
                      <th>EXPECTED</th>    
                      <th><?php echo number_format($row['mp'],2);?></th> 
                    </tr>

                  </thead>
                </table>
                <?php
                }

                ?>
          </div> <!---->
        </div>
        <!-- end panel 
                </div>-->
                <!-- end col-6 -->


            </div>
            <!-- end row -->
		</div>
		<!-- end #content -->
 
</div>
	<!-- end page container -->
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
  
        