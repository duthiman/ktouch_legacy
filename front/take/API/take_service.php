<?php

if (!function_exists('take_service_build_item_groups')) {
    function take_service_build_item_groups($items) {
        $grouped = [];

        foreach ((array)$items as $item) {
            $label = trim((string)($item['category_name'] ?? ''));
            if ($label === '') {
                $label = 'NO CATEGORY ITEMS';
            }
            if (!isset($grouped[$label])) {
                $grouped[$label] = [];
            }
            $grouped[$label][] = $item;
        }

        $groups = [];
        foreach ($grouped as $label => $groupItems) {
            $groups[] = [
                'label' => $label,
                'items' => $groupItems,
            ];
        }

        return $groups;
    }
}

if (!function_exists('take_service_items_payload')) {
    function take_service_items_payload($context) {
        $items = take_repo_fetch_remaining_items($context['closing_table'], $context['take_column'], $context['dpt']);
        return [
            'items' => $items,
            'groups' => take_service_build_item_groups($items),
            'html' => take_render_item_options_html(take_service_build_item_groups($items)),
            'remaining_count' => count($items),
            'has_items' => count($items) > 0,
        ];
    }
}

if (!function_exists('take_service_list_payload')) {
    function take_service_list_payload($context) {
        $rows = [];
        $variance_count = 0;

        foreach (take_repo_fetch_take_rows($context['take_table'], $context['shift']) as $row) {
            $opening = (float)$row['opening'];
            $counted = (float)$row['counted'];
            $diff = round($counted - $opening, 2);
            if ($diff == 0.0) {
                $color = '#2f7d32';
                $label = 'OK';
            } elseif ($diff > 0) {
                $color = '#d97706';
                $label = '+' . take_format_number($diff);
                $variance_count++;
            } else {
                $color = '#c0392b';
                $label = take_format_number($diff);
                $variance_count++;
            }
            $rows[] = [
                'item_id' => (int)$row['item_id'],
                'name' => (string)($row['name'] ?? ('Item #' . $row['item_id'])),
                'counted' => $counted,
                'variance_label' => $label,
                'variance_raw' => $diff,
                'color' => $color,
            ];
        }

        return [
            'rows' => $rows,
            'html' => take_render_live_list_html($rows),
            'counted_count' => count($rows),
            'variance_count' => $variance_count,
        ];
    }
}

if (!function_exists('take_service_boot')) {
    function take_service_boot($user_id) {
        $context = take_repo_context($user_id);
        $items = take_service_items_payload($context);
        $list = take_service_list_payload($context);
        return [
            'ok' => true,
            'message' => 'Take API is ready.',
            'context' => $context,
            'items_html' => $items['html'],
            'list_html' => $list['html'],
            'metrics' => [
                'remaining_count' => $items['remaining_count'],
                'has_items' => $items['has_items'],
                'counted_count' => $list['counted_count'],
                'variance_count' => $list['variance_count'],
            ],
        ];
    }
}

if (!function_exists('take_service_fetch_items')) {
    function take_service_fetch_items($user_id) {
        $context = take_repo_context($user_id);
        $items = take_service_items_payload($context);

        return [
            'ok' => true,
            'message' => 'Take items loaded.',
            'html' => $items['html'],
            'remaining_count' => $items['remaining_count'],
            'has_items' => $items['has_items'],
        ];
    }
}

if (!function_exists('take_service_calc_weight_qty')) {
    function take_service_calc_weight_qty($entry_weight, $empty_weight, $full_weight, $bottle_mls, $tot_num) {
        $entry_weight = (float)$entry_weight;
        $empty_weight = (float)$empty_weight;
        $full_weight = (float)$full_weight;
        $bottle_mls = (float)$bottle_mls;
        $tot_num = (float)$tot_num;

        if ($full_weight <= $empty_weight || $bottle_mls <= 0 || $tot_num <= 0) {
            return false;
        }

        $current_liquid_weight = max(0, $entry_weight - $empty_weight);
        $full_liquid_weight = $full_weight - $empty_weight;
        $current_mls = ($current_liquid_weight / $full_liquid_weight) * $bottle_mls;
        $current_mls = max(0, min($current_mls, $bottle_mls));
        $ml_per_tot = $bottle_mls / $tot_num;

        if ($ml_per_tot <= 0) {
            return false;
        }

        return round($current_mls / $ml_per_tot, 2);
    }
}

