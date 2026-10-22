<?php
require_once '../../clude/head.php';
$shift = isset($_GET['shift']) ? trim((string)$_GET['shift']) : '';
?>
<link rel="stylesheet" href="take.css">
<div id="page-container" class="fade page-header-fixed take-page-fixed">
  <?php require '../../clude/header.php'; ?>
  <?php //require '../../clude/menu.php'; ?>
  <div id="content" class="content">
    <div class="row">
      <div class="col-md-4">
        <div class="panel panel-success take-card">
          <div class="panel-heading"><h4 class="panel-title"><i class="fa fa-archive"></i> TAKE STOCK</h4></div>
          <div class="panel-body">
            <div class="take-summary-cards" aria-label="Stock take progress">
              <div class="take-stat total"><span>Total</span><strong id="stat-total">0</strong></div>
              <div class="take-stat taken"><span>Taken</span><strong id="stat-taken">0</strong></div>
              <div class="take-stat remaining"><span>Remaining</span><strong id="stat-remaining">0</strong></div>
            </div>
            <div id="results"></div>
            <div class="form-group"><label for="id">SELECT ITEM TO TAKE STOCK</label><select class="form-control" id="id" name="id"></select></div>
            <div class="form-group"><label for="qty">COUNTED QUANTITY</label><input type="text" inputmode="decimal" pattern="[0-9]+([.][0-9]+)?" class="form-control" id="qty" name="qty" autocomplete="off" autofocus><small class="help-block">Enter <code>00</code> when the counted quantity is zero.</small></div>
          </div>
          <div class="panel-footer text-right"><button type="button" id="take" class="btn btn-lime btn-lg take-action-btn"><i class="fa fa-plus"></i> Take Stock</button><button type="button" id="finish" class="btn btn-danger btn-lg take-action-btn" style="display:none"><i class="fa fa-flag-checkered"></i> Finish</button></div>
        </div>
      </div>
      <div class="col-md-8">
        <div class="panel panel-success take-card">
          <div class="panel-heading"><h4 class="panel-title"><i class="fa fa-list"></i> TAKEN STOCK</h4></div>
          <div class="panel-body take-results">
            <div class="take-summary-cards take-variance-cards" aria-label="Taken stock summary">
              <div class="take-stat taken"><span>Total Taken</span><strong id="stat-total-taken">0</strong></div>
              <div class="take-stat ok"><span>OK</span><strong id="stat-ok">0</strong></div>
              <div class="take-stat excess"><span>Excess</span><strong id="stat-excess">0</strong></div>
              <div class="take-stat less"><span>Less</span><strong id="stat-less">0</strong></div>
              <div class="take-stat variance"><span>Variance Count</span><strong id="stat-variance">0</strong></div>
            </div>
            <div id="d1"><div class="text-center text-muted"><i class="fa fa-spinner fa-spin"></i> Loading...</div></div>
          </div>
        </div>
      </div>
    </div>
  </div>
