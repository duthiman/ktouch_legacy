<?php 
 include("../../inc/connection.php");
 include("../clude/funcs.php"); 

$user_id = $_SESSION['user_id'];

// ── Set printer name for a counter ────────────────────────────────
if (isset($_POST['action']) && $_POST['action'] === 'set_printer') {
  $cntr_id      = (int)$_POST['cntr_id'];
  $printer_name = mysqli_real_escape_string($connection, trim($_POST['printer_name']));

  // Ensure column exists
  $_col_chk = mysqli_query($connection, "SHOW COLUMNS FROM kp_counters LIKE 'printer_name'");
  if (mysqli_num_rows($_col_chk) === 0) {
      mysqli_query($connection, "ALTER TABLE kp_counters ADD COLUMN printer_name VARCHAR(100) NOT NULL DEFAULT ''");
  }

  $upd = mysqli_query($connection, "UPDATE kp_counters SET printer_name='$printer_name' WHERE cntr_id='$cntr_id'");
  if ($upd) { ?>
<div class="alert alert-success"><strong>Saved.</strong> Printer name updated.</div>
<?php } else { ?>
<div class="alert alert-danger"><strong>Error.</strong> <?php echo mysqli_error($connection); ?></div>
<?php }
  exit;
}
if (isset($_POST['action']) && $_POST['action'] === 'set_mpesa') {
  $cntr_id = (int)$_POST['cntr_id'];
  $type = strtoupper(trim($_POST['mpesa_type'] ?? 'TILL')) === 'PAYBILL' ? 'PAYBILL' : 'TILL';
  $shortcode = mysqli_real_escape_string($connection, trim($_POST['mpesa_shortcode'] ?? ''));
  $account = mysqli_real_escape_string($connection, trim($_POST['mpesa_account'] ?? ''));
  foreach (["mpesa_type VARCHAR(20) NOT NULL DEFAULT 'TILL'", "mpesa_shortcode VARCHAR(50) NOT NULL DEFAULT ''", "mpesa_account VARCHAR(100) NOT NULL DEFAULT ''"] as $definition) { $parts = explode(' ', $definition, 2); $check = mysqli_query($connection, "SHOW COLUMNS FROM kp_counters LIKE '{$parts[0]}'"); if ($check && mysqli_num_rows($check) === 0) mysqli_query($connection, "ALTER TABLE kp_counters ADD COLUMN {$definition}"); }
  $ok = mysqli_query($connection, "UPDATE kp_counters SET mpesa_type='$type', mpesa_shortcode='$shortcode', mpesa_account='$account' WHERE cntr_id='$cntr_id'");
  echo $ok ? '<div class="alert alert-success"><strong>Saved.</strong> M-Pesa settings updated.</div>' : '<div class="alert alert-danger">'.htmlspecialchars(mysqli_error($connection)).'</div>'; exit;
}

// ── Create new counter ─────────────────────────────────────────────
$name = strtoupper($_REQUEST['name'] ?? '');
$dpt  = $_REQUEST['dpt'] ?? '';

if ( empty($name)){
?>
<script type="text/javascript"> 
  var hulla = new hullabaloo();
    hulla.send('Name cannot be empty!!!!!!!', "warning");
</script>
<?php
  }elseif ( empty($dpt) or $dpt==0){
?>
<script type="text/javascript"> 
  var hulla = new hullabaloo();
    hulla.send('Department not selected!!!!!!!', "warning");
</script>
<?php
}else{

$query = "SHOW TABLES LIKE 'kp_counters'";
$result = mysqli_query($connection, $query);
if (!$result || mysqli_num_rows($result) === 0) {
  create_table();
  create_counter($name,$user_id,$dpt); 
}else{
  $get_id = mysqli_query($connection,"SELECT cntr_id FROM kp_counters WHERE name='" . mysqli_real_escape_string($connection, $name) . "'");
  $mx = mysqli_num_rows($get_id);
  if ($mx>0) { 
   ?> 
<script type="text/javascript"> 
  var hulla = new hullabaloo();
    hulla.send('COUNTER NAME EXISTS!!!!!!!', "warning");
</script>
<?php
  }else{
   create_counter($name,$user_id,$dpt); 
  }
  
}
}
function create_table(){
   global $connection;
   $query = "CREATE TABLE `kp_counters` (
  `cntr_id` int(11) AUTO_INCREMENT,
  `name` varchar(11) NOT NULL,
  `dpt` INT NOT NULL,
  `staff_id` int(11) NOT NULL,
  `date` datetime NOT NULL DEFAULT CURRENT_TIMESTAMP,
  `del_status` int(11) NOT NULL DEFAULT '2',
  `printer_name` varchar(100) NOT NULL DEFAULT '',
  PRIMARY KEY (`cntr_id`)
  ) ENGINE=InnoDB DEFAULT CHARSET=latin1;";
  $result = mysqli_query($connection, $query);
}
 
