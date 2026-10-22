<?php
require("../clude/head.php");

$settingsModules = [
    [
        'key' => 'inventory',
        'title' => 'Inventory',
        'icon' => 'fa-cubes',
        'items' => [
            ['id' => 'happy', 'label' => 'Happy Hour', 'copy' => 'Control happy-hour timing and selling windows.'],
            ['id' => 'points', 'label' => 'Waiter Tokens', 'copy' => 'Manage waiter token and point behavior.'],
        ],
    ],
    [
        'key' => 'purchase',
        'title' => 'Purchase',
        'icon' => 'fa-shopping-cart',
        'items' => [
            ['href' => 'purchase_set.php', 'label' => 'Purchase Settings', 'copy' => 'Front purchase and auto-stock rules.'],
        ],
    ],
    [
        'key' => 'warehouse-stock',
        'title' => 'Warehouse Stock',
        'icon' => 'fa-archive',
        'items' => [
            ['id' => 'gen', 'label' => 'General Details', 'copy' => 'Base business information used across stock documents.'],
            ['id' => 'logo-set', 'label' => 'Logos', 'copy' => 'Main and thermal branding assets.'],
            ['id' => 'biz-profile', 'label' => 'Business Profile', 'copy' => 'Branch and business identity setup.'],
            ['id' => 'front-images', 'label' => 'Front Images', 'copy' => 'Upload and assign images used by the front screens.'],
        ],
    ],
    [
        'key' => 'counters-stock',
        'title' => 'Counters Stock',
        'icon' => 'fa-cutlery',
        'items' => [
            ['id' => 'counter', 'label' => 'Bar Counters', 'copy' => 'Counter setup, ownership, and printer assignment.'],
            ['id' => 'kiosk-set', 'label' => 'Front Panel / Kiosk', 'copy' => 'Front operation and kiosk-facing options.'],
        ],
    ],
    [
        'key' => 'finance',
        'title' => 'Finance',
        'icon' => 'fa-money',
        'items' => [
            ['id' => 'pay-method', 'label' => 'Payment Methods', 'copy' => 'Configure payment channels and defaults.'],
            ['id' => 'dbback', 'label' => 'DB Backup', 'copy' => 'Database backup and protection tools.'],
            ['href' => 'return_set.php', 'label' => 'Return Settings', 'copy' => 'Approval behavior for returns.'],
        ],
    ],
    [
        'key' => 'kreach',
        'title' => 'KReach',
        'icon' => 'fa-commenting',
        'items' => [
            ['id' => 'kreach-set', 'label' => 'KReach SMS', 'copy' => 'SMS provider and messaging preferences.'],
        ],
    ],
    [
        'key' => 'kpesa',
        'title' => 'KPesa',
        'icon' => 'fa-mobile',
        'items' => [
            ['id' => 'kpesa-set', 'label' => 'KPesa Settings', 'copy' => 'KPesa API, STK, and integration options.'],
        ],
    ],
    [
        'key' => 'sales',
        'title' => 'Sales',
        'icon' => 'fa-line-chart',
        'items' => [
            ['id' => 'sales', 'label' => 'ID Settings', 'copy' => 'Sales numbering and related operational rules.'],
        ],
    ],
    [
        'key' => 'administration',
        'title' => 'Administration',
        'icon' => 'fa-user-secret',
        'items' => [
            ['href' => '../sys_menu/menu.php?pnl=1', 'label' => 'Back Menus', 'copy' => 'Manage admin-side menu visibility.'],
            ['href' => '../sys_menu/menu.php?pnl=2', 'label' => 'Front Menu', 'copy' => 'Manage front panel menu visibility.'],
        ],
    ],
    [
        'key' => 'printing',
        'title' => 'Printing',
        'icon' => 'fa-print',
        'items' => [
            ['id' => 'thermal-print', 'label' => 'Thermal Print', 'copy' => 'Thermal receipt template and behavior.'],
            ['id' => 'A4-print', 'label' => 'A4 Print', 'copy' => 'A4 document print layout options.'],
            ['id' => 'printer-set', 'label' => 'Printer Setup', 'copy' => 'Printer registry, QZ, and counter printer mapping.'],
        ],
    ],
];
?>

