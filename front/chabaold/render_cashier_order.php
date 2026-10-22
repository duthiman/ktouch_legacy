<?php
session_start();
include('../../inc/connection.php');
include('../../inc/gen_funcs.php');
require_once(__DIR__ . '/render_css.php');
require_once(__DIR__ . '/api/chaba_helpers.php');
require_once(__DIR__ . '/api/chaba_schema.php');
require_once(__DIR__ . '/api/chaba_repository.php');
require_once(__DIR__ . '/api/chaba_service.php');

chaba_api_ensure_schema($connection);

$receiptDivider = function_exists('kp_receipt_divider_text') ? kp_receipt_divider_text($connection) : str_repeat('*', 50);
$sale_id = (int)($_GET['sale_id'] ?? 0);
$delta_only = (int)($_GET['delta_only'] ?? 0) === 1;
$batch_id = (int)($_GET['batch_id'] ?? 0);
if ($sale_id < 1) { exit('Invalid sale'); }

$result = chaba_service_get_ticket_payload($sale_id, 'cashier_order', $delta_only, $batch_id);
if (empty($result['ok'])) {
    exit((string)($result['message'] ?? 'Ticket payload not available'));
}

$ticket_payload = $result['payload'] ?? [];
$print_template = kp_render_print_template_id();
$ticket_page_title = 'Cashier Order';
$ticket_heading = 'CASHIER ORDER TICKET';
$ticket_show_counter_name = true;
$ticket_footer_rows = [];
require __DIR__ . '/render_ticket_view.php';
