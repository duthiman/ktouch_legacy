<?php
include("../clude/head.php");
?>
<body>
  <div class="container" style="max-width:520px; margin:80px auto;">
    <div class="panel panel-default" style="border-radius:10px; overflow:hidden;">
      <div class="panel-heading" style="font-weight:700;">Submitting Generated Units</div>
      <div class="panel-body">
        <div id="tot-place-status" style="font-weight:700; color:#6f4b2e;">Preparing the generated-units print action...</div>
      </div>
    </div>
  </div>

  <script src="../../assets/plugins/jquery/jquery-3.2.1.min.js"></script>
  <script>
    $.ajax({
      url: 'API/tots_api.php',
      type: 'POST',
      dataType: 'json',
      data: { action: 'get_print_modal' }
    }).done(function(res) {
      if (res && res.ok) {
        window.location = 'index.php';
        return;
      }
      $('#tot-place-status').text((res && res.message) ? res.message : 'Could not continue to submit generated units.');
    }).fail(function() {
      $('#tot-place-status').text('Could not continue to submit generated units.');
    });
  </script>
</body>
</html>
