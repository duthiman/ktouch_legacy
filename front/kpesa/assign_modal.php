 <?php
  session_start(); 
  include("../../inc/connection.php");
  include("funcs.php");
  $user_id = $_SESSION['user_id'];
  $code=$_REQUEST['code'];
  $name=$_REQUEST['name'];
  $amount=$_REQUEST['amount'];
  ?>
 <div class="modal-content">
    <div class="modal-header">
      <h4 class="modal-title">ASSIGN THIS MPESA TO WAITER</h4> 
    </div>
    <div class="modal-body">
      <table class="table table-bordered" style="padding: 2px;">
        <thead>
          <tr style="padding: 2px;">
            <th>CODE</th>
            <th><?php echo $code; ?></th>
          </tr>
          <tr style="padding: 2px;">
            <th>NAME</th>
            <th><?php echo $name; ?></th>
          </tr>
          <tr style="padding: 2px;">
            <th>AMOUNT</th>
            <th><?php echo $amount; ?></th>
          </tr>
        </thead>
      </table>
      <div class="container-fluid">
        <div class="row">
            <?php
            $drole="cashier";
              $get_tables = "SELECT user_id,username FROM kp_users WHERE role_id!=1";
              $get_tables_r = mysqli_query($connection,$get_tables) or die("Could not fetch the tables");
              while ($tab = mysqli_fetch_assoc($get_tables_r)) {
                  $name = $tab['username'];
                  $sid=$tab['user_id'];
            ?>
            <a style=" font-family: 'comic sans ms'; font-size: 16PX; font-weight: bold; color: #02550E !important; text-transform: uppercase; padding-top:8%; margin: 10px; border-radius: 100%; height: 100px; width:100px; background-color: #FFF;border: 5px solid #02550E;" class="btn btn-sm btn-success m-b-10" href="assignact.php?id=<?php echo $sid; ?>&code=<?php echo $code; ?>"><?php echo $name;?> </a>
            <?php
              }
            ?>
        </div>
      </div>
    </div>
    <div class="modal-footer">
      <button type="button" class="btn btn-warning" data-dismiss="modal">Close</button>
    </div>
  </div>