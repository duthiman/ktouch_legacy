<style>
  .kw-submit-modal .modal-content {
    border-radius: 22px;
    overflow: hidden;
    display: flex;
    flex-direction: column;
    max-height: 86vh;
  }
  .kw-submit-modal .modal-header {
    display: flex;
    align-items: center;
    justify-content: space-between;
    gap: 12px;
    background: #8a1232;
    color: #fff;
    padding: 14px 16px;
  }
  .kw-submit-modal .modal-header .modal-title {
    margin: 0;
    font-weight: 900;
  }
  .kw-submit-modal .modal-close-btn {
    width: 34px;
    height: 34px;
    border: 2px solid rgba(255,255,255,0.78);
    border-radius: 50%;
    background: transparent;
    color: #fff;
    font-size: 20px;
    font-weight: 900;
    line-height: 1;
    display: inline-flex;
    align-items: center;
    justify-content: center;
    padding: 0;
    margin-left: auto;
    opacity: 1;
  }
  .kw-submit-modal .modal-body {
    background: #fffaf3;
    overflow: hidden;
    display: flex;
    flex-direction: column;
  }
  .kw-submit-table-wrap {
    overflow-y: auto;
    max-height: 38vh;
    border: 1px solid #ead8be;
    border-radius: 12px;
    background: #fff;
  }
  .kw-submit-table-wrap table {
    margin-bottom: 0;
  }
  .kw-submit-table-wrap thead th {
    position: sticky;
    top: 0;
    z-index: 1;
    background: #f8efe4;
  }
  .kw-print-step-note {
    margin-top: 14px;
    padding: 12px 14px;
    border-radius: 14px;
    background: #fff;
    border: 1px solid #ead8be;
    color: #7a5424;
    font-size: 13px;
    font-weight: 700;
  }
  .kw-flow-card {
    margin-top: 14px;
    padding: 12px 14px;
    border-radius: 12px;
    background: #fff3e4;
    border: 1px solid #f3c69e;
  }
  .kw-modal-footer-grid {
    display: grid;
    grid-template-columns: repeat(3, minmax(0, 1fr));
    gap: 10px;
    width: 100%;
  }
  .kw-footer-btn {
    min-height: 42px;
    padding: 0 10px;
    border-radius: 12px;
    border: 0;
    font-size: 12px;
    font-weight: 900;
    text-transform: uppercase;
    letter-spacing: .04em;
  }
  .kw-footer-btn.back-btn {
    background: #c93636;
    color: #fff;
  }
  .kw-footer-btn.closed-btn {
    background: #2f7a31;
    color: #fff;
  }
  .kw-footer-btn.open-btn {
    background: #f36c26;
    color: #fff;
  }
</style>
<div class="kw-submit-modal" id="kw-submit-confirm-root">
  <div class="modal-content">
    <div class="modal-header">
      <h4 class="modal-title">Confirm Order</h4>
      <button type="button" class="modal-close-btn" data-dismiss="modal" aria-label="Close">&times;</button>
    </div>
    <div class="modal-body" id="kw-submit-confirm-body" style="padding:20px; text-align:center; font-weight:bold;">
      Loading...
    </div>
    <div class="modal-footer" id="kw-submit-confirm-footer" style="display:none;"></div>
  </div>
