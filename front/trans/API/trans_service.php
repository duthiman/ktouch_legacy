<?php

if (!function_exists('trans_service_modal_error_html')) {
    function trans_service_modal_error_html($message)
    {
        return '<div class="modal-content"><div class="modal-body"><div class="alert alert-danger" style="margin-bottom:0;">'
            . trans_escape($message)
            . '</div></div></div>';
    }
}

if (!function_exists('trans_service_bootstrap')) {
    function trans_service_bootstrap($connection, $user_id)
    {
        $user_id = (int)$user_id;
        if ($user_id < 1) {
            return ['ok' => false, 'message' => 'Session expired.', 'status' => 401];
        }

        $context = trans_repo_context($connection, $user_id);
        return [
            'ok' => true,
            'message' => 'Transfer page loaded.',
            'status' => 200,
            'context' => $context,
            'categories' => trans_repo_category_rows($connection, (int)($context['department_id'] ?? 0)),
            'transfer_items' => trans_repo_transfer_items($connection, (int)($context['trans_id'] ?? 0)),
        ];
    }
}

if (!function_exists('trans_service_counter_picker_modal')) {
    function trans_service_counter_picker_modal($connection, $user_id)
    {
        $context = trans_repo_context($connection, $user_id);
        $counter_from = (int)($context['counter_id'] ?? 0);
        $res = mysqli_query($connection, "SELECT cntr_id, name FROM kp_counters WHERE del_status=2 AND cntr_id!='$counter_from' ORDER BY name ASC");

        ob_start();
        ?>
<div class="modal-content" style="border-radius:16px; overflow:hidden;">
  <div class="modal-header" style="background: linear-gradient(135deg, #7e2014 0%, #b94828 52%, #e7a33a 100%); color:#fff8ea;">
    <button type="button" class="close" data-dismiss="modal" aria-label="Close"><span aria-hidden="true">&times;</span></button>
    <h4 class="modal-title" style="font-weight: bold;">SELECT COUNTER TO TRANSFER STOCK FROM <?php echo trans_escape(get_counter($counter_from)); ?></h4>
  </div>
  <div class="modal-body" style="background:#fffdf8;">
    <div class="container-fluid">
      <div class="row" style="display:flex; flex-wrap:wrap; justify-content:center;">
        <?php while ($res && $row = mysqli_fetch_assoc($res)) { ?>
        <button type="button" class="btn btn-success trans-counter-choice" data-counter-id="<?php echo (int)($row['cntr_id'] ?? 0); ?>" style="font-family:'comic sans ms'; font-size:16px; font-weight:bold; color:#fff !important; text-transform:uppercase; background:linear-gradient(135deg, #22515e 0%, #3e8f9e 100%); height:120px; width:120px; border-radius:100%; border:4px solid #61b846; display:flex; align-items:center; justify-content:center; text-align:center; margin:10px;">
          <?php echo trans_escape((string)($row['name'] ?? '')); ?>
        </button>
        <?php } ?>
      </div>
    </div>
  </div>
  <div class="modal-footer">
    <button type="button" class="btn" style="color:white; font-size:18px; font-weight:bold; padding:8px 14px; background-color:#02550E; border:4px solid #61b846; border-radius:10px;" data-dismiss="modal">CLOSE</button>
  </div>
</div>
<script>
$(document).off('click.transCounterChoice').on('click.transCounterChoice', '.trans-counter-choice', function() {
  var counterId = $(this).data('counterId');
  $.ajax({
    url: '../trans/API/trans_api.php',
    type: 'POST',
    data: { action: 'init_transfer', counter: counterId },
    dataType: 'json'
  }).done(function(res) {
    if (res && res.ok && res.data && res.data.redirect) {
      window.location.href = res.data.redirect;
      return;
    }
    alert((res && res.message) ? res.message : 'Could not start transfer.');
  }).fail(function() {
    alert('Could not start transfer.');
  });
});
</script>
        <?php
        return ['ok' => true, 'status' => 200, 'html' => ob_get_clean()];
    }
}

if (!function_exists('trans_service_init_transfer')) {
    function trans_service_init_transfer($connection, $user_id, $counter_to)
    {
        $user_id = (int)$user_id;
        $counter_to = (int)$counter_to;
        if ($user_id < 1) {
            return ['ok' => false, 'message' => 'Session expired.', 'status' => 401];
        }
        if ($counter_to < 1) {
            return ['ok' => false, 'message' => 'Choose the destination counter.', 'status' => 400];
        }

        $counter_from = (int)get_column('kp_users', 'counter', "user_id='$user_id'");
        $check_order = mysqli_query($connection, "SELECT trans_id FROM kp_counter_trans_status WHERE user_id='$user_id'");
        if ($check_order && mysqli_num_rows($check_order) > 0) {
            $on = mysqli_fetch_assoc($check_order);
            $new_trans = (int)($on['trans_id'] ?? 0);
            $_SESSION['trans'] = $new_trans;
        } else {
            $last_trans = (int)get_column('kp_counter_trans_id', 'trans_id', '1');
            $new_trans = $last_trans + 1;
            mysqli_query($connection, "INSERT INTO kp_counter_trans_status(trans_id,cntr_from,cntr_to,user_id) VALUES('$new_trans','$counter_from','$counter_to','$user_id')");
            mysqli_query($connection, "UPDATE kp_counter_trans_id SET trans_id='$new_trans'");
            $_SESSION['trans'] = $new_trans;
        }

        trans_log_action('STARTED|-|-|STOCK TRANSFER NO|' . (int)$_SESSION['trans'] . 'FOR' . get_counter($counter_from) . ' COUNTER ', '../logs');

        return [
            'ok' => true,
            'message' => 'Transfer started.',
            'status' => 200,
            'redirect' => 'index.php',
            'trans_id' => (int)$_SESSION['trans'],
        ];
    }
}

