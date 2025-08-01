<!-- admin-return-request.php  -->
<?php
session_start();
include 'include/db.php';
include 'include/header.php';

$sql = "SELECT rr.*, u.name, p.product_name, p.stock_quantity
        FROM return_requests rr 
        JOIN user_reg u ON rr.user_id = u.id 
        JOIN products p ON rr.product_id = p.p_id
        ORDER BY rr.id DESC";

$result = $conn->query($sql);
?>

<div class="mainHeading">
    <h3>Admin Return Requests</h3>
</div>

<div class="content">
    <div class="mainContent">
        <table id="myTable" class="table table-striped">
            <thead>
                <tr>
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
                <?php while ($row = $result->fetch_assoc()) { ?>
                    <tr>
                        <td><?php echo $row['id']; ?></td>
                        <td><?php echo htmlspecialchars($row['name']); ?></td>
                        <td><?php echo htmlspecialchars($row['product_name']); ?></td>
                        <td><?php echo htmlspecialchars($row['color']); ?></td>
                        <td><?php echo htmlspecialchars($row['size']); ?></td>
                        <td><?php echo htmlspecialchars($row['reason']); ?></td>
                        <td>
                            <?php if ($row['status'] == 'Pending') { ?>
                                <span class="badge bg-warning">Pending</span>
                            <?php } elseif ($row['status'] == 'Approved') { ?>
                                <span class="badge bg-success">Approved</span>
                            <?php } elseif ($row['status'] == 'Rejected') { ?>
                                <span class="badge bg-danger">Rejected</span>
                            <?php } ?>
                        </td>
                        <td>
                            <?php 
                            if ($row['refund_status'] == 'Refunded') {
                                echo '<span class="badge bg-success">Refunded</span>';
                            } elseif ($row['refund_status'] == 'Pending') {
                                echo '<span class="badge bg-warning">Pending</span>';
                            } else {
                                echo '-';
                            }
                            ?>
                        </td> 
                        <td>
                            <?php if ($row['status'] == 'Pending') { ?>
                                <form method="POST" action="process_admin_return.php" style="display:inline;">
                                    <input type="hidden" name="request_id" value="<?php echo $row['id']; ?>">
                                    <input type="hidden" name="order_id" value="<?php echo $row['order_id']; ?>">
                                    <input type="hidden" name="product_id" value="<?php echo $row['product_id']; ?>">
                                    <input type="hidden" name="color" value="<?php echo $row['color']; ?>">
                                    <input type="hidden" name="size" value="<?php echo $row['size']; ?>">
                                    <input type="hidden" name="quantity" value="<?php echo $row['stock_quantity']; ?>">
                                    <button type="submit" name="approve_return" class="btn btn-success btn-sm">Approve</button>
                                    <button type="submit" name="reject_return" class="btn btn-danger btn-sm">Reject</button>
                                </form>
                            <?php } elseif ($row['status'] == 'Approved' && ($row['refund_status'] == '' || $row['refund_status'] == 'Pending')) { ?>
                                <form method="POST" action="process_admin_return.php" style="display:inline;">
                                    <input type="hidden" name="request_id" value="<?php echo $row['id']; ?>">
                                    <button type="submit" name="process_refund" class="btn btn-primary btn-sm">Process Refund</button>
                                </form>
                            <?php } else { ?>
                                <span class="badge bg-secondary">No Action</span>
                            <?php } ?>
                        </td>
                    </tr>
                <?php } ?>
            </tbody>
        </table>
    </div>
</div>

<?php include("include/footer.php"); ?>

<!-- Include SweetAlert2 -->
<script src="https://cdn.jsdelivr.net/npm/sweetalert2@11"></script>

<script>
document.addEventListener("DOMContentLoaded", function() {
    <?php if (isset($_SESSION['success'])) { ?>
        Swal.fire({
            icon: 'success',
            title: 'Success',
            text: '<?php echo $_SESSION['success']; ?>',
            confirmButtonText: 'OK'
        });
    <?php unset($_SESSION['success']); } ?>

    <?php if (isset($_SESSION['error'])) { ?>
        Swal.fire({
            icon: 'error',
            title: 'Error',
            text: '<?php echo $_SESSION['error']; ?>',
            confirmButtonText: 'OK'
        });
    <?php unset($_SESSION['error']); } ?>
});
</script>
