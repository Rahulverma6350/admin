<?php
session_start();
include("include/db.php");

if (isset($_POST['login'])) {
    $useremail = trim($_POST['useremail']);
    $password = trim($_POST['userpassword']);

    $sql = "SELECT * FROM user_reg WHERE email = ?";
    $stmt = $conn->prepare($sql);
    $stmt->bind_param("s", $useremail);
    $stmt->execute();
    $res = $stmt->get_result();

    if ($res->num_rows > 0) {
        $user = $res->fetch_assoc();
        $dbPassword = $user['passw'];

        if ($password === $dbPassword) {
            $_SESSION['user_id'] = $user['id'];
            $_SESSION['email'] = $user['email'];
            $_SESSION['name']    = $user['name'];

            $user_id = $_SESSION['user_id'];
            $session_id = $_SESSION['guest_session'] ?? session_id();

            // ✅ MERGE GUEST CART with variant support
            $merge_cart_query = "
                INSERT INTO cart (user_id, product_id, quantity, color, size)
                SELECT ?, product_id, quantity, color, size
                FROM cart
                WHERE session_id = ?
                  ON DUPLICATE KEY UPDATE quantity = VALUES(quantity)
            ";
            $stmt = $conn->prepare($merge_cart_query);
            if ($stmt) {
                $stmt->bind_param("is", $user_id, $session_id);
                $stmt->execute();
                $stmt->close();
            }

            // ✅ DELETE guest cart
            $stmt = $conn->prepare("DELETE FROM cart WHERE session_id = ?");
            if ($stmt) {
                $stmt->bind_param("s", $session_id);
                $stmt->execute();
                $stmt->close();
            }

            // ✅ MERGE GUEST WISHLIST with variant support
            $merge_wishlist_query = "
                INSERT INTO wishlist (product_id, user_id, color, size)
                SELECT product_id, ?, color, size
                FROM wishlist
                WHERE session_id = ?
                AND NOT EXISTS (
                    SELECT 1 FROM wishlist 
                    WHERE user_id = ? AND product_id = wishlist.product_id 
                    AND color = wishlist.color AND size = wishlist.size
                )
            ";
            $stmt = $conn->prepare($merge_wishlist_query);
            if ($stmt) {
                $stmt->bind_param("isi", $user_id, $session_id, $user_id);
                $stmt->execute();
                $stmt->close();
            }

            // ✅ DELETE guest wishlist
            $stmt = $conn->prepare("DELETE FROM wishlist WHERE session_id = ?");
            if ($stmt) {
                $stmt->bind_param("s", $session_id);
                $stmt->execute();
                $stmt->close();
            }

            // ✅ Remove guest session only after all merges
            unset($_SESSION['guest_session']);

            // ✅ Redirect
            $redirect_page = $_SESSION['redirect_after_login'] ?? "index.php";
            unset($_SESSION['redirect_after_login']);
            header("Location: $redirect_page");
            exit();
        } else {
            echo "<script>alert('Incorrect password! Please try again.');</script>";
        }
    } else {
        echo "<script>alert('No user found with this email!');</script>";
    }
}
?>


<!DOCTYPE html>
<html lang="en">

<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Login - Furea</title>
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

            <!-- Login Form -->
            <form method="POST">
                <div class="mb-3">
                    <label class="form-label">Email address</label>
                    <input type="email" placeholder="Enter your email" name="useremail" required class="form-control">
                </div>

                <div class="mb-3 position-relative">
                    <label class="form-label">Password</label>
                    <div class="input-group">
                        <input type="password" placeholder="Enter your password" name="userpassword" id="passwordField" required class="form-control">
                        <span class="input-group-text" id="togglePassword" style="cursor: pointer;">
                            👁️
                        </span>
                    </div>
                </div>

                <button type="submit" name="login" class="btn btn-login">Log In</button>
            </form>

            <style>
                .login-card {
                    background: #fff;
                    padding: 2rem;
                    border-radius: 16px;
                    box-shadow: 0 6px 24px rgba(0, 0, 0, 0.1);
                    width: 100%;
                    max-width: 400px;
                    margin: 40px auto;
                }

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
                    transition: border 0.3s;
                }

                .form-control:focus {
                    border-color: #007bff;
                    box-shadow: 0 0 0 3px rgba(0, 123, 255, 0.15);
                }

                .mb-3 {
                    margin-bottom: 1.5rem;
                }

                .input-group {
                    position: relative;
                    width: 100%;
                }

                .input-group input.form-control {
                    padding-right: 50px;
                }

                .input-group-text {
                    position: absolute;
                    right: -10px;
                    top: 50%;
                    transform: translateY(-50%);
                    font-size: 18px;
                    cursor: pointer;
                    user-select: none;

                    background: #fff;
                    border: 1px solid #ccc;
                    border-radius: 50%;
                    width: 32px;
                    height: 32px;
                    display: flex;
                    align-items: center;
                    justify-content: center;

                    color: #333;
                    box-shadow: 0 2px 6px rgba(0, 0, 0, 0.1);
                    transition: background 0.2s;
                }

                .input-group-text:hover {
                    background: #f1f1f1;
                }

                .btn-login {
                    width: 100%;
                    padding: 10px;
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



                .input-group-text {
                    position: absolute;
                    right: 0px;
                    /* top: 50%; */
                    /* transform: translateY(-70%); */
                    font-size: 18px;
                    cursor: pointer;
                    color: #333;
                    user-select: none;
                    background: #fff;
                    border: 1px solid #ccc;

                    width: 46px;
                    height: 40px;
                    display: flex;
                    align-items: center;
                    justify-content: center;
                    box-shadow: 0 2px 6px rgba(0, 0, 0, 0.1);
                    transition: all 0.2s ease-in-out;
                    border-radius: 0px;
                }

                .input-group-text:hover {
                    background: #f8f9fa;
                }
            </style>


            <script>
                const togglePassword = document.getElementById("togglePassword");
                const passwordField = document.getElementById("passwordField");

                togglePassword.addEventListener("click", function() {
                    const type = passwordField.type === "password" ? "text" : "password";
                    passwordField.type = type;
                    this.textContent = type === "password" ? "👁️" : "🙈";
                });
            </script>



            <div class="form-footer">
                <p class="footer-text">Don't have an account? <a href="registation.php" class="footer-link">Sign up</a></p>
            </div>
        </div>
    </div>

</body>

</html>