<?php
session_start();
include('../../inc/connection.php');
include('../gwitia/funcs.php'); 
$req_id = $_SESSION['req'];
$user_id = $_SESSION['user_id']; 
$counter=user_counter($user_id);  

$get_gen_details = mysqli_query($connection,"SELECT * FROM kp_settings_gen") or die("Could not get the gen details");
$gen = mysqli_fetch_assoc($get_gen_details);
$shift = get_column('kp_shift','shift',"1");
?>
<!DOCTYPE html>
<!--[if IE 8]> <html lang="en" class="ie8"> <![endif]-->
<!--[if !IE]><!-->
<html lang="en">
  <head>
    <meta http-equiv="content-type" content="text/html; charset=<charset>" />
    <title>Receipt</title>
    <link href="../assets/css/thermal_print.css" rel="stylesheet" />
    <script type="text/javascript">
      (function () {
        var reqCompleted = false;
        var printStarted = false;

        function completeRequest() {
          if (reqCompleted) {
            return;
          }
          reqCompleted = true;
window.location.replace('../gwitia/req_place.php');
        }

        function startPrint() {
          if (printStarted) {
            return;
          }
          printStarted = true;
          window.print();
        }

        window.addEventListener('load', function () {
          setTimeout(startPrint, 250);
        });

        window.addEventListener('afterprint', function () {
          setTimeout(completeRequest, 150);
        });

        document.addEventListener('visibilitychange', function () {
          if (!document.hidden && printStarted) {
            setTimeout(completeRequest, 300);
          }
        });
      })();
    </script>
  </head> 
<!-- -->
 <body>
<!-- START RECEIPT -->
   <div id="receipt">

    <div id="bord">
    <div id="receipt-header" class="text-center">
      <h1 class="title"><?php echo get_counter($counter); ?><br>REQUISITIONED ITEMS</h1> 
       <p style="margin: 0px 0px 0px 0px;">**********************************************************</p> 
       <span><b>Shift: <?php echo $shift; ?></b></span> <BR>
       <span><b>Print Time: <?php echo today(); ?></b></span> <BR>
      <span class="text-center">Req By:<?php echo get_user($user_id); ?></span> <br>
      <span class="text-center"><b>Req No: <?php echo $req_id; ?></b></span> 
      <BR>
      <p style="margin: 0px 0px 0px 0px;">**********************************************************</p>
   </div>
   <div id="receipt-body">
    <table>
      <tbody>
        <?php
        $get_items = mysqli_query($connection,"SELECT * FROM kp_counter_req_items WHERE req_id='$req_id'");
        while ($s = mysqli_fetch_assoc($get_items)){
          $item_id = $s['item_id'];
          $qty=$s['req_qty']; 
          ?>
        <tr>
         <td><?php echo get_item($item_id); ?></td>
         <td><?php echo $qty; ?></td> 
        </tr>
        <?php
          }
        ?>
      </tbody> 
    </table>
    <BR>
    <p style="margin: 0px 0px 0px 0px;">**********************************************************</p> 
    <p style="margin: 0px 0px 0px 0px;">**********************************************************</p>
  </div>
</div>
</div>

</body>
</html> 
