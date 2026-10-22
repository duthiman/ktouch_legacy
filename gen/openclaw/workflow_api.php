<?php
/**
 * OpenClaw Workflow Export API
 *
 * Standalone OpenClaw entrypoint.
 * Dependency on main system: database connection only.
 */

if (session_status() === PHP_SESSION_NONE) {
    session_start();
}

header('Vary: Origin');
header('Access-Control-Allow-Origin: *');
header('Access-Control-Allow-Headers: Content-Type, Authorization, X-API-Key');
header('Access-Control-Allow-Methods: GET, POST, OPTIONS');

if (strtoupper((string)($_SERVER['REQUEST_METHOD'] ?? 'GET')) === 'OPTIONS') {
    echo json_encode([
        'ok' => true,
        'message' => 'preflight_ok',
    ]);
    exit;
}

header('Content-Type: application/json; charset=UTF-8');

include __DIR__ . '/../../inc/connection.php';
include __DIR__ . '/auth.php';
include __DIR__ . '/api_helpers.php';

if (!isset($connection) || !($connection instanceof mysqli)) {
    echo json_encode([
        'ok' => false,
        'error' => 'database_unavailable',
    ]);
    exit;
}

mysqli_set_charset($connection, 'utf8mb4');

$warnings = [];

$expectedKey = oc_configured_api_key();
$providedKey = oc_provided_api_key();
$sessionUserId = (int)($_SESSION['user_id'] ?? 0);

$authorized = false;
$authMode = 'none';

if ($expectedKey !== '') {
    if ($providedKey !== '' && hash_equals($expectedKey, $providedKey)) {
        $authorized = true;
        $authMode = 'api_key';
    } elseif ($sessionUserId > 0) {
        $authorized = true;
        $authMode = 'session';
    }
} elseif ($sessionUserId > 0) {
    $authorized = true;
    $authMode = 'session';
}

if (!$authorized) {
    echo json_encode([
        'ok' => false,
        'error' => 'unauthorized',
        'message' => $expectedKey === ''
            ? 'API key is not configured. Set OPENCLAW_X_API_KEY in gen/openclaw/auth.php, or call from logged-in session.'
            : 'Invalid or missing API key.',
    ]);
    exit;
}

$rawDatasetsValue = oc_request_value('datasets', oc_request_value('dataset', ''));
if (is_array($rawDatasetsValue)) {
    $rawDatasetsValue = implode(',', $rawDatasetsValue);
}
$rawDatasets = trim((string)$rawDatasetsValue);
if ($rawDatasets === '') {
    $rawDatasets = 'all';
}

$from = trim((string)oc_request_value('from', date('Y-m-d', strtotime('-30 days'))));
$to = trim((string)oc_request_value('to', date('Y-m-d')));

if (!oc_valid_date($from) || !oc_valid_date($to)) {
    echo json_encode([
        'ok' => false,
        'error' => 'invalid_date_range',
        'message' => 'Use from/to format YYYY-MM-DD',
    ]);
    exit;
}

if ($from > $to) {
    $tmp = $from;
    $from = $to;
    $to = $tmp;
}

$limit = (int)oc_request_value('limit', 200);
$page = (int)oc_request_value('page', 1);
$withTotals = oc_request_bool('with_totals', true);
$responseModeRaw = trim((string)oc_request_value('response_mode', ''));
$responseMode = strtolower($responseModeRaw !== '' ? $responseModeRaw : 'full');
if (!in_array($responseMode, ['full', 'compact', 'analysis'], true)) {
    $responseMode = 'full';
}
if ($limit < 1) {
    $limit = 200;
}
if ($limit > 1000) {
    $limit = 1000;
}
if ($page < 1) {
    $page = 1;
}
$offset = ($page - 1) * $limit;

$customerId = (int)oc_request_value('customer_id', 0);
$pushUrl = trim((string)oc_request_value('push_url', ''));
$pushToken = trim((string)oc_request_value('push_token', ''));
$pushTimeout = (int)oc_request_value('push_timeout', 20);

