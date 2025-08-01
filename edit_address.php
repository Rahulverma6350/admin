<?php
session_start();
require 'include/db.php'; // :white_check_mark: Make sure your DB connection is correct
if (!isset($_SESSION['user_id'])) {
    echo "User not logged in.";
    exit;
}
$user_id = $_SESSION['user_id'];
$index = $_GET['index'];
// Fetch user info from the database
$stmt = $conn->prepare("select * FROM new_address
          WHERE id = ?");
$stmt->bind_param("i", $index);
$stmt->execute();
$result = $stmt->get_result();
if ($result->num_rows === 0) {
    echo "User not found.";
    exit;
}
$user = $result->fetch_assoc();
// Parse address info
 $current_address = isset($user['address']) ? $user['address'] : "";
$current_city    = isset($user['city']) ? $user['city'] : "";
$current_country = isset($user['country']) ? $user['country'] : "";
$current_postal   = isset($user['postal_code']) ? $user['postal_code'] : "";
$current_name     = isset($user['name']) ? $user['name'] : "";
$current_phone    = isset($user['phone']) ? $user['phone'] : "";

?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <title>Edit Address</title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/css/bootstrap.min.css" rel="stylesheet">
    <style>
        body {
            background: #F4F7FC;
            font-family: 'Segoe UI', Tahoma, Geneva, Verdana, sans-serif;
        }
        .custom-form {
            background: white;
            padding: 40px;
            border-radius: 15px;
            box-shadow: 0 10px 25px rgba(0, 0, 0, 0.1);
            max-width: 600px;
            margin: 50px auto;
        }
        .form-title {
            font-weight: bold;
            margin-bottom: 30px;
            text-align: center;
            color: #0D6EFD;
        }
        .form-floating>label {
            color: #6C757D;
        }
        .btn-gradient {
            background: linear-gradient(135deg, #0D6EFD, #6610F2);
            color: white;
            border: none;
        }
        .btn-gradient:hover {
            background: linear-gradient(135deg, #6610F2, #0D6EFD);
            box-shadow: 0 4px 12px rgba(0, 0, 0, 0.15);
        }
    </style>
</head>
<body>
    <div class="custom-form">
        <h4 class="form-title"><i class="bi bi-pencil-square"></i> Edit Address Details</h4>
        <form action="update_address.php" method="POST">
            <input type="hidden" name="index" value="<?= $index ?>">
            <div class="form-floating mb-3">
                <input type="text" name="name" class="form-control" id="floatingAddress" value="<?= htmlspecialchars($current_name) ?>" required>
                <label for="floatingAddress">name</label>
            </div>
            <div class="form-floating mb-3">
                <input type="text" name="phone" class="form-control" id="floatingAddress" value="<?= htmlspecialchars($current_phone) ?>" required>
                <label for="floatingAddress">phone</label>
            </div>
            <div class="form-floating mb-3">
                <input type="text" name="address" class="form-control" id="floatingAddress" value="<?= htmlspecialchars($current_address) ?>" required>
                <label for="floatingAddress">Address</label>
            </div>
            <div class="form-floating mb-3">
                <input type="text" name="city" class="form-control" id="floatingCity" value="<?= htmlspecialchars($current_city) ?>" required>
                <label for="floatingCity">City</label>
            </div>
            <div class="form-floating mb-3">
                <input type="text" name="country" class="form-control" id="floatingCountry" value="<?= htmlspecialchars($current_country) ?>" required>
                <label for="floatingCountry">Country</label>
            </div>
            <div class="form-floating mb-4">
                <input type="text" name="postal_code" class="form-control" id="floatingPostal" value="<?= htmlspecialchars($current_postal) ?>" required>
                <label for="floatingPostal">Postal Code</label>
            </div>
            <div class="d-flex justify-content-between">
                <button type="submit" class="btn btn-gradient px-4">Update</button>
                <a href="checkout.php" class="btn btn-outline-secondary">Cancel</a>
            </div>
        </form>
    </div>
    <!-- Optionally add Bootstrap Icons -->
    <link href="https://cdn.jsdelivr.net/npm/bootstrap-icons@1.10.5/font/bootstrap-icons.css" rel="stylesheet">
</body>
</html>