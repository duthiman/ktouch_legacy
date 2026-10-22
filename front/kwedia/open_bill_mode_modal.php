<style>
  .kw-open-mode-modal .modal-content {
    border-radius: 22px;
    overflow: hidden;
  }
  .kw-open-mode-modal .modal-body {
    background: #fffaf3;
    padding: 18px 16px;
  }
  .kw-open-mode-grid {
    display: grid;
    grid-template-columns: 1fr 1fr;
    gap: 12px;
    margin-top: 14px;
  }
  .kw-open-mode-btn {
    display: flex;
    align-items: flex-start;
    width: 100%;
    min-height: 120px;
    padding: 14px;
    border-radius: 14px;
    border: 2px solid #e6d0b3;
    background: #fff;
    text-align: left;
    transition: all .2s ease;
  }
  .kw-open-mode-btn:hover,
  .kw-open-mode-btn:focus {
    outline: none;
    box-shadow: 0 12px 24px rgba(73, 67, 25, 0.12);
  }
  .kw-open-mode-btn.every-btn {
    border-color: #9acb87;
    color: #2f7a31;
  }
  .kw-open-mode-btn.last-btn {
    border-color: #d6a6b4;
    color: #8a1232;
  }
  .kw-open-mode-btn strong {
    display: block;
    font-size: 15px;
    font-weight: 900;
  }
  .kw-open-mode-btn span {
    display: block;
    margin-top: 4px;
    font-size: 12px;
    color: #7a5424;
  }
  @media (max-width: 575px) {
    .kw-open-mode-grid {
      grid-template-columns: 1fr;
    }
  }
</style>
<div class="kw-open-mode-modal" id="kw-open-mode-root">
  <div class="modal-content">
    <div class="modal-header" style="background:#8a1232; color:#fff;">
      <button type="button" class="close" data-dismiss="modal" style="color:#fff; opacity:1;">&times;</button>
      <h4 class="modal-title" id="kw-open-mode-title">Choose Open Bill Style</h4>
    </div>
    <div class="modal-body" id="kw-open-mode-body" style="text-align:center; font-weight:bold;">
      Loading...
    </div>
    <div class="modal-footer">
      <button type="button" class="btn btn-danger" onclick="loadKwModal('submit_confirm.php', {}, 'POST')">Back</button>
    </div>
  </div>
</div>
<script>
  (function() {
    var apiUrl = window.KWEDIA_API_URL || 'api/bill_api.php';
    var title = document.getElementById('kw-open-mode-title');
    var body = document.getElementById('kw-open-mode-body');

    function escapeHtml(value) {
      return String(value == null ? '' : value)
        .replace(/&/g, '&amp;')
        .replace(/</g, '&lt;')
        .replace(/>/g, '&gt;')
        .replace(/"/g, '&quot;')
        .replace(/'/g, '&#39;');
    }

    function renderOption(option) {
      var styleClass = option.style === 'last' ? 'last-btn' : 'every-btn';
      return '<button type="button" class="kw-open-mode-btn ' + styleClass + '"'
        + ' data-action-type="' + escapeHtml(option.action_type || '') + '"'
        + ' data-open-mode="' + escapeHtml(option.open_mode || '') + '"'
        + ' data-bill-mode="' + escapeHtml(option.bill_mode || '') + '"'
        + ' data-open-state="' + escapeHtml(option.open_state || '') + '">'
        + '<span>'
        + '<strong>' + escapeHtml(option.title || '') + '</strong>'
        + '<span>' + escapeHtml(option.copy || '') + '</span>'
        + '</span>'
        + '</button>';
    }

    function renderError(message) {
      body.innerHTML = '<div class="alert alert-danger" style="margin-bottom:0;">' + escapeHtml(message || 'Unable to load open bill options.') + '</div>';
    }

    fetch(apiUrl + '?action=load_open_bill_mode_modal', {
      credentials: 'same-origin'
    })
      .then(function(response) { return response.json(); })
      .then(function(res) {
        var data = res && res.data ? res.data : {};
        var options = data.options || [];
        var html = '';
        var i;

        if (!res || !res.ok) {
          renderError(res && res.message ? res.message : 'Unable to load open bill options.');
          return;
        }

        title.textContent = data.title || 'Choose Open Bill Style';
        html += '<p style="font-weight:bold; margin-bottom:12px;">' + escapeHtml(data.copy || '') + '</p>';
        html += options.length === 1
          ? '<div class="kw-open-mode-grid" style="grid-template-columns:1fr;">'
          : '<div class="kw-open-mode-grid">';
        for (i = 0; i < options.length; i++) {
          html += renderOption(options[i]);
        }
        html += '</div>';
        body.innerHTML = html;

        var buttons = body.querySelectorAll('.kw-open-mode-btn');
        for (i = 0; i < buttons.length; i++) {
          buttons[i].addEventListener('click', function() {
            var actionType = this.getAttribute('data-action-type') || '';
            var openMode = this.getAttribute('data-open-mode') || '';
            var billMode = this.getAttribute('data-bill-mode') || '';
            var openState = this.getAttribute('data-open-state') || '';

            if (actionType === 'counter_modal') {
              openBillCounterModal(openState, openMode, billMode);
              return;
            }

            confirmDirectSubmitBill(openMode, billMode, openState);
          });
        }
      })
      .catch(function() {
        renderError('Unable to load open bill options.');
      });
  })();
</script>
