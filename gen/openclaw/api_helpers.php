<?php

function oc_table_exists(mysqli $connection, string $table): bool
{
    static $cache = [];
    $cacheKey = spl_object_hash($connection) . ':' . $table;
    if (array_key_exists($cacheKey, $cache)) {
        return $cache[$cacheKey];
    }

    $safe = mysqli_real_escape_string($connection, $table);
    $r = mysqli_query($connection, "SHOW TABLES LIKE '$safe'");
    $cache[$cacheKey] = ($r && mysqli_num_rows($r) > 0);
    return $cache[$cacheKey];
}

function oc_column_exists(mysqli $connection, string $table, string $column): bool
{
    static $cache = [];
    $cacheKey = spl_object_hash($connection) . ':' . $table . ':' . $column;
    if (array_key_exists($cacheKey, $cache)) {
        return $cache[$cacheKey];
    }

    $t = mysqli_real_escape_string($connection, $table);
    $c = mysqli_real_escape_string($connection, $column);
    $r = mysqli_query($connection, "SHOW COLUMNS FROM `$t` LIKE '$c'");
    $cache[$cacheKey] = ($r && mysqli_num_rows($r) > 0);
    return $cache[$cacheKey];
}

function oc_first_existing_column(mysqli $connection, string $table, array $columns, string $default = ''): string
{
    foreach ($columns as $column) {
        if (oc_column_exists($connection, $table, $column)) {
            return $column;
        }
    }

    return $default;
}

function oc_sql_in_ints(array $ids): string
{
    $clean = [];
    foreach ($ids as $id) {
        $intId = (int)$id;
        if ($intId > 0) {
            $clean[$intId] = $intId;
        }
    }

    return $clean ? implode(',', $clean) : '0';
}

function oc_unique_int_ids(array $rows, array $fields): array
{
    $ids = [];
    foreach ($rows as $row) {
        if (!is_array($row)) {
            continue;
        }

        foreach ($fields as $field) {
            if (!isset($row[$field])) {
                continue;
            }

            $id = (int)$row[$field];
            if ($id > 0) {
                $ids[$id] = $id;
            }
        }
    }

    return array_values($ids);
}

function oc_index_by(array $rows, string $field): array
{
    $indexed = [];
    foreach ($rows as $row) {
        if (is_array($row) && isset($row[$field])) {
            $indexed[(int)$row[$field]] = $row;
        }
    }
    return $indexed;
}

function oc_group_by(array $rows, string $field): array
{
    $grouped = [];
    foreach ($rows as $row) {
        if (!is_array($row) || !isset($row[$field])) {
            continue;
        }

        $grouped[(int)$row[$field]][] = $row;
    }

    return $grouped;
}

function oc_fetch_customers_map(mysqli $connection, array $customerIds): array
{
    if (!$customerIds || !oc_table_exists($connection, 'kp_customers')) {
        return [];
    }

    $in = oc_sql_in_ints($customerIds);
    $rows = oc_fetch_all($connection, "SELECT * FROM kp_customers WHERE cust_id IN ($in)");
    return oc_index_by($rows, 'cust_id');
}

function oc_customer_ref(?array $customer): ?array
{
    if (!is_array($customer)) {
        return null;
    }

    return [
        'name' => (string)($customer['name'] ?? ''),
        'phone' => (string)($customer['phone'] ?? ''),
        'balance' => $customer['bal'] ?? null,
    ];
}

function oc_fetch_users_map(mysqli $connection, array $userIds): array
{
    if (!$userIds || !oc_table_exists($connection, 'kp_users')) {
        return [];
    }

    $cols = ['user_id', 'username', 'role_id', 'drole', 'work_status', 'job', 'counter', 'counter_status', 'log_status', 'profile_id'];
    $present = [];
    foreach ($cols as $column) {
        if (oc_column_exists($connection, 'kp_users', $column)) {
            $present[] = $column;
        }
    }

    if (!$present) {
        $present = ['*'];
    }

    $in = oc_sql_in_ints($userIds);
    $fieldList = implode(',', $present);
    $rows = oc_fetch_all($connection, "SELECT $fieldList FROM kp_users WHERE user_id IN ($in)");
    return oc_index_by($rows, 'user_id');
}

