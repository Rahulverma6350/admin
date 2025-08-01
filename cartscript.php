<?php
session_start();
include("include/db.php");
// Create guest session if needed
if (!isset($_SESSION['user_id'])) {
    if (!isset($_SESSION['guest_session'])) {
        $_SESSION['guest_session'] = session_id(); // Generate guest session ID
    }
    $user_id = null;
    $session_id = $_SESSION['guest_session'];
} else {
    $user_id = $_SESSION['user_id'];
    $session_id = null;
}
if (isset($_POST['product_id'], $_POST['quantity'])) {
    $product_id = intval($_POST['product_id']);
    $quantity = max(1, intval($_POST['quantity'])); // Enforce min = 1
    // Variant check
    $variant_check = $conn->prepare("SELECT COUNT(*) AS variant_exists FROM product_variants WHERE product_id = ?");
    $variant_check->bind_param("i", $product_id);
    $variant_check->execute();
    $variant_result = $variant_check->get_result();
    $is_variant = $variant_result->fetch_assoc()['variant_exists'] > 0;
    // Get variant values
    $color = $_POST['color'] ?? 'default_color';
    $size = $_POST['size'] ?? 'default_size';
    // Check existing cart item
    $stmt = $conn->prepare("SELECT quantity FROM cart WHERE (user_id = ? OR session_id = ?) AND product_id = ? AND color = ? AND size = ?");
    $stmt->bind_param("isiss", $user_id, $session_id, $product_id, $color, $size);
    $stmt->execute();
    $result = $stmt->get_result();
    $existing = $result->fetch_assoc();
    $existing_qty = $existing ? intval($existing['quantity']) : 0;
    // :wrench: DEBUG LOG: Existing Quantity
    error_log("DEBUG: Existing cart quantity for product $product_id ($color, $size): $existing_qty");
    // :white_check_mark: FIX: REPLACE quantity (not add to it)
    $new_quantity = $quantity;
    // :wrench: DEBUG LOG: Final Quantity to Store
    error_log("DEBUG: Final quantity to store for product $product_id: $new_quantity");
    // Check stock
    if ($is_variant) {
        $stock_stmt = $conn->prepare("SELECT quantity FROM product_variants WHERE product_id = ? AND color = ? AND size = ?");
        $stock_stmt->bind_param("iss", $product_id, $color, $size);
    } else {
        $stock_stmt = $conn->prepare("SELECT stock_quantity AS quantity FROM products WHERE p_id = ?");
        $stock_stmt->bind_param("i", $product_id);
    }
    $stock_stmt->execute();
    $stock_data = $stock_stmt->get_result()->fetch_assoc();
    $available = intval($stock_data['quantity']);
    // :wrench: DEBUG LOG: Stock Check
    error_log("DEBUG: Available stock for product $product_id: $available");
    if ($new_quantity > $available) {
        error_log("DEBUG: Requested quantity $new_quantity exceeds available $available");
        echo json_encode(["status" => "error", "message" => "Only $available item(s) available."]);
        exit;
    }
    // Update or Insert
    if ($existing) {
        $update_stmt = $conn->prepare("UPDATE cart SET quantity = ? WHERE (user_id = ? OR session_id = ?) AND product_id = ? AND color = ? AND size = ?");
        $update_stmt->bind_param("iisiss", $new_quantity, $user_id, $session_id, $product_id, $color, $size);
        $update_stmt->execute();
        error_log("DEBUG: Updated cart item for product $product_id to quantity $new_quantity");
    } else {
        $insert_stmt = $conn->prepare("INSERT INTO cart (user_id, session_id, product_id, color, size, quantity) VALUES (?, ?, ?, ?, ?, ?)");
        $insert_stmt->bind_param("isissi", $user_id, $session_id, $product_id, $color, $size, $quantity);
        $insert_stmt->execute();
        error_log("DEBUG: Inserted new cart item for product $product_id with quantity $quantity");
    }
    echo json_encode(["status" => "success"]);
}
?>