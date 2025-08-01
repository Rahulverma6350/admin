<?php
session_start();
include("include/db.php");
$productId = $_POST['prductId'];
$color = $_POST['color'] ?? '';
$size = $_POST['size'] ?? '';
$responseMsg = "Something went wrong!";
$status = "error";
if (isset($_SESSION['user_id'])) {
    $userId = $_SESSION['user_id'];
    $delete = mysqli_query($conn, "DELETE FROM wishlist WHERE product_id='$productId' AND user_id='$userId' AND color='$color' AND size='$size'");
    if ($delete) {
        $responseMsg = "Product removed from wishlist!";
        $_SESSION['wishlist_added_' . $productId] = false;
        $status = "removed";
    }
} else {
    $sessionId = $_SESSION['session_id'];
    $delete = mysqli_query($conn, "DELETE FROM wishlist WHERE product_id='$productId' AND session_id='$sessionId' AND color='$color' AND size='$size'");
    if ($delete) {
        $responseMsg = "Product removed from wishlist!";
        $status = "removed";
    }
}
// Return JSON for flexibility
echo json_encode([
    'status' => $status,
    'message' => $responseMsg
]);
?>