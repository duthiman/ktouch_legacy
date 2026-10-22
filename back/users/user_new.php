<?php
require_once('../../inc/connection.php');
function role_panel($role_id){
  global $connection;
  $panel = mysqli_query($connection,"SELECT role_access_panel FROM kp_roles WHERE role_id='$role_id'") or die(mysql_error($connection));
  $row=mysqli_fetch_assoc($panel);
  $panel=$row['role_access_panel'];

  return $panel;
}
?>
<div class="modal-content">
	<div class="modal-header">
		<button type="button" class="close" data-dismiss="modal" aria-hidden="true">×</button>
		<h4 class="modal-title">CREATE NEW USERS</h4>
	</div>
	<div class="modal-body"> 
    <div id="results"></div>
   <div class="form-group row"> 
      <div class="form-group col-md-8 col-sm-12">
        <label >Select Staff</label>
        <select class="form-control" name="staff" id="staff" >
           <option value="">--------SELECT STAFF------</option>
          <?php
          $role = mysqli_query($connection,"SELECT * FROM kp_staff WHERE staff_del_status=2 AND user_status=2") or die(mysql_error());
          while($fetch_r=mysqli_fetch_assoc($role)) {
          ?>
          <option value="<?=$fetch_r['staff_id']?>"><?=$fetch_r['staff_name']?></option>
          <?php
          }
          ?>
        </select>
      </div>
      <div class="form-group col-md-6 col-sm-12">
        <label >Username</label>
        <input type="text" class="form-control" id="username" name="username" placeholder="Username" required />
      </div>  
      <div class="form-group col-md-6 col-sm-12">
        <label >User Roles</label>
        <select class="form-control" name="roles" id="roles">
           <option value="0">------SELECT USER ROLE-------</option>
          <?php
          $role = mysqli_query($connection,"SELECT * FROM kp_roles ORDER BY role_name ASC") or die(mysql_error());
          while($ro=mysqli_fetch_assoc($role)) {
            $role_id=$ro['role_id'];
          ?>
          <option data-id="<?php echo role_panel($role_id); ?>" value="<?php echo $role_id; ?>"><?php echo $ro['role_name']; ?></option>
          <?php
          }
          ?>
        </select>
      </div>
      <div class="form-group col-md-6 col-sm-12" id="pass-div" style="display: none;">
        <label >Password</label>
        <input type="password" class="form-control" id="pass" name="password" placeholder="Password" required />
      </div>
      <div class="form-group col-md-6 col-sm-12" id="cpass-div" style="display: none;">
        <label >Confirm Password</label>
        <input type="password" class="form-control form-control-xs" id="cpass" name="cpassword" placeholder="Confirm password" required />
        <div style="font-size: 16px;" id="msg"></div>
      </div>
      <div class="form-group col-md-6 col-sm-12" id="pin-div" style="display: none;">
        <label >PIN</label>
        <input type="password" class="form-control" id="pin" name="pin" placeholder="PIN" required />
      </div>
      <div class="form-group col-md-6 col-sm-12" id="cpin-div" style="display: none;">
        <label >Confirm PIN</label>
        <input type="password" class="form-control" id="cpin" name="cpin" placeholder="Confirm PIN" required />
        <div style="font-size: 16px;" id="msg"></div>
      </div>

      <div class="form-group col-md-6 col-sm-12" id="drole-div" style="display: none;">
        <label >Default Role</label>
        <select class="form-control  form-control-xs" name="drole" id="drole">
          <option value="0">------SELECT DEFAULT ROLE-------</option> 
          <option value="1">CASHIER</option>
          <option value="2">WAITER</option>
          <option value="3">KITCHEN CASHIER</option>
           
        </select>
      </div>
    </div>
  </div>
  <div class="modal-footer">
    <a href="javascript:;" class="btn btn-xs btn-danger" data-dismiss="modal">Close</a>
    <button type="button" name="new_user" id="btn-user" class="btn btn-purple btn-xs">Save</button>
  </div>
</div>

<script type="text/javascript">
  $(document).ready(function(){
    $("#cpass").keyup(function(){
        if ($("#pass").val() != $("#cpass").val()) {
            $("#msg").html("Passwords entered do not match").css("color","red");
            $("#btn-user").hide();
        }else{
            $("#msg").html("Passwords entered matched").css("color","green");
            $("#btn-user").show();
        }
    });

    $('#roles').change(function () {
      var panel=$(this).find('option:selected').attr('data-id'); 
      if (panel==1) {
        document.getElementById("pass-div").style.display = "block";
        document.getElementById("cpass-div").style.display = "block"; 
        document.getElementById("pin-div").style.display = "none";
        document.getElementById("cpin-div").style.display = "none"; 
        document.getElementById("drole-div").style.display = "none";
        $("#pin").val('');
        $("#pass").val('');  
        $("#cpin").val('');
        $("#cpass").val('');  
      } else if (panel==2) {
        document.getElementById("pin-div").style.display = "block";
        document.getElementById("cpin-div").style.display = "block"; 
        document.getElementById("pass-div").style.display = "none";
        document.getElementById("cpass-div").style.display = "none"; 
        document.getElementById("drole-div").style.display = "block";
       $("#pin").val('');
        $("#pass").val('');  
        $("#cpin").val('');
        $("#cpass").val('');   
      } else if (panel==3) {
        document.getElementById("pass-div").style.display = "block";
        document.getElementById("cpass-div").style.display = "block"; 
        document.getElementById("pin-div").style.display = "block";
        document.getElementById("cpin-div").style.display = "block";
        document.getElementById("drole-div").style.display = "block";
        $("#pin").val('');
        $("#pass").val('');  
        $("#cpin").val('');
        $("#cpass").val(''); 
        $("#drole").val('');
      } else {
        cosole.log('error');
      }
    });
  }); 

document.getElementById('btn-user').addEventListener('click', customer);
function customer(){ 
  var staff = $("#staff").val();  
  var username = $("#username").val();
  var cpass = $("#cpass").val();
  var cpin = $("#cpin").val();
  var role = $("#roles").val();
  var drole = $("#drole").val();
  $.ajax({
    url: '../users/new_act.php',
    typr: 'POST',                           
    data: 'staff='+staff+'&username='+username+'&cpass='+cpass+'&role='+role+'&cpin='+cpin+'&drole='+drole,
    dataType: 'html'
  })
  .done(function(data){
    $('#results').html(''); // blank before load.
    $('#results').html(data);
  })
  .fail(function(){
    $('#results').html('<div class="alert alert-danger"><a href="#" class="close" data-dismiss="alert">&times;</a><strong>Warning!</strong> Error occured.</div>');
  });
}
</script>