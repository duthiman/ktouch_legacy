<?php
$filename = __DIR__ . "/../../inc/gen_funcs.php";
if (file_exists($filename)) {
    include_once($filename);
} elseif (file_exists(__DIR__ . "/../inc/gen_funcs.php")) {
    include_once(__DIR__ . "/../inc/gen_funcs.php");
}
// Shared sale/session helpers are loaded by open/api/open_bill_bridge.php.
// This file owns the open-order registry and continuation workflows and does
// not import the kwedia runtime directly.

if (!function_exists('open_ensure_closed_opened_table')) {
    function open_ensure_closed_opened_table($connection) {
        mysqli_query($connection, "CREATE TABLE IF NOT EXISTS kp_sales_closed_opened (id INT NOT NULL AUTO_INCREMENT, original_sale_id INT NOT NULL, user_id INT NOT NULL, reopened_at TIMESTAMP NOT NULL DEFAULT CURRENT_TIMESTAMP, bill_mode VARCHAR(20) NOT NULL DEFAULT 'each', is_fresh_open TINYINT(1) NOT NULL DEFAULT 0, PRIMARY KEY (id), UNIQUE KEY uniq_original_sale_id (original_sale_id))");
    }
}
if (!function_exists('open_identifier_colors')) {
    function open_identifier_colors() {
        return [
            ['key' => 'green',    'label' => 'Green',    'hex' => '#61b846'],
            ['key' => 'orange',   'label' => 'Orange',   'hex' => '#f36c26'],
            ['key' => 'red',      'label' => 'Red',      'hex' => '#c5432f'],
            ['key' => 'yellow',   'label' => 'Yellow',   'hex' => '#d9a441'],
            ['key' => 'blue',     'label' => 'Blue',     'hex' => '#2e7ecb'],
            ['key' => 'teal',     'label' => 'Teal',     'hex' => '#1e8a83'],
            ['key' => 'purple',   'label' => 'Purple',   'hex' => '#814d8c'],
            ['key' => 'black',    'label' => 'Black',    'hex' => '#4d4d4d'],
            ['key' => 'pink',     'label' => 'Pink',     'hex' => '#d94f87'],
            ['key' => 'brown',    'label' => 'Brown',    'hex' => '#8b5a2b'],
            ['key' => 'navy',     'label' => 'Navy',     'hex' => '#234a8f'],
            ['key' => 'lime',     'label' => 'Lime',     'hex' => '#8bcf3f'],
            ['key' => 'maroon',   'label' => 'Maroon',   'hex' => '#8a1c3a'],
            ['key' => 'cyan',     'label' => 'Cyan',     'hex' => '#1fa7c9'],
            ['key' => 'silver',   'label' => 'Silver',   'hex' => '#8f98a3'],
        ];
    }
}

if (!function_exists('open_user_can_view_all')) {
    function open_user_can_view_all($job) {
        return in_array((string)$job, ['1', '3'], true);
    }
}

if (!function_exists('open_ensure_identifier_table')) {
    function open_ensure_identifier_table($connection) {
        mysqli_query(
            $connection,
            "CREATE TABLE IF NOT EXISTS kp_open_identifier_preset (
                id INT NOT NULL AUTO_INCREMENT,
                user_id INT NOT NULL DEFAULT 0,
                preset_name VARCHAR(80) NOT NULL,
                last_used_at TIMESTAMP NULL DEFAULT NULL,
                created_at TIMESTAMP NOT NULL DEFAULT CURRENT_TIMESTAMP,
                PRIMARY KEY (id),
                UNIQUE KEY uniq_user_preset (user_id, preset_name),
                KEY idx_user_last_used (user_id, last_used_at)
            )"
        );
    }
}

if (!function_exists('open_identifier_presets')) {
    function open_identifier_presets($connection, $user_id, $limit = 8) {
        open_ensure_identifier_table($connection);
        $items = [];
        $limit = (int)$limit;
        if ($limit < 1) {
            $limit = 8;
        }
        $query = mysqli_query(
            $connection,
            "SELECT preset_name
             FROM kp_open_identifier_preset
             WHERE user_id='" . (int)$user_id . "'
             ORDER BY COALESCE(last_used_at, created_at) DESC, id DESC
             LIMIT $limit"
        );
        if ($query) {
            while ($row = mysqli_fetch_assoc($query)) {
                $items[] = $row['preset_name'];
            }
        }
        return $items;
    }
}

if (!function_exists('open_format_identifier_value')) {
    function open_format_identifier_value($type, $value, $extra = []) {
        if ($type === 'color') {
            $label = isset($extra['label']) ? trim($extra['label']) : trim($value);
            return [
                'type' => 'color',
                'value' => trim($value),
                'label' => $label,
                'hex' => isset($extra['hex']) ? trim($extra['hex']) : '',
            ];
        }
        return [
            'type' => 'name',
            'value' => trim($value),
            'label' => trim($value),
            'hex' => '',
        ];
    }
}

if (!function_exists('open_column_exists')) {
    function open_column_exists($connection, $table, $column) {
        $table = mysqli_real_escape_string($connection, $table);
        $column = mysqli_real_escape_string($connection, $column);
        $query = mysqli_query($connection, "SHOW COLUMNS FROM `$table` LIKE '$column'");
        return $query && mysqli_num_rows($query) > 0;
    }
}

if (!function_exists('open_primary_key_columns')) {
    function open_primary_key_columns($connection, $table) {
        $table = mysqli_real_escape_string($connection, $table);
        $columns = [];
        $query = mysqli_query($connection, "SHOW KEYS FROM `$table` WHERE Key_name='PRIMARY'");
        if ($query) {
            while ($row = mysqli_fetch_assoc($query)) {
                $columns[] = $row['Column_name'];
            }
        }
        return $columns;
    }
}

if (!function_exists('open_ensure_parent_columns')) {
    function open_ensure_parent_columns($connection) {
        if (!open_column_exists($connection, 'kp_sale_status', 'parent')) {
            mysqli_query($connection, "ALTER TABLE kp_sale_status ADD COLUMN parent INT NOT NULL DEFAULT 0");
        }
        if (!open_column_exists($connection, 'kp_sales', 'parent')) {
            mysqli_query($connection, "ALTER TABLE kp_sales ADD COLUMN parent INT NOT NULL DEFAULT 0");
        }
        if (!open_column_exists($connection, 'kp_sales', 'is_child_bill')) {
            mysqli_query($connection, "ALTER TABLE kp_sales ADD COLUMN is_child_bill TINYINT(1) NOT NULL DEFAULT 0 AFTER parent");
        }
        if (!open_column_exists($connection, 'kp_sales', 'bill_role')) {
            mysqli_query($connection, "ALTER TABLE kp_sales ADD COLUMN bill_role VARCHAR(20) NOT NULL DEFAULT 'closed' AFTER is_child_bill");
        }
        if (!open_column_exists($connection, 'kp_sales', 'bill_status')) {
            mysqli_query($connection, "ALTER TABLE kp_sales ADD COLUMN bill_status TINYINT(1) NOT NULL DEFAULT 2 AFTER bill_role");
        }
        if (!open_column_exists($connection, 'kp_sales', 'root_parent_sale_id')) {
            mysqli_query($connection, "ALTER TABLE kp_sales ADD COLUMN root_parent_sale_id INT NOT NULL DEFAULT 0 AFTER bill_role");
        }
        if (!open_column_exists($connection, 'kp_sales', 'previous_sale_id')) {
            mysqli_query($connection, "ALTER TABLE kp_sales ADD COLUMN previous_sale_id INT NOT NULL DEFAULT 0 AFTER root_parent_sale_id");
        }
        if (!open_column_exists($connection, 'kp_sales', 'previous_balance')) {
            mysqli_query($connection, "ALTER TABLE kp_sales ADD COLUMN previous_balance DECIMAL(10,2) NOT NULL DEFAULT 0.00 AFTER previous_sale_id");
        }
        if (!open_column_exists($connection, 'kp_sales', 'current_total')) {
            mysqli_query($connection, "ALTER TABLE kp_sales ADD COLUMN current_total DECIMAL(10,2) NOT NULL DEFAULT 0.00 AFTER previous_balance");
        }
        if (!open_column_exists($connection, 'kp_sales', 'current_balance')) {
            mysqli_query($connection, "ALTER TABLE kp_sales ADD COLUMN current_balance DECIMAL(10,2) NOT NULL DEFAULT 0.00 AFTER current_total");
        }
        mysqli_query($connection, "UPDATE kp_sales SET bill_status=2 WHERE bill_status IS NULL OR bill_status=0");
    }
}

