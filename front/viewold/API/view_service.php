<?php

if (!function_exists('view_service_modal_error_html')) {
    function view_service_modal_error_html($message)
    {
        return '<div class="modal-content"><div class="modal-body"><div class="alert alert-danger" style="margin-bottom:0;">'
            . view_escape($message)
            . '</div></div></div>';
    }
}

if (!function_exists('view_service_health')) {
    function view_service_health($user_id)
    {
        $user_id = (int)$user_id;

        return [
            'ok' => $user_id > 0,
            'message' => $user_id > 0 ? 'View API is ready.' : 'User session not found.',
            'status' => $user_id > 0 ? 200 : 401,
            'context' => [
                'user_id' => $user_id,
            ],
        ];
    }
}

if (!function_exists('view_service_bootstrap')) {
    function view_service_bootstrap($connection, $user_id, $page = 1, $search = '')
    {
        $user_id = (int)$user_id;
        if ($user_id < 1) {
            return [
                'ok' => false,
                'message' => 'User session not found.',
                'status' => 401,
            ];
        }

        $context = view_repo_user_context($connection, $user_id);
        $shift = (string)($context['shift'] ?? '');
        $sales_page = view_repo_fetch_sales_page($connection, $user_id, $shift, $page, 10, $search);
        $sold_rows = view_repo_fetch_sold_rows($connection, $shift, $user_id);

        return [
            'ok' => true,
            'message' => 'View bootstrap loaded.',
            'status' => 200,
            'context' => $context,
            'counts' => [],
            'sales_rows' => $sales_page['rows'],
            'pagination' => $sales_page,
            'sold_rows' => $sold_rows,
            'items' => $sold_rows,
            'merge' => [],
            'merge_cards' => [],
        ];
    }
}

if (!function_exists('view_service_merge_add_sale')) {
    function view_service_merge_add_sale($connection, $user_id, $sale_id)
    {
        $user_id = (int)$user_id;
        $sale_id = (int)$sale_id;
        if ($user_id < 1) {
            return ['ok' => false, 'message' => 'Session expired.', 'status' => 401];
        }
        if ($sale_id < 1) {
            return ['ok' => false, 'message' => 'Invalid bill selected.', 'status' => 400];
        }

        $owner_id = (int)get_column('kp_sales', 'user_id', "sale_id='$sale_id'");
        $draft = function_exists('cash_active_merge_draft') ? cash_active_merge_draft($user_id, true) : null;
        if (!$draft) {
            return ['ok' => false, 'message' => 'Could not open your merge draft.', 'status' => 400];
        }

        $merge_id = (int)($draft['merge_id'] ?? 0);
        if ($owner_id !== $user_id) {
            $approved = false;
            $req_r = mysqli_query(
                $connection,
                "SELECT requester_sale_id
                 FROM kp_bill_merge_request
                 WHERE merge_id='$merge_id' AND requester_user_id='$user_id' AND target_sale_id='$sale_id' AND status=2
                 ORDER BY request_id DESC LIMIT 1"
            );
            if ($req_r && mysqli_num_rows($req_r) > 0) {
                $req = mysqli_fetch_assoc($req_r);
                $requester_sale_id = (int)($req['requester_sale_id'] ?? 0);
                $in_draft = (int)column_count('kp_bill_merge_items', 'id', "merge_id='$merge_id' AND sale_id='$requester_sale_id' AND status=1");
                $approved = $in_draft > 0;
            }
            if (!$approved) {
                return ['ok' => false, 'message' => 'This bill still needs an approved request tied to your current merge draft.', 'status' => 400];
            }
        }

        $error_message = '';
        $added = function_exists('cash_add_sale_to_merge_draft') ? cash_add_sale_to_merge_draft($merge_id, $sale_id, $error_message) : false;
        if (!$added) {
            return ['ok' => false, 'message' => $error_message ?: 'Could not add the bill to the draft.', 'status' => 400];
        }

        return ['ok' => true, 'message' => 'Bill added to merge draft.', 'status' => 200, 'merge_id' => $merge_id];
    }
}

if (!function_exists('view_service_merge_remove_sale')) {
    function view_service_merge_remove_sale($connection, $user_id, $sale_id)
    {
        $user_id = (int)$user_id;
        $sale_id = (int)$sale_id;
        if ($user_id < 1) {
            return ['ok' => false, 'message' => 'Session expired.', 'status' => 401];
        }
        if ($sale_id < 1) {
            return ['ok' => false, 'message' => 'Invalid bill selected.', 'status' => 400];
        }

        $draft = function_exists('cash_active_merge_draft') ? cash_active_merge_draft($user_id, false) : null;
        if (!$draft) {
            return ['ok' => false, 'message' => 'No active merge draft was found.', 'status' => 400];
        }

        if (function_exists('cash_remove_sale_from_merge_draft')) {
            cash_remove_sale_from_merge_draft((int)$draft['merge_id'], $sale_id);
        }

        return ['ok' => true, 'message' => 'Bill removed from merge draft.', 'status' => 200];
    }
}

if (!function_exists('view_service_merge_request')) {
    function view_service_merge_request($connection, $user_id, $my_sale_id, $target_bill_no)
    {
        $user_id = (int)$user_id;
        $my_sale_id = (int)$my_sale_id;
        $target_bill_no = (int)$target_bill_no;
        if ($user_id < 1) {
            return ['ok' => false, 'message' => 'Session expired.', 'status' => 401];
        }
        if ($my_sale_id < 1 || $target_bill_no < 1) {
            return ['ok' => false, 'message' => 'Choose your bill and enter the target bill number.', 'status' => 400];
        }

        $my_owner = (int)get_column('kp_sales', 'user_id', "sale_id='$my_sale_id'");
        if ($my_owner !== $user_id) {
            return ['ok' => false, 'message' => 'You can only request merges using your own bill.', 'status' => 400];
        }

        $draft = function_exists('cash_active_merge_draft') ? cash_active_merge_draft($user_id, true) : null;
        if (!$draft) {
            return ['ok' => false, 'message' => 'Could not open your merge draft.', 'status' => 400];
        }
        $merge_id = (int)$draft['merge_id'];

        $my_in_draft = (int)column_count('kp_bill_merge_items', 'id', "merge_id='$merge_id' AND sale_id='$my_sale_id' AND status=1");
        if ($my_in_draft < 1) {
            return ['ok' => false, 'message' => 'Add your bill to the merge basket first before requesting another user bill.', 'status' => 400];
        }

        $target_sale_id = $target_bill_no;
        if ($target_sale_id < 1 || (int)get_column('kp_sales', 'sale_id', "sale_id='$target_sale_id'") < 1) {
            return ['ok' => false, 'message' => 'The target bill could not be found.', 'status' => 400];
        }

        $target_owner = (int)get_column('kp_sales', 'user_id', "sale_id='$target_sale_id'");
        if ($target_owner < 1) {
            return ['ok' => false, 'message' => 'The target bill owner could not be found.', 'status' => 400];
        }
        if ($target_owner === $user_id) {
            return ['ok' => false, 'message' => 'That bill belongs to you already. Merge it directly instead.', 'status' => 400];
        }

        if (
            function_exists('cash_sale_in_open_merge') &&
            (cash_sale_in_open_merge($my_sale_id, cash_merge_root_sale_id($my_sale_id)) ||
             cash_sale_in_open_merge($target_sale_id, cash_merge_root_sale_id($target_sale_id)))
        ) {
            return ['ok' => false, 'message' => 'One of the bills is already in an open merge.', 'status' => 400];
        }

        $pending_exists = (int)column_count(
            'kp_bill_merge_request',
            'request_id',
            "merge_id='$merge_id' AND status=1 AND requester_sale_id='$my_sale_id' AND target_sale_id='$target_sale_id'"
        );
        if ($pending_exists > 0) {
            return ['ok' => false, 'message' => 'A pending request already exists for these bills.', 'status' => 400];
        }

        mysqli_query(
            $connection,
            "INSERT INTO kp_bill_merge_request (merge_id, requester_user_id, requester_sale_id, requester_root_sale_id, target_user_id, target_sale_id, target_root_sale_id, status)
             VALUES('$merge_id', '$user_id', '$my_sale_id', '" . cash_merge_root_sale_id($my_sale_id) . "', '$target_owner', '$target_sale_id', '" . cash_merge_root_sale_id($target_sale_id) . "', 1)"
        );

        return ['ok' => true, 'message' => 'Merge request sent.', 'status' => 200, 'merge_id' => $merge_id];
    }
}

