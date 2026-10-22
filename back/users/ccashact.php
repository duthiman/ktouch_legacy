<?php
session_start();
include('../../inc/connection.php'); 
include('../../inc/gen_funcs.php'); 
	$transby=(int)$_SESSION['user_id'];
	$user_id = isset($_REQUEST['user_id']) ? (int)$_REQUEST['user_id'] : 0;
    $cntr_id = isset($_REQUEST['cntr_id']) ? (int)$_REQUEST['cntr_id'] : 0;
	$shift=shift();

    if ($user_id < 1) {
        ?>
        <div class="alert alert-danger">
            <a href="#" class="close" data-dismiss="alert">&times;</a>
            <strong>Warning!</strong> Select a cashier first.
        </div>
        <?php
        exit;
    }

    if ($cntr_id > 0) {
        mysqli_begin_transaction($connection);

        $selectedCounterQ = mysqli_query($connection, "SELECT * FROM kp_counters WHERE cntr_id='$cntr_id' LIMIT 1") or die(mysqli_error($connection));
        $selectedCounter = ($selectedCounterQ && mysqli_num_rows($selectedCounterQ) > 0) ? mysqli_fetch_assoc($selectedCounterQ) : null;
        if (!$selectedCounter) {
            mysqli_rollback($connection);
            ?>
            <div class="alert alert-danger">
                <a href="#" class="close" data-dismiss="alert">&times;</a>
                <strong>Warning!</strong> Counter not found.
            </div>
            <?php
            exit;
        }

        $resetCounterUsers = mysqli_query($connection, "UPDATE kp_users SET counter_status=2,counter=0 WHERE counter='$cntr_id' AND user_id!='$user_id'") or die(mysqli_error($connection));
        $resetUserOldCounter = mysqli_query($connection, "UPDATE kp_counters SET user_id=0,counter_status=2,close_status=2 WHERE user_id='$user_id' AND cntr_id!='$cntr_id'") or die(mysqli_error($connection));
        $resetUserState = mysqli_query($connection, "UPDATE kp_users SET counter=0,counter_status=2 WHERE user_id='$user_id' AND counter!='$cntr_id'") or die(mysqli_error($connection));
        $assignUser = mysqli_query($connection, "UPDATE kp_users SET job=1,shift_status=1,counter_status=1,counter='$cntr_id',cleared=2 WHERE user_id='$user_id'") or die(mysqli_error($connection));
        $assignCounter = mysqli_query($connection, "UPDATE kp_counters SET user_id='$user_id',counter_status=1,close_status=3 WHERE cntr_id='$cntr_id'") or die(mysqli_error($connection));

        if ($resetCounterUsers && $resetUserOldCounter && $resetUserState && $assignUser && $assignCounter) {
            mysqli_commit($connection);
        } else {
            mysqli_rollback($connection);
            ?>
            <div class="alert alert-danger">
                <a href="#" class="close" data-dismiss="alert">&times;</a>
                <strong>Warning!</strong> Could not change the counter cashier.
            </div>
            <?php
            exit;
        }

        $trans = "ASSIGNED COUNTER CASHIER|" . $cntr_id;
        mysqli_query($connection, "INSERT INTO kp_user_hist (user_id,trans,trans_by,shift) VALUES ('$user_id','$trans','$transby','$shift')") or die(mysqli_error($connection));
    } else {
	    $edit_user = "UPDATE kp_users SET role_id=6 WHERE role_id=3" ;
        $edit_user_r = mysqli_query($connection,$edit_user) or die(mysqli_error($connection));

	    $edit_user = "UPDATE kp_users SET role_id=3 WHERE user_id='$user_id'" ;
        $edit_user_r = mysqli_query($connection,$edit_user) or die(mysqli_error($connection));

	    $trans="ASSIGNED CASHIER";
        $edit_user = "INSERT INTO kp_user_hist (user_id,trans,trans_by,shift) VALUES ('$user_id','$trans','$transby','$shift')" ;
        $edit_user_r = mysqli_query($connection,$edit_user) or die(mysqli_error($connection));
    }
  ?>
	 	<div class="alert alert-success">
	 		<a href="#" class="close" data-dismiss="alert">&times;</a>
	 		<strong>Success!</strong> Cashier changed successfully.
	 	</div>
	 	<script type="text/javascript">
	 		window.setTimeout(function(){
	 			window.location.reload();
	 		}, 300);
	 	</script>

	<?php 
?>
