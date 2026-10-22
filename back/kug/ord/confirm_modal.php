<?php
require_once __DIR__ . '/../../../inc/connection.php';
require_once __DIR__ . '/../../../inc/gen_funcs.php';
require_once __DIR__ . '/../kug_schema.php';

try {
    kug_ensure_schema($connection);
} catch (Throwable $schemaError) {
    echo '<div class="alert alert-danger m-3">Could not prepare purchase-order tables: ' . htmlspecialchars($schemaError->getMessage()) . '</div>';
    return;
}

$user_id  = (int)($_SESSION['mwene'] ?? $_SESSION['user_id'] ?? 0);
$url_po_id = (int)($_GET['po_id'] ?? $_GET['id'] ?? $_POST['po_id'] ?? $_POST['id'] ?? 0);
$po_id = $url_po_id > 0
    ? $url_po_id
    : (int)get_column('kp_thesion', 'trans_id', "user_id='{$user_id}'");

$posted_disc = isset($_POST['disc']) ? (float)$_POST['disc'] : null;
$posted_vatable = isset($_POST['vatable']) ? (float)$_POST['vatable'] : null;
$posted_vatt = isset($_POST['vatt']) ? (float)$_POST['vatt'] : null;
$posted_total = isset($_POST['tt']) ? (float)$_POST['tt'] : null;
$posted_perc = isset($_POST['perc']) ? (float)$_POST['perc'] : null;
$posted_supplier = isset($_POST['sup']) ? trim((string)$_POST['sup']) : '';
$posted_supplier_ref = isset($_POST['supplier_ref']) ? trim((string)$_POST['supplier_ref']) : '';

$action = 'LOADED CONFIRM MODAL';
logs('../../', 'back/kugura/', 'PURCHASE', $po_id, '-', '-', $action);
logs('../',   'back/kugura/', 'PURCHASE', $po_id, '-', '-', $action);

function po_first_value(array $row, array $keys, $fallback = '—') {
    foreach ($keys as $key) {
        if (isset($row[$key]) && $row[$key] !== '' && $row[$key] !== null) {
            return $row[$key];
        }
    }
    return $fallback;
}

function po_money($amount) {
    return 'KES ' . number_format((float)$amount, 2);
}

if ($user_id === 0) {
    ?>
    <script>
      new hullabaloo().send('SESSION Issue Logout and login again', 'danger');
    </script>
    <?php
    return;
}

if ($po_id === 0) {
    ?>
    <script>
      new hullabaloo().send('Try to hold and retry the order', 'danger');
    </script>
    <?php
    return;
}

$po_result = mysqli_query(
    $connection,
    "SELECT * FROM kp_po_status WHERE po_id='{$po_id}' LIMIT 1"
);
$po = $po_result ? (mysqli_fetch_assoc($po_result) ?: []) : [];
$sup = (int)($po['sup_id'] ?? 0);

if ($sup === 0) {
    ?>
    <script>
      new hullabaloo().send('You Must Select supplier', 'danger');
    </script>
    <?php
    return;
}

$supplier_result = mysqli_query(
    $connection,
    "SELECT * FROM kp_suppliers WHERE sup_id='{$sup}' LIMIT 1"
);
$supplier = $supplier_result ? (mysqli_fetch_assoc($supplier_result) ?: []) : [];

$sup_name = (string)po_first_value($supplier, ['sup_name', 'supplier_name', 'name'], 'Unknown supplier');
$po_date = (string)po_first_value($po, ['po_day', 'day', 'po_date', 'date'], '—');
$supplier_ref = (string)po_first_value($po, ['supplier_ref', 'sup_ref', 'reference', 'ref_no'], '—');
$supplier_ref = $posted_supplier_ref !== '' ? $posted_supplier_ref : $supplier_ref;
$supplier_balance = (float)po_first_value($supplier, ['balance', 'sup_balance', 'current_balance'], 0);

$raw_paid = strtolower((string)po_first_value($po, ['payment_status', 'paid_status', 'paid'], 'unpaid'));
$is_paid = in_array($raw_paid, ['1', 'paid', 'yes', 'true', 'complete', 'completed'], true);
$payment_status = $is_paid ? 'PAID' : 'UNPAID';

$cashier = (string)($_SESSION['username'] ?? $_SESSION['user_name'] ?? $_SESSION['name'] ?? ('User #' . $user_id));

