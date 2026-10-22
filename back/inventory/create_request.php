<?php
require("../clude/head.php");

$counter_id   = (int)($_GET['counter'] ?? 0);
$counter_name = $counter_id > 0 ? get_counter($counter_id) : '';
$shift        = trim($_GET['shift'] ?? date('Y-m-d') . ' Morning');
$requested_by = $_SESSION['username'] ?? 'KTOUCH_ADMIN';
$request_id   = (int)($_GET['req_id'] ?? 0);

if ($counter_id < 1 || $counter_name === '') {
    header('Location: counter_req.php');
    exit;
}

if ($request_id < 1) {
    $next_id_q = mysqli_query($connection, "SELECT COALESCE(MAX(req_id), 0) + 1 AS next_id FROM kp_counter_req");
    $next_id_row = $next_id_q ? mysqli_fetch_assoc($next_id_q) : null;
    $request_id = max(1, (int)($next_id_row['next_id'] ?? 1));
    $safe_shift = mysqli_real_escape_string($connection, $shift);
    $user_id = (int)($_SESSION['user_id'] ?? $_SESSION['mwene'] ?? 0);
    mysqli_query($connection, "INSERT INTO kp_counter_req(req_id,req_user,req_shift,counter,issue_status,rec_status) VALUES('$request_id','$user_id','$safe_shift','$counter_id',0,0)") or die(mysqli_error($connection));
}
?>

