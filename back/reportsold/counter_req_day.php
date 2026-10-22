 
<?php 
require("../clude/head.php");
$shift = $_GET['shift']; 
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
    		<span class="pull-right hidden-print" style="height: 50px; margin-top: 10px;">
        		<a href="javascript:;" onclick="window.print()" class="btn btn-sm btn-success m-b-10  hidden-print" ><i class="fa fa-print m-r-5"></i> Print</a>
      		</span>
					<div class="panel-body">
                     <table class="datatable table table-valign-middle m-b-0">
						<thead>
						   	<tr><th colspan="5"><h1><b><?php echo $shift;?> COUNTER REQUISITION REPORT</b></h1></th> </tr>
						    <tr>
						      	<th>Req No:</th> 
						      	<th>Req By</th>   
						      	<th>Req Time</th> 	
						      	 <th>Issue Status</th>	
						      	 <th>Issue By</th>		
						      	 <th>Issue Time</th>		         
						         <th class="hidden-print">Action</th>
						      </tr>
						   </thead>
						   <tbody>
                          <?php 
						   $sales = mysqli_query($connection,"SELECT * FROM kp_counter_req WHERE req_shift='$shift' ORDER BY req_id DESC");
						   if (mysqli_num_rows($sales) < 1) {
						   	?>
						   	<div class="alert alert-warning">
						   		<p>There are no recorded sales</p>
						   	</div>
						   	<?php
						   }else{
						   	while ($sa = mysqli_fetch_assoc($sales)) {

						   		$req_datime = $sa['datime']; 
						   		$req_id = $sa['req_id']; 
						   		$user_id = $sa['req_user'];  
						   		$counter = $sa['counter'];  
						   		$issue_status = $sa['issue_status']; 
                                                   // Older records used issue_datime; newer requisitions use issue_created_at.
                                                   $issue_datime = $sa['issue_created_at'] ?? ($sa['issue_datime'] ?? '');
						   		$issue_user = $sa['issue_user']; 
						   		?>
						   		<tr>
						   			<td><?php echo $req_id; ?></td> 						   	
						   			<td><?php echo get_user($user_id); ?></td> 
						   			<td><?php echo $req_datime; ?></td>
						   			<td><?php if ($issue_status==2) { echo "Not Issued";}else{ echo "Issued";} ?></td> 					   	
						   			<td><?php echo get_user($issue_user); ?></td> 	   	
						   			<td><?php echo $issue_datime; ?></td>
						   			<td class="hidden-print"> <button data-toggle="modal" data-target="#edit_item" data-id="<?php echo $req_id; ?>" id="getItem" ><i class='fa  fa-edit' ></i></button>|</td>
						   		</tr>
						   		<?php	
						   	}
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
   	App.init(); 
      $(document).on('click','#getItem', function(e)
      {
      	e.preventDefault();
      	var uid = $(this).data('id'); // get id of clicked row
      	$('#dynamic-content').html(''); // leave this diff blank 
      	$.ajax(
      	   {
      		 url: 'req_items.php',
             typr: 'POST',
             data: 'id='+uid,
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
