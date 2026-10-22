<?php
include("../clude/head.php");  
 $user_id = $_SESSION['user_id']; 
$shift = get_column('kp_shift','shift',"1");
$job = get_column('kp_users','job',"user_id='$user_id'");
?>
<style>
  body {
    background: url("../assets/img/bg/3.jpg")no-repeat;
    -webkit-background-size: cover;
    -moz-background-size: cover;
    -o-background-size: cover;
    background-size: cover;
  }
</style>
<body class="fixed-sn deep-orange-skin">
  <header>
    <nav class="navbar navbar-fixed-top scrolling-navbar" style="padding: 1px;">
      <ul class="nav navbar-nav navbar-left">
        <li class="nav-item" style="margin-left: 10px; margin-top: 10px;">
          <a href="../dash/dash.php"  name="open" style=" font-family: 'comic sans ms'; font-size: 12PX; font-weight: bold; color: #02550E !important; padding-top:30%; border-radius: 100%; height: 80px; width:80px; background-color: #FFF; border: 5px solid #02550E;" class="btn btn-sm btn-success">HOME</a>
        </li>
      </ul>
      <ul class="nav navbar-nav navbar-right" style="margin-right: 10px; margin-top: 10px;">
        <li  class="nav-item">
          <a  href="../log_out.php" style=" font-family: 'comic sans ms'; font-size: 12PX; font-weight: bold; color: #02550E !important; padding-top:30%; border-radius: 100%; height: 80px; width:80px; background-color: #FFF; border: 5px solid #02550E;" class="btn btn-sm btn-success">Log Out</a>
        </li>  
      </ul>
    </nav>
    <!--/.Navbar-->
  </header>
  <div id="content" class="content m-l-10">
    <div class="row" >  
      <div class="panel  col-md-7 m-5">
        <div class="panel-body " style="overflow-y:scroll; height: 600px; background-color: #fff;">
          <?php
          $get_start = mysqli_query($connection,"SELECT * FROM kp_day_shift WHERE shift='$shift'") or die(mysqli_error($connection)." Could not get day shift");
          $sr = mysqli_fetch_assoc($get_start);
          $start=$sr['opening'];
          if ($job==1) {
            $filter="WHERE timestamp BETWEEN '$start' AND NOW()";
          }else{
            $filter="WHERE claim_staff='$user_id' AND timestamp BETWEEN '$start' AND NOW()";
          }    
          $get_orders = "SELECT * FROM kp_split_message $filter ORDER BY timestamp DESC  ";
          $get_orders_r = mysqli_query($connection,$get_orders) or die("Could not get the orders");
           if (mysqli_num_rows($get_orders_r) < 1) {
              echo "NO MPESA RECEIVED TODAY";
           }else{
        ?>
        <table class="table table-striped">
          <thead>
            <tr>
               <th style="width: 90px;">CODE</th> 
               <th>CUSTOMER NAME</th> 
               <th>PHONE</th> 
               <th>AMOUNT</th> 
               <th>TIME</th>  
                <?php 
                if ($job==1) {  
                  ?>
                  <th>ASSIGNED TO</th> 
                  <th>ACTION</th>  
                  <?php
                }
                ?>
            </tr>
          </thead>
          <tbody>
              <?php
              while ($or = mysqli_fetch_assoc($get_orders_r)) {
                  $code = $or['code']; 
                  $name = $or['name'];
                  $amount = $or['amount'];
                  $phone = $or['phone'];
                  $time = $or['time'];
                  $waiter = $or['claim_staff'];  
                  $status = $or['approve_status']; 
                  ?>
                  <tr style="padding: 0px 0px 0px 0px;">
                    <th><?php echo '';//$code; ?></th> 
                    <th><?php echo $name; ?></th> 
                    <th><?php echo $phone; ?></th> 
                    <th><?php echo $amount; ?></th>
                    <th><?php echo $time; ?></th>
                    <?php 
                       if ($job==1) {  
                       ?>

                    <th><?php echo get_column("kp_users","username","user_id='$waiter'"); ?></th>
                    <th>
                      <?php 
                      if ($status==1) {
                        ?>
                       <a class="btn btn-success" id="assignbtn" data-id="<?php echo $code; ?>" data-id2="<?php echo $name; ?>" data-id3="<?php echo $amount; ?>" style="padding: 0px 0px 0px 0px; width:100px; font-weight: bold; border-radius: 10px; background-color: orange;border: 3px solid #fff; color: white; ">RE-ASSIGN</a>
                       <?php
                      }else{
                        ?>
                       <a class="btn btn-success" id="assignbtn" data-id="<?php echo $code; ?>" data-id2="<?php echo $name; ?>" data-id3="<?php echo $amount; ?>" style="padding: 0px 0px 0px 0px; width:100px; font-weight: bold; border-radius: 10px; background-color: lime;border: 3px solid #fff; color: white; ">ASSIGN</a>
                       <?php
                      } ?>
                     </th>
                       <?php
                     }else{} ?> 
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
      <div class="panel  col-md-4 m-5">
        <div class="panel-body" id="cont" style="overflow-y:scroll; height: 700px; background-color: #fff;">  
            <table class="table table-striped ">
             <thead>
              <tr>
               <th colspan="2">
                <h4 class="text-center"><b>UNSPLIT MESSAGES</b></h4>
              </th> 
             </tr>
             <tr>
               <th>MESSAGE</th> 
             </tr>
            </thead>  
            <tbody>
             
            <?php
            $get_sale = "SELECT * FROM mpesa_message WHERE split_status!=1 AND datime BETWEEN '$start' AND NOW() ";
            $get_sale_r = mysqli_query($connection,$get_sale) or die("Could not fetch the order");
            while ($o = mysqli_fetch_assoc($get_sale_r)) {
              $message = $o['message'];  
              ?>
              <tr>
                 <td><?php echo $message; ?></td> 
              </tr>
              <?php  } ?> 
            </tbody>  
          </table>  
        </div>
      </div>
    </div>
        <!--/Main layout-->
<div id="modal" class="modal fade" tabindex="-1" role="dialog" aria-labelledby="gridModalLabel" aria-hidden="true" >
  <div class="modal-dialog" id="load"></div>
</div>
      
        </div> 
      </div>
    </div>
  </div> 
  <!-- ================== BEGIN BASE JS ================== -->
  <script src="../../assets/plugins/jquery/jquery-3.2.1.min.js"></script> 
  <!--<script src="../../assets/plugins/jquery-ui/jquery-ui.min.js"></script>-->
  <script src="../../assets/plugins/bootstrap/4.0.0/js/bootstrap.bundle.min.js"></script> 
<script type="text/javascript">
    $( document ).ready(function() {
      $(document).on('click','#assignbtn', function(e){
          e.preventDefault();

          $('#modal').modal('show');
          var code = $(this).data('id');
          var name = $(this).data('id2');
          var amount = $(this).data('id3');    
          $('#load').html(''); // leave this diff blankm
          $.ajax({
            url: 'assign_modal.php',
            typr: 'POST',
            data: {code:code,amount:amount,name :name},
            dataType: 'html'
          })
          .done(function(data){ 
            $('#load').html(''); // blank before load.
            $('#load').html(data);
          })
          .fail(function(){
            $('#load').html('<i class="fa  fa-info-sign"></i> Something went wrong, Please try again...'); ;
          });
        });

    $(document).on('click','#clear', function(e){
      e.preventDefault();
      var uid = $(this).data('id'); // get id of clicked row
      var cntr = $(this).data('id2'); // get id of clicked row
      $('#load').html(''); // leave this diff blank
      //$('#modal-loader').show(); // load ajax loader button cliked
      $.ajax({
        url: 'staff_clear_modal.php',
        typr: 'POST',
        data: {id:uid,cntr:cntr},
        dataType: 'html'
      })
      .done(function(data){
        console.log(data);
        $('#load').html(data);
      })
      .fail(function()
      {
        $('#load').html('<i class="fa  fa-info-sign"></i> Something went wrong, Please try again...');
        
      });
    }); 
    }); 
  </script>
  </body>
</html>