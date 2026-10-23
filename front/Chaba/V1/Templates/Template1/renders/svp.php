<?php

$header = is_array($document['header'] ?? null) ? $document['header'] : [];
$summary = is_array($document['variance_summary'] ?? null) ? $document['variance_summary'] : [];
$rows = is_array($document['variance_items'] ?? null) ? $document['variance_items'] : [];

$rows = array_values(array_filter(
    $rows,
    static fn($row) => (float)($row['variance'] ?? 0) !== 0.0
));

$escape = static fn($value) => htmlspecialchars(
    (string)$value,
    ENT_QUOTES | ENT_SUBSTITUTE,
    'UTF-8'
);

$formatNumber = static fn($value) => rtrim(
    rtrim(number_format((float)$value, 2, '.', ''), '0'),
    '.'
);

$varianceCount = count($rows);
$varianceTotal = array_reduce(
    $rows,
    static fn($total, $row) => $total + abs((float)($row['variance'] ?? 0)),
    0.0
);
?>
<!doctype html>
<html lang="en">
<head>
    <meta charset="utf-8">
    <title>Variance</title>
    <style>
        @page {
            size: 79mm auto;
            margin: 0 !important;
        }

        * {
            box-sizing: border-box;
        }

        html,
        body {
            width: 79mm !important;
            max-width: 79mm !important;
            margin: 0 !important;
            padding: 0 !important;
            background: #fff;
            color: #000;
        }

        body {
            font-family: Arial, "Helvetica Neue", Helvetica, sans-serif;
            font-size: 12px;
        }

        .receipt {
            width: 79mm;
            max-width: 79mm;
            margin: 0;
            padding: 0.5mm 0;
        }

        .border {
            width: 79mm;
            max-width: 79mm;
            border: 2px solid #000;
        }

        .center {
            text-align: center !important;
        }

        .divider {
            clear: both;
            display: block;
            margin: 3px 0;
            line-height: 1.1;
            white-space: nowrap;
            overflow: hidden;
            text-align: center;
            font-weight: 700;
            letter-spacing: 0.06em;
        }

        .header h2 {
            margin: 0;
            font-size: 18px;
        }

        .header p {
            margin: 0;
            font-size: 12px;
            font-weight: 800;
        }

        .logo {
            display: block;
            max-width: 100%;
            max-height: 60px;
            margin: 4px auto;
            object-fit: contain;
        }

        .summary {
            display: grid;
            grid-template-columns: 1fr 1fr;
            gap: 6px;
            padding: 6px 4px;
        }

        .summary-box {
            padding: 5px 4px;
            border: 1px solid #000;
            text-align: center;
        }

        .summary-box strong {
            display: block;
            font-size: 10px;
            text-transform: uppercase;
        }

        .summary-box span {
            display: block;
            margin-top: 2px;
            font-size: 14px;
            font-weight: 900;
        }

        .items {
            width: calc(100% - 2px);
            max-width: calc(100% - 2px);
            margin: 1px;
            border-collapse: collapse;
            table-layout: fixed;
            background: #fff;
        }

        .items th,
        .items td {
            padding: 3px 2px;
            border: 1px solid #000 !important;
            font-family: Arial, "Helvetica Neue", Helvetica, sans-serif;
            font-size: 12px;
            white-space: nowrap;
            overflow: hidden;
            text-overflow: clip;
        }

        .items th {
            font-size: 11px;
            font-weight: 900;
            text-transform: uppercase;
        }

        .items td:first-child,
        .items th:first-child {
            width: 34%;
            text-align: left;
        }

        .items td:first-child {
            white-space: normal;
            overflow-wrap: break-word;
            word-break: break-word;
        }

        .items td:nth-child(n + 2),
        .items th:nth-child(n + 2) {
            width: 16%;
            text-align: right;
        }

        .items td:nth-child(4) {
            font-family: Arial, "Helvetica Neue", Helvetica, sans-serif;
            font-size: 12px;
            font-weight: 400;
        }

        .up {
            color: #000;
        }

        .down {
            color: #000;
        }

        .empty {
            padding: 12px 8px;
            text-align: center;
            font-size: 12px;
            font-weight: 800;
        }
    </style>
</head>
<body>
    <main class="receipt">
        <div class="border">
            <header class="header center">
                <div class="divider">***********************************************************</div>
                <?php if (!empty($header['logo_src'])): ?>
                    <img class="logo" src="<?= $escape($header['logo_src']) ?>" alt="Logo">
                <?php endif; ?>
                <h2>
                    <?= $escape(strtoupper($header['counter_name'] ?? '') . ' VARIANCE REPORT') ?>
                </h2>
                <div class="divider">***********************************************************</div>
                <p>SHIFT: <?= $escape($header['shift'] ?? $header['shift_date'] ?? '') ?></p>
                <p>PRINT TIME: <?= $escape($header['print_time'] ?? '') ?></p>
                <div class="divider">***********************************************************</div>
            </header>

            <section class="summary">
                <div class="summary-box">
                    <strong>Variance Items</strong>
                    <span><?= $varianceCount ?></span>
                </div>
                <div class="summary-box">
                    <strong>Total Variance</strong>
                    <span><?= $formatNumber($varianceTotal) ?></span>
                </div>
            </section>

            <?php if (!$rows): ?>
                <div class="empty">All counted items are balanced. No variance found.</div>
            <?php else: ?>
                <table class="items">
                    <thead>
                        <tr>
                            <th>Item</th>
                            <th>Open</th>
                            <th>Count</th>
                            <th>Var</th>
                        </tr>
                    </thead>
                    <tbody>
                        <?php foreach ($rows as $row): ?>
                            <?php
                            $row = is_array($row) ? $row : [];
                            $variance = (float)($row['variance'] ?? 0);
                            ?>
                            <tr>
                                <td>
                                    <?= $escape($row['item'] ?? $row['name'] ?? $row['item_name'] ?? '') ?>
                                </td>
                                <td>
                                    <?= $formatNumber($row['opening'] ?? $row['expected'] ?? $row['system_qty'] ?? 0) ?>
                                </td>
                                <td>
                                    <?= $formatNumber($row['counted'] ?? $row['counted_qty'] ?? 0) ?>
                                </td>
                                <td class="<?= $variance >= 0 ? 'up' : 'down' ?>">
                                    <?= $variance > 0 ? '+' : '' ?><?= $formatNumber($variance) ?>
                                </td>
                            </tr>
                        <?php endforeach; ?>
                    </tbody>
                </table>
            <?php endif; ?>

            <div class="divider">***********************************************************</div>
        </div>
    </main>
</body>
</html>
