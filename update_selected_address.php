<?php
session_start();
include 'include/db.php';
mysqli_report(MYSQLI_REPORT_ERROR | MYSQLI_REPORT_STRICT); // Enable exceptions for mysqli
try {
    if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['selected_address']) && isset($_SESSION['user_id'])) {
        $user_id = $_SESSION['user_id'];
        $selected_address = htmlspecialchars(trim($_POST['selected_address']));
        // Validate that it's a numeric ID (optional but recommended)
        if (!is_numeric($selected_address)) {
            throw new Exception("Invalid address ID.");
        }
        // // Reset previously selected addresses for this user
        $reset_stmt = $conn->prepare("UPDATE new_address SET selected_address = 0 WHERE user_id = ?");
        $reset_stmt->bind_param("s", $user_id);
        $reset_stmt->execute();
        $reset_stmt->close();
        // Set the selected address
        $stmt = $conn->prepare("UPDATE new_address SET selected_address = 1 WHERE id = ? ");
        $stmt->bind_param("s", $selected_address);
        $stmt->execute();
        $stmt->close();
        // echo "<div class='alert alert-success'>Selected address updated!</div>";
    } else {
        throw new Exception("Invalid request.");
    }
    $conn->close();
} catch (Exception $e) {
    // Friendly error message to user
    echo "<div class='alert alert-danger'>Error: " . htmlspecialchars($e->getMessage()) . "</div>";
    // Optional: Log actual error details for internal debugging (don't show in production)
    // error_log($e->getMessage());
}
?>