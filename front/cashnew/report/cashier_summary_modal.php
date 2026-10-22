<?php
$summary = function_exists('cash_repo_cashier_summary') ? cash_repo_cashier_summary($connection) : [];
$shift = isset($summary['shift']) ? (string)$summary['shift'] : date('Y-m-d');
?>
<style>
  .cashier-summary-toolbar {
    display: flex;
    gap: 8px;
    align-items: center;
    flex-wrap: wrap;
    margin-left: auto;
  }
  .cashier-summary-toolbar .btn {
    border-radius: 20px;
    font-weight: 700;
  }
  .cashier-summary-dialog {
    width: 210mm;
    max-width: 95vw;
    margin: 10px auto;
  }
  .cashier-summary-dialog .modal-content {
    min-height: 297mm;
    padding: 12mm;
    border-radius: 4px;
  }
  .cashier-summary-dialog .modal-body {
    padding: 0;
  }
  @media (max-width: 992px) {
    .cashier-summary-dialog {
      width: auto;
      max-width: 98vw;
    }
    .cashier-summary-dialog .modal-content {
      min-height: auto;
      padding: 16px;
    }
  }
  @media print {
    body.cashier-summary-print-a4, body.cashier-summary-print-thermal {
      background: #fff !important;
    }
    body.cashier-summary-print-a4 .modal-backdrop,
    body.cashier-summary-print-thermal .modal-backdrop,
    body.cashier-summary-print-a4 .cashier-summary-toolbar,
    body.cashier-summary-print-thermal .cashier-summary-toolbar,
    body.cashier-summary-print-a4 .close,
    body.cashier-summary-print-thermal .close {
      display: none !important;
    }
    body.cashier-summary-print-a4 .cashier-summary-dialog {
      width: 210mm !important;
      max-width: 210mm !important;
      margin: 0 !important;
    }
    body.cashier-summary-print-a4 .cashier-summary-dialog .modal-content {
      min-height: 297mm !important;
      padding: 12mm !important;
      border: none !important;
      box-shadow: none !important;
    }
    body.cashier-summary-print-thermal .cashier-summary-dialog {
      width: 80mm !important;
      max-width: 80mm !important;
      margin: 0 !important;
    }
    body.cashier-summary-print-thermal .cashier-summary-dialog .modal-content {
      min-height: auto !important;
      padding: 4mm !important;
      border: none !important;
      box-shadow: none !important;
    }
    body.cashier-summary-print-thermal .col-md-4,
    body.cashier-summary-print-thermal .col-md-6,
    body.cashier-summary-print-a4 .col-md-4,
    body.cashier-summary-print-a4 .col-md-6 {
      float: none !important;
      width: 100% !important;
    }
    body.cashier-summary-print-thermal table,
    body.cashier-summary-print-a4 table {
      width: 100% !important;
      font-size: 10px !important;
    }
  }
