<?php

if (!function_exists('attr_service_render_mapped_items_html')) {
    function attr_service_render_mapped_items_html($items, $attr_id)
    {
        if (count($items) < 1) {
            return '<p>No items mapped to this attribute.</p>';
        }

        $output = '<table class="table table-striped table-bordered">';
        $output .= '<thead><tr><th>No.</th><th>Item Name</th><th>Action</th></tr></thead><tbody>';

        $no = 0;
        foreach ($items as $item) {
            $no++;
            $item_id = (int)$item['item_id'];
            $item_name = htmlspecialchars((string)$item['name'], ENT_QUOTES, 'UTF-8');
            $output .= "<tr>";
            $output .= "<td>{$no}</td>";
            $output .= "<td>{$item_name}</td>";
            $output .= "<td><button class='btn btn-xs btn-danger unmap-item' data-item_id='{$item_id}' data-attr_id='" . (int)$attr_id . "'>Unmap</button></td>";
            $output .= "</tr>";
        }

        $output .= '</tbody></table>';
        return $output;
    }
}

if (!function_exists('attr_service_modal_view')) {
    function attr_service_modal_view($view, $params = [])
    {
        $allowed = [
            'attr_new' => __DIR__ . '/../attr_new.php',
        ];

        if (!isset($allowed[$view])) {
            return ['ok' => false, 'message' => 'Unknown modal view.'];
        }

        return [
            'ok' => true,
            'message' => 'Modal loaded.',
            'html' => attr_include_capture($allowed[$view], $params),
        ];
    }
}

if (!function_exists('attr_service_create_attribute')) {
    function attr_service_create_attribute($name, $affects_price, $price_change, $status)
    {
        $name = attr_clean_text($name);
        $affects_price = (int)$affects_price;
        $price_change = (float)$price_change;
        $status = (int)$status;

        if ($name === '') {
            return ['ok' => false, 'message' => 'Attribute name is required.'];
        }

        if (attr_repo_attribute_exists_by_name($name)) {
            return ['ok' => false, 'message' => 'Attribute name "' . $name . '" already exists.'];
        }

        $ok = attr_repo_insert_attribute($name, $affects_price, $price_change, $status);
        return $ok
            ? ['ok' => true, 'message' => 'Attribute ' . $name . ' added successfully.', 'refresh' => true]
            : ['ok' => false, 'message' => 'Error saving attribute.'];
    }
}

if (!function_exists('attr_service_load_mapped_items')) {
    function attr_service_load_mapped_items($attr_id)
    {
        $attr_id = (int)$attr_id;
        if ($attr_id < 1) {
            return ['ok' => false, 'message' => 'Invalid attribute ID.', 'html' => '<p>Invalid attribute ID.</p>'];
        }

        $items = attr_repo_fetch_mapped_items($attr_id);
        return [
            'ok' => true,
            'message' => 'Mapped items loaded.',
            'html' => attr_service_render_mapped_items_html($items, $attr_id),
        ];
    }
}

if (!function_exists('attr_service_map_item')) {
    function attr_service_map_item($item_id, $attr_id)
    {
        global $connection;
        $item_id = (int)$item_id;
        $attr_id = (int)$attr_id;

        if ($item_id < 1 || $attr_id < 1) {
            return ['ok' => false, 'message' => 'Invalid request'];
        }

        mysqli_begin_transaction($connection);
        try {
            if (attr_repo_mapping_exists($item_id, $attr_id)) {
                mysqli_rollback($connection);
                return ['ok' => false, 'message' => 'Item already mapped'];
            }

            if (!attr_repo_insert_mapping($item_id, $attr_id)) {
                throw new Exception('Error mapping item.');
            }

            if (!attr_repo_update_item_has_attributes($item_id, 1)) {
                throw new Exception('Error updating item attribute flag.');
            }

            mysqli_commit($connection);
            return ['ok' => true, 'message' => 'Item mapped successfully'];
        } catch (Exception $e) {
            mysqli_rollback($connection);
            return ['ok' => false, 'message' => $e->getMessage()];
        }
    }
}

if (!function_exists('attr_service_unmap_item')) {
    function attr_service_unmap_item($item_id, $attr_id)
    {
        global $connection;
        $item_id = (int)$item_id;
        $attr_id = (int)$attr_id;

        if ($item_id < 1 || $attr_id < 1) {
            return ['ok' => false, 'message' => 'Invalid request'];
        }

        mysqli_begin_transaction($connection);
        try {
            if (!attr_repo_delete_mapping($item_id, $attr_id)) {
                throw new Exception('Error unmapping item.');
            }

            $mapping_count = attr_repo_item_mapping_count($item_id);
            $has_attributes = $mapping_count > 0 ? 1 : 2;

            if (!attr_repo_update_item_has_attributes($item_id, $has_attributes)) {
                throw new Exception('Error updating item attribute flag.');
            }

            mysqli_commit($connection);
            return ['ok' => true, 'message' => 'Item unmapped successfully'];
        } catch (Exception $e) {
            mysqli_rollback($connection);
            return ['ok' => false, 'message' => $e->getMessage()];
        }
    }
}
