<?php
// The open page uses the shared billing schema. Keep schema setup here so the
// endpoint has one predictable entry point and does not duplicate DDL logic.
function open_schema_boot($connection)
{
    // Schema healing is safety-critical but should not block every modal or
    // close request with repeated SHOW INDEX/DDL checks. Recheck periodically
    // across requests; the first request after expiry still fully heals.
    $cache_file = rtrim(sys_get_temp_dir(), DIRECTORY_SEPARATOR)
        . DIRECTORY_SEPARATOR . 'mvure_open_schema_' . md5(__DIR__) . '.stamp';
    $cache_ttl = 300;
    $cache_time = @filemtime($cache_file);
    if ($cache_time !== false && (time() - $cache_time) < $cache_ttl) {
        return;
    }

    require_once __DIR__ . '/open_bill_bridge.php';
    if (function_exists('kw_ensure_bill_schema')) {
        kw_ensure_bill_schema();
    }

    // Native open schema is maintained by open/funcs.php.
    require_once dirname(__DIR__) . '/funcs.php';
    if (function_exists('open_ensure_sales_open_table')) {
        open_ensure_sales_open_table($connection);
    }
    if (function_exists('open_ensure_sales_closed_opened_table')) {
        open_ensure_sales_closed_opened_table($connection);
    }
    if (function_exists('open_ensure_color_table')) {
        open_ensure_color_table($connection);
    }

    @touch($cache_file);
}
