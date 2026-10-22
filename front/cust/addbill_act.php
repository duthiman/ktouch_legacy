<?php
session_start();
include('../../inc/connection.php');
include("funcs.php"); 
$shift=$_SESSION['shift'];
$user_id = $_SESSION['user_id']; 
$sale_id = $_REQUEST['id'];
$cust_id = $_REQUEST['cust']; 
$bill_total=get_column('kp_sales','amount',"sale_id='$sale_id'"); 

 //insert into payments 
$update_orders = mysqli_query($connection,"UPDATE kp_sales SET pay_status=3,cust_id='$cust_id',sale_bal='$bill_total',cf_bal=cf_bal+'$bill_total',type=2 WHERE sale_id='$sale_id'") or die("Mysqli_error.:".$connection);
$update_orders_items = mysqli_query($connection,"UPDATE kp_sale_items SET pay_status=3,type=2 WHERE sale_id='$sale_id'") or die("Mysqli_error.:".$connection);

$update_bal=mysqli_query($connection,"UPDATE kp_customers SET bal=bal+'$bill_total' WHERE cust_id='$cust_id'");
cust_trans($cust_id,"SALE",$sale_id,$bill_total);
	if (!$update_orders ) {
		echo "Failed to update the orders";
	}else{
		?>
		 <script type="text/javascript"> 
		 	 window.location='../print/print_inv.php?status=3&id=<?php echo $sale_id; ?>';
		</script>
		<?php
	}	 
?>