<?php
session_start();
include('header.php');
include("include/db.php");

// Order Details Fetch Logic
$show_invoice = false;
$order_result = [];
$items_result = [];

// Check if 'order_id' is provided for invoice
if (isset($_GET['order_id'])) {
    $show_invoice = true;
    $order_id = intval($_GET['order_id']);

    // Fetch Order Details with User Information
    $order_query = "SELECT o.order_id, o.order_date, o.total_amount, o.payment_status, o.shipping_status, o.website_url, o.traking_orderid, o.delivered_at, u.name, u.phone, o.selected_addres
                    FROM orders o
                    JOIN user_reg u ON o.user_id = u.id
                    WHERE o.order_id = ?";
    $stmt = $conn->prepare($order_query);
    $stmt->bind_param("i", $order_id);
    $stmt->execute();
    $order_result = $stmt->get_result()->fetch_assoc();

    // address 
    $selected_address = $order_result['selected_addres'] ?? '';
    $parts = explode('|', $selected_address);

    // Default empty values
    $name = $phone = $street = $city = $country = $pincode = '';

    if (count($parts) >= 6) {
        $name = trim($parts[0]);
        $phone = trim($parts[1]);
        $street = trim($parts[2]);
        $city = trim($parts[3]);
        $country = trim($parts[4]);
        $pincode = trim($parts[5]);
    }

    // Fetch Order Items with Image
    $items_query = "SELECT oi.product_id, p.product_name, oi.quantity, oi.price, p.product_image, p.p_id, oi.color, oi.size,r.refund_amount, 
    r.refund_status
FROM order_items oi
JOIN products p ON oi.product_id = p.p_id
LEFT JOIN refunds r ON oi.order_id = r.order_id AND oi.product_id = r.product_id 
WHERE oi.order_id = ?";
    $stmt_items = $conn->prepare($items_query);
    $stmt_items->bind_param("i", $order_id);
    $stmt_items->execute();
    $items_result = $stmt_items->get_result();
}



// cancel order 
if (isset($_POST['cancel_order'])) {
    $order_id = $_POST['order_id'];

    // Step 1: Fetch all order items for this order, including color and size
    $order_query = "SELECT o.*, oi.product_id, oi.quantity, oi.color, oi.size, p.product_name, p.discounted_price, p.p_id
                    FROM `orders` o 
                    JOIN `order_items` oi ON o.order_id = oi.order_id
                    JOIN `products` p ON oi.product_id = p.p_id
                    WHERE o.order_id = ?";
    $stmt = $conn->prepare($order_query);
    $stmt->bind_param("i", $order_id);
    $stmt->execute();
    $order_result = $stmt->get_result();

    $order_items = [];
    while ($row = $order_result->fetch_assoc()) {
        $order_items[] = $row;
    }

    // Step 2: Check if order is Pending
    if (!empty($order_items) && $order_items[0]['shipping_status'] == 'Pending') {

        $insert_query = "INSERT INTO `cancelled_orders` 
     (order_id, user_id, product_id, product_name, total_amount, payment_method, payment_status, order_date, color, size, quantity, cancel_date) VALUES (?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, NOW())";

        $stmt_insert = $conn->prepare($insert_query);

        foreach ($order_items as $item) {
            $cancel_amount = $item['discounted_price'] * $item['quantity'];

            // Insert into cancelled_orders
            $stmt_insert->bind_param(
                "iiisdsssssi",
                $item['order_id'],
                $item['user_id'],
                $item['product_id'],
                $item['product_name'],
                $cancel_amount,
                $item['payment_method'],
                $item['payment_status'],
                $item['order_date'],
                $item['color'],
                $item['size'],
                $item['quantity']
            );
            $stmt_insert->execute();

            // Now Restore Stock
            if (!empty($item['color']) && !empty($item['size'])) {
                // Product Variant hai
                $update_variant_stock = "UPDATE product_variants 
            SET quantity = quantity + ? 
            WHERE product_id = ? AND color = ? AND size = ?";
                $stmt_variant = $conn->prepare($update_variant_stock);
                $stmt_variant->bind_param("iiss", $item['quantity'], $item['product_id'], $item['color'], $item['size']);
                $stmt_variant->execute();
            } else {
                // Simple Product hai
                $update_product_stock = "UPDATE products 
            SET stock_quantity = stock_quantity + ? 
            WHERE p_id = ?";
                $stmt_product = $conn->prepare($update_product_stock);
                $stmt_product->bind_param("ii", $item['quantity'], $item['product_id']);
                $stmt_product->execute();
            }
        }


        // Step 4: Delete from orders table
        $delete_query = "DELETE FROM `orders` WHERE order_id = ?";
        $stmt_delete = $conn->prepare($delete_query);
        $stmt_delete->bind_param("i", $order_id);
        $stmt_delete->execute();

        echo "
        <script src='https://cdn.jsdelivr.net/npm/sweetalert2@11'></script>
        <script>
        const Toast = Swal.mixin({
          toast: true,
          position: 'top-end',   // right top corner
          showConfirmButton: false, // no OK button
          timer: 4000,            // 3 seconds
          timerProgressBar: true,
          didOpen: (toast) => {
            toast.addEventListener('mouseenter', Swal.stopTimer)
            toast.addEventListener('mouseleave', Swal.resumeTimer)
          }
        })
        
        Toast.fire({
          icon: 'success',
          title: 'Order Cancelled Successfully!'
        }).then(() => {
          window.location = 'my-account.php';
        });
        </script>
        ";
    } else {
        echo "<script>alert('Order cannot be canceled as it is already In Progress or Delivered.'); window.location='my-account.php';</script>";
    }
}


