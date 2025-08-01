<?php
session_start();
include("include/db.php");
mysqli_report(MYSQLI_REPORT_ERROR | MYSQLI_REPORT_STRICT); // Enable exceptions
try {
    if (!isset($_SESSION['user_id'])) {
        header("Location: login.php");
        exit();
    }
    $user_id = $_SESSION['user_id'];
    if (isset($_POST['cod'])) {
        $total_amount = $_POST['total_amount'];
        $payment_method = "COD";
        $status = "Pending";
        // :white_check_mark: Step 1: Get selected address
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
        // :white_check_mark: Step 2: Insert into orders
        $stmt = $conn->prepare("INSERT INTO orders (user_id, total_amount, payment_method, payment_status, selected_addres, order_date)
                                VALUES (?, ?, ?, ?, ?, NOW())");
        $stmt->bind_param("idsss", $user_id, $total_amount, $payment_method, $status, $selected_address);
        $stmt->execute();
        $order_id = $stmt->insert_id;
        // :white_check_mark: Step 3: Fetch cart items
        $cart_query = "SELECT cart.product_id, cart.quantity, cart.color, cart.size, products.discounted_price
                       FROM cart
                       JOIN products ON cart.product_id = products.p_id
                       WHERE cart.user_id = ?";
        $stmt_cart = $conn->prepare($cart_query);
        $stmt_cart->bind_param("i", $user_id);
        $stmt_cart->execute();
        $result_cart = $stmt_cart->get_result();
        while ($row = $result_cart->fetch_assoc()) {
            $product_id = $row['product_id'];
            $quantity = $row['quantity'];
            $color = $row['color'];
            $size = $row['size'];
            $price = $row['discounted_price'];
            // :white_check_mark: Insert into order_items
            $stmt_items = $conn->prepare("INSERT INTO order_items (order_id, product_id, quantity, price, color, size)
                                          VALUES (?, ?, ?, ?, ?, ?)");
            $stmt_items->bind_param("iiidss", $order_id, $product_id, $quantity, $price, $color, $size);
            $stmt_items->execute();
            // :white_check_mark: Update stock
            if (empty($color) && empty($size)) {
                // Simple product
                $update_stock = $conn->prepare("UPDATE products SET stock_quantity = stock_quantity - ? WHERE p_id = ?");
                $update_stock->bind_param("ii", $quantity, $product_id);
                $update_stock->execute();
            } else {
                // Variant product
                $update_variant_stock = $conn->prepare("UPDATE product_variants
                                                        SET quantity = quantity - ?
                                                        WHERE product_id = ? AND color = ? AND size = ?");
                $update_variant_stock->bind_param("iiss", $quantity, $product_id, $color, $size);
                $update_variant_stock->execute();
            }
        }
        // :white_check_mark: Step 4: Clear the cart
        $stmt_clear = $conn->prepare("DELETE FROM cart WHERE user_id = ?");
        $stmt_clear->bind_param("i", $user_id);
        $stmt_clear->execute();
        // :white_check_mark: Step 5: Redirect
        header("Location: order_success.php");
        exit();
    }
} catch (Exception $e) {
    echo "<div class='alert alert-danger'>Order failed: " . htmlspecialchars($e->getMessage()) . "</div>";
    // Optional: error_log($e->getMessage());
}