<?php
$templateType = 'SOT';
$document['document_type'] = 'SOT';
$document['title'] = $document['title'] ?? 'SWAP ORDER TICKET';
$header = is_array($document['header'] ?? null) ? $document['header'] : [];
$old = is_array($document['old_items'] ?? null) ? $document['old_items'] : [];
$new = is_array($document['new_items'] ?? null) ? $document['new_items'] : [];
$e = static fn($v) => htmlspecialchars((string)$v, ENT_QUOTES | ENT_SUBSTITUTE, 'UTF-8');
$q = static fn($v) => ((float)$v == floor((float)$v)) ? (string)(int)$v : rtrim(rtrim(number_format((float)$v, 3, '.', ''), '0'), '.');
?>
<style>
@page{size:80mm auto;margin:0}*{box-sizing:border-box}html,body{width:80mm;max-width:80mm;margin:0;padding:0;background:#fff;color:#000;font:13px Arial,sans-serif}.sot{width:80mm;padding:1mm}.sot-border{border:2px solid #000;padding:2mm}.center{text-align:center}.divider{margin:0;line-height:1;white-space:nowrap;overflow:hidden;font-weight:700}.sot h1{font-size:22px;margin:2px 0}.sot h2{font-size:16px;margin:3px 0}.meta{font-size:12px;font-weight:700;margin:2px 0}.sot table{width:100%;border-collapse:collapse;table-layout:fixed;margin:5px 0}.sot th,.sot td{border:1px solid #000;padding:4px 3px;font-size:13px;overflow-wrap:anywhere}.sot th:first-child,.sot td:first-child{width:82%;text-align:left}.sot th:last-child,.sot td:last-child{width:18%;text-align:right}.section{background:#000;color:#fff;font-weight:900;text-align:center}.note{text-align:center;font-size:11px;font-weight:700}@media print{html,body{width:80mm!important;margin:0!important}.sot{padding:.5mm}}
</style>
<main class="sot"><div class="sot-border">
<div class="divider">***********************************************************</div>
<div class="center"><h1><?= $e($header['counter_name'] ?? '') ?></h1><h2>SWAP ORDER TICKET (SOT)</h2><div class="meta">WAITER: <?= $e(strtoupper($header['waiter_name'] ?? $header['served_by'] ?? '')) ?></div><div class="meta">OLD BILL: <?= $e($header['original_sale_id'] ?? '') ?> &nbsp; NEW BILL: <?= $e($header['sale_id'] ?? '') ?></div><div class="meta">SHIFT: <?= $e($header['shift'] ?? $header['shift_date'] ?? '') ?></div><div class="meta">PRINT TIME: <?= $e($header['print_time'] ?? date('Y-m-d H:i:s')) ?></div></div>
<div class="divider">***********************************************************</div>
<?php foreach ([['GIVE BACK / REMOVE',$old],['RECEIVE / GIVE CUSTOMER',$new]] as [$title,$rows]): ?><table><thead><tr><th class="section" colspan="2"><?= $e($title) ?></th></tr><tr><th>ITEM</th><th>QTY</th></tr></thead><tbody><?php if (!$rows): ?><tr><td colspan="2" class="center">None</td></tr><?php endif; ?><?php foreach($rows as $row): ?><tr><td><?= $e($row['name'] ?? $row['item_name'] ?? '') ?></td><td><?= $q($row['qty'] ?? 0) ?></td></tr><?php endforeach; ?></tbody></table><?php endforeach; ?>
<p class="note">CHECK ITEMS BEFORE HANDING OVER THE REPLACEMENT.</p><div class="divider">***********************************************************</div>
</div></main>
