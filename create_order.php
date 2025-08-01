<?php
session_start();
require('vendor/autoload.php');  // Razorpay PHP Library include
include("include/db.php");

use Razorpay\Api\Api;

// Razorpay Credentials
$key_id = "YOUR_RAZORPAY_KEY";
$key_secret = "YOUR_RAZORPAY_SECRET";

if (!isset($_SESSION['user_id'])) {
    echo json_encode(["status" => "login_required"]);
    exit();
}

$user_id = $_SESSION['user_id'];

// Cart total calculate karo
$subtotal = 0;
$cart_query = "SELECT cart.*, products.discounted_price FROM cart 
               JOIN products ON cart.product_id = products.p_id WHERE cart.user_id = ?";
$stmt_cart = $conn->prepare($cart_query);
$stmt_cart->bind_param("i", $user_id);
$stmt_cart->execute();
$result_cart = $stmt_cart->get_result();

while ($row = $result_cart->fetch_assoc()) {
    $subtotal += ($row['discounted_price'] * $row['quantity']);
}

// Razorpay API initialize
$api = new Api($key_id, $key_secret);
$orderData = [
    'receipt'         => 'ORD' . time(),
    'amount'          => $subtotal * 100, // Amount in paisa (₹1 = 100 paisa)
    'currency'        => 'INR',
    'payment_capture' => 1 // Auto capture
];

$order = $api->order->create($orderData);

if ($order) {
    echo json_encode([
        "status" => "success",
        "order_id" => $order['id'],
        "amount" => $order['amount']
    ]);
} else {
    echo json_encode(["status" => "error"]);
}
?>
