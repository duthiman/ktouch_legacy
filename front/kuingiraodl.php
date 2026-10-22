<?php 
session_start();
include('../inc/connection.php');
include('funcs.php');
//CHECK IF THE PASSCODE ENTERED IS EMPTY
if (empty($_POST['password'])) {
    ?>
    <div class="alert alert-danger">
        <h4><b>Empty Passcode!!...</b></h4>
    </div>
    <?php
}else{
//AFTER CHECKING IF PASSCODE IS EMPTY..
    $password = $_POST['password'];
    $check = "SELECT * FROM kp_users WHERE pin='$password'";
    $check_r = mysqli_query($connection,$check) or die("Could not check login");
    $check_num = mysqli_num_rows($check_r);
    //CHECK IF THE PASCODE MATCHES ANY IN THE DB
    if ($check_num == 1) {
        $st = mysqli_fetch_assoc($check_r);
        $name = $st['username'];
        $user_id = $st['user_id'];
        $job = $st['job'];
        $djob = $st['drole']; //DEFAULT JOB TO GET THE PEOPLE WHO CAN ONLY OPEN SHIFT
        $work_status = $st['work_status'];
        $login_status = 2;//$st['user_log_status'];
        $staff_shift_status = $st['shift_status'];
        $_SESSION['user_id']=$user_id; 
        $update_login_status = mysqli_query($connection,"UPDATE kp_users SET log_status=2 WHERE user_id='$user_id'") or die(mysqli_error($connection));
        $role_can_open_counters = function_exists('front_role_can_open_counters') ? front_role_can_open_counters($djob) : in_array((int)$djob, [1, 3], true);
        $role_pending_counters = function_exists('front_role_pending_counter_count') ? front_role_pending_counter_count($djob) : 0;
        $waiter_blocked = ((int)$djob === 2 && function_exists('front_waiter_blocked_by_counter_setup')) ? front_waiter_blocked_by_counter_setup() : false;
        //check login status
        if ($login_status==2) { 
            //check work status
            if ($work_status==2) { 
                //CHECK PERSONAL SHIFT
                $user_shift = user_shift($user_id);
                if ($user_shift==1) {

                    $shift = main_shift();
                    if ($shift!=0) {  
                        // $count_unpaid= mysqli_query($connection,"SELECT sale_id FROM kp_sales WHERE user_id='$user_id' AND  pay_status!=1 AND shift!='$shift'");
                        // if (mysqli_num_rows($count_unpaid) < 1) {
                            //CHECK OPEN COUNTER 
                            $open=open_counters();
                            if ($waiter_blocked) {
                                ?>
                                <div class="alert alert-success">
                                  <h4><b>WAIT FOR CASHIERS TO FINISH OPENING AND STOCKING THE COUNTERS!!!</b></h4>
                                </div>
                                <?php
                            }elseif ($role_can_open_counters && $role_pending_counters > 0) {
                                session($user_id,$name,$job,$shift,$djob);
                                $action="LOGGED IN TO OPEN COUNTERS";
                                logs($action,'logs'); 
                                ?> 
                                <script>
                                  window.location = "dash/dash.php";
                                </script>
                                <?php   
                            }else{
                                //COUNTER STOCK TAKE
                                $counter_take=counter_take();
                                if ($waiter_blocked) {
                                    ?>
                                    <div class="alert alert-danger">
                                       <h4><b>Wait for stock take to finish</b></h4>
                                    </div>
                                    <?php
                                }elseif ($counter_take==0 AND $job==1) {  
                                    //GET CASHIERS COUNTER 
                                    $status=user_counter_status($user_id);
                                    if ($status==0) {
                                        ?>    
                                        <div class="alert alert-success">
                                          <h4><b>Contact the Manager/Supervisor!!! Ask for a reassing of the counter</b></h4>
                                        </div> 
                                        <?php
                                    }elseif ($status==1) {
                                        thesion_init($user_id);
                                        session($user_id,$name,$job,$shift,$djob);
                                        $action="LOGGED IN";
                                        logs($action,'logs');
                                        ?>        
                                        <script>
                                           window.location = "dash/dash.php";
                                        </script>
                                        <?php
                                    }else{
                                        thesion_init($user_id);
                                        session($user_id,$name,$job,$shift,$djob);
                                        $action="LOGGED IN TO TAKE STOCK";
                                        logs($action,'logs');
                                        ?>        
                                        <script>
                                            window.location = "take/init_take.php";
                                        </script>
                                    <?php
                                    }
                                }elseif ($counter_take>0 AND $job==1) {  
                                    //GET CASHIERS COUNTER 
                                    $status=user_counter_status($user_id);
                                    if ($status==0) {
                                        ?>    
                                        <div class="alert alert-success">
                                          <h4><b>Contact the Manager/Supervisor!!! Ask for a reassing of the counter</b></h4>
                                        </div> 
                                        <?php
                                    }elseif ($status==1) {
                                        thesion_init($user_id);
                                        session($user_id,$name,$job,$shift,$djob);
                                        $action="LOGGED IN";
                                        logs($action,'logs');
                                        ?>        
                                        <script>
                                           window.location = "dash/dash.php";
                                        </script>
                                        <?php
                                    }else{
                                        thesion_init($user_id);
                                        session($user_id,$name,$job,$shift,$djob);
                                        $action="LOGGED IN TO TAKE STOCK";
                                        logs($action,'logs');
                                        ?>        
                                        <script>
                                            window.location = "take/init_take.php";
                                        </script>
                                    <?php
                                    }
                                }else{
                                    thesion_init($user_id);
                                    session($user_id,$name,$job,$shift,$djob);
                                    $action="LOGGED IN";
                                    logs($action,'logs');
                                    ?>        
                                    <script>
                                        window.location = "dash/dash.php";
                                    </script>
                                    <?php
                                } 
                            }
                        
                    }else{
                        if ($role_can_open_counters) { 
                            thesion_init($user_id);
                            session($user_id,$name,$job,$shift,$djob);
                            $action="LOGGED IN";
                            logs($action,'logs'); 
                            ?> 
                            <script>
                              window.location = "dash/dash.php";
                            </script>
                        <?php   
                        }else{
                            $action="TRIED LOGINNG IN WHILE SHIFT WAS CLOSED";
                            logs($action,'logs');  
                            ?> 
                            <div class="alert alert-success">
                                <h4><b>ASK CASHIER TO OPEN SHIFT!!!</b></h4>
                            </div>
                            <?php
                        }
                    }
                     
                }else{
                    //START OPENING SHIFT
                    if (open_user_shift($user_id)) {
                        $shift=date('Y-m-d');
                        //session($user_id,$name,$job,$shift,$djob);
                        $action="OPENED|-|-|SHIFT|".$shift;
                        logs($action,'logs');
                        ?>
                        <div class="alert alert-success">
                          <h4><b>Welcome to Your shift!!! <br> PRESS LOGIN AGAIN</b></h4>
                        </div>
                        <?php
                    }else{
                        ?>
                        <div class="alert alert-success">
                          <h4><b>Contact the administrator!!! Shift Opening Error</b></h4>
                        </div>
                        <?php   
                    }
                }
                //END PERSONAL SHIFT 
            }else{
                ?>
                <div class="alert alert-danger">
                    <h4><b>You are suspended, Cotact your adminstrator, to unsuspend!!!</b></h4>
                </div>
                <?php
            }
            //end check work status
        }else{
                ?>
            <div class="alert alert-danger">
              <h4><b>You are Logged in another system, Cotact your adminstrator!!!</b></h4>
            </div>
        <?php  
        }
    }else{
    //SHOW ERROR IF NO MATCH FOUND
    ?>
    <div class="alert alert-danger">
       <h4><b>Wrong Passcode!!.</b></h4>
    </div>
    <?php
    } 
}
?>
