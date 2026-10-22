
	<!-- ================== BEGIN BASE JS ================== -->
	<script src="<?php echo $asset_prefix; ?>assets/plugins/jquery/jquery-3.2.1.min.js"></script>
	<script src="<?php echo $asset_prefix; ?>assets/plugins/jquery-ui/jquery-ui.min.js"></script>
	<script src="<?php echo $asset_prefix; ?>assets/plugins/bootstrap/4.0.0/js/bootstrap.bundle.min.js"></script> 
	<!-- ================== BEGIN PAGE LEVEL JS ================== -->
	  	<script src="<?php echo $asset_prefix; ?>assets/plugins/Hullabaloo/js/hullabaloo.js"></script> 
  	<!-- ================== END PAGE LEVEL JS ================== -->
	<script src="<?php echo $asset_prefix; ?>assets/plugins/slimscroll/jquery.slimscroll.min.js"></script>
	<script src="<?php echo $asset_prefix; ?>assets/plugins/js-cookie/js.cookie.js"></script>
	<script src="<?php echo $asset_prefix; ?>assets/js/theme/default.min.js"></script>
	<script src="<?php echo $asset_prefix; ?>assets/js/apps.min.js"></script>
	<script>
		jQuery(function () {
			if (typeof App !== 'undefined' && typeof App.init === 'function') {
				App.init();
			}
		});
	</script>
	<!-- ================== END BASE JS ================== -->
