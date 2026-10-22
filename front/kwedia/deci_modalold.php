<?php
session_start();
include("../../inc/connection.php");
include_once("../../inc/gen_funcs.php");

$item_id = isset($_REQUEST['id']) ? (int)$_REQUEST['id'] : 0;
$has_attributes = $item_id > 0 ? (int)get_column('kp_items', 'has_attributes', "item_id='$item_id'") : 0;
$unit_price = $item_id > 0 ? (float)get_column('kp_items', 'price', "item_id='$item_id'") : 0;
?>

<style type="text/css">
    .deci-desktop {
        display: block;
    }
    .deci-mobile {
        display: none;
    }
    .deci-shell .modal-content {
        border-radius: 14px;
        overflow: hidden;
    }
    .deci-body {
        padding: 12px;
        background: linear-gradient(180deg, #f6f0d7 0%, #efe3bf 100%);
    }
    .deci-top {
        padding: 12px;
        border-radius: 10px;
        background: linear-gradient(135deg, #7e2014 0%, #b94828 52%, #e7a33a 100%);
        color: #fff8ea;
        margin-bottom: 10px;
    }
    .deci-top-row {
        display: flex;
        justify-content: space-between;
        align-items: flex-start;
        gap: 10px;
    }
    .deci-top-main {
        min-width: 0;
    }
    .deci-top h4 {
        margin: 0;
        font-size: 16px;
        font-weight: 900;
        text-transform: uppercase;
    }
    .deci-top p {
        margin: 4px 0 0;
        font-size: 12px;
    }
    .deci-price {
        min-width: 88px;
        text-align: right;
        font-size: 22px;
        font-weight: 900;
        color: #fff7cf;
        line-height: 1;
        letter-spacing: 0.04em;
    }
    .deci-price small {
        display: block;
        margin-bottom: 3px;
        font-size: 10px;
        color: #fff0c5;
        text-transform: uppercase;
        letter-spacing: 0.08em;
    }
    .deci-mode-row {
        display: grid;
        grid-template-columns: 1fr 1fr;
        gap: 8px;
        margin-bottom: 10px;
    }
    .deci-toggle-btn {
        min-height: 44px;
        border: 0;
        border-radius: 10px;
        background: #fff;
        color: #7e3a20;
        font-size: 12px;
        font-weight: 800;
        text-transform: uppercase;
        box-shadow: 0 6px 12px rgba(86, 61, 24, 0.12);
    }
    .deci-display {
        width: 100%;
        height: 54px;
        border: 2px solid #d39f45;
        border-radius: 10px;
        font-size: 22px;
        font-weight: 800;
        text-align: right;
        padding: 8px 12px;
        margin-bottom: 10px;
        background: #fffdf8;
    }
    .deci-pad {
        display: grid;
        grid-template-columns: repeat(3, 1fr);
        gap: 8px;
    }
    .deci-key {
        min-height: 58px;
        border: 0;
        border-radius: 10px;
        background: #fff;
        color: #60320d;
        font-size: 22px;
        font-weight: 900;
        box-shadow: 0 6px 14px rgba(86, 61, 24, 0.12);
    }
    .deci-key-clear {
        background: #ffd9c2;
        color: #8a3a0a;
    }
    .deci-actions {
        display: grid;
        grid-template-columns: 1fr 1fr;
        gap: 8px;
        margin-top: 12px;
    }
    .deci-action-btn {
        min-height: 48px;
        border: 0;
        border-radius: 10px;
        font-size: 14px;
        font-weight: 900;
        text-transform: uppercase;
    }
    .deci-close {
        background: #ead6d6;
        color: #7e2014;
    }
    .deci-order {
        background: linear-gradient(135deg, #4f7e1e 0%, #7fbb33 100%);
        color: #fff;
    }
    .deci-order.is-loading {
        opacity: 0.78;
        cursor: wait;
    }
    .deci-order .deci-loading-spinner {
        display: inline-block;
        width: 14px;
        height: 14px;
        margin-right: 6px;
        vertical-align: -2px;
        border: 2px solid rgba(255,255,255,0.45);
        border-top-color: #fff;
        border-radius: 50%;
        animation: deci-spin .65s linear infinite;
    }
    @keyframes deci-spin { to { transform: rotate(360deg); } }
    .deci-desktop .modal-dialog {
        width: 360px;
        margin: 30px auto;
    }
    @media (max-width: 991px) {
        .deci-desktop {
            display: none;
        }
        .deci-mobile {
            display: block;
        }
        .modal-dialog {
            width: calc(100vw - 24px) !important;
            max-width: 420px;
            margin: 18px auto;
        }
    }
</style>

<input type="hidden" class="form-control" id="item_id" value="<?php echo $item_id; ?>">
<input type="hidden" id="unit_price" value="<?php echo $unit_price; ?>">

<div class="deci-desktop deci-shell">
  <div class="modal-content">
    <div class="deci-body">
      <div class="deci-top">
        <div class="deci-top-row">
          <div class="deci-top-main">
            <h4>Choose Quantity</h4>
            <p id="entry_mode" style="margin-top: 4px;">Mode: Quantity</p>
          </div>
          <div class="deci-price"><small>Price</small><?php echo number_format($unit_price, 2); ?></div>
        </div>
      </div>
      <div class="deci-mode-row">
        <button type="button" class="deci-toggle-btn" id="toggle_mode">Switch to Amount</button>
        <button type="button" class="deci-toggle-btn" id="clear">Clear</button>
      </div>
      <input type="text" name="deci_qty" id="deci_qty" class="deci-display" autofocus />
      <div class="deci-pad">
        <?php
        $buttons = ['7', '8', '9', '4', '5', '6', '1', '2', '3'];
        foreach ($buttons as $btn) {
            echo '<button type="button" class="deci-key desktop-num" data-value="' . $btn . '">' . $btn . '</button>';
        }
        ?>
        <button type="button" class="deci-key deci-key-clear" id="backspace_desktop">&larr;</button>
        <button type="button" class="deci-key desktop-num" data-value="0">0</button>
        <button type="button" class="deci-key desktop-num" data-value=".">.</button>
      </div>
      <div class="deci-actions">
        <button type="button" id="close" class="deci-action-btn deci-close" data-dismiss="modal">Close</button>
        <?php if ($has_attributes === 1) { ?>
          <button type="button" onclick="orderattr(this)" class="deci-action-btn deci-order">Order</button>
        <?php } else { ?>
          <button type="button" onclick="order(this)" class="deci-action-btn deci-order">Order</button>
        <?php } ?>
      </div>
    </div>
  </div>
</div>

<div class="deci-mobile deci-shell">
  <div class="modal-content">
    <div class="deci-body">
      <div class="deci-top">
        <div class="deci-top-row">
          <div class="deci-top-main">
            <h4>Choose Quantity</h4>
            <p id="entry_mode_mobile">Mode: Quantity</p>
          </div>
          <div class="deci-price"><small>Price</small><?php echo number_format($unit_price, 2); ?></div>
        </div>
      </div>
      <div class="deci-mode-row">
        <button type="button" class="deci-toggle-btn" id="toggle_mode_mobile">Switch to Amount</button>
        <button type="button" class="deci-toggle-btn" id="clear_mobile">Clear</button>
      </div>
      <input type="text" name="deci_qty_mobile" id="deci_qty_mobile" class="deci-display" autofocus />
      <div class="deci-pad">
        <?php
        foreach ($buttons as $btn) {
            echo '<button type="button" class="deci-key mobile-num" data-value="' . $btn . '">' . $btn . '</button>';
        }
        ?>
        <button type="button" class="deci-key deci-key-clear" id="backspace_mobile">&larr;</button>
        <button type="button" class="deci-key mobile-num" data-value="0">0</button>
        <button type="button" class="deci-key mobile-num" data-value=".">.</button>
      </div>
      <div class="deci-actions">
        <button type="button" id="close_mobile" class="deci-action-btn deci-close" data-dismiss="modal">Close</button>
        <?php if ($has_attributes === 1) { ?>
          <button type="button" onclick="orderattr(this)" class="deci-action-btn deci-order">Order</button>
        <?php } else { ?>
          <button type="button" onclick="order(this)" class="deci-action-btn deci-order">Order</button>
        <?php } ?>
      </div>
    </div>
  </div>
</div>

<script type="text/javascript">
  window.setDeciOrderLoading = function(button, loading) {
    if (!button) return;
    if (loading) {
      if (!button.dataset.originalHtml) button.dataset.originalHtml = button.innerHTML;
      button.disabled = true;
      button.classList.add('is-loading');
      button.setAttribute('aria-busy', 'true');
      button.innerHTML = '<span class="deci-loading-spinner"></span>Adding...';
    } else {
      button.disabled = false;
      button.classList.remove('is-loading');
      button.removeAttribute('aria-busy');
      if (button.dataset.originalHtml) {
        button.innerHTML = button.dataset.originalHtml;
        delete button.dataset.originalHtml;
      }
    }
  };

  $(document).ready(function () {
    var mode = 'qty';
    var unitPrice = parseFloat($('#unit_price').val()) || 0;
    var modalRoot = $('.deci-shell:visible').first();

    function syncDisplay(value) {
      $('#deci_qty').val(value);
      $('#deci_qty_mobile').val(value);
    }

    $('#toggle_mode').off('click.deci').on('click.deci', function () {
      if (mode === 'qty') {
        mode = 'amount';
        $('#toggle_mode, #toggle_mode_mobile').text('Switch to Quantity');
        $('#entry_mode, #entry_mode_mobile').text('Mode: Amount');
      } else {
        mode = 'qty';
        $('#toggle_mode, #toggle_mode_mobile').text('Switch to Amount');
        $('#entry_mode, #entry_mode_mobile').text('Mode: Quantity');
      }
      syncDisplay('');
    });

    $('#toggle_mode_mobile').off('click.deci').on('click.deci', function () {
      $('#toggle_mode').trigger('click');
    });

    $('.desktop-num').off('click.deci').on('click.deci', function () {
      syncDisplay($('#deci_qty').val() + $(this).data('value'));
    });

    $('.mobile-num').off('click.deci').on('click.deci', function () {
      syncDisplay($('#deci_qty_mobile').val() + $(this).data('value'));
    });

    $('#clear, #clear_mobile').off('click.deci').on('click.deci', function () {
      syncDisplay('');
    });

    $('#backspace_desktop').off('click.deci').on('click.deci', function () {
      syncDisplay($('#deci_qty').val().slice(0, -1));
    });

    $('#backspace_mobile').off('click.deci').on('click.deci', function () {
      syncDisplay($('#deci_qty_mobile').val().slice(0, -1));
    });

    $('#deci_qty').off('input.deci').on('input.deci', function () {
      if ($('#deci_qty').is(':visible')) {
        syncDisplay($(this).val());
      }
    });

    $('#deci_qty_mobile').off('input.deci').on('input.deci', function () {
      if ($('#deci_qty_mobile').is(':visible')) {
        syncDisplay($(this).val());
      }
    });

    if (modalRoot.length) {
      modalRoot.find('#deci_qty, #deci_qty_mobile').first().trigger('focus');
    }

    window.getFinalQty = function () {
      var currentValue = $('#deci_qty_mobile').is(':visible') ? $('#deci_qty_mobile').val() : $('#deci_qty').val();
      var val = parseFloat(currentValue);
      if (isNaN(val) || val <= 0) {
        return 0;
      }
      if (mode === 'qty') {
        return val;
      }
      if (unitPrice <= 0) {
        return 0;
      }
      return (val / unitPrice).toFixed(3);
    };
  });
</script>