// **Check if 'cancelled' is set in URL**
$show_cancelled = isset($_GET['cancelled']);

// $colors_result = mysqli_query($conn, "SELECT DISTINCT color FROM product_variants WHERE product_id = '$product_id'");
// $colors = mysqli_fetch_all($colors_result, MYSQLI_ASSOC);

// exchange  m colour or size 
if (isset($_GET['product_id']) && isset($_GET['color'])) {
    $product_id = $_GET['product_id'];
    $color = $_GET['color'];

    $query = mysqli_query($conn, "SELECT DISTINCT size FROM product_variants WHERE product_id = '$product_id' AND color = '$color' AND quantity > 0");
    $sizes = [];

    while ($row = mysqli_fetch_assoc($query)) {
        $sizes[] = ['size' => $row['size']];
    }

    header('Content-Type: application/json');
    echo json_encode($sizes);
}

?>
<style>
    .hidden {
        display: none;
    }

    .invoice-container {
        max-width: 800px;
        margin: 20px auto;
        padding: 20px;
        border: 1px solid #ddd;
    }

    table {
        width: 100%;
        border-collapse: collapse;
    }

    th,
    td {
        padding: 10px;
        border: 1px solid #ddd;
        text-align: left;
    }

    .invoice-header {
        text-align: center;
        margin-bottom: 20px;
    }

    .total-amount {
        font-weight: bold;
        text-align: right;
        margin-top: 20px;
    }

    /* Orders Table */
    .account__table {
        width: 100%;
        border-collapse: collapse;
        margin-top: 20px;
    }

    .account__table th,
    .account__table td {
        padding: 12px;
        text-align: left;
        border: 1px solid #ddd;
    }

    .account__table th {
        background-color: #4CAF50;
        color: #fff;
    }

    .btn-primary {
        background-color: #4CAF50;
        color: #fff;
        padding: 8px 20px;
        border: none;
        border-radius: 5px;
        text-decoration: none;
        display: inline-block;
        margin-top: 5px;
        cursor: pointer;
        transition: background-color 0.3s ease;
    }

    .btn-primary:hover {
        background-color: #388E3C;
    }

    /* Invoice Container */
    .invoice-container {
        max-width: 800px;
        background-color: #ffffff;
        box-shadow: 0 4px 15px rgba(0, 0, 0, 0.1);
        padding: 30px;
        border-radius: 10px;
        border: 3px solid #4CAF50;
        margin: 20px 0;
        /* Center se hata kar left align karne ke liye margin-left: 0; */
        margin-left: 0;
        /* Invoice ko left align karne ke liye */
        font-family: Arial, sans-serif;
    }

    .invoice-header {
        text-align: left;
        /* Header text ko bhi left align kar diya */
        border-bottom: 2px solid #4CAF50;
        padding-bottom: 15px;
        margin-bottom: 20px;
    }

    .invoice-header h1 {
        color: #4CAF50;
        font-size: 28px;
        margin-bottom: 10px;
        text-align: left;
        /* Invoice ID ko bhi left align kar diya */
    }


    /* Progress Bar */
    .progress-bar {
        display: flex;
        justify-content: space-between;
        align-items: center;
        margin: 20px 0;
        position: relative;
        border: 2px solid #4CAF50;
        border-radius: 30px;
        background-color: #f9f9f9;
        padding: 10px 0;
    }

    .progress-step {
        flex: 1;
        text-align: center;
        position: relative;
        font-weight: bold;
        color: #999;
        font-size: 14px;
    }

    .progress-step::before {
        content: '';
        position: absolute;
        top: -8px;
        left: 50%;
        transform: translateX(-50%);
        width: 18px;
        height: 18px;
        background-color: transparent;
        /* Blue dot removed */
    }

    .progress-step.step-completed::after {
        content: '✔';
        position: absolute;
        top: -8px;
        left: 50%;
        transform: translate(-50%, -50%);
        color: #4CAF50;
        font-weight: bold;
        background-color: #fff;
        border: 3px solid #4CAF50;
        border-radius: 50%;
        width: 20px;
        height: 20px;
        line-height: 18px;
        text-align: center;
    }

    /* cancelled .....  */
    .order-container {
        background: white;
        padding: 20px;
        border-radius: 8px;
        box-shadow: 0 0 10px rgba(0, 0, 0, 0.1);
        max-width: 600px;
        margin: auto;
    }

    .order-header {
        display: flex;
        justify-content: space-between;
        border-bottom: 1px solid #ddd;
        padding-bottom: 10px;
        margin-bottom: 10px;
        font-size: 14px;
        color: #666;
    }

    .cancelled-text {
        font-weight: bold;
        color: red;
        font-size: 16px;
        margin-bottom: 10px;
    }

    .order-item {
        display: flex;
        gap: 15px;
    }

    .order-item img {
        width: 80px;
        height: 80px;
        border-radius: 5px;
    }

    .order-details {
        font-size: 14px;
    }

    .archive-link {
        display: inline-block;
        margin-top: 10px;
        color: blue;
        text-decoration: none;
        font-size: 14px;
    }

    .archive-link:hover {
        text-decoration: underline;
    }

    /* model  */
    .custom-modal {
        display: none;
        position: fixed;
        z-index: 1050;
        left: 0;
        top: 0;
        width: 100%;
        height: 100%;
        overflow: auto;
        background: rgba(0, 0, 0, 0.5);
    }

    .custom-modal-content {
        background: white;
        margin: 10% auto;
        padding: 20px;
        width: 400px;
        border-radius: 10px;
        position: relative;
    }

    .close-btn {
        position: absolute;
        right: 15px;
        top: 10px;
        font-size: 22px;
        font-weight: bold;
        cursor: pointer;
    }

    .invoice-header .second {
        padding-top: 70px;
    }

    @media only screen and (max-width:768px) {
        .invoice-header .second {
            padding-top: 0px;
        }
    }
