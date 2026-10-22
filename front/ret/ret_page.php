<?php
include("../clude/head.php");
$sale_id = (int)($_GET['sale_id'] ?? 0);
?>
<link href="ret.css?v=<?php echo filemtime(__DIR__ . '/ret.css'); ?>" rel="stylesheet" />
<body class="fixed-sn deep-orange-skin">
  <div class="ret-desktop">
    <header>
      <nav class="navbar navbar-fixed-top scrolling-navbar double-nav">
        <ul class="nav navbar-nav navbar-left">
          <li class="nav-item">
            <a href="../dash/dash.php" style="font-family: 'comic sans ms'; font-size: 12PX; font-weight: bold; color: #02550E !important; padding-top:30%; border-radius: 100%; height: 70px; width:70px; background-color: #FFF;border: 5px solid #02550E;" class="btn btn-sm btn-success m-b-10 ret-leave-link">Home</a>
          </li>
        </ul>
        <ul class="nav navbar-nav navbar-right" style="margin-right: 10px; margin-top: 10px;">
          <li class="nav-item">
            <a href="../log_out.php" style="font-family: 'comic sans ms'; font-size: 12PX; font-weight: bold; color: #02550E !important; padding-top:30%; border-radius: 100%; height: 80px; width:80px; background-color: #FFF; border: 5px solid #02550E;" class="btn btn-sm btn-success ret-logout-link">Log Out</a>
          </li>
        </ul>
      </nav>
    </header>
    <div id="contents" class="contents">
      <div class="row">
        <div class="col-md-6">
          <div class="panel ret-panel" >
          <div class="panel-heading">Sale Items For Return</div>
          <div class="panel-body" id="ordscreen"></div>
</div>
        </div>
        <div class="col-md-6">
          <div class="panel ret-panel" >
          <div class="panel-heading">Selected Returns</div>
          <div class="panel-body" id="screen"></div>
        </div>
</div>
      </div>
    </div>
  </div>

  <div class="ret-mobile">
    <div class="ret-mobile-app">
      <div class="ret-mobile-topbar">
        <h1>Return Items</h1>
        <p>Bill No: <?php echo $sale_id; ?></p>
        <div class="ret-mobile-actions">
          <a href="../view/my_sales.php" class="ret-leave-link">Back</a>
          <a href="../dash/dash.php" class="ret-leave-link">Home</a>
          <a href="../log_out.php" class="ret-logout-link">Log Out</a>
        </div>
      </div>
      <div class="ret-mobile-card">
        <div class="ret-mobile-card-head">Sale Items For Return</div>
        <div class="ret-mobile-card-body" id="ordscreen_mobile"></div>
      </div>
      <div class="ret-mobile-card">
        <div class="ret-mobile-card-head">Selected Returns</div>
        <div class="ret-mobile-card-body" id="screen_mobile"></div>
      </div>
    </div>
  </div>

  <div id="modal" class="modal fade" tabindex="-1" role="dialog" aria-labelledby="gridModalLabel" aria-hidden="true">
    <div class="modal-dialog" id="load"></div>
  </div>

  <div id="edit_item" class="modal fade" tabindex="-1" role="dialog" aria-labelledby="gridModalLabel" aria-hidden="true">
    <div class="modal-dialog" id="dynamic-content"></div>
  </div>

  <div id="finish_return_modal" class="modal fade" tabindex="-1" role="dialog" aria-hidden="true">
    <div class="modal-dialog">
      <div class="modal-content">
        <div class="modal-header" style="background: linear-gradient(135deg, #7e2014 0%, #b94828 52%, #e7a33a 100%); color:#fff8ea;">
          <h4 class="modal-title"><b>Finish Return</b></h4>
        </div>
        <div class="modal-body">
          <div class="ret-finish-warning">
            <p style="margin:0 0 8px;">Warning: this action is irreversible.</p>
            <p style="margin:0 0 8px;">Remember to ask cashier to confirm the returns so you get the copy receipt.</p>
            <p style="margin:0;">If this is skipped, the items will still remain in your sales.</p>
          </div>
        </div>
        <div class="modal-footer">
          <button type="button" class="btn btn-danger" data-dismiss="modal" style="font-weight:bold;">Close</button>
          <button type="button" class="btn btn-success ret-finish-link" id="ret_finish_confirm_btn" style="font-weight:bold;">Continue</button>
        </div>
      </div>
    </div>
  </div>

  <script src="../../assets/plugins/jquery/jquery-3.2.1.min.js"></script>
  <script src="../../assets/plugins/bootstrap/4.0.0/js/bootstrap.bundle.min.js"></script>
  <script type="text/javascript">
    var sale_id = <?php echo json_encode($sale_id); ?>;
    <?php include('ret.js'); ?>
    $(document).ready(function(){
      var allowReturnLeave = false;
      loadRetPage();

      $(document).on('click','.ret-leave-link', function(e){
        if (allowReturnLeave || !hasPendingReturns()) {
          return true;
        }
        e.preventDefault();
        alert('You have a raised return. Finish it or remove the return items before leaving this page.');
      });

      // Logging out must always be allowed, even when a return is currently selected.
      $(document).on('click','.ret-logout-link', function(){
        allowReturnLeave = true;
      });

      $(document).on('click','.ret-finish-link', function(){
        allowReturnLeave = true;
      });

      $(document).on('click','#ret', function(e){
        e.preventDefault();
        var uid = $(this).data('id');
        var counter = $(this).data('id3');
        openReturnModal(uid, counter);
      });

      $(document).on('click', '#btn-ret', function(){
        var qty = parseFloat($('#ret_modal_qty').val() || 0);
        var itemId = parseInt($('#ret_modal_item_id').val() || 0, 10);
        var counter = parseInt($('#ret_modal_counter').val() || 0, 10);
        var ordered = parseFloat($('#ret_modal_oqty').val() || 0);
        if (qty <= 0 || qty > ordered) {
          alert('Enter a valid return quantity.');
          return;
        }

        $.post('API/ret_api.php', {
          action: 'add_return',
          sale_id: sale_id,
          item_id: itemId,
          counter: counter,
          qty: qty
        }).done(function(response){
          if (!response.ok) {
            alert(response.message || 'Could not raise return.');
            return;
          }
          $('#edit_item').modal('hide');
          renderRetState(response.data || {});
        }).fail(function(xhr){
          alert((xhr.responseJSON && xhr.responseJSON.message) || 'Could not raise return.');
        });
      });

      $(document).on('click', '#ret_finish_confirm_btn', function(){
        finishReturnRequest();
      });

      $(window).on('beforeunload', function(e){
        if (allowReturnLeave || !hasPendingReturns()) {
          return;
        }
        var message = 'You have a raised return. Finish it or remove it before leaving this page.';
        e.preventDefault();
        e.returnValue = message;
        return message;
      });
    });
  </script>
</body>
</html>
