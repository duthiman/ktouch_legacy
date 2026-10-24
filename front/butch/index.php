<?php
session_start();
include_once('../../inc/connection.php');
include_once('../../inc/gen_funcs.php');
include_once('../../back/menu/API/menu_schema.php');
include_once('../kwedia/api/funcs.php');
include_once('../kwedia/api/bill_repository.php');
if (function_exists('menu_ensure_item_tables')) { menu_ensure_item_tables(); }
/* Older installations may have kp_items but not the Butchery flag yet. */
$butcheryColumnCheck = mysqli_query($connection, "SHOW COLUMNS FROM kp_items LIKE 'butchery_enabled'");
if ($butcheryColumnCheck && mysqli_num_rows($butcheryColumnCheck) < 1) {
  mysqli_query($connection, "ALTER TABLE kp_items ADD COLUMN butchery_enabled TINYINT(1) NOT NULL DEFAULT 0");
}

$user_id = (int)($_SESSION['user_id'] ?? 0);
$cashier = $user_id > 0 && function_exists('get_user') ? get_user($user_id) : 'Cashier';
$currentShift = function_exists('get_column') ? (string)get_column('kp_shift', 'shift', '1') : date('Y-m-d');
if ($currentShift === '' || $currentShift === '0') { $currentShift = date('Y-m-d'); }