if (!function_exists('take_service_list_rows')) {
    function take_service_list_rows($user_id) {
        $context = take_repo_context($user_id);
        $list = take_service_list_payload($context);

        return [
            'ok' => true,
            'message' => 'Take list loaded.',
            'rows' => $list['rows'],
            'html' => $list['html'],
            'counted_count' => $list['counted_count'],
            'variance_count' => $list['variance_count'],
        ];
    }
}

if (!function_exists('take_service_add_item')) {
    function take_service_add_item($user_id, $item_id, $qty, $take_entry) {
        $context = take_repo_context($user_id);
        $item_id = (int)$item_id;
        $qty = $qty;
        $take_entry = $take_entry;

        if ($item_id < 1) {
            return ['ok' => false, 'message' => 'Select an item before adding quantity.'];
        }

        $item = take_repo_fetch_item_meta($item_id);
        $stock_take_type = $item ? (int)$item['stock_take_type'] : 2;

        if ($stock_take_type === 1) {
            if ($take_entry === '' || !is_numeric($take_entry)) {
                return ['ok' => false, 'message' => 'Enter the current bottle weight before adding item.'];
            }
            $qty = take_service_calc_weight_qty($take_entry, $item['empty_bottle_weight'], $item['full_bottle_weight'], $item['bottle_mls'], $item['tot_num']);
            if ($qty === false) {
                return ['ok' => false, 'message' => 'Complete the weight stock take setup for this item first.'];
            }
        } elseif ($qty === '' || !is_numeric($qty)) {
            return ['ok' => false, 'message' => 'Enter counted quantity before adding item.'];
        }

        $counted = round((float)$qty, 2);
        $opening = round((float)take_repo_fetch_opening_qty($context['closing_table'], $item_id), 2);
        $placed = take_repo_insert_take_row($context['take_table'], $item_id, $user_id, $opening, $counted, $context['shift']);

        if (!$placed) {
            return ['ok' => false, 'message' => 'Could not save stock take history.'];
        }

        $updated = take_repo_update_item_count($context['take_column'], $context['qty_column'], $item_id, $counted);
        if (!$updated) {
            return ['ok' => false, 'message' => 'Could not update items.'];
        }

        $list = take_service_list_rows($user_id);
        $items = take_service_fetch_items($user_id);

        return [
            'ok' => true,
            'message' => 'Take item recorded.',
            'item_name' => (string)($item['name'] ?? ('Item #' . $item_id)),
            'counted' => $counted,
            'counted_label' => take_format_number($counted),
            'list_html' => $list['html'] ?? '',
            'items_html' => $items['html'] ?? '',
            'remaining_count' => $items['remaining_count'] ?? 0,
            'has_items' => $items['has_items'] ?? false,
            'variance_count' => $list['variance_count'] ?? 0,
        ];
    }
}

if (!function_exists('take_service_delete_item')) {
    function take_service_delete_item($user_id, $item_id) {
        $context = take_repo_context($user_id);
        $item_id = (int)$item_id;
        if ($item_id < 1) {
            return ['ok' => false, 'message' => 'Invalid item selected for delete.'];
        }

        $row = take_repo_fetch_take_row_by_item($context['take_table'], $item_id, $context['shift']);
        if (!$row) {
            return ['ok' => false, 'message' => 'Item was not found in stock take list.'];
        }

        $opening = (float)$row['opening'];
        $reset = take_repo_reset_item_after_delete($context['qty_column'], $context['take_column'], $item_id, $opening);
        $deleted = take_repo_delete_take_row($context['take_table'], $item_id, $context['shift']);

        if (!$reset || !$deleted) {
            return ['ok' => false, 'message' => 'Failed to delete item from stock take.'];
        }

        $list = take_service_list_rows($user_id);
        $items = take_service_fetch_items($user_id);
        return [
            'ok' => true,
            'message' => 'Item removed from stock take list.',
            'list_html' => $list['html'] ?? '',
            'items_html' => $items['html'] ?? '',
            'remaining_count' => $items['remaining_count'] ?? 0,
            'has_items' => $items['has_items'] ?? false,
            'variance_count' => $list['variance_count'] ?? 0,
        ];
    }
}

