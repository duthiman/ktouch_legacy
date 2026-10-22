<?php
$item_id = isset($item_id) ? (int)$item_id : (int)($_REQUEST['id'] ?? 0);
$item_name = isset($item_name) ? (string)$item_name : '';
?>
<div class="modal-content">
  <div class="modal-header">
    <button type="button" class="close" data-dismiss="modal" aria-hidden="true">×</button>
    <h4 class="modal-title">CONFRIM RECIPE MANAGEMENT FOR <?php echo htmlspecialchars($item_name, ENT_QUOTES, 'UTF-8'); ?></h4>
  </div>
  <div class="modal-body">
    <div id="modal-results"></div>
  </div>
  <div class="modal-footer">
    <a href="javascript:;" class="btn btn-xs btn-danger" data-dismiss="modal">NO</a>
    <a type="button" id="btn-cancel" class="btn btn-xs btn-success" onclick="confirm_recipe_edit();">YES</a>
  </div>
</div>
<script type="text/javascript">
  function confirm_recipe_edit() {
    var item_id = '<?php echo $item_id; ?>';
    window.location.replace("../recipe/recipe.php?id=" + item_id);
  }
</script>
