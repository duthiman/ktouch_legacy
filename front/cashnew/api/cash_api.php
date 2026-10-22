<?php
session_start();

require_once '../../../inc/connection.php';
require_once '../../../inc/gen_funcs.php';
require_once __DIR__ . '/../funcs.php';
require_once __DIR__ . '/cash_schema.php';
require_once __DIR__ . '/cash_helpers.php';
require_once __DIR__ . '/cash_logger.php';
require_once __DIR__ . '/cash_repository.php';
require_once __DIR__ . '/cash_service.php';
require_once '../../../front/chaba/funcs.php';

cash_schema_boot($connection);

$action = cash_api_clean_text($_REQUEST['action'] ?? 'live');
$user_id = isset($_SESSION['user_id']) ? (int)$_SESSION['user_id'] : 0;

cash_log_event('cash_api_request', [
    'action' => $action,
    'user_id' => $user_id,
]);

switch ($action) {
    case 'live':
        header('Content-Type: application/json');
        $result = cash_api_live($connection, $user_id);
        cash_api_json_response($result['ok'], $result['message'], $result['summary'] ?? [], $result['status'] ?? 200);
        break;

    case 'panel':
        header('Content-Type: text/html; charset=UTF-8');
        $result = cash_api_load_panel(cash_api_clean_text($_REQUEST['panel'] ?? ''));
        if (!$result['ok']) {
            http_response_code($result['status'] ?? 404);
            echo '<div class="alert alert-danger">' . htmlspecialchars($result['message'], ENT_QUOTES, 'UTF-8') . '</div>';
            exit;
        }
        echo $result['html'] ?? '';
        exit;

    case 'clrbill_staff_sales':
        header('Content-Type: text/html; charset=UTF-8');
        $result = cash_api_load_current_bill_rows();
        echo $result['html'] ?? '';
        exit;

    case 'clrbill_staff_prev':
        header('Content-Type: text/html; charset=UTF-8');
        $result = cash_api_load_previous_bill_rows();
        echo $result['html'] ?? '';
        exit;

    case 'clrbill_save_max_sale':
        header('Content-Type: application/json');
        $result = cash_api_save_max_sale($connection, cash_api_clean_int($_REQUEST['user_id'] ?? 0), $_REQUEST['max_sale'] ?? '');
        cash_api_json_response($result['ok'], $result['message'], [], $result['status'] ?? 200);
        break;

    case 'clrstaff_summary':
        header('Content-Type: text/html; charset=UTF-8');
        $result = cash_api_load_staff_clearance_summary();
        echo $result['html'] ?? '';
        exit;

    case 'queue_staff_summary':
        header('Content-Type: application/json');
        $staff_id = (int)($_REQUEST['staff_id'] ?? 0);
        $counter_id = (int)get_column('kp_users', 'counter', "user_id='$user_id'");
        if ($staff_id < 1) {
            cash_api_json_response(false, 'Invalid waiter selected.', [], 422);
        }
        $queue_id = kp_print_enqueue($connection, [
            'sale_id' => 0,
            'ref_id' => $staff_id,
            'print_type' => 'staff_sum',
            'label' => 'STAFF SUMMARY',
            'cntr_id' => $counter_id,
            'route_path' => 'front/chaba/render_staff_summary.php',
            'route_query' => http_build_query(['id' => $staff_id]),
            'requested_by' => $user_id,
        ]);
        cash_api_json_response($queue_id > 0, $queue_id > 0 ? 'Staff summary queued for printing.' : 'Could not queue staff summary.', ['queue_id' => $queue_id], $queue_id > 0 ? 200 : 500);
        break;

    case 'clrstaff_save':
        header('Content-Type: text/html; charset=UTF-8');
        $result = cash_api_save_staff_clearance();
        echo $result['html'] ?? '';
        exit;

    case 'expense_save':
        header('Content-Type: text/html; charset=UTF-8');
        $result = cash_api_save_expense();
        echo $result['html'] ?? '';
        exit;

    case 'expense_delete':
        header('Content-Type: text/html; charset=UTF-8');
        $result = cash_api_delete_expense();
        echo $result['html'] ?? '';
        exit;

    case 'merge_create':
        header('Content-Type: application/json');
        $result = cash_api_create_merge();
        cash_api_json_response(
            !empty($result['ok']),
            $result['message'] ?? ($result['err'] ?? 'Merge request processed.'),
            $result,
            $result['status'] ?? (!empty($result['ok']) ? 200 : 500)
        );
        break;

    case 'payment_mpesa_rows':
        header('Content-Type: text/html; charset=UTF-8');
        $result = cash_api_payment_mpesa_rows();
        echo $result['html'] ?? '';
        exit;

    case 'payment_mpesa_search':
        header('Content-Type: text/html; charset=UTF-8');
        $result = cash_api_payment_mpesa_search();
        echo $result['html'] ?? '';
        exit;

    case 'payment_submit':
        header('Content-Type: application/json');
        $result = cash_api_payment_submit();
        cash_api_json_response(
            ($result['status'] ?? '') === 'success' || !empty($result['ok']),
            $result['message'] ?? 'Payment processed.',
            $result,
            (($result['status'] ?? '') === 'success' || !empty($result['ok'])) ? 200 : 400
        );
        break;

    case 'payment_confirm_sale':
        header('Content-Type: text/html; charset=UTF-8');
        $result = cash_api_payment_confirm_sale();
        echo $result['html'] ?? '';
        exit;

    case 'collect_approve':
        header('Content-Type: application/json');
        $result = cash_api_collect_approve();
        cash_api_json_response(
            ($result['status'] ?? '') === 'success' || !empty($result['ok']),
            $result['message'] ?? 'Collection processed.',
            $result,
            (($result['status'] ?? '') === 'success' || !empty($result['ok'])) ? 200 : 400
        );
        break;

    case 'handover_save':
        header('Content-Type: application/json');
        $result = cash_api_handover_save();
        cash_api_json_response(
            ($result['status'] ?? '') === 'success' || !empty($result['ok']),
            $result['message'] ?? 'Handover processed.',
            $result,
            (($result['status'] ?? '') === 'success' || !empty($result['ok'])) ? 200 : 400
        );
        break;

    case 'error_check':
        header('Content-Type: text/html; charset=UTF-8');
        $result = cash_api_error_check();
        echo $result['html'] ?? '';
        exit;

    case 'customer_credit_lookup':
        header('Content-Type: text/html; charset=UTF-8');
        $result = cash_api_customer_credit_lookup();
        echo $result['html'] ?? '';
        exit;

    case 'credit_assign':
        header('Content-Type: application/json');
        $result = cash_api_credit_assign();
        cash_api_json_response(
            ($result['status'] ?? '') === 'success' || !empty($result['ok']),
            $result['message'] ?? 'Credit assignment processed.',
            $result,
            (($result['status'] ?? '') === 'success' || !empty($result['ok'])) ? 200 : 400
        );
        break;

    case 'customer_credit_workbench':
        header('Content-Type: text/html; charset=UTF-8');
        $result = cash_api_customer_credit_workbench();
        echo $result['html'] ?? '';
        exit;

    case 'customer_credit_pay':
        header('Content-Type: application/json');
        $result = cash_api_customer_credit_pay();
        cash_api_json_response(
            ($result['status'] ?? '') === 'success' || !empty($result['ok']),
            $result['message'] ?? 'Customer credit payment processed.',
            $result,
            (($result['status'] ?? '') === 'success' || !empty($result['ok'])) ? 200 : 400
        );
        break;

    case 'modal':
        header('Content-Type: text/html; charset=UTF-8');
        $result = cash_api_load_modal(cash_api_clean_text($_REQUEST['modal'] ?? ''));
        if (!$result['ok']) {
            http_response_code($result['status'] ?? 404);
            echo '<div class="modal-content"><div class="modal-body" style="padding:20px; text-align:center; font-weight:bold;">' . htmlspecialchars($result['message'], ENT_QUOTES, 'UTF-8') . '</div></div>';
            exit;
        }
        echo $result['html'] ?? '';
        exit;

    default:
        header('Content-Type: application/json');
        cash_api_json_response(false, 'Unknown action.', ['action' => $action], 400);
        break;
}
