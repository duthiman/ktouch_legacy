<?php
$genFuncsPath = realpath(__DIR__ . '/../../inc/gen_funcs.php');
if ($genFuncsPath && is_file($genFuncsPath)) {
    require_once $genFuncsPath;
}

if (!function_exists('ensure_chaba_print_system')) {
    function ensure_chaba_print_system($connection)
    {
        static $done = false;
        if ($done || !$connection) {
            return;
        }

        mysqli_query($connection, "CREATE TABLE IF NOT EXISTS kp_print_queue (
          queue_id INT NOT NULL AUTO_INCREMENT,
          sale_id INT NOT NULL DEFAULT 0,
          ref_id INT NOT NULL DEFAULT 0,
          print_type VARCHAR(55) NOT NULL DEFAULT 'bill',
          label VARCHAR(120) NOT NULL DEFAULT '',
          cntr_id INT NOT NULL DEFAULT 0,
          route_path VARCHAR(255) NOT NULL DEFAULT '',
          route_query TEXT NULL,
          requested_by INT NOT NULL DEFAULT 0,
          reprint_of INT NOT NULL DEFAULT 0,
          status TINYINT(1) NOT NULL DEFAULT 0,
          print_attempts TINYINT UNSIGNED NOT NULL DEFAULT 0,
          last_error VARCHAR(255) NOT NULL DEFAULT '',
          printed_at DATETIME NULL,
          created_at TIMESTAMP NOT NULL DEFAULT CURRENT_TIMESTAMP,
          PRIMARY KEY (queue_id),
          KEY idx_cntr_status (cntr_id, status),
          KEY idx_type_ref (print_type, ref_id),
          KEY idx_reprint (reprint_of)
        ) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4");

        mysqli_query($connection, "CREATE TABLE IF NOT EXISTS kp_print_reprints (
          reprint_id INT NOT NULL AUTO_INCREMENT,
          queue_id INT NOT NULL DEFAULT 0,
          source_queue_id INT NOT NULL DEFAULT 0,
          print_type VARCHAR(55) NOT NULL DEFAULT '',
          ref_id INT NOT NULL DEFAULT 0,
          cntr_id INT NOT NULL DEFAULT 0,
          requested_by INT NOT NULL DEFAULT 0,
          created_at TIMESTAMP NOT NULL DEFAULT CURRENT_TIMESTAMP,
          PRIMARY KEY (reprint_id),
          KEY idx_queue (queue_id),
          KEY idx_source_queue (source_queue_id)
        ) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4");

        $queueColumns = [
            'ref_id' => "ALTER TABLE kp_print_queue ADD COLUMN ref_id INT NOT NULL DEFAULT 0 AFTER sale_id",
            'label' => "ALTER TABLE kp_print_queue ADD COLUMN label VARCHAR(120) NOT NULL DEFAULT '' AFTER print_type",
            'route_path' => "ALTER TABLE kp_print_queue ADD COLUMN route_path VARCHAR(255) NOT NULL DEFAULT '' AFTER cntr_id",
            'route_query' => "ALTER TABLE kp_print_queue ADD COLUMN route_query TEXT NULL AFTER route_path",
            'requested_by' => "ALTER TABLE kp_print_queue ADD COLUMN requested_by INT NOT NULL DEFAULT 0 AFTER route_query",
            'reprint_of' => "ALTER TABLE kp_print_queue ADD COLUMN reprint_of INT NOT NULL DEFAULT 0 AFTER requested_by",
            'print_attempts' => "ALTER TABLE kp_print_queue ADD COLUMN print_attempts TINYINT UNSIGNED NOT NULL DEFAULT 0 AFTER status",
            'last_error' => "ALTER TABLE kp_print_queue ADD COLUMN last_error VARCHAR(255) NOT NULL DEFAULT '' AFTER print_attempts",
            'printed_at' => "ALTER TABLE kp_print_queue ADD COLUMN printed_at DATETIME NULL AFTER last_error",
            'created_at' => "ALTER TABLE kp_print_queue ADD COLUMN created_at TIMESTAMP NOT NULL DEFAULT CURRENT_TIMESTAMP AFTER printed_at",
        ];
        foreach ($queueColumns as $col => $sql) {
            $chk = mysqli_query($connection, "SHOW COLUMNS FROM kp_print_queue LIKE '$col'");
            if ($chk && mysqli_num_rows($chk) === 0) {
                mysqli_query($connection, $sql);
            }
        }
        $queuePrintTypeChk = mysqli_query($connection, "SHOW COLUMNS FROM kp_print_queue LIKE 'print_type'");
        if ($queuePrintTypeChk && ($queuePrintType = mysqli_fetch_assoc($queuePrintTypeChk))) {
            $queueType = strtolower((string)($queuePrintType['Type'] ?? ''));
            if (strpos($queueType, 'varchar(55)') === false) {
                mysqli_query($connection, "ALTER TABLE kp_print_queue CHANGE print_type print_type VARCHAR(55) CHARACTER SET utf8mb4 COLLATE utf8mb4_general_ci NOT NULL DEFAULT 'bill'");
            }
        }

        $reprintColumns = [
            'source_queue_id' => "ALTER TABLE kp_print_reprints ADD COLUMN source_queue_id INT NOT NULL DEFAULT 0 AFTER queue_id",
            'print_type' => "ALTER TABLE kp_print_reprints ADD COLUMN print_type VARCHAR(55) NOT NULL DEFAULT '' AFTER source_queue_id",
            'ref_id' => "ALTER TABLE kp_print_reprints ADD COLUMN ref_id INT NOT NULL DEFAULT 0 AFTER print_type",
            'cntr_id' => "ALTER TABLE kp_print_reprints ADD COLUMN cntr_id INT NOT NULL DEFAULT 0 AFTER ref_id",
            'requested_by' => "ALTER TABLE kp_print_reprints ADD COLUMN requested_by INT NOT NULL DEFAULT 0 AFTER cntr_id",
            'created_at' => "ALTER TABLE kp_print_reprints ADD COLUMN created_at TIMESTAMP NOT NULL DEFAULT CURRENT_TIMESTAMP AFTER requested_by",
        ];
        foreach ($reprintColumns as $col => $sql) {
            $chk = mysqli_query($connection, "SHOW COLUMNS FROM kp_print_reprints LIKE '$col'");
            if ($chk && mysqli_num_rows($chk) === 0) {
                mysqli_query($connection, $sql);
            }
        }
        $reprintTypeChk = mysqli_query($connection, "SHOW COLUMNS FROM kp_print_reprints LIKE 'print_type'");
        if ($reprintTypeChk && ($reprintType = mysqli_fetch_assoc($reprintTypeChk))) {
            $reprintTypeDef = strtolower((string)($reprintType['Type'] ?? ''));
            if (strpos($reprintTypeDef, 'varchar(55)') === false) {
                mysqli_query($connection, "ALTER TABLE kp_print_reprints CHANGE print_type print_type VARCHAR(55) CHARACTER SET utf8mb4 COLLATE utf8mb4_general_ci NOT NULL DEFAULT ''");
            }
        }

        $queueIndexes = [
            'idx_cntr_status' => "ALTER TABLE kp_print_queue ADD KEY idx_cntr_status (cntr_id, status)",
            'idx_type_ref' => "ALTER TABLE kp_print_queue ADD KEY idx_type_ref (print_type, ref_id)",
            'idx_reprint' => "ALTER TABLE kp_print_queue ADD KEY idx_reprint (reprint_of)",
        ];
        foreach ($queueIndexes as $indexName => $sql) {
            $idxChk = mysqli_query($connection, "SHOW INDEX FROM kp_print_queue WHERE Key_name='$indexName'");
            if ($idxChk && mysqli_num_rows($idxChk) === 0) {
                mysqli_query($connection, $sql);
            }
        }
        $reprintIndexes = [
            'idx_queue' => "ALTER TABLE kp_print_reprints ADD KEY idx_queue (queue_id)",
            'idx_source_queue' => "ALTER TABLE kp_print_reprints ADD KEY idx_source_queue (source_queue_id)",
        ];
        foreach ($reprintIndexes as $indexName => $sql) {
            $idxChk = mysqli_query($connection, "SHOW INDEX FROM kp_print_reprints WHERE Key_name='$indexName'");
            if ($idxChk && mysqli_num_rows($idxChk) === 0) {
                mysqli_query($connection, $sql);
            }
        }

        $printerChk = mysqli_query($connection, "SHOW COLUMNS FROM kp_counters LIKE 'printer_name'");
        if ($printerChk && mysqli_num_rows($printerChk) === 0) {
            mysqli_query($connection, "ALTER TABLE kp_counters ADD COLUMN printer_name VARCHAR(100) NOT NULL DEFAULT ''");
        }

        $done = true;
    }
}

