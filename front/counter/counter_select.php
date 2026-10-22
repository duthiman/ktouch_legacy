<?php
include('../../inc/connection.php');

$get_counters = mysqli_query($connection, "SELECT * FROM kp_counters WHERE del_status=2 AND take_status=1") or die("Error fetching the counters: " . mysqli_error($connection));
$counter_count = mysqli_num_rows($get_counters);
$single_counter = null;
if ($counter_count == 1) {
    $single_counter = mysqli_fetch_assoc($get_counters);
}

$sale_id = isset($_REQUEST['sale_id']) ? (int)$_REQUEST['sale_id'] : 0;
$init_status = $sale_id ? 2 : 1;
$init_api = "../kwedia/api/bill_api.php";
?>
<style type="text/css">
  .counter-select-shell .modal-content {
    width: 100%;
    max-width: 460px;
    margin: 0 auto;
    border-radius: 18px;
    overflow: hidden;
    border: 0;
    box-shadow: 0 20px 40px rgba(33, 19, 6, 0.24);
  }
  .counter-select-shell .modal-header {
    border-bottom: 0;
    padding: 14px 16px;
    background: linear-gradient(135deg, #7e2014 0%, #b94828 52%, #e7a33a 100%);
    color: #fff8ea;
  }
  .counter-select-shell .modal-title {
    margin: 0;
    font-size: 20px;
    font-weight: 900;
    text-transform: uppercase;
    line-height: 1.2;
  }
  .counter-select-shell .close {
    color: #fff;
    opacity: 1;
  }
  .counter-select-shell .modal-body {
    padding: 14px;
    background: linear-gradient(180deg, #fff9ea 0%, #f7edd2 100%);
  }
  .counter-select-note {
    margin: 0 0 10px;
    padding: 10px 12px;
    border-radius: 12px;
    background: #fff;
    color: #7e3a20;
    font-size: 12px;
    font-weight: 800;
    text-transform: uppercase;
    box-shadow: 0 8px 16px rgba(86, 61, 24, 0.08);
  }
  .counter-select-grid {
    display: grid;
    grid-template-columns: 1fr 1fr;
    gap: 10px;
  }
  .counter-select-btn {
    min-height: 96px;
    border-radius: 16px;
    background: linear-gradient(135deg, #02550E 0%, #2e8a2d 100%);
    color: #fff !important;
    text-decoration: none !important;
    display: flex;
    flex-direction: column;
    align-items: center;
    justify-content: center;
    text-align: center;
    padding: 12px 10px;
    box-shadow: 0 14px 24px rgba(25, 88, 18, 0.18);
    border: 3px solid #61b846;
  }
  .counter-select-btn strong,
  .counter-select-btn span {
    display: block;
  }
  .counter-select-btn strong {
    font-size: 24px;
    font-weight: 900;
    line-height: 1;
  }
  .counter-select-btn span {
    margin-top: 8px;
    font-size: 12px;
    font-weight: 900;
    letter-spacing: 0.05em;
    text-transform: uppercase;
    line-height: 1.2;
  }
  .counter-select-empty {
    padding: 16px;
    border-radius: 12px;
    background: #fff7dc;
    color: #8a6a3a;
    font-size: 13px;
    font-weight: 800;
    text-align: center;
  }
  .counter-select-shell .modal-footer {
    border-top: 0;
    background: #fffdf8;
    padding: 12px 14px 14px;
  }
  .counter-select-close {
    min-height: 44px;
    border: 0;
    border-radius: 12px;
    background: #ead6d6;
    color: #7e2014;
    font-size: 13px;
    font-weight: 900;
    text-transform: uppercase;
    padding: 0 18px;
  }
  @media (max-width: 420px) {
    .counter-select-btn {
      min-height: 84px;
    }
  }
</style>
<div class="counter-select-shell">
  <div class="modal-content" data-init-api="<?php echo $init_api; ?>" data-init-action="initialise" data-init-status="<?php echo $init_status; ?>" data-sale-id="<?php echo $sale_id; ?>">
    <div class="modal-header">
      <?php if (!$sale_id) { ?>
      <button type="button" class="close" data-dismiss="modal" aria-label="Close"><span aria-hidden="true">&times;</span></button>
      <?php } ?>
      <h3 class="modal-title"><?php echo $sale_id ? 'Select Counter For Bill No. '.$sale_id : 'Select Counter'; ?></h3>
    </div>
    <div class="modal-body">
      <p class="counter-select-note"><?php echo $sale_id ? 'Choose where this bill should continue.' : 'Choose a counter to continue.'; ?></p>
      <?php
      mysqli_data_seek($get_counters, 0);
      if ($counter_count < 1) {
        ?>
        <div class="counter-select-empty">No active counters are available right now.</div>
        <?php
      } else {
        ?>
        <div class="counter-select-grid">
          <?php while ($row = mysqli_fetch_assoc($get_counters)) { ?>
            <a href="#" class="counter-select-btn" data-counter="<?php echo (int)$row['cntr_id']; ?>" data-action="initialise">
              <strong><?php echo $row['cntr_id']; ?></strong>
              <span><?php echo $row['name']; ?></span>
            </a>
          <?php } ?>
        </div>
        <?php
      }
      ?>
    </div>
    <div class="modal-footer">
      <button type="button" class="counter-select-close" data-dismiss="modal">Close</button>
    </div>
  </div>
</div>
<?php if ($counter_count == 1 && $single_counter): ?>
<script>
  $(function () {
    $('.counter-select-btn[data-counter="<?php echo (int)$single_counter['cntr_id']; ?>"]').trigger('click');
  });
</script>
<?php endif; ?>
<script>
  (function() {
    $(document).off('click.counterSelectInit', '.counter-select-shell .counter-select-btn');
    $(document).on('click.counterSelectInit', '.counter-select-shell .counter-select-btn', function(e) {
      e.preventDefault();

      var $button = $(this);
      var $modalContent = $button.closest('.modal-content');
      var initApi = $modalContent.data('initApi');
      var initAction = $button.data('action') || $modalContent.data('initAction') || 'initialise';
      var initStatus = parseInt($modalContent.data('initStatus'), 10) || 0;
      var saleId = parseInt($modalContent.data('saleId'), 10) || 0;
      var counter = parseInt($button.data('counter'), 10) || 0;

      if (!initApi || !initAction || !counter || !initStatus) {
        return;
      }

      $('.counter-select-shell .counter-select-btn').addClass('is-locked').css('pointer-events', 'none');

      function unlockButtons() {
        $('.counter-select-shell .counter-select-btn').removeClass('is-locked').css('pointer-events', '');
      }
      function esc(value) { return $('<div>').text(value == null ? '' : String(value)).html(); }
      function showDraft(xhr) {
        var response = (xhr && xhr.responseJSON) || xhr || {};
        var data = response.data || response;
        var items = Array.isArray(data.items) ? data.items : [];
        var counterName = data.counter_name || ('Counter ' + (data.counter || ''));
        var list = items.length ? '<ul style="text-align:left;padding-left:22px">' + items.map(function(item) {
          item = item || {};
          return '<li><strong>' + esc(item.item_name || item.name || 'Item') + '</strong> &times; ' + esc(item.qty || item.quantity || 0) + '</li>';
        }).join('') + '</ul>' : '<p>No draft items found.</p>';
        unlockButtons();
        if (!(window.Swal && typeof window.Swal.fire === 'function')) {
          window.alert('Draft sale on ' + counterName + '.');
          return;
        }
        window.Swal.fire({
          icon: 'warning',
          title: 'Draft sale #' + esc(data.sale_id || ''),
          html: '<p>Items already added to this sale:</p>' + list,
          showDenyButton: true,
          showCancelButton: true,
          confirmButtonText: 'Continue sale',
          denyButtonText: 'Cancel sale',
          allowOutsideClick: false
        }).then(function(result) {
          if (result.isConfirmed && data.sale_id && data.counter) {
            $modalContent.data('initStatus', 2).data('saleId', data.sale_id);
            $modalContent.find('.counter-select-btn[data-counter="' + parseInt(data.counter, 10) + '"]').trigger('click');
          } else if (result.isDenied) {
            $.post(initApi, { action: 'cancel_order' }).done(function(cancel) {
              if (cancel && cancel.ok) {
                $modalContent.data('initStatus', 1).data('saleId', 0);
                $modalContent.find('.counter-select-btn[data-counter="' + counter + '"]').trigger('click');
              } else {
                window.Swal.fire('Could not cancel draft', cancel && cancel.message ? cancel.message : 'Please try again.', 'error');
              }
            });
          }
        });
      }

      $.ajax({
        url: initApi,
        type: 'POST',
        dataType: 'json',
        data: {
          action: initAction,
          counter: counter,
          st: initStatus,
          sale_id: saleId
        }
      }).done(function(res) {
        var ok = !!(res && (res.ok || res.success));
        var redirect = '';

        if (res) {
          if (res.data && res.data.redirect) {
            redirect = res.data.redirect;
          } else if (res.redirect) {
            redirect = res.redirect;
          }
        }

        if (ok && redirect) {
          window.location.href = redirect;
          return;
        }

        if (res && res.data && res.data.ongoing_sale) {
          showDraft(res);
          return;
        }
        unlockButtons();
      }).fail(function(xhr) {
        if (xhr && xhr.status === 409) {
          showDraft(xhr);
          return;
        }
        unlockButtons();
      });
    });
  })();
</script>
