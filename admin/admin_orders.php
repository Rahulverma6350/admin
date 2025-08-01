
<?php
session_start();
date_default_timezone_set('Asia/Kolkata');
include('include/db.php');
include('include/header.php');

// Fetch orders
$sqls = "SELECT * FROM orders ORDER BY order_id DESC";
$result = mysqli_query($conn, $sqls);

if (isset($_POST['update_shipping'])) {
    $order_id = $_POST['order_id'];
    $shipping_status = $_POST['shipping_status'];
    $website_url = $_POST['website_url'];
    $shipping_order_id = $_POST['shipping_order_id'];

    if ($shipping_status == 'Delivered') {
        $delivered_at = date('Y-m-d H:i:s');

        $sql = "UPDATE orders SET shipping_status = ?, website_url = ?, traking_orderid = ?, delivered_at = ? WHERE order_id = ?";
        $stmt = $conn->prepare($sql);
        $stmt->bind_param("ssssi", $shipping_status, $website_url, $shipping_order_id, $delivered_at, $order_id);
    } else {
        $sql = "UPDATE orders SET shipping_status = ?, website_url = ?, traking_orderid = ? WHERE order_id = ?";
        $stmt = $conn->prepare($sql);
        $stmt->bind_param("sssi", $shipping_status, $website_url, $shipping_order_id, $order_id);
    }

    if ($stmt->execute()) {          
        echo "
        <script src='https://cdn.jsdelivr.net/npm/sweetalert2@11'></script>
        <script>
        const Toast = Swal.mixin({
          toast: true,
          position: 'top-end',   // right top corner
          showConfirmButton: false, // no OK button
          timer: 3000,            // 3 seconds
          timerProgressBar: true,
          didOpen: (toast) => {
            toast.addEventListener('mouseenter', Swal.stopTimer)
            toast.addEventListener('mouseleave', Swal.resumeTimer)
          }
        })
        
        Toast.fire({
          icon: 'success',
          title: 'Shipping status updated successfully!!'
        }).then(() => {
          window.location = 'admin_orders.php';
        });
        </script>
        ";
        
        
    } else {
        echo "<script>alert('Failed to update shipping status.'); window.location='admin_orders.php';</script>";
    }
}

?>

<style>
/* Modal Overlay */
.modal-overlay {
    display: none; /* ✅ Modal hidden by default */
    position: fixed;
    top: 0; left: 0; right: 0; bottom: 0;
    background-color: rgba(0, 0, 0, 0.5);
    z-index: 1000;
    justify-content: center;
    align-items: center;
}

/* Modal Content */
.modal-content {
    background-color: #fff;
    padding: 20px 30px;
    border-radius: 10px;
    width: 100%;
    max-width: 400px;
    box-shadow: 0 4px 15px rgba(0, 0, 0, 0.3);
    text-align: center;
}

/* Close Button */
.close-btn {
    position: absolute;
    top: 10px;
    right: 15px;
    font-size: 20px;
    cursor: pointer;
    color: red;
}

/* Success Button */
.btn-success {
    background-color: #4CAF50;
    color: #fff;
    border: none;
    padding: 8px 20px;
    border-radius: 5px;
    cursor: pointer;
}

.btn-success:hover {
    background-color: #388E3C;
}

/* ...sdfgh  */
/* Button Container */
.action-buttons {
    display: flex;
    gap: 8px;  /* Button spacing */
}

/* Common Button Styling */
.btn {
    padding: 6px 12px;
   
    border-radius: 5px;
    cursor: pointer;
    color: #fff;
    font-size: 16px;
    display: inline-flex;
    align-items: center;
    justify-content: center;
    text-decoration: none; /* Remove underline */
    width: 40px;
    height: 40px;
}

/* Individual Button Styles */
.btn-view {
    background-color: #2196F3;
}

.btn-delete {
    background-color: #F44336;
}

.btn-delivery {
    background-color: #4CAF50;
}

/* Icon Alignment */
.btn i {
    font-size: 18px;
}

</style>