if (!function_exists('open_ensure_color_table')) {
    function open_ensure_color_table($connection) {
        mysqli_query(
            $connection,
            "CREATE TABLE IF NOT EXISTS kp_open_color (
                id INT NOT NULL AUTO_INCREMENT,
                color VARCHAR(20) NOT NULL,
                label VARCHAR(60) NOT NULL DEFAULT '',
                color_key VARCHAR(40) NOT NULL DEFAULT '',
                PRIMARY KEY (id),
                UNIQUE KEY uniq_color_key (color_key)
            )"
        );

        if (!open_column_exists($connection, 'kp_open_color', 'label')) {
            mysqli_query($connection, "ALTER TABLE kp_open_color ADD COLUMN label VARCHAR(60) NOT NULL DEFAULT ''");
        }
        if (!open_column_exists($connection, 'kp_open_color', 'color_key')) {
            mysqli_query($connection, "ALTER TABLE kp_open_color ADD COLUMN color_key VARCHAR(40) NOT NULL DEFAULT ''");
            mysqli_query($connection, "ALTER TABLE kp_open_color ADD UNIQUE KEY uniq_color_key (color_key)");
        }

        foreach (open_identifier_colors() as $color) {
            $color_key = mysqli_real_escape_string($connection, $color['key']);
            $hex = mysqli_real_escape_string($connection, $color['hex']);
            $label = mysqli_real_escape_string($connection, $color['label']);
            mysqli_query(
                $connection,
                "INSERT INTO kp_open_color(color, label, color_key)
                 VALUES('$hex', '$label', '$color_key')
                 ON DUPLICATE KEY UPDATE color=VALUES(color), label=VALUES(label)"
            );
        }
    }
}

if (!function_exists('open_ensure_sales_closed_opened_table')) {
    function open_ensure_sales_closed_opened_table($connection) {
        mysqli_query(
            $connection,
            "CREATE TABLE IF NOT EXISTS kp_sales_closed_opened (
                closed_open_id INT NOT NULL AUTO_INCREMENT,
                sale_id INT NOT NULL DEFAULT 0,
                user_id INT NOT NULL DEFAULT 0,
                color INT NOT NULL DEFAULT 0,
                identifier_type VARCHAR(20) NOT NULL DEFAULT 'color',
                identifier_value VARCHAR(120) NOT NULL DEFAULT '',
                identifier_label VARCHAR(120) NOT NULL DEFAULT '',
                identifier_hex VARCHAR(20) NOT NULL DEFAULT '',
                bill_mode VARCHAR(20) NOT NULL DEFAULT 'each',
                counter_id INT NOT NULL DEFAULT 0,
                print_cntr_id INT NOT NULL DEFAULT 0,
                shift VARCHAR(60) NOT NULL DEFAULT '',
                status TINYINT(1) NOT NULL DEFAULT 1,
                closed_at TIMESTAMP NULL DEFAULT NULL,
                created_at TIMESTAMP NOT NULL DEFAULT CURRENT_TIMESTAMP,
                PRIMARY KEY (closed_open_id),
                KEY idx_sale_id (sale_id),
                KEY idx_user_id (user_id),
                KEY idx_status (status)
            )"
        );

        if (!open_column_exists($connection, 'kp_sales_closed_opened', 'closed_open_id')) {
            $primary_columns = open_primary_key_columns($connection, 'kp_sales_closed_opened');
            if (count($primary_columns) > 0) {
                mysqli_query($connection, "ALTER TABLE kp_sales_closed_opened ADD COLUMN closed_open_id INT NOT NULL AUTO_INCREMENT UNIQUE FIRST");
            } else {
                mysqli_query($connection, "ALTER TABLE kp_sales_closed_opened ADD COLUMN closed_open_id INT NOT NULL AUTO_INCREMENT PRIMARY KEY FIRST");
            }
        }

        $columns = [
            'bill_mode' => "ALTER TABLE kp_sales_closed_opened ADD COLUMN bill_mode VARCHAR(20) NOT NULL DEFAULT 'each'",
            'print_cntr_id' => "ALTER TABLE kp_sales_closed_opened ADD COLUMN print_cntr_id INT NOT NULL DEFAULT 0",
            'status' => "ALTER TABLE kp_sales_closed_opened ADD COLUMN status TINYINT(1) NOT NULL DEFAULT 1",
            'closed_at' => "ALTER TABLE kp_sales_closed_opened ADD COLUMN closed_at TIMESTAMP NULL DEFAULT NULL",
            'created_at' => "ALTER TABLE kp_sales_closed_opened ADD COLUMN created_at TIMESTAMP NOT NULL DEFAULT CURRENT_TIMESTAMP",
        ];
        foreach ($columns as $column => $statement) {
            if (!open_column_exists($connection, 'kp_sales_closed_opened', $column)) {
                mysqli_query($connection, $statement);
            }
        }
    }
}

if (!function_exists('open_ensure_sales_open_table')) {
    function open_ensure_sales_open_table($connection) {
        mysqli_query(
            $connection,
            "CREATE TABLE IF NOT EXISTS kp_sales_open (
                open_id INT NOT NULL AUTO_INCREMENT,
                sale_id INT NOT NULL DEFAULT 0,
                user_id INT NOT NULL DEFAULT 0,
                color INT NOT NULL DEFAULT 0,
                identifier_type VARCHAR(20) NOT NULL DEFAULT 'color',
                identifier_value VARCHAR(120) NOT NULL DEFAULT '',
                identifier_label VARCHAR(120) NOT NULL DEFAULT '',
                identifier_hex VARCHAR(20) NOT NULL DEFAULT '',
                child_sale_id INT NOT NULL DEFAULT 0,
                last_bill INT NOT NULL DEFAULT 0,
                bill_count INT NOT NULL DEFAULT 0,
                parent_closed_open_id INT NOT NULL DEFAULT 0,
                counter_id INT NOT NULL DEFAULT 0,
                print_cntr_id INT NOT NULL DEFAULT 0,
                shift VARCHAR(60) NOT NULL DEFAULT '',
                bill_mode VARCHAR(20) NOT NULL DEFAULT 'last',
                open_amount DECIMAL(10,2) NOT NULL DEFAULT 0.00,
                status TINYINT(1) NOT NULL DEFAULT 1,
                created_at TIMESTAMP NOT NULL DEFAULT CURRENT_TIMESTAMP,
                PRIMARY KEY (open_id),
                KEY idx_sale_id (sale_id),
                KEY idx_parent_closed_open_id (parent_closed_open_id),
                KEY idx_user_shift (user_id, shift)
            )"
        );

        if (!open_column_exists($connection, 'kp_sales_open', 'open_id')) {
            $primary_columns = open_primary_key_columns($connection, 'kp_sales_open');
            if (count($primary_columns) > 0) {
                mysqli_query($connection, "ALTER TABLE kp_sales_open ADD COLUMN open_id INT NOT NULL AUTO_INCREMENT UNIQUE FIRST");
            } else {
                mysqli_query($connection, "ALTER TABLE kp_sales_open ADD COLUMN open_id INT NOT NULL AUTO_INCREMENT PRIMARY KEY FIRST");
            }
        }

        $columns = [
            'identifier_type' => "ALTER TABLE kp_sales_open ADD COLUMN identifier_type VARCHAR(20) NOT NULL DEFAULT 'color'",
            'identifier_value' => "ALTER TABLE kp_sales_open ADD COLUMN identifier_value VARCHAR(120) NOT NULL DEFAULT ''",
            'identifier_label' => "ALTER TABLE kp_sales_open ADD COLUMN identifier_label VARCHAR(120) NOT NULL DEFAULT ''",
            'identifier_hex' => "ALTER TABLE kp_sales_open ADD COLUMN identifier_hex VARCHAR(20) NOT NULL DEFAULT ''",
            'parent_closed_open_id' => "ALTER TABLE kp_sales_open ADD COLUMN parent_closed_open_id INT NOT NULL DEFAULT 0",
            'counter_id' => "ALTER TABLE kp_sales_open ADD COLUMN counter_id INT NOT NULL DEFAULT 0",
            'print_cntr_id' => "ALTER TABLE kp_sales_open ADD COLUMN print_cntr_id INT NOT NULL DEFAULT 0",
            'open_amount' => "ALTER TABLE kp_sales_open ADD COLUMN open_amount DECIMAL(10,2) NOT NULL DEFAULT 0.00",
            'bill_mode' => "ALTER TABLE kp_sales_open ADD COLUMN bill_mode VARCHAR(20) NOT NULL DEFAULT 'last'",
            'status' => "ALTER TABLE kp_sales_open ADD COLUMN status TINYINT(1) NOT NULL DEFAULT 1",
            'created_at' => "ALTER TABLE kp_sales_open ADD COLUMN created_at TIMESTAMP NOT NULL DEFAULT CURRENT_TIMESTAMP",
        ];
        foreach ($columns as $column => $statement) {
            if (!open_column_exists($connection, 'kp_sales_open', $column)) {
                mysqli_query($connection, $statement);
            }
        }

        mysqli_query($connection, "UPDATE kp_sales_open SET counter_id=0 WHERE counter_id IS NULL");
        mysqli_query($connection, "UPDATE kp_sales_open SET print_cntr_id=0 WHERE print_cntr_id IS NULL");
        mysqli_query($connection, "UPDATE kp_sales_open SET open_amount=0.00 WHERE open_amount IS NULL");
        mysqli_query($connection, "UPDATE kp_sales_open SET identifier_type='color' WHERE identifier_type=''");
        mysqli_query($connection, "UPDATE kp_sales_open SET identifier_value='' WHERE identifier_value IS NULL");
        mysqli_query($connection, "UPDATE kp_sales_open SET identifier_label='' WHERE identifier_label IS NULL");
        mysqli_query($connection, "UPDATE kp_sales_open SET identifier_hex='' WHERE identifier_hex IS NULL");
        mysqli_query($connection, "UPDATE kp_sales_open SET parent_closed_open_id=0 WHERE parent_closed_open_id IS NULL");
        mysqli_query($connection, "UPDATE kp_sales_open SET bill_mode='last' WHERE bill_mode IS NULL OR bill_mode=''");

        // A sale is one open workspace. Older versions allowed duplicate
        // rows because they matched on both sale_id and user_id. Keep the
        // newest row for each sale before adding the database-level guard.
        $duplicates = mysqli_query(
            $connection,
            "SELECT sale_id, MAX(open_id) AS keep_open_id
             FROM kp_sales_open
             WHERE sale_id > 0
             GROUP BY sale_id
             HAVING COUNT(*) > 1"
        );
        if ($duplicates) {
            while ($duplicate = mysqli_fetch_assoc($duplicates)) {
                $duplicate_sale_id = (int)$duplicate['sale_id'];
                $keep_open_id = (int)$duplicate['keep_open_id'];
                mysqli_query(
                    $connection,
                    "DELETE FROM kp_sales_open
                     WHERE sale_id='$duplicate_sale_id' AND open_id <> '$keep_open_id'"
                );
            }
        }

        $unique_index = mysqli_query(
            $connection,
            "SHOW INDEX FROM kp_sales_open WHERE Key_name='uniq_kp_sales_open_sale_id'"
        );
        if (!$unique_index || mysqli_num_rows($unique_index) === 0) {
            mysqli_query(
                $connection,
                "ALTER TABLE kp_sales_open ADD UNIQUE KEY uniq_kp_sales_open_sale_id (sale_id)"
            );
        }
    }
}