function oc_user_ref(?array $user): ?array
{
    if (!is_array($user)) {
        return null;
    }

    return [
        'name' => (string)($user['username'] ?? ''),
        'role' => $user['drole'] ?? ($user['role_id'] ?? null),
        'counter' => $user['counter'] ?? null,
    ];
}

function oc_fetch_items_map(mysqli $connection, array $itemIds): array
{
    if (!$itemIds || !oc_table_exists($connection, 'kp_items')) {
        return [];
    }

    $catJoin = oc_table_exists($connection, 'kp_cat') ? 'LEFT JOIN kp_cat c ON c.cat_id=i.cat_id' : '';
    $subJoin = oc_table_exists($connection, 'kp_sub_cat') ? 'LEFT JOIN kp_sub_cat sc ON sc.sub_id=i.sub_id' : '';
    $catSelect = oc_table_exists($connection, 'kp_cat') ? ', c.name AS cat_name' : '';
    $subSelect = oc_table_exists($connection, 'kp_sub_cat') ? ', sc.name AS subcat_name' : '';
    $in = oc_sql_in_ints($itemIds);
    $rows = oc_fetch_all($connection, "SELECT i.*$catSelect$subSelect FROM kp_items i $catJoin $subJoin WHERE i.item_id IN ($in)");
    return oc_index_by($rows, 'item_id');
}

function oc_item_ref(?array $item): ?array
{
    if (!is_array($item)) {
        return null;
    }

    return [
        'name' => (string)($item['name'] ?? ''),
        'category' => (string)($item['cat_name'] ?? ''),
        'subcategory' => (string)($item['subcat_name'] ?? ''),
        'price' => $item['price'] ?? ($item['selling_price'] ?? null),
    ];
}

function oc_fetch_sale_payments_for_sales(mysqli $connection, array $saleIds): array
{
    if (!$saleIds || !oc_table_exists($connection, 'kp_sale_payment') || !oc_column_exists($connection, 'kp_sale_payment', 'sale_id')) {
        return [];
    }

    $in = oc_sql_in_ints($saleIds);
    $orderCol = oc_first_existing_column($connection, 'kp_sale_payment', ['pay_id', 'sale_id'], 'sale_id');
    return oc_fetch_all($connection, "SELECT * FROM kp_sale_payment WHERE sale_id IN ($in) ORDER BY $orderCol DESC");
}

function oc_fetch_sale_items_for_sales(mysqli $connection, array $saleIds): array
{
    if (!$saleIds || !oc_table_exists($connection, 'kp_sale_items') || !oc_column_exists($connection, 'kp_sale_items', 'sale_id')) {
        return [];
    }

    $in = oc_sql_in_ints($saleIds);
    $orderCol = oc_first_existing_column($connection, 'kp_sale_items', ['id', 'sale_item_id', 'item_id', 'sale_id'], 'sale_id');
    return oc_fetch_all($connection, "SELECT * FROM kp_sale_items WHERE sale_id IN ($in) ORDER BY $orderCol DESC");
}

function oc_enrich_sale_items(mysqli $connection, array $saleItems, array $salesMap = []): array
{
    if (!$saleItems) {
        return [];
    }

    $itemMap = oc_fetch_items_map($connection, oc_unique_int_ids($saleItems, ['item_id']));
    $userMap = oc_fetch_users_map($connection, oc_unique_int_ids($saleItems, ['user_id']));

    foreach ($saleItems as &$row) {
        if (!is_array($row)) {
            continue;
        }

        $item = isset($row['item_id'], $itemMap[(int)$row['item_id']]) ? $itemMap[(int)$row['item_id']] : null;
        $user = isset($row['user_id'], $userMap[(int)$row['user_id']]) ? $userMap[(int)$row['user_id']] : null;
        $row['item_name'] = is_array($item) ? (string)($item['name'] ?? '') : '';
        $row['username'] = is_array($user) ? (string)($user['username'] ?? '') : '';
        $row['item_category'] = is_array($item) ? (string)($item['cat_name'] ?? '') : '';
        $row['item_subcategory'] = is_array($item) ? (string)($item['subcat_name'] ?? '') : '';
        $row['item_buy'] = is_array($item) ? (float)($item['b_price'] ?? 0) : 0.0;
        $row['item_sell'] = is_array($item) ? (float)($item['price'] ?? 0) : 0.0;
    }
    unset($row);

    return $saleItems;
}

