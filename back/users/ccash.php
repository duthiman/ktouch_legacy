<?php
include("../../inc/connection.php");
include("../../inc/gen_funcs.php");

$id = isset($_REQUEST['id']) ? (int)$_REQUEST['id'] : 0;
$counter = null;

if ($id > 0) {
    $counter_q = mysqli_query($connection, "SELECT * FROM kp_counters WHERE cntr_id='$id' LIMIT 1") or die(mysqli_error($connection));
    if ($counter_q && mysqli_num_rows($counter_q) > 0) {
        $counter = mysqli_fetch_assoc($counter_q);
    }
}

$counter_id = $counter ? (int)$counter['cntr_id'] : 0;
$counter_name = $counter ? (string)$counter['name'] : 'Cashier';
$current_cashier_id = $counter ? (int)$counter['user_id'] : 0;
$current_cashier_name = $current_cashier_id > 0 ? get_user($current_cashier_id) : 'No cashier assigned';

$is_kitchen_counter = false;
if ($counter) {
    if (isset($counter['dpt']) && (int)$counter['dpt'] === 2) {
        $is_kitchen_counter = true;
    } elseif (isset($counter['type']) && stripos((string)$counter['type'], 'kitchen') !== false) {
        $is_kitchen_counter = true;
    } elseif (isset($counter['counter_type']) && stripos((string)$counter['counter_type'], 'kitchen') !== false) {
        $is_kitchen_counter = true;
    } elseif (stripos((string)$counter['name'], 'kitchen') !== false) {
        $is_kitchen_counter = true;
    }
}

$default_role = $is_kitchen_counter ? 3 : 1;
$role_label = $is_kitchen_counter ? 'Kitchen Cashier' : 'Cashier';
$user_sql = "SELECT user_id,username,counter,counter_status,drole FROM kp_users WHERE drole='$default_role' ORDER BY username ASC";
$users_q = mysqli_query($connection, $user_sql) or die(mysqli_error($connection));
?>
<div class="modal-content">
    <div class="modal-header">
        <button type="button" class="close" data-dismiss="modal" aria-hidden="true">×</button>
        <h4 class="modal-title">Select <?php echo htmlspecialchars($role_label); ?></h4>
    </div>
    <div class="modal-body">
        <div id="results3"></div>
        <?php if ($counter_id > 0) { ?>
        <div class="alert alert-warning" style="margin-bottom:12px;">
            <strong><?php echo htmlspecialchars($counter_name); ?></strong><br>
            Current cashier: <?php echo htmlspecialchars($current_cashier_name); ?>
        </div>
        <?php } else { ?>
        <div class="alert alert-warning" style="margin-bottom:12px;">
            Select a cashier to assign.
        </div>
        <?php } ?>

        <div class="form-group col-md-12" style="padding-left:0;padding-right:0;">
            <label>Select <?php echo htmlspecialchars($role_label); ?></label><br>
            <select class="form-control form-control-xs pickers text-black" id="user_id" name="user_id" style="width:100%;">
                <option value="0">-----SELECT <?php echo strtoupper($role_label); ?>-----</option>
                <?php while ($user = mysqli_fetch_assoc($users_q)) { ?>
                    <?php
                    $user_counter = (int)$user['counter'];
                    $user_state = ((int)$user['counter_status'] === 1 && $user_counter > 0)
                        ? ' - On ' . get_counter($user_counter)
                        : ' - Available';
                    ?>
                    <option value="<?php echo (int)$user['user_id']; ?>" <?php echo ((int)$user['user_id'] === $current_cashier_id ? 'selected' : ''); ?>>
                        <?php echo htmlspecialchars($user['username'] . $user_state); ?>
                    </option>
                <?php } ?>
            </select>
            <p id="usererr" class="text-red"></p>
        </div>
    </div>
    <div class="modal-footer">
        <button type="button" class="btn btn-danger" data-dismiss="modal">Close Modal</button>
        <button type="button" id="btn_suspend" class="btn btn-lime">Assign Cashier</button>
    </div>
<script type="text/javascript">
    $(document).ready(function() {
        $('.pickers').select2({ dropdownParent: $('#modal') });
        document.getElementById('btn_suspend').addEventListener('click', suspend);
        function suspend(){
            $('#btn_suspend').text('Processing...');
            $('#btn_suspend').attr('disabled',true);
            $('#usererr').text('');
            var user_id = $("#user_id").val();
            if (user_id == 0) {
                $('#usererr').text('You must select a cashier');
                $('#btn_suspend').text('Assign Cashier');
                $('#btn_suspend').attr('disabled',false);
                return;
            }

            $.ajax({
                url: '../users/ccashact.php',
                type: 'POST',
                data: {
                    user_id: user_id,
                    cntr_id: <?php echo (int)$counter_id; ?>
                },
                dataType: 'html'
            })
            .done(function(data){
                $('#results3').html(data);
            })
            .fail(function(){
                $('#results3').html('<div class="alert alert-danger"><a href="#" class="close" data-dismiss="alert">&times;</a><strong>Warning!</strong> Error occurred. Try again.</div>');
            })
            .always(function(){
                $('#btn_suspend').text('Assign Cashier');
                $('#btn_suspend').attr('disabled',false);
            });
        }
    });
</script>