if (!function_exists('kp_print_mode_uses_queue')) {
    function kp_print_mode_uses_queue($mode = null)
    {
        if ($mode === null && function_exists('get_print_mode')) {
            $mode = (int)get_print_mode();
        }
        return in_array((int)$mode, [2, 3, 4], true);
    }
}

if (!function_exists('kp_chaba_open_bill_mode_normalize')) {
    function kp_chaba_open_bill_mode_normalize($mode)
    {
        $mode = strtolower(trim((string)$mode));
        return $mode === 'last' ? 'last' : 'each';
    }
}

if (!function_exists('kp_chaba_bill_context')) {
    function kp_chaba_bill_context($connection, $sale_id, array $sale = [], $open_state = '')
    {
        if (function_exists('open_ensure_parent_columns')) {
            open_ensure_parent_columns($connection);
        }
        if (function_exists('open_ensure_sales_open_table')) {
            open_ensure_sales_open_table($connection);
        }

        $sale_id = (int)$sale_id;
        $parent_bill = (int)($sale['parent'] ?? 0);
        $is_child_bill = !empty($sale['is_child_bill']) ? 1 : 0;
        $has_parent_sale_col = false;
        $has_sales_open_table = false;
        $closed_parent_final = false;
        $open_display_bill_no = 0;
        $previous_bill_sale_id = 0;
        $root_parent_sale_id = (int)($sale['root_parent_sale_id'] ?? 0);
        $previous_balance = isset($sale['previous_balance']) ? (float)$sale['previous_balance'] : 0.0;
        $current_total = isset($sale['current_total']) && (float)$sale['current_total'] > 0 ? (float)$sale['current_total'] : (float)($sale['amount'] ?? 0);
        $current_balance = isset($sale['current_balance']) && (float)$sale['current_balance'] > 0 ? (float)$sale['current_balance'] : $current_total;

        if ($is_child_bill === 1 && $parent_bill <= 0) {
            $parent_bill = (int)get_column('kp_sales', 'parent', "sale_id='$sale_id'");
        }

        $parent_sale_col = mysqli_query($connection, "SHOW COLUMNS FROM kp_sales LIKE 'parent_sale'");
        if ($parent_sale_col && mysqli_num_rows($parent_sale_col) > 0) {
            $has_parent_sale_col = true;
            if ($parent_bill <= 0) {
                $parent_bill = (int)($sale['parent_sale'] ?? 0);
                if ($parent_bill <= 0) {
                    $parent_bill = (int)get_column('kp_sales', 'parent_sale', "sale_id='$sale_id'");
                }
            }
        }

        $sales_open_table = mysqli_query($connection, "SHOW TABLES LIKE 'kp_sales_open'");
        if ($sales_open_table && mysqli_num_rows($sales_open_table) > 0) {
            $has_sales_open_table = true;
            if ($parent_bill <= 0) {
                $open_lookup = mysqli_query(
                    $connection,
                    "SELECT sale_id, parent_sale_id, status, last_bill
                     FROM kp_sales_open
                     WHERE last_bill='$sale_id' OR child_sale_id='$sale_id' OR sale_id='$sale_id'
                     ORDER BY open_id DESC
                     LIMIT 1"
                );
                if ($open_lookup && mysqli_num_rows($open_lookup) > 0) {
                    $open_match = mysqli_fetch_assoc($open_lookup);
                    $parent_bill = (int)($open_match['parent_sale_id'] ?? 0);
                    if ($parent_bill < 1) {
                        $parent_bill = (int)($open_match['sale_id'] ?? 0);
                    }
                }
            }
        }

        if ($parent_bill <= 0 && $has_sales_open_table) {
            $closed_parent_q = mysqli_query($connection, "SELECT sale_id, last_bill, bill_count, status, bill_mode FROM kp_sales_open WHERE sale_id='$sale_id' LIMIT 1");
            if ($closed_parent_q && mysqli_num_rows($closed_parent_q) > 0) {
                $closed_parent = mysqli_fetch_assoc($closed_parent_q);
                if ((int)($closed_parent['status'] ?? 0) === 2 && kp_chaba_open_bill_mode_normalize($closed_parent['bill_mode'] ?? 'each') === 'last') {
                    $parent_bill = $sale_id;
                    $open_state = 'last';
                    $closed_parent_final = true;
                    $open_display_bill_no = (int)($closed_parent['bill_count'] ?? 1);
                    if ($open_display_bill_no < 1) {
                        $open_display_bill_no = max(1, (int)column_count('kp_sales', 'sale_id', "sale_id='$sale_id' OR parent='$sale_id'"));
                    }
                }
            }
        }

        if ($parent_bill > 0 && $has_sales_open_table) {
            $open_row = mysqli_query(
                $connection,
                "SELECT status, last_bill, bill_count, sale_id, child_sale_id
                 FROM kp_sales_open
                 WHERE parent_sale_id='$parent_bill' OR sale_id='$parent_bill'
                 ORDER BY open_id DESC
                 LIMIT 1"
            );
            if ($open_row && mysqli_num_rows($open_row) > 0) {
                $open_data = mysqli_fetch_assoc($open_row);
                $open_state = ((int)($open_data['status'] ?? 1) === 2 && (int)($open_data['last_bill'] ?? 0) === $sale_id) ? 'last' : 'continue';
                if ($sale_id === $parent_bill && $open_display_bill_no < 1) {
                    $open_display_bill_no = max(1, (int)($open_data['bill_count'] ?? 0));
                }
            }
        }

        if ($root_parent_sale_id <= 0) {
            $root_parent_sale_id = $parent_bill > 0 ? $parent_bill : $sale_id;
        }
        if ($sale_id === $root_parent_sale_id) {
            $open_display_bill_no = 1;
        }
        if ($sale_id !== $root_parent_sale_id) {
            $position_q = mysqli_query(
                $connection,
                "SELECT COUNT(*) AS total_bills
                 FROM kp_sales
                 WHERE sale_id='$root_parent_sale_id'
                    OR (parent='$root_parent_sale_id' AND sale_id<='$sale_id')"
            );
            if ($position_q && ($position_row = mysqli_fetch_assoc($position_q))) {
                $position = (int)($position_row['total_bills'] ?? 0);
                if ($position > 1) {
                    $open_display_bill_no = $position;
                }
            }

            if ($previous_bill_sale_id < 1) {
                $previous_bill_q = mysqli_query(
                    $connection,
                    "SELECT sale_id
                     FROM kp_sales
                     WHERE sale_id='$root_parent_sale_id'
                        OR (parent='$root_parent_sale_id' AND sale_id<'$sale_id')
                     ORDER BY sale_id DESC
                     LIMIT 1"
                );
                if ($previous_bill_q && ($previous_bill_row = mysqli_fetch_assoc($previous_bill_q))) {
                    $previous_bill_sale_id = (int)($previous_bill_row['sale_id'] ?? 0);
                }
            }
            if ($open_display_bill_no < 2 && $previous_bill_sale_id > 0) {
                $fallback_position_q = mysqli_query(
                    $connection,
                    "SELECT COUNT(*) AS total_bills
                     FROM kp_sales
                     WHERE sale_id='$root_parent_sale_id'
                        OR (parent='$root_parent_sale_id' AND sale_id<='$previous_bill_sale_id')"
                );
                if ($fallback_position_q && ($fallback_position_row = mysqli_fetch_assoc($fallback_position_q))) {
                    $fallback_position = (int)($fallback_position_row['total_bills'] ?? 0);
                    if ($fallback_position > 0) {
                        $open_display_bill_no = $fallback_position + 1;
                    }
                }
            }
        }
        if ($sale_id !== $root_parent_sale_id && $open_display_bill_no < 2) {
            $open_display_bill_no = 2;
        }
        if ($sale_id !== $root_parent_sale_id) {
            if ($previous_bill_sale_id < 1) {
                $previous_bill_sale_id = $parent_bill > 0 ? $parent_bill : 0;
            }
            if ($previous_balance <= 0) {
                $previous_balance = (float)get_column('kp_sales', 'amount', "sale_id='$root_parent_sale_id'");
                if ($sale_id !== $root_parent_sale_id) {
                    $previous_balance += (float)column_total('kp_sales', 'amount', "parent='$root_parent_sale_id' AND sale_id<'$sale_id'");
                }
            }
            if ($current_total <= 0) {
                $current_total = (float)($sale['amount'] ?? 0);
            }
            if ($current_balance <= 0) {
                $current_balance = $previous_balance + $current_total;
            }
        } else {
            $previous_bill_sale_id = 0;
            $previous_balance = 0.0;
            if ($current_total <= 0) {
                $current_total = (float)($sale['amount'] ?? 0);
            }
            if ($current_balance <= 0) {
                $current_balance = $current_total;
            }
        }

        return [
            'parent_bill' => $parent_bill,
            'is_child_bill' => $is_child_bill,
            'has_parent_sale_col' => $has_parent_sale_col,
            'has_sales_open_table' => $has_sales_open_table,
            'open_state' => $open_state,
            'closed_parent_final' => $closed_parent_final,
            'open_display_bill_no' => $open_display_bill_no,
            'previous_bill_sale_id' => $previous_bill_sale_id,
            'root_parent_sale_id' => $root_parent_sale_id,
            'previous_balance' => round($previous_balance, 2),
            'current_total' => round($current_total, 2),
            'current_balance' => round($current_balance, 2),
        ];
    }
}

