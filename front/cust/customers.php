<?php
session_start();
include('../../inc/connection.php');
include('funcs.php');
$sale_id=$_GET['id'];
if ($sale_id==0 || empty($sale_id)) {
   header('Location:../dash/dash.php');
}
?>
<!DOCTYPE html>
<html lang="en">

<head>
    <!-- Required meta tags always come first -->
   <meta http-equiv="refresh" content="40" />
    <title>Landing</title>
 <!-- Bootstrap core CSS -->
    <link rel="stylesheet" href="../../assets/plugins/bootstrap/3.3.7/css/bootstrap.min.css">
<script src="../../assets/plugins/jquery/jquery-3.2.1.min.js"></script>
<script src="../../assets/plugins/bootstrap/3.3.7/js/bootstrap.min.js"></script>

<!-- ================== BEGIN PAGE LEVEL JS ================== -->
<script src="../../assets/plugins/Hullabaloo/js/hullabaloo.js"></script> 
<!-- ================== END PAGE LEVEL JS ================== -->
<!-- ================== BEGIN PAGE LEVEL JS ================== -->  
  <link rel="stylesheet" href="../../assets/plugins/keyboard/jkeyboard.css">
  <link rel="stylesheet" href="../../assets/plugins/keyboard/jquery-ui.min.css"> 
  <link href="../../assets/plugins/keyboard/keyboard.css" rel="stylesheet">
  <script src="../../assets/plugins/keyboard/jquery.keyboard.js"></script>
  <script src="../../assets/plugins/keyboard/jkeyboard.js"></script>
<!-- ================== END PAGE LEVEL JS ================== -->

