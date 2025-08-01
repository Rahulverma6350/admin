<?php
session_start();
include("include/db.php");

if (!isset($_SESSION['user_id'])) {
    header("Location: login.php");
    exit();
}

$user_id = $_SESSION['user_id'];

// Fetch cart data
$query = "SELECT cart.*, products.p_id, products.product_name, products.discounted_price, products.product_image 
          FROM cart 
          JOIN products ON cart.product_id = products.p_id 
          WHERE cart.user_id = ?";
$stmt = $conn->prepare($query);
$stmt->bind_param("i", $user_id);
$stmt->execute();
$result = $stmt->get_result();

$grand_total = 0;
?>

<table border="1">
    <tr>
        <th>User ID</th>
        <th>Product ID</th>
        <th>Product Name</th>
        <th>Quantity</th>
        <th>Price</th>
        <th>Total</th>
    </tr>

    <?php 
    if ($result->num_rows > 0) {
        while ($row = $result->fetch_assoc()) {
            $total_price = $row['quantity'] * $row['discounted_price'];
            $grand_total += $total_price;
    ?>
        <tr>
            <td><?php echo $user_id; ?></td>
            <td><?php echo $row['p_id']; ?></td>
            <td><?php echo $row['product_name']; ?></td>
            <td><?php echo $row['quantity']; ?></td>
            <td>£<?php echo $row['discounted_price']; ?></td>
            <td>£<?php echo $total_price; ?></td>
        </tr>
    <?php
        }
    }
    ?>
</table>

<h3>Grand Total: £<?php echo $grand_total; ?></h3>

<form method="POST" action="process_order.php">
    <button type="submit">Place Order</button>
</form>
