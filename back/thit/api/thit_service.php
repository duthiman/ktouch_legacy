<?php
declare(strict_types=1);

require_once __DIR__ . '/thit_repo.php';

function thit_service_dashboard(mysqli $db): array
{
    $k = thit_repo_kpis($db);
    $v = thit_repo_stock_values($db);
    return [
        'kpi' => [
            'pending_grn' => $k['pendingGrn'], 'pending_issues' => $k['pendingIssues'],
            'out_of_stock' => $k['out'], 'low_stock' => $k['low'],
            'approvals' => $k['approvals'], 'pending_dispatch' => $k['pendingDispatch'], 'counter_requests' => $k['counterRequests'],
        ],
        'values' => ['retail' => $v['retail'], 'cost' => $v['cost'], 'margin' => $v['margin'], 'margin_pct' => $v['marginPct']],
        'activity' => thit_repo_activity($db),
        'counters' => thit_repo_counters($db),
        'recent_grns' => thit_repo_recent_grns($db),
        'generated_at' => date('c'),
    ];
}
