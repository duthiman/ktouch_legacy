<?php
include('../../inc/connection.php');
 
 	$name = $_REQUEST['name'];
 	$dpt = $_REQUEST['dpt'];
 	$cat_id = $_REQUEST['cat_id'];
	
	if (empty($name)) {
	?>
	<div class="alert alert-danger">
		<a href="#" class="close" data-dismiss="alert">&times;</a>
		<strong>Warning!</strong> Make sure you fill the name.
	</div>
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
		$new_cat = "INSERT INTO kp_sub_cat(name,cat_id,dpt) VALUES('$name','$cat_id','$dpt')";
		$new_cat_r = mysqli_query($connection,$new_cat) or die(mysqli_error($connection));
		if (!$new_cat_r) { }else{  
	?>
	<div class="alert alert-success">
		<a href="#" class="close" data-dismiss="alert">&times;</a><strong>Success!</strong> Successfully Added.</div>
		<?php
	}
}

?>
