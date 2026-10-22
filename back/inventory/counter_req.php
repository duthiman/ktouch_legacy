<?php
require("../clude/head.php");

$no = 0;
function counter_req_count($connection, $where = '1=1') {
    $result = mysqli_query($connection, "SELECT COUNT(*) AS total FROM kp_counter_req WHERE $where");
    $row = $result ? mysqli_fetch_assoc($result) : ['total' => 0];
    return (int)($row['total'] ?? 0);
}
$total_requests = counter_req_count($connection);
$pending_requests = counter_req_count($connection, 'issue_status=2');
$issued_requests = counter_req_count($connection, 'issue_status=3');
$received_requests = counter_req_count($connection, 'issue_status=4');
$pending_receive_requests = counter_req_count($connection, 'issue_status=3 AND (rec_status IS NULL OR rec_status<>4)');
$pending_po = mysqli_query($connection, "SELECT * FROM kp_counter_req WHERE issue_status=2 OR (issue_status=3 AND (rec_status IS NULL OR rec_status<>4)) ORDER BY req_id DESC");
$open_counters = [];
$open_counter_result = mysqli_query($connection, "SELECT cntr_id,name FROM kp_counters WHERE del_status=2 AND counter_status=1 ORDER BY name ASC");
while ($open_counter_result && ($open_counter = mysqli_fetch_assoc($open_counter_result))) $open_counters[] = $open_counter;
$open_counter_ids = array_map('intval', array_column($open_counters, 'cntr_id'));
$open_shifts = [];
$open_shift_result = mysqli_query($connection, "SELECT shift FROM kp_shift ORDER BY shift ASC");
while ($open_shift_result && ($open_shift = mysqli_fetch_assoc($open_shift_result))) $open_shifts[] = (string)$open_shift['shift'];
$can_create_issue = count($open_counters) > 0 && count($open_shifts) > 0;
$activity = [];
$activity_result = mysqli_query($connection, "SELECT c.cntr_id,c.name,COUNT(r.req_id) AS requested,SUM(CASE WHEN r.issue_status=3 THEN 1 ELSE 0 END) AS issued FROM kp_counters c LEFT JOIN kp_counter_req r ON r.counter=c.cntr_id GROUP BY c.cntr_id,c.name ORDER BY c.name ASC");
while ($activity_result && ($activity_row = mysqli_fetch_assoc($activity_result))) $activity[] = $activity_row;
$recent_pending = [];
$recent_pending_result = mysqli_query($connection, "SELECT req_id,counter,req_shift,issue_status,rec_status FROM kp_counter_req WHERE issue_status=2 OR (issue_status=3 AND (rec_status IS NULL OR rec_status<>4)) ORDER BY req_id DESC LIMIT 5");
while ($recent_pending_result && ($recent_row = mysqli_fetch_assoc($recent_pending_result))) $recent_pending[] = $recent_row;
?>

