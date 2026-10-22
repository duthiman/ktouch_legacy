<?php
include('../../inc/connection.php'); 
include('funcs.php');
$start_date = mysqli_real_escape_string($connection,$_GET['start_date']);
$end_date = mysqli_real_escape_string($connection,$_GET['end_date']);
 $tcr=0;
 $tcs=0;
 $no=0;
?>
<table id="data-table-combine" class="table table-striped table-bordered">
  <thead>
  	<tr class="text-center ">
  		<td colspan="8" style="border-top: 2px solid #000;">
        <h3>
          <b><U>PURCHASED ITEMS SUMMARY FROM <?PHP echo $start_date;?> to <?php echo $end_date;?></U></b>
        </h3>
      </td>
  	</tr>
    <tr>
       <th style="width: 20px;">No:</th>
    	 <th style="width: 150px;">Item Name:</th>  
       <th style="width: 20px;"> Qty</th>
    	 <th style="width: 50px;"> Amount:</th>    
    </tr>
  </thead>
<tbody>
<?php 
 $ttotal=0;
$get_id = mysqli_query($connection,"SELECT item_id,SUM(qty) as qty,SUM(total) as total FROM kp_po_items INNER JOIN kp_po on kp_po_items.po_id=kp_po.po_id WHERE kp_po.po_date BETWEEN '$start_date' AND '$end_date' GROUP BY item_id") or die("Error fetching items.".mysqli_error($connection));
 
  while ($sa = mysqli_fetch_assoc($get_id)) {
		$item_id = $sa['item_id'];
     $qty= $sa['qty'];
     $total = $sa['total'];
 
    $ttotal=$ttotal+$total;
    $no=$no+1; 
    ?>
		<tr ondblclick="item_hist(<?php echo $item_id; ?>,'<?php echo $start_date; ?>','<?php echo $end_date; ?>')">
      <td><?php echo $no; ?></td>
			<td><?php echo get_item($item_id).$item_id; ?></td>
			<td><?php echo $qty; ?></td> 
			<td><?php echo $total; ?></td> 
    </tr>
		<?php
  }	 
?>
  <tr>
    <td colspan="3" class="text-center"><h3>TOTAL</h3></td>  
    <td><h3><?php echo $ttotal; ?></h3></td>
  </tr>
  </tbody>
</table> 
