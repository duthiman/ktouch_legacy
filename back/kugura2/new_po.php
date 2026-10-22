<?php  
session_start();
include('../../inc/connection.php');
require_once('funcs.php'); 
$user_id = $_SESSION['user_id'];
page_restrict();
$folder="front/kugura/"; 
thesion_page($folder);
$get_details = mysqli_query($connection,"SELECT * FROM kp_thesion WHERE user_id='$user_id'") or die("Could not get the user permissions");
$row=mysqli_fetch_assoc($get_details); 
$po_id = $row['po_id'];
$sup_id = $row['sup_id'];
$day = $row['day']; 
$panel= $row['panel']; 
if ($panel==1) {
 header("Location:../../log_out.php");
}elseif ($po_id==0) {
  ?>
 <script> 
  window.location = "../dash/";
 </script>
  <?php
} 
?> 
<!DOCTYPE html> 
<html lang="en"> 
<head>

  <meta charset="utf-8" />
  <title>PURCHASE PAGE</title>
  <meta content="width=device-width, initial-scale=1.0, maximum-scale=1.0, user-scalable=no" name="viewport" /> 
   <!-- ================== BEGIN BASE CSS STYLE ================== --> 
  <link href="../../assets/plugins/jquery-ui/jquery-ui.css" rel="stylesheet" />
  <link href="../../assets/plugins/bootstrap/4.0.0/css/bootstrap.css" rel="stylesheet" />
  <link href="../../assets/plugins/font-awesome/5.11.2/css/all.min.css" rel="stylesheet" /> 
  <link href="../../assets/css/default/desktop.css" rel="stylesheet" media="screen and (min-width: 768px)" />
  <link href="../../assets/css/default/mobile.css" rel="stylesheet" media="screen and (max-width: 767px)" /> 
  <!-- ================== END BASE CSS STYLE ================== -->  
  <!-- ================== BEGIN DATATABLE================== --> 
  <link href="../../assets/plugins/select2/dist/css/select2.min.css" rel="stylesheet" />
  <!-- ================== END DATATABLE ================== --> 
  <!-- ================== BEGIN PAGE LEVEL STYLE ================== -->
  <link href="../../assets/plugins/gritter/css/jquery.gritter.css" rel="stylesheet" />
  <!-- ================== END PAGE LEVEL STYLE ================== -->
  <style>
    .footer {
      position: fixed;
      margin-left:  0;
      bottom: 0;
      width: 100%;
      background-color: purple;
      color: white;
      text-align: center;
    }
    @media (max-width: 767px) {
      .footer {
        display: none !important;
      }
      .panel-wrapper {
        flex-direction: column !important;
      }
      .panel-left {
        width: 100% !important;
        margin-left: 0 !important;
        margin-bottom: 15px !important;
      }
      .panel-right {
        width: 100% !important;
        margin-left: 0 !important;
      }
      .quick-actions {
        flex-direction: column !important;
      }
      .quick-actions > div {
        margin-bottom: 10px !important;
        border-right: none !important;
        border-bottom: 1px solid #80008036 !important;
      }
      #bar {
        font-size: 16px !important; /* Prevents zoom on iOS */
      }
      #qty, #price, #total {
        font-size: 16px !important; /* Prevents zoom on iOS */
      }
      .btn-xs {
        padding: 0.25rem 0.5rem !important;
        font-size: 0.875rem !important;
      }
     
  /* ... your existing mobile styles ... */
  
  /* FIX: Ensure selected item name is visible in barcode input on mobile */
  #bar {
    min-height: 44px !important;       /* Apple HIG minimum touch target */
    font-size: 16px !important;        /* Prevents iOS zoom + ensures readability */
    color: #000 !important;            /* Force dark text for visibility */
    background-color: #fff !important; /* Pure white background */
    padding: 10px 15px !important;     /* Ample padding for text visibility */
    border: 2px solid #4CAF50 !important; /* Green border when populated */
    box-shadow: 0 0 8px rgba(76, 175, 80, 0.3) !important; /* Subtle glow */
    transition: all 0.3s ease !important;
  }
  
  /* Visual feedback when input has value (item selected) */
  #bar:not(:placeholder-shown) {
    border-color: #2e7d32 !important;
    background-color: #f1f8e9 !important; /* Light green background */
    font-weight: 600 !important;
  }
  
  /* Keep button visible and tappable */
  .input-group-prepend .btn {
    min-width: 44px !important;
    min-height: 44px !important;
    font-size: 18px !important;
  }
  
  /* Prevent input group from collapsing */
  .input-group-xs {
    flex-wrap: nowrap !important;
    width: 100% !important;
  }
  
  .input-group-xs > .form-control {
    flex: 1 !important;
    min-width: 0 !important; /* Prevent overflow */
  }
}
  </style>
