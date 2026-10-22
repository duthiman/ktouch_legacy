<?php
declare(strict_types=1);
namespace KTouch\Modules\Chaba\V1\Services;
use KTouch\Core\Exceptions\ValidationException;
final class Document
{
    public static function build(mixed $value, array $header): string
    {
        if(is_string($value)) {
            try {$value=json_decode($value,true,512,JSON_THROW_ON_ERROR);} catch(\JsonException) {throw new ValidationException('payload_json must contain valid JSON');}
        }
        if(!is_array($value)||array_is_list($value)) throw new ValidationException('A complete document object is required');
        if(isset($value['payload_version'])&&$value['payload_version']!==1) throw new ValidationException('Unsupported payload_version');
        if(isset($value['header'])&&!is_array($value['header'])) throw new ValidationException('header must be an object');
        $value['header']=array_replace($value['header']??[],$header);
        $value['payload_version']=1;
        $content=false;
        foreach(['items','users','sales','payments','counters','expenses','stock_counts','summary'] as $section) {
            if(!empty($value[$section])&&is_array($value[$section])) $content=true;
        }
        if(!$content) throw new ValidationException('Print document is empty; reference IDs alone are insufficient');
        if(isset($value['items'])) {
            if(!is_array($value['items'])||!array_is_list($value['items'])) throw new ValidationException('items must be a list');
            $qty=0.0;$amount=0.0;
            foreach($value['items'] as &$item) {
                if(!is_array($item)||trim((string)($item['name']??$item['item_name']??''))==='') throw new ValidationException('Each item requires its printable name');
                $q=self::number($item['qty']??null,'item qty');
                $price=self::number($item['price']??$item['unit_price']??null,'item price');
                $item['qty']=$q;$item['price']=$price;$item['total']=round($q*$price,2);
                $qty+=$q;$amount+=$item['total'];
            }
            unset($item);
            if(isset($value['summary'])&&!is_array($value['summary'])) throw new ValidationException('summary must be an object');
            $value['summary']=array_replace($value['summary']??[],['item_count'=>$qty,'amount'=>round($amount,2)]);
            if(!$value['items']&&empty($value['users'])&&empty($value['sales'])&&empty($value['stock_counts'])) throw new ValidationException('Print document has no items or summary content');
        } else {
            if(!is_array($value['summary']??null)) throw new ValidationException('Summary documents require summary.item_count and summary.amount');
            $value['summary']['item_count']=self::number($value['summary']['item_count']??null,'summary.item_count');
            $value['summary']['amount']=self::number($value['summary']['amount']??null,'summary.amount');
        }
        return json_encode($value,JSON_THROW_ON_ERROR|JSON_UNESCAPED_UNICODE);
    }
    private static function number(mixed $value,string $name): float
    {
        if(!is_numeric($value)||!is_finite((float)$value)||(float)$value<0) throw new ValidationException($name.' must be a finite non-negative number');
        return (float)$value;
    }
    public static function decode(string $json): array
    {
        try {$doc=json_decode($json,true,512,JSON_THROW_ON_ERROR);} catch(\JsonException) {throw new ValidationException('Stored print snapshot is invalid JSON');}
        if(!is_array($doc)) throw new ValidationException('Stored print snapshot is invalid');
        // Documents queued before payload versioning are treated as V1.
        if(!array_key_exists('payload_version',$doc))$doc['payload_version']=1;
        if((int)$doc['payload_version']!==1) throw new ValidationException('Unsupported payload_version');
        return $doc;
    }
    public static function html(array $doc): string
    {
        $escape=static fn($s)=>htmlspecialchars((string)$s,ENT_QUOTES|ENT_SUBSTITUTE,'UTF-8');
        $type=strtoupper((string)($doc['document_type']??''));
        // CUSTOMER_BILL uses the same visual template as BILL.
        $templateType=$type==='CUSTOMER_BILL'?'BILL':($type==='RETURN_RECEIPT'?'SRT':$type);
        $renderPath=dirname(__DIR__).'/Templates/Template1/renders/'.strtolower($templateType).'.php';
        if(is_file($renderPath)) {
            $document=$doc;
            $templateType=$type;
            ob_start();
            include $renderPath;
            $markup=(string)ob_get_clean();
            $baseCssPath=dirname(__DIR__).'/Templates/Template1/assets/css/base.css';
            $variantCssPath=dirname(__DIR__).'/Templates/Template1/assets/css/'.(in_array($type,['BOT','KOT','GOT','COT'],true)?'kitchen.css':'receipt.css');
            $sharedCss='';
            foreach([$baseCssPath,$variantCssPath] as $cssPath) if(is_file($cssPath)) $sharedCss.=file_get_contents($cssPath);
            return '<!doctype html><html><head><meta charset="utf-8"><title>'.$escape($doc['title']??$type).'</title><style>'.$sharedCss.'</style></head><body>'.$markup.'</body></html>';
        }
        $header=is_array($doc['header']??null)?$doc['header']:[];
        $items=is_array($doc['items']??null)?$doc['items']:[];
        $summary=is_array($doc['summary']??null)?$doc['summary']:[];
        $isBill=$type==='BILL';
        $css='@page{size:80mm auto;margin:0}*{box-sizing:border-box}body{margin:2mm;background:'.($isBill?'#f5f5f5':'#fff').';color:#000;font-family:Arial,"Helvetica Neue",Helvetica,sans-serif;font-size:13px;width:80mm}.receipt{width:79mm;max-width:79mm;margin:0 auto;padding:1mm;background:'.($isBill?'#25e25e':'#fff').';color:#000}.border{border:2px solid #000;padding:1mm}.center{text-align:center}.title{font-size:26px;font-weight:700;margin:0;letter-spacing:0}.meta{display:flex;justify-content:space-between;gap:8px;font-size:13px;margin:0}.meta span{white-space:nowrap;overflow:hidden;text-overflow:ellipsis}.divider{border:0;border-top:2px solid #000;margin:2px 0}.divider.dashed{border-top:1px dashed #555}.stars{font-weight:700;line-height:1.1;white-space:nowrap;overflow:hidden;text-align:center}.table{padding:1mm;margin:0 auto;width:75mm;background:'.($isBill?'#25e25e':'#fff').';border-collapse:collapse;table-layout:fixed}.table th,.table td{font-size:12px;border:1px solid #000;padding:2px;vertical-align:top;word-break:break-word}.table th{font-size:13px}.table th:last-child,.table td:last-child{text-align:right}.item-name{font-weight:700}.section{font-size:12px;font-weight:700;text-transform:uppercase;padding:3px 0}.total{font-size:16px;font-weight:700}.footer{text-align:center;margin-top:4px;padding-top:2px;font-size:13px}.footer strong{font-size:20px;letter-spacing:3px}';
        $meta=[];
        foreach(['sale_id'=>'Sale','counter_name'=>'Counter','counter_id'=>'Counter','docket_type'=>'Docket','waiter'=>'Waiter','waiter_name'=>'Waiter','served_by'=>'Served By','order_name'=>'Order','cashier'=>'Cashier','shift_date'=>'Shift Date','shift'=>'Shift','order_time'=>'Order Time','sale_time'=>'Sale Time','print_time'=>'Print Time','opening_time'=>'Opened','closing_time'=>'Closed','clearance_id'=>'Clearance'] as $key=>$label) {
            if($key==='counter_id'&&isset($header['counter_name'])) continue;
            if($key==='waiter_name'&&isset($header['waiter'])) continue;
            if(array_key_exists($key,$header)&&$header[$key]!==''&&$header[$key]!==null) $meta[]='<span>'.$escape($label.': '.$header[$key]).'</span>';
        }
        $itemsHtml='';
        if($items) {
            $itemsHtml='<table class="table">'.(!$isBill?'<thead><tr><th>Name</th><th>Qty</th><th>Price</th><th>Total</th></tr></thead>':'').'<tbody>';
            $previousCategory='';
            foreach($items as $item) {
                if(is_object($item)) $item=get_object_vars($item);
                if(!is_array($item)) continue;
                $name=$item['item_name']??$item['name']??'';
                $qty=$item['qty']??0;
                $amount=$item['amount']??$item['total']??(($item['unit_price']??$item['price']??0)*$qty);
                $price=$item['unit_price']??$item['price']??0;
                $category=$item['category']??(((int)($item['hour']??1)===2)?'HAPPY HOUR ORDER':'');
                if($isBill&&$category!==''&&$category!==($previousCategory??'')) $itemsHtml.='<tr><td colspan="5" style="font-size:14px"><b>'.$escape($category).'</b></td></tr>';
                $previousCategory=$category;
                $itemsHtml.='<tr>'.($isBill?'<td style="width:10%">'.$escape($qty).'</td><td style="width:43%" class="item-name">'.$escape($name).'</td>':'<td style="width:43%" class="item-name">'.$escape($name).'</td><td style="width:12%;text-align:right">'.$escape($qty).'</td>').'<td style="width:22%;text-align:right">'.$escape(number_format((float)$price,2)).'</td><td style="width:25%;text-align:right">'.$escape(number_format((float)$amount,2)).'</td></tr>';
            }
            $itemsHtml.='<tr><th colspan="4" class="total" style="text-align:center">Total: '.number_format((float)($summary['total']??$summary['amount']??0),2).'</th></tr></tbody></table>';
        }
        $summaryHtml='';
        if($summary) {
            $summaryHtml='<div class="divider dashed"></div><div class="section">Summary</div><table class="table">';
            foreach($summary as $key=>$value) {
                if(is_array($value)||is_object($value)) continue;
                $label=ucwords(str_replace('_',' ',(string)$key));
                $class=in_array((string)$key,['total','sales_total','total_collected','expected_amount','actual_amount'],true)?' class="total"':'';
                $summaryHtml.='<tr'.$class.'><td>'.$escape($label).'</td><td>'.$escape(is_numeric($value)?number_format((float)$value,2):$value).'</td></tr>';
            }
            $summaryHtml.='</table>';
        }
        $title=$doc['title']??$type??'DOCUMENT';
        $footer=$doc['footer']??'';
        $company=$header['company_name']??'THE GRAND TAVERN';
        $tagline=$header['company_tagline']??'Fine Dining & Bar';
        $address=$header['company_address']??'Westlands, Nairobi';
        $phone=$header['company_phone']??'';
        $logoSrc=trim((string)($header['logo_src']??''));
        $qrSrc=trim((string)($header['qr_src']??''));
        $qrValue=trim((string)($header['qr_value']??''));
        $showLogo=(bool)($header['show_logo']??($logoSrc!==''));
        $showQr=(bool)($header['show_qr']??($qrSrc!==''||$qrValue!==''));
        $showTax=(bool)($header['show_tax']??isset($summary['tax'])||isset($summary['vat'])||isset($summary['cat_levy']));
        $showPin=(bool)($header['show_pin']??false);
        $thankYou=$header['thank_you_note']??$footer;
        $logoHtml=$showLogo&&$logoSrc!==''?'<img src="'.$escape($logoSrc).'" alt="Logo" style="display:block;height:60px;max-width:70mm;margin:4mm auto 1mm;object-fit:contain" onerror="this.style.display=\'none\'">':'';
        $qrHtml='';
        $qrScript='';
        if($showQr&&$qrSrc!=='') {
            $qrHtml='<div style="text-align:center;margin:6px 0"><img src="'.$escape($qrSrc).'" alt="QR code" style="width:140px;height:140px;object-fit:contain"><div style="font-size:10px;margin-top:3px">Scan to place next order'.($header['sale_id']??null?' · Bill: '.$escape($header['sale_id']):'').'</div></div>';
        } elseif($showQr&&$qrValue!=='') {
            $qrHtml='<div class="qr-wrap"><div id="qr-preview"></div><div style="font-size:10px;margin-top:3px">Scan to place next order'.($header['sale_id']??null?' · Bill: '.$escape($header['sale_id']):'').'</div></div>';
            $qrScript='<script src="https://cdnjs.cloudflare.com/ajax/libs/qrcodejs/1.0.0/qrcode.min.js"></script><script>new QRCode(document.getElementById("qr-preview"),{text:'.json_encode($qrValue,JSON_HEX_TAG|JSON_HEX_AMP|JSON_HEX_APOS|JSON_HEX_QUOT).',width:140,height:140,colorDark:"#000000",colorLight:"#25e25e",correctLevel:QRCode.CorrectLevel.M});</script>';
        }
        $pinHtml=$showPin&&($header['pin']??'')!==''?'<span class="meta">PIN: '.$escape($header['pin']).'</span>':'';
        $taxHtml=$showTax?'<div class="stars">***********************************************************</div><div class="meta"><span>Gross: '.number_format((float)($summary['gross']??$summary['total']??0),2).'</span><span>V.A.T: '.number_format((float)($summary['vat']??$summary['tax']??0),2).'</span></div><div class="meta"><span></span><span>Cat Levy: '.number_format((float)($summary['cat_levy']??0),2).'</span></div>':'';
        $billMeta=$meta?'<div class="meta">'.implode('',$meta).'</div>':'';
        $docketType=$header['docket_type']??$type;
        $billHeader=$isBill?'<section id="bill-title-section" class="center"><div class="stars">***********************************************************</div><div class="title">'.$escape($company).'</div><p class="center" style="font-size:18px;margin:0">('.$escape($tagline).')</p></section><section id="bill-logo-section" class="center">'.$logoHtml.'</section><section id="bill-header-details" class="center"><div class="stars">***********************************************************</div><p style="margin:0">'.$escape($address).'</p><p style="margin:0">Cell: '.$escape($phone).'</p><div class="stars">***********************************************************</div>'.$pinHtml.$billMeta.'<div class="stars">***********************************************************</div></section>':'<header class="center"><div class="stars">***********************************************************</div><div class="title">'.$escape($title).'</div><div style="font-size:18px;font-weight:700;margin:2px 0">'.$escape($docketType).'</div>'.$billMeta.'<div class="stars">***********************************************************</div></header>';
        $paymentLine=trim((string)($header['payment_line']??''));
        if($paymentLine==='') $paymentLine=(($header['paybill']??'')!==''?'PAYBILL '.$header['paybill'].' ACCOUNT: '.($header['paybill_account']??''):'');
        $billFooter=$isBill?'<section id="bill-tax-section">'.$taxHtml.'</section><section id="bill-thank-you-section"><div class="stars">***********************************************************</div><div class="footer"><b>THANK YOU</b><br>'.$escape((string)$thankYou).'<br><span>This is not an ETR. Request one</span></div></section><section id="bill-paybill-section"><div class="stars">***********************************************************</div>'.($paymentLine!==''?'<h2 class="center" style="margin:0;font-size:20px;letter-spacing:3px">'.$escape($paymentLine).'</h2>':'').'</section><section id="bill-qr-section">'.$qrHtml.'</section><section id="bill-advertising-section"><div class="stars">***********************************************************</div><p class="center" style="font-size:11px;margin:0">K-TOUCH POS Designed &amp; Distributed by K-TEN<br>+254716100335 | +254735100335</p><div class="stars">***********************************************************</div></section>':$summaryHtml.'<footer class="footer">'.$escape($footer).'</footer>';
        $itemsSection=$isBill?'<section id="bill-items-section">'.$itemsHtml.'</section>':$itemsHtml;
        return '<!doctype html><html><head><meta charset="utf-8"><title>'.$escape($title).'</title><style>'.$css.'</style></head><body><main class="receipt"><div class="border">'.$billHeader.$itemsSection.$billFooter.'</div></main>'.$qrScript.'</body></html>';
    }
}
