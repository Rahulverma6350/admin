<?php
session_start();
include 'include/db.php';
include 'include/header.php';

// Fetch Return Requests
$sql = "SELECT rr.*, u.name, p.product_name, oi.quantity as ordered_quantity
        FROM return_requests rr 
        JOIN user_reg u ON rr.user_id = u.id 
        JOIN products p ON rr.product_id = p.p_id
        JOIN order_items oi ON oi.order_id = rr.order_id 
            AND oi.product_id = rr.product_id
            AND (oi.color = rr.color OR (oi.color IS NULL AND rr.color IS NULL))
            AND (oi.size = rr.size OR (oi.size IS NULL AND rr.size IS NULL))
        ORDER BY rr.id DESC";

$result = $conn->query($sql);
?>

<div class="mainHeading">
    <h3>Admin - Return Requests</h3>
</div>

<div class="content">
    <div class="mainContent">
        <table id="myTable" class="table table-striped">
            <thead>
                <tr>
                    <th>s.no</th>
                    <th>Request ID</th>
                    <th>Username</th>
                    <th>Product</th>
                    <th>Color</th>
                    <th>Size</th>
                    <th>Reason</th>
                    <th>Status</th>
                    <th>Refund Status</th>
                    <th>Actions</th>
                </tr>
            </thead>
            <tbody>
                <?php
                $i=1;
                 while ($row = $result->fetch_assoc()) { ?>
                    <tr>
                        <td> <?php echo $i ?> </td>
                        <td><?php echo $row['id']; ?></td>
                        <td><?php echo $row['name']; ?></td>
                        <td><?php echo $row['product_name']; ?></td>
                        <td><?php echo $row['color']; ?></td>
                        <td><?php echo $row['size']; ?></td>
                        <td><?php echo $row['reason']; ?></td>
                        <td><?php echo $row['status']; ?></td>
                        <td><?php echo $row['refund_status']; ?></td>
                        <td>
                            <form method="POST" action="process_admin_return.php">
                                <input type="hidden" name="request_id" value="<?php echo $row['id']; ?>">
                                <input type="hidden" name="order_id" value="<?php echo $row['order_id']; ?>">
                                <input type="hidden" name="product_id" value="<?php echo $row['product_id']; ?>">
                                <input type="hidden" name="color" value="<?php echo $row['color']; ?>">
                                <input type="hidden" name="size" value="<?php echo $row['size']; ?>">
                                <input type="hidden" name="quantity" value="<?php echo $row['ordered_quantity']; ?>">

                                <?php if ($row['status'] == 'Pending') { ?>
                                    <button type="submit" name="approve_return" class="btn btn-success">Approve</button>
                                    <button type="submit" name="reject_return" class="btn btn-danger">Reject</button>
                                <?php } elseif ($row['status'] == 'Approved' && (empty($row['refund_status']) || $row['refund_status'] == 'Pending')) { ?>
                                    <button type="submit" name="process_refund" class="btn btn-primary">Process Refund</button>
                                <?php } ?>
                            </form>
                        </td>
                    </tr>
                <?php
            $i++;
            } ?>
            </tbody>
        </table>
    </div>
</div>

<?php include("include/footer.php"); ?>

<!-- SweetAlert2 for Success/Error Notification -->
<script src="https://cdn.jsdelivr.net/npm/sweetalert2@11"></script>

<script>
document.addEventListener("DOMContentLoaded", function() {
    <?php if (isset($_SESSION['success'])) { ?>
        Swal.fire({
            title: "Success!",
            text: "<?php echo $_SESSION['success']; ?>",
            icon: "success",
            confirmButtonText: "OK"
        }).then(function() {
            window.location.reload(); // Page refresh after success
        });
    <?php unset($_SESSION['success']); } ?>

    <?php if (isset($_SESSION['error'])) { ?>
        Swal.fire({
            title: "Error!",
            text: "<?php echo $_SESSION['error']; ?>",
            icon: "error",
            confirmButtonText: "Try Again"
        });
    <?php unset($_SESSION['error']); } ?>
});
</script>
