<?php
session_start();
include('include/db.php');
if (isset($_POST['prductId'])) {
    $productId = $_POST['prductId'];
    // Always unset the wishlist_added flag
    $_SESSION['wishlist_added_' . $productId] = false;
    // Check if user is logged in
    if (isset($_SESSION['user_id'])) {
        $userId = $_SESSION['user_id'];
        // Remove from database for logged in user
        $sql = "DELETE FROM wishlist WHERE product_id = '$productId' AND user_id = '$userId'";
        $result = mysqli_query($conn, $sql);
        if ($result) {
            echo "Product successfully removed from wishlist.";
        } else {
            echo "Error removing product.";
        }
    } else {
        // If not logged in, remove from SESSION wishlist
        if (isset($_SESSION['wishlist'])) {
            // Remove product from session wishlist
            if (($key = array_search($productId, $_SESSION['wishlist'])) !== false) {
                unset($_SESSION['wishlist'][$key]);
                echo "Product removed from wishlist (guest).";
            } else {
                echo "Product not found in guest wishlist.";
            }
        } else {
            echo "No wishlist found for guest.";
        }
    }
}
?>