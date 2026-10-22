<?php
/* ──────────────────────────────────────────────────────────────
   APPROVE LPO – Streamlined   (kug/app/index.php)
   Modelled after kug/rec/index.php — KPIs, sidebar, clean list
   ────────────────────────────────────────────────────────────── */
include(__DIR__ . '/funcs.php');
$user_id = $_SESSION['mwene'];
?>

<style>
/* ── KPI cards ──────────────────────────────────────────────────── */
.ap-kpi{border-radius:10px;padding:.85rem 1rem;box-shadow:0 2px 10px rgba(0,0,0,.06);}
.ap-kpi-label{font-size:.62rem;font-weight:700;text-transform:uppercase;letter-spacing:.04em;}
.ap-kpi-value{font-size:1.6rem;font-weight:800;line-height:1.1;}
.ap-kpi-sub{font-size:.65rem;margin-top:.15rem;}
.ap-kpi-icon{width:40px;height:40px;border-radius:8px;display:flex;align-items:center;justify-content:center;font-size:1rem;flex-shrink:0;}

/* ── Sidebar nav ───────────────────────────────────────────────── */
.ap-nav-btn{display:flex;align-items:center;gap:.5rem;width:100%;padding:.42rem .65rem;margin-bottom:.2rem;border-radius:6px;border:none;background:transparent;font-size:.77rem;font-weight:600;cursor:pointer;text-align:left;transition:all .15s;}
.ap-nav-btn:hover{background:#e0f7f4;color:#008080;}
.ap-nav-btn.active{background:#008080;color:#fff;}
.ap-nav-btn.active i{color:#fff!important;}
.ap-nav-btn i{width:16px;text-align:center;opacity:.7;}

/* ── Tables ─────────────────────────────────────────────────────── */
.ap-tbl thead th{font-size:.64rem;font-weight:700;text-transform:uppercase;letter-spacing:.04em;padding:.38rem .5rem;white-space:nowrap;background:#f8f9fa;}
.ap-tbl tbody td{font-size:.78rem;vertical-align:middle;padding:.35rem .5rem;}
.ap-tbl tbody tr:hover{background:#f5f7ff!important;}
.ap-tbl .po-link{font-weight:700;cursor:pointer;}
.ap-tbl .po-link:hover{text-decoration:underline;}

/* ── Status pills ───────────────────────────────────────────────── */
.st-pending{background:#fef3c7;color:#92400e;font-size:.62rem;font-weight:700;padding:2px 7px;border-radius:10px;}
.st-approved{background:#d1fae5;color:#065f46;font-size:.62rem;font-weight:700;padding:2px 7px;border-radius:10px;}
.st-rejected{background:#fee2e2;color:#991b1b;font-size:.62rem;font-weight:700;padding:2px 7px;border-radius:10px;}
.st-held{background:#e0e7ff;color:#3730a3;font-size:.62rem;font-weight:700;padding:2px 7px;border-radius:10px;}

hr{margin:.4375rem 0;color:#adb5bd;border:0;border-top:1px solid;opacity:.3;}
</style>

<!-- ═══ KPI ROW ═════════════════════════════════════════════════ -->
<div class="row g-2 mb-3">
  <div class="col-md-3 col-6">
    <div class="ap-kpi" style="background:linear-gradient(135deg,#fff7ed,#fff);border-left:4px solid #ea580c;">
      <div class="d-flex justify-content-between align-items-start">
        <div>
          <div class="ap-kpi-label text-muted">Pending Approval</div>
          <div class="ap-kpi-value" style="color:#ea580c;" id="ap-kpi-pending">–</div>
          <div class="ap-kpi-sub text-muted">awaiting your review</div>
        </div>
        <div class="ap-kpi-icon" style="background:#ffedd5;color:#ea580c;"><i class="fa fa-clock"></i></div>
      </div>
    </div>
  </div>
  <div class="col-md-3 col-6">
    <div class="ap-kpi" style="background:linear-gradient(135deg,#f0fdf4,#fff);border-left:4px solid #16a34a;">
      <div class="d-flex justify-content-between align-items-start">
        <div>
          <div class="ap-kpi-label text-muted">Approved</div>
          <div class="ap-kpi-value text-success" id="ap-kpi-approved">–</div>
          <div class="ap-kpi-sub text-muted">total approved POs</div>
        </div>
        <div class="ap-kpi-icon" style="background:#bbf7d0;color:#16a34a;"><i class="fa fa-check-circle"></i></div>
      </div>
    </div>
  </div>
  <div class="col-md-3 col-6">
    <div class="ap-kpi" style="background:linear-gradient(135deg,#fff1f2,#fff);border-left:4px solid #dc2626;">
      <div class="d-flex justify-content-between align-items-start">
        <div>
          <div class="ap-kpi-label text-muted">Rejected</div>
          <div class="ap-kpi-value" style="color:#dc2626;" id="ap-kpi-rejected">–</div>
          <div class="ap-kpi-sub text-muted">declined POs</div>
        </div>
        <div class="ap-kpi-icon" style="background:#fecdd3;color:#dc2626;"><i class="fa fa-times-circle"></i></div>
      </div>
    </div>
  </div>
  <div class="col-md-3 col-12">
    <div class="ap-kpi" style="background:linear-gradient(135deg,#f5f3ff,#fff);border-left:4px solid #7c3aed;">
      <div class="d-flex justify-content-between align-items-start">
        <div>
          <div class="ap-kpi-label text-muted">On Hold</div>
          <div class="ap-kpi-value" style="color:#7c3aed;" id="ap-kpi-held">–</div>
          <div class="ap-kpi-sub text-muted">held for further review</div>
        </div>
        <div class="ap-kpi-icon" style="background:#ede9fe;color:#7c3aed;"><i class="fa fa-pause-circle"></i></div>
      </div>
    </div>
  </div>
</div>

<!-- ═══ SUMMARY STRIP ══════════════════════════════════════════ -->
<div class="row g-2 mb-3">
  <div class="col-md-4 col-6">
    <div class="d-flex align-items-center gap-2 p-2 rounded-2" style="background:#fff7ed;border:1px solid #fed7aa;">
      <i class="fa fa-hourglass-half" style="color:#ea580c;font-size:.9rem;"></i>
      <div>
        <div style="font-size:.58rem;font-weight:700;text-transform:uppercase;color:#9a3412;">Pending Value</div>
        <div style="font-size:1rem;font-weight:800;color:#ea580c;" id="ap-fin-pend">–</div>
      </div>
    </div>
  </div>
  <div class="col-md-4 col-6">
    <div class="d-flex align-items-center gap-2 p-2 rounded-2" style="background:#ecfdf5;border:1px solid #a7f3d0;">
      <i class="fa fa-wallet" style="color:#059669;font-size:.9rem;"></i>
      <div>
        <div style="font-size:.58rem;font-weight:700;text-transform:uppercase;color:#065f46;">Approved This Month</div>
        <div style="font-size:1rem;font-weight:800;color:#059669;" id="ap-fin-month">–</div>
      </div>
    </div>
  </div>
  <div class="col-md-4 col-12">
    <div class="d-flex align-items-center gap-2 p-2 rounded-2" style="background:#eff6ff;border:1px solid #bfdbfe;">
      <i class="fa fa-calendar-day" style="color:#2563eb;font-size:.9rem;"></i>
      <div>
        <div style="font-size:.58rem;font-weight:700;text-transform:uppercase;color:#1e40af;">Today's Pending</div>
        <div style="font-size:1rem;font-weight:800;color:#2563eb;" id="ap-kpi-today">–</div>
      </div>
    </div>
  </div>
</div>

<div class="row mb-4">

  <!-- ═══ LEFT SIDEBAR ═══ -->
  <div class="col-md-2 col-12">
    <div class="widget">
      <div class="widget-header p-2">
        <h4 class="my-0 text-cyan" style="font-size:.76rem;"><i class="fa fa-cogs me-1"></i> OPERATIONS</h4>
      </div>
      <div class="p-2">
        <button class="ap-nav-btn active" id="nav-pending" onclick="apShowPanel('pending')">
          <i class="fa fa-clock text-warning"></i> Pending
          <span class="badge bg-danger ms-auto d-none" id="nav-pend-badge" style="font-size:.6rem;"></span>
        </button>
        <button class="ap-nav-btn" id="nav-approved" onclick="apShowPanel('approved')">
          <i class="fa fa-check-circle text-success"></i> Approved
        </button>
        <button class="ap-nav-btn" id="nav-rejected" onclick="apShowPanel('rejected')">
          <i class="fa fa-times-circle text-danger"></i> Rejected
        </button>
        <button class="ap-nav-btn" id="nav-held" onclick="apShowPanel('held')">
          <i class="fa fa-pause-circle text-info"></i> On Hold
          <span class="badge bg-info ms-auto d-none" id="nav-held-badge" style="font-size:.6rem;"></span>
        </button>
        <hr class="my-1">
        <button class="ap-nav-btn ajax-loader" data-folder="kug" data-file="index"
                data-target="#content" data-target-type="div" data-method="GET" data-id="0">
          <i class="fa fa-shopping-cart text-secondary"></i> Purchase Dashboard
        </button>
        <button class="ap-nav-btn ajax-loader" data-folder="kug/rec" data-file="index"
                data-target="#content" data-target-type="div" data-method="GET" data-id="0">
          <i class="fa fa-inbox text-primary"></i> Receive Goods
        </button>
        <button class="ap-nav-btn ajax-loader" data-folder="kug/init" data-file="initset"
                data-target="#load" data-modal="#modal" data-target-type="modal" data-method="GET" data-id="0">
          <i class="fa fa-plus-circle text-success"></i> Generate LPO
        </button>
      </div>
    </div>
  </div>

  <!-- ═══ MAIN CONTENT ═══ -->
  <div class="col-md-10 col-12" id="ap-main">

    <!-- ── PENDING PANEL ─────────────────────────────────────── -->
    <div class="ap-panel" id="panel-pending">
      <div class="widget">
        <div class="widget-header p-2 d-flex justify-content-between align-items-center"
             style="background:linear-gradient(90deg,#fff7ed,#fff);border-bottom:2px solid #fed7aa;">
          <h4 class="my-0 fw-bold" style="font-size:.82rem;color:#ea580c;">
            <i class="fa fa-clock me-1"></i> PURCHASE ORDERS — PENDING APPROVAL
          </h4>
          <div class="d-flex gap-1">
            <span class="badge" style="background:#fff7ed;color:#ea580c;font-size:.7rem;border:1px solid #fed7aa;" id="ap-title-badge">0 pending</span>
            <button class="btn btn-xs btn-outline-warning" onclick="apDashReload();">
              <i class="fa fa-refresh me-1"></i>Refresh
            </button>
          </div>
        </div>
        <div id="ap-pend-content" style="overflow-x:auto;">
          <div id="ap-pend-loader" class="text-center py-4 text-muted small">
            <i class="fa fa-spinner fa-spin me-1"></i>Loading pending orders…
          </div>
          <div id="ap-pend-empty" class="d-none text-center py-5 text-muted">
            <i class="fa fa-check-circle d-block fa-3x mb-3" style="color:#16a34a;opacity:.3;"></i>
            <div class="fw-bold">No POs awaiting approval</div>
            <div class="small mt-1">All purchase orders have been reviewed. Well done!</div>
          </div>
          <div id="ap-pend-wrap" class="d-none">
            <table class="table table-sm table-hover mb-0 ap-tbl">
              <thead><tr>
                <th>PO #</th><th>Supplier</th><th>Raised By</th>
                <th>Date</th><th>Items</th><th>Amount</th>
                <th>Age</th><th class="text-center">Action</th>
              </tr></thead>
              <tbody id="ap-pend-tbody"></tbody>
            </table>
          </div>
        </div>
      </div>
    </div>

    <!-- ── APPROVED PANEL ────────────────────────────────────── -->
    <div class="ap-panel d-none" id="panel-approved">
      <div class="widget">
        <div class="widget-header p-2 d-flex justify-content-between align-items-center"
             style="background:linear-gradient(90deg,#ecfdf5,#fff);border-bottom:2px solid #a7f3d0;">
          <h4 class="my-0 fw-bold" style="font-size:.82rem;color:#16a34a;">
            <i class="fa fa-check-circle me-1"></i> RECENTLY APPROVED
          </h4>
          <button class="btn btn-xs btn-outline-success" onclick="apDashReload();">
            <i class="fa fa-refresh me-1"></i>Refresh
          </button>
        </div>
        <div style="overflow-x:auto;">
          <div id="ap-appr-loader" class="text-center py-4 text-muted small">
            <i class="fa fa-spinner fa-spin me-1"></i>Loading…
          </div>
          <div id="ap-appr-empty" class="d-none text-center py-4 text-muted small">
            <i class="fa fa-inbox d-block fa-2x mb-2" style="opacity:.25;"></i>No recent approvals.
          </div>
          <div id="ap-appr-wrap" class="d-none">
            <table class="table table-sm table-hover mb-0 ap-tbl">
              <thead><tr>
                <th>PO #</th><th>Supplier</th><th>Raised By</th><th>Date</th><th>Amount</th><th>Approved By</th><th>Comment</th>
              </tr></thead>
              <tbody id="ap-appr-tbody"></tbody>
            </table>
          </div>
        </div>
      </div>
    </div>

    <!-- ── REJECTED PANEL ────────────────────────────────────── -->
    <div class="ap-panel d-none" id="panel-rejected">
      <div class="widget">
        <div class="widget-header p-2 d-flex justify-content-between align-items-center"
             style="background:linear-gradient(90deg,#fff1f2,#fff);border-bottom:2px solid #fecdd3;">
          <h4 class="my-0 fw-bold" style="font-size:.82rem;color:#dc2626;">
            <i class="fa fa-times-circle me-1"></i> REJECTED POs
          </h4>
          <button class="btn btn-xs btn-outline-danger" onclick="apDashReload();">
            <i class="fa fa-refresh me-1"></i>Refresh
          </button>
        </div>
        <div style="overflow-x:auto;">
          <div id="ap-rej-loader" class="text-center py-4 text-muted small">
            <i class="fa fa-spinner fa-spin me-1"></i>Loading…
          </div>
          <div id="ap-rej-empty" class="d-none text-center py-4 text-muted small">
            <i class="fa fa-check d-block fa-2x mb-2" style="opacity:.25;"></i>No rejected orders.
          </div>
          <div id="ap-rej-wrap" class="d-none">
            <table class="table table-sm table-hover mb-0 ap-tbl">
              <thead><tr>
                <th>PO #</th><th>Supplier</th><th>Raised By</th><th>Date</th><th>Amount</th><th>Reason</th>
              </tr></thead>
              <tbody id="ap-rej-tbody"></tbody>
            </table>
          </div>
        </div>
      </div>
    </div>

    <!-- ── ON HOLD PANEL ─────────────────────────────────────── -->
    <div class="ap-panel d-none" id="panel-held">
      <div class="widget">
        <div class="widget-header p-2 d-flex justify-content-between align-items-center"
             style="background:linear-gradient(90deg,#f5f3ff,#fff);border-bottom:2px solid #ddd6fe;">
          <h4 class="my-0 fw-bold" style="font-size:.82rem;color:#7c3aed;">
            <i class="fa fa-pause-circle me-1"></i> ON HOLD
          </h4>
          <button class="btn btn-xs btn-outline-primary" onclick="apDashReload();">
            <i class="fa fa-refresh me-1"></i>Refresh
          </button>
        </div>
        <div style="overflow-x:auto;">
          <div id="ap-held-loader" class="text-center py-4 text-muted small">
            <i class="fa fa-spinner fa-spin me-1"></i>Loading…
          </div>
          <div id="ap-held-empty" class="d-none text-center py-4 text-muted small">
            <i class="fa fa-pause d-block fa-2x mb-2" style="opacity:.25;"></i>No orders on hold.
          </div>
          <div id="ap-held-wrap" class="d-none">
            <table class="table table-sm table-hover mb-0 ap-tbl">
              <thead><tr>
                <th>PO #</th><th>Supplier</th><th>Raised By</th><th>Date</th><th>Amount</th><th class="text-center">Action</th>
              </tr></thead>
              <tbody id="ap-held-tbody"></tbody>
            </table>
          </div>
        </div>
      </div>
    </div>

  </div><!-- /#ap-main -->
</div><!-- /row -->

<script>
(function () {
'use strict';

/* ── Helpers ──────────────────────────────────────────────── */
function _e(v) { return $('<span>').text(v || '').html(); }
function _fmt(n) { return 'Ksh ' + Number(n || 0).toLocaleString('en-KE', {minimumFractionDigits:2, maximumFractionDigits:2}); }
function _fmtDate(d) {
  if (!d) return '–';
  var p = new Date(d);
  return isNaN(p) ? d : p.toLocaleDateString('en-GB', {day:'2-digit', month:'short', year:'numeric'});
}
function _ago(dt) {
  if (!dt) return '–';
  var ms = Date.now() - new Date(dt).getTime();
  if (ms < 0) ms = 0;
  var m = Math.floor(ms/60000), h = Math.floor(ms/3600000), d = Math.floor(ms/86400000);
  if (d > 0) return d + 'd ago';
  if (h > 0) return h + 'h ago';
  if (m > 0) return m + 'm ago';
  return 'just now';
}

/* ── Panel switcher (sidebar nav) ─────────────────────────── */
window.apShowPanel = function (panel) {
  $('.ap-panel').addClass('d-none');
  $('#panel-' + panel).removeClass('d-none');
  $('.ap-nav-btn').removeClass('active');
  $('#nav-' + panel).addClass('active');
};

/* ── Open review page ─────────────────────────────────────── */
window.apOpenReview = function(poId) {
  var spin = '<div class="text-center p-4"><div class="spinner-border text-primary"></div></div>';
  $('#content').html(spin);
  $.ajax({url:'kug/app/apppage.php', type:'GET', data:{id:poId}, dataType:'html'})
   .done(function(h){ $('#content').html(h); })
   .fail(function(){ $('#content').html('<div class="alert alert-danger m-3">Failed to load approval page.</div>'); });
};

/* ── Load data ────────────────────────────────────────────── */
window.apDashReload = function () {
  $.post('kug/app/api/approval_dash.php', {}, function (r) {
    if (!r.success) return;

    /* KPIs */
    $('#ap-kpi-pending').text(r.pending);
    $('#ap-kpi-approved').text(r.approved);
    $('#ap-kpi-rejected').text(r.rejected);
    $('#ap-kpi-held').text(r.on_hold);
    $('#ap-kpi-today').text(r.today_pending);

    /* Title badge */
    $('#ap-title-badge').text(r.pending + ' pending');

    /* Financial summaries */
    $('#ap-fin-pend').text(_fmt(r.pending_value));
    $('#ap-fin-month').text(_fmt(r.approved_month_value));

    /* Nav badges */
    if (r.pending > 0) $('#nav-pend-badge').text(r.pending).removeClass('d-none'); else $('#nav-pend-badge').addClass('d-none');
    if (r.on_hold > 0) $('#nav-held-badge').text(r.on_hold).removeClass('d-none'); else $('#nav-held-badge').addClass('d-none');

    /* ── Pending table ──────────────────── */
    $('#ap-pend-loader').addClass('d-none');
    var pl = r.pending_list || [];
    if (!pl.length) {
      $('#ap-pend-empty').removeClass('d-none');
      $('#ap-pend-wrap').addClass('d-none');
    } else {
      var h = '';
      pl.forEach(function(row) {
        h += '<tr>' +
          '<td><strong class="text-warning po-link" onclick="apOpenReview(' + _e(row.po_id) + ')">#' + _e(row.po_id) + '</strong></td>' +
          '<td><span class="fw-medium">' + _e(row.sup_name) + '</span></td>' +
          '<td class="text-muted">' + _e(row.raised_by) + '</td>' +
          '<td class="text-muted">' + _fmtDate(row.po_date) + '</td>' +
          '<td class="text-center">' + (row.item_count || '–') + '</td>' +
          '<td class="fw-medium">' + _fmt(row.amount) + '</td>' +
          '<td><span class="st-pending">' + _ago(row.datime) + '</span></td>' +
          '<td class="text-center">' +
            '<button class="btn btn-xs btn-warning" onclick="apOpenReview(' + _e(row.po_id) + ')" style="font-size:.68rem;">' +
            '<i class="fa fa-check me-1"></i>Review</button>' +
          '</td></tr>';
      });
      $('#ap-pend-tbody').html(h);
      $('#ap-pend-wrap').removeClass('d-none');
      $('#ap-pend-empty').addClass('d-none');
    }

    /* ── Approved table ─────────────────── */
    $('#ap-appr-loader').addClass('d-none');
    var al = r.approved_list || [];
    if (!al.length) {
      $('#ap-appr-empty').removeClass('d-none');
      $('#ap-appr-wrap').addClass('d-none');
    } else {
      var h2 = '';
      al.forEach(function(row) {
        h2 += '<tr>' +
          '<td><strong class="text-success">#' + _e(row.po_id) + '</strong></td>' +
          '<td>' + _e(row.sup_name) + '</td>' +
          '<td class="text-muted">' + _e(row.raised_by) + '</td>' +
          '<td class="text-muted">' + _fmtDate(row.po_date) + '</td>' +
          '<td class="fw-medium">' + _fmt(row.amount) + '</td>' +
          '<td>' + _e(row.approved_by || '–') + '</td>' +
          '<td class="text-muted" style="max-width:140px;overflow:hidden;text-overflow:ellipsis;white-space:nowrap;">' +
            _e(row.appr_comment || '–') + '</td>' +
          '</tr>';
      });
      $('#ap-appr-tbody').html(h2);
      $('#ap-appr-wrap').removeClass('d-none');
      $('#ap-appr-empty').addClass('d-none');
    }

    /* ── Rejected table ─────────────────── */
    $('#ap-rej-loader').addClass('d-none');
    var rl = r.rejected_list || [];
    if (!rl.length) {
      $('#ap-rej-empty').removeClass('d-none');
      $('#ap-rej-wrap').addClass('d-none');
    } else {
      var h3 = '';
      rl.forEach(function(row) {
        h3 += '<tr>' +
          '<td><strong class="text-danger">#' + _e(row.po_id) + '</strong></td>' +
          '<td>' + _e(row.sup_name) + '</td>' +
          '<td class="text-muted">' + _e(row.raised_by) + '</td>' +
          '<td class="text-muted">' + _fmtDate(row.po_date) + '</td>' +
          '<td class="fw-medium">' + _fmt(row.amount) + '</td>' +
          '<td class="text-muted" style="max-width:160px;overflow:hidden;text-overflow:ellipsis;white-space:nowrap;">' +
            _e(row.appr_comment || '–') + '</td>' +
          '</tr>';
      });
      $('#ap-rej-tbody').html(h3);
      $('#ap-rej-wrap').removeClass('d-none');
      $('#ap-rej-empty').addClass('d-none');
    }

    /* ── Held table ─────────────────────── */
    $('#ap-held-loader').addClass('d-none');
    var hl = r.held_list || [];
    if (!hl.length) {
      $('#ap-held-empty').removeClass('d-none');
      $('#ap-held-wrap').addClass('d-none');
    } else {
      var h4 = '';
      hl.forEach(function(row) {
        h4 += '<tr>' +
          '<td><strong class="text-info">#' + _e(row.po_id) + '</strong></td>' +
          '<td>' + _e(row.sup_name) + '</td>' +
          '<td class="text-muted">' + _e(row.raised_by) + '</td>' +
          '<td class="text-muted">' + _fmtDate(row.po_date) + '</td>' +
          '<td class="fw-medium">' + _fmt(row.amount) + '</td>' +
          '<td class="text-center">' +
            '<button class="btn btn-xs btn-outline-primary" onclick="apOpenReview(' + _e(row.po_id) + ')" style="font-size:.68rem;">' +
            '<i class="fa fa-eye me-1"></i>View</button>' +
          '</td></tr>';
      });
      $('#ap-held-tbody').html(h4);
      $('#ap-held-wrap').removeClass('d-none');
      $('#ap-held-empty').addClass('d-none');
    }

  }, 'json')
  .fail(function() {
    console.error('Approval dashboard API failed');
  });
};

/* ── Boot ─────────────────────────────────────────────────── */
App.setPageTitle('KSOFT | Approve LPO');
App.restartGlobalFunction();
apDashReload();

})();
</script>
