<?php
session_start();
require_once '../../inc/connection.php';
require_once '../../inc/gen_funcs.php';

$currentUser = (int)($_SESSION['user_id'] ?? 0);
$counters = [];
$counterQuery = mysqli_query($connection, "SELECT cntr_id,name FROM kp_counters WHERE del_status=2 AND counter_status=2 ORDER BY cntr_id");
while ($counterQuery && ($row = mysqli_fetch_assoc($counterQuery))) $counters[] = $row;
$users = [];
$userQuery = mysqli_query($connection, "SELECT user_id,username FROM kp_users WHERE user_id=" . $currentUser . " OR (counter=0 AND job IN (1,2)) ORDER BY username");
while ($userQuery && ($row = mysqli_fetch_assoc($userQuery))) $users[(int)$row['user_id']] = $row;
?>
<style>
.counter-open-modal{width:100%;max-width:1040px;margin:0 auto;background:linear-gradient(180deg,#fff9ea,#f7edd2);color:#243b53;border-radius:7px;overflow:hidden;box-shadow:0 20px 40px rgba(33,19,6,.24)}
.counter-open-modal .modal-header{padding:14px 16px!important;background:linear-gradient(135deg,#7e2014,#b94828 52%,#e7a33a)!important;color:#fff8ea!important;border:0}
.counter-open-modal .modal-title{text-transform:uppercase;font-weight:900;letter-spacing:.04em}
.counter-open-modal .modal-body{background:linear-gradient(180deg,#fff9ea,#f7edd2);padding:14px!important}
.counter-open-modal .form-group{background:rgba(255,255,255,.92);border:1px solid rgba(146,110,52,.18);border-radius:7px;padding:12px;box-shadow:0 10px 20px rgba(86,61,24,.08)}
.counter-open-modal label{display:block;color:#7e3a20;font-size:12px;font-weight:900;text-transform:uppercase;letter-spacing:.08em;margin-bottom:7px}
.counter-open-modal select{border-radius:7px;height:42px;font-weight:700}
.counter-open-steps{background:#fff;border:1px solid #eadcc0;border-radius:7px!important;color:#7e3a20;font-size:12px;font-weight:800;line-height:2;padding:12px!important}
.counter-open-modal .modal-footer{background:#fffdf8;border:0;padding:12px 14px 14px}
.counter-open-submit{border:0;border-radius:7px;font-weight:900;text-transform:uppercase;min-height:44px;padding:0 20px}
.counter-open-result{border-radius:7px;margin-top:12px}
@media(max-width:520px){.counter-open-modal .modal-body{padding:10px!important}.counter-open-modal .modal-footer{display:flex;gap:8px}.counter-open-modal .modal-footer button{flex:1}.counter-open-submit{padding:0 10px}}
</style>
<div class="counter-open-modal" style="background:#fff;color:#243b53;border-radius:7px;overflow:hidden;">
  <div class="modal-header" style="background:#166534;color:#fff;border-radius:7px 7px 0 0;">
    <button type="button" class="close" data-dismiss="modal" aria-label="Close"><span aria-hidden="true">&times;</span></button>
    <h4 class="modal-title">Open Counter</h4>
  </div>
  <div class="modal-body" style="padding:18px;">
    <div class="counter-open-steps" style="background:#f0fdf4;border:1px solid #bbf7d0;border-radius:7px;padding:12px;margin-bottom:14px;">
      <div data-open-step="1">1. Checking the active shift</div>
      <div data-open-step="2">2. Assigning the cashier</div>
      <div data-open-step="3">3. Opening the counter</div>
      <div data-open-step="4">4. Updating stock-take status</div>
    </div>
    <div class="form-group"><label for="counter-open-id">Counter</label><select id="counter-open-id" class="form-control">
      <?php foreach ($counters as $counter): ?><option value="<?php echo (int)$counter['cntr_id']; ?>"><?php echo htmlspecialchars((string)$counter['name'], ENT_QUOTES, 'UTF-8'); ?></option><?php endforeach; ?>
    </select></div>
    <div class="form-group"><label for="counter-open-user">Cashier</label><select id="counter-open-user" class="form-control">
      <?php foreach ($users as $user): ?><option value="<?php echo (int)$user['user_id']; ?>"<?php echo (int)$user['user_id'] === $currentUser ? ' selected' : ''; ?>><?php echo htmlspecialchars((string)$user['username'], ENT_QUOTES, 'UTF-8'); ?></option><?php endforeach; ?>
    </select></div>
    <div class="counter-open-result" aria-live="polite"></div>
  </div>
  <div class="modal-footer"><button type="button" class="btn btn-default" data-dismiss="modal">Cancel</button><button type="button" class="btn btn-success counter-open-submit">Open Counter</button></div>
</div>
<script>
(function () {
  var shell = document.querySelector('.counter-open-modal');
  if (!shell || shell.dataset.ready === '1') return;
  shell.dataset.ready = '1';
  shell.querySelector('.counter-open-submit').addEventListener('click', function () {
    var button = this, result = shell.querySelector('.counter-open-result');
    var counter = Number(shell.querySelector('#counter-open-id').value || 0);
    var user = Number(shell.querySelector('#counter-open-user').value || 0);
    button.disabled = true; button.textContent = 'Opening…';
    fetch('../counter/api/counter_api.php?action=open&counter_id=' + counter + '&user_id=' + user + '&take_status=3', {credentials:'same-origin', headers:{Accept:'application/json'}})
      .then(function (response) { return response.json().then(function (payload) { if (!response.ok || !payload.ok) throw new Error(payload.message || 'Could not open counter.'); return payload; }); })
      .then(function (payload) { result.className='counter-open-result alert alert-success'; result.textContent=payload.message || 'Counter opened successfully.'; Array.prototype.forEach.call(shell.querySelectorAll('[data-open-step]'), function(step){ step.style.color='#15803d'; step.style.fontWeight='700'; step.textContent='✓ ' + step.textContent.replace(/^✓\s*/, ''); }); button.textContent='Opened'; setTimeout(function(){window.location.reload();},900); })
      .catch(function (error) { result.className='counter-open-result alert alert-danger'; result.textContent=error.message || 'Could not open counter.'; button.disabled=false; button.textContent='Open Counter'; });
  });
}());
</script>
