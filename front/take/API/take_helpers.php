<?php

if (!function_exists('take_json_response')) {
    function take_json_response($ok, $message, $data = [], $status_code = 200) {
        http_response_code((int)$status_code);
        echo json_encode([
            'ok' => (bool)$ok,
            'message' => (string)$message,
            'data' => is_array($data) ? $data : [],
        ]);
        exit;
    }
}

if (!function_exists('take_check_session')) {
    function take_check_session() {
        $user_id = isset($_SESSION['user_id']) ? (int)$_SESSION['user_id'] : 0;

        if ($user_id < 1) {
            return [
                'ok' => false,
                'message' => 'Session expired. Please login again.',
                'user_id' => 0,
            ];
        }

        return [
            'ok' => true,
            'message' => 'Session is active.',
            'user_id' => $user_id,
        ];
    }
}

if (!function_exists('take_clean_text')) {
    function take_clean_text($value) {
        return trim((string)$value);
    }
}

if (!function_exists('take_clean_int')) {
    function take_clean_int($value) {
        return (int)$value;
    }
}

if (!function_exists('take_clean_float')) {
    function take_clean_float($value) {
        return (float)$value;
    }
}

if (!function_exists('take_escape')) {
    function take_escape($value) {
        return htmlspecialchars((string)$value, ENT_QUOTES, 'UTF-8');
    }
}

if (!function_exists('take_format_number')) {
    function take_format_number($value) {
        return rtrim(rtrim(number_format((float)$value, 2, '.', ''), '0'), '.');
    }
}

if (!function_exists('take_render_item_options_html')) {
    function take_render_item_options_html($grouped_items) {
        $html = '';

        foreach ((array)$grouped_items as $group) {
            $label = take_escape($group['label'] ?? '');
            $html .= "<optgroup label=\"{$label}\">";
            foreach ((array)($group['items'] ?? []) as $item) {
                $html .= '<option value="' . (int)$item['item_id'] . '"';
                $html .= ' data-opening="' . take_escape($item['opening_qty'] ?? 0) . '"';
                $html .= ' data-stock-take-type="' . (int)($item['stock_take_type'] ?? 2) . '"';
                $html .= ' data-empty-weight="' . take_escape($item['empty_bottle_weight'] ?? 0) . '"';
                $html .= ' data-full-weight="' . take_escape($item['full_bottle_weight'] ?? 0) . '"';
                $html .= ' data-bottle-mls="' . take_escape($item['bottle_mls'] ?? 0) . '"';
                $html .= ' data-tot-num="' . take_escape($item['tot_num'] ?? 0) . '"';
                $html .= '>' . take_escape($item['name'] ?? '') . '</option>';
            }
            $html .= '</optgroup>';
        }

        return $html;
    }
}

if (!function_exists('take_render_live_list_html')) {
    function take_render_live_list_html($rows) {
        if (count((array)$rows) < 1) {
            return '<p class="take-live-empty">No counted items yet.</p>';
        }

        $total_counted = count($rows);
        $total_ok = 0;
        $total_variance = 0;

        foreach ($rows as $row) {
            if (($row['variance_raw'] ?? 0.0) == 0.0) {
                $total_ok++;
            } else {
                $total_variance++;
            }
        }

        $html = '<div class="take-summary-grid">';
        $html .= '<div class="take-summary-card take-summary-total"><strong>Total Counted</strong><span>' . $total_counted . '</span></div>';
        $html .= '<div class="take-summary-card take-summary-ok"><strong>Total Ok</strong><span>' . $total_ok . '</span></div>';
        $html .= '<div class="take-summary-card take-summary-var"><strong>Total Variance</strong><span>' . $total_variance . '</span></div>';
        $html .= '</div>';
        $html .= '<div class="take-live-table-wrap"><table class="table table-bordered take-live-table"><thead><tr><th>Item Name</th><th>Counted</th><th>Action</th></tr></thead><tbody>';

        foreach ($rows as $row) {
            $html .= '<tr style="color: ' . take_escape($row['color'] ?? '#7e3a20') . ';">';
            $html .= '<td>' . take_escape($row['name'] ?? '') . '</td>';
            $html .= '<td>' . take_format_number($row['counted'] ?? 0) . '</td>';
            $html .= '<td><button type="button" class="take-live-remove" data-take-delete-id="' . (int)($row['item_id'] ?? 0) . '" style="background: ' . take_escape($row['color'] ?? '#7e3a20') . ';">Remove</button></td>';
            $html .= '</tr>';
        }

        $html .= '</tbody></table></div>';

        return $html;
    }
}

