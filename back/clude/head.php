<?php
if (session_status() === PHP_SESSION_NONE) {
	session_start();
}

// Pages under back/thit/take are one directory deeper than the legacy back/* pages.
// Keep asset URLs relative to the current page so shared includes work at both depths.
$asset_prefix = '../../';
$script_path = str_replace('\\', '/', (string)($_SERVER['SCRIPT_NAME'] ?? ''));
$back_position = strpos($script_path, '/back/');
if ($back_position !== false) {
	$relative_back_path = trim(substr($script_path, $back_position + 6), '/');
	$relative_directory = trim(dirname($relative_back_path), './');
	$directory_depth = $relative_directory === '' ? 0 : count(array_filter(explode('/', $relative_directory)));
	$asset_prefix = str_repeat('../', $directory_depth + 1);
}
	if (file_exists("../inc/connection.php")){
	    include('../inc/connection.php');
	}elseif (file_exists("../../inc/connection.php")){
	    include('../../inc/connection.php');
	}elseif (file_exists("../../../inc/connection.php")){
	    include('../../../inc/connection.php');
	}else{}
	
include('funcs.php');
?>
<!DOCTYPE html>
<!--[if IE 8]> <html lang="en" class="ie8"> <![endif]-->
<!--[if !IE]><!-->
<html lang="en">
<!--<![endif]-->
<head>
	<meta charset="utf-8" />
	<title>K-TOUCH</title>
	<meta content="width=device-width, initial-scale=1.0, maximum-scale=1.0, user-scalable=no" name="viewport" />
	<meta http-equiv="cache-control" content="no-cache" />
