<?php
session_start();
include('../../inc/connection.php');
include('../../inc/gen_funcs.php');

$user_id = (int)($_SESSION['user_id'] ?? 0);
if ($user_id < 1) {
  header('Location:../log_out.php');
  exit;
}

$po_id = (int)($_GET['po_id'] ?? 0);
if ($po_id < 1) {
  header('Location:pending.php');
  exit;
}

$poQuery = mysqli_query($connection, "SELECT * FROM kp_po WHERE po_id='$po_id' ORDER BY id DESC LIMIT 1") or die(mysqli_error($connection));
if (!$poQuery || mysqli_num_rows($poQuery) < 1) {
  header('Location:pending.php');
  exit;
}

$po = mysqli_fetch_assoc($poQuery);
$items = mysqli_query($connection, "SELECT * FROM kp_po_items WHERE po_id='$po_id' ORDER BY id ASC") or die(mysqli_error($connection));
$suppliers = mysqli_query($connection, "SELECT sup_id FROM kp_suppliers ORDER BY sup_id ASC") or die(mysqli_error($connection));
$currentSupplier = (int)$po['sup_id'];
$currentDate = !empty($po['po_date']) ? date('Y-m-d', strtotime((string)$po['po_date'])) : date('Y-m-d');
$currentDiscount = (float)$po['disc'];
$currentTaxBy = (int)$po['tax_by'];
$currentTaxPerc = (float)$po['tax_perc'];
$currentRefNo = trim((string)$po['refno']);
?>
<!DOCTYPE html>
<html lang="en">
<head>
  <meta charset="utf-8">
  <meta name="viewport" content="width=device-width, initial-scale=1">
  <title>Approve Purchase #<?php echo $po_id; ?></title>
  <link rel="stylesheet" href="../../assets/plugins/bootstrap/3.3.7/css/bootstrap.min.css">
  <style>
    body { margin:0; min-height:100vh; background:linear-gradient(180deg,#f8f1df 0%,#efdcb8 100%); color:#2c2217; }
    .approve-shell { max-width:1240px; margin:0 auto; padding:16px 10px 30px; }
    .approve-head { padding:18px; border-radius:24px; background:linear-gradient(135deg,#7e2014 0%,#b94828 52%,#e7a33a 100%); color:#fff8ea; box-shadow:0 18px 34px rgba(74,37,11,.18); }
    .approve-head h1 { margin:0; font-size:28px; font-weight:900; text-transform:uppercase; }
    .approve-head p { margin:6px 0 0; font-size:13px; font-weight:700; }
    .approve-card { margin-top:14px; background:rgba(255,255,255,.95); border-radius:22px; box-shadow:0 16px 28px rgba(0,0,0,.08); padding:16px; }
    .approve-grid { display:grid; grid-template-columns:repeat(auto-fit,minmax(180px,1fr)); gap:12px; margin-bottom:14px; }
    .approve-grid label { display:block; font-size:11px; font-weight:900; text-transform:uppercase; color:#7a5a2c; letter-spacing:.08em; margin-bottom:6px; }
    .approve-grid input,.approve-grid select { width:100%; height:44px; border:1px solid #d8c3a2; border-radius:12px; padding:0 12px; font-size:14px; font-weight:700; background:#fffdf8; }
    .approve-table-wrap { overflow-x:auto; border-radius:18px; border:1px solid rgba(126,58,32,.10); }
    .approve-table { width:100%; min-width:860px; border-collapse:collapse; }
    .approve-table th,.approve-table td { padding:10px 8px; border-bottom:1px solid rgba(126,58,32,.08); vertical-align:middle; }
    .approve-table th { background:#faf3e2; font-size:12px; font-weight:900; text-transform:uppercase; color:#7a5628; }
    .approve-table td input { width:100%; height:42px; border:1px solid #d8c3a2; border-radius:10px; padding:0 10px; font-size:14px; font-weight:700; background:#fff; }
    .approve-item-name { font-size:14px; font-weight:900; color:#322417; }
    .approve-summary { margin-top:14px; display:grid; grid-template-columns:repeat(auto-fit,minmax(180px,1fr)); gap:12px; }
    .approve-stat { padding:14px; border-radius:16px; background:#faf5ea; }
    .approve-stat strong,.approve-stat span { display:block; }
    .approve-stat strong { font-size:11px; font-weight:900; text-transform:uppercase; color:#7a5a2c; letter-spacing:.08em; }
    .approve-stat span { margin-top:6px; font-size:22px; font-weight:900; color:#2c2217; }
    .approve-actions { margin-top:16px; display:flex; gap:10px; flex-wrap:wrap; justify-content:flex-end; }
    .approve-actions a,.approve-actions button { min-height:46px; padding:0 18px; border-radius:14px; border:0; text-decoration:none !important; font-size:13px; font-weight:900; text-transform:uppercase; display:inline-flex; align-items:center; justify-content:center; }
    .approve-cancel { background:#ead6d6; color:#7e2014 !important; }
    .approve-save { background:linear-gradient(135deg,#355f16 0%,#58a03f 100%); color:#fff; }
  </style>
</head>
<body>
  <div class="approve-shell">
    <div class="approve-head">
      <h1>Approve Purchase #<?php echo $po_id; ?></h1>
      <p>Adjust the invoice details, quantities, prices and tax before posting stock.</p>
    </div>

    <form method="post" action="approve_save.php" class="approve-card" id="purchase-approve-form">
      <input type="hidden" name="po_id" value="<?php echo $po_id; ?>">
      <div class="approve-grid">
        <div>
          <label for="sup_id">Supplier</label>
          <select name="sup_id" id="sup_id" required>
            <option value="">Select supplier</option>
            <?php while ($supplier = mysqli_fetch_assoc($suppliers)) { ?>
              <option value="<?php echo (int)$supplier['sup_id']; ?>"<?php echo $currentSupplier === (int)$supplier['sup_id'] ? ' selected' : ''; ?>><?php echo htmlspecialchars(get_sup((int)$supplier['sup_id'])); ?></option>
            <?php } ?>
          </select>
        </div>
        <div>
          <label for="invoice_date">Invoice Date</label>
          <input type="date" name="invoice_date" id="invoice_date" value="<?php echo htmlspecialchars($currentDate); ?>" required>
        </div>
        <div>
          <label for="refno">Invoice / Ref No</label>
          <input type="text" name="refno" id="refno" value="<?php echo htmlspecialchars($currentRefNo); ?>" placeholder="Invoice number or reference">
        </div>
        <div>
          <label for="tax_by">Add Tax</label>
          <select name="tax_by" id="tax_by">
            <option value="0"<?php echo $currentTaxBy === 0 ? ' selected' : ''; ?>>No Tax</option>
            <option value="1"<?php echo $currentTaxBy === 1 ? ' selected' : ''; ?>>Add Tax</option>
          </select>
        </div>
        <div>
          <label for="tax_perc">Tax %</label>
          <input type="number" step="0.01" min="0" name="tax_perc" id="tax_perc" value="<?php echo htmlspecialchars((string)$currentTaxPerc); ?>">
        </div>
        <div>
          <label for="discount">Discount</label>
          <input type="number" step="0.01" min="0" name="discount" id="discount" value="<?php echo htmlspecialchars((string)$currentDiscount); ?>">
        </div>
      </div>
      <div class="approve-table-wrap">
        <table class="approve-table">
          <thead><tr><th>No.</th><th>Item</th><th>Qty</th><th>Unit Price</th><th>Total</th></tr></thead>
          <tbody>
            <?php $no = 0; while ($item = mysqli_fetch_assoc($items)) { $no++; ?>
              <tr>
                <td><?php echo $no; ?></td>
                <td><div class="approve-item-name"><?php echo htmlspecialchars(get_item($item['item_id'])); ?></div><input type="hidden" name="item_id[]" value="<?php echo (int)$item['item_id']; ?>"></td>
                <td><input type="number" step="0.01" min="0.01" name="qty[]" class="approve-qty" value="<?php echo htmlspecialchars((string)(float)$item['qty']); ?>" required></td>
                <td><input type="number" step="0.01" min="0.01" name="price[]" class="approve-price" value="<?php echo htmlspecialchars((string)(float)$item['price']); ?>" required></td>
                <td><input type="number" step="0.01" min="0" name="line_total[]" class="approve-total" value="<?php echo htmlspecialchars((string)(float)$item['total']); ?>" readonly></td>
              </tr>
            <?php } ?>
          </tbody>
        </table>
      </div>
      <div class="approve-summary">
        <div class="approve-stat"><strong>Sub Total</strong><span id="summary-subtotal">0.00</span></div>
        <div class="approve-stat"><strong>Tax</strong><span id="summary-tax">0.00</span></div>
        <div class="approve-stat"><strong>Discount</strong><span id="summary-discount">0.00</span></div>
        <div class="approve-stat"><strong>Grand Total</strong><span id="summary-grand">0.00</span></div>
      </div>
      <div class="approve-actions">
        <a href="pending.php" class="approve-cancel">Back</a>
        <button type="submit" class="approve-save">Approve And Stock</button>
      </div>
    </form>
  </div>
  <script>
    (function() {
      var qtyInputs = document.querySelectorAll('.approve-qty');
      var priceInputs = document.querySelectorAll('.approve-price');
      var totalInputs = document.querySelectorAll('.approve-total');
      var taxBy = document.getElementById('tax_by');
      var taxPerc = document.getElementById('tax_perc');
      var discount = document.getElementById('discount');
      var subtotalEl = document.getElementById('summary-subtotal');
      var taxEl = document.getElementById('summary-tax');
      var discountEl = document.getElementById('summary-discount');
      var grandEl = document.getElementById('summary-grand');
      function money(value) { return Number(value || 0).toFixed(2); }
      function recalc() {
        var subtotal = 0;
        for (var i = 0; i < qtyInputs.length; i++) {
          var qty = parseFloat(qtyInputs[i].value || 0);
          var price = parseFloat(priceInputs[i].value || 0);
          var total = qty * price;
          totalInputs[i].value = money(total);
          subtotal += total;
        }
        var discountValue = parseFloat(discount.value || 0);
        var taxValue = 0;
        if (parseInt(taxBy.value || '0', 10) === 1) {
          taxValue = subtotal * (parseFloat(taxPerc.value || 0) / 100);
        }
        var grand = subtotal + taxValue - discountValue;
        subtotalEl.textContent = money(subtotal);
        taxEl.textContent = money(taxValue);
        discountEl.textContent = money(discountValue);
        grandEl.textContent = money(grand);
      }
      for (var i = 0; i < qtyInputs.length; i++) {
        qtyInputs[i].addEventListener('input', recalc);
        priceInputs[i].addEventListener('input', recalc);
      }
      taxBy.addEventListener('change', recalc);
      taxPerc.addEventListener('input', recalc);
      discount.addEventListener('input', recalc);
      recalc();
    })();
  </script>
</body>
</html>
