<?php
if (session_status() === PHP_SESSION_NONE) {
    session_start();
}

header('Content-Type: application/json; charset=utf-8');
require_once '../../inc/connection.php';

function sup_api_ensure_schema($connection)
{
    $columns = [
        'sup_email' => "VARCHAR(150) NULL",
        'sup_address' => "VARCHAR(255) NULL",
        'sup_location' => "VARCHAR(150) NULL",
        'sup_pin' => "VARCHAR(80) NULL",
        'sup_vat' => "VARCHAR(80) NULL",
        'sup_type' => "TINYINT NOT NULL DEFAULT 1",
        'del_status' => "TINYINT NOT NULL DEFAULT 2",
    ];
    foreach ($columns as $name => $definition) {
        $check = mysqli_query($connection, "SHOW COLUMNS FROM kp_suppliers LIKE '" . $name . "'");
        if ($check && mysqli_num_rows($check) === 0) {
            mysqli_query($connection, "ALTER TABLE kp_suppliers ADD COLUMN `{$name}` {$definition}");
        }
    }
}

sup_api_ensure_schema($connection);

function sup_api_response($ok, $message, $data = [], $status = 200)
{
    http_response_code($status);
    echo json_encode(['ok' => (bool)$ok, 'message' => (string)$message, 'data' => $data]);
    exit;
}

function sup_api_input($key, $default = '')
{
    return trim((string)($_REQUEST[$key] ?? $default));
}

function sup_api_row($row)
{
    return [
        'id' => (int)$row['sup_id'],
        'sup_id' => (int)$row['sup_id'],
        'value' => (int)$row['sup_id'],
        'name' => (string)($row['sup_name'] ?? ''),
        'sup_name' => (string)($row['sup_name'] ?? ''),
        'phone' => (string)($row['sup_phone'] ?? ''),
        'sup_phone' => (string)($row['sup_phone'] ?? ''),
        'email' => (string)($row['sup_email'] ?? ''),
        'sup_email' => (string)($row['sup_email'] ?? ''),
        'address' => (string)($row['sup_address'] ?? ''),
        'sup_address' => (string)($row['sup_address'] ?? ''),
        'location' => (string)($row['sup_location'] ?? ''),
        'sup_location' => (string)($row['sup_location'] ?? ''),
        'pin' => (string)($row['sup_pin'] ?? ''),
        'sup_pin' => (string)($row['sup_pin'] ?? ''),
        'vat' => (string)($row['sup_vat'] ?? ''),
        'sup_vat' => (string)($row['sup_vat'] ?? ''),
        'label' => trim((string)($row['sup_name'] ?? '')) . (trim((string)($row['sup_phone'] ?? '')) !== '' ? ' — ' . trim((string)$row['sup_phone']) : ''),
    ];
}

$action = strtolower(sup_api_input('action', sup_api_input('q', '') !== '' || sup_api_input('search', '') !== '' ? 'search' : 'list'));
$connection = $GLOBALS['connection'];

if ($action === 'search' || $action === 'list' || $action === 'get') {
    $id = (int)sup_api_input('sup_id', sup_api_input('id', '0'));
    if ($action === 'get' && $id > 0) {
        $stmt = mysqli_prepare($connection, 'SELECT * FROM kp_suppliers WHERE sup_id=? LIMIT 1');
        mysqli_stmt_bind_param($stmt, 'i', $id);
    } elseif ($action === 'search') {
        $term = sup_api_input('q', sup_api_input('search', ''));
        $like = '%' . $term . '%';
        $stmt = mysqli_prepare($connection, 'SELECT * FROM kp_suppliers WHERE del_status=2 AND (sup_name LIKE ? OR sup_phone LIKE ?) ORDER BY sup_name ASC LIMIT 30');
        mysqli_stmt_bind_param($stmt, 'ss', $like, $like);
    } else {
        $stmt = mysqli_prepare($connection, 'SELECT * FROM kp_suppliers WHERE del_status=2 ORDER BY sup_name ASC');
    }
    if (!$stmt || !mysqli_stmt_execute($stmt)) sup_api_response(false, 'Could not fetch suppliers.', [], 500);
    $result = mysqli_stmt_get_result($stmt);
    $rows = [];
    while ($row = mysqli_fetch_assoc($result)) $rows[] = sup_api_row($row);
    mysqli_stmt_close($stmt);
    if ($action === 'get') sup_api_response(true, 'Supplier loaded.', $rows[0] ?? []);
    if ($action === 'search') echo json_encode(array_map(function ($row) { return ['id'=>$row['id'], 'value'=>$row['value'], 'name'=>$row['name'], 'label'=>$row['label']]; }, $rows));
    else sup_api_response(true, 'Suppliers loaded.', ['suppliers' => $rows]);
    exit;
}