if (!function_exists('take_render_variance_modal_html')) {
    function take_render_variance_modal_html($rows) {
        $total_rows = count((array)$rows);
        $html = '<div class="take-variance-shell"><div class="take-variance-modal-card">';
        $html .= '<div class="take-variance-modal-head"><div><strong>Variance Items</strong><span>Review all counted items that do not match expected stock.</span></div><button type="button" class="take-variance-close" id="take_variance_close_btn">&times;</button></div>';
        $html .= '<div class="take-variance-modal-body">';
        $html .= '<div class="take-variance-summary"><div class="take-variance-summary-card"><strong>Flagged Items</strong><span>' . $total_rows . '</span></div></div>';

        if ($total_rows < 1) {
            $html .= '<div class="take-variance-empty">All counted items are balanced. No variance to review.</div>';
        } else {
            $html .= '<div class="take-variance-table-wrap">';
            $html .= '<div class="take-variance-row take-variance-row-head"><div>Item</div><div>Expected</div><div>Counted</div><div>Variance</div></div>';
            foreach ($rows as $row) {
                $html .= '<div class="take-variance-row ' . take_escape($row['tone'] ?? 'is-danger') . '">';
                $html .= '<div><strong>' . take_escape($row['name'] ?? '') . '</strong><span class="take-variance-item-note">' . take_escape($row['label'] ?? 'Variance') . '</span></div>';
                $html .= '<div><span class="take-variance-figure">' . take_format_number($row['opening'] ?? 0) . '</span></div>';
                $html .= '<div><span class="take-variance-figure">' . take_format_number($row['counted'] ?? 0) . '</span></div>';
                $html .= '<div><span class="take-variance-amount">' . take_escape($row['variance_label'] ?? '0') . '</span></div>';
                $html .= '</div>';
            }
            $html .= '</div>';
        }

        $html .= '</div></div></div>';
        return $html;
    }
}

if (!function_exists('take_render_confirm_modal_html')) {
    function take_render_confirm_modal_html($counter_name, $shift, $issues) {
        $counter_name = take_escape($counter_name);
        $shift = take_escape($shift);
        $html = '<div class="take-confirm-shell"><div class="take-modal-card">';
        $html .= '<div class="take-confirm-head"><h4>Confirm Stock Take</h4><button type="button" class="take-confirm-close" id="take_confirm_close_btn">&times;</button></div>';
        $html .= '<div class="take-confirm-body">';
        $html .= '<div class="take-confirm-strip"><strong>' . $counter_name . '</strong><span>Shift ' . $shift . '</span></div>';

        if (count((array)$issues) < 1) {
            $html .= '<div class="take-confirm-ok"><div class="take-confirm-ok-icon">&#10003;</div><h5>Stock Is Ok</h5><p>Congratulations. All counted items match the expected stock.</p></div>';
        } else {
            $html .= '<div class="take-confirm-table-wrap"><table class="take-confirm-table"><thead><tr><th>Item</th><th>Expected</th><th>Counted</th><th>Variance</th><th>Status</th></tr></thead><tbody>';
            foreach ($issues as $issue) {
                $html .= '<tr>';
                $html .= '<td>' . take_escape($issue['name'] ?? '') . '</td>';
                $html .= '<td>' . take_format_number($issue['opening'] ?? 0) . '</td>';
                $html .= '<td>' . take_format_number($issue['counted'] ?? 0) . '</td>';
                $html .= '<td style="color: ' . take_escape($issue['color'] ?? '#c0392b') . '; font-weight:900;">' . take_escape($issue['variance_label'] ?? '0') . '</td>';
                $html .= '<td><span class="take-confirm-row-label ' . take_escape($issue['tone'] ?? 'is-danger') . '">' . take_escape($issue['label'] ?? 'Variance') . '</span></td>';
                $html .= '</tr>';
            }
            $html .= '</tbody></table></div>';
        }

        $html .= '</div><div class="take-confirm-foot"><div id="take_confirm_feedback" class="take-confirm-feedback"></div><button type="button" class="take-confirm-btn take-confirm-cancel" id="take_confirm_cancel_btn">Close</button><button type="button" id="take_confirm_now" class="take-confirm-btn take-confirm-go">Confirm Now</button></div></div></div>';
        return $html;
    }
}
