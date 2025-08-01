<?php
session_start(); // Start the session
include('include/db.php');

if (isset($_POST['submit'])) {
    $name = mysqli_real_escape_string($conn, $_POST['fname']);
    $email = mysqli_real_escape_string($conn, $_POST['email']);
    $password = $_POST['password'];
    $cpassword = $_POST['cpassword'];

    // Check if passwords match
    if ($password !== $cpassword) {
        echo "<script>alert('Passwords do not match!');</script>";
        exit;
    }

    // Check if email already exists
    $check_email_query = "SELECT * FROM admin WHERE email = ?";
    $stmt = mysqli_prepare($conn, $check_email_query);
    mysqli_stmt_bind_param($stmt, "s", $email);
    mysqli_stmt_execute($stmt);
    $result = mysqli_stmt_get_result($stmt);
    
    if (mysqli_num_rows($result) > 0) {
        echo "<script>alert('Email already exists. Please use another email.');</script>";
        exit;
    }

    // Hash the password
    $hashed_password = password_hash($password, PASSWORD_DEFAULT);

    // Handle image upload
    $image = $_FILES['imagfile']['name'];
    $image_tmp = $_FILES['imagfile']['tmp_name'];
    $image_path = 'img/'. basename($image);

    if (move_uploaded_file($image_tmp, $image_path)) {
        // Insert new user into database using prepared statements
        $insert_query = "INSERT INTO admin (name, email, password, img) VALUES (?, ?, ?, ?)";
        $stmt = mysqli_prepare($conn, $insert_query);
        mysqli_stmt_bind_param($stmt, "ssss", $name, $email, $hashed_password, $image_path);

        if (mysqli_stmt_execute($stmt)) {
            // Set session for user login
            $_SESSION['email'] = $email;
          header('location:login.php');
        } else {
            echo "<script>alert('Error: " . mysqli_error($conn) . "');</script>";
        }
    } else {
        echo "<script>alert('Failed to upload image.');</script>";
    }
}
?>



<!doctype html>
<html lang="en">
  <head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <title>Register - E-Commerce</title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/css/bootstrap.min.css" rel="stylesheet">
    <style>
      body {
        background-color: #f8f9fa;
        display: flex;
        justify-content: center;
        align-items: center;
        height: 100vh;
      }
      .register-container {
        background: #fff;
        padding: 30px;
        border-radius: 10px;
        box-shadow: 0px 0px 10px rgba(0, 0, 0, 0.1);
        width: 100%;
        max-width: 400px;
      }
      .register-container h2 {
        text-align: center;
        margin-bottom: 20px;
        font-weight: bold;
      }
      .btn-register {
        width: 100%;
        background-color: #ff9900;
        border: none;
        font-size: 16px;
        font-weight: bold;
      }
      .btn-register:hover {
        background-color: #e68900;
      }
    </style>
  </head>
  <body>
   <style>
  .error-message {
    color: red;
    font-size: 0.9rem;
    margin-top: 5px;
  }

  .password-wrapper {
    position: relative;
  }

  .toggle-password {
    position: absolute;
    top: 50%;
    right: 10px;
    transform: translateY(-50%);
    cursor: pointer;
    font-size: 18px;
    color: #666;
  }
</style>

<link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.5.0/css/all.min.css" />

<div class="register-container">
  <h2>Create Account</h2>
  <form id="registerForm" action="" method="post" enctype="multipart/form-data" novalidate>
    
    <!-- Name -->
    <div class="mb-3">
      <label class="form-label">Your Name</label>
      <input type="text" name="fname" id="fname" class="form-control" placeholder="Your name" required>
      <div id="fnameError" class="error-message"></div>
    </div>

    <!-- Email -->
    <div class="mb-3">
      <label class="form-label">Email address</label>
      <input type="email" name="email" id="email" class="form-control" placeholder="Please enter email" required>
      <div id="emailError" class="error-message"></div>
    </div>

    <!-- Password -->
    <div class="mb-3 password-wrapper">
      <label class="form-label">Password</label>
      <input type="password" name="password" id="password" class="form-control" placeholder="Numeric password (1–10 digits)" required>
      <i class="fa-regular fa-eye toggle-password" style="padding-top: 28px;" onclick="togglePassword('password', this)"></i>
      <div id="passwordError" class="error-message"></div>
    </div>

    <!-- Confirm Password -->
    <div class="mb-3 password-wrapper">
      <label class="form-label">Confirm Password</label>
      <input type="password" name="cpassword" id="cpassword" class="form-control" placeholder="Confirm password" required>
      <i class="fa-regular fa-eye toggle-password" style="padding-top: 28px;" onclick="togglePassword('cpassword', this)"></i>
      <div id="cpasswordError" class="error-message"></div>
    </div>

    <!-- Image -->
    <div class="mb-3">
      <label class="form-label">Profile Image</label>
      <input type="file" name="imagfile" id="imagfile" class="form-control" required accept=".jpg,.jpeg,.png">
      <div id="imageError" class="error-message"></div>
    </div>

    <button type="submit" name="submit" class="btn btn-register">Sign Up</button>
  </form>

  <div class="login-container">
    <p>Have an account? <a href="login.php">Login here</a></p>
  </div>
</div>

<script>
  function togglePassword(fieldId, icon) {
    const input = document.getElementById(fieldId);
    if (input.type === "password") {
      input.type = "text";
      icon.classList.remove("fa-eye");
      icon.classList.add("fa-eye-slash");
    } else {
      input.type = "password";
      icon.classList.remove("fa-eye-slash");
      icon.classList.add("fa-eye");
    }
  }

  document.getElementById("registerForm").addEventListener("submit", function (e) {
    let isValid = true;

    // Clear previous errors
    document.querySelectorAll('.error-message').forEach(el => el.textContent = '');

    const name = document.getElementById("fname").value.trim();
    const email = document.getElementById("email").value.trim();
    const password = document.getElementById("password").value.trim();
    const cpassword = document.getElementById("cpassword").value.trim();
    const imageFile = document.getElementById("imagfile").files[0];

    const namePattern = /^[A-Za-z\s]+$/;
    if (!namePattern.test(name)) {
      document.getElementById("fnameError").textContent = "Name must not contain numbers or special characters.";
      isValid = false;
    }

    const emailPattern = /^[^\s@]+@[^\s@]+\.[^\s@]+$/;
    if (!emailPattern.test(email)) {
      document.getElementById("emailError").textContent = "Please enter a valid email address.";
      isValid = false;
    }

    const passwordPattern = /^\d{1,10}$/;
    if (!passwordPattern.test(password)) {
      document.getElementById("passwordError").textContent = "Password must be numeric and 1–10 digits long.";
      isValid = false;
    }

    if (password !== cpassword) {
      document.getElementById("cpasswordError").textContent = "Passwords do not match.";
      isValid = false;
    }

    if (imageFile) {
      const allowedTypes = ["image/jpeg", "image/png", "image/jpg"];
      if (!allowedTypes.includes(imageFile.type)) {
        document.getElementById("imageError").textContent = "Profile image must be JPG, JPEG, or PNG.";
        isValid = false;
      }
    }

    if (!isValid) e.preventDefault();
  });
</script>

    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/js/bootstrap.bundle.min.js"></script>
  </body>
</html>

<style>
 

  .login-container {
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
    color:rgb(10, 30, 211);
    /* Change text color on hover */
  }

  .button {
    background-color:rgb(7, 43, 82);
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