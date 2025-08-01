
<?php
session_start();
include('include/db.php');

if (isset($_GET['order_id'])) {
    $order_id = $_GET['order_id'];

    $order_query = "SELECT * FROM orders WHERE order_id = ?";
    $stmt_order = $conn->prepare($order_query);
    $stmt_order->bind_param("i", $order_id);
    $stmt_order->execute();
    $order = $stmt_order->get_result()->fetch_assoc();

    $items_query = "SELECT oi.*, p.product_name, p.product_image
                     FROM order_items oi
                     JOIN products p ON oi.product_id = p.p_id
                     WHERE oi.order_id = ?";
    $stmt_items = $conn->prepare($items_query);
    $stmt_items->bind_param("i", $order_id);
    $stmt_items->execute();
    $items = $stmt_items->get_result();

        // Fetch Order Details with User Information
        $order_query = "SELECT o.order_id, o.order_date, o.total_amount, o.payment_status, o.shipping_status, u.name, u.phone, u.address
        FROM orders o
        JOIN user_reg u ON o.user_id = u.id
        WHERE o.order_id = ?";
$stmt = $conn->prepare($order_query);
$stmt->bind_param("i", $order_id);
$stmt->execute();
$order_result = $stmt->get_result()->fetch_assoc();
}
?>


<style>
        /* Invoice Container */
        body {
    background-color: #f0f2f5;  /* Light Gray Background */
    font-family: Arial, sans-serif;
    margin: 0;
    padding: 0;
    box-sizing: border-box;
}

/* Invoice Container */
.invoice-container {
    max-width: 800px;
    background-color: #ffffff;
    box-shadow: 0 4px 15px rgba(0, 0, 0, 0.1);
    padding: 30px;
    border-radius: 12px;
    border: 3px solid #4CAF50;
    margin: 50px auto;
}

/* Invoice Header */
.invoice-header {
    text-align: left;
    border-bottom: 2px solid #4CAF50;
    padding-bottom: 15px;
    margin-bottom: 20px;
}

.invoice-header h1 {
    color: #4CAF50;
    font-size: 28px;
    margin-bottom: 10px;
}

/* Progress Bar */
.progress-bar {
    display: flex;
    justify-content: space-between;
    align-items: center;
    margin: 20px 0;
    position: relative;
    border: 2px solid #4CAF50;
    border-radius: 30px;
    background-color: #f9f9f9;
    padding: 10px 0;
}

.progress-step {
    flex: 1;
    text-align: center;
    position: relative;
    font-weight: bold;
    color: #999;
    font-size: 14px;
}

.progress-step.step-completed::after {
    content: '✔';
    position: absolute;
    top: -8px;
    left: 50%;
    transform: translate(-50%, -50%);
    color: #4CAF50;
    font-weight: bold;
    background-color: #fff;
    border: 3px solid #4CAF50;
    border-radius: 50%;
    width: 20px;
    height: 20px;
    line-height: 18px;
    text-align: center;
}

/* Products Table */
table {
    width: 100%;
    border-collapse: collapse;
    margin-top: 20px;
}

table th, table td {
    border: 1px solid #ddd;
    padding: 10px;
    text-align: center;
}

table th {
    background-color: #4CAF50;
    color: #fff;
}

/* Total Amount */
.total-amount {
    background-color: #e7f7e7;
    text-align: right;
    margin-top: 20px;

}

/* Back to Orders Button */
.btn-primary {
    background-color: #2196F3;
    color: #fff;
    padding: 10px 20px;
    text-decoration: none;
    border-radius: 5px;
    display: inline-block;
    margin-top: 15px;
}

.btn-primary:hover {
    background-color: #1976D2;
}


</style>
 <!-- Invoice Section -->
 <?php if ($order_result) { ?>
<!-- Progress Bar -->
<div class="progress-bar">
    <div class="progress-step <?php echo ($order_result['shipping_status'] == 'Pending' || $order_result['shipping_status'] == 'In Progress' || $order_result['shipping_status'] == 'Delivered') ? 'step-completed' : ''; ?>">
        Pending
    </div>
    
    <div class="progress-step <?php echo ($order_result['shipping_status'] == 'In Progress' || $order_result['shipping_status'] == 'Delivered') ? 'step-completed' : ''; ?>">
        In Progress
    </div>
    
    <div class="progress-step <?php echo ($order_result['shipping_status'] == 'Delivered') ? 'step-completed' : ''; ?>">
        Delivered
    </div>
</div>

<div class="invoice-container">
<!-- Invoice Header -->
<div class="invoice-header">
    <h1>Invoice #<?php echo $order_result['order_id']; ?></h1>
    <h4><?php echo $order_result['name']; ?></h4>
    <p>Order ID: 1668266176</p>
    <p>Billing Address: <?php echo $order_result['address']; ?></p>
    <p>Phone no.: <?php echo $order_result['phone']; ?></p>
    <p>Shipping Address: Dhaka, Mirpur12, London, England</p>
    <p>Order Date: <?php echo $order_result['order_date']; ?></p>
    <p><strong>Payment Status:</strong> <?php echo $order_result['payment_status']; ?></p>
    <p><strong>Shipping Status:</strong> <?php echo $order_result['shipping_status']; ?></p>
</div>

<!-- Products Ordered -->
<h3>Products Ordered:</h3>
<table>
    <tr>
        <th>Image</th>
        <th>Product Name</th>
        <th>Quantity</th>
        <th>Color</th>
        <th>Size</th>
        <th>Price (₹)</th>
    </tr>
    <?php 
    $subtotal = 0;
    while ($item = mysqli_fetch_assoc($items)) {
        $subtotal += $item['quantity'] * $item['price'];
    ?>
    <tr>
        <td>
            <img src="img/<?php echo $item['product_image'];?>" 
                alt="Product Image" 
                style="width: 70px; height: 60px; object-fit: cover;">
        </td>
        <td><?php echo $item['product_name']; ?></td>
        <td><?php echo $item['quantity']; ?></td>
        <td><?php echo $item['color']; ?></td>
        <td><?php echo $item['size']; ?></td>
        <td>₹<?php echo $item['price']; ?></td>
    </tr>
    <?php } ?>
</table>

<div class="total-amount">
    <h3>Total Amount: ₹<?php echo $order_result['total_amount']; ?></h3>
</div>

<div style="text-align: center; margin-top: 20px;">
    <a href="admin_orders.php" class="btn btn-primary">Back to Orders</a>
</div>
</div>

<?php } ?>