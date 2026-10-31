<?php 
  require("../clude/head.php");
  // Keep the summary cards in sync with the catalogue instead of displaying
  // design-time placeholder values.
  $menu_summary = [
    'items' => 0,
    'categories' => 0,
    'subcategories' => 0,
    'departments' => 0,
    'uom' => 0,
    'variants' => 0,
  ];

  if (isset($connection)) {
    $menu_table_exists = function ($table) use ($connection) {
      $safe_table = mysqli_real_escape_string($connection, $table);
      $result = mysqli_query($connection, "SHOW TABLES LIKE '{$safe_table}'");
      return $result && mysqli_num_rows($result) > 0;
    };

    $menu_summary_queries = [
      'items' => "SELECT COUNT(*) AS total FROM kp_items",
      'categories' => "SELECT COUNT(*) AS total FROM kp_cat",
      'subcategories' => "SELECT COUNT(*) AS total FROM kp_sub_cat",
      // Departments are stored as IDs on catalogue items in this schema.
      'departments' => "SELECT COUNT(DISTINCT dpt) AS total FROM kp_items WHERE dpt > 0",
      'uom' => "SELECT COUNT(*) AS total FROM kp_items_uom",
      'variants' => "SELECT COUNT(*) AS total FROM kp_inventory_stock_variants WHERE is_active = 1",
    ];

    foreach ($menu_summary_queries as $key => $query) {
      $table_by_summary = [
        'items' => 'kp_items',
        'categories' => 'kp_cat',
        'subcategories' => 'kp_sub_cat',
        'departments' => 'kp_items',
        'uom' => 'kp_items_uom',
        'variants' => 'kp_inventory_stock_variants',
      ];
      if (!$menu_table_exists($table_by_summary[$key])) {
        continue;
      }
      $result = mysqli_query($connection, $query);
      if ($result && ($row = mysqli_fetch_assoc($result))) {
        $menu_summary[$key] = (int)($row['total'] ?? 0);
      }
    }
  }
  $folder="back/prods/";
  //thesion_page($folder);
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
    <!-- ================== BEGIN DATATABLE================== --> 
    <link href="../../assets/plugins/select2/dist/css/select2.min.css" rel="stylesheet" />
    <link href="menu.css" rel="stylesheet" />
    <!-- ================== END DATATABLE ================== -->  
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
      <div id="content" class="content">
        <section class="menu-hero">
          <div><span class="menu-kicker">OPERATIONS</span><h1><i class="fa fa-cubes"></i> Inventory Management</h1><p>Manage the product catalogue, categories, variants, and inventory setup from one workspace.</p></div>
          <div class="menu-hero-actions">
            <div class="menu-drop"><button type="button" class="menu-hero-btn menu-drop-toggle"><i class="fa fa-archive"></i> Catalogue <i class="fa fa-angle-down"></i></button><div class="menu-drop-list"><a href="#data-table-combine"><i class="fa fa-cubes"></i> Master Inventory</a><a href="#data-table-combine"><i class="fa fa-tags"></i> Master Attributes</a><a href="#data-table-combine"><i class="fa fa-list"></i> Attribute Options</a><a href="#data-table-combine"><i class="fa fa-pencil"></i> Units of Measure</a><a href="#data-table-combine"><i class="fa fa-clone"></i> Stock Variants</a><a href="#data-table-combine"><i class="fa fa-sliders"></i> Inventory Settings</a></div></div>
            <a href="#modal" data-toggle="modal" class="menu-hero-btn primary menu-quick-add-product"><i class="fa fa-plus"></i> New Product</a><div class="menu-drop"><button type="button" class="menu-hero-btn menu-drop-toggle"><i class="fa fa-archive"></i> Quick Add <i class="fa fa-angle-down"></i></button><div class="menu-drop-list"><a href="#modal" data-toggle="modal" class="menu-quick-add-category"><i class="fa fa-folder"></i> New Category</a><a href="#modal" data-toggle="modal" class="menu-quick-add-subcategory"><i class="fa fa-cubes"></i> New Subcategory</a><a href="#modal" data-toggle="modal" class="menu-quick-add-settings"><i class="fa fa-building"></i> New Department</a></div></div>
            <a class="menu-hero-btn soft" href="#data-table-combine"><i class="fa fa-clone"></i> Variants</a><a class="menu-hero-btn purple" href="#data-table-combine"><i class="fa fa-sliders"></i> Settings</a>
          </div>
        </section>
        <div class="menu-summary-row">
          <div class="menu-summary-card"><i class="fa fa-cubes menu-summary-icon"></i><span>ITEMS</span><strong><?php echo $menu_summary['items']; ?></strong><small>active products</small></div>
          <div class="menu-summary-card blue"><i class="fa fa-list-alt menu-summary-icon"></i><span>CATEGORIES</span><strong><?php echo $menu_summary['categories']; ?></strong><small>item categories</small></div>
          <div class="menu-summary-card purple"><i class="fa fa-tags menu-summary-icon"></i><span>SUBCATEGORIES</span><strong><?php echo $menu_summary['subcategories']; ?></strong><small>sub-categories</small></div>
          <div class="menu-summary-card orange"><i class="fa fa-building menu-summary-icon"></i><span>DEPARTMENTS</span><strong><?php echo $menu_summary['departments']; ?></strong><small>item departments</small></div>
          <div class="menu-summary-card green"><i class="fa fa-pencil menu-summary-icon"></i><span>UOM</span><strong><?php echo $menu_summary['uom']; ?></strong><small>active UOMs</small></div>
          <div class="menu-summary-card cyan"><i class="fa fa-clone menu-summary-icon"></i><span>VARIANTS</span><strong><?php echo $menu_summary['variants']; ?></strong><small>stock variants</small></div>
        </div>
        <div class="row">
          <aside class="menu-sidebar hidden-print">
            <div class="menu-side-card"><h3><i class="fa fa-th-large"></i> CATALOGUE</h3><a class="selected"><i class="fa fa-cubes"></i> Master Inventory</a><a><i class="fa fa-tags"></i> Master Attributes</a><a><i class="fa fa-list"></i> Attribute Options</a><a><i class="fa fa-pencil"></i> Units of Measure</a><a><i class="fa fa-clone"></i> Stock Variants</a><a><i class="fa fa-sliders"></i> Inventory Settings</a></div>
            <div class="menu-side-card"><h3><i class="fa fa-plus-circle"></i> QUICK ADD</h3><a href="#modal" data-toggle="modal" class="menu-add-product"><i class="fa fa-plus-circle"></i> New Product</a><a><i class="fa fa-folder"></i> New Category</a><a><i class="fa fa-cubes"></i> New Subcategory</a><a><i class="fa fa-building"></i> New Department</a><a><i class="fa fa-cog"></i> New Attribute</a><a><i class="fa fa-object-group"></i> Merge Items</a><a><i class="fa fa-clone"></i> New Variant</a></div>
          </aside>
          <div class="col-md-12">
            <div class="panel panel-default hidden-print menu-filter-panel">
              <div class="menu-section-title"><i class="fa fa-filter"></i> Filter Inventory</div>
              <div class="panel-body">
                <p class="menu-filter-help">Narrow the item list by department, category, subcategory, or product type.</p>
                <div class="form-group col-3 filter-department">
                      <label for="dpt">Department</label>
                      <select class="form-control pickers" id="dpt" name="dpt">
                        <option value="0">--Select a Department--</option><option value="1">BAR</option><option value="2">KITCHEN</option>
                      </select>
                </div>
                <div class="form-group col-3 filter-category">
                      <label for="cat_id">Category</label>
                      <select class="form-control pickers" id="cat_id" name="cat_id"><option value="0">---SELECT CATEGORY----</option></select>
                </div>
                <div class="form-group col-3 filter-subcategory">
                      <label for="sub_id">Sub Category</label>
                      <select class="form-control pickers" id="sub_id" name="sub_id"><option value="0">---SELECT A SUB CATEGORY----</option></select>
                </div>
                <div class="form-group col-3 filter-stock">
                      <label for="stock">Product Type</label>
                      <select class="form-control" id="stock" name="stock">
                        <option value="0">----Select a Type -----</option><option value="1">Inventory</option><option value="2">Non Inventory</option><option value="3">Service</option>
                      </select>
                </div>
                <div class="form-group col-3 filter-counter">
                      <label for="counter_id">Counter</label>
                      <select class="form-control" id="counter_id" name="counter_id">
                        <option value="0">--All Counters--</option>
                      </select>
                </div>
                <div class="form-group col-12 counter-assignment-tabs-wrap" style="display:none;">
                  <label>Counter item view</label>
                  <div class="btn-group counter-assignment-tabs" role="tablist">
                    <button type="button" class="btn btn-primary counter-assignment-tab active" data-assignment-mode="assigned">Assigned Items</button>
                    <button type="button" class="btn btn-default counter-assignment-tab" data-assignment-mode="unassigned">Not Assigned</button>
                  </div>
                </div>
                <div class="menu-filter-actions"><button type="button" class="btn btn-primary" id="apply-menu-filters"><i class="fa fa-search"></i> Apply</button><button type="button" class="btn btn-default" id="reset-menu-filters"><i class="fa fa-undo"></i> Reset</button></div>
              </div>
            </div>
          </div>
          <div class="col-md-12">
          <div class="panel panel-default hidden-print menu-table-panel" style="margin-bottom: 1px;">
            <div class="menu-table-header">
              <div><h2><i class="fa fa-cubes"></i> Inventory Items</h2><span class="menu-active-count"><i class="fa fa-archive"></i> 6 active items</span></div>
              <div class="menu-table-actions"><a href="items_import.php" id="import" class="btn"> <i class="fa fa-upload"></i> Import</a><a href="#export-modal" data-toggle="modal" id="export-menu" class="btn"><i class="fa fa-download"></i> Export</a><a href="#modal" data-toggle="modal" id="prods-add" class="btn"><i class="fa fa-plus"></i> Add New Item</a></div>
            </div>
            <div class="panel panel-default hidden-print menu-bulk-panel" style="margin-bottom: 1px;">
              <div class="panel-body p-5"> 
                <div class="form-group row">
                  <div class="col-md-12 pull-right"> 
                    <a href="#modal" data-toggle="modal" class="btn btn-xs btn-lime m-3 pull-right" id="prods-add-old" style="display:none">NEW PRODUCT&nbsp;<i class="fa fa-plus"></i></a>
                    <span class="menu-bulk-label">Bulk actions</span>
                    <a href="items_import.php" id="import-old" name="filter" class="btn btn-primary btn-xs m-3 pull-right" style="display:none">IMPORT</a> 
                    <a href="#export-modal" data-toggle="modal" id="export-menu-old" class="btn btn-success btn-xs m-3 pull-right" style="display:none">EXPORT</a>
                    <a href="#modal" data-toggle="modal" id="del"  name="filter" class="act_btn btn btn-danger btn-xs  m-3 pull-right">Discontinue</a>
                    <a href="#modal" data-toggle="modal" id="tax"  name="filter" class="act_btn btn btn-info btn-xs  m-3 pull-right">Change Tax</a>  
                    <a href="#modal" data-toggle="modal" id="sub_cat"  name="filter" class="act_btn btn btn-purple btn-xs  m-3 pull-right">Change Sub Category</a> 
                    <a href="#modal" data-toggle="modal" id="cat"  name="filter" class="act_btn btn btn-yellow btn-xs  m-3 pull-right">Change Category</a> 
                    <a href="#modal" data-toggle="modal" id="set"  name="filter" class="act_btn btn btn-grey btn-xs  m-3 pull-right">Change Settings</a> 
                    <a href="javascript:;" id="assign-selected-counter" class="btn btn-primary btn-xs m-3 pull-right" style="display:none"><i class="fa fa-building"></i> Assign to Counter</a>
                  </div> 
                </div>
              </div>
            </div> 
            <div class="menu-reference-toolbar hidden-print">
              <div class="menu-toolbar-left"><select id="menu-page-length" class="form-control input-sm"><option value="10">10</option><option value="25">25</option><option value="50">50</option></select><span>entries</span></div>
              <label class="menu-toolbar-search">Search: <input type="search" id="menu-search" class="form-control input-sm" placeholder="Search items..." /></label>
            </div>
            <div id="panel-body" class="p-5">
              <table id="data-table-combine" class="table table-striped table-bordered text-black">
                <thead> 
                  <tr>
                    <th><input type="checkbox" name="checkAll" class="checkAll">All</th>
                    <th>Item ID</th>
                    <th style="width: 150px;">Name</th>
                    <th>Category</th>
                    <th>Sub Category</th>
                    <th>Department</th>
                    <th>Buying Price</th>
                    <th>Price</th>
                    <th>Happy Hour</th>
                    <th>Menu Item</th>
                    <th>Decimal</th>
                    <th>Stock</th>
                    <th>Recipe</th>
                    <th>Portioning</th>
                    <th>Tots</th>
                    <th>Stock Take Type</th>
                    <th>UOM</th>
                    <th>Tax</th>
                    <th>Actions</th>
                  </tr>
                </thead>
              </table>
            </div>
          </div>
        </div>
      </div>
      <div class="modal fade" id="export-modal" tabindex="-1" role="dialog" aria-hidden="true">
        <div class="modal-dialog">
          <div class="modal-content">
            <div class="modal-header">
              <button type="button" class="close" data-dismiss="modal" aria-hidden="true">×</button>
              <h4 class="modal-title">Export Menu Rows</h4>
            </div>
            <div class="modal-body">
              <div id="export-results"></div>
              <p class="text-muted">Select the headers you want in the export. The file will export the rows currently loaded in the table view.</p>
              <div id="export-columns" class="row"></div>
            </div>
            <div class="modal-footer">
              <a href="javascript:;" class="btn btn-xs btn-danger" data-dismiss="modal">Close</a>
              <button type="button" id="run-export" class="btn btn-xs btn-success">Export Excel</button>
            </div>
          </div>
        </div>
      </div>
    </div> 
    <?php 
      require("../clude/foot.php");
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
    <script src="https://cdn.jsdelivr.net/npm/sweetalert2@11"></script>
    <script>
      var menuApiUrl = 'API/menu_api.php';
    </script>
    <script src="menu.js"></script>
    <script src="menu-page.js"></script>
  </body>
</html>
  
        
