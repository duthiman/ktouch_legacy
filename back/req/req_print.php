<?php
session_start(); 
include('../../inc/connection.php'); 
include('funcs.php');
$url = "../../assets";

if (!isset($_SESSION['user_id'])) {
   header("Location:../log_out.php");
}

$folder="front/req/"; 
thesion_page($folder);

$user_id = $_SESSION['user_id']; 
$get_details = mysqli_query($connection,"SELECT * FROM kp_thesion WHERE user_id='$user_id'") or die("Could not get the user permissions");
$row=mysqli_fetch_assoc($get_details);
if (isset($_GET['id'])) {
  $req_id = $_GET['id'];
}else{
  $req_id = $row['req_id'];
}

$dash = $row['dash']; 
$sales_1 = mysqli_query($connection,"SELECT * FROM kp_req WHERE req_id='$req_id'") or die(mysqli_error($connection));
$sales = mysqli_fetch_assoc($sales_1); 
$shop_id = $sales['shop_id'];  
$req_date = $sales['req_date'];  
$req_user = $sales['req_user'];
$get_gen_details = mysqli_query($connection,"SELECT * FROM kp_settings_gen") or die("Could not get the gen details");
$gen = mysqli_fetch_assoc($get_gen_details);

$action="PRINTED|-|-|-| REQUEST NO|".$req_id;
logs($action,"../../");

date_default_timezone_set("Africa/Nairobi");
$today=date("Y-m-d");
?>
<!DOCTYPE html> 
<html lang="en"> 
<head>
<meta charset="utf-8" />
<title>KPOS</title> 
<link href="<?php echo $url; ?>/plugins/font-awesome/5.1/css/all.min.css" rel="stylesheet" /> 
<link href="<?php echo $url; ?>/css/default/a4_print.css" rel="stylesheet" />
<?php
if (isset($_GET['id'])) {
  ?>
  <script type="text/javascript">
    function close(){
      window.location = '../req/init_page.php';
    } 
  </script>
  <?php
}else{
  ?>
  <script type="text/javascript">
    function close(){
      window.location = '../req/init_page.php';
    } 
  </script>
  <?php
}
?> 
</head> <!-- -->
<body  onload="setTimeout(function () { close(); }, 1000); window.print()">  
  <!-- begin #page-container -->
  <div id="page-container" class="fade page-sidebar-fixed page-header-fixed">
    <!-- begin #content -->
    <div id="contents" class="contents">
      <div class="p-order">
        <!-- begin invoice-header--> 
        <div class="inv-header" style="height:auto;">
          <div style=" border-right: green; width: 50%; float: left;">
            <a href="../dash/admin_dash.php" name="filter"><img src="<?php echo $gen['image_path']; ?>" alt="" style="width: 100px; margin-left: 10px; margin-top: 5px;" /> </a>
          </div>
          <div style=" border: green; width: 50%; float: right;">  
            <h1 style=" text-align: left; margin: 0px 0px 10px 0px;"><u>STOCK REQUISITION</u> </h1>
            <h3 style=" text-align: right; margin: 0px 0px 0px 0px;"> <?php echo $gen['company_name']; ?></h3>
            <address class="m-b-5" style="margin-top: 2px; text-align: right;">
              <?php echo $gen['address']."<br> <b>Mobile. </b>".$gen['phone1']; ?> 
              <?php echo "<b>Tel.</b> ".$gen['phone2']; ?><br/> 
              <?php echo "<b>Email.</b> ".$gen['email']; ?><br/>  
            </address>  
          </div> 
        </div>
        <div class="inv-contact"> 
          <div style=" width: 50%;padding: 5px; float: left; margin-bottom: 40px;"> 
            </div>
            <div class="col-md-4" style="background-color: #753f91;width: 30%; float: right;">
              <table class="table">
                <thead style="background-color:#753f91; ">
                  <tr>
                    <th>REQ No: </th>
                    <th><?php echo $req_id; ?> </th>
                  </tr>
                  <tr>
                    <th>REQ Date: </th>
                    <th><?php echo $req_date; ?> </th>
                  </tr> 
                </thead>
              </table>       
            </div> 
        </div>  
    <!-- end invoice-header -->
    <!-- begin invoice-Details--> 
        <div class="inv-content">
          <table class="table table-invoice table-bordered">
            <thead>
              <tr>
                <th>NO</th> 
                <th>ITEM</th>
                <th>QTY</th> 
              </tr>
            </thead>
            <tbody>
              <?php  
                $no=0;
                $pg=1; 
          //for ($i=0; $i < 60; $i++) {   
            $req_items = mysqli_query($connection ,"SELECT * FROM kp_req_items WHERE req_id= '$req_id'") or die(mysqli_error($connection));
              if (mysqli_num_rows($req_items) < 1) {
                echo "There are no items on this order";
              }else{     
                while ( $data = mysqli_fetch_assoc($req_items)) {
                  $item_id= $data['item_id']; 
                  $quantity = $data['req_qty']; 
                  $no=$no+1; 
              ?>
              <tr>
                <td><?php echo $no; ?></td> 
                <td><?php echo get_item($item_id); ?></td>
                <td><?php echo $quantity; ?></td>  
              </tr>
                  <?php 
                  if ($pg==1) {
                    $start=37;
                    $end=38;
                  }else{
                    $start=50;
                    $end=51;
                  }
                    if ($start <=  $no &&  $no <= $end) {
                      $pg=$pg+1;
                  ?>
                  <tr style="border-color: transparent;page-break-inside:avoid; page-break-after:always;"><td colspan="6" style="border-color: transparent;"></td></tr> 
                  <?php
                  $nos=0;
                } 
                }
              //}
           }
              ?>  
              <tr> 
                <th class="text-left" colspan="<?php echo 3; ?>"  style="vertical-align: bottom;">
                  <b>PREPARED BY: <u><?php echo get_user($req_user); ?></u></b><br><br> 
                  <b>SIGNATURE: ................................................................</b>
                </th> 
            </tbody>
          </table>
        </div> 
        <div id="inv-footer"> 
          <p class="text-center">
            THANK YOU FOR YOUR REQUEST<br/> 
          </p> 
        </div>
      </div> 
      <p class="break"></p>  
    </div>  
  </div> 
</body>
</html> 
 