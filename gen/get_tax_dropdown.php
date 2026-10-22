<?php
include('../inc/connection.php');
include('../inc/gen_funcs.php');

ensure_tax_system();

$item_id = isset($_GET['item']) ? intval($_GET['item']) : 0;
$tax_id = isset($_GET['value']) ? intval($_GET['value']) : 0;

// Get all taxes from kp_tax
$sql = "SELECT tax_id, name, percent FROM kp_tax WHERE del_status = 2 ORDER BY name ASC";
$res = mysqli_query($connection, $sql) or die(mysqli_error($connection));
$taxes = [];
while ($row = mysqli_fetch_assoc($res)) {
    $taxes[] = $row;
}

// Get current tax name
$currentName = 'Not Set';
foreach ($taxes as $t) {
    if ($t['tax_id'] == $tax_id) {
        $currentName = htmlspecialchars($t['name']) . ' (' . number_format($t['percent'], 2) . '%)';
        break;
    }
}
?>
<div class="btn-group">
  <button type="button" class="btn btn-xs btn-outline-secondary dropdown-toggle" data-toggle="dropdown" aria-haspopup="true" aria-expanded="false">
    <?php echo $currentName; ?>
  </button>
  <div class="dropdown-menu">
    <?php foreach ($taxes as $tax) { ?>
      <a class="dropdown-item" href="#" onclick="edit_data('<?php echo $item_id; ?>','<?php echo $tax['tax_id']; ?>','tax_id','Tax'); return false;">
        <?php echo htmlspecialchars($tax['name']); ?> (<?php echo number_format($tax['percent'], 2); ?>%)
      </a>
    <?php } ?>
  </div>
</div>

<?php