function oc_enrich_sale_payments(mysqli $connection, array $payments, array $salesMap = []): array
{
    if (!$payments) {
        return [];
    }

    $customerMap = oc_fetch_customers_map($connection, oc_unique_int_ids($payments, ['cust_id']));
    $userMap = oc_fetch_users_map($connection, oc_unique_int_ids($payments, ['user_id']));

    foreach ($payments as &$row) {
        if (!is_array($row)) {
            continue;
        }

        $customer = isset($row['cust_id'], $customerMap[(int)$row['cust_id']]) ? $customerMap[(int)$row['cust_id']] : null;
        $user = isset($row['user_id'], $userMap[(int)$row['user_id']]) ? $userMap[(int)$row['user_id']] : null;
        $row['customer_name'] = is_array($customer) ? (string)($customer['name'] ?? '') : '';
        $row['username'] = is_array($user) ? (string)($user['username'] ?? '') : '';
        if (isset($row['sale_id'], $salesMap[(int)$row['sale_id']])) {
            $row['sale'] = $salesMap[(int)$row['sale_id']];
        }
    }
    unset($row);

    return $payments;
}

function oc_enrich_sales(mysqli $connection, array $sales): array
{
    if (!$sales) {
        return [];
    }

    $customerMap = oc_fetch_customers_map($connection, oc_unique_int_ids($sales, ['cust_id']));
    $userMap = oc_fetch_users_map($connection, oc_unique_int_ids($sales, ['user_id']));
    $saleIds = oc_unique_int_ids($sales, ['sale_id']);

    $payments = oc_fetch_sale_payments_for_sales($connection, $saleIds);
    $items = oc_fetch_sale_items_for_sales($connection, $saleIds);
    $salesBaseMap = oc_index_by($sales, 'sale_id');
    $payments = oc_enrich_sale_payments($connection, $payments, $salesBaseMap);
    $items = oc_enrich_sale_items($connection, $items, $salesBaseMap);
    $paymentsBySale = oc_group_by($payments, 'sale_id');
    $itemsBySale = oc_group_by($items, 'sale_id');

    foreach ($sales as &$row) {
        if (!is_array($row)) {
            continue;
        }

        $saleId = isset($row['sale_id']) ? (int)$row['sale_id'] : 0;
        $customer = isset($row['cust_id'], $customerMap[(int)$row['cust_id']]) ? $customerMap[(int)$row['cust_id']] : null;
        $user = isset($row['user_id'], $userMap[(int)$row['user_id']]) ? $userMap[(int)$row['user_id']] : null;
        $row['customer_name'] = is_array($customer) ? (string)($customer['name'] ?? '') : '';
        $row['username'] = is_array($user) ? (string)($user['username'] ?? '') : '';
        $row['payments'] = $paymentsBySale[$saleId] ?? [];
        $row['items'] = $itemsBySale[$saleId] ?? [];
    }
    unset($row);

    return $sales;
}

