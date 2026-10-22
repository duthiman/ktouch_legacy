<?php 
include('funcs.php');
$user_id =  $_SESSION['mwene']; 
$po_id = get_column('kp_thesion','trans_id',"user_id='$user_id'");   
if ($po_id==0) {
    header("Location: init_page.php");
}else{   
    $sup_id = get_column('kp_po_status','sup_id',"po_id='$po_id'");
    $potype = get_column('kp_po_status','potype',"po_id='$po_id'");
    $poby = get_column('kp_po_status','poby',"po_id='$po_id'");
    $day = get_column('kp_po_status','po_day',"po_id='$po_id'"); 
	$amount=column_total('kp_po_items','total',"po_id='$po_id'"); 
	 
    $no=0;
    $total=0;  
 
 

	$create_po = mysqli_query($connection,"INSERT INTO kp_po(po_id,sup_id,ord_user,po_date,amount,potype,poby) VALUES('$po_id','$sup_id','$user_id','$day','$amount','$potype','$poby')") or die(mysqli_error($connection));
	if ($create_po) {
		$update_items = mysqli_query($connection,"UPDATE kp_po_items SET status=1 WHERE po_id='$po_id'") or die(mysqli_error($connection));
		if ($update_items) {
			$clear_status = mysqli_query($connection,"DELETE FROM kp_po_status  WHERE po_id='$po_id'") or die("Error");
			if ($clear_status) {
				$action="PLACED PURCHASE ORDER ";
				$url="../../";
				logs($url,'back/kugura/','PURCHASE',$po_id,'-','-',$action);
				logs("../",'back/kugura/','PURCHASE',$po_id,'-','-',$action); 
				?>
				<script type="text/javascript">
					window.open('kug/ord/print_po.php?id=<?php echo $po_id; ?>', '_blank', 'width=900,height=700');
					try { $('#modal').modal('hide'); } catch(e){}
					$.ajax({ url:'kug/index.php', type:'GET', dataType:'html' })
					 .done(function(h){ $('#content').html(h); });
				</script>
				<?php
			}else{
				$update_items = mysqli_query($connection,"UPDATE kp_po_items SET status=2 WHERE po_id='$po_id'") or die(mysqli_error($connection));
				$clear_po = mysqli_query($connection,"DELETE FROM kp_po WHERE po_id='$po_id'") or die(mysqli_error($connection));
				?>
				<p style="color: red;">CONTACT ADMISTRATOR WITH ERROR "COULD NOT CLEAR STATUS"</p>
				<?php
			}
		}else{
			$clear_po = mysqli_query($connection,"DELETE FROM kp_po WHERE po_id='$po_id'") or die(mysqli_error($connection));
			?>
			<p style="color: red;">CONTACT ADMISTRATOR WITH ERROR "COULD NOT UPDATE PO ITEMS"</p>
			<?php
		}
	}else{
	?>
	<p style="color: red;">CONTACT ADMISTRATOR WITH ERROR "COULD NOT INSERT PO"</p>
	<?php
	}
}
?>

 