if (!function_exists('take_service_variance_modal')) {
    function take_service_variance_modal($user_id) {
        $context = take_repo_context($user_id);
        $rows = [];

        foreach (take_repo_fetch_take_rows($context['take_table'], $context['shift']) as $row) {
            $opening = (float)$row['opening'];
            $counted = (float)$row['counted'];
            $variance = round($counted - $opening, 2);
            if ($variance == 0.0) {
                continue;
            }
            $rows[] = [
                'name' => (string)($row['name'] ?? ('Item #' . $row['item_id'])),
                'opening' => $opening,
                'counted' => $counted,
                'variance_label' => ($variance > 0 ? '+' : '') . take_format_number($variance),
                'tone' => $variance > 0 ? 'is-warning' : 'is-danger',
            ];
        }

        return [
            'ok' => true,
            'message' => 'Variance modal loaded.',
            'html' => take_render_variance_modal_html($rows),
        ];
    }
}

if (!function_exists('take_service_confirm_modal')) {
    function take_service_confirm_modal($user_id) {
        $context = take_repo_context($user_id);
        $issues = [];

        foreach (take_repo_fetch_take_rows($context['take_table'], $context['shift']) as $row) {
            $diff = round(((float)$row['counted']) - ((float)$row['opening']), 2);
            if ($diff == 0.0) {
                continue;
            }
            $issues[] = [
                'name' => (string)($row['name'] ?? ('Item #' . $row['item_id'])),
                'counted' => (float)$row['counted'],
                'opening' => (float)$row['opening'],
                'color' => $diff > 0 ? '#d97706' : '#c0392b',
                'tone' => $diff > 0 ? 'is-warning' : 'is-danger',
                'label' => $diff > 0 ? 'Excess' : 'Variance',
                'variance_label' => ($diff > 0 ? '+' : '') . take_format_number($diff),
            ];
        }

        return [
            'ok' => true,
            'message' => 'Confirm modal loaded.',
            'html' => take_render_confirm_modal_html($context['counter_name'], $context['shift'], $issues),
        ];
    }
}

