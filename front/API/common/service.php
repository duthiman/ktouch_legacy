<?php

if (!function_exists('common_service_filters_from_request')) {
    function common_service_filters_from_request($request = null) {
        $source = is_array($request) ? $request : $_REQUEST;

        return [
            'sale_id' => (int)($source['sale_id'] ?? 0),
            'user_id' => (int)($source['user_id'] ?? 0),
            'counter_id' => (int)($source['counter_id'] ?? 0),
            'supplier_id' => (int)($source['supplier_id'] ?? 0),
            'po_id' => (int)($source['po_id'] ?? 0),
            'category_id' => (int)($source['category_id'] ?? 0),
            'subcategory_id' => (int)($source['subcategory_id'] ?? 0),
            'dpt' => (int)($source['dpt'] ?? 0),
            'stock' => isset($source['stock']) ? (string)$source['stock'] : '',
            'menu' => isset($source['menu']) ? (string)$source['menu'] : '',
            'shift' => common_clean_text($source['shift'] ?? ''),
            'date_from' => common_clean_text($source['date_from'] ?? ''),
            'date_to' => common_clean_text($source['date_to'] ?? ''),
            'datetime_from' => common_clean_text($source['datetime_from'] ?? ''),
            'datetime_to' => common_clean_text($source['datetime_to'] ?? ''),
            'time_from' => common_clean_text($source['time_from'] ?? ''),
            'time_to' => common_clean_text($source['time_to'] ?? ''),
        ];
    }
}

if (!function_exists('common_service_fetch_sale_details')) {
    function common_service_fetch_sale_details($filters = []) {
        return [
            'ok' => true,
            'message' => 'Sale details loaded.',
            'rows' => common_repo_fetch_sales($filters),
            'filters' => $filters,
        ];
    }
}

if (!function_exists('common_service_fetch_sale_items')) {
    function common_service_fetch_sale_items($filters = []) {
        return [
            'ok' => true,
            'message' => 'Sale items loaded.',
            'rows' => common_repo_fetch_sale_items($filters),
            'filters' => $filters,
        ];
    }
}

if (!function_exists('common_service_fetch_staff_list')) {
    function common_service_fetch_staff_list() {
        return [
            'ok' => true,
            'message' => 'Staff list loaded.',
            'rows' => common_repo_fetch_staff_list(),
        ];
    }
}

if (!function_exists('common_service_fetch_item_list')) {
    function common_service_fetch_item_list($filters = []) {
        return [
            'ok' => true,
            'message' => 'Item list loaded.',
            'rows' => common_repo_fetch_items($filters),
            'filters' => $filters,
        ];
    }
}

if (!function_exists('common_service_fetch_catalog')) {
    function common_service_fetch_catalog($user_id, $filters = []) {
        $catalog = common_repo_fetch_catalog($user_id, (string)($filters['dpt'] ?? ''), (int)($filters['stock'] ?? 0), (int)($filters['menu'] ?? 0));
        return [
            'ok' => true,
            'message' => 'Catalog loaded.',
            'categories' => $catalog['categories'],
            'subcategories' => $catalog['subcategories'],
            'items' => $catalog['items'],
            'filters' => array_merge($filters, ['dpt' => $catalog['dpt']]),
        ];
    }
}

if (!function_exists('common_service_fetch_user_list')) {
    function common_service_fetch_user_list() {
        return [
            'ok' => true,
            'message' => 'User list loaded.',
            'rows' => common_repo_fetch_users(),
        ];
    }
}

if (!function_exists('common_service_fetch_role_list')) {
    function common_service_fetch_role_list() {
        return [
            'ok' => true,
            'message' => 'Role list loaded.',
            'rows' => common_repo_fetch_roles(),
        ];
    }
}

if (!function_exists('common_service_fetch_purchases')) {
    function common_service_fetch_purchases($filters = []) {
        return [
            'ok' => true,
            'message' => 'Purchases loaded.',
            'rows' => common_repo_fetch_purchases($filters),
            'filters' => $filters,
        ];
    }
}

if (!function_exists('common_service_fetch_purchase_items')) {
    function common_service_fetch_purchase_items($filters = []) {
        return [
            'ok' => true,
            'message' => 'Purchase items loaded.',
            'rows' => common_repo_fetch_purchase_items($filters),
            'filters' => $filters,
        ];
    }
}

if (!function_exists('common_service_fetch_stock_track')) {
    function common_service_fetch_stock_track($filters = []) {
        return [
            'ok' => true,
            'message' => 'Stock track loaded.',
            'rows' => common_repo_fetch_stock_track($filters),
            'filters' => $filters,
        ];
    }
}

?>
