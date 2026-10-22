<?php
session_start();
include("../../inc/connection.php");
include_once("../../inc/gen_funcs.php");

$item_id = isset($_REQUEST['id']) ? (int)$_REQUEST['id'] : 0;
$qty = isset($_REQUEST['qty']) ? (float)$_REQUEST['qty'] : 1;
$required_count = max(1, (int)ceil($qty));
$item_name = $item_id > 0 ? get_column('kp_items', 'name', "item_id='$item_id'") : '';
$attr_rows = [];

$attr_query = "SELECT m.attr_id, a.name, a.price_change
               FROM kp_item_attribute_mapping m
               JOIN kp_items_attributes a ON m.attr_id = a.id
               WHERE m.item_id='$item_id'
               ORDER BY a.name ASC";
$attr_result = mysqli_query($connection, $attr_query);
if ($attr_result) {
    while ($attr = mysqli_fetch_assoc($attr_result)) {
        $attr_rows[] = [
            'id' => (int)$attr['attr_id'],
            'name' => $attr['name'],
            'price_change' => (float)($attr['price_change'] ?? 0),
        ];
    }
}
?>
<style type="text/css">
    .kw-attr-shell {
        max-width: 680px;
    }
    .kw-attr-grid {
        display: flex;
        flex-wrap: wrap;
        gap: 10px;
    }
    .kw-attr-btn {
        min-height: 54px;
        min-width: 110px;
        border-radius: 10px;
        font-size: 14px;
        color: #fff;
        background: #02550E;
        border: 2px solid #61b846;
        font-weight: bold;
        padding: 8px 12px;
        text-align: center;
    }
    .kw-attr-meta {
        display: flex;
        justify-content: space-between;
        gap: 12px;
        flex-wrap: wrap;
        margin-bottom: 12px;
        font-weight: 800;
        color: #5e3b15;
    }
    .kw-attr-picked {
        margin-top: 14px;
        border-top: 1px solid #eee1cf;
        padding-top: 12px;
    }
    .kw-attr-picked ul {
        list-style: none;
        padding: 0;
        margin: 0;
        display: grid;
        gap: 8px;
    }
    .kw-attr-picked li {
        display: flex;
        justify-content: space-between;
        align-items: center;
        gap: 12px;
        padding: 8px 10px;
        border-radius: 8px;
        background: #fff8f0;
        border: 1px solid #ecd8ba;
    }
    .kw-attr-remove {
        border: 0;
        border-radius: 6px;
        background: #b42318;
        color: #fff;
        padding: 6px 10px;
        font-weight: 800;
    }
    .kw-attr-actions {
        display: flex;
        justify-content: space-between;
        align-items: center;
        gap: 12px;
        margin-top: 16px;
    }
    .kw-attr-submit {
        border: 0;
        border-radius: 8px;
        background: #61b846;
        color: #fff;
        padding: 10px 18px;
        font-weight: 900;
    }
    .kw-attr-submit[disabled] {
        opacity: 0.5;
    }
</style>

