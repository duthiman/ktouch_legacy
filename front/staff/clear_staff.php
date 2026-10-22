<?php
session_start();
include('../../inc/connection.php');
include('funcs.php');
?>
<!DOCTYPE html>
<html lang="en">

<head>
    <!-- Required meta tags always come first -->
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1, shrink-to-fit=no">
    <meta http-equiv="x-ua-compatible" content="ie=edge">
    <title>Landing</title>
 <!-- Bootstrap core CSS -->
    <link rel="stylesheet" href="../../assets/plugins/bootstrap/3.3.7/css/bootstrap.min.css">
<script src="../../assets/plugins/jquery/jquery-3.2.1.min.js"></script>
<script src="../../assets/plugins/bootstrap/3.3.7/js/bootstrap.min.js"></script>

<!-- ================== BEGIN PAGE LEVEL JS ================== -->
<script src="../../assets/plugins/Hullabaloo/js/hullabaloo.js"></script> 
<!-- ================== END PAGE LEVEL JS ================== -->

<style type="text/css">
      body { 
  background: url(../assets/img/bg/5.jpg) no-repeat center center fixed; 
  -webkit-background-size: cover;
  -moz-background-size: cover;
  -o-background-size: cover;
  background-size: cover;
}
    </style>
</head>
<body>
  <header>
        <nav class="navbar navbar-fixed-top scrolling-navbar" style="padding: 1px;">
            
  <ul class="nav navbar-nav navbar-left">
     
      <li class="nav-item" style="margin-left: 10px; margin-top: 10px;">
        <a href="../dash/dash.php"  name="open" style=" font-family: 'comic sans ms'; font-size: 12PX; font-weight: bold; color: #02550E !important; padding-top:30%; border-radius: 100%; height: 80px; width:80px; background-color: #FFF; border: 5px solid #02550E;">HOME</a> 
   </ul>
  <ul class="nav navbar-nav navbar-right" style="margin-right: 10px; margin-top: 10px;">
        <li  class="nav-item">
          <a  href="../log_out.php" style=" font-family: 'comic sans ms'; font-size: 12PX; font-weight: bold; color: #02550E !important; padding-top:30%; border-radius: 100%; height: 80px; width:80px; background-color: #FFF; border: 5px solid #02550E;" class="btn btn-sm btn-success">Log Out</a>
             
        </li>       
 </ul>

        </nav> 
    <!--/.Navbar-->
  </header>
  <!-- begin #content -->
  <div id="content" class="content" style="margin-top: 100px; margin-left:15%;">
    <div class="panel">  
      <div class="panel-body col-md-10" style="background-color: #ee731f80; ">
         <?php
          $get_tables = "SELECT user_id,username FROM kp_users ";
          $get_tables_r = mysqli_query($connection,$get_tables) or die("Could not fetch the tables");
          while ($tab = mysqli_fetch_assoc($get_tables_r)) {
              $name = $tab['username'];
              $user_id=$tab['user_id'];
          ?>
          <a href="staff_shift.php?id=<?php echo $user_id; ?>" class="btn btn-lg"  style="font-family: 'comic sans ms' ;
                      font-weight: bold;
                      color: #FFFFFF !important;
                      font-size: 12px; 
                      word-wrap:break-word;
                      height:80px !important; 
                      width: 80px !important; 
                      background: #FF7D0C;
                      border: 4px solid #02550E;
                      text-transform: uppercase; 
                      padding-left: 5px; 
                      padding-right: 5px; 
                      padding-top: 30px; 
                      border-radius: 10px;"><?php echo $name; ?></a>
                 
                      <?php
                        }
                      ?> 
        </div>
         
      </div>
      </div>
    </div>
    <!-- end #content -->
  <div id="clear-staff-modal" class="modal fade" tabindex="-1" role="dialog" aria-labelledby="myModalLabel" aria-hidden="true" style="display: none;">
    <div class="modal-dialog" > 
       <div class="modal-content" id="dynamic-content" style="width: 620px;">   </div>  
    </div> 
  </div>

<!-- end page container --> 
<script type="text/javascript"> 
// $(document).ready(function(){
//   $(document).on('click','#clear-staff', function(e){
//     e.preventDefault();
//     var uid = $(this).data('id');
//     $('#dynamic-content').html('');
//     $.ajax({
//       url: 'clear_modal.php',
//       typr: 'POST',
//       data: {id:uid},
//       dataType: 'html'
//     })
//     .done(function(data){
//       $('#dynamic-content').html(''); // blank before load.
//       $('#dynamic-content').html(data);
//     })
//     .fail(function()
//     {
//       $('#dynamic-content').html('<i class="fa  fa-info-sign"></i> Something went wrong, Please try again...');
//       $('#modal-loader').hide();
//     });
//   }); 
// });
</script>
 </body>    
</html>