if (!function_exists('kp_qz_fit_settings')) {
    function kp_qz_fit_settings($connection)
    {
        static $cached = null;
        if ($cached !== null) {
            return $cached;
        }

        $defaults = [
            'paper_width_mm' => 80,
            'margin_mm' => 0,
            'content_scale_pct' => 80,
        ];

        if (!$connection) {
            $cached = $defaults;
            return $cached;
        }

        $columns = [
            'qz_paper_width_mm' => "ALTER TABLE kp_settings_print ADD COLUMN qz_paper_width_mm DECIMAL(6,2) NOT NULL DEFAULT 80.00",
            'qz_margin_mm' => "ALTER TABLE kp_settings_print ADD COLUMN qz_margin_mm DECIMAL(6,2) NOT NULL DEFAULT 0.00",
            'qz_scale_pct' => "ALTER TABLE kp_settings_print ADD COLUMN qz_scale_pct DECIMAL(6,2) NOT NULL DEFAULT 80.00",
        ];
        foreach ($columns as $col => $sql) {
            $chk = mysqli_query($connection, "SHOW COLUMNS FROM kp_settings_print LIKE '$col'");
            if ($chk && mysqli_num_rows($chk) === 0) {
                mysqli_query($connection, $sql);
            }
        }

        $row = mysqli_fetch_assoc(mysqli_query($connection, "SELECT qz_paper_width_mm, qz_margin_mm, qz_scale_pct FROM kp_settings_print WHERE print_doc=1 LIMIT 1")) ?: [];
        $paper = (float)($row['qz_paper_width_mm'] ?? $defaults['paper_width_mm']);
        $margin = (float)($row['qz_margin_mm'] ?? $defaults['margin_mm']);
        $scale = (float)($row['qz_scale_pct'] ?? $defaults['content_scale_pct']);

        if ($paper < 50) { $paper = 80; }
        if ($paper > 120) { $paper = 120; }
        if ($margin < 0) { $margin = 0; }
        if ($margin > 10) { $margin = 10; }
        if ($scale < 40) { $scale = 40; }
        if ($scale > 120) { $scale = 120; }

        $cached = [
            'paper_width_mm' => $paper,
            'margin_mm' => $margin,
            'content_scale_pct' => $scale,
        ];
        return $cached;
    }
}