$items = [];
$total_qty = 0;
$subtotal = 0.0;
$tax_total = 0.0;
$grand_total = 0.0;

$items_result = mysqli_query(
    $connection,
    "SELECT * FROM kp_po_items WHERE po_id='{$po_id}' ORDER BY item_id ASC"
) or die(mysqli_error($connection));

while ($row = mysqli_fetch_assoc($items_result)) {
    $qty = (float)($row['qty'] ?? 0);
    $unit_cost = (float)($row['price'] ?? 0);
    $tax = (float)po_first_value($row, ['tax', 'vat', 'tax_amount', 'vat_amount'], 0);
    $line_total = (float)($row['total'] ?? (($qty * $unit_cost) + $tax));

    $row['_qty'] = $qty;
    $row['_unit_cost'] = $unit_cost;
    $row['_tax'] = $tax;
    $row['_line_total'] = $line_total;
    $items[] = $row;

    $total_qty += $qty;
    $subtotal += ($qty * $unit_cost);
    $tax_total += $tax;
    $grand_total += $line_total;
}

// Use the totals calculated on the order page when this modal is loaded by AJAX.
$display_subtotal = $posted_vatable !== null ? $posted_vatable : $subtotal;
$display_tax = $posted_vatt !== null ? $posted_vatt : $tax_total;
$display_total = $posted_total !== null ? $posted_total : $grand_total;
?>

