 
<?php 
require("../clude/head.php");
$shift = isset($_GET['shift']) ? trim($_GET['shift']) : date('Y-m-d');
$sum=0;
?>
 
<!-- begin #page-container -->
<div id="page-container" class="page-container fade page-without-sidebar page-header-fixed page-with-top-menu">
  <!-- begin #header -->  
 <?php 
    require("../clude/header.php");
 ?>
  <!-- end #header -->  
  <!-- begin #sidebar -->
 <?php 
    require("../clude/top_menu.php");
 ?>
 <!-- end #sidebar -->
  <!-- begin #content -->
	<div id="content" class="content mt-2">
    <div class="panel panel-default col-md-12 hidden-print" style="margin-bottom: 10px;">
      <div class="panel-body">
        <form role="form" class="form-inline" method="get" action="store_take.php" onsubmit="return false;">
          <div class="col-md-12 form-group">
            <div class="form-group row"> 
              <div class="form-group col-md-6">
                <label class="sr-only" for="pick">Date</label>
                <input type="date" data-provide="datepicker" data-date-format="yyyy-mm-dd" name="shift" class="form-control" id="pick" value="<?php echo htmlspecialchars($shift, ENT_QUOTES, 'UTF-8'); ?>">
              </div>
            </div>
            <button style="margin-left: 30px; " type="button" onclick="filter_report()" name="filter" class="btn btn-warning hidden-print"><i class="fa fa-search "></i> View stock take</button>
          </div>
        </form>
      </div>
    </div> 

    <div class="panel  col-md-12 print">
      <span class="pull-right hidden-print" style="height: 50px;">
        <a href="javascript:;" onclick="window.print()" class="btn btn-sm btn-success m-b-10  hidden-print" ><i class="fa fa-print m-r-5"></i> Print</a>
      </span>
    <div class="panel-body">
        <div id="filter_result" aria-live="polite">
          <div class="text-center text-muted p-20"><i class="fa fa-spinner fa-spin"></i> Loading stock take...</div>
        </div>
      </div>
      </div>
  </div>
 
  <?php
    include('../clude/foot.php');
  ?>

            <script>
                 $(function() {
                    $('#pick').datepicker({ dateFormat:'yy-mm-dd' });
                    filter_report();

                      
                   });
              </script>
 
<script type="text/javascript">
    function filter_report(){
      var shift = document.getElementById('pick').value;
      var result = document.getElementById('filter_result');
      if (!shift) {
        result.innerHTML = '<div class="alert alert-warning">Please select a stock-take date.</div>';
        return;
      }
      result.innerHTML = '<div class="text-center text-muted p-20"><i class="fa fa-spinner fa-spin"></i> Loading stock take...</div>';
      fetch('store_take_content.php?shift=' + encodeURIComponent(shift), { credentials: 'same-origin' })
        .then(function(response) {
          if (!response.ok) throw new Error('Request failed');
          return response.text();
        })
        .then(function(html) { result.innerHTML = html; })
        .catch(function() {
          result.innerHTML = '<div class="alert alert-danger">The stock take could not be loaded. Please try again.</div>';
        });
    }
  </script>
  
 
