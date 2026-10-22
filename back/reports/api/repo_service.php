<?php

if (!function_exists('repo_service_users')) {
    function repo_service_users($connection)
    {
        return ['ok'=>true,'message'=>'Users loaded.','users'=>repo_fetch_users($connection)];
    }
}

if (!function_exists('repo_service_user_sales')) {
    function repo_service_user_sales($connection, $user_id, $from, $to, $counter_id = 0)
    {
        $user_id = (int)$user_id;
        $from = repo_text($from);
        $to = repo_text($to);
        $counter_id = (int)$counter_id;
        if ($user_id < 0 || !preg_match('/^\d{4}-\d{2}-\d{2}$/', $from) || !preg_match('/^\d{4}-\d{2}-\d{2}$/', $to)) {
            return ['ok'=>false,'message'=>'User and valid date range are required.','status'=>422];
        }
        if ($from > $to) return ['ok'=>false,'message'=>'The start date cannot be after the end date.','status'=>422];
        return ['ok'=>true,'message'=>'User sales loaded.','user_id'=>$user_id,'from'=>$from,'to'=>$to,
            'counter_id'=>$counter_id,
            'sales'=>repo_fetch_user_sales($connection,$user_id,$from,$to,$counter_id),
            'item_summary'=>repo_fetch_user_item_summary($connection,$user_id,$from,$to,$counter_id)];
    }
}

if (!function_exists('repo_service_sale_items')) {
    function repo_service_sale_items($connection, $sale_id)
    {
        $sale_id = (int)$sale_id;
        if ($sale_id < 1) return ['ok'=>false,'message'=>'A valid sale is required.','status'=>422];
        $meta = ['sale_id'=>$sale_id,'amount'=>0,'shift'=>'','datime'=>'','username'=>'Unknown user','counter_name'=>'Unknown counter','item_total'=>0,'match_ok'=>true];
        $q = mysqli_query($connection, "SELECT s.sale_id,s.amount,s.shift,s.datime,COALESCE(u.username,'Unknown user') username,COALESCE(c.name,'Unknown counter') counter_name,COALESCE(SUM(si.total),0) item_total FROM kp_sales s LEFT JOIN kp_users u ON u.user_id=s.user_id LEFT JOIN kp_counters c ON c.cntr_id=s.counter LEFT JOIN kp_sale_items si ON si.sale_id=s.sale_id WHERE s.sale_id='$sale_id' GROUP BY s.sale_id,s.amount,s.shift,s.datime,u.username,c.name");
        if ($q && ($m=mysqli_fetch_assoc($q))) { $meta=array_merge($meta,$m); $meta['match_ok']=round((float)$meta['amount'],2)===round((float)$meta['item_total'],2); }
        return ['ok'=>true,'message'=>'Sale items loaded.','sale_id'=>$sale_id,'sale'=>$meta,'items'=>repo_fetch_sale_items($connection,$sale_id)];
    }
}

if (!function_exists('repo_service_item_summary')) {
    function repo_service_item_summary($connection, $item_id, $from, $to)
    {
        $item_id = (int)$item_id;
        $from = trim((string)$from);
        $to = trim((string)$to);
        if ($item_id < 1 || !preg_match('/^\d{4}-\d{2}-\d{2}$/', $from) || !preg_match('/^\d{4}-\d{2}-\d{2}$/', $to) || $from > $to) {
            return ['ok'=>false,'status'=>422,'message'=>'Item and valid date range are required.'];
        }
        $rows = repo_fetch_item_summary($connection, $item_id, $from, $to);
        $qty = 0; $total = 0;
        foreach ($rows as $row) { $qty += $row['qty']; $total += $row['total']; }
        return ['ok'=>true,'message'=>'Item summary loaded.','item_id'=>$item_id,'from'=>$from,'to'=>$to,
            'qty'=>$qty,'total'=>$total,'rows'=>$rows];
    }
}