<style>
  #confirm-lpo-card {
    --po-teal: #008b87;
    --po-teal-dark: #007773;
    --po-navy: #071b3b;
    --po-mint: #f2fbfa;
    --po-line: #d8e2e8;
    border: 0;
    border-radius: 14px;
    box-shadow: 0 12px 38px rgba(7, 27, 59, .22);
    overflow: hidden;
    color: var(--po-navy);
    background: #fff;
  }

  #confirm-lpo-card .po-modal-header {
    min-height: 52px;
    padding: 10px;
    display: flex;
    align-items: center;
    justify-content: space-between;
    background: linear-gradient(135deg, #008b87, #139c91);
  }

  #confirm-lpo-card .po-modal-title {
    margin: 0;
    font-size: 1.25rem;
    font-weight: 800;
    letter-spacing: .01em;
    color: var(--po-navy);
  }

  #confirm-lpo-card .po-close {
    border: 0;
    background: transparent;
    color: #fff;
    font-size: 1.3rem;
    line-height: 1;
    opacity: .95;
  }

  #confirm-lpo-card .po-modal-body { padding: 16px 22px 14px; }

  #confirm-lpo-card .po-section-label {
    display: flex;
    align-items: center;
    gap: 8px;
    margin-bottom: 11px;
    color: var(--po-teal-dark);
    font-size: .82rem;
    font-weight: 800;
    letter-spacing: .025em;
  }

  #confirm-lpo-card .po-details {
    padding: 13px 16px 14px;
    margin-bottom: 13px;
    border: 1px solid #4bb9b2;
    border-radius: 9px;
    background: linear-gradient(105deg, #f4fffd 0%, #fff 72%);
  }

  #confirm-lpo-card .po-details-grid {
    display: grid;
    grid-template-columns: 1.35fr .9fr 1.15fr 1fr .8fr;
  }

  #confirm-lpo-card .po-detail {
    min-width: 0;
    padding: 2px 15px;
    border-left: 1px solid #cbe4e2;
  }

  #confirm-lpo-card .po-detail:first-child { border-left: 0; padding-left: 2px; }
  #confirm-lpo-card .po-detail-label {
    display: block;
    margin-bottom: 5px;
    color: #66727f;
    font-size: .64rem;
    font-weight: 700;
    text-transform: uppercase;
    white-space: nowrap;
  }

  #confirm-lpo-card .po-detail-value {
    display: block;
    overflow: hidden;
    color: var(--po-navy);
    font-size: .82rem;
    font-weight: 700;
    text-overflow: ellipsis;
    white-space: nowrap;
  }

  #confirm-lpo-card .po-supplier-value { color: var(--po-teal-dark); font-size: 1rem; }
  #confirm-lpo-card .po-status {
    display: inline-block;
    padding: 4px 12px;
    border-radius: 7px;
    font-size: .72rem;
    font-weight: 800;
  }
  #confirm-lpo-card .po-status-paid { color: #146c43; background: #d1e7dd; }
  #confirm-lpo-card .po-status-unpaid { color: #513600; background: #ffb51b; }

  #confirm-lpo-card .po-items-card {
    border: 1px solid var(--po-line);
    border-radius: 9px;
    overflow: hidden;
    background: #fff;
  }

  #confirm-lpo-card .po-items-heading {
    display: flex;
    align-items: center;
    gap: 10px;
    padding: 9px 14px;
    border-bottom: 1px solid var(--po-line);
    font-size: .83rem;
    font-weight: 800;
  }

  #confirm-lpo-card .po-item-count { color: #6c757d; font-weight: 500; }
  #confirm-lpo-card .po-items-scroll {
    max-height: 220px;
    overflow-y: auto;
    scrollbar-width: thin;
    scrollbar-color: #8a8a8a #edf1f3;
  }
  #confirm-lpo-card .po-items-scroll::-webkit-scrollbar { width: 9px; }
  #confirm-lpo-card .po-items-scroll::-webkit-scrollbar-track { background: #edf1f3; }
  #confirm-lpo-card .po-items-scroll::-webkit-scrollbar-thumb {
    border: 2px solid #edf1f3;
    border-radius: 8px;
    background: #888;
  }

  #confirm-lpo-card .po-items-table { margin: 0; font-size: .75rem; }
  #confirm-lpo-card .po-items-table thead th {
    position: sticky;
    top: 0;
    z-index: 2;
    padding: 9px 10px;
    border-bottom: 1px solid var(--po-line);
    color: var(--po-navy);
    background: #f8fafb;
    font-size: .67rem;
    font-weight: 800;
    text-transform: uppercase;
  }
  #confirm-lpo-card .po-items-table tbody td {
    padding: 10px;
    vertical-align: middle;
    border-color: #e5ebef;
  }

  #confirm-lpo-card .po-totals {
    display: grid;
    grid-template-columns: repeat(4, 1fr);
    margin-top: 11px;
    border: 1px solid var(--po-line);
    border-radius: 8px;
    overflow: hidden;
  }
  #confirm-lpo-card .po-total-cell {
    padding: 10px 12px;
    text-align: center;
    border-left: 1px solid var(--po-line);
  }
  #confirm-lpo-card .po-total-cell:first-child { border-left: 0; }
  #confirm-lpo-card .po-total-cell span {
    display: block;
    margin-bottom: 3px;
    font-size: .64rem;
    font-weight: 800;
    text-transform: uppercase;
  }
  #confirm-lpo-card .po-total-cell strong { font-size: .93rem; }
  #confirm-lpo-card .po-grand {
    border-left-color: var(--po-teal) !important;
    color: var(--po-teal-dark);
    background: #f0fffd;
  }

  #confirm-lpo-card .po-options {
    margin-top: 11px;
    border: 1px solid #a9ded9;
    border-radius: 8px;
    overflow: hidden;
    background: var(--po-mint);
  }
  #confirm-lpo-card .po-option {
    display: flex;
    align-items: center;
    gap: 14px;
    min-height: 58px;
    padding: 9px 14px;
    border-top: 1px solid #c8e7e4;
  }
  #confirm-lpo-card .po-option:first-child { border-top: 0; }
  #confirm-lpo-card .po-option-copy label {
    display: block;
    margin: 0 0 2px;
    color: var(--po-navy);
    font-size: .82rem;
    font-weight: 800;
    cursor: pointer;
  }
  #confirm-lpo-card .po-option-copy small { color: #65707c; font-size: .69rem; }

  #confirm-lpo-card .po-switch { position: relative; flex: 0 0 58px; height: 30px; margin: 0; }
  #confirm-lpo-card .po-switch input { position: absolute; opacity: 0; pointer-events: none; }
  #confirm-lpo-card .po-slider {
    position: absolute;
    inset: 0;
    border-radius: 20px;
    background: #c9cdd1;
    cursor: pointer;
    transition: .2s ease;
  }
  #confirm-lpo-card .po-slider::before {
    content: '';
    position: absolute;
    width: 24px;
    height: 24px;
    left: 3px;
    top: 3px;
    border-radius: 50%;
    background: #fff;
    box-shadow: 0 1px 4px rgba(0,0,0,.25);
    transition: .2s ease;
  }
  #confirm-lpo-card .po-switch input:checked + .po-slider { background: var(--po-teal); }
  #confirm-lpo-card .po-switch input:checked + .po-slider::before { transform: translateX(28px); }
  #confirm-lpo-card .po-switch input:focus + .po-slider { box-shadow: 0 0 0 3px rgba(0,139,135,.16); }

  #confirm-lpo-card .po-modal-footer {
    display: flex;
    align-items: center;
    justify-content: space-between;
    padding: 12px 22px;
    border-top: 1px solid #eef1f3;
  }
  #confirm-lpo-card .po-confirm-btn {
    min-width: 150px;
    border: 1px solid #086f6c;
    color: #fff;
    background: var(--po-teal);
    font-size: .78rem;
    font-weight: 800;
  }
  #confirm-lpo-card .po-confirm-btn:hover { color: #fff; background: var(--po-teal-dark); }

  @media (max-width: 900px) {
    #confirm-lpo-card .po-details-grid { grid-template-columns: repeat(2, 1fr); gap: 10px; }
    #confirm-lpo-card .po-detail { border-left: 0; padding: 4px 2px; }
    #confirm-lpo-card .po-totals { grid-template-columns: repeat(2, 1fr); }
    #confirm-lpo-card .po-total-cell:nth-child(3) { border-left: 0; border-top: 1px solid var(--po-line); }
    #confirm-lpo-card .po-total-cell:nth-child(4) { border-top: 1px solid var(--po-line); }
  }
