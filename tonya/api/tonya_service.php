<?php
if (!function_exists('tonya_service_html_message')) {
    function tonya_service_html_message($type, $message) {
        return '<div class="alert alert-' . htmlspecialchars($type, ENT_QUOTES) . '"><h4><b>' . htmlspecialchars($message, ENT_QUOTES) . '</b></h4></div>';
    }
}
if (!function_exists('tonya_service_result')) {
    function tonya_service_result($ok, $message, $redirect = '', $html = '') {
        return ['ok' => (bool)$ok, 'message' => (string)$message, 'redirect' => (string)$redirect, 'html' => $html ?: tonya_service_html_message($ok ? 'success' : 'danger', $message)];
    }
}
if (!function_exists('tonya_service_start_session')) {
    function tonya_service_start_session($row, $shift = '') {
        tonya_service_session($row, $shift);
        $_SESSION['cust_logo'] = tonya_repo_logo();
    }
}
if (!function_exists('tonya_service_session')) {
    function tonya_service_session($row, $shift = '') {
        $_SESSION['user_id'] = (int)($row['user_id'] ?? 0);
        $_SESSION['name'] = (string)($row['username'] ?? '');
        $_SESSION['username'] = (string)($row['username'] ?? '');
        $_SESSION['job'] = (int)($row['job'] ?? 0);
        $_SESSION['djob'] = (int)($row['drole'] ?? 0);
        $_SESSION['shift'] = (string)$shift;
        tonya_repo_set_login_status($_SESSION['user_id'], 1);
        return true;
    }
}
if (!function_exists('tonya_service_main_shift')) {
    function tonya_service_main_shift() {
        return tonya_repo_main_shift();
    }
}
if (!function_exists('tonya_service_open_user_shift')) {
    function tonya_service_open_user_shift($user_id) {
        return tonya_repo_open_user_shift($user_id);
    }
}
if (!function_exists('tonya_service_front_login')) {
    function tonya_service_front_login($password) {
        $password = trim((string)$password);
        if ($password === '') return tonya_service_result(false, 'Empty Passcode!!...');
        $row = tonya_repo_find_user('pin', $password);
        if (!$row) return tonya_service_result(false, 'Wrong Passcode!!.');
        $user_id = (int)$row['user_id'];
        $job = (int)($row['job'] ?? 0);
        $djob = (int)($row['drole'] ?? 0);
        if ((int)($row['work_status'] ?? 0) !== 2) return tonya_service_result(false, 'You are suspended, contact your administrator to unsuspend you.');
        $role_can_open = function_exists('front_role_can_open_counters') ? front_role_can_open_counters($djob) : in_array($djob, [1, 3], true);
        $pending = function_exists('front_role_pending_counter_count') ? front_role_pending_counter_count($djob) : 0;
        if (function_exists('front_waiter_blocked_by_counter_setup') && $djob === 2 && front_waiter_blocked_by_counter_setup()) return tonya_service_result(false, 'WAIT FOR CASHIERS TO FINISH OPENING AND STOCKING THE COUNTERS!!!');
        $shift = tonya_service_main_shift();
        if (function_exists('user_shift') && user_shift($user_id) != 1) {
            if (tonya_service_open_user_shift($user_id)) return tonya_service_result(false, 'Welcome to your shift. Press login again.');
            return tonya_service_result(false, 'Contact the administrator. Shift opening error.');
        }
        if ((int)$shift === 0 && !$role_can_open) return tonya_service_result(false, 'ASK CASHIER TO OPEN SHIFT!!!');
        tonya_service_start_session($row, $shift);
        tonya_repo_set_login_status($user_id, 2);
        tonya_repo_log_login($user_id, $role_can_open && $pending > 0 ? 'LOGGED IN TO OPEN COUNTERS' : 'LOGGED IN');
        if ($job === 1) {
            $counter_state = tonya_repo_user_counter_state($user_id);
            if ($counter_state['counter_id'] < 1) return tonya_service_result(false, 'Contact the Manager/Supervisor. Ask for a reassignment of the counter.');
            if ($counter_state['take_status'] === 3) {
                if ($counter_state['counter_status'] !== 1) return tonya_service_result(true, 'Your assigned counter is not open. Open a counter to continue.', 'dash/', '<script>window.location="dash/";</script>');
                if (function_exists('thesion_init')) thesion_init($user_id);
                return tonya_service_result(true, 'Logged in to continue stock take.', 'take/init_take.php', '<script>window.location="take/init_take.php";</script>');
            }
        }
        if ($role_can_open && $pending > 0) return tonya_service_result(true, 'Logged in.', 'dash', '<script>window.location="dash";</script>');
        if (function_exists('thesion_init')) thesion_init($user_id);
        return tonya_service_result(true, 'Logged in.', 'dash', '<script>window.location="dash";</script>');
    }
}
if (!function_exists('tonya_service_admin_login')) {
    function tonya_service_admin_login($password) {
        $row = tonya_repo_find_user('password', $password);
        if (!$row) return tonya_service_result(false, 'Invalid password.', 'index.php?msg=1');
        if ((int)($row['work_status'] ?? 0) !== 2) return tonya_service_result(false, 'Your account is suspended.', 'index.php?msg=2');
        $role = tonya_repo_role((int)($row['role_id'] ?? 0));
        if (!$role) return tonya_service_result(false, 'Your role could not be loaded.', 'index.php?msg=7');
        $user_id = (int)$row['user_id'];
        tonya_service_start_session($row, '');
        $_SESSION['dash'] = (string)($role['access_dash'] ?? 'index.php');
        tonya_repo_set_login_status($user_id, 1);
        tonya_repo_log_login($user_id, 'ADMIN LOGIN');
        $panel = (int)($role['role_access_panel'] ?? 0);
        $redirect = $panel === 2 ? 'back/dash/admin_dash.php' : 'back/dash/' . $_SESSION['dash'];
        return tonya_service_result(true, 'Logged in.', $redirect);
    }
}
