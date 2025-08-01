<?php
include("include/db.php");

// if (isset($_POST['submit'])) {
//     $username = trim($_POST['username']);
//     $useremail = trim($_POST['useremail']);
//     $password = trim($_POST['userpassw']); // No hashing
//     $userphone = trim($_POST['userphone']);

//     $sql = "INSERT INTO user_reg(name, email, passw,phone) VALUES (?, ?, ?,?)";
//     $stmt = $conn->prepare($sql);
//     $stmt->bind_param("ssss", $username, $useremail, $password, $userphone);

//     if ($stmt->execute()) {
//         echo "<script>alert('Registration successful! Please login.'); window.location.href='login.php';</script>";
//     } else {
//         echo "<script>alert('Error registering user! Try again.');</script>";
//     }
// }

if (isset($_POST['submit'])) {
    $username = trim($_POST['username']);
    $useremail = trim($_POST['useremail']);
    $password = trim($_POST['userpassw']);
    $userphone = trim($_POST['userphone']);

    // Check if email or phone already exists
    $checkSql = "SELECT * FROM user_reg WHERE email = ? OR phone = ?";
    $checkStmt = $conn->prepare($checkSql);
    $checkStmt->bind_param("ss", $useremail, $userphone);
    $checkStmt->execute();
    $result = $checkStmt->get_result();

    if ($result->num_rows > 0) {
        echo "<script>alert('Email or phone number already exists. Please use a different one.');</script>";
    } else {
        // Hash the password before storing
        $hashedPassword = password_hash($password, PASSWORD_DEFAULT);

        $sql = "INSERT INTO user_reg(name, email, passw, phone) VALUES (?, ?, ?, ?)";
        $stmt = $conn->prepare($sql);
        $stmt->bind_param("ssss", $username, $useremail, $hashedPassword, $userphone);

        if ($stmt->execute()) {
            echo "<script>alert('Registration successful! Please login.'); window.location.href='login.php';</script>";
        } else {
            echo "<script>alert('Error registering user! Try again.');</script>";
        }
    }
}

?>


<!DOCTYPE html>
<html lang="en">

<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Registration - Furea</title>
    <style>
        /* General body and container styles */
        * {
            margin: 0;
            padding: 0;
            box-sizing: border-box;
        }

        body {
            font-family: 'Arial', sans-serif;
            background-color: #fafafa;
            display: flex;
            justify-content: center;
            align-items: center;
            min-height: 100vh;
        }

        /* Main container holding forms */
        .main-container {
            display: flex;
            justify-content: center;
            align-items: center;
            width: 100%;
            height: 100%;
        }

        .form-container {
            background-color: white;
            border-radius: 8px;
            box-shadow: 0 4px 6px rgba(0, 0, 0, 0.1);
            padding: 30px;
            width: 350px;
        }

        /* Form Header */
        .form-header {
            text-align: center;
            margin-bottom: 20px;
        }

        .form-title {
            font-size: 28px;
            font-weight: bold;
            color: #405de6;
        }

        .form-subtitle {
            font-size: 14px;
            color: #888;
        }

        /* Input fields styling */
        .input-field {
            width: 100%;
            padding: 10px;
            margin: 10px 0;
            border: 1px solid #ddd;
            border-radius: 5px;
            font-size: 14px;
        }

        .input-field:focus {
            outline: none;
            border-color: #405de6;
        }

        /* Submit buttons */
        .submit-button {
            width: 100%;
            padding: 10px;
            background-color: #405de6;
            color: white;
            border: none;
            border-radius: 5px;
            font-size: 16px;
            cursor: pointer;
        }

        .submit-button:hover {
            background-color: #3857b6;
        }

        /* Footer and link styles */
        .form-footer {
            text-align: center;
            margin-top: 20px;
        }

        .footer-text {
            font-size: 14px;
        }

        .footer-link {
            color: #405de6;
            text-decoration: none;
        }

        .footer-link:hover {
            text-decoration: underline;
        }

        /* Responsive design for mobile devices */
        @media (max-width: 600px) {
            .form-container {
                width: 90%;
            }

            .input-field,
            .submit-button {
                font-size: 16px;
            }

            .form-title {
                font-size: 24px;
            }
        }
    </style>

</head>

