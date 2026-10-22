<?php 
session_start();
include('../../../inc/connection.php'); 
require_once('../../../inc/gen_funcs.php');
$invoices = isset($_REQUEST['invoices']) ? trim($_REQUEST['invoices']) : ''; 
$total = 0;
$no = 0;
$order_users = [];
?>  
<div class="panel-body"> 

  <?php if (empty($invoices) || trim($invoices) === ''): ?>
    <div class="alert alert-warning text-center">
      <strong>No bills selected.</strong>
    </div>
  <?php else: ?>
    <div class="cash-clearb-summarybar">
      <button id="cash_btn" class="btn btn-md btn-outline-success pull-right" onclick="pay()">CLEAR BILLS</button>
      <button id="credit_btn" class="btn btn-md btn-outline-warning pull-right" style="margin-right: 8px;" onclick="sendToCredit()">SEND TO CREDIT</button>
    </div>
    <table class="table table-striped text-center">
      <thead>
        <tr>
          <th>#.</th>
          <th>BILL NO.</th>
          <th>WAITER</th>
          <th>AMOUNT</th> 
        </tr>
      </thead>
      <tbody>
        <?php
        $arr = array_filter(array_map('trim', explode(",", $invoices)));

        foreach ($arr as $c) { 
            $get_sale = "SELECT * FROM kp_sales WHERE sale_id='$c'";
            $get_sale_r = mysqli_query($connection, $get_sale) or die("Could not fetch the order");

            if ($sow = mysqli_fetch_assoc($get_sale_r)) {
                $user_id = $sow['user_id'];
                $amount = $sow['amount'];
                $order_users[] = $user_id;
                $total += $amount;
                $no++;
                ?>
                <tr>
                  <td><?php echo $no; ?></td>
                  <td><?php echo $c; ?></td>
                  <td><?php echo get_column('kp_users','username',"user_id='$user_id'");?></td>
                  <td><?php echo $amount; ?></td> 
                </tr>
                <?php
            } else {
                ?>
                <tr>
                  <td colspan="4" class="text-danger">Bill #<?php echo $c; ?> not found.</td>
                </tr>
                <?php
            }
        }
        ?> 
        <tr>
          <th class="text-center" colspan="3"><h4>TOTAL</h4></th>
          <th><h4 id="amo"><?php echo $total; ?></h4></th>
        </tr> 
      </tbody>
    </table>
    <?php
    $order_users = array_values(array_unique(array_filter($order_users)));
    $orduser = count($order_users) === 1 ? $order_users[0] : 0;
    $ordlabel = count($order_users) === 1 ? get_column('kp_users','username',"user_id='$orduser'") : 'Mixed Bills';
    ?>
    <script type="text/javascript">
      $('#cash_btn')
        .attr('data-orduser', '<?php echo $orduser; ?>')
        .attr('data-ordlabel', '<?php echo htmlspecialchars($ordlabel, ENT_QUOTES); ?>');
      $('#credit_btn')
        .attr('data-orduser', '<?php echo $orduser; ?>')
        .attr('data-ordlabel', '<?php echo htmlspecialchars($ordlabel, ENT_QUOTES); ?>');
    </script>
  <?php endif; ?>
</div>
