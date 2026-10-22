<?php
include("../clude/head.php");
?>
<body>
  <div class="container" style="max-width:520px; margin:80px auto;">
    <div class="panel panel-default" style="border-radius:10px; overflow:hidden;">
      <div class="panel-heading" style="font-weight:700;">Preparing Purchase Draft</div>
      <div class="panel-body">
        <div id="buy-init-status" style="font-weight:700; color:#6f4b2e;">Starting your purchase draft...</div>
      </div>
    </div>
  </div>
  <script src="../../assets/plugins/jquery/jquery-3.2.1.min.js"></script>
  <script>
    $.ajax({
      url: 'API/kug_api.php',
      type: 'POST',
      dataType: 'json',
      data: { action: 'init' }
    }).done(function(res) {
      if (res && res.ok && res.data && res.data.redirect) {
        window.location = res.data.redirect;
        return;
      }
      $('#buy-init-status').text((res && res.message) ? res.message : 'Could not start purchase draft.');
    }).fail(function() {
      $('#buy-init-status').text('Could not start purchase draft.');
    });
  </script>
</body>
</html>
