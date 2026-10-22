<?php
require("../clude/head.php");
if (file_exists(__DIR__ . '/../../front/open/funcs.php')) {
    include_once(__DIR__ . '/../../front/open/funcs.php');
}
if (function_exists('open_ensure_parent_columns')) {
    open_ensure_parent_columns($connection);
}

$existingManualMenu = mysqli_query($connection, "SELECT menu_id FROM kp_menu WHERE link='sales/manual_print.php' LIMIT 1");
if ($existingManualMenu && mysqli_num_rows($existingManualMenu) === 0) {
    $parentMenuId = 0;
    $parentCandidates = [
        "name='Sales' AND parent=0",
        "name='Reports' AND parent=0",
        "name='Dashboard' AND parent=0",
    ];
    foreach ($parentCandidates as $candidateWhere) {
        $candidateId = (int)get_column('kp_menu', 'menu_id', $candidateWhere);
        if ($candidateId > 0) {
            $parentMenuId = $candidateId;
            break;
        }
    }
    if ($parentMenuId > 0) {
        mysqli_query(
            $connection,
            "INSERT INTO kp_menu(name, link, icon, level, parent, sub)
             VALUES('Manual Bill Print', 'sales/manual_print.php', 'fa fa-print', 2, '$parentMenuId', 2)"
        );
    }
}

if (!function_exists('manual_bill_money')) {
    function manual_bill_money($value) {
        return number_format((float)$value, 2);
    }
}

$sale_id = (int)($_GET['sale_id'] ?? $_POST['sale_id'] ?? 0);
$sale = null;
$family_rows = [];
$family_total = 0.0;
$root_parent_sale_id = 0;

if ($sale_id > 0) {
    $has_sale_parent = function_exists('open_column_exists') ? open_column_exists($connection, 'kp_sales', 'parent') : false;
    $has_sale_bill_role = function_exists('open_column_exists') ? open_column_exists($connection, 'kp_sales', 'bill_role') : false;
    $has_root_parent = function_exists('open_column_exists') ? open_column_exists($connection, 'kp_sales', 'root_parent_sale_id') : false;
    $has_previous_sale = function_exists('open_column_exists') ? open_column_exists($connection, 'kp_sales', 'previous_sale_id') : false;
    $has_previous_balance = function_exists('open_column_exists') ? open_column_exists($connection, 'kp_sales', 'previous_balance') : false;
    $has_current_total = function_exists('open_column_exists') ? open_column_exists($connection, 'kp_sales', 'current_total') : false;
    $has_current_balance = function_exists('open_column_exists') ? open_column_exists($connection, 'kp_sales', 'current_balance') : false;
    $sale_parent_select = $has_sale_parent ? "parent" : "0 AS parent";
    $sale_bill_role_select = $has_sale_bill_role ? "COALESCE(bill_role, 'closed') AS bill_role" : "'closed' AS bill_role";
    $root_parent_select = $has_root_parent ? "COALESCE(root_parent_sale_id, 0) AS root_parent_sale_id" : "0 AS root_parent_sale_id";
    $previous_sale_select = $has_previous_sale ? "COALESCE(previous_sale_id, 0) AS previous_sale_id" : "0 AS previous_sale_id";
    $previous_balance_select = $has_previous_balance ? "COALESCE(previous_balance, 0) AS previous_balance" : "0 AS previous_balance";
    $current_total_select = $has_current_total ? "COALESCE(current_total, amount) AS current_total" : "amount AS current_total";
    $current_balance_select = $has_current_balance ? "COALESCE(current_balance, amount) AS current_balance" : "amount AS current_balance";
    $sale_q = mysqli_query(
        $connection,
        "SELECT sale_id, user_id, amount, shift, counter, $sale_parent_select, pay_status, created_at,
                $sale_bill_role_select,
                $root_parent_select,
                $previous_sale_select,
                $previous_balance_select,
                $current_total_select,
                $current_balance_select
         FROM kp_sales
         WHERE sale_id='$sale_id'
         LIMIT 1"
    );
    if ($sale_q && mysqli_num_rows($sale_q) > 0) {
        $sale = mysqli_fetch_assoc($sale_q);
        $root_parent_sale_id = (int)$sale['root_parent_sale_id'];
        if ($root_parent_sale_id < 1) {
            $root_parent_sale_id = (int)$sale['parent'] > 0 ? (int)$sale['parent'] : (int)$sale['sale_id'];
        }
        $family_q = mysqli_query(
            $connection,
            "SELECT sale_id, amount, counter, $sale_parent_select,
                    $sale_bill_role_select,
                    $previous_sale_select,
                    $previous_balance_select,
                    $current_total_select,
                    $current_balance_select
             FROM kp_sales
             WHERE sale_id='$root_parent_sale_id' OR parent='$root_parent_sale_id'
             ORDER BY sale_id ASC"
        );
        if ($family_q) {
            while ($row = mysqli_fetch_assoc($family_q)) {
                $family_rows[] = $row;
                $family_total += (float)$row['amount'];
            }
        }
    }
}

