<?php
include('../../inc/connection.php');
include('../../inc/gen_funcs.php');
include_once('funcs.php');
require_once(__DIR__ . '/render_css.php');
require_once(__DIR__ . '/api/chaba_helpers.php');
require_once(__DIR__ . '/api/chaba_schema.php');
require_once(__DIR__ . '/api/chaba_repository.php');
require_once(__DIR__ . '/api/chaba_service.php');

$sale_id = (int)($_GET['sale_id'] ?? 0);
$auto_print = (int)($_GET['auto_print'] ?? 0) === 1;
if ($sale_id < 1) {
    exit('Invalid sale');
}

chaba_api_ensure_schema($connection);
$billResult = chaba_service_get_bill_payload($sale_id);
if (empty($billResult['ok'])) {
    exit((string)($billResult['message'] ?? 'Failed to load bill'));
}
$payload = $billResult['payload'] ?? [];
$context = $payload['context'] ?? [];
$company = $payload['company'] ?? [];
$settings = $payload['print_settings'] ?? [];
$items = is_array($payload['items'] ?? null) ? $payload['items'] : [];
$hourOne = [];
$hourTwo = [];
foreach ($items as $item) {
    if ((int)($item['hour'] ?? 1) === 2) {
        $hourTwo[] = $item;
    } else {
        $hourOne[] = $item;
    }
}

function chaba_bill_esc($value)
{
    return htmlspecialchars((string)$value, ENT_QUOTES, 'UTF-8');
}

function chaba_bill_money($value)
{
    return number_format((float)$value, 2, '.', '');
}

$print_template = function_exists('get_print_template') ? (int)get_print_template() : 1;
$billCssPath = realpath(__DIR__ . '/../assets/css/chaba_bill_template' . ($print_template === 2 ? '2' : '1') . '.css');
$bill_template_css = ($billCssPath && is_file($billCssPath)) ? (file_get_contents($billCssPath) ?: '') : '';
?>
<!DOCTYPE html>
<html lang="en">
<head>
  <meta charset="UTF-8">
  <title>BILL #<?php echo $sale_id; ?></title>
  <style>
    <?php echo kp_render_print_template_css($print_template); ?>
    <?php echo $bill_template_css; ?>
  </style>