</style>


<main class="main__content_wrapper">

    <!-- Start breadcrumb section -->
    <section class="breadcrumb__section breadcrumb__bg">
        <div class="container">
            <div class="row row-cols-1">
                <div class="col">
                    <div class="breadcrumb__content">
                        <h1 class="breadcrumb__content--title text-white mb-10">My Account</h1>
                        <ul class="breadcrumb__content--menu d-flex">
                            <li class="breadcrumb__content--menu__items"><a class="text-white" href="index.php">Home</a></li>
                            <li class="breadcrumb__content--menu__items"><span class="text-white">My Account</span></li>
                        </ul>
                    </div>
                </div>
            </div>
        </div>
    </section>
    <!-- End breadcrumb section -->

    <!-- my account section start -->
    <section class="my__account--section section--padding">
        <div class="container">
            <p class="account__welcome--text">Hello, Admin welcome to your dashboard!</p>
            <div class="my__account--section__inner border-radius-10 d-flex">
                <div class="account__left--sidebar">
                    <h3 class="account__content--title mb-20">My Profile</h3>
                    <ul class="account__menu">
                        <li class="account__menu--list active"><a href="my-account.php">Dashboard</a></li>
                        <li class="account__menu--list"><a href="my-account-2.php">Addresses</a></li>
                        <li class="account__menu--list"><a href="wishlist.php">Wishlist</a></li>
                        <li class="account__menu--list <?= $show_cancelled ? 'active' : ''; ?>">
                            <a href="my-account.php?cancelled=true">Cancelled Order</a>
                        </li>
                        <li class="account__menu--list"><a href="logout.php">Log Out</a></li>
                    </ul>
                </div>
                <div class="account__wrapper">
                    <div class="account__content">
                        <?php if (!$show_cancelled) { ?>
                            <h3 class="account__content--title mb-20">Orders History</h3>
                            <div class="account__table--area <?php echo $show_invoice ? 'hidden' : ''; ?>" id="orders-section">

                                <?php
                                $user_id = $_SESSION['user_id']; // Logged-in user ID

                                $sql = "SELECT o.*, 
        GROUP_CONCAT(DISTINCT p.product_name SEPARATOR ', ') AS product_names, 
        GROUP_CONCAT(DISTINCT p.p_id SEPARATOR ', ') AS product_ids, 
        GROUP_CONCAT(DISTINCT oi.quantity SEPARATOR ', ') AS quantities, 
        GROUP_CONCAT(DISTINCT p.discounted_price SEPARATOR ', ') AS prices
FROM orders o
JOIN order_items oi ON o.order_id = oi.order_id
JOIN products p ON oi.product_id = p.p_id
WHERE o.user_id = ?
GROUP BY o.order_id DESC";  // Sirf order ID pe group karenge

                                $stmt = $conn->prepare($sql);
                                $stmt->bind_param("i", $user_id);
                                $stmt->execute();
                                $result = $stmt->get_result();
                                ?>

                                <table class="account__table">
                                    <thead class="account__table--header">
                                        <tr class="account__table--header__child">
                                            <th>Order</th>
                                            <th>Date</th>
                                            <th>Products</th>
                                            <th>Payment Status</th>
                                            <th>Total</th>
                                            <th>Shipping Status</th>
                                            <th>Action</th>
                                            <th>Cancel</th>
                                        </tr>
                                    </thead>
                                    <tbody class="account__table--body mobile__none">
                                        <?php
                                        if ($result->num_rows > 0) {
                                            while ($row = $result->fetch_assoc()) {
                                        ?>
                                                <tr class="account__table--body__child">
                                                    <td>#<?php echo $row['order_id']; ?></td>
                                                    <td><?php echo date('F d, Y', strtotime($row['order_date'])); ?></td>
                                                    <td><?php echo $row['product_names']; ?></td> <!-- Multiple products ek hi row me -->
                                                    <td><?php echo $row['payment_status']; ?></td>
                                                    <td>₹&nbsp;<?php echo $row['total_amount']; ?></td>
                                                    <td><?php echo $row['shipping_status']; ?></td>
                                                    <td>
                                                        <a href="my-account.php?order_id=<?php echo $row['order_id']; ?>">View Invoice</a>
                                                    </td>
                                                    <td>
                                                        <?php if ($row['shipping_status'] == 'Pending') { ?>
                                                            <form method="POST">
                                                                <input type="hidden" name="order_id" value="<?php echo $row['order_id']; ?>">
                                                                <button type="submit" name="cancel_order" class="btn btn-danger" onclick="return confirm('Are you sure you want to cancel this order?')">
                                                                    Cancel
                                                                </button>
                                                            </form>
                                                        <?php } else { ?>
                                                            <span style="color: #888;">Not Available</span>
                                                        <?php } ?>
                                                    </td>
                                                </tr>
                                        <?php
                                            }
                                        } else {
                                            echo "<tr><td colspan='8'>No orders found.</td></tr>";
                                        }
                                        ?>
                                    </tbody>
                                </table>
                            </div>
                        <?php } else { ?>

                            <!-- ✅ Cancelled Orders Section -->
                            <h3 class="account__content--title mb-20">Cancelled Orders:</h3>
                            <?php
                            $userr = $_SESSION['user_id'];
                            $sqls = "SELECT co.*, u.name AS username, p.product_name, p.product_image
FROM cancelled_orders co
JOIN user_reg u ON co.user_id = u.id
JOIN products p ON co.product_id = p.p_id
WHERE co.user_id = $userr
ORDER BY co.cancel_date DESC";

                            $cancelldRes = mysqli_query($conn, $sqls);

                            // Step 1: Group data by order_id
                            $groupedOrders = [];
                            while ($rows = $cancelldRes->fetch_assoc()) {
                                $groupedOrders[$rows['order_id']][] = $rows;
                            }

                            // Step 2: Loop through grouped data
                            if (!empty($groupedOrders)) {
                                foreach ($groupedOrders as $order_id => $products) {
                                    $first = $products[0]; // first row for order level details
                            ?>
                                    <div class="order-container">
                                        <div class="order-header">
                                            <div>ORDER PLACED<br><strong><?php echo date('F d, Y', strtotime($first['order_date'])); ?></strong></div>
                                            <div>TOTAL<br><strong>₹<?php echo array_sum(array_column($products, 'total_amount')); ?></strong></div>
                                            <div>SHIP TO<br><strong><?php echo $first['username']; ?></strong></div>
                                            <div>ORDER # <?php echo $order_id; ?></div>
                                        </div>
                                        <div class="cancelled-text">Cancelled</div>

                                        <?php foreach ($products as $item) { ?>
                                            <div class="order-item">
                                                <img src="admin/img/<?php echo $item['product_image']; ?>" alt="Product Image">
                                                <div class="order-details">
                                                    <strong><?php echo $item['product_name']; ?></strong><br>
                                                    <span style="color: #444;">Price: ₹<?php echo number_format($item['total_amount'], 2); ?></span><br>

                                                    <?php if (!empty($item['color']) && !empty($item['size'])) { ?>
                                                        <span>Color: <?php echo ucfirst($item['color']); ?></span><br>
                                                        <span>Size: <?php echo strtoupper($item['size']); ?></span><br>
                                                    <?php } ?>

                                                    <span>Quantity: <?php echo $item['quantity']; ?></span>

                                                    <p style="margin-top: 5px;">This product was cancelled.</p>
                                                </div>
                                            </div>
                                        <?php } ?>



                                        <a href="my-account.php" class="archive-link">order-history</a>
                                    </div>
                            <?php
                                }
                            } else {
                                echo "<p>No cancelled orders found.</p>";
                            }
                            ?>

                        <?php } ?>


                        <!-- Invoice Section -->
                        <?php if ($show_invoice && $order_result) { ?>
                            <!-- Progress Bar -->
                            <div class="progress-bar">
                                <div class="progress-step <?php echo ($order_result['shipping_status'] == 'Pending' || $order_result['shipping_status'] == 'In Progress' || $order_result['shipping_status'] == 'Delivered') ? 'step-completed' : ''; ?>">
                                    Pending
                                </div>

                                <div class="progress-step <?php echo ($order_result['shipping_status'] == 'In Progress' || $order_result['shipping_status'] == 'Delivered') ? 'step-completed' : ''; ?>">
                                    In Progress
                                </div>

                                <div class="progress-step <?php echo ($order_result['shipping_status'] == 'Delivered') ? 'step-completed' : ''; ?>">
                                    Delivered
                                </div>
                            </div>

                            <div class="invoice-container">
                                <!-- Invoice Header -->
                                <div class="invoice-header row">
                                    <div class="first col-md-6">
                                        <h1>Invoice #<?php echo $order_result['order_id']; ?></h1>
                                        <h4><?php echo $name; ?></h4>
                                        <p><strong>Order ID:</strong> <?php echo $order_result['order_id']; ?></p>
                                        <p><strong>Phone No.:</strong> <?php echo $phone; ?></p>
                                        <p><strong>Billing Address:</strong> <?php echo "$street, $city, $country - $pincode"; ?></p>
                                        <p><strong>Shipping Address:</strong> Dhaka, Mirpur12, London, England</p>
                                        <p><strong>Order Date:</strong> <?php echo $order_result['order_date']; ?></p>
                                    </div>
                                    <div class="second col-md-6">
                                        <p><strong>Delivered Date:</strong> <?php echo $order_result['delivered_at']; ?></p>
                                        <p><strong>Payment Status:</strong> <?php echo $order_result['payment_status']; ?></p>
                                        <p><strong>Shipping Status:</strong> <?php echo $order_result['shipping_status']; ?></p>
                                    </div>
                                </div>
                                <!-- traking website or orderId  -->
                                <?php if ($order_result['shipping_status'] == 'In Progress') { ?>
                                    <div class="tracking-info" style="background: #f2f2f2; padding: 15px; margin-top: 15px; border-radius: 8px;">
                                        <h4 style="margin-bottom: 10px;">📦 Tracking Info</h4>
                                        <p><strong>Tracking Website:</strong>
                                            <a href="<?php echo $order_result['website_url']; ?>" target="_blank">
                                                <?php echo $order_result['website_url']; ?>
                                            </a>
                                        </p>
                                        <p><strong>Tracking ID:</strong> <?php echo $order_result['traking_orderid']; ?></p>
                                    </div>
                                <?php } ?>

                                <!-- Products Ordered -->
                                <h3>Products Ordered:</h3>
                                <table>
                                    <tr>
                                        <th>Image</th>
                                        <th>Product Name</th>
                                        <th>Quantity</th>
                                        <th>Color</th>
                                        <th>Size</th>
                                        <th>Price (₹)</th>
                                        <th>Return</th>
                                        <th>Status</th>
                                        <th>Exchange</th>
                                    </tr>
                                    <?php
                                    $subtotal = 0;
                                    while ($item = $items_result->fetch_assoc()) {
                                        $subtotal += $item['quantity'] * $item['price'];
                                        $order_id = $order_result['order_id'];
                                        $product_id = $item['product_id'];

                                        // ✅ Check if product is returned
                                        $return_sql = "SELECT * FROM return_requests WHERE order_id = ? AND product_id = ? AND status = 'Approved'";
                                        $stmt = $conn->prepare($return_sql);
                                        $stmt->bind_param("ii", $order_id, $product_id);
                                        $stmt->execute();
                                        $return_result = $stmt->get_result();
                                        $is_returned = $return_result->num_rows > 0; // If found, product is returned
                                    ?>
                                        <tr>
                                            <td>
                                                <img src="admin/img/<?php echo $item['product_image']; ?>"
                                                    alt="Product Image"
                                                    style="width: 70px; height: 60px; object-fit: cover;">
                                            </td>
                                            <td><?php echo $item['product_name']; ?></td>
                                            <td><?php echo $item['quantity']; ?></td>
                                            <td>
                                                <?php
                                                if (!empty($item['color']) && $item['color'] !== 'default_color') {
                                                    echo htmlspecialchars($item['color']);
                                                }
                                                ?>
                                            </td>
                                            <td>
                                                <?php
                                                if (!empty($item['size']) && $item['size'] !== 'default_size') {
                                                    echo htmlspecialchars($item['size']);
                                                }
                                                ?>
                                            </td>

                                            <td>₹<?php echo $item['price']; ?></td>

                                            <!-- ✅ Return Button -->
                                            <?php
                                            $exchange_pending = false;

                                            $exchange_status = null;

                                            $exchange_check_sql = "SELECT status FROM exchanges 
                       WHERE order_id = ? AND product_id = ? AND original_size = ? AND original_color = ? 
                       ORDER BY id DESC LIMIT 1";

                                            $ex_stmt = $conn->prepare($exchange_check_sql);
                                            $ex_stmt->bind_param("iiss", $order_id, $product_id, $item['size'], $item['color']);
                                            $ex_stmt->execute();
                                            $ex_result = $ex_stmt->get_result();

                                            if ($ex_result->num_rows > 0) {
                                                $exchange_row = $ex_result->fetch_assoc();
                                                $exchange_status = $exchange_row['status'];

                                                if ($exchange_status == 'Pending') {
                                                    $exchange_pending = true;
                                                }
                                            }
                                            ?>
                                            <td>
                                                <?php
                                                $return_status = null;
                                                $is_returned = false;

                                                $return_check_sql = "SELECT * FROM return_requests 
                     WHERE order_id = ? AND product_id = ? AND size = ? AND color = ?";
                                                $stmt = $conn->prepare($return_check_sql);
                                                $stmt->bind_param("iiss", $order_id, $product_id, $item['size'], $item['color']);
                                                $stmt->execute();
                                                $return_result = $stmt->get_result();

                                                if ($order_result['shipping_status'] == 'Delivered' && !$exchange_pending) {
                                                    $return_pending = false;

                                                    if ($return_result->num_rows > 0) {
                                                        $return_row = $return_result->fetch_assoc();
                                                        $return_status = $return_row['status'];

                                                        if ($return_status == 'Approved') {
                                                            $is_returned = true;
                                                        }

                                                        if ($return_status == 'Pending') {
                                                            $return_pending = true;
                                                ?>
                                                            <form method="POST" action="cancel_return.php">
                                                                <input type="hidden" name="order_id" value="<?php echo $order_id; ?>">
                                                                <input type="hidden" name="product_id" value="<?php echo $product_id; ?>">
                                                                <input type="hidden" name="size" value="<?php echo $item['size']; ?>">
                                                                <input type="hidden" name="color" value="<?php echo $item['color']; ?>">
                                                                <button type="submit" name="cancel_return_request" class="btn btn-danger">
                                                                    Cancel Return
                                                                </button>
                                                            </form>
                                                        <?php
                                                        } else {
                                                            echo "<span style='color: gray;'>Return " . $return_status . "</span>";
                                                        }
                                                    } else {
                                                        ?>
                                                        <form method="POST" action="process_return.php">
                                                            <input type="hidden" name="order_id" value="<?php echo $order_id; ?>">
                                                            <input type="hidden" name="product_id" value="<?php echo $product_id; ?>">
                                                            <input type="hidden" name="size" value="<?php echo $item['size']; ?>">
                                                            <input type="hidden" name="color" value="<?php echo $item['color']; ?>">
                                                            <input type="hidden" name="quantity" value="<?php echo $item['quantity']; ?>">
                                                            <!-- Return Button -->
                                                            <button type="button" class="btn btn-warning open-return-modal" name="return_request"
                                                                data-modal-id="returnModal_<?php echo $product_id . '_' . $item['color'] . '_' . $item['size']; ?>">
                                                                Return
                                                            </button>

                                                        </form>
                                                <?php
                                                    }
                                                } else {
                                                    echo "<span style='color: gray;'>Not Available</span>";
                                                }
                                                ?>
                                            </td>

                                            <!-- Return Reason Modal -->
                                            <div class="custom-modal" id="returnModal_<?php echo $product_id . '_' . $item['color'] . '_' . $item['size']; ?>">
                                                <div class="custom-modal-content">
                                                    <span class="custom-close">&times;</span>
                                                    <form action="process_return.php" method="POST">
                                                        <h4>Select Reason for Return</h4>
                                                        <input type="hidden" name="order_id" value="<?php echo $order_id; ?>">
                                                        <input type="hidden" name="product_id" value="<?php echo $product_id; ?>">
                                                        <input type="hidden" name="size" value="<?php echo $item['size']; ?>">
                                                        <input type="hidden" name="color" value="<?php echo $item['color']; ?>">
                                                        <input type="hidden" name="quantity" value="<?php echo $item['quantity']; ?>">

                                                        <div>
                                                            <label><input type="radio" name="reason" value="Product not needed anymore" required> Product not needed anymore</label><br>
                                                            <label><input type="radio" name="reason" value="Quality issue"> Quality issue</label><br>
                                                            <label><input type="radio" name="reason" value="Size/Fit issue"> Size/Fit issue</label><br>
                                                            <label><input type="radio" name="reason" value="Damaged/Used product"> Damaged/Used product</label><br>
                                                            <label><input type="radio" name="reason" value="Item Missing in the package"> Item Missing in the package</label><br>
                                                            <label><input type="radio" name="reason" value="Different product delivered"> Different product delivered</label><br>
                                                        </div>
                                                        <br>
                                                        <button type="submit" name="return_request" class="btn btn-primary">Submit</button>
                                                        <button type="button" class="btn btn-secondary custom-close">Cancel</button>
                                                    </form>
                                                </div>
                                            </div>
                                            <!-- ✅ Return Request Status -->
                                            <td>
                                                <?php
                                                if ($return_status) {
                                                    if ($return_status == 'Pending') {
                                                        echo "<span style='color: blue;'>Return Requested</span> - ";
                                                        echo "<span style='color: orange;'>Pending</span>";
                                                    } elseif ($return_status == 'Approved') {
                                                        echo "<span style='color: green;'>Return Approved</span>";

                                                        // ✅ Refund Status Check
                                                        if (!empty($return_row['refund_status']) && $return_row['refund_status'] == 'Processed') {
                                                            echo "<br><span style='color: green;'>Refund Completed (₹" . $item['refund_amount'] . ")</span>";
                                                        } else {
                                                            echo "<br><span style='color: red;'>Refund Pending</span>";
                                                        }
                                                    } elseif ($return_status == 'Rejected') {
                                                        echo "<span style='color: red;'>Request Rejected</span>";
                                                    }
                                                } else {
                                                    echo "<span style='color: gray;'>No Request</span>";
                                                }
                                                ?>
                                            </td>

                                            <!-- ✅ Exchange Button (Show Only If Not Returned) -->
                                            <?php
                                            $show_exchange_button = false;

                                            if (
                                                $order_result['shipping_status'] == 'Delivered' &&
                                                !$is_returned &&
                                                $exchange_status !== 'Approved' &&
                                                !$exchange_pending &&
                                                !$return_pending
                                            ) {
                                                $delivered_at = $order_result['delivered_at'];
                                                if (!empty($delivered_at)) {
                                                    $delivered_time = strtotime($delivered_at);
                                                    $seven_days_later = strtotime('+7 days', $delivered_time);
                                                    $now = time();
                                                    if ($now <= $seven_days_later) {
                                                        $show_exchange_button = true;
                                                    }
                                                }
                                            }

                                            $product_id = $item['product_id'];
                                            $curr_size = $item['size'];
                                            $curr_color = $item['color'];

                                            // Fetch size and color variants
                                            $size_result = mysqli_query($conn, "SELECT DISTINCT size AS size FROM product_variants WHERE product_id = '$product_id'");
                                            $color_result = mysqli_query($conn, "SELECT DISTINCT color AS color FROM product_variants WHERE product_id = '$product_id'");

                                            $sizes = mysqli_fetch_all($size_result, MYSQLI_ASSOC);
                                            $colors = mysqli_fetch_all($color_result, MYSQLI_ASSOC);

                                            $has_valid_size = !empty($curr_size) && $curr_size !== 'default_size';
                                            $has_valid_color = !empty($curr_color) && $curr_color !== 'default_color';

                                            ?>
                                            <td>
                                                <?php if ($show_exchange_button && ($has_valid_size || $has_valid_color)) { ?>

                                                    <!-- ✅ Exchange Button -->
                                                    <button class="btn btn-info open-exchange-modal" data-modal-id="customExchangeModal_<?php echo $item['product_id']; ?>">
                                                        Exchange
                                                    </button>

                                                    <!-- Modal -->
                                                    <div class="custom-modal" id="customExchangeModal_<?php echo $product_id; ?>">
                                                        <div class="custom-modal-content">
                                                            <span class="close-btn" data-modal-id="customExchangeModal_<?php echo $product_id; ?>">&times;</span>

                                                            <h4>Exchange Product:-</h4>

                                                            <!-- Product Info Section -->
                                                            <div class="d-flex align-items-start mb-3">
                                                                <!-- Product Image -->
                                                                <div style="width: 100px; margin-right: 15px;">
                                                                    <img src="admin/img/<?= $item['product_image'] ?>" alt="Product Image" style="width: 100%; border-radius: 8px;">
                                                                </div>

                                                                <!-- Product Details -->
                                                                <div>
                                                                    <p><strong>Product:</strong> <?= $item['product_name'] ?></p>
                                                                    <p><strong>Current:</strong> Size - <?= $curr_size ?>, Color - <?= $curr_color ?></p>
                                                                    <p><strong>Quantity:</strong> <?= $item['quantity'] ?></p>
                                                                </div>
                                                            </div>

                                                            <!-- Exchange Form -->
                                                            <form method="POST" action="process_exchange.php">
                                                                <input type="hidden" name="product_id" value="<?= $product_id ?>">
                                                                <input type="hidden" name="order_id" value="<?= $order_id ?>">
                                                                <input type="hidden" name="old_size" value="<?= $curr_size ?>">
                                                                <input type="hidden" name="old_color" value="<?= $curr_color ?>">

                                                                <!-- Choose New Color -->
                                                                <div class="mb-3">
                                                                    <label>Choose New Color</label>
                                                                    <select name="new_color" id="newColor_<?= $product_id ?>" class="form-select select-color"
                                                                        data-product-id="<?= $product_id ?>"
                                                                        data-current-size="<?= $curr_size ?>"
                                                                        data-ordered-color="<?= $curr_color ?>">
                                                                        <option value="">Select Color</option>
                                                                        <?php foreach ($colors as $rowc): ?>
                                                                            <option value="<?= $rowc['color'] ?>"><?= ucfirst($rowc['color']) ?></option>
                                                                        <?php endforeach; ?>
                                                                    </select>
                                                                </div>

                                                                <!-- Choose New Size (Loaded via AJAX) -->
                                                                <div class="mb-3">
                                                                    <label>Choose New Size</label>
                                                                    <select name="new_size" id="newSize_<?= $product_id ?>" class="form-select" required>
                                                                        <option value="">Select Size</option>
                                                                    </select>
                                                                </div>

                                                                <div class="text-end">
                                                                    <button type="submit" name="exchange_request" class="btn btn-primary">Submit Exchange</button>
                                                                </div>
                                                            </form>
                                                        </div>
                                                    </div>

                                                <?php } else { ?>
                                                    <span style="color: gray;">Exchange not available</span>
                                                <?php } ?>
                                            </td>
                                        </tr>
                                    <?php } ?>
                                </table>

                                <?php
                                // ✅ Fetch total refunded amount
                                $refund_sql = "SELECT SUM(refund_amount) AS total_refunded FROM refunds WHERE order_id = ?";
                                $stmt = $conn->prepare($refund_sql);
                                $stmt->bind_param("i", $order_id);
                                $stmt->execute();
                                $result = $stmt->get_result();
                                $refund_row = $result->fetch_assoc();

                                $total_refunded = $refund_row['total_refunded'] ?? 0; // Default 0 if no refund

                                // ✅ Calculate new payable amount
                                $payable_amount = $order_result['total_amount'] - $total_refunded;
                                ?>
                                <h4>Total Payable Amount: ₹<?php echo $payable_amount; ?></h4>

                                <div style="text-align: center; margin-top: 20px;">
                                    <a href="my-account.php" class="btn btn-primary">Back to Orders</a>
                                </div>

                            </div>

                            <!-- exchangeproduc  -->
                            <?php
                            $exchange_sql = "SELECT e.*, p.product_name, p.product_image 
                 FROM exchanges e
                 JOIN products p ON e.product_id = p.p_id
                 WHERE e.order_id = ?";
                            $stmt = $conn->prepare($exchange_sql);
                            $stmt->bind_param("i", $order_id);
                            $stmt->execute();
                            $exchange_result = $stmt->get_result();
                            ?>
                            <?php if ($exchange_result->num_rows > 0) { ?>
                                <h4>Exchange Product</h4>
                                <table class="table table-bordered">
                                    <tr>
                                        <th>Image</th>
                                        <th>Product</th>
                                        <th>Old Size</th>
                                        <th>Old Color</th>
                                        <th>New Size</th>
                                        <th>New Color</th>
                                        <th>Status</th>
                                    </tr>

                                    <?php while ($ex = $exchange_result->fetch_assoc()) { ?>
                                        <tr>
                                            <td>
                                                <img src="admin/img/<?= $ex['product_image']; ?>" style="width: 60px; height: 60px;">
                                            </td>
                                            <td><?= $ex['product_name']; ?></td>
                                            <td><?= $ex['original_size']; ?></td>
                                            <td><?= $ex['original_color']; ?></td>
                                            <td><?= $ex['new_size']; ?></td>
                                            <td><?= $ex['new_color']; ?></td>
                                            <td>
                                                <?php
                                                if ($ex['status'] == 'Pending') {
                                                    echo "<span class='text-warning'>Pending</span>";
                                                } elseif ($ex['status'] == 'Approved') {
                                                    echo "<span class='text-success'>Approved</span>";
                                                } elseif ($ex['status'] == 'Rejected') {
                                                    echo "<span class='text-danger'>Rejected</span>";
                                                }
                                                ?>
                                            </td>
                                        </tr>
                                    <?php } ?>
                                </table>
                            <?php } ?>

                        <?php } ?>
                    </div>
                </div>
            </div>
    </section>


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