if (!function_exists('kp_print_job_preview_url')) {
    function kp_print_job_preview_url(array $job)
    {
        $routePath = trim((string)($job['route_path'] ?? ''));
        $routeQuery = trim((string)($job['route_query'] ?? ''));
        if ($routePath !== '') {
            return $routePath . ($routeQuery !== '' ? ((strpos($routePath, '?') === false ? '?' : '&') . $routeQuery) : '');
        }

        $type = strtolower(trim((string)($job['print_type'] ?? '')));
        $saleId = (int)($job['sale_id'] ?? 0);
        $refId = (int)($job['ref_id'] ?? 0);
        $base = '../chaba/';

        if ($type === 'bill' && $saleId > 0) return $base . 'render_bill.php?sale_id=' . $saleId;
        if ($type === 'cashier_order' && $saleId > 0) return $base . 'render_cashier_order.php?sale_id=' . $saleId;
        if ($type === 'bot' && $saleId > 0) return $base . 'render_bot.php?sale_id=' . $saleId;
        if ($type === 'kot' && $saleId > 0) return $base . 'render_kot.php?sale_id=' . $saleId;
        if ($type === 'got' && $saleId > 0) return $base . 'render_got.php?sale_id=' . $saleId;
        if ($type === 'ret' && $saleId > 0) return $base . 'render_ret.php?sale_id=' . $saleId;
        if ($type === 'kug' && $saleId > 0) return $base . 'render_kug.php?sale_id=' . $saleId;
        if ($type === 'gwitia' && $refId > 0) return $base . 'render_req.php?id=' . $refId;
        if ($type === 'trans' && $refId > 0) return $base . 'render_trans.php?trans_id=' . $refId;
        if ($type === 'tots' && $refId > 0) return $base . 'render_tots.php?id=' . $refId;
        if ($type === 'variance' && $refId > 0) return $base . 'render_variance.php?cntr_id=' . $refId;
        if (($type === 'summary' || $type === 'shift_sum') && $refId > 0) return $base . 'render_shift_sum.php?id=' . $refId;
        if ($type === 'staff_sum' && $refId > 0) return $base . 'render_staff_summary.php?id=' . $refId;
        if ($type === 'counter_sum' && $refId > 0) return $base . 'render_counter_sum.php?summary_id=' . $refId;
        if ($type === 'cust_credit_pay' && $refId > 0) return $base . 'render_customer_credit_pay.php?credit_pay_id=' . $refId;

        return '';
    }
}

