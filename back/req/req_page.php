<?php 
require("../clude/head.php");
$user_id = $_SESSION['user_id'];
$get_details = mysqli_query($connection,"SELECT * FROM kp_thesion WHERE user_id='$user_id'") or die("Could not get the user permissions");
$row=mysqli_fetch_assoc($get_details); 
$req_id = $row['req_id']; 
$no=0;
?>

<script type="text/javascript">

</script>
<!-- begin #page-container -->
<div id="page-container" class="fade page-without-sidebar page-header-fixed">
    <!-- begin #header -->  
  <?php 
    require("../clude/header.php");
  ?>
  <!-- end #header -->   
  <!-- begin #sidebar -->
  <?php 
    //require("../clude/menu.php");
  ?>
  <!-- end #sidebar -->
 
<div id="content" class="content">
  <div class="row">
    <!-- START COL-MD-8 -->
    <div class="col-md-8">
      <div class="panel panel-inverse" data-sortable-id="index-1"> 
        <div id="results"></div>
        <div class="panel-body">
          <form role="form" id="req_form" >
            <div class="form-group row">
              <div class="col-md-4"> 
                <div class="input-group mb-3">
                  <div class="input-group-prepend">
                    <a class="btn btn-warning" data-toggle="modal" data-target="#quick-modal" id="quick-item"><i class='fa fa-lg fa-plus' ></i></a>   
                  </div> 
                  <select type="text" class="picker form-control col-lg-12"  id="item_id" name="items" style="width: 80%;"> 
                    <?php 
                    
                      $items = mysqli_query($connection,"SELECT * FROM kp_items ");
                      while ($row = mysqli_fetch_assoc($items)){
                        ?>
                        <option value="<?php echo $row['item_id']; ?>"><?php echo strtoupper($row['name']); ?></option>
                        <?php
                      }
                      
                    ?>
                  </select>
                </div>
              </div> 
              <div class="col-md-2 col-lg-3"> 
                <input id="qty" type="text" placeholder="Quantity"  class="form-control">
              </div> 
              <div  class="col-md-2 col-lg-2">
                <button   onclick="btnSubmitForm();"   type="button" id="btnSubmit" value="Add Item" class="btn btn-warning" >ADD</button>
              </div>
            </div> 
        </form>
        <div id="panelChart3" class="panel" style="margin-top: 10px;">
          <div id="d1"> </div> 
        </div> 
      </div> 
    </div>
  </div>

    <!-- end col-md-8-->

  <div class="col-lg-4" >
    <!-- begin panel -->
    <div class="panel paper-cut  col-md-12">
      <div class="panel-heading row"> 
        <div class="col-md-3 text-center" >
          <button type="button"  data-toggle="modal"data-target="#modal" id="hold" class="btn btn-sm btn-warning">HOLD</button>
        </div>
        <div class="col-md-4 text-center" style="height: 40px; padding: 10px 15px; border-right: 1px solid #eee;">
          <h6>REQ NO :<br><b><?php echo $req_id; ?></b></h6> 
        </div>   
      </div> 
      <div class="panel-footer"  style="height: 50px;">
        <a  data-toggle="modal" data-target="#modal"  id="raise-qt" class="btn btn-purple btn-xs pull-right" onclick="raise_quote();">Raise Request</a> 
        <a data-toggle="modal" data-target="#modal" id="cancel_btn" class="btn btn-danger btn-xs pull-left"> CANCEL</a> 
      </div>
    </div>  
  </div> 
</div>
</div>
<!-- end content -->
<div  id="modal" class="modal fade bs-example-modal-lg" tabindex="-1" role="dialog" aria-labelledby="myModalLabel"  style="display: none; width: auto;">
  <div class="modal-dialog" id="load" style="padding: 10px;"> </div>
</div>
<div  id="modal2" class="modal fade bs-example-modal-lg" tabindex="-1" role="dialog" aria-labelledby="myModalLabel"  style="display: none; width: auto;">
  <div class="modal-dialog" id="load2" style="padding: 10px;"> </div>
</div>

<div  id="qt-modal" class="modal fade bs-example-modal-lg" tabindex="-1" role="dialog" aria-labelledby="myModalLabel" style="display: none;">
  <div class="modal-dialog">
    <div class="modal-content">
      <div class="modal-header">
        <button type="button" class="close" data-dismiss="modal" aria-hidden="true">×</button>
        <h4 class="modal-title"><i class="fa fa-stats"></i> &nbsp;&nbsp;Confirm Quotaton</h4>
      </div> 
     
      <div class="modal-body">
        <div class="row">
          <div class="alert alert-danger">
            <p>WARNING!! This action is irreversible and will delete currently placed orders.</p>
          </div>
        </div>
      </div>
      <div class="modal-footer">
        <button type="button" class="btn btn-default waves-effect" data-dismiss="modal">No</button> 
        <a href="#" class="btn btn-lime pull-right" onclick="print_quote();">Yes</a> 
      </div>
    </div>
  </div>
