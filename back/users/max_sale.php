<?php
include('../../inc/connection.php');
$user_id = isset($_REQUEST['id']) ? intval($_REQUEST['id']) : 0;
$user = null;
// Ensure max_sale column exists before querying it
$column_check = mysqli_query($connection, "SHOW COLUMNS FROM kp_users LIKE 'max_sale'");
if ($column_check && mysqli_num_rows($column_check) === 0) {
    mysqli_query($connection, "ALTER TABLE kp_users ADD COLUMN max_sale DECIMAL(12,2) NOT NULL DEFAULT 0");
}
if ($user_id > 0) {
    $get_user = mysqli_query($connection, "SELECT user_id, username, COALESCE(max_sale,0) AS max_sale FROM kp_users WHERE user_id='$user_id' LIMIT 1") or die(mysqli_error($connection));
    $user = mysqli_fetch_assoc($get_user);
}
?>
<div class="modal-content">
    <div class="modal-header">
        <button type="button" class="close" data-dismiss="modal" aria-hidden="true">×</button>
        <h4 class="modal-title">Set Max Sale for <?php echo $user ? htmlspecialchars($user['username']) : 'User'; ?></h4>
    </div>
    <div class="modal-body">
        <div id="results2"></div>
        <?php if ($user) { ?>
            <div class="form-group">
                <label>Maximum Sale Amount</label>
                <input type="number" class="form-control" id="max_sale" value="<?php echo number_format($user['max_sale'],2,'.',''); ?>" min="0" step="0.01" />
                <input type="hidden" id="set_max_user_id" value="<?php echo $user['user_id']; ?>" />
            </div>
        <?php } else { ?>
            <div class="alert alert-danger">Invalid user selected.</div>
        <?php } ?>
    </div>
    <div class="modal-footer">
        <a href="javascript:;" class="btn btn-sm btn-danger" data-dismiss="modal">Close</a>
        <?php if ($user) { ?><button id="save-max-sale" class="btn btn-sm btn-success">Save</button><?php } ?>
    </div>
</div>
<script type="text/javascript">
$(document).ready(function() {
    $(document).on('click', '#save-max-sale', function(e) {
        e.preventDefault();
        var maxSale = $('#max_sale').val();
        var userId = $('#set_max_user_id').val();
        if (maxSale === '' || isNaN(maxSale) || parseFloat(maxSale) < 0) {
            $('#results2').html('<div class="alert alert-warning">Please enter a valid amount.</div>');
            return;
        }
        $.ajax({
            url: '../users/max_sale_act.php',
            type: 'POST',
            data: { user_id: userId, max_sale: maxSale },
            dataType: 'html'
        })
        .done(function(data) {
            $('#results2').html(data);
        })
        .fail(function() {
            $('#results2').html('<div class="alert alert-danger">Error saving max sale. Please try again.</div>');
        });
    });
});
</script>
