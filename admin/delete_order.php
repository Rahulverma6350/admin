

<?php
session_start();
include('include/db.php');

if (isset($_GET['order_id'])) {
    $order_id = $_GET['order_id'];

    // Delete order items first (to maintain foreign key integrity)
    $delete_order_items = "DELETE FROM order_items WHERE order_id = ?";
    $stmt_items = $conn->prepare($delete_order_items);
    $stmt_items->bind_param("i", $order_id);
    $stmt_items->execute();

    // Delete the order itself
    $delete_order = "DELETE FROM orders WHERE order_id = ?";
    $stmt = $conn->prepare($delete_order);
    $stmt->bind_param("i", $order_id);

    if ($stmt->execute()) {
        echo "<script>alert('Order deleted successfully!'); window.location='admin_orders.php';</script>";
    } else {
        echo "<script>alert('Failed to delete order. Try again!'); window.location='admin_orders.php';</script>";
    }
}
?>
