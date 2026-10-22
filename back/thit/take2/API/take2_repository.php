<?php
function take2_repo_items($scope, $scopeId) {
    global $connection;
    $where = "del_status=2 AND stock=1";
    $scope = take2_escape($scope); $scopeId = (int)$scopeId;
    if ($scope === 'category') $where .= " AND cat_id=$scopeId";
    if ($scope === 'subcategory') $where .= " AND sub_id=$scopeId";
    if ($scope === 'department') $where .= " AND dpt=$scopeId";
    $rows = [];
    $q = mysqli_query($connection, "SELECT item_id,name,store_qty FROM kp_items WHERE $where ORDER BY name");
    if ($q) while ($r = mysqli_fetch_assoc($q)) $rows[] = $r;
    return $rows;
}

function take2_repo_create($scope, $scopeId, $notes, $userId) {
    global $connection;
    $scope = take2_escape($scope); $scopeId=(int)$scopeId; $notes=take2_escape($notes); $userId=(int)$userId;
    if (!mysqli_query($connection, "INSERT INTO kp_stock_takes (take_code,scope_type,scope_id,notes,created_by) VALUES ('PENDING','$scope',$scopeId,'$notes',$userId)")) throw new Exception(mysqli_error($connection));
    $id=(int)mysqli_insert_id($connection); $code='ST-'.date('Ymd').'-'.str_pad((string)$id,4,'0',STR_PAD_LEFT);
    mysqli_query($connection,"UPDATE kp_stock_takes SET take_code='".take2_escape($code)."' WHERE take_id=$id");
    foreach (take2_repo_items($scope,$scopeId) as $item) {
        $name=take2_escape($item['name']); $itemId=(int)$item['item_id']; $qty=(float)$item['store_qty'];
        mysqli_query($connection,"INSERT INTO kp_stock_take_lines (take_id,item_id,item_name,expected_qty) VALUES ($id,$itemId,'$name',$qty)");
    }
    take2_event($id,'INITIALISED',$notes); return $id;
}

function take2_repo_list() {
    global $connection; $rows=[]; $q=mysqli_query($connection,"SELECT * FROM kp_stock_takes ORDER BY take_id DESC LIMIT 50");
    if($q) while($r=mysqli_fetch_assoc($q)) $rows[]=$r; return $rows;
}
function take2_repo_get($id) {
    global $connection; $id=(int)$id; $q=mysqli_query($connection,"SELECT * FROM kp_stock_takes WHERE take_id=$id LIMIT 1"); $take=$q?mysqli_fetch_assoc($q):null;
    if(!$take) return null; $lines=[]; $q=mysqli_query($connection,"SELECT l.*,COALESCE(c.name,'') AS category_name,COALESCE(s.name,'') AS subcategory_name FROM kp_stock_take_lines l LEFT JOIN kp_items i ON i.item_id=l.item_id LEFT JOIN kp_cat c ON c.cat_id=i.cat_id LEFT JOIN kp_sub_cat s ON s.sub_id=i.sub_id WHERE l.take_id=$id ORDER BY c.name,l.item_name"); if($q) while($r=mysqli_fetch_assoc($q)) $lines[]=$r; $take['lines']=$lines; $take['review_notes']=take2_repo_review_notes($id); return $take;
}
function take2_repo_update($id,$set) { global $connection; $parts=[]; foreach($set as $k=>$v) $parts[]="$k='".take2_escape($v)."'"; return mysqli_query($connection,"UPDATE kp_stock_takes SET ".implode(',',$parts)." WHERE take_id=".(int)$id); }
function take2_repo_line($id,$lineId,$qty,$notes='') { global $connection; $id=(int)$id;$lineId=(int)$lineId;$qty=(float)$qty;$notes=take2_escape($notes); return mysqli_query($connection,"UPDATE kp_stock_take_lines SET counted_qty=$qty,variance_qty=$qty-expected_qty,retake_required=0,line_status='COUNTED',count_notes='$notes',counted_at=NOW() WHERE take_id=$id AND line_id=$lineId"); }
function take2_repo_assign($id,$userId) { global $connection; return mysqli_query($connection,"UPDATE kp_stock_take_lines SET assigned_user_id=".(int)$userId." WHERE take_id=".(int)$id); }
function take2_repo_users() { global $connection; $rows=[]; $q=mysqli_query($connection,"SELECT user_id,username FROM kp_users ORDER BY username"); if($q) while($r=mysqli_fetch_assoc($q)) $rows[]=$r; return $rows; }
function take2_repo_review_notes($takeId) { global $connection; $rows=[]; $q=mysqli_query($connection,"SELECT line_id,explanation FROM kp_stock_take_reviews WHERE take_id=".(int)$takeId); if($q) while($r=mysqli_fetch_assoc($q)) $rows[(int)$r['line_id']] = (string)$r['explanation']; return $rows; }
function take2_repo_review_order($takeId) { global $connection; $rows=[]; $q=mysqli_query($connection,"SELECT line_id FROM kp_stock_take_reviews WHERE take_id=".(int)$takeId." ORDER BY review_id DESC"); if($q) while($r=mysqli_fetch_assoc($q)) $rows[]=(int)$r['line_id']; return $rows; }
function take2_repo_save_review_note($takeId,$lineId,$itemId,$explanation,$userId) { global $connection; $takeId=(int)$takeId;$lineId=(int)$lineId;$itemId=(int)$itemId;$userId=(int)$userId;$explanation=take2_escape($explanation); return mysqli_query($connection,"INSERT INTO kp_stock_take_reviews(take_id,line_id,item_id,explanation,reviewed_by) VALUES($takeId,$lineId,$itemId,'$explanation',$userId) ON DUPLICATE KEY UPDATE explanation='$explanation',reviewed_by=$userId,updated_at=NOW()"); }
function take2_repo_history($itemId) { global $connection; $rows=[]; $q=mysqli_query($connection,"SELECT * FROM kp_store_hist WHERE item_id=".(int)$itemId." LIMIT 30"); if($q) while($r=mysqli_fetch_assoc($q)) $rows[]=$r; return $rows; }
function take2_repo_scopes($categoryId=0) { global $connection; $out=['categories'=>[],'subcategories'=>[],'departments'=>[['id'=>1,'name'=>'BAR'],['id'=>2,'name'=>'KITCHEN']]]; $q=mysqli_query($connection,"SELECT cat_id AS id,name FROM kp_cat ORDER BY name ASC"); if($q) while($r=mysqli_fetch_assoc($q)) $out['categories'][]=$r; $where=$categoryId>0?" WHERE cat_id=".(int)$categoryId:''; $q=mysqli_query($connection,"SELECT sub_id AS id,name,cat_id FROM kp_sub_cat$where ORDER BY name ASC"); if($q) while($r=mysqli_fetch_assoc($q)) $out['subcategories'][]=$r; return $out; }