<?php include('footer.php'); ?>
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

<!-- return model ka  -->
<script>
    document.addEventListener("DOMContentLoaded", function() {
        // Open modal
        document.querySelectorAll(".open-return-modal").forEach(btn => {
            btn.addEventListener("click", function() {
                const modalId = this.getAttribute("data-modal-id");
                document.getElementById(modalId).style.display = "block";
            });
        });

        // Close modal on close button
        document.querySelectorAll(".custom-close").forEach(btn => {
            btn.addEventListener("click", function() {
                this.closest(".custom-modal").style.display = "none";
            });
        });

        // Close on outside click
        window.onclick = function(event) {
            if (event.target.classList.contains("custom-modal")) {
                event.target.style.display = "none";
            }
        };
    });
</script>

<!-- model exchange ka  -->
<script>
    document.querySelectorAll(".open-exchange-modal").forEach(function(button) {
        button.addEventListener("click", function() {
            const modalId = this.getAttribute("data-modal-id");
            document.getElementById(modalId).style.display = "block";
        });
    });

    document.querySelectorAll(".close-btn").forEach(function(closeBtn) {
        closeBtn.addEventListener("click", function() {
            const modalId = this.getAttribute("data-modal-id");
            document.getElementById(modalId).style.display = "none";
        });
    });

    // Close modal when clicking outside content
    window.onclick = function(event) {
        document.querySelectorAll(".custom-modal").forEach(function(modal) {
            if (event.target === modal) {
                modal.style.display = "none";
            }
        });
    };
</script>

<!-- exchange ka colour se size salect  -->
<script>
    document.querySelectorAll('.select-color').forEach(select => {
        select.addEventListener('change', function() {
            const selectedColor = this.value;
            const productId = this.getAttribute('data-product-id');
            const currentSize = this.getAttribute('data-current-size');
            const orderedColor = this.getAttribute('data-ordered-color');

            fetch(`fetch_sizes_by_color.php?product_id=${productId}&color=${selectedColor}&exclude_size=${currentSize}&ordered_color=${orderedColor}`)
                .then(res => res.json())
                .then(data => {
                    const sizeSelect = document.getElementById(`newSize_${productId}`);
                    sizeSelect.innerHTML = '<option value="">Select Size</option>';
                    data.forEach(size => {
                        sizeSelect.innerHTML += `<option value="${size}">${size.toUpperCase()}</option>`;
                    });
                });
        });
    });
</script>