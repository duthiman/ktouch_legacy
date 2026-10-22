(function () {
  'use strict';
  if (typeof App !== 'undefined' && typeof App.init === 'function') {
    App.init();
  }
  function notify(text) {
    var old = document.querySelector('.toast');
    if (old) old.remove();
    var toast = document.createElement('div');
    toast.className = 'toast';
    toast.textContent = '✓ ' + text + ' selected';
    document.body.appendChild(toast);
    window.setTimeout(function () { toast.remove(); }, 1800);
  }
  function compactMoney(value) {
    value = Number(value || 0);
    if (Math.abs(value) >= 1000000) return 'KSh ' + (value / 1000000).toFixed(2) + 'M';
    if (Math.abs(value) >= 1000) return 'KSh ' + (value / 1000).toFixed(1) + 'K';
    return 'KSh ' + value.toLocaleString('en-KE', { minimumFractionDigits: 2, maximumFractionDigits: 2 });
  }
  function setText(selector, value) {
    var element = document.querySelector(selector);
    if (element) element.textContent = value;
  }
  function escapeHtml(value) {
    return String(value == null ? '' : value)
      .replace(/&/g, '&amp;')
      .replace(/</g, '&lt;')
      .replace(/>/g, '&gt;')
      .replace(/"/g, '&quot;')
      .replace(/'/g, '&#039;');
  }
  function renderPurchaseTrend(rows) {
    var bars = document.querySelectorAll('.trend .bar');
    var line = document.querySelector('.trend .line');
    var points = document.querySelectorAll('.trend circle');
    var labels = document.querySelectorAll('.trend svg text');
    if (!bars.length || !rows || !rows.length) return;

    var maxTotal = Math.max.apply(null, rows.map(function (row) { return Number(row.total || 0); })) || 1;
    var maxCount = Math.max.apply(null, rows.map(function (row) { return Number(row.count || 0); })) || 1;
    var linePoints = [];
    rows.forEach(function (row, index) {
      var total = Number(row.total || 0);
      var count = Number(row.count || 0);
      var x = 99 + (index * 127);
      var barX = 70 + (index * 127);
      var height = Math.max(2, (total / maxTotal) * 160);
      var y = 190 - height;
      if (bars[index]) {
        bars[index].setAttribute('x', barX);
        bars[index].setAttribute('y', y);
        bars[index].setAttribute('height', height);
      }
      var lineY = 190 - ((count / maxCount) * 160);
      linePoints.push(x + ',' + lineY);
      if (points[index]) {
        points[index].setAttribute('cx', x);
        points[index].setAttribute('cy', lineY);
      }
      if (labels[index]) labels[index].textContent = row.label || row.period;
    });
    if (line) line.setAttribute('points', linePoints.join(' '));
  }
  function renderLatestPurchases(rows) {
    var body = document.getElementById('latest-purchases-body');
    if (!body) return;
    if (!rows || !rows.length) {
      body.innerHTML = '<tr><td colspan="5" class="text-center">No purchases found.</td></tr>';
      return;
    }
    body.innerHTML = rows.map(function (purchase) {
      var statusClass = purchase.status === 'Received' ? 'ok' : (purchase.status === 'Pending' ? 'prepare' : 'customs');
      return '<tr>' +
        '<td class="code">PO-' + purchase.po_id + '</td>' +
        '<td>' + escapeHtml(purchase.supplier) + '</td>' +
        '<td>' + escapeHtml(purchase.date || '—') + '</td>' +
        '<td>' + compactMoney(purchase.amount) + '</td>' +
        '<td><span class="status ' + statusClass + '">' + escapeHtml(purchase.status) + '</span></td>' +
        '</tr>';
    }).join('');
  }
  function loadDashboardData() {
    fetch('kug_api.php', { headers: { Accept: 'application/json' } })
      .then(function (response) {
        if (!response.ok) throw new Error('Dashboard request failed');
        return response.json();
      })
      .then(function (response) {
        if (!response.ok) throw new Error(response.message || 'Dashboard request failed');
        var m = response.metrics || {};
        renderPurchaseTrend(response.trend || []);
        renderLatestPurchases(response.latest_purchases || []);
        var kpis = document.querySelectorAll('.kpis .kpi b');
        [m.total_orders, m.pending_approval, m.ordering, m.packaging, m.shipping, m.pending_receiving].forEach(function (value, index) {
          if (kpis[index]) kpis[index].textContent = Number(value || 0).toLocaleString('en-KE');
        });

        var values = document.querySelectorAll('.values .value b');
        [m.month_spend, m.international_value, m.shipping_cost, m.all_time_spend].forEach(function (value, index) {
          if (values[index]) values[index].textContent = compactMoney(value);
        });

        var stages = document.querySelectorAll('.pipeline .stage');
        var stageValues = [m.ordering, m.packaging, m.shipping, m.pending_receiving];
        var stageAmounts = [m.month_spend, 0, 0, m.receiving_value];
        var totalStages = stageValues.reduce(function (sum, value) { return sum + Number(value || 0); }, 0) || 1;
        stageValues.forEach(function (value, index) {
          if (!stages[index]) return;
          var count = Number(value || 0);
          var percent = Math.round(count * 100 / totalStages);
          var countText = stages[index].querySelector('b');
          var track = stages[index].querySelector('.track i');
          var percentText = stages[index].querySelector('em');
          var amountText = stages[index].querySelector('strong');
          if (countText) countText.textContent = count + (count === 1 ? ' Order' : ' Orders');
          if (track) track.style.width = percent + '%';
          if (percentText) percentText.textContent = percent + '%';
          if (amountText) amountText.textContent = compactMoney(stageAmounts[index]);
        });
        setText('.pipeline-total span:first-child', 'Active Pipeline: ' + (totalStages === 1 ? 0 : totalStages) + ' Orders');
        setText('.pipeline-total span:last-child', 'Total Value:  ' + compactMoney(Number(m.month_spend || 0) + Number(m.receiving_value || 0)));
      })
      .catch(function () {
        notify('Dashboard data unavailable');
      });
  }
  document.addEventListener('DOMContentLoaded', function () {
    loadDashboardData();
    if (window.jQuery && $('#generate-lpo').length) {
      $('#generate-lpo').on('click', function (event) {
        event.preventDefault();
        $('#load').html('<div class="modal-content"><div class="modal-body text-center"><i class="fa fa-spinner fa-spin"></i> Loading LPO...</div></div>');
        $('#modal').modal('show');
        $.ajax({ url: 'init/initset.php', type: 'GET', dataType: 'html' })
          .done(function (html) { $('#load').html(html); })
          .fail(function () { $('#load').html('<div class="modal-content"><div class="modal-body"><div class="alert alert-danger">Could not load Generate LPO.</div></div></div>'); });
      });
    }
    document.querySelectorAll('.kug-page button').forEach(function (button) {
      if (!button.hasAttribute('onclick') && !button.classList.contains('help') && !button.id.includes('generate-lpo')) {
        button.addEventListener('click', function () {
          notify(button.textContent.trim().replace(/\s+/g, ' '));
        });
      }
    });
    var help = document.querySelector('.kug-page .help');
    if (help) help.addEventListener('click', function () { notify('Help'); });
  });
}());