$name = sup_api_input('name', sup_api_input('sup_name'));
$phone = sup_api_input('sup_phone', sup_api_input('phone'));
$email = sup_api_input('sup_email', sup_api_input('email'));
$address = sup_api_input('sup_address', sup_api_input('address'));
$location = sup_api_input('sup_location', sup_api_input('sup_loc', sup_api_input('location')));
$pin = sup_api_input('sup_pin', sup_api_input('pin'));
$vat = sup_api_input('sup_vat', sup_api_input('vat'));
$supplierType = (int)sup_api_input('sup_type', '1');
if ($supplierType < 1 || $supplierType > 2) $supplierType = 1;

$typeCheck = mysqli_query($connection, "SHOW COLUMNS FROM kp_suppliers LIKE 'sup_type'");
if ($typeCheck && mysqli_num_rows($typeCheck) === 0) {
    mysqli_query($connection, "ALTER TABLE kp_suppliers ADD COLUMN sup_type TINYINT NOT NULL DEFAULT 1");
}

if ($action === 'create' || $action === 'add') {
    if ($name === '' || $phone === '') sup_api_response(false, 'Supplier name and phone are required.', [], 422);
    $stmt = mysqli_prepare($connection, 'INSERT INTO kp_suppliers(sup_name,sup_phone,sup_email,sup_address,sup_location,sup_pin,sup_vat,sup_type,del_status) VALUES(?,?,?,?,?,?,?,?,2)');
    if (!$stmt) sup_api_response(false, 'Could not prepare supplier creation.', [], 500);
    mysqli_stmt_bind_param($stmt, 'sssssssi', $name, $phone, $email, $address, $location, $pin, $vat, $supplierType);
    if (!mysqli_stmt_execute($stmt)) sup_api_response(false, 'Could not add supplier: ' . mysqli_error($connection), [], 500);
    $id = mysqli_insert_id($connection); mysqli_stmt_close($stmt);
    sup_api_response(true, 'Supplier added successfully.', ['sup_id' => $id]);
}

if ($action === 'update' || $action === 'edit') {
    $id = (int)sup_api_input('sup_id', sup_api_input('id', '0'));
    if ($id < 1 || $name === '' || $phone === '') sup_api_response(false, 'Supplier ID, name, and phone are required.', [], 422);
    $stmt = mysqli_prepare($connection, 'UPDATE kp_suppliers SET sup_name=?,sup_phone=?,sup_email=?,sup_address=?,sup_location=?,sup_pin=?,sup_vat=?,sup_type=? WHERE sup_id=?');
    if (!$stmt) sup_api_response(false, 'Could not prepare supplier update.', [], 500);
    mysqli_stmt_bind_param($stmt, 'sssssssii', $name, $phone, $email, $address, $location, $pin, $vat, $supplierType, $id);
    if (!mysqli_stmt_execute($stmt)) sup_api_response(false, 'Could not update supplier.', [], 500);
    mysqli_stmt_close($stmt); sup_api_response(true, 'Supplier updated successfully.', ['sup_id' => $id]);
}

if ($action === 'delete' || $action === 'remove') {
    $id = (int)sup_api_input('sup_id', sup_api_input('id', '0'));
    if ($id < 1) sup_api_response(false, 'A supplier is required.', [], 422);
    $stmt = mysqli_prepare($connection, 'UPDATE kp_suppliers SET del_status=1 WHERE sup_id=?');
    if (!$stmt) sup_api_response(false, 'Could not prepare supplier deletion.', [], 500);
    mysqli_stmt_bind_param($stmt, 'i', $id);
    if (!mysqli_stmt_execute($stmt)) sup_api_response(false, 'Could not discontinue supplier.', [], 500);
    mysqli_stmt_close($stmt); sup_api_response(true, 'Supplier discontinued successfully.', ['sup_id' => $id]);
}

sup_api_response(false, 'Unknown supplier action.', [], 400);
