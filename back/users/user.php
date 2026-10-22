<?php 
require("../clude/head.php");
$no=0;
// $action="LOADED|-|-|USER PAGE";
// logs($action,"../../");
?>
  <!-- ================== BEGIN DATATABLE================== --> 
  <link href="../../assets/plugins/select2/dist/css/select2.min.css" rel="stylesheet" />
  <!-- ================== END DATATABLE ================== -->  
   <!-- ================== BEGIN PAGE LEVEL STYLE ================== -->
  <link href="../../assets/plugins/DataTables/media/css/dataTables.bootstrap.min.css" rel="stylesheet" />
  <link href="../../assets/plugins/DataTables/extensions/Buttons/css/buttons.bootstrap.min.css" rel="stylesheet" />
  <link href="../../assets/plugins/DataTables/extensions/Responsive/css/responsive.bootstrap.min.css" rel="stylesheet" />
  <link href="../../assets/plugins/DataTables/extensions/AutoFill/css/autoFill.bootstrap.min.css" rel="stylesheet" />
  <link href="../../assets/plugins/DataTables/extensions/ColReorder/css/colReorder.bootstrap.min.css" rel="stylesheet" />
  <link href="../../assets/plugins/DataTables/extensions/KeyTable/css/keyTable.bootstrap.min.css" rel="stylesheet" />
  <link href="../../assets/plugins/DataTables/extensions/RowReorder/css/rowReorder.bootstrap.min.css" rel="stylesheet" />
  <link href="../../assets/plugins/DataTables/extensions/Select/css/select.bootstrap.min.css" rel="stylesheet" />
  <!-- ================== END PAGE LEVEL STYLE ================== -->