if (!function_exists('kp_print_fetch_counter_printer_name')) {
    function kp_print_fetch_counter_printer_name($connection, $cntr_id)
    {
        $cntr_id = (int)$cntr_id;
        if (!$connection || $cntr_id < 1) {
            return '';
        }
        $q = mysqli_query($connection, "SELECT printer_name FROM kp_counters WHERE cntr_id = '$cntr_id' LIMIT 1");
        if (!$q || mysqli_num_rows($q) < 1) {
            return '';
        }
        $row = mysqli_fetch_assoc($q);
        return trim((string)($row['printer_name'] ?? ''));
    }
}

if (!function_exists('kp_print_fetch_registry_printer_name')) {
    function kp_print_fetch_registry_printer_name($connection, $role, $cntr_id = 0)
    {
        $role = trim((string)$role);
        $cntr_id = (int)$cntr_id;
        if (!$connection || $role === '') {
            return '';
        }

        $where = "role = '" . mysqli_real_escape_string($connection, $role) . "'";
        if ($cntr_id > 0) {
            $where .= " AND (cntr_id = '$cntr_id' OR cntr_id = 0)";
        }

        $q = mysqli_query(
            $connection,
            "SELECT windows_name
             FROM kp_print_printers
             WHERE $where
             ORDER BY CASE WHEN cntr_id = '$cntr_id' THEN 0 ELSE 1 END, sort_order ASC, prn_id ASC
             LIMIT 1"
        );
        if (!$q || mysqli_num_rows($q) < 1) {
            return '';
        }
        $row = mysqli_fetch_assoc($q);
        return trim((string)($row['windows_name'] ?? ''));
    }
}

