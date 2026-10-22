<?php
include('../../inc/connection.php');
if (isset($_REQUEST['staff'])) {
	$staff = $_REQUEST['staff']; 
	$username = $_REQUEST['username'];
	$cpass = $_REQUEST['cpass'];
	$cpin = $_REQUEST['cpin'];
	$roles = $_REQUEST['role']; 
	if (empty($username)) {
		?>
		<div class="alert alert-danger">
			<a href="#" class="close" data-dismiss="alert">&times;</a>
			<strong>Warning!</strong> Username cannot be empty.
		</div> 
	<?php
	}elseif (empty($cpass) && empty($cpin)) {
	?>
		<div class="alert alert-danger">
			<a href="#" class="close" data-dismiss="alert">&times;</a>
			<strong>Warning!</strong> Passwords cannot be empty.
		</div>
		<!-- <script type="text/javascript">
			window.setTimeout(function(){
				window.location.href = 'back/users/user.php';
			}, 100);
		</script> -->
	<?php
	}elseif (empty($staff)  ) {
		?>
		<div class="alert alert-danger">
			<a href="#" class="close" data-dismiss="alert">&times;</a>
			<strong>Warning!</strong> Select Username.
		</div>
		<!-- <script type="text/javascript">
			window.setTimeout(function(){
				window.location.href = 'back/users/user.php';
			}, 100);
		</script> -->
	<?php
	}else{
		if (empty($cpass) ) {
			$line="pin";
			$drole = 1;
			$var=$cpin;
			$verify = mysqli_query($connection,"SELECT * FROM kp_users WHERE staff_id='$staff'") or die("Could not verify the password");
			$num=mysqli_num_rows($verify);
			$verify_pin = mysqli_query($connection,"SELECT * FROM kp_users WHERE pin='$cpin'") or die("Could not verify the password");
			$num_pin=mysqli_num_rows($verify_pin);
		}elseif (empty($cpin)) {
			$line="password";
			$drole = $_REQUEST['drole'];
			$var=$cpass;
			$verify = mysqli_query($connection,"SELECT * FROM kp_users WHERE staff_id='$staff'") or die("Could not verify the password");
			$num=mysqli_num_rows($verify);
			$verify_pin = mysqli_query($connection,"SELECT * FROM kp_users WHERE password='$cpass'") or die("Could not verify the password");
			$num_pin=mysqli_num_rows($verify_pin);
		}else{
			$line="password,pin";
			$drole = $_REQUEST['drole'];
			$var=$cpass."','".$cpin;
			$verify = mysqli_query($connection,"SELECT * FROM kp_users WHERE staff_id='$staff'") or die("Could not verify the password");
			$num=mysqli_num_rows($verify);
			$verify_pin = mysqli_query($connection,"SELECT * FROM kp_users WHERE password='$cpass' OR pin='$cpin'") or die("Could not verify the password");
			$num_pin=mysqli_num_rows($verify_pin);
		}

		if ($num_pin==0) {
		 	if ($num==0) {
				$up_staff= "UPDATE kp_staff SET user_status=1 WHERE staff_id='$staff'";
				$up_staff_r = mysqli_query($connection,$up_staff) or die(mysqli_error($connection));
				if (!$up_staff_r) {
				?>
				<div class="alert alert-danger">
					<a href="#" class="close" data-dismiss="alert">&times;</a>
					<strong>Danger!</strong> Contact Administrator...
				</div>
				<?php 
				}else{ 
					echo "INSERT INTO kp_users(staff_id,user_username,$line,user_role_id,drole) VALUES('$staff','$username','$var','$roles','$drole')";
					$new_user = "INSERT INTO kp_users(staff_id,username,$line,role_id,drole) VALUES('$staff','$username','$var','$roles','$drole')";
					$new_user_r = mysqli_query($connection,$new_user) or die("Could not add user ".mysqli_error($connection));
					if (!$new_user_r) {
					?>
						<div class="alert alert-danger">
							<a href="#" class="close" data-dismiss="alert">&times;</a>
							<strong>Danger!</strong> Contact Administrator...
						</div>
					<?php 
					}else{
					?>
						<div class="alert alert-success">
							<a href="#" class="close" data-dismiss="alert">&times;</a><strong>Success!</strong>Customer Succesfully Added.
						</div>
						<script type="text/javascript">
							window.setTimeout(function(){
								window.location.href = '../users/user.php';
							}, 100);
						</script>
					<?php 	
					}
				}
			}else{
				$up_staff= "UPDATE kp_staff SET user_status=1 WHERE staff_id='$staff'";
				$up_staff_r = mysqli_query($connection,$up_staff) or die(mysqli_error($connection));
				if (!$up_staff_r) {
			?>
				<div class="alert alert-danger">
					<a href="#" class="close" data-dismiss="alert">&times;</a>
					<strong>Danger!</strong> Contact Administrator...
				</div>
				<?php 
				}else{
					?>
					<div class="alert alert-warning">
						<a href="#" class="close" data-dismiss="alert">&times;</a><strong>Warning!</strong>Staff Already a User.
					</div>
					<script type="text/javascript">
						window.setTimeout(function(){
							window.location.href = '../users/user.php';
						}, 100);
					</script>
					<?php 	
				}
			}
		}else{
			?>
			<div class="alert alert-warning">
				<a href="#" class="close" data-dismiss="alert">&times;</a><strong>Warning!</strong>Pin Already Taken.
			</div> 
			<?php 	
		}
	}
}
?>