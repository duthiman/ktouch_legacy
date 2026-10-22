<?php

if (!function_exists('tots_json_response')) {
    function tots_json_response($ok, $message, $data = [], $status_code = 200) {
        http_response_code((int)$status_code);
        echo json_encode([
            'ok' => (bool)$ok,
            'message' => (string)$message,
            'data' => is_array($data) ? $data : [],
        ]);
        exit;
    }
}

if (!function_exists('tots_check_session')) {
    function tots_check_session() {
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

if (!function_exists('tots_clean_text')) {
    function tots_clean_text($value) {
        return trim((string)$value);
    }
}

if (!function_exists('tots_clean_int')) {
    function tots_clean_int($value) {
        return (int)$value;
    }
}

if (!function_exists('tots_escape')) {
    function tots_escape($value) {
        return htmlspecialchars((string)$value, ENT_QUOTES, 'UTF-8');
    }
}

if (!function_exists('tots_render_item_buttons_html')) {
    function tots_render_item_buttons_html($items) {
        $html = '';

        foreach ((array)$items as $item) {
            $item_id = (int)($item['item_id'] ?? 0);
            $name = tots_escape($item['name'] ?? '');

            $html .= '<button data-id="' . $item_id . '" class="btn tot-item-btn" id="getItem" type="button">';
            $html .= $name;
            $html .= '</button>';
        }

        return $html;
    }
}

if (!function_exists('tots_render_category_buttons_html')) {
    function tots_render_category_buttons_html($categories, $variant = 'desktop') {
        $html = '';
        $variant = $variant === 'mobile' ? 'mobile' : 'desktop';

        foreach ((array)$categories as $category) {
            $cat_id = (int)($category['cat_id'] ?? 0);
            $name = tots_escape($category['name'] ?? '');

            if ($variant === 'mobile') {
                $html .= '<button type="button" class="tot-mobile-cat tot-mobile-cat-btn" data-cat-id="' . $cat_id . '">' . $name . '</button>';
                continue;
            }

            $html .= '<button type="button" class="btn_nav tot-desktop-cat-btn" data-cat-id="' . $cat_id . '">' . $name . '</button>';
        }

        return $html;
    }
}

if (!function_exists('tots_render_sub_nav_html')) {
    function tots_render_sub_nav_html($sub_categories) {
        if (count((array)$sub_categories) < 1) {
            return '';
        }

        $html = '<div class="navbar-header tot-sub-nav">';

        foreach ($sub_categories as $sub) {
            $sub_id = (int)($sub['sub_id'] ?? 0);
            $name = tots_escape($sub['name'] ?? '');
            $html .= '<button type="button" class="tot-mobile-sub-btn" data-sub-id="' . $sub_id . '">' . $name . '</button>';
        }

        $html .= '</div>';

        return $html;
    }
}

if (!function_exists('tots_render_live_list_html')) {
    function tots_render_live_list_html($items, $has_session) {
        $html = '<div class="tot-live-list">';

        if (!$has_session) {
            $html .= '<div class="tot-live-empty">No active TOT session found.</div>';
            $html .= '</div>';
            return $html;
        }

        if (count((array)$items) < 1) {
            $html .= '<div class="tot-live-empty">No TOT items added yet.</div>';
            $html .= '</div>';
            return $html;
        }

        $no = 0;
        foreach ($items as $item) {
            $no++;
            $tot_item_id = (int)($item['tot_item_id'] ?? 0);
            $name = tots_escape($item['name'] ?? '');
            $tots = tots_escape($item['tots'] ?? '0');

            $html .= '<div class="tot-live-item">';
            $html .= '<div class="tot-live-no">' . $no . '</div>';
            $html .= '<div class="tot-live-copy">';
            $html .= '<strong>' . $name . '</strong>';
            $html .= '<span>Generated Qty</span>';
            $html .= '</div>';
            $html .= '<div class="tot-live-meta">';
            $html .= '<strong>' . $tots . '</strong>';
            $html .= '<button type="button" class="tot-live-remove" data-tot-delete-id="' . $tot_item_id . '">Remove</button>';
            $html .= '</div>';
            $html .= '</div>';
        }

        $html .= '</div>';

        return $html;
    }
}

if (!function_exists('tots_render_summary_modal_html')) {
    function tots_render_summary_modal_html($counter_name, $tot_id, $list_html) {
        $counter_name = tots_escape($counter_name);
        $tot_id = (int)$tot_id;

        return <<<HTML
<div class="tot-summary-shell">
  <div class="modal-content">
    <div class="modal-header">
      <button type="button" class="close" data-dismiss="modal" aria-label="Close"><span aria-hidden="true">&times;</span></button>
      <h4 class="modal-title">Current TOT Items</h4>
    </div>
    <div class="modal-body tot-summary-body">
      <div class="tot-summary-strip">
        <b>{$counter_name}</b>
        <b>TOT No: {$tot_id}</b>
      </div>
      <div id="tot-summary-list">
        {$list_html}
      </div>
    </div>
    <div class="modal-footer">
      <button type="button" class="tot-summary-btn tot-summary-close" data-dismiss="modal">Close</button>
      <button type="button" class="tot-summary-btn tot-summary-submit" id="tot-submit-trigger">Submit</button>
    </div>
  </div>
</div>
HTML;
    }
}

if (!function_exists('tots_render_print_modal_html')) {
    function tots_render_print_modal_html($tot_id, $counters, $my_counter) {
        $tot_id = (int)$tot_id;
        $html = '<div class="tot-print-shell">';
        $html .= '<div class="modal-content">';
        $html .= '<div class="modal-header">';
        $html .= '<button type="button" class="close" data-dismiss="modal" aria-label="Close"><span aria-hidden="true">&times;</span></button>';
        $html .= '<h4 class="modal-title">Select Printer Counter</h4>';
        $html .= '</div>';
        $html .= '<div class="modal-body tot-print-body">';
        $html .= '<div class="tot-print-copy">Choose where TOT No. ' . $tot_id . ' should print.</div>';
        $html .= '<div class="tot-print-grid">';

        foreach ((array)$counters as $counter) {
            $cntr_id = (int)($counter['cntr_id'] ?? 0);
            $active = $cntr_id === (int)$my_counter ? ' active' : '';
            $name = tots_escape($counter['name'] ?? '');
            $dpt = (int)($counter['dpt'] ?? 0);
            $label = $name . ($dpt > 0 ? ' - DPT ' . $dpt : '');

            $html .= '<button type="button" class="tot-print-btn' . $active . '" data-tot-print-counter="' . $cntr_id . '">';
            $html .= '<strong>' . $cntr_id . '</strong>';
            $html .= '<span>' . $label . '</span>';
            $html .= '</button>';
        }

        $html .= '</div>';
        $html .= '<div id="tot-print-feedback" style="margin-top:10px;"></div>';
        $html .= '</div>';
        $html .= '<div class="modal-footer">';
        $html .= '<button type="button" class="btn btn-default" data-dismiss="modal">Close</button>';
        $html .= '</div>';
        $html .= '</div>';
        $html .= '</div>';

        return $html;
    }
}

if (!function_exists('tots_render_cancel_modal_html')) {
    function tots_render_cancel_modal_html() {
        return <<<HTML
<div class="tot-cancel-shell">
  <div class="modal-content">
    <div class="modal-header">
      <button type="button" class="close" data-dismiss="modal" aria-label="Close"><span aria-hidden="true">&times;</span></button>
      <h4 class="modal-title">Cancel Generated Units</h4>
    </div>
    <div class="modal-body tot-cancel-body">
      <div class="alert alert-danger" style="margin-bottom:12px; font-weight:700;">This action will remove the current generated units and restore stock quantities.</div>
      <p style="margin:0; color:#6f4b2e; font-weight:700;">Do you want to continue with the cancel?</p>
      <div id="tot-cancel-feedback" style="margin-top:12px;"></div>
    </div>
    <div class="modal-footer">
      <button type="button" class="tot-summary-btn tot-summary-close" data-dismiss="modal">Close</button>
      <button type="button" class="tot-summary-btn tot-cancel-confirm" id="tot-cancel-confirm">Continue Cancel</button>
    </div>
  </div>
</div>
HTML;
    }
}
