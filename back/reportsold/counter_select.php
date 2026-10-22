<?php
require('../clude/head.php');
?>
<link href="../../assets/plugins/DataTables/media/css/dataTables.bootstrap.min.css" rel="stylesheet" />
<link href="../../assets/plugins/DataTables/extensions/Responsive/css/responsive.bootstrap.min.css" rel="stylesheet" />
<link href="../../assets/plugins/select2/dist/css/select2.min.css" rel="stylesheet" />
<style>
  .counter-report-panel{background:#fff;padding:18px;margin-bottom:14px}
  .counter-report-table th,.counter-report-table td{border:1px solid #000!important;padding:5px!important}
  .counter-report-table th{font-weight:700}
  .report-card{min-height:94px;margin-bottom:14px}
  .counter-filter{background:#f5f0fa;padding:10px;margin-bottom:12px;border-left:3px solid #6f42c1}
  .counter-filter .btn{margin:0 5px 5px 0}
  .category-row td{background:#f3eefb!important;color:#5a3296;font-size:16px;font-weight:700;padding:10px!important;border-top:2px solid #cbb6e8!important}
  .counter-row td{background:#e7dcf5!important;color:#45206f;font-size:19px;font-weight:800;padding:12px!important;border-top:3px solid #6f42c1!important}
  .category-total{font-size:15px;color:#5a3296}
  .counter-row td:nth-child(2),.counter-row td:nth-child(3),.category-row td:nth-child(2),.category-row td:nth-child(3){text-align:center}
  .counter-row strong,.category-row strong{display:block;font-size:18px}
</style>
<div id="page-container" class="page-container fade page-without-sidebar page-header-fixed page-with-top-menu">
  <?php require('../clude/header.php'); require('../clude/top_menu.php'); ?>
  <div id="content" class="content">
    <div class="counter-report-panel hidden-print">
      <div class="row">
        <div class="form-group col-md-4">
          <select id="counter" class="form-control">
            <option value="">---- SELECT COUNTER ----</option>
            <option value="0">ALL COUNTERS</option>
            <?php
            $counters = mysqli_query($connection, 'SELECT cntr_id,name FROM kp_counters ORDER BY name ASC');
            while ($counter = $counters ? mysqli_fetch_assoc($counters) : null) {
                echo '<option value="'.(int)$counter['cntr_id'].'">'.htmlspecialchars($counter['name']).'</option>';
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
      <div class="col-md-6"><div class="widget widget-stats bg-blue report-card"><div class="stats-icon"><i class="fa fa-cubes"></i></div><div class="stats-info"><h4>ITEM QUANTITY SOLD</h4><p id="total-qty">0</p></div></div></div>
      <div class="col-md-6"><div class="widget widget-stats bg-green report-card"><div class="stats-icon"><i class="fa fa-money"></i></div><div class="stats-info"><h4>COUNTER SALES TOTAL</h4><p id="total-total">0.00</p></div></div></div>
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
  var allRows=[];
  function renderTable(rows){
    var qty=0,total=0,counters={},counterOrder=[];
    rows.forEach(function(r){qty+=Number(r.qty||0);total+=Number(r.total||0);var ck=r.counter_id+'|'+(r.counter_name||'Unknown counter');if(!counters[ck]){counters[ck]={name:r.counter_name||'Unknown counter',qty:0,total:0,categories:{},categoryOrder:[]};counterOrder.push(ck);}var counter=counters[ck],category=r.category_name||'Uncategorised';counter.qty+=Number(r.qty||0);counter.total+=Number(r.total||0);if(!counter.categories[category]){counter.categories[category]={rows:[],qty:0,total:0};counter.categoryOrder.push(category);}counter.categories[category].rows.push(r);counter.categories[category].qty+=Number(r.qty||0);counter.categories[category].total+=Number(r.total||0);});
    var h='<table id="counter-summary-table" class="table table-striped table-bordered counter-report-table" width="100%"><thead><tr><th>Item</th><th>Quantity</th><th>Price</th><th>Total</th></tr></thead><tbody>';
    counterOrder.forEach(function(ck){var counter=counters[ck];h+='<tr class="counter-row"><td>'+esc(counter.name)+'</td><td><strong>'+money(counter.total)+'</strong></td><td><strong>'+counter.qty+'</strong></td><td></td></tr>';counter.categoryOrder.forEach(function(category){var g=counter.categories[category];h+='<tr class="category-row"><td>'+esc(category)+'</td><td><strong>'+money(g.total)+'</strong></td><td><strong>'+g.qty+'</strong></td><td></td></tr>';g.rows.forEach(function(r){h+='<tr><td>'+esc(r.item_name)+'</td><td>'+r.qty+'</td><td>'+money(r.price)+'</td><td>'+money(r.total)+'</td></tr>';});});});
    $('#total-qty').text(qty);$('#total-total').text(money(total));$('#summary').show();
    return h+'</tbody><tfoot><tr><th>TOTAL</th><th>'+qty+'</th><th></th><th>'+money(total)+'</th></tr></tfoot></table>';
  }
  function render(rows){
    if(!rows.length){$('#summary').hide();return '<div class="alert alert-warning">There are no recorded sales.</div>';}
    var h='<div class="panel panel-purple"><div class="panel-heading"><h4 class="panel-title">Counter Item Sales Summary</h4></div><div class="panel-body">';
    if($('#counter').val()==='0'){var counters={};allRows.forEach(function(r){counters[r.counter_id]=r.counter_name;});h+='<div class="counter-filter"><strong>Filter counter:</strong> <button type="button" class="btn btn-xs btn-primary counter-filter-btn" data-counter="0">All counters</button>';Object.keys(counters).forEach(function(id){h+='<button type="button" class="btn btn-xs btn-default counter-filter-btn" data-counter="'+id+'">'+esc(counters[id])+'</button>';});h+='</div>';}
    return h+'<div id="counter-table-wrap">'+renderTable(rows)+'</div></div></div>';
  }
  function load(){
    var counter=$('#counter').val(),from=$('#from').val(),to=$('#to').val();
    if(counter===''){ $('#filter-result').html('<div class="alert alert-warning">Select a counter first.</div>');return;}
    if(!from||!to){$('#filter-result').html('<div class="alert alert-warning">Select both dates.</div>');return;}
    $('#filter-result').html('<div class="alert alert-info">Loading counter sales...</div>');
    $.getJSON(api,{action:'counter_summary',counter_id:counter,from:from,to:to}).done(function(r){
      if(!r.ok){$('#filter-result').html('<div class="alert alert-danger">'+esc(r.message)+'</div>');return;}
      var rows=r.data.rows||[];allRows=rows;$('#filter-result').html(render(rows));
      $('.counter-filter-btn').on('click',function(){var selected=String($(this).data('counter')),filtered=selected==='0'?allRows:allRows.filter(function(r){return String(r.counter_id)===selected;});$('#counter-table-wrap').html(renderTable(filtered));$('.counter-filter-btn').removeClass('btn-primary').addClass('btn-default');$(this).removeClass('btn-default').addClass('btn-primary');});
    }).fail(function(){$('#filter-result').html('<div class="alert alert-danger">Could not load counter sales.</div>');});
  }
  $('.quick-date').on('click',function(){var now=new Date(),start=new Date(now),range=$(this).data('range');if(range==='yesterday'){start.setDate(now.getDate()-1);now=new Date(start);}else if(range==='week'){start.setDate(now.getDate()-now.getDay());}else if(range==='month'){start=new Date(now.getFullYear(),now.getMonth(),1);}$('#from').val(iso(start));$('#to').val(iso(now));load();});
  $('#custom-date').on('click',function(){$('#custom-range').slideToggle(150);});
  $('#search').on('click',load);
  $(document).ready(function(){App.init();if($.fn.select2){if($('#counter').hasClass('select2-hidden-accessible'))$('#counter').select2('destroy');$('#counter').select2({width:'100%',minimumResultsForSearch:0});}});
})();
</script>
</body>
</html>