</head>

<body style="background-image: url('../../assets/img/bg/b5.jpg'); background-size: cover; ">
  <!-- begin #page-container -->
  <div id="page-container" class="fade page-without-sidebar page-header-fixed">
      <!-- begin #header -->  
  <?php 
    require("../clude/header.php");
  ?>
  <!-- end #header -->  
<!-- begin #content -->
<div id="content" class="content"> 
  <!-- begin row --> 
  <div class="row panel-wrapper" style="align-items: flex-start; flex-direction: row;">
  <div class="panel panel-left paper-cut" style="margin-left: 8px; width: 60%;" >
    <div class="row" style="padding-top: 5px;">
      <div class="col-lg-12" id="results"></div> 
      <div class="form-group col-md-5 col-xs-4 col-12">
        <div class="input-group input-group-xs">
          <div class="input-group-prepend">
            <a  class="btn btn-warning text-white" data-toggle="modal"data-target="#modal" id="quick_item"><i class='fa  fa-plus' ></i> New</a>   
          </div> 
          <input type="text" class="form-control form-control-xs" name="bar" onfocus="this.value=''" id="bar" autofocus />
          <input type="hidden" class="form-control" name="item_id" id="item_id"/> 
        </div>
      </div>
      <div class="col-md-2 col-xs-2 col-3">
        <input id="qty" type="" placeholder="Qty"  class="form-control form-control-xs" value="1">
      </div>
      <div class="col-md-2 col-xs-2 col-3">
        <input id="price" placeholder="price" onkeyup="get_price();"   class="form-control form-control-xs"> 
      </div> 
      <div class="col-md-2 col-xs-2 col-3">
        <input id="total" placeholder="total" onkeyup="get_total();"   class="form-control form-control-xs"> 
      </div> 
      <div class="col-md-1 col-xs-1 col-1">
        <a class="btn btn-md btn-purple" id="additem"><i class="fa fa-plus"></i>ADD</a>
      </div>
    </div> 
    <div class="table-responsive p-5" id="d1"> </div> 
  </div>
  <div class="panel panel-right paper-cut" style="margin-left: 10px; width: 38%;" >
    <div class="row" style="margin: 2px; padding: 1px 1px;"> 
      <div class="col-md-2 col-3 text-center" style="height: 40px; padding: 1px; border-right: 1px solid #80008036">
        <div class="col-md-12">
          <a  data-toggle="modal"data-target="#modal" id="hold" class="btn btn-xs btn-warning"><h6>HOLD</h6></a>
        </div>
      </div>
      <div class="col-md-3 col-3 text-center" style="height: 40px; padding: 1px; border-right: 1px solid #80008036">
        <h6>LPO NO :<br><?php echo $po_id; ?></h6> 
      </div>
      <div class="col-md-3 col-3 text-center" style="height: 40px; padding: 1px; border-right: 1px solid #80008036">
        <a  data-toggle="modal"data-target="#modal" id="sup-change" onclick="$('#load').html('');"> <h6>SUPPLIER:<br> <b id="supo"><?php echo get_sup($sup_id); ?></b></h6> </a>
      </div> 
      <div class="col-md-3 col-3 text-center" style="height: 40px; padding: 1px;">
        <a  data-toggle="modal"data-target="#modal" id="po-day" onclick="$('#load').html('');">
          <h4 id="po-date" style="color: purple;"><b><?php echo $day; ?></b></h4>
        </a>
      </div> 
    <div class="row" id="d33"  style="margin: 2px; padding: 5px; color: white;"></div>
    </div>
    <!-- begin panel -->
    <div class="panel-body" >  
        <div class="row p-5">
          <div class="col-12" ><b>REF NO: </b></div><br>
          <div class="col-12">
            <input type="text" class="form-control" name="refno" id="refno" />
          </div> 
        </div>    
      <hr>
      <div class="row" style="margin: 5px 5px; border: 1px solid #d9e0e7; padding: 5px;"> 
        <label class="col-md-12 col-12" style=" #6FD64B; font-size: 16px;">ADD TAX:</label> 
        <div class="radio col-md-4 col-4">
          <label><input type="radio" name="tax-by" value="0" checked id="none">None</label>
        </div>
        <div class="radio col-md-4 col-4">
          <label><input type="radio" name="tax-by" value="1">By Item</label>
        </div>
        <div class="radio col-md-4 col-4">
          <label><input type="radio" name="tax-by" value="2">By LPO</label>
        </div> 
        <div class="col-md-6 col-12" id="div-tax"  style="display: none;">
          <ul class="list-group">
            <li class="list-group-item global-discount-group" style="height: 150px"> 
              <div class="row">
                <label style="text-align: center; color: #6FD64B; font-size: 18px;">TAX:</label> 
                <div class="radio col-md-12 col-12">
                  <label><input type="radio" name="tax" value="1">Inclusive</label>
                </div>
                <div class="radio col-md-12 col-12">
                  <label><input type="radio" name="tax" value="2">Exclusive</label>
                </div>
              </div>
            </li>
          </ul>
        </div>
        <div class="col-md-6 col-12" id="div-perc"  style="display: none;">
          <ul class="list-group">
            <li class="list-group-item global-discount-group" style="height: 150px;">
              <div class="row">
                <label style="text-align: center; color: #6FD64B; font-size: 18px;">%:</label>
                <div class="radio col-md-12 col-12">
                  <label><input type="radio" name="perc" id="perc" value="1" checked>16%</label>
                </div>
                <div class="radio col-md-12 col-12">
                  <label><input type="radio" name="perc" id="perc" value="2">Others</label>
                  <input type="text" class="form-control" name="others" id="perc-input" onkeyup="tax_perc();" />
                </div> 
              </div>
            </li>
          </ul>
        </div>
        <div class="col-md-12 col-12 m-t-5">
          <ul class="list-group">
            <li class="list-group-item global-discount-group"> 
              <div class="pull-left" style="text-align: center; color: #6FD64B; font-size: 22px;"><b>VATABLE:</b>&nbsp;<b id="vatable"></b></div>
              <div class="pull-right" style="text-align: center; color: #6FD64B; font-size: 22px;"><b>VAT:</b>&nbsp;<b id="vatt">0</b></div> 
            </li>
            <li class="list-group-item global-discount-group"> 
              <div class="pull-left" style="text-align: center; color: #6FD64B; font-size: 22px;"><b>TOTAL:</b>&nbsp;<b id="tt"></b></div> 
            </li>
          </ul> 
        </div>
      </div> 
      <div class="panel-body" style="margin-top: 10px;">
        <a data-toggle="modal" data-target="#modal" id="po-btn" class="btn  btn-xs btn-purple"  onclick="$('#load').html('');"> COMPLETE LPO</a> 
        <a data-toggle="modal" data-target="#modal" id="cancel_btn" class="btn btn-danger pull-right btn-xs"  onclick="$('#load').html('');">CANCEL</a>
      </div> 
    </div>  
  </div>
