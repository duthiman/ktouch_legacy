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
$rejected_requests = counter_req_count($connection, 'issue_status=5');
$pending_receive_requests = counter_req_count($connection, 'issue_status=3 AND (rec_status IS NULL OR rec_status<>4)');
$pending_po = mysqli_query($connection, "SELECT * FROM kp_counter_req WHERE issue_status=2 OR (issue_status=3 AND (rec_status IS NULL OR rec_status<>4)) ORDER BY req_id DESC");
$open_counters = [];
$open_counter_result = mysqli_query($connection, "SELECT cntr_id,name FROM kp_counters WHERE del_status=2 AND counter_status=1 ORDER BY name ASC");
while ($open_counter_result && ($open_counter = mysqli_fetch_assoc($open_counter_result))) $open_counters[] = $open_counter;
$open_counter_ids = array_map('intval', array_column($open_counters, 'cntr_id'));
$open_shifts = [];
$open_shift_result = mysqli_query($connection, "SELECT shift FROM kp_shift ORDER BY shift ASC");
while ($open_shift_result && ($open_shift = mysqli_fetch_assoc($open_shift_result))) $open_shifts[] = (string)$open_shift['shift'];
$current_shift = $open_shifts[0] ?? date('Y-m-d');
$can_create_issue = count($open_counters) > 0 && count($open_shifts) > 0;
$activity = [];
$activity_result = mysqli_query($connection, "SELECT c.cntr_id,c.name,COUNT(r.req_id) AS requested,SUM(CASE WHEN r.issue_status=3 THEN 1 ELSE 0 END) AS issued FROM kp_counters c LEFT JOIN kp_counter_req r ON r.counter=c.cntr_id GROUP BY c.cntr_id,c.name ORDER BY c.name ASC");
while ($activity_result && ($activity_row = mysqli_fetch_assoc($activity_result))) $activity[] = $activity_row;
$recent_pending = [];
$recent_pending_result = mysqli_query($connection, "SELECT req_id,counter,req_shift,req_user,issue_status,rec_status FROM kp_counter_req WHERE issue_status=2 OR (issue_status=3 AND (rec_status IS NULL OR rec_status<>4)) ORDER BY req_id DESC LIMIT 5");
while ($recent_pending_result && ($recent_row = mysqli_fetch_assoc($recent_pending_result))) $recent_pending[] = $recent_row;
$completed_requests = [];
$completed_result = mysqli_query($connection, "SELECT req_id,counter,req_shift,req_user,issue_status,rec_status FROM kp_counter_req WHERE issue_status=4 OR rec_status=4 OR issue_status=5 ORDER BY req_id DESC LIMIT 5");
while ($completed_result && ($completed_row = mysqli_fetch_assoc($completed_result))) $completed_requests[] = $completed_row;
?>

