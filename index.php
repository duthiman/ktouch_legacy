<!DOCTYPE html>
<!--[if IE 8]> <html lang="en" class="ie8"> <![endif]-->
<!--[if !IE]><!-->
<html lang="en">
<!--<![endif]-->
<head>
    <meta charset="utf-8" />
    <title>K-SOFT ERP</title>
    <meta content="width=device-width, initial-scale=1.0, maximum-scale=1.0, user-scalable=no" name="viewport" />
    <meta content="" name="description" />
    <meta content="" name="author" />
    
    <!-- ================== BEGIN BASE CSS STYLE ================== --> 
    <link href="assets/plugins/jquery-ui/jquery-ui.min.css" rel="stylesheet" />
    <link href="assets/plugins/bootstrap/4.0.0/css/bootstrap.min.css" rel="stylesheet" />
    <link href="assets/plugins/font-awesome/5.0/css/fontawesome-all.min.css" rel="stylesheet" />
    <link href="assets/plugins/animate/animate.min.css" rel="stylesheet" />
    <link href="assets/css/default/style.min.css" rel="stylesheet" />
    <link href="assets/css/default/style-responsive.min.css" rel="stylesheet" />
    <link href="assets/css/default/theme/default.css" rel="stylesheet" id="theme" />
    <!-- ================== END BASE CSS STYLE ================== -->
    <style>
        .login-mobile-brand {
            display: none;
        }

        .login-mobile-brand img {
            width: 52px;
            height: 52px;
            object-fit: contain;
            border-radius: 14px;
            background: #fff;
            padding: 6px;
            box-shadow: 0 12px 30px rgba(17, 25, 38, 0.12);
        }

        .login-mobile-brand h1 {
            margin: 0;
            font-size: 19px;
            font-weight: 800;
            color: #162133;
            line-height: 1.1;
        }

        .login-mobile-brand p {
            margin: 4px 0 0;
            font-size: 12px;
            color: #6d7786;
        }

        .login-content .form-control {
            height: 56px;
            border: 1px solid #dfe6ee;
            border-radius: 16px;
            font-size: 18px;
            box-shadow: none;
        }

        .login-content .form-control:focus {
            border-color: #f18520;
            box-shadow: 0 0 0 0.2rem rgba(241, 133, 32, 0.14);
        }

        .login-buttons {
            margin: 0 -6px;
        }

        .login-buttons > div {
            padding: 0 6px;
        }

        .login-buttons .btn {
            height: 54px;
            border-radius: 16px;
            font-size: 16px;
            font-weight: 700;
            display: flex;
            align-items: center;
            justify-content: center;
            width: 100%;
        }

        .alert {
            border-radius: 16px;
            margin-bottom: 18px;
        }

        @media (max-width: 767.98px) {
            body {
                min-height: 100vh;
                background:
                    radial-gradient(circle at top right, rgba(162, 188, 40, 0.22), transparent 32%),
                    radial-gradient(circle at bottom left, rgba(241, 133, 32, 0.24), transparent 35%),
                    #111926;
            }

            .login-cover-bg {
                background:
                    linear-gradient(135deg, rgba(17, 25, 38, 0.92), rgba(27, 43, 61, 0.78)),
                    rgba(0, 0, 0, 0.2);
            }

            .login.login-v2 {
                width: 100%;
                min-height: 100vh;
                display: block;
                padding: 18px 14px 24px;
                margin: 0 auto;
            }

            .login-header {
                margin-bottom: 0;
                padding: 24px 20px 18px;
                border-radius: 8px 8px 0 0;
                background: linear-gradient(135deg, rgba(241, 133, 32, 0.92), rgba(162, 188, 40, 0.9));
                color: #fff;
                box-shadow: 0 22px 60px rgba(0, 0, 0, 0.24);
            }

            .login-header .brand {
                font-size: 21px;
                font-weight: 700;
                line-height: 1.15;
                padding-right: 54px;
            }

            .login-header .brand small {
                display: block;
                margin-top: 10px;
                font-size: 12px;
                color: rgba(255, 255, 255, 0.88);
                letter-spacing: 0.02em;
            }

            .login-header .icon {
                top: 18px;
                right: 18px;
                width: 44px;
                height: 44px;
                border-radius: 14px;
                font-size: 18px;
                display: flex;
                align-items: center;
                justify-content: center;
                background: rgba(255, 255, 255, 0.16);
            }

            .login-content {
                padding: 20px 18px 22px;
                border-radius: 0 0 8px 8px;
                background: rgba(255, 255, 255, 0.97);
                box-shadow: 0 22px 60px rgba(0, 0, 0, 0.18);
                backdrop-filter: blur(8px);
            }

            .login-mobile-brand {
                display: flex;
                align-items: center;
                gap: 12px;
                margin-bottom: 18px;
            }

            .login-buttons {
                margin: 0;
            }

            .login-buttons.row {
                display: block;
            }

            .login-buttons > div {
                width: 100%;
                max-width: 100%;
                flex: 0 0 100%;
                padding: 0;
                margin-bottom: 10px;
            }

            .login-buttons > div:last-child {
                margin-bottom: 0;
            }
        }

        @media (max-width: 420px) {
            .login-header .brand {
                font-size: 19px;
            }

            .login-content .form-control {
                height: 52px;
                font-size: 16px;
            }

            .login-buttons .btn {
                height: 50px;
                font-size: 15px;
            }
        }
    </style>
     
