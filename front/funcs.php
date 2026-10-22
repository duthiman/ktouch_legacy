<?php
$filename = "../../inc/gen_funcs.php";
if (file_exists("inc/gen_funcs.php")){
    include_once('inc/gen_funcs.php');
}elseif (file_exists("../inc/gen_funcs.php")){
    include_once('../inc/gen_funcs.php');
}elseif (file_exists($filename)){
    include_once('../../inc/gen_funcs.php');
}elseif (file_exists("../../../inc/gen_funcs.php")){
    include_once('../../../inc/gen_funcs.php');
}else{
    
}
if (!function_exists('session')) {
function session($user_id,$name,$job,$shift,$djob){
	global $connection;
    $_SESSION['user_id'] = $user_id; 
    $_SESSION['name'] = $name;
    $_SESSION['job'] = $job;
    $_SESSION['djob'] = $djob;
    $_SESSION['shift'] = $shift;
    $update_login_status = mysqli_query($connection,"UPDATE kp_users SET log_status=1 WHERE user_id='$user_id'") or die(mysqli_error($connection));

}
}
if (!function_exists('main_shift')) {
function main_shift(){
    global $connection;
    $get_shift = mysqli_query($connection,"SELECT * FROM kp_shift") or die("Could not get the shift".mysqli_error($connection));
    $sh=mysqli_num_rows($get_shift);
    if ($sh<1) {
        $shift=0;
    }else{
        $gs = mysqli_fetch_assoc($get_shift);
        $shift=$gs['shift'];
    }
    return $shift;
}
}

if (!function_exists('user_shift')) {
function user_shift($user_id){
    global $connection;
    $get_shift_status = mysqli_query($connection,"SELECT shift_status FROM kp_users WHERE user_id='$user_id'") or die("Could not get the shift status".mysqli_error($connection));
    $gs = mysqli_fetch_assoc($get_shift_status);
    $shift_status=$gs['shift_status'];
    return $shift_status;
}
}

if (!function_exists('open_user_shift')) {
function open_user_shift($user_id){
    global $connection;
    $cashier= mysqli_query($connection,"UPDATE kp_users SET shift_status=1,cleared=2 WHERE user_id='$user_id'");
    if($cashier){
        $shift_hist= mysqli_query($connection,"INSERT INTO kp_user_shift_hist(user_id,shift,opening) VALUES('$user_id',CURRENT_DATE,CURRENT_TIME)");
        if ($shift_hist) {
            return true;
        }else{
            $cashier= mysqli_query($connection,"UPDATE kp_users SET shift_status=2,cleared=2 WHERE user_id='$user_id'");
            return false;
        }
    }else{
        return false;
    }
    
}
}
if (!function_exists('open_counters')) {
function open_counters(){
    global $connection;
    $get_counter = mysqli_query($connection,"SELECT * FROM kp_counters WHERE counter_status=1") or die("Could not get the open counters".mysqli_error($connection));
    $open=mysqli_num_rows($get_counter);
    return $open;
}
}
if (!function_exists('counter_take')) {
function counter_take(){
    global $connection;
    $get_counter_take = mysqli_query($connection,"SELECT * FROM kp_counters WHERE take_status=1 AND counter_status=1") or die("Could not get the shift".mysqli_error($connection));
    $sh=mysqli_num_rows($get_counter_take);
    if ($sh<1) {
        $status=0;
    }else{ 
        $status=$sh;
    }
    return $status;
} 
}

function front_counter_scope_where($drole, $alias = 'kp_counters') {
    $alias = trim((string)$alias);
    if ($alias === '') {
        $alias = 'kp_counters';
    }
    $prefix = $alias . '.';
    $drole = (int)$drole;
    if ($drole === 3) {
        return $prefix . "dpt=2";
    }
    if ($drole === 1) {
        return "(" . $prefix . "dpt IS NULL OR " . $prefix . "dpt<>2)";
    }
    return "1=0";
}

function front_role_can_open_counters($drole) {
    $drole = (int)$drole;
    return in_array($drole, [1, 3], true);
}

function front_role_pending_counter_count($drole) {
    global $connection;
    if (!front_role_can_open_counters($drole)) {
        return 0;
    }
    $scope = front_counter_scope_where($drole);
    return (int)column_count('kp_counters', 'cntr_id', $scope . " AND del_status=2 AND counter_status<>1");
}

function front_role_open_counter_count($drole) {
    global $connection;
    if (!front_role_can_open_counters($drole)) {
        return 0;
    }
    $scope = front_counter_scope_where($drole);
    return (int)column_count('kp_counters', 'cntr_id', $scope . " AND del_status=2 AND counter_status=1");
}

function front_waiter_blocked_by_counter_setup() {
    global $connection;
    $service_scope = front_counter_scope_where(1);
    $ready_open = (int)column_count('kp_counters', 'cntr_id', $service_scope . " AND del_status=2 AND counter_status=1 AND take_status=1");
    if ($ready_open > 0) {
        return false;
    }
    $unready_or_closed = (int)column_count('kp_counters', 'cntr_id', $service_scope . " AND del_status=2 AND (counter_status<>1 OR take_status<>1)");
    return $unready_or_closed > 0;
}


if (!function_exists('thesion_init')) {
function thesion_init($user_id){
    global $connection; 
    $mac_address=get_mac(); 
    $create_thesion= mysqli_query($connection,"INSERT INTO kp_thesion_front(user_id,comp_address,shift) VALUES('$user_id','$mac_address',CURRENT_DATE) ON DUPLICATE KEY  UPDATE  comp_address='$mac_address'") or die(mysqli_error($connection));
    return true;
}
}

 ?>
