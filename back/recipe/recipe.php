<?php
include('../clude/head.php');
$item_id = (int)($_GET['id'] ?? 0);
$item_name = $item_id > 0 ? (string)get_column('kp_items', 'name', "item_id='" . $item_id . "'") : '';
?> 
<!-- ================== BEGIN SELECT 2================== --> 
<link href="../../assets/plugins/select2/dist/css/select2.min.css" rel="stylesheet" />
<style>
  .recipe-layout { display:flex; flex-wrap:wrap; align-items:flex-start; }
  .recipe-main-col { float:none; flex:0 0 66.66666667%; max-width:66.66666667%; }
  .recipe-help-col { float:none; flex:0 0 33.33333333%; max-width:33.33333333%; }
  @media (max-width: 767px) {
    .recipe-main-col, .recipe-help-col { flex:0 0 100%; max-width:100%; }
  }
</style>
<!-- ================== END SELECT 2 ================== -->    
<!-- begin #page-container -->
<div id="page-container" class="fade page-without-sidebar page-header-fixed">
  <!-- begin #header -->  
    <?php 
    require("../clude/header.php");
    ?>
    <!-- end #header -->  
    <!-- begin #sidebar -->
    <?php 
    //require("../clude/menu.php");
    ?>
    <!-- end #sidebar -->
    <!-- begin #content -->
    <div id="content" class="content">
        <!-- begin row -->
        <div class="row recipe-layout">
            <!-- begin col-8 -->
            <div class="col-8 recipe-main-col">
                <!-- begin panel -->
                <div class="panel panel-purple" data-sortable-id="index-1">
                    <div class="panel-heading" style="background:#6f42c1;color:#fff;padding:14px 18px;"> 
                        <div style="font-size:12px;font-weight:700;letter-spacing:.08em;text-transform:uppercase;opacity:.85;">Recipe settings</div>
                        <h3 class="p-1" style="color:#fff;margin:4px 0 0;font-weight:700;">SET RECIPE FOR</h3>
                        <div id="recipe-item-name" style="color:#fff;font-size:20px;font-weight:700;line-height:1.25;word-break:break-word;"><?php echo htmlspecialchars($item_name, ENT_QUOTES, 'UTF-8'); ?></div>
                        <div style="color:rgba(255,255,255,.8);font-size:12px;margin-top:5px;">Item ID: <?php echo $item_id; ?></div>
                    </div>
                    <!-- begin panel-body -->
                    <div class="panel-body">
                        <div id="results"></div>
                        <div class="form-group row">
                            <div class="form-group col-4 mb-0"></div>
                            <div class="form-group col-8 row">
                                <div class="col-2">  
                                    <button onclick="$('#qty').val('0.25');submit_recipe_item(<?php echo $item_id; ?>)" id="btnSubmit" value="Add Item" class="btn btn-outline-info pull-right"  style="width: 100%;">1/4</button>
                                </div>
                                <div class="col-2">  
                                    <button onclick="$('#qty').val('0.5');submit_recipe_item(<?php echo $item_id; ?>)" id="btnSubmit" value="Add Item" class="btn btn-outline-info pull-right"  style="width: 100%;">1/2</button>
                                </div>
                                <div class="col-2">  
                                    <button onclick="$('#qty').val('0.75');submit_recipe_item(<?php echo $item_id; ?>);" id="btnSubmit" value="Add Item" class="btn btn-outline-info pull-right"  style="width: 100%;">3/4</button>
                                </div>
                                <div class="col-2">  
                                    <button onclick="$('#qty').val('1');submit_recipe_item(<?php echo $item_id; ?>);" id="btnSubmit" value="Add Item" class="btn btn-outline-info pull-right"  style="width: 100%;">1</button>
                                </div>
                                <div class="col-3">  
                                    <button onclick="$('#qty').val('0');;" id="btnSubmit" value="Add Item" class="btn btn-outline-danger pull-right"  style="width: 100%;">OTHERS</button>
                                </div>
                            </div> 
                        </div>
                        <div class="form-group row">
                            <div class="form-group col-6">
                                <label>Choose Recipe Items:</label>
                                <select id="raw_id" name="item_id" class="pickers form-control" data-size="10" data-show-subtext="true" data-live-search="true">
                                    <option value="0">--Select Ingridient/R </option>
                                </select>
                            </div>
                            <div class="form-group col-4">
                                <label class=" control-label">Enter Qty</label>
                                <input id="qty"   class="form-control" placeholder="quantity">
                        </div>
                        <div>
                            <a onclick="submit_recipe_item(<?php echo $item_id; ?>);" id="btnSubmit" value="Add Item" class="btn btn-outline-success pull-right"  style="padding: 5px 5px 5px 5px;margin-top: 26px;"><i class="fa fa-plus"></i> ADD ITEMS</a>
                        </div>
                    </div>
                    <div class="p-10" id="items_list"> </div>
                </div>
            </div>
            <!-- end panel / end col-8 -->
            </div></div>
            <div class="col-4 recipe-help-col">
                <div class="panel panel-purple" data-sortable-id="index-1">
                    <div class="panel-body">
                        <div class="alert alert-info fade show">
                            <h5>WHAT TO DO:</h5>
                            <ul class="p-l-25 m-b-15">
                                <li>The process of adding item with recipe is as follows
                                    <ol>
                                        <li>Add the main item or the ingridients first <i class="text-blue"> i.e. RAW PORK</i></li>
                                        <li>After adding the main items add the items with recipe <i class="text-blue"> i.e. 1/4 pork choma, 1/4 stir fry pork,1/2 stri fry pork</i></li>
                                        <li>On the menu click add recipe on an item to set the recipe </li>

                                        <li>By this process you are already on this page</li>
                                    </ol>
                                </li> 
                            </ul> 
                        </div> 
                        <div class="alert alert-warning fade show">
                            <h5>POINTS TO NOTE:</h5>
                            <ol class="p-l-25 m-b-15">
                                <li>By clicking the quantities buttons it adds the right quantity without mistake <img src="recipeqtybtn.png" style="width: 80%">
                                </li>
                                <li>By clicking others button the system expects you to enter the quantities required for your recipe</li>
                                <li class="mb-2">The  <a  type="button" class=" text-red btn btn-outline-danger btn-sm"  ><i class="fa fa-lg fa-trash" ></i> Remove Item</a> Button removes an item from the recipe list if you make a mistake</li>
                                <li class="mb-2">The  <button class="btn btn-outline-success btn-sm">Finish Adding Recipe</button> Button finishes the recipe setting process</li>
                                <li>The  <button class="btn btn-outline-danger btn-sm">UPDATE/CLEAR RECIPE SETTINGS</button> Button clears the items to have no recipe </li>
                                <li><h3 style="color:red;">If you dont understand the process ask !!!!</h3></li>   
                            </ol> 
                        </div>
                    </div>
                </div>
            </div>
            <!-- end col-6 -->
        </div>
    <!--  end #content -->
</div> 
<!-- end page container -->
	
<?php
 include('../clude/foot.php');
?>
<!-- ================== BEGIN PAGE LEVEL JS ================== -->
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
    load_recipe_bootstrap(<?php echo $item_id; ?>);
    $('.pickers').select2({ });
  });
</script>  
<script src="recipe.js"></script>
</body>
</html> 
 