if (!function_exists('open_upsert_bill_registry_row')) {
    function open_upsert_bill_registry_row($connection, array $payload) {
        open_ensure_sales_open_table($connection);

        $sale_id = (int)($payload['sale_id'] ?? 0);
        $parent_sale_id = (int)($payload['parent_sale_id'] ?? 0);
        $parent_closed_open_id = (int)($payload['parent_closed_open_id'] ?? 0);
        $user_id = (int)($payload['user_id'] ?? 0);
        $color = (int)($payload['color'] ?? 0);
        $identifier_type = mysqli_real_escape_string($connection, (string)($payload['identifier_type'] ?? 'color'));
        $identifier_value = mysqli_real_escape_string($connection, (string)($payload['identifier_value'] ?? ''));
        $identifier_label = mysqli_real_escape_string($connection, (string)($payload['identifier_label'] ?? ''));
        $identifier_hex = mysqli_real_escape_string($connection, (string)($payload['identifier_hex'] ?? ''));
        $counter_id = (int)($payload['counter_id'] ?? 0);
        $print_cntr_id = (int)($payload['print_cntr_id'] ?? 0);
        $open_amount = round((float)($payload['open_amount'] ?? 0), 2);
        $shift = mysqli_real_escape_string($connection, (string)($payload['shift'] ?? ''));
        $bill_mode = mysqli_real_escape_string($connection, open_normalize_bill_mode($payload['bill_mode'] ?? 'last'));
        $status = (int)($payload['status'] ?? 1);

        if ($sale_id < 1 || $user_id < 1) {
            return;
        }

        if ($parent_closed_open_id < 1 && $parent_sale_id > 0) {
            $parent_closed_open_id = (int)get_column(
                'kp_sales_closed_opened',
                'closed_open_id',
                "sale_id='$parent_sale_id' AND user_id='$user_id' AND status=1"
            );
        }

        $existing = mysqli_query($connection, "SELECT open_id, user_id FROM kp_sales_open WHERE sale_id='$sale_id' LIMIT 1");
        if ($existing && mysqli_num_rows($existing) > 0) {
            $existing_row = mysqli_fetch_assoc($existing);
            if ((int)($existing_row['user_id'] ?? 0) !== $user_id) {
                return false;
            }
            mysqli_query(
                $connection,
                "UPDATE kp_sales_open
                 SET parent_closed_open_id='$parent_closed_open_id',
                     color='$color',
                     identifier_type='$identifier_type',
                     identifier_value='$identifier_value',
                     identifier_label='$identifier_label',
                     identifier_hex='$identifier_hex',
                     counter_id='$counter_id',
                     print_cntr_id='$print_cntr_id',
                     open_amount='$open_amount',
                     shift='$shift',
                     bill_mode='$bill_mode',
                     status='$status'
                 WHERE sale_id='$sale_id'"
            );
        } else {
            $inserted = mysqli_query(
                $connection,
                "INSERT INTO kp_sales_open
                 (sale_id, parent_closed_open_id, user_id,  counter_id, print_cntr_id, open_amount, shift, bill_mode, status)
                 VALUES
                 ('$sale_id', '$parent_closed_open_id', '$user_id',  '$counter_id', '$print_cntr_id', '$open_amount', '$shift', '$bill_mode', '$status')"
            );
            if (!$inserted) {
                return false;
            }
        }
        return true;
    }
}

if (!function_exists('open_parent_registry_row')) {
    function open_parent_registry_row($connection, $parent_sale_id, $user_id = 0) {
        open_ensure_sales_open_table($connection); //
        open_ensure_sales_closed_opened_table($connection);
        $parent_sale_id = (int)$parent_sale_id;
        if ($parent_sale_id < 1) {
            return null;
        }
        $user_filter = ((int)$user_id > 0) ? " AND user_id='" . (int)$user_id . "'" : '';
        $query = mysqli_query(
            $connection,
            "SELECT *
             FROM kp_sales_open
             WHERE sale_id='$parent_sale_id'$user_filter
             ORDER BY open_id DESC
             LIMIT 1"
        );
        if ($query && mysqli_num_rows($query) > 0) {
            return mysqli_fetch_assoc($query);
        }

        $closed_query = mysqli_query(
            $connection,
            "SELECT closed_open_id, sale_id, user_id, counter_id, print_cntr_id, shift, bill_mode, status
             FROM kp_sales_closed_opened
             WHERE sale_id='$parent_sale_id'$user_filter
             ORDER BY closed_open_id DESC
             LIMIT 1"
        );
        if (!$closed_query || mysqli_num_rows($closed_query) < 1) {
            return null;
        }

        $closed_row = mysqli_fetch_assoc($closed_query);
        $last_bill = (int)get_column('kp_sale_status', 'sale_id', "parent_sale_id='$parent_sale_id' AND user_id='" . (int)($closed_row['user_id'] ?? $user_id) . "' ORDER BY id DESC LIMIT 1");
        if ($last_bill < 1) {
            $last_bill = (int)get_column('kp_sales', 'sale_id', "parent='$parent_sale_id' ORDER BY sale_id DESC LIMIT 1");
        }
        if ($last_bill < 1) {
            $last_bill = $parent_sale_id;
        }

        $bill_count = (int)column_count('kp_sales', 'sale_id', "sale_id='$parent_sale_id' OR parent='$parent_sale_id'");
        if ($bill_count < 1) {
            $bill_count = 1;
        }

        return [
            'open_id' => 0,
            'closed_open_id' => (int)($closed_row['closed_open_id'] ?? 0),
            'sale_id' => $parent_sale_id,
            'parent_sale_id' => $parent_sale_id,
            'user_id' => (int)($closed_row['user_id'] ?? 0),
            'counter_id' => (int)($closed_row['counter_id'] ?? 0),
            'print_cntr_id' => (int)($closed_row['print_cntr_id'] ?? 0),
            'shift' => $closed_row['shift'] ?? '',
            'bill_mode' => $closed_row['bill_mode'] ?? 'each',
            'status' => (int)($closed_row['status'] ?? 1),
            'last_bill' => $last_bill,
            'bill_count' => $bill_count,
        ];
    }
}

if (!function_exists('open_normalize_bill_mode')) {
    function open_normalize_bill_mode($mode) {
        $mode = strtolower(trim((string)$mode)); //
        return $mode === 'last' ? 'last' : 'each';
    }
}

