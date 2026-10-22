 <?php
session_start();
include('../../inc/connection.php');
include('funcs.php');
$con=$connection;
$user_id = $_SESSION['user_id']; 
$pay_id = $_REQUEST['id']; 
?>
 <!--<input type="hidden" id="gate-input2" class="form-control"  name="gate" value="<?php echo $gate; ?>">  -->
<table id="data-table-combine" class="table table-striped table-bordered">
  <thead>
    <tr>
      <th colspan="6">  
          <input type="text"  class="form-control pull-right" id="mpesaphone"   placeholder="ENTER PHONE NUMBER TO SEARCH" style="width: 100%;" onkeyup="mpesasearch(<?php echo $pay_id; ?>)"> 
          
      </th>
    </tr> 
    <tr style="padding: 3px;">
      <th>Select</th>
      <th>Code.</th> 
      <th>Name.</th>
      <th>Amount</th>
      <th>Time</th>  
    </tr>
  </thead>
  <tbody id="mpesarows">
    <?php 
    $get_menu = mysqli_query($con,"SELECT * FROM kp_split_message WHERE status=2 AND claim_status=1 AND date > now() - interval 2 day ORDER BY id DESC LIMIT 200") or die("Could not get the customer invoices with balances". mysqli_error($con));
    while ($mat = mysqli_fetch_assoc($get_menu)) {
      $name=$mat['name'];
      $code=$mat['code'];
      $amount=$mat['amount'];
      $date=$mat['date'];
      $time=$mat['time'];
      ?>
        <td> 
          <input type="checkbox" name="pay-sale[]" onclick="sel<?php echo $pay_id; ?>()" value="<?php echo $amount ?>" data-value1="<?php echo $code ?>" id="r<?php echo $code; ?>" class="bulk2" />
          <label for="r<?php echo $code; ?>"  style="
            font-weight: bold;color: #FFFFFF !important;
            font-size: 12px; 
            background: #000;
            border: 2px solid #e0bd65; 
            padding : 3px;  
            border-radius: 5px; ">&nbsp; SELECT</label> 
        </td>
        <td><?php echo $code; ?></td>
        <td><?php echo $name; ?></td>
        <td><?php echo $date; ?></td>
        <td><?php echo $amount; ?></td>
        <td><?php echo $time; ?></td> 
      </tr>
    <?php
    }
    ?> 
  </tbody>
</table> 