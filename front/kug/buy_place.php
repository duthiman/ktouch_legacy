<?php
include("../clude/head.php");
?>
<body>
  <div class="container" style="max-width:520px; margin:80px auto;">
    <div class="panel panel-default" style="border-radius:10px; overflow:hidden;">
      <div class="panel-heading" style="font-weight:700;">Submitting Purchase</div>
      <div class="panel-body">
        <div id="buy-place-status" style="font-weight:700; color:#6f4b2e;">Submitting the purchase request...</div>
      </div>
    </div>
  </div>
  <script src="../../assets/plugins/jquery/jquery-3.2.1.min.js"></script>
  <script>
    $.ajax({
      url: 'API/kug_api.php',
      type: 'POST',
      dataType: 'json',
      data: { action: 'submit' }
    }).done(function(res) {
      if (res && res.ok && res.data && res.data.redirect) {
        window.location = res.data.redirect;
        return;
      }
      $('#buy-place-status').text((res && res.message) ? res.message : 'Could not submit purchase.');
    }).fail(function() {
      $('#buy-place-status').text('Could not submit purchase.');
    });
  </script>
</body>
</html>
