<?php
$header = is_array($document['header'] ?? null) ? $document['header'] : [];
$items = is_array($document['items'] ?? null) ? $document['items'] : [];
$summary = is_array($document['summary'] ?? null) ? $document['summary'] : [];
$type = strtoupper((string)($document['document_type'] ?? ($templateType ?? 'DOCUMENT')));
$isBill = $type === 'BILL';
$esc = static function($value) {
    $text=(string)$value;
    if (strpos($text,'__CHABA_ITEM__')===0) {
        $doc=json_decode(substr($text,14),true);
        if (is_array($doc)) {
            $html=htmlspecialchars((string)($doc['name']??''),ENT_QUOTES|ENT_SUBSTITUTE,'UTF-8');
            $attrs=is_array($doc['attributes']??null)?$doc['attributes']:[];
            if ($attrs) {$html.='<ul class="item-attribute-list">';foreach($attrs as $a){$html.='<li>'.htmlspecialchars((string)($a['name']??''),ENT_QUOTES|ENT_SUBSTITUTE,'UTF-8').'<strong>'.htmlspecialchars((string)($a['qty']??1),ENT_QUOTES|ENT_SUBSTITUTE,'UTF-8').'</strong></li>';}$html.='</ul>';}
            return $html;
        }
    }
    return htmlspecialchars($text, ENT_QUOTES | ENT_SUBSTITUTE, 'UTF-8');
};
$title = $document['title'] ?? $type;
$defaultSections = is_array($defaultSections ?? null) ? $defaultSections : ['title', 'logo', 'header', 'items', 'tax', 'thank_you', 'paybill', 'qr', 'advertising'];
// Sections are defined by the renderer wrapper, not by queued payload JSON.
$sections = array_map('strtolower', array_map('strval', $defaultSections));
$has = static fn(string $name): bool => in_array($name, $sections, true);
$attributeRows = static function($value): array { $rows=[]; if(!is_array($value))return $rows; foreach($value as $a){if(is_string($a)){$name=trim($a);$qty=1;}elseif(is_array($a)){$name=trim((string)($a['name']??$a['label']??$a['title']??''));$qty=(float)($a['qty']??1);}else continue;if($name!=='')$rows[]=['name'=>$name,'qty'=>$qty>0?$qty:1];}return $rows; };
$isDocket = in_array($type, ['BOT', 'KOT', 'GOT', 'COT'], true);
$layoutClass = $type === 'FLPO' ? ' flpo' : '';
$logo = (string)($header['logo_src'] ?? '');
$qrValue = (string)($header['qr_value'] ?? (($header['sale_id'] ?? $header['order_id'] ?? '') !== '' ? 'ORDER:'.($header['sale_id'] ?? $header['order_id']) : ''));
$showLogo = (bool)($document['show_logo'] ?? $header['show_logo'] ?? ($logo !== ''));
$showQr = (bool)($document['show_qr'] ?? $header['show_qr'] ?? ($qrValue !== ''));
$showTax = (bool)($document['show_tax'] ?? $header['show_tax'] ?? (isset($summary['tax']) || isset($summary['vat'])));
?>
<style>
@page{size:79mm auto;margin:0}
*{box-sizing:border-box}
html,body{width:79mm;max-width:79mm;margin:0;padding:0;background:<?= $type === 'BILL' ? '#f5f5f5' : '#fff' ?>;color:#000;font:13px Arial,"Helvetica Neue",sans-serif}
@media print{html,body{width:79mm!important;max-width:79mm!important;margin:0!important;padding:0!important}.printout{margin:0!important;left:0!important;top:0!important}}
.printout{width:79mm;max-width:79mm;margin:0;padding:1mm;background:<?= $type === 'BILL' ? '#25e25e' : '#fff' ?>;overflow:hidden}.printout-border{width:100%;border:2px solid #000;padding:1mm}.center{text-align:center}.stars{font-weight:700;line-height:1.1;white-space:nowrap;overflow:hidden}.title{font-size:26px;font-weight:700}.meta{display:flex;justify-content:space-between;gap:5px;font-size:13px}.meta span{white-space:nowrap;overflow:hidden;text-overflow:ellipsis}.printout table{width:100%;max-width:100%;margin:0;border-collapse:collapse;background:inherit;table-layout:fixed}.printout td,.printout th{border:1px solid #000;padding:3px;font-size:12px;overflow-wrap:anywhere}.printout th{font-size:13px}.right{text-align:right}.total{font-size:22px!important}.section{margin-top:3px}.footer{text-align:center;font-size:13px}.qr img,.qr canvas{display:block;margin:auto;max-width:100%}.item-attributes{display:block;font-size:10px;font-weight:600;line-height:1.05;margin-top:1px}.item-attributes strong{float:right;margin-left:4px}
.item-attribute-list{margin:2px 0 0 14px;padding:0;font-size:10px;font-weight:600;line-height:1.1}.item-attribute-list li{padding-left:1px}.item-attribute-list strong{float:right;margin-left:5px}
</style>
<style>
.bill table th:first-child,.bill table td:first-child{width:12%;white-space:nowrap;text-align:center}.bill table th:nth-child(2),.bill table td:nth-child(2){width:48%;text-align:left}.bill table th:nth-child(3),.bill table td:nth-child(3){width:20%}.bill table th:nth-child(4),.bill table td:nth-child(4){width:20%}
.flpo table th:first-child,.flpo table td:first-child{width:11%;white-space:nowrap;text-align:right}.flpo table th:nth-child(2),.flpo table td:nth-child(2){width:49%;text-align:left;white-space:normal;overflow-wrap:anywhere;word-break:break-word}.flpo table th:nth-child(3),.flpo table td:nth-child(3){width:20%;white-space:nowrap;text-align:right}.flpo table th:nth-child(4),.flpo table td:nth-child(4){width:20%;white-space:nowrap;text-align:right}.flpo table th,.flpo table td{padding:3px 2px;font-size:11px;vertical-align:top}
.docket table th:first-child,.docket table td:first-child{width:48%;text-align:left}.docket table th:nth-child(2),.docket table td:nth-child(2){width:16%;white-space:nowrap;text-align:center}.docket table th:nth-child(3),.docket table td:nth-child(3){width:18%}.docket table th:nth-child(4),.docket table td:nth-child(4){width:18%}
.docket-heading{display:flex;justify-content:center;align-items:center;gap:10px;white-space:nowrap;font-size:22px;margin:2px 0}
</style>
<main class="printout <?= $isDocket ? 'docket' : 'bill' ?><?= $layoutClass ?>"><div class="printout-border">
<?php if ($has('title') && $isDocket): ?>
    <section data-section="title" class="center">
        <div class="stars">***********************************************************</div>
        <h1 class="title docket-heading"><?= $esc(($header['counter_name'] ?? $header['counter_id'] ?? '') . ' ' . ($header['docket_type'] ?? $type)) ?></h1>
        <h2 style="margin:2px 0"><?= $esc($header['waiter_name'] ?? $header['served_by'] ?? $header['user'] ?? '') ?></h2>
        <h3 style="margin:2px 0">SALE ID: <?= $esc($header['sale_id'] ?? $header['order_id'] ?? '') ?></h3>
        <h3 style="margin:2px 0">SHIFT: <?= $esc($header['shift'] ?? $header['shift_date'] ?? '') ?></h3>
        <h4 style="margin:2px 0">PRINT TIME: <?= $esc($header['print_time'] ?? $header['order_time'] ?? '') ?></h4>
        <div class="stars">***********************************************************</div>
    </section>
<?php elseif ($has('title')): ?>
    <section data-section="title" class="center">
        <div class="stars">***********************************************************</div>
        <div class="title">
            <?= $esc($header['company_name'] ?? $title) ?></div>
            <?php if ($type === 'BILL'): ?>
                <p style="font-size:18px;margin:0">(<?= $esc($header['company_tagline'] ?? 'Fine Dining & Bar') ?>)</p>
                <?php else: ?>
                    <h2 style="margin:2px 0"><?= $esc($header['docket_type'] ?? $type) ?></h2><?php endif; ?></section><?php endif; ?>
<?php if ($has('logo') && $showLogo && $logo !== ''): ?><section data-section="logo" class="center"><img src="<?= $esc($logo) ?>" alt="Logo" style="height:60px;max-width:70mm;margin:4mm auto 1mm;object-fit:contain"></section><?php endif; ?>
<?php if ($has('header') && !$isDocket): ?><section data-section="header" class="center"><div class="stars">***********************************************************</div><p style="margin:0"><?= $esc($header['company_address'] ?? '') ?></p><p style="margin:0">Cell: <?= $esc($header['company_phone'] ?? '') ?></p><div class="meta"><span>SERVED BY: <?= $esc($header['waiter_name'] ?? $header['waiter'] ?? '') ?></span><span>BILL No: <?= $esc($header['sale_id'] ?? '') ?></span></div><div class="meta"><span>SHIFT: <?= $esc($header['shift'] ?? $header['shift_date'] ?? '') ?></span><span>TIME: <?= $esc($header['order_time'] ?? $header['sale_time'] ?? $header['print_time'] ?? '') ?></span></div><div class="stars">***********************************************************</div></section><?php endif; ?>
<?php if ($has('items')): ?><section data-section="items"><table><thead><tr><th><?= $isDocket ? 'Name' : 'Qty' ?></th><?php if (!$isDocket): ?><th>Item</th><?php endif; ?><th><?= $isDocket ? 'Qty' : 'Price' ?></th><?php if ($isDocket): ?><th>Price</th><th>Total</th><?php else: ?><th>Total</th><?php endif; ?></tr></thead><tbody><?php $sum=0; $category=''; foreach($items as $item): if(is_object($item))$item=get_object_vars($item); $item=is_array($item)?$item:[]; $qty=(float)($item['qty']??$item['quantity']??0); $price=(float)($item['unit_price']??$item['price']??0); $line=(float)($item['amount']??$item['total']??($qty*$price)); $sum+=$line; $nextCategory=(string)($item['category']??(((int)($item['hour']??1)===2)?'HAPPY HOUR ORDER':'')); if(!$isDocket&&$nextCategory!==''&&$nextCategory!==$category){$category=$nextCategory; ?><tr><td colspan="5"><b><?= $esc($category) ?></b></td></tr><?php } ?><tr><?php if($isDocket): ?><td><?= $esc($item['item_name']??$item['name']??$item['description']??'') ?></td><td><?= $esc($qty) ?></td><?php else: ?><td><?= $esc($qty) ?></td><td><?= $esc($item['item_name']??$item['name']??$item['description']??'') ?></td><?php endif; ?><td class="right"><?= number_format($price,2) ?></td><td class="right"><?= number_format($line,2) ?></td></tr><?php endforeach; ?><tr><th colspan="<?= $isDocket ? 4 : 4 ?>" class="center total">TOTAL: <?= number_format((float)($summary['total']??$summary['amount']??$sum),2) ?></th></tr></tbody></table></section><?php endif; ?>
<?php $openEach = $isBill && strtolower((string)($header['bill_mode'] ?? '')) === 'each' && (int)($header['parent_sale_id'] ?? 0) > 0 && (int)($header['sale_id'] ?? 0) !== (int)($header['parent_sale_id'] ?? 0); if ($openEach): $previousTotal=(float)($header['previous_total'] ?? 0); $currentTotal=(float)($summary['total'] ?? $summary['amount'] ?? 0); ?><section data-section="open_each_summary"><div class="stars">***********************************************************</div><table><tbody><tr><th colspan="2" class="center" style="font-size:16px">BILL NO. <?= $esc($header['bill_position'] ?? '') ?></th></tr><tr><th colspan="2" style="font-size:12px">PREVIOUS BILL NO. <?= $esc($header['previous_bill_sale_id'] ?? $header['parent_sale_id'] ?? '') ?></th></tr><tr><td><b>PREVIOUS TOTAL:</b></td><td class="right"><b><?= number_format($previousTotal, 2) ?></b></td></tr><tr><td><b>CURRENT BILL:</b></td><td class="right"><b><?= number_format($currentTotal, 2) ?></b></td></tr><tr><td class="total">BALANCE:</td><td class="right total"><?= number_format($previousTotal + $currentTotal, 2) ?></td></tr></tbody></table></section><?php endif; ?>
<?php if ($has('tax') && $showTax): ?><section data-section="tax"><div class="stars">***********************************************************</div><div class="meta"><span>Gross: <?= number_format((float)($summary['gross']??$summary['total']??0),2) ?></span><span>V.A.T: <?= number_format((float)($summary['vat']??$summary['tax']??0),2) ?></span></div><div class="meta"><span></span><span>Cat Levy: <?= number_format((float)($summary['cat_levy']??0),2) ?></span></div></section><?php endif; ?>
<?php if ($has('summary')): ?><section data-section="summary"><div class="stars">***********************************************************</div><table><tbody><?php foreach($summary as $key=>$value): if(is_array($value)||is_object($value))continue; ?><tr><td><?= $esc(ucwords(str_replace('_',' ',$key))) ?></td><td class="right"><?= is_numeric($value)?number_format((float)$value,2):$esc($value) ?></td></tr><?php endforeach; ?></tbody></table></section><?php endif; ?>
<?php if ($has('thank_you')): ?><section data-section="thank_you" class="footer"><div class="stars">***********************************************************</div><b>THANK YOU</b><br><?= $esc($header['thank_you_note']??$document['footer']??'') ?></section><?php endif; ?>
<?php if ($has('paybill') && ($header['payment_line']??'') !== ''): ?><section data-section="paybill" class="center"><div class="stars">***********************************************************</div><h2 style="font-size:20px;letter-spacing:3px;margin:0"><?= $esc($header['payment_line']) ?></h2></section><?php endif; ?>
<?php if ($has('qr') && $showQr && $qrValue !== ''): ?><section data-section="qr" class="qr center"><div id="qr-printout"></div><small>Scan to place next order · Bill: <?= $esc($header['sale_id']??$header['order_id']??'') ?></small></section><script src="https://cdnjs.cloudflare.com/ajax/libs/qrcodejs/1.0.0/qrcode.min.js" onerror="document.getElementById('qr-printout').innerHTML='<img width=140 height=140 alt=QR src=\'https://quickchart.io/qr?text=<?= rawurlencode($qrValue) ?>&size=140\'> '"></script><script>(function(){var el=document.getElementById('qr-printout');if(typeof QRCode==='function'){new QRCode(el,{text:<?= json_encode($qrValue) ?>,width:140,height:140,colorDark:'#000',colorLight:'#25e25e',correctLevel:QRCode.CorrectLevel.M});}else{el.innerHTML='<img width="140" height="140" alt="QR code" src="https://quickchart.io/qr?text=<?= rawurlencode($qrValue) ?>&size=140">';}})();</script><?php endif; ?>
<?php if ($has('advertising')): ?><section data-section="advertising" class="footer"><div class="stars">***********************************************************</div>K-TOUCH POS by K-TEN SOLUTIONS LTD<br>+254716100335 | +254735100335<div class="stars">***********************************************************</div></section><?php endif; ?>
</div></main>
