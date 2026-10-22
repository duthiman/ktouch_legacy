<?php 
require("../clude/head.php");
$user_id = $_SESSION['user_id'];
$req_id = $_GET['req_id'];
$counter = $_GET['cntr'];
$no=0;
?>
 
<!-- begin #page-container -->
<div id="page-container" class="page-container fade page-without-sidebar page-header-fixed page-with-top-menu">
  <!-- begin #header -->  
  <?php require("../clude/header.php"); ?>
  <!-- end #header -->  
  <!-- begin #top-menu -->
  <?php require("../clude/top_menu.php"); ?>
  <!-- end #top-menu -->
  
<!-- begin #content -->
<div id="content" class="content"> 
  <?php if (isset($_GET['msg'])): ?>
    <?php if ($_GET['msg'] == 6): ?>
      <div class="alert alert-success alert-dismissible fade show">
        <button type="button" class="close" data-dismiss="alert">&times;</button>
        <strong>Approved!</strong> Requisition updated successfully.
      </div>
    <?php elseif ($_GET['msg'] == 2): ?>
      <div class="alert alert-lime alert-dismissible fade show col-md-6">
        <button type="button" class="close" data-dismiss="alert">&times;</button>
        <strong>Stock</strong> was updated successfully.
      </div>
    <?php endif; ?>
  <?php endif; ?>
  
  <div class="row">
    <!-- MAIN CONTENT - STACKS FIRST ON MOBILE -->
    <div class="col-md-8 col-12 order-2 order-md-1">
      <div class="panel panel-purple" data-sortable-id="index-1">
        <div class="panel-heading">
          <h4 class="panel-title" style="color:white; font-weight:bold;">
            ITEMS TO ISSUE: <?php echo get_counter($counter); ?> COUNTER | REQUISITION #<?php echo $req_id; ?>
          </h4>
        </div>
        <div class="panel-body">
          <!-- CRITICAL FIX: Added table-responsive wrapper WITH min-width -->
          <div class="table-responsive" style="overflow-x: auto; -webkit-overflow-scrolling: touch;">
            <table id="data-table-combine" class="table table-striped table-bordered table-hover w-100">
              <thead class="thead-dark">
                <tr>
                  <th class="text-nowrap">#</th> 
                  <th class="text-nowrap">Item Name</th> 
                  <th class="text-nowrap">Req Qty</th> 
                  <th class="text-nowrap">Current Stock</th>
                  <th class="text-nowrap">Stock Status</th>
                  <th class="text-nowrap">Action</th>          
                </tr>
              </thead>
              <tbody>
                <?php
                $po_items = mysqli_query($connection,"SELECT * FROM kp_counter_req_items WHERE req_id='$req_id' AND issue_status=2");
                if (mysqli_num_rows($po_items) < 1) {
                  $update_status = mysqli_query($connection,"UPDATE kp_counter_req SET issue_status=1, issue_user='$user_id', issue_shift=CURRENT_DATE WHERE req_id='$req_id'") or die(mysqli_error($connection));
                  echo "<script>location.replace('counter_req.php?msg=1');</script>";
                  exit;
                } else {
                  $stockerr = 0;
                  $no = 0;
                  while ($sa = mysqli_fetch_assoc($po_items)) {
                    $item_id = $sa['item_id'];
                    $no++;
                    $current_stock = get_column('kp_items','store_qty',"item_id='$item_id'");
                    $stock_ok = ($current_stock >= $sa['req_qty']);
                    $adjbtn = ($stock_ok) ? '' : "<button class='btn btn-primary btn-sm adj-stock' data-item-id='$item_id'><i class='fas fa-balance-scale'></i> ADJUST STOCK FIRST</button>";
                    if (!$stock_ok) $stockerr++;
                    ?>
                    <tr>
                      <td class="text-nowrap"><?php echo $no; ?></td>
                      <td class="text-nowrap"><?php echo htmlspecialchars(get_item($item_id)); ?></td>
                      <td class="text-nowrap"><?php echo $sa['req_qty']; ?></td>
                      <td class="text-nowrap"><?php echo $current_stock; ?></td>  
                      <td class="text-nowrap">
                        <span class="badge badge-<?php echo $stock_ok ? 'success' : 'danger'; ?> p-2">
                          <?php echo $stock_ok ? 'STOCK OK' : 'LESS STOCK'; ?>
                        </span>
                      </td> 
                      <td class="text-nowrap">
                        <button class="btn btn-success btn-sm issue-item" 
                                data-req-id="<?php echo $req_id; ?>" 
                                data-item-id="<?php echo $item_id; ?>">
                          <i class="fas fa-truck-loading"></i> ISSUE ITEM
                        </button>  
                        <button class="btn btn-danger btn-sm remove-item" 
                                data-req-id="<?php echo $req_id; ?>" 
                                data-item-id="<?php echo $item_id; ?>">
                          <i class="fas fa-trash-alt"></i> REMOVE
                        </button>
                        <?php echo $adjbtn; ?>
                      </td>
                    </tr>
                  <?php
                  }
                }
                ?>
              </tbody>
            </table>
          </div> <!-- END table-responsive -->
        </div> 
      </div>
    </div>
    
    <!-- SIDEBAR - STACKS BELOW ON MOBILE -->
    <div class="col-md-4 col-12 order-1 order-md-2 mb-4 mb-md-0">
      <div class="panel panel-inverse">
        <div class="panel-body text-center">
          <div class="row gx-2">
            <div class="col-12 mb-2">
              <?php if ($stockerr <= 0): ?>
                <button class="btn btn-success btn-block btn-lg issue-all" 
                        data-req-id="<?php echo $req_id; ?>">
                  <i class="fas fa-check-double"></i> ISSUE ALL ITEMS
                </button>
              <?php elseif ($stockerr < $no): ?>
                <button class="btn btn-warning btn-block btn-lg issue-all" 
                        data-req-id="<?php echo $req_id; ?>">
                  <i class="fas fa-exclamation-triangle"></i> ISSUE AVAILABLE ITEMS
                </button>
              <?php endif; ?>
            </div>
            <div class="col-12">
              <button class="btn btn-danger btn-block btn-lg cancel-req" 
                      data-req-id="<?php echo $req_id; ?>">
                <i class="fas fa-times-circle"></i> CANCEL REQUEST
              </button>
            </div>
          </div>
        </div>
      </div>
      
      <div class="alert alert-info fade show">
        <h5><i class="fas fa-info-circle"></i> WHAT TO DO:</h5>
        <ul class="pl-3 mb-0">
          <li>Adjust stock first when the store stock is not enough.</li>
          <li>After stock is adjusted, return to the list and issue the item.</li>
          <li>Issue more or less than the requested quantity if needed.</li>
          <li>Portioned items: 1KG potatoes = 2 chips.</li>
        </ul>
      </div> 
      
      <div class="alert alert-danger fade show">
        <h5><i class="fas fa-exclamation-triangle"></i> CRITICAL NOTES:</h5>
        <ol class="pl-3 mb-0">
          <li>Stock adjustment only changes stock. It does not issue the item automatically.</li>
          <li>Once stock is available, issue the item from this requisition list.</li>
          <li>Clear this list for items to appear at the counter.</li>
          <li>Ask if unsure about the process.</li>
        </ol>
      </div> 
    </div>
  </div>
