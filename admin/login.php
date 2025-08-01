<?php
session_start();

include('include/db.php');

if (isset($_POST['submit'])) {
    $email = trim($_POST['email']);
    $user_pass = trim($_POST['password']);

    // Check if fields are empty
    if (empty($email) || empty($user_pass)) {
        echo '<div class="alert alert-warning">Please enter both email and password.</div>';
    } else {
        // Prepare statement to check if email exists
        $sql = "SELECT id, email, password FROM admin WHERE email = ?";
        $stmt = mysqli_prepare($conn,$sql);
        mysqli_stmt_bind_param($stmt, "s", $email);
        mysqli_stmt_execute($stmt);
        $result = mysqli_stmt_get_result($stmt);

        if (mysqli_num_rows($result) > 0) {
            $row = mysqli_fetch_assoc($result);
            
            // Verify hashed password
            if (password_verify($user_pass, $row['password'])) {
                $_SESSION['email'] = $row['email'];  // Store user email in session
                $_SESSION['loginSessionId'] = $row['id'];
                // $_SESSION['loginSessionId'] = $row['id'];
                header('Location: index.php');
                exit;
            } else {
                echo '<div class="alert alert-danger">Incorrect password. Please try again.</div>';
            }
        } else {
            echo '<div class="alert alert-danger">Email not found. Please register first.</div>';
        }
    }
}
?>

<!doctype html>
<html lang="en">
<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <title>Login - E-Commerce</title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/css/bootstrap.min.css" rel="stylesheet">
    <style>
        body {
            background-color: #f8f9fa;
            display: flex;
            justify-content: center;
            align-items: center;
            height: 100vh;
        }
        .login-container {
            background: #fff;
            padding: 30px;
            border-radius: 10px;
            box-shadow: 0px 0px 10px rgba(0, 0, 0, 0.1);
            width: 100%;
            max-width: 400px;
        }
        .login-container h2 {
            text-align: center;
            margin-bottom: 20px;
            font-weight: bold;
        }
        .btn-login {
            width: 100%;
            background-color: #ff9900;
            border: none;
            font-size: 16px;
            font-weight: bold;
        }
        .btn-login:hover {
            background-color: #e68900;
        }
    </style>
</head>
<body>
    <div class="login-container">
        <h2>Login</h2>
       <!-- Login Form -->
<form action="" method="post">
    <div class="mb-3">
        <label class="form-label">Email address</label>
        <input type="email" placeholder="Enter your email" name="email" required class="form-control">
    </div>

    <div class="mb-3 position-relative">
        <label class="form-label">Password</label>
        <div class="input-group">
            <input type="password" placeholder="Enter your password" name="password" id="passwordField" required class="form-control">
            <span class="input-group-text" id="togglePassword" style="cursor: pointer;">
                👁️
            </span>
        </div>
    </div>

    <button type="submit" name="submit" class="btn btn-login">Login</button>
</form>

<!-- Password Toggle Script -->
<script>
    document.addEventListener("DOMContentLoaded", function () {
        const passwordField = document.getElementById("passwordField");
        const togglePassword = document.getElementById("togglePassword");

        togglePassword.addEventListener("click", function () {
            const type = passwordField.getAttribute("type") === "password" ? "text" : "password";
            passwordField.setAttribute("type", type);
            this.textContent = type === "password" ? "👁️" : "🙈";
        });
    });
</script>

        <div class="login-containerr">
            <p>Don't have an account?<a href="register.php">Sign up</a></p>
        </div>
    </div>
    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/js/bootstrap.bundle.min.js"></script>
</body>
</html>

<style>
    .login-containerr {
        background-color: #ffffff;
        padding: 10px;
        border-radius: 10px;
        box-shadow: 0 4px 20px rgba(0, 0, 0, 0.1);
        max-width: 400px;
        width: 100%;
        text-align: center;
        margin-top: 15px;
    }

    h2 {
        color: #333;
        margin-bottom: 20px;
    }

    p {
        color: #666;
        margin: 10px 0;
    }

    a {
        color:rgb(222, 216, 60);
        text-decoration: none;
        font-weight: bold;
        padding: 5px 10px;
        /* Padding for background effect */
        border-radius: 5px;
        /* Rounded corners */
        transition: background-color 0.3s;
        /* Smooth transition */
    }

    a:hover {
        background-color: #e7f1ff;
        /* Background color on hover */
        color: rgb(10, 30, 211);
        /* Change text color on hover */
    }

    .button {
        background-color: rgb(7, 43, 82);
        color: white;
        border: none;
        border-radius: 5px;
        padding: 10px 20px;
        cursor: pointer;
        margin-top: 20px;
        font-size: 16px;
        transition: background-color 0.3s;
    }

    .button:hover {
        background-color: #0056b3;
    }
</style>