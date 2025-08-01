<?php
session_start();
include('header.php');
include('include/db.php');


if (isset($_SESSION['user_id'])) {
    $user_id = $_SESSION['user_id'];

    // Fetch user data from the database
    $query = "SELECT name, email, phone, company, address, city, country, postal_code,selected_address FROM user_reg WHERE id = ?";
    $stmt = $conn->prepare($query);
    $stmt->bind_param("i", $user_id); // "i" for integer
    $stmt->execute();
    $result = $stmt->get_result();


    if ($result->num_rows > 0) {
        $user = $result->fetch_assoc(); // Fetch user data as an associative array
    } else {
        // Handle case where user is not found
        $user = [];
    }
} else {
    // Handle case where user ID is not set in session
    $user = [];
}


// Cart Data Fetch for Guest or Logged-in User
$user_id = isset($_SESSION['user_id']) ? $_SESSION['user_id'] : null;
$session_id = isset($_SESSION['guest_session']) ? $_SESSION['guest_session'] : null;

$query = "SELECT cart.*, products.p_id, products.product_name, products.discounted_price, products.product_image 
          FROM cart 
          JOIN products ON cart.product_id = products.p_id 
          WHERE cart.user_id = ? OR cart.session_id = ?";
$stmt = $conn->prepare($query);
$stmt->bind_param("is", $user_id, $session_id);
$stmt->execute();
$resul = $stmt->get_result();


$updateaddress = "";

// registation 
if (isset($_POST['submit'])) {
    $username = trim($_POST['username']);
    $useremail = trim($_POST['useremail']);
    $userphone = trim($_POST['userphone']);
    $company = trim($_POST['company']);
    $address = is_array($_POST['address']) ? implode(', ', $_POST['address']) : trim($_POST['address']);

    $city = trim($_POST['city']);
    $country = trim($_POST['country']);
    $postal_code = trim($_POST['postal_code']);
    $password = trim($_POST['userpassw']); // No hashing for now (hashing can be added later)

    // Insert user data into user_reg table
    $sql = "INSERT INTO user_reg (name, email, phone, company, address, city, country, postal_code, passw) 
            VALUES (?, ?, ?, ?, ?, ?, ?, ?, ?)";

    $stmt = $conn->prepare($sql);
    $stmt->bind_param("sssssssss", $username, $useremail, $userphone, $company, $address, $city, $country, $postal_code, $password);

    if ($stmt->execute()) {

        $_SESSION['user_id'] = $stmt->insert_id;  // Set session with new user ID
        // $_SESSION['address'] = $address;
        echo "<script>alert('Registration successful! Redirecting to checkout...'); window.location.href='checkout.php';</script>";
    } else {
        echo "<script>alert('Error registering user! Try again.');</script>";
    }
}

if ($_SERVER["REQUEST_METHOD"] == "POST" && !empty($_POST["address"])) {
    $user_id = $_SESSION['user_id'];
    $newAddress = htmlspecialchars(trim($_POST["address"]));

    // ✅ Step 1: Fetch existing address
    $result = $conn->query("SELECT address FROM user_reg WHERE id = $user_id");
    $row = $result->fetch_assoc();
    $existingAddress = $row['address'];

    // ✅ Step 2: Append new address
    if (!empty($existingAddress)) {
        $updatedAddress = $existingAddress . ", " . $newAddress;
    } else {
        $updatedAddress = $newAddress;
    }

    // ✅ Step 3: Update the address field
    $stmt = $conn->prepare("UPDATE user_reg SET address = ? WHERE id = ?");
    $stmt->bind_param("si", $updatedAddress, $user_id);

    if ($stmt->execute()) {
        echo "<div class='alert alert-success mt-3'>Address saved: <strong>$newAddress</strong></div>";
    } else {
        echo "<div class='alert alert-danger mt-3'>Failed to save address.</div>";
    }

    $stmt->close();
}


$conn->close();


?>


