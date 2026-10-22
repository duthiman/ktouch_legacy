<?php
session_start();
include('../../inc/connection.php');
include('../kug/funcs.php');

if (!isset($_SESSION['user_id'])) {
  header("Location:../log_out.php");
  exit;
}

$po_id = $_GET['id'];
$user_id = $_SESSION['user_id'];
$po_q = mysqli_query($connection, "SELECT * FROM kp_po WHERE po_id='$po_id'") or die(mysqli_error($connection));
$po = mysqli_fetch_assoc($po_q);
if (!$po) {
  header("Location:../stock/store_dash.php");
  exit;
}

$shift = get_column('kp_shift','shift',"1");
$sup_id = (int)$po['sup_id'];
$supplier_name = $sup_id > 0 ? get_sup($sup_id) : 'NOT SELECTED YET';
$counter_name = get_counter((int)$po['req_id']);
$grand_total = 0;
$total_qty = 0;
?>
<!DOCTYPE html>
<html lang="en">
  <head>
    <meta http-equiv="content-type" content="text/html; charset=<charset>" />
    <title>Receipt</title>
  </head>
  <link href="../assets/css/thermal_print.css" rel="stylesheet" />
  <script type="text/javascript">
    function close_po(){
      window.location = '../stock/store_dash.php?msg=3';
    }
  </script>
  <body onload="close_po(); window.print()">
    <div id="receipt">
      <div id="bord">
        <div id="receipt-header" class="text-center">
          <h1 class="title"><?php echo $counter_name; ?><br>BUY STOCK ITEMS</h1>
          <p style="margin: 0px;">**********************************************************</p>
          <span><b>Shift: <?php echo $shift; ?></b></span><br>
          <span><b>Print Time: <?php echo today(); ?></b></span><br>
          <span class="text-center">Raised By: <?php echo get_user($po['ord_user']); ?></span><br>
          <span class="text-center"><b>PO No: <?php echo $po_id; ?></b></span><br>
          <span class="text-center"><b>Supplier: <?php echo $supplier_name; ?></b></span><br>
          <span class="text-center"><b>Status: PENDING BACKEND APPROVAL</b></span><br>
          <p style="margin: 0px;">**********************************************************</p>
        </div>
        <div id="receipt-body">
          <table>
            <tbody>
              <?php
              $items = mysqli_query($connection, "SELECT * FROM kp_po_items WHERE po_id='$po_id' ORDER BY id ASC") or die(mysqli_error($connection));
              while ($row = mysqli_fetch_assoc($items)) {
                $grand_total += (float)$row['total'];
                $total_qty += (float)$row['qty'];
              ?>
              <tr>
                <td><?php echo get_item($row['item_id']); ?></td>
                <td><?php echo $row['qty']; ?></td>
                <td><?php echo $row['price']; ?></td>
                <td><?php echo $row['total']; ?></td>
              </tr>
              <?php } ?>
            </tbody>
          </table>
          <br>
          <p style="margin: 0px;"><b>Total Qty: <?php echo $total_qty; ?></b></p>
          <p style="margin: 0px;"><b>Grand Total: <?php echo number_format($grand_total, 2); ?></b></p>
          <p style="margin: 0px;">**********************************************************</p>
          <p style="margin: 0px;">**********************************************************</p>
        </div>
      </div>
    </div>
  </body>
</html>
