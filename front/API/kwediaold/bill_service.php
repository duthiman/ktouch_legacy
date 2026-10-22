<?php
/**
 * Business rules for Billing
 */
require_once 'bill_repository.php';
require_once 'bill_logger.php';
require_once 'bill_helpers.php';

function continue_open_last() {
    $bill = repo_get_open_bill();
    if (!$bill) {
        return json_response('error', 'No open bills found');
    }
    bill_log("CONTINUE_LAST", "Bill ID: " . $bill['item_id']);
    return json_response('success', '', $bill);
}

function continue_open_each() {
    // Logic for handling multiple open bills
    bill_log("CONTINUE_EACH", "Initiated bulk open process");
    return json_response('success', 'Processing all open bills');
}

function save_identifier($bill_id, $identifier) {
    if (!validate_bill_input($bill_id)) {
        return json_response('error', 'Invalid Bill ID');
    }

    if (repo_save_identifier($bill_id, $identifier)) {
        bill_log("SAVE_ID", "ID $identifier saved for Bill $bill_id");
        return json_response('success', 'Identifier saved');
    }
    
    return json_response('error', 'Database update failed');
}

function close_open_bill($bill_id) {
    // Rules for closing
    bill_log("CLOSE_BILL", "Closing Bill $bill_id");
    return json_response('success', "Bill $bill_id closed");
}

function calculate_bill_total($bill_id) {
    if (!validate_bill_input($bill_id)) {
        return json_response('error', 'Invalid Bill ID');
    }

    $total = repo_get_bill_total($bill_id);
    bill_log("CALC_TOTAL", "Bill $bill_id total: $total");
    return json_response('success', 'Total calculated', ['total' => $total]);
}

function get_current_cart($sale_id) {
    if (!validate_bill_input($sale_id)) {
        return json_response('error', 'Invalid Sale ID');
    }
    $items = repo_get_cart_items($sale_id);
    return json_response('success', '', ['items' => $items]);
}

function commit_session($sale_id) {
    if (repo_commit_session_items($sale_id)) {
        bill_log("SEND_ORDER", "Session items committed for Sale $sale_id");
        return json_response('success', 'Order sent/committed');
    }
    return json_response('error', 'Failed to commit session');
}

function finalize_bill($sale_id) {
    $shift = get_column('kp_shift', 'shift', "1");
    if (repo_finalize_and_move_items($sale_id, $shift)) {
        bill_log("FINALIZE_BILL", "Bill $sale_id moved to permanent records and closed");
        return json_response('success', 'Bill finalized');
    }
    return json_response('error', 'Finalization failed');
}

/**
 * Gathers context for the Send Order process.
 */
if (!function_exists('bill_get_send_order_context')) {
    function bill_get_send_order_context($user_id, $sale_id) {
        $open_row = repo_get_open_family_row($sale_id, $user_id);
        $thesion = repo_get_thesion($user_id);
        return [
            'sale_id' => (int)$sale_id,
            'user_id' => (int)$user_id,
            'open_mode' => $open_row ? 'open' : 'closed',
            'bill_mode' => $open_row['bill_mode'] ?? 'each',
            'is_open_last' => (($open_row['bill_mode'] ?? '') === 'last'),
            'parent_sale_id' => (int)($open_row['parent_sale_id'] ?? 0),
            'counter' => (int)($thesion['cntr_id'] ?? 0)
        ];
    }
}

if (!function_exists('bill_handle_registry_after_send')) {
    /**
     * Updates registry state after sending.
     */
    function bill_handle_registry_after_send($context, $params = []) {
        $sale_id = $context['sale_id'];
        $user_id = $context['user_id'];

        if ($context['is_open_last']) {
            $total = repo_get_bill_total($sale_id);
            $status = ($params['closing_open_last'] ?? false) ? 2 : 1;
            repo_update_open_registry($user_id, $sale_id, [
                'open_amount' => $total,
                'status' => $status
            ]);
            bill_log("REGISTRY_SYNC", "Sale $sale_id synced to amount $total");
        }
        return true;
    }
}

if (!function_exists('bill_handle_registry_before_send')) {
    function bill_handle_registry_before_send($context) {
        bill_log("PRE_SEND_INIT", "Preparing registry for Sale " . $context['sale_id']);
        return true;
    }
}