if (!function_exists('kp_print_resolve_printer_name')) {
    function kp_print_resolve_printer_name($connection, $print_type, $cntr_id)
    {
        ensure_chaba_print_system($connection);
        $type = strtolower(trim((string)$print_type));
        $cntr_id = (int)$cntr_id;

        if ($type === 'cashier_order') {
            $cashierPrinter = kp_print_fetch_registry_printer_name($connection, 'cashier_order', $cntr_id);
            if ($cashierPrinter !== '') {
                return $cashierPrinter;
            }
        }

        return kp_print_fetch_counter_printer_name($connection, $cntr_id);
    }
}

if (!function_exists('kp_print_query_string')) {
    function kp_print_query_string(array $params)
    {
        return http_build_query($params, '', '&', PHP_QUERY_RFC3986);
    }
}

if (!function_exists('kp_print_enqueue')) {
    function kp_print_enqueue($connection, array $job)
    {
        ensure_chaba_print_system($connection);
        $sale_id = (int)($job['sale_id'] ?? 0);
        $ref_id = (int)($job['ref_id'] ?? $sale_id);
        $type = mysqli_real_escape_string($connection, trim((string)($job['print_type'] ?? 'bill')));
        $label = mysqli_real_escape_string($connection, trim((string)($job['label'] ?? strtoupper($type))));
        $cntr_id = (int)($job['cntr_id'] ?? 0);
        $route_path = mysqli_real_escape_string($connection, trim((string)($job['route_path'] ?? '')));
        $route_query = mysqli_real_escape_string($connection, trim((string)($job['route_query'] ?? '')));
        $requested_by = (int)($job['requested_by'] ?? 0);
        $reprint_of = (int)($job['reprint_of'] ?? 0);

        mysqli_query(
            $connection,
            "INSERT INTO kp_print_queue
            (sale_id, ref_id, print_type, label, cntr_id, route_path, route_query, requested_by, reprint_of)
            VALUES
            ('$sale_id', '$ref_id', '$type', '$label', '$cntr_id', '$route_path', '$route_query', '$requested_by', '$reprint_of')"
        );

        return (int)mysqli_insert_id($connection);
    }
}

