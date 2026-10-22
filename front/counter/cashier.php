<?php
session_start();
include('../../inc/connection.php');
include('funcs.php');
$cntr_id = (int)($_REQUEST['id'] ?? 0);
$counter_name = $cntr_id > 0 ? get_counter($cntr_id) : '';
$counter_row = [];
if ($cntr_id > 0) {
  $counter_q = mysqli_query($connection, "SELECT * FROM kp_counters WHERE cntr_id='$cntr_id' LIMIT 1");
  if ($counter_q && mysqli_num_rows($counter_q) > 0) {
    $counter_row = mysqli_fetch_assoc($counter_q) ?: [];
  }
}

$is_kitchen_counter = false;
if (!empty($counter_row)) {
  if (isset($counter_row['dpt']) && (int)$counter_row['dpt'] === 2) {
    $is_kitchen_counter = true;
  } elseif (isset($counter_row['type']) && stripos((string)$counter_row['type'], 'kitchen') !== false) {
    $is_kitchen_counter = true;
  } elseif (isset($counter_row['counter_type']) && stripos((string)$counter_row['counter_type'], 'kitchen') !== false) {
    $is_kitchen_counter = true;
  }
}
if (!$is_kitchen_counter && stripos((string)$counter_name, 'kitchen') !== false) {
  $is_kitchen_counter = true;
}

$default_role = $is_kitchen_counter ? 3 : 1;
?>
<div id="results"></div>
<p class="open-counter-title"><?php echo get_counter($cntr_id); ?> Cashier</p>
<?php
$get_tables = "SELECT * FROM kp_users WHERE counter_status=2 AND drole='$default_role'";
$get_tables_r = mysqli_query($connection,$get_tables) or die("Could not fetch the tables".mysqli_error($connection));
if (mysqli_num_rows($get_tables_r) < 1) {
  ?>
  <div class="open-counter-empty">No available <?php echo $is_kitchen_counter ? 'kitchen cashier' : 'cashier'; ?> found for this counter.</div>
  <?php
} else {
  ?>
  <div class="cashier-list">
    <?php
    while ($tab = mysqli_fetch_assoc($get_tables_r)) {
      $name = $tab['username'];
      $cashier_id = $tab['user_id'];
      ?>
      <button type="button" class="cashier-btn" onclick="count_cash(<?php echo $cashier_id; ?>);">
        <span class="badge"><?php echo strtoupper(substr($name, 0, 1)); ?></span>
        <span class="copy"><?php echo $name; ?></span>
      </button>
      <?php
    }
    ?>
  </div>
  <?php
}
?>
<script type="text/javascript">
function count_cash(cs){
  $("#cashier").attr("disabled", true);
  var cntr = '<?php echo $cntr_id; ?>';
  $.ajax({
    url: '../counter/open_counter.php',
    typr: 'POST',
    data: 'cash_id=' + cs + '&cntr=' + cntr,
    dataType: 'html'
  })
  .done(function(data){
    $('#results').html(data);
  })
  .fail(function(){
    $('#results').html('<div class="open-counter-empty">Could not open counter. Please try again.</div>');
  });
}
</script>
