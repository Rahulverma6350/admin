<?php
session_start();
include("include/db.php");

// Order ID ko GET method se le rahe hain
if (isset($_GET['order_id'])) {
    $order_id = intval($_GET['order_id']);

    // Fetch Order Details
    $order_query = "SELECT o.order_id, o.order_date, o.total_amount, o.payment_status, u.name 
                    FROM orders o
                    JOIN user_reg u ON o.user_id = u.id
                    WHERE o.order_id = ?";
    $stmt = $conn->prepare($order_query);
    $stmt->bind_param("i", $order_id);
    $stmt->execute();
    $order_result = $stmt->get_result()->fetch_assoc();

    // Fetch Order Items
  // Fetch Order Items (Image Added)
$items_query = "SELECT oi.product_id, p.product_name, oi.quantity, oi.price, p.product_image 
FROM order_items oi
JOIN products p ON oi.product_id = p.p_id
WHERE oi.order_id = ?";
$stmt_items = $conn->prepare($items_query);
$stmt_items->bind_param("i", $order_id);
$stmt_items->execute();
$items_result = $stmt_items->get_result();

} else {
    echo "<p>Invalid Order ID.</p>";
    exit();
}
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <title>Invoice #<?php echo $order_result['order_id']; ?></title>
    <style>
        body { font-family: Arial, sans-serif; }
        .invoice-container { max-width: 800px; margin: 20px auto; padding: 20px; border: 1px solid #ddd; }
        table { width: 100%; border-collapse: collapse; }
        th, td { padding: 10px; border: 1px solid #ddd; text-align: left; }
        .invoice-header { text-align: center; margin-bottom: 20px; }
        .total-amount { font-weight: bold; text-align: right; margin-top: 20px; }
    </style>
</head>
<body>

<div class="invoice-container">
    <div class="invoice-header">
        <h1>Invoice #<?php echo $order_result['order_id']; ?></h1>
        <p><strong>Customer Name:</strong> <?php echo $order_result['name']; ?></p>
        <p><strong>Order Date:</strong> <?php echo $order_result['order_date']; ?></p>
        <p><strong>Payment Status:</strong> <?php echo $order_result['payment_status']; ?></p>
    </div>

    <h3>Products Ordered:</h3>
    <table>
        <tr>
        <th>Image</th>
            <th>Product Name</th>
            <th>Quantity</th>
            <th>Price (₹)</th>
        </tr>
        <?php while ($item = $items_result->fetch_assoc()) { ?>
        <tr>
        <td>
            <img src="admin/img/<?php echo $item['product_image'];?>" 
                 alt="Product Image" 
                 style="width: 70px; height: 60px; object-fit: cover;">
        </td>
            <td><?php echo $item['product_name']; ?></td>
            <td><?php echo $item['quantity']; ?></td>
            <td>₹<?php echo $item['price']; ?></td>
        </tr>
        <?php } ?>
    </table>

    <div class="total-amount">
        <h3>Total Amount: ₹<?php echo $order_result['total_amount']; ?></h3>
    </div>

    <div style="text-align: center; margin-top: 20px;">
        <a href="my-account.php" class="btn btn-primary">Back to Orders</a>
    </div>
</div>

</body>
</html>
