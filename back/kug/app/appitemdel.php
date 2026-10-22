<?php
include('funcs.php');
$user_id = $_SESSION['mwene']; 
$row_id  = (int)($_REQUEST['id'] ?? 0);   // ✅ unique kp_po_items.id

if ($user_id == 0 || empty($user_id)) {
?>
<script type="text/javascript">  
	var hulla = new hullabaloo();
	hulla.send("SESSION Issue Logout and login again", "danger");   
</script>
<?php
} elseif ($row_id <= 0) {
?>
<script type="text/javascript">  
	var hulla = new hullabaloo();
	hulla.send("Invalid item row", "danger");   
</script>
<?php
} else {
	// Fetch po_id + item_id from the row for logging
	$r = $connection->query("SELECT po_id, item_id FROM kp_po_items WHERE id='$row_id' LIMIT 1");
	$info = $r ? $r->fetch_assoc() : null;
	$po_id   = $info ? (int)$info['po_id']   : 0;
	$item_id = $info ? (int)$info['item_id'] : 0;

	// ✅ Delete by unique row id
	$delete = mysqli_query($connection, "DELETE FROM kp_po_items WHERE id='$row_id'") or die(mysqli_error($connection));
	if (!$delete) {
?>
<script type="text/javascript">  
	var hulla = new hullabaloo();
	hulla.send("Failed to delete", "danger");   
</script>
<?php
	} else {
		$action = "DELETED " . get_item($item_id);
		logs("../../", 'back/kugura/', 'PURCHASE', $po_id, '-', '-', $action);
		logs("", 'back/kugura/', 'PURCHASE', $po_id, '-', '-', $action);
		echo '<div class=\"alert alert-success py-1 small\"><i class=\"fa fa-check me-1\"></i>Item removed.</div>';
		exit;
<?php
	}
}
?>