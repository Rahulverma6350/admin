<?php
session_start();
include 'include/db.php';

if (isset($_POST['return_request'])) {
    $order_id = $_POST['order_id'];
    $product_id = $_POST['product_id'];
    $size = $_POST['size'];
    $color = $_POST['color'];
    $user_id = $_SESSION['user_id'];
    $reason = $_POST['reason'];

    // Check if the exact same product with size & color is already requested
    $check_sql = "SELECT * FROM return_requests WHERE order_id = ? AND product_id = ? AND size = ? AND color = ?";
    $stmt = $conn->prepare($check_sql);
    $stmt->bind_param("iiss", $order_id, $product_id, $size, $color);
    $stmt->execute();
    $result = $stmt->get_result();

    if ($result->num_rows == 0) {
        // Insert the return request
    
        $insert_sql = "INSERT INTO return_requests (user_id, order_id, product_id, size, color, reason, status, refund_status) 
               VALUES (?, ?, ?, ?, ?, ?, 'Pending', 'Not Initiated')";
$stmt = $conn->prepare($insert_sql);
$stmt->bind_param("iiisss", $user_id, $order_id, $product_id, $size, $color, $reason);
        $stmt->execute();

        $_SESSION['success'] = "Return Request Submitted!";
    } else {
        $_SESSION['error'] = "Return Request Already Exists!";
    }
    

    $order_id = $_POST['order_id'];
header("Location: my-account.php?order_id=$order_id");
    exit();
}

?>