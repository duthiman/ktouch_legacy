<?php
function ad_clean_text($value){return trim((string)$value);}
function ad_clean_int($value,$default=0){return filter_var($value,FILTER_VALIDATE_INT)!==false?(int)$value:(int)$default;}
function ad_json_response($ok,$message,$data=[],$status=200){http_response_code((int)$status);echo json_encode(['ok'=>(bool)$ok,'message'=>(string)$message,'data'=>$data],JSON_UNESCAPED_UNICODE|JSON_UNESCAPED_SLASHES);exit;}
function ad_db_error($connection,$fallback='Database request failed.'){ $error=$connection instanceof mysqli?mysqli_error($connection):'';return $error!==''?$error:$fallback;}