<body>

    <div class="main-container">
        <div class="form-container">
            <div class="form-header">
                <h1 class="form-title">Furea</h1>
            </div>

            <!-- Registration Form -->
            <form method="POST">
                <!-- Name -->
                <div class="mb-3">
                    <label class="form-label">Full Name</label>
                    <input type="text" placeholder="Enter your name" name="username" required class="form-control">
                </div>

                <!-- Email -->
                <div class="mb-3">
                    <label class="form-label">Email Address</label>
                    <input type="email" placeholder="Enter your email" name="useremail" required class="form-control">
                </div>

                <!-- Password -->
                <div class="mb-3">
                    <label class="form-label">Password</label>
                    <div class="input-group">
                        <input type="password" placeholder="Enter your password" name="userpassw" id="passwordField" required class="form-control">
                        <span class="input-group-text" id="togglePassword">👁️</span>
                    </div>
                </div>

                <!-- Confirm Password -->
                <div class="mb-3">
                    <label class="form-label">Confirm Password</label>
                    <div class="input-group">
                        <input type="password" placeholder="Confirm your password" name="userpassw_confirm" id="confirmPasswordField" required class="form-control">
                        <span class="input-group-text" id="toggleConfirmPassword">👁️</span>
                    </div>
                </div>


                <!-- Phone -->
                <div class="mb-3">
                    <label class="form-label">Phone Number</label>
                    <input type="text" placeholder="Enter your phone number" name="userphone" required class="form-control">
                </div>

                <button type="submit" name="submit" class="btn btn-login w-100">Register</button>
            </form>


            <script>
                const togglePassword = document.getElementById("togglePassword");
                const toggleConfirmPassword = document.getElementById("toggleConfirmPassword");
                const passwordField = document.getElementById("passwordField");
                const confirmPasswordField = document.getElementById("confirmPasswordField");

                togglePassword.addEventListener("click", function() {
                    const type = passwordField.type === "password" ? "text" : "password";
                    passwordField.type = type;
                    this.textContent = type === "password" ? "👁️" : "🙈";
                });

                toggleConfirmPassword.addEventListener("click", function() {
                    const type = confirmPasswordField.type === "password" ? "text" : "password";
                    confirmPasswordField.type = type;
                    this.textContent = type === "password" ? "👁️" : "🙈";
                });
            </script>


            <div class="form-footer">
                <p class="footer-text">Already have an account? <a href="login.php" class="footer-link">Login</a></p>
            </div>
        </div>
    </div>


    <style>
        /* Body & Container */
        body {
            margin: 0;
            padding: 0;
            font-family: "Segoe UI", Tahoma, Geneva, Verdana, sans-serif;
            background: #f4f6f8;
            display: flex;
            justify-content: center;
            align-items: center;
            min-height: 100vh;
        }

        .register-container {
            background: #fff;
            padding: 2rem;
            border-radius: 16px;
            box-shadow: 0 6px 24px rgba(0, 0, 0, 0.1);
            width: 100%;
            max-width: 450px;
        }

        /* Heading */
        .register-container h2 {
            text-align: center;
            margin-bottom: 1.5rem;
            color: #333;
        }

        /* Labels & Inputs */
        .form-label {
            font-weight: 500;
            margin-bottom: 0.5rem;
            display: block;
            color: #555;
        }

        .form-control {
            width: 100%;
            padding: 10px 14px;
            border-radius: 8px;
            border: 1px solid #ccc;
            font-size: 1rem;
            outline: none;
            margin-bottom: 1rem;
            transition: border-color 0.3s ease;
        }

        .form-control:focus {
            border-color: #007bff;
            box-shadow: 0 0 0 3px rgba(0, 123, 255, 0.1);
        }

        /* Input Group (for eye icon) */
        .input-group {
            position: relative;
            width: 100%;
        }

        .input-group input.form-control {
            padding-right: 40px;
            /* Room for icon */
        }

        .input-group-text {
            position: absolute;
            right: 12px;
            top: 50%;
            transform: translateY(-50%);
            font-size: 18px;
            cursor: pointer;
            color: #6c757d;
            user-select: none;
            background: transparent;
            border: none;
            padding: 0;
        }

        /* Button */
        .btn-login {
            width: 100%;
            padding: 12px;
            background-color: #007bff;
            color: #fff;
            font-size: 1rem;
            border: none;
            border-radius: 8px;
            cursor: pointer;
            transition: background-color 0.3s ease;
        }

        .btn-login:hover {
            background-color: #0056b3;
        }

        /* Footer */
        .form-footer {
            text-align: center;
            margin-top: 1rem;
        }

        .footer-link {
            color: #007bff;
            text-decoration: none;
        }

        .footer-link:hover {
            text-decoration: underline;
        }

        .input-group {
            position: relative;
            width: 100%;
        }

        .input-group input.form-control {
            padding-right: 50px;
            /* Extra space for icon */
        }

        /* Eye Icon Styling */
        .input-group-text {
            position: absolute;
            right: 0px;
            /* top: 50%; */
            transform: translateY(-70%);
            font-size: 18px;
            cursor: pointer;
            color: #333;
            user-select: none;
            background: #fff;
            border: 1px solid #ccc;
            /* border-radius: 50%; */
            width: 46px;
            height: 40px;
            display: flex;
            align-items: center;
            justify-content: center;
            box-shadow: 0 2px 6px rgba(0, 0, 0, 0.1);
            transition: all 0.2s ease-in-out;
        }

        .input-group-text:hover {
            background: #f8f9fa;
        }
    </style>

</body>

</html>