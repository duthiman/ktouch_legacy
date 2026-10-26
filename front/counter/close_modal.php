<?php
session_start();
require_once '../../inc/connection.php';
require_once '../../inc/gen_funcs.php';

$userId = (int)($_SESSION['user_id'] ?? 0);
$counterId = function_exists('user_counter') ? (int)user_counter($userId) : 0;
$counterName = $counterId > 0 && function_exists('get_counter') ? (string)get_counter($counterId) : ('Counter ' . $counterId);
$closePin = str_pad((string)random_int(0, 9999), 4, '0', STR_PAD_LEFT);
?>
<div class="counter-close-modal" data-counter-id="<?php echo $counterId; ?>" data-close-pin="<?php echo $closePin; ?>" style="background:#fff;color:#243b53;border-radius:7px;overflow:hidden;">
  <div class="modal-header" style="background:#8f1722;color:#fff;border-radius:7px 7px 0 0;">
    <button type="button" class="close" data-dismiss="modal" aria-label="Close"><span aria-hidden="true">&times;</span></button>
    <h4 class="modal-title">Close Counter</h4>
  </div>
  <div class="modal-body" style="padding:18px;">
    <p>Close <strong><?php echo htmlspecialchars($counterName, ENT_QUOTES, 'UTF-8'); ?></strong>?</p>
    <p class="text-muted">Confirm the displayed PIN to start the counter closing process.</p>
    <div style="background:#fff7ed;border:1px solid #fed7aa;border-radius:7px;padding:14px;text-align:center;margin:12px 0;">
      <small style="display:block;color:#9a3412;font-weight:700;">CLOSING PIN</small>
      <strong style="font-size:30px;letter-spacing:8px;color:#9f1239;"><?php echo $closePin; ?></strong>
      <input type="text" inputmode="numeric" maxlength="4" class="form-control counter-close-pin" placeholder="Enter PIN" style="max-width:180px;margin:10px auto 0;text-align:center;font-size:20px;letter-spacing:5px;">
    </div>
    <ol class="counter-close-steps" style="margin:14px 0;padding-left:28px;line-height:2;">
      <li data-step="1">Checking pending returns and bills</li>
      <li data-step="2">Saving counter summary</li>
      <li data-step="3">Queueing counter summary</li>
      <li data-step="4">Closing counter and cashier</li>
      <li data-step="5">Queueing shift summary if this is the last counter</li>
    </ol>
    <div class="counter-close-result" aria-live="polite"></div>
  </div>
  <div class="modal-footer">
    <button type="button" class="btn btn-default" data-dismiss="modal">Cancel</button>
    <button type="button" class="btn btn-danger counter-close-submit" disabled>Verify and Close</button>
  </div>
</div>
<script>
(function () {
  var shell = document.querySelector('.counter-close-modal');
  if (!shell || shell.dataset.ready === '1') return;
  shell.dataset.ready = '1';
  var button = shell.querySelector('.counter-close-submit');
  var result = shell.querySelector('.counter-close-result');
  var pinInput = shell.querySelector('.counter-close-pin');
  var expectedPin = shell.getAttribute('data-close-pin');
  pinInput.addEventListener('input', function () { button.disabled = pinInput.value.replace(/\D/g, '') !== expectedPin; });
  button.addEventListener('click', function () {
    var counterId = Number(shell.getAttribute('data-counter-id') || 0);
    button.disabled = true;
    button.textContent = 'Closing…';
    fetch('../counter/api/counter_api.php?action=close&counter_id=' + encodeURIComponent(counterId), { credentials: 'same-origin', headers: { Accept: 'application/json' } })
      .then(function (response) { return response.json().then(function (payload) { if (!response.ok || !payload.ok) throw new Error(payload.message || 'Could not close counter.'); return payload; }); })
      .then(function (payload) {
        result.className = 'counter-close-result alert alert-success';
        result.textContent = (payload.message || 'Counter closed successfully.') + ' CSP queue: ' + (payload.data && payload.data.counter_queue_id || 'queued') + ((payload.data && payload.data.is_last_counter) ? ' | SSP queued' : '');
        Array.prototype.forEach.call(shell.querySelectorAll('[data-step]'), function (step) { step.style.color = '#15803d'; step.style.fontWeight = '700'; step.textContent = '✓ ' + step.textContent.replace(/^✓\s*/, ''); });
        button.textContent = 'Closed';
        setTimeout(function () { window.location.reload(); }, 900);
      })
      .catch(function (error) {
        result.className = 'counter-close-result alert alert-danger';
        result.textContent = error.message || 'Could not close counter.';
        button.disabled = false;
        button.textContent = 'Close Counter';
      });
  });
}());
</script>