<div class="mainHeading">
    <h3>Admin_Orders</h3>
</div>

<div class="content">
    <div class="mainContent">
    <!-- myTable -->
    <table id="myTable" class="table table-striped">
    <thead>
        <tr>
            <th>s.no</th>
            <th>Order ID</th>
            <th>User ID</th>
            <th>Total Amount</th>
            <th>Payment Status</th>
            <th>Shipping Status</th>
            <th>Order Date</th>
            <th>Actions</th>
        </tr>
    </thead>
    <tbody>
        <?php
        $i=1;
         while($row = mysqli_fetch_assoc($result)) { ?>
        <tr>
            <td><?php echo $i ?></td>
            <td>#<?php echo $row['order_id']; ?></td>
            <td><?php echo $row['user_id']; ?></td>
            <td>₹<?php echo $row['total_amount']; ?></td>
            <td><?php echo $row['payment_status']; ?></td>
            <td><?php echo $row['shipping_status']; ?></td>
            <td><?php echo date('F d, Y h:i A', strtotime($row['order_date'])); ?></td>
         
            <td class="action-buttons">
    <a href="invoice_page.php?order_id=<?php echo $row['order_id']; ?>" 
       class="btn btn-view" title="View Invoice">
       <i class="fa-solid fa-eye"></i>
    </a>

    <a href="delete_order.php?order_id=<?php echo $row['order_id']; ?>" 
       class="btn btn-delete" 
       onclick="return confirm('Are you sure?')" 
       title="Delete Order">
       <i class="fa-solid fa-trash"></i>
    </a>

    <a href="#" 
       class="btn btn-delivery" 
       onclick="openModal('<?php echo $row['order_id']; ?>', '<?php echo $row['shipping_status']; ?>', '<?php echo $row['website_url']; ?>', '<?php echo $row['traking_orderid']; ?>')" 
       title="Update Shipping Status">
       <i class="fa-solid fa-truck"></i>
    </a>
</td>

        </tr>
        <?php 
    $i++;
    } ?>
    </tbody>
</table>

    </div>
</div>

<!-- Custom Modal -->

<!-- Custom Modal -->
<div id="updateShippingModal" class="modal-overlay">
    <div class="modal-content">
        <span class="close-btn" onclick="closeModal()">&times;</span>
        <h2>Update Shipping Status</h2>
        <form method="POST">
            <input type="hidden" name="order_id" id="modal_order_id">
            
            <label>Select Shipping Status:</label>
            <select name="shipping_status" id="modal_shipping_status" required>
                <option value="Pending">Pending</option>
                <option value="In Progress">In Progress</option>
                <option value="Delivered">Delivered</option>
            </select>

            <label>Website URL:</label>
            <input type="text" name="website_url" id="modal_website_url" placeholder="Enter website URL" required>

            <label>Shipping Order ID:</label>
            <input type="text" name="shipping_order_id" id="modal_shipping_order_id" placeholder="Enter shipping order ID" required>

            <div class="text-center mt-3">
                <button type="submit" name="update_shipping" class="btn-success">Update</button>
            </div>
        </form>
    </div>
</div>

<?php
include("include/footer.php");
?>

<script>
function openModal(orderId, shippingStatus, websiteUrl = '', shippingOrderId = '') {
    const modal = document.getElementById('updateShippingModal');
    document.getElementById('modal_order_id').value = orderId;
    document.getElementById('modal_shipping_status').value = shippingStatus; 
    document.getElementById('modal_website_url').value = websiteUrl; 
    document.getElementById('modal_shipping_order_id').value = shippingOrderId;
    modal.style.display = 'flex'; // Modal Show
}

// Modal Close Function
function closeModal() {
    const modal = document.getElementById('updateShippingModal');
    modal.style.display = 'none'; // Modal Hide
}

// Modal Ko Bahar Click Karne Par Band Karne Ka Function
window.onclick = function(event) {
    const modal = document.getElementById('updateShippingModal');
    if (event.target === modal) {
        closeModal();
    }
}
</script>





