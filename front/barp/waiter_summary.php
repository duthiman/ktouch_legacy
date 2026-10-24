<?php
session_start();
require_once '../../inc/connection.php';
require_once '../../inc/gen_funcs.php';

$user_id = (int)($_REQUEST['user_id'] ?? 0);
$shift = (string)get_column('kp_shift', 'shift', '1');
$safe_shift = mysqli_real_escape_string($connection, $shift);
$user = mysqli_fetch_assoc(mysqli_query($connection, "SELECT username FROM kp_users WHERE user_id='$user_id' LIMIT 1"));

$sales = mysqli_fetch_assoc(mysqli_query($connection, "SELECT COUNT(*) AS total_count, COALESCE(SUM(amount),0) AS total_amount FROM kp_sales WHERE user_id='$user_id' AND shift='$safe_shift'")) ?: [];
$returns = (float)column_total('kp_ret_items', 'amount', "user_id='$user_id' AND shift='$safe_shift'");
$voids = (int)column_count('kp_del_orders', 'sale_id', "user_id='$user_id' AND shift='$safe_shift'");
$open_orders = (int)column_count('kp_sales_open', 'sale_id', "user_id='$user_id' AND status=1");
$live_orders = (int)column_count('kp_sales', 'sale_id', "user_id='$user_id' AND shift='$safe_shift' AND status=1");

$item_rows = [];
$item_query = mysqli_query(
    $connection,
    "SELECT item_id, COALESCE(SUM(qty), 0) AS qty, COALESCE(SUM(total), 0) AS total
     FROM kp_sale_items
     WHERE user_id='$user_id' AND shift='$safe_shift'
     GROUP BY item_id
     ORDER BY total DESC"
);
while ($item_query && ($row = mysqli_fetch_assoc($item_query))) {
    $item_rows[] = $row;
}

$counter_rows = [];
$counter_query = mysqli_query(
    $connection,
    "SELECT s.counter AS counter_id,
            COALESCE(c.name, CONCAT('Counter ', s.counter)) AS counter_name,
            COALESCE(SUM(s.amount), 0) AS total
     FROM kp_sales s
     LEFT JOIN kp_counters c ON c.cntr_id=s.counter
     WHERE s.user_id='$user_id' AND s.shift='$safe_shift'
     GROUP BY s.counter, c.name
     ORDER BY total DESC"
);
while ($counter_query && ($row = mysqli_fetch_assoc($counter_query))) {
    $counter_rows[] = $row;
}
?>
<div class="modal-head user-summary-head">
        <div>
            <span class="user-dashboard-kicker">WAITER PERFORMANCE</span>
            <h2><?php echo htmlspecialchars((string)($user['username'] ?? 'Unknown user'), ENT_QUOTES, 'UTF-8'); ?></h2>
            <small><?php echo htmlspecialchars($shift, ENT_QUOTES, 'UTF-8'); ?></small>
        </div>
        <button type="button" class="modal-close" id="user-modal-close" aria-label="Close" data-dismiss="modal">&times;</button>
    </div>

    <div class="modal-body user-summary-body">
        <nav class="user-summary-tabs" aria-label="Waiter summary sections">
            <button type="button" class="user-summary-tab active" data-summary-tab="sales">Sales</button>
            <button type="button" class="user-summary-tab" data-summary-tab="items">Sold Items</button>
            <button type="button" class="user-summary-tab" data-summary-tab="returns">Returns</button>
            <button type="button" class="user-summary-tab" data-summary-tab="cancelled">Cancelled</button>
            <button type="button" class="user-summary-tab" data-summary-tab="voids">Voids</button>
            <button type="button" class="user-summary-tab" data-summary-tab="open">Open Orders</button>
            <button type="button" class="user-summary-tab" data-summary-tab="live">Live Orders</button>
        </nav>
        <div class="user-summary-panel active" data-summary-panel="sales">
        
        <div class="user-dashboard-metrics">
            <div>
                <small>SHIFT SALES</small>
                <strong>KES <?php echo number_format((float)($sales['total_amount'] ?? 0), 2); ?></strong>
            </div>
            <div>
                <small>TOTAL SHIFT RETURNS</small>
                <strong>KES <?php echo number_format($returns, 2); ?></strong>
            </div>
            <div>
                <small>TOTAL VOIDS</small>
                <strong><?php echo $voids; ?></strong>
            </div>
        </div>

        <h4>Sales by Counter</h4>
        <div class="summary-grid counter-sales-summary" style="margin:0 0 20px!important;">
            <?php if ($counter_rows) { ?>
                <?php foreach ($counter_rows as $row) { ?>
                    <div class="stat">
                        <small><?php echo htmlspecialchars((string)$row['counter_name'], ENT_QUOTES, 'UTF-8'); ?></small>
                        <strong>KES <?php echo number_format((float)$row['total'], 2); ?></strong>
                    </div>
                <?php } ?>
            <?php } else { ?>
                <div class="stat">
                    <small>COUNTER SALES</small>
                    <strong>KES 0.00</strong>
                </div>
            <?php } ?>
        </div>

        </div>
        <div class="user-summary-panel" data-summary-panel="items">
        <div class="user-sales">
            <table>
                <thead>
                    <tr>
                        <th>Item</th>
                        <th>Qty</th>
                        <th>Total</th>
                    </tr>
                </thead>
                <tbody>
                    <?php if ($item_rows) { ?>
                        <?php foreach ($item_rows as $row) { ?>
                            <tr>
                                <td><?php echo htmlspecialchars((string)get_item((int)$row['item_id']), ENT_QUOTES, 'UTF-8'); ?></td>
                                <td><?php echo number_format((float)$row['qty'], 2); ?></td>
                                <td>KES <?php echo number_format((float)$row['total'], 2); ?></td>
                            </tr>
                        <?php } ?>
                    <?php } else { ?>
                        <tr><td colspan="3">No items sold.</td></tr>
                    <?php } ?>
                </tbody>
            </table>
        </div>
        </div>
        <?php foreach ([
            'returns' => ['title' => 'Returned Items', 'count' => $returns, 'text' => 'Returns recorded for this waiter during the shift.'],
            'cancelled' => ['title' => 'Cancelled Bills', 'count' => 0, 'text' => 'Cancelled bills recorded for this waiter during the shift.'],
            'voids' => ['title' => 'Voids', 'count' => $voids, 'text' => 'Voided bills recorded for this waiter during the shift.'],
            'open' => ['title' => 'Open Orders', 'count' => $open_orders, 'text' => 'Open orders currently assigned to this waiter.'],
            'live' => ['title' => 'Live Orders', 'count' => $live_orders, 'text' => 'Live orders currently active for this waiter.'],
        ] as $key => $panel) { ?>
        <div class="user-summary-panel" data-summary-panel="<?php echo $key; ?>">
            <div class="user-summary-stat-card">
                <span><?php echo htmlspecialchars($panel['title'], ENT_QUOTES, 'UTF-8'); ?></span>
                <strong><?php echo number_format((float)$panel['count'], 0); ?></strong>
                <small><?php echo htmlspecialchars($panel['text'], ENT_QUOTES, 'UTF-8'); ?></small>
            </div>
        </div>
        <?php } ?>
    </div>

    <div class="modal-footer">
        <button type="button" class="btn btn-success" id="summary-print">PRINT SUMMARY</button>
        <button type="button" class="btn btn-outline-danger" data-dismiss="modal">CLOSE</button>
    </div>