$datasetAliases = [
    'customer' => 'customers',
    'customer_data' => 'customers',
    'customer_credits' => 'customer_credits',
    'credit_sales' => 'customer_credits',
    'items' => 'items',
    'items_list' => 'items',
    'categories' => 'categories',
    'subcategory' => 'subcategories',
    'sub_category' => 'subcategories',
    'subcategories' => 'subcategories',
    'sales' => 'sales',
    'sales_item' => 'sale_items',
    'sales_items' => 'sale_items',
    'sale_items' => 'sale_items',
    'sale_payment' => 'sale_payments',
    'sale_payments' => 'sale_payments',
    'pay_mode' => 'pay_modes',
    'pay_modes' => 'pay_modes',
    'purchase' => 'purchases',
    'purchases' => 'purchases',
    'purchase_items' => 'purchase_items',
    'expenses' => 'expenses',
    'stock_take' => 'stock_take',
    'stock_take_by_date' => 'stock_take',
    'users' => 'users',
    'user_list' => 'users',
];

$allDatasets = [
    'customers',
    'customer_credits',
    'items',
    'categories',
    'subcategories',
    'sales',
    'sale_items',
    'sale_payments',
    'pay_modes',
    'purchases',
    'purchase_items',
    'expenses',
    'stock_take',
];

$requested = [];
foreach (explode(',', strtolower($rawDatasets)) as $d) {
    $key = trim($d);
    if ($key === '') {
        continue;
    }
    if ($key === 'all') {
        $requested = $allDatasets;
        break;
    }
    if (isset($datasetAliases[$key])) {
        $requested[] = $datasetAliases[$key];
    }
}
$requested = array_values(array_unique($requested));
if (!$requested) {
    $requested = ['customers'];
    $warnings[] = 'No valid dataset key provided, defaulted to customers.';
}

if ($responseModeRaw === '' && count($requested) === 1 && $requested[0] === 'sales') {
    $responseMode = 'analysis';
}

if ($responseMode === 'analysis' && !in_array('sales', $requested, true)) {
    $requested[] = 'sales';
}

$fromEsc = mysqli_real_escape_string($connection, $from);
$toEsc = mysqli_real_escape_string($connection, $to);

$data = [];
$pagination = [];