<div class="modal-content kw-attr-shell">
    <div class="modal-header">
        <h4 class="modal-title">SELECT ATTRIBUTES FOR <?php echo htmlspecialchars($item_name, ENT_QUOTES); ?></h4>
        <button type="button" class="close" data-dismiss="modal" aria-label="Close"><span aria-hidden="true">×</span></button>
    </div>
    <div class="modal-body" style="padding: 12px;">
        <div class="kw-attr-meta">
            <span>Quantity: <?php echo number_format($qty, 3); ?></span>
            <span>Accompaniments Needed: <span id="kw-attr-required"><?php echo $required_count; ?></span></span>
            <span>Selected: <span id="kw-attr-selected-count">0</span></span>
        </div>

        <div class="kw-attr-grid">
            <?php foreach ($attr_rows as $attr) { ?>
                <button
                    type="button"
                    class="kw-attr-btn"
                    data-attr-id="<?php echo $attr['id']; ?>"
                    data-attr-name="<?php echo htmlspecialchars($attr['name'], ENT_QUOTES); ?>"
                    data-price-change="<?php echo $attr['price_change']; ?>"
                >
                    <?php echo htmlspecialchars($attr['name'], ENT_QUOTES); ?>
                    (<?php echo $attr['price_change'] != 0 ? (($attr['price_change'] > 0 ? '+' : '') . $attr['price_change']) : 'Free'; ?>)
                </button>
            <?php } ?>
        </div>

        <div class="kw-attr-picked">
            <strong>Selected accompaniments</strong>
            <ul id="kw-attr-picked-list"></ul>
        </div>
    </div>
    <div class="modal-footer kw-attr-actions">
        <button type="button" id="close" class="btn btn-default" data-dismiss="modal">Close</button>
        <button type="button" class="kw-attr-submit" id="kw-attr-submit" disabled>Add Order</button>
    </div>
</div>

<script type="text/javascript">
  (function() {
    var itemId = <?php echo (int)$item_id; ?>;
    var qty = <?php echo json_encode($qty); ?>;
    var requiredCount = <?php echo (int)$required_count; ?>;
    var selections = [];

    function renderSelections() {
      var html = '';
      for (var i = 0; i < selections.length; i++) {
        html += '<li><span>' + escapeHtml(selections[i].name) + '</span><button type="button" class="kw-attr-remove" data-remove-index="' + i + '">Remove</button></li>';
      }
      $('#kw-attr-picked-list').html(html);
      $('#kw-attr-selected-count').text(selections.length);
      $('#kw-attr-submit').prop('disabled', selections.length !== requiredCount);
    }

    function submitSelections() {
      if (!selections.length) {
        return;
      }
      var primaryAttrId = selections.length === 1 ? selections[0].id : '';
      var primaryPriceChange = 0;
      for (var i = 0; i < selections.length; i++) {
        primaryPriceChange += Number(selections[i].price_change || 0);
      }
      $.post(window.KWEDIA_API_URL, {
        action: 'add_item',
        id: itemId,
        qty: qty,
        attr_id: primaryAttrId,
        price_modifier: primaryPriceChange,
        accompaniment_json: JSON.stringify(selections)
      }).done(function(data) {
        if (data.ok) {
          kwNotify('Order Updated', data.message);
          if (data.data && data.data.cart) {
            renderCurrentCart(data.data.cart);
          }
          refreshCurrentItemsList();
          load_item_list();
          $('#modal').modal('hide');
          $('#load').html('');
          return;
        }
        alert(data.message || 'Failed to add item');
      }).fail(function(jqXHR) {
        alert('Error adding item: ' + (jqXHR.responseJSON && jqXHR.responseJSON.message ? jqXHR.responseJSON.message : 'Connection error'));
      });
    }

    $(document).off('click.kwAttrSelect', '.kw-attr-btn').on('click.kwAttrSelect', '.kw-attr-btn', function() {
      if (selections.length >= requiredCount) {
        return;
      }
      selections.push({
        id: parseInt($(this).data('attrId'), 10) || 0,
        name: $(this).data('attrName') || '',
        price_change: parseFloat($(this).data('priceChange')) || 0
      });
      renderSelections();
      if (requiredCount === 1 && selections.length === 1) {
        submitSelections();
      }
    });

    $(document).off('click.kwAttrRemove', '.kw-attr-remove').on('click.kwAttrRemove', '.kw-attr-remove', function() {
      var index = parseInt($(this).data('removeIndex'), 10);
      if (index >= 0) {
        selections.splice(index, 1);
        renderSelections();
      }
    });

    $('#kw-attr-submit').off('click.kwAttrSubmit').on('click.kwAttrSubmit', function() {
      if (selections.length === requiredCount) {
        submitSelections();
      }
    });

    renderSelections();
  })();
</script>
