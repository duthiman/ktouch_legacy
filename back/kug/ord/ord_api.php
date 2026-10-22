<?php
header('Content-Type: application/json; charset=utf-8');

// Load the database connection before gen_funcs.php; get_column() uses the
// global $connection handle.
require_once __DIR__ . '/../../../inc/connection.php';
require_once __DIR__ . '/../../../inc/gen_funcs.php';
require_once __DIR__ . '/../kug_schema.php';

try {
    kug_ensure_schema($connection);
} catch (Throwable $schemaError) {
    http_response_code(500);
    echo json_encode(['ok' => false, 'message' => 'Could not prepare purchase-order tables: ' . $schemaError->getMessage()]);
    exit;
}

$action = trim((string)($_GET['action'] ?? ''));
$userId = (int)($_SESSION['mwene'] ?? $_SESSION['user_id'] ?? 0);
$urlPoId = (int)($_GET['po_id'] ?? $_GET['id'] ?? 0);
$poId = $urlPoId > 0
    ? $urlPoId
    : (int)get_column('kp_thesion', 'trans_id', "user_id='{$userId}'");

if ($action === 'add_item') {
    $itemId = (int)($_POST['item_id'] ?? 0);
    $qty = (float)($_POST['qty'] ?? 0);
    $price = round((float)($_POST['price'] ?? 0), 2);

    if ($poId <= 0) {
        http_response_code(400);
        echo json_encode(['ok' => false, 'message' => 'No active purchase order found.']);
        exit;
    }
    if ($itemId <= 0 || $qty <= 0 || $price < 0) {
        http_response_code(422);
        echo json_encode(['ok' => false, 'message' => 'Select an item and enter valid quantity and price.']);
        exit;
    }

    $itemName = (string)get_column('kp_items', 'name', "item_id='{$itemId}'");
    if ($itemName === '') {
        http_response_code(404);
        echo json_encode(['ok' => false, 'message' => 'Selected item was not found.']);
        exit;
    }

    $duplicate = $connection->prepare('SELECT id FROM kp_po_items WHERE po_id = ? AND item_id = ? LIMIT 1');
    $duplicate->bind_param('ii', $poId, $itemId);
    $duplicate->execute();
    if ($duplicate->get_result()->fetch_assoc()) {
        http_response_code(409);
        echo json_encode(['ok' => false, 'message' => 'This item is already in the purchase order.']);
        exit;
    }

    $total = $qty * $price;
    $insert = $connection->prepare(
        'INSERT INTO kp_po_items (po_id, item_id, qty, price, total, day)
         VALUES (?, ?, ?, ?, ?, CURRENT_DATE)'
    );
    if (!$insert) {
        http_response_code(500);
        echo json_encode(['ok' => false, 'message' => 'Could not prepare item insert.']);
        exit;
    }

    $insert->bind_param('iiddd', $poId, $itemId, $qty, $price, $total);
    if (!$insert->execute()) {
        http_response_code(500);
        echo json_encode(['ok' => false, 'message' => 'Could not add item to the purchase order.']);
        exit;
    }

    echo json_encode([
        'ok' => true,
        'message' => $itemName . ' added to the purchase order.',
        'id' => (int)$insert->insert_id,
    ]);
    exit;
}

if ($action === 'update_item') {
    $rowId = (int)($_POST['id'] ?? 0);
    $qty = (float)($_POST['qty'] ?? 0);
    $price = round((float)($_POST['price'] ?? 0), 2);
    $discount = max(0, round((float)($_POST['discount'] ?? 0), 2));

    if ($poId <= 0 || $rowId <= 0 || $qty <= 0 || $price < 0) {
        http_response_code(422);
        echo json_encode(['ok' => false, 'message' => 'Enter a valid quantity and price.']);
        exit;
    }

    $update = $connection->prepare(
        'UPDATE kp_po_items SET qty = ?, price = ?, disc = ?, total = ? WHERE id = ? AND po_id = ? LIMIT 1'
    );
    // Item discount is applied to each unit price, not once to the line total.
    $total = max(0, $qty * max(0, $price - $discount));
    $update->bind_param('ddddii', $qty, $price, $discount, $total, $rowId, $poId);
    if (!$update->execute()) {
        http_response_code(404);
        echo json_encode(['ok' => false, 'message' => 'Purchase order item was not found.']);
        exit;
    }

    echo json_encode(['ok' => true, 'id' => $rowId, 'qty' => $qty, 'price' => $price, 'discount' => $discount, 'total' => $total]);
    exit;
}

