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

<div id="page-container" class="page-container fade page-without-sidebar page-header-fixed kt-settings-shell">
<style>
:root{--kt-green:#073e35;--kt-green2:#006b51;--kt-orange:#fa7925;--kt-ink:#142c43;--kt-muted:#718297;--kt-border:#e3eaf0;--kt-bg:#f3f6f8}
.kt-settings-shell{min-height:100vh;background:var(--kt-bg);font-family:Arial,"Helvetica Neue",sans-serif;color:var(--kt-ink)}
.kt-settings-shell #content{margin:0!important;padding:0!important;max-width:none!important}
.kt-top{height:70px;background:linear-gradient(100deg,#00382f,#004f41);display:flex;align-items:center;justify-content:space-between;padding:0 28px;color:white;gap:16px}
.kt-logo{font-size:27px;font-weight:850;letter-spacing:-1px;color:#fff;line-height:1}.kt-logo b{color:#ff8b32}.kt-logo small{display:block;font-size:9px;letter-spacing:1px;font-weight:600;margin-top:5px;color:#d1e4df}
.kt-top-title{margin-right:auto;margin-left:26px;border-left:1px solid #ffffff30;padding-left:24px;font-size:20px;font-weight:750}.kt-top-title small{display:block;font-size:12px;font-weight:400;color:#bdd8d0;margin-top:4px}
.kt-branch{border:1px solid #ffffff30;border-radius:6px;padding:11px 18px;font-size:12px;color:#fff;background:#ffffff0b}.kt-admin{font-size:13px;font-weight:700;white-space:nowrap}.kt-avatar{display:inline-flex;align-items:center;justify-content:center;width:35px;height:35px;border-radius:50%;background:#14866d;margin-right:8px}
.kt-body{padding:20px 26px 24px;max-width:1800px;margin:auto}
.kt-toolbar{background:white;border:1px solid var(--kt-border);box-shadow:0 3px 14px #20384b08;border-radius:8px;padding:13px 18px;display:flex;align-items:center;justify-content:space-between;gap:12px;margin-bottom:16px}
.kt-crumb{font-size:13px;font-weight:750}.kt-crumb a{color:#536b80;margin-right:16px;text-decoration:none}.kt-crumb span{border-left:1px solid var(--kt-border);padding-left:16px}
.kt-search{width:260px;max-width:100%;border:1px solid #d7e2e9;border-radius:6px;padding:10px 12px;color:var(--kt-ink);font-size:12px;outline:none}.kt-search:focus{border-color:#07936d}
.kt-grid{display:grid;grid-template-columns:minmax(290px,390px) minmax(0,1fr);gap:20px;align-items:start}
.kt-categories{display:grid;gap:8px;max-height:calc(100vh - 190px);overflow:auto;padding:1px 3px 6px 1px;scrollbar-width:thin}
.kt-category{display:flex;width:100%;align-items:center;gap:14px;text-align:left;border:1px solid var(--kt-border);background:white;border-radius:9px;padding:13px 14px;min-height:72px;cursor:pointer;box-shadow:0 2px 6px #213b4b07;transition:background .15s,border .15s}
.kt-category:hover{border-color:#b7d8ca;background:#f9fdfb}.kt-category.active{border-color:#0ab37a;background:#effaf5;box-shadow:0 0 0 1px #0ab37a12}
.kt-category-icon{width:44px;height:44px;min-width:44px;border-radius:9px;display:flex;align-items:center;justify-content:center;font-size:20px;color:white;background:var(--kt-green2)}
.kt-category:nth-child(4n+1) .kt-category-icon{background:#ff7826}.kt-category:nth-child(4n+2) .kt-category-icon{background:#008c64}.kt-category:nth-child(4n+3) .kt-category-icon{background:#1684e5}.kt-category:nth-child(4n+4) .kt-category-icon{background:#8243d9}
.kt-category-copy{flex:1;min-width:0}.kt-category-copy strong{display:block;font-size:13px;color:#152e44;margin-bottom:4px}.kt-category-copy small{display:block;font-size:11px;color:#71859a;line-height:1.35}.kt-count{background:#e9f0f4;color:#587080;font-size:10px;border-radius:15px;padding:4px 8px;white-space:nowrap}.kt-category .fa-chevron-right{font-size:11px;color:#4d6d83}
.kt-workspace{background:#fff;border:1px solid var(--kt-border);border-radius:10px;min-height:620px;padding:20px 20px 24px;box-shadow:0 3px 12px #20384b08}
.kt-work-header{display:flex;align-items:center;gap:14px;padding-bottom:16px;border-bottom:1px solid var(--kt-border)}.kt-work-icon{height:49px;width:49px;min-width:49px;border-radius:10px;background:#fff0e7;color:#ff7826;display:flex;align-items:center;justify-content:center;font-size:22px}.kt-work-title{flex:1}.kt-work-title h1{font-size:22px;font-weight:800;margin:0 0 5px;color:#142c43}.kt-work-title p{margin:0;color:#718297;font-size:12px}
.kt-scope{background:#f0faf5;padding:8px 10px;border-radius:7px;font-size:11px;color:#177e59}.kt-scope select{display:block;margin-top:5px;background:white;border:1px solid #d8e4e8;padding:7px 10px;border-radius:5px;min-width:165px;color:#243c51;font-size:12px}
.kt-subnav{display:flex;gap:8px;overflow:auto;padding:14px 0;border-bottom:1px solid var(--kt-border)}.kt-subnav span{color:#fb7926;font-size:12px;font-weight:700;border-bottom:2px solid #fb7926;padding:0 8px 12px;white-space:nowrap}
.kt-module-content{display:none}.kt-module-content.active{display:block}.kt-section{margin-top:15px;border:1px solid var(--kt-border);border-radius:8px;overflow:hidden}.kt-section-heading{padding:15px 16px;border-left:4px solid var(--kt-orange);background:#fff}.kt-section-heading h2{font-size:15px;font-weight:800;margin:0 0 4px}.kt-section-heading p{font-size:11px;color:var(--kt-muted);margin:0}
.kt-action-list{padding:4px 14px 14px}.kt-action-row{display:flex;align-items:center;gap:12px;border-top:1px solid #edf1f5;padding:13px 4px}.kt-action-icon{background:#eef8f3;color:#00795b;border-radius:7px;width:34px;height:34px;display:flex;align-items:center;justify-content:center}.kt-action-text{flex:1}.kt-action-main{border:0;background:none;color:#16354b;font-size:13px;font-weight:750;padding:0;cursor:pointer;text-align:left;text-decoration:none;display:inline-block}.kt-action-main:hover{color:#f07823;text-decoration:none}.kt-action-text small{display:block;font-size:11px;color:#7c8d9d;margin-top:5px}.kt-action-row>.fa{color:#8da0ad}
.kt-loaded{margin-top:18px;border:1px solid var(--kt-border);border-radius:8px;overflow:hidden;background:#fff}.kt-loaded-header{padding:12px 15px;background:#f7fafb;border-bottom:1px solid var(--kt-border);font-size:13px;font-weight:750;display:flex;justify-content:space-between;align-items:center}.kt-loaded-header small{font-size:11px;font-weight:400;color:#81919f}.kt-loaded #set{min-height:220px;padding:18px;background:white}.kt-placeholder{color:#81919f;font-size:12px;line-height:1.8}.kt-footer{display:flex;justify-content:space-between;align-items:center;padding:15px 4px 0;font-size:11px;color:#81919f}.kt-footer b{color:#00735b}
@media(max-width:900px){.kt-grid{grid-template-columns:280px minmax(0,1fr)}.kt-body{padding:14px}.kt-scope{display:none}.kt-top-title{font-size:16px}}
@media(max-width:650px){.kt-grid{grid-template-columns:1fr}.kt-categories{display:flex;overflow:auto;max-height:none;padding-bottom:10px}.kt-category{min-width:185px;max-width:185px}.kt-category-copy small,.kt-count{display:none}.kt-workspace{min-height:500px;padding:14px}.kt-top{padding:0 14px}.kt-top-title{margin-left:12px;padding-left:12px}.kt-top-title small,.kt-admin{display:none}.kt-toolbar{padding:10px}.kt-search{width:165px}.kt-work-title h1{font-size:18px}}
</style>
<div id="content" class="content">
<header class="kt-top"><div class="kt-logo"><b>K</b>-TOUCH<small>POS &amp; BUSINESS MANAGEMENT</small></div><div class="kt-top-title"><i class="fa fa-cog"></i> &nbsp; Settings Center<small>Manage your business, POS and system settings</small></div><div class="kt-branch"><i class="fa fa-building-o"></i> &nbsp; Main Branch &nbsp; <i class="fa fa-angle-down"></i></div><div class="kt-admin"><span class="kt-avatar">A</span> Admin</div></header>
<main class="kt-body"><div class="kt-toolbar"><div class="kt-crumb"><a href="../dash/admin_dash.php"><i class="fa fa-home"></i> &nbsp; Home</a><span>Settings Center</span></div><input id="ktSettingsSearch" class="kt-search" type="search" placeholder="&#xf002;  Search settings..." aria-label="Search settings"></div>
<div class="kt-grid"><nav class="kt-categories" aria-label="Settings categories">
<?php foreach ($settingsModules as $index => $module): ?>
<button type="button" class="kt-category <?php echo $index === 0 ? 'active' : ''; ?>" data-module="<?php echo htmlspecialchars($module['key'], ENT_QUOTES); ?>"><span class="kt-category-icon"><i class="fa <?php echo htmlspecialchars($module['icon'], ENT_QUOTES); ?>"></i></span><span class="kt-category-copy"><strong><?php echo htmlspecialchars($module['title']); ?></strong><small><?php echo htmlspecialchars($module['items'][0]['copy'] ?? 'Configure module preferences.'); ?></small></span><span class="kt-count"><?php echo count($module['items']); ?> Items</span><i class="fa fa-chevron-right"></i></button>
<?php endforeach; ?>
</nav><section class="kt-workspace"><div class="kt-work-header"><div class="kt-work-icon"><i id="ktWorkIcon" class="fa fa-cubes"></i></div><div class="kt-work-title"><h1 id="ktWorkTitle">Inventory Settings</h1><p id="ktWorkSubtitle">Manage configuration and preferences for this module.</p></div><label class="kt-scope">Apply settings to:<select aria-label="Settings scope"><option>Entire Business</option><option>Main Branch</option></select></label></div><div class="kt-subnav"><span id="ktActiveTab">Inventory Configuration</span></div>
<?php foreach ($settingsModules as $index => $module): ?>
<div class="kt-module-content <?php echo $index === 0 ? 'active' : ''; ?>" data-content="<?php echo htmlspecialchars($module['key'], ENT_QUOTES); ?>"><div class="kt-section"><div class="kt-section-heading"><h2><?php echo htmlspecialchars($module['title']); ?> Configuration</h2><p>Select a setting to open its existing configuration page in the workspace below.</p></div><div class="kt-action-list">
<?php foreach ($module['items'] as $item): ?><div class="kt-action-row"><div class="kt-action-icon"><i class="fa fa-sliders"></i></div><div class="kt-action-text">
<?php if (isset($item['id'])): ?><button type="button" class="kt-action-main tablinks" id="<?php echo htmlspecialchars($item['id'], ENT_QUOTES); ?>"><?php echo htmlspecialchars($item['label']); ?></button><?php else: ?><a class="kt-action-main" href="<?php echo htmlspecialchars($item['href'], ENT_QUOTES); ?>"><?php echo htmlspecialchars($item['label']); ?></a><?php endif; ?>
<small><?php echo htmlspecialchars($item['copy']); ?></small></div><i class="fa fa-chevron-right"></i></div><?php endforeach; ?>
</div></div></div><?php endforeach; ?>
<div class="kt-loaded"><div class="kt-loaded-header"><span><i class="fa fa-wrench"></i> &nbsp; Settings Workspace</span><small>Existing settings forms load here</small></div><div id="set"><div class="kt-placeholder">Select a setting above to open its configuration.<br>Your existing settings functionality is preserved.</div></div></div><div class="kt-footer"><span>Powered by <b>K-CORE</b></span><span>K-TOUCH Settings Center</span></div></section></div></main></div></div>
<script>
(function(){
function selectModule(key){var btn=document.querySelector('.kt-category[data-module="'+key+'"]');if(!btn)return;document.querySelectorAll('.kt-category').forEach(function(el){el.classList.toggle('active',el===btn)});document.querySelectorAll('.kt-module-content').forEach(function(el){el.classList.toggle('active',el.getAttribute('data-content')===key)});var title=btn.querySelector('strong').textContent;document.getElementById('ktWorkTitle').textContent=title+' Settings';document.getElementById('ktActiveTab').textContent=title+' Configuration';document.getElementById('ktWorkIcon').className=btn.querySelector('.kt-category-icon i').className;}
document.querySelectorAll('.kt-category').forEach(function(el){el.addEventListener('click',function(){selectModule(el.getAttribute('data-module'))})});
var search=document.getElementById('ktSettingsSearch');search.addEventListener('input',function(){var q=this.value.toLowerCase().trim();document.querySelectorAll('.kt-category').forEach(function(el){var key=el.getAttribute('data-module'),content=document.querySelector('.kt-module-content[data-content="'+key+'"]');el.style.display=(!q||el.textContent.toLowerCase().includes(q)||content.textContent.toLowerCase().includes(q))?'':'none'});});
})();
</script>
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
