<?php
session_start();

header('Content-Type: application/json; charset=utf-8');

require_once __DIR__ . '/../../../inc/connection.php';
require_once __DIR__ . '/../funcs.php';
require_once __DIR__ . '/chaba_helpers.php';
require_once __DIR__ . '/chaba_schema.php';
require_once __DIR__ . '/chaba_repository.php';
require_once __DIR__ . '/chaba_service.php';

chaba_api_ensure_schema($connection);

$action = trim((string)($_REQUEST['action'] ?? ''));
$request = chaba_api_request_data();

$publicActions = ['ping', 'sale_context', 'bill_payload', 'ticket_payload', 'render_payload'];
if (!in_array($action, $publicActions, true)) {
    $session = chaba_api_check_session();
    if (!$session['ok']) {
        chaba_api_json_response(false, $session['message'], ['user_id' => 0], 401);
    }
}

switch ($action) {
    case '':
    case 'ping':
        chaba_api_json_response(true, 'Chaba render API is ready.', [
            'template' => function_exists('get_print_template') ? (int)get_print_template() : 1,
        ]);
        break;

    case 'sale_context':
        $sale_id = (int)($request['sale_id'] ?? $_REQUEST['sale_id'] ?? 0);
        $result = chaba_service_get_sale_context($sale_id);
        chaba_api_json_response($result['ok'], $result['message'], ['context' => $result['context'] ?? []], $result['ok'] ? 200 : 400);
        break;

    case 'bill_payload':
        $sale_id = (int)($request['sale_id'] ?? $_REQUEST['sale_id'] ?? 0);
        $result = chaba_service_get_bill_payload($sale_id);
        chaba_api_json_response($result['ok'], $result['message'], ['payload' => $result['payload'] ?? []], $result['ok'] ? 200 : 400);
        break;

    case 'ticket_payload':
        $sale_id = (int)($request['sale_id'] ?? $_REQUEST['sale_id'] ?? 0);
        $ticket_type = (string)($request['ticket_type'] ?? $_REQUEST['ticket_type'] ?? '');
        $delta_only = (int)($request['delta_only'] ?? $_REQUEST['delta_only'] ?? 0) === 1;
        $batch_id = (int)($request['batch_id'] ?? $_REQUEST['batch_id'] ?? 0);
        $result = chaba_service_get_ticket_payload($sale_id, $ticket_type, $delta_only, $batch_id);
        chaba_api_json_response($result['ok'], $result['message'], ['payload' => $result['payload'] ?? []], $result['ok'] ? 200 : 400);
        break;

    case 'render_payload':
        $type = (string)($request['type'] ?? $_REQUEST['type'] ?? '');
        $sale_id = (int)($request['sale_id'] ?? $_REQUEST['sale_id'] ?? 0);
        $ref_id = (int)($request['ref_id'] ?? $_REQUEST['ref_id'] ?? 0);
        $delta_only = (int)($request['delta_only'] ?? $_REQUEST['delta_only'] ?? 0) === 1;
        $batch_id = (int)($request['batch_id'] ?? $_REQUEST['batch_id'] ?? 0);
        $result = chaba_service_get_render_payload($type, $sale_id, $ref_id, $delta_only, $batch_id);
        chaba_api_json_response($result['ok'], $result['message'], ['payload' => $result['payload'] ?? []], $result['ok'] ? 200 : 400);
        break;

    default:
        chaba_api_json_response(false, 'Unknown action.', ['action' => $action], 400);
        break;
}
