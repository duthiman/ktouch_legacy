// kug/js.js — Purchase Management Dashboard JS
'use strict';

var _trendChart = null;
var _supChart   = null;
var _activeTab  = 'appr';

function _e(s) {
  var d = document.createElement('div');
  d.textContent = String(s == null ? '' : s);
  return d.innerHTML;
}
function _fmt(n) {
  n = parseFloat(n) || 0;
  if (n >= 1000000) return 'KSh\u00a0' + (n / 1000000).toFixed(2) + 'M';
  if (n >= 1000)    return 'KSh\u00a0' + (n / 1000).toFixed(1) + 'K';
  return 'KSh\u00a0' + n.toLocaleString('en-KE', { minimumFractionDigits:2 });
}
function _fmtDate(d) { return d ? String(d).split(' ')[0] : '–'; }
function _pct(a, b)  { return b > 0 ? Math.min(100, Math.round(a / b * 100)) : 0; }
function _poType(t)  { return t == 1 ? '<span class="badge bg-info">Intl</span>' : '<span class="badge bg-secondary">Local</span>'; }

function _grnStatusBadge(s) {
  s = parseInt(s);
  if (s === 1) return '<span class="badge bg-success"  style="font-size:.6rem;">Complete</span>';
  if (s === 3) return '<span class="badge bg-warning text-dark" style="font-size:.6rem;">Ongoing</span>';
  if (s === 2) return '<span class="badge bg-danger"   style="font-size:.6rem;">Pending</span>';
  return '<span class="badge bg-secondary" style="font-size:.6rem;">Unknown</span>';
}

// ── Tab switch ────────────────────────────────────────────────────────────────
function kugTab(name, el) {
  _activeTab = name;
  document.querySelectorAll('.kug-tab-nav .nav-link').forEach(function (b) { b.classList.remove('active'); });
  if (el) el.classList.add('active');
  ['appr','rec','grn'].forEach(function (t) {
    var p = document.getElementById('tab-' + t);
    if (p) p.classList.toggle('d-none', t !== name);
  });
}

// ── Trend chart ───────────────────────────────────────────────────────────────
function _initTrendChart(labels, counts, amounts) {
  if (!document.getElementById('kug-trend-chart')) return;
  var opts = {
    chart: { type:'line', height:230, toolbar:{show:false}, background:'transparent', fontFamily:'inherit', animations:{enabled:true,speed:600} },
    series: [
      { name:'PO Count', type:'column', data: counts  },
      { name:'Amount',   type:'line',   data: amounts }
    ],
    stroke:  { width: [0, 3], curve: 'smooth' },
    fill:    { opacity: [0.85, 1] },
    colors:  ['#008080', '#7c3aed'],
    xaxis:   { categories: labels, labels:{style:{fontSize:'11px'}}, axisBorder:{show:false}, axisTicks:{show:false} },
    yaxis:   [
      { seriesName:'PO Count', min:0, labels:{style:{fontSize:'11px'}, formatter:function(v){return Math.round(v);}} },
      { seriesName:'Amount', opposite:true, labels:{style:{fontSize:'10px'}, formatter:function(v){ if(v>=1000000) return (v/1000000).toFixed(1)+'M'; if(v>=1000) return (v/1000).toFixed(0)+'K'; return v; }} }
    ],
    plotOptions: { bar:{columnWidth:'52%', borderRadius:5} },
    dataLabels: { enabled:false },
    legend: { position:'top', fontSize:'11px', markers:{width:10,height:10,radius:3} },
    grid: { borderColor:'#f0f0f6', strokeDashArray:4 },
    tooltip: { shared:true, intersect:false }
  };
  if (_trendChart) { _trendChart.updateOptions(opts); }
  else {
    _trendChart = new ApexCharts(document.getElementById('kug-trend-chart'), opts);
    _trendChart.render();
  }
}