</div>
</div>
<!-- end row -->
</div>
<!-- end #content -->

<div class="footer">
  <div class="row" style="margin: 2px; padding: 1px 1px;"> 
    <div class="col-md-1 text-center" style="height: 30px; padding: 1px; border-right: 1px solid #eee;">
      <a class=""><b>F2 -  LOOKUP</b></a>
    </div>
    <div class="col-md-1 text-center" style="height: 30px; padding: 1px; border-right: 1px solid #eee;">
      <a class=""><b>INSERT -  CHECK OUT</b></a>
    </div>
    <div class="col-md-1 text-center" style="height: 30px; padding: 1px; border-right: 1px solid #eee;">
      <a class=""><b>ESC-HOLD SALE</b></a>
    </div> 
    <div class="col-md-1 text-center" style="height: 30px; padding: 1px; border-right: 1px solid #eee;">
      <a class=""><b>DELETE -  CANCEL SALE</b></a>
    </div> 
    <div class="col-md-1 text-center" style="height: 30px; padding: 1px; border-right: 1px solid #eee;"> </div> 
  </div>
</div>

    <!-- end #sidebar -->
<div  id="modal" class="modal fade bs-example-modal-lg" tabindex="-1" role="dialog" aria-labelledby="myModalLabel"  style="display: none; width: auto;">
  <div class="modal-dialog" id="load" style="padding: 10px;"> </div>
</div>
<div  id="modal2" class="modal fade bs-example-modal-lg" tabindex="-1" role="dialog" aria-labelledby="myModalLabel"  style="display: none; width: auto;">
  <div class="modal-dialog" id="load2" style="padding: 10px;"> </div>
</div>
 <!-- end page container --> 
<?php include("../clude/foot.php") ?>  
  <script src="../../assets/plugins/select2/dist/js/select2.min.js"></script>  
<script src="kugura.js"></script>
</body>
</html>