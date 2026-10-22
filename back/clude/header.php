
		<div id="header" class="header navbar-default">
			<!-- begin navbar-header -->
			<div class="navbar-header">
				<a href="<?php echo $asset_prefix; ?>back/dash/admin_dash.php" class="navbar-brand"><img src="<?php echo $asset_prefix; ?>assets/img/logo/ktouch.png" style="width: 120px; height: auto;"> </a>
				<button type="button" class="navbar-toggle" id="back-mobile-menu-toggle" data-click="sidebar-toggled" aria-label="Toggle menu">
					<span class="icon-bar"></span>
					<span class="icon-bar"></span>
					<span class="icon-bar"></span>
				</button>
			</div>
			<!-- end navbar-header -->
 

			
			<!-- begin header-nav -->
			<ul class="navbar-nav navbar-right">
				<li class="nav-item">
					<a href="../settings/settings.php" class="dropdown-toggle f-s-14">
						<i class="fa fa-cog"></i>
						<span class="d-none d-md-inline">Settings</span>
					</a>
				</li>
				 
				<li class="dropdown">
					<a href="javascript:;" data-toggle="dropdown" class="dropdown-toggle f-s-14">
						<i class="fa fa-bell"></i>
						<span class="label" ><?php //echo stock_out_alert()+purchase_alerts()+reorder_alerts(); ?></span>
					</a>
					<ul class="dropdown-menu media-list dropdown-menu-right"> 
						<li class="media">
							<a href="javascript:;"> 
								<div class="media-body">
									<h6 class="media-heading"  style="color: #fff;">Received Purchase Orders<span class="badge badge-primary pull-right"><?php //echo purchase_alerts(); ?></span></h6>
									<p  style="color: #fff;">Shows all raised Purchase order not yet received.</p>

								</div>
							</a>
						</li>
						<li class="media">
							<a href="javascript:;"> 
								<div class="media-body">
									<h6 class="media-heading"  style="color: #fff;">Items Below Reorder level<span class="badge badge-warning pull-right"><?php //echo reorder_alerts(); ?></span></h6>
									<p  style="color: #fff;">Shows Items that are below reorder level.</p> 
								</div>
							</a>
						</li>
						<li class="media">
							<a href="javascript:;"> 
								<div class="media-body">
									<h6 class="media-heading"  style="color: #fff;">Out Of Stock<span class="badge badge-danger pull-right"><?php //echo stock_out_alert(); ?></span></h6>
									<p  style="color: #fff;">Shows items that are completely out of stock.</p> 
								</div>
							</a>
						</li>
						 
					</ul>
				</li>
				<li class="dropdown navbar-user">
					<a href="javascript:;" class="dropdown-toggle" data-toggle="dropdown">
						<img src="<?php echo $asset_prefix; ?>assets/img/user/user-12.jpg" alt="" style="margin: 0 auto;  padding: 3px;border: 3px solid #d2d6de; border-radius: 50%;" /> 
						<span class="d-none d-md-inline"><?php echo htmlspecialchars($_SESSION['username'] ?? 'User'); ?></span> <b class="caret"></b>
					</a>
					<div class="dropdown-menu dropdown-menu-right">
						<a href="javascript:;" class="dropdown-item">Edit Profile</a>
						<a href="javascript:;" class="dropdown-item"><span class="badge badge-danger pull-right">2</span> Inbox</a>
						<a href="javascript:;" class="dropdown-item">Calendar</a>
						<a href="../settings/settings.php" class="dropdown-item">Settings</a>
						<div class="dropdown-divider"></div>
						<a href="../../log_out.php" class="dropdown-item">Log Out</a>
					</div>
				</li>
			</ul>
			<!-- end header navigation right -->
		</div>
		<script type="text/javascript">
		if (typeof window !== 'undefined') {
			document.addEventListener('DOMContentLoaded', function () {
				var toggleButton = document.getElementById('back-mobile-menu-toggle');
				if (!toggleButton || typeof window.jQuery === 'undefined') {
					return;
				}

				window.jQuery(toggleButton).on('click', function () {
					var page = document.getElementById('page-container');
					if (!page || !page.classList.contains('page-with-top-menu') || window.innerWidth > 767) {
						return;
					}

					window.jQuery('#top-menu').stop(true, true).slideToggle(250);
				});
			});
		}
		</script>
