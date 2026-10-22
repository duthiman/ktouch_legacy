<?php
session_start();
include('../../inc/connection.php');
include('../../inc/gen_funcs.php');
include('../../front/kug/funcs.php');

$user_id = (int)($_SESSION['user_id'] ?? 0);
if ($user_id < 1) {
  header('Location:../log_out.php');
  exit;
}

$po_id = (int)($_POST['po_id'] ?? 0);
$sup_id = (int)($_POST['sup_id'] ?? 0);
$invoice_date = trim((string)($_POST['invoice_date'] ?? ''));
$refno = trim((string)($_POST['refno'] ?? ''));
$tax_by = (int)($_POST['tax_by'] ?? 0);
$tax_perc = (float)($_POST['tax_perc'] ?? 0);
$discount = (float)($_POST['discount'] ?? 0);
$item_ids = $_POST['item_id'] ?? [];
$qtys = $_POST['qty'] ?? [];
$prices = $_POST['price'] ?? [];

if ($po_id < 1 || $sup_id < 1 || $invoice_date === '' || empty($item_ids)) {
  header('Location:pending.php');
  exit;
}

$poQuery = mysqli_query($connection, "SELECT * FROM kp_po WHERE po_id='$po_id' ORDER BY id DESC LIMIT 1") or die(mysqli_error($connection));
if (!$poQuery || mysqli_num_rows($poQuery) < 1) {
  header('Location:pending.php');
  exit;
}

$po = mysqli_fetch_assoc($poQuery);
if ((int)$po['rec_status'] === 1 && (int)$po['stock_status'] === 1) {
  header('Location:pending.php?msg=approved');
  exit;
}

$counter = (int)$po['req_id'];
$purchaseSettings = kp_purchase_settings($connection);
$useCounterStock = ((int)$purchaseSettings['front_auto_stock_enabled'] === 1);
$shift = (string)get_column('kp_shift', 'shift', "1");
$supplierBalance = (float)get_column('kp_suppliers', 'sup_bal', "sup_id='$sup_id'");
$subtotal = 0;

foreach ($item_ids as $index => $item_id) {
  $item_id = (int)$item_id;
  $qty = round((float)($qtys[$index] ?? 0), 2);
  $price = round((float)($prices[$index] ?? 0), 2);
  if ($item_id < 1 || $qty <= 0 || $price <= 0) {
    continue;
  }
  $lineTotal = round($qty * $price, 2);
  $subtotal += $lineTotal;
  mysqli_query($connection, "UPDATE kp_po_items SET qty='$qty', price='$price', total='$lineTotal', rec_qty='$qty', rec_price='$price', rec_total='$lineTotal', rec_user='$user_id', rec_status='1', status='1', s2s='1' WHERE po_id='$po_id' AND item_id='$item_id'") or die(mysqli_error($connection));
}

$taxAmount = $tax_by === 1 ? round($subtotal * ($tax_perc / 100), 2) : 0;
$grandTotal = round($subtotal + $taxAmount - $discount, 2);
$vatable = $subtotal;
$safeRefNo = mysqli_real_escape_string($connection, $refno);
$safeInvoiceDate = mysqli_real_escape_string($connection, $invoice_date);

mysqli_query($connection, "UPDATE kp_po SET sup_id='$sup_id', user_id='$user_id', shift='$shift', po_date='$safeInvoiceDate', amount='$grandTotal', disc='$discount', tax_by='$tax_by', tax_type='VAT', tax_perc='$tax_perc', vatable='$vatable', vat='$taxAmount', cf_bal='$supplierBalance', refno='$safeRefNo', appr_status='1', appr_user='$user_id', appr_datime=CURRENT_TIMESTAMP, rec_status='1', stock_status='1', s2s='1', rec_staff='$user_id', rec_date=CURRENT_DATE, rec_datime=CURRENT_TIMESTAMP WHERE po_id='$po_id'") or die(mysqli_error($connection));

$grn_id = get_id('kp_grn_id', 'grn_id');
mysqli_query($connection, "INSERT INTO kp_grn(grn_id,po_id,user_id,sup_id,amount,bal,day) VALUES('$grn_id','$po_id','$user_id','$sup_id','$grandTotal','$grandTotal',CURRENT_DATE)") or die(mysqli_error($connection));

foreach ($item_ids as $index => $item_id) {
  $item_id = (int)$item_id;
  $qty = round((float)($qtys[$index] ?? 0), 2);
  $price = round((float)($prices[$index] ?? 0), 2);
  if ($item_id < 1 || $qty <= 0 || $price <= 0) {
    continue;
  }
  $lineTotal = round($qty * $price, 2);
  $dpt = get_column('kp_items', 'dpt', "item_id='$item_id'");
  $column = kp_purchase_stock_column($connection, $counter, $useCounterStock);
  $stockCounter = ($useCounterStock && $column !== 'qty') ? $counter : 0;
  mysqli_query($connection, "INSERT INTO kp_grn_items(grn_id,item_id,qty,price,total,user_id,day,s2s) VALUES('$grn_id','$item_id','$qty','$price','$lineTotal','$user_id',CURRENT_DATE,1)") or die(mysqli_error($connection));
  mysqli_query($connection, "UPDATE kp_items SET b_price='$price' WHERE item_id='$item_id'") or die(mysqli_error($connection));
  mysqli_query($connection, "UPDATE kp_items SET $column=$column+'$qty' WHERE item_id='$item_id'") or die(mysqli_error($connection));
  mysqli_query($connection, "INSERT INTO kp_stock_track(item_id,po_id,grn_id,user_id,qty,status,stock_counter,shift,dpt) VALUES('$item_id','$po_id','$grn_id','$user_id','$qty','IN','$stockCounter',CURRENT_DATE,'$dpt')") or die(mysqli_error($connection));
}

mysqli_query($connection, "UPDATE kp_suppliers SET sup_bal=sup_bal+'$grandTotal' WHERE sup_id='$sup_id'") or die(mysqli_error($connection));

$action = "APPROVED|-|-|BUY STOCK NO|".$po_id."|GRN|".$grn_id;
logs($action, '../logs');

header('Location:pending.php?msg=approved');
exit;
