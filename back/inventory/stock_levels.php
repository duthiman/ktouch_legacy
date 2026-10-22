<?php
require_once '../../inc/connection.php';
require_once '../../inc/gen_funcs.php';
$item_id = (int)($_POST['id'] ?? $_GET['id'] ?? 0);
if ($item_id < 1) { exit('<div class="alert alert-danger">Invalid item.</div>'); }
$item_q = mysqli_query($connection, "SELECT item_id,name,store_qty FROM kp_items WHERE item_id='$item_id' LIMIT 1");
$item = $item_q ? mysqli_fetch_assoc($item_q) : null;
if (!$item) { exit('<div class="alert alert-danger">Item was not found.</div>'); }
$columns = [];
$cols_q = mysqli_query($connection, "SHOW COLUMNS FROM kp_items");
while ($col = $cols_q ? mysqli_fetch_assoc($cols_q) : null) {
  if (preg_match('/^cntr([0-9]+)_qty$/', (string)$col['Field'], $m)) $columns[(int)$m[1]] = (string)$col['Field'];
}
ksort($columns);
echo '<div class="modal-content"><div class="modal-header"><button type="button" class="close" data-dismiss="modal">&times;</button><h4 class="modal-title">Stock Levels: ' . htmlspecialchars($item['name'], ENT_QUOTES, 'UTF-8') . '</h4></div><div class="modal-body"><table class="table table-striped table-bordered"><thead><tr><th>Location</th><th>Quantity</th></tr></thead><tbody>';
echo '<tr><td><strong>Main Store</strong></td><td>' . htmlspecialchars($item['store_qty'], ENT_QUOTES, 'UTF-8') . '</td></tr>';
foreach ($columns as $counter_id => $column) {
  $name = 'Counter ' . $counter_id;
  $cq = mysqli_query($connection, "SELECT name FROM kp_counters WHERE cntr_id='" . (int)$counter_id . "' LIMIT 1");
  if ($cq && ($cr = mysqli_fetch_assoc($cq)) && trim((string)$cr['name']) !== '') $name = $cr['name'];
  $value_q = mysqli_query($connection, "SELECT `$column` AS qty FROM kp_items WHERE item_id='$item_id' LIMIT 1");
  $value = $value_q ? (mysqli_fetch_assoc($value_q)['qty'] ?? 0) : 0;
  echo '<tr><td>' . htmlspecialchars($name, ENT_QUOTES, 'UTF-8') . '</td><td>' . htmlspecialchars($value, ENT_QUOTES, 'UTF-8') . '</td></tr>';
}
echo '</tbody></table><p class="text-muted">Business stock is the main store plus all counter quantities.</p></div><div class="modal-footer"><button type="button" class="btn btn-default" data-dismiss="modal">Close</button></div></div>';
