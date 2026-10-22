<?php
require('storage.php');
$items = array_reverse(menu_new_load_items());
?>
<?php if (empty($items)): ?>
<div class="mn-banner">
  No drafts saved yet in `menu_new`. Save one of the forms to start building the isolated item registry.
</div>
<?php else: ?>
<table class="mn-table">
  <thead>
    <tr>
      <th style="width:8%;">ID</th>
      <th style="width:18%;">Type</th>
      <th>Name</th>
      <th style="width:18%;">Control</th>
      <th style="width:16%;">Area</th>
      <th style="width:18%;">Saved</th>
    </tr>
  </thead>
  <tbody>
    <?php foreach ($items as $item): ?>
    <tr>
      <td><?php echo (int)$item['draft_id']; ?></td>
      <td><?php echo htmlspecialchars(ucwords(str_replace('_', ' ', $item['form_type']))); ?></td>
      <td>
        <strong><?php echo htmlspecialchars($item['name']); ?></strong><br>
        <span style="color:#657983;font-size:12px;"><?php echo htmlspecialchars($item['summary']); ?></span>
      </td>
      <td><?php echo htmlspecialchars(ucwords(str_replace('_', ' ', $item['control_method']))); ?></td>
      <td><?php echo htmlspecialchars(ucwords(str_replace('_', ' ', $item['department']))); ?></td>
      <td><?php echo htmlspecialchars($item['created_at']); ?></td>
    </tr>
    <?php endforeach; ?>
  </tbody>
</table>
<?php endif; ?>
