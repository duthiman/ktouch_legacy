<?php
session_start();
include('../../inc/connection.php');
include('../../inc/gen_funcs.php');

ensure_token_system();

$token_enabled = isset($_POST['token_enabled']) ? (int)$_POST['token_enabled'] : 2;
$token_mode = isset($_POST['token_mode']) ? (int)$_POST['token_mode'] : 1;
$token_piece_value = isset($_POST['token_piece_value']) ? (float)$_POST['token_piece_value'] : 0;
$token_percent_value = isset($_POST['token_percent_value']) ? (float)$_POST['token_percent_value'] : 0;
$token_brackets = trim((string)($_POST['token_brackets'] ?? ''));
$range_mins = isset($_POST['range_min']) && is_array($_POST['range_min']) ? $_POST['range_min'] : array();
$range_maxs = isset($_POST['range_max']) && is_array($_POST['range_max']) ? $_POST['range_max'] : array();
$range_tokens = isset($_POST['range_token']) && is_array($_POST['range_token']) ? $_POST['range_token'] : array();

if ($token_enabled !== 1) {
    $token_enabled = 2;
}
if (!in_array($token_mode, array(1, 2, 3), true)) {
    $token_mode = 1;
}
if ($token_piece_value < 0) {
    $token_piece_value = 0;
}
if ($token_percent_value < 0) {
    $token_percent_value = 0;
}

if ($token_mode === 2 && !empty($range_mins)) {
    $rebuilt = array();
    $row_total = max(count($range_mins), count($range_maxs), count($range_tokens));
    for ($i = 0; $i < $row_total; $i++) {
        $min_raw = trim((string)($range_mins[$i] ?? ''));
        $max_raw = trim((string)($range_maxs[$i] ?? ''));
        $token_raw = trim((string)($range_tokens[$i] ?? ''));

        if ($min_raw === '' && $max_raw === '' && $token_raw === '') {
            continue;
        }

        if (!is_numeric($min_raw) || !is_numeric($token_raw)) {
            continue;
        }

        $min = round((float)$min_raw, 2);
        $token = round((float)$token_raw, 2);
        $max = $max_raw === '' ? '' : (is_numeric($max_raw) ? round((float)$max_raw, 2) : '');

        if ($min < 0 || $token < 0) {
            continue;
        }

        if ($max !== '' && $max < $min) {
            continue;
        }

        $rebuilt[] = $min . ',' . $max . ',' . $token;
    }

    if (!empty($rebuilt)) {
        $token_brackets = implode("\n", $rebuilt);
    }
}

$token_brackets_safe = mysqli_real_escape_string($connection, $token_brackets);
$check = mysqli_query($connection, "SELECT print_doc FROM kp_settings_print WHERE print_doc=1 LIMIT 1");
if ($check && mysqli_num_rows($check) > 0) {
    $sql = "UPDATE kp_settings_print SET
        token_enabled='$token_enabled',
        token_mode='$token_mode',
        token_piece_value='$token_piece_value',
        token_percent_value='$token_percent_value',
        token_brackets='$token_brackets_safe'
        WHERE print_doc=1";
} else {
    $sql = "INSERT INTO kp_settings_print(
        print_doc, token_enabled, token_mode, token_piece_value, token_percent_value, token_brackets
    ) VALUES(
        1, '$token_enabled', '$token_mode', '$token_piece_value', '$token_percent_value', '$token_brackets_safe'
    )";
}

$result = mysqli_query($connection, $sql);
if (!$result) {
?>
<div class="alert alert-danger">
  <a href="#" class="close" data-dismiss="alert">&times;</a>
  <strong>Failed</strong> <?php echo htmlspecialchars(mysqli_error($connection)); ?>
</div>
<?php
exit;
}
?>
<div class="alert alert-success">
  <a href="#" class="close" data-dismiss="alert">&times;</a>
  <strong>Success!</strong> Waiter token settings updated.
</div>