if ($action === 'delete_item') {
    $rowId = (int)($_POST['id'] ?? 0);

    if ($poId <= 0 || $rowId <= 0) {
        http_response_code(422);
        echo json_encode(['ok' => false, 'message' => 'Invalid PO item selected.']);
        exit;
    }

    $delete = $connection->prepare('DELETE FROM kp_po_items WHERE id = ? AND po_id = ? LIMIT 1');
    if (!$delete) {
        http_response_code(500);
        echo json_encode(['ok' => false, 'message' => 'Could not prepare item deletion.']);
        exit;
    }

    $delete->bind_param('ii', $rowId, $poId);
    if (!$delete->execute()) {
        http_response_code(500);
        echo json_encode(['ok' => false, 'message' => 'Could not remove item.']);
        exit;
    }

    if ($delete->affected_rows < 1) {
        http_response_code(404);
        echo json_encode(['ok' => false, 'message' => 'PO item was not found.']);
        exit;
    }

    echo json_encode(['ok' => true, 'message' => 'Item removed.']);
    exit;
}

if ($action === 'po_status') {
    if ($poId <= 0) {
        echo json_encode(['ok' => true, 'po_id' => 0, 'status' => null]);
        exit;
    }

    $statusQuery = $connection->prepare(
        'SELECT po_id, sup_id, po_day, po_status
         FROM kp_po_status
         WHERE po_id = ?
         LIMIT 1'
    );
    if (!$statusQuery) {
        http_response_code(500);
        echo json_encode(['ok' => false, 'message' => 'Could not prepare PO status query.']);
        exit;
    }

    $statusQuery->bind_param('i', $poId);
    $statusQuery->execute();
    $status = $statusQuery->get_result()->fetch_assoc();

    if (!$status) {
        echo json_encode(['ok' => true, 'po_id' => $poId, 'status' => null]);
        exit;
    }

    $supplierId = (int)$status['sup_id'];
    $supplierName = $supplierId > 0
        ? (string)get_column('kp_suppliers', 'sup_name', "sup_id='{$supplierId}'")
        : '';
    $supplierBalance = $supplierId > 0
        ? (float)get_column('kp_suppliers', 'sup_bal', "sup_id='{$supplierId}'")
        : 0;
    $cashierName = $userId > 0
        ? (string)get_column('kp_users', 'username', "user_id='{$userId}'")
        : '';

    echo json_encode([
        'ok' => true,
        'po_id' => $poId,
        'status' => [
            'sup_id' => $supplierId,
            'supplier' => $supplierName !== '' ? $supplierName : 'Not selected',
            'cashier' => $cashierName !== '' ? $cashierName : '—',
            'balance' => $supplierBalance,
            'date' => (string)($status['po_day'] ?? ''),
            'state' => (int)($status['po_status'] ?? 2),
        ],
    ]);
    exit;
}

if ($action === 'update_date') {
    $day = trim((string)($_POST['day'] ?? ''));
    $date = DateTime::createFromFormat('Y-m-d', $day);
    $validDate = $date && $date->format('Y-m-d') === $day;

    if ($poId <= 0 || !$validDate) {
        http_response_code(422);
        echo json_encode(['ok' => false, 'message' => 'Enter a valid order date.']);
        exit;
    }

    $updateDate = $connection->prepare('UPDATE kp_po_status SET po_day = ? WHERE po_id = ?');
    if (!$updateDate) {
        http_response_code(500);
        echo json_encode(['ok' => false, 'message' => 'Could not prepare date update.']);
        exit;
    }

    $updateDate->bind_param('si', $day, $poId);
    if (!$updateDate->execute()) {
        http_response_code(500);
        echo json_encode(['ok' => false, 'message' => 'Could not update the order date.']);
        exit;
    }

    echo json_encode(['ok' => true, 'date' => $day, 'message' => 'Order date updated.']);
    exit;
}

