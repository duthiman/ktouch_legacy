<?php
include("../clude/head.php");
?>
<body>
  <div class="container" style="max-width:520px; margin:80px auto;">
    <div class="panel panel-default" style="border-radius:10px; overflow:hidden;">
      <div class="panel-heading" style="font-weight:700;">Cancelling Generated Units</div>
      <div class="panel-body">
        <div id="tot-cancel-status" style="font-weight:700; color:#6f4b2e;">Restoring stock and clearing the current generated-units session...</div>
      </div>
    </div>
  </div>

  <script src="../../assets/plugins/jquery/jquery-3.2.1.min.js"></script>
  <script>
    $.ajax({
      url: 'API/tots_api.php',
      type: 'POST',
      dataType: 'json',
      data: { action: 'cancel' }
    }).done(function(res) {
      if (res && res.ok && res.data && res.data.redirect) {
        window.location = res.data.redirect;
        return;
      }
      $('#tot-cancel-status').text((res && res.message) ? res.message : 'Could not cancel generated units.');
    }).fail(function() {
      $('#tot-cancel-status').text('Could not cancel generated units.');
    });
  </script>
</body>
</html>
