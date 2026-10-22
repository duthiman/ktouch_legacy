	<?php 
session_start();
include('../../inc/connection.php');  
include('funcs.php');

$user_id = $_SESSION['user_id'];
$get_details = mysqli_query($connection,"SELECT * FROM kp_thesion WHERE user_id='$user_id'") or die("Could not get the user permissions");
$row=mysqli_fetch_assoc($get_details);
$po_id = $row['po_id'];
if ($po_id==0) {
    header("Location: init_page.php");
}else{   
$action="LOADED CONFIRM MODAL FOR PURCHASE ORDER NO | ".$po_id;
$url="../../";
logs($action,$url);

$disc = $_REQUEST['disc'];
$tt = $_REQUEST['tt'];
$vatable = $_REQUEST['vatable'];
$vatt = $_REQUEST['vatt']; 
$perc = $_REQUEST['perc'];  
$sup = $_REQUEST['sup'];
$ship = $_REQUEST['ship'];
$no=0;
$total=0; 
}
?>
<div class="modal-content">
    <div id="po-results"></div>
		<div class="modal-header"> 
            <h3 class="modal-title" style="color:purple;">CONFIRM PURCHASE ORDER NO <?php echo $po_id; ?></h3>
        </div>
        <div class="modal-body"> 
             <table class="table table-bordered" id="TheSo">
    <thead>
        <tr> 
            <th colspan="6"><h3 style="color:blue;">SUPPLIER: <?PHP echo get_sup($sup); ?></h3></th>
        </tr>
        <tr>
            <th>No:</th>
            <th>Item Name</th>
            <th>Quantity</th> 
            <th>Price</th> 
            <th>Discount</th> 
            <th>Amount</th>                
        </tr>
    </thead>
    <tbody>
        <?php 
            $requisitions = mysqli_query($connection,"SELECT * FROM kp_po_items WHERE  po_id='$po_id'") or die(mysqli_error($connection));
            while ($row = mysqli_fetch_assoc($requisitions)) {
                $item_id = $row['item_id']; 
                $no=$no+1;
                $amount=$row['total'];
                $total=$total+$amount;
        ?>
        <tr>
            <td><?php echo $no; ?></td>
            <td><?php echo get_item($item_id); ?></td>
            <td><?php echo $row['qty']; ?></td>  
            <td><?php echo $row['price'];  ?></td>
             <td> <?php echo $row['disc']; ?>  </td>
            <td><?php echo $amount ?></td> 
        </tr>
    </tbody>
    <?php
     }
    ?>
    <tfooter>
        <tr>
            <th colspan="5" class="text-right">SUB-TOTAL</th>
            <th id="ttt"><?php echo round($total,2) ?></th>
        </tr>
        <tr>
            <th colspan="5" class="text-right">DISCOUNT</th>
            <th id="ttt"><?php echo round($disc,2) ?></th>
        </tr>
        <tr>
            <th colspan="5" class="text-right">TAXABLE</th>
            <th id="ttt"><?php echo $vatable ?></th>
        </tr>
        <tr>
            <th colspan="5" class="text-right">VAT (<?php echo $perc ?>%)</th>
            <th id="ttt"><?php echo $vatt ?></th>
        </tr>
        <tr style="font-size: 18px;">
            <th colspan="5" class="text-right">TOTAL</th>
            <th id="ttt"><?php echo $tt ?></th>
        </tr>
        <tr style="font-size: 18px;">
            <th colspan="5" class="text-right">SHIPPING</th>
            <th id="ttt"><?php echo $ship ?></th>
        </tr>
    </tfooter>
</table>
</div>
<div class="modal-footer">
    <button type="button" class="btn btn-danger btn-xs" data-dismiss="modal">Close</button>
    <a class="btn btn-lime btn-xs" onclick="place_po();"  >Confirm</a> 
</div>
</div>
<script type="text/javascript">
    function place_po(){
        var disc= $('#disc').val();
        var vatable= $('#vatable').html(); 
        var vatt= $('#vatt').html();
        var tt= $('#tt').html();
        var perc= $('#perc-input').val(); 
        var ship= $('#ship').val(); 
        var sup = document.getElementById("sup_id").value;
        var vat_type=$('input[name="tax"]:checked').val(); 

        $.ajax({
            url: 'place_po.php',
            type: 'POST',
            data: {disc:disc,vatable:vatable,vatt:vatt,tt:tt,perc:perc,sup:sup,vat_type:vat_type,ship:ship},
            dataType: 'html'
        })
        .done(function(data){
            console.log(data);
            $('#po-results').html(''); // blank before load.
            $('#po-results').html(data); // blank before load.
        })
        .fail(function(){
            $('#po-results').html(''); // blank before load.
            $('#po-results').html('<i class="fa  fa-info-sign"></i> Something went wrong, Please try again...');
        });
    }
</script>