<main class="main__content_wrapper">

    <!-- Start breadcrumb section -->
    <section class="breadcrumb_section breadcrumb_bg">
        <div class="container">
            <div class="row row-cols-1">
                <div class="col">
                    <div class="breadcrumb__content">
                        <h1 class="breadcrumb__content--title text-white mb-10">Checkout</h1>
                        <ul class="breadcrumb__content--menu d-flex">
                            <li class="breadcrumb_content--menu_items"><a class="text-white" href="index.php">Home</a></li>
                            <li class="breadcrumb_content--menu_items"><span class="text-white">Checkout</span></li>
                        </ul>
                    </div>
                </div>
            </div>
        </div>
    </section>
    <!-- End breadcrumb section -->

    <!-- Start checkout page area -->
    <div class="checkout__page--area section--padding">
        <div class="container">
            <div class="row">
                <div class="col-lg-7 col-md-6">
                    <div class="main checkout__mian">
                        <?php
                        // Check if user is logged in
                        $isLoggedIn = isset($_SESSION['user_id']);
                        ?>
                        <!-- Table to display after login -->
                        <?php if ($isLoggedIn): ?>

                            <!-- add registration form -->
                            <div class="form-container">
                                <section class="step">
                                    <h2> Delivery Address</h2>
                                    <div class="address">
                                        <label>
                                            <!-- <input type="radio" name="address" checked> -->
                                            <div>
                                                <p><strong><?php echo $user['name']; ?></strong> | <?php echo $user['phone']; ?></p>

                                                <div id="address-list">
                                                    <?php
                                                    $all_addresses = explode(',', $user['address']);
                                                    foreach ($all_addresses as $index => $addr) {
                                                        $trimmed = trim($addr);
                                                    ?>
                                                        <label style="display: block; margin-bottom: 8px;">
                                                            <input type="radio" name="selected_address" class="address-radio"
                                                                value="<?php echo htmlspecialchars($trimmed); ?>"
                                                                <?php echo ($user['selected_address'] == $trimmed) ? 'checked' : ''; ?>>
                                                            <?php echo htmlspecialchars($trimmed) . ', ' . $user['city'] . ', ' . $user['country'] . ' - ' . $user['postal_code']; ?>
                                                        </label>
                                                    <?php } ?>
                                                </div>

                                                <!-- Response Message -->
                                                <div id="responseMessage" class="mt-2"></div>

                                                <script>
                                                    document.querySelectorAll('.address-radio').forEach(function(radio) {
                                                        radio.addEventListener('change', function() {
                                                            const selectedAddress = this.value;

                                                            fetch('update_selected_address.php', {
                                                                    method: 'POST',
                                                                    headers: {
                                                                        'Content-Type': 'application/x-www-form-urlencoded'
                                                                    },
                                                                    body: 'selected_address=' + encodeURIComponent(selectedAddress)
                                                                })
                                                                .then(response => response.text())
                                                                .then(data => {
                                                                    document.getElementById("responseMessage").innerHTML = data;
                                                                })
                                                                .catch(error => {
                                                                    document.getElementById("responseMessage").innerHTML = "<div class='alert alert-danger'>Something went wrong.</div>";
                                                                });
                                                        });
                                                    });
                                                </script>

                                            </div>
                                        </label>
                                        <!-- <button class="add-new-btn ">+ Add New Address</button> -->

                                        <!-- Add New Address Button -->
                                        <button class="add-new-btn btn btn-primary" id="showInputBtn">
                                            + Add New Address
                                        </button>

                                        <!-- Hidden input field -->
                                        <div id="addressInputWrapper" style="display: none; margin-top: 15px;">
                                            <form method="post" action="">
                                                <input type="text" class="form-control mb-2" name="address" placeholder="Enter your address">
                                                <button type="save_address" class="btn btn-success">Save Address</button>
                                            </form>
                                        </div>

                                        <!-- Script to show input -->
                                        <script>
                                            document.getElementById("showInputBtn").addEventListener("click", function() {
                                                document.getElementById("addressInputWrapper").style.display = "block";
                                            });
                                        </script>

                                        <?php
                                        // PHP: Form handling
                                        if ($_SERVER["REQUEST_METHOD"] == "POST" && !empty($_POST["address"])) {
                                            $address = htmlspecialchars($_POST["address"]);
                                            echo "<div class='alert alert-success mt-3'>Address saved: <strong>$address</strong></div>";
                                        }
                                        ?>

                                    </div>
                                </section>
                            </div>

                        <?php else: ?>

                            <!-- Contact Information Form -->
                            <form action="checkout.php" method="POST">
                                <div class="section_header checkout_section--header d-flex align-items-center justify-content-between mb-25">
                                    <h2 class="section__header--title h3">Contact Information</h2>
                                    <p>Already have an account? <a class="layout_flex--item_link" href="login.php">Log in</a></p>
                                </div>

                                <div class="checkout_content--step section_contact--information">
                                    <div class="checkout__email--phone mb-12">
                                        <input class="checkout__input--field" placeholder="Full Name" name="username" type="text" required>
                                    </div>
                                    <div class="checkout__email--phone mb-12">
                                        <input class="checkout__input--field" placeholder="Email" name="useremail" type="email" required>
                                    </div>
                                    <div class="checkout__email--phone mb-12">
                                        <input class="checkout__input--field" placeholder="Phone Number" name="userphone" type="text" required>
                                    </div>
                                    <div class="checkout__email--phone mb-12">
                                        <input class="checkout__input--field" placeholder="Password" name="userpassw" type="password" required>
                                    </div>
                                </div>

                                <div class="section_shipping--address_content mb-5">
                                    <h2 class="section__header--title h3">Billing Details</h2>
                                    <input class="checkout__input--field" placeholder="Company (optional)" name="company" type="text">

                                    <input class="checkout__input--field" placeholder="Address" name="address[]" type="text" required>

                                    <input class="checkout__input--field" placeholder="City" name="city" type="text" required>

                                    <select class="checkout_input--select_field" name="country" required>
                                        <option value="India">India</option>
                                        <option value="United States">United States</option>
                                        <option value="Netherlands">Netherlands</option>
                                    </select>

                                    <input class="checkout__input--field" placeholder="Postal Code" name="postal_code" type="text" required>
                                </div>

                                <div class="checkout_content--step_footer d-flex align-items-center">
                                    <button class="continue_shipping--btn primary_btn" type="submit" name="submit">Complete Registration</button>
                                    <a class="previous__link--content" href="cart.php">Return to cart</a>
                                </div>
                            </form>
                        <?php endif; ?>

                        <style>
                            table {
                                width: 60%;
                                margin: 20px auto;
                                border-collapse: collapse;
                            }

                            th,
                            td {
                                border: 1px solid #ddd;
                                padding: 8px;
                                text-align: left;
                            }

                            th {
                                background-color: #f2f2f2;
                            }
                        </style>

                    </div>
                </div>

                <div class="col-lg-5 col-md-6">
                    <aside class="checkout__sidebar sidebar border-radius-10">
                        <h2 class="checkout_order--summary_title text-center mb-15">Your Order Summary</h2>
                        <div class="cart_table checkout_product--table">
                            <table class="cart__table--inner">
                                <tbody class="cart__table--body">
                                    <?php
                                    $subtotal = 0;
                                    while ($row = mysqli_fetch_assoc($resul)) {
                                        $product_price = $row['discounted_price'] * $row['quantity'];
                                        $subtotal += $product_price;
                                    ?>
                                        <tr class="cart_table--body_items">
                                            <td class="cart_table--body_list">
                                                <div class="product__image two  d-flex align-items-center">
                                                    <div class="product__thumbnail border-radius-5">
                                                        <a class="display-block" href="product-details.php"><img class="display-block border-radius-5" src="admin/img/<?php echo $row['product_image']; ?>" alt="cart-product"></a>
                                                        <span class="product__thumbnail--quantity"><?php echo $row['quantity']; ?></span>
                                                    </div>
                                                    <div class="product__description">
                                                        <h4 class="product__description--name"><a href="product-details.php?id=<?php echo $row['p_id']; ?>"><?php echo $row['product_name']; ?></a></h4>
                                                        <!-- <span class="product__description--variant">COLOR: Blue</span> -->
                                                    </div>
                                                </div>
                                            </td>
                                            <td class="cart_table--body_list">
                                                <span class="cart__price">₹ <?php echo $row['discounted_price']; ?></span>
                                            </td>
                                        </tr>
                                    <?php
                                    }
                                    ?>
                                </tbody>
                            </table>
                        </div>
                        <div class="checkout__discount--code">
                            <form class="d-flex" action="#">
                                <label>
                                    <input class="checkout_discount--code_input--field border-radius-5" placeholder="Gift card or discount code" type="text">
                                </label>
                                <button class="checkout_discount--codebtn primary_btn border-radius-5" type="submit">Apply</button>
                            </form>
                        </div>
                        <div class="checkout__total">
                            <table class="checkout__total--table">
                                <tbody class="checkout__total--body">
                                    <tr class="checkout__total--items">
                                        <td class="checkout__total--title text-left">Subtotal </td>
                                        <td class="checkout__total--amount text-right">₹ <?php echo number_format($subtotal, 2); ?></td>
                                    </tr>
                                    <tr class="checkout__total--items">
                                        <td class="checkout__total--title text-left">Shipping</td>
                                        <td class="checkout_total--calculated_text text-right">Calculated at next step</td>
                                    </tr>
                                </tbody>
                                <tfoot class="checkout__total--footer">
                                    <tr class="checkout_total--footer_items">
                                        <td class="checkout_total--footertitle checkouttotal--footer_list text-left">Total </td>
                                        <td class="checkout_total--footeramount checkouttotal--footer_list text-right">₹ <?php echo number_format($subtotal, 2); ?></td>
                                    </tr>
                                </tfoot>
                            </table>
                        </div>
                        <form id="paymentForm" method="POST" action="process_order.php">
                            <input type="hidden" name="total_amount" value="<?= $subtotal; ?>">

                            <!-- COD Button -->
                            <button type="submit" name="cod" class="checkout_now--btn primary_btn">Cash on Delivery (COD)</button>

                            <!-- Razorpay Button -->
                            <button type="button" id="razorpayBtn" class="checkout_now--btn primary_btn">Pay with Razorpay</button>
                        </form>
                    </aside>
                </div>

            </div>
        </div>
    </div>
    <!-- End checkout page area -->

    <!-- Start Newsletter banner section -->
    <section class="newsletter__banner--section section--padding pt-0">
        <div class="container">
            <div class="newsletter_banner--thumbnail position_relative">
                <img class="newsletter_banner--thumbnail_img" src="assets/img/banner/banner-bg7.webp" alt="newsletter-banner">
                <div class="newsletter_content newsletter_subscribe">
                    <h5 class="newsletter__content--subtitle text-white">Want to offer regularly ?</h5>
                    <h2 class="newsletter__content--title text-white h3 mb-25">Subscribe Our Newsletter <br>
                        for Get Daily Update</h2>
                    <form class="newsletter_subscribe--form position_relative" action="#">
                        <label>
                            <input class="newsletter__subscribe--input" placeholder="Enter your email address" type="email">
                        </label>
                        <button class="newsletter_subscribe--button primary_btn" type="submit">Subscribe
                            <svg class="newsletter_subscribe--button_icon" xmlns="http://www.w3.org/2000/svg" width="9.159" height="7.85" viewbox="0 0 9.159 7.85">
                                <path data-name="Icon material-send" d="M3,12.35l9.154-3.925L3,4.5,3,7.553l6.542.872L3,9.3Z" transform="translate(-3 -4.5)" fill="currentColor"></path>
                            </svg>
                        </button>
                    </form>
                </div>
            </div>
        </div>
    </section>
    <!-- End Newsletter banner section -->
