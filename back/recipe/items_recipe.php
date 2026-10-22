<?php 
require("../clude/head.php");
$no=0;

?>
      <!-- ================== BEGIN PAGE LEVEL STYLE ================== -->
  <link href="../../assets/plugins/DataTables/media/css/dataTables.bootstrap.min.css" rel="stylesheet" />
  <link href="../../assets/plugins/DataTables/extensions/Buttons/css/buttons.bootstrap.min.css" rel="stylesheet" />
  <link href="../../assets/plugins/DataTables/extensions/Responsive/css/responsive.bootstrap.min.css" rel="stylesheet" />
  <link href="../../assets/plugins/DataTables/extensions/AutoFill/css/autoFill.bootstrap.min.css" rel="stylesheet" />
  <link href="../../assets/plugins/DataTables/extensions/ColReorder/css/colReorder.bootstrap.min.css" rel="stylesheet" />
  <link href="../../assets/plugins/DataTables/extensions/KeyTable/css/keyTable.bootstrap.min.css" rel="stylesheet" />
  <link href="../../assets/plugins/DataTables/extensions/RowReorder/css/rowReorder.bootstrap.min.css" rel="stylesheet" />
  <link href="../../assets/plugins/DataTables/extensions/Select/css/select.bootstrap.min.css" rel="stylesheet" />
  <!-- ================== END PAGE LEVEL STYLE ================== -->
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
    <div class="row ">
        <!-- begin col-6 -->
        <div class="col-lg-8" id="recipe">
            <!-- begin panel -->
            <div class="panel  panel-purple">
                <div class="panel-heading">
                    <div class="panel-heading-btn">
                        <a href="javascript:;" class="btn btn-xs  btn-default">UPDATE ITEMS</a>
                    </div>
                    <h4 class="panel-title">ITEMS WITH RECIPE</h4>
                </div>
                <!-- begin panel-body -->
                <div class="panel-body">
                <?php
                $get_items = "SELECT * FROM kp_items WHERE del_status=2 AND recipe=1";
                $get_items_r = mysqli_query($connection,$get_items)
                             or die("Could not get the ite list");
                $item_count = mysqli_num_rows($get_items_r);
                if ($item_count < 1) {
                    echo "No items with recipe";
                }else{
                    ?>
                 <table id="data-table-combine" class="table table-striped table-bordered">
                    <thead>
                        <tr>
                            <th>No.</th>
                            <th>Name</th>
                            <th>Category</th>
                            <th>Sub Category</th> 
                            <th>Action</th>
                        </tr>
                    </thead>
                    <tbody>
                        <?php
                        $no=0;
                        while ($i = mysqli_fetch_assoc($get_items_r)) {
                            $item_id = $i['item_id']; 
                            $name = $i['name'];
                            $cat_id = $i['cat_id']; 
                            $sub_id = $i['sub_id'];
                            $recipe = $i['recipe']; 
                            $no=$no+1;
                            ?>
                            <tr>
                                <td><?php echo $no; ?></td>
                                <td style="display: none;" ><?php echo $item_id; ?></td>
                                <td><?php echo $name; ?></td>
                                <td><?php echo get_cat($cat_id); ?></td>
                                <td><?php echo get_sub($sub_id); ?></td>
                                <td>
                                    <button data-id="<?php echo $item_id; ?>" class="btn btn-primary btn-xs waves-effect waves-light btnSelect">View items</button>
                                </td>
                            </tr> 
                            <?php
                        }
                        ?>
                    </tbody>
                   </table>
                   <?php } ?>
                <!-- end panel-body -->
          
            </div>
                <!-- end panel -->
        </div>
      </div>
    <!-- end col-6 -->
     <!-- begin col-6 -->
        <div class="col-lg-4" id="recipe">
           <!-- begin panel -->
            <div class="panel  panel-inverse">
              <div class="panel-body" id="items_list">
                 
               </div>
            </div> 
          </div>
    <!-- end col-6 -->
        </div>
        <!-- end row -->


</div>
<!-- end #content -->
</div>
<!-- end page container -->
<?php
 include('../clude/foot.php');
?> <!-- ================== BEGIN PAGE LEVEL JS ================== -->
    <script src="../../assets/plugins/DataTables/media/js/jquery.dataTables.js"></script>
    <script src="../../assets/plugins/DataTables/media/js/dataTables.bootstrap.min.js"></script>
    <script src="../../assets/plugins/DataTables/extensions/Buttons/js/dataTables.buttons.min.js"></script>
    <script src="../../assets/plugins/DataTables/extensions/Buttons/js/buttons.bootstrap.min.js"></script>
    <script src="../../assets/plugins/DataTables/extensions/Buttons/js/buttons.flash.min.js"></script>   
    <script src="../../assets/plugins/DataTables/extensions/ColReorder/js/dataTables.colReorder.min.js"></script> 
    <script src="../../assets/plugins/DataTables/extensions/Buttons/js/buttons.print.min.js"></script> 
    <script src="../../assets/js/demo/table-manage-combine.demo.min.js"></script>
    <!-- ================== END PAGE LEVEL JS ================== -->
    <script src="../../assets/plugins/select2/dist/js/select2.min.js"></script>
    <script type="text/javascript">
      $(document).ready(function() { 
        App.init();
      TableManageCombine.init();    
      }); 
      <?php include("recipe.js");?>
    </script>  
  </body>
</html> 