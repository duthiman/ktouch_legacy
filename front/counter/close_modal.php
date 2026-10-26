<?php
session_start();
require_once '../../inc/connection.php';
require_once '../../inc/gen_funcs.php';

$userId = (int)($_SESSION['user_id'] ?? 0);
$counterId = function_exists('user_counter') ? (int)user_counter($userId) : 0;
$counterName = $counterId > 0 && function_exists('get_counter') ? (string)get_counter($counterId) : ('Counter ' . $counterId);
?>
<div class="counter-close-modal" data-counter-id="<?php echo $counterId; ?>" style="background:#fff;color:#243b53;border-radius:7px;">
  <div class="modal-header" style="background:#8f1722;color:#fff;border-radius:7px 7px 0 0;">
    <button type="button" class="close" data-dismiss="modal" aria-label="Close"><span aria-hidden="true">&times;</span></button>
    <h4 class="modal-title">Close Counter</h4>
  </div>
  <div class="modal-body" style="padding:18px;">
    <p>Close <strong><?php echo htmlspecialchars($counterName, ENT_QUOTES, 'UTF-8'); ?></strong>?</p>
    <p class="text-muted">This will run the current counter close process and queue its summary for printing.</p>
    <div class="counter-close-result" aria-live="polite"></div>
  </div>
  <div class="modal-footer">
    <button type="button" class="btn btn-default" data-dismiss="modal">Cancel</button>
    <button type="button" class="btn btn-danger counter-close-submit">Close Counter</button>
  </div>
</div>
<script>
(function () {
  var shell = document.querySelector('.counter-close-modal');
  if (!shell || shell.dataset.ready === '1') return;
  shell.dataset.ready = '1';
  var button = shell.querySelector('.counter-close-submit');
  var result = shell.querySelector('.counter-close-result');
  button.addEventListener('click', function () {
    var counterId = Number(shell.getAttribute('data-counter-id') || 0);
    button.disabled = true;
    button.textContent = 'Closing…';
    fetch('../counter/api/counter_api.php?action=close&counter_id=' + encodeURIComponent(counterId), { credentials: 'same-origin', headers: { Accept: 'application/json' } })
      .then(function (response) { return response.json().then(function (payload) { if (!response.ok || !payload.ok) throw new Error(payload.message || 'Could not close counter.'); return payload; }); })
      .then(function (payload) {
        result.className = 'counter-close-result alert alert-success';
        result.textContent = payload.message || 'Counter closed successfully.';
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