if (!function_exists('trans_service_subcategories_html')) {
    function trans_service_subcategories_html($connection, $cat_id)
    {
        $rows = trans_repo_subcategory_rows($connection, $cat_id);
        ob_start();
        if (!empty($rows)) {
            echo '<div class="navbar-header" style="margin-left: 100px; margin-top: 5px;">';
            foreach ($rows as $row) {
                echo '<button type="button" class="trans-sub-btn" data-sub-id="' . (int)$row['sub_id'] . '" style="font-family:\'comic sans ms\'; font-weight:bold; color:#FFFFFF !important; font-size:14px; word-wrap:break-word; width:auto; white-space:normal; height:35px; background:#000000; border:3px solid #B10421; padding-left:5px; padding-right:5px; margin:5px; border-radius:10px 0 10px 0;">' . trans_escape($row['name']) . '</button>';
            }
            echo '</div>';
        }
        return ['ok' => true, 'html' => ob_get_clean(), 'status' => 200];
    }
}

if (!function_exists('trans_service_items_html')) {
    function trans_service_items_html($rows)
    {
        ob_start();
        foreach ($rows as $row) {
            echo '<button data-toggle="modal" data-target="#modal" data-id="' . (int)$row['item_id'] . '" class="btn btn-inverse m-r-5 m-b-5" id="getItem" style="font-weight:bold; color:#FFFFFF !important; font-size:13px; word-wrap:break-word; width:70px; white-space:normal; height:70px; background:#6F9F07; border:2px solid #6F9F07; padding-left:0; padding-right:0; margin:5px; border-radius:10px;">' . trans_escape($row['name']) . '</button>';
        }
        return ob_get_clean();
    }
}

if (!function_exists('trans_service_items_by_cat')) {
    function trans_service_items_by_cat($connection, $cat_id)
    {
        return ['ok' => true, 'status' => 200, 'html' => trans_service_items_html(trans_repo_item_rows_by_cat($connection, $cat_id))];
    }
}

if (!function_exists('trans_service_items_by_sub')) {
    function trans_service_items_by_sub($connection, $sub_id)
    {
        return ['ok' => true, 'status' => 200, 'html' => trans_service_items_html(trans_repo_item_rows_by_sub($connection, $sub_id))];
    }
}

if (!function_exists('trans_service_list_html')) {
    function trans_service_list_html($connection, $trans_id)
    {
        $items = trans_repo_transfer_items($connection, $trans_id);
        ob_start();
        ?>
<table class="table table-bordered" id="TheSo">
  <thead>
    <tr><th>Item</th><th>Qty</th><div id="hidetd"><th>Action</th></div></tr>
  </thead>
  <tbody>
    <?php foreach ($items as $row) { ?>
    <tr class="trans-live-item">
      <td><?php echo trans_escape($row['item_name']); ?></td>
      <td><?php echo (float)$row['qty']; ?></td>
      <div id="hidetd"><td><a style="margin-bottom:8px; margin-right:5px; font-size:12px;" class="btn btn-danger btn-xs" onclick="del_item(<?php echo (int)$row['item_id']; ?>)">DEL</a></td></div>
    </tr>
    <?php } ?>
  </tbody>
</table>
        <?php
        return ['ok' => true, 'status' => 200, 'html' => ob_get_clean(), 'count' => count($items)];
    }
}