function oc_enrich_credit_payload(mysqli $connection, array $payload): array
{
    $creditSales = isset($payload['credit_sales']) && is_array($payload['credit_sales']) ? oc_enrich_sales($connection, $payload['credit_sales']) : [];
    $salesMap = oc_index_by($creditSales, 'sale_id');

    $creditPayments = isset($payload['credit_payments']) && is_array($payload['credit_payments']) ? $payload['credit_payments'] : [];
    $creditPaymentItems = isset($payload['credit_payment_items']) && is_array($payload['credit_payment_items']) ? $payload['credit_payment_items'] : [];

    $customerMap = oc_fetch_customers_map($connection, array_merge(
        oc_unique_int_ids($creditPayments, ['cust_id']),
        oc_unique_int_ids($creditPaymentItems, ['cust_id']),
        oc_unique_int_ids($creditSales, ['cust_id'])
    ));

    $userMap = oc_fetch_users_map($connection, array_merge(
        oc_unique_int_ids($creditPayments, ['user_id']),
        oc_unique_int_ids($creditPaymentItems, ['user_id']),
        oc_unique_int_ids($creditSales, ['user_id'])
    ));

    $paymentMap = oc_index_by($creditPayments, 'credit_pay_id');

    foreach ($creditPayments as &$row) {
        if (!is_array($row)) {
            continue;
        }

        $customer = isset($row['cust_id'], $customerMap[(int)$row['cust_id']]) ? $customerMap[(int)$row['cust_id']] : null;
        $user = isset($row['user_id'], $userMap[(int)$row['user_id']]) ? $userMap[(int)$row['user_id']] : null;
        $row['customer_name'] = is_array($customer) ? (string)($customer['name'] ?? '') : '';
        $row['username'] = is_array($user) ? (string)($user['username'] ?? '') : '';
        if (isset($row['sale_id'], $salesMap[(int)$row['sale_id']])) {
            $row['sale'] = $salesMap[(int)$row['sale_id']];
        }
    }
    unset($row);

    foreach ($creditPaymentItems as &$row) {
        if (!is_array($row)) {
            continue;
        }

        $creditPayId = isset($row['credit_pay_id']) ? (int)$row['credit_pay_id'] : 0;
        $relatedPayment = $paymentMap[$creditPayId] ?? null;
        $userId = isset($row['user_id']) ? (int)$row['user_id'] : (is_array($relatedPayment) && isset($relatedPayment['user_id']) ? (int)$relatedPayment['user_id'] : 0);
        $customer = isset($row['cust_id'], $customerMap[(int)$row['cust_id']]) ? $customerMap[(int)$row['cust_id']] : null;
        $user = ($userId > 0 && isset($userMap[$userId])) ? $userMap[$userId] : null;

        $row['customer_name'] = is_array($customer) ? (string)($customer['name'] ?? '') : '';
        $row['username'] = is_array($user) ? (string)($user['username'] ?? '') : '';
        $row['credit_payment'] = $relatedPayment;
        if (isset($row['sale_id'], $salesMap[(int)$row['sale_id']])) {
            $row['sale'] = $salesMap[(int)$row['sale_id']];
        }
    }
    unset($row);

    return [
        'credit_sales' => $creditSales,
        'credit_payments' => $creditPayments,
        'credit_payment_items' => $creditPaymentItems,
    ];
}

function oc_get_header(string $name): string
{
    $serverKey = 'HTTP_' . strtoupper(str_replace('-', '_', $name));
    return trim((string)($_SERVER[$serverKey] ?? ''));
}

function oc_json_payload(): array
{
    static $payload = null;
    if (is_array($payload)) {
        return $payload;
    }

    $payload = [];
    $method = strtoupper((string)($_SERVER['REQUEST_METHOD'] ?? 'GET'));
    if ($method !== 'POST') {
        return $payload;
    }

    $contentType = strtolower(trim((string)($_SERVER['CONTENT_TYPE'] ?? $_SERVER['HTTP_CONTENT_TYPE'] ?? '')));
    if (strpos($contentType, 'application/json') === false) {
        return $payload;
    }

    $raw = file_get_contents('php://input');
    if (!is_string($raw) || trim($raw) === '') {
        return $payload;
    }

    $decoded = json_decode($raw, true);
    if (is_array($decoded)) {
        $payload = $decoded;
    }

    return $payload;
}

function oc_request_value(string $key, $default = '')
{
    if (array_key_exists($key, $_GET)) {
        return $_GET[$key];
    }
    if (array_key_exists($key, $_POST)) {
        return $_POST[$key];
    }

    $json = oc_json_payload();
    if (array_key_exists($key, $json)) {
        return $json[$key];
    }

    return $default;
}

function oc_configured_api_key(): string
{
    return trim(openclaw_api_key());
}

function oc_provided_api_key(): string
{
    $x = oc_get_header('X-API-Key');
    if ($x !== '') {
        return $x;
    }

    $auth = oc_get_header('Authorization');
    if ($auth !== '' && preg_match('/^Bearer\s+(.+)$/i', $auth, $m)) {
        return trim((string)$m[1]);
    }

    return trim((string)oc_request_value('api_key', ''));
}

function oc_valid_date(string $s): bool
{
    return (bool)preg_match('/^\d{4}-\d{2}-\d{2}$/', $s);
}

