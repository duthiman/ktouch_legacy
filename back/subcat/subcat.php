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
                <!-- begin col-6 -->
			    <div class="col-lg-7">
			        <!-- begin panel -->
                    	<div class="panel panel-inverse" data-sortable-id="index-1">
						<div class="panel-heading"> 
               <div class="pull-right">
                <Button href="#sub-modal" class="btn btn-sm btn-lime pull-right" data-toggle="modal" id="sub-add" ><i class="fa fa-plus"></i> &nbsp;ADD SUB CATEGORY</Button> &nbsp; &nbsp; &nbsp; &nbsp;
          
               </div>
              
							<h4 class="panel-title">SUB CATEGORY LIST</h4>
             	</div>
					<div class="panel-body">

            <?php
            $sub_categories = mysqli_query($connection,"SELECT * FROM  kp_sub_cat");
            if (mysqli_num_rows($sub_categories) < 1) {
              echo "No sub categories on the database";
            }else{
              ?>
                        <table id="data-table-combine" class="table table-striped table-bordered">
               <thead>
                  <tr>
                    <th>id</th>
                    <th>Name</th>
                    <th>Main Category</th>
                    <th>Department</th>
                    <th>Action</th>
                  </tr>
               </thead>
               <tbody>
               <?php
               while ($sub = mysqli_fetch_assoc($sub_categories)) {
                $cat_id = $sub['cat_id'];
                $sub_id = $sub['sub_id'];
                if ($sub['dpt']==1) {
                  $dpt="BAR";
                }elseif ($sub['dpt']==2) {
                  $dpt="KITCHEN";
                }else{
                  $dpt="ERROR";
                } 
                $get_main_cat = mysqli_query($connection,"SELECT * FROM kp_cat WHERE cat_id='$cat_id'");
                $m = mysqli_fetch_assoc($get_main_cat);
                  
                  ?>
                    <tr>
                       
                       <td><?php echo $sub_id; ?></td>
                       <td><?php echo $sub['name']; ?></td>
                       <td><?php echo $m['name']; ?></td>   
                       <td><?php echo $dpt; ?></td>           
                       <td>
                        <a data-toggle="modal" data-target="#sub-modal" data-id="<?php echo $sub_id; ?>" id="sub-edit" class="btn btn-xs btn-info"><i  class="fa fa-edit"></i></a>
                        |&nbsp;
                        <a data-toggle="modal" data-target="#sub-modal" data-id="<?php echo $sub_id; ?>" id="sub-del" class="btn btn-xs btn-danger"><i  class=" fa fa-times"></i></a>
                                        |&nbsp;
                                        <button  class="btn btn-lime btn-xs waves-effect waves-light btnSelect">Select</button>
                          
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
                <!-- end col-6 -->
<!-- begin col-5 -->
                <div class="col-lg-5">
                   <!-- begin panel -->
                    <div class="panel  panel-inverse" id="items_list">
                      
                    </div> 
                  </div>
            <!-- end col-5 -->

            </div>
            <!-- end row -->
		</div>
		<!-- end #content -->
<div  id="sub-modal" class="modal fade bs-example-modal-lg" tabindex="-1" role="dialog" aria-labelledby="myModalLabel" style="display: none;">
   <div class="modal-dialog"  id="sub-load"></div>
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

  

  <script src="../../assets/plugins/select2/dist/js/select2.min.js"></script> 

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
  <script src="../../assets/js/demo/table-manage-combine.demo.min.js"></script> 
   
  <script>
    $(document).ready(function() {
      App.init();
      TableManageCombine.init();

    });
  <?php include("sub.js");?>
  </script>  
   <script type="text/javascript">
 $('.btnSelect').click(function () {
    var sub_id = $(this).closest("tr").find('td:eq(0)').text();
    
            xmlhttp = new XMLHttpRequest();
            xmlhttp.open("GET","sub_items.php?id="+sub_id,false);
            xmlhttp.send(null);
            document.getElementById("items_list").innerHTML = xmlhttp.responseText;
            document.getElementById("items_list").style.visibility = "visible";
});
</script>
</body>
</html>
  