// ── Supplier donut ─────────────────────────────────────────────────────────────
function _initSupChart(labels, values) {
  if (!labels.length || !document.getElementById('kug-sup-chart')) return;
  var opts = {
    chart: { type:'donut', height:230, background:'transparent', animations:{enabled:true,speed:600} },
    series: values, labels: labels,
    colors: ['#008080','#7c3aed','#f59c1a','#2563eb','#f43f5e'],
    legend: { position:'bottom', fontSize:'10px', offsetY:2, markers:{width:9,height:9,radius:9} },
    dataLabels: { enabled:false },
    plotOptions: { pie:{ donut:{ size:'62%', labels:{ show:true, value:{fontSize:'16px',fontWeight:700}, total:{show:true,label:'Total',fontSize:'11px',color:'#6c757d', formatter:function(w){var t=w.globals.seriesTotals.reduce(function(a,b){return a+b;},0); if(t>=1000000) return (t/1000000).toFixed(1)+'M'; if(t>=1000) return (t/1000).toFixed(0)+'K'; return t;} } } } } },
    stroke: { width:2 },
    tooltip: { y:{formatter:function(v){ return 'KSh '+parseFloat(v).toLocaleString('en-KE',{minimumFractionDigits:2}); }} }
  };
  if (_supChart) { _supChart.updateOptions(opts); }
  else {
    _supChart = new ApexCharts(document.getElementById('kug-sup-chart'), opts);
    _supChart.render();
  }
}