if ($action === 'update_supplier') {
    $supplierId = (int)($_POST['sup_id'] ?? 0);

    if ($poId <= 0 || $supplierId <= 0) {
        http_response_code(422);
        echo json_encode(['ok' => false, 'message' => 'Select a valid supplier.']);
        exit;
    }

    $supplierName = (string)get_column('kp_suppliers', 'sup_name', "sup_id='{$supplierId}'");
    if ($supplierName === '') {
        http_response_code(404);
        echo json_encode(['ok' => false, 'message' => 'Supplier was not found.']);
        exit;
    }

    $updateSupplier = $connection->prepare('UPDATE kp_po_status SET sup_id = ? WHERE po_id = ?');
    if (!$updateSupplier) {
        http_response_code(500);
        echo json_encode(['ok' => false, 'message' => 'Could not prepare supplier update.']);
        exit;
    }

    $updateSupplier->bind_param('ii', $supplierId, $poId);
    if (!$updateSupplier->execute()) {
        http_response_code(500);
        echo json_encode(['ok' => false, 'message' => 'Could not update the supplier.']);
        exit;
    }

    echo json_encode([
        'ok' => true,
        'sup_id' => $supplierId,
        'supplier' => $supplierName,
        'message' => 'Supplier updated successfully.',
    ]);
    exit;
}

