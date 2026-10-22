<!doctype html>
<html lang="en">
<head>
<meta charset="utf-8">
<meta name="viewport" content="width=device-width,initial-scale=1">
<title>K-touch — Purchase Order</title>
<link rel="stylesheet" href="../../../assets/plugins/bootstrap/4.0.0/css/bootstrap.css">
<link rel="stylesheet" href="../../../assets/plugins/sweetalert/dist/sweetalert2.min.css">
<link rel="stylesheet" href="ord.css">
</head>
<body>
<?php $po_id = (int)($_GET['po_id'] ?? $_GET['id'] ?? 0); ?>
<div id="app" class="app app-header-fixed app-without-sidebar">
    <div id="header" class="app-header">
        <div class="navbar-header">
            <a class="navbar-brand">
                <img src="../../../assets/img/logo/ktouch.png" style="width: 120px; height: auto;">
            </a>
            <button type="button" class="navbar-mobile-toggler" data-toggle="app-top-menu-mobile">
                <span class="icon-bar"></span>
                <span class="icon-bar"></span>
                <span class="icon-bar"></span>
            </button>
        </div>
        <div class="navbar-nav">
            <img src="../../../assets/img/logo/ktouch.png" style="width: 120px; height: auto; margin-right: 50%;" class="hlogo">
            <div class="navbar-item navbar-user dropdown">
                <img src="../../assets/img/user/user-12.jpg" alt="">
                <span class="d-md-inline" style="margin-right: 10px;">
                    <?php //echo get_column("kp_users", "username", "user_id='$user_id'"); ?>
                </span>
            </div>
        </div>
    </div>
<main class="page"> 
<div class="layout">
<section class="card left"> 
    <div class="entry">
        <div class="entry-search-with-add">
            <button id="add-item" class="add-product-button" type="button">＋ Add item</button>
            <div class="entry-search">
                <label class="label">Search products</label>
                <div class="control item-search">⌕<input id="item-search" autocomplete="off" placeholder="Search item, code or scan barcode..."><input type="hidden" id="item_id" name="item_id">
                </div>
            </div>
        </div>
        <div class="entry-qty">
            <label class="label">Quantity</label>
            <div class="control">
                <input id="item-qty" type="number" min="1" step="1" value="1">
            </div>
        </div>
        <div class="">
            <label class="label price-label">
                <span>Price</span>
                <button type="button" id="open-price-calculator" class="price-calculator-link btn btn-outline-primary btn-xs">Price calculator</button>
            </label>
            <div class="control"> 
                    <input id="item-price" class="price-input" type="number" min="0" step="0.01" value="0">
                 
            </div>
        </div>
        <button id="add-selected-item" class="add" type="button">＋ Add selected</button>
    </div>
    <div class="notice">
        <b>✓</b>Ready to add products to this purchase order
    </div>
    <div class="table-wrap">
        <table class="table" id="po-items-table" data-po-id="<?= $po_id ?>">
            <thead>
                <tr>
                    <th>Item</th>
                    <th>Qty</th>
                    <th>Unit price</th>
                    <th>Discount</th>
                    <th>Amount</th>
                    <th>Actions</th>
                </tr>
            </thead>
            <tbody id="po-items-body">
                <tr><td colspan="6" class="empty-items">Loading PO items...</td></tr>
            </tbody>
        </table>
        <div class="subtotal">
            <span>Order subtotal</span>
            <span id="order-subtotal">KES 0.00</span>
        </div>
    </div>
</section>
<aside class="right">
    <section class="card side-card">
        <div class="section-head">
            <div class="round">▤</div>
                <h2>Order Details</h2>
                <div class="detail order-type-detail">
                    <div class="badge">◆</div>
                    <div>
                        <span>Order type</span>
                        <strong>Local</strong>
                    </div>
                </div>
            </div> 
            <div class="details">
                <div class="detail">
                    <div class="badge">#</div>
                    <div>
                        <span>PO Number</span>
                        <strong><?= $po_id ?></strong>
                    </div>
                </div>
                <div class="detail">
                    <div class="badge">▦</div>
                    <div>
                        <span>Supplier</span>
                    <strong id="po-supplier" class="po-supplier-action" role="button" tabindex="0" title="Click to change supplier">Loading...</strong>
                    </div>
                </div>
                <div class="detail">
                    <div class="badge">▣</div>
                    <div>
                        <span>Order date</span>
                    <strong id="po-date" class="po-date-action" role="button" tabindex="0" title="Click to change order date">Loading...</strong>
                    </div>
                </div>
            </div>
    </section>
    <div class="split">
        <section class="card side-card">
            <h3>Order adjustments</h3>
            <div class="adjustments-grid">
                <div class="adjust">
                    <label>Summary discount</label>
                    <input id="summary-discount" class="full-width-input" type="number" min="0" step="0.01" value="0.00">
                </div>
                <div class="adjust supplier-ref-adjust">
                    <label>Supplier REF:</label>
                    <input id="supplier-ref" class="full-width-input" type="text" value="" placeholder="Supplier reference">
                </div>
            </div>
        </section>
        <section class="card side-card">
            <h3>Tax settings</h3>
            <div class="tax-grid">
                <div>
                    <label class="label">Tax type</label>
                    <div class="segments">
                        <button type="button" data-tax-type="none">None</button>
                        <button type="button" class="active" data-tax-type="inclusive">Inclusive</button>
                        <button type="button" data-tax-type="exclusive">Exclusive</button>
                    </div>
                </div>
                <div>
                    <label class="label">Rate</label>
                    <div class="segments">
                        <button type="button" class="active" data-tax-rate="16">16%</button>
                        <button type="button" data-tax-rate="custom">Custom</button>
                    </div>
                    <div class="rate">
                        <input id="tax-rate-input" type="number" min="0" step="0.01" value="16">
                        <span>%</span>
                    </div>
                </div>
            </div>
        </section>
    </div>
    <section class="card totals">
    <div class="total">
    <span>Subtotal</span>
    <strong id="total-subtotal">KES 0.00</strong>
    </div>
    <div class="total">
    <span>Discount</span>
    <span id="total-discount">KES 0.00</span>
    </div> 
    <div class="rule">
    </div>
    <div class="total">
    <span>Taxable</span>
    <span id="total-taxable">KES 0.00</span>
    </div>
    <div class="total">
    <span id="vat-label">VAT (16%)</span>
    <span id="total-vat">KES 0.00</span>
    </div>
    <div class="rule">
    </div>
    <div class="total grand">
    <span>Grand total</span>
    <span id="total-grand">KES 0.00</span>
    </div>
    </section>
    <section class="card buttons">
