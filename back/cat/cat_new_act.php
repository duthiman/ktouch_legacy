<?php
include('../../inc/connection.php');
 
 	$name = $_REQUEST['name'];
 	$dpt = $_REQUEST['dpt'];
	
	if (empty($name)) {?>

		<div class="alert alert-danger"><a href="#" class="close" data-dismiss="alert">&times;</a><strong>Warning!</strong> Please Fill All The Fields.</div>
		<?php
	}elseif ( empty($dpt) or $dpt==0){
		?>

		<div class="alert alert-warning"><a href="#" class="close" data-dismiss="alert">&times;</a><strong><?php echo $dpt; ?>Warning!</strong> Department not selected.</div>
		 

		<?php
	}else{

		$new_cat = "INSERT INTO kp_cat(name,dpt) VALUES('$name','$dpt')";
		$new_cat_r = mysqli_query($connection,$new_cat) or die(mysqli_error($connection));

		if (!$new_cat_r) {
			
		}else{  
				?>
	<div class="alert alert-success">
		<a href="#" class="close" data-dismiss="alert">&times;</a><strong>Success!</strong> Successfully Added.</div>
		<?php
	}
}

?>