// ── Main data load ────────────────────────────────────────────────────────────
function kugLoad() {
  $.post('kug/api/lpo_api.php', { action: 'dashboard' }, function (r) {
    if (!r.success) { console.error('purchase_dash:', r.message); return; }

    var tot = r.total_pos || 1;

    /* KPI */
    $('#kpi-total-pos').text(r.total_pos);
    $('#kpi-pend-appr').text(r.pending_approval);
    $('#kpi-pend-rec').text(r.pending_receiving);
    $('#kpi-grn-done').text(r.completed_grns);
    $('#kpi-grn-ong').text(r.ongoing_grns);
    $('#kpi-supp').text(r.total_suppliers);

    $('#kbar-appr').css('width', _pct(r.pending_approval, tot) + '%');
    $('#kbar-rec').css('width', _pct(r.pending_receiving, tot) + '%');
    $('#kbar-grn').css('width', _pct(r.completed_grns, tot) + '%');
    $('#kbar-ong').css('width', _pct(r.ongoing_grns, tot) + '%');

    /* Sidebar badges */
    $('#sb-pend-appr').text(r.pending_approval);
    $('#sb-pend-rec').text(r.pending_receiving);
    $('#sb-ongoing').text(r.ongoing_grns);
    $('#sb-suppliers').text(r.total_suppliers);
    if (r.pending_approval > 0) { $('#sb-appr-btn').text(r.pending_approval).removeClass('d-none'); }
    if (r.pending_receiving > 0) { $('#sb-rec-btn').text(r.pending_receiving).removeClass('d-none'); }

    /* Financial strip */
    $('#fin-month').text(_fmt(r.spend_this_month));
    $('#fin-30d').text(_fmt(r.spend_30d));
    $('#fin-total').text(_fmt(r.spend_all_time));

    /* Charts */
    if (typeof ApexCharts !== 'undefined') {
      _initTrendChart(r.trend_labels || [], r.trend_count || [], r.trend_amount || []);
      if ((r.supplier_labels || []).length) _initSupChart(r.supplier_labels, r.supplier_values);
    }

    /* Tab badges */
    if (r.pending_approval > 0) $('#tab-appr-badge').text(r.pending_approval).removeClass('d-none');
    if (r.pending_receiving > 0) $('#tab-rec-badge').text(r.pending_receiving).removeClass('d-none');

    /* ── Pending Approval table ─────────────── */
    $('#kug-appr-loader').addClass('d-none').hide();
    var alist = r.approval_list || [];
    if (!alist.length) {
      $('#kug-appr-empty').removeClass('d-none');
    } else {
      var h = '';
      alist.forEach(function (row) {
        h += '<tr>' +
          '<td><strong class="text-primary">#' + _e(row.po_id) + '</strong></td>' +
          '<td><span class="fw-medium">' + _e(row.sup_name) + '</span></td>' +
          '<td class="text-muted">' + _e(row.raised_by) + '</td>' +
          '<td class="text-muted">' + _fmtDate(row.po_date) + '</td>' +
          '<td class="fw-medium">' + _fmt(row.amount) + '</td>' +
          '<td>' + _poType(row.potype) + '</td>' +
          '<td><button class="btn btn-xs btn-warning ajax-loader"' +
          ' data-folder="kug/app" data-file="apppage"' +
          ' data-target="#content" data-target-type="div"' +
          ' data-method="GET" data-id="' + _e(row.po_id) + '">' +
          '<i class="fa fa-check"></i> Review</button></td>' +
          '</tr>';
      });
      $('#kug-appr-tbody').html(h);
      $('#kug-appr-wrap').removeClass('d-none');
    }

    /* ── Pending Receiving table ──────────── */
    $('#kug-rec-loader').addClass('d-none').hide();
    var rlist = r.receiving_list || [];
    if (!rlist.length) {
      $('#kug-rec-empty').removeClass('d-none');
    } else {
      var h2 = '';
      rlist.forEach(function (row) {
        h2 += '<tr>' +
          '<td><strong class="text-success">#' + _e(row.po_id) + '</strong></td>' +
          '<td><span class="fw-medium">' + _e(row.sup_name) + '</span></td>' +
          '<td class="text-muted">' + _e(row.raised_by) + '</td>' +
          '<td class="text-muted">' + _fmtDate(row.po_date) + '</td>' +
          '<td class="fw-medium">' + _fmt(row.amount) + '</td>' +
          '<td class="text-center"><span class="badge bg-secondary">' + _e(row.item_count) + '</span></td>' +
          '<td><button class="btn btn-xs btn-primary ajax-loader"' +
          ' data-folder="kug/rec" data-file="index"' +
          ' data-target="#content" data-target-type="div"' +
          ' data-method="POST" data-id="' + _e(row.po_id) + '">' +
          '<i class="fa fa-inbox"></i> Receive</button></td>' +
          '</tr>';
      });
      $('#kug-rec-tbody').html(h2);
      $('#kug-rec-wrap').removeClass('d-none');
    }

    /* ── Recent GRNs table ────────────────── */
    $('#kug-grn-loader').addClass('d-none').hide();
    var glist = r.grn_list || [];
    if (!glist.length) {
      $('#kug-grn-empty').removeClass('d-none');
    } else {
      var h3 = '';
      glist.forEach(function (row) {
        h3 += '<tr>' +
          '<td><strong class="text-info">#' + _e(row.grn_id) + '</strong></td>' +
          '<td class="text-muted">#' + _e(row.po_id) + '</td>' +
          '<td><span class="fw-medium">' + _e(row.sup_name) + '</span></td>' +
          '<td class="text-muted">' + _e(row.received_by) + '</td>' +
          '<td class="text-muted">' + _fmtDate(row.day) + '</td>' +
          '<td class="text-center"><span class="badge bg-secondary">' + _e(row.item_count) + '</span></td>' +
          '<td>' + _grnStatusBadge(row.status) + '</td>' +
          '<td><button class="btn btn-xs btn-outline-cyan ajax-loader"' +
          ' data-folder="kug/dash" data-file="vwgrn"' +
          ' data-target="#load" data-modal="#modal"' +
          ' data-target-type="modal" data-method="GET" data-id="' + _e(row.grn_id) + '">' +
          '<i class="fa fa-eye"></i></button></td>' +
          '</tr>';
      });
      $('#kug-grn-tbody').html(h3);
      $('#kug-grn-wrap').removeClass('d-none');
    }

  }, 'json').fail(function () {
    ['kug-appr-loader','kug-rec-loader','kug-grn-loader'].forEach(function (id) {
      var el = document.getElementById(id);
      if (el) el.innerHTML = '<span class="text-danger small"><i class="fa fa-exclamation-triangle me-1"></i>Load failed.</span>';
    });
  });
}

function kugShowGrns() {
  $('#modal').modal('show');
  $('#load').html('<div class="text-center p-4"><i class="fa fa-spinner fa-spin fa-2x"></i></div>');
  $.get('kug/dash/grns.php', {}, function (d) { $('#load').html(d); });
}

