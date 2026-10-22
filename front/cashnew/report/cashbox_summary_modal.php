<?php
$summary = function_exists('cash_repo_cashbox_summary') ? cash_repo_cashbox_summary($connection) : [];
$shift = isset($summary['shift']) ? (string)$summary['shift'] : date('Y-m-d');
?>
<style>
  .cashbox-summary-dialog {
    width: 210mm;
    max-width: 95vw;
    margin: 10px auto;
  }
  .cashbox-summary-dialog .modal-content {
    min-height: 297mm;
    padding: 12mm;
    border-radius: 4px;
  }
  .cashbox-summary-dialog .modal-body {
    padding: 0;
  }
  .cashbox-summary-toolbar {
    display: flex;
    gap: 8px;
    align-items: center;
    flex-wrap: wrap;
    margin-left: auto;
  }
  .cashbox-summary-toolbar .btn {
    border-radius: 20px;
    font-weight: 700;
  }
  @media (max-width: 992px) {
    .cashbox-summary-dialog {
      width: auto;
      max-width: 98vw;
    }
    .cashbox-summary-dialog .modal-content {
      min-height: auto;
      padding: 16px;
    }
  }
  @media print {
    body.cashbox-summary-print-a4, body.cashbox-summary-print-thermal {
      background: #fff !important;
    }
    body.cashbox-summary-print-a4 .modal-backdrop,
    body.cashbox-summary-print-thermal .modal-backdrop,
    body.cashbox-summary-print-a4 .cashbox-summary-toolbar,
    body.cashbox-summary-print-thermal .cashbox-summary-toolbar,
    body.cashbox-summary-print-a4 .close,
    body.cashbox-summary-print-thermal .close {
      display: none !important;
    }
    body.cashbox-summary-print-a4 .cashbox-summary-dialog {
      width: 210mm !important;
      max-width: 210mm !important;
      margin: 0 !important;
    }
    body.cashbox-summary-print-a4 .cashbox-summary-dialog .modal-content {
      min-height: 297mm !important;
      padding: 12mm !important;
      border: none !important;
      box-shadow: none !important;
    }
    body.cashbox-summary-print-thermal .cashbox-summary-dialog {
      width: 80mm !important;
      max-width: 80mm !important;
      margin: 0 !important;
    }
    body.cashbox-summary-print-thermal .cashbox-summary-dialog .modal-content {
      min-height: auto !important;
      padding: 4mm !important;
      border: none !important;
      box-shadow: none !important;
    }
    body.cashbox-summary-print-thermal table,
    body.cashbox-summary-print-a4 table {
      width: 100% !important;
      font-size: 10px !important;
    }
  }
</style>
<div class="modal-dialog cashbox-summary-dialog" role="document">
  <div class="modal-content">
    <div class="modal-header">
      <button type="button" class="close" data-dismiss="modal" aria-hidden="true">×</button>
      <h4 class="modal-title">Cashbox Summary Report</h4>
      <div class="cashbox-summary-toolbar">
        <button type="button" class="btn btn-primary btn-sm" id="cashbox-summary-print-a4">Print A4</button>
        <button type="button" class="btn btn-warning btn-sm" id="cashbox-summary-print-thermal">Print 80mm</button>
      </div>
    </div>
    <div class="modal-body">
      <div class="row">
        <div class="col-md-3"><div class="alert alert-info"><strong>Shift</strong><div><?php echo htmlspecialchars($shift, ENT_QUOTES, 'UTF-8'); ?></div></div></div>
        <div class="col-md-3"><div class="alert alert-success"><strong>Today Paid</strong><div><?php echo number_format((float)($summary['today_paid'] ?? 0), 2); ?></div></div></div>
        <div class="col-md-3"><div class="alert alert-warning"><strong>Previous Paid</strong><div><?php echo number_format((float)($summary['previous_paid'] ?? 0), 2); ?></div></div></div>
        <div class="col-md-3"><div class="alert alert-primary"><strong>Total Paid</strong><div><?php echo number_format((float)($summary['total_paid'] ?? 0), 2); ?></div></div></div>
      </div>
      <div class="row">
        <div class="col-md-4"><div class="alert alert-danger"><strong>Credit Paid</strong><div><?php echo number_format((float)($summary['credit_paid'] ?? 0), 2); ?></div></div></div>
        <div class="col-md-4"><div class="alert alert-info"><strong>Payment Entries</strong><div><?php echo (int)($summary['payment_count'] ?? 0); ?></div></div></div>
        <div class="col-md-4"><div class="alert alert-success"><strong>Payment Modes</strong><div><?php echo count($summary['mode_rows'] ?? []); ?></div></div></div>
      </div>
      <div class="row">
        <div class="col-md-6">
          <h5>Payment Totals by Mode</h5>
          <table class="table table-striped">
            <thead><tr><th>Mode</th><th class="text-right">Count</th><th class="text-right">Total</th></tr></thead>
            <tbody>
              <?php foreach (($summary['mode_rows'] ?? []) as $row) { ?>
                <tr>
                  <td><?php echo htmlspecialchars((string)($row['name'] ?? ''), ENT_QUOTES, 'UTF-8'); ?></td>
                  <td class="text-right"><?php echo (int)($row['payment_count'] ?? 0); ?></td>
                  <td class="text-right"><?php echo number_format((float)($row['total_amount'] ?? 0), 2); ?></td>
                </tr>
              <?php } ?>
            </tbody>
          </table>
        </div>
        <div class="col-md-6">
          <h5>Payment Rows</h5>
          <table class="table table-striped">
            <thead><tr><th>Pay ID</th><th>Bill(s)</th><th class="text-right">Total</th></tr></thead>
            <tbody>
              <?php foreach (($summary['cashbox_rows'] ?? []) as $row) { ?>
                <tr>
                  <td><?php echo (int)($row['pay_id'] ?? 0); ?></td>
                  <td style="min-width:180px;"><?php echo htmlspecialchars((string)($row['sale_id'] ?? ''), ENT_QUOTES, 'UTF-8'); ?></td>
                  <td class="text-right"><?php echo number_format((float)($row['paid_total'] ?? 0), 2); ?></td>
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
    function printCashboxSummary(mode) {
      var body = document.body;
      var cls = mode === 'thermal' ? 'cashbox-summary-print-thermal' : 'cashbox-summary-print-a4';
      body.classList.remove('cashbox-summary-print-a4', 'cashbox-summary-print-thermal');
      body.classList.add(cls);
      window.setTimeout(function () {
        window.print();
      }, 50);
    }

    $(document).off('click.cashboxSummaryA4').on('click.cashboxSummaryA4', '#cashbox-summary-print-a4', function () {
      printCashboxSummary('a4');
    });

    $(document).off('click.cashboxSummaryThermal').on('click.cashboxSummaryThermal', '#cashbox-summary-print-thermal', function () {
      printCashboxSummary('thermal');
    });

    window.addEventListener('afterprint', function () {
      document.body.classList.remove('cashbox-summary-print-a4', 'cashbox-summary-print-thermal');
    });
  })();
</script>