if (!function_exists('view_service_merge_finalize')) {
    function view_service_merge_finalize($connection, $user_id)
    {
        $user_id = (int)$user_id;
        if ($user_id < 1) {
            return ['ok' => false, 'message' => 'Session expired.', 'status' => 401];
        }

        $draft = function_exists('cash_active_merge_draft') ? cash_active_merge_draft($user_id, false) : null;
        if (!$draft) {
            return ['ok' => false, 'message' => 'No active merge draft was found.', 'status' => 400];
        }

        $merge_id = (int)$draft['merge_id'];
        $items = function_exists('cash_merge_draft_items') ? cash_merge_draft_items($merge_id) : [];
        if (count($items) < 2) {
            return ['ok' => false, 'message' => 'Add at least two bills before finishing the merge.', 'status' => 400];
        }

        $pending_requests = (int)column_count('kp_bill_merge_request', 'request_id', "merge_id='$merge_id' AND status=1");
        if ($pending_requests > 0) {
            return ['ok' => false, 'message' => 'You still have pending merge requests on this merge. Remove them or wait for approval first.', 'status' => 400];
        }

        mysqli_query($connection, "UPDATE kp_bill_merge SET status=1, merge_state=2 WHERE merge_id='$merge_id'");
        mysqli_query($connection, "UPDATE kp_bill_merge_request SET status=4, responded_at=NOW(), note='Closed after merge finished' WHERE merge_id='$merge_id' AND status IN (1,2)");

        return ['ok' => true, 'message' => 'Merge finished successfully.', 'status' => 200, 'merge_no' => (string)($draft['merge_no'] ?? '')];
    }
}

if (!function_exists('view_service_merge_accept')) {
    function view_service_merge_accept($connection, $user_id, $request_id)
    {
        $user_id = (int)$user_id;
        $request_id = (int)$request_id;
        if ($user_id < 1) {
            return ['ok' => false, 'message' => 'Session expired.', 'status' => 401];
        }
        if ($request_id < 1) {
            return ['ok' => false, 'message' => 'Invalid merge request.', 'status' => 400];
        }

        $request_r = mysqli_query($connection, "SELECT * FROM kp_bill_merge_request WHERE request_id='$request_id' AND status=1 LIMIT 1");
        if (!$request_r || mysqli_num_rows($request_r) < 1) {
            return ['ok' => false, 'message' => 'This merge request is no longer pending.', 'status' => 400];
        }
        $request = mysqli_fetch_assoc($request_r);
        if ((int)$request['target_user_id'] !== $user_id) {
            return ['ok' => false, 'message' => 'Only the owner of the target bill can accept this request.', 'status' => 403];
        }

        mysqli_query(
            $connection,
            "UPDATE kp_bill_merge_request
             SET status=2, responded_at=NOW(), responded_by='$user_id'
             WHERE request_id='$request_id'"
        );

        return ['ok' => true, 'message' => 'Merge request accepted.', 'status' => 200, 'request_id' => $request_id];
    }
}

if (!function_exists('view_service_merge_reject')) {
    function view_service_merge_reject($connection, $user_id, $request_id)
    {
        $user_id = (int)$user_id;
        $request_id = (int)$request_id;
        if ($user_id < 1) {
            return ['ok' => false, 'message' => 'Session expired.', 'status' => 401];
        }
        if ($request_id < 1) {
            return ['ok' => false, 'message' => 'Invalid merge request.', 'status' => 400];
        }

        $request_r = mysqli_query($connection, "SELECT * FROM kp_bill_merge_request WHERE request_id='$request_id' AND status=1 LIMIT 1");
        if (!$request_r || mysqli_num_rows($request_r) < 1) {
            return ['ok' => false, 'message' => 'This merge request is no longer pending.', 'status' => 400];
        }
        $request = mysqli_fetch_assoc($request_r);
        if ((int)$request['target_user_id'] !== $user_id) {
            return ['ok' => false, 'message' => 'Only the bill owner can reject this request.', 'status' => 403];
        }

        mysqli_query(
            $connection,
            "UPDATE kp_bill_merge_request
             SET status=3, responded_at=NOW(), responded_by='$user_id'
             WHERE request_id='$request_id'"
        );

        return ['ok' => true, 'message' => 'Merge request rejected.', 'status' => 200];
    }
}

if (!function_exists('view_service_merge_close_request')) {
    function view_service_merge_close_request($connection, $user_id, $request_id)
    {
        $user_id = (int)$user_id;
        $request_id = (int)$request_id;
        if ($user_id < 1) {
            return ['ok' => false, 'message' => 'Session expired.', 'status' => 401];
        }
        if ($request_id < 1) {
            return ['ok' => false, 'message' => 'Invalid merge request.', 'status' => 400];
        }

        $request_r = mysqli_query($connection, "SELECT * FROM kp_bill_merge_request WHERE request_id='$request_id' LIMIT 1");
        if (!$request_r || mysqli_num_rows($request_r) < 1) {
            return ['ok' => false, 'message' => 'This merge request could not be found.', 'status' => 400];
        }
        $request = mysqli_fetch_assoc($request_r);
        if ((int)$request['requester_user_id'] !== $user_id) {
            return ['ok' => false, 'message' => 'Only the user who sent this request can close it.', 'status' => 403];
        }

        $status = (int)$request['status'];
        if (!in_array($status, [1, 2], true)) {
            return ['ok' => false, 'message' => 'Only pending or accepted requests can be closed.', 'status' => 400];
        }

        mysqli_query(
            $connection,
            "UPDATE kp_bill_merge_request
             SET status=4, responded_at=NOW(), responded_by='$user_id', note='Closed by requester'
             WHERE request_id='$request_id'"
        );

        return ['ok' => true, 'message' => 'Merge request closed.', 'status' => 200];
    }
}

if (!function_exists('view_service_merge_create_from_sales')) {
    function view_service_merge_create_from_sales($connection, $user_id, $sale_ids)
    {
        $user_id = (int)$user_id;
        if ($user_id < 1) {
            return ['ok' => false, 'message' => 'Session expired.', 'status' => 401];
        }

        if (function_exists('cash_cleanup_merge_requests')) {
            cash_cleanup_merge_requests();
        }

        if (!is_array($sale_ids)) {
            $sale_ids = [$sale_ids];
        }

        $sale_ids = array_values(array_unique(array_filter(array_map('intval', $sale_ids))));
        if (count($sale_ids) < 1) {
            return ['ok' => false, 'message' => 'Select at least one of your bills.', 'status' => 400];
        }

        $approved_requests = [];
        foreach ($sale_ids as $sale_id) {
            $owner = (int)get_column('kp_sales', 'user_id', "sale_id='$sale_id'");
            if ($owner !== $user_id) {
                $approved = false;
                $req_r = mysqli_query(
                    $connection,
                    "SELECT request_id, requester_sale_id
                     FROM kp_bill_merge_request
                     WHERE requester_user_id='$user_id' AND target_sale_id='$sale_id' AND status=2
                     ORDER BY request_id DESC"
                );
                while ($req_r && $req = mysqli_fetch_assoc($req_r)) {
                    $approved_requests[(int)$req['request_id']] = true;
                    $requester_sale_id = (int)($req['requester_sale_id'] ?? 0);
                    if (in_array($requester_sale_id, $sale_ids, true)) {
                        $approved = true;
                        break;
                    }
                }
                if (!$approved) {
                    return [
                        'ok' => false,
                        'message' => 'Bill #' . $sale_id . ' needs an approved merge request tied to one of your selected bills.',
                        'status' => 400,
                    ];
                }
            }
        }

        $error_message = '';
        $created = function_exists('cash_create_merge_record_from_sales')
            ? cash_create_merge_record_from_sales($user_id, $sale_ids, $error_message)
            : false;
        if (!$created) {
            return ['ok' => false, 'message' => $error_message ?: 'Could not create the merge.', 'status' => 400];
        }

        if (!empty($approved_requests)) {
            $request_ids = implode(',', array_map('intval', array_keys($approved_requests)));
            mysqli_query(
                $connection,
                "UPDATE kp_bill_merge_request
                 SET status=4, responded_at=NOW(), note='Used in merge'
                 WHERE request_id IN ($request_ids) AND status=2"
            );
        }

        return [
            'ok' => true,
            'message' => 'Merge created successfully.',
            'status' => 200,
            'merge_id' => (int)($created['merge_id'] ?? 0),
            'merge_no' => (string)($created['merge_no'] ?? ''),
            'total_amount' => number_format((float)($created['total_amount'] ?? 0), 2, '.', ''),
        ];
    }
}

