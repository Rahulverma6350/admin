<?php
session_start();
include 'include/db.php';

if (isset($_POST['mark_paid'])) {
    $order_id = $_POST['order_id'];

    // ✅ Update Payment Status
    $update_sql = "UPDATE orders SET payment_status = 'Paid' WHERE order_id = ?";
    $stmt = $conn->prepare($update_sql);
    $stmt->bind_param("i", $order_id);

    if ($stmt->execute()) {
        $_SESSION['success'] = "COD Payment Marked as Paid!";
    } else {
        $_SESSION['error'] = "Error updating payment: " . $stmt->error;
    }

    header("Location: admin_orders.php");
    exit();
}
?>
