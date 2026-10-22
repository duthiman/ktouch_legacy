<?php
include('funcs.php');
$user_id = $_SESSION['id']; 
$po_id 	= get_column('kp_thesion','trans_id',"user_id='$user_id'");
if ($user_id==0) {
	// code...
}elseif ($po_id==0) {
	// code...
}else{

}
$potype 	= get_column('kp_po_status','potype',"po_id='$po_id'");
$sup_id		= get_column('kp_po_status','sup_id',"po_id='$po_id'");
$poby 	= get_column('kp_po_status','poby',"po_id='$po_id'");
$day 		= get_column('kp_po_status','po_day',"po_id='$po_id'");
//if($sale_id==0 OR empty($sale_id)) {
	?>
	<script type="text/javascript">
		//window.location.href ="../";
	</script>
	<?php 
//}else{
	?>

		<link href="po.css" rel="stylesheet" />
	<!-- begin #content -->
	<!-- begin row -->
	<div class="mains row"> 
		
	  <!-- begin col-6 -->
    <div class="col-md-6">
      <div class="panel panel-purple" data-sortable-id="index-1" style="margin-top: 10px;">  
        <div class="panel-body"> 
          <span id="results"></span>
          <div class="p-order">
            <!-- begin invoice-header--> 
            <div class="inv-header" style="border-top:  2px solid #753f91; ">
              <div style=" border-right: green; width: 50%; float: left;">
                <a href="../dash/admin_dash.php" >
                  <img src="<?php echo $gen['image_path']; ?>" style="width: 100px; margin-left: 5px; margin-top: 5px;" />
                  </a> 
              </div>
              <div style=" border-right: green; width: 50%; float: left;"> 
                <h2 style=" text-align: right; font-size: 16px; margin:1px;"><u>PURCHASE ORDER</u> </h2> 
                <h2 style=" text-align: right; font-size: 16px; margin:1px;"> <?php echo $gen['company_name']; ?> </h2> 
                <address style="margin-top: 1px; text-align: right;"> 
                  <?php echo $gen['address']."<br> Tel. ".$gen['phone1'].", ".$gen['phone2']; ?>  
                </address>
              </div> 
            </div>
            <div class="inv-contact">
              <div style=" border-right: green; width: 60%; float: left;">
                <strong style="color: #753f91;">REF NO: <?php echo $refno;?></strong><br>
                <strong style="color: #753f91;">PO TO:</strong><br>
                  <strong class="text-inverse"><?php echo $bname;?></strong><br />
                  <?php echo $address; ?><br/> 
                  <?php echo $mobile; ?><br/> 
                  <?php echo $email; ?> 
                </div>
                <div class="col-md-4" style=" width: 30%; float: right; padding:5px 1px">
                  <table class="table">
                    <tbody >
                      <tr>
                        <td><b class="pull-left">PO No: </b></td>
                        <td><b class="pull-right"><?php echo $po_id; ?></b> </td>
                      </tr>
                      <tr>
                        <td><b class="pull-left">PO Date: </b></td>
                        <td><b class="pull-right"><?php echo $po_date; ?></b> </td>
                      </tr> 
                    </tbody>
                  </table>       
                </div> 
            </div>  
            <!-- end invoice-header -->
            <!-- begin invoice-Details--> 
            <div class="inv-content" >
              <table class="table">
                <thead>
                  <tr> 
                    <th>No</th>
                    <th style="text-align: left;">Item Description</th>
                    <th style="text-align: right;">Qty</th>
                    <th style="text-align: right; color: red;">Current Stock</th>
                    <th style="text-align: right;">Unit Price</th>
                    <th style="text-align: right;">Disc</th>
                    <th style="text-align: right;">Total</th>  
                    <?php 
                    if ($taxby==1) {
                      ?>
                      <th>Tax</th>
                      <?php
                    }
                    ?>
                  </tr>
                </thead>
                <tbody>
                  <?php 
                  $no=0;
                  $sum=0; 
                  $sumd=0;  
                  $po_items = mysqli_query($con ,"SELECT * FROM kp_po_items WHERE po_id= '$po_id'") or die(mysqli_error($connection));
                  if (mysqli_num_rows($po_items) < 1) {
                    echo "There are no items on this order";
                  }else{
                    while ( $data = mysqli_fetch_assoc($po_items)) {
                      $row_id  = (int)$data['id'];    // ✅ unique row PK
                      $item_id = $data['item_id'];
                      $price = $data['price'];
                      $quantity = $data['qty'];
                      $disc = $data['disc'];
                      $total = $data['total'];
                      $no=$no+1;
                      $sum=$sum+$total;
                      $sumd=$sumd+$disc;
                      
                      ?>
                      <tr>
                        <td><?php echo $no; ?></td>
                        <td><?php echo get_item($item_id); ?></td>
                        <!-- <td style="text-align: right;"><?php echo $quantity; ?></td> -->
                        <td>
                          <div class="input-group"> 
                            <input type="number" step="1" min="1" max="" name="quantity" value="<?php echo $quantity; ?>" title="Qty" id="<?php echo "qty_".$row_id; ?>" class="input-text qty text" size="6" pattern="" inputmode="" onfocus="showbtn(this);">&nbsp;
                            <div class="input-group-prepend <?php echo "qtydiv_".$row_id; ?> btn-div" style="display: none;"  id="">
                              <button class="btn btn-outline-info" onclick="updatepo(<?php echo $row_id; ?>);"  style="height: 25px;"><i style="font-size: 18px;  margin: -2px -2px -2px -2px ;" class='fa fa-sm fa-check' ></i> </button>   
                            </div>
                          </div>
                      </td>
                        <td style="text-align: right; color: red;"><?php
                          $v_id = (int)($data['variant_id'] ?? $data['stock_id'] ?? 0);
                          if ($v_id > 0) {
                            echo get_column('kp_inventory_stock_variants','qty',"variant_id='$v_id'");
                          } else {
                            echo '–';
                          }
                        ?></td>
                        <td>
                          <div class="input-group"> 
                            <input type="number" step="1" min="1" max="" name="price" value="<?php echo $price; ?>" title="price" id="<?php echo "price_".$row_id; ?>" class="input-text price text" size="6" pattern="" inputmode="" onfocus="showbtn(this);">&nbsp;
                            <div class="input-group-prepend <?php echo "pricediv_".$row_id; ?> btn-div" style="display: none;"  id="">
                              <button  class="btn btn-outline-info" onclick="updatep(<?php echo $row_id; ?>);"  style="height: 25px;"><i style="font-size: 18px;  margin: -2px -2px -2px -2px ;" class='fa fa-sm fa-check' ></i> </button>   
                            </div>
                          </div>
                        </td>
                        <!-- <td style="text-align: right;"><?php echo number_format($price,2); ?></td> -->
                        <td style="text-align: right;"><?php echo number_format($disc,2); ?></td>
                        <td style="text-align: right;"><?php echo number_format($total,2); ?></td> 
                        
                      </tr>
                      <?php 
                    }
                  }
                  ?> 
                </tbody>
                <tfoot>
                  <!-- <tr> 
                    <td colspan="2" rowspan="4" class="text-right m-b-5"></td>
                    <td colspan="4" style="text-align: center;" ><strong>Sub Total:</strong></td>
                    <td style="text-align: right;"><strong> Ksh. &nbsp;<?php echo number_format($sum,2); ?></strong></td>
                  </tr> --> 
                 
                  <tr style="font-size: 18px;">  
                    <td style="text-align: center;" colspan="6"> <strong>TOTAL</strong> </td>
                    <td style="text-align: right;"> 
                      <strong> 
                        Ksh. &nbsp;<?php 
                        $tt=$sum-$sales['vat'];
                        echo number_format($tt,2); ?></strong> </td>
                  </tr>
                </tfoot>
              </table>
              <br>
            </div>  
          </div> 
        </div>
      </div>
    </div>
    <!-- end col-6 -->
	</div>
	<!-- end row -->  
	<script type="text/javascript">
	    App.setPageTitle('KSOFT | ORDER');
	    App.restartGlobalFunction();

	    // ── Inline JS for approval page (row-id based) ──
	    function showbtn(el) {
	        $('.btn-div').hide();
	        if (el) {
	            var id = $(el).attr('id');
	            var div = id.replace('qty_','qtydiv_').replace('price_','pricediv_');
	            $('.' + div).show();
	        }
	    }

	    function updatepo(rowId) {
	        var qty = $('#qty_' + rowId).val();
	        $.ajax({
	            url: 'kug/app/upqty.php',
	            type: 'POST',
	            data: { id: rowId, qty: qty },
	            dataType: 'html'
	        }).done(function(d) {
	            $('#results').html(d);
	            window.location.reload();
	        });
	    }

	    function updatep(rowId) {
	        var price = $('#price_' + rowId).val();
	        $.ajax({
	            url: 'kug/app/upprice.php',
	            type: 'POST',
	            data: { id: rowId, price: price },
	            dataType: 'html'
	        }).done(function(d) {
	            $('#results').html(d);
	            window.location.reload();
	        });
	    }
	</script>
	<?php 
//}
?>