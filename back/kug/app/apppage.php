<?php
include('funcs.php');
$user_id = $_SESSION['mwene'];

if (!isset($_GET['id'])) {
    echo '<div class="alert alert-danger">Invalid PO ID.</div>';
    exit;
}
$po_id = (int)$_GET['id'];

$sales_1 = mysqli_query($connection, "SELECT * FROM kp_po WHERE po_id='$po_id'") or die(mysqli_error($connection));
$sales   = mysqli_fetch_assoc($sales_1);
if (!$sales) {
    echo '<div class="alert alert-danger">Purchase order not found.</div>';
    exit;
}

$sup_id   = (int)$sales['sup_id'];
$po_date  = $sales['po_date'];
$ord_user = (int)$sales['ord_user'];

$sup_r   = mysqli_query($connection, "SELECT * FROM kp_suppliers WHERE sup_id='$sup_id'") or die(mysqli_error($connection));
$sup     = mysqli_fetch_assoc($sup_r);
$bname   = $sup ? $sup['sup_name']    : '—';
$mobile  = $sup ? $sup['sup_phone']   : '—';
$address = $sup ? $sup['sup_address'] : '—';

$raised_by = get_column('kp_users', 'username', "user_id='$ord_user'");

$po_items_r = mysqli_query($connection, "SELECT * FROM kp_po_items WHERE po_id='$po_id'") or die(mysqli_error($connection));

$no   = 0;
$sum  = 0;
$sumd = 0;
$items_html = '';
while ($data = mysqli_fetch_assoc($po_items_r)) {
    $row_id   = (int)$data['id'];             // unique row PK
    $item_id  = (int)$data['item_id'];
    $price    = $data['price'];
    $quantity = $data['qty'];
    $disc     = $data['disc'];
    $total    = $data['total'];
    $no++;
    $sum  += $total;
    $sumd += $disc;

    // Parse snap for receipt_desc + item name
    $snap = [];
    if (!empty($data['snap'])) { $d = json_decode($data['snap'], true); if (is_array($d)) $snap = $d; }
    $item_name   = (!empty($snap['item_name']) ? $snap['item_name'] : get_item($item_id));
    $receiptDesc = (!empty($snap['receipt_desc']) ? $snap['receipt_desc'] : '');
    if ($receiptDesc === '' && !empty($data['variant_id'])) {
        $v = (int)$data['variant_id'];
        $rdR = $connection->query("SELECT receipt_desc FROM kp_inventory_stock_variants WHERE variant_id='$v' LIMIT 1");
        if ($rdR && $rdRow = $rdR->fetch_assoc()) $receiptDesc = (string)$rdRow['receipt_desc'];
    }
    $nameHtml = htmlspecialchars($item_name);
    if ($receiptDesc !== '') $nameHtml .= '<div class="text-muted" style="font-size:.7rem;line-height:1.1;">'.htmlspecialchars($receiptDesc).'</div>';

    $items_html .= '
    <tr>
      <td>'.$no.'</td>
      <td>'.$nameHtml.'</td>
      <td>
        <div class="input-group input-group-sm" style="width:100px;">
          <input type="number" step="1" min="1" value="'.htmlspecialchars($quantity).'"
                 id="qty_'.$row_id.'" class="form-control form-control-sm"
                 onfocus="showbtn(this);" style="font-size:.78rem;padding:2px 5px;">
          <div class="qtydiv_'.$row_id.' btn-div" style="display:none;">
            <button class="btn btn-sm btn-outline-info" onclick="updatepo('.$row_id.');" style="padding:1px 5px;">
              <i class="fa fa-check"></i>
            </button>
          </div>
        </div>
      </td>
      <td>
        <div class="input-group input-group-sm" style="width:110px;">
          <input type="number" step="0.01" min="0" value="'.htmlspecialchars($price).'"
                 id="price_'.$row_id.'" class="form-control form-control-sm"
                 onfocus="showbtn(this);" style="font-size:.78rem;padding:2px 5px;">
          <div class="pricediv_'.$row_id.' btn-div" style="display:none;">
            <button class="btn btn-sm btn-outline-info" onclick="updatep('.$row_id.');" style="padding:1px 5px;">
              <i class="fa fa-check"></i>
            </button>
          </div>
        </div>
      </td>
      <td class="text-end" style="font-size:.78rem;">'.number_format($disc, 2).'</td>
      <td class="text-end fw-bold" style="font-size:.78rem;">'.number_format($total, 2).'</td>
      <td>
        <button class="btn btn-sm btn-outline-danger" onclick="del_this('.$row_id.');" style="padding:1px 5px;font-size:.7rem;">
          <i class="fa fa-trash"></i>
        </button>
      </td>
    </tr>';
}
$grand_total = $sum - (float)($sales['vat'] ?? 0);
?>

