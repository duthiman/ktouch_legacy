<?php
session_start();
include('../../inc/connection.php');
if (isset($_REQUEST['user_id'])) {
    $user_id = $_REQUEST['user_id'];
    $reason = $_REQUEST['reason'];

    $suspend = mysqli_query($connection,"INSERT INTO kp_displinary(user_id,reason,day) VALUES('$user_id','$reason',current_date)") or die("Could not suspend user");
    $update_susp = mysqli_query($connection,"UPDATE kp_users SET user_work_status=1 WHERE user_id='$user_id'") or die("Could not update users");


    if (!$suspend && !$update_susp) {
        ?>
        <div class="alert alert-danger"><a href="#" class="close" data-dismiss="alert">&times;</a><strong>Warning!</strong> Error Deleting Customer Details.</div>
        <script type="text/javascript">
            window.setTimeout(function(){
                window.location.href = 'user.php';
            }, 50);
        </script>

        <?php
    }else{
        ?>
        <div class="alert alert-Success"><a href="#" class="close" data-dismiss="alert">&times;</a><strong>Success!</strong> User has Been suspended.</div>
        <script type="text/javascript">
            window.setTimeout(function(){

                window.location.href = 'user.php';

            }, 50);
        </script>

        <?php
    }
}



?>