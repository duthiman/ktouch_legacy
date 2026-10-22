<?php

include('../../inc/connection.php');

//$connection = connectidentitiDB();
$array = json_decode(file_get_contents('php://input'), true);

$response = [];
$status = 0;
$messages = [];

if (empty($array)) {
    $status = 0;
    $messages[] = 'Empty Payload';
} else {
    $list = $array['list'];
    foreach ($list as $data) {
        $split_status = $data['split_status'];
        $mpesa_message = $connection->real_escape_string(str_replace("'", '', $data['mpesa_message']));

        if ($split_status == 1) {
            $name = $connection->real_escape_string(str_replace("'", '', $data['name']));
            $phone = $connection->real_escape_string($data['phone']);
            $code = $connection->real_escape_string($data['code']);
            $amount = $connection->real_escape_string($data['amount']);
            $account = $connection->real_escape_string($data['account']);
            $balance = $connection->real_escape_string($data['balance']);
            $cost = $connection->real_escape_string($data['cost']);
            $date = $connection->real_escape_string($data['date']);
            $time = $connection->real_escape_string($data['time']);

            $insert_split_message = $connection->query("INSERT IGNORE INTO split_message(name, phone, code, amount, account, balance, cost, date, time) 
                VALUES ('$name', '$phone', '$code', '$amount', '$account', '$balance', '$cost', '$date', '$time')");

            $update_balance = $connection->query("INSERT INTO balance(id, balance) VALUES(1, '$balance') 
                ON DUPLICATE KEY UPDATE balance='$balance'");

            $insert_message = $connection->query("INSERT INTO mpesa_message(message, split_status) VALUES('$mpesa_message', '1')");

            if (!$insert_message) {
                $status = 0;
                $messages[] = "Failed to insert message (1): " . $connection->error;
            } else {
                $status = 1;
                $messages[] = "Split message recorded.";
            }
        } else {
            $insert_message = $connection->query("INSERT INTO mpesa_message(message, split_status) VALUES('$mpesa_message', '0')");

            if (!$insert_message) {
                $status = 0;
                $messages[] = "Failed to insert message (0): " . $connection->error;
            } else {
                $status = 1;
                $messages[] = "Unsplit message recorded.";
            }
        }
    }
}

$response = array('status' => $status, 'message' => implode(" | ", $messages));
echo json_encode($response);
