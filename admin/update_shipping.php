

<?php
session_start();
include('include/db.php');

if (isset($_POST['update_shipping'])) {
    $order_id = $_POST['order_id'];
    $shipping_status = $_POST['shipping_status'];

    $sql = "UPDATE orders SET shipping_status = ? WHERE order_id = ?";
    $stmt = $conn->prepare($sql);
    $stmt->bind_param("si", $shipping_status, $order_id);

    if ($stmt->execute()) {
        echo "<script>alert('Shipping status updated successfully!'); window.location='admin_orders.php';</script>";
    } else {
        echo "<script>alert('Failed to update shipping status.'); window.location='admin_orders.php';</script>";
    }
}
?>

<!DOCTYPE html>
<html>
<head>
    <title>Update Shipping Status</title>
</head>
<body>
    <form method="POST" action="update_shipping.php">
        <input type="hidden" name="order_id" value="<?php echo $_GET['order_id']; ?>">
        <label>Select Shipping Status:</label>
        <select name="shipping_status" required>
            <option value="Pending">Pending</option>
            <option value="In Progress">In Progress</option>
            <option value="Delivered">Delivered</option>
        </select>
        <button type="submit" name="update_shipping">Update</button>
    </form>
</body>
</html>