</main>




<?php include('footer.php') ?>

<?php
require('razorpay-php/Razorpay.php');

use Razorpay\Api\Api;

// Razorpay API keys
$api_key = 'rzp_test_thFNzw5pkSaYe3';
$api_secret = 'LpYOPjdQQmAKZHdiWVjUvTPu';

// Initialize Razorpay API
$api = new Api($api_key, $api_secret);

// Convert amount to paise (Razorpay uses paise)
$amount_in_paise = $subtotal * 100;

// Create an order in Razorpay
$order = $api->order->create([
    'amount' => $amount_in_paise,
    'currency' => 'INR',
    'receipt' => 'order_receipt_' . time(),
]);

$order_id = $order->id;
?>


<script src="https://code.jquery.com/jquery-3.6.0.min.js"></script> <!-- Include jQuery -->


<script>
    document.addEventListener("DOMContentLoaded", function() {
        let razorpayBtn = document.getElementById("razorpayBtn");

        if (razorpayBtn) {
            razorpayBtn.addEventListener("click", function() {
                console.log("Razorpay button clicked!");

                // Step 1: Create Order
                $.ajax({
                    url: "razorpay_process.php",
                    type: "POST",
                    data: {
                        create_order: true,
                        amount: <?= $subtotal ?>
                    },
                    dataType: "json",
                    success: function(orderData) {
                        if (orderData.success) {
                            var options = {
                                "key": "<?= $api_key ?>",
                                "amount": orderData.amount,
                                "currency": "INR",
                                "name": "Your Company Name",
                                "description": "Payment for your order",
                                "image": "https://cdn.razorpay.com/logos/GhRQcyean79PqE_medium.png",
                                "order_id": orderData.order_id,
                                "theme": {
                                    "color": "#738276"
                                },
                                "handler": function(response) {
                                    console.log("Payment Response:", response);

                                    // Step 2: Process Payment
                                    $.ajax({
                                        url: "razorpay_process.php",
                                        type: "POST",
                                        contentType: "application/json",
                                        data: JSON.stringify({
                                            payment_id: response.razorpay_payment_id,
                                            order_id: response.razorpay_order_id,
                                            signature: response.razorpay_signature,
                                            amount: <?= $subtotal ?>
                                        }),
                                        dataType: "json",
                                        success: function(data) {
                                            console.log("Server Response:", data);
                                            if (data.success) {
                                                window.location.href = "order_success.php";
                                            } else {
                                                alert("Payment verification failed!");
                                            }
                                        },
                                        error: function(xhr) {
                                            console.error("AJAX Error:", xhr.responseText);
                                        }
                                    });
                                },
                                "prefill": {
                                    "email": "<?= $_SESSION['email'] ?? 'guest@example.com' ?>"
                                }
                            };

                            var rzp = new Razorpay(options);
                            rzp.open();
                        } else {
                            alert(orderData.message);
                        }
                    },
                    error: function(xhr) {
                        console.error("AJAX Error:", xhr.responseText);
                    }
                });
            });
        } else {
            console.error("Razorpay button not found!");
        }
    });
</script>

<script>
    let addressCount = 1;
    const maxAddressFields = 5;

    function addNewAddress() {
        if (addressCount >= maxAddressFields) {
            alert("You can only add up to " + maxAddressFields + " addresses.");
            return;
        }
        const container = document.getElementById('address-container');
        const newInput = document.createElement('input');
        newInput.className = "checkout__input--field mt-2";
        newInput.placeholder = "Additional Address";
        newInput.name = "address[]";
        newInput.type = "text";
        container.appendChild(newInput);
        addressCount++;
    }
</script>