<?php
include('include/db.php');

// Check if the request is a POST and contains 'id' and 'status'
if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['id']) && isset($_POST['status'])) {
    $productId = intval($_POST['id']);
    $newStatus = intval($_POST['status']);

    // Debugging: Check the values of productId and newStatus
    error_log("Product ID: $productId, New Status: $newStatus");

    // Prepare the SQL query using prepared statements
    $sql = "UPDATE products SET FatalProduct = ? WHERE p_id = ?";
    $stmt = mysqli_prepare($conn, $sql);

    if ($stmt) {
        // Bind parameters
        mysqli_stmt_bind_param($stmt, 'ii', $newStatus, $productId);

        // Execute the query
        if (mysqli_stmt_execute($stmt)) {
            echo 'success';
        } else {
            // Log and display error if query execution fails
            error_log('Error executing query: ' . mysqli_error($conn));
            echo 'error';
        }

        // Close the prepared statement
        mysqli_stmt_close($stmt);
    } else {
        // Log and display error if prepared statement fails
        error_log('Error preparing the statement: ' . mysqli_error($conn));
        echo 'prepare_failed';
    }
} else {
    // Handle invalid requests
    echo 'invalid_request';
}

// Close the database connection
mysqli_close($conn);
?>