<?php

require_once __DIR__ . '/lpo_repo.php';

if (!function_exists('lpo_service_get_context')) {
    function lpo_service_get_context($lpo_id)
    {
        $lpo_id = (int)$lpo_id;
        if ($lpo_id < 1) {
            return [
                'ok' => false,
                'message' => 'Invalid LPO.',
                'context' => [],
            ];
        }

        return [
            'ok' => true,
            'message' => 'LPO service shell is ready.',
            'context' => [
                'lpo_id' => $lpo_id,
            ],
        ];
    }
}

if (!function_exists('lpo_service_create')) {
    function lpo_service_create($payload = [])
    {
        return lpo_repo_create($payload);
    }
}

if (!function_exists('lpo_service_initialize')) {
    function lpo_service_initialize($userId, $supplierId, $poType, $poBy = 5)
    {
        return lpo_repo_initialize($userId, $supplierId, $poType, $poBy);
    }
}

if (!function_exists('lpo_service_continue')) {
    function lpo_service_continue($poId, $supplierId = 0)
    {
        return lpo_repo_continue($poId, $supplierId);
    }
}

if (!function_exists('lpo_service_get_dashboard_data')) {
    function lpo_service_get_dashboard_data()
    {
        $totals = lpo_repo_dashboard_totals();
        $spend = lpo_repo_dashboard_spend();
        $trend = lpo_repo_dashboard_trend();
        $suppliers = lpo_repo_dashboard_top_suppliers();

        return [
            'success' => true,
            'message' => 'LPO dashboard loaded.',
            'total_pos' => (int)($totals['total_pos'] ?? 0),
            'pending_approval' => (int)($totals['pending_approval'] ?? 0),
            'pending_receiving' => (int)($totals['pending_receiving'] ?? 0),
            'completed_grns' => (int)($totals['completed_grns'] ?? 0),
            'ongoing_grns' => (int)($totals['ongoing_grns'] ?? 0),
            'total_suppliers' => (int)($totals['total_suppliers'] ?? 0),
            'spend_this_month' => (float)($spend['spend_this_month'] ?? 0),
            'spend_30d' => (float)($spend['spend_30d'] ?? 0),
            'spend_all_time' => (float)($spend['spend_all_time'] ?? 0),
            'trend_labels' => $trend['trend_labels'] ?? [],
            'trend_count' => $trend['trend_count'] ?? [],
            'trend_amount' => $trend['trend_amount'] ?? [],
            'supplier_labels' => $suppliers['supplier_labels'] ?? [],
            'supplier_values' => $suppliers['supplier_values'] ?? [],
            'approval_list' => lpo_repo_dashboard_approval_list(),
            'receiving_list' => lpo_repo_dashboard_receiving_list(),
            'grn_list' => lpo_repo_dashboard_grn_list(),
        ];
    }
}
