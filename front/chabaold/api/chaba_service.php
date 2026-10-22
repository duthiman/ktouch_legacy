<?php

require_once __DIR__ . '/../funcs.php';

if (!function_exists('chaba_service_get_sale_context')) {
    function chaba_service_get_sale_context($sale_id)
    {
        $sale_id = (int)$sale_id;
        if ($sale_id < 1) {
            return ['ok' => false, 'message' => 'Invalid sale.', 'context' => []];
        }

        $sale = chaba_repo_get_sale($sale_id);
        if (!$sale) {
            $sale = chaba_repo_get_sale_fallback_context($sale_id);
        }

        $totals = chaba_repo_get_sale_totals($sale_id);
        $user_id = (int)($sale['user_id'] ?? 0);
        $counter = (int)($sale['counter'] ?? 0);

        return [
            'ok' => true,
            'message' => 'Sale context loaded.',
            'context' => [
                'sale_id' => $sale_id,
                'user_id' => $user_id,
                'waiter_name' => $user_id > 0 ? (string)get_column('kp_users', 'username', "user_id='$user_id'") : '',
                'shift' => (string)($sale['shift'] ?? ''),
                'counter' => $counter,
                'counter_name' => $counter > 0 ? get_counter($counter) : '',
                'amount' => (float)($sale['amount'] ?? 0),
                'totals' => $totals,
            ],
        ];
    }
}

if (!function_exists('chaba_service_get_bill_payload')) {
    function chaba_service_get_bill_payload($sale_id)
    {
        $contextResult = chaba_service_get_sale_context($sale_id);
        if (!$contextResult['ok']) {
            return $contextResult;
        }

        $sale_id = (int)$sale_id;
        $context = $contextResult['context'];
        $sale = chaba_repo_get_sale($sale_id);
        if (!$sale) {
            $sale = chaba_repo_get_sale_fallback_context($sale_id);
        }

        $amount = (float)($sale['amount'] ?? (($context['totals']['items_total'] ?? 0) ?: ($context['totals']['print_total'] ?? 0)));
        $bill_context = function_exists('kp_chaba_bill_context')
            ? kp_chaba_bill_context($GLOBALS['connection'], $sale_id, $sale, '')
            : [
                'parent_bill' => (int)($sale['parent'] ?? 0),
                'has_parent_sale_col' => false,
                'has_sales_open_table' => false,
                'open_state' => '',
                'closed_parent_final' => false,
                'open_display_bill_no' => 0,
                'previous_balance' => 0,
                'current_total' => 0,
                'current_balance' => 0,
                'previous_bill_sale_id' => 0,
            ];

        $parent_bill = (int)($bill_context['parent_bill'] ?? 0);
        $show_continue_summary = ($parent_bill > 0 && $sale_id !== $parent_bill);
        $open_display_previous = (float)($bill_context['previous_balance'] ?? 0);
        $open_current_total = (float)($bill_context['current_total'] ?? $amount);
        $open_balance_total = (float)($bill_context['current_balance'] ?? ($open_display_previous + $open_current_total));
        $open_display_bill_no = (int)($bill_context['open_display_bill_no'] ?? 0);
        $vat = round((16 / 116) * $amount, 2, PHP_ROUND_HALF_UP);
        $ctl = round((2 / 102) * $amount, 2, PHP_ROUND_HALF_UP);

        $items = chaba_repo_get_sale_lines($sale_id, 'kp_sale_items');
        $hour_one_items = [];
        $hour_two_items = [];
        foreach ($items as $item) {
            if ((int)($item['hour'] ?? 1) === 2) {
                $hour_two_items[] = $item;
            } else {
                $hour_one_items[] = $item;
            }
        }

        $print_settings = chaba_repo_get_print_settings();
        $company = chaba_repo_get_company_settings();
        $mpesa_label = (!empty($print_settings['mpesa']) && (int)$print_settings['mpesa'] === 2)
            ? 'PAYBILL ' . ($company['mpesa_no'] ?? '') . ' ACCOUNT: ' . ($company['mpesa_account'] ?? '')
            : 'TILL ' . ($company['mpesa_no'] ?? '');

        return [
            'ok' => true,
            'message' => 'Bill payload loaded.',
            'payload' => [
                'context' => $context,
                'items' => $items,
                'print_items' => chaba_repo_get_sale_lines($sale_id, 'kp_sale_print'),
                'hour_one_items' => $hour_one_items,
                'hour_two_items' => $hour_two_items,
                'print_settings' => $print_settings,
                'company' => $company,
                'amount' => $amount,
                'vat' => $vat,
                'ctl' => $ctl,
                'mpesa_label' => $mpesa_label,
                'show_continue_summary' => $show_continue_summary,
                'continue_summary' => [
                    'parent_bill' => $parent_bill,
                    'previous_bill_sale_id' => (int)($bill_context['previous_bill_sale_id'] ?? 0),
                    'previous_total' => $open_display_previous,
                    'current_bill' => $open_current_total,
                    'balance' => $open_balance_total,
                    'bill_no' => $open_display_bill_no,
                ],
                'served_by_name' => (string)($context['waiter_name'] ?? ''),
            ],
        ];
    }
}