if (!function_exists('trans_service_item_modal')) {
    function trans_service_item_modal($connection, $user_id, $item_id)
    {
        $counter = (int)get_column('kp_users', 'counter', "user_id='$user_id'");
        $qty_column = 'cntr' . $counter . '_qty';
        $stock_row = mysqli_fetch_assoc(mysqli_query($connection, "SELECT $qty_column AS stock FROM kp_items WHERE item_id='" . (int)$item_id . "'")) ?: [];
        $stock = (float)($stock_row['stock'] ?? 0);

        ob_start();
        ?>
<div class="modal-content" style="width:320px;">
 <div class="modal-header" style="padding:10px; margin-bottom:0;">
  <h4 style="float:left;">ENTER QUANTITY TO TRANSFER</h4>
 </div>
 <div class="modal-body">
  <div id="results"><h4 style="color: blue;">CURRENT STOCK: <?php echo $stock; ?></h4></div>
  <div class="row" style="margin-top:-15px; margin-bottom:20px;">
    <input type="hidden" class="form-control" id="item_id" value="<?php echo (int)$item_id; ?>">
    <input type="text" name="trans_qty" id="trans_qty" class="form-control" style="width:290px; margin:15px 15px 0 18px;" autofocus><br>
  </div>
  <div id="numpad" style="display:grid; grid-template-columns:repeat(3, 80px); gap:10px; justify-content:center;">
    <?php foreach ([7,8,9,4,5,6,1,2,3,'CLEAR',0,'.'] as $key) { ?>
      <?php if ($key === 'CLEAR') { ?>
      <button type="button" id="clear" class="btn" style="background-color:#f06c26 !important; padding:0; font-weight:bold; color:black; height:70px; width:70px; border:2px solid #62b647; border-radius:10px;">CLEAR</button>
      <?php } else { ?>
      <div class="num" style="height:70px; width:70px; border-radius:10%; box-shadow:2px 1000px 1px #fff inset; text-align:center; border:solid 3px transparent; background-image:linear-gradient(rgba(255,255,255,0), rgba(255,255,255,0)), linear-gradient(101deg, #f36c26, #61b846);">
        <h2 class="number" style="font-weight:bold; margin-top:25%;"><?php echo trans_escape((string)$key); ?></h2>
        <div class="txt" style="display:none"><?php echo trans_escape((string)$key); ?></div>
      </div>
      <?php } ?>
    <?php } ?>
  </div>
 </div>
 <div class="modal-footer" style="padding:10px;">
   <button type="button" data-dismiss="modal" class="btn btn-danger">CLOSE</button>
   <button type="submit" onclick="trans_action()" name="place_order" id="pay" class="btn btn-warning">SUBMIT</button>
 </div>
</div>
<script>
$(document).off('click.transNum').on('click.transNum', '.num', function () {
  var text = $.trim($(this).find('.txt').text());
  var qty = $('#trans_qty');
  qty.val(qty.val() + text);
});
$(document).off('click.transClear').on('click.transClear', '#clear', function () {
  $('#trans_qty').val('');
});
</script>
        <?php
        return ['ok' => true, 'status' => 200, 'html' => ob_get_clean()];
    }
}

