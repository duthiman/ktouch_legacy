<?php
session_start();
header('Content-Type: application/json; charset=UTF-8');
require_once '../../../inc/connection.php';
require_once __DIR__ . '/ad_helper.php';
require_once __DIR__ . '/ad_schema.php';
require_once __DIR__ . '/ad_repo.php';
require_once __DIR__ . '/ad_service.php';
if(!isset($connection)||!($connection instanceof mysqli))ad_json_response(false,'Database connection unavailable.',[],500);
try {
    ad_schema_boot();
} catch (Throwable $error) {
    ad_json_response(false, $error->getMessage(), [], 500);
}
$action=strtolower(ad_clean_text($_REQUEST['action']??'list'));
switch($action){
case 'list':
case 'staff':
 $result=ad_service_staff_list($_REQUEST);ad_json_response($result['ok'],$result['message'],['staff'=>$result['staff']??[],'pagination'=>$result['pagination']??[]],$result['ok']?200:500);break;
case 'get':
 $result=ad_service_staff_get(ad_clean_int($_REQUEST['staff_id']??$_REQUEST['id']??0));ad_json_response($result['ok'],$result['message'],['staff'=>$result['staff']??null],$result['ok']?200:404);break;
case 'stats':
 $result=ad_service_stats();ad_json_response($result['ok'],$result['message'],['stats'=>$result['stats']??[]],$result['ok']?200:500);break;
case 'relationships':
 $result=ad_service_relationships();ad_json_response($result['ok'],$result['message'],['relationships'=>$result['relationships']??[]],200);break;
case 'roles':
 $result=ad_service_roles();ad_json_response($result['ok'],$result['message'],['roles'=>$result['roles']??[]],200);break;
case 'role_list':
 $result=ad_service_role_list();ad_json_response($result['ok'],$result['message'],['roles'=>$result['roles']??[]],200);break;
case 'role_get':
 $result=ad_service_role_get(ad_clean_int($_REQUEST['role_id']??$_REQUEST['id']??0));ad_json_response($result['ok'],$result['message'],['role'=>$result['role']??null],$result['ok']?200:404);break;
case 'role_create':
case 'role_update':
 $result=ad_service_role_save($_POST);ad_json_response($result['ok'],$result['message'],['role_id'=>$result['role_id']??0],$result['ok']?201:422);break;
case 'users':
case 'user_list':
 $result=ad_service_users($_REQUEST);ad_json_response($result['ok'],$result['message'],['users'=>$result['users']??[]],200);break;
case 'user_get':
 $result=ad_service_user_get(ad_clean_int($_REQUEST['user_id']??$_REQUEST['id']??0));ad_json_response($result['ok'],$result['message'],['user'=>$result['user']??null],$result['ok']?200:404);break;
case 'user_edit':
 $result=ad_service_user_edit($_POST);ad_json_response($result['ok'],$result['message'],[],$result['ok']?200:422);break;
case 'user_create':
 $result=ad_service_user_create($_POST);ad_json_response($result['ok'],$result['message'],['user_id'=>$result['user_id']??0],$result['ok']?201:422);break;
case 'user_change_pin':
 $result=ad_service_user_credential($_POST,'pin');ad_json_response($result['ok'],$result['message'],[],$result['ok']?200:422);break;
case 'user_change_password':
 $result=ad_service_user_credential($_POST,'password');ad_json_response($result['ok'],$result['message'],[],$result['ok']?200:422);break;
case 'user_target':
 $result=ad_service_user_target($_POST);ad_json_response($result['ok'],$result['message'],[],$result['ok']?200:422);break;
case 'user_permissions_get':
 $result=ad_service_user_permission_data(ad_clean_int($_REQUEST['user_id']??$_REQUEST['id']??0));ad_json_response($result['ok'],$result['message'],['permissions'=>$result['permissions']??[],'selected'=>$result['selected']??[]],$result['ok']?200:404);break;
case 'user_permissions_save':
 $result=ad_service_user_permissions($_POST);ad_json_response($result['ok'],$result['message'],[],$result['ok']?200:422);break;
case 'user_suspend':
 $result=ad_service_user_status($_POST,1);ad_json_response($result['ok'],$result['message'],[],$result['ok']?200:422);break;
case 'user_unsuspend':
 $result=ad_service_user_status($_POST,2);ad_json_response($result['ok'],$result['message'],[],$result['ok']?200:422);break;
case 'save_staff':
case 'create_staff':
 $result=ad_service_save_staff($_POST,$_FILES,(int)($_SESSION['user_id']??0));ad_json_response($result['ok'],$result['message'],['staff_id'=>$result['staff_id']??0,'user_id'=>$result['user_id']??0],$result['ok']?201:422);break;
default: ad_json_response(false,'Unknown administration action.',['action'=>$action],400);
}