if (!function_exists('open_parent_bill_mode')) {
    function open_parent_bill_mode($connection, $parent_sale_id, $user_id = 0) {
        open_ensure_sales_closed_opened_table($connection);
        if (function_exists('repo_ensure_sale_status_schema')) {
            repo_ensure_sale_status_schema();
        }
        $parent_sale_id = (int)$parent_sale_id;
        if ($parent_sale_id < 1) {
            return 'each';
        }
        $where = "sale_id='$parent_sale_id'";
        if ((int)$user_id > 0) {
            $where .= " AND user_id='" . (int)$user_id . "'";
        }
        $mode = 'each';
        
        // First try to get from kp_sales_closed_opened (closed parents)
        $query = mysqli_query(
            $connection,
            "SELECT bill_mode
             FROM kp_sales_closed_opened
             WHERE $where
             LIMIT 1"
        );
        if ($query && mysqli_num_rows($query) > 0) {
            $row = mysqli_fetch_assoc($query);
            $mode = $row['bill_mode'];
        } else {
            $status_query = mysqli_query(
                $connection,
                "SELECT mode
                 FROM kp_sale_status
                 WHERE $where
                 LIMIT 1"
            );
            if ($status_query && mysqli_num_rows($status_query) > 0) {
                $status_row = mysqli_fetch_assoc($status_query);
                $status_mode = strtolower(trim((string)($status_row['mode'] ?? '')));
                if ($status_mode === 'last') {
                    $mode = 'last';
                } elseif ($status_mode === 'each' || $status_mode === 'fresh') {
                    $mode = 'each';
                }
            } else {
                // Safety fallback only for older records.
                $query = mysqli_query(
                    $connection,
                    "SELECT bill_mode
                     FROM kp_sales_open
                     WHERE $where
                     ORDER BY open_id DESC
                     LIMIT 1"
                );
                if ($query && mysqli_num_rows($query) > 0) {
                    $row = mysqli_fetch_assoc($query);
                    $mode = $row['bill_mode'];
                }
            }
        }
        return open_normalize_bill_mode($mode);
    }
}

if (!function_exists('open_color_id_for_identifier')) {
    function open_color_id_for_identifier($connection, $identifier) {
        open_ensure_color_table($connection); //
        if (($identifier['type'] ?? '') !== 'color') {
            return 0;
        }
        $color_key = mysqli_real_escape_string($connection, $identifier['value']);
        $query = mysqli_query($connection, "SELECT id FROM kp_open_color WHERE color_key='$color_key' LIMIT 1");
        if ($query && mysqli_num_rows($query) > 0) {
            $row = mysqli_fetch_assoc($query);
            return (int)$row['id'];
        }
        return 0;
    }
}

if (!function_exists('open_create_new_sale_id')) {
    function open_discard_active_draft($connection, $user_id, $sale_id) {
        $user_id = (int)$user_id; //
        $sale_id = (int)$sale_id;
        if ($user_id < 1 || $sale_id < 1) {
            return false;
        }

        $shift = get_column('kp_shift', 'shift', "1");
        $sum = (float)column_total("kp_sale_print", "total", "sale_id='$sale_id'");
        mysqli_query($connection, "INSERT INTO kp_cancelled(sale_id,user_id,amount,shift) VALUES('$sale_id','$user_id','$sum','$shift')");

        $get_items = mysqli_query(
            $connection,
            "SELECT item_id, attr_id, SUM(qty) AS qty, MIN(price) AS price, SUM(total) AS total, stock_counter
             FROM kp_sale_print
             WHERE sale_id='$sale_id'
             GROUP BY item_id, attr_id, stock_counter"
        );
        if ($get_items) {
            while ($gid = mysqli_fetch_assoc($get_items)) {
                $item_id = (int)$gid['item_id'];
                $cqty = (float)$gid['qty'];
                $price = (float)$gid['price'];
                $total = (float)$gid['total'];
                $counter = (int)$gid['stock_counter'];
                $qty_column = "cntr" . $counter . "_qty";

                mysqli_query(
                    $connection,
                    "INSERT INTO kp_cancelled_sale_items(sale_id,item_id,qty,price,total,cntr_id)
                     VALUES('$sale_id','$item_id','$cqty','$price','$total','$counter')"
                );

                $recipe = get_column('kp_items', 'recipe', "item_id='$item_id'");
                if ($recipe == 1) {
                    $get_recipe_items = mysqli_query($connection, "SELECT raw_id, qty FROM kp_recipe WHERE item_id='$item_id'");
                    while ($row = $get_recipe_items ? mysqli_fetch_assoc($get_recipe_items) : null) {
                        $raw_id = (int)$row['raw_id'];
                        $recipe_qty = (float)$row['qty'];
                        $qty = $cqty * $recipe_qty;
                        mysqli_query($connection, "UPDATE kp_items SET $qty_column=$qty_column+'$qty' WHERE item_id='$raw_id'");
                    }
                } else {
                    mysqli_query($connection, "UPDATE kp_items SET $qty_column=$qty_column+'$cqty' WHERE item_id='$item_id'");
                }
            }
        }

        mysqli_query($connection, "DELETE FROM kp_sale_items WHERE sale_id='$sale_id'");
        mysqli_query($connection, "DELETE FROM kp_stock_track WHERE sale_id='$sale_id'");
        mysqli_query($connection, "DELETE FROM kp_sale_print WHERE sale_id='$sale_id'");
        mysqli_query($connection, "DELETE FROM kp_sale_status WHERE sale_id='$sale_id' AND user_id='$user_id'");
        mysqli_query($connection, "UPDATE kp_thesion_front SET sale_id=0, status=0 WHERE user_id='$user_id'");
        logs("AUTO CANCELLED DRAFT|-|-|ORDER NO|$sale_id", "../logs");
        return true;
    }

    function open_create_new_sale_id($connection, $user_id) {
        $user_id = (int)$user_id;
        $active_query = mysqli_query($connection, "SELECT sale_id FROM kp_sale_status WHERE user_id='$user_id' AND status IN (2,3) LIMIT 1");
        if ($active_query && mysqli_num_rows($active_query) > 0) {
            $row = mysqli_fetch_assoc($active_query);
            $active_sale_id = (int)$row['sale_id'];
            $sale_item_count = (int)column_count('kp_sale_items', 'item_id', "sale_id='$active_sale_id'");
            $sale_print_count = (int)column_count('kp_sale_print', 'item_id', "sale_id='$active_sale_id'");
            if (($sale_item_count + $sale_print_count) > 0) {
                open_discard_active_draft($connection, $user_id, $active_sale_id);
            } else {
                mysqli_query($connection, "DELETE FROM kp_sale_status WHERE sale_id='$active_sale_id' AND user_id='$user_id'");
                mysqli_query($connection, "UPDATE kp_thesion_front SET sale_id=0, status=0 WHERE user_id='$user_id'");
            }
        }

        $get_order_r = mysqli_query($connection, "SELECT sale_id FROM kp_sale_id");
        if (!$get_order_r || mysqli_num_rows($get_order_r) < 1) {
            mysqli_query($connection, "INSERT INTO kp_sale_id(sale_id) VALUES(0)");
            $prev_sale_id = 0;
        } else {
            $row = mysqli_fetch_assoc($get_order_r);
            $prev_sale_id = (int)$row['sale_id'];
        }

        $sale_id = $prev_sale_id + 1;
        mysqli_query($connection, "INSERT INTO kp_sale_status(sale_id,user_id) VALUES('$sale_id','$user_id')");
        mysqli_query($connection, "UPDATE kp_sale_id SET sale_id='$sale_id'");

        return ['ok' => true, 'sale_id' => $sale_id];
    }
}

