<?php
session_start();
include('../../inc/connection.php');
include('../../inc/gen_funcs.php'); 
//$connection = connectidentitiDB();
if (!$connection) {
    $response['message'] = 'Database connection failed';
    logError('Database connection failed: ' . mysqli_connect_error());
    echo json_encode($response);
    exit;
}
$user_id = $_REQUEST['user_id'];
if (empty($user_id)) {
    $response["status"] = 0;
    $response["message"] = "User ID missing";
    echo json_encode($response);
} else { 
    $claims_list = array();
    $query = "SELECT p.*, COALESCE(t.bank_name,'Unassigned') AS bank_name,
                     COALESCE(t.card_label,'Unknown Card') AS card_label,
                     COALESCE(t.terminal_code,'') AS terminal_code
              FROM kp_user_pdq p
              LEFT JOIN kp_user_pdq_terminals_map pm ON pm.pdq_id = p.id
              LEFT JOIN kp_pdq_terminals t ON t.terminal_id = pm.terminal_id
              WHERE p.clear_status=2 AND p.user_id='$user_id'";
    $result = mysqli_query($connection, $query);
    $claims = array();
    if (mysqli_num_rows($result) > 0) { 
        while ($pow = mysqli_fetch_array($result)) {
            $refno = $pow['refno']; 
            $amount = $pow['amount']; 
            $date = $pow['datime'];
            $claims[] = array(
                "user_id" => $user_id,
                "refno" => $refno,
                "amount" => $amount,
                "date" => $date,
                "bank_name" => ($pow['bank_name'] ?? 'Unassigned'),
                "card_label" => ($pow['card_label'] ?? 'Unknown Card'),
                "terminal_code" => ($pow['terminal_code'] ?? '')
            );
        }
    }
    $response = array();
    $response['pdqs'] = $claims;
    $response["status"] = 1;
    $response["message"] = "Use PDQ Report";

    echo json_encode($response);
}