if (!function_exists('chaba_service_get_ticket_payload')) {
    function chaba_service_get_ticket_payload($sale_id, $ticket_type, $delta_only = false, $batch_id = 0)
    {
        $ticket_type = chaba_api_clean_ticket_type($ticket_type);
        if ($ticket_type === '') {
            return ['ok' => false, 'message' => 'Invalid ticket type.', 'payload' => []];
        }

        $contextResult = chaba_service_get_sale_context($sale_id);
        if (!$contextResult['ok']) {
            return $contextResult;
        }

        $department_map = ['bot' => 1, 'kot' => 2, 'got' => 3, 'cashier_order' => 0];
        $department_id = $department_map[$ticket_type];
        $source = $delta_only ? (($batch_id > 0) ? 'ticket_batch' : 'sale_print') : 'sale_items';

        if ($ticket_type === 'cashier_order') {
            $sections = [];
            foreach ([1 => 'BAR ITEMS', 2 => 'KITCHEN ITEMS'] as $dpt => $label) {
                $sections[] = [
                    'dpt' => $dpt,
                    'label' => $label,
                    'lines' => chaba_repo_get_ticket_lines($sale_id, $dpt, $source, $batch_id),
                ];
            }
            $lines = [];
        } else {
            $sections = [];
            $lines = chaba_repo_get_ticket_lines($sale_id, $department_id, $source, $batch_id);
        }

        return [
            'ok' => true,
            'message' => 'Ticket payload loaded.',
            'payload' => [
                'type' => $ticket_type,
                'delta_only' => (bool)$delta_only,
                'batch_id' => (int)$batch_id,
                'context' => $contextResult['context'],
                'lines' => $lines,
                'sections' => $sections,
                'print_settings' => chaba_repo_get_print_settings(),
                'company' => chaba_repo_get_company_settings(),
            ],
        ];
    }
}

if (!function_exists('chaba_service_get_render_payload')) {
    function chaba_service_get_render_payload($type, $sale_id = 0, $ref_id = 0, $delta_only = false, $batch_id = 0)
    {
        $type = chaba_api_clean_print_type($type);
        if ($type === '') {
            return ['ok' => false, 'message' => 'Invalid render type.', 'payload' => []];
        }

        if (in_array($type, ['bill', 'ret'], true)) {
            return chaba_service_get_bill_payload($sale_id);
        }

        if (in_array($type, ['bot', 'kot', 'got', 'cashier_order'], true)) {
            return chaba_service_get_ticket_payload($sale_id, $type, $delta_only, $batch_id);
        }

        return [
            'ok' => true,
            'message' => 'Render route prepared.',
            'payload' => [
                'type' => $type,
                'sale_id' => (int)$sale_id,
                'ref_id' => (int)$ref_id,
                'render_url' => chaba_api_absolute_url((string)(kp_print_job_preview_url([
                    'print_type' => $type,
                    'sale_id' => (int)$sale_id,
                    'ref_id' => (int)$ref_id,
                ]) ?? '')),
                'print_settings' => chaba_repo_get_print_settings(),
                'company' => chaba_repo_get_company_settings(),
            ],
        ];
    }
}
