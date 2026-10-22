<?php
include_once('../../inc/connection.php');
include_once('../../inc/gen_funcs.php');

if (!function_exists('pass_act_user_column_exists')) {
    function pass_act_user_column_exists($connection, $column) {
        $column = mysqli_real_escape_string($connection, $column);
        $res = mysqli_query($connection, "SHOW COLUMNS FROM kp_users LIKE '$column'");
        return $res && mysqli_num_rows($res) > 0;
    }
}

if (!function_exists('pass_act_history_column_exists')) {
    function pass_act_history_column_exists($connection, $column) {
        $column = mysqli_real_escape_string($connection, $column);
        $res = mysqli_query($connection, "SHOW COLUMNS FROM kp_user_hist LIKE '$column'");
        return $res && mysqli_num_rows($res) > 0;
    }
}

if (!function_exists('pass_act_ensure_user_history_schema')) {
    function pass_act_ensure_user_history_schema($connection) {
        mysqli_query($connection, "CREATE TABLE IF NOT EXISTS kp_user_hist (
            hist_id INT NOT NULL AUTO_INCREMENT,
            user_id INT NOT NULL DEFAULT 0,
            transaction VARCHAR(190) NOT NULL DEFAULT '',
            affected_user_id INT NOT NULL DEFAULT 0,
            day DATE NULL,
            created_at TIMESTAMP NOT NULL DEFAULT CURRENT_TIMESTAMP,
            PRIMARY KEY (hist_id)
        )");

        if (!pass_act_history_column_exists($connection, 'hist_id')) {
            return;
        }
        if (!pass_act_history_column_exists($connection, 'user_id')) {
            mysqli_query($connection, "ALTER TABLE kp_user_hist ADD COLUMN user_id INT NOT NULL DEFAULT 0");
        }
        if (!pass_act_history_column_exists($connection, 'transaction')) {
            mysqli_query($connection, "ALTER TABLE kp_user_hist ADD COLUMN transaction VARCHAR(190) NOT NULL DEFAULT ''");
        }
        if (!pass_act_history_column_exists($connection, 'affected_user_id')) {
            mysqli_query($connection, "ALTER TABLE kp_user_hist ADD COLUMN affected_user_id INT NOT NULL DEFAULT 0");
        }
        if (!pass_act_history_column_exists($connection, 'day')) {
            mysqli_query($connection, "ALTER TABLE kp_user_hist ADD COLUMN day DATE NULL");
        }
        if (!pass_act_history_column_exists($connection, 'created_at')) {
            mysqli_query($connection, "ALTER TABLE kp_user_hist ADD COLUMN created_at TIMESTAMP NOT NULL DEFAULT CURRENT_TIMESTAMP");
        }
    }
}

if (!function_exists('pass_act_log_user_history')) {
    function pass_act_log_user_history($connection, $actor_user_id, $affected_user_id, $transaction_text, $day) {
        pass_act_ensure_user_history_schema($connection);

        $columns = array();
        $values = array();

        if (pass_act_history_column_exists($connection, 'user_id')) {
            $columns[] = 'user_id';
            $values[] = "'" . (int)$actor_user_id . "'";
        }
        if (pass_act_history_column_exists($connection, 'transaction')) {
            $columns[] = 'transaction';
            $values[] = "'" . mysqli_real_escape_string($connection, $transaction_text) . "'";
        } elseif (pass_act_history_column_exists($connection, 'activity')) {
            $columns[] = 'activity';
            $values[] = "'" . mysqli_real_escape_string($connection, $transaction_text) . "'";
        } elseif (pass_act_history_column_exists($connection, 'trans')) {
            $columns[] = 'trans';
            $values[] = "'" . mysqli_real_escape_string($connection, $transaction_text) . "'";
        }
        if (pass_act_history_column_exists($connection, 'affected_user_id')) {
            $columns[] = 'affected_user_id';
            $values[] = "'" . (int)$affected_user_id . "'";
        } elseif (pass_act_history_column_exists($connection, 'staff_id')) {
            $columns[] = 'staff_id';
            $values[] = "'" . (int)$affected_user_id . "'";
        }
        if (pass_act_history_column_exists($connection, 'day')) {
            $columns[] = 'day';
            $values[] = "'" . mysqli_real_escape_string($connection, $day) . "'";
        } elseif (pass_act_history_column_exists($connection, 'date')) {
            $columns[] = 'date';
            $values[] = "'" . mysqli_real_escape_string($connection, $day) . "'";
        }

        if (count($columns) < 1) {
            return false;
        }

        $sql = "INSERT INTO kp_user_hist(" . implode(', ', $columns) . ") VALUES(" . implode(', ', $values) . ")";
        try {
            return mysqli_query($connection, $sql);
        } catch (Throwable $e) {
            error_log('User history log skipped: ' . $e->getMessage());
            return false;
        }
    }
}

$user_id = (int)($_REQUEST['user_id'] ?? 0);
$confirm = trim((string)($_REQUEST['confirm'] ?? ''));
$st = trim((string)($_REQUEST['st'] ?? ''));

if ($user_id < 1 || $confirm === '') {
    ?>
    <div class="alert alert-danger">
        <a href="#" class="close" data-dismiss="alert">&times;</a><strong>Warning!</strong> Missing user or value.
    </div>
    <?php
    exit;
}

$column = '';
if ($st === 'pin') {
    if (pass_act_user_column_exists($connection, 'user_pin')) {
        $column = 'user_pin';
    } elseif (pass_act_user_column_exists($connection, 'pin')) {
        $column = 'pin';
    }
} elseif ($st === 'pass') {
    if (pass_act_user_column_exists($connection, 'user_pass')) {
        $column = 'user_pass';
    } elseif (pass_act_user_column_exists($connection, 'password')) {
        $column = 'password';
    } elseif (pass_act_user_column_exists($connection, 'pass')) {
        $column = 'pass';
    }
}

if ($column === '') {
    ?>
    <div class="alert alert-danger">
        <a href="#" class="close" data-dismiss="alert">&times;</a><strong>Warning!</strong> Could not find the correct user credential column on this system.
    </div>
    <?php
    exit;
}

$value = mysqli_real_escape_string($connection, $confirm);
$update_sql = "UPDATE kp_users SET $column='$value' WHERE user_id='$user_id'";
$edit_pass_r = mysqli_query($connection, $update_sql);

if (!$edit_pass_r) {
    ?>
    <div class="alert alert-danger">
        <a href="#" class="close" data-dismiss="alert">&times;</a><strong>Warning!</strong> Error occurred. Try again.
    </div>
    <script type="text/javascript">
        window.setTimeout(function () {
            window.location.href = 'user.php';
        }, 100);
    </script>
    <?php
    exit;
}

$day = date('Y-m-d');
$transact = ($st === 'pin') ? "USER PIN CHANGE" : "USER PASSWORD CHANGE";
pass_act_log_user_history($connection, $user_id, $user_id, $transact, $day);
?>
<div class="alert alert-info">
    <a href="#" class="close" data-dismiss="alert">&times;</a><strong>Success!</strong> <?php echo $st === 'pin' ? 'PIN' : 'Password'; ?> updated successfully.
</div>
<script type="text/javascript">
    window.setTimeout(function () {
        window.location.href = 'user.php';
    }, 100);
</script>
