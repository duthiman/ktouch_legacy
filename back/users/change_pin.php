<?php
include_once("../../inc/connection.php");

$id = (int)($_REQUEST['id'] ?? 0);
$row = array(
    'username' => '',
    'pin' => '',
    'user_id' => $id,
);

if ($id > 0) {
    $get_customer_details = mysqli_query($connection, "SELECT * FROM kp_users WHERE user_id='$id' LIMIT 1") or die("Could not get the user details");
    if ($get_customer_details && mysqli_num_rows($get_customer_details) > 0) {
        $db_row = mysqli_fetch_assoc($get_customer_details);
        $row['username'] = (string)($db_row['username'] ?? $db_row['user_username'] ?? '');
        $row['pin'] = (string)($db_row['pin'] ?? $db_row['user_pin'] ?? '');
        $row['user_id'] = (int)($db_row['user_id'] ?? $id);
    }
}
?>
<div class="modal-content">
    <div class="modal-header"> 
        <button type="button" class="close" data-dismiss="modal" aria-hidden="true">×</button>
        <h4 class="modal-title"> <i class="fa fa-stats"></i> &nbsp;&nbsp;Change <?php echo htmlspecialchars($row['username']); ?> PIN</h4> 
    </div>
<div class="modal-body">
    <div class="form-group">
        <span id='message' style="margin-top: 5px ; margin-bottom: 5px;"></span>
        <label >Previous Pin</label>
        <input type="hidden" class="form-control" id="showpass" placeholder="Previous PIN" value="<?php echo htmlspecialchars($row['pin']); ?>" />
        <input type="password" class="form-control " id="prpass" placeholder="Previous PIN" required="" />
        <input type="hidden" class="form-control" id="user_id" value="<?php echo (int)$row['user_id']; ?>" />
        <small class="text-muted" style="display:block;margin-top:6px;">Use normal submit if the old PIN is known. Use force change if the user does not remember it.</small>
    </div>
    <div class="form-group">
        <label >New PIN</label>
        <input type="password" class="form-control " id="password" placeholder="New PIN" required="" /> 
        <div class="pwstrength_viewport_progress" style="margin-top: 10px;"></div>
    </div>
    <div class="form-group">
        <label >Confirm PIN</label>
        <input type="password" class="form-control " id="confirm" placeholder="Confirm PIN" required="" />
    </div>
<script type="text/javascript">
    $('#showpass, #prpass').on('keyup', function () {
        if ($('#showpass').val() == $('#prpass').val()) {
            $('#message').html('<div class="alert alert-success"><a href="#" class="close" data-dismiss="alert">&times;</a><strong>Success!</strong> Perfect Match.</div>');
        } else 
        $('#message').html('<div class="alert alert-danger"><a href="#" class="close" data-dismiss="alert">&times;</a><strong>Warning!</strong> Passwords Does Not Match.</div>');
    });
</script>
</div>
<div class="modal-footer">
    <button type="button" class="btn btn-danger btn-sm" data-dismiss="modal">Close</button>
    <button type="button" class="btn btn-warning btn-sm" id="btn_force_pass">Force Change PIN</button>
    <button type="button" class="btn btn-lime btn-sm" id="btn_pass">Submit</button>
</div>
</div>
<script type="text/javascript">
    $(document ).ready(function() {
        document.getElementById('btn_pass').addEventListener('click', function(){ changePass(false); });
        document.getElementById('btn_force_pass').addEventListener('click', function(){ changePass(true); });

        function changePass(forceChange){
            var user_id = $("#user_id").val();
            var showpass = $("#showpass").val();
            var prpass = $("#prpass").val();
            var password = $("#password").val();
            var confirm = $("#confirm").val();
            if (!confirm || !password) {
                $('#message').html('<div class="alert alert-danger"><a href="#" class="close" data-dismiss="alert">&times;</a><strong>Warning!</strong> Enter the new PIN and confirm it.</div>');
            } else if( password != confirm){
                $('#message').html('<div class="alert alert-danger"><a href="#" class="close" data-dismiss="alert">&times;</a><strong>Warning!</strong> Passwords Do Not Match!!.</div>');
            } else if (!forceChange && showpass != prpass) {
                $('#message').html('<div class="alert alert-danger"><a href="#" class="close" data-dismiss="alert">&times;</a><strong>Warning!</strong> Previous PIN does not match.</div>');
            }else{
                var extra = forceChange ? '&force=1' : '';
                $.ajax({
                    url: '../users/pass_act.php',
                    type: 'POST',                           
                    data: 'user_id='+encodeURIComponent(user_id)+'&confirm='+encodeURIComponent(confirm)+'&st=pin'+extra,
                    dataType: 'html'
                })
                .done(function(data)
                 {
                    $('#message').html(''); // blank before load.
                    $('#message').html(data);
                })
                .fail(function()
                {
                    $('#message').html('<div class="alert alert-danger"><a href="#" class="close" data-dismiss="alert">&times;</a><strong>Warning!</strong> Error occured.</div>');
                    window.setTimeout(function(){
                        window.location.href = 'user.php';
                    }, 100);
                });
            }
        }
    });
</script> 
