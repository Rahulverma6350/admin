<?php
session_start();
require 'include/db.php'; // Your DB connection file
if (!isset($_SESSION['user_id'])) {
    echo "<div class='alert alert-danger'>User not logged in.</div>";
    exit;
}
$user_id = $_SESSION['user_id'];
$index = intval($_POST['index']);
// Step 5: Update the database
$update = $conn->prepare("DELETE FROM new_address WHERE id = ?");
$update->bind_param("i", $index);
if ($update->execute()) {
      header("Location: checkout.php");
    exit;
} else {
    echo "<div class='alert alert-danger'>Failed to update database.</div>";
}