<div id="page-container" class="fade page-without-sidebar page-header-fixed page-with-top-menu counter-issues-page">
  <?php require("../clude/header.php"); ?>
  <?php require("../clude/top_menu.php"); ?>

  <div id="content" class="content ci-content">
    <header class="ci-page-head">
      <div class="ci-head-icon"><i class="fa fa-building"></i></div>
      <div>
        <h1>Stock Issuing</h1>
        <p>Issue stock to counters, shops or departments. Create, track and manage all stock issues.</p>
      </div>
      <div class="ci-head-actions">
        <button type="button" class="btn ci-btn-primary" data-toggle="modal" data-target="#new-counter-issue" <?php echo $can_create_issue ? '' : 'disabled title="No open counter or shift"'; ?>><i class="fa fa-plus"></i> New Counter Issue</button>
        <button class="btn ci-btn-light" onclick="window.location.reload()"><i class="fa fa-refresh"></i> Refresh</button>
      </div>
    </header>

    <section class="ci-kpis">
      <article class="ci-kpi kpi-blue"><div class="ci-kpi-icon"><i class="fa fa-file-text-o"></i></div><div><span>Total Requests</span><strong><?php echo $total_requests; ?></strong><small>This month</small></div></article>
      <article class="ci-kpi kpi-amber"><div class="ci-kpi-icon"><i class="fa fa-hourglass-half"></i></div><div><span>Pending</span><strong><?php echo $pending_requests; ?></strong><small>Needs issuing</small></div></article>
      <article class="ci-kpi kpi-purple"><div class="ci-kpi-icon"><i class="fa fa-cube"></i></div><div><span>Pending Receiving</span><strong><?php echo $pending_receive_requests; ?></strong><small>Awaiting receipt</small></div></article>
      <article class="ci-kpi kpi-green"><div class="ci-kpi-icon"><i class="fa fa-check-square-o"></i></div><div><span>Completed</span><strong><?php echo $received_requests; ?></strong><small>Received successfully</small></div></article>
      <article class="ci-kpi kpi-red"><div class="ci-kpi-icon"><i class="fa fa-times-circle-o"></i></div><div><span>Rejected</span><strong><?php echo $rejected_requests; ?></strong><small>Not issued</small></div></article>
      <article class="ci-kpi kpi-teal"><div class="ci-kpi-icon"><i class="fa fa-clock-o"></i></div><div><span>Average Processing Time</span><strong>18 min</strong><small class="ci-good"><i class="fa fa-arrow-down"></i> 30% from last month</small></div></article>
    </section>

    <section class="ci-overview-grid">
      <article class="ci-card ci-chart-card">
        <div class="ci-card-title"><h2>Issue Activity by Counter</h2><div class="ci-legend"><span><i class="dot blue"></i>Requested</span><span><i class="dot purple"></i>Issued</span></div></div>
        <div class="ci-chart ci-counter-horizontal">
          <?php $chart_max=1; foreach($activity as $a) $chart_max=max($chart_max,(int)$a['requested'],(int)$a['issued']); if(!$activity) echo '<div class="ci-chart-empty">No request activity yet.</div>'; foreach($activity as $a){$requested=(int)$a['requested'];$issued=(int)$a['issued']; ?>
            <div class="ci-counter-row"><b class="ci-counter-row-name"><?php echo htmlspecialchars($a['name']); ?></b><div class="ci-counter-row-bars"><div class="ci-horizontal-track"><i class="ci-request-bar" style="width:<?php echo round(($requested/$chart_max)*100); ?>%"></i><span>Requested <?php echo $requested; ?></span></div><div class="ci-horizontal-track"><i class="ci-issued-bar" style="width:<?php echo round(($issued/$chart_max)*100); ?>%"></i><span>Issued <?php echo $issued; ?></span></div></div></div>
          <?php } ?>
        </div>
      </article>

      <article class="ci-card ci-pending-card">
        <div class="ci-pending-head"><div><h2>Pending Counter Issues</h2><p>Requests requiring action</p></div><i class="fa fa-exchange"></i></div>
        <div class="ci-pending-stats"><div><strong><?php echo $pending_requests; ?></strong><span>Pending issue</span></div><div><strong><?php echo $pending_receive_requests; ?></strong><span>Awaiting receipt</span></div></div>
        <div class="ci-pending-tabs"><button type="button" class="active" data-pending-tab="issue">Pending Issues</button><button type="button" data-pending-tab="receive">Pending Receiving</button><button type="button" data-pending-tab="complete">Completed</button></div>
        <div class="ci-pending-list ci-pending-table-wrap">
          <?php if (!$recent_pending) { ?><div class="ci-pending-empty">No pending stock issues.</div><?php } else { ?>
          <table class="ci-pending-table"><thead><tr><th>Issue No.</th><th>Requested By</th><th>Destination</th><th>Items</th><th>Requested On</th><th>Action</th></tr></thead><tbody>
          <?php foreach ($recent_pending as $recent) { $recent_req_id=(int)$recent['req_id']; $recent_counter=(int)$recent['counter']; $item_count_q=mysqli_query($connection,"SELECT COUNT(*) total FROM kp_counter_req_items WHERE req_id='$recent_req_id'"); $item_count_row=$item_count_q?mysqli_fetch_assoc($item_count_q):[]; $tab_state=(int)$recent['issue_status']===2?'issue':'receive'; ?>
          <tr data-pending-state="<?php echo $tab_state; ?>"><td><b>ISS-<?php echo $recent_req_id; ?></b></td><td><?php echo htmlspecialchars(get_user((int)($recent['req_user']??0))); ?></td><td><?php echo htmlspecialchars(get_counter($recent_counter)); ?></td><td><?php echo (int)($item_count_row['total']??0); ?></td><td><?php echo htmlspecialchars((string)$recent['req_shift']); ?></td><td><?php if ((int)$recent['issue_status']===2) { ?><button type="button" class="ci-pending-action counter-issue-trigger" data-req-id="<?php echo $recent_req_id; ?>" data-counter="<?php echo $recent_counter; ?>">Approve</button><?php } else { ?><button type="button" class="ci-pending-action" onclick="ciOpenRequest(<?php echo $recent_req_id; ?>)">View</button><?php } ?></td></tr>
          <?php } foreach ($completed_requests as $recent) { $recent_req_id=(int)$recent['req_id']; $recent_counter=(int)$recent['counter']; $item_count_q=mysqli_query($connection,"SELECT COUNT(*) total FROM kp_counter_req_items WHERE req_id='$recent_req_id'"); $item_count_row=$item_count_q?mysqli_fetch_assoc($item_count_q):[]; ?>
          <tr data-pending-state="complete"><td><b>ISS-<?php echo $recent_req_id; ?></b></td><td><?php echo htmlspecialchars(get_user((int)($recent['req_user']??0))); ?></td><td><?php echo htmlspecialchars(get_counter($recent_counter)); ?></td><td><?php echo (int)($item_count_row['total']??0); ?></td><td><?php echo htmlspecialchars((string)$recent['req_shift']); ?></td><td><button type="button" class="ci-pending-action" onclick="ciOpenRequest(<?php echo $recent_req_id; ?>)">View</button></td></tr>
          <?php } ?></tbody></table><?php } ?>
        </div>
        <a href="../reports/counter_req.php" class="ci-view-all-btn">View all issues <i class="fa fa-arrow-right"></i></a>
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

  <div id="ci-request-view-modal" class="modal fade" tabindex="-1" role="dialog" aria-hidden="true">
    <div class="modal-dialog modal-lg" role="document"><div class="modal-content ci-view-modal">
      <div class="modal-header"><button type="button" class="close" data-dismiss="modal"><span>&times;</span></button><h4 class="modal-title"><i class="fa fa-cube"></i> Stock Issue Details</h4></div>
      <div class="modal-body"><iframe id="ci-request-view-frame" title="Stock issue details" src="about:blank"></iframe></div>
    </div></div>
  </div>

  <div id="new-counter-issue" class="modal fade" tabindex="-1" role="dialog" aria-hidden="true">
    <div class="modal-dialog modal-md ci-new-issue-dialog" role="document"><div class="modal-content ci-modal"><div class="modal-header"><button type="button" class="close" data-dismiss="modal"><span>&times;</span></button><h4 class="modal-title">New Counter Issue</h4></div><div class="modal-body" id="new-issue-modal-body">
      <article class="ci-card ci-create-card" id="new-issue-card">
        <h2>Create New Counter Issue</h2>
        <form action="create_request.php" method="get" id="create-counter-issue-form">
          <div class="ci-form-grid">
            <label>Open Counter<select name="counter" id="create-counter-select" required><option value="">Select an open counter</option><?php foreach ($open_counters as $c) echo '<option value="'.(int)$c['cntr_id'].'">'.htmlspecialchars($c['name']).'</option>'; ?></select></label>
            <label>Active Shift<input name="shift" value="<?php echo htmlspecialchars($current_shift); ?>" readonly></label>
          </div>
          <button class="ci-start-btn" type="submit">Start New Issue</button>
          <p class="ci-form-note"><i class="fa fa-info-circle"></i> A requisition number will be generated automatically.</p>
        </form>
      </article>
    </div></div></div>
  </div>
  <div id="counter-issue-confirm" class="modal fade" tabindex="-1" role="dialog" aria-hidden="true">
    <div class="modal-dialog modal-sm" role="document"><div class="modal-content ci-modal"><div class="modal-header"><button type="button" class="close" data-dismiss="modal"><span>&times;</span></button><h4 class="modal-title">Confirm Stock Issue</h4></div><div class="modal-body"><div class="ci-modal-icon"><i class="fa fa-cubes"></i></div><h4>Issue this requisition?</h4><p>Do you want to open this requisition and continue issuing its stock?</p></div><div class="modal-footer"><button class="btn btn-default" data-dismiss="modal">Cancel</button><button class="btn ci-btn-primary" id="confirm-counter-issue">Yes, Issue Stock</button></div></div></div>
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
<style>
/* Counter requisition layout: compact, action-first and responsive. */
.counter-issues-page #content.ci-content{padding:8px 16px 14px!important}
.counter-issues-page .ci-page-head{height:54px;margin:-8px -16px 8px;padding:7px 16px}
.counter-issues-page .ci-head-icon{width:36px;height:36px;border-radius:7px;font-size:19px}
.counter-issues-page .ci-page-head h1{font-size:17px}
.counter-issues-page .ci-page-head p{font-size:10px}
.counter-issues-page .ci-head-actions{gap:6px}
.counter-issues-page .ci-head-actions .btn{height:31px;padding:0 11px;font-size:10px;border-radius:7px}
.counter-issues-page .ci-kpis{grid-template-columns:repeat(5,minmax(0,1fr));gap:7px;margin-bottom:8px}
.counter-issues-page .ci-kpi{height:72px;border-radius:7px;padding:9px 10px;gap:9px}
.counter-issues-page .ci-kpi-icon{width:34px;height:34px;border-radius:7px;font-size:17px}
.counter-issues-page .ci-kpi span{font-size:9px;white-space:nowrap}
.counter-issues-page .ci-kpi strong{font-size:18px;margin:1px 0}
.counter-issues-page .ci-kpi small{font-size:8px;white-space:nowrap;overflow:hidden;text-overflow:ellipsis}
.counter-issues-page .ci-overview-grid{grid-template-columns:1.15fr 1fr;gap:8px;margin-bottom:8px}
.counter-issues-page .ci-pending-card{order:1;height:360px;padding:11px 13px;border-radius:7px}
.counter-issues-page .ci-chart-card{order:2;height:360px;padding:10px 13px;border-radius:7px}
.counter-issues-page .ci-card-title,.counter-issues-page .ci-pending-head{height:25px}
.counter-issues-page .ci-card h2,.counter-issues-page .ci-pending-head h2{font-size:12px}
.counter-issues-page .ci-pending-head p{font-size:9px;margin-top:2px}
.counter-issues-page .ci-pending-stats{gap:5px;margin:8px 0 5px}
.counter-issues-page .ci-pending-stats>div{padding:5px 7px;border-radius:7px}
.counter-issues-page .ci-pending-stats strong{font-size:17px}
.counter-issues-page .ci-pending-stats span{font-size:8px;margin-top:2px}
.counter-issues-page .ci-pending-item{padding:4px 0;font-size:9px;grid-template-columns:38px minmax(60px,1fr) auto auto;gap:5px}
.counter-issues-page .ci-pending-action{font-size:8px;padding:3px 6px;border-radius:7px}
.counter-issues-page .ci-counter-chart{min-height:132px;height:145px;gap:9px;padding:5px 2px}
.counter-issues-page .ci-column-group{height:128px;min-width:38px}
.counter-issues-page .ci-column-bars{height:91px}
.counter-issues-page .ci-column-bars i{width:12px}
.counter-issues-page .ci-column-name{max-width:60px;font-size:8px}
.counter-issues-page .ci-column-values{font-size:7px}
.counter-issues-page .ci-table-card{padding:8px 9px;border-radius:7px}
.counter-issues-page .ci-table-top{margin-bottom:6px}
.counter-issues-page .ci-table-top h2{font-size:13px}
.counter-issues-page .ci-tabs button{height:26px;min-width:92px;font-size:8px;border-radius:0}
.counter-issues-page .ci-table-tools{gap:5px}
.counter-issues-page .ci-table-tools select,.counter-issues-page .ci-table-tools input,.counter-issues-page .ci-table-tools button,.counter-issues-page .ci-table-report-link{height:26px;font-size:8px;border-radius:7px}
.counter-issues-page .ci-table th,.counter-issues-page .ci-table td{padding:4px 8px!important;font-size:9px}
.counter-issues-page .ci-modal{border-radius:7px}
.counter-issues-page .ci-kpis{grid-template-columns:repeat(6,minmax(0,1fr))}
.counter-issues-page .ci-pending-tabs{display:flex;gap:4px;margin:6px 0;border-bottom:1px solid #e7deef}
.counter-issues-page .ci-pending-tabs button{border:0;border-bottom:2px solid transparent;background:transparent;color:#6d5b79;padding:5px 7px;font-size:8px;font-weight:800;white-space:nowrap}
.counter-issues-page .ci-pending-tabs button.active{color:var(--ci-purple);border-bottom-color:var(--ci-purple)}
.counter-issues-page .ci-pending-table tbody tr[data-pending-state]{display:none}
.counter-issues-page .ci-pending-table tbody tr[data-pending-state="issue"]{display:table-row}
.counter-issues-page .ci-view-modal{border:0;border-radius:10px;overflow:hidden}
.counter-issues-page .ci-view-modal .modal-header{background:linear-gradient(135deg,#68269b,#9143bd);color:#fff;border:0;padding:13px 16px}
.counter-issues-page .ci-view-modal .modal-title{font-weight:800}
.counter-issues-page .ci-view-modal .close{color:#fff;opacity:1}
.counter-issues-page .ci-view-modal .modal-body{padding:0;background:#f7f8fb}
.counter-issues-page .ci-counter-horizontal{height:300px;display:flex;flex-direction:column;justify-content:center;gap:16px;padding:12px 4px;overflow:auto}
.counter-issues-page .ci-counter-row{display:grid;grid-template-columns:130px minmax(0,1fr);gap:12px;align-items:center;width:100%}
.counter-issues-page .ci-counter-row-name{font-size:10px;color:#263450;white-space:nowrap;overflow:hidden;text-overflow:ellipsis;text-align:right}
.counter-issues-page .ci-counter-row-bars{display:grid;gap:5px;min-width:0}
.counter-issues-page .ci-horizontal-track{height:22px;position:relative;border-radius:6px;background:#f0f3f8;overflow:hidden}
.counter-issues-page .ci-horizontal-track i{display:block;height:100%;min-width:3px;border-radius:6px;opacity:.92}
.counter-issues-page .ci-horizontal-track span{position:absolute;left:8px;top:4px;color:#fff;font-size:8px;font-weight:800;text-shadow:0 1px 2px #0008;white-space:nowrap}
.counter-issues-page .ci-request-bar{background:#2775ef}.counter-issues-page .ci-issued-bar{background:#7c2db5}
.counter-issues-page .ci-new-issue-dialog{max-width:520px;width:calc(100% - 24px)}
.counter-issues-page #ci-request-view-frame{display:block;width:100%;height:68vh;border:0;background:#fff}
.counter-issues-page .ci-table-card{display:none}
.counter-issues-page .kpi-red{background:#fffafa}
.counter-issues-page .kpi-red .ci-kpi-icon{background:#ffe4e4;color:#e34b4b}
.counter-issues-page .ci-pending-table-wrap{overflow:auto}
.counter-issues-page .ci-pending-table{width:100%;min-width:610px;border-collapse:collapse;font-size:8px}
.counter-issues-page .ci-pending-table th{height:24px;padding:4px 6px;text-align:left;background:#faf8ff;color:#263450;border-bottom:1px solid #e3d9ef;white-space:nowrap}
.counter-issues-page .ci-pending-table td{height:27px;padding:4px 6px;border-bottom:1px solid #edf0f5;color:#34445f;white-space:nowrap}
.counter-issues-page .ci-pending-table tr:last-child td{border-bottom:0}
.counter-issues-page a.ci-view-all-btn{display:block;text-decoration:none!important}
@media(max-width:1100px){
  .counter-issues-page .ci-kpis{grid-template-columns:repeat(3,1fr)}
  .counter-issues-page .ci-overview-grid{grid-template-columns:1fr 1fr}
}
@media(max-width:767px){
  .counter-issues-page #content.ci-content{padding:6px!important}
  .counter-issues-page .ci-page-head{margin:-6px -6px 7px;padding:6px 8px;height:48px}
  .counter-issues-page .ci-page-head p{display:none}
  .counter-issues-page .ci-head-actions .ci-btn-light{display:none}
  .counter-issues-page .ci-head-actions .btn{padding:0 8px;font-size:9px}
  .counter-issues-page .ci-kpis{grid-template-columns:repeat(2,1fr);gap:5px}
  .counter-issues-page .ci-kpi{height:62px;padding:7px 8px}
  .counter-issues-page .ci-kpi-icon{width:28px;height:28px;font-size:14px}
  .counter-issues-page .ci-kpi strong{font-size:16px}
  .counter-issues-page .ci-overview-grid{display:flex;flex-direction:column;gap:7px}
  .counter-issues-page .ci-pending-card{order:1;height:360px}
  .counter-issues-page .ci-chart-card{order:2;height:175px}
  .counter-issues-page .ci-counter-horizontal{height:130px;gap:8px;padding:4px 0}
  .counter-issues-page .ci-counter-row{grid-template-columns:70px minmax(0,1fr);gap:6px}
  .counter-issues-page .ci-counter-row-name{font-size:8px}
  .counter-issues-page .ci-horizontal-track{height:16px}
  .counter-issues-page .ci-horizontal-track span{top:2px;font-size:7px}
  .counter-issues-page .ci-table-top{display:block}
  .counter-issues-page .ci-table-tools{margin-top:6px;display:grid;grid-template-columns:1fr 1fr}
  .counter-issues-page .ci-table-tools>*{width:100%!important}
  .counter-issues-page .ci-search,.counter-issues-page .ci-search input{grid-column:1/-1;width:100%!important}
  .counter-issues-page .ci-table-wrap{overflow-x:auto}
  .counter-issues-page .ci-table{min-width:720px}
}
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
    if (!$('#create-counter-select').val()) {
      Swal.fire({icon:'warning', title:'Select a counter', text:'Please select the counter for this issue first.'});
      return;
    }
    Swal.fire({
      icon: 'question', title: 'Confirm counter selection',
      html: '<div style="text-align:left"><b>Counter:</b> ' + $('<div>').text(counter).html() + '<br><b>Shift:</b> ' + $('<div>').text(shift).html() + '</div><br>Continue to the stock issue page?',
      showCancelButton: true, confirmButtonText: 'Yes, continue', cancelButtonText: 'Go back', confirmButtonColor: '#752d9b'
    }).then(function(result){
      if (result.isConfirmed) {
        var target = 'create_request.php?counter=' + encodeURIComponent($('#create-counter-select').val()) + '&shift=' + encodeURIComponent(shift);
        window.location.href = target;
      }
    });
  });
  window.ciOpenRequest = function(id){
    $('#ci-request-view-frame').attr('src','../reports/req_items.php?req_id='+encodeURIComponent(id));
    $('#ci-request-view-modal').modal('show');
  };
  $('.ci-pending-tabs button').on('click', function(){
    $('.ci-pending-tabs button').removeClass('active');
    $(this).addClass('active');
    var state=$(this).data('pending-tab');
    $('.ci-pending-table tbody tr[data-pending-state]').each(function(){
      $(this).toggle($(this).data('pending-state')===state);
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
