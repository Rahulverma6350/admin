<?php
session_start();
include("include/db.php");
require('razorpay-php/Razorpay.php');
use Razorpay\Api\Api;
// Set response as JSON
header('Content-Type: application/json');
// Razorpay API Credentials
$api_key = 'rzp_test_thFNzw5pkSaYe3';
$api_secret = 'LpYOPjdQQmAKZHdiWVjUvTPu';
$api = new Api($api_key, $api_secret);
// Step 1: Create Order in Razorpay
if ($_SERVER['REQUEST_METHOD'] == 'POST' && isset($_POST['create_order'])) {
    if (!isset($_SESSION['user_id'])) {
        echo json_encode(['success' => false, 'message' => 'User not logged in']);
        exit;
    }
    $user_id = $_SESSION['user_id'];
    $amount = $_POST['amount'] * 100; // Razorpay accepts amount in paisa
    $currency = "INR";
    try {
        // Create order in Razorpay
        $razorpayOrder = $api->order->create([
            'receipt' => "order_" . time(),
            'amount' => $amount,
            'currency' => $currency,
            'payment_capture' => 1 // Auto capture payment
        ]);
        // Save Razorpay Order ID in session to insert later
        $razorpay_order_id = $_SESSION['razorpay_order_id'] = $razorpayOrder->id;
        $order_date = date('Y-m-d H:i:s');
        $query = "SELECT * FROM new_address WHERE user_id = ? AND selected_address = 1";
        $stmt = $conn->prepare($query);
        $stmt->bind_param("i", $user_id);
        $stmt->execute();
        $addr = $stmt->get_result()->fetch_assoc();
        if (!$addr) {
            throw new Exception("No selected address found.");
        }
        $fullName = trim($addr["name"]);
        $fullPhone = trim($addr["phone"]);
        $fullAddress = trim($addr["address"]);
        $city = trim($addr["city"]);
        $country = trim($addr["country"]);
        $postal = trim($addr["postal_code"]);
        $selected_address = "$fullName|$fullPhone|$fullAddress|$city|$country|$postal";
        $payment_method = 'Razorpay';
        $payment_status = 'Pending';
        $payment_id = '777777';
        $query = "INSERT INTO orders (user_id, total_amount, payment_method, payment_status, payment_id, razorpay_order_id, order_date, selected_addres)
          VALUES (?, ?, ?, ?, ?, ?, ?, ?)";
        $stmt = $conn->prepare($query);
        // Example values
        $stmt = $conn->prepare($query);
        $stmt->bind_param("idssssss", $user_id, $amount, $payment_method, $payment_status, $payment_id, $razorpay_order_id, $order_date, $selected_address);
        $stmt->execute();
        echo json_encode([
            'success' => true,
            'order_id' => $razorpayOrder->id,
            'amount' => $amount
        ]);
    } catch (Exception $e) {
        echo json_encode(['success' => false, 'message' => 'Error creating order: ' . $e->getMessage()]);
    }
    //exit;
}
// Step 2: Process Payment After User Pays
$data = json_decode(file_get_contents("php://input"), true);
if ($_SERVER['REQUEST_METHOD'] == 'POST' && isset($data['payment_id'], $data['order_id'], $data['signature'], $data['amount'])) {
    if (!isset($_SESSION['user_id'])) {
        echo json_encode(['success' => false, 'message' => 'User not logged in']);
        exit;
    }
    $user_id = $_SESSION['user_id'];
    $payment_id = $data['payment_id'];
    $razorpay_order_id = $_SESSION['razorpay_order_id'] ?? '';
    $signature = $data['signature'];
    $total_amount = $data['amount'];
    try {
        // 1. Verify Signature
        $attributes = [
            'razorpay_order_id' => $razorpay_order_id,
            'razorpay_payment_id' => $payment_id,
            'razorpay_signature' => $signature
        ];
        $api->utility->verifyPaymentSignature($attributes);
        // 2. Fetch Payment
        $payment = $api->payment->fetch($payment_id);
        if ($payment->status !== 'captured') {
            echo json_encode(['success' => false, 'message' => 'Payment not captured']);
            exit;
        }
        // 3. Update order with payment details
        $query = "UPDATE orders SET payment_status = ?, payment_id = ? WHERE razorpay_order_id = ?";
        $stmt = $conn->prepare($query);
        $payment_status = 'Paid';
        $stmt->bind_param("sss", $payment_status, $payment_id, $razorpay_order_id);
        $stmt->execute();
        // 4. Now get the correct order_id using razorpay_order_id
        $query = "SELECT order_id FROM orders WHERE razorpay_order_id = ?";
        $stmt_get_order_id = $conn->prepare($query);
        $stmt_get_order_id->bind_param("s", $razorpay_order_id);
        $stmt_get_order_id->execute();
        $result = $stmt_get_order_id->get_result();
        $row = $result->fetch_assoc();
        if (!$row) {
            echo json_encode(['success' => false, 'message' => 'Order not found']);
            exit;
        }
        $order_id = $row['order_id'];
        // 5. Insert into order_items
        $cart_query = "SELECT cart.product_id, cart.quantity, cart.color, cart.size, products.discounted_price
                       FROM cart
                       JOIN products ON cart.product_id = products.p_id
                       WHERE cart.user_id = ?";
        $stmt_cart = $conn->prepare($cart_query);
        $stmt_cart->bind_param("i", $user_id);
        $stmt_cart->execute();
        $result_cart = $stmt_cart->get_result();
        while ($row = $result_cart->fetch_assoc()) {
            $order_items_query = "INSERT INTO order_items (order_id, product_id, quantity, price, color, size)
                                  VALUES (?, ?, ?, ?, ?, ?)";
            $stmt_items = $conn->prepare($order_items_query);
            $stmt_items->bind_param("iiidss", $order_id, $row['product_id'], $row['quantity'], $row['discounted_price'], $row['color'], $row['size']);
            if (!$stmt_items->execute()) {
                die("Order item insert failed: " . $stmt_items->error);
            }
        }
        // 6. Clear Cart
        $delete_cart = "DELETE FROM cart WHERE user_id = ?";
        $stmt_delete = $conn->prepare($delete_cart);
        $stmt_delete->bind_param("i", $user_id);
        $stmt_delete->execute();
        // 7. Clear session
        unset($_SESSION['razorpay_order_id']);
        echo json_encode(['success' => true, 'message' => 'Order placed successfully!', 'order_id' => $order_id]);
    } catch (Exception $e) {
        echo json_encode(['success' => false, 'message' => 'Payment processing error: ' . $e->getMessage()]);
    }
}