<style type="text/css">
  body { 
    background: url(../assets/img/bg/k3.jpg) no-repeat center center fixed; 
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
  <div id="content" class="content" style="margin-top: 100px; margin-left: 20%;">
     
     <div class="panel panel-default col-md-8  "> 
      <div class="panel-heading" >
        <?php
        $user_id=$_SESSION['user_id']; 
        $get_tables = "SELECT user_id,username FROM kp_users WHERE role_id!=1";
        $get_tables_r = mysqli_query($connection,$get_tables) or die("Could not fetch the tables");
        while ($tab = mysqli_fetch_assoc($get_tables_r)) {
          $name = $tab['username'];
          $sid=$tab['user_id'];
          if ( column_total('kp_sales','amount',"user_id='$user_id' AND pay_status=2")>0) {
            
        ?>
          <button onclick="filter_uncleared(<?php echo $sid; ?>)" style="font-family: 'comic sans ms' ;
            font-weight: bold;
            color: #FFFFFF !important;
            font-size: 12px; 
            word-wrap:break-word;
            height:60px; 
            width: auto; 
            background: #000;
            border: 4px solid #e0bd65; 
            padding-left: 5px; 
            padding-right: 5px; 
            text-transform: uppercase;
            margin: 5px 5px 5px 5px; 
            border-radius: 10px;"><?php echo $name; ?><br><?php echo column_total('kp_sales','amount',"user_id='$sid' AND pay_status!=1"); ?>
          </button>
        <?php
          }
        }
        ?> 
      </div>
      <div class="panel-body" style="overflow-y:scroll; height: 450px; width: auto;">
   <table id="data-table-combine" class="table table-striped table-bordered">
              <thead>
                <tr>
                  <th colspan="5">  
                      <input type="text"  class="form-control pull-left" id="mpesaphone"  placeholder="ENTER PHONE NUMBER OR NAME TO SEARCH" style="width: 60%;" onkeyup="mpesasearch(<?php echo $pay_id; ?>)"> 
                      <a  class="btn btn-xs pull-right" style="
                        font-weight: bold;color: #FFFFFF !important;
                        font-size: 12px; 
                        background: #000;
                        border: 2px solid #e0bd65; 
                        padding : 3px;  
                        border-radius: 5px; " onclick="quick_cust();">ADD CUSTOMER ACCOUNT</a>
                  </th>
                </tr> 
                <tr> 
                  <th>Customer</th>
                  <th>Phone</th>
                  <th>Balance</th> 
                  <th>Action</th>  
                </tr>
              </thead>
              <tbody id="mpesarows">
                <?php 
                $get_menu = mysqli_query($connection,"SELECT * FROM kp_customers WHERE del_status=2") or die("Could not get the customer invoices with balances". mysqli_error($connection));
                while ($mat = mysqli_fetch_assoc($get_menu)) {
                  $name=$mat['name'];
                  $phone=$mat['phone'];
                  $bal=$mat['bal']; 
                  $cust_id=$mat['cust_id'];
                  ?> 
                    <td><?php echo $name; ?></td> 
                    <td><?php echo $phone; ?></td>
                    <td><?php echo $bal; ?></td> 
                    <td><a class="btn btn-xs" style="
                        font-weight: bold;color: #FFFFFF !important;
                        font-size: 12px; 
                        background: #000;
                        border: 2px solid #e0bd65; 
                        padding : 3px;  
                        border-radius: 5px; " onclick="addbill(<?php echo $cust_id; ?>);">ADD BILL</a></td>
                  </tr>
                <?php
                }
                ?> 
              </tbody>
            </table>
        </div>
      </div>
    </div>
    <!-- end #content --> 

    <div id="modal" class="modal fade" tabindex="-1" role="dialog" aria-labelledby="myModalLabel" aria-hidden="true" style="display: none;"> 
      <div class="modal-dialog" > 
        <div class="modal-content" id="load" ></div> 
      </div>
    </div>
<!-- end page container --> 
 <script type="text/javascript">
  $('#mpesaphone').keyboard({ }) ;

  function quick_cust(){ 
    $('#modal').modal('show'); 
    $.ajax({
      url: 'quick_cust.php',
      type: 'POST',                           
      data: {},
      dataType: 'html'
    })
    .done(function(data){
      $('#load').html(data);
      //$('#modal').modal('toggle'); 
    })
    .fail(function(){
      $('#load').html('<div class="alert alert-danger"><a href="#" class="close" data-dismiss="alert">&times;</a><strong>Warning!</strong> Error occured.</div>');
    }); 
  }

  function addbill(cust){ 
    var id=<?php echo $sale_id; ?>;
    $('#modal').modal('show'); 
    $.ajax({
      url: 'addbill.php',
      type: 'POST',                           
      data: {cust:cust,id:id},
      dataType: 'html'
    })
    .done(function(data){
      $('#load').html(data);
      //$('#modal').modal('toggle'); 
    })
    .fail(function(){
      $('#load').html('<div class="alert alert-danger"><a href="#" class="close" data-dismiss="alert">&times;</a><strong>Warning!</strong> Error occured.</div>');
    }); 
  } 
  function filter_uncleared(id){
    xmlhttp = new XMLHttpRequest();
    xmlhttp.open("GET","staff_sales.php?id="+id,false);
    xmlhttp.send(null);
    document.getElementById("load").innerHTML = xmlhttp.responseText;
    document.getElementById("load").style.visibility = "visible";
  } 
$(document).ready(function(){
  $(document).on('click','#getItem', function(e){
    e.preventDefault();
    var uid = $(this).data('id'); // get id of clicked row
    $('#dynamic-content').html(''); // leave this diff blank
    $('#modal-loader').show(); // load ajax loader button cliked
    $.ajax({
      url: 'cashier_modal.php',
      typr: 'POST',
      data: {id:uid},
      dataType: 'html'
    })
    .done(function(data){
      $('#dynamic-content').html(''); // blank before load.
      $('#dynamic-content').html(data);
    })
    .fail(function()
    {
      $('#dynamic-content').html('<i class="fa  fa-info-sign"></i> Something went wrong, Please try again...');
      $('#modal-loader').hide();
    });
  }); 
});
</script>
 </body>    
</html>
