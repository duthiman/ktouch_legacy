<?php
session_start();
require_once '../../inc/connection.php';
require_once '../chaba/funcs.php';

$user_id = (int)($_REQUEST['user_id'] ?? 0);
$shift = (string)get_column('kp_shift', 'shift', '1');
$safe_shift = mysqli_real_escape_string($connection, $shift);
$user = mysqli_fetch_assoc(mysqli_query($connection, "SELECT username FROM kp_users WHERE user_id='$user_id' LIMIT 1"));

$sales = mysqli_fetch_assoc(mysqli_query($connection, "SELECT COUNT(*) AS total_count, COALESCE(SUM(amount),0) AS total_amount FROM kp_sales WHERE user_id='$user_id' AND shift='$safe_shift'")) ?: [];
$returns = (float)column_total('kp_ret_items', 'amount', "user_id='$user_id' AND shift='$safe_shift'");
$voids = (int)column_count('kp_del_orders', 'sale_id', "user_id='$user_id' AND shift='$safe_shift'");

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
<div class="modal-content">
    <div class="modal-header">
        <div>
            <span class="user-dashboard-kicker">WAITER PERFORMANCE</span>
            <h2><?php echo htmlspecialchars((string)($user['username'] ?? 'Unknown user'), ENT_QUOTES, 'UTF-8'); ?></h2>
            <small><?php echo htmlspecialchars($shift, ENT_QUOTES, 'UTF-8'); ?></small>
        </div>
        <button type="button" class="modal-close" aria-label="Close" data-dismiss="modal">&times;</button>
    </div>

    <div class="modal-body">
        
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

        <h4>Items sold</h4>
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

    <div class="modal-footer">
        <button type="button" class="btn btn-outline-danger" data-dismiss="modal">CLOSE</button>
    </div>
</div>
