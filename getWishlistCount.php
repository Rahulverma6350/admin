<?php
session_start();
include('include/db.php');
// Ensure session ID exists for guests
if (!isset($_SESSION['session_id'])) {
    $_SESSION['session_id'] = session_id();
}
$sessionId = $_SESSION['session_id'];
// Initialize the response array
$response = array();
if (isset($_SESSION['user_id'])) {
    // Logged-in user
    $userId = $_SESSION['user_id'];
    $sql = "SELECT COUNT(*) AS count FROM wishlist WHERE user_id = '$userId'";
} else {
    // Guest user using session_id
    $sql = "SELECT COUNT(*) AS count FROM wishlist WHERE session_id = '$sessionId'";
}
$result = mysqli_query($conn, $sql);
if ($result) {
    $row = mysqli_fetch_assoc($result);
    $response['wc'] = $row['count'];
} else {
    $response['wc'] = 0;
}
echo json_encode($response);
?>