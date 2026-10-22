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
          <b><U>MPESA REPORT FROM <?PHP echo $start_date;?> to <?php echo $end_date;?></U></b>
        </h3>
      </td>
  	</tr>
    <tr>
       <th style="width: 10px;" class="text-center">No:</th>
    	 <th style="width: 100px;">CODE</th> 
       <th style="width: 150px;">CUSTOMER</th>  
    	 <th style="width: 50px;">AMOUNT</th>  
       <th style="width: 50px;">DATE</th>  
       <th style="width: 50px;"> TIME</th> 
       <th style="width: 50px;">CLAIMSTATUS</th> 
       <th style="width: 50px;">CASHIER STATUS</th>    
    </tr>
  </thead>
<tbody>
<?php 
 $ttotal=0;
$get_id = mysqli_query($con,"SELECT * FROM split_message WHERE date BETWEEN '$start_date' AND '$end_date' ORDER BY id ASC") or die("Error fetching items.".mysqli_error($connection));
 
  while ($sa = mysqli_fetch_assoc($get_id)) { 
     $total = $sa['amount'];
    $claim_status = $sa['claim_status']; 
    $clear_status = $sa['clear_status'];
    if ($claim_status==2) {
      $claimer="<b class='text-red'>NOT_CLAIMED</b>";
    }else{
      $claim_user = $sa['claim_staff'];
      $claimer="<b class='text-green'>".get_column('kp_users','username',"user_id='$claim_user'")."</b>"; 
    }
    if ($clear_status==2) {
      $clearer="<b class='text-red'>NOT_CLEARED</b>";
    }else{

      $clearer="<b class='text-green'>CLEARED</b>";
    }

    $ttotal=$ttotal+$total;
    $no=$no+1; 
    ?>
		<tr ondblclick="item_hist(<?php echo $item_id; ?>,'<?php echo $start_date; ?>','<?php echo $end_date; ?>')">
      <td><?php echo $no; ?></td>
			<td><?php echo $sa['code']; ?></td>
			<td><?php echo strtoupper($sa['name']); ?></td> 
			<td><?php echo $total; ?></td> 
      <td><?php echo $sa['date']; ?></td> 
      <td><?php echo $sa['time']; ?></td> 
      <td><?php echo $claimer; ?></td>
      <td><?php echo $clearer; ?></td>  
    </tr>
		<?php
  }	 
?>
  <tr>
    <td colspan="3" class="text-center"><h3>TOTAL</h3></td>  
    <td><h3><?php echo $ttotal; ?></h3></td>
    <td colspan="4"></td>
  </tr>
  </tbody>
</table> 
