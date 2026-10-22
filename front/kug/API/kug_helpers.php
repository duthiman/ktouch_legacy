<?php

if (!function_exists('kug_json_response')) {
    function kug_json_response($ok, $message, $data = [], $status_code = 200) {
        http_response_code((int)$status_code);
        echo json_encode([
            'ok' => (bool)$ok,
            'message' => (string)$message,
            'data' => is_array($data) ? $data : [],
        ]);
        exit;
    }
}

if (!function_exists('kug_clean_text')) {
    function kug_clean_text($value) {
        return trim((string)$value);
    }
}

if (!function_exists('kug_clean_int')) {
    function kug_clean_int($value) {
        return (int)$value;
    }
}

if (!function_exists('kug_clean_float')) {
    function kug_clean_float($value) {
        return (float)$value;
    }
}

if (!function_exists('kug_escape')) {
    function kug_escape($value) {
        return htmlspecialchars((string)$value, ENT_QUOTES, 'UTF-8');
    }
}

if (!function_exists('kug_render_category_buttons_html')) {
    function kug_render_category_buttons_html($categories, $variant = 'desktop') {
        $html = '';
        $variant = $variant === 'mobile' ? 'mobile' : 'desktop';

        foreach ((array)$categories as $category) {
            $cat_id = (int)($category['cat_id'] ?? 0);
            $name = kug_escape($category['name'] ?? '');
            $class = $variant === 'mobile' ? 'kw-mobile-cat' : 'btn btn_nav';
            $html .= '<button type="button" class="' . $class . '" data-cat-id="' . $cat_id . '">' . $name . '</button>';
        }

        return $html;
    }
}

if (!function_exists('kug_render_subcategories_html')) {
    function kug_render_subcategories_html($subcategories) {
        $html = '';

        foreach ((array)$subcategories as $sub) {
            $sub_id = (int)($sub['sub_id'] ?? 0);
            $name = kug_escape($sub['name'] ?? '');
            $html .= '<button type="button" class="kw-sub-btn kug-sub-btn" data-sub-id="' . $sub_id . '">' . $name . '</button>';
        }

        return $html;
    }
}

if (!function_exists('kug_render_items_html')) {
    function kug_render_items_html($items) {
        $html = '';

        foreach ((array)$items as $item) {
            $item_id = (int)($item['item_id'] ?? 0);
            $name = kug_escape($item['name'] ?? '');
            $html .= '<button data-id="' . $item_id . '" class="btn btn-inverse additem kug-item-btn" id="getItem" type="button">' . $name . '</button>';
        }

        return $html;
    }
}

if (!function_exists('kug_render_list_html')) {
    function kug_render_list_html($items) {
        $html = '<table class="table table-bordered table-striped"><thead><tr><th><h4>NO.</h4></th><th><h4>ITEM</h4></th><th><h4>QTY</h4></th><th><h4>PRICE</h4></th><th><h4>TOTAL</h4></th><th><h4>ACTION</h4></th></tr></thead><tbody>';
        $no = 0;

        foreach ((array)$items as $row) {
            $no++;
            $html .= '<tr>';
            $html .= '<td>' . $no . '</td>';
            $html .= '<td>' . kug_escape($row['item_name'] ?? '') . '</td>';
            $html .= '<td>' . kug_escape($row['qty'] ?? '0') . '</td>';
            $html .= '<td>' . kug_escape($row['price'] ?? '0') . '</td>';
            $html .= '<td>' . kug_escape($row['total'] ?? '0') . '</td>';
            $html .= '<td><button type="button" onclick="del_item(' . (int)($row['item_id'] ?? 0) . ')" style="margin-bottom: 8px; margin-right: 5px; font-size: 12px; color:red;" class="btn btn-outline-danger btn-sm">REMOVE</button></td>';
            $html .= '</tr>';
        }

        $html .= '</tbody></table>';

        return $html;
    }
}

if (!function_exists('kug_render_supplier_modal_html')) {
    function kug_render_supplier_modal_html($suppliers, $current_supplier_id) {
        $current_supplier_id = (int)$current_supplier_id;
        $current_name = 'NONE';

        foreach ((array)$suppliers as $supplier) {
            if ((int)$supplier['sup_id'] === $current_supplier_id) {
                $current_name = strtoupper((string)$supplier['sup_name']);
                break;
            }
        }

        $html = '<div class="modal-content">';
        $html .= '<div class="modal-header"><button type="button" class="close" data-dismiss="modal" aria-label="Close"><span aria-hidden="true">&times;</span></button><h4 class="modal-title" style="font-weight: bold;">SELECT SUPPLIER</h4></div>';
        $html .= '<div class="modal-body">';
        $html .= '<div style="background: #fff4c2; color: #6a4d00; border-radius: 12px; padding: 10px; margin-bottom: 12px; font-size: 18px; font-weight: bold;">Current: ' . kug_escape($current_name) . '</div>';
        $html .= '<p style="margin-bottom: 10px;">A supplier is required before this purchase can be raised.</p>';
        $html .= '<select class="form-control" id="sup_id" style="height: 54px; font-size: 20px; font-weight: bold;"><option value="0">SELECT SUPPLIER</option>';

        foreach ((array)$suppliers as $supplier) {
            $sup_id = (int)$supplier['sup_id'];
            $selected = $sup_id === $current_supplier_id ? ' selected' : '';
            $html .= '<option value="' . $sup_id . '"' . $selected . '>' . kug_escape(strtoupper((string)$supplier['sup_name'])) . '</option>';
        }

        $html .= '</select></div>';
        $html .= '<div class="modal-footer"><button type="button" class="btn btn-outline-danger" data-dismiss="modal" style="font-size: 20px; min-width: 120px;">CLOSE</button><button type="button" class="btn btn-outline-primary" id="add-new-front-supplier" style="font-size: 20px; min-width: 150px;">ADD NEW</button><button type="button" class="btn btn-outline-success" id="save-supplier" style="font-size: 20px; min-width: 120px;">SAVE</button></div>';
        $html .= '</div>';

        return $html;
    }
}

