<?php
session_start();
include 'include/db.php'; // DB connection

if (isset($_POST['exchange_request'])) {
    $order_id = $_POST['order_id'];
    $product_id = $_POST['product_id'];
    $old_size = $_POST['old_size'];
    $old_color = $_POST['old_color'];
    $new_size = $_POST['new_size'];
    $new_color = $_POST['new_color'];
    $user_id = $_SESSION['user_id'];

    // ✅ Check if exchange already exists for this product
    $check = mysqli_query($conn, "SELECT * FROM exchanges WHERE order_id = '$order_id' AND product_id = '$product_id' AND status != 'Rejected'");
    if (mysqli_num_rows($check) > 0) {
        $_SESSION['exchange_msg'] = "Exchange request already submitted.";
        header("Location: my-account.php?order_id=$order_id");
        exit;
    }

    // ✅ Get ordered quantity
    $order_item_query = mysqli_query($conn, "SELECT quantity FROM order_items WHERE order_id = '$order_id' AND product_id = '$product_id' AND size = '$old_size' AND color = '$old_color'");
    $order_item = mysqli_fetch_assoc($order_item_query);
    if (!$order_item) {
        $_SESSION['exchange_msg'] = "Order item not found.";
        header("Location: my-account.php?order_id=$order_id");
        exit;
    }

    $ordered_quantity = $order_item['quantity'];

    // ✅ Check stock for new variant
    $variant_query = mysqli_query($conn, "SELECT quantity FROM product_variants WHERE product_id = '$product_id' AND color = '$new_color' AND size = '$new_size'");
    $variant = mysqli_fetch_assoc($variant_query);
    if (!$variant) {
        $_SESSION['exchange_msg'] = "Selected variant not found.";
        header("Location: my-account.php?order_id=$order_id");
        exit;
    }

    $available_quantity = $variant['quantity'];

    // ✅ Check if enough stock available
    if ($available_quantity < $ordered_quantity) {
        $_SESSION['exchange_msg'] = "Insufficient stock for the selected variant.";
        header("Location: my-account.php?order_id=$order_id");
        exit;
    }

    // ✅ Insert into exchange table
    $insert = mysqli_query($conn, "INSERT INTO exchanges (order_id, product_id, user_id, original_size, original_color, new_size, new_color, quantity, status, created_at)
        VALUES ('$order_id', '$product_id', '$user_id', '$old_size', '$old_color', '$new_size', '$new_color', '$ordered_quantity', 'Pending', NOW())");

    if ($insert) {
        $_SESSION['exchange_msg'] = "Exchange request submitted successfully.";
    } else {
        $_SESSION['exchange_msg'] = "Error while submitting exchange request.";
    }

    header("Location: my-account.php?order_id=$order_id");
    exit;
}
?>
