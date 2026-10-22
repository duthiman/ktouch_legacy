<?php
include('../inc/connection.php');
include('../inc/gen_funcs.php');
$item_id = isset($_GET['id']) ? $_GET['id'] : 0;
if (!$item_id) {
    echo "<div class='p-3'>Invalid item.</div>";
    exit;
}

// Get store qty from kp_items
$storeQty = get_column('kp_items','qty',"item_id='$item_id'");
if (!$storeQty) $storeQty = 0;

// Get item name
$itemName = get_column('kp_items','name',"item_id='$item_id'");
if (!$itemName) $itemName = 'Unknown Item';

// Calculate net qty per counter (IN as +, OUT as -) and join with kp_counters for names
$sql = "SELECT COALESCE(st.stock_counter,'UNASSIGNED') AS stock_counter, 
               COALESCE(c.name, st.stock_counter, 'UNASSIGNED') AS counter_name,
               SUM(CASE WHEN st.status='IN' THEN st.qty WHEN st.status='OUT' THEN -st.qty ELSE 0 END) AS qty 
        FROM kp_stock_track st
        LEFT JOIN kp_counters c ON c.cntr_id = CAST(st.stock_counter AS UNSIGNED)
        WHERE st.item_id='".intval($item_id)."' 
        GROUP BY st.stock_counter";
$res = mysqli_query($connection, $sql) or die(mysqli_error($connection));
$rows = [];
$total = 0;
while ($r = mysqli_fetch_assoc($res)) {
    $rows[] = $r;
    $total += floatval($r['qty']);
}

?>
<div class="modal-content" style="background:#f8f9fa; border-radius:8px; padding:20px; margin:10px 0; border:1px solid #dee2e6;">
  <div class="modal-header" style="border-bottom:1px solid #dee2e6; padding-bottom:15px; margin-bottom:15px;">
    <h5 class="modal-title"><strong><?php echo htmlspecialchars($itemName); ?></strong> - Item ID: <?php echo intval($item_id); ?></h5>
  </div>
  <div class="modal-body">
    <div class="row mb-3">
      <div class="col-md-6">
        <div class="card border-success">
          <div class="card-header bg-success text-white">
            <strong>Store Qty</strong>
          </div>
          <div class="card-body text-center">
            <h3 class="text-success"><?php echo number_format($storeQty, 2); ?></h3>
          </div>
        </div>
      </div>
      <div class="col-md-6">
        <div class="card border-primary">
          <div class="card-header bg-primary text-white">
            <strong>Overall Stock</strong>
          </div>
          <div class="card-body text-center">
            <h3 class="text-primary"><?php echo number_format($total, 2); ?></h3>
          </div>
        </div>
      </div>
    </div>

    <hr>

    <h6 class="mb-3"><strong>Stock by Counter:</strong></h6>
    <?php
    if (count($rows) == 0) {
        echo "<p class='text-muted'>No counter stock records found.</p>";
    } else {
        echo "<table class='table table-sm table-striped'>";
        echo "<thead class='table-light'><tr><th>Counter Name</th><th class='text-right'>Quantity</th></tr></thead><tbody>";
        foreach ($rows as $rr) {
            $cnt = htmlspecialchars($rr['counter_name']);
            $q = number_format($rr['qty'],2);
            echo "<tr><td>$cnt</td><td class='text-right'>$q</td></tr>";
        }
        echo "</tbody></table>";
    }
    ?>
  </div>
</div>

<?php