<meta http-equiv="Pragma" content="no-cache" />
	<meta content="" name="description" />
	<meta content="" name="author" />
	<!-- ================== BEGIN BASE CSS STYLE ================== --> 
	<link href="<?php echo htmlspecialchars($asset_prefix, ENT_QUOTES, 'UTF-8'); ?>assets/plugins/jquery-ui/jquery-ui.css" rel="stylesheet" />
	<link href="<?php echo htmlspecialchars($asset_prefix, ENT_QUOTES, 'UTF-8'); ?>assets/plugins/bootstrap/4.0.0/css/bootstrap.css" rel="stylesheet" />
	<link href="<?php echo htmlspecialchars($asset_prefix, ENT_QUOTES, 'UTF-8'); ?>assets/plugins/font-awesome/5.1/css/all.css" rel="stylesheet" />
	<link href="<?php echo htmlspecialchars($asset_prefix, ENT_QUOTES, 'UTF-8'); ?>assets/plugins/animate/animate.min.css" rel="stylesheet" />
	<link href="<?php echo htmlspecialchars($asset_prefix, ENT_QUOTES, 'UTF-8'); ?>assets/css/default/style.min.css" rel="stylesheet" />
	<link href="<?php echo htmlspecialchars($asset_prefix, ENT_QUOTES, 'UTF-8'); ?>assets/css/default/style-responsive.min.css" rel="stylesheet" />
	<link href="<?php echo htmlspecialchars($asset_prefix, ENT_QUOTES, 'UTF-8'); ?>assets/css/default/theme/default.css" rel="stylesheet" id="theme" />
	<!-- ================== END BASE CSS STYLE ================== -->
	<style type="text/css">
		.content {
			padding: 10px !important;
		}
		 
		#sidebar.sidebar,
		.sidebar-bg,
		#top-menu.top-menu {
			background: #753f91 !important;
		}

		#sidebar .nav > li > a,
		#sidebar .nav > li > a .caret,
		#sidebar .sub-menu > li > a,
		#top-menu .nav > li > a,
		#top-menu .nav > li > a .caret,
		#top-menu .sub-menu > li > a,
		#header .navbar-brand,
		#header .navbar-toggle .icon-bar {
			color: #ffffff !important;
		}

		#header .navbar-toggle .icon-bar {
			background: #ffffff !important;
		}

		#header .navbar-nav.navbar-right > li > a,
		#header .navbar-nav.navbar-right > li > a i,
		#header .navbar-nav.navbar-right > li > a .caret,
		#header .navbar-nav.navbar-right .navbar-user > a,
		#header .navbar-nav.navbar-right .navbar-user > a .caret,
		#header .navbar-nav.navbar-right .navbar-user > a span {
			color: #1f1f1f !important;
		}

		#sidebar .nav > li > a:hover,
		#sidebar .nav > li > a:focus,
		#sidebar .nav > li.active > a,
		#sidebar .nav > li.expand > a,
		#sidebar .sub-menu > li > a:hover,
		#sidebar .sub-menu > li > a:focus,
		#top-menu .nav > li > a:hover,
		#top-menu .nav > li > a:focus,
		#top-menu .nav > li.active > a,
		#top-menu .nav > li.expand > a,
		#top-menu .sub-menu > li > a:hover,
		#top-menu .sub-menu > li > a:focus {
			background: rgba(255, 255, 255, 0.14) !important;
			color: #ffffff !important;
		}

		#sidebar .sub-menu,
		#top-menu .sub-menu,
		#header .dropdown-menu {
			background: #68377f !important;
		}

		#header .dropdown-menu > li > a,
		#header .dropdown-menu .dropdown-item,
		#header .dropdown-menu .media-heading,
		#header .dropdown-menu p {
			color: #1f1f1f !important;
		}

		#header .dropdown-menu > li > a:hover,
		#header .dropdown-menu .dropdown-item:hover,
		#header .dropdown-menu .dropdown-item:focus {
			background: rgba(255, 255, 255, 0.14) !important;
			color: #ffffff !important;
		}

		@media (max-width: 767px) {
			#header.header.navbar-default {
				display: flex;
				align-items: center;
				justify-content: space-between;
				padding: 0 10px;
				min-height: 60px;
			}

			#header .navbar-header {
				display: flex;
				align-items: center;
				flex: 1 1 auto;
				border-bottom: none !important;
				min-width: 0;
			}

			#header .navbar-brand {
				display: flex;
				align-items: center;
				padding: 8px 0;
				margin-right: 10px;
				float: none;
			}

			#header .navbar-brand img {
				width: 96px !important;
				max-width: 100%;
			}

			#header .navbar-nav.navbar-right {
				display: flex;
				align-items: center;
				float: none !important;
				margin: 0;
				padding: 0;
				gap: 8px;
			}

			#header .navbar-nav.navbar-right > li {
				float: none;
			}

			#header .navbar-nav.navbar-right > .nav-item,
			#header .navbar-nav.navbar-right > li.dropdown:not(.navbar-user) {
				display: none !important;
			}

			#header .navbar-user {
				display: flex;
				align-items: center;
			}

			#header .navbar-user > a {
				display: flex;
				align-items: center;
				gap: 6px;
				padding: 10px 0;
			}

			#header .navbar-user img {
				width: 30px;
				height: 30px;
				margin-right: 0 !important;
			}

			#header .navbar-user .d-none.d-md-inline {
				display: inline !important;
				max-width: 92px;
				overflow: hidden;
				text-overflow: ellipsis;
				white-space: nowrap;
				font-size: 12px;
				font-weight: 700;
			}

			#header .navbar-toggle {
				display: block;
				float: none;
				margin: 0 0 0 8px;
				padding: 9px 10px;
				order: 3;
			}

			#page-container.page-with-top-menu #top-menu {
				display: none;
				position: fixed;
				top: 64px;
				left: 10px;
				right: 10px;
				z-index: 1060;
				border-radius: 14px;
				box-shadow: 0 18px 40px rgba(0, 0, 0, 0.28);
				overflow: hidden;
			}

			#page-container.page-with-top-menu #top-menu .nav {
				display: block;
				margin: 0 !important;
				padding: 6px 0;
			}

			#page-container.page-with-top-menu #top-menu .nav > li {
				display: block;
				float: none;
			}

			#page-container.page-with-top-menu #top-menu .nav > li > a {
				padding: 12px 16px;
			}

			#page-container.page-with-top-menu #top-menu .sub-menu > li > a {
				padding-left: 28px;
			}

			#page-container.page-with-top-menu #top-menu .menu-control {
				display: none !important;
			}
		}
	</style>
 
  <link href="<?php echo htmlspecialchars($asset_prefix, ENT_QUOTES, 'UTF-8'); ?>assets/css/default/invoice-print.css" rel="stylesheet" /> 
 
</head>
<body> 
