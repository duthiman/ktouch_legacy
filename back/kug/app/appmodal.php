<?php 
include('funcs.php');
$user_id = $_SESSION['mwene']; 
$po_id = $_REQUEST['id']; 

$comm = $_REQUEST['comm']; 
if ($po_id==0) {
    //header("Location: init_page.php");
}else{   
    $sup = get_column('kp_po','sup_id',"po_id='$po_id'");
    $day = get_column('kp_po','po_date',"po_id='$po_id'");

    $action="LOADED CONFIRM APPROVAL MODAL";
    $url="../../"; 
    logs($url,'back/kugura/','PURCHASE',$po_id,'-','-',$action);
    logs("",'back/kugura/','PURCHASE',$po_id,'-','-',$action);
 
    $tt = get_column('kp_po_items','total',"po_id='$po_id'");
     
    $no=0;
    $total=0;
    $sup_name = get_column('kp_suppliers','sup_name',"sup_id='$sup'");
     ?>
    <div class="modal-content" style="border:none;box-shadow:0 8px 32px rgba(0,0,0,.18);border-radius:10px;overflow:hidden;">
        <div id="po-results"></div>
        <div class="modal-header py-2 px-3" style="background:linear-gradient(135deg,#16a34a,#15803d);border:none;">
          <h5 class="modal-title text-white mb-0" style="font-size:.88rem;font-weight:700;">
            <i class="fa fa-check-circle me-2"></i>APPROVE PO #<?php echo $po_id; ?>
          </h5>
          <button type="button" class="btn-close btn-close-white" data-bs-dismiss="modal"></button>
        </div>
        <div class="modal-body px-3 py-3">
          <?php if (!empty($comm)): ?>
          <div class="p-2 rounded-2 mb-2" style="background:#fffbeb;border:1px solid #fde68a;">
            <div style="font-size:.58rem;font-weight:700;text-transform:uppercase;color:#92400e;"><i class="fa fa-comment me-1"></i>Approval Comment</div>
            <div style="font-size:.8rem;color:#78350f;"><?php echo htmlspecialchars($comm); ?></div>
          </div>
          <?php endif; ?>
          <div class="d-flex align-items-center gap-2 mb-2 p-2 rounded-2" style="background:#f0fdf4;border:1px solid #bbf7d0;">
            <i class="fa fa-building" style="color:#16a34a;"></i>
            <div>
              <div style="font-size:.58rem;font-weight:700;text-transform:uppercase;color:#6c757d;">Supplier</div>
              <div style="font-size:.85rem;font-weight:700;color:#16a34a;"><?php echo htmlspecialchars($sup_name); ?></div>
            </div>
          </div>
          <table class="table table-sm table-bordered mb-0" style="font-size:.78rem;">
            <thead class="table-light">
              <tr>
                <th style="width:30px;">#</th>
                <th>Item Name</th>
                <th class="text-center" style="width:60px;">Qty</th>
                <th class="text-end" style="width:80px;">Price</th>
                <th class="text-end" style="width:90px;">Amount</th>
              </tr>
            </thead>
            <tbody>
              <?php
              $requisitions = mysqli_query($connection,"SELECT * FROM kp_po_items WHERE po_id='$po_id'") or die(mysqli_error($connection));
              while ($row = mysqli_fetch_assoc($requisitions)) {
                  $item_id = $row['item_id'];
                  $no=$no+1;
                  $amount=$row['total'];
                  $total=$total+$amount;
              ?>
              <tr>
                <td class="text-muted"><?php echo $no; ?></td>
                <td><?php echo get_item($item_id); ?></td>
                <td class="text-center fw-bold"><?php echo $row['qty']; ?></td>
                <td class="text-end"><?php echo number_format($row['price'],2); ?></td>
                <td class="text-end fw-bold"><?php echo number_format($amount,2); ?></td>
              </tr>
              <?php } ?>
            </tbody>
            <tfoot>
              <tr class="table-light">
                <td colspan="4" class="text-end fw-bold">TOTAL</td>
                <td class="text-end fw-bold" style="color:#16a34a;font-size:.9rem;">Ksh <?php echo number_format(round($total,2),2); ?></td>
              </tr>
            </tfoot>
          </table>
        </div>
        <div class="modal-footer py-2 px-3" style="border-top:1px solid #f0f0f0;">
          <button type="button" class="btn btn-sm btn-outline-secondary" data-bs-dismiss="modal" style="font-size:.76rem;">
            <i class="fa fa-times me-1"></i>Cancel
          </button>
          <button type="button" class="btn btn-sm" id="btn-approve-po"
                  style="font-size:.76rem;font-weight:700;background:#16a34a;color:#fff;">
            <i class="fa fa-check-circle me-1"></i>APPROVE PURCHASE ORDER
          </button>
        </div>
    </div>
    <script>
    (function(){
      $(document).off('click.approvePo').on('click.approvePo','#btn-approve-po',function(){
        var $b=$(this);
        $b.prop('disabled',true).html('<span class="spinner-border spinner-border-sm me-1"></span>Approving…');
        $.ajax({
          url:'kug/app/apppo.php',
          type:'POST',
          data:{id:'<?php echo $po_id; ?>',comm:'<?php echo addslashes($comm); ?>'},
          dataType:'html'
        }).done(function(d){
          $('#po-results').html(d);
        }).fail(function(){
          $b.prop('disabled',false).html('<i class="fa fa-check-circle me-1"></i>APPROVE PURCHASE ORDER');
          $('#po-results').html('<div class="alert alert-danger py-1 small">Something went wrong. Please try again.</div>');
        });
      });
    })();
    </script>
    <?php 
}
?>