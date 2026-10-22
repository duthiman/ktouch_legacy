<?php
session_start();

if (!isset($_SESSION['user_id']) || (int)$_SESSION['user_id'] === 0) {
    exit;
}

$selected_cntr = (int)($_POST['bill_cntr'] ?? $_GET['bill_cntr'] ?? 0);
$open_mode = trim((string)($_POST['open_mode'] ?? $_GET['open_mode'] ?? ''));
$bill_mode = trim((string)($_POST['bill_mode'] ?? $_GET['bill_mode'] ?? ''));
$open_state = trim((string)($_POST['open_state'] ?? $_GET['open_state'] ?? ''));
$final_verdict = trim((string)($_POST['final_verdict'] ?? $_GET['final_verdict'] ?? ''));
$close_parent_sale_id = (int)($_POST['close_parent_sale_id'] ?? $_GET['close_parent_sale_id'] ?? 0);
$close_current_bill = (int)($_POST['close_current_bill'] ?? $_GET['close_current_bill'] ?? 0) === 1 ? 1 : 0;
$print_final = (int)($_POST['print_final'] ?? $_GET['print_final'] ?? 0) === 1 ? 1 : 0;
?>
<style>
  .kw-bill-counter-modal .modal-content {
    border: 0;
    border-radius: 24px;
    overflow: hidden;
    background: #fff9f1;
    box-shadow: 0 28px 52px rgba(73, 67, 25, 0.24);
  }
  .kw-bill-counter-modal .modal-header {
    padding: 16px 18px;
    background: linear-gradient(135deg, #2f7a31 0%, #61b846 42%, #f36c26 100%);
    color: #fff9f1;
  }
  .kw-bill-counter-modal .modal-header h4 {
    margin: 0;
    font-size: 20px;
    font-weight: 900;
    text-transform: uppercase;
    letter-spacing: .04em;
  }
  .kw-bill-counter-modal .modal-body {
    padding: 14px;
    background: linear-gradient(180deg, #fffdf8 0%, #fff5ea 100%);
  }
  .kw-bill-counter-copy {
    margin-bottom: 12px;
    color: #fff9f1;
    font-size: 12px;
    font-weight: 700;
    opacity: .94;
  }
  .kw-bill-counter-grid {
    display: grid;
    grid-template-columns: repeat(auto-fit, minmax(150px, 1fr));
    gap: 12px;
  }
  .kw-bill-counter-btn {
    min-height: 106px;
    border-radius: 18px;
    border: 2px solid rgba(97, 184, 70, 0.18);
    background: #fff;
    color: #2f7a31;
    text-align: center;
    padding: 12px;
    box-shadow: 0 14px 24px rgba(79, 58, 24, 0.08);
    transition: all .2s ease;
  }
  .kw-bill-counter-btn.active {
    border-color: #2f7a31;
    background: linear-gradient(135deg, #eef8e9 0%, #fff7ef 100%);
    color: #235f25;
  }
  .kw-bill-counter-btn strong {
    display: block;
    font-size: 28px;
    font-weight: 900;
    line-height: 1;
  }
  .kw-bill-counter-btn span {
    display: block;
    margin-top: 10px;
    font-size: 12px;
    font-weight: 900;
    text-transform: uppercase;
    color: #7a5424;
  }
  .kw-bill-counter-actions {
    display: flex;
    justify-content: flex-end;
    gap: 10px;
    margin-top: 14px;
  }
  .kw-bill-counter-close {
    min-height: 48px;
    padding: 0 18px;
    border-radius: 14px;
    font-size: 13px;
    font-weight: 900;
    text-transform: uppercase;
    border: 0;
    background: #ead6d6;
    color: #7e2014;
  }
  .kw-bill-counter-status {
    margin-bottom: 10px;
    padding: 10px 12px;
    border-radius: 12px;
    background: #fff7dc;
    color: #8a6a3a;
    font-size: 12px;
    font-weight: 800;
  }
  @media (max-width: 575px) {
    .kw-bill-counter-grid {
      grid-template-columns: 1fr 1fr;
      gap: 10px;
    }
    .kw-bill-counter-actions {
      flex-direction: column;
    }
  }
</style>
<div class="kw-bill-counter-modal">
  <div
    class="modal-content"
    id="kw-bill-counter-root"
    data-bill-cntr="<?php echo $selected_cntr; ?>"
    data-open-mode="<?php echo htmlspecialchars($open_mode, ENT_QUOTES); ?>"
    data-bill-mode="<?php echo htmlspecialchars($bill_mode, ENT_QUOTES); ?>"
    data-open-state="<?php echo htmlspecialchars($open_state, ENT_QUOTES); ?>"
    data-final-verdict="<?php echo htmlspecialchars($final_verdict, ENT_QUOTES); ?>"
    data-close-parent-sale-id="<?php echo $close_parent_sale_id; ?>"
    data-close-current-bill="<?php echo $close_current_bill; ?>"
    data-print-final="<?php echo $print_final; ?>"
  >
    <div class="modal-header">
      <button type="button" class="close" data-dismiss="modal" style="color:#fff; opacity:1;">&times;</button>
      <h4 class="modal-title" id="kw-bill-counter-title">Loading Counter...</h4>
      <div class="kw-bill-counter-copy" id="kw-bill-counter-copy">Loading bill counter state...</div>
    </div>
    <div class="modal-body">
      <div class="kw-bill-counter-status" id="kw-bill-counter-status">Loading...</div>
      <input type="hidden" id="bill_cntr_select" value="<?php echo $selected_cntr; ?>">
      <div class="kw-bill-counter-grid" id="kw-bill-counter-grid"></div>
      <div class="kw-bill-counter-actions">
        <button type="button" class="kw-bill-counter-close" onclick="loadKwModal('submit_confirm.php', {}, 'POST')">Back</button>
      </div>
    </div>
  </div>
</div>
<script>
  (function() {
    var root = document.getElementById('kw-bill-counter-root');
    if (!root || typeof $ === 'undefined') return;

    var titleEl = document.getElementById('kw-bill-counter-title');
    var copyEl = document.getElementById('kw-bill-counter-copy');
    var statusEl = document.getElementById('kw-bill-counter-status');
    var gridEl = document.getElementById('kw-bill-counter-grid');

    function escapeHtml(value) {
      return String(value || '')
        .replace(/&/g, '&amp;')
        .replace(/</g, '&lt;')
        .replace(/>/g, '&gt;')
        .replace(/"/g, '&quot;')
        .replace(/'/g, '&#39;');
    }

    function renderState(data) {
      titleEl.textContent = data.title || 'Select Counter';
      copyEl.textContent = data.copy || '';
      statusEl.style.display = 'none';

      var counters = Array.isArray(data.counters) ? data.counters : [];
      if (!counters.length) {
        gridEl.innerHTML = '<div class="alert alert-warning" style="margin-bottom:0;">No active bill counters are available right now.</div>';
        return;
      }

      var html = '';
      for (var i = 0; i < counters.length; i++) {
        var counter = counters[i];
        html += '<button'
          + ' type="button"'
          + ' class="kw-bill-counter-btn' + (counter.active ? ' active' : '') + '"'
          + ' data-bill-counter="' + counter.id + '"'
          + ' data-action="' + escapeHtml(counter.action || '') + '"'
          + ' data-close-parent-sale-id="' + Number(counter.close_parent_sale_id || 0) + '"'
          + ' data-print-final="' + (counter.print_final ? '1' : '0') + '"'
          + ' data-open-state="' + escapeHtml(counter.open_state || '') + '"'
          + ' data-open-mode="' + escapeHtml(counter.open_mode || '') + '"'
          + ' data-bill-mode="' + escapeHtml(counter.bill_mode || '') + '"'
          + '>'
          + '<strong>' + counter.id + '</strong>'
          + '<span>' + escapeHtml(counter.label) + '</span>'
          + '</button>';
      }
      gridEl.innerHTML = html;
    }

    function setBusy(state) {
      var buttons = gridEl.querySelectorAll('.kw-bill-counter-btn');
      for (var i = 0; i < buttons.length; i++) {
        buttons[i].disabled = !!state;
      }
    }

    function showStatus(message) {
      statusEl.style.display = 'block';
      statusEl.textContent = message;
    }

    function submitCounterAction(button) {
      var actionType = button.getAttribute('data-action') || '';
      var counterId = parseInt(button.getAttribute('data-bill-counter') || '0', 10) || 0;
      var apiAction = actionType === 'submit_bill' ? 'open_order' : 'close_order';
      var payload = {
        action: apiAction,
        bill_cntr: counterId,
        counter: counterId,
        open_state: button.getAttribute('data-open-state') || '',
        open_mode: button.getAttribute('data-open-mode') || '',
        bill_mode: button.getAttribute('data-bill-mode') || '',
        final_verdict: root.getAttribute('data-final-verdict') || '',
        close_parent_sale_id: button.getAttribute('data-close-parent-sale-id') || '0',
        print_final: button.getAttribute('data-print-final') || '0',
        close_current_bill: actionType === 'close_current_bill' ? 1 : 0
      };

      setBusy(true);
      showStatus(apiAction === 'close_order' ? 'Closing bill...' : 'Keeping bill open...');

      $.ajax({
        url: 'api/open_api.php',
        type: 'POST',
        dataType: 'json',
        data: payload
      }).done(function(res) {
        if (!res || !res.ok) {
          setBusy(false);
          showStatus((res && res.message) ? res.message : 'Could not complete this bill action.');
          return;
        }

        var redirect = (res.data && res.data.redirect) ? res.data.redirect : '';
        if (apiAction === 'open_order') {
          window.location = redirect || 'index.php';
          return;
        }
        if (redirect) {
          window.location = redirect;
          return;
        }

        setBusy(false);
        showStatus(res.message || 'Bill action completed.');
      }).fail(function() {
        setBusy(false);
        showStatus('Could not complete this bill action right now.');
      });
    }

    gridEl.addEventListener('click', function(event) {
      var button = event.target.closest('.kw-bill-counter-btn');
      if (!button) return;
      event.preventDefault();
      submitCounterAction(button);
    });

    $.getJSON('api/open_api.php', {
      action: 'load_counter_print_modal',
      bill_cntr: root.getAttribute('data-bill-cntr') || '',
      open_mode: root.getAttribute('data-open-mode') || '',
      bill_mode: root.getAttribute('data-bill-mode') || '',
      open_state: root.getAttribute('data-open-state') || '',
      final_verdict: root.getAttribute('data-final-verdict') || '',
      close_parent_sale_id: root.getAttribute('data-close-parent-sale-id') || '0',
      close_current_bill: root.getAttribute('data-close-current-bill') || '0',
      print_final: root.getAttribute('data-print-final') || '0'
    }).done(function(res) {
      if (!res || !res.ok || !res.data) {
        statusEl.textContent = (res && res.message) ? res.message : 'Could not load counter print modal.';
        return;
      }
      renderState(res.data);
    }).fail(function() {
      statusEl.textContent = 'Could not load counter print modal right now.';
    });
  })();
</script>