</div> 
<!-- ================== BEGIN BASE JS ================== -->
  <script src="../../assets/plugins/jquery/jquery-3.2.1.min.js"></script>
  <script src="../../assets/plugins/jquery-ui/jquery-ui.min.js"></script>
  <script src="../../assets/plugins/bootstrap/4.0.0/js/bootstrap.bundle.min.js"></script> 
  <script src="../../assets/plugins/slimscroll/jquery.slimscroll.min.js"></script>
  <script src="../../assets/plugins/js-cookie/js.cookie.js"></script>
  <script src="../../assets/plugins/gritter/js/jquery.gritter.js"></script>
  <script src="../../assets/js/apps.min.js"></script>
  <!-- <script src="../../assets/js/theme/default.min.js"></script>
  ================== END BASE JS ================== --> 

 
<!-- ================== BEGIN PAGE LEVEL JS ================== -->
    <script src="../../assets/plugins/Hullabaloo/js/hullabaloo.js"></script> 
    <!-- ================== END PAGE LEVEL JS ================== -->
  <script src="../../assets/plugins/select2/dist/js/select2.min.js"></script> 
 
 <script>
  function load_items(){
    xmlhttp = new XMLHttpRequest();
    xmlhttp.open("GET","../req/req_items.php",false);
    xmlhttp.send(null);
    document.getElementById("d1").innerHTML = xmlhttp.responseText;
    document.getElementById("d1").style.visibility = "visible"; 
  }  
  
  function btnSubmitForm() {
    var item_id = $("#item_id").val();
    var quantity = $("#qty").val(); 
    $.post("../req/req_action.php", { item_id: item_id, quantity: quantity },
    function(data) {
      $('#results').html(data);
      $('#req_form')[0].reset();
      load_items(); 
    });
  }
  function raise_quote(){
      $.ajax({
        url: 'confirm_modal.php',
        type: 'POST',
        data: {},
        dataType: 'html'
      })
      .done(function(data){ 
        $('#load').html(data); // blank before load.
      })
      .fail(function(){ 
        $('#load').html('<i class="fa  fa-info-sign"></i> Something went wrong, Please try again...');
      });
  }  
function del_this(item_id){
  $.ajax({
    url: 'del_item.php',
    type: 'POST',
    data: 'item_id='+item_id,
    dataType: 'html'
  })
  .done(function(data){
    load_items();
  })
  .fail(function(){
    //console.log("Fail To Delete");
    $('#d1').html(''); // blank before load.
    $('#d1').html('<i class="fa  fa-info-sign"></i> Something went wrong, Please try again...');
  });
} 

  $(".selectpicker").select2();
 
  $(document).ready(function() {
    App.init();
    load_items(); 
       
    $(document).on('click','#hold', function(e){  
      $('#gen-load').html(''); 
      $.ajax({
        url: 'hold_modal.php',
        typr: 'POST',
        data: '',
        dataType: 'html'
      })
      .done(function(data){ 
        $('#load').html(''); // blank before load.
        $('#load').html(data);
      })
      .fail(function()
      {
        $('#load').html('<i class="fa  fa-info-sign"></i> Something went wrong, Please try again...');
        $('#modal-loader').hide();
      });
    });

    $(document).on('click','#cancel_btn', function(e){  
      $('#gen-load').html(''); 
      $.ajax({
        url: 'cancel_modal.php',
        typr: 'POST',
        data: '',
        dataType: 'html'
      })
      .done(function(data){ 
        $('#load').html(''); // blank before load.
        $('#load').html(data);
      })
      .fail(function()
      {
        $('#load').html('<i class="fa  fa-info-sign"></i> Something went wrong, Please try again...');
        $('#modal-loader').hide();
      });
    }); 

    $.fn.inputFilter = function(inputFilter) { 
      return this.on("input keydown keyup mousedown mouseup select contextmenu drop", function() {
        if (inputFilter(this.value)) {
          this.oldValue = this.value;
          this.oldSelectionStart = this.selectionStart;
          this.oldSelectionEnd = this.selectionEnd;
        } else if (this.hasOwnProperty("oldValue")) {
          this.value = this.oldValue;
          this.setSelectionRange(this.oldSelectionStart, this.oldSelectionEnd);
        }
      });
    };
    $("#qty").inputFilter(function(value) {
      return /^\d*$/.test(value); 
    });
    $("#price").inputFilter(function(value) {
      return /^\d*$/.test(value); 
    });
  }); 
  </script>
  
</body>
</html>