</head>
<body class="pace-top">
    <!-- begin #page-loader -->
    <div id="page-loader" class="fade show"><span class="spinner"></span></div>
    <!-- end #page-loader -->
    
    <div class="login-cover">
        <div class="login-cover-image" style="background-image: url(assets/img/login-bg/login-bg-12.jpg)" data-id="login-cover-image"></div>
        <div class="login-cover-bg"></div>
    </div>
    <!-- begin #page-container -->
    <div id="page-container" class="fade">
        <!-- begin login -->
        <div class="login login-v2" data-pageload-addclass="animated fadeIn">
            <!-- begin brand -->
            <div class="login-header">

                <div class="brand">
                    <span class="logo"></span> <b>K-TOUCH</b> Point Of Sale
                    <small>Your trusted Sales Partner</small>
                </div>
                <div class="icon">
                    <i class="fa fa-lock"></i>
                </div>
            </div>
            <!-- end brand -->
            <!-- begin login-content -->
            <div class="login-content">
                 <div class="login-mobile-brand">
                    <img src="assets/img/logo/ktouch.png" alt="KTouch">
                    <div>
                        <h1>K-TOUCH Access</h1>
                        <p>Secure sign in for back office and front desk.</p>
                    </div>
                 </div>
                 <?php 
                              //check for any errors
                               if(isset($_GET["msg"]))     
                                {
                                    if ($_GET["msg"] == 1) {
                                        
                                    
                                       ?>
                                         <div class="alert alert-danger">
                                             
                                             <p><i class="fa fa-lg fa-warning"></i>&nbsp;Wrong Password</p>
                                             </div>
                                             <?php
                                    }elseif ($_GET['msg'] == 2) {
                                        ?>
                                        <div class="alert alert-danger">
                                             
                                             <p><i class="fa fa-lg fa-warning"></i>&nbsp;Your access has been revoked.Please contact the admin</p>
                                             </div>
                                        <?php
                                    }elseif ($_GET['msg'] == 3) {
                                        ?>
                                        <div class="alert alert-danger">
                                             
                                             <p><i class="fa fa-lg fa-warning"></i>&nbsp;You are logged in another device</p>
                                             </div>
                                        <?php
                                    }elseif ($_GET['msg'] == 4) {
                                        ?>
                                        <div class="alert alert-danger">
                                             
                                             <p><i class="fa fa-lg fa-warning"></i>&nbsp;Contact Administrators for permisions.</p>
                                             </div>
                                        <?php
                                    }elseif ($_GET['msg'] == 5) {
                                        ?>
                                        <div class="alert alert-danger">
                                            <p><i class="fa fa-lg fa-warning"></i>&nbsp;Report logger Error to administrator or developers.</p>
                                        </div>
                                        <?php
                                    }elseif ($_GET['msg'] == 6) {
                                        ?>
                                        <div class="alert alert-danger">
                                            <p><i class="fa fa-lg fa-warning"></i>&nbsp;Hacking is a serious affair.!!!</p>
                                        </div>
                                        <?php
                                    }elseif ($_GET['msg'] == 7) {
                                        ?>
                                        <div class="alert alert-danger">
                                            <p><i class="fa fa-lg fa-warning"></i>&nbsp;Ask Administrator to check roles!!!.</p>
                                        </div>
                                        <?php
                                    }
                                }
                            ?>
                         <form action="kuingira.php" method="POST" class="margin-bottom-0">
                            <div class="form-group m-b-20">
                                <input type="password" name="password" class="form-control input-lg" placeholder="Password" required  autofocus/ >
                            </div>
                            <div class="login-buttons row">
                            	<div class="col-md-6">
                                	<button type="submit" name="login" class="btn btn-success btn-block btn-lg">Sign me in</button>
                                </div>
                                <div class="col-md-6">
                                	<a href="front" type="submit" name="login" class="btn btn-warning btn-block btn-lg">FRONT</a>
                                </div>


                            </div>
                        </form>
            </div>
            <!-- end login-content -->
        </div>
        <!-- end login -->
        
    </div>
    <!-- end page container -->
    
    <!-- ================== BEGIN BASE JS ================== -->
    <script src="assets/plugins/jquery/jquery-3.2.1.min.js"></script>
    <script src="assets/plugins/jquery-ui/jquery-ui.min.js"></script>
    <script src="assets/plugins/bootstrap/4.0.0/js/bootstrap.bundle.min.js"></script>
    <!--[if lt IE 9]>
        <script src="assets/crossbrowserjs/html5shiv.js"></script>
        <script src="assets/crossbrowserjs/respond.min.js"></script>
        <script src="assets/crossbrowserjs/excanvas.min.js"></script>
    <![endif]-->
    <script src="assets/plugins/slimscroll/jquery.slimscroll.min.js"></script>
    <script src="assets/plugins/js-cookie/js.cookie.js"></script>
    <script src="assets/js/theme/default.min.js"></script>
    <script src="assets/js/apps.min.js"></script>
    <!-- ================== END BASE JS ================== --> 
    <script>
        $(document).ready(function() {
            App.init(); 
        });
    </script>
</body>
</html>
