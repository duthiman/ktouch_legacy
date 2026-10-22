<?php
session_start();
include('../../../inc/gen_funcs.php');  
$url = "../../assets";
$user_id = $_SESSION['mwene'];
$grn_id=$_REQUEST['id'];  
$no=0; 
$sup_id=get_column('kp_grn','sup_id',"grn_id='$grn_id'");  
$po_id=get_column('kp_grn','po_id',"grn_id='$grn_id'"); 
?>
<link href="<?php echo $url; ?>/css/default/invoice-print.css" rel="stylesheet" />

<div class="modal-content" style="width:auto;">
    <div class="modal-header pull-right">
		<h4 class="panel-title"> GOODS RECEIVED NOTE NO:.<?php echo $grn_id; ?></h4>  
	</div>
	<div class="modal-body" id="menudets">  
<div class="p-order">
<!-- begin invoice-header--> 
<div class="inv-header" style="border-top:  2px solid #753f91; "> 
  <div style=" border-right: green; width: 30%; float: left;">  
    <h2 style=" text-align: right; font-size: 16px; margin:1px;"> <?php echo get_column('kp_settings_gen','company_name',"1"); ?> </h2>  
  </div> 
  <div style=" border-right: green; width: 70%; float: left;"> 
    <h2 style=" text-align: right; font-size: 16px; margin:1px;"><u>GOODS RECEIVED NOTE</u> </h2> 
    <h2 style=" text-align: right; font-size: 16px; margin:1px;"> <?php echo strtoupper(get_column('kp_suppliers','sup_name',"sup_id='$sup_id'")); ?> </h2> 

    <p style="margin-top: 1px; text-align:right;">   
      <?php echo get_column('kp_suppliers','sup_phone',"sup_id='$sup_id'"); ?><br/> 
      <?php echo get_column('kp_suppliers','sup_address',"sup_id='$sup_id'"); ?><br/> 
      <?php echo get_column('kp_suppliers','sup_email',"sup_id='$sup_id'"); ?>
    </p>
  </div> 
</div>
<div class="inv-contact"> 
    <div class="col-md-4" style=" width: 100%; float: right; padding:5px 1px">
      <table class="table">
        <tbody >
          <tr>
            <td>GRN No:. <?php echo $grn_id; ?></td>
            <td>REF NO:. <?php echo $po_id; ?> </td>
            <td>DATE:. <?php echo get_column('kp_grn','day',"grn_id='$grn_id'");; ?> </td>
            <td>PO NO:. <?php echo get_column('kp_grn','po_id',"grn_id='$grn_id'");; ?> </td>
            <td><?php echo $po_id; ?> </td>
          </tr>  
        </tbody>
      </table>       
    </div> 
</div>  
<!-- end invoice-header -->

<!-- begin invoice-Details--> 
<div class="inv-content" >
       <table class="table table-invoice">
<thead>
  <tr>
    <th>No:</th>
    <th>Item Name</th> 
    <th>Brand</th> 
    <th>Size</th> 
    <th>Color</th> 
    <th>Qty</th> 
    <th>Price</th> 
    <th>Total</th> 
  </tr>
</thead>
<tbody>
  <?php
  $sum=0;
  $get_orders = "SELECT * FROM kp_grn_items WHERE grn_id='$grn_id' ";
  $get_orders_r = mysqli_query($connection,$get_orders) or die("Could not fetch the orders");
  while ($s = mysqli_fetch_assoc($get_orders_r)) {
    $no=$no+1;
    $item_id = $s['item_id'];
    $qty = $s['qty']; 
    $price = $s['price']; 
    $total = $s['total']; 
    $size_id = $s['size_id']; 
    $brand_id = $s['brand_id']; 
    $clr_id = $s['clr_id']; 
    $sum=$sum+$total; 
    ?>
    <tr>
      <td><?php echo $no; ?></td>
      <td><?php echo get_item($item_id); ?></td> 
      <td><?php echo get_column('kp_brands','name',"brand_id='$brand_id'"); ?></td> 
      <td><?php echo get_column('kp_item_sizes','name',"size_id='$size_id'"); ?></td> 
      <td><?php echo get_column('kp_item_color','name',"clr_id='$clr_id'"); ?></td> 
      <td><?php echo $qty; ?></td> 
      <td><?php echo $price; ?></td> 
      <td><?php echo $total; ?></td> 
    </tr>
    <?php
  }
  ?>
  <tr>
    <th colspan="7" class="text-center "><h3>TOTAL</h3></th>
    <th><h3><?PHP echo $sum; ?></h3></th>
  </tr>
</tbody> 
</table>
    </div> 
</div>  
</div>  
</div>  
 