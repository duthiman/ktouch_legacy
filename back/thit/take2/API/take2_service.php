<?php
function take2_service($action, $input) {
    $uid=take2_user_id();
    switch($action) {
        case 'options': return take2_repo_scopes((int)($input['category_id']??0));
        case 'list': return ['takes'=>take2_repo_list(),'users'=>take2_repo_users()];
        case 'view': $viewTake=take2_repo_get((int)($input['take_id']??0)); if($viewTake) $viewTake['review_order']=take2_repo_review_order((int)($input['take_id']??0)); return ['take'=>$viewTake];
        case 'history': return ['history'=>take2_repo_history((int)($input['item_id']??0))];
        case 'review_note':
            $take=take2_repo_get((int)$input['take_id']); $line=null; foreach(($take['lines']??[]) as $candidate) if((int)$candidate['line_id']===(int)$input['line_id']) $line=$candidate;
            if(!$line) throw new Exception('Review item not found.'); if(trim((string)($input['explanation']??''))==='') throw new Exception('Enter an explanation for this variance.');
            take2_repo_save_review_note((int)$input['take_id'],(int)$line['line_id'],(int)$line['item_id'],$input['explanation'],$uid); take2_event((int)$input['take_id'],'VARIANCE_EXPLAINED',$line['item_name']); $reviewTake=take2_repo_get((int)$input['take_id']); $reviewTake['review_order']=take2_repo_review_order((int)$input['take_id']); return ['take'=>$reviewTake];
        case 'initialize':
            $id=take2_repo_create($input['scope_type']??'all',(int)($input['scope_id']??0),$input['notes']??'',$uid);
            return ['take'=>take2_repo_get($id)];
        case 'approve':
            $id=(int)$input['take_id']; take2_repo_update($id,['status'=>'APPROVED','approval_notes'=>$input['notes']??'','approved_by'=>$uid,'approved_at'=>date('Y-m-d H:i:s')]); take2_event($id,'APPROVED',$input['notes']??''); return ['take'=>take2_repo_get($id)];
        case 'assign':
            $id=(int)$input['take_id']; take2_repo_assign($id,(int)$input['user_id']); take2_repo_update($id,['status'=>'ASSIGNED','assigned_by'=>$uid]); take2_event($id,'ASSIGNED','Assigned user '.$input['user_id']); return ['take'=>take2_repo_get($id)];
        case 'start':
            $id=(int)$input['take_id']; take2_repo_update($id,['status'=>'IN_PROGRESS']); take2_event($id,'STARTED'); return ['take'=>take2_repo_get($id)];
        case 'count':
            $id=(int)$input['take_id']; $rawQty=trim((string)($input['qty']??'')); if($rawQty===''||$rawQty==='0') throw new Exception('Enter 00 to record zero stock.'); if(!preg_match('/^[0-9]+(?:\.[0-9]+)?$/',$rawQty)) throw new Exception('Enter a valid quantity.'); take2_repo_line($id,(int)$input['line_id'],(float)$rawQty,$input['notes']??''); return ['take'=>take2_repo_get($id)];
        case 'submit':
            $id=(int)$input['take_id']; take2_repo_update($id,['status'=>'SUBMITTED','submitted_at'=>date('Y-m-d H:i:s')]); take2_event($id,'SUBMITTED'); return ['take'=>take2_repo_get($id)];
        case 'retake':
            global $connection; $id=(int)$input['take_id'];$line=(int)$input['line_id'];$reason=take2_escape($input['reason']??'Variance review');
            mysqli_query($connection,"UPDATE kp_stock_take_lines SET retake_required=1,line_status='RETAKE_REQUIRED',retake_notes='$reason' WHERE take_id=$id AND line_id=$line");
            mysqli_query($connection,"INSERT INTO kp_stock_take_retakes(take_id,line_id,item_id,original_counted_qty,reason,request_notes,requested_by) SELECT take_id,line_id,item_id,counted_qty,'$reason','$reason',$uid FROM kp_stock_take_lines WHERE take_id=$id AND line_id=$line");
            take2_repo_update($id,['status'=>'RETAKE_REQUIRED','review_notes'=>$input['notes']??'']); take2_event($id,'RETAKE_REQUESTED',$reason); return ['take'=>take2_repo_get($id)];
        case 'review':
            $id=(int)$input['take_id']; $take=take2_repo_get($id); foreach(($take['lines']??[]) as $line) { $variance=(float)($line['variance_qty']??0); if($variance===0.0) continue; $note=trim((string)($take['review_notes'][(int)$line['line_id']]??'')); if($note==='') throw new Exception('Explain every Less or More variance before approving the review. Items with zero variance are already accepted.'); } take2_repo_update($id,['status'=>'APPROVED_FOR_POSTING','review_notes'=>$input['notes']??'','reviewed_by'=>$uid,'reviewed_at'=>date('Y-m-d H:i:s')]); take2_event($id,'REVIEW_APPROVED',$input['notes']??''); return ['take'=>take2_repo_get($id)];
        case 'post':
            global $connection; $id=(int)$input['take_id']; $take=take2_repo_get($id); if(!$take) throw new Exception('Stock take not found.'); if($take['status']!=='APPROVED_FOR_POSTING') throw new Exception('Only a reviewed stock take pending posting can be posted.');
            mysqli_begin_transaction($connection); foreach($take['lines'] as $line) { $qty=$line['final_qty']!==null?$line['final_qty']:($line['counted_qty']??$line['expected_qty']); mysqli_query($connection,"UPDATE kp_items SET store_qty=".(float)$qty." WHERE item_id=".(int)$line['item_id']); mysqli_query($connection,"UPDATE kp_stock_take_lines SET final_qty=".(float)$qty.",line_status='FINAL' WHERE line_id=".(int)$line['line_id']); } take2_repo_update($id,['status'=>'COMPLETED','posted_by'=>$uid,'completed_at'=>date('Y-m-d H:i:s')]); mysqli_commit($connection); take2_event($id,'POSTED'); return ['take'=>take2_repo_get($id)];
        case 'print': return take2_service_enqueue_print((int)$input['take_id'],$input['report']??'sheet',$input['format']??'a4',$uid);
        default: throw new Exception('Unknown stock take action.');
    }
}

function take2_service_enqueue_print($takeId,$report,$format,$uid) {
    global $connection;
    $take=take2_repo_get($takeId); if(!$take) throw new Exception('Stock take not found.');
    require_once dirname(__DIR__,4).'/front/chaba/funcs.php';
    if(function_exists('ensure_print_queue')) ensure_print_queue($connection);
    $type='stock_take_'.$report.'_'.$format; $query=http_build_query(['take_id'=>$takeId,'report'=>$report,'format'=>$format]);
    $queueId=kp_print_enqueue($connection,['sale_id'=>0,'ref_id'=>$takeId,'print_type'=>$type,'label'=>strtoupper($report).' '.strtoupper($format).' '.$take['take_code'],'cntr_id'=>0,'route_path'=>'front/chaba/render_take2.php','route_query'=>$query,'requested_by'=>$uid,'reprint_of'=>0]);
    if(!$queueId) throw new Exception('Could not add print job to Chaba queue.');
    take2_event($takeId,'PRINT_QUEUED',$type.' queue #'.$queueId); return ['queue_id'=>$queueId,'type'=>$type];
}
