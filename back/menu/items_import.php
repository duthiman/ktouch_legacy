<?php
require("../clude/head.php");
?>
<div id="page-container" class="page-container fade page-without-sidebar page-header-fixed page-with-top-menu">
  <?php require("../clude/header.php"); ?>
  <?php require("../clude/top_menu.php"); ?>
  <div id="content" class="content">
    <div class="row">
      <div class="col-lg-12 col-md-12">
        <div class="panel">
          <div class="panel-body text-center">
            <form id="import-form" enctype="multipart/form-data">
              <div class="form-group">
                <div id="upload-msg"></div>
                <input type="file" name="file" id="file" accept=".csv">
              </div>
              <a href="items_template.csv" download="items_template"><i class="fa fa-file-excel" style="color: green;"></i> Download template</a>
            </form>
          </div>
          <div class="panel-footer text-right">
            <button type="button" id="preview-import" class="btn btn-primary btn-xs">Preview Data Before Upload</button>
            <button type="button" id="submit-import" class="btn btn-lime btn-xs">Upload</button>
          </div>
        </div>
      </div>
      <div class="col-md-12 panel" id="results"></div>
    </div>
  </div>
</div>
<script src="../../assets/plugins/jquery/jquery-3.2.1.min.js"></script>
<script src="../../assets/plugins/jquery-ui/jquery-ui.min.js"></script>
<script src="../../assets/plugins/bootstrap/4.0.0/js/bootstrap.bundle.min.js"></script>
<script src="../../assets/plugins/slimscroll/jquery.slimscroll.min.js"></script>
<script src="../../assets/plugins/js-cookie/js.cookie.js"></script>
<script src="../../assets/js/theme/default.min.js"></script>
<script src="../../assets/plugins/Hullabaloo/js/hullabaloo.js"></script>
<script src="../../assets/js/apps.min.js"></script>
<script>
  var menuApiUrl = 'API/menu_api.php';
  $(document).ready(function() {
    App.init();
  });
</script>
<script src="items_import.js"></script>
</body>
</html>
