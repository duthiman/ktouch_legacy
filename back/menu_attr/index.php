<?php
require_once __DIR__ . '/API/attr_schema.php';
attr_schema_ready();
require("../clude/head.php");
$no = 0;
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
<link href="../../assets/plugins/select2/dist/css/select2.min.css" rel="stylesheet" />
<!-- ================== END PAGE LEVEL STYLE ================== -->
<!-- begin #page-container -->
<div id="page-container" class="fade page-sidebar-fixed page-header-fixed">
    <!-- begin #header -->
    <?php require("../clude/header.php"); ?>
    <!-- end #header -->
    <!-- begin #sidebar -->
    <?php require("../clude/menu.php"); ?>
    <!-- end #sidebar -->
    <!-- begin #content -->
    <div id="content" class="content">
        <!-- begin row -->
        <div class="row">
            <!-- begin col-8 -->
            <div class="col-lg-8" id="recipe">
                <!-- begin panel -->
                <div class="panel panel-purple">
                    <div class="panel-heading">
                        <div class="panel-heading-btn">
                            <a id="newattr" class="btn btn-xs btn-default">Add New Attribute</a>
                        </div>
                        <h4 class="panel-title">MENU ITEMS ATTRIBUTES</h4>
                    </div>
                    <!-- begin panel-body -->
                    <div class="panel-body">
                        <?php
                        $get_attrs = "SELECT * FROM kp_items_attributes ORDER BY name ASC";
                        $get_attrs_r = mysqli_query($connection, $get_attrs) or die("Could not get attribute list");
                        $attr_count = mysqli_num_rows($get_attrs_r);

                        if ($attr_count < 1) {
                            echo "No attributes found.";
                        } else {
                        ?>
                            <table id="data-table-combine" class="table table-striped table-bordered">
                                <thead>
                                    <tr>
                                        <th>No.</th>
                                        <th>Name</th>
                                        <th>Affects Price?</th>
                                        <th>Price Change</th>
                                        <th>Action</th>
                                    </tr>
                                </thead>
                                <tbody>
                                    <?php
                                    $no = 0;
                                    while ($a = mysqli_fetch_assoc($get_attrs_r)) {
                                        $no++;
                                        $id = $a['id'];
                                        $name = $a['name'];
                                        $affects_price = $a['affects_price'] == 1 ? "Yes" : "No";
                                        $price_change = number_format($a['price_change']);
                                        echo "
                                            <tr>
                                                <td>$no</td>
                                                <td>$name</td>
                                                <td>$affects_price</td>
                                                <td>KES $price_change</td>
                                                <td>
                                                    <button class='btn btn-warning btn-xs map-attr' data-id='$id' data-name=\"$name\">Map to Items</button>
                                                    <button class='btn btn-info btn-xs view-mapped' data-id='$id' data-name=\"$name\">View Mapped Items</button>
                                                </td>
                                            </tr>
                                        ";
                                    }
                                    ?>
                                </tbody>
                            </table>
                        <?php } ?>
                    </div>
                    <!-- end panel-body -->
                </div>
                <!-- end panel -->
            </div>
            <!-- end col-8 -->
            <!-- begin col-4 -->
            <div class="col-lg-4" id="recipe">
                <!-- begin panel -->
                <div class="panel panel-inverse">
                    <div class="panel-heading">
                        <h4 class="panel-title">Item Mapping</h4>
                    </div>
                    <div class="panel-body" id="items_list">
                        <p>Select an attribute to view or map items.</p>
                    </div>
                </div>
                <!-- end panel -->
            </div>
            <!-- end col-4 -->
        </div>
        <!-- end row -->
    </div>
    <!-- end #content -->
    <!-- begin modal for mapping items -->
    <div class="modal fade" id="mapModal" tabindex="-1" role="dialog" aria-labelledby="mapModalLabel">
        <div class="modal-dialog" role="document">
            <div class="modal-content">
                <div class="modal-header">
                    <h4 class="modal-title" id="mapModalLabel">Map Item to Attribute</h4>
                    <button type="button" class="close" data-dismiss="modal" aria-label="Close">
                        <span aria-hidden="true">&times;</span>
                    </button>
                </div>
                <div class="modal-body">
                    <form id="mapForm">
                        <div class="form-group">
                            <label for="itemSelect">Select Item</label>
                            <select id="itemSelect" class="form-control" style="width: 100%"></select>
                            <input type="hidden" id="attr_id" name="attr_id">
                        </div>
                    </form>
                </div>
                <div class="modal-footer">
                    <button type="button" class="btn btn-default" data-dismiss="modal">Close</button>
                    <button type="button" class="btn btn-primary" id="saveMapping">Map Item</button>
                </div>
            </div>
        </div>
    </div>
    <!-- end modal -->
</div>
<!-- end page container -->
<?php include('../clude/foot.php'); ?>
<!-- ================== BEGIN PAGE LEVEL JS ================== -->
<script src="../../assets/plugins/DataTables/media/js/jquery.dataTables.js"></script>
<script src="../../assets/plugins/DataTables/media/js/dataTables.bootstrap.min.js"></script>
<script src="../../assets/plugins/DataTables/extensions/Buttons/js/dataTables.buttons.min.js"></script>
<script src="../../assets/plugins/DataTables/extensions/Buttons/js/buttons.bootstrap.min.js"></script>
<script src="../../assets/plugins/DataTables/extensions/Buttons/js/buttons.flash.min.js"></script>
<script src="../../assets/plugins/DataTables/extensions/ColReorder/js/dataTables.colReorder.min.js"></script>
<script src="../../assets/plugins/DataTables/extensions/Buttons/js/buttons.print.min.js"></script>
<script src="../../assets/js/demo/table-manage-combine.demo.min.js"></script>
<script src="../../assets/plugins/select2/dist/js/select2.min.js"></script>
<!-- ================== END PAGE LEVEL JS ================== -->
    <script>
        var menuApiUrl = '../menu/API/menu_api.php';
        var attrApiUrl = 'API/attr_api.php';
    </script>
    <script type="text/javascript"> 
        <?php include("attr.js");?>
    </script>  
  </body>
</html> 
