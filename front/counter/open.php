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
<div class="counter-open-modal" style="background:#fff;color:#243b53;border-radius:7px;">
  <div class="modal-header" style="background:#166534;color:#fff;border-radius:7px 7px 0 0;">
    <button type="button" class="close" data-dismiss="modal" aria-label="Close"><span aria-hidden="true">&times;</span></button>
    <h4 class="modal-title">Open Counter</h4>
  </div>
  <div class="modal-body" style="padding:18px;">
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
      .then(function (payload) { result.className='counter-open-result alert alert-success'; result.textContent=payload.message || 'Counter opened successfully.'; button.textContent='Opened'; setTimeout(function(){window.location.reload();},900); })
      .catch(function (error) { result.className='counter-open-result alert alert-danger'; result.textContent=error.message || 'Could not open counter.'; button.disabled=false; button.textContent='Open Counter'; });
  });
}());
</script>