/* Butchery drafts are deliberately separate from normal sales until the payment is completed. */
mysqli_query($connection, "CREATE TABLE IF NOT EXISTS kp_shift_butch_drafts (
  draft_id INT NOT NULL AUTO_INCREMENT,
  item_id INT NOT NULL DEFAULT 0,
  item_name VARCHAR(255) NOT NULL DEFAULT '',
  unit_price DECIMAL(12,2) NOT NULL DEFAULT 0,
  qty DECIMAL(12,3) NOT NULL DEFAULT 0,
  amount DECIMAL(12,2) NOT NULL DEFAULT 0,
  user_id INT NOT NULL DEFAULT 0,
  shift VARCHAR(100) NOT NULL DEFAULT '',
  status VARCHAR(20) NOT NULL DEFAULT 'DRAFT',
  created_at TIMESTAMP NOT NULL DEFAULT CURRENT_TIMESTAMP,
  PRIMARY KEY (draft_id), KEY idx_butch_draft_shift (shift), KEY idx_butch_draft_user (user_id)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4");
mysqli_query($connection, "CREATE TABLE IF NOT EXISTS kp_shift_butch_drafts_payments (
  payment_id INT NOT NULL AUTO_INCREMENT,
  draft_id INT NOT NULL DEFAULT 0,
  pay_mode_id INT NOT NULL DEFAULT 0,
  amount DECIMAL(12,2) NOT NULL DEFAULT 0,
  user_id INT NOT NULL DEFAULT 0,
  created_at TIMESTAMP NOT NULL DEFAULT CURRENT_TIMESTAMP,
  PRIMARY KEY (payment_id), KEY idx_butch_payment_draft (draft_id), KEY idx_butch_payment_mode (pay_mode_id)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4");

/* Create the same finalized sale records used by Kwedia, then queue the customer bill. */
function butch_send_drafts_to_sale($draftIds, $userId, $shift) {
  global $connection;
  $draftIds = array_values(array_filter(array_map('intval', (array)$draftIds)));
  if (!$draftIds) return ['ok'=>false, 'message'=>'No draft items selected.'];
  $idList = implode(',', $draftIds);
  $userFilter = $userId > 0 ? " AND d.user_id='".(int)$userId."'" : '';
  $rows = mysqli_query($connection, "SELECT d.* FROM kp_shift_butch_drafts d WHERE d.draft_id IN ($idList) AND UPPER(COALESCE(d.status,'DRAFT')) NOT IN ('SALES','PRINTED') $userFilter ORDER BY d.draft_id ASC");
  $drafts = [];
  while ($rows && ($row = mysqli_fetch_assoc($rows))) $drafts[] = $row;
  if (!$drafts) return ['ok'=>false, 'message'=>'There are no unsent drafts.'];
  $counter = function_exists('get_column') ? (int)get_column('kp_users','counter',"user_id='".(int)$userId."'") : 0;
  $saleId = function_exists('get_id') ? (int)get_id('kp_sale_id','sale_id') : 0;
  $total = 0;
  foreach ($drafts as $draft) $total += (float)$draft['amount'];
  mysqli_begin_transaction($connection);
  try {
    if (!repo_insert_sales_header(['sale_id'=>$saleId,'user_id'=>$userId,'shift'=>$shift,'amount'=>$total,'counter'=>$counter,'bill_counter'=>$counter])) throw new Exception('Could not create sale header.');
    foreach ($drafts as $draft) {
      $itemId = (int)$draft['item_id'];
      $itemDpt = function_exists('get_column') ? (int)get_column('kp_items','dpt',"item_id='$itemId'") : 0;
      if (!repo_upsert_sale_item(['sale_id'=>$saleId,'item_id'=>$itemId,'user_id'=>$userId,'qty'=>(float)$draft['qty'],'dpt'=>$itemDpt,'price'=>(float)$draft['unit_price'],'total'=>(float)$draft['amount'],'shift'=>$shift,'counter'=>$counter,'hour'=>(int)date('H'),'stock_counter'=>$counter,'session_flag'=>2], false, 'kp_sale_items')) throw new Exception('Could not create sale item.');
      $draftId = (int)$draft['draft_id'];
      mysqli_query($connection, "UPDATE kp_shift_butch_drafts SET status='SALES' WHERE draft_id='$draftId'");
    }
    $payments = mysqli_query($connection, "SELECT p.pay_mode_id, SUM(p.amount) amount FROM kp_shift_butch_drafts_payments p WHERE p.draft_id IN ($idList) GROUP BY p.pay_mode_id");
    $modeValues = [];
    while ($payments && ($payment = mysqli_fetch_assoc($payments))) $modeValues[(int)$payment['pay_mode_id']]=(float)$payment['amount'];
    $columns = ['pay_id','sale_id','user_id','tendered','paid_total','type','counter'];
    $values = [function_exists('pay_id') ? (int)pay_id() : $saleId, $saleId, $userId, $total, $total, 1, $counter];
    $colCheck = mysqli_query($connection, "SHOW COLUMNS FROM kp_sale_payment");
    $available=[]; while ($colCheck && ($c=mysqli_fetch_assoc($colCheck))) $available[$c['Field']]=true;
    foreach ($modeValues as $modeId=>$modeAmount) { $modeCol='mode'.$modeId; if(isset($available[$modeCol])) { $columns[]=$modeCol; $values[]=$modeAmount; } }
    $quoted=[]; foreach($values as $value) $quoted[] = is_numeric($value) ? "'".(float)$value."'" : "'".mysqli_real_escape_string($connection,(string)$value)."'";
    if (!mysqli_query($connection,"INSERT INTO kp_sale_payment (".implode(',', $columns).") VALUES (".implode(',', $quoted).")")) throw new Exception('Could not save payment.');
    $queueId = function_exists('kp_print_enqueue') ? kp_print_enqueue($connection,['sale_id'=>$saleId,'ref_id'=>$saleId,'print_type'=>'bill','label'=>'BUTCHERY BILL','cntr_id'=>$counter,'route_path'=>'','route_query'=>'','requested_by'=>$userId]) : 0;
    if ($queueId < 1) throw new Exception('Could not queue the bill.');
    mysqli_commit($connection);
    return ['ok'=>true,'sale_id'=>$saleId,'queue_id'=>$queueId];
  } catch (Throwable $e) { mysqli_rollback($connection); return ['ok'=>false,'message'=>$e->getMessage()]; }
}

function butch_track_draft_stock($draftId, $itemId, $qty, $userId, $counter, $shift) {
  global $connection;
  foreach ([
    ['trans', "VARCHAR(40) NOT NULL DEFAULT ''"],
    ['trans_id', "INT NOT NULL DEFAULT 0"],
    ['stock_counter', "INT NOT NULL DEFAULT 0"],
    ['shift', "VARCHAR(100) NOT NULL DEFAULT ''"],
    ['dpt', "INT NOT NULL DEFAULT 0"]
  ] as $column) {
    $name = $column[0];
    $check = mysqli_query($connection, "SHOW COLUMNS FROM kp_stock_track LIKE '$name'");
    if ($check && mysqli_num_rows($check) < 1) mysqli_query($connection, "ALTER TABLE kp_stock_track ADD COLUMN `$name` {$column[1]}");
  }
  $dpt = function_exists('get_column') ? (int)get_column('kp_items','dpt',"item_id='".(int)$itemId."'") : 0;
  return mysqli_query($connection, "INSERT INTO kp_stock_track (item_id,qty,user_id,trans,trans_id,status,stock_counter,shift,dpt) VALUES ('".(int)$itemId."','".(float)$qty."','".(int)$userId."','DRAFT','".(int)$draftId."','DRAFT','".(int)$counter."','".mysqli_real_escape_string($connection,(string)$shift)."','".$dpt."')");
}

if (($_POST['action'] ?? '') === 'save_butch_draft') {
  header('Content-Type: application/json; charset=utf-8');
  $itemId = (int)($_POST['item_id'] ?? 0);
  $itemName = trim((string)($_POST['item_name'] ?? ''));
  $unitPrice = (float)($_POST['unit_price'] ?? 0);
  $qty = (float)($_POST['qty'] ?? 0);
  $amountPaid = (float)($_POST['payment_amount'] ?? 0);
  $payModeId = (int)($_POST['pay_mode_id'] ?? 0);
  $isCredit = !empty($_POST['send_to_credit']);
  $draftStatus = $isCredit ? 'CREDIT' : 'DRAFT';
  $payments = json_decode((string)($_POST['payments'] ?? ''), true);
  if (!is_array($payments) || !$payments) { $payments = $isCredit ? [] : [['pay_mode_id'=>$payModeId, 'amount'=>$amountPaid]]; }
  $payments = array_values(array_filter($payments, function($payment) { return (int)($payment['pay_mode_id'] ?? 0) > 0 && (float)($payment['amount'] ?? 0) > 0; }));
  $paymentTotal = 0;
  foreach ($payments as $payment) { $paymentTotal += (float)$payment['amount']; }
  $draftAmount = $isCredit ? ($unitPrice * $qty) : $paymentTotal;
  if ($itemId < 1 || $draftAmount <= 0 || (!$isCredit && (!$paymentTotal || !$payments))) { echo json_encode(['ok'=>false, 'message'=>'Add at least one payment amount.']); exit; }
  mysqli_begin_transaction($connection);
  try {
    $stmt = mysqli_prepare($connection, "INSERT INTO kp_shift_butch_drafts (item_id,item_name,unit_price,qty,amount,user_id,shift,status) VALUES (?,?,?,?,?,?,?,?)");
    mysqli_stmt_bind_param($stmt, 'isdddiss', $itemId, $itemName, $unitPrice, $qty, $draftAmount, $user_id, $currentShift, $draftStatus);
    if (!mysqli_stmt_execute($stmt)) { throw new Exception(mysqli_stmt_error($stmt)); }
    $draftId = mysqli_insert_id($connection);
    mysqli_stmt_close($stmt);
    if (!butch_track_draft_stock($draftId, $itemId, $qty, $user_id, function_exists('get_column') ? (int)get_column('kp_users','counter',"user_id='".(int)$user_id."'") : 0, $currentShift)) { throw new Exception('Could not track draft stock.'); }
    foreach ($payments as $payment) {
      $paymentMode = (int)$payment['pay_mode_id']; $paymentAmount = (float)$payment['amount'];
      $stmt = mysqli_prepare($connection, "INSERT INTO kp_shift_butch_drafts_payments (draft_id,pay_mode_id,amount,user_id) VALUES (?,?,?,?)");
      mysqli_stmt_bind_param($stmt, 'iidi', $draftId, $paymentMode, $paymentAmount, $user_id);
      if (!mysqli_stmt_execute($stmt)) { throw new Exception(mysqli_stmt_error($stmt)); }
      mysqli_stmt_close($stmt);
    }
    mysqli_commit($connection);
    if (!empty($_POST['send_to_credit'])) {
      echo json_encode(['ok'=>true, 'draft_id'=>$draftId, 'message'=>'Butchery item saved as credit.']);
    } elseif (!empty($_POST['send_to_sales'])) {
      $sent = butch_send_drafts_to_sale([$draftId], $user_id, $currentShift);
      echo json_encode($sent);
    } else echo json_encode(['ok'=>true, 'draft_id'=>$draftId, 'message'=>'Butchery draft saved.']);
  } catch (Throwable $e) {
    mysqli_rollback($connection);
    echo json_encode(['ok'=>false, 'message'=>'Could not save the butchery draft.']);
  }
  exit;
}
if (($_POST['action'] ?? '') === 'send_all_butch_drafts') {
  header('Content-Type: application/json; charset=utf-8');
  $shiftEsc = mysqli_real_escape_string($connection, $currentShift);
  $userFilter = $user_id > 0 ? " AND user_id='{$user_id}'" : '';
  $ids=[]; $q=mysqli_query($connection,"SELECT draft_id FROM kp_shift_butch_drafts WHERE shift='{$shiftEsc}' AND UPPER(COALESCE(status,'DRAFT')) NOT IN ('SENT_TO_SALES','PRINTED','SALES','CREDIT'){$userFilter}");
  while($q && ($r=mysqli_fetch_assoc($q))) $ids[]=(int)$r['draft_id'];
  echo json_encode(butch_send_drafts_to_sale($ids,$user_id,$currentShift));
  exit;
}
if (($_POST['action'] ?? '') === 'delete_butch_draft') {
  header('Content-Type: application/json; charset=utf-8');
  $draftId = (int)($_POST['draft_id'] ?? 0);
  $draftQ = mysqli_query($connection, "SELECT status FROM kp_shift_butch_drafts WHERE draft_id='{$draftId}' AND user_id='{$user_id}' AND shift='" . mysqli_real_escape_string($connection, $currentShift) . "' LIMIT 1");
  $draft = $draftQ ? mysqli_fetch_assoc($draftQ) : null;
  if (!$draft || strtoupper((string)$draft['status']) !== 'DRAFT') {
    echo json_encode(['ok' => false, 'message' => 'Only unsent draft items can be deleted.']);
    exit;
  }
  mysqli_begin_transaction($connection);
  try {
    if (!mysqli_query($connection, "DELETE FROM kp_shift_butch_drafts_payments WHERE draft_id='{$draftId}'") || !mysqli_query($connection, "DELETE FROM kp_stock_track WHERE trans='DRAFT' AND trans_id='{$draftId}'") || !mysqli_query($connection, "DELETE FROM kp_shift_butch_drafts WHERE draft_id='{$draftId}' AND user_id='{$user_id}'")) {
      throw new Exception(mysqli_error($connection));
    }
    mysqli_commit($connection);
    echo json_encode(['ok' => true]);
  } catch (Throwable $e) {
    mysqli_rollback($connection);
    echo json_encode(['ok' => false, 'message' => 'Could not delete the draft.']);
  }
  exit;
}
$butchItems = [];
$payModes = [];
$payModesQuery = mysqli_query($connection, "SELECT pay_id, name FROM kp_pay_mode WHERE del_status=2 ORDER BY name ASC");
if ($payModesQuery) { while ($payModeRow = mysqli_fetch_assoc($payModesQuery)) { $payModes[] = ['id'=>(int)$payModeRow['pay_id'], 'name'=>(string)$payModeRow['name']]; } }
$butchQuery = mysqli_query($connection, "SELECT item_id, name, COALESCE(price, 0) AS price FROM kp_items WHERE butchery_enabled=1 ORDER BY name ASC");
if ($butchQuery) { while ($butchRow = mysqli_fetch_assoc($butchQuery)) { $butchItems[] = ['id'=>(int)$butchRow['item_id'], 'name'=>(string)$butchRow['name'], 'price'=>(float)$butchRow['price']]; } }
$draftItems = [];
$draftShiftEsc = mysqli_real_escape_string($connection, $currentShift);
$draftUserFilter = $user_id > 0 ? " AND d.user_id='{$user_id}'" : '';
$draftQuery = mysqli_query($connection, "SELECT d.draft_id,d.item_id,d.item_name,d.unit_price,d.qty,d.amount,d.status,d.created_at FROM kp_shift_butch_drafts d WHERE d.shift='{$draftShiftEsc}' AND (UPPER(COALESCE(d.status,'DRAFT')) IN ('DRAFT','CREDIT','SENT_TO_SALES','UNPRINTED','SALES','PRINTED')){$draftUserFilter} ORDER BY d.draft_id DESC");
if ($draftQuery) {
  while ($draftRow = mysqli_fetch_assoc($draftQuery)) {
    $draftItems[] = ['draft_id'=>(int)$draftRow['draft_id'], 'type'=>'item_'.(int)$draftRow['item_id'], 'name'=>(string)$draftRow['item_name'], 'price'=>(float)$draftRow['unit_price'], 'qty'=>(float)$draftRow['qty'], 'amount'=>(float)$draftRow['amount'], 'status'=>(string)$draftRow['status'], 'print'=>false];
  }
}
?>
<!doctype html>
<html lang="en">
<head>
  <meta charset="utf-8">
  <meta name="viewport" content="width=device-width, initial-scale=1, viewport-fit=cover">
  <title>K-TOUCH | Butchery</title>
  <style>
    :root{--green:#087b45;--green-dark:#064b32;--orange:#ef7f16;--ink:#102139;--line:#dce7e6;--muted:#71808d;--soft:#f3f8f7}
    *{box-sizing:border-box}body{margin:0;background:var(--soft);color:var(--ink);font-family:Inter,system-ui,-apple-system,"Segoe UI",sans-serif}button,input{font:inherit}button{cursor:pointer}button:focus-visible,input:focus-visible{outline:3px solid #f5ad3d;outline-offset:2px}
    .butch-app{width:min(100%,900px);min-height:100dvh;margin:auto;background:#fff;display:flex;flex-direction:column}.butch-header{height:64px;padding:10px 14px;display:flex;align-items:center;justify-content:space-between;border-bottom:1px solid var(--line)}.brand{font-size:26px;font-weight:900;color:var(--orange);letter-spacing:-1px}.brand b{color:var(--green)}.cashier{padding:6px 10px;border-radius:7px;background:#e8f7ef;color:var(--green-dark);font-size:11px;text-align:right}.cashier strong{display:block;font-size:13px}
    .butch-main{display:grid;grid-template-columns:1fr 1fr;gap:12px;padding:12px}.categories{grid-column:1/-1;display:grid;grid-template-columns:repeat(3,1fr);gap:8px}.category,.panel,.calculation,.clear-btn,.keypad button,.action-btn{border-radius:7px}.category{min-height:58px;border:1px solid var(--line);background:#f7faf9;color:var(--ink);font-weight:850}.category.active{background:var(--green);border-color:var(--green);color:#fff;box-shadow:0 3px 8px #087b4522}.panel{border:1px solid var(--line);background:#fff;padding:12px}.field-label{display:block;margin-bottom:7px;font-size:13px;font-weight:800}.amount-box{height:55px;display:flex;align-items:center;padding:0 11px;border:1px solid #cad9df;border-radius:7px;background:#fbfdfd}.amount-box span{font-weight:900;color:var(--muted)}.amount-box input{width:100%;min-width:0;border:0;outline:0;background:transparent;padding:0 9px;font-size:27px;font-weight:900;color:var(--ink)}.reset{border:0;background:transparent;color:#d83232;font-size:24px;line-height:1}
    .calculations{display:grid;grid-template-columns:1fr 1fr;gap:8px;margin:9px 0}.calculation{padding:9px;background:#f8fbfb;border:1px solid var(--line)}.calculation small{display:block;color:var(--muted);font-size:10px}.calculation b{display:block;margin-top:3px;color:var(--green-dark);font-size:15px}
    .keypad{display:grid;grid-template-columns:repeat(3,1fr);gap:7px}.keypad button{height:52px;border:1px solid #d5e1e5;background:#fff;color:var(--ink);font-size:22px;font-weight:850;box-shadow:0 2px 4px #0a26310c}.keypad button:active{background:#e5f5ed;transform:translateY(1px)}.keypad .key-clear{background:#fff0eb;color:#bd3d26}.entry-actions{display:grid;grid-template-columns:1fr 1.25fr;gap:8px;margin-top:11px}.action-btn{min-height:47px;border:1px solid var(--orange);background:#fff8f0;color:#d86b08;font-size:13px;font-weight:900}.action-btn.primary,.pay-btn{border-color:var(--green);background:var(--green);color:#fff}
    .cart-head{display:flex;align-items:center;justify-content:space-between;margin-bottom:7px}.cart-head strong{font-size:17px}.clear-btn{padding:7px 9px;border:1px solid #ffcaca;background:#fff2f2;color:#d83232;font-size:11px;font-weight:800}.cart-list{max-height:250px;overflow:auto}.empty{padding:22px 5px;text-align:center;color:var(--muted);font-size:13px}.cart-item{display:grid;grid-template-columns:1fr auto auto;gap:9px;align-items:center;padding:9px 0;border-bottom:1px solid #edf1f1}.cart-item:last-child{border-bottom:0}.meat-pill{display:inline-block;padding:3px 8px;border-radius:5px;background:#ffe0c8;font-size:11px;font-weight:900}.meat-pill.goat{background:#e8ddff}.meat-pill.chicken{background:#dcf7dc}.item-sub{margin-top:4px;color:var(--muted);font-size:11px}.item-total{text-align:right;font-weight:900;font-size:13px}.remove{border:0;background:transparent;color:#d83232;font-size:20px}.totals{margin-top:10px;padding:11px 13px;border:1px solid #c7ead6;border-radius:7px;background:#e8f7ef}.total-line{display:flex;justify-content:space-between;gap:10px;margin:4px 0;font-size:13px}.total-line b{color:var(--green-dark)}.total-line.grand{font-size:17px;font-weight:900}.pay-btn{width:100%;height:49px;margin-top:10px;border-radius:7px;font-weight:900;font-size:16px}.pay-btn:disabled{opacity:.45;cursor:not-allowed}.butch-footer{padding:10px 0 18px;text-align:center;color:#89959d;font-size:10px}.butch-footer b{color:var(--green)}
    @media(max-width:619px){.butch-main{grid-template-columns:1fr;padding:5px;gap:5px}.categories{display:flex;flex-wrap:nowrap;gap:4px;overflow-x:auto;overflow-y:hidden;padding:2px 1px 5px;scroll-snap-type:x proximity;scrollbar-width:thin}.categories::-webkit-scrollbar{height:4px}.categories::-webkit-scrollbar-thumb{background:#d18a62;border-radius:4px}.category{flex:0 0 calc(25% - 3px);min-width:0;min-height:39px;padding:4px 3px;font-size:10px;line-height:12px;white-space:normal;scroll-snap-align:start}.panel{padding:7px}.field-label{margin-bottom:3px;font-size:11px}.amount-box{height:46px}.amount-box input{font-size:22px}.amount-main-btn{width:40px;flex-basis:40px}.entry-actions{gap:5px;margin-top:6px}.action-btn{min-height:36px;font-size:11px}.calculations{gap:4px;margin:5px 0}.calculation{padding:5px}.calculation small{font-size:8px}.calculation b{font-size:12px}.keypad{gap:6px}.keypad button{height:48px;font-size:19px}.cart-head{margin-bottom:3px}.cart-head strong{font-size:14px}.cart-list{max-height:285px}.cart-item{padding:5px 0;gap:6px}.meat-pill{padding:2px 6px;font-size:10px}.item-sub{font-size:9px;margin-top:2px}.item-total{font-size:11px}.remove{font-size:17px}.totals{margin-top:5px;padding:6px 9px}.total-line{margin:2px 0;font-size:11px}.total-line.grand{font-size:14px}.pay-btn{height:38px;margin-top:6px;font-size:13px}}
    @media(max-width:360px){.butch-header{height:58px}.brand{font-size:23px}.cashier{font-size:10px}.cashier strong{font-size:12px}.category{font-size:11px}.keypad button{height:44px}.amount-box input{font-size:24px}}
    .payment-overlay{position:fixed;inset:0;z-index:20;display:none;align-items:center;justify-content:center;padding:14px;background:#061c2dcc;backdrop-filter:blur(7px)}.payment-overlay.show{display:flex}.payment-modal{width:min(100%,760px);max-height:94dvh;overflow:auto;border-radius:14px;background:#fff;box-shadow:0 25px 80px #001a2a88}.payment-head{display:flex;align-items:center;justify-content:space-between;padding:18px 22px;color:#fff;background:linear-gradient(135deg,#087b45,#075d39)}.payment-head h2{margin:0;font-size:22px}.payment-head p{margin:3px 0 0;font-size:12px;opacity:.9}.payment-close{border:0;background:transparent;color:#fff;font-size:30px;line-height:1}.payment-body{padding:16px}.payment-summary{display:grid;grid-template-columns:1fr 1fr 1fr;gap:8px;padding:12px;border:1px solid #cbe9d8;border-radius:9px;background:#effaf3}.payment-summary small{display:block;color:var(--muted);font-size:10px;font-weight:800}.payment-summary b{display:block;margin-top:3px;color:var(--green-dark);font-size:18px}.payment-summary .grand b{font-size:22px}.payment-grid{display:grid;grid-template-columns:1fr 1fr;gap:12px;margin-top:14px}.pay-option{min-height:54px;border:1px solid var(--line);border-radius:7px;background:#fff;font-weight:900;color:var(--ink)}.pay-option.active{border:2px solid var(--green);background:#ecf9f1;color:var(--green)}.pay-fields{margin-top:10px;padding:12px;border-radius:9px;background:#f2fbf6;border:1px solid #d4eee0}.pay-fields.cash{background:#fff8ed;border-color:#f6dfb8}.pay-label{display:block;margin-bottom:6px;font-size:12px;font-weight:850}.pay-input{width:100%;height:48px;border:1px solid #ccd9df;border-radius:7px;padding:0 12px;font-size:22px;font-weight:900;color:var(--ink);background:#fff}.payment-keypad{display:grid;grid-template-columns:repeat(3,1fr);gap:6px;margin-top:10px}.payment-keypad button{height:43px;border:1px solid #d7e3e7;border-radius:7px;background:#fff;font-size:19px;font-weight:900}.payment-keypad button:active{background:#e5f5ed}.payment-total{display:flex;justify-content:space-between;margin-top:14px;padding:12px;border-top:1px solid var(--line);font-weight:900}.payment-actions{display:grid;grid-template-columns:1fr 1.4fr;gap:9px;margin-top:10px}.payment-actions button{height:50px;border-radius:7px;font-weight:900}.payment-cancel{border:1px solid #efb1a8;background:#fff;color:#d6543e}.payment-confirm{border:1px solid var(--green);background:var(--green);color:#fff}@media(max-width:560px){.payment-modal{width:100%;border-radius:10px}.payment-head{padding:14px 16px}.payment-body{padding:11px}.payment-summary{grid-template-columns:1fr 1fr}.payment-summary .grand{grid-column:1/-1}.payment-grid{grid-template-columns:1fr}.payment-keypad button{height:40px}}
  </style>
</head>
<body>
<div class="butch-app">
  <header class="butch-header"><div class="brand"><b>K-</b>touch<span class="brand-divider">|</span><strong class="butchery-heading">BUTCHERY</strong><small>MEAT SALES</small></div><a class="butch-home" href="../dash/" aria-label="Go to home dashboard"><span>⌂</span><strong>HOME</strong></a></header>
  <main class="butch-main">
    <nav class="categories" aria-label="Butchery items"><?php foreach ($butchItems as $i => $butchItem) { ?><button class="category<?php echo $i === 0 ? ' active' : ''; ?>" data-type="item_<?php echo (int)$butchItem['id']; ?>"><?php echo htmlspecialchars($butchItem['name']); ?></button><?php } ?></nav>
    <section class="panel">
      <label class="field-label" for="amount">Amount (KES)</label>
      <div class="amount-box"><span>KES</span><input id="amount" value="" readonly inputmode="none" placeholder="0.00"><button class="amount-main-btn amount-main-back" id="amountBackspace" type="button" aria-label="Remove last digit">⌫</button></div>
      <div class="main-quick-amounts" aria-label="Quick amounts"><button type="button" data-main-quick="5">5</button><button type="button" data-main-quick="10">10</button><button type="button" data-main-quick="50">50</button><button type="button" data-main-quick="100">100</button><button type="button" data-main-quick="500">500</button><button type="button" data-main-quick="1000">1,000</button></div>
      <div class="calculations"><div class="calculation"><small>PRICE / KG</small><b id="price">KES 700.00</b></div><div class="calculation"><small>QUANTITY (AUTO)</small><b id="qty">1.000 Kg</b></div></div>
      <div class="keypad" id="keypad"><button data-key="1">1</button><button data-key="2">2</button><button data-key="3">3</button><button data-key="4">4</button><button data-key="5">5</button><button data-key="6">6</button><button data-key="7">7</button><button data-key="8">8</button><button data-key="9">9</button><button data-key=".">.</button><button data-key="0">0</button><button class="key-clear" data-key="clear">CLEAR</button></div>
      <div class="entry-actions"><button class="action-btn" id="add">＋ ADD</button><button class="action-btn primary" id="addPrint">▣ ADD &amp; PRINT</button></div>
    </section>
    <section class="panel"><div class="cart-head"><strong>🛒 Added Items <span id="count">(0)</span></strong></div><div class="cart-list"><table class="cart-table"><thead><tr><th>ITEM</th><th>QTY</th><th>AMOUNT</th><th>STATUS</th></tr></thead><tbody id="items"><tr><td colspan="4" class="empty">No items added yet</td></tr></tbody></table></div><div class="totals"><div class="total-line"><span>Total Weight</span><b id="weight">0.000 Kg</b></div><div class="total-line grand"><span>Total Amount</span><b id="total">KES 0.00</b></div></div><button class="pay-btn" id="pay" disabled>⇧ SEND TO SALES</button></section>
  </main>
  <footer class="butch-footer">Powered by <b>K-CORE</b> · K-TEN Solutions Ltd</footer>
</div>
<div class="payment-overlay" id="paymentOverlay" role="dialog" aria-modal="true" aria-labelledby="paymentTitle"><div class="payment-modal"><header class="payment-head"><div><h2 id="paymentTitle">Payment <span id="payHeaderItem">BEEF</span></h2><p>Collect payment for this butchery item</p></div><button class="payment-close" id="paymentClose">×</button></header><div class="payment-body"><div class="payment-summary"><div><small>WEIGHT</small><b id="payWeight">0.000 Kg</b></div><div class="grand"><small>AMOUNT</small><b id="payTotal">KES 0.00</b></div><div><small>TENDERED</small><b id="paySummaryPaid">KES 0.00</b></div><div><small>CHANGE</small><b id="paySummaryChange">KES 0.00</b></div></div><div class="payment-grid"><?php foreach ($payModes as $payModeIndex => $payMode) { ?><button class="pay-option<?php echo $payModeIndex === 0 ? ' active' : ''; ?>" data-payment-id="<?php echo (int)$payMode['id']; ?>"><span><?php echo htmlspecialchars($payMode['name']); ?></span><b class="mode-amount">KES 0.00</b></button><?php } ?></div><div class="quick-amounts"><button type="button" data-quick="10">10</button><button type="button" data-quick="50">50</button><button type="button" data-quick="100">100</button><button type="button" data-quick="500">500</button><button type="button" data-quick="1000">1,000</button></div><div class="payment-keypad" id="paymentKeypad"><button data-paykey="1">1</button><button data-paykey="2">2</button><button data-paykey="3">3</button><button data-paykey="4">4</button><button data-paykey="5">5</button><button data-paykey="6">6</button><button data-paykey="7">7</button><button data-paykey="8">8</button><button data-paykey="9">9</button><button data-paykey="clear">CLEAR</button><button data-paykey="0">0</button><button data-paykey="back">⌫</button></div><div class="payment-breakdown" id="paymentBreakdown"></div><div class="payment-actions"><button class="payment-cancel" id="paymentCancel">CANCEL</button><button class="payment-confirm" id="paymentConfirm">✓ &nbsp; CONFIRM PAYMENT</button></div></div></div></div>
<div class="send-overlay" id="sendOverlay" role="dialog" aria-modal="true"><div class="send-modal"><header><div><h2>Send to Sales</h2><p>Review unprinted butchery items before sending</p></div><button id="sendClose" type="button">×</button></header><div class="send-body"><div class="send-summary"><div><small>ITEMS</small><b id="sendCount">0</b></div><div><small>TOTAL WEIGHT</small><b id="sendWeight">0.000 Kg</b></div><div><small>TOTAL AMOUNT</small><b id="sendTotal">KES 0.00</b></div></div><div class="send-list" id="sendList"></div><div class="send-actions"><button id="sendCancel" type="button">CANCEL</button><button id="sendConfirm" type="button">✓ CONFIRM &amp; SEND</button></div></div></div></div>
<script>
(function(){
  'use strict';
  var products=<?php echo json_encode(array_reduce($butchItems,function($carry,$item){$carry['item_'.$item['id']]=['name'=>$item['name'],'price'=>$item['price']];return $carry;},[]), JSON_UNESCAPED_UNICODE|JSON_UNESCAPED_SLASHES); ?>,selected=<?php echo json_encode(!empty($butchItems) ? 'item_'.$butchItems[0]['id'] : ''); ?>,amount='',cart=<?php echo json_encode($draftItems, JSON_UNESCAPED_UNICODE|JSON_UNESCAPED_SLASHES); ?>;
  var $=function(id){return document.getElementById(id)},money=function(n){return 'KES '+Number(n||0).toLocaleString('en-KE',{minimumFractionDigits:2,maximumFractionDigits:2})};
  function update(){var p=products[selected],value=Number(amount)||0;$('amount').value=amount;$('price').textContent=money(p.price);$('qty').textContent=(value/p.price).toFixed(3)+' Kg'}
  function render(){var total=0,weight=0,i,html='',removeButton,status,statusLabel;for(i=0;i<cart.length;i++){total+=cart[i].amount;weight+=cart[i].qty;status=String(cart[i].status||'DRAFT').toUpperCase();removeButton=status==='DRAFT'?'<button class="remove" data-remove="'+i+'" aria-label="Remove item" title="Remove draft">🗑</button>':status==='SENT_TO_SALES'?'<span class="sale-sent" title="Bill sent to sales">✓✓ SENT</span>':'<span class="sale-locked" title="Printed sale cannot be deleted">🔒</span>';statusLabel='<span class="item-status">'+(status==='DRAFT'?'DRAFT':status==='SENT_TO_SALES'?'SENT TO SALES':'PRINTED')+'</span>';html+='<tr><td class="cart-item-name"><span class="meat-pill '+cart[i].type+'">'+cart[i].name+'</span></td><td class="cart-item-qty">'+cart[i].qty.toFixed(3)+' Kg</td><td class="item-total">'+money(cart[i].amount)+'</td><td class="cart-item-status">'+statusLabel+removeButton+'</td></tr>'}$('count').textContent='('+cart.length+')';$('items').innerHTML=html||'<tr><td colspan="4" class="empty">No items added yet</td></tr>';$('weight').textContent=weight.toFixed(3)+' Kg';$('total').textContent=money(total);$('pay').disabled=!cart.length}
  var pending=null,paymentModeId=<?php echo json_encode(!empty($payModes) ? (int)$payModes[0]['id'] : 0); ?>,paymentEntry='',paymentParts={},savingDraft=false;
  function renderPaymentParts(){var total=0,html='';document.querySelectorAll('.pay-option').forEach(function(button){var id=button.dataset.paymentId,part=paymentParts[id],value=part?part.amount:0;button.querySelector('.mode-amount').textContent=money(value);if(value>0){total+=value;html+='<div><span>'+part.name+'</span><b>'+money(value)+'</b></div>'}});$('paymentBreakdown').innerHTML=html||'<small>No payment added yet.</small>';$('paySummaryPaid').textContent=money(total);var change=total-(pending?pending.amount:0);$('paySummaryChange').textContent=money(Math.abs(change));$('paySummaryChange').classList.toggle('due',change<0)}
  function openPayment(printAfter){var value=Number(amount),p=products[selected];if(!Number.isFinite(value)||value<=0){alert('Enter an amount first.');return}pending={type:selected,name:p.name,price:p.price,amount:value,qty:value/p.price,print:!!printAfter};paymentParts={};$('payHeaderItem').textContent='· '+p.name;$('payWeight').textContent=(value/p.price).toFixed(3)+' Kg';$('payTotal').textContent=money(value);paymentEntry='';renderPaymentParts();$('paymentOverlay').classList.add('show')}
  function addPending(){if(!pending)return;var draft=pending;var form=new FormData();form.append('action','save_butch_draft');form.append('item_id',draft.type.replace('item_',''));form.append('item_name',draft.name);form.append('unit_price',draft.price);form.append('qty',draft.qty);form.append('payment_amount',Object.keys(paymentParts).reduce(function(sum,id){return sum+paymentParts[id].amount},0));form.append('pay_mode_id',paymentModeId);form.append('send_to_sales',draft.print?'1':'0');form.append('payments',JSON.stringify(Object.keys(paymentParts).map(function(id){return {pay_mode_id:Number(id),amount:paymentParts[id].amount}})));$('paymentConfirm').disabled=true;fetch(window.location.href,{method:'POST',body:form}).then(function(r){return r.json()}).then(function(res){if(!res.ok){alert(res.message||'Could not save the draft.');return}window.location.reload()}).catch(function(){alert('Could not save the butchery draft.');}).finally(function(){$('paymentConfirm').disabled=false})}
  var creditButton=document.createElement('button');creditButton.type='button';creditButton.id='paymentCredit';creditButton.className='payment-cancel';creditButton.textContent='SEND AS CREDIT';var paymentActions=document.querySelector('.payment-actions');paymentActions.appendChild(creditButton);paymentActions.style.gridTemplateColumns='1fr 1.4fr';creditButton.style.gridColumn='1 / -1';creditButton.style.background='#f39c12';creditButton.style.borderColor='#d68910';creditButton.style.color='#fff';$('paymentCancel').style.background='#c0392b';$('paymentCancel').style.borderColor='#a93226';$('paymentCancel').style.color='#fff';$('paymentConfirm').style.background='#198754';$('paymentConfirm').style.borderColor='#146c43';$('paymentConfirm').style.color='#fff';
  creditButton.onclick=function(){if(!pending||savingDraft)return;savingDraft=true;var form=new FormData();form.append('action','save_butch_draft');form.append('item_id',pending.type.replace('item_',''));form.append('item_name',pending.name);form.append('unit_price',pending.price);form.append('qty',pending.qty);form.append('payment_amount','0');form.append('pay_mode_id','0');form.append('send_to_credit','1');form.append('payments','[]');creditButton.disabled=true;fetch(window.location.href,{method:'POST',body:form}).then(function(r){return r.json()}).then(function(res){if(!res.ok){alert(res.message||'Could not save credit draft.');savingDraft=false;creditButton.disabled=false;return}window.location.reload()}).catch(function(){alert('Could not save credit draft.');savingDraft=false;creditButton.disabled=false})};
  document.querySelectorAll('.category').forEach(function(b){b.onclick=function(){selected=b.dataset.type;document.querySelectorAll('.category').forEach(function(x){x.classList.toggle('active',x===b)});update()}});
  $('keypad').onclick=function(e){var key=e.target.closest('button')&&e.target.closest('button').dataset.key;if(!key)return;if(key==='clear')amount='';else if(key==='.'&&!amount.includes('.'))amount=amount?amount+'.':'0.';else if(key!=='.'&&amount.replace('.','').length<9)amount=amount==='0'?key:amount+key;update()};
  document.querySelectorAll('[data-main-quick]').forEach(function(button){button.onclick=function(){amount=String((Number(amount)||0)+Number(button.dataset.mainQuick));update()}});
  $('amountBackspace').onclick=function(){amount=amount.slice(0,-1);update()};$('items').onclick=function(e){var b=e.target.closest('[data-remove]');if(b){var index=Number(b.dataset.remove),draft=cart[index];if(!draft)return;if(!confirm('Delete this added sale?'))return;b.disabled=true;var form=new FormData();form.append('action','delete_butch_draft');form.append('draft_id',draft.draft_id);fetch(window.location.href,{method:'POST',body:form}).then(function(r){return r.json()}).then(function(res){if(!res.ok){alert(res.message||'Could not delete the draft.');b.disabled=false;return}cart.splice(index,1);render()}).catch(function(){alert('Could not delete the draft.');b.disabled=false})}};document.addEventListener('keydown',function(e){if(/^[0-9.]$/.test(e.key)){var b=document.querySelector('[data-key="'+e.key+'"]');if(b)b.click()}else if(e.key==='Backspace')$('amountBackspace').click()});update();render();setInterval(function(){window.location.reload()},180000);
  $('add').onclick=function(){openPayment(false)};$('addPrint').onclick=function(){openPayment(true)};
  function isUnprinted(item){var status=String(item.status||'DRAFT').toUpperCase();return status!=='SENT_TO_SALES'&&status!=='PRINTED'&&status!=='SALES'}
  function openSendModal(){var rows=cart.filter(isUnprinted),total=0,weight=0,html='';rows.forEach(function(item){total+=item.amount;weight+=item.qty;html+='<div class="send-row"><span>'+item.name+'</span><b>'+item.qty.toFixed(3)+' Kg</b><strong>'+money(item.amount)+'</strong></div>'});if(!rows.length){alert('There are no unprinted drafts to send.');return}$('sendCount').textContent=rows.length;$('sendWeight').textContent=weight.toFixed(3)+' Kg';$('sendTotal').textContent=money(total);$('sendList').innerHTML=html;$('sendOverlay').classList.add('show')}
  function closeSendModal(){$('sendOverlay').classList.remove('show')}
  $('pay').onclick=openSendModal;$('sendClose').onclick=closeSendModal;$('sendCancel').onclick=closeSendModal;$('sendConfirm').onclick=function(){var form=new FormData();form.append('action','send_all_butch_drafts');$('sendConfirm').disabled=true;fetch(window.location.href,{method:'POST',body:form}).then(function(r){return r.json()}).then(function(res){if(!res.ok){alert(res.message||'Could not send drafts to sales.');$('sendConfirm').disabled=false;return}window.location.reload()}).catch(function(){alert('Could not send drafts to sales.');$('sendConfirm').disabled=false})};
  document.querySelectorAll('.pay-option').forEach(function(b){b.onclick=function(){paymentModeId=Number(b.dataset.paymentId)||0;paymentEntry=paymentParts[paymentModeId]?String(paymentParts[paymentModeId].amount):'';document.querySelectorAll('.pay-option').forEach(function(x){x.classList.toggle('active',x===b)});renderPaymentParts()}});
  function updatePaymentEntry(){var button=document.querySelector('.pay-option[data-payment-id="'+paymentModeId+'"]'),name=button?button.querySelector('span').textContent.trim():'Payment';if(paymentEntry){paymentParts[paymentModeId]={name:name,amount:Number(paymentEntry)||0}}else{delete paymentParts[paymentModeId]}renderPaymentParts()}
  $('paymentKeypad').onclick=function(e){var key=e.target.closest('button')&&e.target.closest('button').dataset.paykey;if(!key)return;if(key==='clear')paymentEntry='';else if(key==='back')paymentEntry=paymentEntry.slice(0,-1);else if(paymentEntry.length<9)paymentEntry+=key;updatePaymentEntry()};
  document.querySelectorAll('[data-quick]').forEach(function(button){button.onclick=function(){paymentEntry=String((Number(paymentEntry)||0)+Number(button.dataset.quick));updatePaymentEntry()}});
  function closePayment(){pending=null;$('paymentOverlay').classList.remove('show')}$('paymentClose').onclick=closePayment;$('paymentCancel').onclick=closePayment;$('paymentConfirm').onclick=function(){if(savingDraft)return;var total=Object.keys(paymentParts).reduce(function(sum,id){return sum+paymentParts[id].amount},0);if(!pending||!total||total<pending.amount){alert('Payment is less than the total amount.');return}savingDraft=true;addPending()};
  document.addEventListener('keydown',function(e){if(e.key==='Escape')closePayment()});
}());
</script>
</body>
<style>
  #paymentOverlay .payment-modal{overflow-y:auto}
  #paymentOverlay .payment-body{overflow-y:auto}
  #paymentOverlay .payment-actions{padding-bottom:12px}
  .pay-option:before{display:none!important}.payment-head h2 span{font-size:.7em;font-weight:700;color:#ffd5ae}.payment-summary{grid-template-columns:repeat(2,minmax(0,1fr))!important;gap:0 14px}.payment-summary>div{grid-column:auto!important;grid-row:auto!important;padding:10px 0!important;border-top:0!important}.payment-summary>div:nth-child(3),.payment-summary>div:nth-child(4){border-top:1px solid #efd5bd!important}.payment-summary>div:nth-child(2),.payment-summary>div:nth-child(4){text-align:right}.payment-summary>div:nth-child(4) b{color:#087b45}.payment-summary>div:nth-child(4) b.due{color:#bd392d}
  .send-overlay{position:fixed;inset:0;z-index:30;display:none;align-items:center;justify-content:center;padding:14px;background:#24100dcc;backdrop-filter:blur(6px)}.send-overlay.show{display:flex}.send-modal{width:min(100%,620px);max-height:92dvh;overflow:hidden;border-radius:14px;background:#fffaf5;box-shadow:0 25px 80px #25100866}.send-modal>header{display:flex;align-items:center;justify-content:space-between;padding:17px 20px;color:#fff;background:linear-gradient(135deg,#7b251b,#b94727)}.send-modal h2{margin:0;font-size:21px}.send-modal header p{margin:3px 0 0;font-size:11px;opacity:.9}.send-modal header button{border:0;background:#ffffff22;color:#fff;border-radius:50%;width:34px;height:34px;font-size:24px}.send-body{padding:14px}.send-summary{display:grid;grid-template-columns:repeat(3,1fr);gap:7px;padding:10px;border:1px solid #f0d2b0;border-radius:10px;background:#fff1e3}.send-summary small{display:block;color:#956d5b;font-size:9px;font-weight:900}.send-summary b{display:block;margin-top:3px;color:#8c301c;font-size:17px}.send-list{max-height:330px;overflow:auto;margin-top:11px;border-top:1px solid #ead7c9}.send-row{display:grid;grid-template-columns:1fr 100px 120px;gap:8px;align-items:center;padding:11px 4px;border-bottom:1px solid #f0e1d6;color:#58251c;font-size:13px}.send-row b{text-align:right;color:#24777b}.send-row strong{text-align:right;color:#8c301c}.send-actions{display:grid;grid-template-columns:1fr 1.4fr;gap:8px;margin-top:12px}.send-actions button{height:46px;border-radius:8px;font-weight:900}.send-actions button:first-child{border:1px solid #dfb6a5;background:#fff;color:#a94b32}.send-actions button:last-child{border:1px solid #7d2819;background:#9e351f;color:#fff}@media(max-width:560px){.send-overlay{padding:0}.send-modal{width:100%;height:100dvh;max-height:none;border-radius:0}.send-body{padding:8px}.send-list{max-height:none;flex:1}.send-modal .send-body{display:flex;flex-direction:column;height:calc(100dvh - 73px)}.send-row{grid-template-columns:1fr 75px 90px;padding:9px 2px;font-size:12px}.send-summary b{font-size:14px}.send-actions{margin-top:8px}.send-actions button{height:42px}}
  .payment-keypad:before{display:none!important}.payment-body{padding:3px}.payment-summary{padding:3px!important}.payment-grid{padding:3px 2px 4px}.quick-amounts{margin-top:3px;padding:3px}.payment-keypad{gap:3px;margin-top:3px}.payment-breakdown{margin-top:3px;padding:3px 6px}.payment-actions{margin-top:3px}
  .payment-overlay{padding:0}.payment-modal{width:100vw;height:100dvh;max-height:none;border-radius:0;overflow:hidden;display:flex;flex-direction:column}.payment-head{flex:0 0 auto;padding:10px 14px}.payment-body{flex:1;min-height:0;overflow:hidden;display:flex;flex-direction:column}.payment-summary{flex:0 0 auto}.payment-grid{flex:0 0 auto}.quick-amounts{flex:0 0 auto}.payment-keypad{flex:0 0 auto}.payment-breakdown{flex:0 0 auto;max-height:44px;overflow:hidden}.payment-actions{flex:0 0 auto}.payment-actions button{height:42px}
  @media(max-width:560px){.payment-head{padding:8px 10px}.payment-head h2{font-size:18px}.payment-head p{margin:1px 0;font-size:10px}.payment-summary small{font-size:8px}.payment-summary b{font-size:11px}.payment-summary .grand b{font-size:14px}.pay-option{min-height:45px;padding:5px;font-size:11px}.pay-option .mode-amount{font-size:12px}.quick-amounts button{min-height:34px;font-size:12px}.payment-keypad button{height:35px;font-size:15px}.payment-breakdown{font-size:10px}.payment-actions button{height:38px;font-size:12px}}
.cart-columns,.cart-item{display:grid;grid-template-columns:minmax(0,1.5fr) minmax(72px,.8fr) minmax(92px,1fr) 42px;align-items:center;gap:6px}.cart-columns{padding:0 0 5px;border-bottom:1px solid #ead7c9;color:#956d5b;font-size:10px;font-weight:900;letter-spacing:.04em}.cart-columns span:nth-child(2),.cart-columns span:nth-child(3),.cart-item-qty,.item-total{text-align:right}.cart-item{align-items:stretch}.cart-item-name,.cart-item-qty,.item-total{font-size:14px;align-self:center}.cart-item-qty{color:#24777b;font-weight:800}.item-total{color:#8c301c}.cart-item .remove{width:42px;height:auto;min-height:42px;align-self:stretch;padding:0;border:1px solid #e5a39a;border-radius:8px;background:#fff0ed;color:#c0392b;font-size:23px;line-height:1;text-align:center}.cart-item .remove:hover{background:#ffd9d4;color:#98261d}@media(max-width:619px){.cart-columns,.cart-item{grid-template-columns:minmax(0,1.35fr) 65px minmax(78px,1fr) 38px;gap:4px}.cart-columns{font-size:8px}.cart-item{padding:7px 0}.cart-item-name,.cart-item-qty,.item-total{font-size:12px}.cart-item .remove{width:38px;min-height:38px;font-size:21px}}
.sale-locked{display:flex;align-items:center;justify-content:center;min-height:42px;color:#8b9aa0;font-size:18px}
.sale-sent{display:flex;align-items:center;justify-content:center;min-height:42px;color:#087b45;font-size:17px;font-weight:900;letter-spacing:-3px}
.cart-columns{border:1px solid #ead7c9;border-radius:5px 5px 0 0;padding:9px 8px 7px;background:#fff4e8;color:#7e3a20}.cart-list{border:1px solid #ead7c9;border-top:0;border-radius:0 0 5px 5px;background:#fff;overflow:auto}.cart-item{min-height:48px;padding:5px 8px;border-bottom:1px solid #f0e5dc;background:#fff}.cart-item:last-child{border-bottom:0}.cart-item:hover{background:#fffaf5}.meat-pill{border-radius:5px;background:#fff0df;color:#7e3a20}.cart-item .remove{border-radius:5px}.sale-sent{border-radius:5px;background:#e8f7ef}.empty{padding:20px 8px;color:#927c70}
.cart-list{border:1px solid #ead7c9;border-radius:5px;overflow:auto}.cart-table{width:100%;border-collapse:separate;border-spacing:0;table-layout:fixed}.cart-table th{padding:9px 8px;background:#fff4e8;border-bottom:1px solid #ead7c9;color:#7e3a20;font-size:10px;text-align:left;letter-spacing:.04em}.cart-table th:nth-child(2),.cart-table th:nth-child(3),.cart-table th:nth-child(4),.cart-table td:nth-child(2),.cart-table td:nth-child(3),.cart-table td:nth-child(4){text-align:right}.cart-table td{padding:7px 8px;border-bottom:1px solid #f0e5dc;background:#fff;vertical-align:middle;font-size:13px}.cart-table tr:last-child td{border-bottom:0}.cart-table tr:hover td{background:#fffaf5}.cart-item-status{white-space:nowrap}.item-status{display:inline-block;margin-right:5px;color:#92776b;font-size:9px;font-weight:900}.cart-table .remove{width:32px;min-height:32px;margin-left:3px;border-radius:5px}.cart-table .sale-sent{display:inline-block;min-height:0;padding:4px 5px;margin-left:3px;font-size:9px;letter-spacing:0}.cart-table .sale-locked{display:inline-block;min-height:0;padding:4px}.cart-table .empty{text-align:center!important;color:#927c70;font-size:12px}
</style>
</html>
<style>
  .add-payment{display:block;width:100%;margin-top:8px;padding:10px;border:1px solid #b9e4ca;border-radius:7px;background:#e8f7ef;color:#087b45;font-weight:900}
  .payment-breakdown{margin-top:10px;padding:9px 11px;border:1px solid #dce7e6;border-radius:7px;background:#f8fbfb;font-size:12px;color:#52666d}
  .payment-breakdown div{display:flex;justify-content:space-between;padding:3px 0}.payment-breakdown b{color:#087b45}.payment-breakdown small{color:#89959d}
  /* Butchery payment modal: simple, warm and touch-friendly */
  .payment-modal{border:1px solid #eadccf;background:#fffaf5;box-shadow:0 25px 80px rgba(45,24,12,.35)}
  .payment-head{padding:20px 22px;background:linear-gradient(135deg,#7b251b,#b94727 58%,#e47b27);position:relative;overflow:hidden}
  .payment-head:after{content:'🥩';position:absolute;right:62px;bottom:-18px;font-size:76px;opacity:.16;transform:rotate(-12deg)}
  .payment-head h2{font-size:24px;letter-spacing:.01em}.payment-head p{font-size:12px}.payment-close{position:relative;z-index:1;width:38px;height:38px;border-radius:50%;background:#ffffff22;font-size:27px}
  .payment-body{padding:18px}.payment-summary{border:1px solid #f0d7bd;background:#fff3e4;border-radius:12px;padding:13px;box-shadow:0 3px 0 #edc7a555}
  .payment-summary small{color:#9a715f;letter-spacing:.06em}.payment-summary b{color:#51261b}.payment-summary .grand b{color:#a6331e}
  .payment-grid{grid-template-columns:repeat(auto-fit,minmax(130px,1fr));gap:9px;margin-top:16px}.payment-grid:before{content:'1. Choose payment mode';grid-column:1/-1;color:#74331f;font-weight:900;font-size:13px}
  .pay-option{min-height:58px;border:2px solid #eadfd6;border-radius:10px;background:#fff;color:#5b4239;font-size:14px;box-shadow:0 2px 5px #5424140d}.pay-option:before{content:'▣';display:block;margin-bottom:3px;color:#d76724;font-size:17px}.pay-option.active{border-color:#c64b27;background:#fff0e3;color:#9b321d;box-shadow:0 0 0 3px #f4c9a366}
  .pay-fields{margin-top:16px;padding:14px;border:1px solid #f0d2b0;border-radius:11px;background:#fff6eb}.pay-label:before{content:'2. ';color:#bd4a24}.pay-label{color:#74331f;font-size:13px}.pay-input{height:58px;border:2px solid #e6cdbb;border-radius:10px;background:#fff;font-size:28px;text-align:right;color:#54261b}.pay-input:focus{border-color:#d15a2c;box-shadow:0 0 0 3px #f4c9a366}
  .add-payment{padding:12px;border-color:#dfb38f;background:#fff;color:#b44722;font-size:13px}.add-payment:hover{background:#fff0e3}
  .payment-keypad{gap:8px;margin-top:12px}.payment-keypad:before{content:'3. Enter amount';grid-column:1/-1;color:#74331f;font-weight:900;font-size:13px}.payment-keypad button{height:49px;border-color:#eadfd6;border-radius:9px;background:#fff;color:#51261b;font-size:20px;box-shadow:0 2px 4px #5424140d}.payment-keypad button[data-paykey="clear"]{background:#fff0e7;color:#b43625;font-size:13px}.payment-keypad button:active{background:#ffe7d2}
  .payment-breakdown{border-color:#ead1bd;background:#fff;color:#65483d;border-radius:9px}.payment-breakdown:before{content:'Added payments';display:block;margin-bottom:4px;color:#74331f;font-weight:900}.payment-breakdown b{color:#a43c20}.payment-total{margin-top:15px;padding:14px;border-top:1px solid #e9cdb9;color:#74331f;font-size:14px}.payment-total span:last-child{color:#a6331e;font-size:20px}
  .pay-option .mode-amount{display:block;margin-top:4px;color:#a43c20;font-size:15px}.pay-option.active .mode-amount{color:#8b2f1d}.quick-amounts{display:flex;gap:6px;margin-top:9px}.quick-amounts button{flex:1;min-height:36px;border:1px solid #e0b58f;border-radius:7px;background:#fff;color:#a94723;font-weight:900}.quick-amounts button:active{background:#ffe7d2}.payment-change{margin-top:8px;padding:10px 12px;border-radius:8px;background:#e8f7ef;color:#087b45;font-weight:900;text-align:right}.payment-change.due{background:#fff0ed;color:#bd392d}
  .payment-grid{display:flex;flex-wrap:nowrap;overflow-x:auto;overflow-y:hidden;gap:9px;padding:4px 2px 9px;scrollbar-width:thin}.payment-grid:before{display:none}.payment-grid::-webkit-scrollbar{height:5px}.payment-grid::-webkit-scrollbar-thumb{background:#d18a62;border-radius:5px}.payment-grid .pay-option{flex:0 0 132px}.quick-amounts{display:grid;grid-template-columns:repeat(5,minmax(0,1fr));gap:6px;margin-top:14px;padding:10px;border:1px solid #f0d2b0;border-radius:11px;background:#fff6eb}.quick-amounts:before{display:none}.quick-amounts button{width:100%;min-height:44px;padding:0;font-size:14px;box-shadow:0 2px 4px #5424140d}
  .payment-summary{grid-template-columns:repeat(3,minmax(0,1fr));align-items:center}.payment-summary>div:nth-child(1){grid-column:1}.payment-summary>div:nth-child(2){grid-column:3}.payment-summary>div:nth-child(3),.payment-summary>div:nth-child(4),.payment-summary>div:nth-child(5){grid-row:2;padding-top:9px;border-top:1px solid #efd5bd}.payment-summary>div:nth-child(3){grid-column:1}.payment-summary>div:nth-child(4){grid-column:2}.payment-summary>div:nth-child(5){grid-column:3}.payment-summary b{font-size:14px;white-space:nowrap}.payment-summary .grand b{font-size:18px}.payment-summary>div:last-child b{color:#087b45}.payment-summary>div:last-child b.due{color:#bd392d}.payment-keypad{display:grid;grid-template-columns:repeat(3,1fr);gap:7px;margin-top:12px}.payment-keypad button{height:44px;border:1px solid #eadfd6;border-radius:8px;background:#fff;color:#51261b;font-size:18px;font-weight:900}.payment-keypad button[data-paykey="clear"]{background:#fff0ed;color:#bd392d;font-size:11px}.payment-keypad button:active{background:#ffe7d2}
  @media(max-width:560px){.payment-summary{grid-template-columns:repeat(2,minmax(0,1fr))!important}.payment-summary>div:nth-child(1),.payment-summary>div:nth-child(2),.payment-summary>div:nth-child(3),.payment-summary>div:nth-child(4){grid-column:auto!important;grid-row:auto!important}.payment-summary b{font-size:12px}.payment-summary .grand b{font-size:15px}}
  .pay-option:before{display:none!important}.payment-summary{grid-template-columns:repeat(2,minmax(0,1fr))!important}.payment-summary>div:nth-child(1),.payment-summary>div:nth-child(2),.payment-summary>div:nth-child(3),.payment-summary>div:nth-child(4){grid-column:auto!important;grid-row:auto!important;padding:10px 0!important;border-top:0!important}.payment-summary>div:nth-child(3),.payment-summary>div:nth-child(4){border-top:1px solid #efd5bd!important}.payment-summary>div:nth-child(2),.payment-summary>div:nth-child(4){text-align:right}.payment-summary>div:nth-child(4) b{color:#087b45}
  .calculations .calculation:first-child{border-color:#f0c39d;background:#fff4e8}.calculations .calculation:first-child small{color:#a85a28}.calculations .calculation:first-child b{color:#b94722}.calculations .calculation:last-child{border-color:#b9dfe0;background:#eefafa}.calculations .calculation:last-child small{color:#24777b}.calculations .calculation:last-child b{color:#087b7f}
  .main-quick-amounts{display:grid;grid-template-columns:repeat(6,minmax(0,1fr));gap:5px;margin:6px 0}.main-quick-amounts button{min-height:34px;padding:0 4px;border:1px solid #e0b58f;border-radius:7px;background:#fff4e8;color:#a94723;font-weight:900}.main-quick-amounts button:active{background:#ffe1c4;transform:translateY(1px)}
  .amount-entry-wrap{display:flex;align-items:stretch;gap:6px}.amount-entry-wrap .pay-input{flex:1;min-width:0}.amount-edit-btn{width:44px;border-radius:9px;font-size:18px;font-weight:900}.amount-back{border:1px solid #e0bda8;background:#fff;color:#a34a2b}.amount-clear{border:1px solid #e8aaa0;background:#fff0ed;color:#bd392d}.amount-edit-btn:active{transform:translateY(1px);filter:brightness(.96)}
  .amount-main-btn{width:46px;flex:0 0 46px;border-radius:7px;font-weight:900;font-size:16px}.amount-main-back{border:2px solid #dfb38f;background:#fff;color:#a34a2b;font-size:24px}.amount-main-clear{border:1px solid #e8aaa0;background:#fff0ed;color:#bd392d}.amount-main-btn:active{transform:translateY(1px);filter:brightness(.96)}
  .keypad .key-clear{font-size:12px;letter-spacing:.02em;background:#fff0ed;color:#bd392d;border-color:#e8aaa0}
  .butch-header{height:78px;padding:10px 16px;background:linear-gradient(135deg,#4b1714,#7c271d 58%,#b84925);border-bottom:4px solid #e47b27;color:#fff}.brand{display:flex;align-items:center;gap:8px;color:#ffd0a0;font-size:25px}.brand b{color:#ff9b39}.brand-divider{color:#ffffff66;font-weight:300}.butchery-heading{color:#fff;font-size:21px;letter-spacing:.12em}.brand small{display:block;align-self:flex-end;margin:0 0 4px -3px;color:#ffd9b7;font-size:8px;letter-spacing:.16em;font-weight:800}.cashier{background:#ffffff1c;border:1px solid #ffffff3d;color:#ffe0c7}.cashier span{display:block;font-size:10px;color:#ffd1ad;text-transform:uppercase;letter-spacing:.06em}.cashier strong{color:#fff;font-size:13px}
  .butch-home{display:flex;align-items:center;gap:7px;padding:8px 12px;border:1px solid #ffffff55;border-radius:8px;background:#ffffff1c;color:#fff;text-decoration:none;font-size:11px;font-weight:900;letter-spacing:.06em}.butch-home span{font-size:22px;line-height:18px;color:#ffd09d}.butch-home:hover,.butch-home:focus{background:#ffffff33;color:#fff;text-decoration:none}
  .butch-main{background:#fffaf5}.categories{padding:2px}.category{border-color:#ead7c9;background:#fff8f0;color:#58251c}.category.active{background:#9e351f;border-color:#7d2819;box-shadow:0 3px 8px #7b251b33}.panel{border-color:#ead7c9;box-shadow:0 3px 12px #5b25120d}.field-label,.cart-head strong{color:#6e2b1e}.totals{border-color:#efcba9;background:#fff1e3}.total-line b{color:#8c301c}.pay-btn{background:#9e351f;border-color:#7d2819}.butch-footer{background:#fffaf5}
  @media(min-width:620px){.butch-main{grid-template-columns:minmax(0,1fr) minmax(0,1fr);align-items:start}.categories{grid-column:1/-1}.panel{min-width:0}}
  .payment-actions{margin-top:13px}.payment-actions button{height:54px;border-radius:10px;font-size:14px}.payment-confirm{border-color:#8b2f1d;background:#9f351f;box-shadow:0 4px 0 #702317}.payment-confirm:active{transform:translateY(2px);box-shadow:0 2px 0 #702317}.payment-cancel{border-color:#dfb6a5;background:#fff8f3;color:#a94b32}
  @media(max-width:619px){.amount-box{height:60px;padding:0 8px}.amount-box input{font-size:25px}.amount-main-btn{width:48px;flex-basis:48px}.butch-main{width:100%;overflow:hidden}.panel{min-width:0}.keypad button{min-width:0}}
  @media(max-width:430px){.butch-header{height:68px;padding:8px 10px}.brand{gap:5px;font-size:20px}.butchery-heading{font-size:16px;letter-spacing:.08em}.brand small{font-size:6px}.cashier{padding:5px 7px}.cashier strong{font-size:11px}}
  @media(max-width:560px){.payment-head{padding:16px}.payment-body{padding:12px}.payment-head h2{font-size:21px}.payment-head:after{right:42px;font-size:58px}.payment-summary b{font-size:16px}.payment-summary .grand b{font-size:20px}.payment-keypad button{height:45px}}
</style>