if (!function_exists('repo_service_counter_summary')) {
    function repo_service_counter_sales($connection,$counter_id,$from,$to){
        $from=repo_text($from);$to=repo_text($to);$counter_id=(int)$counter_id;
        if($counter_id<0||!preg_match('/^\d{4}-\d{2}-\d{2}$/',$from)||!preg_match('/^\d{4}-\d{2}-\d{2}$/',$to)||$from>$to)return ['ok'=>false,'message'=>'Valid counter and date range are required.','status'=>422];
        $rows=repo_fetch_counter_sales($connection,$counter_id,$from,$to);$sales=0;$amount=0;$items=0;$mismatches=0;foreach($rows as $r){$sales++;$amount+=$r['amount'];$items+=$r['item_total'];if(round((float)$r['amount'],2)!=round((float)$r['item_total'],2))$mismatches++;}$difference=round($amount-$items,2);return ['ok'=>true,'message'=>'Counter sales loaded.','rows'=>$rows,'summary'=>['sales'=>$sales,'amount'=>$amount,'item_total'=>$items,'mismatches'=>$mismatches,'difference'=>$difference,'balanced'=>$mismatches===0],'sales'=>$sales,'amount'=>$amount,'item_total'=>$items,'mismatches'=>$mismatches,'difference'=>$difference,'balanced'=>$mismatches===0,'from'=>$from,'to'=>$to,'counter_id'=>$counter_id];
    }

    function repo_service_counter_summary($connection, $counter_id, $from, $to)
    {
        $counter_id = (int)$counter_id;
        $from = repo_text($from);
        $to = repo_text($to);
        if ($counter_id < 0 || !preg_match('/^\d{4}-\d{2}-\d{2}$/', $from) || !preg_match('/^\d{4}-\d{2}-\d{2}$/', $to)) {
            return ['ok'=>false,'message'=>'Counter and valid date range are required.','status'=>422];
        }
        if ($from > $to) return ['ok'=>false,'message'=>'The start date cannot be after the end date.','status'=>422];
        $rows = repo_fetch_counter_item_summary($connection, $counter_id, $from, $to);
        $qty = 0; $total = 0;
        foreach ($rows as $row) { $qty += $row['qty']; $total += $row['total']; }
        return ['ok'=>true,'message'=>'Counter summary loaded.','counter_id'=>$counter_id,'from'=>$from,'to'=>$to,
            'qty'=>$qty,'total'=>$total,'rows'=>$rows];
    }
}

if (!function_exists('repo_service_item_orders')) {
    function repo_service_item_orders($connection, $user_id, $item_id, $from, $to, $counter_id = 0)
    {
        $user_id = (int)$user_id;
        $item_id = (int)$item_id;
        $counter_id = (int)$counter_id;
        $from = repo_text($from);
        $to = repo_text($to);
        if ($user_id < 1 || $item_id < 1 || !preg_match('/^\d{4}-\d{2}-\d{2}$/', $from) || !preg_match('/^\d{4}-\d{2}-\d{2}$/', $to)) {
            return ['ok'=>false,'message'=>'User, item, and valid date range are required.','status'=>422];
        }
        if ($from > $to) return ['ok'=>false,'message'=>'The start date cannot be after the end date.','status'=>422];
        return ['ok'=>true,'message'=>'Item orders loaded.','orders'=>repo_fetch_item_orders($connection,$user_id,$item_id,$from,$to,$counter_id)];
    }
}

if (!function_exists('repo_service_user_returns')) {
    function repo_service_user_returns($connection, $user_id, $from, $to)
    {
        $user_id = (int)$user_id; $from = repo_text($from); $to = repo_text($to);
        if ($user_id < 1 || !preg_match('/^\d{4}-\d{2}-\d{2}$/', $from) || !preg_match('/^\d{4}-\d{2}-\d{2}$/', $to)) {
            return ['ok'=>false,'message'=>'User and valid date range are required.','status'=>422];
        }
        if ($from > $to) return ['ok'=>false,'message'=>'The start date cannot be after the end date.','status'=>422];
        $rows = repo_fetch_user_returns($connection,$user_id,$from,$to);
        $qty = 0; $amount = 0; foreach ($rows as $row) { $qty += $row['qty']; $amount += $row['amount']; }
        return ['ok'=>true,'message'=>'User returns loaded.','user_id'=>$user_id,'from'=>$from,'to'=>$to,'qty'=>$qty,'amount'=>$amount,'rows'=>$rows];
    }
}
