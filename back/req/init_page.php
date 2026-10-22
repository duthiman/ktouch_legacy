<?php 
include("../clude/head.php"); 
$user_id = $_SESSION['user_id'];
$folder="front/kuot/"; 
thesion_page($folder);
clear_thesion_ids($user_id);
 ?>
<body>
    <!-- begin #page-container -->
	<div id="page-container" class="fade page-sidebar-fixed page-header-fixed">
        <!-- begin #menu -->
        <?php include("../clude/header.php") ?>
    <!-- end #menu -->
    <!-- begin #menu -->
        <?php //include("../clude/menu.php") ?>
		<!-- end #menu -->
        <!-- begin #content -->
		<div id="contents" class="contents">
            <!-- begin row -->
            <div class="row">
                <!-- begin col-6 -->
			    <div class="col-lg-5">
            <!-- begin panel --> 
            <div class="panel panel-purple">
              <div class="panel-heading">  
                <div class="pull-right">
                  <a href="../dash/<?php echo $dash; ?>" name="filter" class="btn btn-purple btn-xs pull-left"><i class="fa fa-home"></i> HOME</a>&nbsp;&nbsp;
                  <a data-toggle="modal" data-target="#modal" name="new-qt" id="new-qt" class="btn btn-lime btn-xs pull-right"  style="color: #fff;">NEW REQUEST</a>
                </div>
                <h2 class="panel-title" style="color: #fff; font-weight: bold;">ONGOING REQUEST</h2>
              </div>
              <div class="panel-body"> 
                <table  class="table table-bordered">
                  <thead>
                    <tr> 
                      <th>Req No</th>    
                      <th>USER</th> 
                      <th>DATIME</th>  
                      <th>ACTION</th>
                   </tr>
                  </thead>
              <tbody>
                <?php
                $get_items = mysqli_query($connection,"SELECT * FROM kp_req_status") or die(mysqli_error($connection));
                while ($oow = mysqli_fetch_assoc($get_items)) { 
                  $req_id = $oow['req_id'];   
                  $user_id=$oow['user_id'];    
                  $status=$oow['status']; 
                  $datime=$oow['datime'];   
                  ?>
                  <tr>
                    <td><?php echo $req_id; ?></td>  
                    <td><?php echo get_user($user_id); ?></td> 
                    <td><?php echo $datime; ?></td> 
                    <td>
                      <?php 
                      if ($_SESSION['user_id']==$user_id OR empty($user_id) OR $user_id==0) {
                      ?>
                      <a href="init_req.php?id=<?php echo $req_id; ?>&st=CONT" style="margin-left: 10px;" type="button"  name="filter" class="btn btn-warning btn-xs">CONTINUE</a>  
                      <?php
                      } 
                      ?>
                    </td>
                  </tr>
                <?php
                }
                ?>
              </tbody>  
          </table>
        </div>
      </div>
  </div>
                <!-- end col-6 --> <div class="col-md-7">
          <!-- begin panel -->
          <div class="panel panel-purple">
            <div class="panel-heading"> 
              <h2 class="panel-title" style="color: #fff; font-weight: bold;">TODAY'S REQUESTS</h2>
            </div>
            <!-- begin panel-body -->
            <div class="panel-body ">
              <table id="data-table-combine" class="table table-striped table-bordered">
                <thead>
                  <tr> 
                    <th>Request No.</th>  
                    <th>Order By</th> 
                    <th>Date</th> <!-- 
                    <th>Status</th>                                        -->
                    <th>Action</th>
                  </tr>
                </thead>
                <tbody>
                  <?php
                  $pending_orders = mysqli_query($connection,"SELECT * FROM kp_req WHERE req_date=CURRENT_DATE") or die("Could not fetch today's quotes");
            if (mysqli_num_rows($pending_orders) < 1) {
              ?>
              <tr>
                <td colspan="7">There are no raised Purchase orders today</td>
              </tr>
              <?php 
            }else{ 
                  while ($row = mysqli_fetch_assoc($pending_orders)) { 
                    $id = $row['req_id']; 
                    $day = $row['req_date'];
                    $user_id = $row['req_user'];  
                    ?>
                    <tr> 
                      <td><?php echo $id; ?></td>  
                      <td><?php echo get_user($user_id); ?></td> 
                      <td><?php echo $day; ?></td>
                     <!--  <td>
                        <?php 
                        if ($rec_status==2) {
                          echo "PENDING APPROVAL";
                        }elseif ($rec_status==1 AND $stock_status==2) {
                          echo "PENDING ISSUING";
                        }elseif ($rec_status==1 AND $stock_status==1) {
                          echo "COMPLETELY PROCESSED";
                        } ?>
                      </td> -->
                      <td class="with-btn-group" nowrap>
                        <div class="btn-group">
                          <a href="#" class="btn btn-warning btn-xs width-60" style="color: white;">Actions</a>
                          <a href="#" class="btn btn-warning btn-xs dropdown-toggle width-30 no-caret" data-toggle="dropdown">
                          <span class="caret"  style="color: white;"></span>
                          </a>
                          <ul class="dropdown-menu pull-right" style="background-color: purple;"> 
                            <li><a href="req_print.php?id=<?php echo $id; ?>">Re-print</a></li> 
                          </ul>
                        </div>
                      </td>
                    </tr>
                    <?php
                  }
                  }
                  ?>
                </tbody>
              </table>
      </div>
      <!-- end panel-body --> 
  </div>