if (!function_exists('take_queue_variance_report')) {
    function take_queue_variance_report($connection, $counter, $user_id, $shift, array $rows) {
        $counter = (int)$counter;
        $user_id = (int)$user_id;
        $shift = trim((string)$shift);
        $variance_items = [];

        foreach ($rows as $row) {
            $opening = (float)($row['opening'] ?? 0);
            $counted = (float)($row['counted'] ?? 0);
            $variance = round($counted - $opening, 2);
            if ($variance == 0.0) {
                continue;
            }
            $variance_items[] = [
                'item_id' => (int)($row['item_id'] ?? 0),
                'name' => (string)($row['name'] ?? ('Item #' . (int)($row['item_id'] ?? 0))),
                'opening' => $opening,
                'counted' => $counted,
                'variance' => $variance,
            ];
        }

        if (!$variance_items || $counter < 1 || $user_id < 1 || $shift === '') {
            return 0;
        }

        mysqli_query($connection, "CREATE TABLE IF NOT EXISTS kp_print_queue (
            queue_id INT NOT NULL AUTO_INCREMENT,
            sale_id INT NOT NULL DEFAULT 0,
            ref_id INT NOT NULL DEFAULT 0,
            print_type VARCHAR(55) NOT NULL DEFAULT 'bill',
            label VARCHAR(120) NOT NULL DEFAULT '',
            cntr_id INT NOT NULL DEFAULT 0,
            route_path VARCHAR(255) NOT NULL DEFAULT '',
            route_query MEDIUMTEXT NULL,
            requested_by INT NOT NULL DEFAULT 0,
            reprint_of INT NOT NULL DEFAULT 0,
            status TINYINT(1) NOT NULL DEFAULT 0,
            created_at TIMESTAMP NOT NULL DEFAULT CURRENT_TIMESTAMP,
            shift VARCHAR(60) NOT NULL DEFAULT '',
            payload_json LONGTEXT NULL,
            items_json LONGTEXT NULL,
            print_attempts TINYINT UNSIGNED NOT NULL DEFAULT 0,
            last_error VARCHAR(255) NOT NULL DEFAULT '',
            printed_at DATETIME NULL,
            PRIMARY KEY (queue_id),
            KEY idx_queue_counter_status (cntr_id, status),
            KEY idx_queue_status_created (status, created_at)
        ) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4");
        if (mysqli_errno($connection)) {
            return 0;
        }

        $counter_name = function_exists('get_counter') ? (string)get_counter($counter) : ('COUNTER ' . $counter);
        $payload = json_encode([
            'payload_version' => 1,
            'document_type' => 'SVP',
            'title' => 'STOCK VARIANCE PRINT',
            'header' => [
                'counter_id' => $counter,
                'counter_name' => $counter_name,
                'shift' => $shift,
                'print_time' => date('Y-m-d H:i:s'),
            ],
            'variance_summary' => [
                'variance_count' => count($variance_items),
                'variance_qty' => array_sum(array_map(static function ($item) {
                    return abs((float)$item['variance']);
                }, $variance_items)),
            ],
            'variance_items' => $variance_items,
        ], JSON_UNESCAPED_UNICODE | JSON_UNESCAPED_SLASHES);
        if ($payload === false) {
            return 0;
        }

        $counter_sql = (int)$counter;
        $user_sql = (int)$user_id;
        $shift_sql = mysqli_real_escape_string($connection, $shift);
        $payload_sql = mysqli_real_escape_string($connection, $payload);
        $query_sql = mysqli_real_escape_string($connection, 'cntr_id=' . $counter . '&shift=' . urlencode($shift));
        $sql = "INSERT INTO kp_print_queue
            (sale_id, ref_id, print_type, label, cntr_id, route_path, route_query,
             requested_by, reprint_of, status, shift, payload_json, items_json)
            VALUES (0, {$counter_sql}, 'SVP', 'STOCK VARIANCE PRINT', {$counter_sql},
                    'svp.php', '{$query_sql}', {$user_sql}, 0, 0, '{$shift_sql}',
                    '{$payload_sql}', '')";

        return mysqli_query($connection, $sql) ? (int)mysqli_insert_id($connection) : 0;
    }
}

if (!function_exists('take_service_confirm_take')) {
    function take_service_confirm_take($user_id) {
        global $connection;
        $context = take_repo_context($user_id);
        $counter = (int)$context['counter'];
        if ($user_id < 1) {
            return ['ok' => false, 'status' => 'error', 'message' => 'Session expired. Please log in again.', 'redirect' => '../log_out.php'];
        }
        if ($counter < 1) {
            return ['ok' => false, 'status' => 'warning', 'message' => 'Log out and log in again. If issue persists, contact admin.'];
        }

        $updated = take_repo_update_counter_take_status($counter, 1);
        if (!$updated) {
            return ['ok' => false, 'status' => 'error', 'message' => 'Error updating stock status. Please try again.'];
        }

        if (take_repo_open_take_count() == 0) {
            take_repo_mark_stock_shift_complete();
        }

        $action = 'CONFIRMED SHIFT STOCK TAKE | | | | FOR' . (function_exists('get_counter') ? get_counter($counter) : ('COUNTER ' . $counter)) . 'COUNTER';
        if (function_exists('logs')) {
            logs($action, '../logs');
        }

        $take_rows = take_repo_fetch_take_rows($context['take_table'], $context['shift']);
        $has_variance = false;
        foreach ($take_rows as $row) {
            if ((float)$row['opening'] != (float)$row['counted']) {
                $has_variance = true;
                break;
            }
        }

        if ($has_variance) {
            $queue_id = take_queue_variance_report($connection, $counter, $user_id, $context['shift'], $take_rows);
            if ($queue_id > 0) {
                return ['ok' => true, 'status' => 'success', 'message' => 'Stock take confirmed. Variance report queued for printing.', 'redirect' => '../log_out.php'];
            }
            return ['ok' => false, 'status' => 'error', 'message' => 'Stock take confirmed, but the variance report could not be queued.', 'redirect' => '../log_out.php'];
        }

        return ['ok' => true, 'status' => 'success', 'message' => 'Stock take confirmed successfully.', 'redirect' => '../log_out.php'];
    }
}
