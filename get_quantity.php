<?php
include('include/db.php');

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $product_id = $_POST['product_id'];
    $color = $_POST['color'] ?? '';
    $size = $_POST['size'] ?? '';

    if (!empty($color) && !empty($size)) {
        // Variant product stock check
        $stmt = $conn->prepare("SELECT quantity FROM product_variants WHERE product_id = ? AND color = ? AND size = ?");
        $stmt->bind_param("iss", $product_id, $color, $size);
        $stmt->execute();
        $result = $stmt->get_result();

        if ($row = $result->fetch_assoc()) {
            echo $row['quantity'];
        } else {
            echo 0;
        }
    } else {
        // Simple product stock check
        $stmt = $conn->prepare("SELECT stock_quantity FROM products WHERE p_id = ?");
        $stmt->bind_param("i", $product_id);
        $stmt->execute();
        $result = $stmt->get_result();

        if ($row = $result->fetch_assoc()) {
            echo $row['stock_quantity'];
        } else {
            echo 0;
        }
    }
}
?>
