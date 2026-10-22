<?php
if (!function_exists('back_menu_prefix')) {
    function back_menu_prefix()
    {
        $script = str_replace('\\', '/', (string)($_SERVER['PHP_SELF'] ?? ''));
        $pos = strpos($script, '/back/');
        if ($pos === false) {
            return '../';
        }
        $relative = substr($script, $pos + 6);
        $relative = ltrim($relative, '/');
        if ($relative === '') {
            return '';
        }
        $parts = explode('/', $relative);
        array_pop($parts);
        $depth = count(array_filter($parts, static function ($part) {
            return $part !== '';
        }));
        if ($depth <= 0) {
            return '';
        }
        return str_repeat('../', $depth);
    }
}

if (!function_exists('back_menu_href')) {
    function back_menu_href($link)
    {
        $link = trim((string)$link);
        if ($link === '' || $link === '#') {
            return $link;
        }
        if (preg_match('/^(?:[a-z]+:|\\/|#|javascript:)/i', $link)) {
            return $link;
        }
        // Menu links are stored relative to the back folder. Remove any
        // legacy ../ prefix before adding the current page's back prefix;
        // otherwise nested pages receive ../../ when they only need ../.
        $link = preg_replace('#^(?:\.\./)+#', '', $link);
        $link = ltrim($link, './');
        return back_menu_prefix() . $link;
    }
}
?>
<!-- begin #sidebar -->
		<div id="sidebar" class="sidebar sidebar-transparent">
			<!-- begin sidebar scrollbar -->
			<div data-scrollbar="true" data-height="100%">
				 
				<!-- begin sidebar nav -->
				<ul class="nav">
                    <?php
                    $backAdminMenu = __DIR__ . DIRECTORY_SEPARATOR . 'a_menu.php';
                    if (is_file($backAdminMenu)) {
                        include($backAdminMenu);
                    }
                    ?>
					<li class="has-sub">
						<a href="javascript:;">
					        <b class="caret"></b>
						    <i class="fa fa-cogs"></i>
						    <span>SETTINGS</span> 
						</a>
						<ul class="sub-menu">
							<li><a href="<?php echo back_menu_href('settings/settings.php'); ?>">General Details</a></li>
							<li class="has-sub"><a href="javascript:;"><b class="caret"></b>System Menus</a>
								<ul class="sub-menu">
									<li><a href="<?php echo back_menu_href('sys_menu/menu.php?pnl=1'); ?>">Back Menus</a></li> 
									<li><a href="<?php echo back_menu_href('sys_menu/menu.php?pnl=2'); ?>">Front Menu</a></li>
								</ul>
							</li>
						</ul>
					</li>
					 
			        <!-- begin sidebar minify button -->
					<li><a href="javascript:;" class="sidebar-minify-btn" data-click="sidebar-minify"><i class="fa fa-angle-double-left"></i></a></li>
			        <!-- end sidebar minify button -->
				</ul>
				<!-- end sidebar nav -->
			</div>
			<!-- end sidebar scrollbar -->
		</div>
		<!-- end #sidebar -->
<div  id="modal" class="modal fade bs-example-modal-lg" tabindex="-1" role="dialog" aria-labelledby="myModalLabel" style="display: none;">
<div class="modal-dialog"  id="load"></div>
</div>
<div  id="modal2" class="modal fade bs-example-modal-lg" tabindex="1" role="dialog" aria-labelledby="myModalLabel" style="display: none;">
<div class="modal-dialog"  id="load2"></div>
</div> 	
