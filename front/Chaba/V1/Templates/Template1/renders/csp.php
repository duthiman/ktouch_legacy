<?php
declare(strict_types=1);
$doc = is_array($document ?? null) ? $document : [];
$data = is_array($doc['csp'] ?? null) ? $doc['csp'] : [];
$header = is_array($doc['header'] ?? null) ? $doc['header'] : [];
$e = static fn($v): string => htmlspecialchars((string)$v, ENT_QUOTES | ENT_SUBSTITUTE, 'UTF-8');
$money = static fn($v): string => number_format((float)$v, 2);
$rows = static function ($value): array { return is_array($value) ? $value : []; };
$item = static function (array $row): string { return (string)($row['item_name'] ?? $row['name'] ?? ('Item ' . (int)($row['item_id'] ?? 0))); };
$user = static function (array $row): string { return (string)($row['user_name'] ?? $row['username'] ?? ('User ' . (int)($row['user_id'] ?? 0))); };
$counter = (int)($header['counter_id'] ?? $data['counter_id'] ?? 0);
$counterName = (string)($header['counter_name'] ?? $data['counter_name'] ?? '');
$section = static function (string $title, array $headers, array $items, callable $render, callable $e): void {
    echo '<section><h2>' . $e($title) . '</h2><table><thead><tr>';
    foreach ($headers as $header) echo '<th>' . $e($header) . '</th>';
    echo '</tr></thead><tbody>';
    if (!$items) echo '<tr><td colspan="' . count($headers) . '">No records.</td></tr>';
    foreach ($items as $row) echo $render($row);
    echo '</tbody></table></section>';
};
?><!doctype html>
<html><head><meta charset="utf-8"><title><?= $e($counterName) ?> Counter Summary</title>
<style>
@page{size:79mm auto;margin:0}*{box-sizing:border-box}html,body{margin:0;padding:0;width:79mm;background:#fff;color:#000;font:11px Arial,sans-serif}#receipt{width:79mm;padding:0}#bord{width:100%;border:2px solid #000;padding:2px}.divider{text-align:center;white-space:nowrap;overflow:hidden;font-weight:bold;margin:2px 0}h1{font-size:16px;text-align:center;margin:3px 0}h2{text-align:center;font-size:13px;margin:4px 0;border-top:1px solid #000;border-bottom:1px solid #000;padding:3px 0}table{width:100%;border-collapse:collapse;table-layout:auto}th,td{border:1px solid #000;padding:3px 2px;vertical-align:top;overflow-wrap:anywhere}th{font-size:11px}td.num,th.num{text-align:right;white-space:nowrap}.meta td:first-child{font-weight:bold;width:28%}.meta td:nth-child(2){width:32%}.meta td:last-child{width:40%}.total td{font-weight:bold}@media print{html,body,#receipt{width:79mm!important;margin:0!important;padding:0!important}#bord{width:79mm!important}}
</style><style>.sold-items td,.sold-items th{font-size:12px}.sold-items .category-row td{font-size:12px;font-weight:bold;background:#eee}.grand-total td{font-size:13px;font-weight:bold;padding-top:5px;padding-bottom:5px}</style><style>@page{size:80mm auto;margin:0!important}html,body,#receipt{width:80mm!important;max-width:80mm!important;margin:0!important;padding:0!important}#bord{width:100%!important}</style></head><body><div id="receipt"><div id="bord">
<div class="divider">**************************************************</div>
<h1><?= $e($counterName) ?> COUNTER SUMMARY</h1>
<div class="divider">**************************************************</div>
<table class="meta"><tbody>
<tr><td>OPENING</td><td><?= $e($header['opening_staff'] ?? $header['opening_staff_id'] ?? '') ?></td><td><?= $e($header['opening_at'] ?? '') ?></td></tr>
<tr><td>CLOSING</td><td><?= $e($header['closing_staff'] ?? $header['closing_staff_id'] ?? '') ?></td><td><?= $e($header['closing_at'] ?? '') ?></td></tr>
<tr class="total"><td colspan="2">TOTAL SALES</td><td class="num"><?= $money($header['total_sales'] ?? 0) ?></td></tr>
</tbody></table>
<?php
$transferRows = $rows($data['transfers'] ?? []);
$section('STOCK OUT / COUNTER STOCK TRANSFERS',['ITEM','QTY','TO'],array_values(array_filter($transferRows,fn($r)=>(int)($r['cntr_from']??0)===$counter)),fn($r)=>'<tr><td>'.$e($item($r)).'</td><td class="num">'.$money($r['qty']??0).'</td><td>'.$e($r['to_counter_name']??$r['cntr_to']??'').'</td></tr>',$e);
$section('STOCK OUT / UNIT GENERATED',['ITEM','QTY','USER'],$rows($data['units_generated']??[]),fn($r)=>'<tr><td>'.$e($item($r)).'</td><td class="num">'.$money($r['qty']??0).'</td><td>'.$e($user($r)).'</td></tr>',$e);
$sales=$rows($data['sales']??[]);usort($sales,fn($a,$b)=>strcmp((string)($a['category_name']??'Uncategorised'),(string)($b['category_name']??'Uncategorised')));$salesQty=0;$salesAmount=0;$salesHtml='';$lastCategory=null;foreach($sales as $r){$category=(string)($r['category_name']??'Uncategorised');if($category!==$lastCategory){$salesHtml.='<tr class="category-row"><td colspan="3">'.$e($category).'</td></tr>';$lastCategory=$category;}$qty=(float)($r['qty']??0);$amount=(float)($r['amount']??$r['total']??0);$salesQty+=$qty;$salesAmount+=$amount;$salesHtml.='<tr><td>'.$e($item($r)).'</td><td class="num">'.$money($qty).'</td><td class="num">'.$money($amount).'</td></tr>';}if($salesHtml==='')$salesHtml='<tr><td colspan="3">No records.</td></tr>';echo '<section class="sold-items"><h2>STOCK OUT / SOLD ITEMS</h2><table><thead><tr><th>ITEM</th><th>QTY</th><th>TOTAL</th></tr></thead><tbody>'.$salesHtml.'<tr class="grand-total"><td>TOTAL SOLD</td><td class="num">'.$money($salesQty).'</td><td class="num">'.$money($salesAmount).'</td></tr></tbody></table></section>';
$section('STOCK IN / REQUISITIONS',['ITEM','QTY','ISSUER'],$rows($data['requisitions']??[]),fn($r)=>'<tr><td>'.$e($item($r)).'</td><td class="num">'.$money($r['qty']??0).'</td><td>'.$e($r['issuer_name']??$r['issue_user']??'-').'</td></tr>',$e);
$section('BILLS SUMMARY',['DESCRIPTION','TOTAL'],[['name'=>'TOTAL BILLS','value'=>$data['bill_count']??0]],fn($r)=>'<tr><td>'.$e($r['name']).'</td><td class="num">'.$e($r['value']).'</td></tr>',$e);
$section('DELETED BILLS',['BILL','AMOUNT'],$rows($data['deleted_bills']??[]),fn($r)=>'<tr><td>'.$e($r['sale_id']??$r['id']??'').'</td><td class="num">'.$money($r['amount']??0).'</td></tr>',$e);
$section('USER SALES BY ORDER / TOKENS',['USER','ORDERS','SALES'],$rows($data['user_sales']??[]),fn($r)=>'<tr><td>'.$e($user($r)).'</td><td class="num">'.(int)($r['orders_count']??0).'</td><td class="num">'.$money($r['sales_total']??0).'</td></tr>',$e);
$section('RETURNED ITEMS BY USER',['ITEM','QTY','STATUS','APPR BY'],$rows($data['returns']??[]),fn($r)=>'<tr><td>'.$e($item($r)).'</td><td class="num">'.$money($r['qty']??0).'</td><td>'.$e($r['status']??($r['confirm_status']??'PENDING')).'</td><td>'.$e($r['approver_name']??$r['confirm_by']??'-').'</td></tr>',$e);
$section('STOCK IN / STOCK TRANSFER',['ITEM','QTY','COUNTER','USER'],array_values(array_filter($transferRows,fn($r)=>(int)($r['cntr_to']??0)===$counter)),fn($r)=>'<tr><td>'.$e($item($r)).'</td><td class="num">'.$money($r['qty']??0).'</td><td>'.$e($r['from_counter_name']??$r['cntr_from']??'').'</td><td>'.$e($r['from_user_name']??$r['user_from']??'').'</td></tr>',$e);
$staffRows=$rows($data['staff_sales']??[]);$staffTotal=0;foreach($staffRows as $r)$staffTotal+=(float)($r['sales_total']??0);$section('STAFF SUMMARY',['STAFF','SALES'],$staffRows,fn($r)=>'<tr><td>'.$e($user($r)).'</td><td class="num">'.$money($r['sales_total']??0).'</td></tr>',$e);echo '<table><tbody><tr class="grand-total"><td>TOTAL STAFF SALES</td><td class="num">'.$money($staffTotal).'</td></tr></tbody></table>';
?>
<div class="divider">**************************************************</div>
</div></div></body></html>
