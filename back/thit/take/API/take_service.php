<?php
function take_service_items() { return ['ok'=>true,'message'=>'Take items loaded.','items'=>take_repo_items()]; }
function take_service_content($shift) { return ['ok'=>true,'message'=>'Taken stock loaded.','rows'=>take_repo_rows($shift)]; }

function take_service_save($itemId, $qty, $shift, $userId) {
    $itemId=(int)$itemId; $rawQty=trim((string)$qty); $shift=take_clean_text($shift);
    if ($itemId<1) return ['ok'=>false,'message'=>'Select an item first.'];
    if ($rawQty==='' || $rawQty==='0') return ['ok'=>false,'message'=>'Enter 00 to record zero stock.'];
    if (!preg_match('/^[0-9]+(?:\.[0-9]+)?$/', $rawQty)) return ['ok'=>false,'message'=>'Enter a valid quantity.'];
    $qty=(float)$rawQty;
    if ($shift==='') return ['ok'=>false,'message'=>'A shift is required.'];
    if (!take_repo_save($itemId,$qty,$shift,$userId)) return ['ok'=>false,'message'=>'Could not save stock take.'];
    take_logger_log('STOCK TAKE SAVED',['item_id'=>$itemId,'qty'=>$qty,'shift'=>$shift,'user_id'=>$userId]);
    return ['ok'=>true,'message'=>'Stock taken successfully.','items'=>take_repo_items()];
}

function take_service_delete($itemId, $shift, $userId) {
    if ((int)$itemId<1 || take_clean_text($shift)==='') return ['ok'=>false,'message'=>'Take details are incomplete.'];
    if (!take_repo_delete($itemId,$shift)) return ['ok'=>false,'message'=>'Could not remove stock take.'];
    take_logger_log('STOCK TAKE REMOVED',['item_id'=>(int)$itemId,'shift'=>take_clean_text($shift),'user_id'=>(int)$userId]);
    return ['ok'=>true,'message'=>'Stock take removed.','rows'=>take_repo_rows($shift)];
}

function take_service_finish($userId) {
    if (!take_repo_finish()) return ['ok'=>false,'message'=>'Could not finish stock take.'];
    take_logger_log('STOCK TAKE FINISHED',['user_id'=>(int)$userId]);
    return ['ok'=>true,'message'=>'Stock take finished.'];
}
