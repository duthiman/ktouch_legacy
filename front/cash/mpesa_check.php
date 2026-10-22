<?php
session_start();
include('../../inc/connection.php');
include('funcs.php');
$con=$connection;
$user_id = $_SESSION['user_id']; 
if(isset($_POST['search'])){
	$no=0;
    $search = $_POST['search'];
    $pay_id = $_POST['id'];
    //$has_change = $_POST['change'];
    $get_menu = mysqli_query($con,"SELECT * FROM split_message WHERE   code like'%".$search."%' AND status=2  ORDER BY id DESC LIMIT 200") or die("Could not get the customer invoices with balances". mysqli_error($con));
    while ($mat = mysqli_fetch_assoc($get_menu)) {
        $name=$mat['name'];
        $code=$mat['code'];
        $amount=$mat['amount'];
        $time=$mat['time'];
        ?>
        <tr>
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
          <td><?php echo $amount; ?></td>
          <td><?php echo $time; ?></td> 
        </tr>
        <?php
    } 
}else{
  echo "test";
}
 
?>  