<div id="page-container" class="fade page-without-sidebar page-header-fixed page-with-top-menu counter-issues-page">
  <?php require("../clude/header.php"); ?>
  <?php require("../clude/top_menu.php"); ?>

  <div id="content" class="content ci-content">
    <header class="ci-page-head">
      <div class="ci-head-icon"><i class="fa fa-building"></i></div>
      <div>
        <h1>Counter Issues</h1>
        <p>Create, issue and track stock requests across every counter.</p>
      </div>
      <div class="ci-head-actions">
        <button type="button" class="btn ci-btn-primary" data-toggle="modal" data-target="#new-counter-issue" <?php echo $can_create_issue ? '' : 'disabled title="No open counter or shift"'; ?>><i class="fa fa-plus"></i> New Counter Issue</button>
        <button class="btn ci-btn-light" onclick="window.location.reload()"><i class="fa fa-refresh"></i> Refresh</button>
      </div>
    </header>

    <section class="ci-kpis">
      <article class="ci-kpi kpi-blue"><div class="ci-kpi-icon"><i class="fa fa-file-text-o"></i></div><div><span>Total Requests</span><strong><?php echo $total_requests; ?></strong><small>This month</small></div></article>
      <article class="ci-kpi kpi-amber"><div class="ci-kpi-icon"><i class="fa fa-hourglass-half"></i></div><div><span>Pending</span><strong><?php echo $pending_requests; ?></strong><small>Needs issuing</small></div></article>
      <article class="ci-kpi kpi-purple"><div class="ci-kpi-icon"><i class="fa fa-cube"></i></div><div><span>Issued</span><strong><?php echo $issued_requests; ?></strong><small>Awaiting receipt</small></div></article>
      <article class="ci-kpi kpi-green"><div class="ci-kpi-icon"><i class="fa fa-check-circle-o"></i></div><div><span>Received</span><strong><?php echo $received_requests; ?></strong><small>Completed successfully</small></div></article>
      <article class="ci-kpi kpi-teal"><div class="ci-kpi-icon"><i class="fa fa-clock-o"></i></div><div><span>Average Issue Time</span><strong>18 min</strong><small class="ci-good"><i class="fa fa-arrow-down"></i> 6 min vs last month</small></div></article>
    </section>

    <section class="ci-overview-grid">
      <article class="ci-card ci-chart-card">
        <div class="ci-card-title"><h2>Issue Activity by Counter</h2><div class="ci-legend"><span><i class="dot blue"></i>Requested</span><span><i class="dot purple"></i>Issued</span></div></div>
        <div class="ci-chart ci-counter-chart">
          <?php $chart_max=1; foreach($activity as $a) $chart_max=max($chart_max,(int)$a['requested'],(int)$a['issued']); if(!$activity) echo '<div class="ci-chart-empty">No request activity yet.</div>'; foreach($activity as $a){$requested=(int)$a['requested'];$issued=(int)$a['issued']; ?>
            <div class="ci-column-group"><div class="ci-column-bars"><i class="ci-request-bar" style="height:<?php echo round(($requested/$chart_max)*100); ?>%" title="Requested: <?php echo $requested; ?>"></i><i class="ci-issued-bar" style="height:<?php echo round(($issued/$chart_max)*100); ?>%" title="Issued: <?php echo $issued; ?>"></i></div><b class="ci-column-name"><?php echo htmlspecialchars($a['name']); ?></b><span class="ci-column-values"><?php echo $requested; ?> / <?php echo $issued; ?></span></div>
          <?php } ?>
        </div>
      </article>

      <article class="ci-card ci-pending-card">
        <div class="ci-pending-head"><div><h2>Pending Counter Issues</h2><p>Requests requiring action</p></div><i class="fa fa-exchange"></i></div>
        <div class="ci-pending-stats"><div><strong><?php echo $pending_requests; ?></strong><span>Pending issue</span></div><div><strong><?php echo $pending_receive_requests; ?></strong><span>Awaiting receipt</span></div></div>
        <div class="ci-pending-list">
          <?php if (!$recent_pending) { ?><div class="ci-pending-empty">No pending counter issues.</div><?php } else { foreach ($recent_pending as $recent) { $recent_req_id=(int)$recent['req_id']; $recent_counter=(int)$recent['counter']; $recent_status = (int)$recent['issue_status'] === 2 ? 'Pending issue' : 'Awaiting receipt'; ?><div class="ci-pending-item"><b>#<?php echo $recent_req_id; ?></b><span><?php echo htmlspecialchars(get_counter($recent_counter)); ?></span><em><?php echo $recent_status; ?></em><?php if ((int)$recent['issue_status'] === 2) { ?><button type="button" class="ci-pending-action" onclick="window.location.href='req_view.php?req_id=<?php echo $recent_req_id; ?>&cntr=<?php echo $recent_counter; ?>'">Issue</button><?php } else { ?><button type="button" class="ci-pending-action" onclick="window.location.href='../reports/req_items.php?req_id=<?php echo $recent_req_id; ?>'">View items</button><?php } ?></div><?php } } ?>
        </div>
        <button type="button" class="ci-view-all-btn" onclick="document.getElementById('dataTables-example').scrollIntoView({behavior:'smooth'});">View recent issues <i class="fa fa-arrow-down"></i></button>
      </article>
    </section>

    <section class="ci-card ci-table-card">
      <div class="ci-table-top">
        <div><h2>Recent Counter Issues</h2><div class="ci-tabs"><button class="active" data-status="all">All Recent (<?php echo $pending_requests + $pending_receive_requests; ?>)</button><button data-status="pending">Pending Issue (<?php echo $pending_requests; ?>)</button><button data-status="issued">Awaiting Receipt (<?php echo $pending_receive_requests; ?>)</button></div></div>
        <div class="ci-table-tools"><select id="ci-counter-filter"><option value="">All Counters</option></select><input type="date" id="ci-date-filter"><div class="ci-search"><i class="fa fa-search"></i><input id="ci-search" placeholder="Search requisition no. or requested by..."></div><a href="../reports/counter_req.php" class="ci-table-report-link"><i class="fa fa-bar-chart"></i> All Reports</a><button onclick="window.print()"><i class="fa fa-download"></i> Export</button></div>
      </div>

      <?php if ($pending_requests > 0) { ?><div class="ci-alert"><span><i class="fa fa-bell"></i> <b><?php echo $pending_requests; ?> requests</b> are waiting to be issued</span><button id="show-pending">View pending only <i class="fa fa-arrow-right"></i></button></div><?php } ?>

      <div class="ci-table-wrap">
        <table id="dataTables-example" class="table ci-table">
          <thead><tr><th>Requisition No.</th><th>Requested By</th><th>Counter</th><th>Shift / Date</th><th>Items</th><th>Status</th><th>Waiting Time</th><th>Action</th></tr></thead>
          <tbody>
          <?php
          if (!$pending_po || mysqli_num_rows($pending_po) < 1) {
              echo '<tr><td colspan="8" class="ci-empty">There are no counter requisitions at the moment.</td></tr>';
          } else {
              while ($row = mysqli_fetch_assoc($pending_po)) {
                  $req_id = (int)$row['req_id'];
                  $staff_id = (int)$row['req_user'];
                  $counter = (int)$row['counter'];
                  $shift = htmlspecialchars($row['req_shift']);
                  $requester = htmlspecialchars(get_user($staff_id));
                  $counter_name = htmlspecialchars(get_counter($counter));
                  $no++;
                  $issue_state = (int)($row['issue_status'] ?? 0);
                  $rec_state = (int)($row['rec_status'] ?? 0);
                  $item_count_q = mysqli_query($connection, "SELECT COUNT(*) AS total FROM kp_counter_req_items WHERE req_id='$req_id'");
                  $item_count_row = $item_count_q ? mysqli_fetch_assoc($item_count_q) : null;
                  $item_count = (int)($item_count_row['total'] ?? 0);
                  $status = $issue_state === 2 ? 'pending' : (($issue_state === 3) ? 'issued' : (($issue_state === 4 || $rec_state === 4) ? 'received' : 'draft'));
                  $status_label = ucfirst($status);
                  echo '<tr data-status="'.$status.'" data-counter="'.$counter.'">';
                  echo '<td><b>'.$req_id.'</b></td><td>'.$requester.'</td><td>'.$counter_name.'</td><td>'.$shift.'</td><td>'.$item_count.'</td>';
                  echo '<td><span class="ci-status '.$status.'">'.$status_label.'</span></td><td class="ci-wait">'.($status === 'pending' ? 'Waiting' : 'Completed').'</td>';
                  if ($status === 'pending') {
                      $request_can_issue = in_array($counter, $open_counter_ids, true) && in_array((string)$row['req_shift'], $open_shifts, true);
                      echo $request_can_issue ? '<td><button class="ci-issue-btn counter-issue-trigger" data-req-id="'.$req_id.'" data-counter="'.$counter.'">Issue Stock</button></td>' : '<td><button class="ci-issue-btn" disabled title="Counter or shift is not open">Unavailable</button></td>';
                  } else {
                      echo '<td><a class="ci-issue-btn" href="../reports/req_items.php?req_id='.$req_id.'">View</a></td>';
                  }
                  echo '</tr>';
              }
          }
          ?>
          </tbody>
        </table>
      </div>
    </section>
  </div>

  <div id="new-counter-issue" class="modal fade" tabindex="-1" role="dialog" aria-hidden="true">
    <div class="modal-dialog modal-lg" role="document"><div class="modal-content ci-modal"><div class="modal-header"><button type="button" class="close" data-dismiss="modal"><span>&times;</span></button><h4 class="modal-title">New Counter Issue</h4></div><div class="modal-body" id="new-issue-modal-body">
      <article class="ci-card ci-create-card" id="new-issue-card">
        <h2>Create New Counter Issue</h2>
        <form action="create_request.php" method="get" id="create-counter-issue-form">
          <div class="ci-form-grid">
            <label>Counter<select name="counter" id="create-counter-select" required><option value="">Select open counter</option><?php foreach ($open_counters as $c) echo '<option value="'.(int)$c['cntr_id'].'">'.htmlspecialchars($c['name']).'</option>'; ?></select></label>
            <label>Shift<select name="shift" required><option value="">Select open shift</option><?php foreach ($open_shifts as $open_shift) echo '<option value="'.htmlspecialchars($open_shift).'">'.htmlspecialchars($open_shift).'</option>'; ?></select></label>
            <label>Requested By<input value="<?php echo htmlspecialchars($_SESSION['username'] ?? 'KTOUCH_ADMIN'); ?>" readonly></label>
            <label>Priority<div class="ci-priority"><input type="radio" id="normal" name="priority" value="normal" checked><label for="normal">Normal</label><input type="radio" id="urgent" name="priority" value="urgent"><label for="urgent">Urgent</label></div></label>
          </div>
          <button class="ci-start-btn" type="submit">Start New Issue</button>
          <p class="ci-form-note"><i class="fa fa-info-circle"></i> A requisition number will be generated automatically.</p>
        </form>
      </article>
    </div></div></div>
  </div>
  <div id="counter-issue-confirm" class="modal fade" tabindex="-1" role="dialog" aria-hidden="true">
    <div class="modal-dialog modal-sm" role="document"><div class="modal-content ci-modal"><div class="modal-header"><button type="button" class="close" data-dismiss="modal"><span>&times;</span></button><h4 class="modal-title">Issue Counter</h4></div><div class="modal-body"><div class="ci-modal-icon"><i class="fa fa-cubes"></i></div><h4>Open this requisition?</h4><p>The requisition details will open so you can verify quantities and issue the stock.</p></div><div class="modal-footer"><button class="btn btn-default" data-dismiss="modal">Cancel</button><button class="btn ci-btn-primary" id="confirm-counter-issue">Yes, Continue</button></div></div></div>
  </div>
</div>

<style>
:root{--ci-purple:#752d9b;--ci-purple2:#8e3db1;--ci-navy:#17263f;--ci-muted:#68758a;--ci-line:#dde4ed;--ci-bg:#f6f8fb}
body{background:var(--ci-bg)}#content.ci-content{margin-left:0!important;padding:10px 18px 16px!important;min-height:calc(100vh - 100px);color:var(--ci-navy)}
.ci-page-head{height:64px;margin:-10px -18px 10px;padding:10px 18px;display:flex;align-items:center;gap:13px;background:#fff;border-bottom:1px solid var(--ci-line)}.ci-head-icon{width:46px;height:46px;border-radius:5px;background:#f1e4f7;color:var(--ci-purple);display:flex;align-items:center;justify-content:center;font-size:25px}.ci-page-head h1{font-size:19px;margin:0 0 3px;font-weight:800}.ci-page-head p{margin:0;color:var(--ci-muted);font-size:11px}.ci-head-actions{margin-left:auto;display:flex;gap:10px}.ci-head-actions .btn{height:36px;padding:0 17px;display:flex;align-items:center;gap:7px;border-radius:5px;font-size:11px;font-weight:700}.ci-btn-primary{background:linear-gradient(135deg,var(--ci-purple),var(--ci-purple2));color:#fff!important;border:0}.ci-btn-light{background:#fff;border:1px solid #d7dfe9;color:#263750}
.ci-kpis{display:grid;grid-template-columns:repeat(5,minmax(0,1fr));gap:14px;margin-bottom:10px}.ci-kpi{height:92px;background:#fff;border:1px solid var(--ci-line);border-radius:6px;padding:14px 16px;display:flex;align-items:center;gap:17px;box-shadow:0 2px 7px rgba(20,38,65,.025)}.ci-kpi-icon{width:50px;height:50px;border-radius:14px;display:flex;align-items:center;justify-content:center;font-size:24px}.ci-kpi span,.ci-kpi small{display:block}.ci-kpi span{font-size:11px;font-weight:700}.ci-kpi strong{display:block;font-size:23px;line-height:1.15;margin:2px 0 3px}.ci-kpi small{font-size:10px;color:var(--ci-muted)}.kpi-blue .ci-kpi-icon{background:#e8f1ff;color:#1f70e8}.kpi-amber{background:#fffcf7}.kpi-amber .ci-kpi-icon{background:#fff0d5;color:#f09400}.kpi-purple .ci-kpi-icon{background:#f3e9f8;color:#8a2fc0}.kpi-green{background:#fafffb}.kpi-green .ci-kpi-icon{background:#e6f5e8;color:#26823b}.kpi-teal .ci-kpi-icon{background:#e2f6f7;color:#139aa4}.ci-good{color:#00a349!important}
.ci-overview-grid{display:grid;grid-template-columns:3fr 2fr;gap:14px;margin-bottom:10px}.ci-card{background:#fff;border:1px solid var(--ci-line);border-radius:6px;box-shadow:0 2px 7px rgba(20,38,65,.025)}.ci-chart-card,.ci-create-card{height:218px;padding:12px 16px}.ci-card-title{display:flex;align-items:center;height:28px}.ci-card h2{font-size:14px;margin:0;font-weight:800}.ci-card-title select{margin-left:auto;height:28px;border:1px solid #d9e1eb;border-radius:4px;background:#fff;font-size:10px;padding:0 28px 0 10px}.ci-legend{display:flex;gap:22px;margin-left:38px;font-size:9px}.dot{display:inline-block;width:20px;height:2px;vertical-align:middle;margin-right:6px}.dot:after{content:'';display:block;width:5px;height:5px;border-radius:50%;margin:-3.5px auto}.dot.blue,.dot.blue:after{background:#2775ef}.dot.purple,.dot.purple:after{background:#7c2db5}.ci-chart{height:160px}.ci-chart svg{width:100%;height:137px;overflow:visible}.grid line{stroke:#edf1f6;stroke-width:1}.area{stroke:none}.blue-area{fill:url(#blueFill)}.purple-area{fill:url(#purpleFill)}.line{fill:none;stroke-width:2}.blue-line{stroke:#2775ef}.purple-line{stroke:#7c2db5}.ci-chart-labels{display:flex;justify-content:space-between;padding:0 8px;font-size:9px;color:#59677d}
.ci-create-card h2{margin-bottom:9px}.ci-form-grid{display:grid;grid-template-columns:1fr 1fr;gap:8px 14px}.ci-form-grid>label{font-size:9px;font-weight:700;margin:0}.ci-form-grid select,.ci-form-grid input{display:block;width:100%;height:29px;margin-top:3px;border:1px solid #d9e1eb;border-radius:4px;background:#fff;padding:5px 9px;font-size:10px;color:#40506a}.ci-form-grid input[readonly]{background:#f4f6f8}.ci-priority{display:grid;grid-template-columns:1fr 1fr;margin-top:3px}.ci-priority input{display:none}.ci-priority label{height:29px;margin:0;border:1px solid #d9e1eb;display:flex;align-items:center;justify-content:center;font-size:9px;background:#fff}.ci-priority input:checked+label{background:var(--ci-purple);border-color:var(--ci-purple);color:#fff}.ci-start-btn{width:100%;height:26px;margin-top:9px;border:0;border-radius:3px;background:var(--ci-purple);color:#fff;font-size:9px;font-weight:800}.ci-form-note{font-size:9px;color:#69768a;margin:5px 0 0}
.ci-pending-card{height:218px;padding:14px 16px;display:flex;flex-direction:column}.ci-pending-head{display:flex;align-items:flex-start;justify-content:space-between}.ci-pending-head h2{margin:0;font-size:14px}.ci-pending-head p{margin:4px 0 0;color:var(--ci-muted);font-size:10px}.ci-pending-head>i{width:32px;height:32px;border-radius:9px;background:#f1e4f7;color:var(--ci-purple);display:flex;align-items:center;justify-content:center;font-size:16px}.ci-pending-stats{display:grid;grid-template-columns:1fr 1fr;gap:8px;margin:12px 0 8px}.ci-pending-stats>div{background:#faf7fc;border:1px solid #eee4f4;border-radius:5px;padding:7px 9px}.ci-pending-stats strong{display:block;color:var(--ci-purple);font-size:20px;line-height:1}.ci-pending-stats span{display:block;margin-top:4px;color:var(--ci-muted);font-size:9px}.ci-pending-list{overflow:auto;flex:1}.ci-pending-item{display:grid;grid-template-columns:42px 1fr auto;gap:7px;align-items:center;padding:5px 0;border-bottom:1px solid #eef1f5;font-size:9px}.ci-pending-item b{color:var(--ci-navy)}.ci-pending-item span{white-space:nowrap;overflow:hidden;text-overflow:ellipsis}.ci-pending-item em{font-style:normal;color:#bd7400;font-size:8px}.ci-pending-empty{color:var(--ci-muted);font-size:10px;padding:8px 0}.ci-view-all-btn{border:0;background:none;color:var(--ci-purple);font-size:9px;font-weight:800;text-align:left;padding:7px 0 0}
.ci-table-card{padding:10px 12px}.ci-table-top{display:flex;align-items:flex-end;justify-content:space-between;margin-bottom:7px}.ci-tabs{display:flex;margin-top:6px}.ci-tabs button{height:29px;min-width:102px;border:1px solid #dce4ed;background:#fff;font-size:9px;font-weight:700}.ci-tabs button:first-child{border-radius:4px 0 0 4px}.ci-tabs button:last-child{border-radius:0 4px 4px 0}.ci-tabs button.active{color:var(--ci-purple);border-bottom:2px solid var(--ci-purple);background:#fcf9fd}.ci-table-tools{display:flex;gap:9px}.ci-table-tools select,.ci-table-tools input,.ci-table-tools button{height:29px;border:1px solid #d9e1eb;background:#fff;border-radius:4px;font-size:9px;padding:0 10px}.ci-table-tools select{width:155px}.ci-search{position:relative}.ci-search i{position:absolute;left:10px;top:9px;color:#8190a4}.ci-search input{width:280px;padding-left:28px}.ci-table-tools button{font-weight:700;min-width:100px}.ci-alert{height:28px;border:1px solid #f5ce70;background:#fffaf0;border-radius:4px;padding:0 12px;display:flex;align-items:center;font-size:9px;color:#5d4819;margin-bottom:7px}.ci-alert i{color:#be7c10;margin-right:5px}.ci-alert button{margin-left:auto;border:0;background:none;color:var(--ci-purple);font-size:9px;font-weight:700}.ci-table-wrap{overflow:auto}.ci-table{width:100%;margin:0!important;border-collapse:collapse}.ci-table th{background:#fbfcfe;font-size:9px;font-weight:800;color:#1d2c43;height:27px;padding:5px 14px!important;border:1px solid #dfe6ef}.ci-table td{height:27px;padding:4px 14px!important;font-size:9px;border:1px solid #e2e8ef;color:#26364e}.ci-table tbody tr:nth-child(even){background:#fbfcfe}.ci-status{display:inline-block;border-radius:4px;padding:3px 9px;font-size:8px;font-weight:700}.ci-status.pending{background:#fff0d5;color:#e68400}.ci-wait{color:#db6a00!important}.ci-issue-btn{border:0;border-radius:3px;background:var(--ci-purple);color:#fff;padding:4px 12px;font-size:8px;font-weight:700}.ci-empty{text-align:center!important;padding:20px!important;color:#758197!important}.ci-modal{border:0;border-radius:7px;overflow:hidden}.ci-modal .modal-header{background:var(--ci-purple);color:#fff}.ci-modal .close{color:#fff;opacity:1}.ci-modal .modal-body{text-align:center;padding:22px}.ci-modal-icon{width:52px;height:52px;border-radius:50%;margin:0 auto 10px;background:#f0e4f5;color:var(--ci-purple);display:flex;align-items:center;justify-content:center;font-size:24px}
@media(max-width:1200px){.ci-kpis{grid-template-columns:repeat(3,1fr)}.ci-overview-grid{grid-template-columns:1fr}.ci-chart-card,.ci-create-card,.ci-pending-card{height:auto}.ci-table-top{display:block}.ci-table-tools{margin-top:8px;flex-wrap:wrap}}
@media(max-width:767px){#content.ci-content{padding:8px!important}.ci-page-head{margin:-8px -8px 8px}.ci-page-head p{display:none}.ci-head-actions .ci-btn-light{display:none}.ci-kpis{grid-template-columns:1fr 1fr}.ci-kpi{height:82px;padding:10px}.ci-overview-grid{display:block}.ci-chart-card{margin-bottom:8px}.ci-form-grid{grid-template-columns:1fr}.ci-table-tools>*{width:100%!important}}
 .ci-status.issued{background:#eee8ff;color:#6f2f96}.ci-status.received{background:#e6f5e8;color:#26823b}.ci-status.draft{background:#edf1f6;color:#64748b}.ci-issue-btn{color:#fff!important}
.ci-counter-chart{overflow:auto;padding:8px 0}.ci-chart-row{display:grid;grid-template-columns:115px 1fr 45px;gap:10px;align-items:center;margin:8px 0;font-size:10px}.ci-chart-bars{height:15px;background:#f1f4f8;border-radius:10px;overflow:hidden;position:relative}.ci-chart-bars i{display:block;height:7px;border-radius:8px}.ci-request-bar{background:#2775ef}.ci-issued-bar{background:#7c2db5;margin-top:1px}.ci-chart-row>span{text-align:right;color:#68758a;font-size:9px}.ci-chart-empty{text-align:center;color:#68758a;padding:35px}.ci-chart-key{text-align:right;color:#68758a;font-size:9px}.ci-chart-key span{margin-left:15px}.ci-modal-field{display:block;text-align:left;font-size:11px;font-weight:700;margin-bottom:12px}.ci-modal-field select{display:block;width:100%;height:34px;margin-top:5px;border:1px solid #d9e1eb;border-radius:4px;padding:5px}
</style>

<style>
.ci-table-report-link{display:flex;align-items:center;gap:5px;height:29px;padding:0 10px;border:1px solid #d9e1eb;border-radius:4px;background:#fff;color:var(--ci-purple);font-size:9px;font-weight:700;text-decoration:none!important}
.ci-chart{overflow:auto}.ci-counter-chart{padding:8px 0}.ci-line-chart{display:block;width:100%;height:70px;margin-bottom:3px}.ci-line-chart line{stroke:#dfe6ef;stroke-width:1}.ci-line-chart polyline{fill:none;stroke-width:3;stroke-linejoin:round;stroke-linecap:round}.ci-request-line{stroke:#2775ef}.ci-issued-line{stroke:#7c2db5}.ci-chart-row{display:grid;grid-template-columns:115px 1fr 45px;gap:10px;align-items:center;margin:8px 0;font-size:10px}.ci-chart-bars{height:15px;background:#f1f4f8;border-radius:10px;overflow:hidden}.ci-chart-bars i{display:block;height:7px;border-radius:8px}.ci-request-bar{background:#2775ef}.ci-issued-bar{background:#7c2db5;margin-top:1px}.ci-chart-row>span{text-align:right;color:#68758a;font-size:9px}.ci-chart-empty{text-align:center;color:#68758a;padding:35px}.ci-chart-key{text-align:right;color:#68758a;font-size:9px}.ci-chart-key span{margin-left:15px}.ci-modal-field{display:block;text-align:left;font-size:11px;font-weight:700;margin-bottom:12px}.ci-modal-field select{display:block;width:100%;height:34px;margin-top:5px;border:1px solid #d9e1eb;border-radius:4px;padding:5px}
</style>

<style>
.ci-line-chart path{fill:none;stroke-width:3;stroke-linejoin:round;stroke-linecap:round}
.ci-pending-item{grid-template-columns:42px minmax(70px,1fr) auto auto}
.ci-pending-item em{white-space:nowrap}
.ci-pending-action{color:var(--ci-purple);font-size:10px;font-weight:800;white-space:nowrap;background:#fff;border:1px solid var(--ci-purple);border-radius:4px;padding:4px 8px;cursor:pointer}
.ci-pending-action:hover{background:#f4eafa;color:#542075}
.ci-pending-item{font-size:10px}
.ci-pending-item em{font-size:9px}
.ci-chart-bars{height:22px;border-radius:4px;padding:2px 0}
.ci-chart-bars i{height:8px;border-radius:3px}
.ci-counter-chart{display:flex;align-items:flex-end;gap:18px;min-height:160px;padding:12px 8px 4px}
.ci-column-group{height:145px;min-width:58px;flex:1;display:flex;flex-direction:column;align-items:center;justify-content:flex-end;position:relative}
.ci-column-bars{height:112px;width:100%;display:flex;align-items:flex-end;justify-content:center;gap:4px;border-bottom:1px solid #dfe6ef;background:repeating-linear-gradient(to top,transparent 0,transparent 27px,#edf1f6 28px)}
.ci-column-bars i{display:block;width:18px;min-height:2px;border-radius:4px 4px 0 0}
.ci-column-name{max-width:85px;margin-top:5px;overflow:hidden;text-overflow:ellipsis;white-space:nowrap;font-size:9px}
.ci-column-values{font-size:8px;color:var(--ci-muted);margin-top:2px}
.ci-table th{font-size:10px}
.ci-table td{font-size:10px}
</style>
<script src="../../assets/plugins/jquery/jquery-3.2.1.min.js"></script>
<script src="../../assets/plugins/jquery-ui/jquery-ui.min.js"></script>
<script src="../../assets/plugins/bootstrap/4.0.0/js/bootstrap.bundle.min.js"></script>
<script src="../../assets/plugins/slimscroll/jquery.slimscroll.min.js"></script>
<script src="../../assets/plugins/js-cookie/js.cookie.js"></script>
<script src="../../assets/js/theme/default.min.js"></script>
<script src="../../assets/js/apps.min.js"></script>
<link rel="stylesheet" href="../../assets/plugins/sweetalert/dist/sweetalert2.min.css">
<script src="../../assets/plugins/sweetalert/dist/sweetalert2.all.min.js"></script>
<script>
$(function(){
  App.init();
  $('#create-counter-issue-form').on('submit', function(e){
    e.preventDefault();
    var form = this;
    var counter = $('#create-counter-select option:selected').text();
    var shift = $(form).find('[name="shift"]').val() || '';
    var priority = $(form).find('[name="priority"]:checked').val() || 'normal';
    if (!$('#create-counter-select').val()) {
      Swal.fire({icon:'warning', title:'Select a counter', text:'Please select the counter for this issue first.'});
      return;
    }
    Swal.fire({
      icon: 'question', title: 'Confirm counter selection',
      html: '<div style="text-align:left"><b>Counter:</b> ' + $('<div>').text(counter).html() + '<br><b>Shift:</b> ' + $('<div>').text(shift).html() + '<br><b>Priority:</b> ' + $('<div>').text(priority).html() + '</div><br>Continue to the stock issue page?',
      showCancelButton: true, confirmButtonText: 'Yes, continue', cancelButtonText: 'Go back', confirmButtonColor: '#752d9b'
    }).then(function(result){
      if (result.isConfirmed) {
        var target = 'create_request.php?counter=' + encodeURIComponent($('#create-counter-select').val()) + '&shift=' + encodeURIComponent(shift) + '&priority=' + encodeURIComponent(priority);
        window.location.href = target;
      }
    });
  });
  var selectedRequest=null;
  $(document).on('click','.counter-issue-trigger',function(e){e.preventDefault();selectedRequest={reqId:$(this).data('req-id'),counter:$(this).data('counter')};$('#counter-issue-confirm').modal('show');});
  $('#confirm-counter-issue').on('click',function(){if(!selectedRequest)return;$(this).prop('disabled',true).text('Loading...');window.location.href='req_view.php?req_id='+encodeURIComponent(selectedRequest.reqId)+'&cntr='+encodeURIComponent(selectedRequest.counter);});
  $('#ci-search').on('input',function(){var q=this.value.toLowerCase();$('.ci-table tbody tr').each(function(){$(this).toggle($(this).text().toLowerCase().indexOf(q)>-1);});});
  $('.ci-tabs button').on('click',function(){$('.ci-tabs button').removeClass('active');$(this).addClass('active');var status=$(this).data('status');$('.ci-table tbody tr').toggle(status==='all').filter('[data-status="'+status+'"]').show();});
  $('#show-pending').on('click',function(){$('.ci-tabs button[data-status="pending"]').trigger('click');});
});
</script>
</body>
</html>
