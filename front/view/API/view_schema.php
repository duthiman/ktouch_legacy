<?php

if (!function_exists('view_schema_boot')) {
    function view_schema_boot($connection)
    {
        if (!$connection) {
            return;
        }

        if (function_exists('ensure_sales_parent_system')) {
            ensure_sales_parent_system();
        }

        if (function_exists('cash_ensure_merge_tables')) {
            cash_ensure_merge_tables();
        }

        if (function_exists('cash_ensure_merge_request_table')) {
            cash_ensure_merge_request_table();
        }
    }
}