foreach ($requested as $ds) {
    if ($ds === 'customers') {
        if (!oc_table_exists($connection, 'kp_customers')) {
            $warnings[] = 'kp_customers table not found.';
            $data['customers'] = [];
            $pagination['customers'] = oc_pagination_meta($page, $limit, $withTotals ? 0 : null, 0);
            continue;
        }
        $where = '1=1';
        if ($customerId > 0) {
            $where .= ' AND cust_id=' . $customerId;
        }
        $rows = oc_fetch_all(
            $connection,
            "SELECT * FROM kp_customers WHERE $where ORDER BY cust_id DESC LIMIT $limit OFFSET $offset"
        );
        $totalRecords = $withTotals ? oc_count_query($connection, "SELECT COUNT(*) FROM kp_customers WHERE $where") : null;
        $data['customers'] = $rows;
        $pagination['customers'] = oc_pagination_meta($page, $limit, $totalRecords, count($rows));
        continue;
    }

    if ($ds === 'customer_credits') {
        $payload = [
            'credit_sales' => [],
            'credit_payments' => [],
            'credit_payment_items' => [],
        ];
        $creditPagination = [];

        if (oc_table_exists($connection, 'kp_sales')) {
            $where = "pay_status=3 AND shift >= '$fromEsc' AND shift <= '$toEsc'";
            if ($customerId > 0) {
                $where .= ' AND cust_id=' . $customerId;
            }
            $rows = oc_fetch_all(
                $connection,
                "SELECT * FROM kp_sales WHERE $where ORDER BY sale_id DESC LIMIT $limit OFFSET $offset"
            );
            $payload['credit_sales'] = $rows;
            $creditPagination['credit_sales'] = oc_pagination_meta($page, $limit, $withTotals ? oc_count_query($connection, "SELECT COUNT(*) FROM kp_sales WHERE $where") : null, count($rows));
        } else {
            $warnings[] = 'kp_sales table not found for customer_credits.';
            $creditPagination['credit_sales'] = oc_pagination_meta($page, $limit, $withTotals ? 0 : null, 0);
        }

        if (oc_table_exists($connection, 'kp_customer_credit_payments')) {
            $where = "shift_date >= '$fromEsc' AND shift_date <= '$toEsc'";
            if ($customerId > 0) {
                $where .= ' AND cust_id=' . $customerId;
            }
            $rows = oc_fetch_all(
                $connection,
                "SELECT * FROM kp_customer_credit_payments WHERE $where ORDER BY credit_pay_id DESC LIMIT $limit OFFSET $offset"
            );
            $payload['credit_payments'] = $rows;
            $creditPagination['credit_payments'] = oc_pagination_meta($page, $limit, $withTotals ? oc_count_query($connection, "SELECT COUNT(*) FROM kp_customer_credit_payments WHERE $where") : null, count($rows));
        } else {
            $creditPagination['credit_payments'] = oc_pagination_meta($page, $limit, $withTotals ? 0 : null, 0);
        }

        if (oc_table_exists($connection, 'kp_customer_credit_payment_items')) {
            $where = "pay_date >= '$fromEsc' AND pay_date <= '$toEsc'";
            if ($customerId > 0) {
                $where .= ' AND cust_id=' . $customerId;
            }
            $rows = oc_fetch_all(
                $connection,
                "SELECT * FROM kp_customer_credit_payment_items WHERE $where ORDER BY credit_item_id DESC LIMIT $limit OFFSET $offset"
            );
            $payload['credit_payment_items'] = $rows;
            $creditPagination['credit_payment_items'] = oc_pagination_meta($page, $limit, $withTotals ? oc_count_query($connection, "SELECT COUNT(*) FROM kp_customer_credit_payment_items WHERE $where") : null, count($rows));
        } else {
            $creditPagination['credit_payment_items'] = oc_pagination_meta($page, $limit, $withTotals ? 0 : null, 0);
        }

        $data['customer_credits'] = oc_enrich_credit_payload($connection, $payload);
        $pagination['customer_credits'] = $creditPagination;
        continue;
    }

    if ($ds === 'items') {
        if (!oc_table_exists($connection, 'kp_items')) {
            $warnings[] = 'kp_items table not found.';
            $data['items'] = [];
            $pagination['items'] = oc_pagination_meta($page, $limit, $withTotals ? 0 : null, 0);
            continue;
        }
        $catJoin = oc_table_exists($connection, 'kp_cat') ? 'LEFT JOIN kp_cat c ON c.cat_id=i.cat_id' : '';
        $subJoin = oc_table_exists($connection, 'kp_sub_cat') ? 'LEFT JOIN kp_sub_cat sc ON sc.sub_id=i.sub_id' : '';
        $catSelect = oc_table_exists($connection, 'kp_cat') ? ', c.name AS cat_name' : '';
        $subSelect = oc_table_exists($connection, 'kp_sub_cat') ? ', sc.name AS subcat_name' : '';

        $rows = oc_fetch_all(
            $connection,
            "SELECT i.*$catSelect$subSelect FROM kp_items i $catJoin $subJoin ORDER BY i.item_id DESC LIMIT $limit OFFSET $offset"
        );
        $data['items'] = $rows;
        $pagination['items'] = oc_pagination_meta($page, $limit, $withTotals ? oc_count_query($connection, "SELECT COUNT(*) FROM kp_items") : null, count($rows));
        continue;
    }

    if ($ds === 'categories') {
        if (!oc_table_exists($connection, 'kp_cat')) {
            $warnings[] = 'kp_cat table not found.';
            $data['categories'] = [];
            $pagination['categories'] = oc_pagination_meta($page, $limit, $withTotals ? 0 : null, 0);
            continue;
        }
        $rows = oc_fetch_all(
            $connection,
            "SELECT * FROM kp_cat ORDER BY cat_id DESC LIMIT $limit OFFSET $offset"
        );
        $data['categories'] = $rows;
        $pagination['categories'] = oc_pagination_meta($page, $limit, $withTotals ? oc_count_query($connection, "SELECT COUNT(*) FROM kp_cat") : null, count($rows));
        continue;
    }

    if ($ds === 'subcategories') {
        if (!oc_table_exists($connection, 'kp_sub_cat')) {
            $warnings[] = 'kp_sub_cat table not found.';
            $data['subcategories'] = [];
            $pagination['subcategories'] = oc_pagination_meta($page, $limit, $withTotals ? 0 : null, 0);
            continue;
        }
        $rows = oc_fetch_all(
            $connection,
            "SELECT * FROM kp_sub_cat ORDER BY sub_id DESC LIMIT $limit OFFSET $offset"
        );
        $data['subcategories'] = $rows;
        $pagination['subcategories'] = oc_pagination_meta($page, $limit, $withTotals ? oc_count_query($connection, "SELECT COUNT(*) FROM kp_sub_cat") : null, count($rows));
        continue;
    }

    if ($ds === 'sales') {
        if (!oc_table_exists($connection, 'kp_sales')) {
            $warnings[] = 'kp_sales table not found.';
            $data['sales'] = [];
            $pagination['sales'] = oc_pagination_meta($page, $limit, $withTotals ? 0 : null, 0);
            continue;
        }
        $where = "shift >= '$fromEsc' AND shift <= '$toEsc'";
        if ($customerId > 0) {
            $where .= ' AND cust_id=' . $customerId;
        }
        $rows = oc_fetch_all(
            $connection,
            "SELECT * FROM kp_sales WHERE $where ORDER BY sale_id DESC LIMIT $limit OFFSET $offset"
        );
        $data['sales'] = oc_enrich_sales($connection, $rows);
        $pagination['sales'] = oc_pagination_meta($page, $limit, $withTotals ? oc_count_query($connection, "SELECT COUNT(*) FROM kp_sales WHERE $where") : null, count($rows));
        continue;
    }

    if ($ds === 'sale_items') {
        if (!oc_table_exists($connection, 'kp_sale_items')) {
            $warnings[] = 'kp_sale_items table not found.';
            $data['sale_items'] = [];
            $pagination['sale_items'] = oc_pagination_meta($page, $limit, $withTotals ? 0 : null, 0);
            continue;
        }

        $hasShift = oc_column_exists($connection, 'kp_sale_items', 'shift');
        $hasSale = oc_column_exists($connection, 'kp_sale_items', 'sale_id');
        $salesExists = oc_table_exists($connection, 'kp_sales');
        $saleItemsOrderCol = oc_first_existing_column($connection, 'kp_sale_items', ['id', 'sale_item_id', 'item_id', 'sale_id'], 'sale_id');

        if ($hasShift) {
            $sql = "SELECT * FROM kp_sale_items WHERE shift >= '$fromEsc' AND shift <= '$toEsc' ORDER BY $saleItemsOrderCol DESC LIMIT $limit OFFSET $offset";
            $countSql = "SELECT COUNT(*) FROM kp_sale_items WHERE shift >= '$fromEsc' AND shift <= '$toEsc'";
        } elseif ($hasSale && $salesExists) {
            $sql = "SELECT si.*
                    FROM kp_sale_items si
                    INNER JOIN kp_sales s ON s.sale_id=si.sale_id
                    WHERE " . oc_sales_date_filter($fromEsc, $toEsc) . "
                    ORDER BY si.$saleItemsOrderCol DESC
                    LIMIT $limit OFFSET $offset";
            $countSql = "SELECT COUNT(*)
                         FROM kp_sale_items si
                         INNER JOIN kp_sales s ON s.sale_id=si.sale_id
                         WHERE " . oc_sales_date_filter($fromEsc, $toEsc);
        } else {
            $sql = "SELECT * FROM kp_sale_items ORDER BY $saleItemsOrderCol DESC LIMIT $limit OFFSET $offset";
            $countSql = "SELECT COUNT(*) FROM kp_sale_items";
            $warnings[] = 'sale_items returned without date filter (missing shift/sales link).';
        }

        $rows = oc_fetch_all($connection, $sql);
        $salesMap = [];
        if ($hasSale) {
            $salesRows = oc_fetch_all($connection, "SELECT * FROM kp_sales WHERE sale_id IN (" . oc_sql_in_ints(oc_unique_int_ids($rows, ['sale_id'])) . ")");
            $salesMap = oc_index_by(oc_enrich_sales($connection, $salesRows), 'sale_id');
        }
        $data['sale_items'] = oc_enrich_sale_items($connection, $rows, $salesMap);
        $pagination['sale_items'] = oc_pagination_meta($page, $limit, $withTotals ? oc_count_query($connection, $countSql) : null, count($rows));
        continue;
    }

    if ($ds === 'sale_payments') {
        if (!oc_table_exists($connection, 'kp_sale_payment')) {
            $warnings[] = 'kp_sale_payment table not found.';
            $data['sale_payments'] = [];
            $pagination['sale_payments'] = oc_pagination_meta($page, $limit, $withTotals ? 0 : null, 0);
            continue;
        }

        $dateCol = '';
        if (oc_column_exists($connection, 'kp_sale_payment', 'pay_date')) {
            $dateCol = 'pay_date';
        } elseif (oc_column_exists($connection, 'kp_sale_payment', 'created_at')) {
            $dateCol = 'DATE(created_at)';
        }

        if ($dateCol !== '') {
            $sql = "SELECT * FROM kp_sale_payment WHERE $dateCol >= '$fromEsc' AND $dateCol <= '$toEsc' ORDER BY pay_id DESC LIMIT $limit OFFSET $offset";
            $countSql = "SELECT COUNT(*) FROM kp_sale_payment WHERE $dateCol >= '$fromEsc' AND $dateCol <= '$toEsc'";
        } else {
            $sql = "SELECT * FROM kp_sale_payment ORDER BY pay_id DESC LIMIT $limit OFFSET $offset";
            $countSql = "SELECT COUNT(*) FROM kp_sale_payment";
            $warnings[] = 'sale_payments returned without date filter (date column missing).';
        }

        $rows = oc_fetch_all($connection, $sql);
        $salesMap = [];
        if (oc_column_exists($connection, 'kp_sale_payment', 'sale_id')) {
            $salesRows = oc_fetch_all($connection, "SELECT * FROM kp_sales WHERE sale_id IN (" . oc_sql_in_ints(oc_unique_int_ids($rows, ['sale_id'])) . ")");
            $salesMap = oc_index_by(oc_enrich_sales($connection, $salesRows), 'sale_id');
        }
        $data['sale_payments'] = oc_enrich_sale_payments($connection, $rows, $salesMap);
        $pagination['sale_payments'] = oc_pagination_meta($page, $limit, $withTotals ? oc_count_query($connection, $countSql) : null, count($rows));
        continue;
    }

    if ($ds === 'pay_modes') {
        if (!oc_table_exists($connection, 'kp_pay_mode')) {
            $warnings[] = 'kp_pay_mode table not found.';
            $data['pay_modes'] = [];
            $pagination['pay_modes'] = oc_pagination_meta($page, $limit, $withTotals ? 0 : null, 0);
            continue;
        }

        if (oc_column_exists($connection, 'kp_pay_mode', 'del_status')) {
            $sql = "SELECT * FROM kp_pay_mode WHERE del_status=2 ORDER BY pay_id ASC LIMIT $limit OFFSET $offset";
            $countSql = "SELECT COUNT(*) FROM kp_pay_mode WHERE del_status=2";
        } else {
            $sql = "SELECT * FROM kp_pay_mode ORDER BY pay_id ASC LIMIT $limit OFFSET $offset";
            $countSql = "SELECT COUNT(*) FROM kp_pay_mode";
        }

        $rows = oc_fetch_all($connection, $sql);
        $data['pay_modes'] = $rows;
        $pagination['pay_modes'] = oc_pagination_meta($page, $limit, $withTotals ? oc_count_query($connection, $countSql) : null, count($rows));
        continue;
    }

    if ($ds === 'purchases') {
        if (!oc_table_exists($connection, 'kp_po')) {
            $warnings[] = 'kp_po table not found.';
            $data['purchases'] = [];
            $pagination['purchases'] = oc_pagination_meta($page, $limit, $withTotals ? 0 : null, 0);
            continue;
        }

        $dateCol = 'po_date';
        if (!oc_column_exists($connection, 'kp_po', 'po_date') && oc_column_exists($connection, 'kp_po', 'shift')) {
            $dateCol = 'shift';
        }

        if (oc_column_exists($connection, 'kp_po', $dateCol)) {
            $sql = "SELECT * FROM kp_po WHERE $dateCol >= '$fromEsc' AND $dateCol <= '$toEsc' ORDER BY po_id DESC LIMIT $limit OFFSET $offset";
            $countSql = "SELECT COUNT(*) FROM kp_po WHERE $dateCol >= '$fromEsc' AND $dateCol <= '$toEsc'";
        } else {
            $sql = "SELECT * FROM kp_po ORDER BY po_id DESC LIMIT $limit OFFSET $offset";
            $countSql = "SELECT COUNT(*) FROM kp_po";
            $warnings[] = 'purchases returned without date filter (po date column missing).';
        }

        $rows = oc_fetch_all($connection, $sql);
        $data['purchases'] = $rows;
        $pagination['purchases'] = oc_pagination_meta($page, $limit, $withTotals ? oc_count_query($connection, $countSql) : null, count($rows));
        continue;
    }

    if ($ds === 'purchase_items') {
        if (!oc_table_exists($connection, 'kp_po_items')) {
            $warnings[] = 'kp_po_items table not found.';
            $data['purchase_items'] = [];
            $pagination['purchase_items'] = oc_pagination_meta($page, $limit, $withTotals ? 0 : null, 0);
            continue;
        }

        $hasPoTable = oc_table_exists($connection, 'kp_po');
        $hasPoId = oc_column_exists($connection, 'kp_po_items', 'po_id');
        $hasDay = oc_column_exists($connection, 'kp_po_items', 'day');
        $poDateExpr = '';
        $poItemsOrderCol = oc_first_existing_column($connection, 'kp_po_items', ['id', 'po_item_id', 'item_id', 'po_id'], 'po_id');

        if ($hasPoTable && $hasPoId) {
            if (oc_column_exists($connection, 'kp_po', 'po_date')) {
                $poDateExpr = 'p.po_date';
            } elseif (oc_column_exists($connection, 'kp_po', 'shift')) {
                $poDateExpr = 'p.shift';
            }
        }

        if ($poDateExpr !== '') {
            $sql = "SELECT i.*, $poDateExpr AS po_date_ref
                    FROM kp_po_items i
                    INNER JOIN kp_po p ON p.po_id=i.po_id
                    WHERE $poDateExpr >= '$fromEsc' AND $poDateExpr <= '$toEsc'
                    ORDER BY i.$poItemsOrderCol DESC
                    LIMIT $limit OFFSET $offset";
            $countSql = "SELECT COUNT(*)
                         FROM kp_po_items i
                         INNER JOIN kp_po p ON p.po_id=i.po_id
                         WHERE $poDateExpr >= '$fromEsc' AND $poDateExpr <= '$toEsc'";
        } elseif ($hasDay) {
            $sql = "SELECT * FROM kp_po_items WHERE day >= '$fromEsc' AND day <= '$toEsc' ORDER BY $poItemsOrderCol DESC LIMIT $limit OFFSET $offset";
            $countSql = "SELECT COUNT(*) FROM kp_po_items WHERE day >= '$fromEsc' AND day <= '$toEsc'";
        } else {
            $sql = "SELECT * FROM kp_po_items ORDER BY $poItemsOrderCol DESC LIMIT $limit OFFSET $offset";
            $countSql = "SELECT COUNT(*) FROM kp_po_items";
            $warnings[] = 'purchase_items returned without date filter (missing po/day link).';
        }

        $rows = oc_fetch_all($connection, $sql);
        $data['purchase_items'] = $rows;
        $pagination['purchase_items'] = oc_pagination_meta($page, $limit, $withTotals ? oc_count_query($connection, $countSql) : null, count($rows));
        continue;
    }

    if ($ds === 'expenses') {
        if (!oc_table_exists($connection, 'kp_shop_expenses')) {
            $warnings[] = 'kp_shop_expenses table not found.';
            $data['expenses'] = [];
            $pagination['expenses'] = oc_pagination_meta($page, $limit, $withTotals ? 0 : null, 0);
            continue;
        }

        $rows = oc_fetch_all(
            $connection,
            "SELECT * FROM kp_shop_expenses WHERE shift_date >= '$fromEsc' AND shift_date <= '$toEsc' ORDER BY exp_id DESC LIMIT $limit OFFSET $offset"
        );
        $data['expenses'] = $rows;
        $pagination['expenses'] = oc_pagination_meta($page, $limit, $withTotals ? oc_count_query($connection, "SELECT COUNT(*) FROM kp_shop_expenses WHERE shift_date >= '$fromEsc' AND shift_date <= '$toEsc'") : null, count($rows));
        continue;
    }

    if ($ds === 'stock_take') {
        if (!oc_table_exists($connection, 'kp_stock_track')) {
            $warnings[] = 'kp_stock_track table not found.';
            $data['stock_take'] = [];
            $pagination['stock_take'] = oc_pagination_meta($page, $limit, $withTotals ? 0 : null, 0);
            continue;
        }

        $dateExpr = '';
        $stockTrackOrderCol = oc_first_existing_column($connection, 'kp_stock_track', ['id', 'track_id', 'item_id', 'stock_id'], 'item_id');
        if (oc_column_exists($connection, 'kp_stock_track', 'shift')) {
            $dateExpr = 'shift';
        } elseif (oc_column_exists($connection, 'kp_stock_track', 'day')) {
            $dateExpr = 'day';
        } elseif (oc_column_exists($connection, 'kp_stock_track', 'created_at')) {
            $dateExpr = 'DATE(created_at)';
        }

        if ($dateExpr !== '') {
            $sql = "SELECT * FROM kp_stock_track WHERE $dateExpr >= '$fromEsc' AND $dateExpr <= '$toEsc' ORDER BY $stockTrackOrderCol DESC LIMIT $limit OFFSET $offset";
            $countSql = "SELECT COUNT(*) FROM kp_stock_track WHERE $dateExpr >= '$fromEsc' AND $dateExpr <= '$toEsc'";
        } else {
            $sql = "SELECT * FROM kp_stock_track ORDER BY $stockTrackOrderCol DESC LIMIT $limit OFFSET $offset";
            $countSql = "SELECT COUNT(*) FROM kp_stock_track";
            $warnings[] = 'stock_take returned without date filter (date column missing).';
        }

        $rows = oc_fetch_all($connection, $sql);
        $data['stock_take'] = $rows;
        $pagination['stock_take'] = oc_pagination_meta($page, $limit, $withTotals ? oc_count_query($connection, $countSql) : null, count($rows));
        continue;
    }

    if ($ds === 'users') {
        if (!oc_table_exists($connection, 'kp_users')) {
            $warnings[] = 'kp_users table not found.';
            $data['users'] = [];
            $pagination['users'] = oc_pagination_meta($page, $limit, $withTotals ? 0 : null, 0);
            continue;
        }

        $cols = ['user_id', 'username', 'role_id', 'drole', 'work_status', 'job', 'counter', 'counter_status', 'log_status', 'profile_id'];
        $present = [];
        foreach ($cols as $c) {
            if (oc_column_exists($connection, 'kp_users', $c)) {
                $present[] = $c;
            }
        }
        if (!$present) {
            $present = ['*'];
            $warnings[] = 'users exported with full row fallback (column probe failed).';
        }

        $fieldList = implode(',', $present);
        $rows = oc_fetch_all(
            $connection,
            "SELECT $fieldList FROM kp_users ORDER BY user_id DESC LIMIT $limit OFFSET $offset"
        );
        $data['users'] = $rows;
        $pagination['users'] = oc_pagination_meta($page, $limit, $withTotals ? oc_count_query($connection, "SELECT COUNT(*) FROM kp_users") : null, count($rows));
        continue;
    }
}