</div>
<?php require '../../clude/foot.php'; ?>
<script>
if (typeof App !== 'undefined' && typeof App.init === 'function') {
  App.init();
}
(function($) {
  var apiUrl='API/take_api.php', shift=<?php echo json_encode($shift); ?>, remainingItems=[], takenRows=[];
  function esc(value){ return $('<div>').text(value == null ? '' : value).html(); }
  function updateSummary(){ var remaining=remainingItems.length, taken=takenRows.length, ok=0, excess=0, less=0, varianceCount=0; (takenRows||[]).forEach(function(row){ var v=parseFloat(row.variance)||0; if(v===0){ok++;} else if(v>0){excess++; varianceCount++;} else if(v<0){less++; varianceCount++;} }); $('#stat-total').text(remaining+taken); $('#stat-taken,#stat-total-taken').text(taken); $('#stat-remaining').text(remaining); $('#stat-ok').text(ok); $('#stat-excess').text(excess); $('#stat-less').text(less); $('#stat-variance').text(varianceCount); }
  function updateFinishVisibility(){ var remaining=$('#id option').length; $('#finish').toggle(remaining===0); $('#take').prop('disabled',remaining===0); }
  function renderItems(items){ remainingItems=items||[]; var html=''; remainingItems.forEach(function(item){ html+='<option value="'+parseInt(item.item_id,10)+'">'+esc(item.name)+'</option>'; }); $('#id').html(html); updateFinishVisibility(); updateSummary(); }
  function renderRows(rows){
    var html='<table class="table table-bordered table-striped take-table"><thead><tr><th>Item Name</th><th>System Closing</th><th>Counted Qty</th><th>Variance</th><th>Action</th></tr></thead><tbody>';
    if(!rows || !rows.length){ html+='<tr><td colspan="5" class="text-center text-muted">No stock has been taken for this shift.</td></tr>'; }
    (rows||[]).forEach(function(row){ var variance=parseFloat(row.variance)||0; var cls=variance===0?'success':'danger'; html+='<tr class="text-'+cls+'"><td>'+esc(row.name)+'</td><td>'+row.closing+'</td><td>'+row.counted+'</td><td>'+variance+'</td><td><button type="button" class="btn btn-'+(cls==='success'?'success':'danger')+' btn-xs delete-take" data-id="'+row.item_id+'"><i class="fa fa-times"></i> Remove</button></td></tr>'; });
    takenRows=rows||[]; $('#d1').html(html+'</tbody></table>'); updateSummary();
  }
  function loadItems(){ $.getJSON(apiUrl,{action:'items'}).done(function(res){ renderItems(res&&res.data?res.data.items:[]); }); }
  function loadRows(){ $.getJSON(apiUrl,{action:'content',shift:shift}).done(function(res){ renderRows(res&&res.data?res.data.rows:[]); }).fail(function(){ $('#d1').html('<div class="alert alert-danger">Could not load taken stock.</div>'); }); }
  function take(){ var rawQty=String($('#qty').val()||'').trim(); if(rawQty===''||rawQty==='0'){ $('#results').html('<div class="alert alert-danger">Enter 00 to record zero stock.</div>'); return; } if(!/^[0-9]+(?:\.[0-9]+)?$/.test(rawQty)){ $('#results').html('<div class="alert alert-danger">Enter a valid quantity.</div>'); return; } var itemId=parseInt($('#id').val(),10); $.ajax({url:apiUrl,type:'POST',dataType:'json',data:{action:'save',id:itemId,qty:rawQty,shift:shift}}).done(function(res){ $('#results').html('<div class="alert alert-'+(res.ok?'success':'danger')+'">'+esc(res.message)+'</div>'); if(res.ok){ remainingItems=$.grep(remainingItems,function(item){ return parseInt(item.item_id,10)!==itemId; }); $('#id option[value="'+itemId+'"]').remove(); $('#id').val($('#id option:first').val()||''); updateFinishVisibility(); updateSummary(); $('#qty').val('').focus(); loadRows(); } }).fail(function(xhr){ $('#results').html('<div class="alert alert-danger">'+esc(xhr.responseJSON&&xhr.responseJSON.message?xhr.responseJSON.message:'Could not save stock take.')+'</div>'); }); }
  $('#take').on('click',take); $('#qty').on('keydown',function(e){if(e.which===13){e.preventDefault();take();}});
  $(document).on('click','.delete-take',function(){ var itemId=parseInt($(this).data('id'),10); $.post(apiUrl,{action:'delete',item_id:itemId,shift:shift},function(res){ $('#results').html('<div class="alert alert-'+(res.ok?'success':'danger')+'">'+esc(res.message)+'</div>'); if(res.ok){ loadItems(); } loadRows(); },'json'); });
  $('#finish').on('click',function(){ $.post(apiUrl,{action:'finish'},function(res){ $('#results').html('<div class="alert alert-'+(res.ok?'success':'danger')+'">'+esc(res.message)+'</div>'); },'json'); });
  $(function(){ loadItems(); loadRows(); });
}(jQuery));
</script>