if (!function_exists('open_register_parent_bill')) {
    function open_register_parent_bill($connection, $user_id, $parent_sale_id, $identifier, $bill_mode = 'each', $counter_id = 0) {
        open_ensure_color_table($connection); //
        open_ensure_sales_open_table($connection);

        $user_id = (int)$user_id;
        $parent_sale_id = (int)$parent_sale_id;
        $parent_query = mysqli_query($connection, "SELECT sale_id, user_id, shift FROM kp_sales WHERE sale_id='$parent_sale_id' LIMIT 1");
        $parent_sale = $parent_query ? mysqli_fetch_assoc($parent_query) : null;
        // $debug_msg = "DEBUG open_register_parent_bill: parent_sale_id=$parent_sale_id, parent_sale found in kp_sales: " . ($parent_sale ? 'YES' : 'NO') . "\n";
        // file_put_contents(__DIR__ . '/../kwedia/place_sale_debug.log', $debug_msg, FILE_APPEND);
        // For 'last' mode, the bill might not be in kp_sales yet, so check kp_sale_print
        if (!$parent_sale) {
            $parent_print_query = mysqli_query($connection, "SELECT sale_id, user_id FROM kp_sale_print WHERE sale_id='$parent_sale_id' LIMIT 1");
            $parent_print = $parent_print_query ? mysqli_fetch_assoc($parent_print_query) : null;
            // $debug_msg = "DEBUG open_register_parent_bill: parent_print found in kp_sale_print: " . ($parent_print ? 'YES' : 'NO') . "\n";
            // file_put_contents(__DIR__ . '/../kwedia/place_sale_debug.log', $debug_msg, FILE_APPEND);
            if ($parent_print && (int)$parent_print['user_id'] === $user_id) {
                $parent_sale = ['sale_id' => $parent_sale_id, 'user_id' => $user_id, 'shift' => get_column('kp_shift', 'shift', "1")];
                // $debug_msg = "DEBUG open_register_parent_bill: Created parent_sale from kp_sale_print\n";
                // file_put_contents(__DIR__ . '/../kwedia/place_sale_debug.log', $debug_msg, FILE_APPEND);
            }
        }
        if (!$parent_sale || (int)$parent_sale['user_id'] !== $user_id) {
            // $debug_msg = "DEBUG open_register_parent_bill: FAILED - parent_sale not found or user mismatch\n";
            // file_put_contents(__DIR__ . '/../kwedia/place_sale_debug.log', $debug_msg, FILE_APPEND);
            return ['ok' => false, 'err' => 'You can only register your own bill as open.'];
        }
        $locked_user_id = kw_open_parent_owner($connection, $parent_sale_id);
        if ($locked_user_id > 0 && $locked_user_id !== $user_id) {
            return ['ok' => false, 'err' => kw_open_parent_locked_message($locked_user_id)];
        }

        // Flag the bill in kp_sales as being an active parent of a continuation chain
        mysqli_query($connection, "UPDATE kp_sales SET bill_role='parent' WHERE sale_id='$parent_sale_id'"); //

        $shift = mysqli_real_escape_string($connection, (string)$parent_sale['shift']);
        $color_id = open_color_id_for_identifier($connection, $identifier);
        $identifier_type = mysqli_real_escape_string($connection, $identifier['type']);
        $identifier_value = mysqli_real_escape_string($connection, $identifier['value']);
        $identifier_label = mysqli_real_escape_string($connection, $identifier['label']);
        $identifier_hex = mysqli_real_escape_string($connection, $identifier['hex']);
        $bill_mode = mysqli_real_escape_string($connection, open_normalize_bill_mode($bill_mode));
        $counter_id = (int)$counter_id;
        if ($counter_id < 1) {
            $counter_id = (int)($parent_sale['counter'] ?? 0);
        }
        $last_bill = (int)get_column('kp_sales_open', 'last_bill', "sale_id='$parent_sale_id' AND user_id='$user_id'");
        if ($last_bill < 1) {
            $last_bill = $parent_sale_id;
        }
        $bill_count = (int)column_count('kp_sales', 'sale_id', "parent='$parent_sale_id'");
        if ($bill_count < 1) {
            $bill_count = 1;
        }
        if ($bill_mode === 'each') {
            // For 'each' mode, try kp_sales first, fallback to kp_sale_items for fresh bills
            $parent_amount = (float)get_column('kp_sales', 'amount', "sale_id='$parent_sale_id'");
            if ($parent_amount == 0) {
                $parent_amount = (float)column_total('kp_sale_items', 'total', "sale_id='$parent_sale_id'");
            }
            $children_amount = (float)column_total('kp_sales', 'amount', "parent='$parent_sale_id'");
        } else {
            $parent_amount = (float)column_total('kp_sale_print', 'total', "sale_id='$parent_sale_id'");
            $children_amount = (float)column_total('kp_sale_print', 'total', "sale_id IN (SELECT sale_id FROM kp_sales WHERE parent='$parent_sale_id')");
        }
        $total_open_amount = round($parent_amount + $children_amount, 2);

        open_upsert_bill_registry_row($connection, [
            'sale_id' => $parent_sale_id,
            'parent_sale_id' => $parent_sale_id,
            'user_id' => $user_id,
            'child_sale_id' => 0,
            'color' => $color_id,
            'identifier_type' => $identifier['type'],
            'identifier_value' => $identifier['value'],
            'identifier_label' => $identifier['label'],
            'identifier_hex' => $identifier['hex'],
            'sale_role' => 'parent',
            'is_child_bill' => 0,
            'last_bill' => $last_bill,
            'bill_count' => $bill_count,
            'counter_id' => $counter_id,
            'open_amount' => $total_open_amount,
            'shift' => $shift,
            'bill_mode' => $bill_mode,
            'status' => 1,
        ]);

        $_SESSION['open_continue_draft'] = [
            'parent_sale_id' => $parent_sale_id,
            'type' => $identifier['type'],
            'value' => $identifier['value'],
            'label' => $identifier['label'],
            'hex' => $identifier['hex'],
            'bill_mode' => open_normalize_bill_mode($bill_mode),
            'counter_id' => $counter_id,
            'saved_at' => date('Y-m-d H:i:s'),
        ];

        return ['ok' => true, 'parent_sale_id' => $parent_sale_id];
    }
}

if (!function_exists('open_clear_bill_registry')) {
    function open_clear_bill_registry($connection, $sale_id, $user_id = 0) {
        open_ensure_sales_open_table($connection);
        open_ensure_sales_closed_opened_table($connection);
        $sale_id = (int)$sale_id;
        $user_id = (int)$user_id;
        if ($sale_id < 1) {
            return;
        }
        
        // Get the closed_open_id for this sale_id from kp_sales_closed_opened
        $closed_open_query = mysqli_query($connection, "SELECT closed_open_id FROM kp_sales_closed_opened WHERE sale_id='$sale_id' LIMIT 1");
        $closed_open = $closed_open_query ? mysqli_fetch_assoc($closed_open_query) : null;
        $closed_open_id = $closed_open ? (int)$closed_open['closed_open_id'] : 0;

        $candidate_ids = [$sale_id];

        $status_children_q = mysqli_query(
            $connection,
            "SELECT sale_id
             FROM kp_sale_status
             WHERE parent_sale_id='$sale_id'" . ($user_id > 0 ? " AND user_id='$user_id'" : '')
        );
        if ($status_children_q) {
            while ($row = mysqli_fetch_assoc($status_children_q)) {
                $child_id = (int)($row['sale_id'] ?? 0);
                if ($child_id > 0) {
                    $candidate_ids[] = $child_id;
                }
            }
        }

        $open_family_q = mysqli_query(
            $connection,
            "SELECT sale_id, child_sale_id, last_bill
             FROM kp_sales_open
             WHERE (sale_id='$sale_id' OR child_sale_id='$sale_id' OR last_bill='$sale_id')" . ($user_id > 0 ? " AND user_id='$user_id'" : '')
        );
        if ($open_family_q) {
            while ($row = mysqli_fetch_assoc($open_family_q)) {
                foreach (['sale_id', 'child_sale_id', 'last_bill'] as $field) {
                    $candidate_id = (int)($row[$field] ?? 0);
                    if ($candidate_id > 0) {
                        $candidate_ids[] = $candidate_id;
                    }
                }
            }
        }

        $candidate_ids = array_values(array_unique(array_filter(array_map('intval', $candidate_ids))));
        $id_list = implode("','", $candidate_ids);

        // Delete parent rows and any child rows tied to this parent.
        $where = "sale_id IN ('$id_list')";
        $where .= " OR child_sale_id IN ('$id_list')";
        $where .= " OR last_bill IN ('$id_list')";
        if ($closed_open_id > 0) {
            $where .= " OR parent_closed_open_id='$closed_open_id'";
        }
        if ($user_id > 0) {
            $where = "($where) AND user_id='$user_id'";
        }
        mysqli_query($connection, "DELETE FROM kp_sales_open WHERE $where");
    }
}

if (!function_exists('open_purge_stale_bill_registry')) {
    function open_purge_stale_bill_registry($connection, $user_id = 0) {
        open_ensure_sales_open_table($connection); //
        $user_id = (int)$user_id;
        $user_filter = $user_id > 0 ? " AND user_id='$user_id'" : '';
        $active_check = mysqli_query(
            $connection,
            "SELECT COUNT(*) AS active_count
             FROM kp_sale_status
             WHERE status IN (1, 2)"
        );
        $active_count = 0;
        if ($active_check && ($active_row = mysqli_fetch_assoc($active_check))) {
            $active_count = (int)($active_row['active_count'] ?? 0);
        }
        if ($active_count < 1) {
            return;
        }
        mysqli_query(
            $connection,
            "DELETE FROM kp_sales_open
             WHERE sale_id NOT IN (
                 SELECT sale_id
                 FROM kp_sale_status
                 WHERE status IN (1, 2)
             )$user_filter"
        );
    }
}

