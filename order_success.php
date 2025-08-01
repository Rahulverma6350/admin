<?php
session_start();
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Order Confirmation</title>
    <style>
        body {
            display: flex;
            height: 100vh;
            align-items: center;
            justify-content: center;
            background-color: #f5f5f5;
            margin: 0;
            font-family: Arial, sans-serif;
        }

        .order-container {
            background: #ffffff;
            padding: 40px;
            border-radius: 12px;
            box-shadow: 0 8px 20px rgba(0, 0, 0, 0.1);
            text-align: center;
            max-width: 400px;
            width: 100%;
            overflow: hidden;
        }

        .order-container h2 {
            color: #4CAF50;
            font-size: 26px;
            margin-bottom: 10px;
        }

        .order-container p {
            color: #555;
            font-size: 16px;
            margin-bottom: 20px;
        }

        .icon {
            font-size: 60px;
            color: #4CAF50;
            margin-bottom: 15px;
            opacity: 0;
            transform: scale(0.5);
            animation: tickAnimation 0.8s ease-out forwards;
        }

        @keyframes tickAnimation {
            from {
                opacity: 0;
                transform: scale(0.5);
            }
            to {
                opacity: 1;
                transform: scale(1);
            }
        }

        .continue-btn {
            display: inline-block;
            padding: 12px 30px;
            background-color: #4CAF50;
            color: #fff;
            text-decoration: none;
            border-radius: 25px;
            font-weight: bold;
            transition: background 0.3s ease;
        }

        .continue-btn:hover {
            background-color: #45a049;
        }
    </style>
</head>
<body>

    <div class="order-container">
        <div class="icon">✔️</div>
        <h2>Order Placed Successfully!</h2>
        <p>Your order has been confirmed. We'll send you an update when it's shipped.</p>
        <a href="index.php" class="continue-btn">Continue Shopping</a>
    </div>

</body>
</html>