$preview_items = [];
if ($sale) {
    $encoded_sale = urlencode((string)$sale_id);
    $preview_items = [
        [
            'key' => 'bill',
            'label' => 'Customer Bill',
            'path' => "../../front/chaba/render_bill.php?sale_id={$encoded_sale}",
        ],
        [
            'key' => 'bot',
            'label' => 'BOT',
            'path' => "../../front/chaba/render_bot.php?sale_id={$encoded_sale}",
        ],
        [
            'key' => 'kot',
            'label' => 'KOT',
            'path' => "../../front/chaba/render_kot.php?sale_id={$encoded_sale}",
        ],
        [
            'key' => 'cashier',
            'label' => 'Cashier Order',
            'path' => "../../front/chaba/render_cot.php?sale_id={$encoded_sale}",
        ],
    ];
}
?>
<div id="content" class="content">
    <ol class="breadcrumb pull-right">
        <li><a href="../dash/">Dashboard</a></li>
        <li class="active">Manual Bill Print</li>
    </ol>
    <h1 class="page-header">Manual Bill Print <small>Preview customer bill and kitchen/bar tickets</small></h1>

    <div class="panel panel-inverse">
        <div class="panel-heading">
            <h4 class="panel-title">Find Bill</h4>
        </div>
        <div class="panel-body">
            <form method="get" class="row" style="margin-bottom:0;">
                <div class="col-md-4 col-sm-6 col-xs-12">
                    <label for="sale_id" class="control-label">Sale ID / Bill Number</label>
                    <input type="number" min="1" name="sale_id" id="sale_id" class="form-control input-lg" value="<?php echo $sale_id > 0 ? (int)$sale_id : ''; ?>" placeholder="Enter sale id">
                </div>
                <div class="col-md-3 col-sm-6 col-xs-12" style="padding-top:25px;">
                    <button type="submit" class="btn btn-success btn-lg btn-block">Load Bill</button>
                </div>
            </form>
        </div>
    </div>

    <?php if ($sale_id > 0 && !$sale) { ?>
        <div class="alert alert-danger" style="border-radius:5px;">
            Bill <strong>#<?php echo (int)$sale_id; ?></strong> was not found.
        </div>
    <?php } ?>

    <?php if ($sale) { ?>
        <style>
            .manual-bill-grid {
                display: grid;
                grid-template-columns: repeat(auto-fit, minmax(180px, 1fr));
                gap: 12px;
                margin-bottom: 14px;
            }
            .manual-bill-card,
            .manual-bill-panel,
            .manual-bill-table,
            .manual-bill-frame {
                border-radius: 5px;
            }
            .manual-bill-card {
                background: #fff;
                border: 1px solid #e2e7eb;
                padding: 12px 14px;
                min-height: 96px;
            }
            .manual-bill-card .label {
                display: inline-block;
                font-size: 11px;
                letter-spacing: .04em;
                text-transform: uppercase;
                margin-bottom: 8px;
            }
            .manual-bill-card .value {
                display: block;
                font-size: 22px;
                font-weight: 700;
                color: #242a30;
                line-height: 1.2;
            }
            .manual-bill-card .sub {
                display: block;
                margin-top: 6px;
                color: #6c757d;
                font-size: 12px;
            }
            .manual-bill-panel {
                background: #fff;
                border: 1px solid #e2e7eb;
                padding: 12px;
                margin-bottom: 14px;
            }
            .manual-bill-table {
                width: 100%;
                border-collapse: collapse;
                overflow: hidden;
            }
            .manual-bill-table th,
            .manual-bill-table td {
                padding: 8px 10px;
                border-bottom: 1px solid #eef1f4;
                font-size: 12px;
            }
            .manual-bill-table th {
                background: #f7f9fb;
                color: #5e6a75;
                text-transform: uppercase;
                font-size: 11px;
            }
            .manual-bill-frames {
                display: grid;
                grid-template-columns: repeat(auto-fit, minmax(340px, 1fr));
                gap: 14px;
            }
            .manual-bill-frame {
                background: #fff;
                border: 1px solid #d8dee4;
                overflow: hidden;
            }
            .manual-bill-frame-head {
                display: flex;
                justify-content: space-between;
                align-items: center;
                gap: 10px;
                padding: 10px 12px;
                background: #f7f9fb;
                border-bottom: 1px solid #e5eaee;
            }
            .manual-bill-frame-head h4 {
                margin: 0;
                font-size: 14px;
                font-weight: 700;
            }
            .manual-bill-frame-actions .btn {
                border-radius: 5px;
                margin-left: 6px;
            }
            .manual-bill-iframe {
                width: 100%;
                height: 540px;
                border: 0;
                background: #fff;
            }
            @media (max-width: 767px) {
                .manual-bill-frames {
                    grid-template-columns: 1fr;
                }
                .manual-bill-frame-actions .btn {
                    margin-top: 6px;
                    margin-left: 0;
                }
                .manual-bill-frame-head {
                    display: block;
                }
            }
        </style>

        <div class="manual-bill-grid">
            <div class="manual-bill-card">
                <span class="label label-primary">Sale ID</span>
                <span class="value">#<?php echo (int)$sale['sale_id']; ?></span>
                <span class="sub"><?php echo htmlspecialchars((string)$sale['bill_role']); ?> bill</span>
            </div>
            <div class="manual-bill-card">
                <span class="label label-success">Current Bill</span>
                <span class="value"><?php echo manual_bill_money($sale['current_total']); ?></span>
                <span class="sub">Saved snapshot total for this bill</span>
            </div>
            <div class="manual-bill-card">
                <span class="label label-warning">Previous Balance</span>
                <span class="value"><?php echo manual_bill_money($sale['previous_balance']); ?></span>
                <span class="sub">Previous sale: #<?php echo (int)$sale['previous_sale_id']; ?></span>
            </div>
            <div class="manual-bill-card">
                <span class="label label-danger">Current Balance</span>
                <span class="value"><?php echo manual_bill_money($sale['current_balance']); ?></span>
                <span class="sub">Family total: <?php echo manual_bill_money($family_total); ?></span>
            </div>
            <div class="manual-bill-card">
                <span class="label label-default">Counter / User</span>
                <span class="value"><?php echo htmlspecialchars(get_counter((int)$sale['counter'])); ?></span>
                <span class="sub"><?php echo htmlspecialchars(get_user((int)$sale['user_id'])); ?></span>
            </div>
            <div class="manual-bill-card">
                <span class="label label-info">Root Parent</span>
                <span class="value">#<?php echo (int)$root_parent_sale_id; ?></span>
                <span class="sub">Shift: <?php echo htmlspecialchars((string)$sale['shift']); ?></span>
            </div>
        </div>

        <div class="manual-bill-panel">
            <h4 style="margin-top:0;">Bill Family</h4>
            <div class="table-responsive">
                <table class="manual-bill-table">
                    <thead>
                        <tr>
                            <th>Sale ID</th>
                            <th>Role</th>
                            <th>Parent</th>
                            <th>Previous Sale</th>
                            <th>Bill Total</th>
                            <th>Prev Balance</th>
                            <th>Current Balance</th>
                            <th>Counter</th>
                        </tr>
                    </thead>
                    <tbody>
                        <?php foreach ($family_rows as $row) { ?>
                            <tr<?php echo (int)$row['sale_id'] === (int)$sale['sale_id'] ? ' style="background:#fff9e8;"' : ''; ?>>
                                <td>#<?php echo (int)$row['sale_id']; ?></td>
                                <td><?php echo htmlspecialchars((string)$row['bill_role']); ?></td>
                                <td><?php echo (int)$row['parent'] > 0 ? '#' . (int)$row['parent'] : '-'; ?></td>
                                <td><?php echo (int)$row['previous_sale_id'] > 0 ? '#' . (int)$row['previous_sale_id'] : '-'; ?></td>
                                <td><?php echo manual_bill_money($row['current_total']); ?></td>
                                <td><?php echo manual_bill_money($row['previous_balance']); ?></td>
                                <td><?php echo manual_bill_money($row['current_balance']); ?></td>
                                <td><?php echo htmlspecialchars(get_counter((int)$row['counter'])); ?></td>
                            </tr>
                        <?php } ?>
                    </tbody>
                </table>
            </div>
        </div>

        <div class="manual-bill-frames">
            <?php foreach ($preview_items as $item) { ?>
                <div class="manual-bill-frame">
                    <div class="manual-bill-frame-head">
                        <h4><?php echo htmlspecialchars($item['label']); ?></h4>
                        <div class="manual-bill-frame-actions">
                            <a href="<?php echo htmlspecialchars($item['path']); ?>" target="_blank" class="btn btn-default btn-sm">Open Raw</a>
                            <button type="button" class="btn btn-success btn-sm" onclick="manualPrintFrame('<?php echo $item['key']; ?>')">Print</button>
                        </div>
                    </div>
                    <iframe id="manual-frame-<?php echo $item['key']; ?>" class="manual-bill-iframe" src="<?php echo htmlspecialchars($item['path']); ?>"></iframe>
                </div>
            <?php } ?>
        </div>

        <script>
            function manualPrintFrame(key) {
                var frame = document.getElementById('manual-frame-' + key);
                if (!frame || !frame.contentWindow) {
                    return;
                }
                frame.contentWindow.focus();
                frame.contentWindow.print();
            }
        </script>
    <?php } ?>
</div>
<?php require("../clude/foot.php"); ?>
