<?php
session_start();
include("include/db.php");

$product_id = isset($_POST['product_id']) ? intval($_POST['product_id']) : null;
$color = isset($_POST['color']) ? $_POST['color'] : null;
$size = isset($_POST['size']) ? $_POST['size'] : null;

if (!$product_id) {
    echo json_encode(["status" => "error", "message" => "Invalid Product ID"]);
    exit();
}

$user_id = isset($_SESSION['user_id']) ? $_SESSION['user_id'] : null;
$session_id = isset($_SESSION['guest_session']) ? $_SESSION['guest_session'] : null;

if ($user_id) {
    $delete_query = "DELETE FROM cart WHERE user_id = ? AND product_id = ? AND color = ? AND size = ?";
    $stmt = $conn->prepare($delete_query);
    $stmt->bind_param("iiss", $user_id, $product_id, $color, $size);
} else {
    $delete_query = "DELETE FROM cart WHERE session_id = ? AND product_id = ? AND color = ? AND size = ?";
    $stmt = $conn->prepare($delete_query);
    $stmt->bind_param("siss", $session_id, $product_id, $color, $size);
}

if ($stmt->execute()) {
    echo json_encode(["status" => "deleted"]);
} else {
    echo json_encode(["status" => "error", "message" => "Failed to delete product"]);
}

?>