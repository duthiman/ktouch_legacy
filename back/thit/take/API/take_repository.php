<?php
function take_repo_items() {
    global $connection;
    $rows = [];
    $result = mysqli_query($connection, "SELECT item_id, name FROM kp_items WHERE store_take=2 AND del_status=2 AND stock=1 ORDER BY name ASC");
    if ($result) while ($row = mysqli_fetch_assoc($result)) $rows[] = ['item_id'=>(int)$row['item_id'], 'name'=>(string)$row['name']];
    return $rows;
}

function take_repo_rows($shift) {
    global $connection;
    $safeShift = mysqli_real_escape_string($connection, (string)$shift);
    $rows = [];
    $sql = "SELECT t.take_id,t.item_id,t.closing,t.counted,t.shift,COALESCE(i.name,'Unknown item') AS name
            FROM kp_store_stock_take t LEFT JOIN kp_items i ON i.item_id=t.item_id
            WHERE t.shift='{$safeShift}' ORDER BY t.take_id DESC";
    $result = mysqli_query($connection, $sql);
    if ($result) while ($row = mysqli_fetch_assoc($result)) {
        $closing = (float)$row['closing']; $counted = (float)$row['counted'];
        $rows[] = ['take_id'=>(int)$row['take_id'], 'item_id'=>(int)$row['item_id'], 'name'=>(string)$row['name'], 'closing'=>$closing, 'counted'=>$counted, 'variance'=>$closing-$counted, 'shift'=>(string)$row['shift']];
    }
    return $rows;
}

function take_repo_save($itemId, $qty, $shift, $userId) {
    global $connection;
    $itemId=(int)$itemId; $qty=(float)$qty; $userId=(int)$userId; $safeShift=mysqli_real_escape_string($connection,(string)$shift);
    $itemResult=mysqli_query($connection,"SELECT store_qty FROM kp_items WHERE item_id='{$itemId}' AND del_status=2 AND stock=1 LIMIT 1");
    $item=$itemResult ? mysqli_fetch_assoc($itemResult) : null;
    if (!$item) return false;
    $closing=(float)$item['store_qty'];
    if (!mysqli_query($connection,"UPDATE kp_items SET store_take=1, store_qty='{$qty}' WHERE item_id='{$itemId}'")) return false;
    return mysqli_query($connection,"INSERT INTO kp_store_stock_take(item_id,user_id,closing,counted,shift) VALUES('{$itemId}','{$userId}','{$closing}','{$qty}','{$safeShift}')") !== false;
}

function take_repo_delete($itemId, $shift) {
    global $connection;
    $itemId=(int)$itemId; $safeShift=mysqli_real_escape_string($connection,(string)$shift);
    if (mysqli_query($connection,"DELETE FROM kp_store_stock_take WHERE item_id='{$itemId}' AND shift='{$safeShift}'") === false) return false;
    return mysqli_query($connection,"UPDATE kp_items SET store_take=2 WHERE item_id='{$itemId}'") !== false;
}

function take_repo_finish() {
    global $connection;
    return mysqli_query($connection, "TRUNCATE TABLE kp_store_take_status") !== false;
}
