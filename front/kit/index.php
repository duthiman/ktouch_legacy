<?php 
include("../clude/head.php");  
  $_SESSION['user_id'];
 if (!isset($_SESSION['user_id'])) {
  header("Location:../log_out.php");
}
?>
<style type="text/css">
body {
  background: url("../assets/img/bg/k3.jpg")no-repeat center center fixed;
  -webkit-background-size: cover;
  -moz-background-size: cover;
  -o-background-size: cover;
  background-size: cover;
}
@media (max-width: 991px) {
  body {
    background: radial-gradient(circle at top, rgba(246,208,137,0.22), transparent 32%), linear-gradient(180deg, #f6f0d7 0%, #efe3bf 36%, #d3b67d 100%);
  }
  #page-container.page-header-fixed {
    padding-top: 0 !important;
  }
  #header {
    position: sticky !important;
    top: 0;
    z-index: 20;
  }
  #content.content {
    padding: 5px !important;
    margin-top: 0 !important;
  }
  .vertical-box {
    display: block !important;
  }
  .vertical-box-column {
    display: block !important;
    width: 100% !important;
    position: static !important;
  }
  .vertical-box-column .btn_nav {
    min-width: 88px;
  }
  #menu {
    margin-left: 0 !important;
    min-height: 42vh;
  }
  #screen {
    height: 34vh !important;
  }
}
</style>
<body  id="whole"> 
  <!-- begin #page-container -->
  <div id="page-container" class="page-without-sidebar page-header-fixed">
    <!-- begin #header -->
    <div id="header" class="header navbar-default" style="background-color: #6F9F0766 !important; ">
      <div class="navbar_header" id="sub_cat" ></div>
    </div>
    <!-- END HEADER-->
    <!-- begin #content -->
    <div id="content" class="content">
      <div class="vertical-box kit-shell">
        <div class="vertical-box-column" style="width: 7%; background-color: transparent;">
          <?php
          $get_category = "SELECT * FROM kp_cat "; 
          $get_category_r = mysqli_query($connection,$get_category) or die("Could not fetch the categories");
          if (mysqli_num_rows($get_category_r) < 1) {
            echo "There is no registsred category at the moment";
          }else{
            while ($r = mysqli_fetch_assoc($get_category_r)) {
            $category = $r['name'];
            $cat_id = $r['cat_id'];
            ?>
            <button onclick="req_navbar('<?php echo $cat_id; ?>');load_cat_food('<?php echo $cat_id; ?>')" class="btn_nav"><?php echo $category; ?></button>
            <?php
              }
            }
            ?> 
          </div>

          <div class="vertical-box-column" style="width: 65%;" >
            <div id="menu" style="margin-left: 10px;"> </div>
          </div>
          <div class="vertical-box-column "  style=" width: 28% !important; height: 90%; position: absolute; padding-right: 2px;">
            <div class="wrapper" style="background-color: #B10421; text-align: center;">
              <b style="color: white; font-size: 20px;"> KITCHEN RECEIED ITEMS</b>  
            </div>
            <div id="screen" style="overflow-y:scroll; height: 80%; width: auto; background-color: #fff;"></div>
              <div class="wrapper">
                <a href="../print/print_kit.php" class="btn btn_ord" style="padding-top: 10px;">SUBMIT REQUISTION</a> 
                <button  data-toggle="modal" data-target="#modal" type="button" class="btn_ord" id="cancel">CANCEL</button> <br>
              </div>
            </div>
          </div>
        </div>
    <!-- end #content --> 
        <div id="modal" class="modal" tabindex="-1">
        <div class="modal-dialog" id="load"> </div>
      </div>  
    </div>
 
<!-- ================== BEGIN BASE JS ================== -->
   <script src="../../assets/plugins/jquery/jquery-3.2.1.min.js"></script> 
  <!--<script src="../../assets/plugins/jquery-ui/jquery-ui.min.js"></script>-->
  <script src="../../assets/plugins/bootstrap/4.0.0/js/bootstrap.bundle.min.js"></script> 
  <script src="req_scripts.js"></script>
  <!-- ================== END BASE JS ================== --> 
</body>
</html>
 
