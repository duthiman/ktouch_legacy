<?php
if (!isset($ticket_payload) || !is_array($ticket_payload)) {
    exit('Ticket payload missing');
}

$ticket_context = $ticket_payload['context'] ?? [];
$ticket_lines = $ticket_payload['lines'] ?? [];
$ticket_sections = $ticket_payload['sections'] ?? [];
$ticket_sale_id = (int)($ticket_context['sale_id'] ?? 0);
$ticket_waiter = (string)($ticket_context['waiter_name'] ?? '');
$ticket_counter = (string)($ticket_context['counter_name'] ?? '');
$ticket_shift = (string)($ticket_context['shift'] ?? '');
$ticket_total = 0.0;

if (!empty($ticket_sections)) {
    foreach ($ticket_sections as $ticket_section) {
        foreach (($ticket_section['lines'] ?? []) as $ticket_line) {
            $ticket_total += (float)($ticket_line['total'] ?? 0);
        }
    }
} else {
    foreach ($ticket_lines as $ticket_line) {
        $ticket_total += (float)($ticket_line['total'] ?? 0);
    }
}
?>
<!DOCTYPE html>
<html lang="en">
<head>
  <meta charset="UTF-8">
  <title><?php echo htmlspecialchars((string)$ticket_page_title); ?> #<?php echo $ticket_sale_id; ?></title>
  <style>
    <?php echo kp_render_print_template_css($print_template); ?>
    <?php
      $ticketCssPath = realpath(__DIR__ . '/../assets/css/chaba_bill_template.css');
      echo ($ticketCssPath && is_file($ticketCssPath)) ? file_get_contents($ticketCssPath) : '';
    ?>
  </style>
</head>
<body class="chaba-ticket-shell">
<div id="receipt">
  <div id="bord">
    <div id="receipt-header" class="text-center">
      <p class="receipt-divider"><?php echo htmlspecialchars($receiptDivider); ?></p>
      <?php if (!empty($ticket_counter) && !empty($ticket_show_counter_name)) { ?>
      <h1 style="margin:0;"><?php echo htmlspecialchars($ticket_counter); ?></h1>
      <p class="receipt-divider"><?php echo htmlspecialchars($receiptDivider); ?></p>
      <?php } ?>
      <h2 style="margin:0;"><?php echo htmlspecialchars((string)$ticket_heading); ?></h2>
      <p class="receipt-divider"><?php echo htmlspecialchars($receiptDivider); ?></p>
      <h4 style="margin:0;"><u>WAITER: <?php echo htmlspecialchars(strtoupper($ticket_waiter)); ?></u></h4>
      <h2 style="margin:0;">Order No: <?php echo $ticket_sale_id; ?></h2>
      <span><strong>SHIFT: <?php echo htmlspecialchars($ticket_shift); ?></strong></span><br>
      <span><strong>PRINT TIME: <?php echo totime(); ?></strong></span>
      <p class="receipt-divider"><?php echo htmlspecialchars($receiptDivider); ?></p>
    </div>
    <div id="receipt-body">
      <table class="<?php echo $print_template === 2 ? 'items-tbl' : 'table table-bordered'; ?>" id="deSo">
        <thead>
          <?php if ($print_template === 2): ?>
          <tr>
            <th>Item</th><th>Qty</th><th>Price</th><th>Total</th>
          </tr>
          <?php else: ?>
          <tr>
            <th>Qty</th><th>Item</th><th>Amount</th><th>Total</th>
          </tr>
          <?php endif; ?>
        </thead>
        <tbody>
          <?php if (!empty($ticket_sections)) { ?>
            <?php foreach ($ticket_sections as $ticket_section) { ?>
              <?php if (empty($ticket_section['lines'])) { continue; } ?>
              <tr class="section-row">
                <td colspan="4"><?php echo htmlspecialchars((string)($ticket_section['label'] ?? '')); ?></td>
              </tr>
              <?php foreach (($ticket_section['lines'] ?? []) as $ticket_line) { ?>
              <tr>
                <?php if ($print_template === 2): ?>
                <td><?php echo htmlspecialchars((string)($ticket_line['name'] ?? '')); ?></td>
                <td><?php echo number_format((float)($ticket_line['qty'] ?? 0), 2); ?></td>
                <td><?php echo number_format((float)($ticket_line['price'] ?? 0), 2); ?></td>
                <td><?php echo number_format((float)($ticket_line['total'] ?? 0), 2); ?></td>
                <?php else: ?>
                <td><?php echo number_format((float)($ticket_line['qty'] ?? 0), 2); ?></td>
                <td><?php echo htmlspecialchars((string)($ticket_line['name'] ?? '')); ?></td>
                <td><?php echo number_format((float)($ticket_line['price'] ?? 0), 2); ?></td>
                <td><?php echo number_format((float)($ticket_line['total'] ?? 0), 2); ?></td>
                <?php endif; ?>
              </tr>
              <?php } ?>
            <?php } ?>
          <?php } else { ?>
            <?php foreach ($ticket_lines as $ticket_line) { ?>
            <tr>
              <?php if ($print_template === 2): ?>
              <td><?php echo htmlspecialchars((string)($ticket_line['name'] ?? '')); ?></td>
              <td><?php echo number_format((float)($ticket_line['qty'] ?? 0), 2); ?></td>
              <td><?php echo number_format((float)($ticket_line['price'] ?? 0), 2); ?></td>
              <td><?php echo number_format((float)($ticket_line['total'] ?? 0), 2); ?></td>
              <?php else: ?>
              <td><?php echo number_format((float)($ticket_line['qty'] ?? 0), 2); ?></td>
              <td><?php echo htmlspecialchars((string)($ticket_line['name'] ?? '')); ?></td>
              <td><?php echo number_format((float)($ticket_line['price'] ?? 0), 2); ?></td>
              <td><?php echo number_format((float)($ticket_line['total'] ?? 0), 2); ?></td>
              <?php endif; ?>
            </tr>
            <?php } ?>
          <?php } ?>
        </tbody>
        <tfoot>
          <tr>
            <th colspan="4" class="text-center">
              <b style="font-size:22px;">TOTAL: <?php echo number_format($ticket_total, 2); ?></b>
            </th>
          </tr>
          <?php if (!empty($ticket_footer_rows) && is_array($ticket_footer_rows)) { ?>
            <?php foreach ($ticket_footer_rows as $ticket_footer_row) { ?>
            <tr style="height:<?php echo htmlspecialchars((string)($ticket_footer_row['height'] ?? '30px')); ?>;">
              <th colspan="<?php echo (int)($ticket_footer_row['colspan'] ?? 4); ?>"><?php echo $ticket_footer_row['label']; ?></th>
            </tr>
            <?php } ?>
          <?php } ?>
        </tfoot>
      </table>
    </div>
    <p class="receipt-divider"><?php echo htmlspecialchars($receiptDivider); ?></p>
    <br>
  </div>
</div>
</body>
</html>