</div>
<script>
  (function() {
    var apiUrl = window.KWEDIA_API_URL || '../API/kwedia/bill_api.php';
    var body = document.getElementById('kw-submit-confirm-body');
    var footer = document.getElementById('kw-submit-confirm-footer');

    function escapeHtml(value) {
      return String(value == null ? '' : value)
        .replace(/&/g, '&amp;')
        .replace(/</g, '&lt;')
        .replace(/>/g, '&gt;')
        .replace(/"/g, '&quot;')
        .replace(/'/g, '&#39;');
    }

    function formatMoney(value) {
      var amount = Number(value || 0);
      return amount.toFixed(2);
    }

    function formatQty(value) {
      var qty = Number(value || 0);
      if (Math.floor(qty) === qty) {
        return String(qty);
      }
      return qty.toFixed(2).replace(/\.?0+$/, '');
    }

    function renderItems(items, total) {
      var rows = '';
      for (var i = 0; i < items.length; i++) {
        rows += '<tr>'
          + '<td>' + escapeHtml(items[i].name) + '</td>'
          + '<td>' + escapeHtml(formatQty(items[i].qty)) + '</td>'
          + '<td>' + escapeHtml(formatMoney(items[i].total)) + '</td>'
          + '</tr>';
      }

      if (!rows) {
        rows = '<tr><td colspan="3" class="text-center">No items found.</td></tr>';
      }

      return ''
        + '<p style="font-weight:bold; margin-bottom:12px;">Please confirm these bill items before sending.</p>'
        + '<div class="kw-submit-table-wrap">'
        + '  <table class="table table-bordered table-striped">'
        + '    <thead><tr><th>Item</th><th>Qty</th><th>Total</th></tr></thead>'
        + '    <tbody>' + rows + '</tbody>'
        + '    <tfoot><tr><th colspan="2" class="text-center">Total</th><th>' + escapeHtml(formatMoney(total)) + '</th></tr></tfoot>'
        + '  </table>'
        + '</div>';
    }

    function renderNotes(data) {
      var html = '';

      if (data.has_parent) {
        html += '<div class="kw-print-step-note">The saved bill type controls the customer receipt. BOT, KOT, and cashier tickets will still print for the order items you are sending now.</div>';
        html += '<div class="kw-flow-card">';
        html += '<div style="font-size:13px; font-weight:900; color:#8a4a12; text-transform:uppercase;">'
          + escapeHtml(data.parent_bill_mode === 'last' ? 'Open Bill #' + data.parent_bill : 'Continuation Bill #' + data.parent_bill)
          + '</div>';
        if (data.parent_bill_mode === 'last') {
          html += '<div class="kw-print-step-note" style="margin-top:10px;">This is an <strong>Open Bill</strong>. <strong>Still Open</strong> prints only BOT, KOT, and cashier tickets for the newly added items. <strong>Close Order</strong> prints the final customer bill too.</div>';
        } else {
          html += '<div class="kw-print-step-note" style="margin-top:10px;">This is a <strong>Continuation Bill</strong>. <strong>Still Open</strong> prints this continuation bill and keeps the parent active. <strong>Close Order</strong> prints the last continuation bill and closes the chain.</div>';
        }
        html += '</div>';
      } else {
        html += '<div class="kw-print-step-note">Choose whether this should finish as a closed bill or stay open. BOT, KOT, and cashier tickets will still print for the order.</div>';
      }

      if (data.is_empty_session) {
        if (data.parent_bill_mode === 'last') {
          html += '<div class="kw-print-step-note" style="margin-top:12px; border-color:#d6a6b4; background:#fff4f7;">No new items were added in this session. Closing now will print the full final bill and move it from <strong>Open Bills</strong> to <strong>Closed Bills</strong>.</div>';
        } else {
          html += '<div class="kw-print-step-note" style="margin-top:12px; border-color:#d6a6b4; background:#fff4f7;">No new items were added in this session. Closing now will only move this bill from <strong>Open Bills</strong> to <strong>Closed Bills</strong>. No bill, BOT, KOT, or cashier order will print.</div>';
        }
      }

      return html;
    }

    function renderFooter(data) {
      if (data.has_parent) {
        if (data.is_empty_session) {
          return '<div class="kw-modal-footer-grid" style="grid-template-columns:repeat(3, minmax(0, 1fr));">'
            + '<button type="button" class="kw-footer-btn back-btn" data-dismiss="modal">Back</button>'
            + '<button type="button" class="kw-footer-btn closed-btn" onclick="openCloseParentCounterModal(' + Number(data.parent_bill) + ', ' + (data.parent_bill_mode === 'last' ? 'true' : 'false') + ', \'open\', \'' + escapeHtml(data.parent_bill_mode) + '\')">Close Order</button>'
            + '<button type="button" class="kw-footer-btn open-btn" data-dismiss="modal">Still Open</button>'
            + '</div>';
        }

        if (data.parent_bill_mode === 'last') {
          return '<div class="kw-modal-footer-grid" style="grid-template-columns:repeat(3, minmax(0, 1fr));">'
            + '<button type="button" class="kw-footer-btn back-btn" data-dismiss="modal">Back</button>'
            + '<button type="button" class="kw-footer-btn closed-btn" onclick="openCloseParentCounterModal(' + Number(data.parent_bill) + ', true, \'open\', \'last\')">Close Order</button>'
            + '<button type="button" class="kw-footer-btn open-btn" onclick="confirmDirectSubmitBill(\'open\', \'last\', \'continue\')">Still Open</button>'
            + '</div>';
        }

        return '<div class="kw-modal-footer-grid" style="grid-template-columns:repeat(3, minmax(0, 1fr));">'
          + '<button type="button" class="kw-footer-btn back-btn" data-dismiss="modal">Back</button>'
          + '<button type="button" class="kw-footer-btn closed-btn" onclick="openCloseParentCounterModal(' + Number(data.parent_bill) + ', false, \'open\', \'each\')">Close Order</button>'
          + '<button type="button" class="kw-footer-btn open-btn" onclick="openBillCounterModal(\'continue\', \'open\', \'each\')">Still Open</button>'
          + '</div>';
      }

      return '<div class="kw-modal-footer-grid">'
        + '<button type="button" class="kw-footer-btn back-btn" data-dismiss="modal">Back</button>'
        + '<button type="button" class="kw-footer-btn closed-btn" onclick="openCloseCurrentCounterModal()">Closed Bill</button>'
        + '<button type="button" class="kw-footer-btn open-btn" onclick="openOpenBillModeModal()">Open Bill</button>'
        + '</div>';
    }

    function renderError(message) {
      body.innerHTML = '<div class="alert alert-danger" style="margin-bottom:0;">' + escapeHtml(message || 'Unable to load order confirmation.') + '</div>';
      footer.style.display = 'none';
      footer.innerHTML = '';
    }

    fetch(apiUrl + '?action=load_submit_confirm_modal', {
      credentials: 'same-origin'
    })
      .then(function(response) { return response.json(); })
      .then(function(res) {
        var data = res && res.data ? res.data : {};
        if (!res || !res.ok) {
          renderError(res && res.message ? res.message : 'Unable to load order confirmation.');
          return;
        }

        body.innerHTML = renderItems(data.items || [], data.total || 0) + renderNotes(data);
        footer.innerHTML = renderFooter(data);
        footer.style.display = '';
      })
      .catch(function() {
        renderError('Unable to load order confirmation.');
      });
  })();
</script>