<div id="page-container" class="fade page-without-sidebar page-header-fixed page-with-top-menu cci-page">
  <?php require("../clude/header.php"); ?>
  <?php require("../clude/top_menu.php"); ?>

  <main id="content" class="content cci-content">
    <header class="cci-heading">
      <div class="cci-title-icon"><i class="fa fa-cube"></i></div>
      <div><h1>Create Counter Issue</h1><p>Select stock items and prepare an issue for a counter.</p></div>
    </header>

    <form id="counter-issue-form" method="post" action="save_counter_issue.php">
      <input type="hidden" name="counter" value="<?php echo $counter_id; ?>">
      <input type="hidden" name="shift" value="<?php echo htmlspecialchars($shift); ?>">
      <input type="hidden" name="priority" id="priority-value" value="normal">
      <input type="hidden" name="request_id" value="<?php echo htmlspecialchars($request_id); ?>">
      <input type="hidden" name="basket_json" id="basket-json" value="[]">

    <div class="cci-workspace">
      <div class="cci-work-left">
      <section class="cci-card cci-add-card">
      <h2>Add Item to Issue</h2>
      <div class="cci-add-grid">
        <label class="cci-item-field">Select Item
          <div class="cci-item-search"><span id="selected-item-name">Select an item</span><input id="item-search" autocomplete="off" placeholder="Search item, SKU or scan barcode..."><i class="fa fa-angle-down"></i></div>
          <div class="cci-results" id="item-results"></div>
        </label>
        <button type="button" class="cci-barcode" title="Scan barcode"><i class="fa fa-barcode"></i></button>
        <div class="cci-stock-box"><span>Available Store Stock</span><div><strong id="available-stock">0</strong> <b id="available-unit">Units</b> <em id="stock-state">Select item</em></div></div>
        <label>Issue Quantity<div class="cci-stepper"><button type="button" data-step="-1">−</button><input id="issue-qty" type="number" min="1" value="1"><button type="button" data-step="1">+</button></div></label>
        <label>Unit<input id="issue-unit" value="—" readonly></label>
        <button type="button" class="cci-add-btn" id="add-to-list"><i class="fa fa-plus"></i> Add to List</button>
      </div>
      <div class="cci-feedback"><span id="selection-feedback"><i class="fa fa-info-circle"></i> Select an item to see its available store stock.</span><span class="cci-warning" id="quantity-warning"><i class="fa fa-exclamation-triangle"></i> Quantity cannot exceed store stock.</span></div>
      </section>

      <section class="cci-card cci-basket-card">
        <div class="cci-basket-head"><h2>Items Added</h2><b class="cci-request-badge">#<?php echo htmlspecialchars($request_id); ?></b><span id="basket-count">0 items</span></div>
        <div class="cci-table-wrap">
          <table class="cci-table">
            <thead><tr><th>Item</th><th>Current Counter Stock</th><th>Available Store Stock</th><th>Issue Qty</th><th>Unit</th><th>Store After Issue</th><th>Action</th></tr></thead>
            <tbody id="basket-body"><tr class="cci-empty-row"><td colspan="6"><i class="fa fa-shopping-basket"></i><b>No items selected</b><span>Choose an item above, enter its quantity and click Add to List.</span></td></tr></tbody>
          </table>
        </div>
      </section>
      </div>

      <aside class="cci-card cci-side-summary">
        <div class="cci-side-head"><h2>Issue Summary</h2><b class="cci-request-badge">#<?php echo htmlspecialchars($request_id); ?></b></div>
        <div class="cci-summary-grid">
          <div class="cci-summary-tile"><i class="fa fa-building-o"></i><span>Counter<strong><?php echo htmlspecialchars($counter_name); ?></strong></span></div>
          <div class="cci-summary-tile"><i class="fa fa-user-o"></i><span>Requested By<strong><?php echo htmlspecialchars($requested_by); ?></strong></span></div>
          <div class="cci-summary-tile"><i class="fa fa-calendar-o"></i><span>Shift<strong><?php echo htmlspecialchars($shift); ?></strong></span></div>
          <div class="cci-summary-tile cci-priority-tile"><i class="fa fa-flag-o"></i><span>Priority<div class="cci-priority"><input id="priority-normal" type="radio" name="priority-view" value="normal" checked><label for="priority-normal">Normal</label><input id="priority-urgent" type="radio" name="priority-view" value="urgent"><label for="priority-urgent">Urgent</label></div></span></div>
        </div>
        <div class="cci-connected"><i class="fa fa-check-circle-o"></i> Store connected</div>
        <label class="cci-note"><i class="fa fa-pencil-square-o"></i> Issue Note <small>(optional)</small><textarea name="issue_note" placeholder="Add a note for the store or counter...">Stock for morning shift</textarea></label>
        <div class="cci-checks"><span id="check-stock"><i class="fa fa-check-circle"></i> All quantities available</span><span><i class="fa fa-check-circle"></i> Counter selected</span><span id="check-items"><i class="fa fa-circle-o"></i> No items added</span></div>
        <div class="cci-totals"><div><span>Different Items</span><strong id="total-items">0</strong></div><div><span>Total Units</span><strong id="total-units">0</strong></div></div>
        <p class="cci-pending-note"><i class="fa fa-info-circle"></i> The requisition will remain pending until stock is issued.</p>
        <div class="cci-action-row">
          <button type="submit" class="cci-btn cci-submit" id="submit-issue"><i class="fa fa-paper-plane"></i> Create &amp; Send for Issuing</button>
          <a href="counter_issues_dashboard.php" class="cci-btn cci-cancel"><i class="fa fa-trash-o"></i> Cancel Issue</a>
        </div>
        <button type="button" class="cci-btn cci-btn-light" id="save-draft-bottom"><i class="fa fa-floppy-o"></i> Save Draft</button>
      </aside>
    </div>
    </form>
  </main>
</div>

