<?php
include("../clude/head.php");
?>
<body>
  <div class="container" style="max-width:520px; margin:80px auto;">
    <div class="panel panel-default" style="border-radius:10px; overflow:hidden;">
      <div class="panel-heading" style="font-weight:700;">Creating Supplier</div>
      <div class="panel-body">
        <div id="supplier-create-status" style="font-weight:700; color:#6f4b2e;">Submitting the supplier details...</div>
      </div>
    </div>
  </div>
  <script src="../../assets/plugins/jquery/jquery-3.2.1.min.js"></script>
  <script>
    $.ajax({
      url: 'API/kug_api.php',
      type: 'POST',
      dataType: 'json',
      data: {
        action: 'create_supplier',
        sup_name: <?php echo json_encode($_POST['sup_name'] ?? $_GET['sup_name'] ?? ''); ?>,
        sup_phone: <?php echo json_encode($_POST['sup_phone'] ?? $_GET['sup_phone'] ?? ''); ?>,
        sup_pin: <?php echo json_encode($_POST['sup_pin'] ?? $_GET['sup_pin'] ?? ''); ?>,
        sup_vat: <?php echo json_encode($_POST['sup_vat'] ?? $_GET['sup_vat'] ?? ''); ?>
      }
    }).done(function(res) {
      $('#supplier-create-status').text((res && res.message) ? res.message : 'Supplier request completed.');
    }).fail(function() {
      $('#supplier-create-status').text('Could not create supplier.');
    });
  </script>
</body>
</html>