</div>
<!-- end #content -->

<!-- MODAL CONTAINER -->
<div id="rec-modal" class="modal fade bs-example-modal-lg" tabindex="-1" role="dialog">
  <div class="modal-dialog modal-dialog-centered">
    <div class="modal-content">
      <div class="modal-header">
        <h5 class="modal-title">Processing...</h5>
        <button type="button" class="close" data-dismiss="modal">&times;</button>
      </div>
      <div class="modal-body" id="rec-load" style="min-height: 150px;">
        <div class="text-center py-4">
          <div class="spinner-border text-purple" role="status">
            <span class="sr-only">Loading...</span>
          </div>
          <p class="mt-2">Loading content...</p>
        </div>
      </div>
    </div>
  </div>
</div>

<!-- ================== BASE JS ================== -->
<script src="../../assets/plugins/jquery/jquery-3.2.1.min.js"></script>
<script src="../../assets/plugins/jquery-ui/jquery-ui.min.js"></script>
<script src="../../assets/plugins/bootstrap/4.0.0/js/bootstrap.bundle.min.js"></script>
<script src="../../assets/plugins/slimscroll/jquery.slimscroll.min.js"></script>
<script src="../../assets/plugins/js-cookie/js.cookie.js"></script>
<script src="../../assets/js/theme/default.min.js"></script>
<script src="../../assets/plugins/gritter/js/jquery.gritter.js"></script>
<script src="../../assets/js/apps.min.js"></script>

