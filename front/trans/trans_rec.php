<!DOCTYPE html>
<html lang="en">
<head>
  <meta charset="utf-8">
  <meta name="viewport" content="width=device-width, initial-scale=1, shrink-to-fit=no">
  <meta http-equiv="x-ua-compatible" content="ie=edge">
  <title>Receive Transfer</title>
  <link rel="stylesheet" href="../../assets/plugins/bootstrap/3.3.7/css/bootstrap.min.css">
  <script src="../../assets/plugins/jquery/jquery-3.2.1.min.js"></script>
  <script src="../../assets/plugins/bootstrap/3.3.7/js/bootstrap.min.js"></script>
  <style>
    body { background: url("../assets/img/bg/3.jpg") no-repeat; background-size: cover; }
    .trans-rec-shell { background: rgba(255,255,255,0.94); border-radius: 16px; box-shadow: 0 14px 28px rgba(0,0,0,0.08); overflow: hidden; }
    .trans-rec-wrap { overflow-x: auto; }
    .trans-rec-table th, .trans-rec-table td { white-space: nowrap; vertical-align: middle !important; }
  </style>
</head>
<body>
  <header>
    <nav class="navbar navbar-fixed-top scrolling-navbar" style="padding: 1px;">
      <ul class="nav navbar-nav navbar-left">
        <li class="nav-item" style="margin-left: 10px; margin-top: 10px;">
          <a href="../dash/dash.php" style="font-family:'comic sans ms'; font-size:12px; font-weight:bold; color:#02550E !important; padding-top:30%; border-radius:100%; height:80px; width:80px; background-color:#FFF; border:5px solid #02550E;">HOME</a>
        </li>
      </ul>
      <ul class="nav navbar-nav navbar-right" style="margin-right: 10px; margin-top: 10px;">
        <li class="nav-item">
          <a href="../log_out.php" style="font-family:'comic sans ms'; font-size:12px; font-weight:bold; color:#02550E !important; padding-top:30%; border-radius:100%; height:80px; width:80px; background-color:#FFF; border:5px solid #02550E;">Log Out</a>
        </li>
      </ul>
    </nav>
  </header>
  <div id="page-container" class="page-without-sidebar page-header-fixed">
    <div id="content" class="content" style="margin-top: 50px;">
      <div class="panel trans-rec-shell">
        <div class="panel-body col-md-8" style="margin-left: 20%; background-color: white;">
          <div class="trans-rec-wrap">
            <table class="table table-bordered trans-rec-table" style="padding: 5px 5px;">
              <thead>
                <tr><th>Item Name</th><th>Transfered Qty</th><th>Action</th></tr>
              </thead>
              <tbody id="trans-rec-body">
                <tr><td colspan="3">Loading transfer items...</td></tr>
              </tbody>
            </table>
          </div>
        </div>
      </div>
    </div>
    <div id="modal" class="modal fade" tabindex="-1" role="dialog" aria-hidden="true">
      <div class="modal-dialog"><div class="modal-content" id="load" style="width: 290px;"></div></div>
    </div>
  </div>
  <script>
    (function() {
      function queryParam(name) {
        return new URLSearchParams(window.location.search).get(name) || '';
      }
      function escapeHtml(value) {
        return String(value).replace(/&/g, '&amp;').replace(/</g, '&lt;').replace(/>/g, '&gt;').replace(/"/g, '&quot;').replace(/'/g, '&#39;');
      }
      function loadReceivePage() {
        var transId = queryParam('trans');
        if (!transId) {
          window.location.href = '../rec/rec_pending.php';
          return;
        }
        $.ajax({
          url: 'API/trans_api.php',
          type: 'GET',
          data: { action: 'receive_page', trans: transId },
          dataType: 'json'
        }).done(function(res) {
          if (!res || !res.ok) {
            if (res && res.data && res.data.redirect) {
              window.location.href = res.data.redirect;
              return;
            }
            $('#trans-rec-body').html('<tr><td colspan="3">' + ((res && res.message) ? escapeHtml(res.message) : 'Could not load transfer.') + '</td></tr>');
            return;
          }
          if (res.data && res.data.redirect) {
            window.location.href = res.data.redirect;
            return;
          }
          var rows = (res.data && res.data.rows) ? res.data.rows : [];
          if (!rows.length) {
            $('#trans-rec-body').html('<tr><td colspan="3">No items pending.</td></tr>');
            return;
          }
          var html = '';
          for (var i = 0; i < rows.length; i++) {
            var row = rows[i] || {};
            html += '<tr><td>' + escapeHtml(row.item_name || '') + '</td><td>' + escapeHtml(String(row.qty || 0)) + '</td><td><a data-toggle="modal" data-target="#modal" data-id="' + (row.item_id || 0) + '" data-id2="' + transId + '" id="trans-item" class="btn btn-primary btn-sm">Receive</a></td></tr>';
          }
          $('#trans-rec-body').html(html);
        }).fail(function() {
          $('#trans-rec-body').html('<tr><td colspan="3">Could not load transfer.</td></tr>');
        });
      }
      $(document).ready(function(){
        loadReceivePage();
        $(document).on('click', '#trans-item', function(){
          var uid = $(this).data('id');
          var transId = $(this).data('id2');
          $.ajax({
            url: 'API/trans_api.php',
            type: 'POST',
            data: { action:'accept_modal', id:uid, trans:transId },
            dataType: 'html'
          }).done(function(data){
            $('#load').html(data);
          }).fail(function(){
            $('#load').html('<div class="modal-content"><div class="modal-body">Something went wrong, Please try again...</div></div>');
          });
        });
      });
    })();
  </script>
</body>
</html>
