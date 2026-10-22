 <?php
session_start(); 
include("../../inc/connection.php");
include("funcs.php");
$user_id = $_SESSION['user_id'];
$id=$_REQUEST['id']; 
$sql="SELECT message FROM mpesa_message WHERE id='$id'"; 
$result = mysqli_query($con,$sql);
    $rows = mysqli_fetch_assoc($result);
$message=$rows['message'];//get_column('mpesa_message','message',"id='$id'");
$new_str =  str_replace(' ', '', $message) ; 

function get_between_data($string, $start, $end){
  $pos_string = stripos($string, $start);
  $substr_data = substr($string, $pos_string);
  $string_two = substr($substr_data, strlen($start));
  $second_pos = stripos($string_two, $end);
  $string_three = substr($string_two, 0, $second_pos);

  $result_unit = trim($string_three);

  return $result_unit;
}
$searchdate = get_between_data($new_str, 'confirmed.on', 'Ksh');
$date= strtok($searchdate, "at "); 
$time=substr($searchdate, strrpos($searchdate, 'at')+2);

$date = DateTime::createFromFormat('d/m/Y', $date); 
//$date= DateTime::createFromFormat('d/m/y', $date)->format('Y-m-d');;
$searchname = get_between_data($new_str, 'receivedfrom', '.NewAccount');

$phone=substr($searchname, -12);

$name = get_between_data($message, 'received from', $phone);
$amount = get_between_data($new_str, $time.'Ksh', 'receivedfrom');
$sent=strpos(strtolower($new_str), "sent");
if (strpos(strtolower($new_str), "sent") || strpos(strtolower($new_str), 'reversal')) {
  // code...
}else{
  ?>
  <div class="modal-content" >
    <div class="modal-header">
      <h4 class="modal-title">SPLIT THIS MPESAS </h4> 
    </div>
    <div class="modal-body">
      <p id="split_res"></p>
      <table class="table table-bordered" style="padding: 2px;">
        <thead>
          <tr> 
            <th colspan="2" style="font-size: 16px;">
              <?php echo $message; ?>
            </th>

          </tr>
          <tr>
            <th colspan="2" style="font-size: 16px; white-space: wrap;">
              <?php 
              //echo $date;

              
              ?>
            </th>
          </tr>
           <tr>
            <th>CODE</th>
            <th><input type="text" class="form-control" name="code" id="code" style="text-transform: uppercase" value="<?php $code = substr($new_str, 0, 10); echo $code ?>"></th>
          </tr>
          <tr>
            <th>NAME</th>
            <th><input type="text" class="form-control" name="name" id="name" value="<?php echo $name; ?>" style="text-transform: uppercase"></th>
          </tr>
          <tr>
            <th>PHONE</th>
            <th><input type="text" class="form-control" name="phone" id="phone" value="<?php echo $phone; ?>" style="text-transform: uppercase"></th>
          </tr>
          <tr>
            <th>AMOUNT</th>
            <th><input type="text" class="form-control" name="amount" id="amount" value="<?php echo $amount; ?>" style="text-transform: uppercase"></th>
          </tr>
          <tr>
            <th>DATE</th>
            <th><input type="date" class="form-control" name="date" id="date"   style="text-transform: uppercase"></th>
          </tr>
          <tr>
            <th>TIME</th>
            <th><input type="text" class="form-control" name="time" id="time" value="<?php echo date('h:ia', strtotime($time)); ?>" style="text-transform: uppercase"> </th>
          </tr> 
        </thead>
      </table> 
    </div> 
    <div class="modal-footer">
      <button type="button" class="btn btn-outline-danger btn-md" data-dismiss="modal">CLOSE MODAL</button>
      <button class="btn btn-outline-lime btn-md" id="splitact" data-id1="<?php echo $id; ?>">SPLIT MESSAGE</button>
    </div>
  </div>
  <script type="text/javascript">
    // Add your custom JS code here
    $( document ).ready(function() {  
      $(document).on('click','#splitact', function(e){
        e.preventDefault(); 
        var id = $(this).data('id1'); 
        var name = $('#name').val(); 
        var code = $('#code').val(); 
        var date = $('#date').val(); 
        var time = $('#time').val(); 
        var phone = $('#phone').val();  
        var amount = $('#amount').val(); 
        if (id==0) {
          var hulla = new hullabaloo();
          hulla.send("You have to select message!!!!", "warning");
        }else{
          $.ajax({
            url: '../split/split_act.php',
            typr: 'POST',
            data: {id:id,name:name,code:code,date:date,time:time,phone:phone,amount:amount},
            dataType: 'html'
          })
          .done(function(data){
            $('#split_res').html(''); // blank before load.
            $('#split_res').html(data);
          })
          .fail(function(){
            $('#split_res').html('<i class="fa  fa-info-sign"></i> Something went wrong, Please try again...'); 
          });
        }
      });
    });
  </script>
  <?php  
}
?>