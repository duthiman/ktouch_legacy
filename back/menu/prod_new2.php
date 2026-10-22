<?php
// Visual replica of the Quick Add Item reference screen.
// The existing product form and save behaviour remain in prods_new.php.
require __DIR__ . '/prods_new.php';
?>
<link rel="stylesheet" href="prod_new2.css">
<script>
$(function () {
  // The reference screen opens with Inventory selected and its stock panel visible.
  if ($('#stock').length && $('#stock').val() === '0') $('#stock').val('1').trigger('change');
});
</script>