if (!function_exists('open_identifier_from_parent')) {
    function open_identifier_from_parent($connection, $parent_sale_id, $user_id = 0) {
        open_ensure_sales_closed_opened_table($connection);
        $parent_sale_id = (int)$parent_sale_id;
        $user_filter = $user_id > 0 ? " AND user_id='" . (int)$user_id . "'" : '';
        
        // First try to get from kp_sales_closed_opened (closed parents)
        $query = mysqli_query(
            $connection,
            "SELECT identifier_type, identifier_value, identifier_label, identifier_hex
             FROM kp_sales_closed_opened
             WHERE sale_id='$parent_sale_id'$user_filter
             LIMIT 1"
        );
        if ($query && mysqli_num_rows($query) > 0) {
            $row = mysqli_fetch_assoc($query);
            return [
                'type' => trim((string)$row['identifier_type']) ?: 'color',
                'value' => trim((string)$row['identifier_value']),
                'label' => trim((string)$row['identifier_label']),
                'hex' => trim((string)$row['identifier_hex']),
            ];
        }
        
        // Fallback to kp_sales_open (for backward compatibility)
        $query = mysqli_query(
            $connection,
            "SELECT identifier_type, identifier_value, identifier_label, identifier_hex
             FROM kp_sales_open
             WHERE sale_id='$parent_sale_id'$user_filter
             ORDER BY child_sale_id DESC, bill_count DESC
             LIMIT 1"
        );
        if ($query && mysqli_num_rows($query) > 0) {
            $row = mysqli_fetch_assoc($query);
            return [
                'type' => trim((string)$row['identifier_type']) ?: 'color',
                'value' => trim((string)$row['identifier_value']),
                'label' => trim((string)$row['identifier_label']),
                'hex' => trim((string)$row['identifier_hex']),
            ];
        }
        return null;
    }
}

if (!function_exists('open_parent_sale_owner_id')) {
    function open_parent_sale_owner_id($connection, $parent_sale_id) {
        $parent_sale_id = (int)$parent_sale_id; //
        if ($parent_sale_id < 1) {
            return 0;
        }
        $sale_owner = (int)get_column('kp_sales', 'user_id', "sale_id='$parent_sale_id'");
        if ($sale_owner > 0) {
            return $sale_owner;
        }
        $open_owner = (int)get_column('kp_sales_open', 'user_id', "sale_id='$parent_sale_id'");
        return $open_owner;
    }
}

if (!function_exists('open_is_parent_locked')) {
    /**
     * Checks if a bill is a parent in an active continuation chain.
     * Such bills should have their finalized items locked for editing.
     */
    function open_is_parent_locked($connection, $sale_id) {
        $sale_id = (int)$sale_id;
        if ($sale_id < 1) {
            return false;
        }
        $role = get_column('kp_sales', 'bill_role', "sale_id='$sale_id'");
        return $role === 'parent';
    }
}

if (!function_exists('open_start_child_sale')) {
    function open_start_child_sale($connection, $user_id, $parent_sale_id, $identifier, $counter_id = 0) {
        open_ensure_sales_closed_opened_table($connection);
        open_ensure_color_table($connection);
        open_ensure_sales_open_table($connection);

        $user_id = (int)$user_id;
        $parent_sale_id = (int)$parent_sale_id;
        
        // Get parent from kp_sales_closed_opened (closed parents)
        $parent_closed_open_query = mysqli_query($connection, "SELECT closed_open_id, user_id, counter_id, print_cntr_id, shift, color, identifier_type, identifier_value, identifier_label, identifier_hex, bill_mode FROM kp_sales_closed_opened WHERE sale_id='$parent_sale_id' AND user_id='$user_id' LIMIT 1");
        $parent_closed_open = $parent_closed_open_query ? mysqli_fetch_assoc($parent_closed_open_query) : null;
        
        // Fallback to kp_sales_open for open last mode bills
        $parent_open = null;
        if (!$parent_closed_open) {
            $parent_open_query = mysqli_query($connection, "SELECT open_id, user_id, counter_id, print_cntr_id, shift, color, identifier_type, identifier_value, identifier_label, identifier_hex, bill_mode FROM kp_sales_open WHERE sale_id='$parent_sale_id' AND user_id='$user_id' AND parent_closed_open_id=0 LIMIT 1");
            $parent_open = $parent_open_query ? mysqli_fetch_assoc($parent_open_query) : null;
        }
        
        if (!$parent_closed_open && !$parent_open) {
            return ['ok' => false, 'err' => 'The selected parent bill no longer exists.'];
        }
        
        $parent_closed_open_id = $parent_closed_open ? (int)$parent_closed_open['closed_open_id'] : 0;
        $parent_owner_id = $parent_closed_open ? (int)$parent_closed_open['user_id'] : (int)$parent_open['user_id'];
        $parent_info = $parent_closed_open ?: $parent_open;
        
        if ($parent_owner_id !== $user_id) {
            return ['ok' => false, 'err' => 'You can only continue bills that belong to your account.'];
        }
        $locked_user_id = kw_open_parent_owner($connection, $parent_sale_id);
        if ($locked_user_id > 0 && $locked_user_id !== $user_id) {
            return ['ok' => false, 'err' => kw_open_parent_locked_message($locked_user_id)];
        }
        $max_sale = kw_user_max_sale($connection, $user_id);
        if ($max_sale > 0) {
            $running_total = kw_parent_sale_chain_total($connection, $parent_sale_id, 0);
            if ($running_total >= $max_sale) {
                return ['ok' => false, 'err' => 'This continued bill chain is already at KES ' . number_format($running_total, 2) . ', which has reached your max sale of KES ' . number_format($max_sale, 2) . '.'];
            }
        }

        $counter_id = (int)$counter_id;
        if ($counter_id < 1) {
            $counter_id = (int)get_column('kp_users', 'counter', "user_id='$user_id'");
        }
        if ($counter_id < 1 && $parent_info) {
            $counter_id = (int)($parent_info['counter_id'] ?? 0);
        }
        if ($counter_id < 1) {
            return ['ok' => false, 'err' => 'Choose the counter you want to continue from.'];
        }

        $new_sale = open_create_new_sale_id($connection, $user_id);
        if (empty($new_sale['ok'])) {
            return $new_sale;
        }
        $child_sale_id = (int)$new_sale['sale_id'];

        mysqli_query(
            $connection,
            "UPDATE kp_sale_status
             SET parent='$parent_sale_id',
                 status=2,
                 mode='each',
                 parent_status=1,
                 parent_sale_id='$parent_sale_id'
             WHERE sale_id='$child_sale_id' AND user_id='$user_id'"
        );
        sales_thesion("front/kwedia/", $child_sale_id, $counter_id, 2);

        $shift = mysqli_real_escape_string($connection, (string)($parent_info['shift'] ?? get_column('kp_shift', 'shift', "1")));
        $color_id = open_color_id_for_identifier($connection, $identifier);
        $identifier_type = mysqli_real_escape_string($connection, $identifier['type']);
        $identifier_value = mysqli_real_escape_string($connection, $identifier['value']);
        $identifier_label = mysqli_real_escape_string($connection, $identifier['label']);
        $identifier_hex = mysqli_real_escape_string($connection, $identifier['hex']);

        $parent_mode = open_parent_bill_mode($connection, $parent_sale_id, $user_id);
        if ($parent_mode === 'each') {
            $parent_amount = (float)get_column('kp_sales', 'amount', "sale_id='$parent_sale_id'");
            $children_amount = (float)column_total('kp_sales', 'amount', "parent='$parent_sale_id'");
        } else {
            $parent_amount = (float)column_total('kp_sale_print', 'total', "sale_id='$parent_sale_id'");
            $children_amount = (float)column_total('kp_sale_print', 'total', "sale_id IN (SELECT sale_id FROM kp_sales WHERE parent='$parent_sale_id')");
        }
        $total_open_amount = round($parent_amount + $children_amount, 2);
        
        // Only live open-last workspaces should keep child rows in kp_sales_open.
        if ($parent_closed_open_id < 1 && $parent_mode === 'last') {
            $child_amount = (float)column_total('kp_sale_print', 'total', "sale_id='$child_sale_id'");
            open_upsert_bill_registry_row($connection, [
                'sale_id' => $child_sale_id,
                'parent_closed_open_id' => $parent_closed_open_id,
                'user_id' => $user_id,
                'color' => $color_id,
                'identifier_type' => $identifier['type'],
                'identifier_value' => $identifier['value'],
                'identifier_label' => $identifier['label'],
                'identifier_hex' => $identifier['hex'],
                'counter_id' => $counter_id,
                'print_cntr_id' => (int)($parent_info['print_cntr_id'] ?? 0),
                'open_amount' => round($child_amount, 2),
                'shift' => $shift,
                'bill_mode' => $parent_mode,
                'status' => 1,
            ]);
        }

        $_SESSION['open_continue_draft'] = [
            'parent_sale_id' => $parent_sale_id,
            'child_sale_id' => $child_sale_id,
            'type' => $identifier['type'],
            'value' => $identifier['value'],
            'label' => $identifier['label'],
            'hex' => $identifier['hex'],
            'bill_mode' => $parent_mode,
            'counter_id' => $counter_id,
            'saved_at' => date('Y-m-d H:i:s'),
        ];

        $action = "OPEN ORDER FLOW|-|-|PARENT|$parent_sale_id|CHILD|$child_sale_id|IDENTIFIER|" . strtoupper($identifier['label']) . "|COUNTER|$counter_id";
        logs($action, "../logs");

        return [
            'ok' => true,
            'parent_sale_id' => $parent_sale_id,
            'child_sale_id' => $child_sale_id,
            'counter_id' => $counter_id,
            'redirect' => '../kwedia/index.php?sale_id=' . $child_sale_id . '&sale_counter=' . $counter_id,
        ];
    }
}