<button id="hold-order" class="btn hold" type="button">⏸ Hold</button>
    <button class="btn draft">Save draft</button>
    <button id="cancel-order" class="btn cancel" type="button">✕ Cancel</button>
<button id="submit-order" class="btn complete" type="button">✓ Submit order</button>
    <button class="btn print">▣ Print PO</button>
    </section>
</aside>
</div>
</main>

<div id="price-calculator-modal" class="price-calculator-modal" aria-hidden="true">
    <div class="price-calculator-dialog" role="dialog" aria-modal="true" aria-labelledby="price-calculator-title">
        <div class="price-calculator-header">
            <h3 id="price-calculator-title">Price per piece calculator</h3>
            <button id="close-price-calculator" type="button" aria-label="Close">&times;</button>
        </div>
        <div class="price-calculator-body">
            <label>Packaging unit
                <select id="package-unit">
                    <option>Crate</option>
                    <option>Case</option>
                    <option>Carton</option>
                </select>
            </label>
            <label><span id="package-pieces-label">Pieces per crate</span>
                <input id="package-pieces" type="number" min="1" step="1" placeholder="e.g. 24">
            </label>
            <label><span id="package-count-label">Number of crates ordered</span>
                <input id="package-count" type="number" min="1" step="1" placeholder="e.g. 3">
            </label>
            <label><span id="package-price-label">Price per crate</span>
                <input id="package-price" type="number" min="0" step="0.01" placeholder="e.g. 2400">
            </label>
            <label class="package-total-field"><span>Total cost</span>
                <input id="package-total" type="number" min="0" step="0.01" placeholder="Optional total cost">
            </label>
            <div class="price-calculator-result">
                Total pieces: <strong id="total-package-pieces">0</strong><br>
                Price per piece: <strong id="price-per-piece">KES 0.00</strong>
            </div>
        </div>
        <div class="price-calculator-footer">
            <button id="cancel-price-calculator" type="button">Cancel</button>
            <button id="use-price-per-piece" type="button">Use price</button>
        </div>
    </div>
</div>

<div id="discount-modal" class="price-calculator-modal" aria-hidden="true">
    <div class="price-calculator-dialog" role="dialog" aria-modal="true" aria-labelledby="discount-title">
        <div class="price-calculator-header">
            <h3 id="discount-title">Apply item discount</h3>
            <button id="close-discount-modal" type="button" aria-label="Close">&times;</button>
        </div>
        <div class="price-calculator-body">
            <label>Discount method
                <select id="discount-method">
                    <option value="percent">Percentage (%)</option>
                    <option value="amount">Amount</option>
                </select>
            </label>
            <label>Apply to
                <select id="discount-scope">
                    <option value="total">Whole total</option>
                    <option value="price">Unit price</option>
                </select>
            </label>
            <label>Value
                <input id="discount-value" type="number" min="0" step="0.01" placeholder="Enter value">
            </label>
            <div id="discount-preview" class="price-calculator-result">Discount per unit: 0.00</div>
        </div>
        <div class="price-calculator-footer">
            <button id="cancel-discount-modal" type="button">Cancel</button>
            <button id="apply-discount" type="button">Apply discount</button>
        </div>
    </div>
</div>
</div>
  <!-- end #sidebar -->
<div  id="modal" class="modal fade bs-example-modal-lg" tabindex="-1" role="dialog" aria-labelledby="myModalLabel"  style="display: none; width: auto;">
  <div class="modal-dialog" id="load" style="padding: 10px;"> </div>
</div>
<div  id="modal2" class="modal fade bs-example-modal-lg" tabindex="-1" role="dialog" aria-labelledby="myModalLabel"  style="display: none; width: auto;">
  <div class="modal-dialog" id="load2" style="padding: 10px;"> </div>
</div> 	
<?php
    // ordpage2.php is three levels below the project root.
    $asset_prefix = '../../../';
    include('../../clude/foot.php');
?>
<script src="../../../assets/plugins/sweetalert/dist/sweetalert2.all.min.js"></script>
<script src="ord.js"></script>
</body>
</html>

