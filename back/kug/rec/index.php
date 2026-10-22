<?php
include('funcs.php');
if (session_status() === PHP_SESSION_NONE) session_start();
$user_id = $_SESSION['mwene'];
$folder  = "back/#prods/1.php";
thesion_page($folder);
?>

<style>
/* ── KPI cards ─────────────────────────────────────────────────────────── */
.rec-kpi {
  border-radius:10px; padding:.85rem 1rem;
  box-shadow:0 2px 10px rgba(0,0,0,.06);
}
.rec-kpi-label { font-size:.62rem; font-weight:700; text-transform:uppercase; letter-spacing:.04em; }
.rec-kpi-value { font-size:1.6rem; font-weight:800; line-height:1.1; }
.rec-kpi-sub   { font-size:.65rem; margin-top:.15rem; }
.rec-kpi-icon  {
  width:40px; height:40px; border-radius:8px;
  display:flex; align-items:center; justify-content:center;
  font-size:1rem; flex-shrink:0;
}

/* ── Breadcrumb steps ──────────────────────────────────────────────────── */
.rec-bc-step { color:#adb5bd; font-weight:600; }
.rec-bc-step.active { color:#008080; }
.rec-bc-num {
  display:inline-flex; width:18px; height:18px; border-radius:50%;
  background:#e5e7eb; color:#6c757d;
  align-items:center; justify-content:center; font-size:.6rem; font-weight:700;
  margin-right:.25rem;
}
.rec-bc-step.active .rec-bc-num { background:#008080; color:#fff; }

/* ── Sidebar nav ───────────────────────────────────────────────────────── */
.rec-nav-btn {
  display:flex; align-items:center; gap:.5rem;
  width:100%; padding:.42rem .65rem; margin-bottom:.2rem;
  border-radius:6px; border:none; background:transparent;
  font-size:.77rem; font-weight:600; cursor:pointer; text-align:left;
  transition:all .15s;
}
.rec-nav-btn:hover { background:#e0f7f4; color:#008080; }
.rec-nav-btn.active { background:#008080; color:#fff; }
.rec-nav-btn.active i { color:#fff!important; }
.rec-nav-btn i { width:16px; text-align:center; opacity:.7; }

/* ── Step panels ───────────────────────────────────────────────────────── */
.rec-step { display:none; }
.rec-step.active { display:block; }

/* ── Tables ────────────────────────────────────────────────────────────── */
.rec-tbl thead th {
  font-size:.64rem; font-weight:700; text-transform:uppercase;
  letter-spacing:.04em; padding:.38rem .5rem; white-space:nowrap; background:#f8f9fa;
}
.rec-tbl tbody td { font-size:.78rem; vertical-align:middle; padding:.35rem .5rem; }
.rec-tbl tbody tr:hover { background:#f5f7ff!important; }

/* ── Receive quantity input colours ────────────────────────────────────── */
.recv-qty-input {
  width:70px; text-align:center; font-weight:700; font-size:.85rem;
  border:1.5px solid #d1d5db; border-radius:6px; padding:.2rem .3rem;
  transition:border-color .2s, box-shadow .2s;
}
.recv-qty-input:focus { outline:none; border-color:#008080; box-shadow:0 0 0 2px rgba(0,128,128,.15); }
.qty-exact { border-color:#16a34a!important; background:#f0fdf4; color:#16a34a; }
.qty-short { border-color:#f59e0b!important; background:#fffbeb; color:#92400e; }
.qty-over  { border-color:#ef4444!important; background:#fef2f2; color:#dc2626; }

/* ── GRN status badges ─────────────────────────────────────────────────── */
.grn-status-1 { background:#d1fae5; color:#065f46; }
.grn-status-3 { background:#fef3c7; color:#92400e; }

/* ── Success card ──────────────────────────────────────────────────────── */
.grn-success-card {
  background:linear-gradient(135deg,#ecfdf5,#fff); border:2px solid #bbf7d0;
  border-radius:12px; padding:1.5rem; text-align:center;
}
</style>

<!-- ═══ KPI ROW ═══ -->
<div class="row g-2 mb-3">
  <div class="col-md-3 col-6">
    <div class="rec-kpi" style="background:linear-gradient(135deg,#eff6ff,#fff);border-left:4px solid #2563eb;">
      <div class="d-flex justify-content-between align-items-start">
        <div>
          <div class="rec-kpi-label text-muted">Pending Receiving</div>
          <div class="rec-kpi-value text-primary" id="kpi-pending">–</div>
          <div class="rec-kpi-sub text-muted">Approved POs awaiting delivery</div>
        </div>
        <div class="rec-kpi-icon" style="background:#dbeafe;color:#2563eb;">
          <i class="fa fa-clock"></i>
        </div>
      </div>
    </div>
  </div>
  <div class="col-md-3 col-6">
    <div class="rec-kpi" style="background:linear-gradient(135deg,#fffbeb,#fff);border-left:4px solid #f59e0b;">
      <div class="d-flex justify-content-between align-items-start">
        <div>
          <div class="rec-kpi-label text-muted">Partially Received</div>
          <div class="rec-kpi-value" style="color:#f59e0b;" id="kpi-partial">–</div>
          <div class="rec-kpi-sub text-muted">POs with remaining items</div>
        </div>
        <div class="rec-kpi-icon" style="background:#fef3c7;color:#f59e0b;">
          <i class="fa fa-exclamation-triangle"></i>
        </div>
      </div>
    </div>
  </div>
  <div class="col-md-3 col-6">
    <div class="rec-kpi" style="background:linear-gradient(135deg,#ecfdf5,#fff);border-left:4px solid #16a34a;">
      <div class="d-flex justify-content-between align-items-start">
        <div>
          <div class="rec-kpi-label text-muted">GRNs Today</div>
          <div class="rec-kpi-value text-success" id="kpi-today">–</div>
          <div class="rec-kpi-sub text-muted">Delivered today</div>
        </div>
        <div class="rec-kpi-icon" style="background:#bbf7d0;color:#16a34a;">
          <i class="fa fa-check-circle"></i>
        </div>
      </div>
    </div>
  </div>
  <div class="col-md-3 col-12">
    <div class="rec-kpi" style="background:linear-gradient(135deg,#f0fdf9,#fff);border-left:4px solid #008080;">
      <div class="d-flex justify-content-between align-items-start">
        <div>
          <div class="rec-kpi-label text-muted">Total GRNs</div>
          <div class="rec-kpi-value" style="color:#008080;" id="kpi-total">–</div>
          <div class="rec-kpi-sub text-muted">All received deliveries</div>
        </div>
        <div class="rec-kpi-icon" style="background:#ccfbf1;color:#008080;">
          <i class="fa fa-clipboard-list"></i>
        </div>
      </div>
    </div>
  </div>
</div>

<div class="row mb-4">

  <!-- ═══ BREADCRUMB ═══ -->
  <div class="col-12 mb-2">
    <div class="d-flex align-items-center gap-1" style="font-size:.72rem;">
      <span class="rec-bc-step active" id="bc-pos">
        <span class="rec-bc-num">1</span> PO List
      </span>
      <i class="fa fa-chevron-right text-muted mx-1" style="font-size:.6rem;"></i>
      <span class="rec-bc-step" id="bc-receive">
        <span class="rec-bc-num">2</span> Enter Quantities
      </span>
      <i class="fa fa-chevron-right text-muted mx-1" style="font-size:.6rem;"></i>
      <span class="rec-bc-step" id="bc-success">
        <span class="rec-bc-num">3</span> GRN Created
      </span>
    </div>
  </div>

  <!-- ═══ LEFT SIDEBAR ═══ -->
  <div class="col-md-2 col-12">
    <div class="widget">
      <div class="widget-header p-2">
        <h4 class="my-0 text-cyan" style="font-size:.76rem;"><i class="fa fa-cogs me-1"></i> OPERATIONS</h4>
      </div>
      <div class="p-2">
        <button class="rec-nav-btn active" id="nav-pos" onclick="recShowPos()">
          <i class="fa fa-list-alt text-primary"></i> Approved POs
          <span class="badge bg-warning text-dark ms-auto d-none" id="nav-pos-badge" style="font-size:.6rem;"></span>
        </button>
        <button class="rec-nav-btn" id="nav-grns" onclick="recShowGrns()">
          <i class="fa fa-clipboard-list text-success"></i> GRN History
        </button>
        <hr class="my-1">
        <button class="rec-nav-btn ajax-loader" data-folder="kug" data-file="index"
            data-target="#content" data-target-type="div" data-method="GET" data-id="0">
          <i class="fa fa-shopping-cart text-secondary"></i> Purchase Dashboard
        </button>
      </div>
    </div>
  </div>

  <!-- ═══ MAIN CONTENT ═══ -->
  <div class="col-md-10 col-12" id="rec-main">

    <!-- ── STEP 1: Approved PO List ────────────────────────────────────── -->
    <div class="rec-step active" id="step-pos">
      <div class="widget">
        <div class="widget-header p-2 d-flex justify-content-between align-items-center"
             style="background:linear-gradient(90deg,#f0fdf9,#fff);border-bottom:2px solid #ccfbf1;">
          <h4 class="my-0 fw-bold" style="font-size:.82rem;color:#008080;">
            <i class="fa fa-list me-1"></i> APPROVED PURCHASE ORDERS — PENDING RECEIVING
          </h4>
          <button class="btn btn-xs btn-outline-success" onclick="recLoadPos()">
            <i class="fa fa-refresh me-1"></i>Refresh
          </button>
        </div>
        <div id="pos-content" style="overflow-x:auto;">
          <div id="pos-loader" class="text-center py-4 text-muted small">
            <i class="fa fa-spinner fa-spin me-1"></i>Loading purchase orders…
          </div>
          <div id="pos-empty" class="d-none text-center py-5 text-muted">
            <i class="fa fa-check-circle d-block fa-3x mb-3" style="color:#008080;opacity:.3;"></i>
            <div class="fw-bold">No approved POs pending receiving.</div>
            <div class="small mt-1">All purchase orders have been received.</div>
          </div>
          <div id="pos-wrap" class="d-none">
            <table class="table table-sm table-hover mb-0 rec-tbl">
              <thead><tr>
                <th>PO #</th><th>Supplier</th><th>Raised By</th>
                <th>Date</th><th>Amount</th><th>Items</th><th>Received</th>
                <th>Status</th><th>Type</th>
                <th class="text-center">Action</th>
              </tr></thead>
              <tbody id="pos-tbody"></tbody>
            </table>
          </div>
        </div>
      </div>
    </div>

    <!-- ── STEP 2: Receive a PO ────────────────────────────────────────── -->
    <div class="rec-step" id="step-receive">
      <div class="widget mb-2">
        <div class="widget-header p-2 d-flex justify-content-between align-items-center"
             style="background:linear-gradient(90deg,#f0fdf9,#fff);border-bottom:2px solid #ccfbf1;">
          <h4 class="my-0 fw-bold" style="font-size:.82rem;color:#008080;">
            <i class="fa fa-inbox me-1"></i> RECEIVE GOODS — PO <span id="recv-po-num" class="text-primary fw-bold"></span>
          </h4>
          <button class="btn btn-xs btn-outline-secondary" onclick="recShowPos()">
            <i class="fa fa-arrow-left me-1"></i>Back to PO List
          </button>
        </div>
        <div class="p-3">
          <!-- PO summary cards -->
          <div class="row g-2 mb-3" id="recv-po-info"></div>

          <!-- GRN form fields -->
          <div class="p-2 rounded-2 mb-3" style="background:#f8f9fa;border:1px solid #e5e7eb;">
            <div class="row g-2">
              <div class="col-md-3 col-6">
                <label class="form-label" style="font-size:.72rem;font-weight:700;">INVOICE #</label>
                <input type="text" class="form-control form-control-sm" id="recv-inv" placeholder="Supplier invoice number">
              </div>
              <div class="col-md-3 col-6">
                <label class="form-label" style="font-size:.72rem;font-weight:700;">TAX APPLIES?</label>
                <select class="form-control form-control-sm" id="recv-tax">
                  <option value="0">No Tax</option>
                  <option value="1">Tax Applies</option>
                </select>
              </div>
              <div class="col-md-3 col-6">
                <label class="form-label" style="font-size:.72rem;font-weight:700;">DELIVERY NOTE #</label>
                <input type="text" class="form-control form-control-sm" id="recv-dnote" placeholder="Delivery note / waybill">
              </div>
              <div class="col-md-3 col-6">
                <label class="form-label" style="font-size:.72rem;font-weight:700;">RECEIVED DATE</label>
                <input type="date" class="form-control form-control-sm" id="recv-day" value="<?php echo date('Y-m-d'); ?>">
              </div>
            </div>
          </div>

          <!-- Items loader / table -->
          <div style="overflow-x:auto;">
            <div id="recv-items-loader" class="text-center py-3 text-muted small">
              <i class="fa fa-spinner fa-spin me-1"></i>Loading items…
            </div>
            <div id="recv-items-wrap" class="d-none">
              <div class="mb-2">
                <button class="btn btn-outline-primary btn-sm" onclick="recAddExtraItem()">
                  <i class="fa fa-plus me-1"></i>Add New Item Not In PO
                </button>
              </div>
              <div style="max-height:420px;overflow-y:auto;">
                <table class="table table-sm table-hover rec-tbl mb-2">
                  <thead><tr>
                    <th>#</th><th>Item</th><th>Brand</th><th>Size</th><th>Color</th><th>UOM</th>
                    <th class="text-center">Ordered</th>
                    <th class="text-center text-muted">Prev Recv</th>
                    <th class="text-center">Remaining</th>
                    <th class="text-center" style="color:#008080;">Receive Now</th>
                    <th class="text-end">Unit Price</th>
                    <th class="text-end">Edit Price</th>
                    <th class="text-end">Total</th>
                    <th>Extras</th>
                  </tr></thead>
                  <tbody id="recv-items-tbody"></tbody>
                  <tbody id="recv-extra-tbody"></tbody>
                  <tfoot>
                    <tr style="background:#f8f9fa;">
                      <td colspan="12" class="text-end fw-bold" style="font-size:.75rem;">ESTIMATED TOTAL:</td>
                      <td colspan="2" class="text-end fw-bold text-success" id="recv-total" style="font-size:.85rem;">–</td>
                    </tr>
                  </tfoot>
                </table>
              </div>
              <div id="recv-msg" class="mb-2"></div>
              <div class="d-flex justify-content-end gap-2">
                <button class="btn btn-secondary btn-sm" onclick="recShowPos()">
                  <i class="fa fa-times me-1"></i>Cancel
                </button>
                <button class="btn btn-success btn-sm" id="btn-create-grn" onclick="recCreateGrn()">
                  <i class="fa fa-check-circle me-1"></i>Create GRN &amp; Send to Store
                </button>
              </div>
            </div>
          </div>
        </div>
      </div>
    </div>

    <!-- ── STEP 3: GRN Created Success ─────────────────────────────────── -->
    <div class="rec-step" id="step-success">
      <div class="grn-success-card mb-3">
        <div class="text-center mb-3">
          <i class="fa fa-check-circle fa-4x" style="color:#16a34a;opacity:.8;"></i>
          <h3 class="mt-2 fw-bold text-success">GRN Created Successfully!</h3>
          <p class="text-muted mb-0" style="font-size:.85rem;">The goods receiving note has been created and forwarded to the store team for stock confirmation.</p>
          <div id="success-po-status" class="mt-2"></div>
        </div>
        <div class="row g-2 mb-3" id="success-info"></div>
        <div class="alert alert-info py-2 mb-3" style="font-size:.78rem;">
          <i class="fa fa-info-circle me-1"></i>
          The store team can now view this GRN in the <strong>Store Receiving Module</strong> and confirm each item into stock.
        </div>
        <div class="d-flex gap-2 justify-content-center mb-3">
          <button class="btn btn-outline-success btn-sm" onclick="recShowGrns()">
            <i class="fa fa-clipboard-list me-1"></i>View GRN History
          </button>
          <button class="btn btn-primary btn-sm" onclick="recShowPos()">
            <i class="fa fa-list me-1"></i>Back to PO List
          </button>
        </div>
      </div>
      <div class="widget mt-2">
        <div class="widget-header p-2 d-flex justify-content-between align-items-center"
             style="background:linear-gradient(90deg,#f0fdf9,#fff);border-bottom:2px solid #ccfbf1;">
          <h4 class="my-0 fw-bold" style="font-size:.82rem;color:#008080;">
            <i class="fa fa-list me-1"></i> RECEIVED ITEMS — FORWARDED TO STORE
          </h4>
          <span class="badge bg-warning text-dark" style="font-size:.65rem;">
            <i class="fa fa-clock me-1"></i>Pending store confirmation
          </span>
        </div>
        <div id="success-items" style="overflow-x:auto;">
          <div class="text-center py-3 text-muted small">
            <i class="fa fa-spinner fa-spin me-1"></i>Loading items…
          </div>
        </div>
      </div>
    </div>

    <!-- ── GRN History Panel ───────────────────────────────────────────── -->
    <div class="rec-step" id="step-grns">
      <div class="widget">
        <div class="widget-header p-2 d-flex justify-content-between align-items-center"
             style="background:linear-gradient(90deg,#f0fdf9,#fff);border-bottom:2px solid #ccfbf1;">
          <h4 class="my-0 fw-bold" style="font-size:.82rem;color:#008080;">
            <i class="fa fa-clipboard-list me-1"></i> GRN HISTORY
          </h4>
          <button class="btn btn-xs btn-outline-success" onclick="recLoadGrns()">
            <i class="fa fa-refresh me-1"></i>Refresh
          </button>
        </div>
        <div id="grns-content" style="overflow-x:auto;">
          <div id="grns-loader" class="text-center py-4 text-muted small">
            <i class="fa fa-spinner fa-spin me-1"></i>Loading GRNs…
          </div>
          <div id="grns-empty" class="d-none text-center py-5 text-muted">
            <i class="fa fa-clipboard d-block fa-3x mb-3" style="opacity:.3;"></i>
            No GRNs found.
          </div>
          <div id="grns-wrap" class="d-none">
            <table class="table table-sm table-hover mb-0 rec-tbl">
              <thead><tr>
                <th>GRN #</th><th>PO #</th><th>Supplier</th>
                <th>Invoice</th><th>Date</th>
                <th class="text-center">Items</th><th class="text-center">Stored</th>
                <th>Amount</th><th>Status</th><th>Received By</th><th></th>
              </tr></thead>
              <tbody id="grns-tbody"></tbody>
            </table>
          </div>
        </div>
      </div>
    </div>

  </div><!-- /#rec-main -->
</div><!-- /row -->

<!-- ═══ MODAL: Add Extra Item Variant ═══ -->
<div class="modal fade" id="extraItemModal" tabindex="-1" aria-labelledby="extraItemModalLabel" aria-hidden="true">
  <div class="modal-dialog modal-dialog-centered">
    <div class="modal-content">
      <div class="modal-header py-2" style="background:linear-gradient(90deg,#f0fdf9,#fff);border-bottom:2px solid #ccfbf1;">
        <h6 class="modal-title fw-bold" style="color:#008080;" id="extraItemModalLabel">
          <i class="fa fa-plus-circle me-1"></i>Add Extra Item (Not In PO)
        </h6>
        <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
      </div>
      <div class="modal-body">

        <!-- PHASE 1: Search only -->
        <div id="modal-phase-search">
          <label class="form-label fw-bold" style="font-size:.78rem;">Search Stock Variant</label>
          <div class="input-group input-group-sm">
            <span class="input-group-text"><i class="fa fa-search"></i></span>
            <input type="text" class="form-control" id="extraItemSearch"
                   placeholder="Type item name, code, barcode or SKU…" autocomplete="off">
          </div>
          <div class="text-muted mt-1" style="font-size:.65rem;">Type at least 2 characters to search</div>

          <!-- Search results appear here -->
          <div id="extraItemSearchResults" class="mt-2" style="max-height:220px;overflow-y:auto;"></div>

          <!-- No results message + request link -->
          <div id="extraItemNotFound" class="d-none mt-2">
            <div class="alert alert-warning py-2 mb-2" style="font-size:.78rem;">
              <i class="fa fa-exclamation-circle me-1"></i>
              Variant not found in stock.
              <a href="#" id="extraShowRequestForm" class="fw-bold text-decoration-underline ms-1">Request New Variant</a>
            </div>
          </div>
        </div>

        <!-- PHASE 2: Selected variant → qty & price -->
        <div id="modal-phase-selected" class="d-none">
          <div class="alert alert-success py-2 mb-2" style="font-size:.78rem;">
            <div class="d-flex justify-content-between align-items-start">
              <div>
                <i class="fa fa-check-circle me-1"></i>
                <strong>Selected:</strong> <span id="selVarName"></span>
              </div>
              <a href="#" id="selVarChange" class="text-danger" style="font-size:.7rem;">
                <i class="fa fa-times me-1"></i>Change
              </a>
            </div>
            <div class="mt-1" style="font-size:.68rem;color:#6c757d;" id="selVarDetails"></div>
          </div>
          <div class="row g-2">
            <div class="col-6">
              <label class="form-label fw-bold" style="font-size:.72rem;">QUANTITY RECEIVED</label>
              <input type="number" class="form-control form-control-sm" id="selVarQty" placeholder="0" min="0" step="0.01">
            </div>
            <div class="col-6">
              <label class="form-label fw-bold" style="font-size:.72rem;">UNIT PRICE</label>
              <input type="number" class="form-control form-control-sm" id="selVarPrice" placeholder="0.00" min="0" step="0.01">
            </div>
          </div>
        </div>

        <!-- PHASE 3: Request new variant form (shown only when not in stock) -->
        <div id="modal-phase-request" class="d-none">
          <div class="d-flex justify-content-between align-items-center mb-2">
            <label class="form-label fw-bold mb-0" style="font-size:.78rem;color:#d97706;">
              <i class="fa fa-file-alt me-1"></i>Request New Variant
            </label>
            <a href="#" id="reqBackToSearch" class="text-muted" style="font-size:.7rem;">
              <i class="fa fa-arrow-left me-1"></i>Back to Search
            </a>
          </div>
          <div class="p-2 rounded-2 mb-2" style="background:#fffbeb;border:1px solid #fde68a;">
            <div class="text-muted mb-1" style="font-size:.65rem;">
              Fill in the item details below. A request will be submitted and the item will
              be added to this GRN with snapshot data while awaiting stock approval.
            </div>
            <div class="row g-2">
              <div class="col-12">
                <input type="text" class="form-control form-control-sm" id="reqName" placeholder="Item Name *">
              </div>
              <div class="col-6">
                <input type="text" class="form-control form-control-sm" id="reqBrand" placeholder="Brand">
              </div>
              <div class="col-6">
                <input type="text" class="form-control form-control-sm" id="reqSize" placeholder="Size">
              </div>
              <div class="col-6">
                <input type="text" class="form-control form-control-sm" id="reqColor" placeholder="Color">
              </div>
              <div class="col-6">
                <input type="text" class="form-control form-control-sm" id="reqUom" placeholder="UOM (e.g. Pcs, Kg)">
              </div>
              <div class="col-6">
                <input type="number" class="form-control form-control-sm" id="reqQty" placeholder="Quantity *" min="0" step="0.01">
              </div>
              <div class="col-6">
                <input type="number" class="form-control form-control-sm" id="reqPrice" placeholder="Unit Price *" min="0" step="0.01">
              </div>
              <div class="col-12">
                <input type="text" class="form-control form-control-sm" id="reqNotes" placeholder="Notes (optional)">
              </div>
            </div>
          </div>
        </div>

        <div id="modalMsg" class="mt-2"></div>
      </div>
      <div class="modal-footer py-2">
        <button type="button" class="btn btn-secondary btn-sm" data-bs-dismiss="modal">Cancel</button>
        <button type="button" class="btn btn-success btn-sm d-none" id="btnAddSelected">
          <i class="fa fa-plus me-1"></i>Add to GRN
        </button>
        <button type="button" class="btn btn-warning btn-sm d-none" id="btnAddRequested">
          <i class="fa fa-paper-plane me-1"></i>Request &amp; Add to GRN
        </button>
      </div>
    </div>
  </div>
</div>

<!-- ═══════════════════════════════════════════════════════════════════════════ -->
<!-- JAVASCRIPT                                                                -->
<!-- ═══════════════════════════════════════════════════════════════════════════ -->
<script>
(function () {
'use strict';

var _currentPoId = 0;
var _poItems     = [];
var _extraItems  = [];

// ── Utilities ─────────────────────────────────────────────────────────────────
function _e(s) {
  var d = document.createElement('div');
  d.textContent = String(s == null ? '' : s);
  return d.innerHTML;
}
function _fmt(n) {
  n = parseFloat(n) || 0;
  if (n >= 1e6) return 'KSh\u00a0' + (n / 1e6).toFixed(2) + 'M';
  if (n >= 1e3) return 'KSh\u00a0' + (n / 1e3).toFixed(1) + 'K';
  return 'KSh\u00a0' + n.toLocaleString('en-KE', {minimumFractionDigits:2});
}
function _d(s) { return s ? String(s).split(' ')[0] : '–'; }
function _poType(t) {
  return t == 1
    ? '<span class="badge bg-info">Intl</span>'
    : '<span class="badge bg-secondary">Local</span>';
}
function _grnStatus(s) {
  s = parseInt(s);
  if (s === 1) return '<span class="badge grn-status-1" style="font-size:.62rem;">Complete</span>';
  if (s === 3) return '<span class="badge grn-status-3" style="font-size:.62rem;">Ongoing</span>';
  return '<span class="badge bg-secondary" style="font-size:.62rem;">Unknown</span>';
}

// ══════════════════════════════════════════════════════════════════════════════
// STEP NAVIGATION
// ══════════════════════════════════════════════════════════════════════════════
function _showStep(name) {
  document.querySelectorAll('.rec-step').forEach(function (el) { el.classList.remove('active'); });
  var el = document.getElementById('step-' + name);
  if (el) el.classList.add('active');

  document.querySelectorAll('.rec-nav-btn').forEach(function (b) { b.classList.remove('active'); });
  if (name === 'pos')  document.getElementById('nav-pos').classList.add('active');
  if (name === 'grns') document.getElementById('nav-grns').classList.add('active');

  document.querySelectorAll('.rec-bc-step').forEach(function (s) { s.classList.remove('active'); });
  if (name === 'pos')     document.getElementById('bc-pos').classList.add('active');
  if (name === 'receive') document.getElementById('bc-receive').classList.add('active');
  if (name === 'success') document.getElementById('bc-success').classList.add('active');
}

// ══════════════════════════════════════════════════════════════════════════════
// STEP 1: PO LIST
// ══════════════════════════════════════════════════════════════════════════════
function recShowPos() {
  _showStep('pos');
  recLoadPos();
}

function recLoadPos() {
  $('#pos-loader').removeClass('d-none');
  $('#pos-empty').addClass('d-none');
  $('#pos-wrap').addClass('d-none');

  $.get('kug/rec/api/rec_api.php', {action:'list_pos'}, function (r) {
    $('#pos-loader').addClass('d-none');
    if (!r || !r.success) {
      var msg = (r && r.message) ? _e(r.message) : 'Failed to load POs.';
      $('#pos-loader').removeClass('d-none').html(
        '<div class="alert alert-danger py-2 small"><i class="fa fa-exclamation-triangle me-1"></i>' + msg + '</div>');
      return;
    }
    var list = r.pos || [];
    $('#kpi-pending').text(list.length);
    if (list.length > 0) {
      $('#nav-pos-badge').text(list.length).removeClass('d-none');
    } else {
      $('#nav-pos-badge').addClass('d-none');
    }
    if (!list.length) {
      $('#pos-empty').removeClass('d-none');
      return;
    }
    var h = '';
    list.forEach(function (row) {
      var statusBadge = row.is_partial
        ? '<span class="badge bg-warning text-dark" style="font-size:.6rem;"><i class="fa fa-exclamation-triangle me-1"></i>Partial</span>'
        : '<span class="badge bg-info text-white" style="font-size:.6rem;"><i class="fa fa-clock me-1"></i>New</span>';
      var recvCol = row.items_received > 0
        ? '<span class="text-success fw-bold">' + _e(row.items_received) + '</span>/' + _e(row.item_count)
        : '<span class="text-muted">0/' + _e(row.item_count) + '</span>';
      var btnText = row.is_partial ? 'Continue Receiving' : 'Start Receiving';
      var btnIcon = row.is_partial ? 'fa-redo' : 'fa-inbox';
      var grnInfo = row.grn_count > 0
        ? '<br><small class="text-muted">' + _e(row.grn_count) + ' GRN(s)</small>'
        : '';
      h += '<tr>' +
        '<td><strong class="text-primary">#' + _e(row.po_id) + '</strong>' + grnInfo + '</td>' +
        '<td><span class="fw-medium">' + _e(row.sup_name) + '</span></td>' +
        '<td class="text-muted">' + _e(row.raised_by) + '</td>' +
        '<td class="text-muted">' + _d(row.po_date) + '</td>' +
        '<td class="fw-medium">' + _fmt(row.amount) + '</td>' +
        '<td class="text-center"><span class="badge bg-secondary">' + _e(row.item_count) + '</span></td>' +
        '<td class="text-center">' + recvCol + '</td>' +
        '<td>' + statusBadge + '</td>' +
        '<td>' + _poType(row.potype) + '</td>' +
        '<td class="text-center">' +
          '<button class="btn btn-xs btn-success" onclick="recOpenPo(' + _e(row.po_id) + ')">' +
          '<i class="fa ' + btnIcon + ' me-1"></i>' + btnText + '</button>' +
        '</td>' +
        '</tr>';
    });
    $('#pos-tbody').html(h);
    $('#pos-wrap').removeClass('d-none');
  }, 'json').fail(function (xhr, status, error) {
    $('#pos-loader').addClass('d-none').end;
    $('#pos-loader').removeClass('d-none').html(
      '<div class="alert alert-danger py-2 small"><i class="fa fa-exclamation-triangle me-1"></i>Network error: ' + _e(error || 'Could not reach server') + '</div>');
  });
}

// ══════════════════════════════════════════════════════════════════════════════
// STEP 2: OPEN PO FOR RECEIVING
// ══════════════════════════════════════════════════════════════════════════════
function recOpenPo(po_id) {
  _currentPoId = po_id;
  _extraItems  = [];
  _showStep('receive');
  $('#recv-po-num').text('#' + po_id);
  $('#recv-po-info').html('');
  $('#recv-items-loader').removeClass('d-none');
  $('#recv-items-wrap').addClass('d-none');
  $('#recv-msg').html('');

  $.get('kug/rec/api/rec_api.php', {action:'get_po_detail', po_id: po_id}, function (r) {
    $('#recv-items-loader').addClass('d-none');
    if (!r || typeof r !== 'object' || !r.success) {
      var msg = (r && r.message) ? _e(r.message) : 'Unknown error.';
      if (msg.toLowerCase().indexOf('unauthorized') >= 0) {
        msg = '<strong>Session expired or not logged in.</strong><br>Please log in to continue.';
      }
      $('#recv-items-loader').removeClass('d-none').html(
        '<div class="alert alert-danger py-2 small"><i class="fa fa-exclamation-triangle me-1"></i>' + msg + '</div>');
      return;
    }
    var po = r.po || {};

    // PO info cards
    var iHtml =
      '<div class="col-md-3 col-6"><div class="card p-2" style="border-color:#e5e7eb;">' +
        '<div style="font-size:.6rem;font-weight:700;text-transform:uppercase;color:#6c757d;">Supplier</div>' +
        '<div class="fw-bold" style="font-size:.85rem;">' + _e(po.sup_name || '–') + '</div>' +
      '</div></div>' +
      '<div class="col-md-2 col-6"><div class="card p-2" style="border-color:#e5e7eb;">' +
        '<div style="font-size:.6rem;font-weight:700;text-transform:uppercase;color:#6c757d;">PO Date</div>' +
        '<div class="fw-bold" style="font-size:.85rem;">' + _d(po.po_date || '') + '</div>' +
      '</div></div>' +
      '<div class="col-md-2 col-6"><div class="card p-2" style="border-color:#e5e7eb;">' +
        '<div style="font-size:.6rem;font-weight:700;text-transform:uppercase;color:#6c757d;">PO Amount</div>' +
        '<div class="fw-bold text-success" style="font-size:.85rem;">' + _fmt(po.amount || 0) + '</div>' +
      '</div></div>' +
      '<div class="col-md-2 col-6"><div class="card p-2" style="border-color:#e5e7eb;">' +
        '<div style="font-size:.6rem;font-weight:700;text-transform:uppercase;color:#6c757d;">Raised By</div>' +
        '<div class="fw-bold" style="font-size:.85rem;">' + _e(po.raised_by || '–') + '</div>' +
      '</div></div>' +
      '<div class="col-md-3 col-6"><div class="card p-2" style="border-color:' + (po.is_partial ? '#f59e0b' : '#e5e7eb') + ';">' +
        '<div style="font-size:.6rem;font-weight:700;text-transform:uppercase;color:#6c757d;">Receiving Status</div>' +
        '<div class="fw-bold" style="font-size:.85rem;">' +
          (po.is_partial
            ? '<span style="color:#f59e0b;"><i class="fa fa-exclamation-triangle me-1"></i>Partial — more items remain</span>'
            : '<span class="text-info"><i class="fa fa-clock me-1"></i>First Delivery</span>') +
        '</div>' +
      '</div></div>';
    $('#recv-po-info').html(iHtml);

    // Items table
    _poItems = Array.isArray(r.items) ? r.items : [];
    var h = '';
    if (_poItems.length === 0) {
      h = '<tr><td colspan="14" class="text-center text-muted">No items found for this PO.</td></tr>';
    } else {
      _poItems.forEach(function (it, idx) {
        var prevRecv    = parseFloat(it.rec_qty) || 0;
        var remaining   = parseFloat(it.remaining_qty) || 0;
        var isComplete  = (it.rec_status === 1);
        var defaultRecv = isComplete ? 0 : remaining;
        var rowClass    = isComplete ? ' class="table-secondary"' : (prevRecv > 0 ? ' class="table-warning"' : '');
        var prevBadge   = prevRecv > 0
          ? '<span class="badge bg-success" style="font-size:.6rem;">' + prevRecv + '</span>'
          : '<span class="text-muted" style="font-size:.65rem;">–</span>';
        var remainBadge = isComplete
          ? '<span class="badge bg-light text-success" style="font-size:.6rem;"><i class="fa fa-check me-1"></i>Done</span>'
          : '<strong>' + remaining + '</strong>';

        h += '<tr id="recv-row-' + _e(it.id) + '"' + rowClass + '>' +
          '<td class="text-muted">' + (idx + 1) + '</td>' +
          '<td><span class="fw-medium">' + _e(it.item_name) + '</span>' +
            (it.product_code ? '<br><small class="text-muted">' + _e(it.product_code) + '</small>' : '') +
          '</td>' +
          '<td class="text-muted">' + _e(it.brand || '–') + '</td>' +
          '<td class="text-muted">' + _e(it.size  || '–') + '</td>' +
          '<td class="text-muted">' + _e(it.color || '–') + '</td>' +
          '<td class="text-muted">' + _e(it.uom   || '–') + '</td>' +
          '<td class="text-center"><strong>' + _e(it.qty) + '</strong></td>' +
          '<td class="text-center">' + prevBadge + '</td>' +
          '<td class="text-center">' + remainBadge + '</td>' +
          '<td class="text-center">' +
            (isComplete
              ? '<span class="text-muted" style="font-size:.7rem;">–</span>'
              : '<input type="number" class="recv-qty-input' + (defaultRecv === remaining ? ' qty-exact' : '') + '" min="0" step="0.01"' +
                ' id="rqty-' + _e(it.id) + '" data-item-id="' + _e(it.id) + '"' +
                ' data-ordered="' + _e(remaining) + '" data-price="' + _e(it.price) + '"' +
                ' value="' + defaultRecv + '" oninput="recQtyChanged(this)">') +
          '</td>' +
          '<td class="text-end">' + _fmt(it.price) + '</td>' +
          '<td class="text-end">' +
            (isComplete
              ? '<span class="text-muted" style="font-size:.7rem;">–</span>'
              : '<input type="number" class="recv-qty-input" min="0" step="0.01"' +
                ' id="rprice-' + _e(it.id) + '" data-item-id="' + _e(it.id) + '"' +
                ' value="' + _e(it.price) + '" oninput="recPriceChanged(this)">') +
          '</td>' +
          '<td class="text-end" id="rtotal-' + _e(it.id) + '">' + _fmt(defaultRecv * (parseFloat(it.price)||0)) + '</td>' +
          '<td>' +
            (isComplete
              ? '<span class="badge bg-success" style="font-size:.55rem;">COMPLETE</span>'
              : '<input type="text" class="form-control form-control-sm" id="rextra-' + _e(it.id) + '" placeholder="Notes…">') +
          '</td>' +
          '</tr>';
      });
    }
    $('#recv-items-tbody').html(h);
    recRenderExtraItems();
    recUpdateTotal();
    $('#recv-items-wrap').removeClass('d-none');
  }, 'json').fail(function (xhr, status, error) {
    $('#recv-items-loader').removeClass('d-none').html(
      '<div class="alert alert-danger py-2 small"><i class="fa fa-exclamation-triangle me-1"></i>Network error: ' + _e(error || 'Could not reach server') + '</div>');
  });
}

// ══════════════════════════════════════════════════════════════════════════════
// QTY / PRICE CHANGE HANDLERS
// ══════════════════════════════════════════════════════════════════════════════
function recQtyChanged(el) {
  var ordered = parseFloat(el.getAttribute('data-ordered')) || 0;
  var itemId  = el.getAttribute('data-item-id');
  var entered = parseFloat(el.value) || 0;
  var priceEl = document.getElementById('rprice-' + itemId);
  var price   = priceEl ? parseFloat(priceEl.value) || 0 : 0;

  el.classList.remove('qty-exact','qty-short','qty-over');
  if (entered === ordered) el.classList.add('qty-exact');
  else if (entered < ordered) el.classList.add('qty-short');
  else el.classList.add('qty-over');

  $('#rtotal-' + itemId).text(_fmt(entered * price));
  recUpdateTotal();
}

function recPriceChanged(el) {
  var itemId = el.getAttribute('data-item-id');
  var qtyEl  = document.getElementById('rqty-' + itemId);
  var qty    = qtyEl ? parseFloat(qtyEl.value) || 0 : 0;
  var price  = parseFloat(el.value) || 0;
  $('#rtotal-' + itemId).text(_fmt(qty * price));
  recUpdateTotal();
}

function recUpdateTotal() {
  var total = 0;
  _poItems.forEach(function (it) {
    if (it.rec_status === 1) return; // skip completed items
    var qtyEl   = document.getElementById('rqty-' + it.id);
    var priceEl = document.getElementById('rprice-' + it.id);
    var qty     = qtyEl   ? parseFloat(qtyEl.value)   || 0 : 0;
    var price   = priceEl ? parseFloat(priceEl.value)  || 0 : it.price;
    total += qty * price;
  });
  _extraItems.forEach(function (it, idx) {
    var qty   = parseFloat(it.recv_qty) || 0;
    var price = parseFloat(it.recv_price) || 0;
    total += qty * price;
  });
  $('#recv-total').text(_fmt(total));
}

// ══════════════════════════════════════════════════════════════════════════════
// EXTRA ITEMS (Not in PO)
// ══════════════════════════════════════════════════════════════════════════════
function recAddExtraItem() {
  var modalEl = document.getElementById('extraItemModal');
  if (!modalEl) return;
  var modal = new bootstrap.Modal(modalEl);
  // Reset modal to search-only phase
  _modalReset();
  modal.show();
  setTimeout(function(){ $('#extraItemSearch').focus(); }, 300);
}

function _modalReset() {
  window._selectedVariant = null;
  $('#extraItemSearch').val('');
  $('#extraItemSearchResults').html('');
  $('#extraItemNotFound').addClass('d-none');
  $('#modal-phase-search').show();
  $('#modal-phase-selected').addClass('d-none');
  $('#modal-phase-request').addClass('d-none');
  $('#btnAddSelected').addClass('d-none');
  $('#btnAddRequested').addClass('d-none');
  $('#selVarQty').val('');
  $('#selVarPrice').val('');
  $('#reqName,#reqBrand,#reqSize,#reqColor,#reqUom,#reqQty,#reqPrice,#reqNotes').val('');
  $('#modalMsg').html('');
}

function _modalShowSelected(v) {
  window._selectedVariant = v;
  $('#modal-phase-search').hide();
  $('#modal-phase-request').addClass('d-none');
  $('#modal-phase-selected').removeClass('d-none');
  $('#btnAddSelected').removeClass('d-none');
  $('#btnAddRequested').addClass('d-none');

  $('#selVarName').text(v.item_name);
  var details = [];
  if (v.brand) details.push('Brand: ' + v.brand);
  if (v.size)  details.push('Size: ' + v.size);
  if (v.color) details.push('Color: ' + v.color);
  if (v.uom)   details.push('UOM: ' + v.uom);
  if (v.variation_code) details.push('Code: ' + v.variation_code);
  if (v.barcode) details.push('Barcode: ' + v.barcode);
  $('#selVarDetails').text(details.join(' | '));
  if (v.buy_price) $('#selVarPrice').val(v.buy_price);
}

function _modalShowRequest() {
  $('#modal-phase-search').hide();
  $('#modal-phase-selected').addClass('d-none');
  $('#modal-phase-request').removeClass('d-none');
  $('#btnAddSelected').addClass('d-none');
  $('#btnAddRequested').removeClass('d-none');
  // Pre-fill the search term into item name
  var q = $('#extraItemSearch').val().trim();
  if (q) $('#reqName').val(q);
}

function recRenderExtraItems() {
  var h = '';
  _extraItems.forEach(function(it, idx) {
    var reqBadge = it._is_request
      ? '<span class="badge bg-warning text-dark ms-1" style="font-size:.5rem;">PENDING APPROVAL</span>'
      : '';
    h += '<tr class="table-info">' +
      '<td><span class="badge bg-primary" style="font-size:.55rem;">EXTRA</span>' + reqBadge + '</td>' +
      '<td><span class="fw-medium">' + _e(it.item_name) + '</span>' +
        (it.brand ? '<br><small class="text-muted">' + _e(it.brand) + '</small>' : '') +
      '</td>' +
      '<td class="text-muted">' + _e(it.brand || '–') + '</td>' +
      '<td class="text-muted">' + _e(it.size || '–') + '</td>' +
      '<td class="text-muted">' + _e(it.color || '–') + '</td>' +
      '<td class="text-muted">' + _e(it.uom || '–') + '</td>' +
      '<td class="text-center text-muted">–</td>' +
      '<td class="text-center text-muted">–</td>' +
      '<td class="text-center text-muted">–</td>' +
      '<td class="text-center"><input type="number" class="recv-qty-input" min="0" step="0.01" value="' + (it.recv_qty||0) + '" onchange="recSetExtra(' + idx + ',\'recv_qty\',this.value); recUpdateTotal();"></td>' +
      '<td class="text-end text-muted">–</td>' +
      '<td class="text-end"><input type="number" class="recv-qty-input" min="0" step="0.01" value="' + (it.recv_price||0) + '" onchange="recSetExtra(' + idx + ',\'recv_price\',this.value); recUpdateTotal();"></td>' +
      '<td class="text-end">' + _fmt((parseFloat(it.recv_qty)||0) * (parseFloat(it.recv_price)||0)) + '</td>' +
      '<td><button class="btn btn-xs btn-danger" onclick="recRemoveExtra(' + idx + ')"><i class="fa fa-trash"></i></button></td>' +
      '</tr>';
  });
  $('#recv-extra-tbody').html(h);
}

function recSetExtra(idx, key, val) {
  if (_extraItems[idx]) _extraItems[idx][key] = val;
  recRenderExtraItems();
}

function recRemoveExtra(idx) {
  _extraItems.splice(idx, 1);
  recRenderExtraItems();
  recUpdateTotal();
}

// ══════════════════════════════════════════════════════════════════════════════
// MODAL: SEARCH STOCK VARIANT (live search)
// ══════════════════════════════════════════════════════════════════════════════
var _searchTimer = null;
$(document).on('input', '#extraItemSearch', function() {
  var q = $(this).val().trim();
  var resultsDiv = $('#extraItemSearchResults');
  var notFound   = $('#extraItemNotFound');
  resultsDiv.html('');
  notFound.addClass('d-none');
  window._selectedVariant = null;
  if (!q || q.length < 2) return;

  clearTimeout(_searchTimer);
  _searchTimer = setTimeout(function() {
    resultsDiv.html('<div class="text-muted small py-1"><i class="fa fa-spinner fa-spin me-1"></i>Searching…</div>');
    $.get('kug/rec/api/rec_api.php', {action:'search_variant', q:q}, function(r) {
      if (!r || !r.success || !r.variants || !r.variants.length) {
        resultsDiv.html('');
        notFound.removeClass('d-none');
        return;
      }
      notFound.addClass('d-none');
      var h = '<div class="list-group list-group-flush">';
      r.variants.forEach(function(v, i) {
        var desc = [];
        if (v.brand) desc.push(v.brand);
        if (v.size)  desc.push(v.size);
        if (v.color) desc.push(v.color);
        if (v.uom && v.uom !== '–') desc.push(v.uom);
        var sub = desc.length ? ' <span class="text-muted">(' + _e(desc.join(', ')) + ')</span>' : '';
        var code = v.variation_code ? '<span class="badge bg-light text-dark ms-1" style="font-size:.58rem;">' + _e(v.variation_code) + '</span>' : '';
        h += '<a href="#" class="list-group-item list-group-item-action py-2 variant-pick" data-idx="' + i + '"' +
          ' style="font-size:.78rem;">' +
          '<strong>' + _e(v.item_name) + '</strong>' + code + sub +
          '</a>';
      });
      h += '</div>';
      resultsDiv.html(h);

      // Click handler for each result
      resultsDiv.find('.variant-pick').on('click', function(e) {
        e.preventDefault();
        var idx = $(this).data('idx');
        _modalShowSelected(r.variants[idx]);
      });
    }, 'json').fail(function() {
      resultsDiv.html('<div class="text-danger small py-1"><i class="fa fa-exclamation-triangle me-1"></i>Search failed.</div>');
    });
  }, 300); // 300ms debounce
});

// "Request New Variant" link (when not found)
$(document).on('click', '#extraShowRequestForm', function(e) {
  e.preventDefault();
  _modalShowRequest();
});

// "Back to Search" link (from request form)
$(document).on('click', '#reqBackToSearch', function(e) {
  e.preventDefault();
  _modalReset();
  $('#extraItemSearch').focus();
});

// "Change" link (from selected phase)
$(document).on('click', '#selVarChange', function(e) {
  e.preventDefault();
  _modalReset();
  $('#extraItemSearch').focus();
});

// ── Button: Add selected variant to GRN ──────────────────────────────────────
$(document).on('click', '#btnAddSelected', function() {
  var v = window._selectedVariant;
  if (!v) return;
  var qty   = parseFloat($('#selVarQty').val()) || 0;
  var price = parseFloat($('#selVarPrice').val()) || 0;
  if (qty <= 0) {
    $('#modalMsg').html('<div class="alert alert-danger py-1 small"><i class="fa fa-exclamation-triangle me-1"></i>Enter a quantity.</div>');
    return;
  }
  if (price <= 0) {
    $('#modalMsg').html('<div class="alert alert-danger py-1 small"><i class="fa fa-exclamation-triangle me-1"></i>Enter a unit price.</div>');
    return;
  }
  _extraItems.push({
    item_name:    v.item_name,
    brand:        v.brand || '',
    size:         v.size  || '',
    color:        v.color || '',
    uom:          v.uom   || '',
    product_code: v.product_code || v.variation_code || '',
    barcode:      v.barcode || '',
    variant_id:   v.variant_id || 0,
    recv_qty:     qty,
    recv_price:   price,
    extra:        '',
    _is_request:  false
  });
  recRenderExtraItems();
  recUpdateTotal();
  var modal = bootstrap.Modal.getInstance(document.getElementById('extraItemModal'));
  if (modal) modal.hide();
});

// ── Button: Request new variant + add snapshot to GRN ────────────────────────
$(document).on('click', '#btnAddRequested', function() {
  var name  = $('#reqName').val().trim();
  var brand = $('#reqBrand').val().trim();
  var size  = $('#reqSize').val().trim();
  var color = $('#reqColor').val().trim();
  var uom   = $('#reqUom').val().trim();
  var qty   = parseFloat($('#reqQty').val()) || 0;
  var price = parseFloat($('#reqPrice').val()) || 0;
  var notes = $('#reqNotes').val().trim();

  if (!name) {
    $('#modalMsg').html('<div class="alert alert-danger py-1 small"><i class="fa fa-exclamation-triangle me-1"></i>Item Name is required.</div>');
    return;
  }
  if (qty <= 0) {
    $('#modalMsg').html('<div class="alert alert-danger py-1 small"><i class="fa fa-exclamation-triangle me-1"></i>Enter a quantity.</div>');
    return;
  }
  if (price <= 0) {
    $('#modalMsg').html('<div class="alert alert-danger py-1 small"><i class="fa fa-exclamation-triangle me-1"></i>Enter a unit price.</div>');
    return;
  }

  // Submit variant request to backend
  $('#btnAddRequested').prop('disabled', true).html('<i class="fa fa-spinner fa-spin me-1"></i>Submitting…');
  $.post('kug/rec/api/rec_api.php', {
    action:    'request_variant',
    item_name: name,
    brand:     brand,
    size:      size,
    color:     color,
    uom:       uom,
    notes:     notes
  }, function(r) {
    $('#btnAddRequested').prop('disabled', false).html('<i class="fa fa-paper-plane me-1"></i>Request &amp; Add to GRN');
    if (!r || !r.success) {
      $('#modalMsg').html('<div class="alert alert-danger py-1 small"><i class="fa fa-exclamation-triangle me-1"></i>' + _e(r && r.message ? r.message : 'Request failed') + '</div>');
      return;
    }

    // Add snapshot to GRN extra items (before variant is approved into stock)
    _extraItems.push({
      item_name:    name,
      brand:        brand,
      size:         size,
      color:        color,
      uom:          uom,
      product_code: '',
      barcode:      '',
      variant_id:   0,
      recv_qty:     qty,
      recv_price:   price,
      extra:        'Variant request #' + r.request_id + ' (pending approval)',
      _is_request:  true,
      _request_id:  r.request_id
    });
    recRenderExtraItems();
    recUpdateTotal();
    var modal = bootstrap.Modal.getInstance(document.getElementById('extraItemModal'));
    if (modal) modal.hide();
  }, 'json').fail(function() {
    $('#btnAddRequested').prop('disabled', false).html('<i class="fa fa-paper-plane me-1"></i>Request &amp; Add to GRN');
    $('#modalMsg').html('<div class="alert alert-danger py-1 small"><i class="fa fa-exclamation-triangle me-1"></i>Network error.</div>');
  });
});

// ══════════════════════════════════════════════════════════════════════════════
// CREATE GRN
// ══════════════════════════════════════════════════════════════════════════════
function recCreateGrn() {
  var inv   = $('#recv-inv').val().trim();
  var dnote = $('#recv-dnote').val().trim();
  var day   = $('#recv-day').val();
  var tax   = $('#recv-tax').val();

  if (!day) {
    $('#recv-msg').html('<div class="alert alert-danger py-2 small"><i class="fa fa-exclamation-triangle me-1"></i>Please enter the received date.</div>');
    return;
  }

  var items = [];
  var hasQty = false;
  _poItems.forEach(function (it) {
    if (it.rec_status === 1) return; // skip already fully received items
    var qtyEl   = document.getElementById('rqty-' + it.id);
    var priceEl = document.getElementById('rprice-' + it.id);
    var extraEl = document.getElementById('rextra-' + it.id);
    var qty   = qtyEl   ? Math.max(0, parseFloat(qtyEl.value)   || 0) : 0;
    var price = priceEl ? Math.max(0, parseFloat(priceEl.value)  || 0) : it.price;
    var extra = extraEl ? extraEl.value.trim() : '';
    items.push({po_item_id: it.id, recv_qty: qty, recv_price: price, extra: extra});
    if (qty > 0) hasQty = true;
  });

  _extraItems.forEach(function(it) {
    var qty   = Math.max(0, parseFloat(it.recv_qty)   || 0);
    var price = Math.max(0, parseFloat(it.recv_price)  || 0);
    if (qty > 0 && price > 0 && it.item_name) {
      items.push({
        item_name: it.item_name, product_code: it.product_code||'', barcode: it.barcode||'',
        uom: it.uom||'', brand: it.brand||'', size: it.size||'', color: it.color||'',
        variant_id: it.variant_id||0,
        recv_qty: qty, recv_price: price, extra: it.extra||'',
        is_request: it._is_request ? 1 : 0,
        request_id: it._request_id || 0
      });
      hasQty = true;
    }
  });

  if (!hasQty) {
    $('#recv-msg').html('<div class="alert alert-danger py-2 small"><i class="fa fa-exclamation-triangle me-1"></i>Please enter at least one received quantity.</div>');
    return;
  }

  $('#recv-msg').html('<div class="text-muted small"><i class="fa fa-spinner fa-spin me-1"></i>Creating GRN…</div>');
  $('#btn-create-grn').prop('disabled', true);

  $.post('kug/rec/api/rec_api.php', {
    action: 'create_grn',
    po_id:  _currentPoId,
    inv:    inv,
    dnote:  dnote,
    day:    day,
    tax:    tax,
    items:  JSON.stringify(items)
  }, function (r) {
    $('#btn-create-grn').prop('disabled', false);
    if (!r || !r.success) {
      $('#recv-msg').html('<div class="alert alert-danger py-2 small"><i class="fa fa-exclamation-triangle me-1"></i>' + _e(r && r.message ? r.message : 'Server error') + '</div>');
      return;
    }
    recShowSuccess(r.grn_id, inv, dnote, day, r.po_complete);
  }, 'json').fail(function () {
    $('#btn-create-grn').prop('disabled', false);
    $('#recv-msg').html('<div class="alert alert-danger py-2 small"><i class="fa fa-exclamation-triangle me-1"></i>Server error. Please try again.</div>');
  });
}

// ══════════════════════════════════════════════════════════════════════════════
// STEP 3: SUCCESS SCREEN
// ══════════════════════════════════════════════════════════════════════════════
function recShowSuccess(grn_id, inv, dnote, day, po_complete) {
  _showStep('success');
  var statusMsg = po_complete
    ? '<span class="text-success"><i class="fa fa-check-circle me-1"></i>PO Fully Received</span>'
    : '<span style="color:#f59e0b;"><i class="fa fa-exclamation-triangle me-1"></i>PO Partially Received — Remaining items can still be delivered</span>';
  var html =
    '<div class="col-md-3 col-6"><div class="card p-2 border-success">' +
      '<div style="font-size:.6rem;font-weight:700;text-transform:uppercase;color:#6c757d;">GRN #</div>' +
      '<div class="fw-bold text-success" style="font-size:1.2rem;">#' + _e(grn_id) + '</div>' +
    '</div></div>' +
    '<div class="col-md-2 col-6"><div class="card p-2" style="border-color:#e5e7eb;">' +
      '<div style="font-size:.6rem;font-weight:700;text-transform:uppercase;color:#6c757d;">PO #</div>' +
      '<div class="fw-bold" style="font-size:.85rem;">#' + _e(_currentPoId) + '</div>' +
    '</div></div>' +
    '<div class="col-md-2 col-6"><div class="card p-2" style="border-color:#e5e7eb;">' +
      '<div style="font-size:.6rem;font-weight:700;text-transform:uppercase;color:#6c757d;">Invoice #</div>' +
      '<div class="fw-bold" style="font-size:.85rem;">' + (inv ? _e(inv) : '<em class="text-muted">N/A</em>') + '</div>' +
    '</div></div>' +
    '<div class="col-md-2 col-6"><div class="card p-2" style="border-color:#e5e7eb;">' +
      '<div style="font-size:.6rem;font-weight:700;text-transform:uppercase;color:#6c757d;">Received Date</div>' +
      '<div class="fw-bold" style="font-size:.85rem;">' + _e(day) + '</div>' +
    '</div></div>' +
    '<div class="col-md-3 col-6"><div class="card p-2" style="border-color:' + (po_complete ? '#16a34a' : '#f59e0b') + ';">' +
      '<div style="font-size:.6rem;font-weight:700;text-transform:uppercase;color:#6c757d;">PO Status</div>' +
      '<div class="fw-bold" style="font-size:.8rem;">' + statusMsg + '</div>' +
    '</div></div>';
  $('#success-info').html(html);
  var cur = parseInt($('#kpi-today').text()) || 0;
  $('#kpi-today').text(cur + 1);
  recViewGrn(grn_id);
}

function recViewGrn(grn_id) {
  $('#success-items').html('<div class="text-center py-3 text-muted small"><i class="fa fa-spinner fa-spin me-1"></i>Loading items…</div>');
  $.get('kug/rec/api/rec_api.php', {action:'view_grn', grn_id:grn_id}, function (r) {
    if (!r || !r.success || !r.items || !r.items.length) {
      $('#success-items').html('<div class="text-muted text-center py-3 small">No items found.</div>');
      return;
    }
    var h = '<table class="table table-sm table-hover mb-0 rec-tbl"><thead><tr>' +
      '<th>#</th><th>Item / Description</th><th>Code</th>' +
      '<th class="text-center">Ordered</th><th class="text-center">Received</th>' +
      '<th class="text-end">Price</th><th class="text-end">Total</th><th>Store Status</th>' +
      '</tr></thead><tbody>';
    r.items.forEach(function (it, i) {
      var badge = it.tostore_status == 1
        ? '<span class="badge bg-success" style="font-size:.6rem;"><i class="fa fa-check me-1"></i>In Store</span>'
        : '<span class="badge bg-warning text-dark" style="font-size:.6rem;"><i class="fa fa-clock me-1"></i>Pending</span>';
      h += '<tr>' +
        '<td class="text-muted">' + (i+1) + '</td>' +
        '<td><span class="fw-medium">' + _e(it.receipt_desc || it.item_name || '–') + '</span></td>' +
        '<td class="text-muted" style="font-size:.72rem;">' + _e(it.product_code || '–') + '</td>' +
        '<td class="text-center text-muted">' + _e(it.qty) + '</td>' +
        '<td class="text-center"><strong class="text-success">' + _e(it.confirm_qty) + '</strong></td>' +
        '<td class="text-end">' + _fmt(it.price) + '</td>' +
        '<td class="text-end fw-medium">' + _fmt(it.total) + '</td>' +
        '<td>' + badge + '</td></tr>';
    });
    h += '</tbody></table>';
    $('#success-items').html(h);
  }, 'json').fail(function () {
    $('#success-items').html('<div class="text-danger text-center py-2 small"><i class="fa fa-exclamation-triangle me-1"></i>Failed to load items.</div>');
  });
}

// ══════════════════════════════════════════════════════════════════════════════
// GRN HISTORY
// ══════════════════════════════════════════════════════════════════════════════
function recShowGrns() {
  _showStep('grns');
  recLoadGrns();
}

function recLoadGrns() {
  $('#grns-loader').removeClass('d-none');
  $('#grns-empty').addClass('d-none');
  $('#grns-wrap').addClass('d-none');

  $.get('kug/rec/api/rec_api.php', {action:'list_grns'}, function (r) {
    $('#grns-loader').addClass('d-none');
    if (!r || !r.success) {
      $('#grns-loader').removeClass('d-none').html(
        '<div class="alert alert-danger py-2 small"><i class="fa fa-exclamation-triangle me-1"></i>' + _e(r && r.message ? r.message : 'Failed') + '</div>');
      return;
    }
    var list = r.grns || [];
    $('#kpi-total').text(list.length);
    if (!list.length) { $('#grns-empty').removeClass('d-none'); return; }

    var h = '';
    list.forEach(function (row) {
      var pct = row.item_count > 0 ? Math.round(row.items_stored / row.item_count * 100) : 0;
      var barColor = pct >= 100 ? '#16a34a' : '#f59c1a';
      h += '<tr id="grn-row-' + _e(row.grn_id) + '">' +
        '<td><strong class="text-info">#' + _e(row.grn_id) + '</strong></td>' +
        '<td class="text-muted">#' + _e(row.po_id) + '</td>' +
        '<td><span class="fw-medium">' + _e(row.sup_name) + '</span></td>' +
        '<td class="text-muted">' + _e(row.inv || '–') + '</td>' +
        '<td class="text-muted">' + _d(row.day) + '</td>' +
        '<td class="text-center"><span class="badge bg-secondary">' + _e(row.item_count) + '</span></td>' +
        '<td>' +
          '<div style="min-width:80px;">' +
            '<div class="d-flex justify-content-between mb-1" style="font-size:.65rem;">' +
              '<span>' + _e(row.items_stored) + '/' + _e(row.item_count) + '</span>' +
              '<span>' + pct + '%</span>' +
            '</div>' +
            '<div style="height:4px;background:#e5e7eb;border-radius:2px;">' +
              '<div style="width:' + pct + '%;height:4px;border-radius:2px;background:' + barColor + ';"></div>' +
            '</div>' +
          '</div>' +
        '</td>' +
        '<td class="fw-medium">' + _fmt(row.amount) + '</td>' +
        '<td>' + _grnStatus(row.status) + '</td>' +
        '<td class="text-muted">' + _e(row.received_by) + '</td>' +
        '<td class="text-center">' +
          '<button class="btn btn-xs btn-outline-info" title="View items"' +
          ' onclick="recToggleGrnDetail(' + _e(row.grn_id) + ', this)">' +
          '<i class="fa fa-eye"></i></button>' +
        '</td>' +
        '</tr>';
    });
    $('#grns-tbody').html(h);
    $('#grns-wrap').removeClass('d-none');
  }, 'json').fail(function () {
    $('#grns-loader').removeClass('d-none').html(
      '<div class="alert alert-danger py-2 small"><i class="fa fa-exclamation-triangle me-1"></i>Failed to load GRNs.</div>');
  });
}

function recToggleGrnDetail(grn_id, btn) {
  var detailId = 'grn-detail-' + grn_id;
  var existing = document.getElementById(detailId);
  if (existing) { $(existing).toggle(); return; }

  var $parentTr = $(btn).closest('tr');
  var colCount  = $parentTr.find('td').length;
  var $detailTr = $('<tr id="' + detailId + '"><td colspan="' + colCount + '" class="p-0" style="background:#f8f9fa;"></td></tr>');
  var $cell     = $detailTr.find('td');
  $cell.html('<div class="p-2 text-center text-muted small"><i class="fa fa-spinner fa-spin me-1"></i>Loading…</div>');
  $parentTr.after($detailTr);

  $.get('kug/rec/api/rec_api.php', {action:'view_grn', grn_id:grn_id}, function (r) {
    if (!r || !r.success) {
      $cell.html('<div class="text-danger p-2 small"><i class="fa fa-exclamation-triangle me-1"></i>Failed to load items.</div>');
      return;
    }
    var h = '<div style="padding:.4rem;"><table class="table table-sm mb-0" style="font-size:.74rem;">' +
      '<thead><tr style="background:#e8f4f1;">' +
      '<th>#</th><th>Item / Description</th><th>Code</th>' +
      '<th class="text-center">Ordered</th><th class="text-center">Received</th>' +
      '<th class="text-end">Price</th><th class="text-end">Total</th><th>Store Status</th></tr></thead><tbody>';
    (r.items || []).forEach(function (it, i) {
      var badge = it.tostore_status == 1
        ? '<span class="badge bg-success" style="font-size:.58rem;">In Store</span>'
        : '<span class="badge bg-warning text-dark" style="font-size:.58rem;">Pending</span>';
      h += '<tr>' +
        '<td class="text-muted">' + (i+1) + '</td>' +
        '<td>' + _e(it.receipt_desc || it.item_name || '–') + '</td>' +
        '<td class="text-muted">' + _e(it.product_code || '–') + '</td>' +
        '<td class="text-center text-muted">' + _e(it.qty) + '</td>' +
        '<td class="text-center text-success fw-bold">' + _e(it.confirm_qty) + '</td>' +
        '<td class="text-end">' + _fmt(it.price) + '</td>' +
        '<td class="text-end">' + _fmt(it.total) + '</td>' +
        '<td>' + badge + '</td></tr>';
    });
    if (!r.items || !r.items.length) {
      h += '<tr><td colspan="8" class="text-center text-muted py-2">No items.</td></tr>';
    }
    h += '</tbody></table></div>';
    $cell.html(h);
  }, 'json');
}

// ══════════════════════════════════════════════════════════════════════════════
// EXPOSE FUNCTIONS GLOBALLY
// ══════════════════════════════════════════════════════════════════════════════
window.recShowPos       = recShowPos;
window.recLoadPos       = recLoadPos;
window.recOpenPo        = recOpenPo;
window.recQtyChanged    = recQtyChanged;
window.recPriceChanged  = recPriceChanged;
window.recAddExtraItem  = recAddExtraItem;
window.recSetExtra      = recSetExtra;
window.recRemoveExtra   = recRemoveExtra;
window.recCreateGrn     = recCreateGrn;
window.recShowGrns      = recShowGrns;
window.recLoadGrns      = recLoadGrns;
window.recToggleGrnDetail = recToggleGrnDetail;

// ══════════════════════════════════════════════════════════════════════════════
// INIT
// ══════════════════════════════════════════════════════════════════════════════
App.setPageTitle('KSOFT | GRN Receiving');
App.restartGlobalFunction();

// Load PO list on init
recLoadPos();

// Load KPI counts from dedicated endpoint
$.get('kug/rec/api/rec_api.php', {action:'kpi_stats'}, function (r) {
  if (r && r.success) {
    $('#kpi-pending').text(r.pending + r.partial);
    $('#kpi-partial').text(r.partial);
    $('#kpi-today').text(r.today_grns);
    $('#kpi-total').text(r.total_grns);
  }
}, 'json');

})();
</script>
