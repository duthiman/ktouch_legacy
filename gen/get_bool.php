<?php
include('../inc/connection.php');
include('../inc/gen_funcs.php');

// Read inputs safely
$text = isset($_GET['text']) ? $_GET['text'] : '';
$value = isset($_GET['value']) ? $_GET['value'] : '';
$item_id = isset($_GET['item']) ? intval($_GET['item']) : 0;

$tots = get_column('kp_items','tot_num',"item_id='$item_id'");
$portions = get_column('kp_items','portions',"item_id='$item_id'");

function outline_from($btn){
    return 'btn-outline-'.str_replace('btn-','',$btn);
}

$colorMap = [
    'menu' => 'btn-success',
    'deci' => 'btn-info',
    'stock' => 'btn-primary',
    'recipe' => 'btn-secondary',
    'portion' => 'btn-dark',
    'tots' => 'btn-dark',
    'dpt' => 'btn-secondary'
];

// recipe: special modal links
if ($text === 'recipe') {
    if ($value == 1) {
        echo '<a data-toggle="modal" data-target="#modal" data-id="'.$item_id.'" id="recipe" class="btn btn-info btn-xs text-white">View Items</a>';
    } else {
        echo '<a data-toggle="modal" data-target="#modal" data-id="'.$item_id.'" id="addRecipe" class="btn btn-outline-info btn-xs text-info">Add Recipe</a>';
    }
    exit;
}

// department selector
if ($text === 'dpt') {
    if ($value == 1) { $label = 'BAR'; $btnClass = 'btn-success'; }
    elseif ($value == 2) { $label = 'KITCHEN'; $btnClass = 'btn-dark'; }
    else { $label = 'NO SET'; $btnClass = 'btn-secondary'; }
    $outline = outline_from($btnClass);
    ?>
    <div class="btn-group">
      <button type="button" class="btn btn-xs <?php echo $outline; ?> dropdown-toggle" data-toggle="dropdown" aria-haspopup="true" aria-expanded="false"><?php echo $label; ?></button>
      <div class="dropdown-menu">
        <a class="dropdown-item bg-success text-white" href="#" onclick="edit_data('<?php echo $item_id; ?>','1','dpt','Department'); return false;">BAR</a>
        <a class="dropdown-item bg-dark text-white" href="#" onclick="edit_data('<?php echo $item_id; ?>','2','dpt','Department'); return false;">KITCHEN</a>
        <a class="dropdown-item bg-secondary text-white" href="#" onclick="edit_data('<?php echo $item_id; ?>','0','dpt','Department'); return false;">NO SET</a>
      </div>
    </div>
    <?php
    exit;
}

// tots (counter units)
if ($text === 'tots') {
    if ($value == 1) {
        echo '<a data-toggle="modal" data-target="#modal" data-id="'.$item_id.'" id="manage-tots" class="btn btn-dark btn-xs text-white">'.$tots.' UNITS/PCS</a>';
    } else {
        echo '<a data-toggle="modal" data-target="#modal" data-id="'.$item_id.'" id="manage-tots" class="btn btn-outline-dark btn-xs text-dark">Manage Units</a>';
    }
    exit;
}

// portion
if ($text === 'portion') {
    if ($value == 1) {
        echo '<a data-toggle="modal" data-target="#modal" data-id="'.$item_id.'" id="manage-portion" class="btn btn-dark btn-xs text-white">'.$portions.' PORTIONS</a>';
    } else {
        echo '<a data-toggle="modal" data-target="#modal" data-id="'.$item_id.'" id="manage-portion" class="btn btn-outline-primary btn-xs text-primary">Manage Portions</a>';
    }
    exit;
}

// Generic boolean toggle for other fields
$baseBtn = isset($colorMap[$text]) ? $colorMap[$text] : 'btn-secondary';
if ($value == 1) { $label = 'YES'; $btnClass = $baseBtn; }
elseif ($value == 2) { $label = 'NO'; $btnClass = 'btn-danger'; }
else { $label = 'NOT'; $btnClass = 'btn-secondary'; }
$outline = outline_from($btnClass);
$fieldLabel = ucfirst($text);
?>
<div class="btn-group">
  <button type="button" class="btn btn-xs <?php echo $outline; ?> dropdown-toggle" data-toggle="dropdown" aria-haspopup="true" aria-expanded="false"><?php echo $label; ?></button>
  <div class="dropdown-menu">
    <a class="dropdown-item bg-success text-white" href="#" onclick="edit_data('<?php echo $item_id; ?>','1','<?php echo $text; ?>','<?php echo $fieldLabel; ?>'); return false;">YES</a>
    <a class="dropdown-item bg-danger text-white" href="#" onclick="edit_data('<?php echo $item_id; ?>','2','<?php echo $text; ?>','<?php echo $fieldLabel; ?>'); return false;">NO</a>
    <?php if ($text == 'stock') { ?>
      <div class="dropdown-divider"></div>
      <a class="dropdown-item bg-info text-white" href="#" data-toggle="modal" data-target="#modal" data-id="<?php echo $item_id; ?>" id="stock-info">View Counters / Overall Stock</a>
    <?php } ?>
  </div>
</div>

<?php
