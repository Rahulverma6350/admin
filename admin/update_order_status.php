
<?php
session_start();
include 'include/db.php';

if (isset($_POST['update_status'])) {
    $order_id = $_POST['order_id'];
    $new_status = $_POST['status'];

    // 🔍 Fetch Payment Method & Status
    $query = "SELECT payment_method, payment_status FROM orders WHERE id = ?";
    $stmt = $conn->prepare($query);
    $stmt->bind_param("i", $order_id);
    $stmt->execute();
    $result = $stmt->get_result();
    $order = $result->fetch_assoc();

    // 🛑 Prevent "Delivered" if COD & Payment Pending
    if ($new_status == "Delivered" && $order['payment_method'] == "COD" && $order['payment_status'] == "Pending") {
        $_SESSION['error'] = "COD Order must be marked as Paid before Delivery!";
        header("Location: admin_orders.php");
        exit();
    }

    // ✅ Update Order Status
    $update_query = "UPDATE orders SET order_status = ? WHERE id = ?";
    $stmt = $conn->prepare($update_query);
    $stmt->bind_param("si", $new_status, $order_id);

    if ($stmt->execute()) {
        $_SESSION['success'] = "Order Status Updated Successfully!";
    } else {
        $_SESSION['error'] = "Error updating order: " . $stmt->error;
    }

    header("Location: admin_orders.php");
    exit();
}
?>
