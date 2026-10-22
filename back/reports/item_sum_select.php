<?php
require('../clude/head.php');
?>
<link href="../../assets/plugins/DataTables/media/css/dataTables.bootstrap.min.css" rel="stylesheet" />
<link href="../../assets/plugins/DataTables/extensions/Responsive/css/responsive.bootstrap.min.css" rel="stylesheet" />
<link href="../../assets/plugins/select2/dist/css/select2.min.css" rel="stylesheet" />
<style>
  .item-report-panel{background:#fff;padding:18px;margin-bottom:14px}
  .item-report-table th,.item-report-table td{border:1px solid #000!important;padding:5px!important}
  .item-report-table th{font-weight:700}
  .report-card{min-height:94px;margin-bottom:14px}
</style>
<div id="page-container" class="page-container fade page-without-sidebar page-header-fixed page-with-top-menu">
  <?php require('../clude/header.php'); require('../clude/top_menu.php'); ?>
  <div id="content" class="content">
    <div class="item-report-panel hidden-print">
      <div class="row">
        <div class="form-group col-md-4">
          <select id="item" class="form-control">
            <option value="">---- SELECT ITEM ----</option>
            <?php
            $items = mysqli_query($connection, 'SELECT item_id,name FROM kp_items ORDER BY name ASC');
            while ($item = $items ? mysqli_fetch_assoc($items) : null) {
                echo '<option value="'.(int)$item['item_id'].'">'.htmlspecialchars($item['name']).'</option>';
            }
            ?>
          </select>
        </div>
        <div class="form-group col-md-8 quick-dates">
          <button type="button" class="btn btn-default quick-date" data-range="yesterday">Yesterday</button>
          <button type="button" class="btn btn-default quick-date" data-range="today">Today</button>
          <button type="button" class="btn btn-default quick-date" data-range="week">This week</button>
          <button type="button" class="btn btn-default quick-date" data-range="month">This month</button>
          <button type="button" class="btn btn-warning" id="custom-date">Custom</button>
        </div>
      </div>
      <div id="custom-range" class="row" style="display:none">
        <div class="form-group col-md-3"><input id="from" type="date" class="form-control"></div>
        <div class="form-group col-md-3"><input id="to" type="date" class="form-control"></div>
        <div class="form-group col-md-2"><button id="search" type="button" class="btn btn-warning"><i class="fa fa-search"></i> Search</button></div>
      </div>
    </div>
    <div id="summary" class="row" style="display:none">
      <div class="col-md-4"><div class="widget widget-stats bg-blue report-card"><div class="stats-icon"><i class="fa fa-cubes"></i></div><div class="stats-info"><h4>ITEM QUANTITY SOLD</h4><p id="total-qty">0</p></div></div></div>
      <div class="col-md-4"><div class="widget widget-stats bg-green report-card"><div class="stats-icon"><i class="fa fa-money"></i></div><div class="stats-info"><h4>ITEM SALES TOTAL</h4><p id="total-total">0.00</p></div></div></div>
      <div class="col-md-4"><div class="widget widget-stats bg-purple report-card"><div class="stats-icon"><i class="fa fa-list"></i></div><div class="stats-info"><h4>SALE LINES</h4><p id="total-lines">0</p></div></div></div>
    </div>
    <div id="filter-result"></div>
  </div>
</div>
<script src="../../assets/plugins/jquery/jquery-3.2.1.min.js"></script>
<script src="../../assets/plugins/jquery-ui/jquery-ui.min.js"></script>
<script src="../../assets/plugins/bootstrap/4.0.0/js/bootstrap.bundle.min.js"></script>
<script src="../../assets/plugins/slimscroll/jquery.slimscroll.min.js"></script>
<script src="../../assets/plugins/js-cookie/js.cookie.js"></script>
<script src="../../assets/js/theme/default.min.js"></script>
<script src="../../assets/plugins/select2/dist/js/select2.min.js"></script>
<script src="../../assets/js/apps.min.js"></script>
<script src="../../assets/plugins/DataTables/media/js/jquery.dataTables.js"></script>
<script src="../../assets/plugins/DataTables/media/js/dataTables.bootstrap.min.js"></script>
<script src="../../assets/plugins/DataTables/extensions/Responsive/js/dataTables.responsive.min.js"></script>
<script>
(function(){
  var api='api/repo_api.php';
  function esc(v){return $('<div>').text(v==null?'':v).html();}
  function money(v){return Number(v||0).toFixed(2);}
  function iso(d){return d.toISOString().slice(0,10);}
  function render(rows){
    if(!rows.length){$('#summary').hide();return '<div class="alert alert-warning">There are no recorded sales.</div>';}
    var qty=0,total=0,h='<div class="panel panel-default"><div class="panel-heading"><h4 class="panel-title">Item Sale Distribution</h4></div><div class="panel-body"><table id="item-summary-table" class="table table-striped table-bordered item-report-table" width="100%"><thead><tr><th>Sale ID</th><th>Shift</th><th>Served By</th><th>Quantity</th><th>Price</th><th>Total</th></tr></thead><tbody>';
    rows.forEach(function(r){qty+=Number(r.qty||0);total+=Number(r.total||0);h+='<tr><td>'+r.sale_id+'</td><td>'+esc(r.shift)+'</td><td>'+esc(r.username)+'</td><td>'+r.qty+'</td><td>'+money(r.price)+'</td><td>'+money(r.total)+'</td></tr>';});
    $('#total-qty').text(qty);$('#total-total').text(money(total));$('#total-lines').text(rows.length);$('#summary').show();
    return h+'</tbody><tfoot><tr><th colspan="3">TOTAL</th><th>'+qty+'</th><th></th><th>'+money(total)+'</th></tr></tfoot></table></div></div>';
  }
  function load(){
    var item=$('#item').val(),from=$('#from').val(),to=$('#to').val();
    if(!item){$('#filter-result').html('<div class="alert alert-warning">Select an item first.</div>');return;}
    if(!from||!to){$('#filter-result').html('<div class="alert alert-warning">Select both dates.</div>');return;}
    $('#filter-result').html('<div class="alert alert-info">Loading item sales...</div>');
    $.getJSON(api,{action:'item_summary',item_id:item,from:from,to:to}).done(function(r){
      if(!r.ok){$('#filter-result').html('<div class="alert alert-danger">'+esc(r.message)+'</div>');return;}
      var rows=r.data.rows||[];$('#filter-result').html(render(rows));
      if(rows.length)$('#item-summary-table').DataTable({responsive:true,pageLength:25,order:[[0,'desc']]});
    }).fail(function(){$('#filter-result').html('<div class="alert alert-danger">Could not load item sales.</div>');});
  }
  $('.quick-date').on('click',function(){var now=new Date(),start=new Date(now),range=$(this).data('range');if(range==='yesterday'){start.setDate(now.getDate()-1);now=new Date(start);}else if(range==='week'){start.setDate(now.getDate()-now.getDay());}else if(range==='month'){start=new Date(now.getFullYear(),now.getMonth(),1);}$('#from').val(iso(start));$('#to').val(iso(now));load();});
  $('#custom-date').on('click',function(){$('#custom-range').slideToggle(150);});
  $('#search').on('click',load);
  $(document).ready(function(){App.init();if($.fn.select2){if($('#item').hasClass('select2-hidden-accessible'))$('#item').select2('destroy');$('#item').select2({width:'100%',minimumResultsForSearch:0});}});
})();
</script>
</body>
</html>
