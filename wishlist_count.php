<?php
session_start();
include "include/db.php"; // Database connection

// Ensure session ID is set
if (!isset($_SESSION['session_id'])) {
    $_SESSION['session_id'] = session_id();
}

$count = 0;

if (isset($_SESSION['user_id'])) {
    // Logged-in user
    $user_id = $_SESSION['user_id'];

    $query = "SELECT COUNT(*) as count FROM wishlist WHERE user_id = ?";
    $stmt = $conn->prepare($query);
    $stmt->bind_param("i", $user_id);
    $stmt->execute();
    $result = $stmt->get_result();
    $data = $result->fetch_assoc();
    $count = $data['count'];

} else {
    // Guest user
    $session_id = $_SESSION['session_id'];

    $query = "SELECT COUNT(*) as count FROM wishlist WHERE session_id = ?";
    $stmt = $conn->prepare($query);
    $stmt->bind_param("s", $session_id);
    $stmt->execute();
    $result = $stmt->get_result();
    $data = $result->fetch_assoc();
    $count = $data['count'];
}

echo $count;
?>
