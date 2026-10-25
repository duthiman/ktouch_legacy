<?php 
require("../clude/head.php");
$no=0;
?>
<!-- begin #page-container -->
<div id="page-container" class="page-container fade page-without-sidebar page-header-fixed page-with-top-menu">
  <!-- begin #header -->  
 <?php 
    require("../clude/header.php");
 ?>
  <!-- end #header -->  
  <!-- begin #sidebar -->
 <?php 
    require("../clude/top_menu.php");
 ?>
 <!-- end #sidebar -->
  <!-- begin #content -->
	<div id="content" class="content mt-2">
		<!-- begin row -->
    <div class="row"> 

      <!-- begin col-6 -->
      <div class="col-md-12">
        <!-- begin panel -->
        <div class="panel panel-inverse" data-sortable-id="index-1">
          <div class="panel-heading">  
            <h4 class="panel-title">COUNTERS LIST</h4>
          </div>
          <div class="panel-body">
            <?php
            $get_counter = mysqli_query($connection,"SELECT * FROM kp_counters") or die("Could not get any items");
            if (mysqli_num_rows($get_counter) < 1) {
              echo "There are no registered counters";
            }else{
              ?>
              <table id="data-table-combine" class="table table-striped table-bordered">
                <thead>
                  <tr> 
                    <th>Counter Name</th>
                    <th>COUNTER STATUS</th>
                    <th>TAKE STATUS</th>
                    <th>CASHIER</th>
                    <th>ACTION</th>
                  </tr>
                </thead>
                <tbody>
                  <?php
                  while ($row = mysqli_fetch_assoc($get_counter)) { 
                    $name=$row['name'];  
                    $take_status=$row['take_status'];  
                    $counter_status=$row['counter_status']; 

                    if ($take_status==1) {
                      $stock="FINISHED";
                    }elseif ($take_status==3) {
                      $stock="CONTINUING";
                    }else{
                      $stock="NOT STARTED";
                    } 

                    if ($counter_status==2) {
                      $status="NOT OPEN";
                    }else{
                      $status="OPEN";
                    } 
                    $counter=$row['cntr_id'];
                    $cashier=get_column('kp_counters','user_id',"cntr_id='$counter'");//counter_cashier($row['cntr_id']);                          
                    ?>
                    <tr> 
                      <td><?php echo $name; ?></td>
                      <td><?php echo $status; ?></td>
                      <td><?php echo $stock; ?></td>
                      <td><?php echo get_user($cashier); ?></td>
                      <td class="with-btn-group" nowrap>
                        <div class="btn-group">
                          <a href="#" class="btn btn-lime btn-sm width-60">Actions</a>
                          <a href="#" class="btn btn-lime btn-sm dropdown-toggle width-30 no-caret" data-toggle="dropdown">
                            <span class="caret"></span>
                          </a>
                          <ul class="dropdown-menu pull-right">
                            <?php
                            if ($counter_status==2) {
                            ?>
                            <li><a data-toggle="modal" data-target="#modal" data-id="<?php echo $row['cntr_id']; ?>" id="open">Open Counter</a></li>
                            <?php  
                            }else{
                             
                            ?>
                            <li><a data-toggle="modal" data-target="#modal" data-id="<?php echo $row['cntr_id']; ?>" id="close">Close Counter</a></li>
                            <li><a data-toggle="modal" data-target="#modal" data-id="<?php echo $row['cntr_id']; ?>" id="assign">Assign Cashier</a></li>
                            <li><a data-toggle="modal" data-target="#modal" data-id="<?php echo $row['cntr_id']; ?>" id="change-stat">Change Take Status</a></li>
                            <?php   
                            } 
                            ?>  
                          </ul>
                        </div>
                      </td>
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
        <!-- end panel -->
      </div>
      <!-- end col-8 -->
      <div class="col-md-12 mb-1">
        <div class="panel panel-default">
          <div class="panel-body"> 
            <div class="form-group row">
              <div class="form-group col-lg-2"> 
                <input type="date" class="form-control"  id="shift" value="<?php echo shift(); ?>" />
              </div>  
              <div class="form-group col-lg-2">
                <button style="margin-left: 30px; " type="button" id="filter"  class="btn btn-warning btn-sm" >
                  <i class="fa fa-search"></i>
                </button> 
              </div>
              <div class="form-group col-lg-8 ">
                <h1 class="pull-right" style="color: darkblue;">CURRENT SHIFT: <?php echo shift(); ?></h1>
              </div>
      <div class="col-md-12 mb-1" id="cntrstat">   </div>
            </div>
          </div>
        </div> 
      </div>

    </div>
  </div>
		<!-- end #content -->
  <div  id="modal" class="modal fade bs-example-modal-lg" tabindex="-1" role="dialog" aria-labelledby="myModalLabel" style="display: none;">
    <div class="modal-dialog"  id="load"></div>
  </div>
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
  <link rel="stylesheet" href="../../assets/plugins/sweetalert/backup_20251002_140623/sweetalert2.min.css">
  <script src="../../assets/plugins/sweetalert/backup_20251002_140623/sweetalert2.all.min.js"></script>
  <script src="../../assets/js/demo/table-manage-combine.demo.min.js"></script> 
     <!-- ================== BEGIN PAGE LEVEL JS ================== -->
    <script src="../../assets/plugins/Hullabaloo/js/hullabaloo.js"></script> 
    <!-- ================== END PAGE LEVEL JS ================== -->
  <script type="text/javascript">
    <?php include("count.js");?>
</script>
  
</body>
</html>

        
