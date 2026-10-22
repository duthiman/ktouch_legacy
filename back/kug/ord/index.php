<?php 
include('funcs.php');
$user_id =  $_SESSION['mwene'];  
?><!--  class="dark-mode"-->
<!DOCTYPE html>
<html lang="en" >
<head>
	<meta charset="utf-8" />
	<title>K-SOFT | ERP</title>
	<meta content="width=device-width, initial-scale=1.0, maximum-scale=1.0, user-scalable=yes" name="viewport" />
	<meta content="" name="descriptison" />
	<meta content="" name="author" />

	<link href="../../../assets/css/vendor.min.css" rel="stylesheet" />
	<link href="../../../assets/css/default/app.css" rel="stylesheet" />
	<!-- ================== BEGIN DATATABLE================== --> 
	<link href="../../../assets/plugins/select2/dist/css/select2.min.css" rel="stylesheet" />
	<!-- ================== END DATATABLE ================== --> 

</head> 
<body>
	<div id="app" class="app app-header-fixed app-without-sidebar">
		<div id="header" class="app-header">
			<div class="navbar-header">
				<a class="navbar-brand"><img src="../../../assets/img/logo/ksoft.png" style="width: 120px; height: auto;"> </a>
				<button type="button" class="d-none navbar-mobile-toggler" data-toggle="app-top-menu-mobile">
					<span class="icon-bar"></span>
					<span class="icon-bar"></span>
					<span class="icon-bar"></span>
				</button>
			</div>
			<div class="navbar-nav"> 
				<img src="../../../assets/img/logo/ksoft.png" style="width: 120px; height: auto; margin-right: 50%;" class="hlogo">
				<div class="navbar-item navbar-user dropdown"> 
						<img src="../../../assets/img/user/user-12.jpg" alt=""/> 
							<span class=" d-md-inline" style="margin-right: 10px;"><?php  echo get_column("kp_users","username","user_id='$user_id'"); 

								?>
							</span>  

				</div>

			</div>

		</div>
		<div id="content" class="app-content"></div>
		<div  id="modal" class="modal fade bs-example-modal-lg" tabindex="-1" role="dialog" aria-labelledby="myModalLabel" style="display: none;">
			<div class="modal-dialog"  id="load"></div>
		</div>
		<div  id="modal2" class="modal fade bs-example-modal-lg" tabindex="1" role="dialog" aria-labelledby="myModalLabel" style="display: none;">
			<div class="modal-dialog"  id="load2"></div>
		</div>
		<a href="javascript:;" class="btn btn-icon btn-circle btn-success btn-scroll-to-top" data-toggle="scroll-to-top"><i class="fa fa-angle-up"></i></a>
		<script src="../../../assets/js/vendor.min.js" type="text/javascript"></script>
		<script src="../../../assets/js/app.min.js" type="text/javascript"></script> 
		<script type="text/javascript">
			App.settings({
				ajaxMode: true,
				ajaxDefaultUrl: '#ordPage.php',
				ajaxType: 'GET',
				ajaxDataType: 'html'
			}); 
		</script>
	</body>
</html>