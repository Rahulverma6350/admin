<?php
session_start();
require 'include/db.php'; // Make sure this file connects to $conn = new mysqli(...)
if (!isset($_SESSION['user_id'])) {
    echo "User not logged in.";
    exit;
}
$user_id = $_SESSION['user_id'];
$index = intval($_POST['index']);
$name = trim($_POST['name']);
$phone = trim($_POST['phone']);
$address = trim($_POST['address']);
$city = trim($_POST['city']);
$country = trim($_POST['country']);
$postal_code = trim($_POST['postal_code']);
$update = $conn->prepare("UPDATE new_address SET name = ?, phone = ?, address = ?, city = ?, country = ?, postal_code = ? WHERE id = ?");
$update->bind_param("ssssssi", $name, $phone, $address, $city, $country, $postal_code, $index);
if ($update->execute()) {
//     print_r($_POST);
// die;
    header("Location: checkout.php?address_saved=1");
    exit;
} else {
    echo "<div class='alert alert-danger'>Failed to update address in DB.</div>";
}
// Fetch existing user data from DB
// $stmt = $conn->prepare("SELECT name, phone, address, city, country, postal_code FROM user_reg WHERE id = ?");
// $stmt->bind_param("i", $user_id);
// $stmt->execute();
// $result = $stmt->get_result();
// if ($result->num_rows === 0) {
//     echo "User not found in database.";
//     exit;
// }
// $user = $result->fetch_assoc();
// // Parse existing fields
// $names = explode(',', $user['name']);
// $phones = explode(',', $user['phone']);
// $addresses = explode(',', $user['address']);
// $cities = explode(',', $user['city']);
// $countries = explode(',', $user['country']);
// $postals = explode(',', $user['postal_code']);
// // Update the specific index
// $names[$index] = $name;
// $phones[$index] = $phone;
// $addresses[$index] = $address;
// $cities[$index] = $city;
// $countries[$index] = $country;
// $postals[$index] = $postal_code;
// // Prepare updated strings
// $updated_name = implode(',', $names);
// $updated_phone = implode(',', $phones);
// $updated_address = implode(',', $addresses);
// $updated_city = implode(',', $cities);
// $updated_country = implode(',', $countries);
// $updated_postal = implode(',', $postals);
// // Update database
// $update = $conn->prepare("UPDATE user_reg SET name = ?, phone = ?, address = ?, city = ?, country = ?, postal_code = ? WHERE id = ?");
// $update->bind_param("ssssssi", $updated_name, $updated_phone, $updated_address, $updated_city, $updated_country, $updated_postal, $user_id);
// if ($update->execute()) {
//     header("Location: checkout.php?address_saved=1");
//     exit;
// } else {
//     echo "<div class='alert alert-danger'>Failed to update address in DB.</div>";
// }
?>