if (!function_exists('open_resume_child_sale')) {
    function open_resume_child_sale($connection, $user_id, $parent_sale_id) {
        $identifier = open_identifier_from_parent($connection, $parent_sale_id, $user_id); //
        if (!$identifier) {
            return ['ok' => false, 'err' => 'No identifier was found for that parent bill.'];
        }
        return open_start_child_sale($connection, $user_id, $parent_sale_id, $identifier, 0);
    }
}

if (!function_exists('open_resume_parent_sale_for_last')) {
    function open_resume_last_child_sale($connection, $user_id, $parent_sale_id, $counter_id = 0) {
        open_ensure_sales_open_table($connection); //

        $user_id = (int)$user_id;
        $parent_sale_id = (int)$parent_sale_id;
        $counter_id = (int)$counter_id;
        if ($user_id < 1 || $parent_sale_id < 1) {
            return ['ok' => false, 'err' => 'Invalid parent bill selected.'];
        }

        $parent_open = open_parent_registry_row($connection, $parent_sale_id, $user_id);
        if (!$parent_open) {
            return ['ok' => false, 'err' => 'This bill is not currently marked as open.'];
        }

        $active_status_child_id = (int)get_column(
            'kp_sale_status',
            'sale_id',
            "user_id='$user_id' AND parent_sale_id='$parent_sale_id' AND parent_status=1 AND mode='last' AND status=3 ORDER BY sale_id DESC LIMIT 1"
        );

        $last_child_id = $active_status_child_id;
        if ($last_child_id < 1) {
            $last_child_id = (int)($parent_open['last_bill'] ?? 0);
        }
        if ($last_child_id < 1) {
            $last_child_id = $parent_sale_id;
        }

        $target_query = mysqli_query(
            $connection,
            "SELECT sale_id, user_id, counter
             FROM kp_sales
             WHERE sale_id='$last_child_id'
             LIMIT 1"
        );
        $target_sale = $target_query ? mysqli_fetch_assoc($target_query) : null;
        if ($target_sale && (int)$target_sale['user_id'] !== $user_id) {
            return ['ok' => false, 'err' => 'You can only continue bills that belong to your account.'];
        }

        $active_query = mysqli_query($connection, "SELECT sale_id FROM kp_sale_status WHERE user_id='$user_id' AND status=2 LIMIT 1");
        if ($active_query && mysqli_num_rows($active_query) > 0) {
            $row = mysqli_fetch_assoc($active_query);
            $active_sale_id = (int)$row['sale_id'];
            if ($active_sale_id !== $last_child_id) {
                $sale_item_count = (int)column_count('kp_sale_items', 'item_id', "sale_id='$active_sale_id'");
                $sale_print_count = (int)column_count('kp_sale_print', 'item_id', "sale_id='$active_sale_id'");
                if (($sale_item_count + $sale_print_count) > 0) {
                    open_discard_active_draft($connection, $user_id, $active_sale_id);
                } else {
                    mysqli_query($connection, "DELETE FROM kp_sale_status WHERE sale_id='$active_sale_id' AND user_id='$user_id'");
                    mysqli_query($connection, "UPDATE kp_thesion_front SET sale_id=0, status=0 WHERE user_id='$user_id'");
                }
            }
        }

        if ($counter_id < 1) {
            $counter_id = (int)($parent_open['counter_id'] ?? 0);
        }
        if ($counter_id < 1) {
            $counter_id = (int)($target_sale['counter'] ?? 0);
        }
        if ($counter_id < 1) {
            $counter_id = (int)get_column('kp_users', 'counter', "user_id='$user_id'");
        }
        if ($counter_id < 1) {
            return ['ok' => false, 'err' => 'Choose the counter you want to continue from.'];
        }

        mysqli_query($connection, "DELETE FROM kp_sale_status WHERE sale_id='$last_child_id' AND user_id='$user_id'");
        // Anything already on the resumed child belongs to the previous
        // ticket batch. New additions must remain a fresh delta, even when
        // they use the same item_id as an earlier line.
        mysqli_query($connection, "UPDATE kp_sale_print SET session_flag=1 WHERE sale_id='$last_child_id' AND session_flag=2");
        mysqli_query(
            $connection,
            "INSERT INTO kp_sale_status(sale_id,user_id,parent,status,mode,parent_status,parent_sale_id)
             VALUES('$last_child_id','$user_id','$parent_sale_id',3,'last',1,'$parent_sale_id')"
        );
        sales_thesion("front/kwedia/", $last_child_id, $counter_id, 2);

        $_SESSION['open_continue_draft'] = [
            'parent_sale_id' => $parent_sale_id,
            'child_sale_id' => $last_child_id,
            'bill_mode' => 'last',
            'counter_id' => $counter_id,
            'saved_at' => date('Y-m-d H:i:s'),
        ];

        logs("OPEN ORDER FLOW|-|-|RESUME OPEN LAST BILL|$last_child_id|PARENT|$parent_sale_id|COUNTER|$counter_id", "../logs");

        return [
            'ok' => true,
            'parent_sale_id' => $parent_sale_id,
            'child_sale_id' => $last_child_id,
            'counter_id' => $counter_id,
            'redirect' => '../kwedia/index.php?sale_id=' . $last_child_id . '&sale_counter=' . $counter_id,
        ];
    }

    function open_resume_parent_sale_for_last($connection, $user_id, $parent_sale_id, $counter_id = 0) {
        open_ensure_sales_open_table($connection);

        $user_id = (int)$user_id;
        $parent_sale_id = (int)$parent_sale_id;
        $counter_id = (int)$counter_id;
        if ($user_id < 1 || $parent_sale_id < 1) {
            return ['ok' => false, 'err' => 'Invalid parent bill selected.'];
        }
        
        $parent_open = open_parent_registry_row($connection, $parent_sale_id, $user_id);
        $parent_query = mysqli_query($connection, "SELECT sale_id, user_id, counter FROM kp_sales WHERE sale_id='$parent_sale_id' LIMIT 1");
        $parent_sale = $parent_query ? mysqli_fetch_assoc($parent_query) : null;
        $parent_owner_id = $parent_sale ? (int)$parent_sale['user_id'] : (int)($parent_open['user_id'] ?? 0);
        if ((!$parent_sale && !$parent_open) || $parent_owner_id !== $user_id) {
            return ['ok' => false, 'err' => 'You can only continue bills that belong to your account.'];
        }
        $locked_user_id = kw_open_parent_owner($connection, $parent_sale_id);
        if ($locked_user_id > 0 && $locked_user_id !== $user_id) {
            return ['ok' => false, 'err' => kw_open_parent_locked_message($locked_user_id)];
        }

        $active_query = mysqli_query($connection, "SELECT sale_id FROM kp_sale_status WHERE user_id='$user_id' AND status IN (2,3) LIMIT 1");
        if ($active_query && mysqli_num_rows($active_query) > 0) { //
            $row = mysqli_fetch_assoc($active_query);
            $active_sale_id = (int)$row['sale_id'];
            if ($active_sale_id !== $parent_sale_id) {
                $sale_item_count = (int)column_count('kp_sale_items', 'item_id', "sale_id='$active_sale_id'");
                $sale_print_count = (int)column_count('kp_sale_print', 'item_id', "sale_id='$active_sale_id'");
                if (($sale_item_count + $sale_print_count) > 0) {
                    open_discard_active_draft($connection, $user_id, $active_sale_id);
                } else {
                    mysqli_query($connection, "DELETE FROM kp_sale_status WHERE sale_id='$active_sale_id' AND user_id='$user_id'");
                    mysqli_query($connection, "UPDATE kp_thesion_front SET sale_id=0, status=0 WHERE user_id='$user_id'");
                }
            }
        }

        if ($counter_id < 1) {
            $counter_id = (int)($parent_open['counter_id'] ?? 0); //
        }
        if ($counter_id < 1 && $parent_sale) {
            $counter_id = (int)($parent_sale['counter'] ?? 0);
        }
        if ($counter_id < 1) {
            $counter_id = (int)get_column('kp_users', 'counter', "user_id='$user_id'");
        }
        if ($counter_id < 1) {
            return ['ok' => false, 'err' => 'Choose the counter you want to continue from.'];
        }

        $new_sale = open_create_new_sale_id($connection, $user_id);
        if (empty($new_sale['ok'])) {
            return $new_sale;
        }
        $child_sale_id = (int)$new_sale['sale_id'];

        mysqli_query(
            $connection,
            "UPDATE kp_sale_status
             SET parent='$parent_sale_id',
                 status=3,
                 mode='last',
                 parent_status=1,
                 parent_sale_id='$parent_sale_id'
             WHERE sale_id='$child_sale_id' AND user_id='$user_id'"
        ); //
        sales_thesion("front/kwedia/", $child_sale_id, $counter_id, 2);

        // Ensure parent record is flagged correctly
        mysqli_query($connection, "UPDATE kp_sales SET bill_role='parent' WHERE sale_id='$parent_sale_id'");

        $identifier = open_identifier_from_parent($connection, $parent_sale_id, $user_id) ?: [
            'type' => 'name',
            'value' => '',
            'label' => '',
            'hex' => '',
        ];
        $color_id = open_color_id_for_identifier($connection, $identifier);
        $shift = (string)($parent_open['shift'] ?? get_column('kp_shift', 'shift', "1"));
        $bill_count = (int)column_count('kp_sales', 'sale_id', "parent='$parent_sale_id'") + 1;

        // Update Parent Registry Entry
        mysqli_query(
            $connection,
            "UPDATE kp_sales_open 
             SET last_bill='$child_sale_id', 
                 bill_count='$bill_count',
                 status=1,
                 bill_mode='last'
             WHERE sale_id='$parent_sale_id' AND user_id='$user_id'"
        );

        // Register Child Entry
        open_upsert_bill_registry_row($connection, [
            'sale_id' => $child_sale_id,
            'parent_sale_id' => $parent_sale_id,
            'user_id' => $user_id,
            'color' => $color_id,
            'identifier_type' => $identifier['type'],
            'identifier_value' => $identifier['value'],
            'identifier_label' => $identifier['label'],
            'identifier_hex' => $identifier['hex'],
            'sale_role' => 'child',
            'is_child_bill' => 1,
            'last_bill' => $child_sale_id,
            'bill_count' => $bill_count,
            'counter_id' => $counter_id,
            'shift' => $shift,
            'bill_mode' => 'last',
            'status' => 1,
            'continuation_type' => 'continuation'
        ]);

        $_SESSION['open_continue_draft'] = [
            'parent_sale_id' => $parent_sale_id,
            'child_sale_id' => $child_sale_id,
            'bill_mode' => 'last',
            'counter_id' => $counter_id,
            'saved_at' => date('Y-m-d H:i:s'),
        ];
        
        logs("OPEN ORDER FLOW|-|-|REOPENED CLOSED BILL AS CHILD|$child_sale_id|PARENT|$parent_sale_id|COUNTER|$counter_id", "../logs");

        return [
            'ok' => true,
            'parent_sale_id' => $parent_sale_id,
            'child_sale_id' => $child_sale_id,
            'counter_id' => $counter_id,
            'redirect' => '../kwedia/index.php?sale_id=' . $child_sale_id . '&sale_counter=' . $counter_id,
        ];
    }
}

