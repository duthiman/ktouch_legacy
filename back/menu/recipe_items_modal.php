<?php
$item_id = isset($item_id) ? (int)$item_id : (int)($_REQUEST['id'] ?? 0);
$item_name = isset($item_name) ? (string)$item_name : '';
$recipe_items = isset($recipe_items) && is_array($recipe_items) ? $recipe_items : [];
?>
<div class="modal-content">
  <div class="modal-header">
    <h4 class="modal-title"><?php echo htmlspecialchars($item_name, ENT_QUOTES, 'UTF-8'); ?> RECIPE ITEMS</h4>
    <a href="../recipe/recipe.php?id=<?php echo $item_id; ?>" class="act_btn btn btn-purple btn-xs m-3 pull-right">Edit Items</a>
  </div>
  <div class="modal-body">
    <div class="table-responsive">
      <table class="table table-bordered table-striped" id="TheSo">
        <thead>
          <tr>
            <th>Raw Material</th>
            <th>Quantity</th>
          </tr>
        </thead>
        <tbody>
          <?php foreach ($recipe_items as $recipe_item) { ?>
            <tr>
              <td><?php echo htmlspecialchars((string)($recipe_item['raw_name'] ?? ''), ENT_QUOTES, 'UTF-8'); ?></td>
              <td><?php echo htmlspecialchars((string)($recipe_item['qty'] ?? ''), ENT_QUOTES, 'UTF-8'); ?></td>
            </tr>
          <?php } ?>
        </tbody>
      </table>
    </div>
  </div>
</div>
