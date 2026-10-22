<?php
session_start();
include('inc/connection.php');
if (isset($_POST['login'])) {
	$kihingo = mysqli_real_escape_string($connection,$_POST['password']);
	$verify = mysqli_query($connection,"SELECT * FROM kp_users WHERE password='$kihingo'") or die("Could not verify the password");
	if (mysqli_num_rows($verify) < 1) {
		header("Location:index.php?msg=1");
	}else{
		$row = mysqli_fetch_assoc($verify);
		$work_status=$row['work_status'];
		$login_status=2;
		$user_id=$row['user_id'];
		$update_login_status = mysqli_query($connection,"UPDATE kp_users SET log_status=2 WHERE user_id='$user_id'") or die(mysqli_error($connection));
 
		if ($login_status!=2) {
		header("Location:index.php?msg=3");
		}else{
			if ($work_status!=2) {
				header("Location:index.php?msg=2");
			}else{
				$role_id = $row['role_id'];
				$get_role= mysqli_query($connection,"SELECT * FROM kp_roles WHERE role_id='$role_id'");
				if (mysqli_num_rows($verify) < 1) {
					header("Location:index.php?msg=7");
				}else{
					$rol = mysqli_fetch_assoc($get_role);
					$panel = $rol['role_access_panel'];
					$dash = $rol['access_dash'];
					
					$create_logger_details = mysqli_query($connection,"INSERT INTO kp_users_logger(log_user_id,shift,log_description) VALUES('$user_id',CURRENT_DATE,'ADMIN LOGIN' )") or die(mysqli_error($connection));
					if (!$create_logger_details) {
						header("Location:index.php?msg=5");
					}else{
						$update_login_status = mysqli_query($connection,"UPDATE kp_users SET log_status=1 WHERE user_id='$user_id'") or die(mysqli_error($connection));
						if (!$update_login_status) {
							header("Location:index.php?msg=5");
						}else{
							$_SESSION['user_id'] = $row['user_id'];
							$_SESSION['username'] = $row['username'];

							$get_logo = mysqli_query($connection,"SELECT image_path FROM  kp_settings_gen") or die("Could not get the gen details ".mysqli_error($connection));
							$gen = mysqli_fetch_assoc($get_logo);
							$_SESSION['cust_logo'] = $gen['image_path'];

							if ($panel==1) {
								$_SESSION['dash'] = $dash;
								$update_login_status = mysqli_query($connection,"UPDATE kp_users SET log_status=1 WHERE user_id='$user_id'") or die(mysqli_error($connection));
								header("Location:back/dash/$dash");
							}elseif ($panel == 2) {
								$update_login_status = mysqli_query($connection,"UPDATE kp_users SET log_status=1 WHERE user_id='$user_id'") or die(mysqli_error($connection));
                                header("Location:back/dash/admin_dash.php");
							}elseif ($panel == 3) {
								$update_login_status = mysqli_query($connection,"UPDATE kp_users SET log_status=1 WHERE user_id='$user_id'") or die(mysqli_error($connection));

								header("Location:back/dash/$dash");
							}
							
						}
					}
				}
				
			}
		}
	}
}else{
	header("Location:index.php?msg=6");	
}
// 	
// 	$verify = mysqli_query($connection,"SELECT * FROM kp_users WHERE user_password='$password'") or die("Could not verify the password");
// 	if (mysqli_num_rows($verify) < 1) {
// 		header("Location:index.php?msg=1");
// 	}elseif (mysqli_num_rows($verify) == 1) {
// 		$row = mysqli_fetch_assoc($verify);
// 		$work_status=$row['user_work_status'];
// 		$login_status=$row['user_log_status'];
// 		if ($login_status!=2) {
// 			header("Location:index.php?msg=3");
// 		}elseif ($work_status!=2) {
// 			header("Location:index.php?msg=2");
// 		}else{
// 			$role_id = $row['user_role_id'];
// 			$get_perm= mysqli_query($connection,"SELECT * FROM kp_roles WHERE role_id='$role_id'");
// 			$acc = mysqli_fetch_assoc($get_perm);
// 			$panel = $acc['role_access_panel'];
// 			$role_name = $acc['role_name'];
// 			$user_id=$row['user_id'];
// 			$update_login_status = mysqli_query($connection,"UPDATE kp_users SET user_log_status=1 WHERE user_id='$user_id'") or die(mysqli_error($connection));
// 			$create_logger_details = mysqli_query($connection,"INSERT INTO kp_users_logger(log_user_id,log_datime) VALUES('$staff_id',CURRENT_DATE )") or die(mysqli_error($connection));
// 			if (!$create_logger_details OR !$update_login_status) {
// 				echo "Failed";
// 			}else{
// 				if (!$create_logger_details OR !$update_login_status) {
// 					header("Location:index.php?msg=1");
// 				}else{

// 			$_SESSION['user_id'] = $row['user_id'];
// 			$_SESSION['username'] = $row['user_username'];

			 
			
// 		}
// 	}
// }
// }


?>