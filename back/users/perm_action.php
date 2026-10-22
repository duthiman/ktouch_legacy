<?php
include('../../inc/connection.php');

header('Content-Type: application/json');

try {
    // Validate input
    if (!isset($_POST['id']) || !is_numeric($_POST['id'])) {
        throw new Exception('Invalid user ID');
    }
    
    if (!isset($_POST['menu']) || !is_array($_POST['menu']) || empty($_POST['menu'])) {
        throw new Exception('No permissions selected');
    }
    
    $user_id = intval($_POST['id']);
    $permissions = array_map('intval', $_POST['menu']);
    $permissions_str = implode(', ', $permissions);
    
    // Validate user exists
    $check_user = mysqli_query($connection, "SELECT user_id FROM kp_users WHERE user_id = $user_id LIMIT 1");
    if (!$check_user || mysqli_num_rows($check_user) === 0) {
        throw new Exception('User not found');
    }
    
    // Update permissions
    $update_query = "UPDATE kp_users SET permissions = '$permissions_str' WHERE user_id = $user_id";
    $result = mysqli_query($connection, $update_query);
    
    if (!$result) {
        throw new Exception('Database error: ' . mysqli_error($connection));
    }
    
    // Success response
    echo json_encode([
        'success' => true,
        'message' => 'Permissions saved successfully!',
        'user_id' => $user_id,
        'permission_count' => count($permissions)
    ]);
    
} catch (Exception $e) {
    // Error response
    http_response_code(400);
    echo json_encode([
        'success' => false,
        'message' => $e->getMessage(),
        'error' => $e->getMessage()
    ]);
}
?>