</style>

<div style="width:750px;" class="modal-content" id="confirm-lpo-card"
     data-discount="<?php echo htmlspecialchars((string)($posted_disc ?? 0)); ?>"
     data-vatable="<?php echo htmlspecialchars((string)$display_subtotal); ?>"
     data-vat="<?php echo htmlspecialchars((string)$display_tax); ?>"
     data-total="<?php echo htmlspecialchars((string)$display_total); ?>"
     data-tax-rate="<?php echo htmlspecialchars((string)($posted_perc ?? 0)); ?>"
     data-supplier="<?php echo htmlspecialchars($posted_supplier); ?>">
  <div id="po-results"></div>

  <header class="po-modal-header">
    <h5 class="po-modal-title">CONFIRM LPO #<?php echo $po_id; ?></h5>
    <button type="button" class="po-close" data-bs-dismiss="modal" aria-label="Close">
      <i class="fa fa-minus"></i>
    </button>
  </header>

  <div class="po-modal-body">
    <section class="po-details" aria-labelledby="po-order-details-title">
      <div class="po-section-label" id="po-order-details-title">
        <i class="fa fa-list-ul"></i> ORDER DETAILS
      </div>
      <div class="po-details-grid">
        <div class="po-detail">
          <span class="po-detail-label"><i class="fa fa-building me-1"></i>Supplier</span>
          <strong class="po-detail-value po-supplier-value" id="confirm-supplier"><?php echo htmlspecialchars($sup_name); ?></strong>
        </div>
        <div class="po-detail">
          <span class="po-detail-label"><i class="fa fa-calendar-alt me-1"></i>PO Date</span>
          <strong class="po-detail-value" id="confirm-date"><?php echo htmlspecialchars($po_date); ?></strong>
        </div>
        <div class="po-detail">
          <span class="po-detail-label"><i class="fa fa-wallet me-1"></i>Supplier Balance</span>
          <strong class="po-detail-value" id="confirm-balance"><?php echo po_money($supplier_balance); ?></strong>
        </div>
        <div class="po-detail">
          <span class="po-detail-label"><i class="fa fa-tag me-1"></i>Supplier Ref</span>
          <strong class="po-detail-value" id="confirm-ref"><?php echo htmlspecialchars($supplier_ref); ?></strong>
        </div>
        <div class="po-detail">
          <span class="po-detail-label">Payment Status</span>
          <strong class="po-status <?php echo $is_paid ? 'po-status-paid' : 'po-status-unpaid'; ?>">
            <?php echo $payment_status; ?>
          </strong>
        </div>
      </div>
      <input type="hidden" id="confirm-cashier" value="<?php echo htmlspecialchars($cashier); ?>">
    </section>

    <section class="po-items-card" aria-labelledby="po-purchase-items-title">
      <div class="po-items-heading" id="po-purchase-items-title">
        <i class="fa fa-shopping-cart"></i>
        <span>PURCHASE ITEMS</span>
        <span class="po-item-count"><?php echo count($items); ?> items</span>
      </div>
      <div class="po-items-scroll">
        <table class="table table-sm po-items-table">
          <thead>
            <tr>
              <th style="width:42px;">#</th>
              <th>Item Name</th>
              <th style="width:75px;" class="text-center">Qty</th>
              <th style="width:105px;" class="text-end">Unit Cost</th>
              <th style="width:95px;" class="text-end">Tax</th>
              <th style="width:115px;" class="text-end">Total</th>
            </tr>
          </thead>
          <tbody id="confirm-items-body">
            <?php foreach ($items as $index => $row): ?>
              <tr>
                <td class="text-muted"><?php echo $index + 1; ?></td>
                <td><?php echo htmlspecialchars((string)get_item($row['item_id'])); ?></td>
                <td class="text-center fw-bold"><?php echo number_format($row['_qty'], 2, '.', '') + 0; ?></td>
                <td class="text-end"><?php echo number_format($row['_unit_cost'], 2); ?></td>
                <td class="text-end"><?php echo number_format($row['_tax'], 2); ?></td>
                <td class="text-end fw-bold"><?php echo number_format($row['_line_total'], 2); ?></td>
              </tr>
            <?php endforeach; ?>
          </tbody>
        </table>
      </div>
    </section>

    <section class="po-totals" aria-label="Purchase totals">
      <div class="po-total-cell">
        <span>Total Qty</span>
        <strong><?php echo number_format($total_qty, 2, '.', '') + 0; ?></strong>
      </div>
      <div class="po-total-cell">
        <span>Subtotal</span>
        <strong id="confirm-subtotal"><?php echo po_money($display_subtotal); ?></strong>
      </div>
      <div class="po-total-cell">
        <span>Tax Total</span>
        <strong id="confirm-vat"><?php echo po_money($display_tax); ?></strong>
      </div>
      <div class="po-total-cell po-grand">
        <span>Grand Total</span>
        <strong id="confirm-total"><?php echo po_money($display_total); ?></strong>
      </div>
    </section>

    <section class="po-options" aria-label="Confirmation options">
      <div class="po-option">
        <label class="po-switch" for="confirm-auto-receive">
          <input type="checkbox" id="confirm-auto-receive" checked>
          <span class="po-slider"></span>
        </label>
        <div class="po-option-copy">
          <label for="confirm-auto-receive">Auto-receive purchase</label>
          <small>Automatically receive stock and generate a Goods Received Note (GRN).</small>
        </div>
      </div>
      <div class="po-option">
        <label class="po-switch" for="confirm-update-buying-price">
          <input type="checkbox" id="confirm-update-buying-price">
          <span class="po-slider"></span>
        </label>
        <div class="po-option-copy">
          <label for="confirm-update-buying-price">Update buying prices</label>
          <small>Replace current buying prices with the LPO unit costs.</small>
        </div>
      </div>
    </section>
  </div>

  <footer class="po-modal-footer">
    <button type="button" class="btn btn-sm btn-outline-secondary" data-bs-dismiss="modal">
      Cancel
    </button>
    <button type="button" class="btn btn-sm po-confirm-btn" id="btn-confirm-lpo">
      CONFIRM LPO
    </button>
  </footer>
</div>

<script>
(function ($) {
  $(document)
    .off('click.confirmLpo')
    .on('click.confirmLpo', '#btn-confirm-lpo', function () {
      var $button = $(this);

      $button
        .prop('disabled', true)
        .html('<span class="spinner-border spinner-border-sm me-1"></span>Submitting…');

      $.ajax({
        url: 'kug/ord/place_po.php',
        type: 'POST',
        dataType: 'html',
        data: {
          po_id: <?php echo $po_id; ?>,
          auto_receive: $('#confirm-auto-receive').is(':checked') ? 1 : 0,
          update_buying_price: $('#confirm-update-buying-price').is(':checked') ? 1 : 0
        }
      }).done(function (response) {
        $('#po-results').html(response);
      }).fail(function () {
        $button.prop('disabled', false).html('CONFIRM LPO');
        $('#po-results').html(
          '<div class="alert alert-danger py-1 small m-2">Something went wrong. Please try again.</div>'
        );
      });
    });
})(jQuery);
</script>
