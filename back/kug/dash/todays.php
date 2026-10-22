<?php
include("../../../inc/gen_funcs.php"); 
?> 
<div class="modal-content"> 
	<div class="modal-body" id="menudets">  
		<table class="table table-striped">
			<thead>   
                <tr> 
                    <th>#</th>
                    <th>Po No.</th> 
                    <th>Supplier</th>
                    <th>Raised By</th> 
                    <th>Date</th> 
                    <th>Status</th> 
                </tr>
            </thead>
            <tbody>
                <?php  
                $no=0; 
                $query = "SELECT * FROM kp_po WHERE po_date=CURRENT_DATE";
                $results = mysqli_query($connection, $query);
                while ($row = mysqli_fetch_array($results)) {
                    $no=$no+1;   
                    $po_id = $row['po_id']; 
                    $sup_id = $row['sup_id'];
                    $user_id = $row['ord_user']; 
                    $status = $row['rec_status']; 
                    $day = $row['po_date'];
                    $button="<button  id='vwgrns' class='btn btn-outline-cyan btn-xs' data-id='$po_id'><i class='fa fa-eye'></i> View Items</button>";
                
                    if ($status==2) {
                        $clr="red";
                        $stat="<b class='text-white'> PENDING</b>";
                    }elseif ($status==3) {
                        $clr="orange";
                        $stat="<b class='text-white'> ONGOING</b>";
                    }elseif ($status==1) {
                        $clr="green";
                        $stat="<b class='text-white'> COMPLETE</b>";
                    }else{
                        $clr="red";
                        $stat="<b class='text-white'>GRN HAS ERROR</b>";
                    }
                
                    $sup=get_column(' kp_suppliers','sup_name',"sup_id='$sup_id'");
                    $user=get_column(' kp_users','username',"user_id='$user_id'");
                    ?>
                    <tr class="fs-11px"> 
                        <th><?php echo $no; ?></th>
                        <th><?php echo $po_id; ?></th>  
                        <th><?php echo $sup; ?></th>
                        <th><?php echo $user; ?></th> 
                        <th><?php echo $day; ?></th> 
                        <th style="background:<?php echo $clr; ?>"><?php echo $stat; ?></th>
                    </tr>
                    <?php
                }
                ?>
            </tbody>
		</table>
	</div> 
</div>  
<script type="text/javascript">
	$( document ).ready(function() {  
	  	$(document).on('click','#vwgrns', function(e){
	        e.preventDefault();  
	        $('#modal').modal('show');
	        var id = $(this).data('id');
	        $.ajax({
	          url: 'kugura/vwgrn.php',
	          typr: 'POST',
	          data: {id:id},
	          dataType: 'html'
	        })
	        .done(function(data){ 
	          $('#load').html('');
	          $('#load').html(data);
	        })
	        .fail(function(){
	          $('#load').html('<i class="fa  fa-info-sign"></i> Something went wrong, Please try again loadig the sale script...');
	          $('#modal-loader').hide();
	        });
      	});  
	});
</script>