function oc_fetch_all(mysqli $connection, string $sql): array
{
    $rows = [];
    $r = mysqli_query($connection, $sql);
    if (!$r) {
        return $rows;
    }
    while ($row = mysqli_fetch_assoc($r)) {
        $rows[] = $row;
    }
    return $rows;
}

function oc_count_query(mysqli $connection, string $sql): int
{
    $r = mysqli_query($connection, $sql);
    if (!$r) {
        return 0;
    }

    $row = mysqli_fetch_row($r);
    if (!is_array($row) || !isset($row[0])) {
        return 0;
    }

    return (int)$row[0];
}

function oc_request_bool(string $key, bool $default = false): bool
{
    $value = oc_request_value($key, $default ? '1' : '0');
    if (is_bool($value)) {
        return $value;
    }

    $normalized = strtolower(trim((string)$value));
    return in_array($normalized, ['1', 'true', 'yes', 'on'], true);
}

function oc_pagination_meta(int $page, int $limit, ?int $totalRecords, int $returnedCount): array
{
    $totalPages = ($totalRecords !== null && $totalRecords > 0) ? (int)ceil($totalRecords / $limit) : null;
    $hasPreviousPage = $page > 1;
    $hasNextPage = $totalPages !== null ? ($totalPages > 0 && $page < $totalPages) : ($returnedCount >= $limit);

    return [
        'page' => $page,
        'page_size' => $limit,
        'limit' => $limit,
        'offset' => ($page - 1) * $limit,
        'returned' => $returnedCount,
        'totals_included' => $totalRecords !== null,
        'total_records' => $totalRecords,
        'total_pages' => $totalPages,
        'has_previous_page' => $hasPreviousPage,
        'has_next_page' => $hasNextPage,
        'previous_page' => $hasPreviousPage ? ($page - 1) : null,
        'next_page' => $hasNextPage ? ($page + 1) : null,
    ];
}

function oc_sales_date_filter(string $fromEsc, string $toEsc): string
{
    return "s.shift >= '$fromEsc' AND s.shift <= '$toEsc'";
}

function oc_post_json(string $url, array $payload, int $timeoutSeconds, string $token = ''): array
{
    $body = json_encode($payload, JSON_UNESCAPED_UNICODE);
    if (!is_string($body)) {
        return [
            'ok' => false,
            'http_code' => 0,
            'error' => 'encode_failed',
            'response' => '',
        ];
    }

    $timeoutSeconds = max(5, min(120, $timeoutSeconds));
    $headers = [
        'Content-Type: application/json',
        'Accept: application/json',
    ];
    if ($token !== '') {
        $headers[] = 'X-Workflow-Token: ' . $token;
    }

    if (function_exists('curl_init')) {
        $ch = curl_init($url);
        if ($ch === false) {
            return [
                'ok' => false,
                'http_code' => 0,
                'error' => 'curl_init_failed',
                'response' => '',
            ];
        }
        curl_setopt_array($ch, [
            CURLOPT_RETURNTRANSFER => true,
            CURLOPT_POST => true,
            CURLOPT_HTTPHEADER => $headers,
            CURLOPT_POSTFIELDS => $body,
            CURLOPT_TIMEOUT => $timeoutSeconds,
            CURLOPT_CONNECTTIMEOUT => min(10, $timeoutSeconds),
        ]);
        $resp = curl_exec($ch);
        $http = (int)curl_getinfo($ch, CURLINFO_HTTP_CODE);
        $err = curl_error($ch);
        curl_close($ch);

        return [
            'ok' => ($err === '' && $http >= 200 && $http < 300),
            'http_code' => $http,
            'error' => $err,
            'response' => is_string($resp) ? $resp : '',
        ];
    }

    $hdr = implode("\r\n", $headers);
    $context = stream_context_create([
        'http' => [
            'method' => 'POST',
            'header' => $hdr . "\r\n",
            'content' => $body,
            'timeout' => $timeoutSeconds,
            'ignore_errors' => true,
        ],
    ]);
    $resp = @file_get_contents($url, false, $context);
    $http = 0;
    if (isset($http_response_header) && is_array($http_response_header) && isset($http_response_header[0])) {
        if (preg_match('/\s(\d{3})\s/', (string)$http_response_header[0], $m)) {
            $http = (int)$m[1];
        }
    }

    return [
        'ok' => ($http >= 200 && $http < 300),
        'http_code' => $http,
        'error' => '',
        'response' => is_string($resp) ? $resp : '',
    ];
}

