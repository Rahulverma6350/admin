
<?php
include 'include/db.php';

// Fetch refunds
$sql = "SELECT r.*, u.name FROM refunds r JOIN user_reg u ON r.user_id = u.id";
$result = $conn->query($sql);
?>

<table border="1">
    <thead>
        <tr>
            <th>Refund ID</th>
            <th>Username</th>
            <th>Order ID</th>
            <th>Amount</th>
            <th>Refund Status</th>
            <th>Refund Date</th>
        </tr>
    </thead>
    <tbody>
        <?php while ($row = $result->fetch_assoc()) { ?>
        <tr>
            <td><?php echo $row['id']; ?></td>
            <td><?php echo $row['name']; ?></td>
            <td><?php echo $row['order_id']; ?></td>
            <td>₹<?php echo $row['refund_amount']; ?></td>
            <td><?php echo $row['refund_status']; ?></td>
            <td><?php echo $row['refund_date']; ?></td>
        </tr>
        <?php } ?>
    </tbody>
</table>