if (!function_exists('kug_render_cancel_modal_html')) {
    function kug_render_cancel_modal_html() {
        return '<div class="modal-content"><div class="modal-header"><button type="button" class="close" data-dismiss="modal" aria-label="Close"><span aria-hidden="true">&times;</span></button><h4 class="modal-title">WARNING</h4></div><div class="modal-body"><div class="container-fluid"><div class="row"><div class="alert alert-danger"><p>WARNING!! This action will delete the current buy stock draft and its items.</p></div></div></div><div id="buy-cancel-feedback"></div></div><div class="modal-footer"><button type="button" class="btn btn-outline-danger" data-dismiss="modal">Close</button><button type="button" class="btn btn-outline-success" id="buy-cancel-confirm">CONTINUE</button></div></div>';
    }
}

if (!function_exists('kug_render_submit_modal_html')) {
    function kug_render_submit_modal_html($payload) {
        $counter_name = kug_escape($payload['counter_name'] ?? '');
        $supplier_name = kug_escape($payload['supplier_name'] ?? 'Not selected');
        $buy_tax = !empty($payload['buy_tax']) ? 'YES' : 'NO';
        $rows = (array)($payload['items'] ?? []);
        $has_items = !empty($payload['has_items']);
        $has_supplier = !empty($payload['has_supplier']);
        $grand_total = 0;
        $total_qty = 0;

        $html = '<div class="modal-content"><div class="modal-header"><button type="button" class="close" data-dismiss="modal" aria-label="Close"><span aria-hidden="true">&times;</span></button><h4 class="modal-title" style="font-weight: bold;">' . $counter_name . ' BUY STOCK CONFIRMATION</h4></div><div class="modal-body"><div class="container-fluid"><div class="row"><div class="alert alert-warning col-md-12"><h4>Confirm the items first. This request will go to backend for approval before stock is received.</h4><p style="margin-bottom: 0;">Supplier: <strong>' . $supplier_name . '</strong></p><p style="margin-bottom: 0;">Add Tax: <strong>' . $buy_tax . '</strong></p></div><div class="col-md-12" style="overflow-y:scroll; height: 60%;"><table class="table table-bordered table-striped"><thead><tr><th><h4>NO.</h4></th><th><h4>ITEM</h4></th><th><h4>QTY</h4></th><th><h4>PRICE</h4></th><th><h4>TOTAL</h4></th></tr></thead><tbody>';

        $no = 0;
        foreach ($rows as $row) {
            $no++;
            $grand_total += (float)($row['total'] ?? 0);
            $total_qty += (float)($row['qty'] ?? 0);
            $html .= '<tr><td>' . $no . '</td><td>' . kug_escape($row['item_name'] ?? '') . '</td><td>' . kug_escape($row['qty'] ?? '0') . '</td><td>' . kug_escape($row['price'] ?? '0') . '</td><td>' . kug_escape($row['total'] ?? '0') . '</td></tr>';
        }

        $html .= '<tr style="font-weight: bold; background-color: #f7f7f7;"><td colspan="2">TOTAL</td><td>' . kug_escape($total_qty) . '</td><td></td><td>' . number_format($grand_total, 2) . '</td></tr>';
        $html .= '</tbody></table></div></div></div><div id="buy-submit-feedback" style="padding:0 15px 10px;"></div></div><div class="modal-footer"><button type="button" class="btn" style="color:white; font-size: 18px; font-weight: bold; padding: 8px; background-color:#FFF; border: 2px solid red; border-radius: 6px; color: red;" data-dismiss="modal">CLOSE</button>';

        if (!$has_items) {
            $html .= '<button type="button" class="btn" style="font-size: 18px; font-weight: bold; padding: 8px; background-color:#FFF; border: 2px solid #999; border-radius: 6px; color: #999;" disabled>ADD ITEMS FIRST</button>';
        } elseif (!$has_supplier) {
            $html .= '<button type="button" class="btn" style="font-size: 18px; font-weight: bold; padding: 8px; background-color:#FFF; border: 2px solid #d97706; border-radius: 6px; color: #d97706;" disabled>SELECT SUPPLIER FIRST</button>';
        } else {
            $html .= '<button type="button" class="btn" style="color:white; font-size: 18px; font-weight: bold; padding: 8px; background-color:#FFF; border: 2px solid #61b846; border-radius: 6px; color: #61b846;" id="buy-submit-confirm">RAISE PURCHASE</button>';
        }

        $html .= '</div></div>';
        return $html;
    }
}