function oc_num($value): float
{
    if (is_int($value) || is_float($value)) {
        return (float)$value;
    }

    if (is_string($value)) {
        $v = trim($value);
        if ($v === '') {
            return 0.0;
        }
        return (float)$v;
    }

    return 0.0;
}

function oc_round_money(float $value)
{
    $rounded = round($value, 2);
    if (abs($rounded - round($rounded)) < 0.00001) {
        return (int)round($rounded);
    }

    return $rounded;
}

function oc_qty_value(float $value)
{
    if (abs($value - round($value)) < 0.00001) {
        return (int)round($value);
    }

    return round($value, 3);
}

function oc_fetch_settings_row(mysqli $connection): array
{
    if (!oc_table_exists($connection, 'kp_settings_gen')) {
        return [];
    }

    $rows = oc_fetch_all(
        $connection,
        "SELECT company_name, company_tag, vat FROM kp_settings_gen ORDER BY id DESC LIMIT 1"
    );

    return isset($rows[0]) && is_array($rows[0]) ? $rows[0] : [];
}

function oc_vat_percent(mysqli $connection, float $fallback = 16.0): float
{
    $settings = oc_fetch_settings_row($connection);
    $vat = isset($settings['vat']) ? oc_num($settings['vat']) : 0.0;
    if ($vat > 0) {
        return $vat;
    }

    return $fallback > 0 ? $fallback : 16.0;
}

function oc_payment_slot_names(mysqli $connection): array
{
    if (!oc_table_exists($connection, 'kp_pay_mode')) {
        return [1 => 'cash', 2 => 'mpesa', 3 => 'card', 4 => 'bank'];
    }

    $sql = oc_column_exists($connection, 'kp_pay_mode', 'del_status')
        ? "SELECT name FROM kp_pay_mode WHERE del_status=2 ORDER BY pay_id ASC"
        : "SELECT name FROM kp_pay_mode ORDER BY pay_id ASC";
    $rows = oc_fetch_all($connection, $sql);

    $out = [];
    $slot = 1;
    foreach ($rows as $row) {
        $name = strtolower(trim((string)($row['name'] ?? '')));
        if ($name === '') {
            continue;
        }
        $out[$slot] = $name;
        $slot++;
        if ($slot > 4) {
            break;
        }
    }

    if (!isset($out[1])) {
        $out[1] = 'cash';
    }
    if (!isset($out[2])) {
        $out[2] = 'mpesa';
    }

    return $out;
}

function oc_sale_methods(array $payments, array $slotNames): array
{
    $methods = [];
    foreach ($payments as $payment) {
        if (!is_array($payment)) {
            continue;
        }

        for ($i = 1; $i <= 4; $i++) {
            $amount = oc_num($payment['mode' . $i] ?? 0);
            if ($amount <= 0) {
                continue;
            }

            $method = [
                'amount' => oc_round_money($amount),
                'via' => $slotNames[$i] ?? ('mode' . $i),
            ];

            $ref = trim((string)($payment['moderef' . $i] ?? ''));
            if ($ref !== '') {
                $method['ref'] = $ref;
            }

            $methods[] = $method;
        }
    }

    return $methods;
}

