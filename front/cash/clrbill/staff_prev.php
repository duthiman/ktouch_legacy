<?php
session_start();
include('../../../inc/connection.php');
include('../funcs.php');

$db = isset($connection) ? $connection : (isset($conn) ? $conn : null);
$shift = get_column('kp_shift', 'shift', "1");
$st = isset($_REQUEST['st']) ? (int) $_REQUEST['st'] : 2;
$user_id = isset($_REQUEST['id']) ? (int) $_REQUEST['id'] : 0;

if ($st === 1 && $user_id > 0) {
  $sales = mysqli_query(
    $db,
    "SELECT * FROM kp_sales WHERE shift!='$shift' AND pay_status=2 AND user_id='$user_id' ORDER BY sale_id DESC"
  );
  $title = get_user($user_id) . " PREVIOUS UNPAID BILLS";
} else {
  $sales = mysqli_query(
    $db,
    "SELECT * FROM kp_sales WHERE shift!='$shift' AND pay_status=2 ORDER BY sale_id DESC"
  );
  $title = "ALL STAFF PREVIOUS UNPAID BILLS";
}
?>
<table class="table table-striped">
  <thead>
    <tr>
      <th id="user" data-id='<?php echo $user_id; ?>' data-id2='2' colspan="6" style="font-size: 20px;" class="text-center">
        <?php echo $title; ?>
      </th>
    </tr>
    <tr>
      <th class="text-left"><h4>SELECT</h4></th>
      <th><h4>ORDER NO:</h4></th>
      <th><h4>SHIFT</h4></th>
      <th><h4>STAFF</h4></th>
      <th><h4>COUNTER</h4></th>
      <th><h4>AMOUNT</h4></th>
    </tr>
  </thead>
  <tbody>
    <?php if ($sales && mysqli_num_rows($sales) > 0) { ?>
      <?php while ($row = mysqli_fetch_assoc($sales)) {
        $sale_user_id = $row['user_id'];
        $sale_id = $row['sale_id'];
        $sale_shift = $row['shift'];
        $totals = isset($row['amount']) ? $row['amount'] : (isset($row['total_amount']) ? $row['total_amount'] : 0);
        $counter = $row['counter'];
      ?>
        <tr style="font-size:16px; font-weight:bolder;">
          <td class="text-left"><input type="checkbox" name="pay-sale[]" onchange="bulk_pay()" value="<?php echo $sale_id; ?>" class="bulk cash-bill-pick" data-amount="<?php echo (float) $totals; ?>" /></td>
          <td><?php echo $sale_id; ?></td>
          <td><?php echo $sale_shift; ?></td>
          <td><?php echo get_user($sale_user_id); ?></td>
          <td><?php echo get_column('kp_counters', 'name', "cntr_id='$counter'"); ?></td>
          <td><?php echo $totals; ?></td>
        </tr>
      <?php } ?>
    <?php } else { ?>
      <tr>
        <td colspan="6" class="text-center" style="padding:20px;">There are no previous unpaid bills in the system at the moment</td>
      </tr>
    <?php } ?>
  </tbody>
</table>
