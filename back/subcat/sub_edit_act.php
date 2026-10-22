<?php
include('../../inc/connection.php');
if (isset($_REQUEST['name'])) {

	$name =$_REQUEST['name'];
	$cat_id = $_REQUEST['cat_id'];
	$sub_id = $_REQUEST['sub_id'];
	$dpt = $_REQUEST['dpt'];
	if ( empty($name) or empty($cat_id) ) {
		?>

		<div class="alert alert-danger"><a href="#" class="close" data-dismiss="alert">&times;</a><strong>Warning!</strong> Please Fill All The Fields.</div>
		<script type="text/javascript">
			 window.setTimeout(function(){

                            window.location.href = 'cat.php';

                        }, 1000);
		</script>

		<?php
	}elseif ( empty($dpt) or $dpt==0){
		?>
	<div class="alert alert-warning">
		<a href="#" class="close" data-dismiss="alert">&times;</a>
		<strong><?php echo $dpt; ?>Warning!</strong> Department not selected.
	</div>
	<?php
	}elseif ( empty($cat_id) or $cat_id==0){
	?>
 	<div class="alert alert-warning">
		<a href="#" class="close" data-dismiss="alert">&times;</a>
		<strong>Warning!!!!!</strong> Category not selected.
	</div>
	<?php
	}else{

		$edit_item = "UPDATE kp_sub_cat SET name='$name',cat_id='$cat_id',dpt='$dpt' WHERE sub_id='$sub_id' " or die("Error edition the file");
		$edit_item_r = mysqli_query($connection,$edit_item) or die("error on 18");
 
	if (!$edit_item_r) {
			?>

		<div class="alert alert-danger"><a href="#" class="close" data-dismiss="alert">&times;</a><strong>Failed!</strong> Failed To Edited.</div>
		<script type="text/javascript">
			 window.setTimeout(function(){

                            window.location.href = 'subcat.php';

                        }, 1000);
		</script>

		<?php
		}else{
			?>

		<div class="alert alert-success"><a href="#" class="close" data-dismiss="alert">&times;</a><strong>Success!</strong> Success Edited.</div>
		<script type="text/javascript">
			 window.setTimeout(function(){

                            window.location.href = 'subcat.php';

                        }, 1000);
		</script>

		<?php
		}
	}

}
?>