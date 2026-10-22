<?php 
require("../clude/head.php");
$no=0;

?>   <!-- ================== BEGIN PAGE LEVEL STYLE ================== -->
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
<div id="page-container" class="page-container fade page-without-sidebar page-header-fixed page-with-top-menu">
  <?php require("../clude/header.php"); ?>
  <?php require("../clude/top_menu.php"); ?>
  <!-- end #sidebar -->
  <!-- begin #content -->
	<div id="content" class="content">
		<!-- begin row -->
		<div class="row">
      <!-- begin col-12 -->
      <div class="col-lg-12">
        <!-- begin panel -->
        <div class="panel panel-purple" data-sortable-id="index-1" style="padding: 1px 15px;">
          <div class="panel-heading"> 
            <!--  <div class="pull-right">
                <Button href="#prods-modal" class="btn btn-sm btn-lime pull-right" data-toggle="modal" id="prods-add" ><i class="fa fa-plus"></i> &nbsp;NEW PRODUCT</Button> &nbsp; &nbsp; &nbsp; &nbsp;
            </div> -->
            <span style="color:white; font-size: 28px;"><B>MAIN STORE STOCK LIST</B></span>
            </div>
            <div class="panel-body">
              <?php
              $get_items = mysqli_query($connection,"SELECT * FROM kp_items WHERE del_status=2 AND stock=1 ORDER BY name ASC") or die("Could not get any items");
              $stock_rows = $get_items ? mysqli_fetch_all($get_items, MYSQLI_ASSOC) : [];
              $stock_summary = mysqli_fetch_assoc(mysqli_query($connection, "SELECT COUNT(*) AS item_count, SUM(CASE WHEN store_qty <= store_reorder THEN 1 ELSE 0 END) AS reorder_count, SUM(CASE WHEN store_qty <= 0 THEN 1 ELSE 0 END) AS out_count FROM kp_items WHERE del_status=2 AND stock=1")) ?: [];
                if (count($stock_rows) < 1) {
                echo "There are no registered items in the database";
              }else{
                ?>
                <style>
                  .stock-summary{display:grid;grid-template-columns:repeat(5,minmax(0,1fr));gap:12px;margin-bottom:16px}.stock-summary-card{border:2px solid currentColor;border-radius:12px;padding:14px 16px;background:#fff;box-shadow:0 5px 16px rgba(30,41,59,.08);position:relative;overflow:hidden;cursor:pointer}.stock-summary-card:before{content:'';position:absolute;left:0;top:0;bottom:0;width:5px;background:currentColor}.stock-summary-card strong{display:block;font-size:24px;line-height:1.1}.stock-summary-card span{display:block;margin-top:5px;font-size:11px;text-transform:uppercase;letter-spacing:.5px;opacity:.85;font-weight:700}.stock-summary-card.items{color:#5b3b8c}.stock-summary-card.qty{color:#167c80}.stock-summary-card.reorder{color:#c76b18}.stock-summary-card.status{color:#c62828}.stock-filters{display:flex;gap:10px;margin-bottom:12px}.stock-filters select{max-width:240px}.stock-print-actions{display:flex;gap:8px;justify-content:flex-end;margin-bottom:12px}.stock-print-actions .btn{font-weight:700}.stock-print-only{display:none}@media(max-width:767px){.stock-summary{grid-template-columns:repeat(2,minmax(0,1fr))}.stock-filters{flex-direction:column}.stock-filters select{max-width:none}.stock-print-actions{justify-content:stretch}.stock-print-actions .btn{flex:1}}
                  @media print{body.stock-print-thermal #sidebar,body.stock-print-thermal #header,body.stock-print-thermal .navbar,body.stock-print-thermal .hidden-print,body.stock-print-thermal .stock-print-actions{display:none!important}body.stock-print-thermal #content{margin:0!important;padding:0!important}.stock-print-thermal .panel{border:0!important;box-shadow:none!important}.stock-print-thermal .stock-summary{display:none}.stock-print-thermal table{font-size:10px;width:100%}.stock-print-thermal th:nth-child(1),.stock-print-thermal td:nth-child(1),.stock-print-thermal th:nth-child(5),.stock-print-thermal td:nth-child(5){display:none!important}@page{size:80mm auto;margin:4mm}body.stock-print-a4 #sidebar,body.stock-print-a4 #header,body.stock-print-a4 .navbar,body.stock-print-a4 .hidden-print,body.stock-print-a4 .stock-print-actions{display:none!important}body.stock-print-a4 #content{margin:0!important;padding:0!important}.stock-print-a4 .panel{border:0!important;box-shadow:none!important}.stock-print-a4 .stock-print-only{display:block}.stock-print-a4 .stock-summary{grid-template-columns:repeat(4,1fr)}@page{size:A4 portrait;margin:12mm}}
                </style>
                <div class="stock-print-actions hidden-print">
                  <button type="button" class="btn btn-primary" onclick="printStock('thermal')"><i class="fa fa-print"></i> Thermal Print</button>
                  <button type="button" class="btn btn-success" onclick="printStock('a4')"><i class="fa fa-print"></i> A4 Print</button>
                </div>
                <div class="stock-summary hidden-print">
                  <div class="stock-summary-card items stock-filter-card" data-stock-filter="all"><strong><?php echo (int)($stock_summary['item_count'] ?? 0); ?></strong><span>Stock Items</span></div>
                  <div class="stock-summary-card reorder stock-filter-card" data-stock-filter="reorder"><strong><?php echo (int)($stock_summary['reorder_count'] ?? 0); ?></strong><span>Below Reorder Level</span></div>
                  <div class="stock-summary-card status stock-filter-card" data-stock-filter="out"><strong><?php echo (int)($stock_summary['out_count'] ?? 0); ?></strong><span>Out of Stock</span></div>
                  <div class="stock-summary-card qty stock-filter-card" data-stock-filter="all"><strong><?php echo (int)($stock_summary['item_count'] ?? 0); ?></strong><span>Items Count</span></div>
                  <div class="stock-summary-card items stock-filter-card" data-stock-filter="all"><strong><?php echo count($stock_rows); ?></strong><span>Items in List</span></div>
                </div>
                <div class="stock-filters hidden-print"><select id="stock-category-filter" class="form-control"><option value="">All Categories</option><?php $cats=[]; foreach($stock_rows as $sr){$v=(string)($sr['cat_id']??($sr['category_id']??''));if($v!==''&&!isset($cats[$v]))$cats[$v]=$v;} foreach($cats as $v){echo '<option value="'.htmlspecialchars($v,ENT_QUOTES,'UTF-8').'">Category '.htmlspecialchars($v,ENT_QUOTES,'UTF-8').'</option>';} ?></select><select id="stock-subcategory-filter" class="form-control"><option value="">All Subcategories</option><?php $subs=[]; foreach($stock_rows as $sr){$v=(string)($sr['subcat_id']??($sr['sub_category_id']??($sr['sub_category']??'')));if($v!==''&&!isset($subs[$v]))$subs[$v]=$v;} foreach($subs as $v){echo '<option value="'.htmlspecialchars($v,ENT_QUOTES,'UTF-8').'">Subcategory '.htmlspecialchars($v,ENT_QUOTES,'UTF-8').'</option>';} ?></select></div>
                <div class="stock-print-only"><h2>MAIN STORE STOCK LIST</h2><p>Printed <?php echo date('Y-m-d H:i'); ?></p></div>
                <table id="data-table-combine" class="table table-striped table-bordered">
                  <thead>
                    <tr>
                      <th>Item ID</th>                                    
                      <th>Item Name</th>   
                      <th>Quantity</th>
                      <th>Reorder Level</th> 
                      <th>Action</th>  
                    </tr>
                  </thead>
                  <tbody>
                    <?php 
                    foreach ($stock_rows as $row) {
                      $no=$no+1;
                      ?>
                        <tr data-stock-category="<?php echo htmlspecialchars((string)($row['cat_id']??($row['category_id']??'')),ENT_QUOTES,'UTF-8'); ?>" data-stock-subcategory="<?php echo htmlspecialchars((string)($row['subcat_id']??($row['sub_category_id']??($row['sub_category']??''))),ENT_QUOTES,'UTF-8'); ?>" data-stock-qty="<?php echo (float)$row['store_qty']; ?>" data-stock-reorder="<?php echo (float)$row['store_reorder']; ?>">
                          <td><?php echo $row['item_id'];?></td> </td>
                          <td class="name" data-id1="<?php echo $row["item_id"]?>" contenteditable><?php echo $row['name']; ?></td>  
                          <td class="qty" data-id1="<?php echo $row["item_id"]?>" contenteditable><?php echo $row['store_qty'];?></td>
                          <td class="reorder" data-id1="<?php echo $row["item_id"]?>" contenteditable><?php echo $row['store_reorder'];?></td> 
                          <td class="with-btn-group" nowrap>
                            <div class="btn-group">
                                <a href="#" class="btn btn-lime btn-xs width-60">Actions</a>
                                <a href="#" class="btn btn-lime btn-xs dropdown-toggle width-30 no-caret" data-toggle="dropdown">
                                  <span class="caret"></span>
                                </a>
                                <ul class="dropdown-menu pull-right">
                                  <li><a data-toggle="modal" data-target="#stock-modal" data-id="<?php echo $row["item_id"]; ?>" id="stock-adj">Adjust Quantity</a></li>
                                  <li><a data-toggle="modal" data-target="#stock-modal" data-id="<?php echo $row["item_id"]; ?>" id="re-adj">Set New Reorder</a></li>
                                  <li><a data-toggle="modal" data-target="#stock-modal" data-id="<?php echo $row["item_id"]; ?>" id="stock-levels">View Stock Levels</a></li>
                                 <!--  <li><a href="#modal-dialog" data-toggle="modal">Stok History</a></li>
                                  <li><a href="#">Item Movment Chart</a></li> -->
                                </ul>
                            </div>
                        </td>
                        </tr>
                    <?php
                      }
                    ?>
                    </tbody>
                </table>
                <table id="stock-print-table" style="display:none;"><thead><tr><th>Item ID</th><th>Item Name</th><th>Quantity</th><th>Reorder Level</th></tr></thead><tbody>
                <?php foreach ($stock_rows as $row) { ?><tr><td><?php echo (int)$row['item_id']; ?></td><td><?php echo htmlspecialchars($row['name'], ENT_QUOTES, 'UTF-8'); ?></td><td><?php echo htmlspecialchars($row['store_qty'], ENT_QUOTES, 'UTF-8'); ?></td><td><?php echo htmlspecialchars($row['store_reorder'], ENT_QUOTES, 'UTF-8'); ?></td></tr><?php } ?>
                </tbody></table>
                <?php
                }

                ?>
					</div>
        </div>
                    <!-- end panel -->
                </div>
                <!-- end col-6 -->


            </div>
            <!-- end row -->
		</div>
		<!-- end #content -->
<div  id="stock-modal" class="modal fade bs-example-modal-lg" tabindex="-1" role="dialog" aria-labelledby="myModalLabel" style="display: none;">
   <div class="modal-dialog"  id="stock-load"></div>
</div>
</div>
	<!-- end page container -->

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
<script type="text/javascript">
  $(document).ready(function() {  
      TableManageCombine.init();     
  });
  <?php include("inve.js");?>
</script>  
<script>
function printStock(mode){
  var table = document.getElementById('stock-print-table');
  if (!table) return;
  var popup = window.open('', '_blank', 'width=900,height=700');
  if (!popup) { window.alert('Please allow pop-ups to print the stock list.'); return; }
  var thermal = mode === 'thermal';
  popup.document.write('<!doctype html><html><head><title>Stock List</title><style>' +
    '@page{size:' + (thermal ? '80mm auto' : 'A4 portrait') + ';margin:' + (thermal ? '4mm' : '12mm') + '}' +
    'body{font-family:Arial,sans-serif;color:#222;font-size:' + (thermal ? '10px' : '12px') + ';margin:0}' +
    'h2{font-size:' + (thermal ? '15px' : '22px') + ';margin:0 0 4px}p{margin:0 0 12px;color:#666}' +
    'table{display:table!important;border-collapse:collapse;width:100%}th,td{border:1px solid #999;padding:' + (thermal ? '4px' : '7px') + ';text-align:left}th{background:#eee}' +
    (thermal ? 'th:first-child,td:first-child,th:last-child,td:last-child{display:none}' : '') +
    '</style></head><body><h2>MAIN STORE STOCK LIST</h2><p>Printed <?php echo date('Y-m-d H:i'); ?></p>' + table.outerHTML + '</body></html>');
  popup.document.close(); popup.focus(); popup.print();
  popup.onafterprint = function(){ popup.close(); };
}
</script>
</body>
</html>
  
 
