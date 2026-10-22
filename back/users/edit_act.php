<?php

include('../../inc/connection.php'); 
	$drole = isset($_REQUEST['drole']) ? (int) $_REQUEST['drole'] : 0;
	$role = isset($_REQUEST['roles']) ? (int) $_REQUEST['roles'] : 0; 
	$username = isset($_REQUEST['username']) ? trim($_REQUEST['username']) : '';
	$user_id = isset($_REQUEST['user_id']) ? (int) $_REQUEST['user_id'] : 0; 

	if (empty($username) || empty($role) || empty($user_id)) {
 ?>
 <div class="alert alert-danger"><a href="#" class="close" data-dismiss="alert">&times;</a><strong>Warning!</strong> FIll All The Fields.</div>
	<script type="text/javascript">
		window.setTimeout(function(){
			window.location.href = 'user.php';
		}, 700);
	</script>
	<?php
	}else{
		$edit_user = "UPDATE kp_users SET username='$username',role_id='$role',drole='$drole' WHERE user_id='$user_id'" ;
	    $edit_user_r = mysqli_query($connection,$edit_user) or die(mysqli_error($connection));
	  ?>
		 	<div class="alert alert-success">
		 		<a href="#" class="close" data-dismiss="alert">&times;</a>
		 		<strong>Success!</strong> User successfully updated.
		 	</div>
		 	<script type="text/javascript">
		 		window.setTimeout(function(){
		 			window.location.href = 'user.php';
		 		}, 700);
		 	</script>

		<?php
	}




?>
