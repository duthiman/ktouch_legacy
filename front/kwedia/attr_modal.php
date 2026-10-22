<?php
// Compatibility entry point. The reusable attribute modal lives in front/common.
$attribute_modal_api = 'api/bill_api.php';
$attribute_modal_action = 'add_item';
$attribute_modal_item_id = (int)($_REQUEST['id'] ?? 0);
$attribute_modal_qty = (float)($_REQUEST['qty'] ?? 1);
include dirname(__DIR__) . '/common/attribute_modal.php';
