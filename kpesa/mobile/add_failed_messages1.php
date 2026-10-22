<?php

include('../../inc/connection.php');

$array = json_decode(file_get_contents('php://input'), true);

$response = array();
$status = 0;
$message = "";

if (empty($array) || !isset($array['list']) || !is_array($array['list'])) {
    $status = 0;
    $message = 'Invalid or Empty Payload';
} else {
    // Prepare statements
    $stmtCheckMpesa = $connection->prepare("SELECT COUNT(*) FROM mpesa_message WHERE REPLACE(LOWER(message), ' ', '') = REPLACE(LOWER(?), ' ', '')");
    $stmtInsertMpesa = $connection->prepare("INSERT INTO mpesa_message(message, split_status) VALUES(?, ?)");
    $stmtSplit = $connection->prepare("INSERT IGNORE INTO split_message(name, phone, code, amount, account, balance, cost, date, time) VALUES (?, ?, ?, ?, ?, ?, ?, ?, ?)");
    $stmtBalance = $connection->prepare("INSERT INTO balance(id, balance) VALUES(1, ?) ON DUPLICATE KEY UPDATE balance=?");

    foreach ($array['list'] as $data) {
        $split_status = isset($data['split_status']) ? intval($data['split_status']) : 0;
        $mpesa_message = isset($data['mpesa_message']) ? $data['mpesa_message'] : '';

        // Check if the message already exists
        $stmtCheckMpesa->bind_param("s", $mpesa_message);
        $stmtCheckMpesa->execute();
        $stmtCheckMpesa->bind_result($messageCount);
        $stmtCheckMpesa->fetch();
        $stmtCheckMpesa->reset();

        if ($messageCount == 0) {  // ✅ Only insert if message does not exist
            if ($split_status === 1) {
                // If status is 1, the split process was successful
                $name = isset($data['name']) ? str_replace("'", '', $data['name']) : '';
                $phone = isset($data['phone']) ? $data['phone'] : '';
                $code = isset($data['code']) ? $data['code'] : '';
                $amount = isset($data['amount']) ? floatval($data['amount']) : 0;
                $account = isset($data['account']) ? $data['account'] : '';
                $balance = isset($data['balance']) ? floatval($data['balance']) : 0;
                $cost = isset($data['cost']) ? floatval($data['cost']) : 0;
                $date = isset($data['date']) ? $data['date'] : '';
                $time = isset($data['time']) ? $data['time'] : '';

                // Insert into split_message
                $stmtSplit->bind_param("sssssdsss", $name, $phone, $code, $amount, $account, $balance, $cost, $date, $time);
                $stmtSplit->execute();

                // Update balance
                $stmtBalance->bind_param("dd", $balance, $balance);
                $stmtBalance->execute();

                // Insert Mpesa message (store original message)
                $stmtInsertMpesa->bind_param("si", $mpesa_message, $split_status);
                $stmtInsertMpesa->execute();

                if ($stmtInsertMpesa->error) {
                    $status = 0;
                    $message = "Error: " . $stmtInsertMpesa->error;
                } else {
                    $status = 1;
                    $message = "Payment Details Recorded";
                }
            } else {
                // Handle failed split transactions
                $stmtInsertMpesa->bind_param("si", $mpesa_message, $split_status);
                $stmtInsertMpesa->execute();

                if ($stmtInsertMpesa->error) {
                    $status = 0;
                    $message = "Error: " . $stmtInsertMpesa->error;
                } else {
                    $status = 1;
                    $message = "Payment Details Recorded";
                }
            }
        } else {
            $status = 0;
            $message = "Duplicate M-Pesa message detected, skipping insert.";
        }
    }

    // Close statements
    $stmtCheckMpesa->close();
    $stmtInsertMpesa->close();
    $stmtSplit->close();
    $stmtBalance->close();
}

// Send response
$response = array('status' => $status, 'message' => $message);
echo json_encode($response);

// Close database connection
$connection->close();
?>