$overallTotalRecords = 0;
$overallReturned = 0;
foreach ($pagination as $meta) {
    if (isset($meta['total_records'])) {
        $overallTotalRecords += (int)$meta['total_records'];
        $overallReturned += (int)($meta['returned'] ?? 0);
        continue;
    }

    foreach ($meta as $childMeta) {
        if (is_array($childMeta)) {
            $overallTotalRecords += (int)($childMeta['total_records'] ?? 0);
            $overallReturned += (int)($childMeta['returned'] ?? 0);
        }
    }
}

$paginationBlock = [
    'page' => $page,
    'page_size' => $limit,
    'limit' => $limit,
    'offset' => $offset,
    'totals_included' => $withTotals,
    'returned' => $overallReturned,
    'total_records' => $withTotals ? $overallTotalRecords : null,
    'total_pages' => $withTotals ? ($overallTotalRecords > 0 ? (int)ceil($overallTotalRecords / $limit) : 0) : null,
    'has_previous_page' => $page > 1,
    'has_next_page' => $withTotals ? ($overallTotalRecords > ($offset + $overallReturned)) : ($overallReturned >= $limit),
    'previous_page' => $page > 1 ? ($page - 1) : null,
    'next_page' => ($withTotals ? ($overallTotalRecords > ($offset + $overallReturned)) : ($overallReturned >= $limit)) ? ($page + 1) : null,
    'datasets' => $pagination,
];