function oc_build_analysis_response(
    mysqli $connection,
    array $sales,
    string $from,
    string $to,
    string $currency = 'KES',
    string $businessCode = 'KLB-001',
    string $businessShortcode = '123',
    string $shiftLabel = 'Day',
    float $vatFallback = 16.0
): array {
    $settings = oc_fetch_settings_row($connection);
    $vatPct = oc_vat_percent($connection, $vatFallback);
    $slotNames = oc_payment_slot_names($connection);

    $businessName = trim((string)($settings['company_name'] ?? ''));
    if ($businessName === '') {
        $businessName = 'Business';
    }

    $tag = trim((string)($settings['company_tag'] ?? ''));
    $business = [
        'code' => $businessCode !== '' ? $businessCode : ($tag !== '' ? $tag : 'KLB-001'),
        'currency' => $currency !== '' ? strtoupper($currency) : 'KES',
        'name' => $businessName,
        'shortcode' => $businessShortcode !== '' ? $businessShortcode : ($tag !== '' ? $tag : '123'),
    ];

    $salesOut = [];
    $cashierAgg = [];
    $categoryAgg = [];
    $paymentAgg = [];
    $statusAgg = [
        'paid' => ['count' => 0, 'amount' => 0.0],
        'partial' => ['count' => 0, 'amount' => 0.0],
        'unpaid' => ['count' => 0, 'amount' => 0.0],
    ];

    $shiftCashiers = [];
    $openTime = null;
    $closeTime = null;

    $sumCogs = 0.0;
    $sumVat = 0.0;
    $sumRevenue = 0.0;
    $sumCollected = 0.0;
    $sumProfit = 0.0;

    foreach ($sales as $sale) {
        if (!is_array($sale)) {
            continue;
        }

        $cashier = trim((string)($sale['username'] ?? 'Unknown'));
        if ($cashier === '') {
            $cashier = 'Unknown';
        }
        $shiftCashiers[$cashier] = $cashier;

        $time = '';
        $rawTime = trim((string)($sale['datime'] ?? ''));
        if ($rawTime !== '') {
            $ts = strtotime($rawTime);
            if ($ts !== false) {
                $time = date('H:i', $ts);
                if ($openTime === null || strcmp($time, $openTime) < 0) {
                    $openTime = $time;
                }
                if ($closeTime === null || strcmp($time, $closeTime) > 0) {
                    $closeTime = $time;
                }
            }
        }

        $items = isset($sale['items']) && is_array($sale['items']) ? $sale['items'] : [];
        $itemsOut = [];
        $totalBuy = 0.0;
        $totalSell = 0.0;

        foreach ($items as $item) {
            if (!is_array($item)) {
                continue;
            }

            $qty = oc_num($item['qty'] ?? 0);
            $sell = oc_num($item['price'] ?? 0);
            $buy = oc_num($item['item_buy'] ?? 0);
            if ($buy <= 0) {
                $buy = oc_num($item['b_price'] ?? 0);
            }

            $lineSell = $sell * $qty;
            $lineBuy = $buy * $qty;
            $totalSell += $lineSell;
            $totalBuy += $lineBuy;

            $cat = trim((string)($item['item_category'] ?? ''));
            if ($cat === '') {
                $cat = 'UNCATEGORIZED';
            }

            $itemsOut[] = [
                'buy' => oc_round_money($buy),
                'cat' => $cat,
                'name' => (string)($item['item_name'] ?? ''),
                'qty' => oc_qty_value($qty),
                'sell' => oc_round_money($sell),
                'vat_pct' => oc_round_money($vatPct),
            ];

            if (!isset($categoryAgg[$cat])) {
                $categoryAgg[$cat] = ['cat' => $cat, 'qty' => 0.0, 'revenue' => 0.0, 'profit' => 0.0];
            }
            $categoryAgg[$cat]['qty'] += $qty;
            $categoryAgg[$cat]['revenue'] += $lineSell;
            $categoryAgg[$cat]['profit'] += ($lineSell - $lineBuy);
        }

        $totalVat = ($totalSell * $vatPct) / 100.0;
        $totalDue = $totalSell + $totalVat;
        $totalProfit = $totalSell - $totalBuy;

        $methods = oc_sale_methods(isset($sale['payments']) && is_array($sale['payments']) ? $sale['payments'] : [], $slotNames);
        $paid = 0.0;
        foreach ($methods as $method) {
            $paid += oc_num($method['amount'] ?? 0);
            $via = (string)($method['via'] ?? 'unknown');
            if (!isset($paymentAgg[$via])) {
                $paymentAgg[$via] = ['via' => $via, 'amount' => 0.0];
            }
            $paymentAgg[$via]['amount'] += oc_num($method['amount'] ?? 0);
        }

        if ($paid <= 0) {
            $paid = oc_num($sale['paid_amount'] ?? 0);
        }

        $status = 'unpaid';
        if ($paid > 0 && $paid + 0.01 >= $totalDue) {
            $status = 'paid';
        } elseif ($paid > 0 && $paid + 0.01 < $totalDue) {
            $status = 'partial';
        }

        $saleOut = [
            'cashier' => $cashier,
            'id' => (string)($sale['sale_id'] ?? ''),
            'items' => $itemsOut,
            'paid' => oc_round_money($paid),
            'status' => $status,
            'time' => $time,
            'totals' => [
                'buy' => oc_round_money($totalBuy),
                'due' => oc_round_money($totalDue),
                'profit' => oc_round_money($totalProfit),
                'sell' => oc_round_money($totalSell),
                'vat' => oc_round_money($totalVat),
            ],
        ];

        $payDate = trim((string)($sale['pay_date'] ?? ''));
        if (($status === 'unpaid' || $status === 'partial') && $payDate !== '' && $payDate !== '0000-00-00') {
            $saleOut['due_date'] = $payDate;
        }

        if (!empty($methods)) {
            $saleOut['methods'] = $methods;
        }

        $salesOut[] = $saleOut;

        if (!isset($cashierAgg[$cashier])) {
            $cashierAgg[$cashier] = [
                'name' => $cashier,
                'sales' => 0,
                'revenue' => 0.0,
                'collected' => 0.0,
                'outstanding' => 0.0,
                'profit' => 0.0,
            ];
        }

        $cashierAgg[$cashier]['sales'] += 1;
        $cashierAgg[$cashier]['revenue'] += $totalDue;
        $cashierAgg[$cashier]['collected'] += $paid;
        $cashierAgg[$cashier]['outstanding'] += max($totalDue - $paid, 0.0);
        $cashierAgg[$cashier]['profit'] += $totalProfit;

        $statusAgg[$status]['count'] += 1;
        $statusAgg[$status]['amount'] += $totalDue;

        $sumCogs += $totalBuy;
        $sumVat += $totalVat;
        $sumRevenue += $totalDue;
        $sumCollected += $paid;
        $sumProfit += $totalProfit;
    }

    usort($salesOut, static function ($a, $b): int {
        return strcmp((string)($a['id'] ?? ''), (string)($b['id'] ?? ''));
    });

    $byCashier = array_values($cashierAgg);
    foreach ($byCashier as &$row) {
        $row['collected'] = oc_round_money((float)$row['collected']);
        $row['outstanding'] = oc_round_money((float)$row['outstanding']);
        $row['profit'] = oc_round_money((float)$row['profit']);
        $row['revenue'] = oc_round_money((float)$row['revenue']);
    }
    unset($row);

    $byCategory = array_values($categoryAgg);
    foreach ($byCategory as &$row) {
        $row['qty'] = oc_qty_value((float)$row['qty']);
        $row['revenue'] = oc_round_money((float)$row['revenue']);
        $row['profit'] = oc_round_money((float)$row['profit']);
    }
    unset($row);

    $byPayment = array_values($paymentAgg);
    foreach ($byPayment as &$row) {
        $row['amount'] = oc_round_money((float)$row['amount']);
    }
    unset($row);

    foreach ($statusAgg as &$row) {
        $row['amount'] = oc_round_money((float)$row['amount']);
    }
    unset($row);

    $shiftDate = ($from === $to) ? $from : ($from . ' to ' . $to);

    return [
        'business' => $business,
        'sales' => $salesOut,
        'shift' => [
            'cashiers' => array_values($shiftCashiers),
            'closed' => $closeTime ?? '',
            'date' => $shiftDate,
            'label' => $shiftLabel !== '' ? $shiftLabel : 'Day',
            'opened' => $openTime ?? '',
        ],
        'summary' => [
            'by_cashier' => $byCashier,
            'by_category' => $byCategory,
            'by_payment' => $byPayment,
            'by_status' => $statusAgg,
            'cogs' => oc_round_money($sumCogs),
            'collected' => oc_round_money($sumCollected),
            'discounts' => 0,
            'outstanding' => oc_round_money(max($sumRevenue - $sumCollected, 0.0)),
            'profit' => oc_round_money($sumProfit),
            'revenue' => oc_round_money($sumRevenue),
            'sales_count' => count($salesOut),
            'vat' => oc_round_money($sumVat),
            'voids' => 0,
        ],
    ];
}
