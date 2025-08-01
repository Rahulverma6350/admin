<?php
session_start();
include 'include/db.php';

if (isset($_POST['cancel_return_request'])) {
    $order_id = $_POST['order_id'];
    $product_id = $_POST['product_id'];
    $size = $_POST['size'];
    $color = $_POST['color'];
    $user_id = $_SESSION['user_id'];

    // Delete the pending return request
    $delete_sql = "DELETE FROM return_requests 
                   WHERE user_id = ? AND order_id = ? AND product_id = ? AND size = ? AND color = ? AND status = 'Pending'";
    $stmt = $conn->prepare($delete_sql);
    $stmt->bind_param("iiiss", $user_id, $order_id, $product_id, $size, $color);
    if ($stmt->execute()) {
        $_SESSION['success'] = "Return Request Cancelled Successfully!";
    } else {
        $_SESSION['error'] = "Failed to cancel return request!";
    }

    $order_id = $_POST['order_id'];
    header("Location: my-account.php?order_id=$order_id");
        exit();
}
?>
