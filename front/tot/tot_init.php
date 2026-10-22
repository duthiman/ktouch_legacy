<?php
include("../clude/head.php");
?>
<body>
  <div class="container" style="max-width:520px; margin:80px auto;">
    <div class="panel panel-default" style="border-radius:10px; overflow:hidden;">
      <div class="panel-heading" style="font-weight:700;">Preparing Generated Units</div>
      <div class="panel-body">
        <div id="tot-init-status" style="font-weight:700; color:#6f4b2e;">Starting your generated-units session...</div>
      </div>
    </div>
  </div>

  <script src="../../assets/plugins/jquery/jquery-3.2.1.min.js"></script>
  <script>
    $.ajax({
      url: 'API/tots_api.php',
      type: 'POST',
      dataType: 'json',
      data: { action: 'init' }
    }).done(function(res) {
      if (res && res.ok && res.data && res.data.redirect) {
        window.location = res.data.redirect;
        return;
      }
      $('#tot-init-status').text((res && res.message) ? res.message : 'Could not start generated units.');
    }).fail(function() {
      $('#tot-init-status').text('Could not start generated units.');
    });
  </script>
</body>
</html>
