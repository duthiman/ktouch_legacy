<?php
header('Content-Type: application/json; charset=utf-8');
header('Cache-Control: public, max-age=300');

$template_files = [
  'desktop' => 'desktop.html',
  'mobile' => 'mobile.html',
  'alert' => 'alert.html',
  'desktopTopAction' => 'desktop_top_action.html',
  'desktopQuickAction' => 'desktop_quick_action.html',
  'desktopManagerPanel' => 'desktop_manager_panel.html',
  'mobileTopAction' => 'mobile_top_action.html',
  'mobileQuickAction' => 'mobile_quick_action.html',
  'listItem' => 'list_item.html',
  'listWrapper' => 'list_wrapper.html',
  'saleCap' => 'sale_cap.html',
  'saleCapStat' => 'sale_cap_stat.html',
  'mobileStats' => 'mobile_stats.html',
  'returnRow' => 'return_row.html',
  'modalLoading' => 'modal_loading.html',
  'modalLock' => 'modal_lock.html',
  'loadError' => 'load_error.html',
  'desktopActionBreak' => 'desktop_action_break.html',
  'mergeBadge' => 'merge_badge.html'
];

$templates = [];
foreach ($template_files as $key => $file) {
  $path = __DIR__ . '/templates/' . $file;
  $content = @file_get_contents($path);
  if ($content === false) {
    http_response_code(500);
    echo json_encode(['success' => false, 'message' => 'Could not load dashboard templates.']);
    exit;
  }
  $templates[$key] = $content;
}

echo json_encode($templates);