<!-- begin #page-container -->
<div id="page-container" class="fade page-sidebar-fixed page-header-fixed">
  <!-- begin #header -->  
  <?php 
    require("../clude/header.php");
  ?>
  <!-- end #header -->  
  <!-- begin #sidebar -->
  <?php 
    require("../clude/menu.php");
  ?>
  <!-- end #sidebar -->
    <!-- begin #content -->
    <div id="content" class="content">
      <!-- begin row -->
      <div class="row">
        <!-- begin col-6 -->
        <div class="col-lg-12">
          <!-- begin panel -->
          <div class="panel panel-inverse" data-sortable-id="index-1">
            <div class="panel-heading"> 
              <div class="pull-right">
                <Button href="#user-modal" class="btn btn-xs btn-purple pull-right" data-toggle="modal" id="user-add" ><i class="fa fa-plus"></i> &nbsp;NEW USER</Button> &nbsp; &nbsp; &nbsp; &nbsp;
              </div>
              <h4 class="panel-title">USER LIST</h4>
            </div>
            <div class="panel-body">
              <?php
              $maxSaleColumn = '0 AS max_sale';
              $columnCheck = mysqli_query($connection, "SHOW COLUMNS FROM kp_users LIKE 'max_sale'");
              if ($columnCheck && mysqli_num_rows($columnCheck) > 0) {
                  $maxSaleColumn = 'COALESCE(kp_users.max_sale,0) AS max_sale';
              }
              $get_users = mysqli_query($connection,"SELECT username,work_status,staff_id,user_id,$maxSaleColumn,kp_roles.role_name,kp_roles.role_access_panel FROM kp_users  INNER JOIN kp_roles ON kp_users.role_id=kp_roles.role_id  WHERE kp_users.sys_default=2") or die("Could not get any users".mysqli_error($connection));
              if (mysqli_num_rows($get_users) < 1) {
                echo "There are no registered users in the database";
              }else{
                ?>
                <table id="data-table-combine" class="table table-striped table-bordered">
                  <thead>
                    <tr>
                      <th>User id:</th>
                      <th>Name</th> 
                      <th>Username</th>  
                      <th>Max Sale</th>
                      <th>Password</th> 
                      <th>Role</th> 
                      <th>Access panel</th>
                      <th>Dispinary</th>
                      <th>Action</th>
                    </tr>
                  </thead>
                  <tbody>
                    <?php 
                    foreach ($get_users as $row) {
                      $no=$no+1;
                      $work_s=$row['work_status'];
                      $panel=$row['role_access_panel'];
                      ?>
                      <tr>
                        <td><?php echo $row['user_id']; ?></td>
                        <td><?php echo get_staff($row['staff_id']); ?></td>
                        <td><?php echo $row['username']; ?></td>
                        <td>KES <?php echo number_format($row['max_sale'],2); ?></td>
                        <td class="text-center">
                          <?php 
                          if ($panel==1) {
                            ?>
                            <a data-toggle="modal" data-target="#user-modal" data-id="<?php echo $row['user_id']; ?>" id="changepass" class="btn btn-xs btn-warning" style="color:white;"><i  class="fa fa-pencil"></i> Change Pass</a>
                            <?php
                          }elseif ($panel==2) {
                            ?>
                            <a data-toggle="modal" data-target="#user-modal" data-id="<?php echo $row['user_id']; ?>" id="changepin" class="btn btn-xs btn-warning" style="color:white;"><i  class="fa fa-pencil"></i> Change PIN</a>
                            <?php
                          }else{
                            ?>
                              <a data-toggle="modal" data-target="#user-modal" data-id="<?php echo $row['user_id']; ?>" id="changepass" class="btn btn-xs btn-warning" style="color:white;"><i  class="fa fa-pencil"></i> Change Pass</a>
                              ||
                              <a data-toggle="modal" data-target="#user-modal" data-id="<?php echo $row['user_id']; ?>" id="changepin" class="btn btn-xs btn-warning" style="color:white;"><i  class="fa fa-pencil"></i> Change PIN</a>
                            <?php
                          }
                          ?>
                        </td> 
                        <td><?php echo $row['role_name']; ?></td>
                        <td><?php 
                          if ($panel==1) {
                            echo "Back End";
                          }elseif ($panel==2) {
                            echo "Front End";
                          }else{
                            echo "Both";
                          }; 
                        ?>
                        </td> 
                        <td>
                    <?php
                    if($work_s == 2){
                    ?>
                    <a data-toggle="modal" data-target="#user-modal" data-id="<?php echo $row['user_id']; ?>" id="suspuser" class="btn btn-xs btn-danger">Suspend <i  class="fa fa-times"> </i></a>
                    <?php
                    }
                    else
                    {
                    ?>
                    <a data-toggle="modal" data-target="#user-modal" data-id="<?php echo $row['user_id']; ?>" id="unsuspuser" class="btn btn-xs btn-lime">Unsuspend <i  class="fa fa-check"> </i></a>
                    <?php
                    }
                    ?>
                </td>
                          <td class="with-btn-group" nowrap>
                            <div class="btn-group">
                                <a href="#" class="btn btn-purple btn-sm width-60">Actions</a>
                                <a href="#" class="btn btn-purple btn-sm dropdown-toggle width-30 no-caret" data-toggle="dropdown">
                                    <span class="caret"></span>
                                </a>
                                <ul class="dropdown-menu pull-right" style="background-color: #753f91;">
                                    <li><a data-toggle="modal" data-target="#user-modal" data-id="<?php echo $row['user_id']; ?>" id="user_edit"><i  class="fa fa-edit"></i>&nbsp; Edit</a></li>
                                    <li><a data-toggle="modal" data-target="#user-modal" data-id="<?php echo $row['user_id']; ?>" id="user-del"><i  class=" fa fa-times"></i>&nbsp;&nbsp; Delete</a></li>
                                    <li><a data-toggle="modal" data-target="#user-modal" data-id="<?php echo $row['user_id']; ?>" id="user-perm"><i  class=" fa fa-lock-open"></i>&nbsp; Set Permission</a></li>
                                    <li><a data-toggle="modal" data-target="#user-modal" data-id="<?php echo $row['user_id']; ?>" class="set-max-sale"><i class="fa fa-money"></i>&nbsp; Set Max Sale</a></li>
                                   <!--  <li><a href="#modal-dialog" data-toggle="modal">Adjust price</a></li>
                                    <li><a href="#">Price History</a></li> -->
                                </ul>
                            </div>
                          </td> 
                        </tr>
                    <?php
                      }
                    ?>
                    </tbody>
                </table>
                <?php
                }

                ?>
          </div>
        </div>
                    <!-- end panel -->
                </div>
                <!-- end col-6 -->


            </div>
            <!-- end row -->
    </div>
    <!-- end #content -->
<div  id="user-modal" class="modal fade bs-example-modal-lg" tabindex="-1" role="dialog" aria-labelledby="myModalLabel" style="display: none;">
   <div class="modal-dialog"  id="user-load"></div>
</div>
</div>
  <!-- end page container -->

<?php 
  require("../clude/foot.php");
?>
<!-- ================== BEGIN PAGE LEVEL JS ================== -->
<script src="../../assets/plugins/DataTables/media/js/jquery.dataTables.js"></script>
<script src="../../assets/plugins/DataTables/media/js/dataTables.bootstrap.min.js"></script>
<script src="../../assets/plugins/DataTables/extensions/Buttons/js/dataTables.buttons.min.js"></script>
<script src="../../assets/plugins/DataTables/extensions/Buttons/js/buttons.bootstrap.min.js"></script>
<script src="../../assets/plugins/DataTables/extensions/Buttons/js/buttons.flash.min.js"></script>   
<script src="../../assets/plugins/DataTables/extensions/ColReorder/js/dataTables.colReorder.min.js"></script> 
  <script src="../../assets/plugins/DataTables/extensions/Buttons/js/buttons.print.min.js"></script> 
<script src="../../assets/js/demo/table-manage-combine.demo.min.js"></script>
  <!-- ================== END PAGE LEVEL JS ================== -->
<script type="text/javascript">
  $(document).ready(function() { 
    App.init();
      TableManageCombine.init();     
  });
  <?php include("user.js"); ?>
</script>  
</body>
</html>
  
   