function create_counter($name,$user_id,$dpt){
  global $connection; 
$safe_name = mysqli_real_escape_string($connection, $name);

// Older installations use user_id; newer installations use staff_id.
// Use the column that exists instead of breaking existing databases.
$owner_column = 'staff_id';
$staff_column_check = mysqli_query($connection, "SHOW COLUMNS FROM kp_counters LIKE 'staff_id'");
if (!$staff_column_check || mysqli_num_rows($staff_column_check) === 0) {
  $owner_column = 'user_id';
}

$new_counter = mysqli_query(
  $connection,
  "INSERT INTO kp_counters(name,dpt,`$owner_column`) VALUES('$safe_name','$dpt','$user_id')"
) or die("Error creating the new counter: " . mysqli_error($connection));

// $get_id = mysqli_query($connection,"SELECT cntr_id FROM kp_counters WHERE name='".$name."'");
// $mx = mysqli_fetch_assoc($get_id);
// $id = $mx['id'];
$id =mysqli_insert_id($connection);
$new_counter = "cntr".$id;
$column_qty="ALTER TABLE `kp_items` ADD `".$new_counter."_qty` DECIMAL(10,2) NOT NULL;";

$column_reorder="ALTER TABLE `kp_items` ADD `".$new_counter."_reorder` DECIMAL(10,2) NOT NULL;";

$column_take="ALTER TABLE `kp_items` ADD `".$new_counter."_take` INT NOT NULL DEFAULT '2';";




$table_stock_take = "CREATE TABLE IF NOT EXISTS `kp_".$new_counter."_stock_take` (
  `take_id` int(11) NOT NULL,
  `item_id` int(11) NOT NULL,
  `staff_id` int(11) NOT NULL,
  `user_id` int(11) NOT NULL DEFAULT 0,
  `opening` decimal(10,2) NOT NULL,
  `counted` decimal(10,2) NOT NULL,
  `closing` decimal(10,2) NOT NULL,
  `issued` decimal(12,2) NOT NULL DEFAULT 0,
  `tots` decimal(12,2) NOT NULL DEFAULT 0,
  `transfers` decimal(12,2) NOT NULL DEFAULT 0,
  `total_in` decimal(12,2) NOT NULL DEFAULT 0,
  `sale` decimal(12,2) NOT NULL DEFAULT 0,
  `trans_out` decimal(12,2) NOT NULL DEFAULT 0,
  `tot_out` decimal(12,2) NOT NULL DEFAULT 0,
  `total_out` decimal(12,2) NOT NULL DEFAULT 0,
  `shift` date NOT NULL
) ENGINE=InnoDB AUTO_INCREMENT=1024 DEFAULT CHARSET=latin1;
";
$alter_stock_take = "ALTER TABLE `kp_".$new_counter."_stock_take` ADD PRIMARY KEY (`take_id`);";

$alter_take="ALTER TABLE `kp_".$new_counter."_stock_take` MODIFY `take_id` int(11) NOT NULL AUTO_INCREMENT,AUTO_INCREMENT=1024;";

$table_closing_stock = "CREATE TABLE IF NOT EXISTS `kp_".$new_counter."_closing_stock` (
  `item_id` int(11) NOT NULL,
  `qty` int(11) NOT NULL,
  `shift` date NOT NULL
) ENGINE=InnoDB DEFAULT CHARSET=latin1;";



$tables = [$column_qty,$column_reorder, $column_take,$table_stock_take,$table_closing_stock,$alter_stock_take,$alter_take]; 
foreach($tables as $k => $sql){
  $query = $connection->query($sql);
  // if(!$query){
  //   return false;
  // }else{
  //   return true;
  // }
}
}
?>