if ($action === 'confirm_lpo') {
    $autoReceive = (int)($_POST['auto_receive'] ?? 0) === 1;
    $updateBuyingPrice = (int)($_POST['update_buying_price'] ?? 0) === 1;
    $postedDiscount = max(0, (float)($_POST['disc'] ?? 0));
    $postedVatable = max(0, (float)($_POST['vatable'] ?? 0));
    $postedVat = max(0, (float)($_POST['vatt'] ?? 0));
    $postedTotal = max(0, (float)($_POST['tt'] ?? 0));
    $postedTaxRate = max(0, (float)($_POST['perc'] ?? 0));
    $postedTaxType = trim((string)($_POST['tax_type'] ?? 'inclusive'));
    $postedPoDate = trim((string)($_POST['po_date'] ?? ''));
    $postedSupplierRef = trim((string)($_POST['supplier_ref'] ?? ''));

    if ($poId <= 0 || $userId <= 0) {
        http_response_code(422);
        echo json_encode(['ok' => false, 'message' => 'A valid purchase order and user are required.']);
        exit;
    }

    try {
        $connection->begin_transaction();

        $statusQuery = $connection->prepare(
            'SELECT sup_id, po_day FROM kp_po_status WHERE po_id = ? LIMIT 1 FOR UPDATE'
        );
        $statusQuery->bind_param('i', $poId);
        $statusQuery->execute();
        $poStatus = $statusQuery->get_result()->fetch_assoc();
        if (!$poStatus) {
            throw new RuntimeException('Purchase order status was not found.');
        }

        $supplierId = (int)($poStatus['sup_id'] ?? 0);
        if ($supplierId <= 0) {
            throw new RuntimeException('Select a supplier before confirming the LPO.');
        }

        $existingPo = $connection->prepare('SELECT po_id FROM kp_po WHERE po_id = ? LIMIT 1');
        $existingPo->bind_param('i', $poId);
        $existingPo->execute();
        if ($existingPo->get_result()->fetch_assoc()) {
            throw new RuntimeException('This LPO has already been confirmed.');
        }

        $itemsQuery = $connection->prepare(
            'SELECT item_id, qty, price, disc, total FROM kp_po_items WHERE po_id = ? ORDER BY id ASC'
        );
        $itemsQuery->bind_param('i', $poId);
        $itemsQuery->execute();
        $itemsResult = $itemsQuery->get_result();
        $items = [];
        $amount = 0.0;

        while ($item = $itemsResult->fetch_assoc()) {
            $itemId = (int)$item['item_id'];
            $qty = (float)$item['qty'];
            $price = (float)$item['price'];
            $itemDiscount = isset($item['disc']) ? (float)$item['disc'] : 0;
            $total = max(0, $qty * max(0, $price - $itemDiscount));

            if ($itemId <= 0 || $qty <= 0) {
                throw new RuntimeException('The LPO contains an invalid item quantity.');
            }

            $items[] = [
                'item_id' => $itemId,
                'qty' => $qty,
                'price' => $price,
                'total' => $total,
            ];
            $amount += $total;
        }

        if (!$items) {
            throw new RuntimeException('Add at least one item before confirming the LPO.');
        }

        $poDate = $postedPoDate !== '' ? $postedPoDate : (string)($poStatus['po_day'] ?? date('Y-m-d'));
        if (!preg_match('/^\d{4}-\d{2}-\d{2}$/', $poDate)) {
            $poDate = date('Y-m-d');
        }
        // Use the page total when supplied; otherwise use the item total.
        if ($postedTotal > 0) {
            $amount = $postedTotal;
        }
        $taxTypeValue = ['none' => 0, 'inclusive' => 1, 'exclusive' => 2][$postedTaxType] ?? 1;
        $shift = (string)($_SESSION['shift'] ?? date('Y-m-d'));
        $shiftTable = $connection->query("SHOW TABLES LIKE 'kp_shift'");
        if ($shiftTable && $shiftTable->num_rows > 0) {
            $shiftResult = $connection->query('SELECT shift FROM kp_shift LIMIT 1');
            if ($shiftResult && ($shiftRow = $shiftResult->fetch_assoc()) && (string)$shiftRow['shift'] !== '') {
                $shift = (string)$shiftRow['shift'];
            }
        }
        $createPo = $connection->prepare(
            'INSERT INTO kp_po
             (po_id, sup_id, ord_user, po_date, amount, disc, tax_type, tax_perc, vatable, vat, refno, rec_status, stock_status, s2s)
             VALUES (?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?)'
        );
        $recStatus = $autoReceive ? 1 : 2;
        $stockStatus = $autoReceive ? 1 : 2;
        $poS2s = $autoReceive ? 1 : 2;
        $poRef = $postedSupplierRef;
        $createPo->bind_param('iiisddidddsiii', $poId, $supplierId, $userId, $poDate, $amount, $postedDiscount, $taxTypeValue, $postedTaxRate, $postedVatable, $postedVat, $poRef, $recStatus, $stockStatus, $poS2s);
        $createPo->execute();

        $updatePoShift = $connection->prepare('UPDATE kp_po SET shift = ? WHERE po_id = ?');
        $updatePoShift->bind_param('si', $shift, $poId);
        $updatePoShift->execute();

        $grnId = 0;
        if ($autoReceive) {
            $grnNumber = $connection->query('SELECT grn_id FROM kp_grn_id LIMIT 1 FOR UPDATE')->fetch_assoc();
            if ($grnNumber) {
                $grnId = (int)$grnNumber['grn_id'] + 1;
                $updateGrnNumber = $connection->prepare('UPDATE kp_grn_id SET grn_id = ?');
                $updateGrnNumber->bind_param('i', $grnId);
                $updateGrnNumber->execute();
            } else {
                $grnId = 1;
                $createGrnNumber = $connection->prepare('INSERT INTO kp_grn_id (grn_id) VALUES (?)');
                $createGrnNumber->bind_param('i', $grnId);
                $createGrnNumber->execute();
            }

            $paid = 0.0;
            $balance = $amount;
            $grnStatus = 1;
            $createGrn = $connection->prepare(
                'INSERT INTO kp_grn
                 (grn_id, po_id, user_id, sup_id, amount, paid, bal, status, pay_day, day, datime)
                 VALUES (?, ?, ?, ?, ?, ?, ?, ?, ?, ?, CURRENT_TIMESTAMP)'
            );
            $payDay = $poDate;
            $createGrn->bind_param('iiiidddiss', $grnId, $poId, $userId, $supplierId, $amount, $paid, $balance, $grnStatus, $payDay, $poDate);
            $createGrn->execute();

            $createGrnItem = $connection->prepare(
                'INSERT INTO kp_grn_items (grn_id, item_id, qty, price, total, user_id, day, s2s)
                 VALUES (?, ?, ?, ?, ?, ?, ?, 1)'
            );
            $addStock = $connection->prepare('UPDATE kp_items SET store_qty = store_qty + ? WHERE item_id = ?');
            $trackStock = $connection->prepare(
                "INSERT INTO kp_stock_track
                 (grn_id, item_id, po_id, user_id, qty, status, `trans`, day)
                 VALUES (?, ?, ?, ?, ?, 'IN', 'GRN', CURRENT_DATE)"
            );

            foreach ($items as $item) {
                $itemId = (int)$item['item_id'];
                $itemQty = (float)$item['qty'];
                $itemPrice = (float)$item['price'];
                $itemTotal = (float)$item['total'];
                $createGrnItem->bind_param('iidddis', $grnId, $itemId, $itemQty, $itemPrice, $itemTotal, $userId, $poDate);
                $createGrnItem->execute();

                $addStock->bind_param('di', $itemQty, $itemId);
                $addStock->execute();

                $trackStock->bind_param('iiiid', $grnId, $itemId, $poId, $userId, $itemQty);
                $trackStock->execute();
            }

            $updateReceiving = $connection->prepare(
                'UPDATE kp_po SET rec_status = 1, stock_status = 1, s2s = 1,
                 rec_staff = ?, rec_date = CURRENT_DATE, shift = ? WHERE po_id = ?'
            );
            $updateReceiving->bind_param('isi', $userId, $shift, $poId);
            $updateReceiving->execute();
        }

        if ($updateBuyingPrice) {
            $updatePrice = $connection->prepare('UPDATE kp_items SET b_price = ? WHERE item_id = ?');
            foreach ($items as $item) {
                $itemPrice = (float)$item['price'];
                $itemId = (int)$item['item_id'];
                $updatePrice->bind_param('di', $itemPrice, $itemId);
                $updatePrice->execute();
            }
        }

        $itemStatus = $connection->prepare(
            'UPDATE kp_po_items SET status = 1, s2s = ? WHERE po_id = ?'
        );
        $itemS2s = $autoReceive ? 1 : 2;
        $itemStatus->bind_param('ii', $itemS2s, $poId);
        $itemStatus->execute();

        $clearStatus = $connection->prepare('DELETE FROM kp_po_status WHERE po_id = ?');
        $clearStatus->bind_param('i', $poId);
        $clearStatus->execute();

        $connection->commit();
        echo json_encode([
            'ok' => true,
            'message' => 'LPO confirmed successfully.',
            'po_id' => $poId,
            'grn_id' => $grnId,
            'auto_receive' => $autoReceive,
            'update_buying_price' => $updateBuyingPrice,
        ]);
    } catch (Throwable $error) {
        $connection->rollback();
        http_response_code(500);
        echo json_encode(['ok' => false, 'message' => $error->getMessage()]);
    }
    exit;
}

