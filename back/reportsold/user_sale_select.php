<?php
require('../clude/head.php');
?>
<style>
  .user-sale-panel{background:#fff;padding:18px;margin-bottom:14px;border-top:4px solid #6f42c1}
  .user-sale-table th,.user-sale-table td{border:1px solid #000!important;padding:5px!important}
  .user-sale-table th{font-weight:700}
  .user-sale-items{background:#f7f7f7;padding:10px}
  .report-card{min-height:94px;margin-bottom:14px}
  .report-card .stats-title{font-size:12px}
  .report-card .stats-number{font-size:24px}
  .quick-date{margin:0 4px 4px 0}
  .report-tabs{background:#fff;border:1px solid #e5e5e5;border-top:3px solid #6f42c1}
  .report-tabs .nav-tabs{display:flex;padding:0;background:#f4f0fa;border-bottom:1px solid #ddd}
  .report-tabs .nav-tabs>li{flex:1;text-align:center;margin-bottom:0}
  .report-tabs .nav-tabs>li>a{font-weight:600;color:#66717d;border:0;border-radius:0;padding:14px 10px;white-space:nowrap}
  .report-tabs .nav-tabs>li.active>a,.report-tabs .nav-tabs>li.active>a:focus{color:#6f42c1;background:#fff;border:0;border-bottom:3px solid #6f42c1}
  .report-tabs .tab-content{padding:15px}
  .category-row td{background:#f3eefb!important;color:#5a3296;font-size:16px;font-weight:700;padding:10px!important;border-top:2px solid #cbb6e8!important}
  .category-total{float:right;font-size:15px;color:#5a3296}
  .counter-row td{background:#e7dcf5!important;color:#45206f;font-size:19px;font-weight:800;padding:12px!important;border-top:3px solid #6f42c1!important}
  .quick-dates{display:flex;flex-wrap:wrap;gap:5px}
  .quick-dates .quick-date,.quick-dates #custom-date{flex:1 1 110px}
  .summary-panel .panel-heading,.report-tabs .panel-heading{background:#6f42c1;color:#fff}
</style>
<link href="../../assets/plugins/DataTables/media/css/dataTables.bootstrap.min.css" rel="stylesheet" />
<link href="../../assets/plugins/DataTables/extensions/Responsive/css/responsive.bootstrap.min.css" rel="stylesheet" />
<link href="../../assets/plugins/select2/dist/css/select2.min.css" rel="stylesheet" />
<div id="page-container" class="page-container fade page-without-sidebar page-header-fixed page-with-top-menu">
  <?php require('../clude/header.php'); require('../clude/top_menu.php'); ?>
  <div id="content" class="content">
    <div class="user-sale-panel hidden-print">
      <div class="row">
        <div class="form-group col-md-4"><select id="user" class="form-control"><option value="">---- SELECT USER ----</option><?php $users = mysqli_query($connection, "SELECT user_id, username FROM kp_users WHERE role_id!=1 ORDER BY username ASC"); while ($u = $users ? mysqli_fetch_assoc($users) : null) { ?><option value="<?php echo (int)$u['user_id']; ?>"><?php echo htmlspecialchars($u['username']); ?></option><?php } ?></select></div>
        <div class="form-group col-md-3"><select id="counter" class="form-control"><option value="0">---- ALL COUNTERS ----</option><?php $counters = mysqli_query($connection, "SELECT cntr_id,name FROM kp_counters ORDER BY name ASC"); while ($counter = $counters ? mysqli_fetch_assoc($counters) : null) { ?><option value="<?php echo (int)$counter['cntr_id']; ?>"><?php echo htmlspecialchars($counter['name']); ?></option><?php } ?></select></div>
        <div class="form-group col-md-5 quick-dates">
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
      <div class="col-md-3"><div class="widget widget-stats bg-green report-card"><div class="stats-icon"><i class="fa fa-shopping-cart"></i></div><div class="stats-info"><h4>SALES</h4><p id="total-sales">0</p></div></div></div>
      <div class="col-md-3"><div class="widget widget-stats bg-blue report-card"><div class="stats-icon"><i class="fa fa-cubes"></i></div><div class="stats-info"><h4>ITEM QUANTITY</h4><p id="total-qty">0</p></div></div></div>
      <div class="col-md-3"><div class="widget widget-stats bg-purple report-card"><div class="stats-icon"><i class="fa fa-money"></i></div><div class="stats-info"><h4>SALES TOTAL</h4><p id="total-amount">0.00</p></div></div></div>
      <div class="col-md-3"><div class="widget widget-stats bg-orange report-card"><div class="stats-icon"><i class="fa fa-list"></i></div><div class="stats-info"><h4>SALE ITEMS TOTAL</h4><p id="total-items">0.00</p></div></div></div>
    </div>
    <div id="result"></div>
  </div>
</div>
<div class="modal fade" id="sale-items-modal" tabindex="-1" role="dialog" aria-hidden="true">
  <div class="modal-dialog modal-lg" role="document">
    <div class="modal-content">
      <div class="modal-header"><button type="button" class="close" data-dismiss="modal">&times;</button><h4 class="modal-title">Sale items</h4></div>
      <div class="modal-body" id="sale-items-modal-body">Loading items...</div>
    </div>
  </div>
</div>
<script src="../../assets/plugins/jquery/jquery-3.2.1.min.js"></script>
<script src="../../assets/plugins/jquery-ui/jquery-ui.min.js"></script>
<script src="../../assets/plugins/bootstrap/4.0.0/js/bootstrap.bundle.min.js"></script>
<!--[if lt IE 9]>
  <script src="../assets/crossbrowserjs/html5shiv.js"></script>
  <script src="../assets/crossbrowserjs/respond.min.js"></script>
  <script src="../assets/crossbrowserjs/excanvas.min.js"></script>
<![endif]-->
<script src="../../assets/plugins/slimscroll/jquery.slimscroll.min.js"></script>
<script src="../../assets/plugins/js-cookie/js.cookie.js"></script>
<script src="../../assets/js/theme/default.min.js"></script>
<script src="../../assets/plugins/select2/dist/js/select2.min.js"></script>
<script src="../../assets/plugins/Hullabaloo/js/hullabaloo.js"></script>
<script src="../../assets/js/apps.min.js"></script>
<script src="../../assets/plugins/DataTables/media/js/jquery.dataTables.js"></script>
<script src="../../assets/plugins/DataTables/media/js/dataTables.bootstrap.min.js"></script>
<script src="../../assets/plugins/DataTables/extensions/Responsive/js/dataTables.responsive.min.js"></script>
<script>
(function(){
  var api='api/repo_api.php';
  function esc(v){return $('<div>').text(v==null?'':v).html();}
  function money(v){return Number(v||0).toFixed(2);}
  function renderSummary(rows){
    var amount=0,qty=0,items=0;
    rows.forEach(function(s){amount+=Number(s.amount||0);qty+=Number(s.item_qty||0);items+=Number(s.item_total||0);});
    $('#total-sales').text(rows.length);$('#total-qty').text(qty);$('#total-amount').text(money(amount));$('#total-items').text(money(items));$('#summary').show();
  }
  function renderSummaryTable(rows){
    if(!rows.length)return '<div class="alert alert-warning">No item summary for this period.</div>';
    var counters={},counterOrder=[];rows.forEach(function(i){var ck=i.counter_id+'|'+(i.counter_name||'Unknown counter');if(!counters[ck]){counters[ck]={name:i.counter_name||'Unknown counter',qty:0,total:0,categories:{},categoryOrder:[]};counterOrder.push(ck);}var counter=counters[ck],category=i.category_name||'Uncategorised';counter.qty+=Number(i.qty||0);counter.total+=Number(i.total||0);if(!counter.categories[category])counter.categories[category]={rows:[],qty:0,total:0,sales:0};counter.categories[category].rows.push(i);counter.categories[category].qty+=Number(i.qty||0);counter.categories[category].total+=Number(i.total||0);counter.categories[category].sales+=Number(i.sale_count||0);if(counter.categoryOrder.indexOf(category)<0)counter.categoryOrder.push(category);});
    var h='<div class="panel panel-default"><div class="panel-heading"><h4 class="panel-title">User Items Sale Summary</h4></div><div class="panel-body"><table id="items-summary-table" class="table table-striped table-bordered user-sale-table" width="100%"><thead><tr><th>Counter / Category / Item</th><th>Sales</th><th>Quantity</th><th>Total</th><th>Action</th></tr></thead><tbody>';
    counterOrder.forEach(function(ck){var counter=counters[ck];h+='<tr class="counter-row"><td>'+esc(counter.name)+'</td><td>COUNTER TOTAL</td><td>'+counter.qty+'</td><td>'+money(counter.total)+'</td><td></td></tr>';counter.categoryOrder.forEach(function(category){var g=counter.categories[category];h+='<tr class="category-row"><td>'+esc(category)+'</td><td>'+g.sales+'</td><td>'+g.qty+'</td><td>'+money(g.total)+'</td><td></td></tr>';g.rows.forEach(function(i){h+='<tr><td>&nbsp;&nbsp;&nbsp;'+esc(i.item_name)+'</td><td>'+i.sale_count+'</td><td>'+i.qty+'</td><td>'+money(i.total)+'</td><td><button type="button" class="btn btn-xs btn-primary item-orders" data-item="'+i.item_id+'">View orders</button></td></tr>';});});});
    return h+'</tbody></table></div></div>';
  }
  function renderTabs(sales,summary){
    return '<div class="report-tabs"><ul class="nav nav-tabs" role="tablist"><li class="active"><a href="#sales-report" data-toggle="tab"><i class="fa fa-line-chart"></i> User Sales</a></li><li><a href="#items-report" data-toggle="tab"><i class="fa fa-cubes"></i> Items Summary</a></li></ul><div class="tab-content"><div class="tab-pane active" id="sales-report">'+render(sales)+'</div><div class="tab-pane" id="items-report">'+renderSummaryTable(summary)+'</div></div></div>';
  }
  function render(rows){
    if(!rows.length){$('#summary').hide();return '<div class="alert alert-warning">No recorded sales.</div>';}
    var h='<div class="panel panel-default"><div class="panel-heading"><h4 class="panel-title">User Sales</h4></div><div class="panel-body"><table id="sales-table" class="table table-striped table-bordered user-sale-table" width="100%"><thead><tr><th>Sale ID</th><th>Date</th><th>Counter</th><th>Amount</th><th>Item Qty</th><th>Items Total</th><th>Action</th></tr></thead><tbody>';
    rows.forEach(function(s){var error=Math.abs(Number(s.amount||0)-Number(s.item_total||0))>0.009;h+='<tr class="'+(error?'danger sale-error':'')+'" title="'+(error?'Sale total does not match sale items total':'')+'"><td>'+s.sale_id+(error?' <i class="fa fa-warning text-danger"></i>':'')+'</td><td>'+esc(s.shift)+'</td><td>'+esc(s.counter_name)+'</td><td>'+money(s.amount)+'</td><td>'+s.item_qty+'</td><td>'+money(s.item_total)+'</td><td><button class="btn btn-xs btn-primary sale-items" data-sale="'+s.sale_id+'">View items</button></td></tr>';});
    return h+'</tbody></table></div></div>';
  }
  function load(){
    var u=$('#user').val(),counter=$('#counter').val(),f=$('#from').val(),t=$('#to').val();
    if(!u||!f||!t){$('#result').html('<div class="alert alert-warning">Select a user and both dates.</div>');return;}
    $('#result').html('<div class="alert alert-info">Loading sales...</div>');
    $.getJSON(api,{action:'user_sales',user_id:u,counter_id:counter,from:f,to:t}).done(function(r){
      if(!r.ok){$('#result').html('<div class="alert alert-danger">'+esc(r.message)+'</div>');return;}
      var rows=r.data.sales||[],summary=r.data.item_summary||[];renderSummary(rows);$('#result').html(renderTabs(rows,summary));
      if(rows.length)$('#sales-table').DataTable({responsive:true,pageLength:25,order:[[1,'desc']]});
      if(summary.length)$('#items-summary-table').DataTable({responsive:true,pageLength:25,ordering:false});
      $('#result a[data-toggle="tab"]').on('shown.bs.tab',function(){if($.fn.dataTable){$.fn.dataTable.tables({visible:true,api:true}).columns.adjust().responsive.recalc();}});
    }).fail(function(){$('#result').html('<div class="alert alert-danger">Could not load user sales.</div>');});
  }
  function iso(d){return d.toISOString().slice(0,10);}
  $('.quick-date').on('click',function(){var now=new Date(),start=new Date(now),range=$(this).data('range');if(range==='yesterday'){start.setDate(now.getDate()-1);now=new Date(start);}else if(range==='week'){start.setDate(now.getDate()-now.getDay());}else if(range==='month'){start=new Date(now.getFullYear(),now.getMonth(),1);}$('#from').val(iso(start));$('#to').val(iso(now));load();});
  $('#custom-date').on('click',function(){$('#custom-range').slideToggle(150);});
  $('#search').on('click',load);
  $('#counter').on('change',function(){if($('#from').val()&&$('#to').val())load();});
  $(document).on('click','.sale-items',function(){var id=$(this).data('sale');$('#sale-items-modal .modal-title').text('Sale #'+id+' items');$('#sale-items-modal-body').html('Loading items...');$('#sale-items-modal').modal('show');$.getJSON(api,{action:'sale_items',sale_id:id}).done(function(r){var h='<table class="table table-striped table-bordered"><thead><tr><th>Item</th><th>Category</th><th>Attribute</th><th>Qty</th><th>Price</th><th>Total</th></tr></thead><tbody>';(r.data.items||[]).forEach(function(i){h+='<tr><td>'+esc(i.item_name)+'</td><td>'+esc(i.category_name)+'</td><td>'+esc(i.attr_name)+'</td><td>'+i.qty+'</td><td>'+money(i.price)+'</td><td>'+money(i.total)+'</td></tr>';});h+='</tbody></table>';$('#sale-items-modal-body').html(h);}).fail(function(){$('#sale-items-modal-body').html('<div class="alert alert-danger">Could not load sale items.</div>');});});
  $(document).on('click','.item-orders',function(){var item=$(this).data('item'),user=$('#user').val(),counter=$('#counter').val(),from=$('#from').val(),to=$('#to').val();$('#sale-items-modal .modal-title').text('Orders containing this item');$('#sale-items-modal-body').html('Loading orders...');$('#sale-items-modal').modal('show');$.getJSON(api,{action:'item_orders',user_id:user,item_id:item,counter_id:counter,from:from,to:to}).done(function(r){if(!r.ok){$('#sale-items-modal-body').html('<div class="alert alert-danger">'+esc(r.message)+'</div>');return;}var orders=r.data.orders||[],h='<table class="table table-striped table-bordered"><thead><tr><th>Sale ID</th><th>Shift</th><th>Amount Total</th><th>Action</th></tr></thead><tbody>';if(!orders.length){h+='<tr><td colspan="4">No complete orders found.</td></tr>';}orders.forEach(function(o){h+='<tr><td>'+o.sale_id+'</td><td>'+esc(o.shift)+'</td><td>'+money(o.amount)+'</td><td><button type="button" class="btn btn-xs btn-primary sale-items" data-sale="'+o.sale_id+'">View items</button></td></tr>';});h+='</tbody></table>';$('#sale-items-modal-body').html(h);}).fail(function(){$('#sale-items-modal-body').html('<div class="alert alert-danger">Could not load orders.</div>');});});
})();
</script>
<script>
    $(document).ready(function() {
      App.init(); 


      if ($.fn.select2) {
        if ($('#user').hasClass('select2-hidden-accessible')) $('#user').select2('destroy');
        $('#user').select2({width: '100%', minimumResultsForSearch: 0});
      }
	}); 
 </script> 
</body></html>
