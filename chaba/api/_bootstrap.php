<?php
session_start();

header('Content-Type: application/json; charset=utf-8');

require_once __DIR__ . '/../../inc/connection.php';

function chaba_api_json($payload, $status = 200)
{
    http_response_code($status);
    echo json_encode($payload);
    exit;
}

function chaba_api_request_data()
{
    static $data = null;
    if ($data !== null) {
        return $data;
    }

    $data = $_POST;
    $raw = file_get_contents('php://input');
    if ($raw) {
        $decoded = json_decode($raw, true);
        if (json_last_error() === JSON_ERROR_NONE && is_array($decoded)) {
            $data = array_merge($data, $decoded);
        }
    }

    return $data;
}

function chaba_api_base_url()
{
    $https = (!empty($_SERVER['HTTPS']) && $_SERVER['HTTPS'] !== 'off') || (($_SERVER['SERVER_PORT'] ?? '') == 443);
    $scheme = $https ? 'https' : 'http';
    $host = $_SERVER['HTTP_HOST'] ?? 'localhost';
    $scriptName = str_replace('\\', '/', $_SERVER['SCRIPT_NAME'] ?? '');
    $marker = '/chaba/api/';
    $pos = strpos($scriptName, $marker);
    $basePath = $pos !== false ? substr($scriptName, 0, $pos) : rtrim(dirname($scriptName), '/');
    return rtrim($scheme . '://' . $host . $basePath, '/') . '/';
}

function chaba_api_absolute_url($path)
{
    if (!$path) {
        return '';
    }

    if (preg_match('#^https?://#i', $path)) {
        return $path;
    }

    $clean = ltrim(str_replace('\\', '/', $path), '/');
    return chaba_api_base_url() . $clean;
}

function chaba_api_render_url(array $job)
{
    // V1 renders frozen queue snapshots directly; do not dispatch through
    // route files or the removed procedural helper layer.
    $queueId = (int)($job['queue_id'] ?? 0);
    if ($queueId > 0) {
        return chaba_api_absolute_url('front/chaba/index.php?action=render&queue_id=' . rawurlencode((string)$queueId));
    }

    $routePath = trim((string)($job['route_path'] ?? ''));
    $routeQuery = trim((string)($job['route_query'] ?? ''));
    if ($routePath !== '') {
        $url = $routePath;
        if (strpos($url, '/') === false) {
            $url = 'front/chaba/' . ltrim($url, '/');
        }
        if ($routeQuery !== '') {
            $url .= (strpos($url, '?') === false ? '?' : '&') . $routeQuery;
        }
        return chaba_api_absolute_url($url);
    }

    $type = strtolower(trim((string)($job['print_type'] ?? '')));
    $saleId = (int)($job['sale_id'] ?? 0);
    $refId = (int)($job['ref_id'] ?? 0);
    if ($refId < 1) {
        $refId = $saleId;
    }

    $map = [
        'bill' => $saleId > 0 ? 'front/chaba/render_bill.php?sale_id=' . rawurlencode((string)$saleId) : '',
        'cashier_order' => $saleId > 0 ? 'front/chaba/render_cashier_order.php?sale_id=' . rawurlencode((string)$saleId) : '',
        'bot' => $saleId > 0 ? 'front/chaba/render_bot.php?sale_id=' . rawurlencode((string)$saleId) : '',
        'kot' => $saleId > 0 ? 'front/chaba/render_kot.php?sale_id=' . rawurlencode((string)$saleId) : '',
        'got' => $saleId > 0 ? 'front/chaba/render_got.php?sale_id=' . rawurlencode((string)$saleId) : '',
        'ret' => $saleId > 0 ? 'front/chaba/render_ret.php?sale_id=' . rawurlencode((string)$saleId) : '',
        'kug' => $saleId > 0 ? 'front/chaba/render_kug.php?sale_id=' . rawurlencode((string)$saleId) : '',
        'gwitia' => $refId > 0 ? 'front/chaba/render_req.php?id=' . rawurlencode((string)$refId) : '',
        'tots' => $refId > 0 ? 'front/chaba/render_tots.php?id=' . rawurlencode((string)$refId) : '',
        'variance' => $refId > 0 ? 'front/chaba/render_variance.php?cntr_id=' . rawurlencode((string)$refId) : '',
        'summary' => $refId > 0 ? 'front/chaba/render_shift_sum.php?id=' . rawurlencode((string)$refId) : '',
        'shift_sum' => $refId > 0 ? 'front/chaba/render_shift_sum.php?id=' . rawurlencode((string)$refId) : '',
        'staff_sum' => $refId > 0 ? 'front/chaba/render_staff_summary.php?id=' . rawurlencode((string)$refId) : '',
        'counter_sum' => $refId > 0 ? 'front/chaba/render_counter_sum.php?summary_id=' . rawurlencode((string)$refId) : '',
    ];

    return isset($map[$type]) ? chaba_api_absolute_url($map[$type]) : '';
}


