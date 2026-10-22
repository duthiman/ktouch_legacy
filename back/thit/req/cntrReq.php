<div class="panel panel-purple thit-counter-request-shell">
  <div class="panel-heading">
    <strong><i class="fa fa-exchange"></i> PENDING COUNTER REQUESTS</strong>
    <button type="button" class="btn btn-default btn-xs pull-right" id="thit-counter-requests-refresh"><i class="fa fa-refresh"></i> Refresh</button>
  </div>
  <div class="panel-body">
    <div class="alert alert-info" id="thit-counter-requests-status"><i class="fa fa-spinner fa-spin"></i> Loading pending counter requests...</div>
    <div class="table-responsive">
      <table class="table table-striped table-bordered table-hover">
        <thead><tr><th>No.</th><th>Request No.</th><th>Requested By</th><th>Counter</th><th>Shift</th><th>Items</th><th>Status</th><th>Action</th></tr></thead>
        <tbody id="thit-counter-requests-body"></tbody>
      </table>
    </div>
  </div>
</div>

<div id="thit-issue-notice" class="modal fade" tabindex="-1" role="dialog" aria-hidden="true">
  <div class="modal-dialog modal-sm" role="document">
    <div class="modal-content thit-issue-modal">
      <div class="modal-header">
        <button type="button" class="close" data-dismiss="modal" aria-label="Close"><span aria-hidden="true">&times;</span></button>
        <div class="thit-issue-modal-icon"><i class="fa fa-exchange"></i></div>
        <h4 class="modal-title">Issue Counter</h4>
      </div>
      <div class="modal-body text-center">
        <p class="lead">Do you want to issue this counter request?</p>
        <p class="text-muted" id="thit-issue-notice-details"></p>
      </div>
      <div class="modal-footer">
        <button type="button" class="btn btn-default" data-dismiss="modal">Not Now</button>
        <button type="button" class="btn btn-success" id="thit-confirm-issue"><i class="fa fa-check"></i> Yes, Issue</button>
      </div>
    </div>
  </div>
</div>
