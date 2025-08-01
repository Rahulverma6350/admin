<?php
session_start();
include("include/db.php");

$user_id = isset($_SESSION['user_id']) ? $_SESSION['user_id'] : null;
$session_id = isset($_SESSION['guest_session']) ? $_SESSION['guest_session'] : null;

$query = "SELECT COUNT(*) AS total_items FROM cart WHERE user_id = ? OR session_id = ?";
$stmt = $conn->prepare($query);
$stmt->bind_param("is", $user_id, $session_id);
$stmt->execute();
$result = $stmt->get_result()->fetch_assoc();

echo json_encode(["success" => true, "count" => $result['total_items'] ?? 0]);
?>
