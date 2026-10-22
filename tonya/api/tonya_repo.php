<?php
if (!function_exists('tonya_repo_find_user')) {
    function tonya_repo_find_user($field, $value) {
        global $connection;
        $field = $field === 'password' ? 'password' : 'pin';
        $value = mysqli_real_escape_string($connection, (string)$value);
        $result = mysqli_query($connection, "SELECT * FROM kp_users WHERE {$field}='{$value}' LIMIT 1");
        return $result ? mysqli_fetch_assoc($result) : null;
    }
}
if (!function_exists('tonya_repo_set_login_status')) {
    function tonya_repo_set_login_status($user_id, $status) {
        global $connection;
        return mysqli_query($connection, "UPDATE kp_users SET log_status='" . (int)$status . "' WHERE user_id='" . (int)$user_id . "'");
    }
}
if (!function_exists('tonya_repo_log_login')) {
    function tonya_repo_log_login($user_id, $description) {
        global $connection;
        $description = mysqli_real_escape_string($connection, (string)$description);
        return mysqli_query($connection, "INSERT INTO kp_users_logger(log_user_id,shift,log_description) VALUES('" . (int)$user_id . "',CURRENT_DATE,'{$description}')");
    }
}
if (!function_exists('tonya_repo_logo')) {
    function tonya_repo_logo() {
        global $connection;
        $result = mysqli_query($connection, "SELECT image_path FROM kp_settings_gen LIMIT 1");
        $row = $result ? mysqli_fetch_assoc($result) : [];
        return (string)($row['image_path'] ?? '');
    }
}
if (!function_exists('tonya_repo_role')) {
    function tonya_repo_role($role_id) {
        global $connection;
        $result = mysqli_query($connection, "SELECT * FROM kp_roles WHERE role_id='" . (int)$role_id . "' LIMIT 1");
        return $result ? mysqli_fetch_assoc($result) : null;
    }
}
if (!function_exists('tonya_repo_main_shift')) {
    function tonya_repo_main_shift() {
        global $connection;
        $result = mysqli_query($connection, "SELECT shift FROM kp_shift LIMIT 1");
        $row = $result ? mysqli_fetch_assoc($result) : null;
        return $row ? (string)($row['shift'] ?? '') : 0;
    }
}
if (!function_exists('tonya_repo_open_user_shift')) {
    function tonya_repo_open_user_shift($user_id) {
        global $connection;
        $user_id = (int)$user_id;
        if ($user_id < 1) return false;
        mysqli_begin_transaction($connection);
        $updated = mysqli_query($connection, "UPDATE kp_users SET shift_status=1, cleared=2 WHERE user_id='{$user_id}'");
        $history = $updated ? mysqli_query($connection, "INSERT INTO kp_user_shift_hist(user_id,shift,opening) VALUES('{$user_id}',CURRENT_DATE,CURRENT_TIME)") : false;
        if ($updated && $history) { mysqli_commit($connection); return true; }
        mysqli_rollback($connection);
        return false;
    }
}
if (!function_exists('tonya_repo_user_counter_state')) {
    function tonya_repo_user_counter_state($user_id) {
        global $connection;
        $user_id = (int)$user_id;
        if ($user_id < 1) return ['counter_id' => 0, 'take_status' => 0, 'counter_status' => 0];
        $result = mysqli_query($connection, "SELECT u.counter AS counter_id, c.take_status, c.counter_status FROM kp_users u LEFT JOIN kp_counters c ON c.cntr_id=u.counter WHERE u.user_id='{$user_id}' LIMIT 1");
        $row = $result ? mysqli_fetch_assoc($result) : null;
        return [
            'counter_id' => (int)($row['counter_id'] ?? 0),
            'take_status' => (int)($row['take_status'] ?? 0),
            'counter_status' => (int)($row['counter_status'] ?? 0)
        ];
    }
}
