<?php
declare(strict_types=1);

function counter_service_status(mysqli $connection, int $counterId): array
{
    $counter = counter_repo_get($connection, $counterId);
    if (!$counter) return counter_api_response(false, 'not_found', 'Counter not found', ['counter_id' => $counterId]);
    return counter_api_response(true, 'loaded', 'Counter status loaded', [
        'counter_id' => $counterId,
        'counter' => $counter,
        'open_counters' => counter_repo_open_count($connection),
        'shift' => shift_repo_current_value($connection),
    ]);
}

function counter_service_open(mysqli $connection, int $counterId, int $userId, int $takeStatus): array
{
    if ($counterId < 1 || $userId < 1 || !in_array($takeStatus, [1, 2, 3], true)) return counter_api_response(false, 'invalid_request', 'Valid counter, cashier, and take status are required', []);
    $counter = counter_repo_get($connection, $counterId);
    if (!$counter) return counter_api_response(false, 'not_found', 'Counter not found', ['counter_id' => $counterId]);
    if ((int)($counter['counter_status'] ?? 2) === 1) return counter_api_response(false, 'already_open', 'Counter is already open', ['counter_id' => $counterId]);
    try {
        counter_repo_open($connection, $counterId, $userId, $takeStatus);
        counter_log('counter_opened', ['counter_id' => $counterId, 'user_id' => $userId, 'take_status' => $takeStatus]);
        return counter_api_response(true, 'opened', 'Counter opened successfully', ['counter_id' => $counterId, 'user_id' => $userId, 'take_status' => $takeStatus]);
    } catch (Throwable $e) {
        counter_log('counter_open_failed', ['counter_id' => $counterId, 'error' => $e->getMessage()]);
        return counter_api_response(false, 'server_error', $e->getMessage(), ['counter_id' => $counterId]);
    }
}

function counter_service_close(mysqli $connection, int $counterId, int $userId): array
{
    if ($counterId < 1 || $userId < 1) return counter_api_response(false, 'invalid_request', 'A valid counter and user are required', []);
    $transactionStarted = false;
    try {
        $counter = counter_repo_assert_open($connection, $counterId);
        $shift = shift_repo_current_value($connection);
        if ($shift === '') throw new LogicException('No active shift found');
        $blockers = counter_repo_close_blockers($connection, $counterId, $shift);
        if ($blockers['pending_returns'] > 0) throw new LogicException('There are pending returns on this counter. Finish or reject them before closing.');
        if ($blockers['pending_transfers'] > 0) throw new LogicException('There are unreceived transfers on this counter. Receive them before closing.');
        $strictBillClose = !function_exists('counter_close_requires_all_bills_cleared') || counter_close_requires_all_bills_cleared();
        if ($blockers['uncleared_bills'] > 0 && $strictBillClose) throw new LogicException('There are uncleared bills on this counter. Clear them before closing.');
        mysqli_begin_transaction($connection);
        $transactionStarted = true;
        $sales = sales_repo_counter_totals($connection, $counterId, $shift);
        $summaryId = counter_summary_repo_create($connection, $counterId, $userId, $shift, $sales);
        $cspData = counter_summary_repo_document($connection, $summaryId, $counterId, $userId, $shift, $counter, $sales);
        $cspDocument = [
            'payload_version' => 1,
            'document_type' => 'CSP',
            'title' => 'COUNTER SUMMARY PRINT',
            'header' => [
                'counter_id' => $counterId,
                'counter_name' => (string)($counter['name'] ?? ''),
                'shift' => $shift,
                'shift_date' => $shift,
                'opening_staff_id' => $cspData['opening_staff_id'] ?? 0,
                'opening_at' => $cspData['opening_at'] ?? '',
                'closing_staff_id' => $cspData['closing_staff_id'] ?? $userId,
                'closing_at' => $cspData['closing_at'] ?? date('Y-m-d H:i:s'),
                'total_sales' => (float)($sales['total'] ?? 0),
            ],
            'csp' => $cspData,
        ];
        $counterQueueId = \KTouch\Modules\Chaba\V1\Repositories\ChabaQueueRepository::enqueue($connection, ['ref_id'=>$summaryId,'print_type'=>'CSP','label'=>'COUNTER SUMMARY','cntr_id'=>$counterId,'route_path'=>'csp.php','route_query'=>"summary_id={$summaryId}&cntr_id={$counterId}&shift=".urlencode($shift),'requested_by'=>$userId,'shift'=>$shift,'payload'=>$cspDocument]);
        $openBefore = counter_repo_open_count($connection);
        counter_repo_close_status($connection, $counterId);
        counter_repo_release_cashier($connection, $userId);
        $isLastCounter = $openBefore <= 1;
        $sspDocument = $isLastCounter ? shift_summary_repo_document($connection, $shift, $userId) : [];
        $sspQueueId = $isLastCounter ? \KTouch\Modules\Chaba\V1\Repositories\ChabaQueueRepository::enqueue($connection, ['print_type'=>'SSP','label'=>'SHIFT SUMMARY','route_path'=>'ssp.php','route_query'=>'shift='.urlencode($shift),'requested_by'=>$userId,'shift'=>$shift,'payload'=>$sspDocument]) : 0;
        $shiftQueueId = $sspQueueId;
        if ($isLastCounter) shift_repo_close_active($connection, $shift, $userId);
        mysqli_commit($connection);
        $transactionStarted = false;
        $result = ['summary_id'=>$summaryId, 'counter_queue_id'=>$counterQueueId, 'shift_queue_id'=>$shiftQueueId, 'is_last_counter'=>$isLastCounter, 'shift'=>$shift];
        counter_log('counter_closed', $result + ['counter_id'=>$counterId, 'user_id'=>$userId]);
        return counter_api_response(true, 'closed', 'Counter closed successfully', ['counter_id'=>$counterId] + $result);
    } catch (LogicException $e) {
        if ($transactionStarted) mysqli_rollback($connection);
        return counter_api_response(false, 'invalid_state', $e->getMessage(), ['counter_id'=>$counterId]);
    } catch (Throwable $e) {
        if ($transactionStarted) mysqli_rollback($connection);
        counter_log('counter_close_failed', ['counter_id'=>$counterId, 'error'=>$e->getMessage()]);
        return counter_api_response(false, 'server_error', $e->getMessage(), ['counter_id'=>$counterId]);
    }
}
