<?php
if (session_status() === PHP_SESSION_NONE) {
  session_start();
}
include('../../../inc/connection.php');
require_once('../funcs.php');

$cust_id = isset($_GET['cust_id']) ? (int)$_GET['cust_id'] : 0;
$from = isset($_GET['from']) && $_GET['from'] !== '' ? $_GET['from'] : date('Y-m-d');
$nos = 50;

$customer_q = mysqli_query($connection,"SELECT * FROM kp_customers WHERE cust_id='$cust_id' LIMIT 1") or die(mysqli_error($connection));
$customer = mysqli_num_rows($customer_q) > 0 ? mysqli_fetch_assoc($customer_q) : null;
?>
<!DOCTYPE html>
<html lang="en">
<head>
  <title>Customer Statement</title>
  <link href="../assets/css/thermal_print.css" rel="stylesheet" />
</head>
<body style="width:80mm" onload="window.print()">
  <div class="receipt">
    <div id="bord">
      <?php if (!$customer) { ?>
        <p style="margin: 0;"><?php echo sep($nos); ?></p>
        <h4 style="margin: 0; text-align: center;"><b>CUSTOMER NOT FOUND</b></h4>
        <p style="margin: 0;"><?php echo sep($nos); ?></p>
      <?php } else { ?>
        <p style="margin: 0;"><?php echo sep($nos); ?></p>
        <p style="margin: 0;"><?php echo sep($nos); ?></p>
        <h4 style="margin: 0; text-align: center;">
          <b>CUSTOMER STATEMENT</b><br>
          <?php echo htmlspecialchars($customer['name']); ?>
        </h4>
        <p style="margin: 0;"><?php echo sep($nos); ?></p>

        <table class="table">
          <tbody>
            <tr>
              <td><b>PHONE</b></td>
              <td><?php echo !empty($customer['phone']) ? htmlspecialchars($customer['phone']) : '-'; ?></td>
            </tr>
            <tr>
              <td><b>FROM</b></td>
              <td><?php echo htmlspecialchars($from); ?></td>
            </tr>
            <tr>
              <td><b>CURRENT BAL</b></td>
              <td><?php echo number_format((float)$customer['bal'], 2); ?></td>
            </tr>
          </tbody>
        </table>

        <p style="margin: 0;"><?php echo sep($nos); ?></p>
        <h4 style="margin: 0; text-align: center;"><b>STATEMENT ITEMS</b></h4>
        <p style="margin: 0;"><?php echo sep($nos); ?></p>

        <table class="table">
          <tbody>
            <tr>
              <td><b>DATE</b></td>
              <td><b>TYPE</b></td>
              <td><b>REF</b></td>
              <td><b>AMT</b></td>
            </tr>
            <?php
            $hist = mysqli_query($connection,"SELECT trans_date,trans,trans_id,amount,cf_bal FROM kp_cust_hist WHERE cust_id='$cust_id' AND trans_date>='$from' ORDER BY trans_date ASC, trans_id ASC, amount ASC") or die(mysqli_error($connection));
            $sales_total = 0;
            $payment_total = 0;
            if (mysqli_num_rows($hist) < 1) {
              echo '<tr><td colspan="4">No statement entries for this date range.</td></tr>';
            } else {
              while ($row = mysqli_fetch_assoc($hist)) {
                $amount = (float)$row['amount'];
                if (strtoupper($row['trans']) === 'SALE') {
                  $sales_total += $amount;
                }
                if (strtoupper($row['trans']) === 'PAYMENT') {
                  $payment_total += $amount;
                }
                echo '<tr>';
                echo '<td>'.htmlspecialchars($row['trans_date']).'</td>';
                echo '<td>'.htmlspecialchars($row['trans']).'</td>';
                echo '<td>'.htmlspecialchars($row['trans_id']).'</td>';
                echo '<td>'.number_format($amount,2).'</td>';
                echo '</tr>';
                echo '<tr>';
                echo '<td colspan="3"><b>C/F BAL</b></td>';
                echo '<td>'.number_format((float)$row['cf_bal'],2).'</td>';
                echo '</tr>';
              }
            }
            ?>
          </tbody>
        </table>

        <p style="margin: 0;"><?php echo sep($nos); ?></p>
        <table class="table">
          <tbody>
            <tr>
              <td><b>TOTAL SALES</b></td>
              <td><?php echo number_format($sales_total, 2); ?></td>
            </tr>
            <tr>
              <td><b>TOTAL PAYMENTS</b></td>
              <td><?php echo number_format($payment_total, 2); ?></td>
            </tr>
            <tr>
              <td><b>OUTSTANDING</b></td>
              <td><?php echo number_format((float)$customer['bal'], 2); ?></td>
            </tr>
          </tbody>
        </table>

        <p style="margin: 0;"><?php echo sep($nos); ?></p>
        <p style="margin: 0;"><?php echo sep($nos); ?></p>
      <?php } ?>
    </div>
  </div>
</body>
</html>
