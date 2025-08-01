<?php
include('include/db.php');

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $product_id = isset($_POST['product_id']) ? intval($_POST['product_id']) : 0;

    if ($product_id > 0) {
        $stmt = $conn->prepare("SELECT stock_quantity FROM products WHERE p_id = ?");
        $stmt->bind_param("i", $product_id);
        $stmt->execute();
        $result = $stmt->get_result();

        if ($row = $result->fetch_assoc()) {
            echo $row['stock_quantity'];
        } else {
            echo 0;
        }
    } else {
        echo 0;
    }
}
?>