if (!function_exists('view_service_bill_items_modal')) {
    function view_service_bill_items_modal($connection, $user_id, $sale_id)
    {
        $user_id = (int)$user_id;
        $sale_id = (int)$sale_id;
        if ($user_id < 1) {
            return ['ok' => false, 'message' => 'Session expired.', 'status' => 401, 'html' => view_service_modal_error_html('Session expired.')];
        }
        if ($sale_id < 1) {
            return ['ok' => false, 'message' => 'Invalid bill selected.', 'status' => 400, 'html' => view_service_modal_error_html('Invalid bill selected.')];
        }

        $current_shift = (string)get_column('kp_shift', 'shift', '1');
        $safe_shift = mysqli_real_escape_string($connection, $current_shift);
        $sale_meta = [];
        $sale_meta_query = mysqli_query(
            $connection,
            "SELECT * FROM kp_sales
             WHERE sale_id='$sale_id' AND user_id='$user_id' AND shift='$safe_shift'
             LIMIT 1"
        );
        if ($sale_meta_query) {
            $sale_meta = mysqli_fetch_assoc($sale_meta_query) ?: [];
        }
        if (empty($sale_meta)) {
            return ['ok' => false, 'message' => 'Sale not found in your current shift.', 'status' => 404, 'html' => view_service_modal_error_html('Sale not found in your current shift.')];
        }

        // Current-shift sales are already closed records on this page. Avoid the
        // extra live/open-bill registry queries when opening the details modal.
        $is_live_sale = false;
        $open_registry = null;
        $sale_owner = $user_id;

        $child_sales = [];
        $has_sale_parent = false;
        $current_items = view_repo_bill_items_rows($connection, $sale_id, $user_id, $current_shift);
        $current_total = 0;
        foreach ($current_items as $row) {
            $current_total += (float)($row['total'] ?? 0);
        }
        $is_ongoing = $open_registry && (int)($open_registry['status'] ?? 0) === 1;
        $ongoing_total = $is_ongoing ? (float)($open_registry['open_amount'] ?? 0) : 0;
        $has_children = !empty($child_sales);
        $panel_title = $has_children ? ('Bill No ' . $sale_id . ' Family') : ('Bill No ' . $sale_id . ' Items');

        $order_shift = (string)($sale_meta['shift'] ?? '');
        $order_time = '';
        foreach (['created_at', 'datime', 'order_time', 'sale_time', 'time'] as $time_column) {
            if (!empty($sale_meta[$time_column])) {
                $order_time = (string)$sale_meta[$time_column];
                break;
            }
        }
        if ($order_time === '') {
            $time_query = mysqli_query($connection, "SELECT hour FROM kp_sale_items WHERE sale_id='$sale_id' AND hour IS NOT NULL AND hour<>'' LIMIT 1");
            if ($time_query && ($time_row = mysqli_fetch_assoc($time_query))) {
                $order_time = (string)($time_row['hour'] ?? '');
            }
        }
        if ($order_time !== '' && preg_match('/\s(\d{1,2}:\d{2}(?::\d{2})?)$/', $order_time, $time_match)) {
            $order_time = $time_match[1];
        }

        ob_start();
        ?>
<div class="modal-content bill-view-modal" style="border-radius:18px; overflow:hidden;">
  <style>
    .bill-view-modal{width:min(900px,calc(100vw - 24px));max-height:calc(100vh - 24px);display:flex;flex-direction:column}
    .bill-view-modal .modal-body{min-height:0;overflow:hidden;display:flex;flex-direction:column}
    .bill-view-modal .bill-family-summary{display:grid;grid-template-columns:repeat(5,minmax(0,1fr));gap:0;margin-bottom:12px;padding:9px 4px;border:1px solid #ecd8c0;border-radius:12px;background:linear-gradient(145deg,#fffaf3,#fff1df);box-shadow:0 3px 10px rgba(111,84,58,.08)}
    .bill-view-modal .bill-family-chip{min-height:48px;padding:5px 10px;border:0;border-right:1px solid #ead8c5;border-radius:0;background:transparent;box-shadow:none;display:flex;flex-direction:column;justify-content:center;gap:3px}
    .bill-view-modal .bill-family-chip:last-child{border-right:0}
    .bill-view-modal .bill-family-chip small{color:#8b6748;font-size:11px;font-weight:800;text-transform:uppercase;letter-spacing:.05em}
    .bill-view-modal .bill-family-chip strong{color:#5f321d;font-size:15px;overflow-wrap:anywhere}
    .bill-view-modal .bill-family-chip.ongoing{border-color:#9ed8a8;background:linear-gradient(145deg,#f5fff5,#e7f8e9)}
    .bill-view-modal .bill-family-chip.ongoing strong{color:#176b2a}
    .bill-view-modal .bill-items-scroll{min-height:0;flex:1;max-height:calc(100vh - 330px);overflow:auto;border:1px solid #eadbc9;border-radius:11px}
    .bill-view-modal .bill-items-scroll table{margin-bottom:0}
    .bill-view-modal .bill-items-scroll thead th{position:sticky;top:0;z-index:1;background:#fff3e4}
    @media (max-width:600px){.bill-view-modal{width:calc(100vw - 12px);max-height:calc(100vh - 12px)}.bill-view-modal .modal-body{padding:12px!important}.bill-view-modal .bill-family-summary{grid-template-columns:repeat(2,minmax(0,1fr));gap:0;padding:7px 3px}.bill-view-modal .bill-family-chip{min-height:46px;padding:6px 8px}.bill-view-modal .bill-family-chip:nth-child(2n){border-right:0}.bill-view-modal .bill-family-chip:nth-child(-n+3){border-bottom:1px solid #ead8c5}.bill-view-modal .bill-items-scroll{max-height:calc(100vh - 330px)}}
  </style>
  <div class="modal-header" style="background:linear-gradient(135deg, #7e2014 0%, #b94828 52%, #e7a33a 100%); color:#fff8ea;">
    <button type="button" class="close" data-dismiss="modal" style="color:#fff; opacity:1;">&times;</button>
    <h4 class="modal-title"><b><?php echo view_escape($panel_title); ?></b></h4>
  </div>
  <div class="modal-body" style="background:#fffdf8;">
    <div class="bill-family-summary">
      <div class="bill-family-chip">
        <small>Owner</small>
        <strong><?php echo view_escape($sale_owner > 0 ? get_user($sale_owner) : 'Unknown'); ?></strong>
      </div>
      <div class="bill-family-chip <?php echo $is_ongoing ? 'ongoing' : ''; ?>">
        <small><?php echo $is_ongoing ? 'Open Total' : 'Bill Total'; ?></small>
        <strong><?php echo number_format($is_ongoing ? $ongoing_total : $current_total, 2); ?></strong>
      </div>
      <div class="bill-family-chip"><small>Order Shift</small><strong><?php echo view_escape($order_shift !== '' ? $order_shift : '—'); ?></strong></div>
      <div class="bill-family-chip"><small>Order Time</small><strong><?php echo view_escape($order_time !== '' ? $order_time : '—'); ?></strong></div>
    </div>
    <div style="display:flex; gap:10px; flex-wrap:wrap; margin-bottom:14px;">
      <button type="button" class="btn btn-warning return-sale" data-id="<?php echo (int)$sale_id; ?>" style="font-weight:800;">RETURN</button>
      <button type="button" class="btn btn-danger void-sale" data-id="<?php echo (int)$sale_id; ?>" style="font-weight:800;">VOID</button>
    </div>
    <?php if ($has_children) { ?>
    <div class="bill-children-list">
      <?php foreach ($child_sales as $index => $child_sale) { ?>
      <button type="button" class="bill-child-btn" data-child-panel="child-panel-<?php echo (int)$index; ?>">
        <small>Child Bill</small>
        <span>#<?php echo (int)$child_sale['sale_id']; ?> · <?php echo view_escape($child_sale['shift']); ?> · <?php echo number_format((float)$child_sale['total'], 2); ?></span>
      </button>
      <div class="bill-child-panel" id="child-panel-<?php echo (int)$index; ?>">
        <div class="bill-table-wrap">
          <table class="table table-striped table-bordered">
            <thead><tr><th>Item</th><th>Qty</th><th>Price</th><th>Total</th></tr></thead>
            <tbody>
            <?php foreach ($child_sale['items'] as $item) { ?>
              <tr>
                <td><?php echo view_escape($item['item']); ?></td>
                <td><?php echo number_format((float)$item['qty'], 2); ?></td>
                <td><?php echo number_format((float)$item['price'], 2); ?></td>
                <td><?php echo number_format((float)$item['total'], 2); ?></td>
              </tr>
            <?php } ?>
            </tbody>
          </table>
        </div>
      </div>
      <?php } ?>
    </div>
    <?php } ?>
    <div class="bill-table-wrap">
      <table class="table table-striped table-bordered" style="margin-bottom:0;">
        <thead><tr><th>Item</th><th>Qty</th><th>Price</th><th>Total</th></tr></thead>
        <tbody>
        <?php foreach ($current_items as $item) { ?>
          <tr>
            <td><?php echo view_escape($item['item']); ?></td>
            <td><?php echo number_format((float)$item['qty'], 2); ?></td>
            <td><?php echo number_format((float)$item['price'], 2); ?></td>
            <td><?php echo number_format((float)$item['total'], 2); ?></td>
          </tr>
        <?php } ?>
        <tr class="bill-total-row"><td colspan="3"><b>Total</b></td><td><b><?php echo number_format($current_total, 2); ?></b></td></tr>
        </tbody>
      </table>
    </div>
  </div>
  <div class="modal-footer"><button type="button" class="btn btn-default" data-dismiss="modal">Close</button></div>
</div>
<script>
$(document).off('click.billChildToggle').on('click.billChildToggle', '.bill-child-btn', function() {
  var panelId = $(this).data('childPanel');
  $('.bill-child-panel').removeClass('active');
  $('#' + panelId).addClass('active');
});
</script>
        <?php
        return ['ok' => true, 'message' => 'Bill items loaded.', 'status' => 200, 'html' => ob_get_clean()];
    }
}

if (!function_exists('view_service_merge_bill_modal')) {
    function view_service_merge_bill_modal($connection, $user_id, $sale_id)
    {
        $user_id = (int)$user_id;
        $sale_id = (int)$sale_id;
        if ($user_id < 1) {
            return ['ok' => false, 'message' => 'Session expired.', 'status' => 401, 'html' => view_service_modal_error_html('Session expired.')];
        }
        if ($sale_id < 1) {
            return ['ok' => false, 'message' => 'Invalid bill selected.', 'status' => 400, 'html' => view_service_modal_error_html('Invalid bill selected.')];
        }

        $owner_id = (int)get_column('kp_sales', 'user_id', "sale_id='$sale_id'");
        if ($owner_id < 1) {
            return ['ok' => false, 'message' => 'Bill owner could not be found.', 'status' => 400, 'html' => view_service_modal_error_html('Bill owner could not be found.')];
        }

        $owner_name = get_user($owner_id);
        $is_mine = $owner_id === $user_id;
        $amount = (float)column_total('kp_sale_items', 'total', "sale_id='$sale_id'");
        $active_draft = function_exists('cash_active_merge_draft') ? cash_active_merge_draft($user_id, false) : null;
        $active_merge_id = $active_draft ? (int)($active_draft['merge_id'] ?? 0) : 0;
        $approved_for_requester_sale = 0;
        $approval_filter = $active_merge_id > 0 ? " AND merge_id='$active_merge_id'" : '';
        $approved_r = mysqli_query(
            $connection,
            "SELECT requester_sale_id
             FROM kp_bill_merge_request
             WHERE requester_user_id='$user_id' AND target_sale_id='$sale_id' AND status=2 $approval_filter
             ORDER BY request_id DESC
             LIMIT 1"
        );
        if ($approved_r && mysqli_num_rows($approved_r) > 0) {
            $approved_row = mysqli_fetch_assoc($approved_r);
            $approved_for_requester_sale = (int)($approved_row['requester_sale_id'] ?? 0);
        }
        $counter_name = get_counter((int)get_column('kp_sales', 'counter', "sale_id='$sale_id'"));
        $items = view_repo_bill_items_rows($connection, $sale_id);

        ob_start();
        ?>
<div class="modal-content" style="border-radius:18px; overflow:hidden;">
  <div class="modal-header" style="background:linear-gradient(135deg, #7e2014 0%, #b94828 52%, #e7a33a 100%); color:#fff8ea;">
    <button type="button" class="close" data-dismiss="modal" style="color:#fff; opacity:1;">&times;</button>
    <h4 class="modal-title"><b>Bill #<?php echo (int)$sale_id; ?> Items</b></h4>
  </div>
  <div class="modal-body" style="background:#fffdf8;">
    <div style="margin-bottom:12px; padding:12px 14px; border-radius:12px; background:#fff5e8; border:1px solid #ecd8c0; color:#6f543a; font-weight:700;">
      <div><b>User:</b> <?php echo view_escape($owner_name); ?></div>
      <div><b>Total:</b> <?php echo number_format($amount, 2); ?></div>
    </div>
    <div class="bill-items-scroll">
      <table class="table table-striped table-bordered" style="margin-bottom:0;">
        <thead><tr><th>No</th><th>Item</th><th>Qty</th><th>Price</th><th>Total</th></tr></thead>
        <tbody>
        <?php foreach ($items as $index => $item) { ?>
          <tr>
            <td><?php echo (int)($index + 1); ?></td>
            <td><?php echo view_escape($item['item']); ?></td>
            <td><?php echo number_format((float)$item['qty'], 2); ?></td>
            <td><?php echo number_format((float)$item['price'], 2); ?></td>
            <td><?php echo number_format((float)$item['total'], 2); ?></td>
          </tr>
        <?php } ?>
        </tbody>
        <tfoot>
          <tr><th colspan="4" class="text-right">Items Total</th><th><?php echo number_format($amount, 2); ?></th></tr>
        </tfoot>
      </table>
    </div>
    <?php if (!$is_mine) { ?>
    <div style="margin-top:14px; padding:12px 14px; border-radius:12px; background:#fff7ef; border:1px solid #ecd8c0; color:#7a5226; font-weight:700;">
      <?php if ($approved_for_requester_sale > 0) { ?>
        This bill is approved for merge with your bill #<?php echo (int)$approved_for_requester_sale; ?>.
      <?php } else { ?>
        This bill belongs to another user. If you already added your own bill to the merge basket, you can send the approval request from here.
      <?php } ?>
    </div>
    <?php } ?>
  </div>
  <div class="modal-footer">
    <button type="button" class="btn btn-default" data-dismiss="modal">Close</button>
    <?php if ($is_mine) { ?>
      <button type="button" class="btn btn-success" onclick="addOwnBillToMergeFromModal(<?php echo (int)$sale_id; ?>, '<?php echo number_format($amount, 2, '.', ''); ?>', '<?php echo view_escape(addslashes($counter_name)); ?>')">Add To Merge</button>
    <?php } elseif ($approved_for_requester_sale > 0) { ?>
      <button type="button" class="btn btn-success" onclick="addApprovedForeignBillToMerge(<?php echo (int)$sale_id; ?>, '<?php echo number_format($amount, 2, '.', ''); ?>', '<?php echo view_escape(addslashes($counter_name)); ?>', <?php echo (int)$approved_for_requester_sale; ?>)">Add Approved Bill</button>
    <?php } else { ?>
      <button type="button" class="btn btn-warning" onclick="sendMergeRequestFromModal(<?php echo (int)$sale_id; ?>)">Send Request</button>
    <?php } ?>
  </div>
</div>
        <?php
        return ['ok' => true, 'message' => 'Merge bill modal loaded.', 'status' => 200, 'html' => ob_get_clean()];
    }
}

if (!function_exists('view_service_merge_review_modal')) {
    function view_service_merge_review_modal($connection, $user_id, $merge_id)
    {
        $user_id = (int)$user_id;
        $merge_id = (int)$merge_id;
        if ($user_id < 1) {
            return ['ok' => false, 'message' => 'Session expired.', 'status' => 401, 'html' => view_service_modal_error_html('Session expired.')];
        }
        if ($merge_id < 1) {
            return ['ok' => false, 'message' => 'Invalid merge selected.', 'status' => 400, 'html' => view_service_modal_error_html('Invalid merge selected.')];
        }

        $merge_r = mysqli_query($connection, "SELECT * FROM kp_bill_merge WHERE merge_id='$merge_id' AND created_by='$user_id' AND status=1 LIMIT 1");
        if (!$merge_r || mysqli_num_rows($merge_r) < 1) {
            return ['ok' => false, 'message' => 'That merged bill could not be found.', 'status' => 404, 'html' => view_service_modal_error_html('That merged bill could not be found.')];
        }
        $merge = mysqli_fetch_assoc($merge_r);
        $items = function_exists('cash_merge_items') ? cash_merge_items($merge_id) : [];

        ob_start();
        ?>
<div class="modal-content" style="border-radius:18px; overflow:hidden;">
  <div class="modal-header" style="background:linear-gradient(135deg, #7e2014 0%, #b94828 52%, #e7a33a 100%); color:#fff8ea;">
    <button type="button" class="close" data-dismiss="modal" style="color:#fff; opacity:1;">&times;</button>
    <h4 class="modal-title"><b><?php echo view_escape((string)($merge['merge_no'] ?? 'Merged Bill')); ?></b></h4>
  </div>
  <div class="modal-body" style="background:#fffdf8;">
    <div style="margin-bottom:12px; padding:12px 14px; border-radius:12px; background:#fff5e8; border:1px solid #ecd8c0; color:#6f543a; font-weight:700;">
      <div><b>Total:</b> <?php echo number_format((float)($merge['total_amount'] ?? 0), 2); ?></div>
      <div><b>Created:</b> <?php echo view_escape((string)($merge['created_at'] ?? '')); ?></div>
      <div><b>Bills:</b> <?php echo count($items); ?></div>
    </div>
    <div style="max-height:46vh; overflow-y:auto;">
      <?php if (empty($items)) { ?>
        <div class="alert alert-warning" style="margin-bottom:0;">No merged bills found.</div>
      <?php } else { ?>
        <?php foreach ($items as $item) { ?>
          <?php
          $sale_id = (int)($item['sale_id'] ?? 0);
          $owner_id = (int)get_column('kp_sales', 'user_id', "sale_id='$sale_id'");
          $type_label = ($item['item_type'] ?? '') === 'open_child' ? 'Child Bill' : (($item['item_type'] ?? '') === 'open_chain' ? 'Open Chain' : 'Bill');
          $sale_items = view_repo_bill_items_rows($connection, $sale_id);
          ?>
          <div style="margin-bottom:14px; border:1px solid #ecd8c0; border-radius:14px; overflow:hidden; background:#fffaf3;">
            <div style="padding:10px 12px; background:#fff3e4; color:#6f543a; font-weight:800;">
              <div><b>Bill:</b> #<?php echo (int)$sale_id; ?> | <b>User:</b> <?php echo view_escape(get_user($owner_id)); ?></div>
              <div><b>Type:</b> <?php echo view_escape($type_label); ?> | <b>Amount:</b> <?php echo number_format((float)($item['amount'] ?? 0), 2); ?></div>
            </div>
            <table class="table table-striped table-bordered" style="margin-bottom:0;">
              <thead><tr><th>No</th><th>Item</th><th>Qty</th><th>Price</th><th>Total</th></tr></thead>
              <tbody>
              <?php if (empty($sale_items)) { ?>
                <tr><td colspan="5">No items found for this bill.</td></tr>
              <?php } else { ?>
                <?php foreach ($sale_items as $line_no => $sale_item) { ?>
                <tr>
                  <td><?php echo (int)($line_no + 1); ?></td>
                  <td><?php echo view_escape($sale_item['item']); ?></td>
                  <td><?php echo number_format((float)$sale_item['qty'], 2); ?></td>
                  <td><?php echo number_format((float)$sale_item['price'], 2); ?></td>
                  <td><?php echo number_format((float)$sale_item['total'], 2); ?></td>
                </tr>
                <?php } ?>
              <?php } ?>
              </tbody>
            </table>
          </div>
        <?php } ?>
      <?php } ?>
    </div>
  </div>
  <div class="modal-footer"><button type="button" class="btn btn-default" data-dismiss="modal">Close</button></div>
</div>
        <?php
        return ['ok' => true, 'message' => 'Merge review loaded.', 'status' => 200, 'html' => ob_get_clean()];
    }
}

if (!function_exists('view_service_merge_requests_modal')) {
    function view_service_merge_requests_modal($connection, $user_id, $incoming_only, $pending_only)
    {
        $user_id = (int)$user_id;
        if ($user_id < 1) {
            return ['ok' => false, 'message' => 'Session expired.', 'status' => 401, 'html' => view_service_modal_error_html('Session expired.')];
        }

        if (function_exists('cash_ensure_merge_request_table')) {
            cash_ensure_merge_request_table();
        }
        $active_draft = function_exists('cash_active_merge_draft') ? cash_active_merge_draft($user_id, false) : null;
        $active_merge_id = $active_draft ? (int)($active_draft['merge_id'] ?? 0) : 0;
        $where = $incoming_only
            ? "target_user_id='$user_id'"
            : ($active_merge_id > 0 ? "merge_id='$active_merge_id'" : "(requester_user_id='$user_id' OR target_user_id='$user_id')");
        if ($pending_only) {
            $where .= " AND status=1";
        }

        $merge_requests = [];
        $requests_res = mysqli_query(
            $connection,
            "SELECT * FROM kp_bill_merge_request WHERE $where ORDER BY request_id DESC LIMIT 80"
        );
        while ($requests_res && $row = mysqli_fetch_assoc($requests_res)) {
            $merge_requests[] = $row;
        }

        ob_start();
        ?>
<div class="modal-content" style="border-radius:18px; overflow:hidden;">
  <div class="modal-header" style="background:linear-gradient(135deg, #7e2014 0%, #b94828 52%, #e7a33a 100%); color:#fff8ea;">
    <button type="button" class="close" data-dismiss="modal" style="color:#fff; opacity:1;">&times;</button>
    <h4 class="modal-title"><b>Merge Requests</b></h4>
  </div>
  <div class="modal-body" style="background:#fffdf8; max-height:70vh; overflow-y:auto;">
    <?php if (empty($merge_requests)) { ?>
      <div class="alert alert-warning" style="margin-bottom:0;">No merge requests yet.</div>
    <?php } else { ?>
      <?php foreach ($merge_requests as $request) { ?>
        <?php
        $reqStatus = (int)($request['status'] ?? 0);
        $statusLabel = 'Pending';
        $statusClass = 'status-pending';
        if ($reqStatus === 2) { $statusLabel = 'Accepted'; $statusClass = 'status-accepted'; }
        elseif ($reqStatus === 3) { $statusLabel = 'Rejected'; $statusClass = 'status-rejected'; }
        elseif ($reqStatus === 4) { $statusLabel = 'Closed'; $statusClass = 'status-used'; }
        $isIncoming = (int)($request['target_user_id'] ?? 0) === $user_id;
        ?>
        <div class="request-chip" data-request-row="<?php echo (int)($request['request_id'] ?? 0); ?>" style="margin-bottom:10px;">
          <small><?php echo $isIncoming ? 'Incoming Request' : 'My Request'; ?></small>
          <strong>#<?php echo (int)(($request['requester_sale_id'] ?: $request['requester_root_sale_id']) ?? 0); ?> + #<?php echo (int)(($request['target_sale_id'] ?: $request['target_root_sale_id']) ?? 0); ?></strong>
          <div class="meta">
            <span><b>Requester:</b> <?php echo view_escape(get_user((int)($request['requester_user_id'] ?? 0))); ?></span>
            <span><b>Owner:</b> <?php echo view_escape(get_user((int)($request['target_user_id'] ?? 0))); ?></span>
            <span><b>Status:</b> <span class="status-pill <?php echo view_escape($statusClass); ?>"><?php echo view_escape($statusLabel); ?></span></span>
            <span><b>Requested:</b> <?php echo view_escape((string)($request['created_at'] ?? '')); ?></span>
            <?php if (!empty($request['responded_at'])) { ?>
            <span><b>Responded:</b> <?php echo view_escape((string)$request['responded_at']); ?></span>
            <?php } ?>
          </div>
          <?php if ($reqStatus === 1 && $isIncoming) { ?>
          <div class="actions">
            <button type="button" class="btn btn-success btn-sm accept-merge-request" data-request-id="<?php echo (int)($request['request_id'] ?? 0); ?>">Accept</button>
            <button type="button" class="btn btn-danger btn-sm reject-merge-request" data-request-id="<?php echo (int)($request['request_id'] ?? 0); ?>">Reject</button>
          </div>
          <?php } elseif (!$isIncoming && in_array($reqStatus, [1, 2], true)) { ?>
          <div class="actions">
            <button type="button" class="btn btn-default btn-sm close-merge-request" data-request-id="<?php echo (int)($request['request_id'] ?? 0); ?>">Close Request</button>
          </div>
          <?php } ?>
        </div>
      <?php } ?>
    <?php } ?>
  </div>
</div>
        <?php
        return ['ok' => true, 'message' => 'Merge requests loaded.', 'status' => 200, 'html' => ob_get_clean()];
    }
}

if (!function_exists('view_service_item_sales_modal')) {
    function view_service_item_sales_modal($connection, $user_id, $item_name)
    {
        $user_id = (int)$user_id;
        $item_name = trim((string)$item_name);
        if ($user_id < 1) {
            return ['ok' => false, 'message' => 'Session expired.', 'status' => 401, 'html' => view_service_modal_error_html('Session expired.')];
        }
        if ($item_name === '') {
            return ['ok' => false, 'message' => 'Invalid item selected.', 'status' => 400, 'html' => view_service_modal_error_html('Invalid item selected.')];
        }

        $safe_item = mysqli_real_escape_string($connection, $item_name);
        $rows = [];
        $res = mysqli_query(
            $connection,
            "SELECT s.sale_id, s.shift, s.amount, SUM(si.qty) AS qty
             FROM kp_sale_items si
             LEFT JOIN kp_items i ON i.item_id = si.item_id
             LEFT JOIN kp_sales s ON s.sale_id = si.sale_id
             WHERE s.user_id='$user_id' AND i.name='$safe_item'
             GROUP BY s.sale_id, s.shift, s.amount
             ORDER BY s.sale_id DESC
             LIMIT 80"
        );
        while ($res && $row = mysqli_fetch_assoc($res)) {
            $rows[] = $row;
        }

        ob_start();
        ?>
<div class="modal-content" style="border-radius:18px; overflow:hidden;">
  <div class="modal-header" style="background:linear-gradient(135deg, #7e2014 0%, #b94828 52%, #e7a33a 100%); color:#fff8ea;">
    <button type="button" class="close" data-dismiss="modal" style="color:#fff; opacity:1;">&times;</button>
    <h4 class="modal-title"><b>Orders With <?php echo view_escape($item_name); ?></b></h4>
  </div>
  <div class="modal-body" style="background:#fffdf8; max-height:70vh; overflow-y:auto;">
    <?php if (empty($rows)) { ?>
      <div class="alert alert-warning" style="margin-bottom:0;">No orders were found with this item.</div>
    <?php } else { ?>
      <table class="table table-striped table-bordered" style="margin-bottom:0;">
        <thead><tr><th>Bill</th><th>Shift</th><th>Qty</th><th>Total</th><th>Items</th></tr></thead>
        <tbody>
          <?php foreach ($rows as $row) { ?>
          <tr>
            <td>#<?php echo (int)($row['sale_id'] ?? 0); ?></td>
            <td><?php echo view_escape((string)($row['shift'] ?? '')); ?></td>
            <td><?php echo number_format((float)($row['qty'] ?? 0), 2); ?></td>
            <td><?php echo number_format((float)($row['amount'] ?? 0), 2); ?></td>
            <td><button type="button" class="btn btn-xs btn-warning item-sale-view" data-sale-id="<?php echo (int)($row['sale_id'] ?? 0); ?>">View Items</button></td>
          </tr>
          <?php } ?>
        </tbody>
      </table>
    <?php } ?>
  </div>
</div>
<script type="text/javascript">
  $(document).off('click.itemSaleView').on('click.itemSaleView', '.item-sale-view', function() {
    var saleId = $(this).data('saleId');
    $('#load').html('<div class="modal-content"><div class="modal-body" style="padding:24px; text-align:center; font-weight:900;">Loading bill items...</div></div>');
    $.ajax({
      url: 'API/view_api.php',
      type: 'POST',
      data: { action: 'bill_items_modal', sale_id: saleId },
      dataType: 'html'
    })
    .done(function(html) {
      $('#load').html('<div class="modal-content"><div class="modal-body" style="background:#fff; border-radius:12px; max-height:80vh; overflow-y:auto;">' + html + '</div></div>');
    })
    .fail(function() {
      $('#load').html('<div class="modal-content"><div class="modal-body"><div class="alert alert-danger" style="margin-bottom:0;">Could not load the sale items.</div></div></div>');
    });
  });
</script>
        <?php
        return ['ok' => true, 'message' => 'Item sales loaded.', 'status' => 200, 'html' => ob_get_clean()];
    }
}

if (!function_exists('view_service_reprint_modal')) {
    function view_service_reprint_modal($connection, $user_id, $sale_id)
    {
        $user_id = (int)$user_id;
        $sale_id = (int)$sale_id;
        if ($user_id < 1) {
            return ['ok' => false, 'message' => 'Session expired.', 'status' => 401, 'html' => view_service_modal_error_html('Session expired.')];
        }
        if ($sale_id < 1) {
            return ['ok' => false, 'message' => 'Invalid bill selected.', 'status' => 400, 'html' => view_service_modal_error_html('Invalid bill selected.')];
        }

        $items = view_repo_bill_items_rows($connection, $sale_id);
        ob_start();
        ?>
<div class="modal-content" style="width:650px; max-width:calc(100vw - 24px); border-radius:18px; overflow:hidden;">
  <div class="modal-header" style="background:linear-gradient(135deg, #7e2014 0%, #b94828 52%, #e7a33a 100%); color:#fff8ea;">
    <button type="button" class="close" data-dismiss="modal" style="color:#fff; opacity:1;">&times;</button>
    <h3 style="margin:0;"><b>AUTHORIZE REPRINT FOR BILL NO: <?php echo (int)$sale_id; ?></b></h3>
  </div>
  <div class="modal-body" style="padding:10px; background:#fffdf8;">
    <div class="row">
      <div id="results" class="form-group col-md-12" style="color:red;"></div>
      <div class="form-group col-md-6">
        <div style="padding:10px 12px; border-radius:12px; background:#fffaf0; box-shadow:inset 0 0 0 1px rgba(120,84,42,0.12);">
          <input type="password" name="pass" id="pass" class="form-control" style="font-size:40px; font-weight:bolder; height:62px; border-radius:12px; text-align:center;" autofocus/>
          <div style="display:grid; grid-template-columns:repeat(3, 1fr); gap:10px; margin-top:12px;">
            <?php foreach ([1,2,3,4,5,6,7,8,9,'C',0,'B'] as $key) { ?>
              <button type="button" class="btn btn-default repad" data-key="<?php echo view_escape((string)$key); ?>" style="height:64px; font-size:22px; font-weight:900; border-radius:12px;"><?php echo view_escape((string)$key); ?></button>
            <?php } ?>
          </div>
        </div>
      </div>
      <div class="form-group col-md-6" style="overflow-y:scroll; height:370px; width:80mm;">
        <table class="table table-striped">
          <thead><tr><th>ITEM</th><th>QTY</th><th>PRICE</th><th>AMOUNT</th></tr></thead>
          <tbody>
            <?php foreach ($items as $item) { ?>
            <tr>
              <td><?php echo view_escape($item['item']); ?></td>
              <td><?php echo number_format((float)$item['qty'], 2); ?></td>
              <td><?php echo number_format((float)$item['price'], 2); ?></td>
              <td><?php echo number_format((float)$item['total'], 2); ?></td>
            </tr>
            <?php } ?>
          </tbody>
        </table>
      </div>
    </div>
  </div>
  <div class="modal-footer" style="padding:0 5px;">
    <button type="button" class="btn btn-danger pull-left" data-dismiss="modal" style="font-family:'comic sans ms'; font-weight:bolder; color:#fff!important; font-size:12px; height:40px; background:#000; border:4px solid #e0bd65; border-radius:10px;">CLOSE</button>
    <button type="button" id="btn-ret" class="btn btn-success pull-right" style="font-family:'comic sans ms'; font-weight:bolder; color:#fff!important; font-size:12px; height:40px; background:#000; border:4px solid #e0bd65; border-radius:10px;">AUTHORIZE</button>
  </div>
</div>
<script type="text/javascript">
  $(document).off('click.reprintAuth').on('click.reprintAuth', '#btn-ret', function () {
    var pass = $("#pass").val();
    $.ajax({
      url: 'API/view_api.php',
      type: 'POST',
      data: { action: 'reprint_authorize', pass: pass, sale_id: <?php echo (int)$sale_id; ?> },
      dataType: 'html'
    }).done(function(data){
      $('#results').html(data);
    }).fail(function(){
      $('#results').html('<div class="alert alert-danger"><h4><b>Something went wrong, please try again.</b></h4></div>');
    });
  });

  $(document).off('click.repad').on('click.repad', '.repad', function () {
    var key = String($(this).data('key'));
    var pass = $('#pass');
    if (key === 'C') {
      pass.val('');
    } else if (key === 'B') {
      pass.val(pass.val().slice(0, -1));
    } else {
      pass.val(pass.val() + key);
    }
  });
</script>
        <?php
        return ['ok' => true, 'message' => 'Reprint modal loaded.', 'status' => 200, 'html' => ob_get_clean()];
    }
}

if (!function_exists('view_service_reprint_authorize')) {
    function view_service_reprint_authorize($connection, $user_id, $sale_id, $pin)
    {
        $user_id = (int)$user_id;
        $sale_id = (int)$sale_id;
        $pin = trim((string)$pin);
        if ($user_id < 1) {
            return ['ok' => false, 'message' => 'Session expired.', 'status' => 401, 'html' => '<div class="alert alert-danger"><h4><b>Session expired.</b></h4></div>'];
        }
        if ($pin === '') {
            return ['ok' => false, 'message' => 'Passcode empty.', 'status' => 400, 'html' => '<div class="alert alert-danger"><h4><b>Passcode Empty!! Trials are documented.</b></h4></div>'];
        }
        if (!function_exists('front_perm') || !front_perm($pin, 8)) {
            return ['ok' => false, 'message' => 'Wrong passcode.', 'status' => 403, 'html' => '<div class="alert alert-danger"><h4><b>Wrong Passcode!! Trials are documented.</b></h4></div>'];
        }

        $shift = get_column('kp_shift', 'shift', '1');
        $appr_by = get_column('kp_users', 'user_id', "pin='$pin'");
        mysqli_query($connection, "INSERT INTO kp_sale_reprint(sale_id,user_id,appr_by,shift) VALUES('$sale_id','$user_id','$appr_by','$shift')");

        $html = '<div class="alert alert-success"><h4><b>Reprint authorized.</b></h4></div>'
            . '<script type="text/javascript">printContent(' . (int)$sale_id . ');</script>';

        return ['ok' => true, 'message' => 'Reprint authorized.', 'status' => 200, 'html' => $html];
    }
}

if (!function_exists('view_service_void_modal')) {
    function view_service_void_modal($connection, $user_id, $sale_id)
    {
        $user_id = (int)$user_id;
        $sale_id = (int)$sale_id;
        if ($user_id < 1) {
            return ['ok' => false, 'message' => 'Session expired.', 'status' => 401, 'html' => view_service_modal_error_html('Session expired.')];
        }
        if ($sale_id < 1) {
            return ['ok' => false, 'message' => 'Invalid bill selected.', 'status' => 400, 'html' => view_service_modal_error_html('Invalid bill selected.')];
        }

        $items = view_repo_bill_items_rows($connection, $sale_id);
        $total = 0;
        foreach ($items as $item) {
            $total += (float)($item['total'] ?? 0);
        }

        ob_start();
        ?>
<div class="modal-content void-shell" style="width:650px; max-width:calc(100vw - 24px); border-radius:18px; overflow:hidden;">
  <div class="modal-header void-head" style="background:linear-gradient(135deg, #7e2014 0%, #b94828 52%, #e7a33a 100%); color:#fff8ea;">
    <button type="button" class="close" data-dismiss="modal" style="color:#fff; opacity:1;">&times;</button>
    <h3><b>AUTHORIZE VOID FOR BILL NO: <?php echo (int)$sale_id; ?></b></h3>
  </div>
  <div class="modal-body" style="padding:10px; background:#fffdf8;">
    <div class="row void-stack">
      <div id="results" class="form-group col-md-12" style="color:red;"></div>
      <div class="form-group col-md-6 void-left">
        <div class="void-pass-card" style="padding:10px 12px 12px; border-radius:12px; background:#fffaf0; box-shadow:inset 0 0 0 1px rgba(120,84,42,0.12);">
          <h3 class="void-pass-title" style="margin:0 0 8px; font-size:20px; font-weight:900; color:#6f2c16; text-transform:uppercase;"><b>Enter Password</b></h3>
          <input type="password" name="pass" id="pass" class="form-control" style="font-size:40px; font-weight:bolder; height:62px; border-radius:12px; text-align:center;" autofocus/>
          <div style="display:grid; grid-template-columns:repeat(3, 1fr); gap:10px; margin-top:12px;">
            <?php foreach ([1,2,3,4,5,6,7,8,9,'C',0,'B'] as $key) { ?>
              <button type="button" class="btn btn-default voidpad" data-key="<?php echo view_escape((string)$key); ?>" style="height:64px; font-size:22px; font-weight:900; border-radius:12px;"><?php echo view_escape((string)$key); ?></button>
            <?php } ?>
          </div>
        </div>
      </div>
      <div class="form-group col-md-6 void-right" style="overflow-y:scroll; height:370px; width:80mm;">
        <table class="table table-striped">
          <thead><tr><th>ITEM</th><th>QTY</th><th>PRICE</th><th>AMOUNT</th></tr></thead>
          <tbody>
            <?php foreach ($items as $item) { ?>
            <tr>
              <td><?php echo view_escape($item['item']); ?></td>
              <td><?php echo number_format((float)$item['qty'], 2); ?></td>
              <td><?php echo number_format((float)$item['price'], 2); ?></td>
              <td><?php echo number_format((float)$item['total'], 2); ?></td>
            </tr>
            <?php } ?>
            <tr class="void-total-row">
              <td colspan="3"><span class="void-total-label" style="font-weight:900; color:#6f2c16; font-size:18px;">TOTAL</span></td>
              <td><span class="void-total-amount" style="font-weight:900; color:#6f2c16; font-size:24px; text-align:right;"><?php echo number_format($total, 2); ?></span></td>
            </tr>
          </tbody>
        </table>
      </div>
    </div>
  </div>
  <div class="modal-footer void-actions" style="display:flex; justify-content:space-between; gap:10px; padding:8px 10px 10px;">
    <button type="button" class="btn btn-danger void-btn pull-left" data-dismiss="modal" style="min-width:140px; min-height:44px; border-radius:10px; font-family:'comic sans ms'; font-weight:bolder; color:#fff!important; font-size:12px; background:#b7212a; border:4px solid #ed6a70;">Close</button>
    <button type="button" id="btn-void" class="btn btn-success void-btn pull-right" style="min-width:140px; min-height:44px; border-radius:10px; font-family:'comic sans ms'; font-weight:bolder; color:#fff!important; font-size:12px; background:#22743d; border:4px solid #59b978;">Authorize</button>
  </div>
</div>
<script type="text/javascript">
  $(document).off('click.voidAuth').on('click.voidAuth', '#btn-void', function () {
    var pass = $("#pass").val();
    $.ajax({
      url: 'API/view_api.php',
      type: 'POST',
      data: { action: 'void_authorize', pass: pass, sale_id: <?php echo (int)$sale_id; ?> },
      dataType: 'html'
    }).done(function(data){
      $('#results').html(data);
    }).fail(function(){
      $('#results').html('<div class="alert alert-danger"><h4><b>Something went wrong, please try again.</b></h4></div>');
    });
  });

  $(document).off('click.voidpad').on('click.voidpad', '.voidpad', function () {
    var key = String($(this).data('key'));
    var pass = $('#pass');
    if (key === 'C') {
      pass.val('');
    } else if (key === 'B') {
      pass.val(pass.val().slice(0, -1));
    } else {
      pass.val(pass.val() + key);
    }
  });
</script>
        <?php
        return ['ok' => true, 'message' => 'Void modal loaded.', 'status' => 200, 'html' => ob_get_clean()];
    }
}

if (!function_exists('view_service_void_authorize')) {
    function view_service_void_authorize($connection, $user_id, $sale_id, $pin)
    {
        $user_id = (int)$user_id;
        $sale_id = (int)$sale_id;
        $pin = trim((string)$pin);
        if ($user_id < 1) {
            return ['ok' => false, 'message' => 'Session expired.', 'status' => 401, 'html' => '<div class="alert alert-danger"><h4><b>Session expired.</b></h4></div>'];
        }
        if ($pin === '') {
            return ['ok' => false, 'message' => 'Enter passcode.', 'status' => 400, 'html' => '<div class="alert alert-danger"><h4><b>Enter Passcode!!.</b></h4></div>'];
        }
        if (!function_exists('front_perm') || !front_perm($pin, 2)) {
            return ['ok' => false, 'message' => 'Wrong passcode.', 'status' => 403, 'html' => '<div class="alert alert-danger"><h4><b>Wrong Passcode!!. Remember Trials are Documented!!!!!</b></h4></div>'];
        }

        $shift = get_column('kp_shift', 'shift', '1');
        $amount = get_column('kp_sales', 'amount', "sale_id='$sale_id'");
        $appr_by = get_column('kp_users', 'user_id', "pin='$pin'");
        $get_id = mysqli_query($connection, "SELECT * FROM kp_sale_items WHERE sale_id='$sale_id'");
        while ($gid = $get_id ? mysqli_fetch_assoc($get_id) : null) {
            $item_id = (int)($gid['item_id'] ?? 0);
            $number = (float)($gid['qty'] ?? 0);
            $price = (float)($gid['price'] ?? 0);
            $counter = (int)($gid['stock_counter'] ?? 0);
            $qty_column = 'cntr' . $counter . '_qty';
            $recipe = get_column('kp_items', 'recipe', "item_id='$item_id'");
            if ((int)$recipe === 1) {
                $get_recipe_items = mysqli_query($connection, "SELECT * FROM kp_recipe WHERE item_id='$item_id'");
                while ($row = $get_recipe_items ? mysqli_fetch_assoc($get_recipe_items) : null) {
                    $raw_id = (int)($row['raw_id'] ?? 0);
                    $recipe_qty = (float)($row['qty'] ?? 0);
                    $qty = $number * $recipe_qty;
                    mysqli_query($connection, "UPDATE kp_items SET $qty_column=$qty_column+'$qty' WHERE item_id='$raw_id'");
                    mysqli_query($connection, "DELETE FROM kp_stock_track WHERE item_id='$raw_id' AND user_id='$user_id' AND sale_id='$sale_id'");
                }
            } else {
                mysqli_query($connection, "UPDATE kp_items SET $qty_column=$qty_column+'$number' WHERE item_id='$item_id'");
                mysqli_query($connection, "DELETE FROM kp_stock_track WHERE item_id='$item_id' AND user_id='$user_id' AND sale_id='$sale_id'");
            }

            mysqli_query($connection, "INSERT INTO kp_del_items(sale_id,item_id,qty,price,counter) VALUES('$sale_id','$item_id','$number','$price','$counter')");
        }

        mysqli_query($connection, "INSERT INTO kp_del_orders(sale_id,user_id,appr_by,amount,shift) VALUES('$sale_id','$user_id','$appr_by','$amount','$shift')");
        if (function_exists('reverse_sale_tokens')) {
            reverse_sale_tokens($sale_id, 'sale_void');
        }

        $delete_orders_r = mysqli_query($connection, "DELETE FROM kp_sale_items WHERE sale_id='$sale_id'");
        $delete_history = mysqli_query($connection, "DELETE FROM kp_sales WHERE sale_id='$sale_id'");
        if ($delete_orders_r && $delete_history) {
            if (function_exists('logs')) {
                logs('DELETED|-|-|-ORDER NO|' . $sale_id, '../');
            }
            return [
                'ok' => true,
                'message' => 'Sale deleted.',
                'status' => 200,
                'html' => '<div class="alert alert-success"><h4><b>Bill voided successfully.</b></h4></div><script>location.reload();</script>',
            ];
        }

        return ['ok' => false, 'message' => 'Failed to delete.', 'status' => 500, 'html' => '<div class="alert alert-danger"><h4><b>Failed to delete.</b></h4></div>'];
    }
}