<div id="page-container" class="page-container fade page-without-sidebar page-header-fixed settings-standalone-shell">
    <style>
        #page-container.settings-standalone-shell { min-height:100vh; background:#f3f5f8; }
        .settings-standalone-shell #content { margin-left:0 !important; padding:18px 22px 30px; }
        .settings-page { max-width:1500px; margin:0 auto; padding:15px; }
        .settings-standalone-bar { display:flex; align-items:center; justify-content:space-between; margin-bottom:14px; }
        .settings-brand { color:#753f91; font-size:19px; font-weight:800; letter-spacing:.02em; }
        .settings-home-btn { display:inline-flex; align-items:center; gap:7px; padding:10px 16px; border-radius:7px; background:#753f91; color:#fff; font-size:12px; font-weight:700; text-decoration:none; box-shadow:0 4px 12px rgba(117,63,145,.22); }
        .settings-home-btn:hover, .settings-home-btn:focus { background:#613477; color:#fff; text-decoration:none; }
        .settings-hero.panel {
            margin-bottom: 15px;
            border: 0;
            border-radius:10px;
            overflow:hidden;
            box-shadow: 0 8px 24px rgba(34, 42, 50, .09);
        }
        .settings-hero .panel-body {
            padding: 18px 20px 14px;
            background: linear-gradient(135deg, #ffffff 0%, #f7f1fa 100%);
        }
        .settings-hero h1 {
            margin: 0;
            font-size: 24px;
            font-weight: 700;
            color: #242a30;
        }
        .settings-hero p {
            margin: 6px 0 0;
            max-width: 900px;
            font-size: 13px;
            line-height: 1.7;
            color: #6c757d;
        }
        .settings-tabs.nav {
            margin-top: 15px;
            border-bottom: 1px solid #d9dee3;
        }
        .settings-tabs.nav > li > a {
            margin-right: 4px;
            padding: 10px 14px;
            border: 1px solid transparent;
            border-bottom: 0;
            border-radius: 4px 4px 0 0;
            color: #5f6b76;
            font-size: 12px;
            font-weight: 700;
            text-transform: uppercase;
            background: transparent;
        }
        .settings-tabs.nav > li > a:hover,
        .settings-tabs.nav > li > a:focus {
            background: #eef1f5;
            color: #242a30;
        }
        .settings-grid {
            display: grid;
            grid-template-columns: minmax(0, 1.05fr) minmax(360px, .95fr);
            gap: 15px;
            align-items: start;
        }
        .settings-modules {
            display: grid;
            gap: 15px;
        }
        .settings-module.panel {
            margin-bottom: 0;
            border: 0;
            box-shadow: 0 1px 3px rgba(0, 0, 0, .12);
        }
        .settings-module .panel-heading {
            display: flex;
            align-items: center;
            justify-content: space-between;
            gap: 12px;
            background: #ffffff;
            border-bottom: 1px solid #eef1f5;
            padding: 12px 15px;
        }
        .settings-module-title {
            display: flex;
            align-items: center;
            gap: 10px;
            font-size: 17px;
            font-weight: 700;
            color: #242a30;
            margin: 0;
        }
        .settings-module-badge {
            width: 30px;
            height: 30px;
            border-radius: 4px;
            background: #753f91;
            color: #fff;
            display: inline-flex;
            align-items: center;
            justify-content: center;
            font-size: 13px;
        }
        .settings-module-count {
            display: inline-block;
            padding: 4px 8px;
            border-radius: 10px;
            background: #eef1f5;
            color: #5f6b76;
            font-size: 11px;
            font-weight: 700;
            text-transform: uppercase;
        }
        .settings-actions-table {
            width: 100%;
            border-collapse: collapse;
        }
        .settings-actions-table tr + tr td {
            border-top: 1px solid #eef1f5;
        }
        .settings-actions-table td {
            vertical-align: top;
            padding: 12px 15px;
            background: #fff;
        }
        .settings-action-main {
            display: block;
            width: 100%;
            padding: 0;
            border: 0;
            background: transparent;
            text-align: left;
            color: #2d353c;
            font-size: 14px;
            font-weight: 700;
        }
        .settings-action-main:hover,
        .settings-action-main:focus {
            color: #753f91;
            text-decoration: none;
            outline: none;
        }
        .settings-action-copy {
            display: block;
            margin-top: 4px;
            color: #7f8c97;
            font-size: 12px;
            line-height: 1.6;
            font-weight: 400;
        }
        .settings-action-arrow {
            width: 40px;
            text-align: right;
            color: #b0b9c1;
            font-size: 14px;
        }
        .settings-panel.panel {
            margin-bottom: 0;
            border: 0;
            box-shadow: 0 1px 3px rgba(0, 0, 0, .12);
            min-height: 480px;
        }
        .settings-panel .panel-heading {
            background: #ffffff;
            border-bottom: 1px solid #eef1f5;
            padding: 12px 15px;
        }
        .settings-panel .panel-title {
            font-size: 17px;
            font-weight: 700;
            color: #242a30;
        }
        .settings-panel-copy {
            margin-top: 4px;
            font-size: 12px;
            color: #7f8c97;
        }
        #set {
            padding: 15px;
            min-height: 380px;
            background: #fff;
        }
        .settings-panel-placeholder {
            padding: 8px 0;
            color: #5f6b76;
        }
        .settings-panel-placeholder strong {
            display: block;
            margin-bottom: 10px;
            font-size: 16px;
            font-weight: 700;
            color: #242a30;
        }
        .settings-panel-placeholder ul {
            margin: 10px 0 0 18px;
            padding: 0;
        }
        .settings-panel-placeholder li {
            margin-bottom: 7px;
            font-size: 13px;
        }
        @media (max-width: 991.98px) {
            .settings-grid {
                grid-template-columns: 1fr;
            }
        }
        @media (max-width: 767.98px) {
            .settings-page {
                padding: 10px;
            }
            .settings-hero h1 {
                font-size: 21px;
            }
        }
    </style>

    <div id="content" class="content settings-page">
        <div class="settings-standalone-bar">
            <div class="settings-brand"><i class="fa fa-sliders"></i> System Settings</div>
            <a href="../dash/admin_dash.php" class="settings-home-btn"><i class="fa fa-home"></i> Home</a>
        </div>
        <div class="panel settings-hero">
            <div class="panel-body">
                <h1>Configure your system</h1>
                <p>Choose a settings group below to manage the system. Use the Home button whenever you want to return to the admin dashboard.</p>
                <ul class="nav nav-tabs settings-tabs">
                    <?php foreach ($settingsModules as $index => $module): ?>
                        <li class="<?php echo $index === 0 ? 'active' : ''; ?>">
                            <a href="#settings-module-<?php echo htmlspecialchars($module['key']); ?>">
                                <?php echo htmlspecialchars($module['title']); ?>
                            </a>
                        </li>
                    <?php endforeach; ?>
                </ul>
            </div>
        </div>

        <div class="settings-grid">
            <div class="settings-modules">
                <?php foreach ($settingsModules as $module): ?>
                    <section class="panel settings-module" id="settings-module-<?php echo htmlspecialchars($module['key']); ?>">
                        <div class="panel-heading">
                            <h2 class="settings-module-title">
                                <span class="settings-module-badge"><i class="fa <?php echo htmlspecialchars($module['icon']); ?>"></i></span>
                                <?php echo htmlspecialchars($module['title']); ?>
                            </h2>
                            <span class="settings-module-count"><?php echo count($module['items']); ?> Items</span>
                        </div>
                        <div class="panel-body" style="padding:0;">
                            <table class="settings-actions-table">
                                <tbody>
                                    <?php foreach ($module['items'] as $item): ?>
                                        <tr>
                                            <td>
                                                <?php if (isset($item['id'])): ?>
                                                    <button type="button" class="settings-action-main tablinks" id="<?php echo htmlspecialchars($item['id']); ?>">
                                                        <?php echo htmlspecialchars($item['label']); ?>
                                                    </button>
                                                <?php else: ?>
                                                    <a href="<?php echo htmlspecialchars($item['href']); ?>" class="settings-action-main">
                                                        <?php echo htmlspecialchars($item['label']); ?>
                                                    </a>
                                                <?php endif; ?>
                                                <span class="settings-action-copy"><?php echo htmlspecialchars($item['copy']); ?></span>
                                            </td>
                                            <td class="settings-action-arrow">
                                                <i class="fa fa-angle-right"></i>
                                            </td>
                                        </tr>
                                    <?php endforeach; ?>
                                </tbody>
                            </table>
                        </div>
                    </section>
                <?php endforeach; ?>
            </div>

            <aside class="panel settings-panel">
                <div class="panel-heading">
                    <div class="panel-title">Settings Workspace</div>
                    <div class="settings-panel-copy">Loaded settings pages open here without leaving the grouped module view.</div>
                </div>
                <div id="set">
                    <div class="settings-panel-placeholder">
                        <strong>Select a settings tool to begin.</strong>
                        The grouped modules on the left are arranged as:
                        <ul>
                            <li>Inventory</li>
                            <li>Purchase</li>
                            <li>Warehouse Stock</li>
                            <li>Counters Stock</li>
                            <li>Finance</li>
                            <li>KReach</li>
                            <li>KPesa</li>
                            <li>Sales</li>
                            <li>Administration</li>
                            <li>Printing</li>
                        </ul>
                    </div>
                </div>
            </aside>
        </div>
    </div>
</div>

<script src="../../assets/plugins/jquery/jquery-3.2.1.min.js"></script>
<script src="../../assets/plugins/jquery-ui/jquery-ui.min.js"></script>
<script src="../../assets/plugins/jquery/jquery-migrate-1.1.0.min.js"></script>
<script src="../../assets/plugins/bootstrap/4.0.0/js/bootstrap.bundle.min.js"></script>
<script src="../../assets/plugins/slimscroll/jquery.slimscroll.min.js"></script>
<script src="../../assets/plugins/js-cookie/js.cookie.js"></script>
<script src="../../assets/js/theme/default.min.js"></script>
<script src="../../assets/plugins/bootstrap-datepicker/js/bootstrap-datepicker.js"></script>
<script src="../../assets/plugins/bootstrap-timepicker/js/bootstrap-timepicker.min.js"></script>
<script src="../../assets/plugins/bootstrap-daterangepicker/moment.js"></script>
<script src="../../assets/plugins/bootstrap-daterangepicker/daterangepicker.js"></script>
<script src="../../assets/plugins/bootstrap-eonasdan-datetimepicker/build/js/bootstrap-datetimepicker.min.js"></script>
<script src="../../assets/plugins/Hullabaloo/js/hullabaloo.js"></script>
<script src="../../assets/js/apps.min.js"></script>
<script>
    $(document).ready(function() {
        App.init();
        $('#datetimepicker1').datetimepicker();
        $('#datetimepicker2').datetimepicker({
            format: 'LT'
        });
    });
    <?php include("settings.js"); ?>
</script>
</body>
</html>
