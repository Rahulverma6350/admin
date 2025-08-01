<?php
session_start();
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


$insertId = 1;
// registation 
if (isset($_POST['submit'])) {
    // Sanitize and trim inputs
    $username     = trim($_POST['username']);
    $useremail    = trim($_POST['useremail']);
    $userphone    = trim($_POST['userphone']);
    $company      = trim($_POST['company']);
    $address      = trim($_POST['address']);
    $city         = trim($_POST['city']);
    $country      = trim($_POST['country']);
    $postal_code  = trim($_POST['postal_code']);
    $password     = trim($_POST['userpassw']); // ⚠️ No hashing — consider using password_hash()

    // Insert user registration data
    $sql = "INSERT INTO user_reg (name, email, phone, company, address, city, country, postal_code, passw) 
                VALUES (?, ?, ?, ?, ?, ?, ?, ?, ?)";

    $stmt = $conn->prepare($sql);
    $stmt->bind_param("sssssssss", $username, $useremail, $userphone, $company, $address, $city, $country, $postal_code, $password);
    $stmt->execute();

    // Get inserted user ID and set session
    $user_id = $_SESSION['user_id'] = $stmt->insert_id;
    $_SESSION['name'] = $username;

    $stmt->close();

    // Insert address with selected_address = 1
    $stmt = $conn->prepare("INSERT INTO new_address (user_id, name, phone, address, city, country, postal_code, selected_address) 
                                VALUES (?, ?, ?, ?, ?, ?, ?, ?)");
    $selected_address = 1;
    $stmt->bind_param("issssssi", $user_id, $username, $userphone, $address, $city, $country, $postal_code, $selected_address);
    $stmt->execute();
    $stmt->close();

    echo "<script>
                alert('Registration successful! Redirecting to checkout...');
                window.location.href='checkout.php';
              </script>";
    exit;
}


if (isset($_POST['address']) && isset($_SESSION['user_id']) && $_SERVER["REQUEST_METHOD"] == "POST") {
    $user_id = $_SESSION['user_id'];

    $newName     = htmlspecialchars(trim($_POST["name"][0]));
    $newPhone    = htmlspecialchars(trim($_POST["phone"][0]));
    $newAddress  = htmlspecialchars(trim($_POST["address"][0]));
    $newCity     = htmlspecialchars(trim($_POST["city"][0]));
    $newCountry  = htmlspecialchars(trim($_POST["country"][0]));
    $newPostal   = htmlspecialchars(trim($_POST["postal_code"][0]));
    $selectedAddress = 1;

    $stmt = $conn->prepare("INSERT INTO new_address (user_id, name, phone, address, city, country, postal_code, selected_address) VALUES (?, ?, ?, ?, ?, ?, ?, ?)");
    $stmt->bind_param("issssssi", $user_id, $newName, $newPhone, $newAddress, $newCity, $newCountry, $newPostal, $selectedAddress);

    if ($stmt->execute()) {
        $_SESSION['user']['selected_address'] = $conn->insert_id;

        // ✅ Redirect to prevent re-submission
        header("Location: checkout.php?address_saved=1");
        exit;
    } else {
        echo "<div class='alert alert-danger'>❌ Failed to insert into new_address.</div>";
    }

    $stmt->close();
}


include('header.php');

?>



<style>
    .continue_shipping--btn {
        width: 60%;
        background: #68eb68;
        border: none;
        color: #fff;
        padding: 10px;
    }

    .checkout_discount--codebtn {
        background: #ff0000;
        border: none;
        color: #fff;
        padding: 3px 10px;
        margin-left: 18px;
    }

    .checkout_discount--codebtn:hover {
        background:rgb(0, 0, 0);
    }

    .checkout_discount--code_input--field {
        width: 100%;
        max-width: 400px;
        padding: 9px 16px;
        font-size: 16px;
        color: #333;
        background-color: #fff;
        border: 1px solid #ccc;
        border-radius: 8px;
        outline: none;
        box-shadow: 0 2px 5px rgba(0, 0, 0, 0.05);
        transition: all 0.3s ease;
    }

    .discount-code-input:focus {
        border-color: #3b82f6;
        /* Tailwind blue-500 */
        box-shadow: 0 0 0 3px rgba(59, 130, 246, 0.2);
        /* blue glow */
    }

    .discount-code-input::placeholder {
        color: #999;
    }

    .checkout_input--select_field {
        width: 100%;

        padding: 12px 16px;
        font-size: 16px;
        color: #333;
        background-color: #fff;
        border: 1px solid #ccc;
        appearance: none;
        -webkit-appearance: none;
        -moz-appearance: none;
        background-image: url("data:image/svg+xml;charset=US-ASCII,%3Csvg xmlns='http://www.w3.org/2000/svg' width='12' height='8' viewBox='0 0 12 8'%3E%3Cpath fill='%23333' d='M1.41 0L6 4.58 10.59 0 12 1.41 6 7.41 0 1.41z'/%3E%3C/svg%3E");
        background-repeat: no-repeat;
        background-position: right 12px center;
        background-size: 12px;
        box-shadow: 0 2px 5px rgba(0, 0, 0, 0.05);
        transition: all 0.3s ease;
        color: gray;
    }

    .checkout_input--select_field:focus {
        border-color: red;
        outline: none;
    }

    .layout_flex--item_link {
        color: blue;
    }
</style>

<main class="main__content_wrapper">


  <!-- Start breadcrumb section -->
        <section class="breadcrumb__section breadcrumb__bg">
            <div class="container-fluid">
                <div class="row row-cols-1">
                    <div class="col">
                        <div class="breadcrumb__content">
                            <h1 class="breadcrumb__content--title text-white mb-10">Checkout Page</h1>
                            <ul class="breadcrumb__content--menu d-flex">
                                <li class="breadcrumb__content--menu__items"><a class="text-white" href="index.php">Home</a></li>
                                <li class="breadcrumb__content--menu__items"><span class="text-white">Checkout Page</span></li>
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

                                            <div>
                                                <?php
                                                // Fetch addresses from database
                                                $query = "SELECT * FROM new_address WHERE user_id = ?";
                                                $stmt = $conn->prepare($query);
                                                $stmt->bind_param("i", $user_id);
                                                $stmt->execute();
                                                $resultAddress = $stmt->get_result();

                                                // Check if any address exists
                                                $hasAddresses = $resultAddress->num_rows > 0;
                                                ?>

                                                <?php if (!$hasAddresses): ?>
                                                    <div style="padding: 10px; background: #f9f9f9; border-left: 4px solid #007bff; margin-bottom: 20px;">
                                                        <strong><?= htmlspecialchars($user['name'] ?? '') ?></strong> | <?= htmlspecialchars($user['phone'] ?? '') ?><br>
                                                        <small style="color: #555;">No address added yet. Please add your delivery address.</small>
                                                    </div>
                                                <?php else: ?>
                                                    <div id="address-list">
                                                        <?php while ($addr = $resultAddress->fetch_assoc()) {
                                                            $city = trim($addr["city"] ?? '');
                                                            $country = trim($addr["country"] ?? '');
                                                            $postal = trim($addr["postal_code"] ?? '');
                                                            $fullName = trim($addr["name"] ?? '');
                                                            $fullPhone = trim($addr["phone"] ?? '');
                                                            $fullAddress = trim($addr["address"] ?? '');
                                                            $full_value = "$fullName|$fullPhone|$fullAddress|$city|$country|$postal";
                                                            $selected_value = $addr['selected_address'];
                                                            $index = $addr['id'];
                                                        ?>
                                                            <div class="address-item" data-index="<?= $index ?>" style="border: 1px solid #ccc; padding: 10px; margin-bottom: 15px; border-radius: 5px; position: relative;">
                                                                <!-- RADIO on top left -->
                                                                <div style="position: absolute; top: 10px; left: 10px;">
                                                                    <input type="radio" name="selected_address" class="address-radio"
                                                                        value="<?= htmlspecialchars($full_value); ?>"
                                                                        <?= ($selected_value == 1) ? 'checked' : '' ?>
                                                                        onclick="updateSelectedAddress('<?= $index ?>')">
                                                                </div>
                                                                <!-- Address Content -->
                                                                <div style="margin-left: 35px;">
                                                                    <p><strong><?= htmlspecialchars($fullName); ?></strong> | <?= htmlspecialchars($fullPhone); ?></p>
                                                                    <p><?= htmlspecialchars("$fullAddress, $city, $country - $postal"); ?></p>
                                                                    <div>
                                                                        <button class="edit-btn" onclick="window.location.href='edit_address.php?index=<?= $index ?>'">Edit</button>
                                                                        <!-- <button class="delete-btn" data-index="<?= $index ?>">Delete</button> -->
                                                                    </div>
                                                                </div>
                                                            </div>
                                                        <?php } ?>
                                                    </div>
                                                <?php endif; ?>

                                                <div id="responseMessage"></div>

                                                <!-- Response Message -->
                                                <div id="responseMessage" class="mt-2"></div>

                                                <!-- address-radio code-->
                                                <script>
                                                    function updateSelectedAddress(address) {
                                                        fetch('update_selected_address.php', {
                                                                method: 'POST',
                                                                headers: {
                                                                    'Content-Type': 'application/x-www-form-urlencoded'
                                                                },
                                                                body: 'selected_address=' + encodeURIComponent(address)
                                                            })
                                                            .then(response => response.text())
                                                            .then(data => {
                                                                document.getElementById("responseMessage").innerHTML = data;
                                                                // Optionally update the frontend display
                                                                // document.getElementById("selectedAddress").innerHTML = "Selected Address: " + address;
                                                            })
                                                            .catch(() => {
                                                                document.getElementById("responseMessage").innerHTML = "<div class='alert alert-danger'>Something went wrong.</div>";
                                                            });
                                                    }
                                                </script>


                                                <!-- Delete functionality -->
                                                <script>
                                                    document.querySelectorAll('.delete-btn').forEach(function(button) {
                                                        button.addEventListener('click', function() {
                                                            const index = this.dataset.index;
                                                            if (confirm('Are you sure you want to delete this address?')) {
                                                                fetch('delete_address.php', {
                                                                        method: 'POST',
                                                                        headers: {
                                                                            'Content-Type': 'application/x-www-form-urlencoded'
                                                                        },
                                                                        body: 'index=' + encodeURIComponent(index)
                                                                    })
                                                                    .then(response => response.text())
                                                                    .then(data => {
                                                                        location.reload(); // ✅ fixed selector
                                                                    })
                                                                    .catch(() => {
                                                                        location.reload();

                                                                    });
                                                            }
                                                        });
                                                    });

                                                    // Edit functionality
                                                    document.querySelectorAll('.edit-btn').forEach(function(button) {
                                                        button.addEventListener('click', function() {
                                                            const index = this.dataset.index;
                                                            const oldAddress = this.dataset.address;
                                                            // ✅ restored prompt

                                                            if (newAddress && newAddress !== oldAddress) {
                                                                fetch('edit_address.php', {
                                                                        method: 'POST',
                                                                        headers: {
                                                                            'Content-Type': 'application/x-www-form-urlencoded'
                                                                        },
                                                                        body: 'index=' + encodeURIComponent(index) + '&new_address=' + encodeURIComponent(newAddress)
                                                                    })
                                                                    .then(response => response.text())
                                                                    .then(data => {
                                                                        document.getElementById("responseMessage").innerHTML = data;
                                                                        location.reload(); // or update just the DOM element
                                                                    })
                                                                    .catch(() => {
                                                                        document.getElementById("responseMessage").innerHTML = "<div class='alert alert-danger'>Failed to edit address.</div>";
                                                                    });
                                                            }
                                                        });
                                                    });
                                                </script>

                                            </div>
                                        </label>

                                        <!-- Add New Address Button -->

                                        <!-- Trigger Button -->
                                        <button id="openModalBtn" class="new_btn"  style="width:40%;">Add or Select Delivery Address</button>


                                        <!-- Styles -->
                                        <style>
                                            /* Modal Styles */
                                            #customModal {
                                                position: fixed;
                                                top: 0;
                                                left: 0;
                                                width: 100vw;
                                                height: 100vh;
                                                background: rgba(0, 0, 0, 0.5);
                                                display: none;
                                                justify-content: center;
                                                align-items: center;
                                                z-index: 9999;
                                            }

                                            #modalContent {
                                                background: white;
                                                padding: 20px 25px;
                                                border-radius: 8px;
                                                max-width: 500px;
                                                width: 90%;
                                                box-shadow: 0 0 10px rgba(0, 0, 0, 0.3);
                                                position: relative;
                                            }

                                            .closeBtn {
                                                position: absolute;
                                                right: 15px;
                                                top: 10px;
                                                font-size: 24px;
                                                border: none;
                                                background: red;
                                                color: white;
                                                border-radius: 50%;
                                                width: 30px;
                                                height: 30px;
                                                cursor: pointer;
                                            }

                                            .radio-card {
                                                border: 2px solid #ccc;
                                                border-radius: 6px;
                                                padding: 12px 15px;
                                                margin-bottom: 10px;
                                                cursor: pointer;
                                                transition: border-color 0.3s;
                                                display: flex;
                                                align-items: flex-start;
                                                gap: 10px;
                                            }

                                            .radio-card:hover {
                                                border-color: #f90;
                                            }

                                            .radio-card input[type="radio"] {
                                                margin-top: 4px;
                                                accent-color: #f90;
                                            }

                                            .form-control {
                                                width: 100%;
                                                padding: 10px;
                                                font-size: 14px;
                                                margin-top: 5px;
                                                border: 1px solid #ccc;
                                                border-radius: 4px;
                                            }

                                            #customAddressFields {
                                                margin-top: 10px;
                                                display: none;
                                            }

                                            .btn-success {
                                                background-color: #28A745;
                                                color: white;
                                                border: none;
                                                padding: 10px;
                                                width: 100%;
                                                margin-top: 15px;
                                                font-size: 16px;
                                                cursor: pointer;
                                                border-radius: 5px;
                                            }

                                            .btn-success:hover {
                                                background-color: #218838;
                                            }
                                        </style>

                                        <!-- Modal HTML -->
                                        <?php
                                        // Count non-empty addresses
                                        $addresses = explode(',', $user['address'] ?? '');
                                        $total_addresses = count(array_filter($addresses));

                                        // Set default values for first address only
                                        $defaultName = '';
                                        $defaultPhone = '';

                                        if ($total_addresses === 0) {
                                            $defaultName = htmlspecialchars($user['name'] ?? '');
                                            $defaultPhone = htmlspecialchars($user['phone'] ?? '');
                                        }
                                        ?>

                                        <!-- Modal Start -->
                                        <div id="customModal">
                                            <div id="modalContent">
                                                <button class="closeBtn" id="closeModal">×</button>

                                                <!-- Heading -->
                                                <h4 style="margin-bottom: 20px;"><?= $total_addresses === 0 ? 'Add Your First Delivery Address' : 'Add Another Address' ?></h4>

                                                <form method="post" action="">

                                                    <!-- Name -->
                                                    <div class="form-floating mb-2">
                                                        <label for="floatingName">Name</label>
                                                        <input type="text" class="form-control" id="floatingName" name="name[]" placeholder="Enter your name"
                                                            value="<?= $defaultName ?>" required>
                                                    </div>

                                                    <!-- Phone -->
                                                    <div class="form-floating mb-2">
                                                        <label for="floatingPhone">Phone</label>
                                                        <input type="tel" class="form-control" id="floatingPhone" name="phone[]" placeholder="Enter your phone number"
                                                            value="<?= $defaultPhone ?>" required>
                                                    </div>

                                                    <!-- Address -->
                                                    <div class="form-floating mb-2">
                                                        <label for="floatingAddress">Address</label>
                                                        <input type="text" class="form-control" id="floatingAddress" name="address[]" placeholder="Enter your address" required>
                                                    </div>

                                                    <!-- City -->
                                                    <div class="form-floating mb-2">
                                                        <div class="form-floating mb-2">
                                                            <option value="">Select your city</option>
                                                            <select class="form-control" id="floatingCity" name="city[]" required>

                                                                <!-- Add cities here -->
                                                                <option value="Mumbai">Mumbai</option>
                                                                <option value="Delhi">Delhi</option>
                                                                <option value="Bengaluru">Bengaluru</option>
                                                                <option value="Hyderabad">Hyderabad</option>
                                                                <option value="Ahmedabad">Ahmedabad</option>
                                                                <option value="Chennai">Chennai</option>
                                                                <option value="Kolkata">Kolkata</option>
                                                                <option value="Pune">Pune</option>
                                                                <option value="Jaipur">Jaipur</option>
                                                                <option value="Lucknow">Lucknow</option>
                                                                <!-- You can add all cities -->
                                                            </select>
                                                        </div>
                                                    </div>

                                                    <!-- Country -->
                                                    <div class="form-floating mb-2">
                                                        <label for="floatingCountry">Country</label>
                                                        <input type="text" class="form-control" id="floatingCountry" name="country[]" value="India" readonly>
                                                    </div>


                                                    <!-- Postal Code -->
                                                    <div class="form-floating mb-3">
                                                        <label for="floatingPostalCode">Postal Code</label>
                                                        <input type="text" class="form-control" id="floatingPostalCode" name="postal_code[]" placeholder="Enter your postal code" required>
                                                    </div>

                                                    <!-- Submit Button -->
                                                    <button type="submit" class="btn btn-success">Save Address</button>
                                                </form>
                                            </div>
                                        </div>

                                        <!-- Script model -->

                                        <script>
                                            const modal = document.getElementById('customModal');
                                            const openBtn = document.getElementById('openModalBtn');
                                            const closeBtn = document.getElementById('closeModal');

                                            openBtn.addEventListener('click', () => {
                                                modal.style.display = 'flex';
                                            });

                                            closeBtn.addEventListener('click', () => {
                                                modal.style.display = 'none';
                                            });

                                            modal.addEventListener('click', (e) => {
                                                if (e.target === modal) {
                                                    modal.style.display = 'none';
                                                }
                                            });
                                        </script>


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

                                    <input class="checkout__input--field" placeholder="Address" name="address" type="text" required>

                                    <!-- <input class="checkout__input--field" placeholder="City" name="city" type="text" required> -->

                                    <select class="checkout_input--select_field" name="city" required>
                                        <option value="">Select your city</option>
                                        <option value="Mumbai">Mumbai</option>
                                        <option value="Delhi">Delhi</option>
                                        <option value="Bengaluru">Bengaluru</option>
                                        <option value="Hyderabad">Hyderabad</option>
                                        <option value="Ahmedabad">Ahmedabad</option>
                                        <option value="Chennai">Chennai</option>
                                        <option value="Kolkata">Kolkata</option>
                                        <option value="Pune">Pune</option>
                                        <option value="Jaipur">Jaipur</option>
                                        <option value="Lucknow">Lucknow</option>
                                    </select>

                                    <select class="checkout_input--select_field" name="country" required>
                                        <option value="India">India</option>
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
                            <button type="submit" name="cod" class="checkout_now--btn primary_btn  new_btn" style="margin-bottom:10px;">Cash on Delivery (COD)</button>

                            <!-- Razorpay Button -->
                            <button type="button" id="razorpayBtn" class="checkout_now--btn primary_btn new_btn">Pay with Razorpay</button>
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
                <div class="newsletter__banner--thumbnail position__relative">
                    <img class="newsletter__banner--thumbnail__img" src="assets/img/banner/banner-bg7.webp" alt="newsletter-banner">
                    <div class="newsletter__content newsletter__subscribe">
                        <h5 class="newsletter__content--subtitle text-white">Want to offer regularly ?</h5>
                        <h2 class="newsletter__content--title text-white h3 mb-25">Subscribe Our Newsletter <br>
                            for Get Daily Update</h2>
                        <form class="newsletter__subscribe--form position__relative" action="#">
                            <label>
                                <input class="newsletter__subscribe--input" placeholder="Enter your email address" type="email">
                            </label>
                            <button class="newsletter__subscribe--button primary__btn" type="submit">Subscribe
                                <svg class="newsletter__subscribe--button__icon" xmlns="http://www.w3.org/2000/svg" width="9.159" height="7.85" viewbox="0 0 9.159 7.85">
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

<?php include('footer.php');  ?>

<script>
    function updateCartCount() {
        $.ajax({
            url: "cart_count.php",
            type: "GET",
            dataType: "json",
            success: function(response) {
                if (response.success) {
                    $("#cartCountValue").text(response.count);
                }
            },
            error: function(xhr, status, error) {
                console.error("Cart Count Error:", error);
            }
        });
    }

    updateCartCount();
</script>

<!-- Script to show input -->
<script>
    document.getElementById("showInputBtn").addEventListener("click", function() {
        document.getElementById("addressInputWrapper").style.display = "block";
    });
</script>

<script src="https://checkout.razorpay.com/v1/checkout.js"></script>

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
                            rzp1.on('payment.failed', function(response) {
                                alert(response.error.code);
                                alert(response.error.description);
                                alert(response.error.source);
                                alert(response.error.step);
                                alert(response.error.reason);
                                alert(response.error.metadata.order_id);
                                alert(response.error.metadata.payment_id);
                            })
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