if ($action === 'hold_order') {
    if ($poId <= 0) {
        http_response_code(422);
        echo json_encode(['ok' => false, 'message' => 'No purchase order was supplied.']);
        exit;
    }

    $hold = $connection->prepare('UPDATE kp_po_status SET po_status = 3 WHERE po_id = ?');
    if (!$hold) {
        http_response_code(500);
        echo json_encode(['ok' => false, 'message' => 'Could not prepare hold action.']);
        exit;
    }

    $hold->bind_param('i', $poId);
    if (!$hold->execute()) {
        http_response_code(500);
        echo json_encode(['ok' => false, 'message' => 'Could not place the order on hold.']);
        exit;
    }

    if ($userId > 0) {
        $clearSessionPo = $connection->prepare('UPDATE kp_thesion SET trans_id = 0, datime = CURRENT_TIMESTAMP WHERE user_id = ?');
        if ($clearSessionPo) {
            $clearSessionPo->bind_param('i', $userId);
            $clearSessionPo->execute();
        }
    }

    unset($_SESSION['po_id']);
    echo json_encode([
        'ok' => true,
        'message' => 'Purchase order placed on hold.',
        'redirect' => '../index.php',
    ]);
    exit;
}

if ($action !== 'po_items') {
    http_response_code(400);
    echo json_encode(['ok' => false, 'message' => 'Unknown action.']);
    exit;
}

if ($poId <= 0) {
    echo json_encode(['ok' => true, 'po_id' => 0, 'items' => [], 'subtotal' => 0]);
    exit;
}

$stmt = $connection->prepare(
    'SELECT pi.id, pi.item_id, i.name, pi.qty, pi.price, pi.disc, pi.total
     FROM kp_po_items AS pi
     LEFT JOIN kp_items AS i ON i.item_id = pi.item_id
     WHERE pi.po_id = ?
     ORDER BY pi.id DESC'
);

if (!$stmt) {
    http_response_code(500);
    echo json_encode(['ok' => false, 'message' => 'Could not prepare PO items query.']);
    exit;
}

$stmt->bind_param('i', $poId);
$stmt->execute();
$result = $stmt->get_result();
$items = [];
$subtotal = 0.0;

while ($row = $result->fetch_assoc()) {
    $itemId = (int)$row['item_id'];
    $name = trim((string)($row['name'] ?? ''));
    if ($name === '') {
        $name = (string)get_column('kp_items', 'name', "item_id='{$itemId}'");
    }

    $lineTotal = max(0, (float)$row['qty'] * max(0, (float)$row['price'] - (float)$row['disc']));
    $subtotal += $lineTotal;
    $items[] = [
        'id' => (int)$row['id'],
        'item_id' => $itemId,
        'name' => $name !== '' ? $name : 'Unnamed item',
        'meta' => 'Item ' . $itemId,
        'qty' => (float)$row['qty'],
        'price' => (float)$row['price'],
        'discount' => (float)$row['disc'],
        'total' => $lineTotal,
    ];
}

echo json_encode([
    'ok' => true,
    'po_id' => $poId,
    'items' => $items,
    'subtotal' => $subtotal,
]);
