<?php
include('include/db.php');
if (isset($_GET['review_id'])) {
    $review_id = (int)$_GET['review_id'];
    // Make sure the review exists and is linked to a valid product
    $delete_query = "DELETE FROM products_review WHERE id = ?";
    $stmt = mysqli_prepare($conn, $delete_query);
    mysqli_stmt_bind_param($stmt, "i", $review_id);
    if (mysqli_stmt_execute($stmt)) {
        echo "<p>Review deleted successfully!</p>";
        // Optionally redirect back to the reviews page
        header("Location: viewreview.php");
    } else {
        echo "<p style='color: red;'>Error deleting review. Please try again later.</p>";
    }
    mysqli_stmt_close($stmt);
}
?>