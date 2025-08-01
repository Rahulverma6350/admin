<?php
include('include/db.php');

if (isset($_POST['approve'])) {
    $exchange_id = $_POST['exchange_id'];

    // ✅ Get exchange details
    $get_exchange = mysqli_query($conn, "SELECT * FROM exchanges WHERE id = $exchange_id");
    $exchange = mysqli_fetch_assoc($get_exchange);

    $order_id = $exchange['order_id'];
    $product_id = $exchange['product_id'];
    $user_id = $exchange['user_id'];
    $original_color = $exchange['original_color'];
    $original_size = $exchange['original_size'];
    $new_color = $exchange['new_color'];
    $new_size = $exchange['new_size'];
    $qty = $exchange['quantity'];

    // ✅ Step 1: Update exchange status
    mysqli_query($conn, "UPDATE exchanges SET status = 'Approved' WHERE id = $exchange_id");

    // ✅ Step 2: Update `order_items` (size/color)
    mysqli_query($conn, "UPDATE order_items 
                         SET size = '$new_size', color = '$new_color' 
                         WHERE order_id = $order_id AND product_id = $product_id 
                         AND size = '$original_size' AND color = '$original_color'");

    // ✅ Step 3: Reduce stock from new variant
    mysqli_query($conn, "UPDATE product_variants 
                         SET quantity = quantity - $qty 
                         WHERE product_id = $product_id AND size = '$new_size' AND color = '$new_color'");

    // ✅ Step 4: (Optional) Increase stock back to original variant (since returned)
    mysqli_query($conn, "UPDATE product_variants 
                         SET quantity = quantity + $qty 
                         WHERE product_id = $product_id AND size = '$original_size' AND color = '$original_color'");

    echo "<script>alert('Exchange Approved & Order Updated'); window.location.href='admin_exchanges.php';</script>";
}
?>