</head>
<body>
<?php
$amount = (float)($payload['amount'] ?? $context['amount'] ?? (($context['totals']['items_total'] ?? 0) ?: ($context['totals']['print_total'] ?? 0)));
$vat = (float)($payload['vat'] ?? 0);
$ctl = (float)($payload['ctl'] ?? 0);
$mpesaLabel = (string)($payload['mpesa_label'] ?? '');
$servedBy = strtoupper((string)($payload['served_by_name'] ?? $context['waiter_name'] ?? ''));
$continueSummary = $payload['continue_summary'] ?? [];
$saleTime = date('h:i:a');
?>
<?php if ($print_template === 2): ?>
<div id="receipt"><div id="bord">
  <div class="text-center">
    <?php if ((int)($settings['show_logo'] ?? 0) === 1 && !empty($company['thermal_logo_path'])): ?>
      <img src="<?php echo chaba_bill_esc($company['thermal_logo_path']); ?>" style="height:<?php echo (int)($settings['thermal_logo_height'] ?? 60); ?>px;width:auto;margin-top:10px;"><br>
    <?php endif; ?>
    <h1 class="title"><?php echo chaba_bill_esc(strtoupper((string)($company['company_name'] ?? ''))); ?></h1>
    <?php if (!empty($company['company_tag'])): ?><p style="margin:0;"><em><?php echo chaba_bill_esc($company['company_tag']); ?></em></p><?php endif; ?>
    <hr class="sep-dot">
    <?php if (!empty($company['address'])): ?><p style="margin:0;"><?php echo chaba_bill_esc($company['address']); ?></p><?php endif; ?>
    <p style="margin:0;">Cell: <?php echo chaba_bill_esc($company['phone1'] ?? ''); ?> | <?php echo chaba_bill_esc($company['phone2'] ?? ''); ?></p>
    <?php if ((int)($settings['pin'] ?? 0) === 1): ?><p style="margin:0;text-align:center;">PIN: <?php echo chaba_bill_esc($company['kra_pin'] ?? ''); ?></p><?php endif; ?>
  </div>
  <hr class="sep-eq">
  <div class="meta center"><span>BILL NO: <b><?php echo (int)($context['sale_id'] ?? $sale_id); ?></b></span></div>
  <div class="meta center"><span>SERVED BY: <b><?php echo chaba_bill_esc($servedBy); ?></b></span></div>
  <div class="meta"><span>SHIFT: <b><?php echo chaba_bill_esc($context['shift'] ?? ''); ?></b></span><span>TIME: <b><?php echo chaba_bill_esc($saleTime); ?></b></span></div>
  <?php if (!empty($payload['show_continue_summary'])): ?>
    <div class="meta"><span>CONTINUATION</span><span>#<?php echo (int)($continueSummary['parent_bill'] ?? 0); ?></span></div>
    <div class="meta"><span>OPEN STATE</span><span>STILL CONTINUING</span></div>
  <?php endif; ?>
  <hr class="sep-eq">
  <table class="items-tbl">
    <thead><tr><th>Item</th><th>Qty</th><th>Price</th><th>Total</th></tr></thead>
    <tbody>
      <?php foreach ($hourOne as $item): ?>
        <tr><td><?php echo chaba_bill_esc($item['name'] ?? ''); ?></td><td><?php echo (float)($item['qty'] ?? 0); ?></td><td><?php echo chaba_bill_money($item['price'] ?? 0); ?></td><td><?php echo chaba_bill_money($item['total'] ?? 0); ?></td></tr>
      <?php endforeach; ?>
      <?php if (!empty($hourTwo)): ?>
        <tr class="sec-label"><td colspan="4">HAPPY HOUR ORDER</td></tr>
        <?php foreach ($hourTwo as $item): ?>
          <tr><td><?php echo chaba_bill_esc($item['name'] ?? ''); ?></td><td><?php echo (float)($item['qty'] ?? 0); ?></td><td><?php echo chaba_bill_money($item['price'] ?? 0); ?></td><td><?php echo chaba_bill_money($item['total'] ?? 0); ?></td></tr>
        <?php endforeach; ?>
      <?php endif; ?>
    </tbody>
  </table>
  <?php if (!empty($payload['show_continue_summary'])): ?>
    <hr>
    <table><thead>
      <tr><th colspan="4"><h2 style="margin:0;font-size:16px;">BILL NO. <?php echo (int)($continueSummary['bill_no'] ?? 0); ?></h2></th></tr>
      <tr><th colspan="4"><b style="font-size:12px;">PREVIOUS BILL NO. <?php echo (int)($continueSummary['previous_bill_sale_id'] ?? 0); ?></b></th></tr>
      <tr><th colspan="2"><b style="font-size:14px;">PREVIOUS TOTAL:</b></th><th colspan="2" class="text-center"><b style="font-size:14px;"><?php echo chaba_bill_money($continueSummary['previous_total'] ?? 0); ?></b></th></tr>
      <tr><th colspan="2"><b style="font-size:14px;">CURRENT BILL:</b></th><th colspan="2" class="text-center"><b style="font-size:14px;"><?php echo chaba_bill_money($continueSummary['current_bill'] ?? 0); ?></b></th></tr>
      <tr><th colspan="2" class="text-center"><b style="font-size:22px;">BALANCE:</b></th><th colspan="2" class="text-center"><b style="font-size:22px;"><?php echo chaba_bill_money($continueSummary['balance'] ?? 0); ?></b></th></tr>
    </thead></table>
  <?php endif; ?>
  <table class="totals-tbl">
    <tr class="grand"><td><strong>TOTAL</strong></td><td><strong><?php echo chaba_bill_money($amount); ?></strong></td></tr>
    <?php if ((int)($settings['tax'] ?? 0) === 2): ?>
      <tr><td>Gross</td><td><?php echo chaba_bill_money($amount); ?></td></tr>
      <tr><td>V.A.T (16%)</td><td><?php echo chaba_bill_money($vat); ?></td></tr>
      <tr><td>Cat Levy (2%)</td><td><?php echo chaba_bill_money($ctl); ?></td></tr>
    <?php endif; ?>
  </table>
  <hr class="sep-dot">
  <p class="text-center" style="margin:0;"><b>THANK YOU</b></p>
  <p class="text-center" style="margin:0;"><?php echo chaba_bill_esc($settings['thankyou_note'] ?? ''); ?></p>
  <p class="text-center" style="margin:0;">This is not an ETR. Request one.</p>
  <hr class="sep-dot">
  <div class="mpesa-line"><?php echo chaba_bill_esc($mpesaLabel); ?></div>
  <hr class="sep-dot">
  <p class="text-center" style="margin:0;font-size:11px;">K-TOUCH POS Designed &amp; Distributed by K-TEN<br>+254716100335 | +254735100335</p>
