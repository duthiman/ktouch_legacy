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
        mysqli_begin_transaction($connection);
        $transactionStarted = true;
        $sales = sales_repo_counter_totals($connection, $counterId, $shift);
        $summaryId = counter_summary_repo_create($connection, $counterId, $userId, $shift, $sales);
        $cspDocument = [
            'payload_version' => 1,
            'document_type' => 'CSP',
            'title' => 'COUNTER SUMMARY PRINT',
            'header' => [
                'counter_id' => $counterId,
                'counter_name' => (string)($counter['name'] ?? ''),
                'shift' => $shift,
                'shift_date' => $shift,
                'closing_staff_id' => $userId,
                'closing_at' => date('Y-m-d H:i:s'),
                'total_sales' => (float)($sales['total'] ?? 0),
            ],
            'csp' => [
                'summary_id' => $summaryId,
                'counter_id' => $counterId,
                'counter_name' => (string)($counter['name'] ?? ''),
                'shift' => $shift,
                'summary' => $sales,
                'bill_count' => (int)($sales['bills'] ?? 0),
                'sales' => [], 'staff_sales' => [], 'transfers' => [],
                'units_generated' => [], 'deleted_bills' => [], 'returns' => [],
                'requisitions' => [], 'user_sales' => [],
            ],
        ];
        $counterQueueId = \KTouch\Modules\Chaba\V1\Repositories\ChabaQueueRepository::queueCsp($connection, $summaryId, $counterId, $shift, $userId, $cspDocument);
        $openBefore = counter_repo_open_count($connection);
        counter_repo_close_status($connection, $counterId);
        counter_repo_release_cashier($connection, $userId);
        $isLastCounter = $openBefore <= 1;
        $sspQueueId = $isLastCounter ? \KTouch\Modules\Chaba\V1\Repositories\ChabaQueueRepository::queueSsp($connection, $shift, $userId, [
            'payload_version' => 1, 'document_type' => 'SSP', 'title' => 'SHIFT SUMMARY PRINT',
            'header' => ['shift' => $shift, 'shift_date' => $shift, 'print_time' => date('Y-m-d H:i:s')],
            'total_sales' => ['bills' => (int)($sales['bills'] ?? 0), 'total' => (float)($sales['total'] ?? 0)],
            'counter_sales' => [], 'cashier_collections' => [], 'customer_credit_bills' => [],
            'customer_credit_payments' => [], 'waiter_totals' => [],
        ]) : 0;
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
