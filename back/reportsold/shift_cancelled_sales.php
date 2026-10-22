 
<?php 
require("../clude/head.php");
$shift = $_GET['shift'];
$tcash=0;
$tmpesa=0;
$tvisa=0;
$sum=0;
?>
 
<!-- begin #page-container -->
<div id="page-container" class="fade page-sidebar-fixed page-header-fixed">
  <!-- begin #header -->  
  <?php 
    require("../clude/header.php");
    
  ?>
  <!-- end #header -->  
  <!-- begin #sidebar -->
  <?php 
    require("../clude/menu.php");
  ?>
  <!-- end #sidebar -->
		<!-- begin #content -->
		<div id="content" class="content">
		<div class="row">
               <div class="col-md-12">
                	<div class="panel panel-success" data-sortable-id="index-1">
					<div class="panel-body">
                     <table class="datatable table table-valign-middle m-b-0">
						   <thead>
						   	<tr><th colspan="5"><h1><b><?php echo $shift;?> VOIDED SALES REPORT</b></h1></th> </tr>
						      <tr>
						      	 <th>Receipt No:</th> 
						      	 <th>Served By</th> 
						         <th>Total</th>	
						         <!-- <th>Pay Status</th> -->					         
						         <th class="hidden-print">Action</th>
						      </tr>
						   </thead>
						   <tbody>
                          <?php 
						   $sales = mysqli_query($connection,"SELECT amount,user_id,sale_id FROM kp_cancelled WHERE shift='$shift' GROUP BY sale_id ORDER BY sale_id DESC");
						   if (mysqli_num_rows($sales) < 1) {
						   	?>
						   	<div class="alert alert-warning">
						   		<p>There are no recorded sales</p>
						   	</div>
						   	<?php
						   }else{
						   	while ($sa = mysqli_fetch_assoc($sales)) {
						   		$sale_id = $sa['sale_id']; 
						   		$user_id = $sa['user_id'];
						   		$amount=$sa['amount'];
						   		$sum=$sum+$amount; 


						   		?>
						   		<tr>
						   			<td><?php echo $sale_id; ?></td> 						   		
						   			<td><?php echo get_user($user_id); ?></td> 
						   			<td><?php echo $amount; ?></td>
						   			<td class="hidden-print"> <button data-toggle="modal" data-target="#edit_item" data-id="<?php echo $sale_id; ?>" id="getItem" ><i class='fa  fa-edit' ></i></button>|</td>
						   		</tr>
						   		<?php	
						   	}
						   	?>
						   	<tfoot>
						   		<tr>
						   			<th colspan="2" class="text-center">Total Amount</th> 
						   			<th><?php echo $sum; ?></th>
						   		</tr>
						   	</tfoot>
						   	<?php
						   }
						   ?>

						
						
							</tbody>
						

					    	</table>
                    </div>


                   
                </div>                  
                
            </div>
			<!-- end invoice -->
		</div>
			<div id="edit_item" class="modal fade" tabindex="-1" role="dialog" aria-labelledby="myModalLabel" aria-hidden="true" style="display: none;">
	  			<div class="modal-dialog"> 
					<div id="dynamic-content"></div>
	  			</div>
			</div>
	</div>
	

	<!-- end page container -->
	
	<?php
		include('../clude/foot.php');
	?>
 
<script type="text/javascript">
   
   $(document).ready(function()
   {
      $(document).on('click','#getItem', function(e)
      {
      	e.preventDefault();
      	var uid = $(this).data('id'); // get id of clicked row
      	$('#dynamic-content').html(''); // leave this diff blank 
      	$.ajax(
      	   {
      		 url: 'cancel_items.php',
             typr: 'POST',
             data: 'id='+uid+'&dpt=1',
             dataType: 'html'
           })
         .done(function(data)
           {
             console.log(data);
             $('#dynamic-content').html(''); // blank before load.
             $('#dynamic-content').html(data);
         })
         .fail(function()
          {
          	$('#dynamic-content').html('<i class="fa  fa-info-sign"></i> Something went wrong, Please try again...');
          });
      });

   });
</script>

   
<script>
    $(document).ready(function() {
      App.init(); 
      $(".selectpicker").select2(); 
	}); 
 </script> 
