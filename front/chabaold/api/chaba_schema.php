<?php

require_once __DIR__ . '/../funcs.php';

if (!function_exists('chaba_api_ensure_schema')) {
    function chaba_api_ensure_schema($connection)
    {
        ensure_chaba_print_system($connection);

        $ticketColumns = [
            'batch_id' => "ALTER TABLE kp_print_ticket_items ADD COLUMN batch_id INT NOT NULL DEFAULT 0 AFTER sale_id",
            'print_type' => "ALTER TABLE kp_print_ticket_items ADD COLUMN print_type VARCHAR(55) NOT NULL DEFAULT '' AFTER batch_id",
            'attr_id' => "ALTER TABLE kp_print_ticket_items ADD COLUMN attr_id INT NOT NULL DEFAULT 0 AFTER item_id",
        ];

        $ticketTableChk = mysqli_query($connection, "SHOW TABLES LIKE 'kp_print_ticket_items'");
        if ($ticketTableChk && mysqli_num_rows($ticketTableChk) > 0) {
            foreach ($ticketColumns as $col => $sql) {
                $colChk = mysqli_query($connection, "SHOW COLUMNS FROM kp_print_ticket_items LIKE '$col'");
                if ($colChk && mysqli_num_rows($colChk) === 0) {
                    mysqli_query($connection, $sql);
                }
            }
        }
    }
}
