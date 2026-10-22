<?php
session_start(); 
include("../../inc/connection.php");
include("funcs.php");
$user_id = $_SESSION['user_id'];
?>
<!-- <script type="text/javascript" src="req_scripts.js"></script> -->
<style type="text/css">
  .rec-single-shell{
    width:100%;
    max-width:420px;
    border-radius:18px;
    overflow:hidden;
    border:1px solid rgba(110,75,24,.10);
    box-shadow:0 22px 44px rgba(77,55,21,.18);
  }
  .rec-single-head{
    background:linear-gradient(135deg,#7e2014 0%, #b94828 52%, #e7a33a 100%);
    color:#fff7ea;
    padding:14px 18px;
  }
  .rec-single-head h4{
    margin:0;
    font-size:16px;
    font-weight:900;
    letter-spacing:.03em;
    text-transform:uppercase;
  }
  .rec-single-body{
    padding:16px 16px 10px;
    background:#fffdf8;
  }
  .rec-single-body .form-control{
    height:48px;
    border-radius:12px;
    font-size:18px;
    font-weight:800;
    text-align:center;
    border:2px solid #f3d7a2;
    box-shadow:none;
  }
  .num{
    height:64px;
    width:64px;
    border-radius:14px;
    font-size:1rem;
    box-shadow:0 10px 20px rgba(77,55,21,.10);
    border:solid 2px transparent;
    background-image:linear-gradient(#fff,#fff), linear-gradient(101deg,#f36c26,#61b846);
    background-origin:border-box;
    background-clip:padding-box,border-box;
    text-align:center;
  }
  .number{
      font-weight:bold;
      margin-top:22%;
  }
  .num-pad{
    padding-left:4px;
  }
  .span4{
    width:72px;
    float:left;
    margin:0 6px 10px 6px;
  }
  .rec-single-foot{
    display:flex;
    gap:10px;
    padding:12px 16px 16px;
    background:#fffdf8;
    border-top:1px solid rgba(110,75,24,.08);
  }
  .rec-single-foot .btn{
    flex:1 1 auto;
    min-height:44px;
    border-radius:12px;
    font-weight:900;
    text-transform:uppercase;
    border:0;
  }
  .rec-single-foot .danger{
    background:#fee2e2;
    color:#b91c1c;
  }
  .rec-single-foot .success{
    background:linear-gradient(135deg,#2f5d17 0%, #58a03f 100%);
    color:#fff;
  }
</style> 
<?php 
//if(isset($_REQUEST['id'])){
    $id = $_REQUEST['id'];
    $req_id = $_REQUEST['req']; 

    $order_details = mysqli_query($connection,"SELECT * FROM kp_counter_req_items WHERE req_id='$req_id' AND item_id='$id'");
    $od = mysqli_fetch_assoc($order_details);
    ?>
  <div class="modal-content rec-single-shell">
  <div class="modal-header rec-single-head">
    <button type="button" class="close" data-dismiss="modal" aria-label="Close"><span aria-hidden="true">&times;</span></button>
    <h4 class="modal-title">RECEIVE <?php echo get_item($id); ?></h4>
  </div>
  <div id="results"></div>
  <div class="modal-body rec-single-body">
  <div class="row" style="margin-top: -15px;">
    <input type="hidden" name="item_id" id="item_id"  value="<?php echo $id; ?>"/>
    <input type="hidden" name="req_id" id="req_id"  value="<?php echo $od['req_id']; ?>"/>  
    <input type="text" name="qty" id="qty"  value="<?php echo $od['issue_qty']; ?>" class="form-control" style="width:100%; margin:15px 0 0;" autofocus/><br>
  </div> 
  <?php  include("../clude/numpad.php");?>
 </div>
 <div class="modal-footer rec-single-foot">
    <button type="button" class="btn danger btn-lg" data-dismiss="modal">Close</button>
     <button class="btn btn success button" onclick="recive_req()" >Receive</button>
  </div>
</div>
 <script type="text/javascript">
function recive_req(){
  var id = $("#item_id").val();
  var req_id = $("#req_id").val();
  var qty = $("#qty").val();
  console.log(id);
  console.log(req_id);
  console.log(qty); 
  $.post("rec.php", { id: id, req:req_id,qty:qty},
  function(data) { 
    console.log(data);

      window.location = "req_rec.php?req="+req_id;
    });
}
$(document).ready(function(){
  $('.num').click(function () {
    var num = $(this);
    var text = $.trim(num.find('.txt').clone().children().remove().end().text());
    var qty = $('#qty');
    $(qty).val(qty.val() + text);
  });
  $('#clear').click(function () {
    var qty =$('#qty');
    $(qty).val('');
  });
});
</script>
<?php
//}
?>