</div> 
 
            </div>
            <!-- end row -->
        </div>
		<!-- end #content -->
	</div>
    <!-- end page container -->

     <div id="modal" class="modal fade" tabindex="-1" role="dialog" aria-labelledby="myModalLabel" aria-hidden="true" style="display: none;">
        <div class="modal-dialog" id="load"> </div>
    </div>
	      <div id="modal2" class="modal fade" tabindex="-1" role="dialog" aria-labelledby="myModalLabel" aria-hidden="true" style="display: none;">
        <div class="modal-dialog" id="load2"> </div>
    </div>
<!-- ================== BEGIN BASE JS ================== -->
  <script src="../../assets/plugins/jquery/jquery-3.2.1.min.js"></script>
  <script src="../../assets/plugins/jquery-ui/jquery-ui.min.js"></script>
  <script src="../../assets/plugins/bootstrap/4.0.0/js/bootstrap.bundle.min.js"></script>
  <!--[if lt IE 9]>
    <script src="../assets/crossbrowserjs/html5shiv.js"></script>
    <script src="../assets/crossbrowserjs/respond.min.js"></script>
    <script src="../assets/crossbrowserjs/excanvas.min.js"></script>
  <![endif]-->
  <script src="../../assets/plugins/slimscroll/jquery.slimscroll.min.js"></script>
  <script src="../../assets/plugins/js-cookie/js.cookie.js"></script>

  <script src="../../assets/js/apps.min.js"></script>
  <script src="../../assets/js/theme/default.min.js"></script>
  <!-- ================== END BASE JS ================== --> 

  <script src="../../assets/plugins/select2/dist/js/select2.min.js"></script> 


  <!-- ================== BEGIN PAGE LEVEL JS ================== -->
  <script src="../../assets/plugins/Hullabaloo/js/hullabaloo.js"></script> 
  <!-- ================== END PAGE LEVEL JS ================== -->

  <!-- <script src="../../assets/js/demo/general_scripts.js"></script> -->
  <script>
    $(document).ready(function() {
      App.init();
      $.fn.get_cust = function(){ 
        $('#cust_id').empty();
        $.ajax({
          type: "GET",
          url: '../../gen/get_cust.php', 
          data: { },
          success:  function(data) {  
            $('#cust_id').append(data);
          }
        });
      }
      $(document).on('click','#new-qt', function(e){       
        $.ajax({
          url: 'new_qt.php',
          typr: 'POST',
          data: {},
          dataType: 'html'
        })
        .done(function(data){ 
          $('#load').html(''); // blank before load.
          $('#load').html(data); 
           $.fn.get_cust();
        })
        .fail(function(){
          $('#sup_result').html('<i class="fa  fa-info-sign"></i> Something went wrong, Please try again... NOT LOADING PAGE'); 
        });
      });
    });
</script>
 
</body>
</html>