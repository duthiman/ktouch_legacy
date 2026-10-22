<?php
$header = is_array($document['header'] ?? null) ? $document['header'] : [];
$items = is_array($document['items'] ?? null) ? $document['items'] : [];
$esc = static fn($value) => htmlspecialchars((string)$value, ENT_QUOTES | ENT_SUBSTITUTE, 'UTF-8');
$divider = str_repeat('*', 59);
?><!doctype html>
<html lang="en">
<head>
<meta charset="utf-8">
<title>Requisition <?= $esc($header['ref_id'] ?? $header['req_id'] ?? '') ?></title>
<style>
@page{size:80mm auto;margin:0}
*{box-sizing:border-box}
body{width:80mm;margin:0;padding:0;background:#fff;color:#000;font:13px Arial,Helvetica,sans-serif}
.receipt{width:79mm;max-width:79mm;margin:0 auto;padding:1.5mm 0}
.border{width:100%;border:2px solid #000;padding:1mm}
.center{text-align:center}.divider{margin:2px 0;line-height:1.1;white-space:nowrap;overflow:hidden;font-weight:700;letter-spacing:.04em}
h1{font-size:22px;line-height:1.05;margin:1px 0;font-weight:900;letter-spacing:.04em}
h2{font-size:15px;line-height:1.05;margin:2px 0 3px;font-weight:900;letter-spacing:.08em}
.meta{display:grid;grid-template-columns:1fr 1fr;gap:2px 8px;margin:2px 0;font-size:12px;font-weight:800;line-height:1.15}
.meta span:nth-child(even){text-align:right}.single{margin:2px 0;text-align:center;font-size:12px;font-weight:800}
table{width:100%;border-collapse:collapse;table-layout:fixed;margin:2px 0}th,td{padding:3px 1px;border:1px solid #000;font-size:12px}th{text-transform:uppercase;font-size:11px}th:first-child,td:first-child{text-align:left;width:78%}th:last-child,td:last-child{text-align:right;width:22%}
@media print{body{width:80mm!important}.receipt{width:79mm!important;padding:1.5mm 0!important}}
</style>
</head>
<body><main class="receipt"><div class="border">
<header class="center">
<div class="divider"><?= $esc($divider) ?></div>
<h1><?= $esc($header['counter_name'] ?? $header['counter'] ?? '') ?></h1>
<div class="divider"><?= $esc($divider) ?></div>
<h2>REQUESTED ITEMS</h2>
<div class="divider"><?= $esc($divider) ?></div>
<div class="single">REQ BY: <?= $esc(strtoupper($header['requested_by_name'] ?? $header['served_by'] ?? $header['user'] ?? '')) ?></div>
<div class="single">REQ NO: <?= $esc($header['ref_id'] ?? $header['req_id'] ?? '') ?></div>
<div class="meta"><span>SHIFT: <?= $esc($header['shift'] ?? '') ?></span><span>PRINT: <?= $esc($header['print_time'] ?? date('Y-m-d H:i:s')) ?></span></div>
<div class="divider"><?= $esc($divider) ?></div>
</header>
<table><thead><tr><th>Name</th><th>Qty</th></tr></thead><tbody>
<?php foreach ($items as $item): ?><tr><td><?= $esc($item['name'] ?? $item['item_name'] ?? '') ?></td><td><?= $esc($item['qty'] ?? $item['req_qty'] ?? 0) ?></td></tr><?php endforeach; ?>
</tbody></table>
<div class="divider"><?= $esc($divider) ?></div><div class="divider"><?= $esc($divider) ?></div>
</div></main></body></html>