<div id="apppage-results" class="mb-2"></div>

<!-- ── Title bar with back button ── -->
<div class="d-flex justify-content-between align-items-center mb-2 px-1">
  <div class="d-flex align-items-center gap-2">
    <h1 class="mb-0" style="font-weight:800;font-size:1rem;color:#1e293b;">
      <i class="fa fa-check-circle me-1" style="color:#ea580c;"></i>REVIEW PO #<?php echo $po_id; ?>
    </h1>
  </div>
  <div class="d-flex gap-1">
    <button class="btn btn-sm btn-outline-secondary ajax-loader" style="font-size:.7rem;border-radius:6px;"
            data-folder="kug/app" data-file="index"
            data-target="#content" data-target-type="div"
            data-method="GET" data-id="0">
      <i class="fa fa-arrow-left me-1"></i>Approve Dashboard
    </button>
    <button class="btn btn-sm btn-outline-secondary ajax-loader" style="font-size:.7rem;border-radius:6px;"
            data-folder="kug" data-file="index"
            data-target="#content" data-target-type="div"
            data-method="GET" data-id="0">
      <i class="fa fa-home me-1"></i>Purchase Dashboard
    </button>
  </div>
</div>

<div class="row g-3">

  <!-- LEFT: PO Card -->
  <div class="col-md-8">
    <div class="card border-0 shadow-sm">
      <div class="card-header py-2" style="background:linear-gradient(135deg,#008080,#00a693);border:none;">
        <div class="d-flex align-items-center justify-content-between">
          <h6 class="mb-0 fw-bold text-white">
            <i class="fa fa-file-alt me-2"></i>
            PO #<?php echo $po_id; ?> &mdash; <?php echo htmlspecialchars($bname); ?>
          </h6>
          <button class="btn btn-sm btn-light" id="supChange" data-id="<?php echo $po_id; ?>">
            <i class="fa fa-exchange-alt me-1"></i>Change Supplier
          </button>
        </div>
      </div>
      <div class="card-body py-2">
        <div class="row g-2 mb-3">
          <div class="col-sm-4">
            <div class="small text-muted">PO Number</div>
            <div class="fw-bold"><?php echo $po_id; ?></div>
          </div>
          <div class="col-sm-4">
            <div class="small text-muted">Date</div>
            <div class="fw-bold"><?php echo htmlspecialchars($po_date); ?></div>
          </div>
          <div class="col-sm-4">
            <div class="small text-muted">Raised By</div>
            <div class="fw-bold"><?php echo htmlspecialchars($raised_by); ?></div>
          </div>
        </div>

        <div class="table-responsive">
          <table class="table table-sm table-bordered mb-0">
            <thead class="table-light">
              <tr>
                <th>#</th>
                <th>Item</th>
                <th>Qty</th>
                <th>Price</th>
                <th class="text-end">Disc</th>
                <th class="text-end">Total</th>
                <th></th>
              </tr>
            </thead>
            <tbody>
              <?php echo $items_html ?: '<tr><td colspan="7" class="text-center text-muted py-3">No items on this order.</td></tr>'; ?>
            </tbody>
            <tfoot>
              <tr>
                <td colspan="5" class="text-end fw-bold">TOTAL</td>
                <td class="text-end fw-bold">Ksh. <?php echo number_format($grand_total, 2); ?></td>
                <td></td>
              </tr>
            </tfoot>
          </table>
        </div>
      </div>
    </div>
  </div>

  <!-- RIGHT: Actions Card -->
  <div class="col-md-4">
    <div class="card border-0 shadow-sm">
      <div class="card-header py-2" style="background:#f8f9fa;border-left:4px solid #008080;">
        <h6 class="mb-0 fw-bold text-teal"><i class="fa fa-tasks me-2"></i>Approval Actions</h6>
      </div>
      <div class="card-body">
        <label class="form-label fw-semibold small">Comment / Reason</label>
        <textarea id="reason" name="reason" class="form-control mb-3" rows="4"
                  placeholder="Enter approval comment or reason..."></textarea>
        <div class="d-grid gap-2">
          <button type="button" class="btn btn-warning btn-sm"
                  onclick="holdpo(<?php echo $po_id; ?>);">
            <i class="fa fa-pause-circle me-1"></i>Hold Approval
          </button>
          <button type="button" class="btn btn-success btn-sm"
                  onclick="apppo(<?php echo $po_id; ?>);">
            <i class="fa fa-check-circle me-1"></i>Approve PO
          </button>
          <button type="button" class="btn btn-danger btn-sm"
                  onclick="rejpo(<?php echo $po_id; ?>);">
            <i class="fa fa-times-circle me-1"></i>Reject PO
          </button>
        </div>
      </div>
    </div>
  </div>