$response = [
    'ok' => true,
    'auth_mode' => $authMode,
    'range' => [
        'from' => $from,
        'to' => $to,
    ],
    'page' => $page,
    'limit' => $limit,
    'pagination' => $paginationBlock,
    'requested_datasets' => $requested,
    'filters' => [
        'customer_id' => $customerId,
    ],
    'warnings' => $warnings,
    'data' => $data,
];

if ($pushUrl !== '') {
    if (!filter_var($pushUrl, FILTER_VALIDATE_URL)) {
        $response['ok'] = false;
        $response['error'] = 'invalid_push_url';
        $response['delivery'] = [
            'enabled' => true,
            'success' => false,
            'http_code' => 0,
            'message' => 'push_url must be a valid absolute URL.',
        ];
    } else {
        $deliveryPayload = [
            'source' => 'ktouch_gpints',
            'generated_at' => date('c'),
            'range' => $response['range'],
            'requested_datasets' => $requested,
            'filters' => $response['filters'],
            'data' => $data,
        ];

        $result = oc_post_json($pushUrl, $deliveryPayload, $pushTimeout, $pushToken);
        $response['delivery'] = [
            'enabled' => true,
            'success' => (bool)$result['ok'],
            'http_code' => (int)$result['http_code'],
            'error' => (string)$result['error'],
            'response_sample' => substr((string)$result['response'], 0, 500),
        ];
        if (!$result['ok']) {
            $response['ok'] = false;
            $response['error'] = 'push_failed';
        }
    }
}

