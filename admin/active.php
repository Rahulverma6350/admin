<?php
include('include/db.php');

// Validate and sanitize inputs
$id = isset($_GET['id']) ? intval($_GET['id']) : 0;
$status = isset($_GET['status']) ? intval($_GET['status']) : 0;

if ($id > 0 && ($status == 0 || $status == 1)) {
    $updateSql = "UPDATE products SET status = $status WHERE p_id = '$id'";
    $updateResult = mysqli_query($conn, $updateSql);

    if ($updateResult) {
        header("Location: viewProduct.php");
        exit();
    } else {
        // Handle the error (optional)
        echo "Error updating record: " . mysqli_error($conn);
    }
} else {
    echo "Invalid parameters.";
}
?>