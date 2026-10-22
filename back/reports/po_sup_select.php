<?php 
require("../clude/head.php");
$day = $_GET['day'];
$tcash=0;
$tmpesa=0;
$tvisa=0;
$sum=0;
?>
 

<style type="text/css">
	.table thead th,.table>thead>tr>th>td {
 color:#242a30;
 font-weight:600;
 border-bottom:1px solid #000 !important;
 border-right:1px solid #000 !important;
 border-left:1px solid #000 !important;
 border-top:1px solid #000 !important;
}
.table>tbody>tr>td,.table>tbody>tr>th,.table>tfoot>tr>td,.table>tfoot>tr>th,.table>thead>tr>td,.table>thead>tr>th {
border-bottom:1px solid #000 !important;
 border-right:1px solid #000 !important;
 border-left:1px solid #000 !important;
 border-top:1px solid #000 !important;
 padding:3px 3px 3px 3px;
}
</style>
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
  <div class="panel panel-default hidden-print col-md-12" style="margin-bottom: 10px;">
    <div class="panel-body"> 
        <div class="form-group row">
          <div class="form-group col-lg-2">
            <label class="sr-only" for="pick">Date</label>
            <input type="date" data-provide="datepicker" data-date-format="yyyy-mm-dd" name="day1" class="form-control" id="pick" >
          </div>
          <div class="form-group col-lg-2">
            <label class="sr-only" for="pick2">Date</label>
            <input type="date" data-provide="datepicker" data-date-format="yyyy-mm-dd" name="day2" class="form-control" id="pick2" >
          </div>
           <div class="form-group col-lg-2">
            <label class="sr-only" for="pick">VENDOR</label>
              <select class="form-control selectpicker" id="sup" name="sup" data-parsley-required="true">
              <option>----SELECT VENDOR----</option> 
              <?php
              $sup = "SELECT * FROM kp_suppliers";
              $sup_r = mysqli_query($connection,$sup) or die("Error getting Supppliers");
              while ($ct = mysqli_fetch_assoc($sup_r)) {
                $name = $ct['sup_name'];
                $sup_id = $ct['sup_id'];
                ?>
                <option  value="<?php echo $sup_id; ?>"><?php echo $name; ?></option>
                <?php
                }
                ?>
              </select>
          </div>
          <div class="form-group col-lg-3">
            <button style="margin-left: 30px; " type="button" onclick="filter_sup()" name="filter" class="btn btn-warning"><i class="fa fa-search"></i></button> 
          </div>
        </div>
        
    </div>
  </div>
    
		<div id="filter_result" class="col-md-12" style="background-color: white;"></div>
	 
</div>
</div> 
<script type="text/javascript">
  function filter_sup(){
    start_date = document.getElementById('pick').value;
    end_date = document.getElementById('pick2').value;
    sup = document.getElementById('sup').value;
    $("#filter_result").html("<img src=../../assets/img/loader/pwait.gif>");
    xmlhttp = new XMLHttpRequest();
    xmlhttp.open("GET","fil_sup_po.php?start_date="+start_date+"&end_date="+end_date+"&sup="+sup,false);
		xmlhttp.send(null);
		document.getElementById("filter_result").innerHTML = xmlhttp.responseText;
		document.getElementById("filter_result").style.visibility = "visible";
	}
	</script>
	
	<!-- end page container -->

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
  <script src="../../assets/js/theme/default.min.js"></script>
  
  <script src="../../assets/plugins/select2/dist/js/select2.min.js"></script> 
  <!-- ================== BEGIN PAGE LEVEL JS ================== -->
    <script src="../../assets/plugins/Hullabaloo/js/hullabaloo.js"></script> 
    <!-- ================== END PAGE LEVEL JS ================== -->
  <script src="../../assets/js/apps.min.js"></script>
  <!-- ================== END BASE JS ================== -->
 
   
<script>
    $(document).ready(function() {
      App.init(); 
      $(".selectpicker").select2(); 
	}); 
 </script> 
</body>
</html>
  
        