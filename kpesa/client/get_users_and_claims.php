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
// Constants
const STATUS_SUCCESS = 1;
const STATUS_FAILURE = 0;

// Initialize response
$response = ['status' => STATUS_FAILURE, 'message' => '', 'claims' => []];

// Get input date, default to today's date if not provided
$date = $_REQUEST['date'] ?? date('Y-m-d');

// Set start and end date ranges
$start_date = $date . ' 09:00:00';
$end_date = date('Y-m-d', strtotime($date . ' +1 day')) . ' 09:00:00';

// Query database for claims data
try {
    $query = "
        SELECT 
            user_id, 
            COUNT(id) AS cnt, 
            SUM(amount) AS amt 
        FROM kp_split_message 
        WHERE claim_status = 1 
        AND datime BETWEEN ? AND ? 
        GROUP BY user_id
    ";

    // Prepare and bind statement
    $stmt = $connection->prepare($query);
    if (!$stmt) {
        throw new Exception("Database prepare failed: " . $connection->error);
    }

    $stmt->bind_param("ss", $start_date, $end_date);
    $stmt->execute();
    $result = $stmt->get_result();

    $claims_list = [];
    while ($row = $result->fetch_assoc()) {
        $user_id = $row['user_id'];
        $username = ($user_id == 0) ? 'NO NAME' : get_column($connection,'kp_users', 'username', "user_id='$user_id'");

        // Build user claim data
        $user = [
            'user_id' => $user_id,
            'total_amount' => $row['amt'],
            'claim_count' => $row['cnt'],
            'username' => $username,
            'date' => $date,
        ];

        $claims_list[] = $user;
    }

    $stmt->close();

    // Set success response
    $response['status'] = STATUS_SUCCESS;
    $response['message'] = "Claims Report";
    $response['claims'] = $claims_list;

} catch (Exception $e) {
    $response['message'] = "An error occurred while fetching the claims report. Please contact support.";
    // Optionally log the error
    // file_put_contents("error_log.txt", $e->getMessage(), FILE_APPEND);
}

echo json_encode($response);