window.kugTab     = kugTab;
window.kugLoad    = kugLoad;
window.kugReload  = kugLoad;
window.kugShowGrns = kugShowGrns;

function kugOpenSuppliers() {
  $('#kug-filt-rest').html('<div class="text-center p-4"><div class="spinner-border text-primary spinner-border-sm me-2"></div> Loading suppliers...</div>');
  $.get('sup/index.php', function(html) { $('#kug-filt-rest').html(html); })
   .fail(function() { $('#kug-filt-rest').html('<div class="alert alert-danger m-3">Failed to load supplier management.</div>'); });
}

function kugNewSupplier() {
  $('#modal').modal('show');
  $('#load').html('<div class="text-center p-4"><i class="fa fa-spinner fa-spin fa-2x"></i></div>');
  $.get('sup/supnew.php', function(html) { $('#load').html(html); })
   .fail(function() { $('#load').html('<div class="alert alert-danger m-3">Failed to load form.</div>'); });
}

window.kugOpenSuppliers = kugOpenSuppliers;
window.kugNewSupplier   = kugNewSupplier;

// ── AJAX-loader click handler (delegates for all .ajax-loader buttons) ────────
$(document).off('click.kugAjax').on('click.kugAjax', '.ajax-loader', function(e) {
  e.preventDefault();
  var btn        = $(this),
      folder     = btn.data('folder') || '',
      file       = btn.data('file'),
      target     = btn.data('target'),
      targetType = btn.data('target-type') || 'div',
      modal      = btn.data('modal'),
      method     = btn.data('method') || 'GET',
      id         = btn.data('id') || '',
      extraRaw   = btn.attr('data-extra');

  var url  = folder + '/' + file + '.php';
  var data = {};
  if (id !== '') data.id = id;
  if (extraRaw) { try { data = $.extend(data, JSON.parse(extraRaw)); } catch(ex) {} }

  // Loading indicator
  if (targetType === 'div') {
    $(target).html('<div class="p-3 text-center"><i class="fa fa-spinner fa-spin fa-2x text-muted"></i></div>');
  } else if (targetType === 'modal') {
    $(target).html('<div class="modal-content"><div class="modal-body text-center p-4"><i class="fa fa-spinner fa-spin fa-2x text-muted"></i></div></div>');
  }

  $.ajax({ url: url, type: method, data: data, dataType: 'html' })
    .done(function(res) {
      if (targetType === 'modal') {
        // Parent page: #load is the modal-dialog, inject content there directly
        $(target).addClass('modal-lg').html(res);
        $(modal).modal('show');
      } else {
        $(target).html(res);
      }
    })
    .fail(function() {
      var msg = '<div class="text-danger p-3"><i class="fa fa-exclamation-triangle me-1"></i>Failed to load content.</div>';
      if (targetType === 'modal') $(target).html('<div class="modal-content"><div class="modal-body">' + msg + '</div></div>');
      else $(target).html(msg);
    });
});

// Ensure supplier management buttons work after async JS load and DOM ready
$(function() {
  $(document).on('click', '.kug-suppliers-btn', function() { if (typeof kugOpenSuppliers === 'function') kugOpenSuppliers(); });
  $(document).on('click', '.kug-new-supplier-btn', function() { if (typeof kugNewSupplier === 'function') kugNewSupplier(); });

  // Report sidebar buttons
  $(document).on('click', '.kug-rpt-btn', function () {
    var rpt = $(this).data('rpt');
    var needsSup = $(this).data('needs-sup');

    if (needsSup) {
      // Supplier statement needs a supplier — show the supplier list first
      rpt = 'sup_list';
    }

    $('#kug-filt-rest').html('<div class="text-center p-4"><i class="fa fa-spinner fa-spin fa-2x text-muted"></i></div>');
    $.get('kug/dash/reports/' + rpt + '.php', function (html) {
      $('#kug-filt-rest').html(html);
    }).fail(function () {
      $('#kug-filt-rest').html('<div class="alert alert-danger m-3">Failed to load report.</div>');
    });
  });
});
