 
<?php 
require("../clude/head.php");
//$shift = $_GET['shift'];
$tcash=0;
$tmpesa=0;
$tvisa=0;
$sum=0;
?>
  <!-- ================== BEGIN PAGE LEVEL STYLE ================== -->
  <link href="../../assets/plugins/DataTables/media/css/dataTables.bootstrap.min.css" rel="stylesheet" />
  <link href="../../assets/plugins/DataTables/extensions/FixedHeader/css/fixedHeader.bootstrap.min.css" rel="stylesheet" />
  <link href="../../assets/plugins/DataTables/extensions/Responsive/css/responsive.bootstrap.min.css" rel="stylesheet" />
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
    require("../clude/top_menu.php");
  ?>
  <!-- end #sidebar -->
  <!-- begin #content -->
  <div id="contents" class="contents" style="margin: 50px;">
    <div class="panel panel-default col-md-12 hidden-print" style="margin-bottom: 10px;">
      <div class="panel-body">
        <form role="form" class="form-inline" method="get" action="staff_time_report.php">
          <div class="col-md-12 form-group">
            <div class="form-group row">  
              <div class="form-group  col-md-6"> 
                <select class="form-control selectpicker" id="counter" name="counter" data-parsley-required="true">
                  <option>----SELECT COUNTER----</option>
                  <?php
                  $cat = "SELECT * FROM kp_counters";
                  $cat_r = mysqli_query($connection,$cat) or die("Error getting categories");
                  while ($ct = mysqli_fetch_assoc($cat_r)) {
                    $name = $ct['name'];
                    $cntr_id = $ct['cntr_id'];
                    ?>
                    <option  value="<?php echo $cntr_id; ?>"><?php echo $name; ?></option>
                    <?php
                  }
                  ?>
                </select>
              </div>
              <div class="form-group col-md-6">
                <label class="sr-only" for="pick">Date</label>
                <input type="" data-provide="datepicker" data-date-format="yyyy-mm-dd" name="day1" class="form-control" id="pick" >
              </div>
            </div>
            <button style="margin-left: 30px; " type="button" onclick="filter_report()" name="filter" class="btn btn-warning hidden-print"><i class="fa fa-search "></i></button>
          </div>
        </form>
      </div>
    </div> 

    <div class="panel panel-purple col-md-12" id="filter_result">
      
    </div>
  </div>
 
  <?php
    include('../clude/foot.php');
  ?>
  <link rel="stylesheet" href="../../assets/plugins/sweetalert/dist/sweetalert2.min.css">
  <script src="../../assets/plugins/sweetalert/dist/sweetalert2.all.min.js"></script>

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
    function ctrCloseVarianceReprint() {
      var modal = document.getElementById('ctr-reprint-modal');
      if (modal) modal.style.display = 'none';
    }

    function ctrVarianceNotice(icon, title, text) {
      if (window.Swal && typeof window.Swal.fire === 'function') {
        return window.Swal.fire({
          icon: icon,
          title: title,
          text: text,
          confirmButtonText: 'OK'
        });
      }
      alert(text);
    }

    function ctrConfirmVarianceReprint(counter, shift) {
      var modal = document.getElementById('ctr-reprint-modal');
      var confirm = document.getElementById('ctr-reprint-confirm');
      if (!modal || !confirm) {
        alert('Please load the variance report first.');
        return;
      }
      modal.style.display = 'block';
      confirm.onclick = function() {
        confirm.disabled = true;
        confirm.textContent = 'Queueing...';
        $.post('queue_variance.php', { counter: counter, shift: shift })
          .done(function(response) {
            if (response && response.ok) {
              ctrCloseVarianceReprint();
              ctrVarianceNotice('success', 'Report queued', 'The variance report was added to the print queue.');
            } else {
              ctrVarianceNotice('error', 'Queue failed', response && response.message ? response.message : 'Could not queue the variance report.');
            }
          })
          .fail(function() { ctrVarianceNotice('error', 'Connection failed', 'Could not connect to the print queue.'); })
          .always(function() {
            confirm.disabled = false;
            confirm.textContent = 'Yes, queue report';
          });
      };
    }

    function filter_report(){
      shift = document.getElementById('pick').value;  
      counter = document.getElementById('counter').value;
      $("#filter_result").html("<img src=../../assets/img/loader/pwait.gif>");
      xmlhttp = new XMLHttpRequest();
      xmlhttp.open("GET","counter_take_content.php?shift="+shift+"&counter="+counter,false);
      xmlhttp.send(null);
      document.getElementById("filter_result").innerHTML = xmlhttp.responseText;
      document.getElementById("filter_result").style.visibility = "visible";
      TableManageFixedHeader.init();
    }
  </script>
  
 
 
<!-- ================== BEGIN PAGE LEVEL JS ================== -->
  <script src="../../assets/plugins/DataTables/media/js/jquery.dataTables.js"></script>
  <script src="../../assets/plugins/DataTables/media/js/dataTables.bootstrap.min.js"></script>
  <script src="../../assets/plugins/DataTables/extensions/FixedHeader/js/dataTables.fixedHeader.min.js"></script>
  <script src="../../assets/plugins/DataTables/extensions/Responsive/js/dataTables.responsive.min.js"></script>
  <script src="../../assets/js/demo/table-manage-fixed-header.demo.min.js"></script>
  <!-- ================== END PAGE LEVEL JS ================== -->

<script type="text/javascript">
  // $(document).ready(function() { 
  //   App.init();    
  // }); 
</script>  
</body>
</html> 
 
