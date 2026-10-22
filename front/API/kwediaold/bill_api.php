<?php
/**
 * API Routing layer
 */
header('Content-Type: application/json');
if (session_status() === PHP_SESSION_NONE) {
    session_start();
}
require_once __DIR__ . '/../../../inc/connection.php';
require_once __DIR__ . '/../../kwedia/funcs.php'; // For hhr(), get_column(), logs()
require_once __DIR__ . '/bill_schema.php';
require_once __DIR__ . '/bill_logger.php';
require_once __DIR__ . '/bill_helpers.php';
require_once __DIR__ . '/bill_repository.php'; // Procedural repository stubs
require_once __DIR__ . '/../../kwedia/bill_repository.php'; // BillRepository Class
require_once __DIR__ . '/bill_service.php'; // Procedural business logic
require_once __DIR__ . '/../../kwedia/bill_service.php'; // KwediaBillApiService Class

if (!isset($_SESSION['user_id']) || $_SESSION['user_id'] == 0) {
    BillHelpers::jsonResponse(false, 'Unauthorized');
}

$user_id = $_SESSION['user_id'];
$action = strtolower(trim($_REQUEST['action'] ?? ''));

$repository = new BillRepository($connection);
$service = new KwediaBillApiService($repository);

switch ($action) {
    case 'add_item':
        $itemId = $_REQUEST['id'];
        $qty = (float)$_REQUEST['qty'];
        $attrId = !empty($_REQUEST['attr_id']) ? $_REQUEST['attr_id'] : null;
        $modifier = (float)($_REQUEST['price_modifier'] ?? 0);

        if ($service->addItem($user_id, $itemId, $qty, $attrId, $modifier)) {
            BillHelpers::jsonResponse(true, 'Item added to cart');
        } else {
            BillHelpers::jsonResponse(false, 'Failed to add item');
        }
        break;

    case 'remove_item':
        $itemId = $_REQUEST['item_id'];
        $hour = $_REQUEST['hhr'];
        $attrId = !empty($_REQUEST['attr_id']) ? $_REQUEST['attr_id'] : null;

        if ($service->removeItem($user_id, $itemId, $hour, $attrId)) {
            BillHelpers::jsonResponse(true, 'Item removed from cart');
        } else {
            BillHelpers::jsonResponse(false, 'Failed to remove item');
        }
        break;

    case 'get_open_bills':
        $bills = $service->getOpenBills($user_id);
        BillHelpers::jsonResponse(true, 'Open bills fetched', ['bills' => $bills]);
        break;

    case 'get_categories':
        $categories = $service->getCategories();
        BillHelpers::jsonResponse(true, 'Categories fetched', ['categories' => $categories]);
        break;

    case 'get_current_cart':
        $cart = $service->getCurrentCart($user_id);
        BillHelpers::jsonResponse(true, 'Cart fetched', $cart);
        break;

    case 'get_order_details':
        $saleId = (int)($_REQUEST['sale_id'] ?? 0);
        if ($saleId < 1) {
            // Fallback to the user's active sale if no ID is provided in the request
            $thesion = $repository->getThesion($user_id);
            $saleId = (int)($thesion['sale_id'] ?? 0);
        }
        if ($saleId > 0) {
            $details = $service->getOrderDetails($saleId);
            BillHelpers::jsonResponse(true, 'Order details fetched', $details);
        } else {
            BillHelpers::jsonResponse(false, 'Invalid sale ID');
        }
        break;

    case 'save_identifier':
        $saleId = (int)$_REQUEST['sale_id'];
        $type = $_REQUEST['type'] ?? 'name';
        $value = $_REQUEST['value'] ?? '';
        $label = $_REQUEST['label'] ?? '';
        $hex = $_REQUEST['hex'] ?? '';

        if ($saleId > 0 && $service->saveIdentifier($user_id, $saleId, $type, $value, $label, $hex)) {
            BillLogger::logAction("API_IDENTIFIER_SAVED|SALE:$saleId|TYPE:$type|VAL:$value");
            BillHelpers::jsonResponse(true, 'Identifier saved');
        } else {
            BillHelpers::jsonResponse(false, 'Failed to save identifier or invalid sale ID');
        }
        break;

    case 'finalize_closed':
        $billCntr = (int)($_REQUEST['bill_cntr'] ?? 0);
        if ($service->finalizeClosed($user_id, $billCntr)) {
            BillHelpers::jsonResponse(true, 'Sale finalized successfully');
        } else {
            BillHelpers::jsonResponse(false, 'Failed to finalize sale');
        }
        break;

    case 'close_bill':
        $billCntr = (int)($_REQUEST['bill_cntr'] ?? 0);
        if ($service->closeBill($user_id, $billCntr)) {
            BillHelpers::jsonResponse(true, 'Bill closed successfully');
        } else {
            BillHelpers::jsonResponse(false, 'Failed to close bill');
        }
        break;

    default:
        BillHelpers::jsonResponse(false, 'Invalid action');
        break;
}
