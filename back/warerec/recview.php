<?php 
require("../clude/head.php");
$user_id = $_SESSION['user_id'];
$req_id = $_GET['req_id']; 
$no=0;

?>
 
<!-- begin #page-container -->
<div id="page-container" class="page-container fade page-without-sidebar page-header-fixed page-with-top-menu">

  <!-- begin #header -->  
  <?php 
    require("../clude/header.php");
    
  ?>
  <!-- end #header -->  
  <!-- begin #sidebar -->
   <!-- begin #sidebar -->
  <?php 
    require("../clude/top_menu.php");
  ?>
  <!-- end #sidebar -->
<!-- begin #content -->
<div id="content" class="content"> 
  <?php
  if (isset($_GET['msg'])) {
    if ($_GET['msg'] == 6) {
      ?>
      <div class="alert alert-success">
        <a href="#" class="close" data-dismiss="alert"> &times; </a>
			   <strong>Approved !</strong> Requisition updated Succesfully.
			</div>
      <?php
    }elseif ($_GET['msg'] == 2){
      ?>
      <div class="alert alert-lime col-md-6" >
        <a href="#" class="close" data-dismiss="alert">&times;</a>
			   <strong>Stock</strong> was updated Succesfully.
			</div>
    <?php
    }
  }
  ?>
  <div class="row">
    <div class="col-md-8 col-12">
      <div class="panel  panel-purple" data-sortable-id="index-1">
        <div class="panel-heading">
          <span style="color:white;  ">ITEMS TO  RECEIVE TO STORE AS PER REQUISITION <?PHP echo $req_id; ?></span>
        </div>
        <div class="panel-body">
          <div class="dataTable_wrapper">
            <table id="data-table-combine" class="table table-striped table-bordered table-hover">
              <thead>
                <tr>
                  <th>#</th> 
                  <th>Item Name</th> 
                  <th>Req Qty</th> 
                  <th>Current Store Stock</th> 
                  <th>Stock Status</th>
                  <th>Action</th>          
                </tr>
              </thead>
              <tbody>
                <?php 
                $stocka=0;
                $po_items = mysqli_query($connection,"SELECT * FROM kp_req_items WHERE req_id='$req_id' AND issue_status=1 AND rec_status=2");
                if (mysqli_num_rows($po_items) < 1) {
                  $update_status = mysqli_query($connection,"UPDATE kp_req SET rec_status=1,rec_user = '$user_id',rec_date=CURRENT_DATE   WHERE req_id='$req_id'") or die(mysqli_error($connection));
                  ?>
                  <script> location.replace("warereq.php?msg=1"); </script>
                  <?php
                }else{
                  
                  $no=0;
                  while ($sa = mysqli_fetch_assoc($po_items)) {
                    $item_id = $sa['item_id'];
                    $no=$no+1; 
                    ?>
                    <tr>
                      <td><?php echo $no; ?></td>
                      <td><?php echo get_item($item_id); ?></td>
                      <td><?php echo $sa['req_qty']; ?></td>
                      <td><?php echo get_column('kp_items','store_qty',"item_id='$item_id'"); ?></td>  
                      <td>
                        <?php 
                        if (get_column('kp_items','ware_qty',"item_id='$item_id'")>=$sa['req_qty']) {
                          echo "<b style='color:green';> STOCK OK</b>";
                          $adjbtn='';
                        }else{
                          echo "<b style='color:red';> LESS STOCK</b>";
                          $adjbtn="<button class='btn btn-primary btn-sm' data-toggle='modal' data-target='#rec-modal' data-id='$item_id' id='adjbtn'>ADJUST STOCK</button>";
                          $stocka=$stocka+1;
                        } 
                        ?>
                      </td> 
                      <td> 
                        <button data-toggle="modal" data-target="#rec-modal" data-id="<?php echo $req_id; ?>" data-id2="<?php echo $item_id; ?>" id="receive"  class="fa fa-md btn btn-outline-success btn-sm">RECEIVE ITEM</button>   
                        <?php echo $adjbtn; ?>
                      </td>
                    </tr>
                  <?php
                  }
                }
                ?>
              </tbody>
            </table>
          </div>
        </div> 
      </div>
      
    </div>
    <div class="col-md-4 col-12"> 
      <!-- <div class="panel">
      <div class="panel-body text-center">
        <div class="row">
          <div class=" col-md-7 col-12"> 
            <?php 
            if ($stocka<=0) {
              ?>
              <a data-toggle="modal" data-target="#rec-modal" data-id="<?php echo $req_id; ?>"  id="issueall"  class="btn btn-outline-success btn-sm mb-2" style="margin-left: 10px; color: green;">ISSUE ALL ITEMS AS REQUESTED</a>
              <?PHP
            }elseif ($stocka==$no) { 
            }else{
              ?>
              <a data-toggle="modal" data-target="#rec-modal" data-id="<?php echo $req_id; ?>"  id="issueall"  class="btn btn-outline-warning btn-sm mb-2" style="margin-left: 10px; color: orange;">ISSUE ALL ITEMS WITH STOCK</a>
              <?PHP 
            }
            ?> 
          </div>
          <div class=" col-md-4 col-12"> 
            <a data-toggle="modal" data-target="#rec-modal" data-id="<?php echo $req_id; ?>"  id="cancel"  class="btn btn-outline-danger btn-sm" style="margin-left: 10px; color: red;">CANCEL REQUEST</a>
          </div>
        </div>
      </div>
      </div> -->
      <div class="alert alert-info fade show">
        <!-- <h5>WHAT TO DO:</h5>
          <ul class="p-l-25 m-b-15">
            <li>You can issue more or less than what is Requested</li>
            <li>You can remove an item with a reason from the request with a reason</li>
            <li>If stock is not enough you can adjust stock to issue</li>
            <li>Portined items issues portions <i>i.e 1KG of potatoes gives 2 chips</i></li>
            <li>Portined items issues portions <i>i.e 1KG of potatoes gives 2 chips</i></li>
          </ul>
        -->
        <!-- <p class="m-b-20">
          <a class="btn btn-inverse btn-sm">View Official Website</a>
        </p> -->
      </div> 
      <div class="alert alert-danger fade show">
        <!-- <h5>POINTS TO NOTE:</h5>
        <ol class="p-l-25 m-b-15">
          <li>Make sure you purchases are one right to help in adding the stocks all time</li>
          <li>Any issues with stock is reported in time</li>
          <li>If you dont understand the process ask</li> 
          <li>YOU HAVE TO CLEAR THIS LIST FOR THE ISSUE TO APPEAR IN THE COUNTER</li> 
          <li>YOU CAN ISSUE 00 TO KEEP THE TRACK OF THE ITEM REQUEST IF THERE IS NO STOCK</li> 
        </ol> -->
        <!-- <p class="m-b-20">
          <a class="btn btn-inverse btn-sm">View Official Website</a>
        </p> -->
      </div> 
    </div>
  </div>

      </div>
    </div>
     <div  id="rec-modal" class="modal fade bs-example-modal-lg" tabindex="-1" role="dialog" aria-labelledby="myModalLabel" style="display: none;">
      <div class="modal-dialog" id="rec-load"> 
        
      </div>
    </div> 
   <!--<div  id="rec-modal" class="modal fade bs-example-modal-lg" tabindex="-1" role="dialog" aria-labelledby="myModalLabel" style="display: none;">
      <div class="modal-dialog"> 
            <div id="result"></div> 
            <div id="dynamic-content1"></div>
          </div> 
      </div>-->
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
  <script src="../../assets/js/theme/default.min.js"></script>
  
  <script src="../../assets/plugins/gritter/js/jquery.gritter.js"></script>
  <script src="../../assets/js/apps.min.js"></script>
  <!-- ================== END BASE JS ================== -->

  <!-- ================== BEGIN PAGE LEVEL JS ================== -->
  <script src="../../assets/plugins/DataTables/media/js/jquery.dataTables.js"></script>
  <script src="../../assets/plugins/DataTables/media/js/dataTables.bootstrap.min.js"></script>
  <script src="../../assets/plugins/DataTables/extensions/Buttons/js/dataTables.buttons.min.js"></script>
  <script src="../../assets/plugins/DataTables/extensions/Buttons/js/buttons.bootstrap.min.js"></script>
  <script src="../../assets/plugins/DataTables/extensions/Buttons/js/buttons.flash.min.js"></script>
  <script src="../../assets/plugins/DataTables/extensions/Buttons/js/jszip.min.js"></script>
  <script src="../../assets/plugins/DataTables/extensions/Buttons/js/pdfmake.min.js"></script>
  <script src="../../assets/plugins/DataTables/extensions/Buttons/js/vfs_fonts.min.js"></script>
  <script src="../../assets/plugins/DataTables/extensions/Buttons/js/buttons.html5.min.js"></script>
  <script src="../../assets/plugins/DataTables/extensions/Buttons/js/buttons.print.min.js"></script>
  <script src="../../assets/plugins/DataTables/extensions/Responsive/js/dataTables.responsive.min.js"></script>
  <script src="../../assets/plugins/DataTables/extensions/AutoFill/js/dataTables.autoFill.min.js"></script>
  <script src="../../assets/plugins/DataTables/extensions/ColReorder/js/dataTables.colReorder.min.js"></script>
  <script src="../../assets/plugins/DataTables/extensions/KeyTable/js/dataTables.keyTable.min.js"></script>
  <script src="../../assets/plugins/DataTables/extensions/RowReorder/js/dataTables.rowReorder.min.js"></script>
  <script src="../../assets/plugins/DataTables/extensions/Select/js/dataTables.select.min.js"></script>
  <script src="../../assets/js/demo/table-manage-combine.demo.min.js"></script>
  <!-- ================== END PAGE LEVEL JS ================== -->
   
  <script>
    $(document).ready(function() {
      App.init();
      TableManageCombine.init();

    }); 
  </script>
  <script type="text/javascript">
      $(document).ready(function() {
        $(document).on('click','#adjbtn', function(e){
          e.preventDefault();
          var uid = $(this).data('id'); // get id of clicked row 
          $.ajax({
            url: 'qty_adj.php',
            typr: 'POST',
            data: 'id='+uid,
            dataType: 'html'
          })
          .done(function(data){ 
            $('#rec-load').html(''); // blank before load.
            $('#rec-load').html(data);
            
          })
          .fail(function(){
            $('#rec-load').html('<i class="fa  fa-info-sign"></i> Something went wrong, Please try again...'); 
          });
        });
        $(document).on('click','#receive', function(e){
          e.preventDefault();
          var uid = $(this).data('id'); // get id of clicked row
          var item = $(this).data('id2'); // get id of clicked row  
          $.ajax({
            url: 'recitem.php',
            typr: 'POST',
            data: 'id='+uid+'&item='+item,
            dataType: 'html'
          })
          .done(function(data){ 
            $('#rec-load').html(''); // blank before load.
            $('#rec-load').html(data);
            
          })
          .fail(function(){
            $('#rec-load').html('<i class="fa  fa-info-sign"></i> Something went wrong, Please try again...'); 
          });
        });
        $(document).on('click','#issueall', function(e){
          e.preventDefault();
          var uid = $(this).data('id');   
          $.ajax({
            url: 'issueall.php',
            typr: 'POST',
            data: 'id='+uid,
            dataType: 'html'
        })
          .done(function(data){ 
          $('#rec-load').html(''); // blank before load.
          $('#rec-load').html(data);
          
        })
          .fail(function(){
            $('#rec-load').html('<i class="fa  fa-info-sign"></i> Something went wrong, Please try again...'); 
          });
        });
        $(document).on('click','#rem', function(e){
          e.preventDefault();
          var uid = $(this).data('id'); // get id of clicked row
          var item = $(this).data('id2'); // get id of clicked row   
          $.ajax({
            url: 'rem.php',
            typr: 'POST',
            data: 'id='+uid+'&item='+item,
            dataType: 'html'
        })
          .done(function(data){  
          $('#rec-load').html(''); // blank before load.
          $('#rec-load').html(data);
          
        })
          .fail(function(){
            $('#rec-load').html('<i class="fa  fa-info-sign"></i> Something went wrong, Please try again...'); 
          });
        });
        $(document).on('click','#cancel', function(e){
          e.preventDefault();
          var uid = $(this).data('id'); // get id of clicked row  
          $.ajax({
            url: 'cancelreq.php',
            typr: 'POST',
            data: 'id='+uid,
            dataType: 'html'
        })
          .done(function(data){  
          $('#rec-load').html(''); // blank before load.
          $('#rec-load').html(data);
          
        })
          .fail(function(){
            $('#rec-load').html('<i class="fa  fa-info-sign"></i> Something went wrong, Please try again...'); 
          });
        });
      });
    </script> 
</body>
</html>
    

