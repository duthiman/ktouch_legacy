<?php 
  session_start(); 
  include("../../inc/connection.php");
  include("funcs.php"); 
  $item_id = $_REQUEST['id'];
  $item_name = get_column('kp_items','name',"item_id='$item_id'");

  $get_qty = mysqli_query($connection,"SELECT port_status,portions FROM kp_items WHERE item_id='$item_id'") or die("Could not get the stock");
    $row = mysqli_fetch_assoc($get_qty);
    $port_status = $row['port_status'];
    $portions = $row['portions'];
?>

<style type="text/css">
    .req-deci-shell .modal-content {
        border-radius: 14px;
        overflow: hidden;
    }
    .req-deci-body {
        padding: 12px;
        background: linear-gradient(180deg, #f6f0d7 0%, #efe3bf 100%);
    }
    .req-deci-top {
        padding: 12px;
        border-radius: 10px;
        background: linear-gradient(135deg, #7e2014 0%, #b94828 52%, #e7a33a 100%);
        color: #fff8ea;
        margin-bottom: 10px;
    }
    .req-deci-top-row {
        display: flex;
        justify-content: space-between;
        align-items: flex-start;
        gap: 10px;
    }
    .req-deci-top-main {
        min-width: 0;
    }
    .req-deci-top h4 {
        margin: 0;
        font-size: 16px;
        font-weight: 900;
        text-transform: uppercase;
        line-height: 1.25;
    }
    .req-deci-top p {
        margin: 4px 0 0;
        font-size: 12px;
    }
    .req-deci-badge {
        min-width: 88px;
        text-align: right;
        font-size: 20px;
        font-weight: 900;
        color: #fff7cf;
        line-height: 1;
        letter-spacing: 0.04em;
    }
    .req-deci-badge small {
        display: block;
        margin-bottom: 4px;
        font-size: 10px;
        color: #fff0c5;
        text-transform: uppercase;
        letter-spacing: 0.08em;
    }
    .req-deci-hint {
        margin-bottom: 10px;
        padding: 10px 12px;
        border-radius: 10px;
        background: #fff7dc;
        color: #7e3a20;
        font-size: 12px;
        font-weight: 700;
        border: 1px solid rgba(126, 58, 32, 0.14);
    }
    .req-deci-display {
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
    .req-deci-pad {
        display: grid;
        grid-template-columns: repeat(3, 1fr);
        gap: 8px;
    }
    .req-deci-key {
        min-height: 58px;
        border: 0;
        border-radius: 10px;
        background: #fff;
        color: #60320d;
        font-size: 22px;
        font-weight: 900;
        box-shadow: 0 6px 14px rgba(86, 61, 24, 0.12);
    }
    .req-deci-key-clear {
        background: #ffd9c2;
        color: #8a3a0a;
    }
    .req-deci-actions {
        display: grid;
        grid-template-columns: 1fr 1fr;
        gap: 8px;
        margin-top: 12px;
    }
    .req-deci-action-btn {
        min-height: 48px;
        border: 0;
        border-radius: 10px;
        font-size: 14px;
        font-weight: 900;
        text-transform: uppercase;
    }
    .req-deci-close {
        background: #ead6d6;
        color: #7e2014;
    }
    .req-deci-submit {
        background: linear-gradient(135deg, #4f7e1e 0%, #7fbb33 100%);
        color: #fff;
    }
    .req-deci-shell .modal-dialog {
        width: 360px;
        margin: 30px auto;
    }
    @media (max-width: 991px) {
        .req-deci-shell .modal-dialog {
            width: calc(100vw - 24px) !important;
            max-width: 420px;
            margin: 18px auto;
        }
    }
</style>

<div class="req-deci-shell">
    <input type="hidden" class="form-control" id="item_id" value="<?php echo $item_id;?>" >
    <div class="modal-content">
        <div class="req-deci-body">
            <div class="req-deci-top">
                <div class="req-deci-top-row">
                    <div class="req-deci-top-main">
                        <h4>Choose Quantity</h4>
                        <p><?php echo $item_name; ?></p>
                    </div>
                    <div class="req-deci-badge"><small>Entry</small>Qty</div>
                </div>
            </div>

            <?php if ($port_status) { ?>
                <div class="req-deci-hint">
                    This item has portions. Request using the portion size <?php echo $portions; ?>.
                </div>
            <?php } ?>

            <div id="results"></div>
            <input type="text" name="req_qty" id="req_qty" class="req-deci-display" autofocus />

            <div class="req-deci-pad">
                <?php
                $buttons = array('7','8','9','4','5','6','1','2','3');
                foreach ($buttons as $btn) {
                    echo '<button type="button" class="req-deci-key req-num" data-value="'.$btn.'">'.$btn.'</button>';
                }
                ?>
                <button type="button" class="req-deci-key req-deci-key-clear" id="req_backspace">&larr;</button>
                <button type="button" class="req-deci-key req-num" data-value="0">0</button>
                <button type="button" class="req-deci-key req-num" data-value=".">.</button>
            </div>

            <div class="req-deci-actions">
                <button type="button" data-dismiss="modal" class="req-deci-action-btn req-deci-close">Close</button>
                <button type="button" onclick="req_action()" name="place_order" id="pay" class="req-deci-action-btn req-deci-submit">Submit</button>
            </div>
        </div>
    </div>
</div>
<script type="text/javascript">
  $(document).ready(function () {
    $('.req-num').click(function () {
      var qty = $('#req_qty');
      qty.val(qty.val() + $(this).data('value'));
    });

    $('#req_backspace').click(function () {
      var qty = $('#req_qty');
      qty.val(qty.val().slice(0, -1));
    });

    window.getFinalQty = function () {
      return $('#req_qty').val();
    };
  });
 
    </script>
