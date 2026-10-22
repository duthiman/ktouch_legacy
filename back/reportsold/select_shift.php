 
<?php 
require("../clude/head.php");
$shift = $_GET['shift'];
$dpt = $_GET['dpt']; 
?>
 
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
		<div class="panel panel-default col-md-12 hidden-print" style="margin-bottom: 10px;">
      <div class="panel-body">
       <!--  <form role="form" class="form-inline" method="get" action="staff_time_report.php"> -->
          <div class="col-md-12 form-group">
            <div class="form-group row">
               <div class="form-group  col-md-3"> 
          <select class="form-control selectpicker" id="dpt" name="dpt" data-parsley-required="true">
              <option>----SELECT DEPARTMENT----</option>
              <option value="2">KITCHEN</option>
              <option value="1">BAR</option>    
          </select>
      </div>
              <div class="form-group col-md-2">
                <label class="sr-only" for="pick">Date</label>
                <input type="" data-provide="datepicker" data-date-format="yyyy-mm-dd" name="day1" class="form-control" id="pick" placeholder="DATE FROM" >
              </div>
              <div class="form-group col-md-2">
                <label class="sr-only" for="pick2">Date</label>
                <input type="" data-provide="datepicker" data-date-format="yyyy-mm-dd" name="day2" class="form-control" id="pick2" placeholder="DATE TO" >
              </div>
              <div class="form-group col-md-2">
            <button style="margin-left: 30px; " type="button" onclick="filter_report()" name="filter" class="btn btn-warning hidden-print"><i class="fa fa-search "></i></button>
              </div>
            </div>
          </div>
      <!--   </form> -->
      </div>
    </div> 

		<div class="panel  col-md-12 print">
      <span class="pull-right hidden-print" style="height: 50px; margin-top: 10px;">
        <a href="javascript:;" onclick="window.print()" class="btn btn-sm btn-success m-b-10  hidden-print" ><i class="fa fa-print m-r-5"></i> Print</a>
      </span>
		<div class="panel-body">
				<div id="filter_result" >
					
				</div>
			</div>
			</div>
	</div>
 
  <?php
    include('../clude/foot.php');
  ?>

            <script>
                 $(function() {
                    $('#pick').datepicker({
                        dateFormat:'yy-mm-dd',
                    });

                       $('#pick2').datepicker({
                        dateFormat:'yy-mm-dd',
                       });

                      
                   });
              </script>
 
<script type="text/javascript">
		function filter_report(){
		  from = document.getElementById('pick').value;
      to = document.getElementById('pick2').value;
      dpt = document.getElementById('dpt').value; 
		  xmlhttp = new XMLHttpRequest();
		  xmlhttp.open("GET","users_sales.php?from="+from+"&to="+to+"&dpt="+dpt,false);
		  xmlhttp.send(null);
		  document.getElementById("filter_result").innerHTML = xmlhttp.responseText;
		  document.getElementById("filter_result").style.visibility = "visible";
		}
	</script>
	
  
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
</script>  
</body>
</html>