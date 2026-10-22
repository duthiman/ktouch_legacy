<?php
session_start();
header('Content-Type: application/json');
require_once '../../../../inc/connection.php';
require_once '../../../../inc/gen_funcs.php';
require_once __DIR__.'/take_helpers.php';
require_once __DIR__.'/take_schema.php';
require_once __DIR__.'/take_logger.php';
require_once __DIR__.'/take_repository.php';
require_once __DIR__.'/take_service.php';
take_schema_ensure();
$action=take_clean_text($_REQUEST['action']??'');
switch($action) {
    case 'items': take_json_response(true,'Take items loaded.',['items'=>take_service_items()['items']]); break;
    case 'content': take_json_response(true,'Taken stock loaded',['rows'=>take_service_content($_REQUEST['shift']??'')['rows']]); break;
    case 'save': $rawQty=isset($_REQUEST['qty']) ? trim((string)$_REQUEST['qty']) : ''; $r=take_service_save(take_clean_int($_REQUEST['id']??0),$rawQty,$_REQUEST['shift']??'',take_user_id()); take_json_response($r['ok'],$r['message'],['items'=>$r['items']??[]],$r['ok']?200:400); break;
    case 'delete': $r=take_service_delete(take_clean_int($_REQUEST['item_id']??0),$_REQUEST['shift']??'',take_user_id()); take_json_response($r['ok'],$r['message'],['rows'=>$r['rows']??[]],$r['ok']?200:400); break;
    case 'finish': $r=take_service_finish(take_user_id()); take_json_response($r['ok'],$r['message'],[],$r['ok']?200:400); break;
    default: take_json_response(false,'Unknown take action.',['action'=>$action],400);
}