</div><!-- /.row -->

<script>
(function() {
    'use strict';
    var _appPoId = <?php echo $po_id; ?>;
    var _spinner = '<div class="text-center p-3"><div class="spinner-border text-primary"></div></div>';

    function _reloadPage() {
        $('#content').html(_spinner);
        $.ajax({
            url: 'kug/app/apppage.php',
            type: 'GET',
            data: { id: _appPoId },
            dataType: 'html'
        }).done(function(h) { $('#content').html(h); });
    }

    window.showbtn = function(el) {
        $('.btn-div').hide();
        if (el) {
            var id = $(el).attr('id');
            // qty_123 → qtydiv_123 ; price_123 → pricediv_123
            var div = id.replace('qty_','qtydiv_').replace('price_','pricediv_');
            $('.' + div).show();
        }
    };

    // ✅ Update by row id (kp_po_items.id)
    window.updatepo = function(rowId) {
        var qty = $('#qty_' + rowId).val();
        $.ajax({
            url: 'kug/app/upqty.php',
            type: 'POST',
            data: { id: rowId, qty: qty },
            dataType: 'html'
        }).done(function(d) {
            $('#apppage-results').html(d);
            _reloadPage();
        }).fail(function() {
            $('#apppage-results').html('<div class="alert alert-danger py-2 small">Update failed.</div>');
        });
    };

    // ✅ Update price by row id
    window.updatep = function(rowId) {
        var price = $('#price_' + rowId).val();
        $.ajax({
            url: 'kug/app/upprice.php',
            type: 'POST',
            data: { id: rowId, price: price },
            dataType: 'html'
        }).done(function(d) {
            $('#apppage-results').html(d);
            _reloadPage();
        }).fail(function() {
            $('#apppage-results').html('<div class="alert alert-danger py-2 small">Update failed.</div>');
        });
    };

    // ✅ Delete by row id
    window.del_this = function(rowId) {
        if (!confirm('Remove this item from the PO?')) return;
        $.ajax({
            url: 'kug/app/appitemdel.php',
            type: 'POST',
            data: { id: rowId },
            dataType: 'html'
        }).done(function() {
            _reloadPage();
        }).fail(function() {
            $('#apppage-results').html('<div class="alert alert-danger py-2 small">Delete failed.</div>');
        });
    };

    window.holdpo = function(po_id) {
        $('#modal').modal('show');
        $('#load').html(_spinner);
        $.ajax({
            url: 'kug/app/apphold.php',
            type: 'POST',
            data: { po: po_id },
            dataType: 'html'
        }).done(function(d) {
            $('#load').html(d);
        }).fail(function() {
            $('#load').html('<div class="alert alert-danger py-2">Request failed.</div>');
        });
    };

    window.apppo = function(po_id) {
        var comm = $('#reason').val();
        $('#modal').modal('show');
        $('#load').html(_spinner);
        $.ajax({
            url: 'kug/app/appmodal.php',
            type: 'POST',
            data: { id: po_id, comm: comm },
            dataType: 'html'
        }).done(function(d) {
            $('#load').html(d);
        }).fail(function() {
            $('#load').html('<div class="alert alert-danger py-2">Request failed.</div>');
        });
    };

    window.rejpo = function(po_id) {
        var comm = $('#reason').val();
        $('#modal').modal('show');
        $('#load').html(_spinner);
        $.ajax({
            url: 'kug/app/appcancel.php',
            type: 'POST',
            data: { id: po_id, comm: comm },
            dataType: 'html'
        }).done(function(d) {
            $('#load').html(d);
        }).fail(function() {
            $('#load').html('<div class="alert alert-danger py-2">Request failed.</div>');
        });
    };

    $(document).on('click', '#supChange', function() {
        var id = $(this).data('id');
        $('#modal').modal('show');
        $('#load').html(_spinner);
        $.ajax({
            url: 'kug/app/appsupchange.php',
            type: 'POST',
            data: { id: id },
            dataType: 'html'
        }).done(function(d) {
            $('#load').html(d);
        }).fail(function() {
            $('#load').html('<div class="alert alert-danger py-2">Request failed.</div>');
        });
    });
})();
</script>
