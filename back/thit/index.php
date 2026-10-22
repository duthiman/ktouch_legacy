<?php
require_once '../clude/head.php';
?>
<link rel="stylesheet" href="thit.css">

<div id="page-container" class="page-container fade page-without-sidebar page-header-fixed page-with-top-menu thit-dashboard">
      <!-- begin universal header -->
      <?php require("../clude/header.php"); ?>
      <!-- end universal header -->
      <!-- begin universal top menu -->
      <?php require("../clude/top_menu.php"); ?>
      <!-- end universal top menu -->

    <div id="content" class="content thit-content">
        <div class="thit-titlebar"><h1><i class="fa fa-archive"></i> STORE MANAGEMENT DASHBOARD</h1></div>
        <div class="row thit-toolbar-row">
            <div class="col-md-2 col-sm-3"><div class="thit-toolbar"><a href="../dash/dash.php" class="btn btn-default"><i class="fa fa-home"></i> HOME</a><button class="btn btn-default" id="thit-refresh"><i class="fa fa-refresh"></i> REFRESH</button></div></div>
            <div class="col-md-7 col-sm-6"><div class="thit-shiftbar"><span class="label label-success"><i class="fa fa-circle"></i> Shift Open</span><span><i class="fa fa-clock-o"></i> 0h 2m</span><span><i class="fa fa-user"></i> 0 on duty</span><span><i class="fa fa-tasks"></i> 0 tasks</span><span class="label label-primary">On Time</span></div></div>
            <div class="col-md-3 col-sm-3 text-right"><button class="btn btn-success"><i class="fa fa-file-text-o"></i> Z-Report</button> <button class="btn btn-danger"><i class="fa fa-power-off"></i> Close Shift</button></div>
        </div>
        <div class="row">
            <div class="col-md-2 col-sm-4">
                <div class="panel panel-default thit-panel kpi-panel"><div class="panel-heading"><i class="fa fa-trophy"></i> MONTHLY KPI ACHIEVEMENT</div><div class="panel-body text-center"><div class="thit-donut" data-value="78"><span>78%<small>Overall</small></span></div><div class="thit-legend"><span class="teal">≥80% On Track</span><span class="orange">50–79%</span><span class="red">&lt;50%</span></div></div></div>
                <div class="panel panel-default thit-panel"><div class="panel-heading"><i class="fa fa-bell-o"></i> REMINDERS</div><ul class="thit-list"><li><i class="fa fa-truck purple"></i> Pending GRNs <b id="thit-rem-pending-grn">36</b></li><li><i class="fa fa-exchange orange"></i> Pending Issues <b id="thit-rem-pending-issues">1</b></li><li><i class="fa fa-warning red"></i> Out of Stock <b id="thit-rem-out-stock">169</b></li><li><i class="fa fa-warning orange"></i> Low Stock <b id="thit-rem-low-stock">2</b></li><li><i class="fa fa-archive blue"></i> Counter Requests <b id="thit-rem-counter-requests">0</b></li></ul></div>
                <div class="panel panel-default thit-panel"><div class="panel-heading"><i class="fa fa-cogs"></i> OPERATIONS</div>
                <ul class="thit-menu-list">
                    <li><a href="#" class="thit-issue-stock"><i class="fa fa-archive"></i> Issue Stock</a></li>
                    <li><a href="#" class="thit-stock-management"><i class="fa fa-shopping-bag"></i> Stock Management</a></li>
                    <li><i class="fa fa-file-text-o"></i> GRN / Receive Stock</li><li><i class="fa fa-cubes"></i> Stock Take</li></ul><div class="thit-process"><span>1 REQUEST</span><span>2 PROCESS</span><span>3 DISPATCH</span></div></div>
                <div class="panel panel-default thit-panel">
                    <div class="panel-heading">
                        <i class="fa fa-line-chart"></i> REPORTS</div>
                        <ul class="thit-menu-list">
                            <li><i class="fa fa-file-text-o"></i> Counter Stocks Level</li>
                            <li><i class="fa fa-file-text-o"></i> Store Stocks Level</li>
                            <li><i class="fa fa-file-text-o"></i> Counter Stock Issues</li>
                        </ul></div>
            </div>
            <div class="col-md-10 col-sm-8 maincont" id="maincont">
                <div class="row thit-kpi-row">
                    <div class="col-md-2 col-sm-4"><div class="thit-kpi blue"><small>PENDING GRN</small><strong id="thit-pending-grn">36</strong><span>awaiting stocking</span><i class="fa fa-archive"></i></div></div>
                    <div class="col-md-2 col-sm-4"><div class="thit-kpi purple"><small>PENDING ISSUES</small><strong id="thit-pending-issues">1</strong><span>requests open</span><i class="fa fa-truck"></i></div></div>
                    <div class="col-md-2 col-sm-4"><div class="thit-kpi orange"><small>OUT OF STOCK</small><strong id="thit-out-stock">169</strong><span>zero qty variants</span><i class="fa fa-cart-plus"></i></div></div>
                    <div class="col-md-2 col-sm-4"><div class="thit-kpi teal"><small>LOW STOCK</small><strong id="thit-low-stock">2</strong><span>below reorder level</span><i class="fa fa-warning"></i></div></div>
                    <div class="col-md-2 col-sm-4"><div class="thit-kpi red"><small>APPROVALS</small><strong id="thit-approvals">0</strong><span>requests pending approval</span><i class="fa fa-times-circle-o"></i></div></div>
                    <div class="col-md-2 col-sm-4"><div class="thit-kpi blue"><small>PENDING DISPATCH</small><strong id="thit-pending-dispatch">1</strong><span>approved, awaiting dispatch</span><i class="fa fa-truck"></i></div></div>
                </div>
                <div class="row"><div class="col-md-4"><div class="thit-value teal-border"><small>RETAIL STOCK VALUE</small><strong id="thit-retail-value">KSh 1.79M</strong><span>Total retail stock sold at sell price</span><i class="fa fa-shopping-cart"></i></div></div><div class="col-md-4"><div class="thit-value blue-border"><small>CAPITAL IN STOCK (COST)</small><strong id="thit-capital-value">KSh 1.45M</strong><span>Cash tied up at buy/cost price</span><i class="fa fa-database"></i></div></div><div class="col-md-4"><div class="thit-value purple-border"><small>EST. GROSS MARGIN</small><strong id="thit-margin-value">KSh 340K <em id="thit-margin-pct">19.0%</em></strong><span>Retail minus cost — potential gross profit</span><i class="fa fa-line-chart"></i></div></div></div>
                <div class="row"><div class="col-md-6"><div class="panel panel-default thit-panel chart-panel"><div class="panel-heading"><i class="fa fa-bolt"></i> 6-MONTH STOCK ACTIVITY <span class="label label-primary pull-right">Live data</span></div><div class="panel-body"><canvas id="activity-chart" height="180"></canvas></div></div></div><div class="col-md-6"><div class="panel panel-default thit-panel chart-panel"><div class="panel-heading"><i class="fa fa-database"></i> COUNTER STOCK VALUE</div><div class="panel-body"><canvas id="counter-chart" height="180"></canvas><div class="thit-chart-footer"><span>Total Retail <b>KSh 1.79M</b></span><span>Total Cost <b>KSh 1.45M</b></span></div></div></div></div></div>
                <div class="row"><div class="col-md-4"><div class="panel panel-default thit-panel quick-panel"><div class="panel-heading"><i class="fa fa-bolt"></i> QUICK ACTIONS</div><div class="panel-body"><div class="row"><div class="col-xs-6"><button class="quick-action"><i class="fa fa-truck"></i>Receive Stock</button></div><div class="col-xs-6"><button class="quick-action"><i class="fa fa-exchange"></i>Transfer Stock</button></div><div class="col-xs-6"><button class="quick-action"><i class="fa fa-clipboard"></i>Stock Count</button></div><div class="col-xs-6"><button class="quick-action"><i class="fa fa-list"></i>Reorder List</button></div></div></div></div></div><div class="col-md-5"><div class="panel panel-default thit-panel activity-panel"><div class="panel-heading"><i class="fa fa-list"></i> RECENT GRNs <span class="badge" id="thit-recent-count">8</span></div><table class="table table-hover"><thead><tr><th>CODE</th><th>BY</th><th>DATE</th><th>ITEMS</th><th>STATUS</th></tr></thead><tbody id="thit-recent-grns"><tr><td>WR-00123</td><td>DAMARIS</td><td>2026-08-08</td><td>0</td><td><span class="label label-warning">pending</span></td></tr><tr><td>WR-00120</td><td>JANE</td><td>2026-08-04</td><td>0</td><td><span class="label label-warning">pending</span></td></tr><tr><td>WR-00117</td><td>DAMARIS</td><td>2026-07-28</td><td>0</td><td><span class="label label-success">approved</span></td></tr><tr><td>WR-00116</td><td>JANE</td><td>2026-07-25</td><td>0</td><td><span class="label label-success">completed</span></td></tr></tbody></table><a class="thit-view-all" href="#">View all activity</a></div></div><div class="col-md-3"><div class="panel panel-default thit-panel health-panel"><div class="panel-heading"><i class="fa fa-heartbeat"></i> STOCK HEALTH</div><div class="panel-body"><div class="health-donut"><span>Total<strong id="thit-health-total">204</strong></span></div><div class="health-legend"><span class="teal">&lt;70% Healthy</span><span class="orange">70–89%</span><span class="red">≥90% Full</span></div></div></div></div></div>
            </div>
        </div>
    </div>
</div>
<?php require_once '../clude/foot.php'; ?>
<script src="thit.js"></script>
