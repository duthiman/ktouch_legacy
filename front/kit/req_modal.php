<?php 
  session_start(); 
  include("../../inc/connection.php");
  include("funcs.php");
  $staff_id = $_SESSION['staff_id'];
  $item_id = $_REQUEST['id'];
?>

<style type="text/css">
    .num{
      height: 70px; 
      width: 70px;
      border-radius: 10%;  
      font-size: 1rem; 
      color: $color-black;
      box-shadow: 0 0 6px 0 rgba(157, 96, 212, 0.5);
      border: solid 3px transparent;
      background-image: linear-gradient(rgba(255, 255, 255, 0), rgba(255, 255, 255, 0)), linear-gradient(101deg, #f36c26, #61b846);
      background-origin: border-box; 
      box-shadow: 2px 1000px 1px #fff inset;
      text-align: center;
    }
        .number{
            font-weight:bold; 
            margin-top: 25%;
        }
        .num-pad
{
    padding-left: 10px;
}
        .span4
{
    width: 80px;
    float: left;
    margin: 0 8px 10px 8px;
}
</style>
<div class="modal-content" style="width: 320px;">
 <div class="modal-header" style="padding: 10px; margin-bottom: 0px;"> 
  <h4 style="float:left;">ENTER QUANTITY REQUIRED</h4>
 </div>

<div class="modal-body"> 
      <div id="results"> </div>
     <div class="row" style="margin-top: -15px; margin-bottom: 20px;">
    <input type="hidden" class="form-control" id="item_id" value="<?php echo $item_id;?>" > 
    <input type="text" name="req_qty" id="req_qty" class="form-control" style="width:290px; margin:15px 15px 0px 18px;" autofocus/><br>
  </div>  
       <!-- strat the buttons -->
       <div id="numpad">
           <div class="span4">
                        <div class="num" >
                            <h2 class="number">7</h2>
                            <div class="txt" style="display:none">
                                7
                            </div>
                        </div>
                    </div>
                    <div class="span4">
                        <div class="num">
                            <h2 class="number">8</h2>
                            <div class="txt"  style="display:none">
                                8 
                            </div>
                        </div>
                    </div>
                    <div class="span4">
                        <div class="num"> 
              <h2 class="number">9</h2>
                            <div class="txt" style="display:none">
                                9 
                            </div>
              
                        </div>
                    </div>
                    <div class="span4">
                        <div class="num">
                            <h2 class="number">4</h2>
                            <div class="txt" style="display:none">
                                4 
                            </div>
                        </div>
                    </div>
                    <div class="span4">
                        <div class="num">
                            <h2 class="number">5</h2>
                            <div class="txt" style="display:none">
                                5
                            </div>
                        </div>
                    </div>
                    <div class="span4">
                        <div class="num">
                            <h2 class="number">6</h2>
                            <div class="txt" style="display:none">
                                6 
                            </div>
                        </div>
                    </div>
                     <div class="span4">
                        <div class="num">
              <h2 class="number">1</h2>
                             <div class="txt" style="display:none">
                                1
                            </div>
                        </div>
                    </div>  
                    <div class="span4">
                        <div class="num">
                            <h2 class="number">2</h2>
                            <div class="txt" style="display:none">
                                2 
                            </div>
                        </div>
                    </div>
                    <div class="span4">
                        <div class="num">
                            <h2 class="number">3</h2>
                            <div class="txt" style="display:none">3</div>
                        </div>
                    </div>
                    <div class="span4">
                        <button type="button" id="clear" name="count" class="btn btn- " style="background-color: #f06c26 !important;
                        padding: 0px;
                        font-weight: bold;
                        color:black;
                        height: 70px;
                        width:70px; 
                        border:2px solid #62b647; 
                        border-radius: 10px;">CLEAR </button>
                    
                    </div>
                    <div class="span4">
                        <div class="num">
                            <h2 class="number">0</h2>
                            <div class="txt" style="display:none">
                                0 
                            </div>
                        </div>
                    </div>
                    <div class="span4">
                        <div class="num">
                            <h2 class="number">.</h2>
                            <div class="txt" style="display:none">.</div>
                        </div>
                    </div>
                    
      </div>
      <!-- end the buttons --> 
</div>
<div class="modal-footer" style="padding: 10px;">
               <button type="button" data-dismiss="modal" class="btn btn-danger">CLOSE</button>
               <button type="submit" onclick="req_action()" name="place_order" id="pay" class="btn btn-warning">SUBMIT</button>
               
            </div>
          </div>
<script type="text/javascript">
  $(document).ready(function () {
   $('.num').click(function () {
   var num = $(this);
   var text = $.trim(num.find('.txt').clone().children().remove().end().text());
   var qty = $('#req_qty');
   $(qty).val(qty.val() + text);
 });

$('#clear').click(function () {
  var qty =$('#req_qty');
  $(qty).val('');
  });
});
 
    </script>