(function ($) {
    'use strict';

    var dashboard = { activity: null, counters: [] };

    function money(value) {
        value = Number(value || 0);
        if (Math.abs(value) >= 1000000) return 'KSh ' + (value / 1000000).toFixed(2) + 'M';
        if (Math.abs(value) >= 1000) return 'KSh ' + (value / 1000).toFixed(1) + 'K';
        return 'KSh ' + value.toFixed(0);
    }

    function setText(id, value) { var node = document.getElementById(id); if (node) node.textContent = value; }

    function drawActivity() {
        var canvas = document.getElementById('activity-chart'); if (!canvas) return;
        var ctx = canvas.getContext('2d'), w = canvas.width = canvas.offsetWidth * 2, h = canvas.height = 360;
        ctx.scale(2, 2); w /= 2; h /= 2; ctx.clearRect(0, 0, w, h);
        var pad = { l: 30, r: 10, t: 18, b: 28 }, chartW = w - pad.l - pad.r, chartH = h - pad.t - pad.b;
        var labels = dashboard.activity && dashboard.activity.labels ? dashboard.activity.labels : ['Mar 26','Apr 26','May 26','Jun 26','Jul 26','Aug 26'];
        var vals = dashboard.activity && dashboard.activity.counts ? dashboard.activity.counts : [8,10,12,14,13,9];
        var max = Math.max.apply(null, vals.concat([1]));
        ctx.strokeStyle = '#e4eaf2'; ctx.lineWidth = 1;
        for (var y = 0; y < 5; y++) { var yy = pad.t + chartH * y / 4; ctx.beginPath(); ctx.moveTo(pad.l, yy); ctx.lineTo(w - pad.r, yy); ctx.stroke(); }
        vals.forEach(function (v, i) { var x = pad.l + i * chartW / Math.max(vals.length - 1, 1), bh = (Number(v) / max) * chartH; ctx.fillStyle = '#16a394'; ctx.fillRect(x - 10, pad.t + chartH - bh, 20, bh); });
        ctx.fillStyle = '#53647d'; ctx.font = '11px Arial';
        labels.forEach(function (label, i) { var x = pad.l + i * chartW / Math.max(labels.length - 1, 1); ctx.fillText(label, x - 16, h - 8); });
    }

    function drawCounter() {
        var canvas = document.getElementById('counter-chart'); if (!canvas) return;
        var ctx = canvas.getContext('2d'), w = canvas.width = canvas.offsetWidth * 2, h = canvas.height = 360;
        ctx.scale(2, 2); w /= 2; h /= 2; ctx.clearRect(0, 0, w, h);
        var rows = dashboard.counters.length ? dashboard.counters : [{counter_name:'Main Store',retail:720000,cost:610000},{counter_name:'Front Counter',retail:410000,cost:360000}];
        var max = 1; rows.forEach(function (row) { max = Math.max(max, Number(row.retail || 0), Number(row.cost || 0)); });
        var x = 95, scale = (w - 130) / max;
        ctx.font = '11px Arial';
        rows.slice(0, 5).forEach(function (row, i) { var y = 30 + i * 38, retail = Number(row.retail || 0), cost = Number(row.cost || 0); ctx.fillStyle = '#53647d'; ctx.fillText(row.counter_name || 'Counter', 0, y + 5); ctx.fillStyle = '#159b8d'; ctx.fillRect(x, y - 9, retail * scale, 10); ctx.fillStyle = '#176be4'; ctx.fillRect(x, y + 3, cost * scale, 10); ctx.fillStyle = '#53647d'; ctx.fillText(money(retail), w - 65, y + 5); });
    }

    function statusLabel(status) {
        status = String(status || 'pending').toLowerCase();
        var cls = status.indexOf('complete') >= 0 || status === 'approved' ? 'success' : (status.indexOf('reject') >= 0 ? 'danger' : 'warning');
        return '<span class="label label-' + cls + '">' + status + '</span>';
    }

    function renderRecent(rows) {
        var body = $('#thit-recent-grns'); if (!body.length) return;
        body.empty(); $('#thit-recent-count').text(rows.length);
        if (!rows.length) { body.append('<tr><td colspan="5" class="text-center">No GRNs found</td></tr>'); return; }
        rows.forEach(function (row) {
            var code = row.grn_id || row.po_id || '—';
            var by = row.user_id || '—';
            var date = row.day || '—';
            var items = row.amount || 0;
            body.append('<tr><td>' + code + '</td><td>' + by + '</td><td>' + date + '</td><td>' + items + '</td><td>' + statusLabel(row.status) + '</td></tr>');
        });
    }

    function escapeHtml(value) {
        return $('<div>').text(value == null ? '' : value).html();
    }

    function loadCounterRequests() {
        var $main = $('#maincont');
        if (!$main.length) return;
        $main.html('<div class="text-center" style="padding:60px"><i class="fa fa-spinner fa-spin fa-2x"></i><p>Loading counter requests...</p></div>');
        $main.load('req/cntrReq.php', function (response, status) {
            if (status !== 'success') { $main.html('<div class="alert alert-danger">Could not load the counter request page.</div>'); return; }
            fetchCounterRequests();
        });
    }

    function fetchCounterRequests() {
        var $body = $('#thit-counter-requests-body');
        if (!$body.length) return;
        $.getJSON('req/api.php', { action: 'list' }).done(function (response) {
            var rows = response && response.ok && response.data ? (response.data.requests || []) : [];
            $body.empty();
            if (!rows.length) {
                $body.append('<tr><td colspan="8" class="text-center">There are no pending counter requests.</td></tr>');
            } else {
                rows.forEach(function (row, index) {
                    $body.append('<tr><td>' + (index + 1) + '</td><td>' + escapeHtml(row.req_id) + '</td><td>' + escapeHtml(row.requester_name || row.req_user) + '</td><td>' + escapeHtml(row.counter_name || row.counter) + '</td><td>' + escapeHtml(row.req_shift) + '</td><td>' + escapeHtml(row.item_count) + '</td><td><span class="thit-status-outline">Pending issue</span></td><td><button type="button" class="btn btn-outline-success btn-sm thit-issue-counter" data-req-id="' + escapeHtml(row.req_id) + '" data-counter-id="' + escapeHtml(row.counter) + '"><i class="fa fa-exchange"></i> Issue Counter</button></td></tr>');
                });
            }
            $('#thit-counter-requests-status').removeClass('alert-info').addClass('alert-success').html('<i class="fa fa-check"></i> Pending counter requests loaded.');
        }).fail(function () { $('#thit-counter-requests-status').removeClass('alert-info').addClass('alert-danger').html('<i class="fa fa-warning"></i> Could not load pending counter requests.'); });
    }

    function loadStockManagement() {
        var $main = $('#maincont'); if (!$main.length) return;
        $main.html('<div class="text-center" style="padding:60px"><i class="fa fa-spinner fa-spin fa-2x"></i><p>Loading stock management...</p></div>');
        $main.load('stokman/index.php', function (response, status) { if (status === 'success') fetchStockItems(); else $main.html('<div class="alert alert-danger">Could not load stock management.</div>'); });
    }

    function fetchStockItems() {
        $.getJSON('stockman/api.php', { action: 'items' }).done(function (response) {
            var $body = $('#thit-stockman-body'); if (!$body.length) return; $body.empty();
            var items = response && response.ok && response.data ? response.data.items : [];
            var okCount = 0, lowCount = 0, outCount = 0;
            items.forEach(function (item, index) {
                var qty = Number(item.store_qty || 0), reorder = Number(item.store_reorder || 0), status = qty <= 0 ? '<span class="label label-danger">Out of stock</span>' : (reorder > 0 && qty <= reorder ? '<span class="label label-warning">Low stock</span>' : '<span class="label label-success">Healthy</span>');
                if (qty <= 0) outCount++; else if (reorder > 0 && qty <= reorder) lowCount++; else okCount++;
                $body.append('<tr><td>' + (index + 1) + '</td><td>' + escapeHtml(item.name) + '</td><td>' + qty + '</td><td>' + reorder + '</td><td>' + status + '</td><td><button class="btn btn-xs btn-outline-success thit-stock-action" data-action="adjust_stock" data-id="' + item.item_id + '" data-name="' + escapeHtml(item.name) + '" data-value="' + qty + '">Adjust Stock</button> <button class="btn btn-xs btn-outline-primary thit-stock-action" data-action="adjust_reorder" data-id="' + item.item_id + '" data-name="' + escapeHtml(item.name) + '" data-value="' + reorder + '">Adjust Reorder</button></td></tr>');
            });
            $('#thit-stock-ok-count').text(okCount); $('#thit-stock-low-count').text(lowCount); $('#thit-stock-out-count').text(outCount);
            if (!items.length) $body.append('<tr><td colspan="6" class="text-center">No stock items found.</td></tr>');
            $('#thit-stockman-status').removeClass('alert-info').addClass('alert-success').html('<i class="fa fa-check"></i> Stock items loaded.');
        }).fail(function () { $('#thit-stockman-status').removeClass('alert-info').addClass('alert-danger').html('<i class="fa fa-warning"></i> Could not load stock items.'); });
    }

    function loadDashboard() {
        $.getJSON('api/thit_api.php', { action: 'dashboard' }).done(function (response) {
            if (!response || !response.ok || !response.data) return;
            var data = response.data, k = data.kpi || {}, v = data.values || {};
            setText('thit-pending-grn', k.pending_grn || 0); setText('thit-rem-pending-grn', k.pending_grn || 0);
            setText('thit-pending-issues', k.pending_issues || 0); setText('thit-rem-pending-issues', k.pending_issues || 0);
            setText('thit-out-stock', k.out_of_stock || 0); setText('thit-rem-out-stock', k.out_of_stock || 0);
            setText('thit-low-stock', k.low_stock || 0); setText('thit-rem-low-stock', k.low_stock || 0);
            setText('thit-rem-counter-requests', k.counter_requests || 0);
            setText('thit-approvals', k.approvals || 0); setText('thit-pending-dispatch', k.pending_dispatch || 0);
            setText('thit-retail-value', money(v.retail)); setText('thit-capital-value', money(v.cost)); setText('thit-margin-value', money(v.margin)); setText('thit-margin-pct', Number(v.margin_pct || 0).toFixed(1) + '%');
            dashboard.activity = data.activity || null; dashboard.counters = data.counters || [];
            renderRecent(data.recent_grns || []); drawActivity(); drawCounter();
        }).fail(function (xhr) { if (window.console) console.warn('THIT dashboard API unavailable', xhr.status); });
    }

    $(function () {
        drawActivity(); drawCounter(); loadDashboard();
        $('#thit-refresh').on('click', function () { loadDashboard(); });
        $(document).on('click', '.thit-issue-stock', function (event) { event.preventDefault(); loadCounterRequests(); });
        $(document).on('click', '#thit-counter-requests-refresh', function () { fetchCounterRequests(); });
        $(document).on('click', '.thit-stock-management', function (event) { event.preventDefault(); loadStockManagement(); });
        $(document).on('click', '#thit-stockman-refresh', function () { fetchStockItems(); });
        $(document).on('input', '#thit-stockman-search', function () { var query = String($(this).val()).toLowerCase(); $('#thit-stockman-body tr').each(function () { $(this).toggle($(this).text().toLowerCase().indexOf(query) >= 0); }); });
        $(document).on('click', '.thit-stock-action', function () { var button = $(this); $('#thit-stockman-modal-title').text(button.data('action') === 'adjust_reorder' ? 'Adjust Reorder Level' : 'Adjust Stock'); $('#thit-stockman-modal-item').text(button.data('name')); $('#thit-stockman-modal-value').val(button.data('value')); $('#thit-stockman-save').data({ action: button.data('action'), id: button.data('id') }); $('#thit-stockman-modal').modal('show'); });
        $(document).on('click', '#thit-stockman-save', function () { var button = $(this), value = Number($('#thit-stockman-modal-value').val()); button.prop('disabled', true); $.post('stockman/api.php', { action: button.data('action'), item_id: button.data('id'), value: value }).done(function (response) { if (!response.ok) { alert(response.message || 'Could not update stock.'); return; } $('#thit-stockman-modal').modal('hide'); fetchStockItems(); }).fail(function () { alert('Could not update stock.'); }).always(function () { button.prop('disabled', false); }); });
        $(document).on('click', '.thit-issue-counter', function () {
            var requestId = $(this).data('req-id');
            var counter = $(this).data('counter-id') || '';
            $('#thit-issue-notice').data('request-id', requestId).data('counter', counter);
            $('#thit-issue-notice-details').text('Request #' + requestId + (counter ? ' • ' + counter : ''));
            $('#thit-issue-notice').modal('show');
        });
        $(document).on('click', '#thit-confirm-issue', function () {
            var modal = $('#thit-issue-notice');
            var requestId = modal.data('request-id');
            var counter = modal.data('counter');
            if (!requestId) return;
            window.location.href = 'req/reqView.php?req_id=' + encodeURIComponent(requestId) + '&cntr=' + encodeURIComponent(counter || '');
        });
        $(window).on('resize', function () { drawActivity(); drawCounter(); });
    });
}(jQuery));
