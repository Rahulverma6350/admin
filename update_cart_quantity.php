<?php
session_start();
include("include/db.php");

if (!isset($_POST['product_id'], $_POST['quantity'])) {
    echo json_encode(["status" => "error", "message" => "Invalid input"]);
    exit;
}

$product_id = intval($_POST['product_id']);
$quantity = max(1, intval($_POST['quantity']));
$color = $_POST['color'] ?? 'default_color';
$size = $_POST['size'] ?? 'default_size';

$user_id = $_SESSION['user_id'] ?? null;
$session_id = $_SESSION['guest_session'] ?? session_id();
if (!$user_id && !isset($_SESSION['guest_session'])) {
    $_SESSION['guest_session'] = $session_id;
}

// Variant stock check
$variant_stmt = $conn->prepare("SELECT quantity FROM product_variants WHERE product_id = ? AND color = ? AND size = ?");
$variant_stmt->bind_param("iss", $product_id, $color, $size);
$variant_stmt->execute();
$variant_result = $variant_stmt->get_result();
$variant = $variant_result->fetch_assoc();

if ($variant) {
    $stock = intval($variant['quantity']);
} else {
    $product_stmt = $conn->prepare("SELECT stock_quantity AS quantity FROM products WHERE p_id = ?");
    $product_stmt->bind_param("i", $product_id);
    $product_stmt->execute();
    $product_result = $product_stmt->get_result();
    $stock_data = $product_result->fetch_assoc();
    $stock = intval($stock_data['quantity']);
}

if ($quantity > $stock) {
    echo json_encode(["status" => "error", "message" => "Only $stock available"]);
    exit;
}

// Update cart
if ($user_id) {
    $update = $conn->prepare("UPDATE cart SET quantity = ? WHERE user_id = ? AND product_id = ? AND color = ? AND size = ?");
    $update->bind_param("iiiss", $quantity, $user_id, $product_id, $color, $size);
} else {
    $update = $conn->prepare("UPDATE cart SET quantity = ? WHERE session_id = ? AND product_id = ? AND color = ? AND size = ?");
    $update->bind_param("isiss", $quantity, $session_id, $product_id, $color, $size);
}
$update->execute();

echo json_encode(["status" => "success", "message" => "Cart updated"]);
?>