<!-- ================== DATATABLES JS ================== -->
<script src="../../assets/plugins/DataTables/media/js/jquery.dataTables.js"></script>
<script src="../../assets/plugins/DataTables/media/js/dataTables.bootstrap.min.js"></script>
<script src="../../assets/plugins/DataTables/extensions/Responsive/js/dataTables.responsive.min.js"></script>
<script src="../../assets/js/demo/table-manage-combine.demo.min.js"></script>

<script>
$(document).ready(function() {
  // Initialize DataTables WITH responsive extension disabled for full column visibility
  $('#data-table-combine').DataTable({
    responsive: false, // CRITICAL: Disable column hiding
    paging: true,
    ordering: true,
    info: true,
    columnDefs: [
      { orderable: false, targets: [-1] } // Disable sorting on Action column
    ]
  });
  
  // MODAL HANDLERS - FIXED AJAX TYPOS (typr → type)
  $(document).on('click', '.adj-stock', function(e) {
    e.preventDefault();
    loadModalContent('qty_adj.php', { id: $(this).data('item-id') });
  });
  
  $(document).on('click', '.issue-item', function(e) {
    e.preventDefault();
    loadModalContent('issue_req.php', { 
      id: $(this).data('req-id'), 
      item: $(this).data('item-id') 
    });
  });
  
  // CRITICAL FIX: "ISSUE ALL" BUTTON NOW WORKS
  $(document).on('click', '.issue-all', function(e) {
    e.preventDefault();
    loadModalContent('issueall.php', { id: $(this).data('req-id') });
  });
  
  $(document).on('click', '.remove-item', function(e) {
    e.preventDefault();
    loadModalContent('rem.php', { 
      id: $(this).data('req-id'), 
      item: $(this).data('item-id') 
    });
  });
  
  $(document).on('click', '.cancel-req', function(e) {
    e.preventDefault();
    loadModalContent('cancelreq.php', { id: $(this).data('req-id') });
  });
  
  // Unified modal loader function (FIXES ALL AJAX CALLS)
  function loadModalContent(url, params) {
    $('#rec-modal').modal('show');
    $('#rec-load').html(`
      <div class="text-center py-4">
        <div class="spinner-border text-purple" role="status">
          <span class="sr-only">Loading...</span>
        </div>
        <p class="mt-2">Processing request...</p>
      </div>
    `);
    
    $.ajax({
      url: url,
      type: 'POST', // FIXED TYPO: was "typr"
      data: params,
      dataType: 'html',
      success: function(data) {
        $('#rec-load').html(data);
      },
      error: function() {
        $('#rec-load').html(`
          <div class="alert alert-danger text-center">
            <i class="fas fa-exclamation-triangle fa-2x mb-2"></i>
            <p><strong>Error!</strong> Failed to load content. Please try again.</p>
            <button class="btn btn-primary mt-2" data-dismiss="modal">Close</button>
          </div>
        `);
      }
    });
  }
  
  // Initialize app
  App.init();
});
</script>
</body>
</html>
