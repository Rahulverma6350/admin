<?php
include 'include/header.php'; // Include your admin panel header
include 'include/db.php'; // Include database connection

// Handle Approve/Reject Requests
if ($_SERVER['REQUEST_METHOD'] == 'POST') {
    $request_id = $_POST['request_id'];
    if (isset($_POST['approve_request'])) {
        $update_query = "UPDATE return_exchange_requests SET status = 'Approved', approved_date = NOW() WHERE id = ?";
    } elseif (isset($_POST['reject_request'])) {
        $update_query = "UPDATE return_exchange_requests SET status = 'Rejected' WHERE id = ?";
    } elseif (isset($_POST['approve_exchange'])) {
        $new_product_id = $_POST['new_product_id'];
        $update_query = "UPDATE return_exchange_requests SET status = 'Approved', new_product_id = ?, approved_date = NOW() WHERE id = ?";
    }

    $stmt = $conn->prepare($update_query);
    if (isset($_POST['approve_exchange'])) {
        $stmt->bind_param("ii", $new_product_id, $request_id);
    } else {
        $stmt->bind_param("i", $request_id);
    }

    if ($stmt->execute()) {
        echo "<script>alert('Request updated successfully!'); window.location='manage_requests.php';</script>";
    } else {
        echo "<script>alert('Failed to update request. Try again!');</script>";
    }
}

// Fetch All Requests
$sql = "SELECT r.*, u.id AS user_id, u.name, p.product_name, p.category_id, o.order_id 
        FROM return_exchange_requests r
        LEFT JOIN user_reg u ON r.user_id = u.id
        LEFT JOIN products p ON r.product_id = p.p_id
        LEFT JOIN orders o ON r.order_id = o.order_id";
$result = $conn->query($sql);
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Manage Requests</title>
    
    <!-- Bootstrap CSS -->
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css" rel="stylesheet">
    
    <!-- Custom CSS -->
    <style>
     
        .container {
            margin-top: 20px;
        }
        .table th {
            background-color: #343a40;
          
        }
        .btn {
            margin-right: 5px;
        }
        .status-approved {
            color: green;
            font-weight: bold;
        }
        .status-rejected {
            color: red;
            font-weight: bold;
        }
        .status-pending {
            color: orange;
            font-weight: bold;
        }
        select {
            width: auto;
            display: inline-block;
        }
    </style>
</head>
<body>

<div class="container">
    <h2 class="my-4 text-center">Return & Exchange Requests</h2>
    
    <table class="table table-bordered table-striped text-center">
        <thead>
            <tr>
                <th>User</th>
                <th>Order ID</th>
                <th>Product</th>
                <th>Type</th>
                <th>Status</th>
                <th>Action</th>
            </tr>
        </thead>
        <tbody>
            <?php while ($row = $result->fetch_assoc()) { ?>
            <tr>
                <td><?php echo $row['name']; ?></td>
                <td>#<?php echo $row['order_id']; ?></td>
                <td><?php echo $row['product_name']; ?></td>
                <td><?php echo $row['request_type']; ?></td>
                <td>
                    <span class="
                        <?php 
                            echo ($row['status'] == 'Approved') ? 'status-approved' :
                                 (($row['status'] == 'Rejected') ? 'status-rejected' : 'status-pending'); 
                        ?>">
                        <?php echo $row['status']; ?>
                    </span>
                </td>
                <td>
                    <?php if ($row['status'] == 'Pending') { ?>
                        <form method="POST" class="d-inline">
                            <input type="hidden" name="request_id" value="<?php echo $row['id']; ?>">
                            
                            <?php if ($row['request_type'] == 'Exchange') { ?>
                                <select name="new_product_id" class="form-select d-inline w-auto">
                                    <?php
                                    $category_query = "SELECT * FROM products WHERE category_id = ?";
                                    $stmt = $conn->prepare($category_query);
                                    $stmt->bind_param("i", $row['category_id']);
                                    $stmt->execute();
                                    $product_result = $stmt->get_result();
                                    while ($product = $product_result->fetch_assoc()) {
                                        echo "<option value='{$product['p_id']}'>{$product['product_name']}</option>";
                                    }
                                    ?>
                                </select>
                                <button type="submit" name="approve_exchange" class="btn btn-success btn-sm">Approve Exchange</button>
                            <?php } else { ?>
                                <button type="submit" name="approve_request" class="btn btn-success btn-sm">Approve</button>
                            <?php } ?>
                            
                            <button type="submit" name="reject_request" class="btn btn-danger btn-sm">Reject</button>
                        </form>
                    <?php } else { ?>
                        <span class="fw-bold text-success"><?php echo $row['status']; ?></span>
                    <?php } ?>
                </td>
            </tr>
            <?php } ?>
        </tbody>
    </table>
</div>

<!-- Bootstrap JS (optional if you need interactive components) -->
<script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/js/bootstrap.bundle.min.js"></script>

</body>
</html>

<?php include 'include/footer.php'; // Include admin footer ?>
