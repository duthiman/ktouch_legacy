<style type="text/css">
  .req-start-shell {
    width: 100%;
    margin: 0;
    box-sizing: border-box;
    position: relative;
    left: 0 !important;
    right: 0 !important;
    transform: none !important;
    -webkit-transform: none !important;
  }
  .req-start-shell .modal-content {
    border-radius: 16px;
    overflow: hidden;
    width: 100%;
    box-sizing: border-box;
    margin: 0 auto;
  }
  .req-start-shell .modal-header {
    background: linear-gradient(135deg, #7e2014 0%, #b94828 52%, #e7a33a 100%);
    color: #fff8ea;
    border-bottom: 0;
    padding: 14px 16px;
  }
  .req-start-shell .close {
    color: #fff;
    opacity: 1;
  }
  .req-start-shell .modal-title {
    font-weight: 900;
    text-transform: uppercase;
    line-height: 1.3;
  }
  .req-start-shell .modal-body {
    background: #fffdf8;
    padding: 14px;
  }
  .req-start-warning {
    margin: 0;
    padding: 14px;
    border-radius: 12px;
    background: #fff2e8;
    border: 1px solid rgba(176, 43, 43, 0.14);
    color: #8a2a19;
    font-size: 14px;
    font-weight: 800;
    line-height: 1.45;
  }
  .req-start-shell .modal-footer {
    border-top: 0;
    background: #fffdf8;
    padding: 12px 14px 14px;
    display: flex;
    align-items: stretch;
    justify-content: space-between;
    flex-wrap: nowrap;
    gap: 8px;
  }
  .req-start-btn {
    min-height: 50px;
    height: 50px;
    flex: 1 1 0;
    display: inline-flex;
    align-items: center;
    justify-content: center;
    border-radius: 10px;
    font-size: 14px;
    font-weight: 900;
    text-transform: uppercase;
    white-space: normal;
    margin: 0;
    line-height: 1.2;
  }
  .req-start-shell .modal-footer .btn + .btn {
    margin-left: 0;
  }
  .req-start-close {
    background-color: #fff;
    border: 3px solid red;
    color: red;
  }
  .req-start-request {
    background-color: #fff;
    border: 3px solid #61b846;
    color: #61b846;
  }
  @media (max-width: 991px) {
    .req-start-shell {
      width: 100%;
      max-width: none;
      margin: 0;
    }
    .req-start-shell .modal-content {
      max-height: calc(100vh - 24px);
      display: flex;
      flex-direction: column;
    }
    .req-start-shell .modal-body {
      overflow-y: auto;
      max-height: calc(100vh - 190px);
    }
  }
  @media (max-width: 480px) {
    .req-start-shell .modal-header {
      padding: 12px 14px;
    }
    .req-start-shell .modal-title {
      font-size: 16px;
    }
    .req-start-shell .modal-footer {
      display: flex;
    }
  }
</style>

<div class="req-start-shell">
  <div class="modal-content">
    <div class="modal-header">
      <button type="button" class="close" data-dismiss="modal" aria-label="Close"><span aria-hidden="true">&times;</span></button>
      <h4 class="modal-title" id="gwit-start-title">Stock Request</h4>
    </div>
    <div class="modal-body">
      <div id="results"></div>
      <p class="req-start-warning">Warning! Are you sure you want to request for stock?</p>
    </div>
    <div class="modal-footer">
      <button type="button" class="btn req-start-btn req-start-close" data-dismiss="modal">Close</button>
      <button type="button" class="btn req-start-btn req-start-request" id="request">Request</button>
    </div>
  </div>
</div>

<script type="text/javascript">
  (function($) {
    $(document).off('click.gwitStartRequest').on('click.gwitStartRequest', '#request', function() {
      $.post('../gwitia/API/gwit_api.php', { action: 'init_request' }, null, 'json')
        .done(function(response) {
          if (!response.ok) {
            $('#results').html('<div class="alert alert-danger">' + (response.message || 'Something went wrong, Please try again...') + '</div>');
            return;
          }
          if (response.data && response.data.redirect) {
            window.location.replace(response.data.redirect);
          }
        })
        .fail(function(xhr) {
          var message = (xhr.responseJSON && xhr.responseJSON.message) || 'Something went wrong, Please try again...';
          $('#results').html('<div class="alert alert-danger">' + message + '</div>');
        });
    });

  })(jQuery);
</script>