if ($responseMode === 'compact') {
    $compactResponse = [
        'ok' => (bool)$response['ok'],
        'mode' => 'compact',
        'dataset' => count($requested) === 1 ? $requested[0] : null,
        'datasets' => $requested,
        'page' => $page,
        'limit' => $limit,
        'rows' => count($requested) === 1 ? ($data[$requested[0]] ?? []) : $data,
    ];

    if (!empty($warnings)) {
        $compactResponse['warnings'] = $warnings;
    }

    if ($withTotals || $page > 1) {
        $compactResponse['pagination'] = $paginationBlock;
    }

    if (isset($response['error'])) {
        $compactResponse['error'] = $response['error'];
    }

    if (isset($response['delivery'])) {
        $compactResponse['delivery'] = $response['delivery'];
    }

    echo json_encode($compactResponse, JSON_UNESCAPED_UNICODE);
    exit;
}

if ($responseMode === 'analysis') {
    $analysisCurrency = trim((string)oc_request_value('currency', 'KES'));
    $analysisCode = trim((string)oc_request_value('business_code', 'KLB-001'));
    $analysisShortcode = trim((string)oc_request_value('business_shortcode', '123'));
    $analysisShiftLabel = trim((string)oc_request_value('shift_label', 'Day'));
    $analysisVatPct = (float)oc_request_value('vat_pct', 16);

    $analysisResponse = oc_build_analysis_response(
        $connection,
        isset($data['sales']) && is_array($data['sales']) ? $data['sales'] : [],
        $from,
        $to,
        $analysisCurrency,
        $analysisCode,
        $analysisShortcode,
        $analysisShiftLabel,
        $analysisVatPct
    );

    echo json_encode($analysisResponse, JSON_UNESCAPED_UNICODE);
    exit;
}

echo json_encode($response, JSON_UNESCAPED_UNICODE);