if (!function_exists('kp_print_counter_select_url')) {
    function kp_print_counter_select_url($type, $ref_id, $return_to = '', array $extra = [])
    {
        $params = array_merge($extra, [
            'type' => $type,
            'id' => (int)$ref_id,
        ]);
        if ($return_to !== '') {
            $params['return_to'] = $return_to;
        }
        return '../chaba/receipt_counter.php?' . kp_print_query_string($params);
    }
}

if (!function_exists('kp_print_fetch_queue_job')) {
    function kp_print_fetch_queue_job($connection, $queue_id)
    {
        ensure_chaba_print_system($connection);
        $queue_id = (int)$queue_id;
        if ($queue_id < 1) {
            return null;
        }
        $q = mysqli_query($connection, "SELECT * FROM kp_print_queue WHERE queue_id='$queue_id' LIMIT 1");
        if (!$q || mysqli_num_rows($q) < 1) {
            return null;
        }
        return mysqli_fetch_assoc($q);
    }
}

if (!function_exists('kp_print_requeue')) {
    function kp_print_requeue($connection, $queue_id, $cntr_id, $user_id)
    {
        $job = kp_print_fetch_queue_job($connection, $queue_id);
        if (!$job) {
            return 0;
        }
        $new_queue_id = kp_print_enqueue($connection, [
            'sale_id' => (int)$job['sale_id'],
            'ref_id' => (int)$job['ref_id'],
            'print_type' => (string)$job['print_type'],
            'label' => (string)$job['label'],
            'cntr_id' => (int)$cntr_id,
            'route_path' => (string)$job['route_path'],
            'route_query' => (string)$job['route_query'],
            'requested_by' => (int)$user_id,
            'reprint_of' => (int)$job['queue_id'],
        ]);
        if ($new_queue_id > 0) {
            $type = mysqli_real_escape_string($connection, (string)$job['print_type']);
            mysqli_query(
                $connection,
                "INSERT INTO kp_print_reprints
                (queue_id, source_queue_id, print_type, ref_id, cntr_id, requested_by)
                VALUES
                ('$new_queue_id', '" . (int)$job['queue_id'] . "', '$type', '" . (int)$job['ref_id'] . "', '" . (int)$cntr_id . "', '" . (int)$user_id . "')"
            );
        }
        return $new_queue_id;
    }
}
