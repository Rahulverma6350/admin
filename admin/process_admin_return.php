<?php
session_start();
include 'include/db.php';

// Approve Return Request
if (isset($_POST['approve_return'])) {
    $request_id = (int) $_POST['request_id'];
    $order_id = (int) $_POST['order_id'];
    $product_id = (int) $_POST['product_id'];
    $color = trim($_POST['color']);
    $size = trim($_POST['size']);
    $quantity = (int) $_POST['quantity'];

    if ($quantity <= 0) {
        $_SESSION['error'] = "Invalid return quantity!";
        header("Location: admin_return_requests.php");
        exit();
    }

    // Check if product variant exists
    $stmt = $conn->prepare("SELECT id, quantity FROM product_variants WHERE product_id = ? AND LOWER(color) = LOWER(?) AND LOWER(size) = LOWER(?)");
    $stmt->bind_param("iss", $product_id, $color, $size);
    $stmt->execute();
    $variant_result = $stmt->get_result();

    if ($variant_result->num_rows > 0) {
        // Variant Product (fashion item)
        $variant = $variant_result->fetch_assoc();
        $new_quantity = $variant['quantity'] + $quantity;

        $update_stmt = $conn->prepare("UPDATE product_variants SET quantity = ? WHERE id = ?");
        $update_stmt->bind_param("ii", $new_quantity, $variant['id']);
        $update_stmt->execute();

        $stock_update_message = "Stock restored for Variant Product (Color: $color, Size: $size)";
    } else {
        // Simple Product
        $update_stmt = $conn->prepare("UPDATE products SET stock_quantity = stock_quantity + ? WHERE p_id = ?");
        $update_stmt->bind_param("ii", $quantity, $product_id);
        $update_stmt->execute();

        $stock_update_message = "Stock restored for Simple Product (Product ID: $product_id)";
    }

    // Update Return Request Status
    $approve_stmt = $conn->prepare("UPDATE return_requests SET status = 'Approved', refund_status = 'Pending' WHERE id = ?");
    $approve_stmt->bind_param("i", $request_id);
    $approve_stmt->execute();

    $_SESSION['success'] = "Return Approved! $stock_update_message. Refund is now Pending.";
    header("Location: admin_return_requests.php");
    exit();
}

// ❌ Reject Return Request
if (isset($_POST['reject_return'])) {
    $request_id = (int) $_POST['request_id'];

    $reject_stmt = $conn->prepare("UPDATE return_requests SET status = 'Rejected' WHERE id = ?");
    $reject_stmt->bind_param("i", $request_id);
    $reject_stmt->execute();

    $_SESSION['success'] = "Return Request Rejected!";
    header("Location: admin_return_requests.php");
    exit();
}

// ✅ Process Refund
if (isset($_POST['process_refund'])) {
    $request_id = (int) $_POST['request_id'];
    $order_id = (int) $_POST['order_id'];

    // Fetch product_id
    $stmt = $conn->prepare("SELECT product_id FROM return_requests WHERE id = ?");
    $stmt->bind_param("i", $request_id);
    $stmt->execute();
    $product_result = $stmt->get_result();

    if ($product_result->num_rows == 0) {
        $_SESSION['error'] = "Product not found!";
        header("Location: admin_return_requests.php");
        exit();
    }

    $product = $product_result->fetch_assoc();
    $product_id = (int) $product['product_id'];

    // Fetch price and quantity
    $stmt = $conn->prepare("SELECT price, quantity FROM order_items WHERE order_id = ? AND product_id = ?");
    $stmt->bind_param("ii", $order_id, $product_id);
    $stmt->execute();
    $order_result = $stmt->get_result();

    if ($order_result->num_rows == 0) {
        $_SESSION['error'] = "Product price not found!";
        header("Location: admin_return_requests.php");
        exit();
    }

    $order_item = $order_result->fetch_assoc();
    $refund_amount = $order_item['price'] * $order_item['quantity'];

    // Fetch user_id
    $stmt = $conn->prepare("SELECT user_id, shipping_status FROM orders WHERE order_id = ?");
    $stmt->bind_param("i", $order_id);
    $stmt->execute();
    $order_data_result = $stmt->get_result();

    if ($order_data_result->num_rows == 0) {
        $_SESSION['error'] = "Order not found!";
        header("Location: admin_return_requests.php");
        exit();
    }

    $order_data = $order_data_result->fetch_assoc();
    $user_id = (int) $order_data['user_id'];
    $shipping_status = $order_data['shipping_status'];

    // Only allow refund if delivered
    if ($shipping_status != "Delivered") {
        $_SESSION['error'] = "Refund can only be processed after order delivery!";
        header("Location: admin_return_requests.php");
        exit();
    }

    // Insert refund record
    $stmt = $conn->prepare("INSERT INTO refunds (return_id, order_id, user_id, product_id, refund_amount, refund_status, refund_date) VALUES (?, ?, ?, ?, ?, 'Processed', NOW())");
    $stmt->bind_param("iiiid", $request_id, $order_id, $user_id, $product_id, $refund_amount);
    
    if ($stmt->execute()) {
        // Update return request refund_status
        $update_stmt = $conn->prepare("UPDATE return_requests SET refund_status = 'Processed' WHERE id = ?");
        $update_stmt->bind_param("i", $request_id);
        $update_stmt->execute();

        $_SESSION['success'] = "Refund Processed Successfully!";
    } else {
        $_SESSION['error'] = "Error processing refund: " . $stmt->error;
    }

    header("Location: admin_return_requests.php");
    exit();
}
?>