if (!function_exists('open_close_parent_bill')) {
    function open_close_parent_bill($connection, $user_id, $parent_sale_id) {
        open_ensure_sales_open_table($connection); //
        open_ensure_sales_closed_opened_table($connection);
        open_ensure_parent_columns($connection);

        $user_id = (int)$user_id;
        $parent_sale_id = (int)$parent_sale_id;
        if ($user_id < 1 || $parent_sale_id < 1) {
            return ['ok' => false, 'err' => 'Invalid parent bill selected.'];
        }

        $open_sale = open_parent_registry_row($connection, $parent_sale_id, $user_id);
        $sale_query = mysqli_query($connection, "SELECT sale_id, user_id FROM kp_sales WHERE sale_id='$parent_sale_id' LIMIT 1");
        $sale = $sale_query ? mysqli_fetch_assoc($sale_query) : null;
        $owner_id = $sale ? (int)$sale['user_id'] : (int)($open_sale['user_id'] ?? 0);
        if ((!$sale && !$open_sale) || $owner_id !== $user_id) {
            return ['ok' => false, 'err' => 'You can only close bills that belong to your account.'];
        }

        $locked_user_id = kw_open_parent_owner($connection, $parent_sale_id);
        if ($locked_user_id > 0 && $locked_user_id !== $user_id) {
            return ['ok' => false, 'err' => kw_open_parent_locked_message($locked_user_id)];
        }

        if (!$open_sale) {
            return ['ok' => false, 'err' => 'This bill is not currently marked as open.'];
        }
        if ((int)$open_sale['status'] !== 1) {
            return ['ok' => false, 'err' => 'This bill is already closed.'];
        }

        $last_bill = (int)($open_sale['last_bill'] ?? 0);
        if ($last_bill < 1) {
            $last_bill = $parent_sale_id;
        }
        $bill_count = (int)($open_sale['bill_count'] ?? 0);
        if ($bill_count < 1) {
            $bill_count = max(1, (int)column_count('kp_sales', 'sale_id', "sale_id='$parent_sale_id' OR parent='$parent_sale_id'"));
        }

        mysqli_query(
            $connection,
            "UPDATE kp_sales_open
             SET status=2,
                 last_bill='$last_bill',
                 bill_count='$bill_count'
             WHERE (sale_id='$parent_sale_id' OR parent_sale_id='$parent_sale_id') AND user_id='$user_id'"
        );

        mysqli_query(
            $connection,
            "DELETE FROM kp_sales_closed_opened
             WHERE sale_id='$parent_sale_id' AND user_id='$user_id'"
        );

        // Mark the entire family as finalized 'closed' in the main sales table
        mysqli_query($connection, "UPDATE kp_sales SET bill_role='closed' WHERE sale_id='$parent_sale_id' OR parent='$parent_sale_id'");
        mysqli_query($connection, "UPDATE kp_sales SET bill_status=2 WHERE sale_id='$parent_sale_id'");

        if (!empty($_SESSION['open_continue_draft']) && (int)($_SESSION['open_continue_draft']['parent_sale_id'] ?? 0) === $parent_sale_id) {
            unset($_SESSION['open_continue_draft']);
        }

        // DO NOT clear registry yet - close_parent.php needs it to find the counter/bill_mode for the final print.
        // Registry is now cleared at the very end of close_parent.php or via a stale purge.

        logs("OPEN ORDER FLOW|-|-|CLOSE PARENT|$parent_sale_id|LAST BILL|$last_bill|COUNT|$bill_count", "../logs");

        return [
            'ok' => true,
            'bill_mode' => $open_sale['bill_mode'],
            'parent_sale_id' => $parent_sale_id,
            'last_bill' => $last_bill,
            'bill_count' => $bill_count,
            'msg' => 'Bill #' . $parent_sale_id . ' was closed successfully.',
        ];
    } //
}

if (!function_exists('open_get_family_indicators')) {
    function open_get_family_indicators($connection, $parent_sale_id, $user_id) {
        $parent_sale_id = (int)$parent_sale_id;
        $user_id = (int)$user_id;

        // 1. Get Registry info
        $reg = open_parent_registry_row($connection, $parent_sale_id, $user_id);
        $bill_mode = open_normalize_bill_mode($reg['bill_mode'] ?? 'each');
        $last_bill_id = (int)($reg['last_bill'] ?? 0);
        $target_bill_id = ($last_bill_id > 0) ? $last_bill_id : $parent_sale_id;

        // 2. Sum Finalized Parts
        // Parent amount
        $parent_finalized = (float)get_column('kp_sales', 'amount', "sale_id='$parent_sale_id'");
        // All children linked to this parent
        $children_finalized = (float)column_total('kp_sales', 'amount', "parent='$parent_sale_id'");

        // 3. Sum Active Draft (items in the cart but not yet "Sent")
        // Only count draft if the target bill is NOT yet in kp_sales (meaning it's a current workspace)
        $active_is_finalized = (int)get_column('kp_sales', 'sale_id', "sale_id='$target_bill_id'") > 0;
        $draft_amount = $active_is_finalized ? 0 : (float)column_total('kp_sale_print', 'total', "sale_id='$target_bill_id'");

        return [
            'parent_finalized' => $parent_finalized,
            'children_finalized' => $children_finalized,
            'draft_amount' => $draft_amount,
            'family_total' => round($parent_finalized + $children_finalized + $draft_amount, 2),
            'bill_mode' => $bill_mode
        ];
    }
}
?>
