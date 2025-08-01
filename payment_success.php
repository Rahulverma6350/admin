<?php
session_start();
include("include/db.php");

if (!isset($_SESSION['user_id'])) {
    echo json_encode(["status" => "login_required"]);
    exit();
}

$user_id = $_SESSION['user_id'];
$payment_id = $_POST['payment_id'];
$order_id = $_POST['order_id'];

// Order store in database
$insert_order = "INSERT INTO orders (user_id, razorpay_order_id, payment_id, payment_status) VALUES (?, ?, ?, 'Paid')";
$stmt = $conn->prepare($insert_order);
$stmt->bind_param("iss", $user_id, $order_id, $payment_id);

if ($stmt->execute()) {
    echo json_encode(["status" => "success"]);
} else {
    echo json_encode(["status" => "error"]);
}
?>
