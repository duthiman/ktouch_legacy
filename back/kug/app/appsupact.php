<?php
include('funcs.php');  
$user_id = $_SESSION['mwene']; 
$po_id = $_REQUEST['po']; 
$sup_id = $_REQUEST['sup'];   
if (empty($sup_id) OR $sup_id==0 ) {
	?> 
	<script type="text/javascript">
		var hulla = new hullabaloo();
	    hulla.send('Danger!!! Supplier Cannot be Empty Please select !!!!!', "danger");  
	</script>
	<?php
}else if (empty($po_id) OR $po_id==0 ) {
	?> 
	<script type="text/javascript">
		var hulla = new hullabaloo();
	    hulla.send('Danger!!! PO ID IS ZERO !!!!!CONTACT ADMIN', "danger");  
	</script>
	<?php
}else{   
	$update = mysqli_query($connection,"UPDATE kp_po SET sup_id='$sup_id' WHERE po_id='$po_id'") or die("Could not update po status");
	if (!$update) {
		?>
		<script type="text/javascript">
			var hulla = new hullabaloo();
		    hulla.send('Danger!!! Contact Developer or Manager', "danger"); 
		   // $('#modal2').modal('toggle');
		</script>
	    <?php
	}else{ 
		$action="CHANGED PURCHASE SUPPLIER TO |". get_column('kp_suppliers','sup_name',"sup_id='$sup_id'");
		logs("../../",'back/kugura/','PURCHASE',$po_id,'-','-',$action);
		logs("",'back/kugura/','PURCHASE',$po_id,'-','-',$action);

		?>
		<script type="text/javascript">
			$('#modal').modal('hide');
			setTimeout(function(){
				$.ajax({
					url:'kug/app/apppage.php',
					type:'GET',
					data:{id:'<?php echo $po_id; ?>'},
					dataType:'html'
				}).done(function(h){ $('#content').html(h); });
			}, 300);
		</script>
	  <?php
	}
}
	?>