<style>
:root{--cci-purple:#5524bd;--cci-purple2:#6e35d3;--cci-navy:#111a42;--cci-muted:#68728d;--cci-line:#dce3ee;--cci-bg:#f6f8fc;--cci-green:#079947;--cci-red:#ff3c32}
body{background:var(--cci-bg)}#content.cci-content{margin-left:0!important;padding:12px 24px 18px!important;color:var(--cci-navy);min-height:calc(100vh - 100px)}
.cci-heading{height:76px;display:flex;align-items:center;gap:15px}.cci-title-icon{font-size:35px;color:var(--cci-purple)}.cci-heading h1{margin:0 0 4px;font-size:27px;font-weight:800}.cci-heading p{margin:0;color:var(--cci-muted);font-size:13px}.cci-heading-actions{margin-left:auto;display:flex;gap:14px}.cci-btn{height:44px;border-radius:5px;padding:0 20px;border:1px solid transparent;background:#fff;font-size:13px;font-weight:700;display:inline-flex;align-items:center;justify-content:center;gap:8px}.cci-btn-outline{border-color:var(--cci-purple);color:var(--cci-purple)!important}.cci-btn-light{border-color:#d6deea;color:#27314f}.cci-context{height:100px;background:#fff;border:1px solid var(--cci-line);border-radius:6px;display:grid;grid-template-columns:repeat(5,1fr) 170px;align-items:center;padding:0 22px;margin-bottom:16px;box-shadow:0 2px 7px rgba(20,30,60,.03)}.cci-context>div:not(.cci-connected){min-height:55px;border-right:1px solid #e3e8ef;padding:5px 36px 5px 0;margin-right:36px}.cci-context span{display:block;font-size:11px;color:#53617b;margin-bottom:9px}.cci-context strong{font-size:15px}.cci-priority{display:grid;grid-template-columns:1fr 1fr;width:195px}.cci-priority input{display:none}.cci-priority label{height:32px;border:1px solid #cfd8e6;display:flex;align-items:center;justify-content:center;font-size:11px;background:#fff;margin:0}.cci-priority label:first-of-type{border-radius:5px 0 0 5px}.cci-priority label:last-of-type{border-radius:0 5px 5px 0}.cci-priority input:checked+label{background:var(--cci-purple);border-color:var(--cci-purple);color:#fff}.cci-connected{height:36px;border:1px solid #31aa62;border-radius:5px;color:#08863d;background:#f7fff9;display:flex;align-items:center;justify-content:center;gap:8px;font-size:11px}.cci-connected i{font-size:19px}
.cci-card{background:#fff;border:1px solid var(--cci-line);border-radius:6px;box-shadow:0 2px 7px rgba(20,30,60,.03)}.cci-add-card{padding:14px 20px;margin-bottom:14px}.cci-card h2{font-size:16px;margin:0 0 12px;font-weight:800}.cci-add-grid{display:grid;grid-template-columns:minmax(400px,3fr) 56px 220px 180px 110px 190px;gap:18px;align-items:end}.cci-add-grid>label{position:relative;font-size:10px;font-weight:700;margin:0}.cci-add-grid input{width:100%;height:43px;border:1px solid #ced8e7;border-radius:5px;padding:8px 11px;font-size:12px;color:#27314f;background:#fff}.cci-item-search{height:43px;margin-top:5px;border:1px solid #ced8e7;border-radius:5px;display:grid;grid-template-columns:auto 1fr auto;align-items:center;overflow:hidden}.cci-item-search span{padding:0 12px;font-weight:700;white-space:nowrap}.cci-item-search input{border:0;border-left:1px solid #e1e6ee;border-radius:0;height:41px}.cci-item-search i{padding:0 12px}.cci-results{display:none;position:absolute;left:0;right:0;top:63px;z-index:30;background:#fff;border:1px solid #d7dfeb;border-radius:5px;box-shadow:0 12px 25px rgba(22,33,67,.16);max-height:230px;overflow:auto}.cci-results.show{display:block}.cci-result{padding:9px 12px;display:flex;justify-content:space-between;border-bottom:1px solid #edf0f5;cursor:pointer;font-size:11px}.cci-result:hover{background:#f7f3ff}.cci-result small{color:#72809a}.cci-barcode{height:43px;border:1px solid #ced8e7;border-radius:5px;background:#fff;font-size:22px}.cci-stock-box>span{font-size:10px;font-weight:700}.cci-stock-box>div{height:49px;margin-top:5px;background:#f5fff8;border:1px solid #b9dfc7;border-radius:5px;padding:6px 10px;display:flex;align-items:center;gap:6px}.cci-stock-box strong{font-size:21px}.cci-stock-box b{font-size:11px}.cci-stock-box em{margin-left:auto;padding:4px 7px;border-radius:4px;background:#e4f7e9;color:#078b41;font-size:9px;font-style:normal}.cci-stepper{display:grid;grid-template-columns:45px 1fr 45px;margin-top:5px}.cci-stepper button{height:43px;border:1px solid #ced8e7;background:#fff;font-size:18px}.cci-stepper input{border-radius:0;text-align:center;border-left:0;border-right:0}.cci-add-btn{height:47px;border:0;border-radius:5px;background:linear-gradient(135deg,var(--cci-purple),var(--cci-purple2));color:#fff;font-size:13px;font-weight:800}.cci-feedback{display:flex;justify-content:space-between;margin-top:10px;font-size:11px;color:var(--cci-green)}.cci-feedback i{margin-right:7px}.cci-warning{display:none;color:#f06000}.cci-warning.show{display:block}
.cci-basket-card{overflow:hidden}.cci-basket-head{height:55px;background:#fbf9ff;border-bottom:1px solid #d9cdf4;padding:0 20px;display:flex;align-items:center}.cci-basket-head h2{margin:0;color:#4421aa}.cci-basket-head span{margin-left:auto;background:#5220bd;color:#fff;border-radius:5px;padding:7px 13px;font-size:11px;font-weight:700}.cci-table-wrap{overflow-x:auto}.cci-table{width:100%;border-collapse:collapse}.cci-table th{height:40px;padding:8px 20px;text-align:left;background:#fff;border-bottom:1px solid #dfe5ee;color:#293451;font-size:10px}.cci-table td{height:48px;padding:7px 20px;border-bottom:1px solid #e2e7ee;font-size:11px;color:#202b4b}.cci-table tbody tr:nth-child(even){background:#fbfcfe}.cci-stock-good{color:var(--cci-green);margin-left:12px}.cci-stock-good:before{content:'';display:inline-block;width:7px;height:7px;border-radius:50%;background:var(--cci-green);margin-right:7px}.cci-row-stepper{display:grid;grid-template-columns:34px 44px 34px;width:112px}.cci-row-stepper button,.cci-row-stepper input{height:30px;border:1px solid #d5ddea;background:#fff;text-align:center}.cci-row-stepper input{border-left:0;border-right:0;width:44px}.cci-remove{width:37px;height:31px;border:1px solid #ffb7b3;border-radius:5px;background:#fff;color:var(--cci-red)}.cci-empty-row td{text-align:center!important;padding:28px!important;color:#7c879b}.cci-empty-row i,.cci-empty-row b,.cci-empty-row span{display:block}.cci-empty-row i{font-size:25px;margin-bottom:7px}.cci-empty-row span{font-size:10px;margin-top:3px}.cci-summary{display:grid;grid-template-columns:1.1fr .75fr 1.3fr;gap:30px;padding:17px 20px}.cci-note{font-size:10px;font-weight:700}.cci-note small{font-weight:400}.cci-note textarea{display:block;width:100%;height:68px;margin-top:6px;border:1px solid #ccd7e6;border-radius:5px;padding:10px;font-size:11px}.cci-checks{border:1px solid #aee0c1;background:#f6fff9;border-radius:5px;padding:11px 16px}.cci-checks span{display:block;color:var(--cci-green);font-size:11px;margin:4px}.cci-checks i{margin-right:8px}.cci-totals{border:1px solid #dbe3ed;border-radius:5px}.cci-totals div{height:50%;padding:0 16px;display:flex;align-items:center;border-bottom:1px solid #e5e9ef}.cci-totals div:last-child{border:0}.cci-totals span{font-size:11px}.cci-totals strong{margin-left:auto;color:var(--cci-purple);font-size:17px}.cci-basket-footer{padding:0 20px 16px;display:flex;align-items:center;gap:18px}.cci-basket-footer p{margin:0 auto 0 0;color:#69758d;font-size:10px}.cci-clear{border-color:#ff6b64;color:#e83027}.cci-submit{background:linear-gradient(135deg,#08a047,#1eaf51);color:#fff;min-width:285px}.cci-submit:disabled{opacity:.55;cursor:not-allowed}
@media(max-width:1200px){.cci-add-grid{grid-template-columns:2fr 50px 1fr 1fr 100px 160px}.cci-context{grid-template-columns:repeat(3,1fr)}.cci-context>div{border:0!important}.cci-summary{grid-template-columns:1fr 1fr}.cci-totals{grid-column:1/-1}}
@media(max-width:767px){#content.cci-content{padding:8px!important}.cci-heading{height:auto;padding:10px 0}.cci-heading p{display:none}.cci-heading-actions .cci-btn-light{display:none}.cci-context{height:auto;grid-template-columns:1fr 1fr;padding:12px}.cci-add-grid{grid-template-columns:1fr}.cci-barcode{display:none}.cci-summary{grid-template-columns:1fr}.cci-basket-footer{flex-wrap:wrap}.cci-basket-footer p{width:100%}.cci-submit{min-width:100%}}

/* Final compact purple two-column layout */
:root{--cci-purple:#6f2f96;--cci-purple2:#873cab;--cci-purple-soft:#f8f2fb}
#content.cci-content{padding:8px 22px 14px!important}.cci-heading{height:60px}.cci-title-icon{font-size:28px;color:var(--cci-purple)}.cci-heading h1{font-size:19px;margin-bottom:2px}.cci-heading p{font-size:10px}
.cci-workspace{display:grid;grid-template-columns:minmax(0,2.35fr) minmax(330px,1fr);gap:14px;align-items:start}.cci-work-left{min-width:0}.cci-add-card{height:156px;padding:15px 16px;margin-bottom:12px;border-top:3px solid var(--cci-purple)}.cci-add-card h2{color:#32154e}.cci-add-grid{grid-template-columns:minmax(260px,2.2fr) 46px minmax(175px,1.2fr) 140px 105px 125px;gap:10px}.cci-add-grid input,.cci-item-search,.cci-stepper button{height:38px}.cci-item-search input{height:36px}.cci-barcode{height:38px}.cci-stock-box>div{height:44px}.cci-add-btn{height:42px;background:linear-gradient(135deg,var(--cci-purple),var(--cci-purple2))}.cci-feedback{font-size:9px;margin-top:8px}
.cci-basket-card{border-top:3px solid var(--cci-purple)}.cci-basket-head{height:45px;padding:0 15px;background:var(--cci-purple-soft)}.cci-basket-head h2{font-size:15px;color:var(--cci-purple)}.cci-request-badge{margin-left:12px;border:1px solid #cdaee0;border-radius:4px;padding:4px 7px;color:var(--cci-purple);background:#fff;font-size:9px}.cci-basket-head span{padding:5px 10px;background:var(--cci-purple)}.cci-table th{height:35px;padding:6px 14px;font-size:9px;background:#fcfbfd}.cci-table td{height:44px;padding:6px 14px;font-size:10px}.cci-row-stepper{grid-template-columns:30px 38px 30px;width:98px}.cci-row-stepper button,.cci-row-stepper input{height:27px}.cci-row-stepper input{width:38px}.cci-remove{width:32px;height:28px}
.cci-side-summary{padding:14px;border-top:3px solid var(--cci-purple);position:sticky;top:108px}.cci-side-head{height:31px;display:flex;align-items:flex-start;border-bottom:1px solid #eadff0;margin:-1px -1px 10px;padding:0 1px}.cci-side-head h2{font-size:15px;color:var(--cci-purple);margin:0}.cci-side-head .cci-request-badge{margin-left:auto}.cci-summary-grid{display:grid;grid-template-columns:1fr 1fr;gap:6px}.cci-summary-tile{min-height:66px;border:1px solid #e2d8e9;border-radius:5px;background:#fff;display:flex;align-items:center;gap:10px;padding:9px}.cci-summary-tile>i{font-size:19px;color:var(--cci-purple);width:21px;text-align:center}.cci-summary-tile>span{display:block;color:#6b5876;font-size:8.5px;min-width:0}.cci-summary-tile strong{display:block;color:#21102f;font-size:11px;margin-top:4px;white-space:nowrap;overflow:hidden;text-overflow:ellipsis}.cci-priority-tile span{width:100%}.cci-priority{width:100%;margin-top:4px}.cci-priority label{height:28px;font-size:9px}.cci-priority input:checked+label{background:var(--cci-purple);border-color:var(--cci-purple)}.cci-connected{height:36px;margin:10px 0;background:#f5fff8}.cci-note{display:block;font-size:9px;margin-bottom:10px;color:#4f315e}.cci-note textarea{height:52px;margin-top:5px}.cci-checks{display:flex;align-items:center;justify-content:space-between;gap:5px;padding:8px;background:#f5fff8}.cci-checks span{font-size:8px;white-space:nowrap;margin:0}.cci-checks i{margin-right:4px}.cci-totals{height:64px;display:grid;grid-template-columns:1fr 1fr;margin-top:10px}.cci-totals div{height:64px;display:flex;flex-direction:column;justify-content:center;text-align:center;padding:0;border:0;border-right:1px solid #e5dbea}.cci-totals div:last-child{border:0}.cci-totals span{font-size:9px}.cci-totals strong{margin:4px 0 0;font-size:20px;color:var(--cci-purple)}.cci-pending-note{font-size:8.5px;color:#6b6174;margin:10px 2px}.cci-side-summary>.cci-btn{width:100%;height:40px;margin-top:7px;font-size:11px}.cci-submit{min-width:0;background:linear-gradient(135deg,#0a9846,#17ad51)}.cci-cancel{border-color:#ed5149!important;color:#dc3129!important;background:#fff}.cci-side-summary>.cci-btn-light{border-color:#af8ac4;color:#4c2462}.cci-empty-row td{height:150px}
@media(max-width:1100px){.cci-workspace{grid-template-columns:1fr}.cci-side-summary{position:static;order:-1}.cci-add-grid{grid-template-columns:2fr 44px 1fr 1fr 90px 120px}.cci-summary-grid{grid-template-columns:repeat(4,1fr)}}
@media(max-width:767px){.cci-summary-grid{grid-template-columns:1fr 1fr}.cci-add-card{height:auto}.cci-add-grid{grid-template-columns:1fr}.cci-side-summary{padding:10px}.cci-checks{flex-wrap:wrap}.cci-workspace{display:flex;flex-direction:column}.cci-work-left,.cci-side-summary{width:100%}}
/* Keep the primary issue actions together and balanced. */
.cci-action-row{display:flex;align-items:stretch;gap:10px;margin-top:10px}
.cci-action-row .cci-btn{flex:1;min-width:0}
@media(max-width:767px){.cci-action-row{gap:8px}.cci-action-row .cci-btn{font-size:10px;padding-left:8px;padding-right:8px}}
</style>

<script src="../../assets/plugins/jquery/jquery-3.2.1.min.js"></script>
<script src="../../assets/plugins/jquery-ui/jquery-ui.min.js"></script>
<script src="../../assets/plugins/bootstrap/4.0.0/js/bootstrap.bundle.min.js"></script>
<script src="../../assets/plugins/slimscroll/jquery.slimscroll.min.js"></script>
<script src="../../assets/plugins/js-cookie/js.cookie.js"></script>
<script src="../../assets/js/theme/default.min.js"></script>
<script src="../../assets/js/apps.min.js"></script>
<script>
$(function(){
  if(window.App) App.init();

  var items=[],selected=null,basket=[];
  var apiUrl='API/counter_issue_api.php', requestId=<?php echo (int)$request_id; ?>, counterId=<?php echo (int)$counter_id; ?>;

  function esc(v){return $('<div>').text(v).html();}
  function searchItems(q){q=(q||'').toLowerCase();return items.filter(function(i){return i.name.toLowerCase().indexOf(q)>-1||String(i.sku||'').toLowerCase().indexOf(q)>-1;});}
  function showResults(){var found=searchItems($('#item-search').val()),html='';found.forEach(function(i){html+='<div class="cci-result" data-id="'+i.id+'"><b>'+esc(i.name)+'</b><small>Counter: '+Number(i.counter_stock||0)+' · Store: '+Number(i.stock||0)+' '+esc(i.unit||'Units')+'</small></div>';});$('#item-results').html(html||'<div class="cci-result"><small>No matching store item</small></div>').addClass('show');}
  var searchTimer;
  $('#item-search').on('focus',showResults).on('input',function(){
    var query=$(this).val();
    clearTimeout(searchTimer);
    searchTimer=setTimeout(function(){
      $.getJSON(apiUrl,{action:'search',q:query,counter:counterId}).done(function(res){if(res&&res.ok){items=res.items||[];showResults();}});
    },150);
  });
  $(document).on('click','.cci-result[data-id]',function(){selected=items.find(function(i){return i.id==$(this).data('id');}.bind(this));$('#selected-item-name').text(selected.name);$('#available-stock').text(selected.stock);$('#available-unit').text(selected.unit+(selected.stock===1?'':'s'));$('#issue-unit').val(selected.unit);$('#stock-state').text(selected.stock>0?'In Stock':'Out of Stock');$('#selection-feedback').html('<i class="fa fa-check-circle"></i> '+esc(selected.name)+' selected — '+selected.stock+' '+esc(selected.unit.toLowerCase())+' available in store.');$('#item-results').removeClass('show');validateQty();});
  $(document).on('click',function(e){if(!$(e.target).closest('.cci-item-field').length)$('#item-results').removeClass('show');});
  $('[data-step]').on('click',function(){var q=Math.max(1,(parseInt($('#issue-qty').val(),10)||1)+parseInt($(this).data('step'),10));$('#issue-qty').val(q);validateQty();});
  $('#issue-qty').on('input',validateQty);
  function validateQty(){var q=Math.max(1,parseInt($('#issue-qty').val(),10)||1),bad=!selected||q>selected.stock;$('#quantity-warning').toggleClass('show',!!selected&&q>selected.stock);return !!selected&&q>0;}
  $('#add-to-list').on('click',function(){if(!selected){$('#item-search').focus();return;}var q=parseInt($('#issue-qty').val(),10)||1,existing=basket.find(function(x){return x.id===selected.id;});if(existing)existing.qty+=q;else basket.push($.extend({},selected,{qty:q}));render();});
  function render(){var html='',units=0,valid=true;basket.forEach(function(i){units+=i.qty;var short=i.qty>i.stock;if(short)valid=false;html+='<tr data-id="'+i.id+'"><td><b>'+esc(i.name)+'</b></td><td><b>'+Number(i.counter_stock||0).toLocaleString()+'</b></td><td>'+i.stock+' <span class="'+(short?'text-danger':'cci-stock-good')+'">'+(short?'Insufficient':'In Stock')+'</span></td><td><div class="cci-row-stepper"><button type="button" data-row-step="-1">−</button><input value="'+i.qty+'" type="number" min="1"><button type="button" data-row-step="1">+</button></div></td><td>'+esc(i.unit)+'</td><td><b class="'+(short?'text-danger':'')+'">'+(i.stock-i.qty)+'</b></td><td>'+(short?'<button type="button" class="btn btn-warning btn-xs cci-adjust" title="Adjust store stock">Adjust</button> ':'')+'<button type="button" class="cci-remove"><i class="fa fa-trash-o"></i></button></td></tr>';});if(!basket.length)html='<tr class="cci-empty-row"><td colspan="7"><i class="fa fa-shopping-basket"></i><b>No items selected</b><span>Choose an item above, enter its quantity and click Add to List.</span></td></tr>';$('#basket-body').html(html);$('#basket-count').text(basket.length+' item'+(basket.length===1?'':'s'));$('#total-items').text(basket.length);$('#total-units').text(units);$('#check-items').html('<i class="fa '+(basket.length?'fa-check-circle':'fa-circle-o')+'"></i> '+(basket.length?basket.length+' items ready':'No items added'));$('#check-stock').toggleClass('bad',!valid).html('<i class="fa '+(valid?'fa-check-circle':'fa-exclamation-triangle')+'"></i> '+(valid?'All quantities available':'Adjust insufficient stock'));$('#basket-json').val(JSON.stringify(basket));$('#submit-issue').prop('disabled',!basket.length||!valid);}
  $(document).on('click','[data-row-step]',function(){var id=parseInt($(this).closest('tr').data('id'),10),item=basket.find(function(x){return x.id===id;}),next=Math.max(1,item.qty+parseInt($(this).data('row-step'),10));item.qty=next;render();});
  $(document).on('change','.cci-row-stepper input',function(){var id=parseInt($(this).closest('tr').data('id'),10),item=basket.find(function(x){return x.id===id;}),q=parseInt(this.value,10)||1;item.qty=Math.max(1,q);render();});
  $(document).on('click','.cci-adjust',function(){var id=parseInt($(this).closest('tr').data('id'),10),item=basket.find(function(x){return x.id===id;});if(!item)return;var next=prompt('Enter the corrected store stock for '+item.name,item.stock);if(next===null||next===''||isNaN(next)||Number(next)<item.qty)return alert('Enter stock equal to or greater than the requested quantity.');$.post('API/inventory_api.php',{action:'adjust_store_qty',item_id:id,qty:Number(next),init:item.stock,reason:'Counter issue adjustment'},function(res){if(!res||!res.ok)return alert(res&&res.message?res.message:'Could not adjust stock.');item.stock=Number(next);render();},'json').fail(function(){alert('Could not adjust stock.');});});
  $(document).on('click','.cci-remove',function(){var id=parseInt($(this).closest('tr').data('id'),10);basket=basket.filter(function(x){return x.id!==id;});render();});
  $('#clear-list').on('click',function(){if(window.confirm('Clear all selected items?')){basket=[];render();}});
  $('input[name="priority-view"]').on('change',function(){$('#priority-value').val(this.value);});
  function saveDraft(){$('#basket-json').val(JSON.stringify(basket));alert('Draft saved. The requisition can be completed from this page.');}
  $('#save-draft-top,#save-draft-bottom').on('click',saveDraft);
  $('#counter-issue-form').on('submit',function(e){e.preventDefault();if(!basket.length){alert('Add at least one item before creating the requisition.');return;}if(basket.some(function(i){return i.qty>i.stock;})){alert('Adjust all insufficient stock before submitting.');return;}var form=$(this),button=$('#submit-issue');button.prop('disabled',true).html('<i class="fa fa-spinner fa-spin"></i> Issuing stock...');$.ajax({url:form.attr('action'),type:'POST',data:form.serialize(),dataType:'json'}).done(function(res){if(res&&res.ok){window.location.href=res.redirect||'counter_req.php?msg=1';return;}alert(res&&res.message?res.message:'Could not complete the counter issue.');button.prop('disabled',false).html('<i class="fa fa-paper-plane"></i> Create &amp; Send for Issuing');}).fail(function(xhr){var res=xhr.responseJSON;alert(res&&res.message?res.message:'Could not complete the counter issue.');button.prop('disabled',false).html('<i class="fa fa-paper-plane"></i> Create &amp; Send for Issuing');});});
  $.getJSON(apiUrl,{action:'search',q:'',counter:counterId}).done(function(res){if(res&&res.ok){items=res.items||[];}}).always(function(){render();});
});
</script>
</body>
</html>
