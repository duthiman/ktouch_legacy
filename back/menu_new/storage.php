<?php

function menu_new_data_dir() {
    return __DIR__ . DIRECTORY_SEPARATOR . 'data';
}

function menu_new_data_file() {
    return menu_new_data_dir() . DIRECTORY_SEPARATOR . 'items.json';
}

function menu_new_ensure_storage() {
    $dir = menu_new_data_dir();
    if (!is_dir($dir)) {
        mkdir($dir, 0777, true);
    }

    $file = menu_new_data_file();
    if (!file_exists($file)) {
        file_put_contents($file, json_encode(array(), JSON_PRETTY_PRINT));
    }
}

function menu_new_load_items() {
    menu_new_ensure_storage();
    $raw = file_get_contents(menu_new_data_file());
    $items = json_decode($raw, true);
    return is_array($items) ? $items : array();
}

function menu_new_save_items($items) {
    menu_new_ensure_storage();
    file_put_contents(
        menu_new_data_file(),
        json_encode(array_values($items), JSON_PRETTY_PRINT | JSON_UNESCAPED_SLASHES)
    );
}

function menu_new_next_id($items) {
    $max = 0;
    foreach ($items as $item) {
        $id = isset($item['draft_id']) ? (int)$item['draft_id'] : 0;
        if ($id > $max) {
            $max = $id;
        }
    }
    return $max + 1;
}

function menu_new_text($value, $default = '') {
    $value = is_string($value) ? trim($value) : '';
    return $value === '' ? $default : $value;
}

function menu_new_number($value, $default = 0) {
    return is_numeric($value) ? (float)$value : $default;
}

function menu_new_bool($value) {
    return !empty($value) && $value !== 'false' && $value !== '0' ? 1 : 0;
}
