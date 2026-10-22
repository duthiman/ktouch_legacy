<?php
include('../../inc/connection.php');
//$connection = connectidentitiDB();

$data = json_decode(file_get_contents('php://input'), true);

$response = array();
$status = 0;
$message = "";

if (empty($data)) {
    $status = 0;
    $message = 'Empty Payload';
} else {
    $split_status = $data['split_status'];
    $mpesa_message = $connection->real_escape_string($data['mpesa_message']);
    $mpesa_message = str_replace("'", '', $mpesa_message);

    if ($split_status == 1) { //if status is 1, the split process was successful, otherwise an error occured
        $name = $data['name'];
        $name = str_replace("'", '', $name);
        $phone = $data['phone'];
        $code = $data['code'];
        $amount = $data['amount'];
        $account = $data['account'];
        $balance = $data['balance'];
        $cost = $data['cost'];
        $date = $data['date'];
        $time = $data['time'];
        
        $datime = date("Y-m-d H:i:s", strtotime($date . ' ' . $time));

        $insert_split_message = $connection->query("INSERT IGNORE INTO kp_split_message(name, phone, code, amount, account, balance, cost, date, time,datime) VALUES ('$name', '$phone', '$code', '$amount', '$account', '$balance', '$cost', '$date', '$time','$datime')");

        $update_balance = $connection->query("INSERT INTO balance(id,balance) VALUES(1,'$balance') ON DUPLICATE KEY UPDATE balance='$balance'");

        $insert_message = $connection->query("INSERT INTO mpesa_message(message,split_status) VALUES('$mpesa_message','1')");

        if (!$insert_message) {
            $status = 0;
            $message = $connection->error;
        } else {
            $status = 1;
            $message = "Payment Details Recorded";
        }
    } else {
        $insert_message = $connection->query("INSERT INTO mpesa_message(message,split_status) VALUES('$mpesa_message','0')");
        if (!$insert_message) {
            $status = 0;
            $message = $connection->error;
        } else {
            $status = 1;
            $message = "Payment Details Recorded";
        }
    }
}
$response = array('status' => $status, 'message' => $message);

echo json_encode($response);