if (!function_exists('trans_service_submit_modal')) {
    function trans_service_submit_modal($connection, $user_id)
    {
        $user_id = (int)$user_id;
        if ($user_id < 1) {
            return ['ok' => false, 'status' => 401, 'html' => trans_service_modal_error_html('Session expired.')];
        }

        $trans_id = isset($_SESSION['trans']) ? (int)$_SESSION['trans'] : 0;
        $counter = (int)get_column('kp_users', 'counter', "user_id='$user_id'");
        $print_mode = (int)get_print_mode();
        $items = trans_repo_transfer_items($connection, $trans_id);
        $item_count = count($items);
        $counters = [];
        if ($print_mode !== 1) {
            $counter_res = mysqli_query($connection, "SELECT cntr_id, name, dpt FROM kp_counters WHERE del_status=2 ORDER BY dpt ASC, cntr_id ASC");
            while ($counter_res && $row = mysqli_fetch_assoc($counter_res)) {
                $counters[] = $row;
            }
        }

        ob_start();
        ?>
<style type="text/css">
  .trans-confirm-shell .modal-content { border-radius: 16px; overflow: hidden; }
  .trans-confirm-shell .modal-header { padding: 14px 16px; background: linear-gradient(135deg, #7e2014 0%, #b94828 52%, #e7a33a 100%); color: #fff8ea; border-bottom: 0; }
  .trans-confirm-shell .close { color: #fff; opacity: 1; }
  .trans-confirm-shell .modal-title { font-weight: 900; text-transform: uppercase; }
  .trans-confirm-body { background: linear-gradient(180deg, #f6f0d7 0%, #efe3bf 100%); padding: 14px; }
  .trans-confirm-warning { padding: 12px 14px; border-radius: 12px; background: #fff7dc; border: 1px solid rgba(126, 58, 32, 0.12); color: #7e3a20; font-weight: 800; margin-bottom: 12px; }
  .trans-confirm-summary { display: flex; justify-content: space-between; align-items: center; gap: 10px; padding: 12px 14px; border-radius: 12px; background: #fff; box-shadow: 0 10px 20px rgba(86, 61, 24, 0.08); margin-bottom: 12px; }
  .trans-confirm-summary strong, .trans-confirm-summary span { display: block; }
  .trans-confirm-summary strong { font-size: 12px; letter-spacing: 0.08em; text-transform: uppercase; color: #7e3a20; }
  .trans-confirm-summary span { font-size: 24px; font-weight: 900; color: #b94828; line-height: 1; margin-top: 4px; }
  .trans-confirm-list { max-height: 42vh; overflow-y: auto; padding-right: 4px; }
  .trans-confirm-empty, .trans-confirm-item { background: #fff; border-radius: 12px; box-shadow: 0 10px 20px rgba(86, 61, 24, 0.08); margin-bottom: 10px; padding: 12px 14px; }
  .trans-confirm-item-top { display: flex; justify-content: space-between; align-items: center; gap: 10px; }
  .trans-confirm-item-no { min-width: 34px; height: 34px; border-radius: 10px; background: #f9e7cd; color: #7e3a20; font-size: 13px; font-weight: 900; display: flex; align-items: center; justify-content: center; }
  .trans-confirm-item-name { flex: 1 1 auto; min-width: 0; font-size: 14px; font-weight: 900; color: #382819; line-height: 1.3; }
  .trans-confirm-item-qty { min-width: 82px; text-align: right; }
  .trans-confirm-item-qty strong { display: block; font-size: 10px; letter-spacing: 0.08em; text-transform: uppercase; color: #8a6a3a; }
  .trans-confirm-item-qty span { display: block; margin-top: 4px; font-size: 20px; font-weight: 900; color: #4f7e1e; line-height: 1; }
  .trans-confirm-pick-copy { margin: 14px 0 10px; padding: 12px 14px; border-radius: 12px; background: #fff; color: #7e3a20; box-shadow: 0 10px 20px rgba(86, 61, 24, 0.08); font-weight: 800; }
  .trans-print-grid { display: grid; grid-template-columns: repeat(auto-fit, minmax(140px, 1fr)); gap: 10px; margin-top: 10px; }
  .trans-print-btn { width: 100%; min-height: 88px; border: 2px solid #d8c3a2; border-radius: 16px; background: #fff; color: #6b4218; text-align: center; padding: 12px 10px; box-shadow: 0 10px 18px rgba(79, 58, 24, 0.08); font-weight: 900; }
  .trans-print-btn.active { border-color: #2f7a31; background: linear-gradient(135deg, #eef8e9 0%, #fff2e6 100%); color: #235f25; }
  .trans-print-btn strong { display: block; font-size: 26px; line-height: 1; }
  .trans-print-btn span { display: block; margin-top: 8px; font-size: 12px; text-transform: uppercase; }
  .trans-confirm-shell .modal-footer { padding: 12px 14px 14px; border-top: 0; background: #fffdf8; display: grid; grid-template-columns: 1fr 1fr; gap: 8px; }
  .trans-confirm-btn { min-height: 48px; border: 0; border-radius: 10px; font-size: 14px; font-weight: 900; text-transform: uppercase; }
  .trans-confirm-close { background: #ead6d6; color: #7e2014; }
  .trans-confirm-submit { background: linear-gradient(135deg, #4f7e1e 0%, #7fbb33 100%); color: #fff; }
</style>
<div class="trans-confirm-shell">
  <div class="modal-content">
    <div class="modal-header">
      <button type="button" class="close" data-dismiss="modal" aria-label="Close"><span aria-hidden="true">&times;</span></button>
      <h4 class="modal-title" style="font-weight: bold;"><?php echo trans_escape(get_counter($counter)); ?> TRANSFER CONFIRMATION</h4>
    </div>
    <div class="modal-body trans-confirm-body">
      <div id="trans-print-feedback"></div>
      <div class="trans-confirm-warning">Make sure you confirm the transferred items below before submitting.</div>
      <div class="trans-confirm-summary">
        <div><strong>Counter</strong><span><?php echo trans_escape(get_counter($counter)); ?></span></div>
        <div style="text-align:right;"><strong>Items Added</strong><span><?php echo $item_count; ?></span></div>
      </div>
      <div class="trans-confirm-list">
        <?php if ($item_count < 1) { ?>
          <div class="trans-confirm-empty">No items have been added to this transfer yet.</div>
        <?php } else { foreach ($items as $index => $row) { ?>
          <div class="trans-confirm-item">
            <div class="trans-confirm-item-top">
              <div class="trans-confirm-item-no"><?php echo (int)($index + 1); ?></div>
              <div class="trans-confirm-item-name"><?php echo trans_escape($row['item_name']); ?></div>
              <div class="trans-confirm-item-qty"><strong>Qty</strong><span><?php echo (float)$row['qty']; ?></span></div>
            </div>
          </div>
        <?php } } ?>
      </div>
      <?php if ($print_mode !== 1 && !empty($counters)) { ?>
      <div class="trans-confirm-pick-copy">Choose where to collect the printed transfer slip.</div>
      <div class="trans-print-grid">
        <?php foreach ($counters as $printer_counter) { $active = ((int)$printer_counter['cntr_id'] === $counter) ? ' active' : ''; ?>
          <button type="button" class="trans-print-btn<?php echo $active; ?>" data-trans-print-counter="<?php echo (int)$printer_counter['cntr_id']; ?>">
            <strong><?php echo (int)$printer_counter['cntr_id']; ?></strong>
            <span><?php echo trans_escape($printer_counter['name']); ?><?php echo !empty($printer_counter['dpt']) ? ' - DPT ' . (int)$printer_counter['dpt'] : ''; ?></span>
          </button>
        <?php } ?>
      </div>
      <?php } ?>
    </div>
    <?php if ($item_count > 0) { ?>
      <div class="modal-footer">
        <button type="button" class="trans-confirm-btn trans-confirm-close" data-dismiss="modal">Close</button>
        <button type="button" class="trans-confirm-btn trans-confirm-submit" id="trans-submit-request" data-print-mode="<?php echo $print_mode; ?>">Transfer</button>
      </div>
    <?php } else { ?>
      <div class="modal-footer" style="grid-template-columns:1fr;">
        <button type="button" class="trans-confirm-btn trans-confirm-close" data-dismiss="modal">Close</button>
      </div>
    <?php } ?>
  </div>
</div>
<script>
  (function() {
    var selectedTransCounter = <?php echo (int)$counter; ?>;
    $(document).off('click.transPrintBtn').on('click.transPrintBtn', '.trans-print-btn', function() {
      $('.trans-print-btn').removeClass('active');
      $(this).addClass('active');
      selectedTransCounter = parseInt($(this).data('transPrintCounter'), 10) || 0;
    });
    $(document).off('click.transSubmitRequest').on('click.transSubmitRequest', '#trans-submit-request', function() {
      $('#trans-submit-request').prop('disabled', true);
      $('#trans-print-feedback').html('<div class="alert alert-info" style="margin-bottom:12px;font-weight:700;">Sending transfer...</div>');
      $.ajax({
        url: 'API/trans_api.php',
        type: 'POST',
        dataType: 'json',
        data: { action: 'submit_transfer', cntr_id: selectedTransCounter }
      })
      .done(function(res) {
        if (res && res.ok && res.data && res.data.redirect) {
          window.location = res.data.redirect;
          return;
        }
        $('#trans-print-feedback').html('<div class="alert alert-danger" style="margin-bottom:12px;font-weight:700;">' + ((res && res.message) ? res.message : 'Could not submit transfer.') + '</div>');
        $('#trans-submit-request').prop('disabled', false);
      })
      .fail(function() {
        $('#trans-print-feedback').html('<div class="alert alert-danger" style="margin-bottom:12px;font-weight:700;">Could not submit transfer.</div>');
        $('#trans-submit-request').prop('disabled', false);
      });
    });
  })();
</script>
        <?php
        return ['ok' => true, 'status' => 200, 'html' => ob_get_clean()];
    }
}

if (!function_exists('trans_service_add_item')) {
    function trans_service_add_item($connection, $user_id, $item_id, $qty)
    {
        $trans_id = isset($_SESSION['trans']) ? (int)$_SESSION['trans'] : 0;
        $shift = (string)get_column('kp_shift', 'shift', '1');
        $counter = (int)get_column('kp_users', 'counter', "user_id='$user_id'");
        $qty_column = 'cntr' . $counter . '_qty';

        if ($trans_id < 1) {
            return ['ok' => false, 'message' => 'Start a transfer first.', 'status' => 400, 'html' => '<div class="alert alert-danger"><h5><b>Start a transfer first.</b></h5></div>'];
        }
        if ($item_id < 1 || $qty <= 0) {
            return ['ok' => false, 'message' => 'Please fill all the required fields.', 'status' => 400, 'html' => '<div class="alert alert-danger"><h5><b>Please fill all the required fields.!!!</b></h5></div>'];
        }

        $stock_res = mysqli_query($connection, "SELECT $qty_column AS stock, dpt FROM kp_items WHERE item_id='$item_id'");
        $stock_row = mysqli_fetch_assoc($stock_res) ?: [];
        $stock = (float)($stock_row['stock'] ?? 0);
        $shop = (int)($stock_row['dpt'] ?? 0);
        if ($stock < $qty) {
            return ['ok' => false, 'message' => 'You do not have enough stock.', 'status' => 400, 'html' => '<div class="alert alert-danger"><h5><b>YOU DOT HAVE ENOUGH STOCK.!!!</b></h5></div>'];
        }

        $check = mysqli_query($connection, "SELECT item_id FROM kp_counter_trans_items WHERE item_id='$item_id' AND trans_id='$trans_id'");
        if ($check && mysqli_num_rows($check) > 0) {
            return ['ok' => false, 'message' => 'Item already in the list.', 'status' => 400, 'html' => '<div class="alert alert-danger"><h5><b>Item already in the list!!!</b></h5></div>'];
        }

        mysqli_query($connection, "INSERT INTO kp_counter_trans_items(trans_id,item_id,qty,counter) VALUES('$trans_id','$item_id','$qty','$counter')");
        mysqli_query($connection, "INSERT INTO kp_stock_track(item_id,qty,user_id,trans_id,status,stock_counter,shift,dpt) VALUES('$item_id','$qty','$user_id','$trans_id','OUT','$counter','$shift','$shop')");
        mysqli_query($connection, "UPDATE kp_items SET $qty_column=$qty_column-'$qty' WHERE item_id='$item_id'");
        trans_log_action('ADDED|' . $qty . '|' . get_item($item_id) . '|TO COUNTER TRANSFER NO|' . $trans_id, '../logs');

        return ['ok' => true, 'message' => 'Item added.', 'status' => 200, 'html' => ''];
    }
}

if (!function_exists('trans_service_delete_item')) {
    function trans_service_delete_item($connection, $user_id, $item_id)
    {
        $trans_id = isset($_SESSION['trans']) ? (int)$_SESSION['trans'] : 0;
        $counter = (int)user_counter($user_id);
        $qty_column = 'cntr' . $counter . '_qty';
        $qty_row = mysqli_fetch_assoc(mysqli_query($connection, "SELECT qty FROM kp_counter_trans_items WHERE item_id='$item_id' AND trans_id='$trans_id'")) ?: [];
        $number = (float)($qty_row['qty'] ?? 0);

        if ($number > 0) {
            mysqli_query($connection, "UPDATE kp_items SET $qty_column=$qty_column+'$number' WHERE item_id='$item_id'");
        }
        mysqli_query($connection, "DELETE FROM kp_counter_trans_items WHERE item_id='$item_id' AND trans_id='$trans_id'");
        mysqli_query($connection, "DELETE FROM kp_stock_track WHERE item_id='$item_id' AND trans_id='$trans_id'");
        trans_log_action('DELETED|-|' . get_item($item_id) . '|FROM STOCK TRANSFER NO|' . $trans_id, '../logs');

        return ['ok' => true, 'message' => 'Item removed.', 'status' => 200];
    }
}

if (!function_exists('trans_service_cancel_modal')) {
    function trans_service_cancel_modal()
    {
        $html = '<div class="modal-content" style="border-radius:16px; overflow:hidden;">';
        $html .= '<div class="modal-header" style="background:linear-gradient(135deg, #7e2014 0%, #b94828 52%, #e7a33a 100%); color:#fff8ea;">';
        $html .= '<button type="button" class="close" data-dismiss="modal" aria-label="Close"><span aria-hidden="true">&times;</span></button>';
        $html .= '<h4 class="modal-title" style="font-weight:900;">Cancel Transfer Draft</h4>';
        $html .= '</div>';
        $html .= '<div class="modal-body" style="background:#fffdf8; padding:16px;">';
        $html .= '<div class="alert alert-danger" style="margin-bottom:12px; font-weight:700;">This action will remove the current transfer draft and all its items.</div>';
        $html .= '<p style="margin:0; color:#6f4b2e; font-weight:700;">Do you want to continue with the cancel?</p>';
        $html .= '<div id="trans-cancel-feedback" style="margin-top:12px;"></div>';
        $html .= '</div>';
        $html .= '<div class="modal-footer" style="background:#fffdf8;">';
        $html .= '<button type="button" class="btn btn-default" data-dismiss="modal">Close</button>';
        $html .= '<button type="button" class="btn btn-danger" id="trans-cancel-confirm">Continue Cancel</button>';
        $html .= '</div></div>';
        $html .= '<script>$(document).off("click.transCancelConfirm").on("click.transCancelConfirm", "#trans-cancel-confirm", function(){ $("#trans-cancel-feedback").html(\'<div class="alert alert-warning" style="margin-bottom:0;font-weight:700;">Cancelling transfer draft...</div>\'); $.ajax({ url: "API/trans_api.php", type: "POST", data: { action: "cancel_transfer" }, dataType: "json" }).done(function(res){ if(res && res.ok && res.data && res.data.redirect){ $("#trans-cancel-feedback").html(\'<div class="alert alert-success" style="margin-bottom:0;font-weight:700;">Transfer draft cancelled.</div>\'); window.location.href = res.data.redirect; return; } $("#trans-cancel-feedback").html(\'<div class="alert alert-danger" style="margin-bottom:0;font-weight:700;">\' + ((res && res.message) ? res.message : "Could not cancel transfer.") + \'</div>\'); }).fail(function(){ $("#trans-cancel-feedback").html(\'<div class="alert alert-danger" style="margin-bottom:0;font-weight:700;">Could not cancel transfer.</div>\'); }); });</script>';
        return ['ok' => true, 'status' => 200, 'html' => $html];
    }
}

if (!function_exists('trans_service_cancel_transfer')) {
    function trans_service_cancel_transfer($connection)
    {
        $trans_id = isset($_SESSION['trans']) ? (int)$_SESSION['trans'] : 0;
        if ($trans_id < 1) {
            return ['ok' => false, 'message' => 'No active transfer.', 'status' => 400];
        }
        mysqli_query($connection, "DELETE FROM kp_counter_trans_items WHERE trans_id='$trans_id'");
        mysqli_query($connection, "DELETE FROM kp_counter_trans_status WHERE trans_id='$trans_id'");
        mysqli_query($connection, "DELETE FROM kp_stock_track WHERE trans_id='$trans_id'");
        unset($_SESSION['trans']);
        trans_log_action('CANCELLED|-|-|COUNTER TRANSFER NO|' . $trans_id, '../logs');
        return ['ok' => true, 'message' => 'Transfer cancelled.', 'status' => 200, 'redirect' => '../stock/store_dash.php?msg=2'];
    }
}

if (!function_exists('trans_service_submit_transfer')) {
    function trans_service_submit_transfer($connection, $user_id, $cntr_id)
    {
        $user_id = (int)$user_id;
        $trans_id = isset($_SESSION['trans']) ? (int)$_SESSION['trans'] : 0;
        $cntr_id = (int)$cntr_id;
        $shift = (string)get_column('kp_shift', 'shift', '1');
        $print_mode = (int)get_print_mode();

        if ($user_id < 1 || $trans_id < 1) {
            return ['ok' => false, 'message' => 'Transfer session is missing.', 'status' => 400];
        }

        $check_order = mysqli_query($connection, "SELECT * FROM kp_counter_trans_status WHERE trans_id='$trans_id'");
        if (!$check_order || mysqli_num_rows($check_order) < 1) {
            return ['ok' => false, 'message' => 'Transfer session is invalid.', 'status' => 400];
        }

        $c = mysqli_fetch_assoc($check_order);
        $cntr_from = (int)($c['cntr_from'] ?? 0);
        $cntr_to = (int)($c['cntr_to'] ?? 0);

        mysqli_query($connection, "INSERT INTO kp_counter_trans(trans_id,cntr_from,cntr_to,user_from,shift) VALUES('$trans_id','$cntr_from','$cntr_to','$user_id','$shift')");
        mysqli_query($connection, "DELETE FROM kp_counter_trans_status WHERE trans_id='$trans_id'");

        trans_log_action('PLACED|-|-| STOCK TRANSFER NO|' . $trans_id, '../logs');
        unset($_SESSION['trans']);

        if ($print_mode === 1) {
            return ['ok' => true, 'message' => 'Transfer placed.', 'status' => 200, 'redirect' => '../chaba/render_trans.php?trans_id=' . $trans_id];
        }

        if ($cntr_id > 0 && function_exists('kp_print_enqueue')) {
            $queue_id = kp_print_enqueue($connection, [
                'sale_id' => 0,
                'ref_id' => $trans_id,
                'print_type' => 'trans',
                'label' => 'TRANSFER',
                'cntr_id' => $cntr_id,
                'route_path' => 'render_trans.php',
                'route_query' => 'trans_id=' . $trans_id,
                'requested_by' => $user_id,
            ]);
            if ($queue_id > 0) {
                return ['ok' => true, 'message' => 'Transfer queued for print.', 'status' => 200, 'redirect' => '../dash/dash.php'];
            }
        }

        return ['ok' => true, 'message' => 'Transfer placed.', 'status' => 200, 'redirect' => '../chaba/receipt_counter.php?type=trans&id=' . $trans_id . '&return_to=../trans/index.php'];
    }
}

if (!function_exists('trans_service_receive_page')) {
    function trans_service_receive_page($connection, $user_id, $trans_id)
    {
        $user_id = (int)$user_id;
        $trans_id = (int)$trans_id;
        if ($user_id < 1) {
            return ['ok' => false, 'message' => 'Session expired.', 'status' => 401];
        }
        if ($trans_id < 1) {
            return ['ok' => false, 'message' => 'Invalid transfer.', 'status' => 400];
        }

        $rows = trans_repo_transfer_receive_items($connection, $trans_id);
        if (empty($rows)) {
            mysqli_query($connection, "UPDATE kp_counter_trans SET status=1,user_to='$user_id',accept_datime=CURRENT_TIMESTAMP WHERE trans_id='$trans_id'");
            return [
                'ok' => true,
                'message' => 'Transfer completed.',
                'status' => 200,
                'redirect' => '../rec/rec_pending.php',
            ];
        }

        return [
            'ok' => true,
            'message' => 'Receive page loaded.',
            'status' => 200,
            'rows' => $rows,
        ];
    }
}

if (!function_exists('trans_service_accept_modal')) {
    function trans_service_accept_modal($connection, $trans_id, $item_id)
    {
        $trans_id = (int)$trans_id;
        $item_id = (int)$item_id;
        $order_details = mysqli_query($connection, "SELECT qty FROM kp_counter_trans_items WHERE trans_id='$trans_id' AND item_id='$item_id'");
        $od = mysqli_fetch_assoc($order_details) ?: [];
        $qty = (float)($od['qty'] ?? 0);

        ob_start();
        ?>
<div class="modal-content" style="width: 300px;">
  <div class="modal-header">
    <button type="button" class="close" data-dismiss="modal" aria-label="Close"><span aria-hidden="true">&times;</span></button>
    <h4 class="modal-title">ACCEPT <?php echo trans_escape($qty . ' ' . get_item($item_id)); ?></h4>
  </div>
  <div id="results"><p id="acc_res" style="color:red"></p></div>
  <div class="modal-body" style="padding:10px;">
    <div class="row" style="margin-top:-15px;">
      <input type="hidden" id="item_id" value="<?php echo (int)$item_id; ?>">
      <input type="hidden" id="trans_id" value="<?php echo (int)$trans_id; ?>">
      <input type="hidden" id="tqty" value="<?php echo $qty; ?>">
      <input type="text" id="qty" value="<?php echo $qty; ?>" class="form-control" style="width:260px; margin:15px 15px 0px 18px;" autofocus><br>
    </div>
    <div id="numpad" style="display:grid; grid-template-columns:repeat(3, 80px); gap:10px; justify-content:center;">
      <?php foreach ([7,8,9,4,5,6,1,2,3,'CLEAR',0,'.'] as $key) { ?>
        <?php if ($key === 'CLEAR') { ?>
        <button type="button" id="clear" class="btn" style="background-color:#f06c26 !important; padding:0; font-weight:bold; color:black; height:70px; width:70px; border:2px solid #62b647; border-radius:10px;">CLEAR</button>
        <?php } else { ?>
        <div class="num" style="height:70px; width:70px; border-radius:10%; box-shadow:2px 1000px 1px #fff inset; text-align:center; border:solid 3px transparent; background-image:linear-gradient(rgba(255,255,255,0), rgba(255,255,255,0)), linear-gradient(101deg, #f36c26, #61b846);">
          <h2 class="number" style="font-weight:bold; margin-top:25%;"><?php echo trans_escape((string)$key); ?></h2>
          <div class="txt" style="display:none"><?php echo trans_escape((string)$key); ?></div>
        </div>
        <?php } ?>
      <?php } ?>
    </div>
  </div>
  <div class="modal-footer">
    <button type="button" class="btn btn-default pull-left" data-dismiss="modal">Close</button>
    <button class="btn btn-danger button" id="trans-receive-submit">Receive</button>
  </div>
</div>
<script>
$(document).off('click.transReceiveSubmit').on('click.transReceiveSubmit', '#trans-receive-submit', function(){
  var id = $("#item_id").val();
  var transId = $("#trans_id").val();
  var qty = $("#qty").val();
  var tqty = $("#tqty").val();
  if (qty != tqty) {
    $("#acc_res").html('YOU HAVE TO RECEIVE WHAT WAS TRANSFERED');
    return;
  }
  $.ajax({
    url: 'API/trans_api.php',
    type: 'POST',
    data: { action: 'receive_item', item_id: id, trans_id: transId, qty: qty },
    dataType: 'json'
  }).done(function(res){
    if (res && res.ok) {
      window.location = 'trans_rec.php?trans=' + encodeURIComponent(transId);
      return;
    }
    $("#acc_res").html((res && res.message) ? res.message : 'Failed to receive item');
  }).fail(function(){
    $("#acc_res").html('Failed to receive item');
  });
});
$(document).off('click.transRecNum').on('click.transRecNum', '.num', function () {
  var text = $.trim($(this).find('.txt').text());
  var qty = $('#qty');
  qty.val(qty.val() + text);
});
$(document).off('click.transRecClear').on('click.transRecClear', '#clear', function () {
  $('#qty').val('');
});
</script>
        <?php
        return ['ok' => true, 'status' => 200, 'html' => ob_get_clean()];
    }
}

if (!function_exists('trans_service_receive_item')) {
    function trans_service_receive_item($connection, $user_id, $trans_id, $item_id, $qty)
    {
        $user_id = (int)$user_id;
        $trans_id = (int)$trans_id;
        $item_id = (int)$item_id;
        $qty = (float)$qty;
        $shift = (string)get_column('kp_shift', 'shift', '1');
        $counter = (int)user_counter($user_id);
        $qty_column = 'cntr' . $counter . '_qty';

        $check_stock = mysqli_query($connection, "SELECT dpt FROM kp_items WHERE item_id='$item_id'");
        $s = mysqli_fetch_assoc($check_stock) ?: [];
        $shop = (int)($s['dpt'] ?? 0);

        mysqli_query($connection, "UPDATE kp_counter_trans_items SET status=1 WHERE trans_id='$trans_id' AND item_id='$item_id'");
        mysqli_query($connection, "UPDATE kp_items SET $qty_column=$qty_column+'$qty' WHERE item_id='$item_id'");
        mysqli_query($connection, "INSERT INTO kp_stock_track(item_id,qty,user_id,trans_id,status,stock_counter,shift,dpt) VALUES('$item_id','$qty','$user_id','$trans_id','IN','$counter','$shift','$shop')");

        return ['ok' => true, 'message' => 'Item received.', 'status' => 200];
    }
}