</div></div>
<?php else: ?>
<div id="receipt"><div id="bord">
  <div id="receipt-header" class="text-center">
    <p class="receipt-divider">**************************************************</p>
    <?php if ((int)($settings['show_logo'] ?? 0) === 1 && !empty($company['thermal_logo_path'])): ?>
      <img src="<?php echo chaba_bill_esc($company['thermal_logo_path']); ?>" style="height:<?php echo (int)($settings['thermal_logo_height'] ?? 60); ?>px;width:auto;margin-top:10px;"><br>
    <?php endif; ?>
    <h1 class="title"><?php echo chaba_bill_esc(strtoupper((string)($company['company_name'] ?? ''))); ?></h1>
    <?php if (!empty($company['company_tag'])): ?><p style="font-size:18px;margin:0;">(<?php echo chaba_bill_esc($company['company_tag']); ?>)</p><?php endif; ?>
    <p class="receipt-divider">**************************************************</p>
    <?php if (!empty($company['address'])): ?><p style="margin:0;"><?php echo chaba_bill_esc($company['address']); ?></p><?php endif; ?>
    <p style="margin:0;">Cell: <?php echo chaba_bill_esc($company['phone1'] ?? ''); ?> | <?php echo chaba_bill_esc($company['phone2'] ?? ''); ?></p>
    <?php if ((int)($settings['pin'] ?? 0) === 1): ?><p style="margin:0;text-align:center;">PIN: <?php echo chaba_bill_esc($company['kra_pin'] ?? ''); ?></p><?php endif; ?>
    <p class="receipt-divider">**************************************************</p>
    <div style="display:flex;justify-content:space-between;gap:8px;font-weight:bold;"><span style="display:block;width:100%;text-align:center;">BILL No: <?php echo (int)($context['sale_id'] ?? $sale_id); ?></span></div>
    <div style="text-align:center;font-weight:bold;"><span>SERVED BY: <?php echo chaba_bill_esc($servedBy); ?></span></div>
    <div style="display:flex;justify-content:space-between;gap:8px;font-weight:bold;"><span>SHIFT: <?php echo chaba_bill_esc($context['shift'] ?? ''); ?></span><span>TIME: <?php echo chaba_bill_esc($saleTime); ?></span></div>
    <p class="receipt-divider">**************************************************</p>
  </div>
  <div id="receipt-body">
    <table class="table table-bordered" id="deSo">
      <thead><tr><th>Qty</th><th>Item</th><th>Amount</th><th>Total</th></tr></thead>
      <tfoot><tr><th colspan="4" class="text-center"><b style="font-size:22px;">Total: <?php echo chaba_bill_money($amount); ?></b></th></tr></tfoot>
      <tbody>
        <?php foreach ($hourOne as $item): ?>
          <tr><td><?php echo (float)($item['qty'] ?? 0); ?></td><td><?php echo chaba_bill_esc($item['name'] ?? ''); ?></td><td><?php echo chaba_bill_money($item['price'] ?? 0); ?></td><td><?php echo chaba_bill_money($item['total'] ?? 0); ?></td></tr>
        <?php endforeach; ?>
        <?php if (!empty($hourTwo)): ?>
          <tr><td colspan="4"><b>HAPPY HOUR ORDER</b></td></tr>
          <?php foreach ($hourTwo as $item): ?>
            <tr><td><?php echo (float)($item['qty'] ?? 0); ?></td><td><?php echo chaba_bill_esc($item['name'] ?? ''); ?></td><td><?php echo chaba_bill_money($item['price'] ?? 0); ?></td><td><?php echo chaba_bill_money($item['total'] ?? 0); ?></td></tr>
          <?php endforeach; ?>
        <?php endif; ?>
      </tbody>
    </table>
    <?php if (!empty($payload['show_continue_summary'])): ?>
      <p class="receipt-divider">**************************************************</p>
      <table class="open-demo-summary"><thead>
        <tr class="bill-no"><th colspan="4"><h2 style="margin:0;font-size:16px;">BILL NO. <?php echo (int)($continueSummary['bill_no'] ?? 0); ?></h2></th></tr>
        <tr class="prev-bill"><th colspan="4"><b>PREVIOUS BILL NO. <?php echo (int)($continueSummary['previous_bill_sale_id'] ?? 0); ?></b></th></tr>
        <tr class="line-label"><th colspan="2"><b>PREVIOUS TOTAL</b></th><th colspan="2"><b><?php echo chaba_bill_money($continueSummary['previous_total'] ?? 0); ?></b></th></tr>
        <tr class="line-value"><th colspan="2"><b>CURRENT BILL</b></th><th colspan="2"><b><?php echo chaba_bill_money($continueSummary['current_bill'] ?? 0); ?></b></th></tr>
        <tr class="total-value"><th colspan="2"><b>BALANCE</b></th><th colspan="2"><b><?php echo chaba_bill_money($continueSummary['balance'] ?? 0); ?></b></th></tr>
      </thead></table>
    <?php endif; ?>
  </div>
  <div id="receipt-footer">
    <?php if ((int)($settings['tax'] ?? 0) === 2): ?>
      <p class="receipt-divider">**************************************************</p>
      <span class="pull-left">Gross: <?php echo chaba_bill_money($amount); ?></span>
      <span class="pull-right">V.A.T(16%): <?php echo chaba_bill_money($vat); ?></span><br>
      <span class="pull-right">Cat Levy(2%): <?php echo chaba_bill_money($ctl); ?></span><br>
    <?php endif; ?>
    <p class="receipt-divider">**************************************************</p>
    <p class="text-center" style="margin:0;"><b>THANK YOU</b></p>
    <p class="text-center" style="margin:0;"><?php echo chaba_bill_esc($settings['thankyou_note'] ?? ''); ?></p>
    <p class="text-center" style="margin:0;">This is not an ETR. Request one.</p>
    <p class="receipt-divider">**************************************************</p>
    <h2 class="text-center" style="margin:0;font-size:20px;font-weight:bold;letter-spacing:3px;"><?php echo chaba_bill_esc($mpesaLabel); ?></h2>
    <p class="receipt-divider">**************************************************</p>
    <p class="text-center" style="margin:0;font-size:11px;">K-TOUCH POS Designed &amp; Distributed by K-TEN<br>+254716100335 | +254735100335</p>
    <p class="receipt-divider">**************************************************</p>
  </div>
</div></div>
<?php endif; ?>
<?php if ($auto_print): ?>
<script>
window.addEventListener('load', function () {
  window.print();
});
</script>
<?php endif; ?>
</body>
</html>