</style>
<div class="modal-dialog cashier-summary-dialog" role="document">
  <div class="modal-content">
    <div class="modal-header">
      <button type="button" class="close" data-dismiss="modal" aria-hidden="true">×</button>
      <h4 class="modal-title">Cashier Summary Report</h4>
      <div class="cashier-summary-toolbar">
        <button type="button" class="btn btn-primary btn-sm" id="cashier-summary-print-a4">Print A4</button>
        <button type="button" class="btn btn-warning btn-sm" id="cashier-summary-print-thermal">Print 80mm</button>
      </div>
    </div>
    <div class="modal-body">
      <div class="row">
        <div class="col-md-4"><div class="alert alert-info"><strong>Shift</strong><div><?php echo htmlspecialchars($shift, ENT_QUOTES, 'UTF-8'); ?></div></div></div>
        <div class="col-md-4"><div class="alert alert-success"><strong>Total Revenue</strong><div><?php echo number_format((float)($summary['total_revenue'] ?? 0), 2); ?></div></div></div>
        <div class="col-md-4"><div class="alert alert-warning"><strong>Total Expenses</strong><div><?php echo number_format((float)($summary['total_expenses'] ?? 0), 2); ?></div></div></div>
      </div>
      <div class="row">
        <div class="col-md-4"><div class="alert alert-primary"><strong>All Sales Totals</strong><div><?php echo number_format((float)($summary['all_sales_total'] ?? 0), 2); ?></div></div></div>
        <div class="col-md-4"><div class="alert alert-primary"><strong>All Waiters Totals</strong><div><?php echo number_format((float)($summary['all_waiters_total'] ?? 0), 2); ?></div></div></div>
        <div class="col-md-4"><div class="alert alert-primary"><strong>All Items Sale Totals</strong><div><?php echo number_format((float)($summary['items_sales_total'] ?? 0), 2); ?></div></div></div>
      </div>
      <div class="row">
        <div class="col-md-4"><div class="alert alert-danger"><strong>Credit Sales Today</strong><div><?php echo number_format((float)($summary['credit_sales_today'] ?? 0), 2); ?></div></div></div>
        <div class="col-md-4"><div class="alert alert-success"><strong>Previous Credit Paid</strong><div><?php echo number_format((float)($summary['previous_credit_paid'] ?? 0), 2); ?></div></div></div>
        <div class="col-md-4"><div class="alert alert-info"><strong>Total Purchases</strong><div><?php echo number_format((float)($summary['total_purchases'] ?? 0), 2); ?></div></div></div>
      </div>
      <hr>
      <div class="row">
        <div class="col-md-6">
          <h5>Sales by Counter</h5>
          <table class="table table-striped">
            <thead><tr><th>Counter</th><th class="text-right">Sales</th></tr></thead>
            <tbody>
              <?php foreach (($summary['counter_rows'] ?? []) as $row) { ?>
                <tr><td><?php echo htmlspecialchars((string)($row['counter'] ?? ''), ENT_QUOTES, 'UTF-8'); ?></td><td class="text-right"><?php echo number_format((float)($row['total_amount'] ?? 0), 2); ?></td></tr>
              <?php } ?>
            </tbody>
          </table>
        </div>
        <div class="col-md-6">
          <h5>Sales by Department</h5>
          <table class="table table-striped">
            <thead><tr><th>Department</th><th class="text-right">Sales</th></tr></thead>
            <tbody>
              <?php foreach (($summary['department_rows'] ?? []) as $row) { ?>
                <tr><td><?php echo ((int)($row['dpt'] ?? 0) === 1) ? 'BAR' : 'KITCHEN'; ?></td><td class="text-right"><?php echo number_format((float)($row['total_amount'] ?? 0), 2); ?></td></tr>
              <?php } ?>
            </tbody>
          </table>
        </div>
      </div>
      <div class="row">
        <div class="col-md-6">
          <h5>Sale Payment Totals by Payment Mode</h5>
          <table class="table table-striped">
            <thead><tr><th>Mode</th><th class="text-right">Total</th></tr></thead>
            <tbody>
              <?php foreach (($summary['mode_rows'] ?? []) as $row) { ?>
                <tr><td><?php echo htmlspecialchars((string)($row['name'] ?? ''), ENT_QUOTES, 'UTF-8'); ?></td><td class="text-right"><?php echo number_format((float)($row['total_amount'] ?? 0), 2); ?></td></tr>
              <?php } ?>
            </tbody>
          </table>
        </div>
        <div class="col-md-6">
          <h5>All Waiters</h5>
          <table class="table table-striped">
            <thead><tr><th>Waiter</th><th class="text-right">Bills</th><th class="text-right">Total</th></tr></thead>
            <tbody>
              <?php foreach (($summary['waiter_rows'] ?? []) as $row) { ?>
                <tr>
                  <td><?php echo htmlspecialchars(get_user((int)($row['user_id'] ?? 0)), ENT_QUOTES, 'UTF-8'); ?></td>
                  <td class="text-right"><?php echo (int)($row['sale_count'] ?? 0); ?></td>
                  <td class="text-right"><?php echo number_format((float)($row['total_amount'] ?? 0), 2); ?></td>
                </tr>
              <?php } ?>
            </tbody>
          </table>
        </div>
      </div>
    </div>
  </div>
</div>
<script>
  (function () {
    function printCashierSummary(mode) {
      var body = document.body;
      var cls = mode === 'thermal' ? 'cashier-summary-print-thermal' : 'cashier-summary-print-a4';
      body.classList.remove('cashier-summary-print-a4', 'cashier-summary-print-thermal');
      body.classList.add(cls);
      window.setTimeout(function () {
        window.print();
      }, 50);
    }

    $(document).off('click.cashierSummaryA4').on('click.cashierSummaryA4', '#cashier-summary-print-a4', function () {
      printCashierSummary('a4');
    });

    $(document).off('click.cashierSummaryThermal').on('click.cashierSummaryThermal', '#cashier-summary-print-thermal', function () {
      printCashierSummary('thermal');
    });

    window.addEventListener('afterprint', function () {
      document.body.classList.remove('cashier-summary-print-a4', 'cashier-summary-print-thermal');
    });
  })();
</script>
