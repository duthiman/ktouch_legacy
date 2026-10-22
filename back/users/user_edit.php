<?php
include("../../inc/connection.php");
if(isset($_REQUEST['id']))
{
    $id = $_REQUEST['id'];
    $get_details = mysqli_query($connection,"SELECT * FROM kp_users WHERE user_id='$id'") or die("Could not get Staff details");
    $row = mysqli_fetch_assoc($get_details);
}
?>
<div class="modal-content">
   		<div class="modal-header"> 
   			<button type="button" class="close" data-dismiss="modal" aria-hidden="true">×</button>
   			<h4 class="modal-title"> <i class="fa fa-user"></i> &nbsp;&nbsp; Edit <?php echo $row['username']; ?> Details</h4> 
   		</div>
   		<div class="modal-body">
   			<div id="results2"></div> 
                <div class="form-group row">
                    <!-- <div class="col-md-6" style="border-top: none;">
                        <div class="form-group ">
                            <label>Name</label>
                            <input type="text" class="form-control" name="name" id="name"  value="<?php echo $row['user_name']; ?>" placeholder="Enter Full Name..." required / >
                        </div>
                    </div> -->
                    <div class="form-group col-md-6">
                        <label>Username</label>
                        <input type="text" class="form-control" name="username" id="username" value="<?php echo $row['username']; ?>" placeholder="Enter Username..." required />
                    </div>
                    <div class="form-group col-md-6">
                        <label >User Roles</label>
                        <select class="form-control"  name="roles" id="roles">
                            <option value="">-----------Please Select-------------------</option>
                            <?php
                                $role = mysqli_query($connection,"SELECT * FROM kp_roles") or die(mysql_error());
                                while($fetch_r=mysqli_fetch_assoc($role)){
                            ?>
                                <option value="<?=$fetch_r['role_id']?>" <?php if ($row['role_id']==$fetch_r['role_id']) {
                                    echo "selected";
                                } ?> ><?=$fetch_r['role_name']?></option>
                            <?php
                                }
                            ?>
                        </select>
                    </div>
                    
                    <div class="form-group col-md-6 col-sm-12" id="drole-div">
        <label >Default Role</label>
        <select class="form-control" name="drole" id="drole">
          <option value="0">------SELECT DEFAULT ROLE-------</option> 
          <option value="1" <?php if ($row['drole']==1) { echo "selected"; } ?> >BARPERSON</option>
          <option value="2" <?php if ($row['drole']==2) { echo "selected"; } ?> >WAITER</option>
          <option value="3" <?php if ($row['drole']==3) { echo "selected"; } ?> >KITCHEN CASHIER</option>
           
        </select>
      </div>
                    <input type="hidden" class="form-control" name="staff_id" id="staff_id" value="<?php echo $row['staff_id']; ?>" required/>
                </div>
                <div class="modal-footer">
                    <a href="javascript:;" class="btn btn-sm btn-danger" data-dismiss="modal">Close</a>
                    <button type="submit" name="edit_user" id="user-edit"  class="btn btn-success">Save</button>
                </div> 
   		</div>
   	</div> 

<script type="text/javascript">                       

// Add your custom JS code here
$( document ).ready(function() {
    // Handler for .ready() called.
    document.getElementById('user-edit').addEventListener('click', edit_user);
        function edit_user(){  
            var roles = $("#roles").val();
            var drole = $("#drole").val();
            var username = $("#username").val();
            var id = '<?php echo $id; ?>';

            if (!username || !roles) {
                $('#results2').html('<div class="alert alert-danger"><a href="#" class="close" data-dismiss="alert">&times;</a><strong>Warning!</strong> Fill all the required fields.</div>');
                return;
            }

             $.ajax(
                {
                    url: 'edit_act.php',
                    type: 'POST',
                    data: {
                        drole: drole,
                        roles: roles,
                        username: username,
                        user_id: id
                    },
                    dataType: 'html'
                })

                .done(function(data)
                 {
                    $('#results2').html(''); // blank before load.
                    $('#results2').html(data);

                })

                .fail(function()
                {
                    $('#results2').html('<div class="alert alert-danger"><a href="#" class="close" data-dismiss="alert">&times;</a><strong>Warning!</strong> Error occured Try Again.</div>');
                    
                });
        }

    });
                    </script>
					


		<!--modal add-->
		
