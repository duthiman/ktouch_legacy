<?php
// kug/rec/api/rec_api.php — Purchase Receiving API
header('Content-Type: application/json');
if (session_status() === PHP_SESSION_NONE) session_start();
if (!isset($_SESSION['mwene'])) {
    exit(json_encode(['success' => false, 'message' => 'Unauthorized']));
}

include("../funcs.php");

$user_id = (int)$_SESSION['mwene'];
$action  = $_REQUEST['action'] ?? '';

// ── Helper: escape string ─────────────────────────────────────────────────────
function esc($con, $v) {
    return mysqli_real_escape_string($con, (string)$v);
}

// ═════════════════════════════════════════════════════════════════════════════
// list_pos — Approved POs pending receiving
// ═════════════════════════════════════════════════════════════════════════════
if ($action === 'list_pos') {
    $r = mysqli_query($connection,
        "SELECT p.po_id, p.po_date, p.amount, p.potype, p.vat, p.rec_status,
                s.sup_name,
                u.username AS raised_by,
                (SELECT COUNT(*) FROM kp_po_items pi WHERE pi.po_id = p.po_id) AS item_count,
                (SELECT COUNT(*) FROM kp_po_items pi WHERE pi.po_id = p.po_id AND pi.rec_qty >= pi.qty) AS items_received,
                (SELECT COUNT(*) FROM kp_grn g WHERE g.po_id = p.po_id AND g.status IN(1,3)) AS grn_count
         FROM kp_po p
         LEFT JOIN kp_suppliers s ON s.sup_id = p.sup_id
         LEFT JOIN kp_users    u ON u.user_id = p.ord_user
         WHERE p.appr_status = 1 AND p.rec_status IN (2, 3)
         ORDER BY p.po_id DESC");

    $rows = [];
    if ($r) {
        while ($row = mysqli_fetch_assoc($r)) {
            $row['amount']         = (float)$row['amount'];
            $row['item_count']     = (int)$row['item_count'];
            $row['items_received'] = (int)$row['items_received'];
            $row['grn_count']      = (int)$row['grn_count'];
            $row['rec_status']     = (int)$row['rec_status'];
            $row['is_partial']     = ($row['rec_status'] === 3) ? 1 : 0;
            $rows[] = $row;
        }
    }
    exit(json_encode(['success' => true, 'pos' => $rows]));
}

// ═════════════════════════════════════════════════════════════════════════════
// get_po_detail — Single PO header + items from snap JSON
// ═════════════════════════════════════════════════════════════════════════════
if ($action === 'get_po_detail') {
    $po_id = (int)($_REQUEST['po_id'] ?? 0);
    if (!$po_id) exit(json_encode(['success' => false, 'message' => 'Missing po_id']));

    // Header
    $r = mysqli_query($connection,
        "SELECT p.*, s.sup_name, s.sup_email, s.sup_phone,
                u.username AS raised_by
         FROM kp_po p
         LEFT JOIN kp_suppliers s ON s.sup_id = p.sup_id
         LEFT JOIN kp_users    u ON u.user_id = p.ord_user
         WHERE p.po_id = '$po_id' LIMIT 1");
    $po = $r ? mysqli_fetch_assoc($r) : null;
    if (!$po) exit(json_encode(['success' => false, 'message' => 'PO not found']));
    $po['amount']     = (float)$po['amount'];
    $po['rec_status'] = (int)($po['rec_status'] ?? 0);
    $po['is_partial'] = ($po['rec_status'] === 3) ? 1 : 0;

    // Items — primary info from snap JSON + received tracking
    $r = mysqli_query($connection,
        "SELECT id, po_id, item_id, variant_id, qty, price, disc, total, snap,
                rec_qty, rec_price, rec_total, rec_status
         FROM kp_po_items
         WHERE po_id = '$po_id'
         ORDER BY id ASC");
    $items = [];
    if ($r) {
        while ($row = mysqli_fetch_assoc($r)) {
            $snap = [];
            if (!empty($row['snap'])) {
                $decoded = json_decode($row['snap'], true);
                if (is_array($decoded)) $snap = $decoded;
            }

            // Extract attributes for display (Size, Color from attributes array)
            $attrs       = isset($snap['attributes']) && is_array($snap['attributes']) ? $snap['attributes'] : [];
            $size_name   = '';
            $color_name  = '';
            $brand_name  = '';
            foreach ($attrs as $a) {
                $n = strtolower($a['attr_name'] ?? '');
                if ($n === 'size')  $size_name  = $a['display'] ?? $a['value'] ?? '';
                if ($n === 'color') $color_name = $a['display'] ?? $a['value'] ?? '';
                if ($n === 'brand') $brand_name = $a['display'] ?? $a['value'] ?? '';
            }

            $items[] = [
                'id'           => (int)$row['id'],
                'item_id'      => (int)$row['item_id'],
                'qty'          => (float)$row['qty'],
                'price'        => (float)$row['price'],
                'disc'         => (float)$row['disc'],
                'total'        => (float)$row['total'],
                'rec_qty'      => (float)($row['rec_qty'] ?? 0),
                'rec_status'   => (int)($row['rec_status'] ?? 0),
                'remaining_qty'=> max(0, (float)$row['qty'] - (float)($row['rec_qty'] ?? 0)),
                'item_name'    => $snap['item_name']    ?? $snap['name']    ?? 'Unknown Item',
                'product_code' => $snap['variation_code'] ?? $snap['product_code'] ?? $snap['code'] ?? '',
                'variant_id'   => (int)($snap['variant_id'] ?? $snap['stock_id'] ?? $row['variant_id'] ?? 0),
                'barcode'      => $snap['barcode'] ?? '',
                'attr_summary' => $snap['attr_summary'] ?? '',
                'size'         => $size_name  ?: ($snap['size']  ?? $snap['size_name']  ?? '–'),
                'color'        => $color_name ?: ($snap['color'] ?? $snap['clr_name']   ?? '–'),
                'brand'        => $brand_name ?: ($snap['brand'] ?? $snap['brand_name'] ?? '–'),
                'uom'          => $snap['uom'] ?? $snap['uom_name'] ?? '–',
                'snap'         => $snap,
            ];
        }
    }

    exit(json_encode(['success' => true, 'po' => $po, 'items' => $items]));
}

// ═════════════════════════════════════════════════════════════════════════════
// create_grn — Create GRN from a PO with received quantities
// POST: po_id, inv, dnote, day, items (JSON array [{po_item_id, recv_qty}])
// ═════════════════════════════════════════════════════════════════════════════
if ($action === 'create_grn') {
    $po_id     = (int)($_POST['po_id'] ?? 0);
    $inv       = esc($connection, $_POST['inv']   ?? '');
    $dnote     = esc($connection, $_POST['dnote'] ?? '');
    $day       = esc($connection, $_POST['day']   ?? date('Y-m-d'));
    $tax       = esc($connection, $_POST['tax'] ?? '0');
    $items_raw = $_POST['items'] ?? '[]';
    $recv_map  = [];  // po_item_id => recv_qty
    $extra_items = [];

    if (!$po_id) exit(json_encode(['success' => false, 'message' => 'Missing po_id']));

    $items_arr = json_decode($items_raw, true);
    if (is_array($items_arr)) {
        foreach ($items_arr as $it) {
            if (!empty($it['po_item_id'])) {
                $recv_map[(int)$it['po_item_id']] = [
                    'qty' => (float)$it['recv_qty'],
                    'price' => isset($it['recv_price']) ? (float)$it['recv_price'] : 0,
                    'extra' => $it['extra'] ?? ''
                ];
            } else if (!empty($it['item_name'])) {
                // New item not in PO
                $extra_items[] = $it;
            }
        }
    }

    // Get PO header for sup_id
    $rpo = mysqli_query($connection, "SELECT * FROM kp_po WHERE po_id = '$po_id' LIMIT 1");
    $po  = $rpo ? mysqli_fetch_assoc($rpo) : null;
    if (!$po) exit(json_encode(['success' => false, 'message' => 'PO not found']));
    $sup_id = (int)$po['sup_id'];

    // Get PO items
    $rit = mysqli_query($connection,
        "SELECT id, item_id, qty, price, disc, snap FROM kp_po_items WHERE po_id = '$po_id' ORDER BY id ASC");
    $po_items = [];
    if ($rit) {
        while ($row = mysqli_fetch_assoc($rit)) $po_items[] = $row;
    }
    if (!$po_items) exit(json_encode(['success' => false, 'message' => 'No items on PO']));

    // ── Generate GRN ID via kp_grn_id counter (BEFORE transaction) ───────────
    $r = mysqli_query($connection, "SELECT grn_id FROM kp_grn_id");
    if (mysqli_num_rows($r) > 0) {
        mysqli_query($connection, "UPDATE kp_grn_id SET grn_id = grn_id + 1");
    } else {
        mysqli_query($connection, "INSERT INTO kp_grn_id(grn_id) VALUES(1)");
    }
    $r2   = mysqli_query($connection, "SELECT grn_id FROM kp_grn_id");
    $gRow = mysqli_fetch_assoc($r2);
    $grn_id = (int)($gRow['grn_id'] ?? 0);
    if (!$grn_id) exit(json_encode(['success' => false, 'message' => 'Could not generate GRN ID']));

    // Calculate received total
    $total_received = 0;
    $item_count     = 0;
    foreach ($po_items as $pit) {
        $recv = isset($recv_map[(int)$pit['id']]) ? $recv_map[(int)$pit['id']] : ['qty'=>(float)$pit['qty'],'price'=>(float)$pit['price'],'extra'=>''];
        $recv_qty = (float)$recv['qty'];
        $recv_price = (float)$recv['price'] ?: (float)$pit['price'];
        if ($recv_qty <= 0) continue;
        $total_received += $recv_qty * $recv_price;
        $item_count++;
    }
    // Add extra items to total
    foreach ($extra_items as $ex) {
        $ex_qty = (float)($ex['recv_qty'] ?? 0);
        $ex_price = (float)($ex['recv_price'] ?? 0);
        if ($ex_qty > 0 && $ex_price > 0) {
            $total_received += $ex_qty * $ex_price;
            $item_count++;
        }
    }
    $total_e = round($total_received, 2);

    mysqli_begin_transaction($connection);
    try {
        // ── INSERT kp_grn ────────────────────────────────────────────────────
        $sql_grn = "INSERT INTO kp_grn (grn_id, po_id, sup_id, inv, dnote, day, datime, user_id, status, tostore_status, amount)
                    VALUES ('$grn_id','$po_id','$sup_id','$inv','$dnote','$day',NOW(),'$user_id',3,2,'$total_e')";
        if (!mysqli_query($connection, $sql_grn)) {
            throw new Exception('GRN header insert failed: ' . mysqli_error($connection));
        }

        // ── INSERT kp_grn_status ─────────────────────────────────────────────
        $sql_status = "INSERT INTO kp_grn_status (grn_id, user_id, po_id, datime)
                       VALUES ('$grn_id','$user_id','$po_id',NOW())";
        if (!mysqli_query($connection, $sql_status)) {
            throw new Exception('GRN status insert failed: ' . mysqli_error($connection));
        }

        // ── INSERT kp_grn_items ──────────────────────────────────────────────
        $inserted = 0;
        foreach ($po_items as $pit) {
            $snap = [];
            if (!empty($pit['snap'])) {
                $decoded = json_decode($pit['snap'], true);
                if (is_array($decoded)) $snap = $decoded;
            }

            $item_id      = (int)$pit['item_id'];
            $ordered_qty  = (float)$pit['qty'];
            $recv = isset($recv_map[(int)$pit['id']]) ? $recv_map[(int)$pit['id']] : ['qty'=>$ordered_qty,'price'=>(float)$pit['price'],'extra'=>''];
            $recv_qty     = (float)$recv['qty'];
            $recv_price   = (float)$recv['price'] ?: (float)$pit['price'];
            $item_total   = round($recv_qty * $recv_price, 2);
            $extra        = esc($connection, $recv['extra'] ?? '');

            if ($recv_qty <= 0) continue;  // skip zero-qty items

            // Map snap fields to actual kp_grn_items columns
            $product_code = esc($connection, $snap['variation_code'] ?? $snap['product_code'] ?? $snap['code'] ?? '');
            $barcode      = esc($connection, $snap['barcode'] ?? '');
            $variant_id   = (int)($snap['variant_id'] ?? $snap['stock_id'] ?? 0);
            $item_name    = $snap['item_name'] ?? $snap['name'] ?? '';
            $attr_summary = $snap['attr_summary'] ?? '';
            $receipt_desc = esc($connection, trim($item_name . ($attr_summary ? ' | ' . $attr_summary : '')));

            $sql_item = "INSERT INTO kp_grn_items
                           (grn_id, item_id, product_code, barcode, receipt_desc,
                            qty, price, total, user_id, day, datime,
                            confirm_qty, confirm_status, tostore_status, s2s, extra)
                         VALUES
                           ('$grn_id','$item_id','$product_code','$barcode','$receipt_desc',
                            '$ordered_qty','$recv_price','$item_total','$user_id','$day',NOW(),
                            '$recv_qty', 1, 2, '$variant_id', '$extra')";

            if (!mysqli_query($connection, $sql_item)) {
                throw new Exception('GRN item insert failed for item_id=' . $item_id . ': ' . mysqli_error($connection));
            }
            $inserted++;
        }

        // Insert extra items not in PO (including requested variants with snapshot)
        foreach ($extra_items as $ex) {
            $ex_name    = esc($connection, $ex['item_name'] ?? '');
            $ex_code    = esc($connection, $ex['product_code'] ?? '');
            $ex_barcode = esc($connection, $ex['barcode'] ?? '');
            $ex_qty     = (float)($ex['recv_qty'] ?? 0);
            $ex_price   = (float)($ex['recv_price'] ?? 0);
            $ex_total   = round($ex_qty * $ex_price, 2);
            $ex_extra   = esc($connection, $ex['extra'] ?? '');
            $ex_uom     = esc($connection, $ex['uom'] ?? '');
            $ex_brand   = esc($connection, $ex['brand'] ?? '');
            $ex_size    = esc($connection, $ex['size'] ?? '');
            $ex_color   = esc($connection, $ex['color'] ?? '');
            $ex_var_id  = (int)($ex['variant_id'] ?? 0);
            $ex_is_req  = (int)($ex['is_request'] ?? 0);
            $ex_req_id  = (int)($ex['request_id'] ?? 0);

            // Build rich description with snapshot data
            $desc_parts = [$ex_name];
            if ($ex_brand) $desc_parts[] = $ex_brand;
            if ($ex_size)  $desc_parts[] = $ex_size;
            if ($ex_color) $desc_parts[] = $ex_color;
            if ($ex_uom)   $desc_parts[] = $ex_uom;
            $ex_desc = esc($connection, implode(' | ', $desc_parts));

            // Add request info to extra field
            if ($ex_is_req && $ex_req_id) {
                $ex_extra .= ($ex_extra ? '; ' : '') . 'Variant request #' . $ex_req_id . ' (pending stock approval)';
                $ex_extra = esc($connection, $ex_extra);
            }

            if ($ex_qty > 0 && $ex_price > 0) {
                $sql_item = "INSERT INTO kp_grn_items
                   (grn_id, item_id, product_code, barcode, receipt_desc,
                    qty, price, total, user_id, day, datime,
                    confirm_qty, confirm_status, tostore_status, s2s, extra)
                 VALUES
                   ('$grn_id',0,'$ex_code','$ex_barcode','$ex_desc',
                    '$ex_qty','$ex_price','$ex_total','$user_id','$day',NOW(),
                    '$ex_qty', 1, 2, '$ex_var_id', '$ex_extra')";
                if (!mysqli_query($connection, $sql_item)) {
                    throw new Exception('GRN extra item insert failed: ' . mysqli_error($connection));
                }
                $inserted++;
            }
        }

        if ($inserted === 0) {
            throw new Exception('No items were received (all quantities are zero)');
        }

        // ── Finalize GRN → status=1, tostore_status=2 ────────────────────────
        mysqli_query($connection,
            "UPDATE kp_grn SET status=1, tostore_status=2 WHERE grn_id='$grn_id'");

        // ── Update per-item received quantities in kp_po_items ───────────────
        foreach ($po_items as $pit) {
            $pid = (int)$pit['id'];
            $recv = isset($recv_map[$pid]) ? $recv_map[$pid] : null;
            if (!$recv) continue;
            $rq = (float)$recv['qty'];
            $rp = (float)$recv['price'] ?: (float)$pit['price'];
            $rt = round($rq * $rp, 2);
            if ($rq > 0) {
                mysqli_query($connection,
                    "UPDATE kp_po_items
                     SET rec_qty    = rec_qty + $rq,
                         rec_price  = $rp,
                         rec_total  = rec_total + $rt,
                         rec_user   = $user_id,
                         rec_status = CASE WHEN (rec_qty + $rq) >= qty THEN 1 ELSE 2 END
                     WHERE id = $pid");
            }
        }

        // ── Determine PO receiving status (partial vs complete) ──────────────
        $po_check = mysqli_query($connection,
            "SELECT COUNT(*) AS total_items,
                    SUM(CASE WHEN rec_qty >= qty THEN 1 ELSE 0 END) AS fully_received
             FROM kp_po_items WHERE po_id = '$po_id'");
        $pc = $po_check ? mysqli_fetch_assoc($po_check) : ['total_items'=>0,'fully_received'=>0];
        $all_received = ((int)$pc['total_items'] > 0 && (int)$pc['fully_received'] === (int)$pc['total_items']);

        if ($all_received) {
            // Fully received — close the PO
            mysqli_query($connection, "UPDATE kp_po SET rec_status=1 WHERE po_id='$po_id'");
        } else {
            // Partially received — keep PO open for further receiving
            mysqli_query($connection, "UPDATE kp_po SET rec_status=3 WHERE po_id='$po_id'");
        }

        // ── Log ──────────────────────────────────────────────────────────────
        if (function_exists('logs')) {
            logs('../../', 'back/kug/rec/', 'GRN', $grn_id, '-', '-', 'GRN CREATED FROM PO ' . $po_id);
        }

        mysqli_commit($connection);

        exit(json_encode([
            'success'      => true,
            'grn_id'       => $grn_id,
            'item_count'   => $inserted,
            'po_complete'  => $all_received ? 1 : 0,
            'message'      => $all_received
                ? "GRN #$grn_id created with $inserted items. PO fully received."
                : "GRN #$grn_id created with $inserted items. PO partially received — remaining items can still be received.",
        ]));

    } catch (Exception $e) {
        mysqli_rollback($connection);
        exit(json_encode(['success' => false, 'message' => $e->getMessage()]));
    }
}

// ═════════════════════════════════════════════════════════════════════════════
// view_grn — GRN header + all items with store status
// ═════════════════════════════════════════════════════════════════════════════
if ($action === 'view_grn') {
    $grn_id = (int)($_REQUEST['grn_id'] ?? 0);
    if (!$grn_id) exit(json_encode(['success' => false, 'message' => 'Missing grn_id']));

    // Header
    $r = mysqli_query($connection,
        "SELECT g.grn_id, g.po_id, g.inv, g.dnote, g.day, g.datime,
                g.status, g.tostore_status, g.amount,
                s.sup_name,
                u.username AS received_by
         FROM kp_grn g
         LEFT JOIN kp_suppliers s ON s.sup_id = g.sup_id
         LEFT JOIN kp_users    u ON u.user_id = g.user_id
         WHERE g.grn_id = '$grn_id' LIMIT 1");
    $grn = $r ? mysqli_fetch_assoc($r) : null;
    if (!$grn) exit(json_encode(['success' => false, 'message' => 'GRN not found']));
    $grn['amount'] = (float)$grn['amount'];

    // Items
    $ri = mysqli_query($connection,
        "SELECT gi.id, gi.item_id, gi.product_code, gi.barcode, gi.receipt_desc,
                gi.qty, gi.confirm_qty, gi.price, gi.total,
                gi.confirm_status, gi.tostore_status,
                gi.day, gi.datime, gi.tostore_datime,
                gi.s2s AS variant_id,
                COALESCE(im.name,'') AS item_name,
                COALESCE(v.receipt_desc,'') AS var_receipt_desc,
                COALESCE(v.variation_code,'') AS variation_code,
                COALESCE(vu.uom_name,'') AS uom_name,
                v.buy_price AS var_buy_price, v.sell_price AS var_sell_price,
                u.username AS stored_by
         FROM kp_grn_items gi
         LEFT JOIN kp_inventory_master im ON im.item_id = gi.item_id
         LEFT JOIN kp_inventory_stock_variants v ON v.variant_id = gi.s2s AND v.is_active = 1
         LEFT JOIN kp_items_uom vu ON vu.uom_id = v.uom_id
         LEFT JOIN kp_users u ON u.user_id = gi.tostore_user
         WHERE gi.grn_id = '$grn_id'
         ORDER BY gi.id ASC");
    $items = [];
    if ($ri) {
        while ($row = mysqli_fetch_assoc($ri)) {
            $row['qty']         = (float)$row['qty'];
            $row['confirm_qty'] = (float)$row['confirm_qty'];
            $row['price']       = (float)$row['price'];
            $row['total']       = (float)$row['total'];
            // Display name: prefer variant receipt_desc, then GRN receipt_desc, then master name
            $row['item_name'] = ($row['var_receipt_desc'] ?: $row['receipt_desc']) ?: ($row['item_name'] ?: ('Item #' . $row['item_id']));
            $items[] = $row;
        }
    }

    exit(json_encode(['success' => true, 'grn' => $grn, 'items' => $items]));
}

// ═════════════════════════════════════════════════════════════════════════════
// list_grns — Ongoing + recent complete GRNs
// ═════════════════════════════════════════════════════════════════════════════
if ($action === 'list_grns') {
    $r = mysqli_query($connection,
        "SELECT g.grn_id, g.po_id, g.inv, g.dnote, g.day, g.status, g.amount,
                s.sup_name,
                u.username AS received_by,
                (SELECT COUNT(*) FROM kp_grn_items gi WHERE gi.grn_id = g.grn_id) AS item_count,
                (SELECT COUNT(*) FROM kp_grn_items gi WHERE gi.grn_id = g.grn_id AND gi.tostore_status = 1) AS items_stored
         FROM kp_grn g
         LEFT JOIN kp_suppliers s ON s.sup_id = g.sup_id
         LEFT JOIN kp_users    u ON u.user_id = g.user_id
         WHERE g.status IN (1, 3)
         ORDER BY g.grn_id DESC
         LIMIT 50");
    $rows = [];
    if ($r) {
        while ($row = mysqli_fetch_assoc($r)) {
            $row['grn_id']       = (int)$row['grn_id'];
            $row['amount']       = (float)$row['amount'];
            $row['item_count']   = (int)$row['item_count'];
            $row['items_stored'] = (int)$row['items_stored'];
            $rows[] = $row;
        }
    }
    exit(json_encode(['success' => true, 'grns' => $rows]));
}

// ═════════════════════════════════════════════════════════════════════════════
// get_grn_detail — GRN header + items (legacy compat)
// ═════════════════════════════════════════════════════════════════════════════
if ($action === 'get_grn_detail') {
    $grn_id = (int)($_REQUEST['grn_id'] ?? 0);
    if (!$grn_id) exit(json_encode(['success' => false, 'message' => 'Missing grn_id']));
    // Delegate to view_grn
    $_REQUEST['action'] = 'view_grn';
    // Re-enter by including self — instead just duplicate the query
    $r = mysqli_query($connection,
        "SELECT g.*, s.sup_name, u.username AS received_by
         FROM kp_grn g
         LEFT JOIN kp_suppliers s ON s.sup_id = g.sup_id
         LEFT JOIN kp_users    u ON u.user_id = g.user_id
         WHERE g.grn_id = '$grn_id' LIMIT 1");
    $grn = $r ? mysqli_fetch_assoc($r) : null;
    if (!$grn) exit(json_encode(['success' => false, 'message' => 'GRN not found']));

    $ri = mysqli_query($connection,
        "SELECT gi.id, gi.item_id, gi.product_code, gi.barcode, gi.receipt_desc,
                gi.qty, gi.confirm_qty, gi.price, gi.total,
                gi.confirm_status, gi.tostore_status, gi.day,
                COALESCE(im.name,'') AS item_name,
                COALESCE(v.receipt_desc,'') AS var_receipt_desc
         FROM kp_grn_items gi
         LEFT JOIN kp_inventory_master im ON im.item_id = gi.item_id
         LEFT JOIN kp_inventory_stock_variants v ON v.variant_id = gi.s2s AND v.is_active = 1
         WHERE gi.grn_id = '$grn_id'
         ORDER BY gi.id ASC");
    $items = [];
    if ($ri) {
        while ($row = mysqli_fetch_assoc($ri)) {
            $row['qty']         = (float)$row['qty'];
            $row['confirm_qty'] = (float)$row['confirm_qty'];
            $row['price']       = (float)$row['price'];
            $items[] = $row;
        }
    }
    exit(json_encode(['success' => true, 'grn' => $grn, 'items' => $items]));
}

// ═════════════════════════════════════════════════════════════════════════════
// search_variant — Search kp_inventory_stock_variants by keyword
// GET: q (search term)
// ═════════════════════════════════════════════════════════════════════════════
if ($action === 'search_variant') {
    $q = trim($_REQUEST['q'] ?? '');
    if (strlen($q) < 2) exit(json_encode(['success' => false, 'message' => 'Search term too short']));
    $like = '%' . esc($connection, $q) . '%';

    $r = mysqli_query($connection,
        "SELECT v.variant_id, v.stock_id, v.item_id, v.variation_code, v.sku_no,
                v.barcode, v.uom_id, v.receipt_desc, v.attributes_json,
                v.buy_price, v.sell_price,
                i.name AS item_name,
                u.uom_name
         FROM kp_inventory_stock_variants v
         LEFT JOIN kp_inventory_master i ON i.item_id = v.item_id
         LEFT JOIN kp_items_uom u ON u.uom_id = v.uom_id
         WHERE v.is_active = 1
           AND (i.name LIKE '$like'
                OR v.receipt_desc LIKE '$like'
                OR v.variation_code LIKE '$like'
                OR v.barcode LIKE '$like'
                OR v.sku_no LIKE '$like')
         ORDER BY i.name ASC
         LIMIT 20");

    $variants = [];
    if ($r) {
        while ($row = mysqli_fetch_assoc($r)) {
            // Parse attributes for brand/size/color
            $attrs = [];
            if (!empty($row['attributes_json'])) {
                $decoded = json_decode($row['attributes_json'], true);
                if (is_array($decoded)) $attrs = $decoded;
            }
            $brand = ''; $size = ''; $color = '';
            foreach ($attrs as $a) {
                $n = strtolower($a['attr_name'] ?? '');
                if ($n === 'brand') $brand = $a['display'] ?? $a['value'] ?? '';
                if ($n === 'size')  $size  = $a['display'] ?? $a['value'] ?? '';
                if ($n === 'color') $color = $a['display'] ?? $a['value'] ?? '';
            }
            $variants[] = [
                'variant_id'    => (int)$row['variant_id'],
                'stock_id'      => $row['stock_id'],
                'item_id'       => (int)$row['item_id'],
                'item_name'     => $row['item_name'] ?: $row['receipt_desc'],
                'variation_code'=> $row['variation_code'],
                'product_code'  => $row['variation_code'],
                'barcode'       => $row['barcode'],
                'receipt_desc'  => $row['receipt_desc'],
                'uom'           => $row['uom_name'] ?: '–',
                'brand'         => $brand,
                'size'          => $size,
                'color'         => $color,
                'buy_price'     => (float)$row['buy_price'],
                'sell_price'    => (float)$row['sell_price'],
            ];
        }
    }
    exit(json_encode(['success' => true, 'variants' => $variants]));
}

// ═════════════════════════════════════════════════════════════════════════════
// request_variant — Request a new variant to be added to stock
// POST: item_name, brand, size, color, uom, notes
// Creates table if missing, inserts request, returns request_id
// ═════════════════════════════════════════════════════════════════════════════
if ($action === 'request_variant') {
    // Auto-create table on first use
    mysqli_query($connection, "CREATE TABLE IF NOT EXISTS kp_variant_requests (
        id          INT AUTO_INCREMENT PRIMARY KEY,
        item_name   VARCHAR(255) NOT NULL,
        brand       VARCHAR(100) DEFAULT '',
        size_val    VARCHAR(100) DEFAULT '',
        color       VARCHAR(100) DEFAULT '',
        uom         VARCHAR(50)  DEFAULT '',
        notes       TEXT,
        status      TINYINT DEFAULT 0 COMMENT '0=pending,1=approved,2=rejected',
        user_id     INT DEFAULT 0,
        created_at  DATETIME DEFAULT CURRENT_TIMESTAMP,
        approved_at DATETIME NULL,
        approved_by INT DEFAULT 0
    ) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4");

    $name  = esc($connection, $_POST['item_name'] ?? '');
    $brand = esc($connection, $_POST['brand']     ?? '');
    $size  = esc($connection, $_POST['size_val']  ?? $_POST['size'] ?? '');
    $color = esc($connection, $_POST['color']     ?? '');
    $uom   = esc($connection, $_POST['uom']       ?? '');
    $notes = esc($connection, $_POST['notes']     ?? '');

    if (!$name) exit(json_encode(['success' => false, 'message' => 'Item name is required']));

    $sql = "INSERT INTO kp_variant_requests (item_name, brand, size_val, color, uom, notes, user_id, created_at)
            VALUES ('$name','$brand','$size','$color','$uom','$notes','$user_id',NOW())";
    if (!mysqli_query($connection, $sql)) {
        exit(json_encode(['success' => false, 'message' => 'Insert failed: ' . mysqli_error($connection)]));
    }
    $req_id = (int)mysqli_insert_id($connection);
    exit(json_encode(['success' => true, 'request_id' => $req_id, 'message' => 'Variant request #' . $req_id . ' submitted.']));
}

// ═════════════════════════════════════════════════════════════════════════════
// kpi_stats — KPI counts for the dashboard
// ═════════════════════════════════════════════════════════════════════════════
if ($action === 'kpi_stats') {
    $today = date('Y-m-d');
    $pending = 0; $partial = 0; $today_grns = 0; $total_grns = 0;

    // Pending POs (approved, not yet received)
    $r1 = mysqli_query($connection,
        "SELECT COUNT(*) AS c FROM kp_po WHERE appr_status = 1 AND rec_status = 2");
    if ($r1) $pending = (int)mysqli_fetch_assoc($r1)['c'];

    // Partially received POs
    $r2 = mysqli_query($connection,
        "SELECT COUNT(*) AS c FROM kp_po WHERE appr_status = 1 AND rec_status = 3");
    if ($r2) $partial = (int)mysqli_fetch_assoc($r2)['c'];

    // GRNs created today
    $r3 = mysqli_query($connection,
        "SELECT COUNT(*) AS c FROM kp_grn WHERE DATE(datime) = '$today'");
    if ($r3) $today_grns = (int)mysqli_fetch_assoc($r3)['c'];

    // Total GRNs
    $r4 = mysqli_query($connection,
        "SELECT COUNT(*) AS c FROM kp_grn WHERE status IN(1,3)");
    if ($r4) $total_grns = (int)mysqli_fetch_assoc($r4)['c'];

    exit(json_encode([
        'success'    => true,
        'pending'    => $pending,
        'partial'    => $partial,
        'today_grns' => $today_grns,
        'total_grns' => $total_grns,
    ]));
}

exit(json_encode(['success' => false, 'message' => 'Unknown action: ' . htmlspecialchars($action)]));
