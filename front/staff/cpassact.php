<?php
session_start();
include('../../inc/connection.php');
include("funcs.php");

$user_id = (int)($_SESSION['user_id'] ?? 0);
$pass = trim((string)($_POST['pass'] ?? ''));

	if ($user_id < 1) {
		?>
		<div class="alert alert-danger">
			<a href="#" class="close" data-dismiss="alert">&times;</a><strong>Warning!</strong>LOGOUT AND LOGGIN AGAIN..SESSION IS EXPIRED</div> 
		<?php
	}elseif ($pass === '') {
		?>
		<div class="alert alert-danger">
			<a href="#" class="close" data-dismiss="alert">&times;</a><strong>Warning!</strong> PASSWORD CANNOT BE EMPTY</div> 
		<?php
	}else{
 
		$edit_stmt = mysqli_prepare($connection, "UPDATE kp_users SET pin=? WHERE user_id=?");
		if ($edit_stmt) {
			mysqli_stmt_bind_param($edit_stmt, 'si', $pass, $user_id);
			$edit_pass_r = mysqli_stmt_execute($edit_stmt);
			mysqli_stmt_close($edit_stmt);
		} else {
			$edit_pass_r = false;
		}
		if (!$edit_pass_r) {
			?>
			<div class="alert alert-danger">
				<a href="#" class="close" data-dismiss="alert">&times;</a><strong>Warning!</strong> Error Occured Try Again. Contact Administrator</div> 
			<?php
		}else{
			$day=date('Y-m-d');
			$transact="USER PIN CHANGE";
			$field = array("user_id","trans","trans_by","shift"); 
			$data = array($user_id,"'".$transact."'",$user_id,"'".$day."'");  
			if (Insertdata("kp_user_hist", $field, $data)) { 
				?>
				<div class="alert alert-info">
					<a href="#" class="close" data-dismiss="alert">&times;</a><strong>Success!</strong> Password Edited SUccessfully.</div>
						<script type="text/javascript">
							window.setTimeout(function(){
								window.location = "../";
							}, 500);
						</script>
				<?php
			}else{
				?>
			<div class="alert alert-danger">
				<a href="#" class="close" data-dismiss="alert">&times;</a><strong>Warning!</strong> COULD NOT CREATE HISTORY</div> 
			<?php
			}
		}
	}
?>
